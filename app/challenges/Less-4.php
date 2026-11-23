<?php
// Less-4 会议纪要查询
if (!defined('SQLI_MALL')) { die('forbidden'); }
return [
  'title' => '会议纪要查询',
  'desc'  => '按纪要编号查看会议记录。',
  'method' => 'GET',
  'form' => ['method' => 'GET', 'fields' => array (
  'id' => '纪要编号',
), 'submit' => '查 询'],
  'difficulty' => 1,
  'task' => '双引号闭合 + 括号，报错注入提数',
  'hints' => array (
  0 => '单引号没反应说明闭合字符不是单引号，换双引号试试，同时注意报错里的括号。',
  1 => '闭合方式是 id=("...")，双引号加右括号。',
  2 => '示例：?id=-1") union select 1,group_concat(username,0x3a,password),3 from oa_employee-- -',
),
  'run' => function ($app) {
    if (!isset($_GET['id'])) return $app->info('请输入纪要编号。');
    $id = $_GET['id'];
    $sql = "SELECT id, title, content FROM oa_notice WHERE id=(\"{$id}\") LIMIT 0,1";
    $res = $app->q($sql);
    $row = $app->fetch($res);
    if ($row) {
    return $app->card(['公文标题' => $row['title'], '正文内容' => $row['content']]);
    }
    return $app->note($app->error());
  },
];
