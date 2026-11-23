<?php
// Less-6 用印申请状态
if (!defined('SQLI_MALL')) { die('forbidden'); }
return [
  'title' => '用印申请状态',
  'desc'  => '查询用印申请的受理状态。',
  'method' => 'GET',
  'form' => ['method' => 'GET', 'fields' => array (
  'id' => '申请单号',
), 'submit' => '查 询'],
  'difficulty' => 2,
  'task' => '双引号闭合的报错注入',
  'hints' => array (
  0 => '单引号打不出反应，换双引号看报错。',
  1 => '双引号闭合 + 报错函数。',
  2 => '示例：?id=1" and updatexml(1,concat(0x7e,(select group_concat(username,0x3a,password) from oa_employee),0x7e),1)-- -',
),
  'run' => function ($app) {
    if (!isset($_GET['id'])) return $app->info('请输入申请单号。');
    $id = $_GET['id'];
    $sql = "SELECT id, wf_no, status FROM oa_workflow WHERE id=\"{$id}\" LIMIT 0,1";
    $res = $app->q($sql);
    $row = $app->fetch($res);
    if ($row) {
    return $app->info('申请已受理。');
    }
    return $app->note($app->error());
  },
];
