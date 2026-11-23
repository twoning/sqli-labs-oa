<?php
// Less-64 内部演练接口（挑战关）
if (!defined('SQLI_MALL')) { die('forbidden'); }
require_once __DIR__ . '/_series.php';
return series_def([
  'times' => 10, 'difficulty' => 5, 'error' => false,
  'sql' => 'SELECT id, title, content FROM oa_notice WHERE id=(({id})) LIMIT 0,1',
  'hints' => array (
  0 => '整型 + 双层括号 + 盲注。',
  1 => 'payload 补 )) 后接子查询条件。',
  2 => '示例：?id=1)) and (select length((select table_name from information_schema.tables where table_schema=\'challenges\' limit 0,1)))=10-- -',
),
]);
