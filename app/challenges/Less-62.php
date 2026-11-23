<?php
// Less-62 内部演练接口（挑战关）
if (!defined('SQLI_MALL')) { die('forbidden'); }
require_once __DIR__ . '/_series.php';
return series_def([
  'times' => 10, 'difficulty' => 5, 'error' => false,
  'sql' => 'SELECT id, title, content FROM oa_notice WHERE id=(\'{id}\') LIMIT 0,1',
  'hints' => array (
  0 => '无报错、无数据回显——时间盲注，10 次限制很紧。',
  1 => '闭合为单引号+括号，用 if+sleep 验证后一次查询带出多信息。',
  2 => '示例：?id=1\') and if((select length((select table_name from information_schema.tables where table_schema=\'challenges\' limit 0,1)))=10,sleep(3),0)-- -',
),
]);
