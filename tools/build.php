<?php
/**
 * 构建脚本：根据配置生成 app/challenges/Less-*.php（星辰OA 版）
 * 仅用于开发期批量生成，不随部署包发布
 */
define('APP', dirname(__DIR__) . '/app');

function php_str($s)
{
    $s = str_replace('\\', '\\\\', $s);
    $s = str_replace('"', '\\"', $s);
    return '"' . $s . '"';
}

function meta_block($c, $extra = [])
{
    $out  = "  'title' => " . var_export($c['title'], true) . ",\n";
    $out .= "  'desc'  => " . var_export($c['desc'], true) . ",\n";
    $out .= "  'method' => " . var_export($c['method'] ?? 'GET', true) . ",\n";
    if (isset($c['form'])) {
        $out .= "  'form' => ['method' => " . var_export($c['method'] ?? 'GET', true) . ", 'fields' => "
              . var_export($c['form'], true) . ", 'submit' => " . var_export($c['submit'] ?? '查 询', true) . "],\n";
    } else {
        $out .= "  'form' => null,\n";
    }
    foreach ($extra as $k => $v) $out .= "  '$k' => " . var_export($v, true) . ",\n";
    $out .= "  'difficulty' => " . (int)$c['d'] . ",\n";
    $out .= "  'task' => " . var_export($c['task'], true) . ",\n";
    $out .= "  'hints' => " . var_export($c['h'], true) . ",\n";
    return $out;
}

function card_code($fields)
{
    $pairs = [];
    foreach ($fields as $f) $pairs[] = var_export($f[0], true) . ' => $row[' . var_export($f[1], true) . ']';
    return '$app->card([' . implode(', ', $pairs) . '])';
}

function filter_code($filters, $var = '$id')
{
    $code = '';
    foreach ($filters as $f) {
        $code .= "    $var = preg_replace(" . var_export($f[0], true) . ', ' . var_export($f[1], true) . ", $var);\n";
    }
    return $code;
}

function escape_code($mode, $var = '$id')
{
    switch ($mode) {
        case 'addslashes': return "    $var = addslashes($var);\n";
        case 'real':       return "    $var = mysqli_real_escape_string(\$app->conn, $var);\n";
        case 'less32':     return "    $var = oa_escape_backslash($var);\n";
        default:           return '';
    }
}

function waf_code($p = 'id')
{
    $len = strlen($p) + 1;
    return <<<PHP
    // 前置 WAF：检查 QUERY_STRING 中第一个 {$p} 参数
    \$qs = \$_SERVER['QUERY_STRING'] ?? '';
    \$wafInput = null;
    foreach (explode('&', \$qs) as \$kv) {
      if (substr(\$kv, 0, {$len}) === '{$p}=') { \$wafInput = substr(\$kv, {$len}, 40); break; }
    }
    if (\$wafInput !== null && !preg_match('/^\d+$/', \$wafInput)) {
      return \$app->note('【安全网关】请求已被 WAF 拦截：参数不合法。');
    }

PHP;
}

function fail_code($c)
{
    if (isset($c['fail']) && $c['fail'] === '') return "    return '';";
    if (isset($c['fail']))    return "    return \$app->info(" . var_export($c['fail'], true) . ");";
    if (($c['err'] ?? false) === true)    return "    return \$app->note(\$app->error());";
    if (($c['err'] ?? false) === 'fixed') return "    return \$app->note(" . var_export($c['fixed'] ?? '', true) . ");";
    return "    return \$app->info('');";
}

function ok_code($c)
{
    if (!empty($c['info_ok'])) return "    return \$app->info(" . var_export($c['info_ok'], true) . ");";
    return "    return " . card_code($c['f']) . ";";
}

