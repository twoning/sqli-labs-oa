<?php
/**
 * 进度接口：api/progress.php
 * POST: action=mark|unmark, challenge=Less-1
 * GET : 返回全部进度
 */
define('SQLI_MALL', true);
error_reporting(0);
header('Content-Type: application/json; charset=utf-8');

require __DIR__ . '/../lib/progress.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo json_encode(['ok' => true, 'data' => progress_load()]);
    exit;
}

$challenge = $_POST['challenge'] ?? '';
$solved    = ($_POST['action'] ?? '') === 'mark';
if (!preg_match('/^Less-\d+[a-z]?$/i', $challenge)) {
    echo json_encode(['ok' => false, 'msg' => '参数错误']);
    exit;
}

progress_mark_solved($challenge, $solved);
echo json_encode(['ok' => true, 'solved' => $solved]);
