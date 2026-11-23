<?php
/**
 * 星辰OA 业务系统 - 数据库封装层
 * 基于 mysqli（兼容 PHP 7.x / 8.x），凭据复用原版 sql-connections/db-creds.inc
 */

if (!defined('SQLI_MALL')) { die('forbidden'); }

/** 读取数据库凭据（只读一次） */
function oa_creds()
{
    static $cache = null;
    if ($cache !== null) return $cache;
    $f = dirname(__DIR__, 2) . '/sql-connections/db-creds.inc';
    if (!file_exists($f)) {
        die('找不到 sql-connections/db-creds.inc，请先解压完整 sqli-labs。');
    }
    include $f;
    $cache = [$host, $dbuser, $dbpass, $dbname, $dbname1];
    return $cache;
}

class App
{
    /** @var mysqli */
    public $conn;
    public $error = '';
    public $dbname;

    public function __construct($dbname = 'security', $charset = 'utf8')
    {
        if (!extension_loaded('mysqli')) {
            die('PHP 未启用 mysqli 扩展：请在 php.ini 中开启 extension=mysqli 后重启 Web 服务。');
        }
        list($host, $dbuser, $dbpass, $dbname0, $dbname1) = oa_creds();

        // $dbname 允许挑战关传入 'challenges'
        $useDb = ($dbname === 'challenges') ? $dbname1 : $dbname0;
        $this->dbname = $useDb;

        mysqli_report(MYSQLI_REPORT_OFF); // 关闭异常抛出，交给各关卡自己处理错误
        $this->conn = @new mysqli($host, $dbuser, $dbpass, $useDb);
        if ($this->conn->connect_errno) {
            die('数据库连接失败：' . $this->conn->connect_error . '<br>请先访问 <a href="setup.php">setup.php</a> 初始化数据库。');
        }
        $this->conn->set_charset($charset);
    }

    /**
     * 探测环境是否可用：返回 '' 表示正常，否则返回错误原因
     * 首页 / 模块页用它决定是渲染业务数据还是提示先初始化
     */
    public static function probe()
    {
        if (!extension_loaded('mysqli')) return 'PHP 未启用 mysqli 扩展';
        list($host, $dbuser, $dbpass, $dbname) = oa_creds();
        mysqli_report(MYSQLI_REPORT_OFF);
        $c = @new mysqli($host, $dbuser, $dbpass, $dbname);
        if ($c->connect_errno) return '数据库连接失败：' . $c->connect_error;
        $r = @$c->query("SHOW TABLES LIKE 'oa_employee'");
        $ok = $r && $r->num_rows > 0;
        @$c->close();
        return $ok ? '' : '数据库尚未初始化';
    }

    /** 执行单条 SQL，出错时记录错误信息（供报错注入关卡展示） */
    public function q($sql)
    {
        $r = @$this->conn->query($sql);
        if ($r === false) {
            $this->error = @$this->conn->error;
        } else {
            $this->error = '';
        }
        return $r;
    }

    /** 执行堆叠语句（多语句），用于 Less-38 起的 stacked queries 关卡 */
    public function q_multi($sql)
    {
        $r = @$this->conn->multi_query($sql);
        if ($r === false) {
            $this->error = @$this->conn->error;
            return false;
        }
        $this->error = '';
        // 取第一个结果集，然后清空其余结果，避免 Commands out of sync
        $first = @$this->conn->store_result();
        while (@$this->conn->more_results() && @$this->conn->next_result()) {
            $extra = @$this->conn->store_result();
            if ($extra) { $extra->free(); }
        }
        return $first ?: true;
    }

    public function fetch($res)
    {
        return ($res instanceof mysqli_result) ? $res->fetch_assoc() : false;
    }

    public function fetch_row($res)
    {
        return ($res instanceof mysqli_result) ? $res->fetch_row() : false;
    }

    public function fetch_all($res)
    {
        return ($res instanceof mysqli_result) ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    /** 当前连接上的最近一次错误（报错注入回显用） */
    public function error()
    {
        return $this->error;
    }

    /* ================= 渲染原语（业务化输出） ================= */

    /** 键值结果卡：模拟业务系统里的“查询结果” */
    public function card($pairs)
    {
        $html = '<div class="result-card"><div class="result-card-title">查询结果</div>';
        foreach ($pairs as $label => $val) {
            $html .= '<div class="result-row"><span class="result-label">' . e($label) . '</span>'
                   . '<span class="result-value">' . e($val) . '</span></div>';
        }
        $html .= '</div>';
        return $html;
    }

    /** 表格：多行结果。$cols 支持两种写法：['字段1','字段2'] 或 ['中文表头' => '字段名'] */
    public function table($rows, $cols = null)
    {
        if (!$rows || !count($rows)) return $this->note('未查询到相关记录。');
        if ($cols === null) $cols = array_keys($rows[0]);
        $head = [];
        foreach ($cols as $k => $v) {
            if (is_string($k)) { $head[] = [$k, $v]; }
            else               { $head[] = [$v, $v]; }
        }
        $html = '<div class="result-table-wrap"><table class="result-table"><thead><tr>';
        foreach ($head as $h) $html .= '<th>' . e($h[0]) . '</th>';
        $html .= '</tr></thead><tbody>';
        foreach ($rows as $row) {
            $html .= '<tr>';
            foreach ($head as $h) $html .= '<td>' . e($row[$h[1]] ?? '') . '</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody></table></div>';
        return $html;
    }

    /** 系统提示框：用于回显错误信息（模拟真实站点把 SQL 报错打印出来的场景） */
    public function note($msg)
    {
        if ($msg === '' || $msg === null) return '';
        return '<div class="sys-note">' . e($msg) . '</div>';
    }

    /** 普通信息提示 */
    public function info($msg)
    {
        if ($msg === '' || $msg === null) return '';
        return '<div class="sys-info">' . e($msg) . '</div>';
    }
}

/** HTML 转义 */
function e($s)
{
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

/**
 * 老版人事系统自研的“安全过滤”函数（Less-32 的模拟对象）
 * 在反斜杠、单引号、双引号前加反斜杠——思路和 addslashes 一样，
 * 但同样不认识多字节编码，GBK 连接下依旧可被宽字节绕过。
 */
function oa_escape_backslash($s)
{
    return str_replace(array('\\', "'", '"'), array('\\\\', "\\'", '\\"'), $s);
}
