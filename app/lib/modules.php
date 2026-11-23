<?php
/**
 * 业务模块定义：把 69 个接口按真实 OA 的模块划分
 * 每个模块 = 侧边栏一个入口 = 一组业务功能页
 */
if (!defined('SQLI_MALL')) { die('forbidden'); }

function oa_modules()
{
    static $m = null;
    if ($m !== null) return $m;

    $m = [
        'notice' => [
            'name' => '公文管理',
            'icon' => '文',
            'color' => '#2563eb',
            'desc' => '公司公文、规章制度、会议纪要的发布与查阅，支持归档导出与全文检索。',
            'items' => ['Less-1', 'Less-3', 'Less-4', 'Less-7', 'Less-27', 'Less-27a', 'Less-31'],
        ],
        'workflow' => [
            'name' => '流程中心',
            'icon' => '流',
            'color' => '#7c3aed',
            'desc' => '差旅报销、用印申请、采购、外出报备等审批流程的提交与进度跟踪。',
            'items' => ['Less-2', 'Less-5', 'Less-6', 'Less-23', 'Less-26', 'Less-26a', 'Less-40', 'Less-41'],
        ],
        'meeting' => [
            'name' => '会议与用车',
            'icon' => '会',
            'color' => '#0891b2',
            'desc' => '会议室占用查询、会议预约确认、公务用车预约。',
            'items' => ['Less-8', 'Less-9', 'Less-10'],
        ],
        'hr' => [
            'name' => '人事管理',
            'icon' => '人',
            'color' => '#059669',
            'desc' => '员工通讯录、账号激活与密码自助修改，以及对接老版人事系统的数据接口。',
            'items' => ['Less-25', 'Less-25a', 'Less-32', 'Less-33', 'Less-36', 'Less-34', 'Less-37', 'Less-17', 'Less-24'],
        ],
        'asset' => [
            'name' => '资产与行政',
            'icon' => '资',
            'color' => '#d97706',
            'desc' => '固定资产领用、办公用品申领、资产编号查询。',
            'items' => ['Less-28', 'Less-28a', 'Less-35'],
        ],
        'portal' => [
            'name' => '门户与登录',
            'icon' => '户',
            'color' => '#dc2626',
            'desc' => '员工工作台登录、企业邮箱单点登录、合作伙伴入口，以及登录后的身份凭证与审计。',
            'items' => ['Less-11', 'Less-12', 'Less-13', 'Less-14', 'Less-15', 'Less-16', 'Less-18', 'Less-19', 'Less-20', 'Less-42', 'Less-43'],
        ],
        'mobile' => [
            'name' => '移动办公',
            'icon' => '移',
            'color' => '#0ea5e9',
            'desc' => '移动端审批、考勤、公告接口，前置安全网关统一校验参数。',
            'items' => ['Less-29', 'Less-30', 'Less-21', 'Less-22', 'Less-44'],
        ],
        'report' => [
            'name' => '报表中心',
            'icon' => '报',
            'color' => '#4f46e5',
            'desc' => '经营数据报表、审批统计、员工通讯录排序与导出。',
            'items' => ['Less-38', 'Less-39', 'Less-46', 'Less-47', 'Less-48', 'Less-49', 'Less-50', 'Less-51', 'Less-52', 'Less-53', 'Less-45'],
        ],
        'admin' => [
            'name' => '内部演练接口',
            'icon' => '演',
            'color' => '#475569',
            'desc' => '信息中心开放的联调接口，带请求频次限制。用于内部红队演练，不对外发布。',
            'items' => ['Less-54', 'Less-55', 'Less-56', 'Less-57', 'Less-58', 'Less-59', 'Less-60', 'Less-61', 'Less-62', 'Less-63', 'Less-64', 'Less-65'],
        ],
    ];
    return $m;
}

/** 某个关卡属于哪个模块 */
function oa_module_of($cid)
{
    foreach (oa_modules() as $key => $mod) {
        if (in_array($cid, $mod['items'], true)) return $key;
    }
    return null;
}

/** 模块名 */
function oa_module_name($key)
{
    $m = oa_modules();
    return isset($m[$key]) ? $m[$key]['name'] : '业务模块';
}

/** 模块链接 */
function oa_module_url($key)
{
    return 'module.php?m=' . urlencode($key);
}
