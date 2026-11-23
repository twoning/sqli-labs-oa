<?php
// Less-10 会议室预约
if (!defined('SQLI_MALL')) { die('forbidden'); }
return [
  'title' => '会议室预约',
  'desc'  => '查询会议室占用与预约状态。',
  'method' => 'GET',
  'form' => ['method' => 'GET', 'fields' => array (
  'id' => '会议室编号',
), 'submit' => '查 询'],
  'difficulty' => 4,
  'task' => '双引号闭合的时间盲注',
  'hints' => array (
  0 => '沿用上一关的时间信道，但先确认闭合字符不是单引号。',
  1 => '双引号闭合 + if/sleep。',
  2 => '示例：?id=1" and if(ascii(substr((select username from oa_employee limit 0,1),1,1))>100,sleep(5),0)-- -',
),
  'run' => function ($app) {
    if (!isset($_GET['id'])) return $app->info('请输入会议室编号。');
    $id = $_GET['id'];
    $sql = "SELECT id, wf_no, status FROM oa_workflow WHERE id=\"{$id}\" LIMIT 0,1";
    $res = $app->q($sql);
    $row = $app->fetch($res);
    if ($row) {
    return $app->info('会议室状态已更新。');
    }
    return $app->info('会议室状态已更新。');
  },
];
