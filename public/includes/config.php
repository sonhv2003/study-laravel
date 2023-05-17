<?php
if (!defined('IN_ECS')) {
    die('Hacking attempt');
}

include_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createUnsafeMutable(dirname(__DIR__, 2));
$dotenv->safeLoad();

// database host
$db_host   = getenv('DB_HOST','localhost') . ":" . getenv('DB_PORT',3306);
// database name
$db_name   = getenv('DB_DATABASE','ec_blog');
// database username
$db_user   = getenv('DB_USERNAME','root');
// database password
$db_pass   = getenv('DB_PASSWORD','password');
// Time work
$open_time   = "8:00-20:00";
// table prefix
$prefix    = getenv('DB_PREFIX','ecsvn_');
$timezone    = getenv('TIMEZONE','Asia/Ho_Chi_Minh');
$cookie_path    = "/";
$cookie_domain    = getenv('HTTP_HOST','localhost');
$cookie_secure = (bool) getenv('HTTPS');
$cookie_http_only = true;
$session = "1440";
$base_cdn = getenv('CDN_DOMAIN','localhost/cdn');
$base_path = getenv('APP_URL','localhost');
/* on/off cache for dev */
$ecsvn_iscached = (bool) getenv('CACHE_ENABLE', false);
$minify_html = (bool) getenv('MINIFY_HTML', false);
/* allow index on Google Search */
$ecsvn_index_follow = true;

/**
 * cấu hình chuyển hướng tìm kiếm từ khóa
 */

$ai_keywords = [];

define('EC_CHARSET', 'utf-8');
define('CDN_PATH', 'cdn');
define('ADMIN_PATH', 'dashboard');
define('ADMIN_PATH_APP', 'app');
define('AUTH_KEY', 'this is a key');
define('OLD_AUTH_KEY', '');
define('API_TIME', '');
define('STORE_KEY', 'c3227bfbd16a1d7ec30fd23cda74771a');

/**
 * SMS Token API
 */
define('SMS_TOKEN', 'HpGrx_c51jmPn7KtdtfyH5RD23mfsqWQdsds');
