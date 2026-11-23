<?php
// Less-9 车辆预约查询
if (!defined('SQLI_MALL')) { die('forbidden'); }
return [
  'title' => '车辆预约查询',
  'desc'  => '查询公务用车预约结果。',
  'method' => 'GET',
  'form' => ['method' => 'GET', 'fields' => array (
  'id' => '预约单号',
), 'submit' => '查 询'],
  'difficulty' => 3,
  'task' => '页面响应永远一样，用响应时间差异提取数据',
  'hints' => array (
  0 => '无论输入什么都返回同一句话——内容没有差异了，还能对比什么？响应快慢。',
  1 => '时间盲注：if(条件, sleep(5), 0)。',
  2 => '示例：?id=1\' and if((select length(group_concat(username,0x3a,password)) from oa_employee)>100,sleep(5),0)-- -',
),
  'run' => function ($app) {
    if (!isset($_GET['id'])) return $app->info('请输入预约单号。');
    $id = $_GET['id'];
    $sql = "SELECT id, wf_no, status FROM oa_workflow WHERE id='{$id}' LIMIT 0,1";
    $res = $app->q($sql);
    $row = $app->fetch($res);
    if ($row) {
    return $app->info('您的预约已提交，请等待审核。');
    }
    return $app->info('您的预约已提交，请等待审核。');
  },
];
