<?php
/**
 * 业务模块页：列出该模块下的功能入口
 */
define('SQLI_MALL', true);
error_reporting(0);
require __DIR__ . '/lib/db.php';
require __DIR__ . '/lib/progress.php';
require __DIR__ . '/lib/modules.php';
require __DIR__ . '/lib/layout.php';

$mods = oa_modules();
$key  = $_GET['m'] ?? '';
if (!isset($mods[$key])) { header('Location: index.php'); exit; }
$mod = $mods[$key];

/* 读取各功能页的元信息 */
$items = [];
foreach ($mod['items'] as $cid) {
    $f = __DIR__ . '/challenges/' . $cid . '.php';
    if (!file_exists($f)) continue;
    $def = include $f;
    if (!is_array($def)) continue;
    $def['id'] = $cid;
    $items[] = $def;
}

$probe = App::probe();
$doneCount = 0;
foreach ($mod['items'] as $cid) if (!empty(progress_get($cid)['solved'])) $doneCount++;

oa_layout_start($mod['name'], $key, [$mod['name']]);
?>

      <div class="mod-head">
        <span class="mod-ico" style="background:<?= e($mod['color']) ?>"><?= e($mod['icon']) ?></span>
        <div class="mod-text">
          <h1><?= e($mod['name']) ?></h1>
          <p><?= e($mod['desc']) ?></p>
        </div>
        <div class="mod-stat">
          <b><?= count($mod['items']) ?></b>
          <span>个功能</span>
        </div>
      </div>

      <?php if ($probe !== ''): ?>
        <div class="oa-card"><div class="oa-card-bd">
          <div class="sys-note"><?= e($probe) ?>，请先访问 <a href="setup.php">环境重置</a> 完成初始化。</div>
        </div></div>
      <?php endif; ?>

      <div class="func-grid">
        <?php foreach ($items as $it): $solved = !empty(progress_get($it['id'])['solved']); ?>
          <a class="func-card <?= $solved ? 'done' : '' ?>" href="challenge.php?c=<?= e($it['id']) ?>">
            <div class="fc-hd">
              <span class="fc-name"><?= e($it['title']) ?></span>
              <?php if ($solved): ?><span class="fc-tag">已完成</span><?php endif; ?>
            </div>
            <div class="fc-desc"><?= e($it['desc'] ?? '') ?></div>
            <div class="fc-foot"><span>进入功能</span><span class="fc-arrow">→</span></div>
          </a>
        <?php endforeach; ?>
      </div>

      <div class="oa-card tip-card">
        <div class="oa-card-bd">
          <b class="tip-t">测试提示</b>
          <p>本模块共有 <b><?= count($mod['items']) ?></b> 个功能入口，你已完成 <b><?= $doneCount ?></b> 个。
             每个入口都是一个独立接口，参数、请求方式、返回内容各不相同。
             逐个确认：它接收什么参数？参数有没有被过滤？失败时返回什么？</p>
        </div>
      </div>

<?php
oa_layout_end();
