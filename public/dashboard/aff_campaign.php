<?php
define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
require_once(ROOT_PATH . 'languages/' . $_CFG['lang'] . '/admin/campaign.php');
$smarty->assign('lang', $_LANG);
include_once(ROOT_PATH . 'includes/cls_image.php');

if ($_REQUEST['act'] == 'list') {
    $id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
    // Lấy dữ liệu article
    $sql_article = 'SELECT * FROM ' . $ecs->table('article') . ' WHERE article_id = ' . $id;
    $data = $db->GetRow($sql_article);
    $article_url = $ecs->url() . build_uri('article', array('aid' => $data['article_id']));

    $smarty->assign('id',           $id);
    $smarty->assign('article',      $data);
    $smarty->assign('article_url',  $article_url);
    $smarty->assign('username',     $_SESSION['admin_name']);

    $campaign_list = get_campaign_list($article_url);

    $smarty->assign('campaign_list',   $campaign_list['list']);
    $smarty->assign('filter',          $campaign_list['filter']);
    $smarty->assign('record_count',    $campaign_list['record_count']);
    $smarty->assign('page_count',      $campaign_list['page_count']);

    assign_query_info();
    $smarty->display('aff_campaign_list.htm');
}
elseif ($_REQUEST['act'] == 'query') {
    $id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
    // Lấy dữ liệu article
    $sql_article = 'SELECT * FROM ' . $ecs->table('article') . ' WHERE article_id = ' . $id;
    $data = $db->GetRow($sql_article);
    $article_url = $ecs->url() . build_uri('article', array('aid' => $data['article_id']));

    $smarty->assign('article_url',  $article_url);
    
    $campaign_list = get_campaign_list($article_url);

    $smarty->assign('id',               $campaign_list['filter']['id']);
    $smarty->assign('campaign_list',    $campaign_list['list']);
    $smarty->assign('filter',          $campaign_list['filter']);
    $smarty->assign('record_count',    $campaign_list['record_count']);
    $smarty->assign('page_count',      $campaign_list['page_count']);

    make_json_result(
        $smarty->fetch('aff_campaign_list.htm'),
        '',
        $campaign_list
    );
}

// Xử lý thêm chiến dịch
if ($_REQUEST['act'] == 'insert_campaign') {
    $campaign_name = $_REQUEST['campaign_name'];
    $article_id = (int) $_REQUEST['article_id'];
    $user_id = $_SESSION['admin_id'];
    $user_name = $_SESSION['admin_name'];
    $utm_medium = $_REQUEST['utm_medium'];

    $is_active = (isset($_REQUEST['is_active'])) ? (int) $_REQUEST['is_active'] : 0;

    if (
        empty($campaign_name) ||
        empty($article_id) ||
        empty($utm_medium)
    ) {
        echo 2;
        exit;
    }

    $sql_insert = "INSERT INTO " . $ecs->table('aff_campaigns') . "(campaign_name,article_id,user_id,user_name,utm_campaign,utm_source,utm_medium,is_active)
    VALUES ('$campaign_name','$article_id','$user_id','$user_name','','$user_name','$utm_medium','$is_active') ";
    $db->query($sql_insert);
    echo 1;
}

// Xử lý active chiến dịch
if ($_REQUEST['act']=="active_campaign") {
    $campaign_id = $_REQUEST['campaign_id'];

    if (isset($_REQUEST['is_active'])) {
        $is_active = $_REQUEST['is_active'];
    } else {
        $is_active = 0;
    }

    // Đổi trạng thái active yes=>no; no=>yes
    $is_active = ($is_active==1) ? 0 : 1 ;

    $sql_active = "UPDATE " . $ecs->table('aff_campaigns') . " SET is_active = '$is_active' WHERE campaign_id = '$campaign_id'";
    echo $db->query($sql_active) ? 1 : 0;
}

