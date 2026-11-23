<?php
// Less-50 报表排序（堆叠）
if (!defined('SQLI_MALL')) { die('forbidden'); }
return [
  'title' => '报表排序（堆叠）',
  'desc'  => '报表中心支持自定义排序与多语句。',
  'method' => 'GET',
  'form' => ['method' => 'GET', 'fields' => array (
  'sort' => '排序字段',
), 'submit' => '查 询'],
  'difficulty' => 4,
  'task' => 'ORDER BY + 多语句双重能力',
  'hints' => array (
  0 => '接口同时支持多语句执行——ORDER BY 注入之外还能直接追加写语句。',
  1 => '报错回显可用；多语句用分号追加 UPDATE。',
  2 => '示例：?sort=1;update oa_employee set password=\'pwned\' where id=1-- -',
),
  'run' => function ($app) {
    if (!isset($_GET['sort'])) return $app->info('请选择排序字段。');
    $sort = $_GET['sort'];
    $sql = "SELECT emp_no, name, dept FROM oa_employee ORDER BY {$sort}";
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
