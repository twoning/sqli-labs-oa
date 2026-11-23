<?php
/**
 * 学习路线：69 步线性主线，明确"第几步 / 学什么 / 在哪个功能里"
 */
define('SQLI_MALL', true);
error_reporting(0);
require __DIR__ . '/lib/db.php';
require __DIR__ . '/lib/progress.php';
require __DIR__ . '/lib/modules.php';
require __DIR__ . '/lib/curriculum.php';
require __DIR__ . '/lib/layout.php';

$cv      = oa_curriculum();
$steps   = $cv['steps'];
$total   = count($steps);
$done    = 0;
foreach ($steps as $s) if (!empty(progress_get($s['c'])['solved'])) $done++;
$pct     = $total ? round($done / $total * 100) : 0;
$next    = oa_next_unsolved();

/* 读取各关卡标题（只取 meta，不执行漏洞逻辑） */
$titles = [];
foreach ($steps as $s) {
    $f = __DIR__ . '/challenges/' . $s['c'] . '.php';
    if (!file_exists($f)) continue;
    $def = include $f;
    $titles[$s['c']] = is_array($def) ? ($def['title'] ?? $s['c']) : $s['c'];
}

/* 技法标签配色 */
$tagColors = [
    '联合查询' => '#2563eb', '报错注入' => '#7c3aed', '布尔盲注' => '#0891b2',
    '时间盲注' => '#0d9488', '登录绕过' => '#dc2626', 'UPDATE 注入' => '#b45309',
    '请求头注入' => '#c2410c', 'Cookie 注入' => '#be123c', '过滤绕过' => '#4f46e5',
    'WAF 绕过' => '#9333ea', '宽字节' => '#059669', '堆叠注入' => '#b91c1c',
    'ORDER BY' => '#1d4ed8', '二次注入' => '#a21caf', '综合挑战' => '#475569',
];
function tag_style($tag, $map)
{
    $c = $map[$tag] ?? '#475467';
    return 'background:' . $c . '14;color:' . $c . ';border:1px solid ' . $c . '33';
}

oa_layout_start('学习路线', 'path', ['实训工具', '学习路线']);
?>

      <div class="path-hero">
        <div class="ph-left">
          <h1>注入专项 · 学习路线</h1>
          <p>69 步，一条主线，按顺序走完即可覆盖全部知识点。
             每一步都标明了「在哪个业务功能里」「练什么技法」「学完能做什么」。
             卡住了就点进去看三级提示，通关后自动跳到下一步。</p>
          <div class="ph-actions">
            <?php if ($next): ?>
              <a class="btn btn-primary" href="challenge.php?c=<?= e($next['c']) ?>">
                继续学习 · 第 <?= (int)(oa_step_of($next['c'])['no']) ?> 步 →
              </a>
              <span class="ph-next"><?= e($next['topic']) ?></span>
            <?php else: ?>
              <span class="ph-alldone">🎉 全部 69 步已完成</span>
            <?php endif; ?>
          </div>
        </div>
        <div class="ph-right">
          <div class="ph-num"><b><?= $done ?></b><span>/ <?= $total ?></span></div>
          <div class="progress-bar"><div class="progress-fill" style="width:<?= $pct ?>%"></div></div>
          <div class="ph-pct">已完成 <?= $pct ?>%</div>
        </div>
      </div>

      <?php foreach ($cv['chapters'] as $chNo => $ch):
            $chSteps = array_values(array_filter($steps, function ($s) use ($chNo) { return $s['ch'] === $chNo; }));
            if (!$chSteps) continue;
            $chDone = 0;
            foreach ($chSteps as $s) if (!empty(progress_get($s['c'])['solved'])) $chDone++;
      ?>
        <section class="path-ch">
          <div class="ch-head">
            <span class="ch-no">第 <?= $chNo ?> 章</span>
            <h2><?= e($ch['title']) ?></h2>
            <span class="ch-prog"><?= $chDone ?>/<?= count($chSteps) ?></span>
          </div>
          <p class="ch-intro"><?= e($ch['intro']) ?></p>

          <div class="step-list">
            <?php foreach ($chSteps as $s):
                  $pos = oa_step_of($s['c']);
                  $solved = !empty(progress_get($s['c'])['solved']);
                  $modKey = oa_module_of($s['c']);
            ?>
              <a class="step <?= $solved ? 'done' : '' ?>" href="challenge.php?c=<?= e($s['c']) ?>">
                <span class="step-no"><?= (int)$pos['no'] ?></span>
                <span class="step-main">
                  <span class="step-top">
                    <b class="step-topic"><?= e($s['topic']) ?></b>
                    <span class="tag" style="<?= tag_style($s['tag'], $tagColors) ?>"><?= e($s['tag']) ?></span>
                  </span>
                  <span class="step-goal"><?= e($s['goal']) ?></span>
                  <span class="step-meta">
                    <?= e(oa_module_name($modKey)) ?> · <?= e($titles[$s['c']] ?? $s['c']) ?>
                  </span>
                </span>
                <span class="step-side">
                  <?php if ($solved): ?><span class="step-ok">✓ 已完成</span>
                  <?php else: ?><span class="step-go">开始</span><?php endif; ?>
                </span>
              </a>
            <?php endforeach; ?>
          </div>
        </section>
      <?php endforeach; ?>

      <div class="oa-card tip-card">
        <div class="oa-card-bd">
          <b class="tip-t">怎么用这条路线</b>
          <p>· <b>按序号走</b>：1 → 69，每一步都建立在前一步的手法之上，跳着做容易卡住。</p>
          <p>· <b>每步只学一件事</b>：标题里的技法就是这一步的知识点，通关标准是拿到任务目标里指定的数据。</p>
          <p>· <b>想按业务查</b>：左侧九个业务模块是同一个系统的另一条入口，功能位置更接近真实渗透；想按阶段复习就用「练习中心」。</p>
          <p>· <b>进度自动记录</b>：在页面底部实训面板点「标记通关」，路线会自动打勾并更新"继续学习"的位置。</p>
        </div>
      </div>

<?php
oa_layout_end();
