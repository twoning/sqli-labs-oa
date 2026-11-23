<?php
// Less-34 人事系统登录
if (!defined('SQLI_MALL')) { die('forbidden'); }
return [
  'title' => '人事系统登录',
  'desc'  => '老版人事系统登录（GBK 编码），输入经 addslashes 转义。',
  'method' => 'GET',
  'form' => ['method' => 'GET', 'fields' => array (
  'uname' => '账号',
  'passwd' => '密码',
), 'submit' => '登 录'],
  'set_names' => 'gbk',
  'difficulty' => 4,
  'task' => 'POST 参数同样存在宽字节问题',
  'hints' => array (
  0 => 'POST 参数做了 addslashes 转义，但 GBK 连接下宽字节照样有效。',
  1 => '账号栏输入 %df%27（URL 编码提交）即可逃逸引号。',
  2 => '示例：uname=1%df%27 union select username,password from oa_employee-- -&passwd=1',
),
  'run' => function ($app) {
    if (!isset($_POST['uname']) || !isset($_POST['passwd'])) return $app->info('请输入账号与密码。');
    $uname = $_POST['uname'];
    $passwd = $_POST['passwd'];
    $uname = addslashes($uname);
    $sql = "SELECT username, name FROM oa_employee WHERE username='{$uname}' and password='{$passwd}' LIMIT 0,1";
    $res = $app->q($sql);
    $row = $app->fetch($res);
    if ($row) {
    return $app->card(['姓名' => $row['name'], '登录名' => $row['username']]);
    }
    return $app->note($app->error());
  },
];
