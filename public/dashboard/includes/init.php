<?php
if (!defined('IN_ECS')) {
    die('Hacking attempt');
}
ob_start();
include_once dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__, 3));
$dotenv->safeLoad();

require_once(dirname(__DIR__, 2) . '/includes/safety.php');

/* https 检测https */
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
    define('FORCE_SSL_LOGIN', true);
    define('FORCE_SSL_ADMIN', true);
} else {
    if (isset($_SERVER['HTTP_ORIGIN']) && substr($_SERVER['HTTP_ORIGIN'], 0, 5) == 'https') {
        $_SERVER['HTTPS'] = 'on';
        define('FORCE_SSL_LOGIN', true);
        define('FORCE_SSL_ADMIN', true);
    }
}

/* https 登陆失败 */

define('ECS_ADMIN', true);

error_reporting(E_ALL);

if (__FILE__ == '') {
    die('Fatal error code: 0');
}

/* 初始化设置 */
@ini_set('memory_limit',          '64M');
@ini_set('session.cache_expire',  180);
@ini_set('session.use_trans_sid', 0);
@ini_set('session.use_cookies',   1);
@ini_set('session.auto_start',    0);


if (DIRECTORY_SEPARATOR == '\\') {
    @ini_set('include_path',      '.;' . ROOT_PATH);
} else {
    @ini_set('include_path',      '.:' . ROOT_PATH);
}


if (!defined('DEBUG_MODE')) {
    define('DEBUG_MODE', 0);
}

include(dirname(__FILE__,3) . '/includes/config.php');


define('ROOT_PATH', str_replace(ADMIN_PATH . '/includes/init.php', '', str_replace('\\', '/', __FILE__)));


/** Debug Mode */
if (DEBUG_MODE && DEBUG_MODE == 8) {
    error_reporting(E_ALL);
    @ini_set('display_errors', 1);
} elseif (DEBUG_MODE && DEBUG_MODE == 2) {
    error_reporting(E_ALL);
    @ini_set('display_errors', 1);
    require_once(ROOT_PATH . 'includes/cls_error_hannder.php');
    $ErrorHandlers = new ErrorHandlers;
    $ErrorHandlers->register(DEBUG_MODE, ROOT_PATH . 'temp/logs/');
} elseif (DEBUG_MODE && DEBUG_MODE == 4) {
    error_reporting(E_ALL);
    @ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL);
    @ini_set('display_errors', 0);
}


if (!empty($timezone)) {
    date_default_timezone_set($timezone);
}

if (isset($_SERVER['PHP_SELF'])) {
    define('PHP_SELF', $_SERVER['PHP_SELF']);
} else {
    define('PHP_SELF', $_SERVER['SCRIPT_NAME']);
}
require(ROOT_PATH . 'includes/inc_constant.php');
require(ROOT_PATH . 'includes/cls_ecshop.php');
require(ROOT_PATH . 'includes/cls_error.php');
require(ROOT_PATH . 'includes/lib_time.php');
require(ROOT_PATH . 'includes/lib_base.php');
require(ROOT_PATH . 'includes/lib_common.php');
require(ROOT_PATH . ADMIN_PATH . '/includes/lib_main.php');
require(ROOT_PATH . ADMIN_PATH . '/includes/cls_exchange.php');
/* add by Ecshopvietnam */
require(ROOT_PATH . 'includes/lib_requests.php');
require(ROOT_PATH . 'includes/lib_ecshopvietnam.php');

/* 对用户传入的变量进行转义操作。*/
// if (!get_magic_quotes_gpc())
// {
if (!empty($_GET)) {
    $_GET  = addslashes_deep($_GET);
}
if (!empty($_POST)) {
    $_POST = addslashes_deep($_POST);
}

$_COOKIE   = addslashes_deep($_COOKIE);
$_REQUEST  = addslashes_deep($_REQUEST);
//}

/* 对路径进行安全处理 */
if (strpos(PHP_SELF, '.php/') !== false) {
    ecs_header("Location:" . substr(PHP_SELF, 0, strpos(PHP_SELF, '.php/') + 4) . "\n");
    exit();
}

/* 创建 ECSHOP 对象 */
$ecs = new ECS($db_name, $prefix);
define('DATA_DIR', CDN_PATH . '/data');
define('IMAGE_DIR', CDN_PATH . '/images');

/* 初始化数据库类 */
require(ROOT_PATH . 'includes/cls_mysql.php');
$db = new cls_mysql($db_host, $db_user, $db_pass, $db_name);
$db_host = $db_user = $db_pass = $db_name = NULL;

