<?php
/**
 * 页面骨架：侧边栏 + 顶栏 + 内容区
 * 让所有页面看起来都是同一个 OA 系统里的不同菜单
 */
if (!defined('SQLI_MALL')) { die('forbidden'); }

/**
 * @param string $pageTitle 顶栏标题
 * @param string $active    当前高亮：模块 key / workbench / train / setup
 * @param array  $crumbs    面包屑（不含首页）
 */
function oa_layout_start($pageTitle, $active = '', $crumbs = [])
{
    $mods = oa_modules();
    $total = 0; $done = 0;
    foreach ($mods as $mod) {
        foreach ($mod['items'] as $cid) {
            $total++;
            if (!empty(progress_get($cid)['solved'])) $done++;
        }
    }
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle) ?> - 星辰OA</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="layout">

  <aside class="sidebar" id="sidebar">
    <a class="sb-brand" href="index.php">
      <span class="sb-mark">星</span>
      <span class="sb-brand-text"><b>星辰OA</b><small>办公协同平台</small></span>
    </a>

    <nav class="sb-nav">
      <a class="sb-item <?= $active === 'workbench' ? 'active' : '' ?>" href="index.php">
        <span class="sb-ico" style="background:#334155">台</span>工作台
      </a>

      <div class="sb-group">业务模块</div>
      <?php foreach ($mods as $key => $mod): ?>
        <a class="sb-item <?= $active === $key ? 'active' : '' ?>" href="<?= e(oa_module_url($key)) ?>">
          <span class="sb-ico" style="background:<?= e($mod['color']) ?>"><?= e($mod['icon']) ?></span><?= e($mod['name']) ?>
        </a>
      <?php endforeach; ?>

      <div class="sb-group">实训工具</div>
      <a class="sb-item <?= $active === 'path' ? 'active' : '' ?>" href="path.php">
        <span class="sb-ico" style="background:#0d9488">路</span>学习路线
      </a>
      <a class="sb-item <?= $active === 'train' ? 'active' : '' ?>" href="train.php">
        <span class="sb-ico" style="background:#b45309">练</span>练习中心
      </a>
      <a class="sb-item <?= $active === 'setup' ? 'active' : '' ?>" href="setup.php">
        <span class="sb-ico" style="background:#64748b">置</span>环境重置
      </a>
    </nav>

    <div class="sb-foot">
      <div class="sb-progress-label">实训进度 <b><?= $done ?></b> / <?= $total ?></div>
      <div class="sb-progress"><i style="width:<?= $total ? round($done / $total * 100) : 0 ?>%"></i></div>
    </div>
  </aside>

  <div class="main">
    <header class="topbar">
      <div class="tb-left">
        <button class="tb-menu" id="sb-toggle" type="button" aria-label="菜单">☰</button>
        <div class="tb-title"><?= e($pageTitle) ?></div>
      </div>
      <div class="tb-right">
        <form class="tb-search" method="GET" action="challenge.php" onsubmit="return false;">
          <input type="text" placeholder="搜索公文、审批单、员工…" aria-label="搜索">
        </form>
        <span class="tb-pill">消息<i class="dot"></i></span>
        <span class="tb-user"><span class="avatar">张</span>张伟 <em>信息中心</em></span>
      </div>
    </header>

    <main class="content">
      <div class="crumb">
        <a href="index.php">首页</a>
        <?php foreach ($crumbs as $c): ?>
          <span class="sep">/</span><?= e($c) ?>
        <?php endforeach; ?>
      </div>
<?php
}

function oa_layout_end()
{
?>
    </main>

    <footer class="foot">
      星辰科技股份有限公司 · OA 办公协同平台 v3.2.1 &nbsp;|&nbsp; 内部系统，请勿外传
    </footer>
  </div>
</div>
<script src="assets/app.js"></script>
</body>
</html>
<?php
}

/** 未初始化时的提示页 */
function oa_layout_not_ready($reason)
{
    oa_layout_start('系统未就绪', 'workbench', []);
    echo '<div class="oa-card"><div class="oa-card-hd"><h2>系统尚未初始化</h2></div>'
       . '<div class="oa-card-bd">'
       . '<div class="sys-note">' . e($reason) . '</div>'
       . '<p class="muted" style="margin-top:14px">请先访问 <a href="setup.php">环境重置</a> 页面完成数据库初始化，'
       . '系统会自动建表并写入业务数据。</p>'
       . '</div></div>';
    oa_layout_end();
}
