<?php
/**
 * 学习路线：把 69 个关卡排成一条有先后顺序的主线
 * 每一步 = 一个技法点 + 一个业务功能 + 一句"学什么"
 */
if (!defined('SQLI_MALL')) { die('forbidden'); }

function oa_curriculum()
{
    static $data = null;
    if ($data !== null) return $data;

    /* 章节：每一章解决一类问题 */
    $chapters = [
        1 => ['title' => '看懂一条 SQL：闭合方式与联合查询', 'intro' => '先解决最基础的问题——参数是怎么拼进 SQL 的？闭合字符是什么？查询有几列？这一章打完，你就能在任何回显正常的页面里把数据读出来。'],
        2 => ['title' => '页面不回显数据：让报错替你说话', 'intro' => '页面只提示"处理中"，看不到数据。但只要它把数据库报错打印出来，就能用报错函数把任意查询结果塞进错误信息里。'],
        3 => ['title' => '连报错都没有：布尔与时间盲注', 'intro' => '最难受的情况——页面没有任何差异。这时只能靠"有没有内容"或"快还是慢"这两种信号，一个字符一个字符地把数据问出来。'],
        4 => ['title' => '登录框也是注入点：POST 与登录绕过', 'intro' => '参数从 URL 挪到了表单里，本质没变。这一章还要掌握"万能钥匙"——不查密码直接登录。'],
        5 => ['title' => '参数不在 URL 里：请求头与会话凭证', 'intro' => '真正的实战里，很多注入点藏在 User-Agent、Referer、Cookie 里。服务端只要信任了这些客户端可控的值，就一样能注。'],
        6 => ['title' => '有防护怎么办：过滤绕过与 WAF', 'intro' => '黑名单过滤是最常见的"防护"。这一章逐个拆：注释被删怎么办、关键词被删怎么双写、网关只查第一个参数怎么办，以及最经典的二次注入。'],
        7 => ['title' => '编码陷阱：宽字节注入', 'intro' => '转义函数不是万能的。当数据库连接编码是 GBK 时，一个 0xdf 就能把反斜杠"吃掉"，让引号重新生效。'],
        8 => ['title' => '从读数据到改数据：堆叠注入', 'intro' => '前面都在读。这一章用分号追加第二条 SQL，直接 UPDATE 掉别人的密码——危害从"泄露"升级为"篡改"。'],
        9 => ['title' => '排序参数：ORDER BY 注入', 'intro' => 'ORDER BY 后面不能接 UNION，但能接表达式。报错、时间盲注、堆叠三条路都通。这类接口在真实系统里极其常见。'],
        10 => ['title' => '综合挑战：限 10 次请求的实战考核', 'intro' => '表名、列名、密钥全部随机，只有 10 次请求机会。这里不再给具体业务场景，全靠前面九章积累的手法自己组合。'],
    ];

    /* 主线步骤：顺序即学习顺序 */
    $steps = [
        // ---------- 第 1 章 ----------
        ['c' => 'Less-1',  'ch' => 1,  'tag' => '联合查询', 'topic' => '字符型注入 · 单引号闭合',
         'goal' => '判断参数是字符型还是数字型，用 order by 数清列数，再用 union 把别的表的数据读出来',
         'key' => '先 1 个单引号看报错；order by N 试到报错为止，N-1 就是列数'],
        ['c' => 'Less-2',  'ch' => 1,  'tag' => '联合查询', 'topic' => '整型注入 · 没有引号',
         'goal' => '识别数字型参数：不需要闭合任何字符，直接接 and / union',
         'key' => 'union 时把原值改成 -1，让第一条查询为空，结果才会显示你的'],
        ['c' => 'Less-3',  'ch' => 1,  'tag' => '联合查询', 'topic' => '单引号 + 括号闭合',
         'goal' => '从报错信息里读出完整的闭合结构，而不只是猜引号',
         'key' => "报错会显示 near '') LIMIT 0,1'——括号是闭合结构的一部分"],
        ['c' => 'Less-4',  'ch' => 1,  'tag' => '联合查询', 'topic' => '双引号 + 括号闭合',
         'goal' => '单引号打不动时，换双引号；同时注意括号层数',
         'key' => '闭合是 ("...")，payload 要补 ") 两个字符'],

        // ---------- 第 2 章 ----------
        ['c' => 'Less-5',  'ch' => 2,  'tag' => '报错注入', 'topic' => 'extractvalue 报错带数据',
         'goal' => '页面只显示"处理中"不显示数据，但会打印 SQL 报错——把查询结果塞进报错信息',
         'key' => 'concat(0x7e,(子查询),0x7e)，0x7e 是 ~ 用来标记数据边界'],
        ['c' => 'Less-6',  'ch' => 2,  'tag' => '报错注入', 'topic' => 'updatexml 报错带数据',
         'goal' => '掌握第二种报错函数，双引号闭合',
         'key' => 'updatexml(1,concat(0x7e,(子查询),0x7e),1) 三个参数'],

        // ---------- 第 3 章 ----------
        ['c' => 'Less-7',  'ch' => 3,  'tag' => '联合查询', 'topic' => '双层括号 + 无任何反馈',
         'goal' => '页面成功失败都是同一句话，没有布尔差异——只能靠联合查询让数据自己返回',
         'key' => "闭合是 (('...'))，补两个右括号；INTO OUTFILE 写文件需要 secure_file_priv 放开"],
        ['c' => 'Less-8',  'ch' => 3,  'tag' => '布尔盲注', 'topic' => '布尔盲注 · 有无内容的差异',
         'goal' => '没有数据回显，但"查到"和"查不到"的页面不同——把每个字符拆成真假问题',
         'key' => 'length() / ascii(substr()) / 二分法，一次问一位'],
        ['c' => 'Less-9',  'ch' => 3,  'tag' => '时间盲注', 'topic' => '时间盲注 · 单引号',
         'goal' => '页面永远一样，连有无差异都没有——只剩响应时间可观测',
         'key' => 'if(条件, sleep(5), 0)，用响应时长判断真假'],
        ['c' => 'Less-10', 'ch' => 3,  'tag' => '时间盲注', 'topic' => '时间盲注 · 双引号',
         'goal' => '同样的时间信道，换一种闭合字符',
         'key' => '先确认是双引号，再套 if+sleep'],

        // ---------- 第 4 章 ----------
        ['c' => 'Less-11', 'ch' => 4,  'tag' => '登录绕过', 'topic' => 'POST 注入 · 万能钥匙',
         'goal' => '不查密码直接登录；同时掌握 POST 参数与 GET 参数本质相同',
         'key' => "账号填 admin' or 1=1-- - ，密码随便"],
        ['c' => 'Less-12', 'ch' => 4,  'tag' => '登录绕过', 'topic' => 'POST · 双引号 + 括号',
         'goal' => '闭合方式变形后，万能钥匙要跟着改写',
         'key' => '闭合是 ("...")，payload 补 ")'],
        ['c' => 'Less-13', 'ch' => 4,  'tag' => '报错注入', 'topic' => 'POST 报错注入',
         'goal' => '登录成功也不给数据——把出口换到报错信息上',
         'key' => 'extractvalue / updatexml，闭合是 (\'...\')'],
        ['c' => 'Less-14', 'ch' => 4,  'tag' => '报错注入', 'topic' => 'POST 双引号报错注入',
         'goal' => '双引号闭合的报错注入',
         'key' => '闭合是 "..."，无需括号'],
        ['c' => 'Less-15', 'ch' => 4,  'tag' => '时间盲注', 'topic' => 'POST 时间盲注',
         'goal' => '表单提交同样能做时间盲注，且成功失败话术不同可以用来辅助判断',
         'key' => 'if(length(子查询)>N, sleep(5), 0)'],
        ['c' => 'Less-16', 'ch' => 4,  'tag' => '时间盲注', 'topic' => 'POST · 括号 + 时间盲注',
         'goal' => '闭合字符与括号组合下的盲注',
         'key' => '闭合是 ("...")，先补 ") 再套 if'],

        // ---------- 第 5 章 ----------
        ['c' => 'Less-17', 'ch' => 5,  'tag' => 'UPDATE 注入', 'topic' => 'UPDATE 语句注入',
         'goal' => '注入点不在 SELECT 而在 UPDATE——改密码的地方也能读数据',
         'key' => '报 1093 错误时套一层派生表：(select ... from (select ...) x)'],
        ['c' => 'Less-18', 'ch' => 5,  'tag' => '请求头注入', 'topic' => 'User-Agent 注入',
         'goal' => '参数不在表单也不在 URL，而在 HTTP 请求头里',
         'key' => 'UA 被原样写进 INSERT；需要先合法登录触发插入'],
        ['c' => 'Less-19', 'ch' => 5,  'tag' => '请求头注入', 'topic' => 'Referer 注入',
         'goal' => '同上，换成 Referer；注意 payload 首字符会被当数字转换',
         'key' => 'payload 以 1 开头，不要带 http:// 前缀'],
        ['c' => 'Less-20', 'ch' => 5,  'tag' => 'Cookie 注入', 'topic' => 'Cookie 注入',
         'goal' => '服务端把 Cookie 当成可信身份直接拼进 SQL——客户端可控的值永远不可信',
         'key' => '登录后改 Cookie uname；空格用 /**/ 代替更方便'],
        ['c' => 'Less-21', 'ch' => 5,  'tag' => 'Cookie 注入', 'topic' => 'base64 Cookie 注入',
         'goal' => '凭证被编码了——编码不是防护，先解码再注入',
         'key' => 'payload 先 base64 编码再放进 Cookie'],
        ['c' => 'Less-22', 'ch' => 5,  'tag' => 'Cookie 注入', 'topic' => 'base64 + 双引号闭合',
         'goal' => '同样的编码 Cookie，但后端拼接方式不同',
         'key' => '解码后闭合是 "..."，不用括号'],

        // ---------- 第 6 章 ----------
        ['c' => 'Less-23', 'ch' => 6,  'tag' => '过滤绕过', 'topic' => '注释符被过滤',
         'goal' => '-- 和 # 都被删了，没法注释掉尾部——改用"把尾部引号用掉"的写法',
         'key' => "有 FROM 时用 where '1'='1 收尾，不能用 and"],
        ['c' => 'Less-24', 'ch' => 6,  'tag' => '二次注入', 'topic' => '二次注入',
         'goal' => '入库时转义了，出库复用时没有——这是最容易被忽视的一类注入',
         'key' => "注册登录名 lina'# → 正常登录 → 改密码，UPDATE 条件被 # 截断"],
        ['c' => 'Less-25', 'ch' => 6,  'tag' => '过滤绕过', 'topic' => 'or / and 双写绕过',
         'goal' => '关键词被直接删除时，把关键词拆成两半让它删完又拼回来',
         'key' => 'oorr → or；注意 password 里的 or 也会被删，要写 passwoorrd'],
        ['c' => 'Less-25a', 'ch' => 6, 'tag' => '过滤绕过', 'topic' => '整型 + 双写 + 布尔盲注',
         'goal' => '把双写绕过和布尔盲注组合起来用',
         'key' => 'aandnd 代替 and，再套 length/ascii'],
        ['c' => 'Less-26', 'ch' => 6,  'tag' => '过滤绕过', 'topic' => '空格与注释全被过滤',
         'goal' => '没有空格怎么写字——用换行、制表符、括号替代',
         'key' => '%0a %09 %0b %0c 都能当空格用'],
        ['c' => 'Less-26a', 'ch' => 6, 'tag' => '过滤绕过', 'topic' => '同款过滤 + 括号 + 盲注',
         'goal' => '过滤条件下做盲注，空格替代同样适用',
         'key' => "闭合是 ('...')，双写 + %0a 组合"],
        ['c' => 'Less-27', 'ch' => 6,  'tag' => '过滤绕过', 'topic' => 'union / select 大小写过滤',
         'goal' => '黑名单枚举了多种大小写写法，用混合大小写让它删不干净',
         'key' => 'uniOn SeLECT；空格用 %0a'],
        ['c' => 'Less-27a', 'ch' => 6, 'tag' => '过滤绕过', 'topic' => '同款过滤 + 双引号',
         'goal' => '把上一关的手法迁移到双引号闭合',
         'key' => '闭合是 "..."，其余不变'],
        ['c' => 'Less-28', 'ch' => 6,  'tag' => '过滤绕过', 'topic' => 'union select 连写被过滤',
         'goal' => '正则只匹配"union 空格 select"时，加个 all 就绕过去了',
         'key' => 'union all select'],
        ['c' => 'Less-28a', 'ch' => 6, 'tag' => '过滤绕过', 'topic' => '只过滤连写（简化版）',
         'goal' => '过滤规则更少的情况，确认思路一致',
         'key' => 'union all select 直接可用'],
        ['c' => 'Less-29', 'ch' => 6,  'tag' => 'WAF 绕过', 'topic' => 'HTTP 参数污染绕 WAF',
         'goal' => '网关只检查第一个 id，应用却取最后一个——同一个参数给两个值',
         'key' => '?id=1&id=payload'],
        ['c' => 'Less-30', 'ch' => 6,  'tag' => 'WAF 绕过', 'topic' => 'HPP + 双引号 + 无报错',
         'goal' => '绕过网关之后再判断闭合，且没有报错可看',
         'key' => '先绕过，再用联合查询验证'],
        ['c' => 'Less-31', 'ch' => 6,  'tag' => 'WAF 绕过', 'topic' => 'HPP + 双引号括号 + 报错',
         'goal' => '同款绕过，闭合变成双引号加括号',
         'key' => 'payload 补 ")'],

        // ---------- 第 7 章 ----------
        ['c' => 'Less-32', 'ch' => 7,  'tag' => '宽字节', 'topic' => '宽字节注入 · 自研转义函数',
         'goal' => '引号被加了反斜杠，但 GBK 编码下 0xdf+0x5c 会拼成一个汉字，反斜杠被吃掉',
         'key' => '%df%27 代替 \'；payload 前面用 -1 让原查询为空'],
        ['c' => 'Less-33', 'ch' => 7,  'tag' => '宽字节', 'topic' => '宽字节 · addslashes',
         'goal' => 'addslashes 只认单字节，遇到多字节编码就失效',
         'key' => '同样 %df%27'],
        ['c' => 'Less-34', 'ch' => 7,  'tag' => '宽字节', 'topic' => '宽字节 · POST 参数',
         'goal' => 'POST 参数同样存在宽字节问题，不是只有 GET 会中招',
         'key' => '表单里直接填 %df%27（URL 编码后提交）'],
        ['c' => 'Less-35', 'ch' => 7,  'tag' => '宽字节', 'topic' => '转义了，但参数是整型',
         'goal' => '转义函数只处理引号——SQL 里本来就没有引号时，转义等于没做',
         'key' => '看 SQL 结构，不要看到 addslashes 就放弃'],
        ['c' => 'Less-36', 'ch' => 7,  'tag' => '宽字节', 'topic' => '宽字节 · 数据库层转义',
         'goal' => 'mysqli_real_escape_string 比 addslashes 专业，但 SET NAMES gbk 下一样会失效',
         'key' => '关键在连接编码，不在转义函数本身'],
        ['c' => 'Less-37', 'ch' => 7,  'tag' => '宽字节', 'topic' => '宽字节 · POST + 数据库层转义',
         'goal' => '把宽字节手法迁移到登录框',
         'key' => '账号栏提交 %df%27'],

        // ---------- 第 8 章 ----------
        ['c' => 'Less-38', 'ch' => 8,  'tag' => '堆叠注入', 'topic' => '堆叠注入 · 单引号',
         'goal' => '分号可以追加第二条完整 SQL——从"读数据"升级为"改数据"',
         'key' => "1';update oa_employee set password='x' where id=1-- -"],
        ['c' => 'Less-39', 'ch' => 8,  'tag' => '堆叠注入', 'topic' => '堆叠注入 · 整型',
         'goal' => '整型参数堆叠时不需要闭合任何字符',
         'key' => '直接 1;update ...'],
        ['c' => 'Less-40', 'ch' => 8,  'tag' => '堆叠注入', 'topic' => '堆叠 + 括号 + 无报错',
         'goal' => '没有报错回显时，靠后续查询验证 UPDATE 是否生效',
         'key' => "闭合 ')，改完再查一次看结果"],
        ['c' => 'Less-41', 'ch' => 8,  'tag' => '堆叠注入', 'topic' => '堆叠 · 整型 + 无报错',
         'goal' => '整型 + 盲 + 堆叠三件套',
         'key' => '分号直接接写语句'],
        ['c' => 'Less-42', 'ch' => 8,  'tag' => '堆叠注入', 'topic' => '两个参数只转义一个',
         'goal' => '账号栏做了转义，密码栏没有——注入点永远要找"被忽略的那个参数"',
         'key' => '登录成功与否不重要，第二条语句照样执行'],
        ['c' => 'Less-43', 'ch' => 8,  'tag' => '堆叠注入', 'topic' => '密码栏堆叠 + 括号',
         'goal' => '闭合方式变成 (\'...\')，payload 补 )',
         'key' => "1');update ..."],
        ['c' => 'Less-44', 'ch' => 8,  'tag' => '堆叠注入', 'topic' => '堆叠 · 无报错回显',
         'goal' => '看不到报错时如何确认注入成功',
         'key' => '用页面话术差异 + 后续查询验证'],
        ['c' => 'Less-45', 'ch' => 8,  'tag' => '堆叠注入', 'topic' => '堆叠 · 括号 + 无报错',
         'goal' => '把前几关的组合起来',
         'key' => "1');update ..."],

        // ---------- 第 9 章 ----------
        ['c' => 'Less-46', 'ch' => 9,  'tag' => 'ORDER BY', 'topic' => 'ORDER BY 注入 · 报错',
         'goal' => '排序参数后面不能接 union，但可以接表达式——用报错函数带数据',
         'key' => '1 and extractvalue(...) 放在 ORDER BY 后'],
        ['c' => 'Less-47', 'ch' => 9,  'tag' => 'ORDER BY', 'topic' => 'ORDER BY · 单引号',
         'goal' => '排序参数被单引号包裹时的注入',
         'key' => "1' and extractvalue(...)-- -"],
        ['c' => 'Less-48', 'ch' => 9,  'tag' => 'ORDER BY', 'topic' => 'ORDER BY · 时间盲注',
         'goal' => '排序参数没有报错回显时，用 if 控制排序结果产生时间差',
         'key' => 'if(条件, 1, (select sleep(5)))'],
        ['c' => 'Less-49', 'ch' => 9,  'tag' => 'ORDER BY', 'topic' => 'ORDER BY · 单引号时间盲注',
         'goal' => '闭合 + 时间信道',
         'key' => "1' and if(...,sleep(5),0)-- -"],
        ['c' => 'Less-50', 'ch' => 9,  'tag' => 'ORDER BY', 'topic' => 'ORDER BY + 堆叠',
         'goal' => '排序接口同时支持多语句，可以直接写库',
         'key' => '1;update ...'],
        ['c' => 'Less-51', 'ch' => 9,  'tag' => 'ORDER BY', 'topic' => 'ORDER BY + 堆叠 + 单引号',
         'goal' => '闭合后接分号',
         'key' => "1';update ..."],
        ['c' => 'Less-52', 'ch' => 9,  'tag' => 'ORDER BY', 'topic' => 'ORDER BY + 堆叠 + 盲',
         'goal' => '无报错时用列表页面验证写入效果',
         'key' => '改完刷新列表看数据变了没'],
        ['c' => 'Less-53', 'ch' => 9,  'tag' => 'ORDER BY', 'topic' => 'ORDER BY + 堆叠 + 单引号盲',
         'goal' => '最后一关的组合技',
         'key' => "1';update ..."],

        // ---------- 第 10 章 ----------
        ['c' => 'Less-54', 'ch' => 10, 'tag' => '综合挑战', 'topic' => '综合 · 单引号联合查询',
         'goal' => '表名/列名/密钥全随机，10 次请求内完成"找表→找列→取值"',
         'key' => 'information_schema.tables → columns → 取密钥'],
        ['c' => 'Less-55', 'ch' => 10, 'tag' => '综合挑战', 'topic' => '综合 · 整型括号',
         'goal' => '整型 + 括号闭合，流程不变',
         'key' => '补 ) 后 union'],
        ['c' => 'Less-56', 'ch' => 10, 'tag' => '综合挑战', 'topic' => '综合 · 单引号括号',
         'goal' => "闭合是 ('...')",
         'key' => "补 ') 后 union"],
        ['c' => 'Less-57', 'ch' => 10, 'tag' => '综合挑战', 'topic' => '综合 · 双引号',
         'goal' => '双引号闭合',
         'key' => '补 " 后 union'],
        ['c' => 'Less-58', 'ch' => 10, 'tag' => '综合挑战', 'topic' => '综合 · 报错注入',
         'goal' => '有报错回显时，报错注入比盲注省请求次数',
         'key' => '10 次限制下优先用 extractvalue'],
        ['c' => 'Less-59', 'ch' => 10, 'tag' => '综合挑战', 'topic' => '综合 · 整型报错',
         'goal' => '整型闭合 + 报错',
         'key' => '不用引号，直接 and extractvalue'],
        ['c' => 'Less-60', 'ch' => 10, 'tag' => '综合挑战', 'topic' => '综合 · 双引号括号报错',
         'goal' => '闭合是 ("...")',
         'key' => '补 ") 后接报错函数'],
        ['c' => 'Less-61', 'ch' => 10, 'tag' => '综合挑战', 'topic' => '综合 · 双层括号报错',
         'goal' => '闭合是 ((\'...\'))',
         'key' => '补 ))'],
        ['c' => 'Less-62', 'ch' => 10, 'tag' => '综合挑战', 'topic' => '综合 · 时间盲注',
         'goal' => '没有报错也没有数据回显，只能时间盲注；10 次限制非常紧',
         'key' => '一次请求判断一个条件，先测长度再二分'],
        ['c' => 'Less-63', 'ch' => 10, 'tag' => '综合挑战', 'topic' => '综合 · 布尔盲注',
         'goal' => '有/无结果两种状态可用',
         'key' => 'and (子查询)=N 控制返回行'],
        ['c' => 'Less-64', 'ch' => 10, 'tag' => '综合挑战', 'topic' => '综合 · 整型双括号盲注',
         'goal' => '整型 + 双层括号 + 盲注',
         'key' => '补 ))'],
        ['c' => 'Less-65', 'ch' => 10, 'tag' => '综合挑战', 'topic' => '综合 · 整型括号时间盲注',
         'goal' => '最后一关：整型 + 单层括号 + 时间盲注',
         'key' => '补 ) 后套 if+sleep'],
    ];

    $data = ['chapters' => $chapters, 'steps' => $steps];
    return $data;
}

/** 取某个关卡在主线里的位置：['i'=>0 基索引, 'no'=>1 起始序号, 'step'=>[...], 'total'=>69] */
function oa_step_of($cid)
{
    $cv = oa_curriculum();
    $total = count($cv['steps']);
    foreach ($cv['steps'] as $i => $s) {
        if ($s['c'] === $cid) {
            return ['i' => $i, 'no' => $i + 1, 'step' => $s, 'total' => $total];
        }
    }
    return null;
}

/** 上一步 / 下一步 */
function oa_step_neighbor($cid, $offset)
{
    $pos = oa_step_of($cid);
    if (!$pos) return null;
    $cv = oa_curriculum();
    $j = $pos['i'] + $offset;
    return isset($cv['steps'][$j]) ? $cv['steps'][$j] : null;
}

/** 第一个还没通关的步骤（"继续学习"用） */
function oa_next_unsolved()
{
    $cv = oa_curriculum();
    foreach ($cv['steps'] as $s) {
        if (empty(progress_get($s['c'])['solved'])) return $s;
    }
    return null;
}
