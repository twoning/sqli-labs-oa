<?php
// Less-46 员工列表排序
if (!defined('SQLI_MALL')) { die('forbidden'); }
return [
  'title' => '员工列表排序',
  'desc'  => '行政部员工通讯录，按指定字段排序。',
  'method' => 'GET',
  'form' => ['method' => 'GET', 'fields' => array (
  'sort' => '排序字段',
), 'submit' => '查 询'],
  'difficulty' => 3,
  'task' => '排序参数直接拼进 ORDER BY——它后面不能接 union，想想 ORDER BY 注入的两大套路',
  'hints' => array (
  0 => 'sort 参数拼进 ORDER BY。UNION 在这里用不了，但有两条路：报错函数带数据，或用 if/sleep 控制排序。',
  1 => '报错注入：ORDER BY 后仍可接 and extractvalue(...)，报错会回显。',
  2 => '示例：?sort=1 and extractvalue(1,concat(0x7e,(select group_concat(username,0x3a,password) from oa_employee),0x7e))-- -',
),
  'run' => function ($app) {
    if (!isset($_GET['sort'])) return $app->info('请选择排序字段。');
    $sort = $_GET['sort'];
    $sql = "SELECT emp_no, name, dept FROM oa_employee ORDER BY {$sort}";
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
