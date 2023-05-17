<?php
ob_start();
if (!defined('IN_ECS')) {
    die('Hacking attempt');
}

if (__FILE__ == '') {
    die('Fatal error code: 0');
}

/* 取得当前ecshop所在的根目录 */
define('ROOT_PATH', str_replace('includes/init.php', '', str_replace('\\', '/', __FILE__)));

/** Get Config */
require(ROOT_PATH . 'includes/config.php');

/* 初始化设置 */
@ini_set('memory_limit',          '64M');
@ini_set('session.cache_expire',  180);
@ini_set('session.use_trans_sid', 0);
@ini_set('session.use_cookies',   1);
//@ini_set('session.auto_start',    0);


if (DIRECTORY_SEPARATOR == '\\') {
    @ini_set('include_path', '.;' . ROOT_PATH);
} else {
    @ini_set('include_path', '.:' . ROOT_PATH);
}

/**
 * Debug Mode
 * 0    => Production
 * 8    => Dev Simple And write log
 * 2    => Recompiled
 * 89   => Dev Advanced
 */
define('DEBUG_MODE', 2);


/** Debug Mode */
if (DEBUG_MODE && DEBUG_MODE == 8) {
    error_reporting(E_ALL);
    @ini_set('display_errors', 1);
} elseif (DEBUG_MODE && DEBUG_MODE == 2) {
    error_reporting(E_ALL);
    @ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL);
    @ini_set('display_errors', 0);
}
require_once(ROOT_PATH . 'includes/cls_error_hannder.php');
$ErrorHandlers = new ErrorHandlers;
$ErrorHandlers->register(DEBUG_MODE, ROOT_PATH . 'temp/logs/');


date_default_timezone_set($timezone);

$php_self = isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];

if ('/' == substr($php_self, -1)) {
    $php_self .= 'index.php';
} 
define('PHP_SELF', $php_self);
//define('MODULE', $_module);

require(ROOT_PATH . 'includes/inc_constant.php');
require(ROOT_PATH . 'includes/cls_ecshop.php');
require(ROOT_PATH . 'includes/cls_error.php');
require(ROOT_PATH . 'includes/lib_time.php');
require(ROOT_PATH . 'includes/lib_base.php');
require(ROOT_PATH . 'includes/lib_common.php');
require(ROOT_PATH . 'includes/lib_main.php');
require(ROOT_PATH . 'includes/lib_insert.php');
require(ROOT_PATH . 'includes/lib_goods.php');
require(ROOT_PATH . 'includes/lib_article.php');
require(ROOT_PATH . 'includes/lib_ecshopvietnam.php');

if (!empty($_GET)) {
    $_GET  = addslashes_deep($_GET);
}
if (!empty($_POST)) {
    $_POST = addslashes_deep($_POST);
}

$_COOKIE   = addslashes_deep($_COOKIE);
$_REQUEST  = addslashes_deep($_REQUEST);




/* 创建 ECSHOP 对象 */
$ecs = new ECS($db_name, $prefix);
define('DATA_DIR', CDN_PATH . '/data');
define('IMAGE_DIR', CDN_PATH . '/images');


$ua = isset($_SERVER['HTTP_USER_AGENT']) ? strtolower($_SERVER['HTTP_USER_AGENT']) : '';
$uachar = "/(iphone|mobile|android|ios)/i";
$_is_mobile = preg_match($uachar, $ua);
$_client = isset($_params['client']) ? $_params['client'] : false;
$_redirect = $ecsvn_request['request_uri'];
$swich_mobile = $ecsvn_request['getUrl'] . '?';
$_device = 'desktop';
setcookie('WEB_VERSION', 'desktop', time() + (84000 * 30), $cookie_path, $cookie_domain, $cookie_secure, $cookie_http_only);


$_protocol = (@$_SERVER["HTTPS"] == "on") ? "https://" : "http://";
// if ($_SERVER['REMOTE_ADDR'] != '127.0.0.1' && (substr($_SERVER['HTTP_HOST'], 0, 4) == 'www.' || $_protocol == 'http://')) {
//     ecvn_withRedirect($base_path.$_url);
// }


/* Connect Database */
require(ROOT_PATH . 'includes/cls_mysql.php');
$db = new cls_mysql($db_host, $db_user, $db_pass, $db_name);
$db->set_disable_cache_tables(array($ecs->table('sessions'), $ecs->table('sessions_data'), $ecs->table('cart')));
$db_host = $db_user = $db_pass = $db_name = NULL;

/* message */
$err = new ecs_error('message.dwt');

/* Lấy toàn bộ cấu hình */
$_CFG = load_config();


/**
 * Chuyển sang giao diện Mobile khi nào ?
 * 1. Dùng thiết bị cho là Mobile
 * 2. Ở Desktop: WEB_VERSION = mobile,  not exits param client
 * 3. Ở Desktop: WEB_VERSION = desktop, param client = mobile
 */
