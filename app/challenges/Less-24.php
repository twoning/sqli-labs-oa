<?php
// Less-24 新员工账号激活（二次注入：注册时转义入库，改密时从会话取出原始值拼 SQL）
if (!defined('SQLI_MALL')) { die('forbidden'); }

function esc24($conn, $v) { return mysqli_real_escape_string($conn, $v); }

function form24($action, $fields, $submit)
{
    $h = '<form class="biz-form" method="POST" action=""><input type="hidden" name="action" value="' . e($action) . '">';
    foreach ($fields as $name => $label) {
        $h .= '<label class="biz-field"><span>' . e($label) . '</span>'
            . '<input type="text" name="' . e($name) . '" value="" autocomplete="off"></label>';
    }
    $h .= '<button type="submit" class="btn btn-primary">' . e($submit) . '</button></form>';
    return $h;
}

return [
  'title' => '新员工账号激活',
  'desc'  => '入职未开户的员工可在此自助注册登录名、激活账号，激活后可登录工作台并修改密码。',
  'method' => 'POST',
  'form' => null,
  'difficulty' => 4,
  'task' => '所有输入在“第一次进库”时都被正确转义——但数据“第二次被使用”时呢？让别人的账号密码变成你指定的值',
  'hints' => [
    '注册、登录、改密三个接口的每个参数都做了转义，表面上没有直接注入点。注意改密语句的 WHERE 条件来自“会话里保存的登录名”，而这个登录名是登录时从数据库原样取出来的。',
    '二次注入：注册一个登录名形如 lina\'# 的账号（转义后原样入库，值本身仍是 lina\'#），用它正常登录，再改密码——UPDATE 语句里的 username=\'lina\'# 会把后面的条件截断。',
    "流程：注册 登录名=lina'#（其他随便填）→ 用 lina'# 登录 → 修改密码提交任意值 → 此时 UPDATE 实际改的是 lina 的密码。之后用新密码登录 lina 验证。（换成 admin'# 也可以，但会影响其他关卡用 admin 登录，建议改完到 setup.php 重置）",
  ],
  'run' => function ($app) {
    if (session_status() === PHP_SESSION_NONE) { @session_start(); }
    $out = '';

    /* ---------- 注册 ---------- */
    if (($_POST['action'] ?? '') === 'register') {
      $emp_no   = esc24($app->conn, $_POST['emp_no'] ?? '');
      $name     = esc24($app->conn, $_POST['name'] ?? '');
      $username = esc24($app->conn, $_POST['username'] ?? '');
      $pass     = esc24($app->conn, $_POST['password'] ?? '');
      $re_pass  = esc24($app->conn, $_POST['re_password'] ?? '');
      if ($username === '' || $pass === '') return $app->info('登录名与密码不能为空。');
      if ($pass !== $re_pass) return $app->info('两次输入的密码不一致。');
      $res = $app->q("select count(*) from oa_employee where username='$username'");
      $row = $app->fetch_row($res);
      if ($row && $row[0] != 0) {
        return $app->info('该登录名已被占用，请换一个。');
      }
      $app->q("insert into oa_employee (emp_no, name, username, password, dept, title, email, mobile, hiredate)
               values('$emp_no', '$name', \"$username\", \"$pass\", '待分配', '待分配', '', '', curdate())");
      if ($app->error()) return $app->note('注册失败：' . $app->error());
      return $app->info('账号激活成功，请使用刚设置的登录名登录。');
    }

    /* ---------- 登录 ---------- */
    if (($_POST['action'] ?? '') === 'login') {
      $username = esc24($app->conn, $_POST['login_user'] ?? '');
      $password = esc24($app->conn, $_POST['login_password'] ?? '');
      $res = $app->q("SELECT * FROM oa_employee WHERE username='$username' and password='$password'");
      $row = $app->fetch($res);
      if ($row) {
        $_SESSION['username'] = $row['username'];   // 从库里原样取出，未再转义
        setcookie('Auth', 1, time() + 3600);
        return $app->info('登录成功，欢迎 ' . $row['name'] . '（' . $row['dept'] . '）。')
             . form24('chpass', ['current_password' => '当前密码', 'password' => '新密码', 're_password' => '确认新密码'], '修改密码');
      }
      return $app->info('账号或密码错误。');
    }

    /* ---------- 修改密码 ---------- */
    if (($_POST['action'] ?? '') === 'chpass') {
      if (empty($_SESSION['username'])) return $app->info('请先登录。');
      $username  = $_SESSION['username'];  // 未转义！来自数据库
      $curr_pass = esc24($app->conn, $_POST['current_password'] ?? '');
      $pass      = esc24($app->conn, $_POST['password'] ?? '');
      $re_pass   = esc24($app->conn, $_POST['re_password'] ?? '');
      if ($pass !== $re_pass) return $app->info('两次输入的新密码不一致。');
      $app->q("UPDATE oa_employee SET password='$pass' where username='$username' and password='$curr_pass'");
      if ($app->error()) return $app->note($app->error());
      if ($app->conn->affected_rows == 1) {
        return $app->info('密码修改成功。') . $app->note('执行语句：UPDATE oa_employee SET password=\'' . $pass . '\' where username=\'' . $username . '\' and password=\'' . $curr_pass . '\'');
      }
      return $app->info('修改失败：当前密码不正确。');
    }

    /* ---------- 默认视图 ---------- */
    if (!empty($_SESSION['username'])) {
      $out .= $app->info('当前登录：' . $_SESSION['username']);
      $out .= form24('chpass', ['current_password' => '当前密码', 'password' => '新密码', 're_password' => '确认新密码'], '修改密码');
    } else {
      $out .= $app->info('已激活的账号可直接登录；新入职员工请先激活账号。');
    }
    $out .= form24('login', ['login_user' => '登录名', 'login_password' => '密码'], '登 录');
    $out .= form24('register', ['emp_no' => '工号', 'name' => '姓名', 'username' => '设置登录名', 'password' => '设置密码', 're_password' => '确认密码'], '激活账号');
    return $out;
  },
];
