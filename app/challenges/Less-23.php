<?php
// Less-23 培训报名查询
if (!defined('SQLI_MALL')) { die('forbidden'); }
return [
  'title' => '培训报名查询',
  'desc'  => '查询年度培训班的报名审核结果。',
  'method' => 'GET',
  'form' => ['method' => 'GET', 'fields' => array (
  'id' => '培训批次号',
), 'submit' => '查 询'],
  'difficulty' => 3,
  'task' => '注释符被过滤了，改用“闭合尾部引号”的方式完成 union 注入',
  'hints' => array (
  0 => '系统会过滤 # 和 -- ，所以常规注释失效。',
  1 => '不注释也能过：payload 末尾把原语句剩下的引号“用掉”，用 and \'1\'=\'1 收尾。',
  2 => '示例：?id=-1\' union select 1,group_concat(username,0x3a,passwoorrd),3 from oa_employee where \'1\'=\'1',
),
  'run' => function ($app) {
    if (!isset($_GET['id'])) return $app->info('请输入培训批次号。');
    $id = $_GET['id'];
    $id = preg_replace('/#/', '', $id);
    $id = preg_replace('/--/', '', $id);
    $sql = "SELECT id, title, content FROM oa_notice WHERE id='{$id}' LIMIT 0,1";
    $res = $app->q($sql);
    $row = $app->fetch($res);
    if ($row) {
    return $app->card(['公文标题' => $row['title'], '正文内容' => $row['content']]);
    }
    return $app->note($app->error());
  },
];