function build_get($c)
{
    $p    = $c['param'] ?? 'id';           // 真实业务参数名（sort / keyword / id ...）
    $sql  = str_replace('{id}', '{$' . $p . '}', php_str($c['sql']));
    $body = "    if (!isset(\$_GET['$p'])) return \$app->info(" . var_export($c['empty'] ?? '请输入编号进行查询。', true) . ");\n";
    $body .= "    \$$p = \$_GET['$p'];\n";
    if (!empty($c['filters'])) $body .= filter_code($c['filters'], '$' . $p);
    if (!empty($c['waf']))     $body .= waf_code($p);
    $body .= escape_code($c['esc'] ?? 'none', '$' . $p);
    $body .= "    \$sql = $sql;\n";
    $body .= ($c['multi'] ?? false) ? "    \$res = \$app->q_multi(\$sql);\n" : "    \$res = \$app->q(\$sql);\n";
    if (!empty($c['table'])) {
        $cols = var_export($c['cols'] ?? ['id', 'emp_no', 'name'], true);
        $body .= "    if (!\$res) {\n" . fail_code($c) . "\n    }\n";
        $body .= "    return \$app->table(\$app->fetch_all(\$res), $cols);\n";
    } else {
        $body .= "    \$row = \$app->fetch(\$res);\n";
        $body .= "    if (\$row) {\n" . ok_code($c) . "\n    }\n";
        $body .= fail_code($c);
    }

    $code = "<?php\n// {$c['id']} {$c['title']}\nif (!defined('SQLI_MALL')) { die('forbidden'); }\n";
    $code .= "return [\n";
    $code .= meta_block($c, isset($c['set_names']) ? ['set_names' => $c['set_names']] : []);
    $code .= "  'run' => function (\$app) {\n" . $body . "\n  },\n];\n";
    return $code;
}

function build_login($c)
{
    $sqlU = str_replace('{u}', '{$uname}', php_str($c['sql']));
    $sqlU = str_replace('{p}', '{$passwd}', $sqlU);
    $keys = array_keys($c['form']);
    $fu = $keys[0]; $fp = $keys[1];

    $body  = "    if (!isset(\$_POST['$fu']) || !isset(\$_POST['$fp'])) return \$app->info('请输入账号与密码。');\n";
    $body .= "    \$uname = \$_POST['$fu'];\n    \$passwd = \$_POST['$fp'];\n";
    $body .= escape_code($c['esc'] ?? 'none', '$uname');
    if (!empty($c['escp'])) $body .= "    \$passwd = " . $c['escp'] . "(\$passwd);\n";
    $body .= "    \$sql = $sqlU;\n";
    $body .= ($c['multi'] ?? false) ? "    \$res = \$app->q_multi(\$sql);\n" : "    \$res = \$app->q(\$sql);\n";
    $body .= "    \$row = \$app->fetch(\$res);\n";
    $body .= "    if (\$row) {\n" . ok_code($c) . "\n    }\n";
    $body .= fail_code($c);

    $code = "<?php\n// {$c['id']} {$c['title']}\nif (!defined('SQLI_MALL')) { die('forbidden'); }\n";
    $code .= "return [\n";
    $code .= meta_block($c, isset($c['set_names']) ? ['set_names' => $c['set_names']] : []);
    $code .= "  'run' => function (\$app) {\n" . $body . "\n  },\n];\n";
    return $code;
}

function build_series($c)
{
    $code  = "<?php\n// {$c['id']} 内部演练接口（挑战关）\nif (!defined('SQLI_MALL')) { die('forbidden'); }\n";
    $code .= "require_once __DIR__ . '/_series.php';\n";
    $code .= "return series_def([\n";
    $code .= "  'times' => 10, 'difficulty' => " . (int)$c['d'] . ", 'error' => " . ($c['err'] ? 'true' : 'false') . ",\n";
    $code .= "  'sql' => " . var_export($c['sql'], true) . ",\n";
    $code .= "  'hints' => " . var_export($c['h'], true) . ",\n";
    $code .= "]);\n";
    return $code;
}

/* ---------- 执行 ---------- */
$configs = array_merge(
    include __DIR__ . '/cfg_get.php',
    include __DIR__ . '/cfg_login.php',
    include __DIR__ . '/cfg_series.php'
);
$n = 0;
foreach ($configs as $c) {
    if (!empty($c['series']))                 $code = build_series($c);
    elseif (($c['type'] ?? 'get') === 'login') $code = build_login($c);
    else                                       $code = build_get($c);
    file_put_contents(APP . '/challenges/' . $c['id'] . '.php', $code);
    $n++;
}
echo "generated $n challenge files\n";
