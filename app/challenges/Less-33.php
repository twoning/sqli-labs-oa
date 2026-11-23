<?php
// Less-33 人事系统接口（二）
if (!defined('SQLI_MALL')) { die('forbidden'); }
return [
  'title' => '人事系统接口（二）',
  'desc'  => '老版人事系统查询接口，输入经 addslashes() 处理。',
  'method' => 'GET',
  'form' => ['method' => 'GET', 'fields' => array (
  'id' => '工号',
), 'submit' => '查 询'],
  'set_names' => 'gbk',
  'difficulty' => 4,
  'task' => 'addslashes 不认识多字节编码，用宽字节绕过',
  'hints' => array (
  0 => 'addslashes 会在引号前加 \\。GBK 编码下 0xdf+0x5c 会拼成一个汉字。',
  1 => '宽字节：%df%27。',
  2 => '示例：?id=-1%df%27%20union%20select%201,group_concat(username,0x3a,password),3%20from%20oa_employee--+',
),
  'run' => function ($app) {
    if (!isset($_GET['id'])) return $app->info('请输入工号。');
    $id = $_GET['id'];
    $id = addslashes($id);
    $sql = "SELECT id, emp_no, name FROM oa_employee WHERE id='{$id}' LIMIT 0,1";
    $res = $app->q($sql);
    $row = $app->fetch($res);
    if ($row) {
    return $app->card(['工号' => $row['emp_no'], '姓名' => $row['name']]);
    }
    return $app->note($app->error());
  },
];
