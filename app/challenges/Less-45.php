<?php
// Less-45 呼叫中心登录
if (!defined('SQLI_MALL')) { die('forbidden'); }
return [
  'title' => '呼叫中心登录',
  'desc'  => '呼叫中心坐席登录入口（无报错回显）。',
  'method' => 'GET',
  'form' => ['method' => 'GET', 'fields' => array (
  'login_user' => '坐席账号',
  'login_password' => '密码',
), 'submit' => '登 录'],
  'difficulty' => 4,
  'task' => '括号闭合 + 无报错回显 + 多语句',
  'hints' => array (
  0 => '密码参数未转义，闭合是 (\'...\')，失败无报错。',
  1 => '补 ) 后接多语句，用页面差异验证。',
  2 => '示例：login_user=admin&login_password=1\');update oa_employee set password=\'pwned\' where id=1-- -',
),
  'run' => function ($app) {
    if (!isset($_POST['login_user']) || !isset($_POST['login_password'])) return $app->info('请输入账号与密码。');
    $uname = $_POST['login_user'];
    $passwd = $_POST['login_password'];
    $uname = mysqli_real_escape_string($app->conn, $uname);
    $sql = "SELECT username, name FROM oa_employee WHERE username=('{$uname}') and password=('{$passwd}')";
    $res = $app->q_multi($sql);
    $row = $app->fetch($res);
    if ($row) {
    return $app->card(['姓名' => $row['name'], '登录名' => $row['username']]);
    }
    return $app->info('账号或密码错误。');
  },
];
