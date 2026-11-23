<?php
// Less-18 终端准入登记（登录后记录 User-Agent，UA 直接进 INSERT → 头部报错注入）
if (!defined('SQLI_MALL')) { die('forbidden'); }

function less18_check_input($conn, $value)
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
  'title' => '终端准入登记',
  'desc'  => '为满足等保要求，登录后系统会采集终端浏览器标识并写入准入审计日志。',
  'method' => 'POST',
  'form' => ['method' => 'POST', 'fields' => ['uname' => '登录名', 'passwd' => '密码'], 'submit' => '登记并登录'],
  'difficulty' => 3,
  'task' => '表单参数都做了处理？登录请求里还有一个字段被原样写进了 INSERT 语句',
  'hints' => [
    '账号密码都经过转义，打不动。但页面说“采集终端浏览器标识”——想想哪个请求头会被写进数据库。',
    'User-Agent 未经处理直接进 INSERT 语句。需要先用合法账号（admin / Xc@Admin2024）登录成功，插入才会执行；UA 里闭合单引号构造报错注入。',
    "示例（Burp 抓包改 UA）：User-Agent: 1' and extractvalue(1,concat(0x7e,(select group_concat(username,0x3a,password) from oa_employee),0x7e)) and '1'='1",
  ],
  'run' => function ($app) {
    $uagent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $IP     = $_SERVER['REMOTE_ADDR'] ?? '';
    echo $app->info('您的终端 IP：' . $IP);
    if (!isset($_POST['uname']) || !isset($_POST['passwd'])) return $app->info('请先完成登录，系统将在登录后采集终端信息。');
    $uname  = less18_check_input($app->conn, $_POST['uname']);
    $passwd = less18_check_input($app->conn, $_POST['passwd']);

    $res = $app->q("SELECT username, name FROM oa_employee WHERE username=$uname and password=$passwd ORDER BY id DESC LIMIT 0,1");
    $row1 = $app->fetch($res);
    if ($row1) {
      $app->q("INSERT INTO `security`.`uagents` (`uagent`, `ip_address`, `username`) VALUES ('$uagent', '$IP', $uname)");
      $out = $app->info('登记成功，已记录终端标识：' . $uagent);
      if ($app->error()) $out .= $app->note($app->error());
      return $out;
    }
    return $app->note($app->error());
  },
];
