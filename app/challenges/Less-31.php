<?php
// Less-31 移动公告接口
if (!defined('SQLI_MALL')) { die('forbidden'); }
return [
  'title' => '移动公告接口',
  'desc'  => '移动端公告接口，前置安全网关仅放行数字参数。',
  'method' => 'GET',
  'form' => ['method' => 'GET', 'fields' => array (
  'id' => '公告编号',
), 'submit' => '查 询'],
  'difficulty' => 4,
  'task' => 'HPP 绕 WAF，双引号括号闭合 + 报错注入',
  'hints' => array (
  0 => '网关仍然只看第一个 id 参数，HPP 绕过方式不变。',
  1 => '闭合是 id=("...")，报错会回显。',
  2 => '示例：?id=1&id=-1") union select 1,group_concat(username,0x3a,password),3 from oa_employee-- -',
),
  'run' => function ($app) {
    if (!isset($_GET['id'])) return $app->info('请输入公告编号。');
    $id = $_GET['id'];
    // 前置 WAF：检查 QUERY_STRING 中第一个 id 参数
    $qs = $_SERVER['QUERY_STRING'] ?? '';
    $wafInput = null;
    foreach (explode('&', $qs) as $kv) {
      if (substr($kv, 0, 3) === 'id=') { $wafInput = substr($kv, 3, 40); break; }
    }
    if ($wafInput !== null && !preg_match('/^\d+$/', $wafInput)) {
      return $app->note('【安全网关】请求已被 WAF 拦截：参数不合法。');
    }
    $sql = "SELECT id, title, content FROM oa_notice WHERE id= (\"{$id}\") LIMIT 0,1";
    $res = $app->q($sql);
    $row = $app->fetch($res);
    if ($row) {
    return $app->card(['公文标题' => $row['title'], '正文内容' => $row['content']]);
    }
    return $app->note($app->error());
  },
];
