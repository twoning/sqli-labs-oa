<?php
// Less-48 绩效榜单（盲）
if (!defined('SQLI_MALL')) { die('forbidden'); }
return [
  'title' => '绩效榜单（盲）',
  'desc'  => '查看绩效榜单（无报错回显）。',
  'method' => 'GET',
  'form' => ['method' => 'GET', 'fields' => array (
  'sort' => '排序字段',
), 'submit' => '查 询'],
  'difficulty' => 4,
  'task' => 'ORDER BY 注入但无报错回显，用时间盲注提取数据',
  'hints' => array (
  0 => '失败时不显示报错——只剩时间信道。',
  1 => 'ORDER BY 后可以接 if() 或与 sleep 相关的表达式。',
  2 => '示例：?sort=if(ascii(substr((select username from oa_employee limit 0,1),1,1))>100,1,(select sleep(5)))',
),
  'run' => function ($app) {
    if (!isset($_GET['sort'])) return $app->info('请选择排序字段。');
    $sort = $_GET['sort'];
    $sql = "SELECT emp_no, name, dept FROM oa_employee ORDER BY {$sort}";
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
