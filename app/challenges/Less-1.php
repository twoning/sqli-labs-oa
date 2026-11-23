<?php
// Less-1 公告详情
if (!defined('SQLI_MALL')) { die('forbidden'); }
return [
  'title' => '公告详情',
  'desc'  => '行政部公文公告查看，按公告编号打开详情。',
  'method' => 'GET',
  'form' => ['method' => 'GET', 'fields' => array (
  'id' => '公告编号',
), 'submit' => '查 询'],
  'difficulty' => 1,
  'task' => '通过公告详情页的注入点，拿到 oa_employee 表中全部员工的登录账号与密码',
  'hints' => array (
  0 => '公告详情完全由 id 参数决定。先在编号后加一个单引号，看看页面是否把数据库报错原样打印出来。',
  1 => '单引号闭合的字符型注入，报错直接回显。先用 order by 确认查询列数（3 列），再用 union 联合查询。',
  2 => '示例：?id=1\' order by 3-- - 确认后：?id=-1\' union select 1,group_concat(username,0x3a,password),3 from oa_employee-- -',
),
  'run' => function ($app) {
    if (!isset($_GET['id'])) return $app->info('请输入公告编号。');
    $id = $_GET['id'];
    $sql = "SELECT id, title, content FROM oa_notice WHERE id='{$id}' LIMIT 0,1";
    $res = $app->q($sql);
    $row = $app->fetch($res);
    if ($row) {
    return $app->card(['公文标题' => $row['title'], '正文内容' => $row['content']]);
    }
    return $app->note($app->error());
  },
];
