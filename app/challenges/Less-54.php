<?php
// Less-54 内部演练接口（挑战关）
if (!defined('SQLI_MALL')) { die('forbidden'); }
require_once __DIR__ . '/_series.php';
return series_def([
  'times' => 10, 'difficulty' => 4, 'error' => false,
  'sql' => 'SELECT id, title, content FROM oa_notice WHERE id=\'{id}\' LIMIT 0,1',
  'hints' => array (
  0 => '先用单引号探测闭合，再 union 查询。回显正常、报错不显示——联合查询是首选。',
  1 => '先 order by 确认列数（3 列），再从 information_schema 找 challenges 库的随机表名，查列名，最后取 secret 值。',
  2 => '示例：?id=-1\' union select 1,2,group_concat(table_name) from information_schema.tables where table_schema=\'challenges\'-- -',
),
]);
