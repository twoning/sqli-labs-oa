<?php
/**
 * 打包脚本：生成可直接丢进 phpStudy 的发布包
 * 只收 app/、sql-connections/db-creds.inc、使用说明.txt
 */
$root = dirname(__DIR__);
$out  = $root . '/sqli-lab-oa-php7-ready.zip';
@unlink($out);

$zip = new ZipArchive();
if ($zip->open($out, ZipArchive::CREATE) !== true) {
    die("无法创建 $out\n");
}

$files = [];
// app/ 全部（跳过运行时数据）
$it = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root . '/app', FilesystemIterator::SKIP_DOTS)
);
foreach ($it as $f) {
    if (!$f->isFile()) continue;
    $rel = str_replace('\\', '/', substr($f->getPathname(), strlen($root) + 1));
    if (strpos($rel, 'app/data/') === 0) continue;   // 运行时生成，不入包
    $files[] = $rel;
}
$files[] = 'sql-connections/db-creds.inc';
$files[] = 'sql-connections/.htaccess';
$files[] = '使用说明.txt';

sort($files);
foreach ($files as $rel) {
    $abs = $root . '/' . $rel;
    if (!file_exists($abs)) { echo "跳过（不存在）：$rel\n"; continue; }
    $zip->addFile($abs, $rel);
}
$zip->close();

echo "打包完成：$out\n";
echo "文件数：" . count($files) . "，大小：" . round(filesize($out) / 1024, 1) . " KB\n";
