<?php
// Less-7 公文归档导出
if (!defined('SQLI_MALL')) { die('forbidden'); }
return [
  'title' => '公文归档导出',
  'desc'  => '输入归档编号生成导出任务。无论成功与否，页面只返回一句固定话术，不透露任何细节。',
  'method' => 'GET',
  'form' => ['method' => 'GET', 'fields' => array (
  'id' => '归档编号',
), 'submit' => '查 询'],
  'difficulty' => 3,
  'task' => '双层括号闭合 + 无报错回显，用联合查询把 oa_employee 的账号密码带出来',
  'hints' => array (
  0 => '页面成功失败都只有固定话术，不泄露任何细节，也没有布尔差异可看。这种时候只剩一条路：让查询自己把数据返回。',
  1 => '闭合方式是 id=((\'...\'))，payload 要先补两个右括号再注释，然后联合查询三列。',
  2 => '示例：?id=-1\')) union select 1,group_concat(username,0x3a,password),3 from oa_employee-- -    进阶：若本机 MySQL 的 secure_file_priv 已放开（my.ini 里设为 ""），可试 ... into outfile \'D:/phpstudy_pro/WWW/shell.php\' 写文件；phpStudy 默认该值为 NULL，导出是被禁用的。',
),
  'run' => function ($app) {
    if (!isset($_GET['id'])) return $app->info('请输入归档编号。');
    $id = $_GET['id'];
    $sql = "SELECT id, title, content FROM oa_notice WHERE id=(('{$id}')) LIMIT 0,1";
    $res = $app->q($sql);
    $row = $app->fetch($res);
    if ($row) {
    return $app->card(['公文标题' => $row['title'], '正文内容' => $row['content']]);
    }
    return $app->note('归档编号不存在，请核对后重试。');
  },
];