// Xử lý thêm số lần copy link
if($_REQUEST['act']=='add_number_copy'){
    $number_copy = $_REQUEST['number_copy'];
    $campaign_id = $_REQUEST['campaign_id'];

    $sql = "UPDATE " . $ecs->table('aff_campaigns') . " SET number_copy = " . intval($number_copy+1) . " WHERE campaign_id = " . $campaign_id;
    $db->query($sql);
    echo 1;
}

function build_article_url_utm($url, $params)
{
    $arrray_uri = ['utm_campaign','utm_source','utm_medium'];
    $uri = [];
    foreach ($arrray_uri as $i => $key) {
        if (isset($params[$key])) {
            $uri[] = $key . '=' . $params[$key];
        }
    }
    return $url . (!empty($uri) ? '?' : '') . implode('&', $uri);
}

// Get campaign list
function get_campaign_list($article_url)
{
    $result = get_filter();
    $id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
    $where = ' WHERE article_id = '. $id;

    if ($result === false) {
        $filter = array();

        // Xử lý sắp xếp
        if(!isset($_REQUEST['sort'])){            
            $_REQUEST['sort'] = "old";
            $filter['sort_by'] = "campaign_id";
            $filter['sort_order'] = "ASC";
        }else{
            if ($_REQUEST['sort']=="a-z") {
                $filter['sort_by'] = "campaign_name";
                $filter['sort_order'] = "ASC";
            } elseif ($_REQUEST['sort']=="z-a") {
                $filter['sort_by'] = "campaign_name";
                $filter['sort_order'] = "DESC";
            } elseif ($_REQUEST['sort']=="old") {
                $filter['sort_by'] = "campaign_id";
                $filter['sort_order'] = "ASC";
            } elseif ($_REQUEST['sort']=="new") {
                $filter['sort_by'] = "campaign_id";
                $filter['sort_order'] = "DESC";
            }
        }
        
        // Xử lý phần filter gửi đến
        $list_filter = ['user_name','campaign_id','utm_source','utm_medium'];
        foreach ($list_filter as $i => $key) {
            if (isset($_REQUEST[$key]) && $_REQUEST[$key]!="") {
                $where .= " AND " . $key . "= '" . $_REQUEST[$key]. "'";
                $filter[$key] = $_REQUEST[$key];
            }
        }

        if (isset($_REQUEST['date_start'])and $_REQUEST['date_start']!='' and isset($_REQUEST['date_end']) and $_REQUEST['date_end']!='') {
            $where .= " AND updated_at BETWEEN '" . $_REQUEST['date_start'] . " 00:00:00' AND '" . $_REQUEST['date_end'] ." 23:59:59'";
            $filter['date_start'] = $_REQUEST['date_start'];
            $filter['date_end'] = $_REQUEST['date_end'];
        }

        /* Đếm tổng số row kết hợp điều kiện để xử lý cho phân trang */
        $sql = 'SELECT COUNT(*) FROM ' .$GLOBALS['ecs']->table('aff_campaigns') . $where . ';';
        $filter['record_count'] = $GLOBALS['db']->getOne($sql);
        $filter = page_and_size($filter);

        /* Lấy danh sách có điều kiện */
        $sql  = 'SELECT *'.
               ' FROM ' .$GLOBALS['ecs']->table('aff_campaigns').
               $where .
                " ORDER BY " .  $filter["sort_by"] . " " .  $filter["sort_order"];
        set_filter($filter, $sql);
    } else {
        $sql    = $result['sql'];
        $filter = $result['filter'];
    }
    $res = $GLOBALS['db']->selectLimit($sql, $filter['page_size'], $filter['start']);

    $list = array();
    while ($rows = $GLOBALS['db']->fetchRow($res)) {
        $rows['share_link'] = build_article_url_utm($article_url, $rows);
        $list[] = $rows;
    }

    $filter['id'] = $id;

    return array(
        'list' => $list,
        'filter' => $filter,
        'page_count' => $filter['page_count'],
        'record_count' => $filter['record_count']
    );
}
