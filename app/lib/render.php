<?php
/**
 * 页面渲染层：把注入点包装成 OA 系统里的一个正常业务页面
 */
if (!defined('SQLI_MALL')) { die('forbidden'); }

require_once __DIR__ . '/modules.php';
require_once __DIR__ . '/curriculum.php';
require_once __DIR__ . '/layout.php';

/**
 * @param array  $meta      关卡元信息
 * @param string $body      业务内容 HTML
 * @param int    $hintLevel 已解锁的提示等级
 * @param bool   $solved    是否已标记通关
 */
function render_page($meta, $body, $hintLevel = 0, $solved = false)
{
    $id    = $meta['id'];
    $title = $meta['title'];
    $desc  = $meta['desc'] ?? '';
    $form  = $meta['form'] ?? null;
    $extra = $meta['extra_html'] ?? '';
    $task  = $meta['task'] ?? '';
    $d     = $meta['difficulty'] ?? 1;
    $stars = str_repeat('★', $d) . str_repeat('☆', 5 - $d);

    $modKey  = oa_module_of($id);
    $modName = oa_module_name($modKey);
    $pos     = oa_step_of($id);
    $prev    = oa_step_neighbor($id, -1);
    $next    = oa_step_neighbor($id, +1);

    $formHtml = '';
    if ($form) {
        $hidden = '<input type="hidden" name="c" value="' . e($id) . '">';
        $formHtml .= '<form class="biz-form" method="' . e($form['method']) . '" action="">' . $hidden;
        foreach (($form['fields'] ?? []) as $name => $label) {
            $val = '';
            if ($form['method'] === 'GET')  $val = $_GET[$name]  ?? '';
            if ($form['method'] === 'POST') $val = $_POST[$name] ?? '';
            $formHtml .= '<label class="biz-field"><span>' . e($label) . '</span>'
                       . '<input type="text" name="' . e($name) . '" value="' . e($val) . '" autocomplete="off"></label>';
        }
        $formHtml .= '<button type="submit" class="btn btn-primary">' . e($form['submit'] ?? '查 询') . '</button></form>';
    }

    oa_layout_start($title, $modKey, [$modName, $title]);
?>

      <div class="oa-card biz-card">
        <div class="oa-card-hd">
          <h2><?= e($title) ?></h2>
          <span class="hd-meta"><?= e($modName) ?></span>
        </div>
        <div class="oa-card-bd">
          <?php if ($desc): ?><p class="biz-desc"><?= e($desc) ?></p><?php endif; ?>
          <?= $formHtml ?>
          <div class="biz-result"><?= $body ?></div>
          <?= $extra ?>
        </div>
      </div>

      <div class="train-panel">
        <div class="tp-hd">
          <span class="tp-badge">实训面板</span>
          <?php if ($pos): ?>
            <span class="tp-step">第 <?= (int)$pos['no'] ?> / <?= (int)$pos['total'] ?> 步</span>
          <?php endif; ?>
          <span class="tp-note">学员工具，真实站点不存在此区域</span>
          <span class="tp-stars">难度 <?= $stars ?></span>
          <?php if ($solved): ?><span class="tp-done">✓ 已通关</span><?php endif; ?>
        </div>
        <div class="tp-bd">
          <?php if ($pos): ?>
            <p class="tp-learn"><b>这一步学什么：</b><?= e($pos['step']['topic']) ?>
              <span class="tp-learn-sep">—</span> <?= e($pos['step']['goal']) ?></p>
          <?php endif; ?>
          <?php if ($task): ?><p class="tp-task"><b>任务目标：</b><?= e($task) ?></p><?php endif; ?>
          <div class="tp-actions">
            <button class="btn" id="hint-btn" data-challenge="<?= e($id) ?>" data-level="<?= (int)$hintLevel ?>">
              获取提示（<?= (int)$hintLevel ?>/3）
            </button>
            <button class="btn <?= $solved ? 'btn-ghost' : 'btn-success' ?>" id="solved-btn"
                    data-challenge="<?= e($id) ?>" data-solved="<?= $solved ? '1' : '0' ?>">
              <?= $solved ? '取消通关标记' : '我已拿到目标数据，标记通关' ?>
            </button>
            <a class="btn btn-ghost" href="<?= e($_SERVER['REQUEST_URI']) ?>">重置本页请求</a>
          </div>
          <div id="hint-box" class="hint-box"></div>

          <div class="tp-nav">
            <?php if ($prev): ?>
              <a class="btn" href="challenge.php?c=<?= e($prev['c']) ?>">← 上一步：<?= e($prev['topic']) ?></a>
            <?php else: ?>
              <span class="tp-nav-label">这是第一步</span>
            <?php endif; ?>
            <a class="btn btn-ghost" href="path.php">学习路线</a>
            <?php if ($next): ?>
              <a class="btn" href="challenge.php?c=<?= e($next['c']) ?>">下一步：<?= e($next['topic']) ?> →</a>
            <?php else: ?>
              <span class="tp-nav-label">这是最后一步 🎉</span>
            <?php endif; ?>
          </div>
        </div>
      </div>

<?php
    oa_layout_end();
}

/** 挑战关（Less-54~65）沿用同一套外壳 */
function render_challenge_page($meta, $body, $hintLevel = 0, $solved = false)
{
    render_page($meta, $body, $hintLevel, $solved);
}
