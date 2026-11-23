<?php
// Less-28 资产领用查询
if (!defined('SQLI_MALL')) { die('forbidden'); }
return [
  'title' => '资产领用查询',
  'desc'  => '查询固定资产领用记录。',
  'method' => 'GET',
  'form' => ['method' => 'GET', 'fields' => array (
  'id' => '资产编号',
), 'submit' => '查 询'],
  'difficulty' => 4,
  'task' => 'union+select 连写组合被过滤，试试 union all select',
  'hints' => array (
  0 => '过滤正则是 union\\s+select（删除一次）。union all select 不匹配该正则。',
  1 => '单引号+括号闭合，无报错回显 → 联合查询成功时有数据。',
  2 => '示例：?id=-1\') union all select 1,group_concat(username,0x3a,password),3 from oa_employee-- -',
),
  'run' => function ($app) {
    if (!isset($_GET['id'])) return $app->info('请输入资产编号。');
    $id = $_GET['id'];
    $id = preg_replace('/[\\/\\*]/', '', $id);
    $id = preg_replace('/[--]/', '', $id);
    $id = preg_replace('/[#]/', '', $id);
    $id = preg_replace('/[ +]/', '', $id);
    $id = preg_replace('/union\\s+select/i', '', $id);
    $sql = "SELECT id, title, content FROM oa_notice WHERE id=('{$id}') LIMIT 0,1";
    $res = $app->q($sql);
    $row = $app->fetch($res);
    if ($row) {
    return $app->card(['公文标题' => $row['title'], '正文内容' => $row['content']]);
    }
    return $app->info('');
  },
];
