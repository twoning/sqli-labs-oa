<?php
// Less-22 员工工作台（Cookie base64 编码 + 双引号闭合）
if (!defined('SQLI_MALL')) { die('forbidden'); }

function less22_check_input($conn, $value)
{
    if (!empty($value)) $value = substr($value, 0, 20);
    if (!ctype_digit($value)) {
        $value = "'" . mysqli_real_escape_string($conn, $value) . "'";
    } else {
        $value = intval($value);
    }
    return $value;
}

return [
  'title' => '员工工作台（移动端会话）',
  'desc'  => '移动端专用的会话标识，同样以编码形式保存在 Cookie 中。',
  'method' => 'POST',
  'form' => ['method' => 'POST', 'fields' => ['uname' => '登录名', 'passwd' => '密码'], 'submit' => '登 录'],
  'difficulty' => 4,
  'task' => '与上一关同样是编码 Cookie，但服务端的 SQL 拼接方式不同，闭合字符也变了',
  'hints' => [
    '同样是 base64 编码的 Cookie，但后端拼接时用的不是单引号。',
    '解码后闭合方式是双引号：username="..."。',
    "示例：payload 为 -1\" union select 1,group_concat(username,0x3a,password),3 from oa_employee-- +，base64 编码后设置 Cookie: uname=<结果>",
  ],
  'run' => function ($app) {
    if (isset($_POST['uname']) && isset($_POST['passwd'])) {
      $uname  = less22_check_input($app->conn, $_POST['uname']);
      $passwd = less22_check_input($app->conn, $_POST['passwd']);
      $res = $app->q("SELECT username, name FROM oa_employee WHERE username=$uname and password=$passwd ORDER BY id DESC LIMIT 0,1");
      $row = $app->fetch($res);
      if ($row) {
        setcookie('uname', base64_encode($row['username']), time() + 3600);
        return $app->info('登录成功，正在进入工作台……') . '<script>setTimeout(function(){location.reload()},600)</script>';
      }
      return $app->info('账号或密码错误。') . $app->note($app->error());
    }

    if (!isset($_COOKIE['uname'])) {
      return $app->info('请先登录。会话标识将以编码形式存入 Cookie。');
    }
    $cookee = base64_decode($_COOKIE['uname']);
    $res = $app->q('SELECT emp_no, name, dept FROM oa_employee WHERE username="' . $cookee . '" LIMIT 0,1');
    $row = $app->fetch($res);
    $out = $app->info('当前会话标识（Cookie）：uname = ' . $_COOKIE['uname']);
    if ($row) {
      $out .= $app->card(['工号' => $row['emp_no'], '姓名' => $row['name'], '所属部门' => $row['dept']]);
    } elseif ($app->error()) {
      $out .= $app->note('系统异常：' . $app->error());
    } else {
      $out .= $app->info('未查询到员工档案，会话可能已失效。');
    }
    return $out;
  },
];
