<?php
// Less-19 登录来源审计（Referer 直接进 INSERT → 头部报错注入）
if (!defined('SQLI_MALL')) { die('forbidden'); }

function less19_check_input($conn, $value)
{
    if (!empty($value)) $value = substr($value, 0, 20);
    if (!ctype_digit($value)) {
        $value = "'" . mysqli_real_escape_string($conn, $value) . "'";
    } else {
        $value = intval($value);
    }
    return $value;
}

return [
  'title' => '登录来源审计',
  'desc'  => '单点登录网关会记录每次登录的访问来源，用于异地登录告警与来源统计。',
  'method' => 'POST',
  'form' => ['method' => 'POST', 'fields' => ['uname' => '登录名', 'passwd' => '密码'], 'submit' => '登 录'],
  'difficulty' => 3,
  'task' => 'Referer 请求头未做处理就进了 INSERT 语句，用它把员工表数据带出来',
  'hints' => [
    '页面统计的是“访问来源”，对应 HTTP 请求里的 Referer 头。登录成功后 Referer 会被原样写入审计表。',
    '先用合法账号登录（admin / Xc@Admin2024），再在 Burp 里改 Referer 闭合单引号。',
    "示例（改 Referer）：Referer: 1' and updatexml(1,concat(0x7e,(select group_concat(username,0x3a,password) from oa_employee),0x7e),1) and '1'='1",
  ],
  'run' => function ($app) {
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    $IP      = $_SERVER['REMOTE_ADDR'] ?? '';
    echo $app->info('您的来源 IP：' . $IP);
    if (!isset($_POST['uname']) || !isset($_POST['passwd'])) return $app->info('请先登录，网关将在登录时记录来源信息。');
    $uname  = less19_check_input($app->conn, $_POST['uname']);
    $passwd = less19_check_input($app->conn, $_POST['passwd']);

    $res = $app->q("SELECT username, name FROM oa_employee WHERE username=$uname and password=$passwd ORDER BY id DESC LIMIT 0,1");
    $row1 = $app->fetch($res);
    if ($row1) {
      $app->q("INSERT INTO `security`.`referers` (`referer`, `ip_address`, `username`) VALUES ('$referer', '$IP', $uname)");
      $out = $app->info('登录成功，来源已记录。');
      if ($app->error()) $out .= $app->note($app->error());
      return $out;
    }
    return $app->note($app->error());
  },
];
