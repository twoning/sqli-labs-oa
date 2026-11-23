<?php
// Less-39 报表接口（整型）
if (!defined('SQLI_MALL')) { die('forbidden'); }
return [
  'title' => '报表接口（整型）',
  'desc'  => '报表数据接口（整型参数）。',
  'method' => 'GET',
  'form' => ['method' => 'GET', 'fields' => array (
  'id' => '报表编号',
), 'submit' => '查 询'],
  'difficulty' => 3,
  'task' => '整型注入 + 多语句执行',
  'hints' => array (
  0 => '整型无引号，多语句直接拼分号。',
  1 => '不需要闭合任何字符。',
  2 => '示例：?id=1;update oa_employee set password=\'pwned\' where id=2-- -',
),
  'run' => function ($app) {
    if (!isset($_GET['id'])) return $app->info('请输入报表编号。');
    $id = $_GET['id'];
    $sql = "SELECT id, title, content FROM oa_notice WHERE id={$id} LIMIT 0,1";
    $res = $app->q_multi($sql);
    $row = $app->fetch($res);
    if ($row) {
    return $app->card(['公文标题' => $row['title'], '正文内容' => $row['content']]);
    }
    return $app->note($app->error());
  },
];
