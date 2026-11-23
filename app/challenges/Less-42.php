<?php
// Less-42 OA 后台登录
if (!defined('SQLI_MALL')) { die('forbidden'); }
return [
  'title' => 'OA 后台登录',
  'desc'  => 'OA 管理后台登录入口。',
  'method' => 'GET',
  'form' => ['method' => 'GET', 'fields' => array (
  'login_user' => '管理员账号',
  'login_password' => '密码',
), 'submit' => '登 录'],
  'difficulty' => 3,
  'task' => '两个输入只有一个做了转义——找出没转义的那个，用多语句改数据',
  'hints' => array (
  0 => '账号做了数据库层转义，密码栏没有。注入点在密码参数，单引号闭合。',
  1 => '支持多语句：登录成功与否不重要，第二句 UPDATE 会照样执行。',
  2 => '示例：login_user=admin&login_password=1\';update oa_employee set password=\'pwned\' where id=1-- -',
),
  'run' => function ($app) {
    if (!isset($_POST['login_user']) || !isset($_POST['login_password'])) return $app->info('请输入账号与密码。');
    $uname = $_POST['login_user'];
    $passwd = $_POST['login_password'];
    $uname = mysqli_real_escape_string($app->conn, $uname);
    $sql = "SELECT username, name FROM oa_employee WHERE username='{$uname}' and password='{$passwd}'";
    $res = $app->q_multi($sql);
    $row = $app->fetch($res);
    if ($row) {
    return $app->card(['姓名' => $row['name'], '登录名' => $row['username']]);
    }
    return $app->note($app->error());
  },
];