/* 创建错误处理对象 */
$err = new ecs_error('message.htm');

/* 初始化session */
require(ROOT_PATH . 'includes/cls_session.php');
$sess = new cls_session($db, $ecs->table('sessions'), $ecs->table('sessions_data'), 'ECSCP_ID');

/* 初始化 action */
if (!isset($_REQUEST['act'])) {
    $_REQUEST['act'] = '';
} elseif (($_REQUEST['act'] == 'login' || $_REQUEST['act'] == 'logout' || $_REQUEST['act'] == 'signin') &&
    strpos(PHP_SELF, '/privilege.php') === false
) {
    $_REQUEST['act'] = '';
} elseif (($_REQUEST['act'] == 'forget_pwd' || $_REQUEST['act'] == 'reset_pwd' || $_REQUEST['act'] == 'get_pwd') &&
    strpos(PHP_SELF, '/get_password.php') === false
) {
    $_REQUEST['act'] = '';
}

/* 载入系统参数 */
$_CFG = load_config();


// TODO : 登录部分准备拿出去做，到时候把以下操作一起挪过去
if ($_REQUEST['act'] == 'captcha') {
    include(ROOT_PATH . 'includes/cls_captcha.php');
    $_captcha = new captcha(ROOT_PATH . DATA_DIR . '/captcha/', 104, 36);
    @ob_end_clean();
    $_captcha->generate_image();
    exit;
}

require(ROOT_PATH . 'languages/' . $_CFG['lang'] . '/admin/common.php');
require(ROOT_PATH . 'languages/' . $_CFG['lang'] . '/admin/log_action.php');

if (file_exists(ROOT_PATH . 'languages/' . $_CFG['lang'] . '/admin/' . basename(PHP_SELF))) {
    include(ROOT_PATH . 'languages/' . $_CFG['lang'] . '/admin/' . basename(PHP_SELF));
}

/* config by themes for admin - ecshopvietnam */
if (file_exists(ROOT_PATH . 'themes/' . $_CFG['template'] . '/options_admin.php')) {
    require_once(ROOT_PATH . 'themes/' . $_CFG['template'] . '/options_admin.php');
}


if (!file_exists(ROOT_PATH . 'temp/backup')) {
    @mkdir(ROOT_PATH . 'temp/backup', 0777);
    @chmod(ROOT_PATH . 'temp/backup', 0777);
}
if (!file_exists(ROOT_PATH . 'temp/caches')) {
    @mkdir(ROOT_PATH . 'temp/caches', 0777);
    @chmod(ROOT_PATH . 'temp/caches', 0777);
}

if (!file_exists(ROOT_PATH . 'temp/compiled/admin')) {
    @mkdir(ROOT_PATH . 'temp/compiled/admin', 0777);
    @chmod(ROOT_PATH . 'temp/compiled/admin', 0777);
}

if (!file_exists(ROOT_PATH . 'temp/compiled/admin_mobile')) {
    @mkdir(ROOT_PATH . 'temp/compiled/admin_mobile', 0777);
    @chmod(ROOT_PATH . 'temp/compiled/admin_mobile', 0777);
}


clearstatcache();

/* 如果有新版本，升级 */
if (!isset($_CFG['ecs_version'])) {
    $_CFG['ecs_version'] = 'v2.0.5';
}


$ecsvn_request = ecsvn_getRequest();
$_url = $ecsvn_request['getUrl'];
$QueryParams = ecsvn_getQueryParams($ecsvn_request['getQuery']);
$_params = $QueryParams['params'];

$ua = isset($_SERVER['HTTP_USER_AGENT']) ? strtolower($_SERVER['HTTP_USER_AGENT']) : '';
$uachar = "/(iphone|mobile|android|ios)/i";
$_is_mobile = preg_match($uachar, $ua);
$_client = isset($_params['client']) ? $_params['client'] : false;
$_redirect = $ecsvn_request['request_uri'];
$swich_mobile = $ecsvn_request['getUrl'] . '?';
$_device = '';
setcookie('ADMIN_VERSION', 'desktop', time() + (84000 * 30), '/' . ADMIN_PATH . '/', $cookie_domain, $cookie_secure, $cookie_http_only);


/**
 * Chuyển sang giao diện Mobile khi nào ?
 * 1. Dùng thiết bị cho là Mobile
 * 2. Ở Desktop: ADMIN_VERSION = mobile,  not exits param client
 * 3. Ở Desktop: ADMIN_VERSION = desktop, param client = mobile
 */