if ($_is_mobile || (!$_is_mobile && !$_client && isset($_COOKIE['WEB_VERSION']) && $_COOKIE['WEB_VERSION'] === 'mobile') || (!$_is_mobile && isset($_COOKIE['WEB_VERSION']) && $_COOKIE['WEB_VERSION'] === 'desktop' && $_client === 'mobile')) {
    $_device = 'mobile';
    $_CFG['template'] = !empty($_CFG['template_mobile']) ? $_CFG['template_mobile'] : $_CFG['template'];
    setcookie('WEB_VERSION', 'mobile', time() + (84000 * 30), $cookie_path, $cookie_domain, $cookie_secure, $cookie_http_only);
}

/* 301 khi tồn tại $_client */
if ($_client) {
    /* Tạo url chuyển hướng */
    unset($_params['client']);
    if (count($_params) > 0) {
        $_redirect = $_redirect . '?' . http_build_query($_params);
        $swich_mobile = $swich_mobile . http_build_query($_params) . '&';
    }
    ecvn_withRedirect($_redirect);
}



/* Khởi tạo các thư mục lưu tạm
  Thay đổi khai báo biến ở hàm Clearcache để xóa được
 */
$_cache_dir     = ROOT_PATH . 'temp/caches/' . $_CFG['template'];
$_compile_dir   = ROOT_PATH . 'temp/compiled/' . $_CFG['template'];
$_static_caches = ROOT_PATH . 'temp/static_caches';
$_query_caches  = ROOT_PATH . 'temp/query_caches';
$_disc_dir      = CDN_PATH . '/disc/' . $_device;

if (!file_exists(ROOT_PATH . 'temp')) {
    @mkdir(ROOT_PATH . 'temp', 0777);
    @chmod(ROOT_PATH . 'temp', 0777);
}
if (!file_exists(ROOT_PATH . 'temp/compiled')) {
    @mkdir(ROOT_PATH . 'temp/compiled', 0777);
    @chmod(ROOT_PATH . 'temp/compiled', 0777);
}
if (!file_exists(ROOT_PATH . 'temp/caches')) {
    @mkdir(ROOT_PATH . 'temp/caches', 0777);
    @chmod(ROOT_PATH . 'temp/caches', 0777);
}

if (!file_exists(ROOT_PATH . CDN_PATH . '/common')) {
    @mkdir(ROOT_PATH . CDN_PATH . '/common', 0777);
    @chmod(ROOT_PATH . CDN_PATH . '/common', 0777);
}
if (!file_exists(ROOT_PATH . $_disc_dir)) {
    @mkdir(ROOT_PATH . $_disc_dir, 0777);
    @chmod(ROOT_PATH . $_disc_dir, 0777);
}
if (!file_exists($_cache_dir)) {
    @mkdir($_cache_dir, 0777);
    @chmod($_cache_dir, 0777);
}
if (!file_exists($_compile_dir)) {
    @mkdir($_compile_dir, 0777);
    @chmod($_compile_dir, 0777);
}
if (!file_exists($_static_caches)) {
    @mkdir($_static_caches, 0777);
    @chmod($_static_caches, 0777);
}
if (!file_exists($_query_caches)) {
    @mkdir($_query_caches, 0777);
    @chmod($_query_caches, 0777);
}

/* lang */
require(ROOT_PATH . 'languages/' . $_CFG['lang'] . '/common.php');
require(ROOT_PATH . 'themes/' . $_CFG['template'] . '/lang/' . $_CFG['lang'] . '/common.php');

if ($_CFG['shop_closed'] == 1) {
    /* 商店关闭了，输出关闭的消息 */
    header('Content-type: text/html; charset=' . EC_CHARSET);

    die('<div style="margin: 150px; text-align: center; font-size: 14px"><p>' . $_LANG['shop_closed'] . '</p><p>' . $_CFG['close_comment'] . '</p></div>');
}


if (is_spider()) {
    if (!defined('INIT_NO_USERS')) {
        define('INIT_NO_USERS', true);
    }
    $_SESSION = array();
    $_SESSION['user_id']     = 0;
    $_SESSION['user_name']   = '';
    $_SESSION['email']       = '';
    $_SESSION['user_rank']   = 0;
    $_SESSION['discount']    = 1.00;
    //addlog('init.txt', $str = 'is_spider');
}
/** Nếu không phải là Robot thì khởi tạo Session */
if (!defined('INIT_NO_USERS')) {
    /* session */
    include(ROOT_PATH . 'includes/cls_session.php');
    $sess = new cls_session($db, $ecs->table('sessions'), $ecs->table('sessions_data'));
    define('SESS_ID', $sess->get_session_id());

    //addlog('init.txt', $str = '!defined(INIT_NO_USERS)');
}
/* Fix Robot */ else {
    define('SESS_ID', mt_rand());
}

