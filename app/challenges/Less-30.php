<?php
// Less-30 移动考勤接口
if (!defined('SQLI_MALL')) { die('forbidden'); }
return [
  'title' => '移动考勤接口',
  'desc'  => '移动端考勤接口，前置安全网关仅放行数字参数。',
  'method' => 'GET',
  'form' => ['method' => 'GET', 'fields' => array (
  'id' => '考勤编号',
), 'submit' => '查 询'],
  'difficulty' => 4,
  'task' => 'HPP 绕 WAF 后，确定闭合方式（无报错回显）',
  'hints' => array (
  0 => '同上一关用 ?id=数字&id=payload 绕过网关；失败时不显示报错。',
  1 => '闭合是双引号，无回显 → 联合查询有数据才可见。',
  2 => '示例：?id=1&id=-1" union select 1,group_concat(username,0x3a,password),3 from oa_employee-- -',
),
  'run' => function ($app) {
    if (!isset($_GET['id'])) return $app->info('请输入考勤编号。');
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
    $sql = "SELECT id, wf_no, status FROM oa_workflow WHERE id=\"{$id}\" LIMIT 0,1";
    $res = $app->q($sql);
    $row = $app->fetch($res);
    if ($row) {
    return $app->card(['审批单号' => $row['wf_no'], '当前状态' => $row['status']]);
    }
    return $app->info('');
  },
];
