<?php
// Less-15 账号找回
if (!defined('SQLI_MALL')) { die('forbidden'); }
return [
  'title' => '账号找回',
  'desc'  => '输入账号密码验证身份后找回。',
  'method' => 'GET',
  'form' => ['method' => 'GET', 'fields' => array (
  'uname' => '账号',
  'passwd' => '密码',
), 'submit' => '验 证'],
  'difficulty' => 3,
  'task' => '页面无差异回显，用响应时间盲注提取数据',
  'hints' => array (
  0 => '成功与失败话术不同但都不给数据，报错也不显示。此时只剩一个可观测维度：响应时间。',
  1 => 'POST 时间盲注，if + sleep。',
  2 => '示例：uname=admin\' and if(length((select password from oa_employee where username=\'admin\'))>5,sleep(5),0)-- -&passwd=1',
),
  'run' => function ($app) {
    if (!isset($_POST['uname']) || !isset($_POST['passwd'])) return $app->info('请输入账号与密码。');
    $uname = $_POST['uname'];
    $passwd = $_POST['passwd'];
    $sql = "SELECT username, name FROM oa_employee WHERE username='{$uname}' and password='{$passwd}' LIMIT 0,1";
    $res = $app->q($sql);
    $row = $app->fetch($res);
    if ($row) {
    return $app->info('身份验证通过。');
    }
    return $app->info('账号或密码错误，请重试。');
  },
];
