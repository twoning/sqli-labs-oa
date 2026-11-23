<?php
/**
 * 环境初始化 / 重置：app/setup.php
 *
 * 重建「星辰OA」业务库（security）：
 *   oa_employee  员工花名册（人事主数据）
 *   oa_notice    公文公告库
 *   oa_workflow  审批流程单
 *   uagents      终端准入审计日志
 *   referers     单点登录来源审计日志
 * 以及 challenges 库（随机表名 + 随机密钥列，供综合挑战关使用）
 *
 * 说明：这里刻意保留“老系统”的历史遗留问题——员工表密码明文存储。
 * 真实世界的遗留 OA / ERP 里这非常常见，也正是注入危害被放大的原因。
 */
define('SQLI_MALL', true);
error_reporting(E_ALL & ~E_DEPRECATED);
require __DIR__ . '/lib/db.php';

$credsFile = dirname(__DIR__) . '/sql-connections/db-creds.inc';
include $credsFile;

$logs = [];
function step(&$logs, $ok, $msg)
{
    $logs[] = ['ok' => $ok, 'msg' => $msg];
}

@$conn = new mysqli($host, $dbuser, $dbpass);
if ($conn->connect_errno) {
    die('无法连接 MySQL，请检查 sql-connections/db-creds.inc 中的账号密码：' . $conn->connect_error);
}
$conn->set_charset('utf8');

/* ================= security 库（星辰OA 业务库） ================= */
$ok = $conn->query("DROP DATABASE IF EXISTS security");
step($logs, $ok, "清理旧 security 库" . ($ok ? '' : '：' . $conn->error));

$ok = $conn->query("CREATE DATABASE security CHARACTER SET utf8mb4");
step($logs, $ok, "创建 security 库" . ($ok ? '' : '：' . $conn->error));
$conn->select_db('security');

