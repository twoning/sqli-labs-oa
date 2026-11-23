<?php
// Less-53 报表排序（单引号盲）
if (!defined('SQLI_MALL')) { die('forbidden'); }
return [
  'title' => '报表排序（单引号盲）',
  'desc'  => '报表中心排序接口（单引号，无报错）。',
  'method' => 'GET',
  'form' => ['method' => 'GET', 'fields' => array (
  'sort' => '排序字段',
), 'submit' => '查 询'],
  'difficulty' => 4,
  'task' => '单引号闭合 ORDER BY + 多语句，无报错回显',
  'hints' => array (
  0 => '单引号、无报错、多语句可用。',
  1 => '闭合后分号接写语句，或用时间盲注读数。',
  2 => '示例：?sort=1\';update oa_employee set password=\'pwned\' where id=1-- -',
),
  'run' => function ($app) {
    if (!isset($_GET['sort'])) return $app->info('请选择排序字段。');
    $sort = $_GET['sort'];
    $sql = "SELECT emp_no, name, dept FROM oa_employee ORDER BY '{$sort}'";
    $res = $app->q_multi($sql);
    if (!$res) {
    return $app->info('');
    }
    return $app->table($app->fetch_all($res), array (
  '工号' => 'emp_no',
  '姓名' => 'name',
  '所属部门' => 'dept',
));

  },
];
