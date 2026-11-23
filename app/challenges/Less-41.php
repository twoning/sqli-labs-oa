<?php
// Less-41 审批统计（整型）
if (!defined('SQLI_MALL')) { die('forbidden'); }
return [
  'title' => '审批统计（整型）',
  'desc'  => '审批统计接口（整型参数，无报错回显）。',
  'method' => 'GET',
  'form' => ['method' => 'GET', 'fields' => array (
  'id' => '统计编号',
), 'submit' => '查 询'],
  'difficulty' => 4,
  'task' => '整型 + 多语句 + 无报错回显',
  'hints' => array (
  0 => '整型注入、失败不回显报错——先用布尔差异确认，再上多语句。',
  1 => '多语句执行后用页面回显验证效果。',
  2 => '示例：?id=1;update oa_employee set password=\'pwned\' where id=2-- -',
),
  'run' => function ($app) {
    if (!isset($_GET['id'])) return $app->info('请输入统计编号。');
    $id = $_GET['id'];
    $sql = "SELECT id, wf_no, status FROM oa_workflow WHERE id={$id} LIMIT 0,1";
    $res = $app->q_multi($sql);
    $row = $app->fetch($res);
    if ($row) {
    return $app->card(['审批单号' => $row['wf_no'], '当前状态' => $row['status']]);
    }
    return $app->info('');
  },
];
