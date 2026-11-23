<?php
// Less-12 渠道员工登录
if (!defined('SQLI_MALL')) { die('forbidden'); }
return [
  'title' => '渠道员工登录',
  'desc'  => '分公司员工专用登录入口。',
  'method' => 'GET',
  'form' => ['method' => 'GET', 'fields' => array (
  'uname' => '账号',
  'passwd' => '密码',
), 'submit' => '登 录'],
  'difficulty' => 2,
  'task' => '确认双引号括号闭合后提数',
  'hints' => array (
  0 => '单引号报错语法不匹配时，看报错里引号附近的完整片段——这里是双引号加括号。',
  1 => '闭合方式 username=("...")，先补 ") 再注释。',
  2 => '示例：uname=-1") union select username,password from oa_employee-- -&passwd=1',
),
  'run' => function ($app) {
    if (!isset($_POST['uname']) || !isset($_POST['passwd'])) return $app->info('请输入账号与密码。');
    $uname = $_POST['uname'];
    $passwd = $_POST['passwd'];
    $sql = "SELECT username, name FROM oa_employee WHERE username=(\"{$uname}\") and password=(\"{$passwd}\") LIMIT 0,1";
    $res = $app->q($sql);
    $row = $app->fetch($res);
    if ($row) {
    return $app->card(['姓名' => $row['name'], '登录名' => $row['username']]);
    }
    return $app->note($app->error());
  },
];
