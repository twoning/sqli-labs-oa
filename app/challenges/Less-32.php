<?php
// Less-32 人事系统接口
if (!defined('SQLI_MALL')) { die('forbidden'); }
return [
  'title' => '人事系统接口',
  'desc'  => '老版人事系统数据接口（GBK 编码），输入经反斜杠转义。',
  'method' => 'GET',
  'form' => ['method' => 'GET', 'fields' => array (
  'id' => '员工编号',
), 'submit' => '查 询'],
  'set_names' => 'gbk',
  'difficulty' => 4,
  'task' => '引号被转义了，但连接编码是 GBK——想想多字节字符和“\\”的关系',
  'hints' => array (
  0 => '引号前会被加上反斜杠。但转义插入的 \\ 在 GBK 里可能不是独立字符：0xdf + \\ 会拼成一个合法汉字。',
  1 => '宽字节注入：%df%27 让反斜杠被“吃”进前一个字节。',
  2 => '示例：?id=-1%df%27%20union%20select%201,group_concat(username,0x3a,password),3%20from%20oa_employee--+',
),
  'run' => function ($app) {
    if (!isset($_GET['id'])) return $app->info('请输入员工编号。');
    $id = $_GET['id'];
    $id = oa_escape_backslash($id);
    $sql = "SELECT id, emp_no, name FROM oa_employee WHERE id='{$id}' LIMIT 0,1";
    $res = $app->q($sql);
    $row = $app->fetch($res);
    if ($row) {
    return $app->card(['工号' => $row['emp_no'], '姓名' => $row['name']]);
    }
    return $app->note($app->error());
  },
];