if (isset($_SERVER['PHP_SELF'])) {
    $_SERVER['PHP_SELF'] = htmlspecialchars($_SERVER['PHP_SELF']);
}
if (!defined('INIT_NO_SMARTY')) {
    header('Cache-control: private');
    header('Content-type: text/html; charset=' . EC_CHARSET);

    /* 创建 Smarty 对象。*/
    require(ROOT_PATH . 'includes/cls_template.php');
    $smarty = new cls_template;
    $smarty->cache_lifetime = $_CFG['cache_time'];
    $smarty->template_dir   = ROOT_PATH . 'themes/' . $_CFG['template'];
    $smarty->cache_dir      = $_cache_dir;
    $smarty->compile_dir    = $_compile_dir;
    $smarty->baseUrl        = rtrim($ecsvn_request['getBaseUrl'], '/');
    $smarty->cdn_path       = CDN_PATH . '/themes/' . $_CFG['template'];
    $smarty->common_path    = CDN_PATH . '/common/';
    $smarty->disc_dir       = $_disc_dir;
    $smarty->direct_output  = ((DEBUG_MODE & 2) == 2) ? true : false;
    $smarty->force_compile  = ((DEBUG_MODE & 2) == 2) ? true : false;
    $smarty->minify_html    = $minify_html;

    $smarty->assign('lang', $_LANG);
    $smarty->assign('ecs_charset', EC_CHARSET);
    $smarty->assign('base_path', $base_path);
    $smarty->assign('template_name', $_CFG['template']);
    $smarty->assign('cdn_path', $base_cdn);
    $smarty->assign('open_time', $open_time);

    $smarty->assign('swich_mobile', $swich_mobile);

    if (!empty($_CFG['stylename'])) {
        $smarty->assign('ecs_css_path', $ecsvn_request['getBaseUrl'] . '/themes/' . $_CFG['template'] . '/style_' . $_CFG['stylename'] . '.css');
    } else {
        $smarty->assign('ecs_css_path', $ecsvn_request['getBaseUrl'] . '/themes/' . $_CFG['template'] . '/style.css');
    }
}


if (!defined('INIT_NO_USERS')) {
    /* 会员信息 */
    $user = init_users(['cookie_domain' => $cookie_domain, 'cookie_path' => $cookie_path, 'cookie_secure' => $cookie_secure, 'cookie_http' => $cookie_http_only]);

    if (!isset($_SESSION['user_id'])) {
        /* 获取投放站点的名称 */
        $site_name = isset($_GET['from'])   ? htmlspecialchars($_GET['from']) : addslashes($_LANG['self_site']);
        $from_ad   = !empty($_GET['ad_id']) ? intval($_GET['ad_id']) : 0;

        $_SESSION['from_ad'] = $from_ad; // 用户点击的广告ID
        $_SESSION['referer'] = stripslashes($site_name); // 用户来源

        unset($site_name);

        if (!defined('INGORE_VISIT_STATS')) {
            visit_stats();
        }
    }

    if (empty($_SESSION['user_id'])) {
        if ($user->get_cookie()) {
            /* 如果会员已经登录并且还没有获得会员的帐户余额、积分以及优惠券 */
            if ($_SESSION['user_id'] > 0) {
                update_user_info();
            }
        } else {
            $_SESSION['user_id']     = 0;
            $_SESSION['user_name']   = '';
            $_SESSION['email']       = '';
            $_SESSION['user_rank']   = 0;
            $_SESSION['discount']    = 1.00;
            if (!isset($_SESSION['login_fail'])) {
                $_SESSION['login_fail'] = 0;
            }
        }
    }

    /* 设置推荐会员 */
    // if (isset($_GET['u']))
    // {
    //     set_affiliate();
    // }

    /* session 不存在，检查cookie */
    if (!empty($_COOKIE['ECS']['user_id']) && !empty($_COOKIE['ECS']['password'])) {
        // 找到了cookie, 验证cookie信息
        $sql = 'SELECT user_id, user_name, password ' .
            ' FROM ' . $ecs->table('users') .
            " WHERE user_id = '" . intval($_COOKIE['ECS']['user_id']) . "' AND password = '" . $_COOKIE['ECS']['password'] . "'";

        $row = $db->GetRow($sql);

        if (!$row) {
            // 没有找到这个记录
            $time = time() - 3600;
            setcookie("ECS[user_id]",  '', $time, '/', NULL, NULL, TRUE);
            setcookie("ECS[password]", '', $time, '/', NULL, NULL, TRUE);
        } else {
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['user_name'] = $row['user_name'];
            update_user_info();
        }
    }

    if (isset($smarty)) {
        $smarty->assign('ecs_session', $_SESSION);
    }
}


/* EcshopVietnam */
@include(ROOT_PATH . 'includes/siy_functions.php');
