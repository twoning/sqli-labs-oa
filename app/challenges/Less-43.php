<?php
// Less-43 运营后台登录
if (!defined('SQLI_MALL')) { die('forbidden'); }
return [
  'title' => '运营后台登录',
  'desc'  => '运营管理后台登录入口。',
  'method' => 'GET',
  'form' => ['method' => 'GET', 'fields' => array (
  'login_user' => '账号',
  'login_password' => '密码',
), 'submit' => '登 录'],
  'difficulty' => 3,
  'task' => '密码参数括号闭合 + 多语句',
  'hints' => array (
  0 => '仍是密码参数未转义，但闭合方式多了括号：username=(\'...\')。',
  1 => 'payload 先补 ) 再分号接第二条语句。',
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
    return $app->note($app->error());
  },
];
