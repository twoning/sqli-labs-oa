<?php
/**
 * 挑战关（Less-54~65）共享逻辑：
 * - 查询 oa_notice（可注入）
 * - 目标：从 challenges 库的随机表里拿到 secret 密钥并提交
 * - 限 10 次请求，超次自动重置（随机表名/列名/密钥）
 */
if (!defined('SQLI_MALL')) { die('forbidden'); }

function series_meta()
{
    $f = __DIR__ . '/../data/challenge_series.json';
    $j = @file_get_contents($f);
    $d = $j ? json_decode($j, true) : null;
    return is_array($d) ? $d : null;
}

/** 重置挑战库（重新随机表名/列名/密钥） */
function series_reset()
{
    include dirname(__DIR__, 2) . '/sql-connections/db-creds.inc';
    @$c = new mysqli($host, $dbuser, $dbpass);
    if ($c->connect_errno) return false;
    $c->query("DROP DATABASE IF EXISTS $dbname1");
    $c->query("CREATE DATABASE $dbname1 CHARACTER SET utf8mb4");
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $gen = function ($len) use ($chars) {
        $s = '';
        for ($i = 0; $i < $len; $i++) $s .= $chars[rand(0, strlen($chars) - 1)];
        return $s;
    };
    $table = $gen(10);
    $col   = 'secret_' . $gen(4);
    $c->query("CREATE TABLE $dbname1.$table (id INT(2) UNSIGNED NOT NULL DEFAULT 1, sessid CHAR(32) PRIMARY KEY NOT NULL, $col CHAR(32) NOT NULL, tryy INT(11) UNSIGNED NOT NULL DEFAULT 0)");
    $sec = '';
    $chars2 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    for ($i = 0; $i < 24; $i++) $sec .= $chars2[rand(0, strlen($chars2) - 1)];
    $hash = md5(rand(0, 100000));
    $c->query("INSERT INTO $dbname1.$table VALUES (1, '$hash', '$sec', 0)");
    file_put_contents(__DIR__ . '/../data/challenge_series.json', json_encode(['table' => $table, 'secret_col' => $col]));
    return true;
}

function series_def($opts)
{
    return [
        'title' => '内部安全演练接口',
        'desc'  => '信息中心开放的内部联调接口，带请求频次限制（' . $opts['times'] . ' 次）。目标：注入拿到 challenges 库随机表中的 secret 密钥。每次重置，表名、列名、密钥全部随机。',
        'method' => 'GET',
        'form' => ['method' => 'GET', 'fields' => ['id' => '编号'], 'submit' => '查 询'],
        'challenge_series' => true,
        'db' => 'security',
        'difficulty' => $opts['difficulty'],
        'task' => '注入获取随机表名 → 字段名 → secret 密钥 → 提交验证',
        'hints' => $opts['hints'],
        'run' => function ($app) use ($opts) {
            $meta = series_meta();
            if (!$meta) {
                return $app->info('挑战环境尚未初始化，请先到 <a href="setup.php">环境重置</a> 页面完成初始化。');
            }
            $table = $meta['table'];
            $col   = $meta['secret_col'];

            // 尝试次数统计（走 challenges 库）
            if (isset($_GET['id'])) {
                $app->q("UPDATE challenges.$table SET tryy=tryy+1 WHERE id=1");
                $r = $app->fetch_row($app->q("SELECT tryy FROM challenges.$table WHERE id=1"));
                $tryy = $r ? (int)$r[0] : 0;
                if ($tryy > $opts['times']) {
                    series_reset();
                    return $app->info("已超过 {$opts['times']} 次尝试上限，挑战环境已重置（随机表名/密钥已更换），请重新开始。");
                }
            }

            // 提交密钥
            if (($_POST['action'] ?? '') === 'key') {
                $key = addslashes($_POST['key'] ?? '');
                $res = $app->q("SELECT 1 FROM challenges.$table WHERE $col='$key'");
                $row = $app->fetch_row($res);
                if ($row) {
                    return $app->info('✔ 密钥正确，挑战通关！表名 ' . $table . ' 的 ' . $col . ' 已被你成功注入提取。');
                }
                return $app->info('密钥不正确，请继续尝试。');
            }

            // 主查询（注入点）
            $out = '';
            $r = $app->fetch_row($app->q("SELECT tryy FROM challenges.$table WHERE id=1"));
            $tryy = $r ? (int)$r[0] : 0;
            $out .= $app->info("当前尝试次数：{$tryy} / {$opts['times']}");

            if (isset($_GET['id'])) {
                $id = $_GET['id'];
                $sql = str_replace('{id}', $id, $opts['sql']);
                $res = $app->q($sql);
                $row = $app->fetch($res);
                if ($row) {
                    $out .= $app->card(['公文标题' => $row['title'] ?? '', '正文内容' => $row['content'] ?? '']);
                } elseif ($opts['error'] && $app->error()) {
                    $out .= $app->note($app->error());
                }
            } else {
                $out .= $app->info('注入目标：oa_notice 表（id 参数）。最终目标：challenges 库随机表中的 secret 密钥。');
            }

            // 密钥提交表单
            $out .= '<form class="biz-form" method="POST" action=""><input type="hidden" name="action" value="key">'
                  . '<label class="biz-field"><span>提交 Secret Key</span><input type="text" name="key" autocomplete="off"></label>'
                  . '<button type="submit" class="btn btn-primary">提 交</button></form>';
            return $out;
        },
    ];
}
