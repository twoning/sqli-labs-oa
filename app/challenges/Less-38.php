<?php
// Less-38 数据报表接口
if (!defined('SQLI_MALL')) { die('forbidden'); }
return [
  'title' => '数据报表接口',
  'desc'  => '经营数据报表接口，底层驱动支持多语句执行。',
  'method' => 'GET',
  'form' => ['method' => 'GET', 'fields' => array (
  'id' => '报表编号',
), 'submit' => '查 询'],
  'difficulty' => 3,
  'task' => '利用多语句执行能力，在一条请求里追加任意 SQL（增删改）',
  'hints' => array (
  0 => '页面提示底层支持多语句——意味着可以用分号 ; 追加第二条完整 SQL。',
  1 => '单引号闭合，闭合后分号结束第一条语句，再写第二条，最后注释。',
  2 => '示例：?id=1\';update oa_employee set password=\'pwned\' where id=1-- -',
),
  'run' => function ($app) {
    if (!isset($_GET['id'])) return $app->info('请输入报表编号。');
    $id = $_GET['id'];
    $sql = "SELECT id, title, content FROM oa_notice WHERE id='{$id}' LIMIT 0,1";
    $res = $app->q_multi($sql);
    $row = $app->fetch($res);
    if ($row) {
    return $app->card(['公文标题' => $row['title'], '正文内容' => $row['content']]);
    }
    return $app->note($app->error());
  },
];
