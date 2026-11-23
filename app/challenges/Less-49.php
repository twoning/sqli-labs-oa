<?php
// Less-49 绩效榜单（单引号）
if (!defined('SQLI_MALL')) { die('forbidden'); }
return [
  'title' => '绩效榜单（单引号）',
  'desc'  => '查看绩效榜单（单引号闭合，无报错）。',
  'method' => 'GET',
  'form' => ['method' => 'GET', 'fields' => array (
  'sort' => '排序字段',
), 'submit' => '查 询'],
  'difficulty' => 4,
  'task' => '单引号闭合的 ORDER BY 时间盲注',
  'hints' => array (
  0 => '单引号包裹 + 无报错回显，走时间盲注。',
  1 => '闭合后构造 if 条件。',
  2 => '示例：?sort=1\' and if(ascii(substr((select username from oa_employee limit 0,1),1,1))>100,sleep(5),0)-- -',
),
  'run' => function ($app) {
    if (!isset($_GET['sort'])) return $app->info('请选择排序字段。');
    $sort = $_GET['sort'];
    $sql = "SELECT emp_no, name, dept FROM oa_employee ORDER BY '{$sort}'";
    $res = $app->q($sql);
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