if ($_is_mobile || (!$_is_mobile && !$_client && isset($_COOKIE['ADMIN_VERSION']) && $_COOKIE['ADMIN_VERSION'] === 'mobile') || (!$_is_mobile && isset($_COOKIE['ADMIN_VERSION']) && $_COOKIE['ADMIN_VERSION'] === 'desktop' && $_client === 'mobile')) {
    $_device = 'mobile';
    setcookie('ADMIN_VERSION', 'mobile', time() + (84000 * 30), '/' . ADMIN_PATH . '/', $cookie_domain, $cookie_secure, $cookie_http_only);
}

/* 创建 Smarty 对象。*/
require(ROOT_PATH . 'includes/cls_template.php');
$smarty = new cls_template;


if (isset($_device) && $_device == 'mobile') {
    $admin_template = 'templates_mobile';
    $admin_compile_dir = 'admin_mobile';
} else {
    $admin_template = 'templates';
    $admin_compile_dir = 'admin';
}


$smarty->template_dir  = ROOT_PATH . ADMIN_PATH . '/' . $admin_template;
$smarty->compile_dir   = ROOT_PATH . 'temp/compiled/' . $admin_compile_dir; 
$ecsvn_request = ecsvn_getRequest();
$smarty->baseUrl        = $ecsvn_request['getBaseUrl'];
$smarty->force_compile = ((DEBUG_MODE & 2) == 2) ? true : false;

$smarty->assign('base_path', $ecsvn_request['getBaseUrl']);
$smarty->assign('domain', str_replace('/' . ADMIN_PATH, '', $ecsvn_request['getBaseUrl']));
$smarty->assign('cdn_path', $base_cdn);
$smarty->assign('lang', $_LANG);
$smarty->assign('help_open', $_CFG['help_open']);
$smarty->assign('is_device', $_device);



if (isset($_CFG['enable_order_check']))  // 为了从旧版本顺利升级到2.5.0
{
    $smarty->assign('enable_order_check', $_CFG['enable_order_check']);
} else {
    $smarty->assign('enable_order_check', 0);
}
/* 验证管理员身份 */
if ((!isset($_SESSION['admin_id']) || intval($_SESSION['admin_id']) <= 0) &&
    $_REQUEST['act'] != 'login' && $_REQUEST['act'] != 'signin' &&
    $_REQUEST['act'] != 'forget_pwd' && $_REQUEST['act'] != 'reset_pwd' && $_REQUEST['act'] != 'check_order' && $_REQUEST['act'] != 'yq_login' && $_REQUEST['act'] != 'is_yunqi_admin' && $_REQUEST['act'] != 'get_certificate'
) {
    /* session 不存在，检查cookie */
    if (!empty($_COOKIE['ECSCP']['admin_id']) && !empty($_COOKIE['ECSCP']['admin_pass'])) {
        // 找到了cookie, 验证cookie信息
        $sql = 'SELECT user_id, user_name, password, add_time, action_list, last_login ' .
            ' FROM ' . $ecs->table('admin_user') .
            " WHERE user_id = '" . intval($_COOKIE['ECSCP']['admin_id']) . "'";
        $row = $db->GetRow($sql);

        if (!$row) {
            // 没有找到这个记录
            setcookie($_COOKIE['ECSCP']['admin_id'],   '', 1, NULL, NULL, NULL, TRUE);
            setcookie($_COOKIE['ECSCP']['admin_pass'], '', 1, NULL, NULL, NULL, TRUE);

            if (!empty($_REQUEST['is_ajax'])) {
                make_json_error($_LANG['priv_error']);
            } else {
                ecs_header("Location: privilege.php?act=login\n");
            }

            exit;
        } else {
            // 检查密码是否正确
            if (md5($row['password'] . $_CFG['hash_code'] . $row['add_time']) == $_COOKIE['ECSCP']['admin_pass']) {
                !isset($row['last_time']) && $row['last_time'] = '';
                set_admin_session($row['user_id'], $row['user_name'], $row['action_list'], $row['last_time']);

                // 更新最后登录时间和IP
                $db->query('UPDATE ' . $ecs->table('admin_user') .
                    " SET last_login = '" . gmtime() . "', last_ip = '" . real_ip() . "'" .
                    " WHERE user_id = '" . $_SESSION['admin_id'] . "'");
            } else {
                setcookie($_COOKIE['ECSCP']['admin_id'],   '', 1, NULL, NULL, NULL, TRUE);
                setcookie($_COOKIE['ECSCP']['admin_pass'], '', 1, NULL, NULL, NULL, TRUE);

                if (!empty($_REQUEST['is_ajax'])) {
                    make_json_error($_LANG['priv_error']);
                } else {
                    ecs_header("Location: privilege.php?act=login\n");
                }

                exit;
            }
        }
    } else {
        if (!empty($_REQUEST['is_ajax'])) {
            make_json_error($_LANG['priv_error']);
        } else {
            ecs_header("Location: privilege.php?act=login\n");
        }

        exit;
    }
}

