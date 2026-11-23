<?php
// Less-13 企业邮箱登录
if (!defined('SQLI_MALL')) { die('forbidden'); }
return [
  'title' => '企业邮箱登录',
  'desc'  => '企业邮箱单点登录入口，登录后不显示账号信息。',
  'method' => 'GET',
  'form' => ['method' => 'GET', 'fields' => array (
  'uname' => '邮箱账号',
  'passwd' => '密码',
), 'submit' => '登 录'],
  'difficulty' => 2,
  'task' => '登录成功不回显数据，改用报错注入提取员工表',
  'hints' => array (
  0 => '成功只有提示、没有数据；但失败时页面会把 SQL 报错打出来。',
  1 => '报错信息是数据出口：extractvalue / updatexml。闭合是 (\'...\')。',
  2 => '示例：uname=admin\') and extractvalue(1,concat(0x7e,(select password from oa_employee where username=\'admin\'),0x7e))-- -&passwd=1',
),
  'run' => function ($app) {
    if (!isset($_POST['uname']) || !isset($_POST['passwd'])) return $app->info('请输入账号与密码。');
    $uname = $_POST['uname'];
    $passwd = $_POST['passwd'];
    $sql = "SELECT username, name FROM oa_employee WHERE username=('{$uname}') and password=('{$passwd}') LIMIT 0,1";
    $res = $app->q($sql);
    $row = $app->fetch($res);
    if ($row) {
    return $app->info('登录成功，正在进入工作台……');
    }
    return $app->note($app->error());
  },
];
