<?php
// Less-57 内部演练接口（挑战关）
if (!defined('SQLI_MALL')) { die('forbidden'); }
require_once __DIR__ . '/_series.php';
return series_def([
  'times' => 10, 'difficulty' => 4, 'error' => false,
  'sql' => 'SELECT id, title, content FROM oa_notice WHERE id="{id}" LIMIT 0,1',
  'hints' => array (
  0 => '单引号试不出效果时换双引号。',
  1 => '双引号闭合 + 联合查询。',
  2 => '示例：?id=-1" union select 1,2,group_concat(table_name) from information_schema.tables where table_schema=\'challenges\'-- -',
),
]);