$smarty->assign('token', $_CFG['token']);

if (
    $_REQUEST['act'] != 'login' && $_REQUEST['act'] != 'signin' &&
    $_REQUEST['act'] != 'forget_pwd' && $_REQUEST['act'] != 'reset_pwd' && $_REQUEST['act'] != 'check_order'
) {
    $admin_path = preg_replace('/:\d+/', '', $ecs->url()) . ADMIN_PATH;
    if (
        !empty($_SERVER['HTTP_REFERER']) &&
        strpos(preg_replace('/:\d+/', '', $_SERVER['HTTP_REFERER']), $admin_path) === false
    ) {
        if (!empty($_REQUEST['is_ajax'])) {
            make_json_error($_LANG['priv_error']);
        } else {
            ecs_header("Location: privilege.php?act=login\n");
        }

        exit;
    }
}



//header('Cache-control: private');
header('content-type: text/html; charset=' . EC_CHARSET);
header('Expires: Fri, 14 Mar 1980 20:53:00 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');

if ((DEBUG_MODE & 1) == 1) {
    error_reporting(E_ALL);
} else {
    error_reporting(E_ALL ^ E_NOTICE);
}
if ((DEBUG_MODE & 4) == 4) {
    include(ROOT_PATH . 'includes/lib.debug.php');
}

/* 判断是否支持gzip模式 */


/* 云起认证 */
// include_once(ROOT_PATH."includes/cls_certificate.php");
// $cert = new certificate();
// $certificate = $cert->get_shop_certificate();
// if(!$certificate['certificate_id']){
//     $callback = $ecs->url()."admin/certificate.php?act=get_certificate&type=index";
//     $iframe_url = $cert->get_authorize_url($callback);
//     $smarty->assign('iframe_url',$iframe_url);
// }
$smarty->assign('certi', '');
/* 云起认证 */

/* Menu, Navigation Global */

if (empty($_REQUEST['is_ajax']) && $_device == 'mobile') {
    include_once(ROOT_PATH . ADMIN_PATH  . '/includes/inc_menu.php');
    include_once(ROOT_PATH . ADMIN_PATH  . '/includes/inc_icon.php');
    include_once(ROOT_PATH . ADMIN_PATH  . '/includes/inc_priv.php');

    foreach ($modules as $key => $value) {
        ksort($modules[$key]);
    }
    ksort($modules);
    foreach ($modules as $key => $val) {
        $menus[$key]['label'] = $_LANG[$key];
        $menus[$key]['icon'] = $icon[$key];
        if (is_array($val)) {
            foreach ($val as $k => $v) {
                if (isset($purview[$k])) {
                    if (is_array($purview[$k])) {
                        $boole = false;
                        foreach ($purview[$k] as $action) {
                            $boole = $boole || admin_priv($action, '', false);
                        }
                        if (!$boole) {
                            continue;
                        }
                    } else {
                        if (!admin_priv($purview[$k], '', false)) {
                            continue;
                        }
                    }
                }
                if ($k == 'ucenter_setup' && $_CFG['integrate_code'] != 'ucenter') {
                    continue;
                }
                $menus[$key]['children'][$k]['label']  = $_LANG[$k];
                $menus[$key]['children'][$k]['action'] = $v;
            }
            $menus[$key]['url'] = empty($menus[$key]['children']) ? '' : $menus[$key]['children'][key($menus[$key]['children'])]['action'];
            $menus[$key]['key'] = empty($menus[$key]['children']) ? '' : key($menus[$key]['children']);
        } else {
            $menus[$key]['action'] = $val;
        }

        // 如果children的子元素长度为0则删除该组
        if (empty($menus[$key]['children'])) {
            unset($menus[$key]);
        }
    }

    $smarty->assign('admin_id', $_SESSION['admin_id']);
    $smarty->assign('admin_name', $_SESSION['admin_name']);
    $smarty->assign('menus',     $menus);
}
