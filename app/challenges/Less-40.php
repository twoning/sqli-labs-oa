<?php
// Less-40 审批统计接口
if (!defined('SQLI_MALL')) { die('forbidden'); }
return [
  'title' => '审批统计接口',
  'desc'  => '审批数据统计接口（括号闭合，无报错回显）。',
  'method' => 'GET',
  'form' => ['method' => 'GET', 'fields' => array (
  'id' => '统计编号',
), 'submit' => '查 询'],
  'difficulty' => 4,
  'task' => '括号闭合 + 多语句（无报错回显，用页面差异验证）',
  'hints' => array (
  0 => '先确认闭合：id=(\'...\')；失败时页面不显示错误。',
  1 => '多语句照常可用，通过后续查询验证 UPDATE 是否生效。',
  2 => '示例：?id=1\');update oa_employee set password=\'pwned\' where id=2-- -',
),
  'run' => function ($app) {
    if (!isset($_GET['id'])) return $app->info('请输入统计编号。');
    $id = $_GET['id'];
    $sql = "SELECT id, wf_no, status FROM oa_workflow WHERE id=('{$id}') LIMIT 0,1";
    $res = $app->q_multi($sql);
    $row = $app->fetch($res);
    if ($row) {
    return $app->card(['审批单号' => $row['wf_no'], '当前状态' => $row['status']]);
    }
    return $app->info('');
  },
];
