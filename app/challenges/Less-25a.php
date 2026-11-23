<?php
// Less-25a 办公用品领用
if (!defined('SQLI_MALL')) { die('forbidden'); }
return [
  'title' => '办公用品领用',
  'desc'  => '查询办公用品领用单状态。',
  'method' => 'GET',
  'form' => ['method' => 'GET', 'fields' => array (
  'id' => '领用单号',
), 'submit' => '查 询'],
  'difficulty' => 4,
  'task' => '整型 + or/and 过滤 + 无报错回显，组合拳提取数据',
  'hints' => array (
  0 => '没有引号包裹（整型），or/and 被删，失败时页面也不显示错误——只能靠有无内容判断。',
  1 => '双写绕过 + 布尔盲注：aandnd。',
  2 => '示例：?id=1 aandnd (select length(group_concat(username,0x3a,passwoorrd)) from oa_employee)>100-- -',
),
  'run' => function ($app) {
    if (!isset($_GET['id'])) return $app->info('请输入领用单号。');
    $id = $_GET['id'];
    $id = preg_replace('/or/i', '', $id);
    $id = preg_replace('/AND/i', '', $id);
    $sql = "SELECT id, emp_no, name FROM oa_employee WHERE id={$id} LIMIT 0,1";
    $res = $app->q($sql);
    $row = $app->fetch($res);
    if ($row) {
    return $app->card(['工号' => $row['emp_no'], '姓名' => $row['name']]);
    }
    return $app->info('');
  },
];
