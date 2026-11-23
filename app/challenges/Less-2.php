<?php
// Less-2 审批单查询
if (!defined('SQLI_MALL')) { die('forbidden'); }
return [
  'title' => '审批单查询',
  'desc'  => '按审批单编号查询流程进度。',
  'method' => 'GET',
  'form' => ['method' => 'GET', 'fields' => array (
  'id' => '审批单编号',
), 'submit' => '查 询'],
  'difficulty' => 1,
  'task' => '整型注入点，拿到员工账号密码',
  'hints' => array (
  0 => '编号直接拼进 SQL，没有引号包裹——先试试在数字后面加 and 1=2 看页面变化。',
  1 => '整型注入，报错回显。union 前把 id 置为 -1 让原查询为空即可。',
  2 => '示例：?id=-1 union select 1,group_concat(username,0x3a,password),3 from oa_employee-- -',
),
  'run' => function ($app) {
    if (!isset($_GET['id'])) return $app->info('请输入审批单编号。');
    $id = $_GET['id'];
    $sql = "SELECT id, wf_no, status FROM oa_workflow WHERE id={$id} LIMIT 0,1";
    $res = $app->q($sql);
    $row = $app->fetch($res);
    if ($row) {
    return $app->card(['审批单号' => $row['wf_no'], '当前状态' => $row['status']]);
    }
    return $app->note($app->error());
  },
];
