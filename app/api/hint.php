<?php
/**
 * 提示接口：api/hint.php?c=Less-1&level=1  （三级提示，解锁后不回收）
 */
define('SQLI_MALL', true);
error_reporting(0);
header('Content-Type: application/json; charset=utf-8');

require __DIR__ . '/../lib/progress.php';

$c = $_GET['c'] ?? '';
$level = (int)($_GET['level'] ?? 1);
$file = __DIR__ . '/../challenges/' . basename($c) . '.php';

if (!preg_match('/^Less-\d+[a-z]?$/i', $c) || !file_exists($file)) {
    echo json_encode(['ok' => false, 'msg' => '关卡不存在']);
    exit;
}
if ($level < 1 || $level > 3) {
    echo json_encode(['ok' => false, 'msg' => '提示等级错误']);
    exit;
}

$def = include $file;
$hints = $def['hints'] ?? [];
if (!isset($hints[$level - 1])) {
    echo json_encode(['ok' => false, 'msg' => '该等级暂无提示']);
    exit;
}

progress_unlock_hint($c, $level);
echo json_encode([
    'ok' => true,
    'level' => $level,
    'text' => $hints[$level - 1],
]);
