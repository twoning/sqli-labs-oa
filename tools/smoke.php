<?php
$base = 'http://127.0.0.1/sqli-labs/app/';
$ids = [];
foreach (glob(__DIR__ . '/../app/challenges/Less-*.php') as $f) {
    $ids[] = basename($f, '.php');
}
usort($ids, function ($a, $b) { return strnatcmp($a, $b); });
$bad = 0;
foreach ($ids as $id) {
    $u = $base . 'challenge.php?c=' . $id;
    $h = @file_get_contents($u);
    if ($h === false) { echo "FAIL $id\n"; $bad++; continue; }
    $warn = (strpos($h, 'Warning') !== false) || (strpos($h, 'Fatal') !== false) || (strpos($h, 'Notice') !== false) || (strpos($h, 'Deprecated') !== false);
    $hasStep = strpos($h, '第 ') !== false && strpos($h, '69 步') !== false;
    if ($warn || !$hasStep) { echo "ISSUE $id  warn=" . ($warn?1:0) . " step=" . ($hasStep?1:0) . "\n"; $bad++; }
}
echo "关卡数：" . count($ids) . "，异常：" . $bad . "\n";
foreach (['index.php','path.php','train.php','module.php?m=notice','module.php?m=admin'] as $p) {
    $h = @file_get_contents($base . $p);
    $code = 0;
    $warn = $h === false ? 1 : ((strpos($h,'Warning')!==false || strpos($h,'Fatal')!==false) ? 1 : 0);
    echo str_pad($p, 24) . ($h === false ? 'FAIL' : 'OK len=' . strlen($h)) . ($warn ? '  <<有告警>>' : '') . "\n";
}
