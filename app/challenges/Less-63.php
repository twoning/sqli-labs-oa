<?php
// Less-63 内部演练接口（挑战关）
if (!defined('SQLI_MALL')) { die('forbidden'); }
require_once __DIR__ . '/_series.php';
return series_def([
  'times' => 10, 'difficulty' => 5, 'error' => false,
  'sql' => 'SELECT id, title, content FROM oa_notice WHERE id=\'{id}\' LIMIT 0,1',
  'hints' => array (
  0 => '布尔差异可用（有/无结果），无报错回显。',
  1 => '单引号闭合，and 条件控制返回行。',
  2 => '示例：?id=1\' and (select length((select table_name from information_schema.tables where table_schema=\'challenges\' limit 0,1)))=10-- -',
),
]);
