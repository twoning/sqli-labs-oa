<?php
// Less-44 客服工作台登录
if (!defined('SQLI_MALL')) { die('forbidden'); }
return [
  'title' => '客服工作台登录',
  'desc'  => '客服团队工作台入口（无报错回显）。',
  'method' => 'GET',
  'form' => ['method' => 'GET', 'fields' => array (
  'login_user' => '账号',
  'login_password' => '密码',
), 'submit' => '登 录'],
  'difficulty' => 4,
  'task' => '同 Less-42 但无报错回显，盲注验证后执行多语句',
  'hints' => array (
  0 => '账号转义、密码未转义、单引号闭合——但失败不回显报错。',
  1 => '先用布尔差异（登录成功/失败的页面话术）确认注入，再执行多语句。',
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
    return $app->info('账号或密码错误。');
  },
];
