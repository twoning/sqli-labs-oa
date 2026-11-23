<?php
// Less-56 内部演练接口（挑战关）
if (!defined('SQLI_MALL')) { die('forbidden'); }
require_once __DIR__ . '/_series.php';
return series_def([
  'times' => 10, 'difficulty' => 4, 'error' => false,
  'sql' => 'SELECT id, title, content FROM oa_notice WHERE id=(\'{id}\') LIMIT 0,1',
  'hints' => array (
  0 => '单引号 + 括号闭合，无报错回显。',
  1 => 'payload 形如 -1\') ... 补齐 ) 后注释。',
  2 => '示例：?id=-1\') union select 1,2,group_concat(table_name) from information_schema.tables where table_schema=\'challenges\'-- -',
),
]);
