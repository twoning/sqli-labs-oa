<?php
/**
 * 练习中心：按训练阶段排列全部 69 个知识点（学员导航用）
 */
define('SQLI_MALL', true);
error_reporting(0);
require __DIR__ . '/lib/db.php';
require __DIR__ . '/lib/progress.php';
require __DIR__ . '/lib/modules.php';
require __DIR__ . '/lib/layout.php';

$groups = [
    'g1' => ['title' => '01 · 公文与查询接口', 'ids' => ['Less-1','Less-2','Less-3','Less-4','Less-5','Less-6','Less-7','Less-8','Less-9','Less-10']],
    'g2' => ['title' => '02 · 登录与表单',     'ids' => ['Less-11','Less-12','Less-13','Less-14','Less-15','Less-16','Less-17']],
    'g3' => ['title' => '03 · 请求头与会话凭证', 'ids' => ['Less-18','Less-19','Less-20','Less-21','Less-22']],
    'g4' => ['title' => '04 · 防护策略与绕过', 'ids' => ['Less-23','Less-24','Less-25','Less-25a','Less-26','Less-26a','Less-27','Less-27a','Less-28','Less-28a','Less-29','Less-30','Less-31','Less-32','Less-33','Less-34','Less-35','Less-36','Less-37']],
    'g5' => ['title' => '05 · 多语句与写操作', 'ids' => ['Less-38','Less-39','Less-40','Less-41','Less-42','Less-43','Less-44','Less-45']],
    'g6' => ['title' => '06 · 通讯录与报表',   'ids' => ['Less-46','Less-47','Less-48','Less-49','Less-50','Less-51','Less-52','Less-53']],
    'g7' => ['title' => '07 · 内部演练接口（限时挑战）', 'ids' => ['Less-54','Less-55','Less-56','Less-57','Less-58','Less-59','Less-60','Less-61','Less-62','Less-63','Less-64','Less-65']],
];

$metas = [];
foreach (glob(__DIR__ . '/challenges/Less-*.php') as $f) {
    $cid = basename($f, '.php');
    $def = include $f;
    if (is_array($def)) { $def['id'] = $cid; $metas[$cid] = $def; }
}

$total = 0; $done = 0;
foreach ($groups as $g) {
    $total += count($g['ids']);
    foreach ($g['ids'] as $i) if (!empty(progress_get($i)['solved'])) $done++;
}
$pct = $total ? round($done / $total * 100) : 0;

oa_layout_start('练习中心', 'train', ['实训工具', '练习中心']);
?>

      <div class="oa-card train-hero">
        <div class="oa-card-bd">
          <div class="th-row">
            <div>
              <h1>Web 安全实训 · 注入专项</h1>
              <p class="muted">这里是按阶段整理的复习视图：01~07 每个阶段对应一类手法，适合查漏补缺。
                 如果你想按「第 1 步 → 第 69 步」一条主线走，请用
                 <a href="path.php"><b>学习路线</b></a>——那里标明了每一步学什么、在哪个功能里练。</p>
            </div>
            <div class="th-progress">
              <div class="thp-num"><b><?= $done ?></b><span>/ <?= $total ?></span></div>
              <div class="progress-bar"><div class="progress-fill" style="width:<?= $pct ?>%"></div></div>
              <div class="muted" style="font-size:12px;margin-top:6px">已完成 <?= $pct ?>%</div>
            </div>
          </div>
        </div>
      </div>

      <div class="oa-card guide-card">
        <div class="oa-card-hd"><h2>训练方法</h2></div>
        <div class="oa-card-bd">
          <ol class="guide-ol">
            <li><b>观察业务功能</b>：每个页面都是一个正常功能，先搞清楚它把什么数据交给了后台（URL 参数、表单字段、Cookie、请求头）。</li>
            <li><b>探测注入点</b>：对参数做单引号 <code>'</code>、双引号、数字型探测，对比页面响应是否变化。</li>
            <li><b>判断类型</b>：页面回显报错 → 报错注入；有回显无报错 → 联合/布尔；无任何差异 → 时间盲注。</li>
            <li><b>摸清数据模型</b>：核心表 <code>oa_employee</code>（人事主数据，含登录名与密码）、<code>oa_notice</code>（公文）、<code>oa_workflow</code>（审批单）。</li>
            <li><b>构造 payload</b>：手注拿库、表、字段、数据；熟练后用 <code>sqlmap</code> 复现，参数名与真实接口一致。</li>
            <li><b>卡住了</b>：每个页面底部的「实训面板」有三级提示（找注入点 / 注入方向 / 示例 payload）。</li>
          </ol>
        </div>
      </div>

      <?php foreach ($groups as $g): ?>
        <h2 class="group-title"><?= e($g['title']) ?></h2>
        <div class="ch-grid">
          <?php foreach ($g['ids'] as $cid): if (!isset($metas[$cid])) continue; $m = $metas[$cid]; $solved = !empty(progress_get($cid)['solved']); ?>
            <a class="ch-card <?= $solved ? 'done' : '' ?>" href="challenge.php?c=<?= e($cid) ?>">
              <div class="ch-name"><?= e($m['title']) ?><?= $solved ? ' ✓' : '' ?></div>
              <div class="ch-scene"><?= e($m['desc'] ?? '') ?></div>
              <div class="ch-foot">
                <span class="ch-id"><?= e($cid) ?></span>
                <span>难度 <?= str_repeat('★', $m['difficulty'] ?? 1) ?></span>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>

<?php
oa_layout_end();
