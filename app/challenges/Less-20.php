<?php
// Less-20 员工工作台（Cookie uname 未过滤，直接拼进 SELECT → Cookie 注入）
if (!defined('SQLI_MALL')) { die('forbidden'); }

function less20_check_input($conn, $value)
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
  'title' => '员工工作台',
  'desc'  => '欢迎回来！您的登录状态保存在浏览器 Cookie 中，进入工作台后自动加载个人档案。',
  'method' => 'POST',
  'form' => ['method' => 'POST', 'fields' => ['uname' => '登录名', 'passwd' => '密码'], 'submit' => '登 录'],
  'difficulty' => 3,
  'task' => '页面展示的身份信息来自 Cookie，检查服务端是否无条件信任了它，并借它拿到全部员工的登录名与密码',
  'hints' => [
    '先正常登录一次（admin / Xc@Admin2024），登录后服务端会种下 uname Cookie，并直接拿它查询数据库。',
    'Cookie 值未经过滤。用 Burp 或浏览器控制台改 Cookie：uname=admin\' 构造单引号闭合，报错会回显。',
    "示例（改 Cookie）：Cookie: uname=-1' union select 1,group_concat(username,0x3a,password),3 from oa_employee#   （Cookie 里不能靠 + 变空格，注释用 # 最稳）",
  ],
  'run' => function ($app) {
    // 登录流程：种 Cookie
    if (isset($_POST['uname']) && isset($_POST['passwd'])) {
      $uname  = less20_check_input($app->conn, $_POST['uname']);
      $passwd = less20_check_input($app->conn, $_POST['passwd']);
      $res = $app->q("SELECT username, name FROM oa_employee WHERE username=$uname and password=$passwd ORDER BY id DESC LIMIT 0,1");
      $row = $app->fetch($res);
      if ($row) {
        setcookie('uname', $row['username'], time() + 3600);
        return $app->info('登录成功，正在进入工作台……') . '<script>setTimeout(function(){location.reload()},600)</script>';
      }
      return $app->info('账号或密码错误。') . $app->note($app->error());
    }

    // Cookie 模式：直接用 Cookie 查询
    if (!isset($_COOKIE['uname'])) {
      return $app->info('请先登录，登录状态将保存在 Cookie 中（有效期 1 小时）。');
    }
    $cookee = $_COOKIE['uname'];
    $res = $app->q("SELECT emp_no, name, dept FROM oa_employee WHERE username='$cookee' LIMIT 0,1");
    $row = $app->fetch($res);
    $out = $app->info('当前会话身份（Cookie uname）：' . $cookee);
    if ($row) {
      $out .= $app->card(['工号' => $row['emp_no'], '姓名' => $row['name'], '所属部门' => $row['dept']]);
    } elseif ($app->error()) {
      $out .= $app->note('系统异常：' . $app->error());
    } else {
      $out .= $app->info('未查询到员工档案，Cookie 可能已失效。');
    }
    return $out;
  },
];
