<?php
// Less-16 账号找回（双引号）
if (!defined('SQLI_MALL')) { die('forbidden'); }
return [
  'title' => '账号找回（双引号）',
  'desc'  => '账号找回验证入口（无报错回显）。',
  'method' => 'GET',
  'form' => ['method' => 'GET', 'fields' => array (
  'uname' => '账号',
  'passwd' => '密码',
), 'submit' => '验 证'],
  'difficulty' => 3,
  'task' => '无报错回显，用时间盲注或布尔差异提取数据',
  'hints' => array (
  0 => '闭合字符和上一关不同——双引号加括号。',
  1 => '闭合方式 username=("...")，POST 时间盲注。',
  2 => '示例：uname=1") and if(ascii(substr((select username from oa_employee limit 0,1),1,1))>100,sleep(5),0)-- -&passwd=1',
),
  'run' => function ($app) {
    if (!isset($_POST['uname']) || !isset($_POST['passwd'])) return $app->info('请输入账号与密码。');
    $uname = $_POST['uname'];
    $passwd = $_POST['passwd'];
    $sql = "SELECT username, name FROM oa_employee WHERE username=(\"{$uname}\") and password=(\"{$passwd}\") LIMIT 0,1";
    $res = $app->q($sql);
    $row = $app->fetch($res);
    if ($row) {
    return $app->info('验证通过。');
    }
    return $app->info('验证失败，请重试。');
  },
];
