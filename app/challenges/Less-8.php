<?php
// Less-8 会议预约查询
if (!defined('SQLI_MALL')) { die('forbidden'); }
return [
  'title' => '会议预约查询',
  'desc'  => '按预约编号查询会议是否预约成功。',
  'method' => 'GET',
  'form' => ['method' => 'GET', 'fields' => array (
  'id' => '预约编号',
), 'submit' => '查 询'],
  'difficulty' => 3,
  'task' => '无报错回显，用页面“有/无响应”的差异逐字符猜数据',
  'hints' => array (
  0 => '存在的预约有提示，不存在的页面完全空白——这就是布尔盲注的判断依据。',
  1 => '构造真/假条件对比页面：and length(database())=N、and ascii(substr(...))>N，逐位二分。',
  2 => '示例：?id=1\' and (select length(group_concat(username,0x3a,password)) from oa_employee)>100-- - 为真时有内容',
),
  'run' => function ($app) {
    if (!isset($_GET['id'])) return $app->info('请输入预约编号。');
    $id = $_GET['id'];
    $sql = "SELECT id, wf_no, status FROM oa_workflow WHERE id='{$id}' LIMIT 0,1";
    $res = $app->q($sql);
    $row = $app->fetch($res);
    if ($row) {
    return $app->info('会议预约已确认。');
    }
    return '';
  },
];
