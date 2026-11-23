<?php
// Less-26a 外出登记（盲）
if (!defined('SQLI_MALL')) { die('forbidden'); }
return [
  'title' => '外出登记（盲）',
  'desc'  => '查询外出报备状态，页面只提示是否登记成功。',
  'method' => 'GET',
  'form' => ['method' => 'GET', 'fields' => array (
  'id' => '报备单号',
), 'submit' => '查 询'],
  'difficulty' => 5,
  'task' => '同款过滤 + 括号闭合 + 无报错，布尔盲注',
  'hints' => array (
  0 => '闭合多了一层括号：id=(\'...\')；失败不显示报错。',
  1 => '括号闭合 + 双写 + %0a 替代空格 + 布尔盲注。',
  2 => '示例：?id=1\')%0aoorr%0a(select length(group_concat(username,0x3a,passwoorrd)) from oa_employee)>100%0aoorr%0a(\'1\')=(\'1',
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
    $sql = "SELECT id, wf_no, status FROM oa_workflow WHERE id=('{$id}') LIMIT 0,1";
    $res = $app->q($sql);
    $row = $app->fetch($res);
    if ($row) {
    return $app->card(['审批单号' => $row['wf_no'], '当前状态' => $row['status']]);
    }
    return $app->info('');
  },
];
