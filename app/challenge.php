<?php
/**
 * 挑战运行时：app/challenge.php?c=Less-1
 * 负责加载关卡定义 → 执行漏洞逻辑 → 套业务页面渲染
 */
define('SQLI_MALL', true);
error_reporting(0);
ob_start(); // 缓冲所有意外输出，保证 Cookie/会话头可用

require __DIR__ . '/lib/db.php';
require __DIR__ . '/lib/render.php';
require __DIR__ . '/lib/progress.php';

// 1. 校验关卡名（白名单：必须存在于 challenges 目录）
$c = $_GET['c'] ?? '';
$file = __DIR__ . '/challenges/' . basename($c) . '.php';
if (!preg_match('/^Less-\d+[a-z]?$/i', $c) || !file_exists($file)) {
    // 缺少关卡标识时引导回练习中心（例如误输入 challenge.php?id=1）
    header('Location: index.php');
    exit;
}

// 2. 加载关卡定义（元信息 + 漏洞逻辑）
$def = include $file;
if (!is_array($def) || !isset($def['run'])) {
    die('关卡定义损坏：' . e($c));
}
$def['id'] = $c;

// 3. 建立数据库连接（宽字节关卡需要 gbk 连接编码）
$app = new App($def['db'] ?? 'security', $def['charset'] ?? 'utf8');
if (!empty($def['set_names'])) {
    // 还原原版 SET NAMES 方式：让转义函数保持“单字节”视角，与真实旧站点行为一致
    $app->conn->query('SET NAMES ' . $def['set_names']);
}

// 4. 执行漏洞逻辑
$body = ($def['run'])($app);

// 5. 读取进度并渲染
$state  = progress_get($c);
$hintLv = (int)($state['hints'] ?? 0);
$solved = !empty($state['solved']);

if (!empty($def['challenge_series'])) {
    render_challenge_page($def, $body, $hintLv, $solved);
} else {
    render_page($def, $body, $hintLv, $solved);
}