/* ---------- 员工花名册 ---------- */
$conn->query("CREATE TABLE oa_employee (
  id INT(6) UNSIGNED NOT NULL AUTO_INCREMENT,
  emp_no VARCHAR(16) NOT NULL,
  name VARCHAR(32) NOT NULL,
  username VARCHAR(32) NOT NULL,
  password VARCHAR(64) NOT NULL,
  dept VARCHAR(32) NOT NULL,
  title VARCHAR(48) NOT NULL,
  email VARCHAR(64) NOT NULL,
  mobile VARCHAR(16) NOT NULL,
  hiredate DATE NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uk_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
step($logs, !$conn->error, "创建 oa_employee 表（员工花名册）" . ($conn->error ? '：' . $conn->error : ''));

$conn->query("INSERT INTO oa_employee (id, emp_no, name, username, password, dept, title, email, mobile, hiredate) VALUES
(1,'XC0001','张伟','admin','Xc@Admin2024','信息中心','系统管理员','zhangwei@xingchen-oa.com','13801234567','2016-03-14'),
(2,'XC0102','李娜','lina','Ln#Hr2023','人力资源部','人事专员','lina@xingchen-oa.com','13902345678','2018-07-02'),
(3,'XC0208','王芳','wangfang','Wf\$Fin2024','财务部','会计主管','wangfang@xingchen-oa.com','13703456789','2015-11-23'),
(4,'XC0115','陈磊','chenlei','Cl@Admin88','行政部','行政主管','chenlei@xingchen-oa.com','13604567890','2017-05-09'),
(5,'XC0301','刘洋','liuyang','Ly!Mkt2023','市场部','市场经理','liuyang@xingchen-oa.com','13505678901','2019-02-18'),
(6,'XC0402','赵静','zhaojing','Zj#Legal66','法务部','法务专员','zhaojing@xingchen-oa.com','13406789012','2020-09-01'),
(7,'XC0503','孙强','sunqiang','Sq@Dev2024','研发中心','高级开发工程师','sunqiang@xingchen-oa.com','13307890123','2017-08-21'),
(8,'XC0601','周梅','zhoumei','Zm\$Buy2023','采购部','采购专员','zhoumei@xingchen-oa.com','13208901234','2018-12-03'),
(9,'XC0704','吴浩','wuhao','Wh@Ops2024','运维中心','运维工程师','wuhao@xingchen-oa.com','13109012345','2019-06-17'),
(10,'XC0116','郑帅','zhengshuai','Zs!Hr2024','人力资源部','招聘主管','zhengshuai@xingchen-oa.com','13010123456','2021-03-08'),
(11,'XC0802','冯洁','fengjie','Fj@Sales20','销售部','区域销售经理','fengjie@xingchen-oa.com','18911234567','2016-10-26'),
(12,'XC0901','徐臻','xuzhen','Xz#Qa2024','质量管理部','质量工程师','xuzhen@xingchen-oa.com','18822345678','2020-04-13'),
(13,'XC1001','何俊','hejun','Hj@Intern24','研发中心','实习生','hejun@xingchen-oa.com','18733456789','2024-07-01')");
step($logs, !$conn->error, "写入 13 名员工数据" . ($conn->error ? '：' . $conn->error : ''));

/* ---------- 公文公告库 ---------- */
$conn->query("CREATE TABLE oa_notice (
  id INT(6) UNSIGNED NOT NULL AUTO_INCREMENT,
  title VARCHAR(128) NOT NULL,
  content TEXT NOT NULL,
  author VARCHAR(32) NOT NULL,
  category VARCHAR(24) NOT NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
step($logs, !$conn->error, "创建 oa_notice 表（公文公告）" . ($conn->error ? '：' . $conn->error : ''));

$conn->query("INSERT INTO oa_notice (id, title, content, author, category, created_at) VALUES
(1,'关于2024年度信息安全培训的通知','各部门：定于本月18日至19日在三楼多功能厅开展年度信息安全培训，内容涵盖账号口令规范、邮件钓鱼识别与终端防护。请各部门于本周五前将参训名单报至信息中心。培训结束后统一进行线上考核，成绩计入部门年度考核。','陈磊','行政公告','2024-09-02 09:15:00'),
(2,'关于调整考勤打卡规则的通知','自10月1日起，考勤打卡时间调整为上午9:00前、下午18:00后。外勤人员需在OA提交《外出报备单》，经直属主管审批后生效。漏打卡当月累计超过3次者，按事假处理。','郑帅','人事制度','2024-09-05 14:30:00'),
(3,'关于印发《差旅费报销管理办法》的通知','新修订的《差旅费报销管理办法》已经总经理办公会审议通过，现予印发。市内交通费凭票实报，住宿费按职级标准执行，超标部分由个人承担。报销单须在出差结束后15个工作日内提交，逾期不予受理。','王芳','财务制度','2024-08-20 10:05:00'),
(4,'关于办公区网络设备升级的公告','为提升办公网络稳定性，信息中心将于本周六 22:00 至次日 02:00 对 A 座核心交换机进行升级。升级期间 OA、邮件系统、财务系统将短时中断，请提前保存工作内容。','张伟','运维公告','2024-09-10 17:40:00'),
(5,'关于开展年度员工体检的通知','本年度员工体检定于10月中下旬进行，体检机构为市第一人民医院体检中心。请在OA「员工服务」模块选择体检套餐并预约时间，逾期未预约者视为放弃。','李娜','员工服务','2024-09-12 11:20:00'),
(6,'关于规范用印申请流程的通知','为加强印章管理，即日起所有用印申请须在OA提交《用印申请单》，注明用印文件名称、份数及用途，经部门负责人及法务审核后方可用印。紧急用印须电话报备并事后补单。','赵静','行政制度','2024-07-18 15:50:00'),
(7,'关于2024年中秋节放假安排的通知','根据国家法定节假日安排，中秋节放假时间为9月15日至17日，共3天。放假期间各部门须安排值班人员，值班表于9月12日前报行政部备案。','陈磊','行政公告','2024-09-01 08:30:00'),
(8,'关于OA系统停机维护的公告','OA系统将于9月21日 00:00-06:00 进行版本升级，期间无法登录。本次升级涉及流程引擎与移动端适配，升级完成后请清除浏览器缓存再访问。','张伟','运维公告','2024-09-18 16:10:00'),
(9,'关于印发《供应商管理办法》的通知','《供应商管理办法》自发布之日起施行。新增供应商须通过资质审核并录入系统，单一来源采购须提交书面说明。采购部每季度对在册供应商进行一次履约评价。','周梅','采购制度','2024-06-25 13:45:00'),
(10,'关于固定资产盘点工作的通知','定于11月开展全公司固定资产盘点。请各部门指定一名盘点负责人，配合行政部核对在用资产编号、存放位置及使用人。盘亏资产须书面说明原因。','陈磊','行政公告','2024-10-08 09:00:00')");
step($logs, !$conn->error, "写入 10 条公文公告" . ($conn->error ? '：' . $conn->error : ''));

/* ---------- 审批流程单 ---------- */
$conn->query("CREATE TABLE oa_workflow (
  id INT(6) UNSIGNED NOT NULL AUTO_INCREMENT,
  wf_no VARCHAR(24) NOT NULL,
  type VARCHAR(24) NOT NULL,
  status VARCHAR(16) NOT NULL,
  applicant VARCHAR(32) NOT NULL,
  amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
step($logs, !$conn->error, "创建 oa_workflow 表（审批流程）" . ($conn->error ? '：' . $conn->error : ''));

$conn->query("INSERT INTO oa_workflow (id, wf_no, type, status, applicant, amount, created_at) VALUES
(1,'WF2024090001','差旅报销','审批中','李娜',1860.00,'2024-09-03 09:12:00'),
(2,'WF2024090002','用印申请','已通过','赵静',0.00,'2024-09-03 10:40:00'),
(3,'WF2024090003','采购申请','待归档','周梅',24800.00,'2024-09-04 14:05:00'),
(4,'WF2024090004','外出报备','已通过','冯洁',0.00,'2024-09-05 08:50:00'),
(5,'WF2024090005','差旅报销','已驳回','孙强',4320.00,'2024-09-05 16:22:00'),
(6,'WF2024090006','资产领用','审批中','何俊',0.00,'2024-09-06 09:30:00'),
(7,'WF2024090007','合同评审','已通过','赵静',0.00,'2024-09-09 11:15:00'),
(8,'WF2024090008','采购申请','审批中','周梅',8600.00,'2024-09-10 13:48:00'),
(9,'WF2024090009','差旅报销','待归档','刘洋',2350.00,'2024-09-11 15:05:00'),
(10,'WF2024090010','用印申请','已驳回','王芳',0.00,'2024-09-12 09:55:00'),
(11,'WF2024090011','外出报备','已通过','徐臻',0.00,'2024-09-13 10:20:00'),
(12,'WF2024090012','资产领用','审批中','吴浩',0.00,'2024-09-14 14:35:00')");
step($logs, !$conn->error, "写入 12 条审批流程单" . ($conn->error ? '：' . $conn->error : ''));

/* ---------- 审计日志表（请求头注入关卡用） ---------- */
$conn->query("CREATE TABLE uagents (id int(3) NOT NULL AUTO_INCREMENT, uagent varchar(256) NOT NULL, ip_address varchar(35) NOT NULL, username varchar(32) NOT NULL, PRIMARY KEY (id))");
step($logs, !$conn->error, "创建 uagents 表（终端准入审计）" . ($conn->error ? '：' . $conn->error : ''));

$conn->query("CREATE TABLE referers (id int(3) NOT NULL AUTO_INCREMENT, referer varchar(256) NOT NULL, ip_address varchar(35) NOT NULL, username varchar(32) NOT NULL, PRIMARY KEY (id))");
step($logs, !$conn->error, "创建 referers 表（登录来源审计）" . ($conn->error ? '：' . $conn->error : ''));

/* ================= challenges 库（综合挑战关用） ================= */
function num_gen($len, $chars)
{
    $s = '';
    for ($i = 0; $i < $len; $i++) $s .= $chars[rand(0, strlen($chars) - 1)];
    return $s;
}
$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
$table = num_gen(10, $chars);
$secret_key = 'secret_' . num_gen(4, $chars);

$conn->query("DROP DATABASE IF EXISTS $dbname1");
$conn->query("CREATE DATABASE $dbname1 CHARACTER SET utf8mb4");
$conn->select_db($dbname1);
$conn->query("CREATE TABLE $table (id INT(2) UNSIGNED NOT NULL DEFAULT 1, sessid CHAR(32) PRIMARY KEY NOT NULL, $secret_key CHAR(32) NOT NULL, tryy INT(11) UNSIGNED NOT NULL DEFAULT 0)");
step($logs, !$conn->error, "创建挑战表 $table" . ($conn->error ? '：' . $conn->error : ''));

$sec_key = num_gen(24, 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789');
$hash = md5(rand(0, 100000));
$conn->query("INSERT INTO $dbname1.$table VALUES (1, '$hash', '$sec_key', 0)");
step($logs, !$conn->error, "写入挑战密钥（24 位随机串）" . ($conn->error ? '：' . $conn->error : ''));

// 把本次随机的表名/密钥列名记录下来，供框架内部使用（学员仍需自己用注入去发现）
if (!is_dir(__DIR__ . '/data')) { @mkdir(__DIR__ . '/data', 0777, true); }
@file_put_contents(__DIR__ . '/data/challenge_series.json', json_encode([
    'table' => $table, 'secret_col' => $secret_key,
]));

// 重置时清掉旧的通关记录，避免难度不一致
@file_put_contents(__DIR__ . '/data/progress.json', json_encode(new stdClass()));
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>环境初始化 - 星辰OA</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="setup-wrap">
  <div class="oa-card">
    <div class="oa-card-hd">
      <h1>环境初始化</h1>
      <span class="hd-meta">运维工具</span>
    </div>
    <div class="oa-card-bd">
      <p class="biz-desc">正在重建「星辰OA」业务库与演练接口库，等同于目标系统重装上线。</p>
      <ul class="setup-log">
        <?php foreach ($logs as $l): ?>
          <li class="<?= $l['ok'] ? 'ok' : 'bad' ?>"><?= $l['ok'] ? '✔' : '✘' ?> <?= e($l['msg']) ?></li>
        <?php endforeach; ?>
      </ul>
      <p class="muted" style="margin-top:16px;font-size:13px">
        初始化完成后即可前往 <a href="index.php">工作台</a> 开始使用。<br>
        注意：此操作会清空全部业务数据、演练密钥与实训进度。
      </p>
    </div>
  </div>
  <p class="muted" style="text-align:center;font-size:12px">
    星辰科技股份有限公司 · OA 办公协同平台 v3.2.1
  </p>
</div>
</body>
</html>
