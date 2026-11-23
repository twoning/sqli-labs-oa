<?php
// Less-5 报销单进度
if (!defined('SQLI_MALL')) { die('forbidden'); }
return [
  'title' => '报销单进度',
  'desc'  => '查询差旅报销单的审批进度，页面只显示流程状态。',
  'method' => 'GET',
  'form' => ['method' => 'GET', 'fields' => array (
  'id' => '报销单编号',
), 'submit' => '查 询'],
  'difficulty' => 2,
  'task' => '页面不回显业务数据，改用报错函数把员工数据带出来',
  'hints' => array (
  0 => '无论查到查不到，页面只有一句话；但查询失败时会把 SQL 报错原样打印。数据不会显示，但报错会“说话”。',
  1 => '报错注入：extractvalue / updatexml / floor(rand())。单引号闭合。',
  2 => '示例：?id=1\' and extractvalue(1,concat(0x7e,(select group_concat(username,0x3a,password) from oa_employee),0x7e))-- -  注意报错只显示 32 字节，数据长时要配 substr 分段取。',
),
  'run' => function ($app) {
    if (!isset($_GET['id'])) return $app->info('请输入报销单编号。');
    $id = $_GET['id'];
    $sql = "SELECT id, wf_no, status FROM oa_workflow WHERE id='{$id}' LIMIT 0,1";
    $res = $app->q($sql);
    $row = $app->fetch($res);
    if ($row) {
    return $app->info('审批流程处理中，请耐心等待。');
    }
    return $app->note($app->error());
  },
];
