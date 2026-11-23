/* 星辰OA - 前端交互：侧边栏抽屉 + 提示解锁 + 通关打卡 */
document.addEventListener('DOMContentLoaded', function () {
  /* ---------- 移动端侧边栏 ---------- */
  var sbToggle = document.getElementById('sb-toggle');
  var sidebar = document.getElementById('sidebar');
  if (sbToggle && sidebar) {
    sbToggle.addEventListener('click', function (e) {
      e.stopPropagation();
      sidebar.classList.toggle('open');
    });
    document.addEventListener('click', function (e) {
      if (sidebar.classList.contains('open') && !sidebar.contains(e.target) && e.target !== sbToggle) {
        sidebar.classList.remove('open');
      }
    });
  }

  var hintBtn = document.getElementById('hint-btn');
  if (hintBtn) {
    var box = document.getElementById('hint-box');
    var level = parseInt(hintBtn.dataset.level || '0', 10);
    var texts = [];

    function renderBox() {
      if (!texts.length) { box.classList.remove('show'); return; }
      box.classList.add('show');
      box.innerHTML = texts.map(function (t, i) {
        return '<b>提示 ' + (i + 1) + '/3：</b>' + escapeHtml(t);
      }).join('<br><br>');
    }
    function escapeHtml(s) {
      return String(s).replace(/[&<>"']/g, function (m) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m];
      });
    }
    function fetchHint(lv) {
      fetch('api/hint.php?c=' + encodeURIComponent(hintBtn.dataset.challenge) + '&level=' + lv)
        .then(function (r) { return r.json(); })
        .then(function (d) {
          if (d.ok) {
            texts.push(d.text);
            renderBox();
            level = lv;
            hintBtn.textContent = '获取提示（' + level + '/3）';
          } else {
            box.classList.add('show');
            box.textContent = d.msg || '获取失败';
          }
        });
    }

    if (level > 0) {
      // 已解锁过的提示一次性拉回来
      var chain = Promise.resolve();
      for (var i = 1; i <= level; i++) (function (lv) {
        chain = chain.then(function () {
          return fetch('api/hint.php?c=' + encodeURIComponent(hintBtn.dataset.challenge) + '&level=' + lv)
            .then(function (r) { return r.json(); })
            .then(function (d) { if (d.ok) texts.push(d.text); });
        });
      })(i);
      chain.then(renderBox);
    }

    hintBtn.addEventListener('click', function () {
      if (level >= 3) return;
      fetchHint(level + 1);
    });
  }

  var solvedBtn = document.getElementById('solved-btn');
  if (solvedBtn) {
    solvedBtn.addEventListener('click', function () {
      var mark = solvedBtn.dataset.solved !== '1';
      var fd = new FormData();
      fd.append('challenge', solvedBtn.dataset.challenge);
      fd.append('action', mark ? 'mark' : 'unmark');
      fetch('api/progress.php', { method: 'POST', body: fd })
        .then(function (r) { return r.json(); })
        .then(function (d) {
          if (d.ok) location.reload();
        });
    });
  }
});
