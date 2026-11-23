<?php
// Less-37 人事系统登录（二）
if (!defined('SQLI_MALL')) { die('forbidden'); }
return [
  'title' => '人事系统登录（二）',
  'desc'  => '人事系统登录（数据库层转义，GBK 连接）。',
  'method' => 'GET',
  'form' => ['method' => 'GET', 'fields' => array (
  'uname' => '账号',
  'passwd' => '密码',
), 'submit' => '登 录'],
  'set_names' => 'gbk',
  'difficulty' => 4,
  'task' => 'POST 双参数转义，宽字节依旧有效',
  'hints' => array (
  0 => '与上一关同款转义，只是换成了数据库层函数。',
  1 => '账号栏提交 %df%27 逃逸引号。',
  2 => '示例：uname=1%df%27 or 1=1--+&passwd=1 先登录成功再 union 提数',
),
  'run' => function ($app) {
    if (!isset($_POST['uname']) || !isset($_POST['passwd'])) return $app->info('请输入账号与密码。');
    $uname = $_POST['uname'];
    $passwd = $_POST['passwd'];
    $uname = mysqli_real_escape_string($app->conn, $uname);
    $sql = "SELECT username, name FROM oa_employee WHERE username='{$uname}' and password='{$passwd}' LIMIT 0,1";
    $res = $app->q($sql);
    $row = $app->fetch($res);
    if ($row) {
    return $app->card(['姓名' => $row['name'], '登录名' => $row['username']]);
    }
    return $app->note($app->error());
  },
];
