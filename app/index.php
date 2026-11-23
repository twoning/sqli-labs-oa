<?php
/**
 * 工作台首页：用真实业务数据铺满，入口全部指向对应功能页
 */
define('SQLI_MALL', true);
error_reporting(0);
require __DIR__ . '/lib/db.php';
require __DIR__ . '/lib/progress.php';
require __DIR__ . '/lib/modules.php';
require __DIR__ . '/lib/curriculum.php';
require __DIR__ . '/lib/layout.php';

$probe = App::probe();
if ($probe !== '') { oa_layout_not_ready($probe); exit; }

$app = new App('security');

/* ---------- 真实业务数据 ---------- */
$todo = $app->fetch_all($app->q(
    "SELECT id, wf_no, type, applicant, amount, status, created_at
     FROM oa_workflow WHERE status IN ('审批中','待归档') ORDER BY created_at DESC LIMIT 6"
));
$todoCount = 0;
$r = $app->fetch_row($app->q("SELECT COUNT(*) FROM oa_workflow WHERE status IN ('审批中','待归档')"));
if ($r) $todoCount = (int)$r[0];

$notices = $app->fetch_all($app->q(
    "SELECT id, title, author, category, created_at FROM oa_notice ORDER BY created_at DESC LIMIT 6"
));
$noticeCount = 0;
$r = $app->fetch_row($app->q("SELECT COUNT(*) FROM oa_notice"));
if ($r) $noticeCount = (int)$r[0];

$empCount = 0;
$r = $app->fetch_row($app->q("SELECT COUNT(*) FROM oa_employee"));
if ($r) $empCount = (int)$r[0];

/* ---------- 实训进度 ---------- */
$mods = oa_modules();
$total = 0; $done = 0;
foreach ($mods as $mod) {
    foreach ($mod['items'] as $cid) {
        $total++;
        if (!empty(progress_get($cid)['solved'])) $done++;
    }
}

$hour = (int)date('G');
$greet = $hour < 6 ? '凌晨好' : ($hour < 12 ? '早上好' : ($hour < 14 ? '中午好' : ($hour < 18 ? '下午好' : '晚上好')));

oa_layout_start('工作台', 'workbench', []);
?>

      <section class="welcome">
        <div class="wc-text">
          <h1><?= $greet ?>，张伟</h1>
          <p>今天是 <?= date('Y 年 n 月 j 日') ?>，您有 <b><?= $todoCount ?></b> 条待处理流程，<b><?= $noticeCount ?></b> 份公文可查阅。</p>
        </div>
        <div class="wc-side">
          <div class="wc-date"><?= date('n') ?>月<?= date('j') ?>日<span><?= ['日','一','二','三','四','五','六'][(int)date('w')] ?></span></div>
        </div>
      </section>

      <?php $nx = oa_next_unsolved(); if ($nx): $npos = oa_step_of($nx['c']); ?>
        <a class="continue-bar" href="challenge.php?c=<?= e($nx['c']) ?>">
          <span class="cb-label">继续学习</span>
          <span class="cb-title">第 <?= (int)$npos['no'] ?> 步 · <?= e($nx['topic']) ?></span>
          <span class="cb-meta"><?= e(oa_module_name(oa_module_of($nx['c']))) ?> · <?= e($nx['tag']) ?></span>
          <span class="cb-arrow">→</span>
        </a>
      <?php endif; ?>

      <div class="kpi-row">
        <div class="kpi">
          <div class="kpi-label">待办流程</div>
          <div class="kpi-value"><?= $todoCount ?></div>
          <div class="kpi-foot">含审批中与待归档</div>
        </div>
        <div class="kpi">
          <div class="kpi-label">公文公告</div>
          <div class="kpi-value"><?= $noticeCount ?></div>
          <div class="kpi-foot">近 30 天新增 4 份</div>
        </div>
        <div class="kpi">
          <div class="kpi-label">在册员工</div>
          <div class="kpi-value"><?= $empCount ?></div>
          <div class="kpi-foot">覆盖 9 个部门</div>
        </div>
        <div class="kpi kpi-alt">
          <div class="kpi-label">实训进度</div>
          <div class="kpi-value"><?= $done ?><em>/<?= $total ?></em></div>
          <div class="kpi-foot"><a href="path.php">查看学习路线 →</a></div>
        </div>
      </div>

      <div class="grid-2">
        <div class="oa-card">
          <div class="oa-card-hd">
            <h2>我的待办</h2>
            <a class="hd-link" href="<?= e(oa_module_url('workflow')) ?>">流程中心</a>
          </div>
          <div class="oa-card-bd nopad">
            <table class="list-table">
              <thead><tr><th>单号</th><th>类型</th><th>申请人</th><th>状态</th></tr></thead>
              <tbody>
              <?php foreach ($todo as $t): ?>
                <tr>
                  <td><a href="challenge.php?c=Less-2&amp;id=<?= (int)$t['id'] ?>"><?= e($t['wf_no']) ?></a></td>
                  <td><?= e($t['type']) ?></td>
                  <td><?= e($t['applicant']) ?></td>
                  <td><span class="st st-<?= $t['status'] === '审批中' ? 'run' : 'wait' ?>"><?= e($t['status']) ?></span></td>
                </tr>
              <?php endforeach; ?>
              <?php if (!$todo): ?><tr><td colspan="4" class="empty">暂无待办</td></tr><?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <div class="oa-card">
          <div class="oa-card-hd">
            <h2>最新公文</h2>
            <a class="hd-link" href="<?= e(oa_module_url('notice')) ?>">公文管理</a>
          </div>
          <div class="oa-card-bd nopad">
            <ul class="notice-list">
              <?php foreach ($notices as $n): ?>
                <li>
                  <a href="challenge.php?c=Less-1&amp;id=<?= (int)$n['id'] ?>"><?= e($n['title']) ?></a>
                  <span class="nl-meta"><?= e($n['category']) ?> · <?= e(substr($n['created_at'], 0, 10)) ?></span>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>
      </div>

      <div class="oa-card">
        <div class="oa-card-hd"><h2>常用功能</h2></div>
        <div class="oa-card-bd">
          <div class="entry-grid">
            <?php foreach ($mods as $key => $mod): ?>
              <a class="entry" href="<?= e(oa_module_url($key)) ?>">
                <span class="entry-ico" style="background:<?= e($mod['color']) ?>"><?= e($mod['icon']) ?></span>
                <span class="entry-body">
                  <b><?= e($mod['name']) ?></b>
                  <small><?= e($mod['desc']) ?></small>
                </span>
              </a>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <div class="oa-card tip-card">
        <div class="oa-card-bd">
          <b class="tip-t">安全测试委托说明</b>
          <p>本次委托范围为本 OA 系统的全部对外功能。系统由不同年代的外包团队陆续开发，部分接口为历史遗留，
             未接入统一认证与参数校验。请以黑盒方式逐个测试各模块的输入点，发现缺陷后记录 URL、参数与复现步骤。</p>
          <p class="muted">提示：系统中不存在「直接查询账号密码」的接口。人事主数据表 oa_employee 保存了员工登录凭据，
             它是本次测试的核心目标之一。</p>
        </div>
      </div>

<?php
oa_layout_end();
