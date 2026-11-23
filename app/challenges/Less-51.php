<?php
// Less-51 报表排序（单引号）
if (!defined('SQLI_MALL')) { die('forbidden'); }
return [
  'title' => '报表排序（单引号）',
  'desc'  => '报表中心排序接口（单引号 + 多语句）。',
  'method' => 'GET',
  'form' => ['method' => 'GET', 'fields' => array (
  'sort' => '排序字段',
), 'submit' => '查 询'],
  'difficulty' => 4,
  'task' => '单引号闭合 ORDER BY + 多语句',
  'hints' => array (
  0 => '单引号闭合、报错回显、多语句可用。',
  1 => '闭合后先报错提数，或分号接第二条语句。',
  2 => '示例：?sort=1\';update oa_employee set password=\'pwned\' where id=1-- -',
),
  'run' => function ($app) {
    if (!isset($_GET['sort'])) return $app->info('请选择排序字段。');
    $sort = $_GET['sort'];
    $sql = "SELECT emp_no, name, dept FROM oa_employee ORDER BY '{$sort}'";
    $res = $app->q_multi($sql);
    if (!$res) {
    return $app->note($app->error());
    }
    return $app->table($app->fetch_all($res), array (
  '工号' => 'emp_no',
  '姓名' => 'name',
  '所属部门' => 'dept',
));

  },
];
