<?php
// Less-3 制度文件详情
if (!defined('SQLI_MALL')) { die('forbidden'); }
return [
  'title' => '制度文件详情',
  'desc'  => '查看公司规章制度文件正文。',
  'method' => 'GET',
  'form' => ['method' => 'GET', 'fields' => array (
  'id' => '文件编号',
), 'submit' => '查 询'],
  'difficulty' => 1,
  'task' => '确认闭合方式后联合查询员工表',
  'hints' => array (
  0 => '加单引号报错后，仔细看报错信息里引号附近的内容——除了引号还有括号。',
  1 => '闭合方式是 id=(\'...\')，payload 要先补一个右括号再注释。',
  2 => '示例：?id=-1\') union select 1,group_concat(username,0x3a,password),3 from oa_employee-- -',
),
  'run' => function ($app) {
    if (!isset($_GET['id'])) return $app->info('请输入文件编号。');
    $id = $_GET['id'];
    $sql = "SELECT id, title, content FROM oa_notice WHERE id=('{$id}') LIMIT 0,1";
    $res = $app->q($sql);
    $row = $app->fetch($res);
    if ($row) {
    return $app->card(['公文标题' => $row['title'], '正文内容' => $row['content']]);
    }
    return $app->note($app->error());
  },
];
