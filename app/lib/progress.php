<?php
/**
 * 进度与提示解锁存档（服务端 JSON 文件，跨浏览器会话保留）
 */
if (!defined('SQLI_MALL')) { die('forbidden'); }

function progress_file()
{
    $dir = __DIR__ . '/../data';
    if (!is_dir($dir)) { @mkdir($dir, 0777, true); }
    return $dir . '/progress.json';
}

function progress_load()
{
    $f = progress_file();
    if (!file_exists($f)) return [];
    $data = json_decode(@file_get_contents($f), true);
    return is_array($data) ? $data : [];
}

function progress_save($data)
{
    $f = progress_file();
    @file_put_contents($f, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX);
}

function progress_get($id, $default = [])
{
    $data = progress_load();
    return $data[$id] ?? $default;
}

/** 解锁提示（只升不降） */
function progress_unlock_hint($id, $level)
{
    $data = progress_load();
    $cur = $data[$id]['hints'] ?? 0;
    $data[$id]['hints'] = max($cur, (int)$level);
    $data[$id]['ts'] = time();
    progress_save($data);
}

/** 标记通关 / 取消通关 */
function progress_mark_solved($id, $solved)
{
    $data = progress_load();
    if ($solved) {
        $data[$id]['solved'] = true;
        $data[$id]['solved_ts'] = time();
    } else {
        unset($data[$id]['solved'], $data[$id]['solved_ts']);
    }
    $data[$id]['ts'] = time();
    progress_save($data);
    return $data[$id];
}
