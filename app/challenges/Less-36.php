<?php
// Less-36 人事系统接口（三）
if (!defined('SQLI_MALL')) { die('forbidden'); }
return [
  'title' => '人事系统接口（三）',
  'desc'  => '人事系统接口，输入经数据库层转义函数处理（GBK 连接）。',
  'method' => 'GET',
  'form' => ['method' => 'GET', 'fields' => array (
  'id' => '员工编号',
), 'submit' => '查 询'],
  'set_names' => 'gbk',
  'difficulty' => 4,
  'task' => '数据库转义函数也无法完全防御 GBK 宽字节',
  'hints' => array (
  0 => '用的是数据库转义函数，比 addslashes 专业；但连接是 SET NAMES gbk，转义函数以为在单字节世界工作。',
  1 => '依旧是宽字节：%df%27。',
  2 => '示例：?id=-1%df%27%20union%20select%201,group_concat(username,0x3a,password),3%20from%20oa_employee--+',
),
  'run' => function ($app) {
    if (!isset($_GET['id'])) return $app->info('请输入员工编号。');
    $id = $_GET['id'];
    $id = mysqli_real_escape_string($app->conn, $id);
    $sql = "SELECT id, emp_no, name FROM oa_employee WHERE id='{$id}' LIMIT 0,1";
    $res = $app->q($sql);
    $row = $app->fetch($res);
    if ($row) {
    return $app->card(['工号' => $row['emp_no'], '姓名' => $row['name']]);
    }
    return $app->note($app->error());
  },
];
