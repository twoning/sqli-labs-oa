<?php
// Less-11 员工登录
if (!defined('SQLI_MALL')) { die('forbidden'); }
return [
  'title' => '员工登录',
  'desc'  => '星辰OA 员工工作台登录入口。',
  'method' => 'GET',
  'form' => ['method' => 'GET', 'fields' => array (
  'uname' => '账号',
  'passwd' => '密码',
), 'submit' => '登 录'],
  'difficulty' => 1,
  'task' => '通过登录框注入，拿到 oa_employee 表全部账号密码',
  'hints' => array (
  0 => '登录框两个参数都进了 SQL。先在账号栏输入一个单引号，看是否报错——POST 参数和 GET 参数本质相同。',
  1 => '单引号闭合。可先万能钥匙登录，也可 union 提数（查询共 2 列）。',
  2 => '示例：uname=admin\' or 1=1-- -&passwd=1 直接登录；提数：uname=-1\' union select username,password from oa_employee-- -&passwd=1',
),
  'run' => function ($app) {
    if (!isset($_POST['uname']) || !isset($_POST['passwd'])) return $app->info('请输入账号与密码。');
    $uname = $_POST['uname'];
    $passwd = $_POST['passwd'];
    $sql = "SELECT username, name FROM oa_employee WHERE username='{$uname}' and password='{$passwd}' LIMIT 0,1";
    $res = $app->q($sql);
    $row = $app->fetch($res);
    if ($row) {
    return $app->card(['姓名' => $row['name'], '登录名' => $row['username']]);
    }
    return $app->note($app->error());
  },
];
