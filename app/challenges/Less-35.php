<?php
// Less-35 资产编号查询
if (!defined('SQLI_MALL')) { die('forbidden'); }
return [
  'title' => '资产编号查询',
  'desc'  => '查询固定资产基础信息。',
  'method' => 'GET',
  'form' => ['method' => 'GET', 'fields' => array (
  'id' => '资产编号',
), 'submit' => '查 询'],
  'set_names' => 'gbk',
  'difficulty' => 3,
  'task' => '参数加了转义还值得想办法吗？先看看 SQL 里有没有引号',
  'hints' => array (
  0 => 'addslashes 转义引号和反斜杠——但看报错信息：这条 SQL 里 id 根本没有引号包裹。',
  1 => '整型注入不需要引号，转义完全无效，直接 union。',
  2 => '示例：?id=-1 union select 1,group_concat(username,0x3a,password),3 from oa_employee-- -',
),
  'run' => function ($app) {
    if (!isset($_GET['id'])) return $app->info('请输入资产编号。');
    $id = $_GET['id'];
    $id = addslashes($id);
    $sql = "SELECT id, title, content FROM oa_notice WHERE id={$id} LIMIT 0,1";
    $res = $app->q($sql);
    $row = $app->fetch($res);
    if ($row) {
    return $app->card(['公文标题' => $row['title'], '正文内容' => $row['content']]);
    }
    return $app->note($app->error());
  },
];
