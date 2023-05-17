<?php
/**
 * Tối Ưu SEO cho thuộc tính lọc
 */
define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');

/* act操作项的初始化 */
$_REQUEST['act'] = trim($_REQUEST['act']);

if (empty($_REQUEST['act']))
{
    $_REQUEST['act'] = 'list';
}

/*------------------------------------------------------ */
//-- 属性列表
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'list')
{

    $smarty->assign('ur_here',      'Danh sách URL Của Thuộc Tính Lọc');
    $smarty->assign('action_link',      array());
    $smarty->assign('full_page',        1);
    $smarty->assign('goods_type_list',  goods_type_list($goods_type)); // 取得商品类型

    $list = get_attr_seo_list();

    $smarty->assign('attr_list',    $list['item']);
    $smarty->assign('filter',       $list['filter']);
    $smarty->assign('record_count', $list['record_count']);
    $smarty->assign('page_count',   $list['page_count']);

    $sort_flag  = sort_flag($list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    /* 显示模板 */
    assign_query_info();
    $smarty->display('attribute_seo_list.htm');
}

/*------------------------------------------------------ */
//-- 排序、翻页
/*------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'query')
{
    $list = get_attr_seo_list();

    $smarty->assign('attr_list',    $list['item']);
    $smarty->assign('filter',       $list['filter']);
    $smarty->assign('record_count', $list['record_count']);
    $smarty->assign('page_count',   $list['page_count']);

    $sort_flag  = sort_flag($list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    make_json_result($smarty->fetch('attribute_seo_list.htm'), '',
        array('filter' => $list['filter'], 'page_count' => $list['page_count']));
}
/* Chỉnh sửa URL SEO cho thuộc tính lọc */
elseif ($_REQUEST['act'] == 'edit_url')
{
    $slug    = isset($_GET['slug'])    ? filter_var($_GET['slug'],FILTER_SANITIZE_STRING) : '';
    $attr_id = isset($_GET['attr_id']) ? intval($_GET['attr_id']) : 0;
    $goods_attr_id = isset($_GET['gaid']) ? intval($_GET['gaid']) : 0;

    /* Check đầu vào */
    if($goods_attr_id == 0){
        sys_msg("Yêu cầu không hợp lệ.", 0, array(), false);
    }

    /* Lấy thông tin */
    $sql = "SELECT g.slug, g.goods_attr_id, g.sort_filter, g.attr_id, g.attr_value, a.attr_name, t.cat_name " .
            " FROM" . $GLOBALS['ecs']->table('goods_attr') . " AS g ".
            " LEFT JOIN  " . $GLOBALS['ecs']->table('attribute') . " AS a ON g.attr_id =  a.attr_id ".
            " LEFT JOIN " . $GLOBALS['ecs']->table('goods_type') . " AS t ON a.cat_id = t.cat_id " .
            " WHERE a.attr_type = 3 AND g.goods_attr_id =  $goods_attr_id";
    $goods_attr = $db->getRow($sql);

    $smarty->assign('goods_attr',     $goods_attr);

    $smarty->assign('ur_here',   'Chỉnh sửa URL');
    $smarty->assign('action_link', array('text' => 'Danh sách URL', 'href' => 'attribute_seo.php?act=list'));
    $smarty->assign('form_act', 'update_url');

    assign_query_info();
    $smarty->display('attribute_seo_url.htm');

}
elseif ($_REQUEST['act'] == 'update_url')
{
    $slug     = isset($_POST['slug'])    ? filter_var(trim($_POST['slug']),FILTER_SANITIZE_STRING) : '';
    $old_slug = isset($_POST['old_slug'])    ? $_POST['old_slug'] : '';
    $attr_id  = isset($_POST['attr_id']) ? intval($_POST['attr_id']) : 0;
    $sort_filter  = isset($_POST['sort']) ? intval($_POST['sort']) : 1;

    /* Check đầu vào */
    if($attr_id == 0 || empty($slug)){
        sys_msg("Dữ liệu xử lí không hợp lệ.", 0, array(), false);
    }
    /* Cập nhật khi có sự thay đổi */
    $update = '';
    if($slug != $old_slug){
        $update = " , slug = '".$slug."'";
    }

    $db->query("UPDATE ".$GLOBALS['ecs']->table('goods_attr')." SET sort_filter = '".$sort_filter."' $update  WHERE attr_id = $attr_id AND slug = '".$old_slug."'");
    clear_cache_files();
    $note = 'Xử lí cập nhật thành công !';

    $link[] = array('text' => 'Danh sách URL', 'href' => 'attribute_seo.php?act=list');
    sys_msg($note, 0, $link);

}
/* END Chỉnh sửa URL SEO cho thuộc tính lọc */

/* Chỉnh sửa URL SEO cho thuộc tính lọc */
elseif ($_REQUEST['act'] == 'edit_meta')
{
    $goods_attr_id = isset($_GET['gaid']) ? intval($_GET['gaid']) : 0;

    /* Check đầu vào */
    if($goods_attr_id == 0){
        sys_msg("Yêu cầu không hợp lệ.", 0, array(), false);
    }

    /* Lấy thông tin */
    $sql = "SELECT g.slug, g.goods_attr_id, g.attribute_seo_id, g.attr_id, g.attr_value, a.attr_name, t.cat_name, t.cat_id " .
            " FROM" . $GLOBALS['ecs']->table('goods_attr') . " AS g ".
            " LEFT JOIN  " . $GLOBALS['ecs']->table('attribute') . " AS a ON g.attr_id =  a.attr_id ".
            " LEFT JOIN " . $GLOBALS['ecs']->table('goods_type') . " AS t ON a.cat_id = t.cat_id " .
            " WHERE a.attr_type = 3 AND g.goods_attr_id =  $goods_attr_id";
    $goods_attr = $db->getRow($sql);

    $smarty->assign('goods_attr', $goods_attr);

    /* lấy thông tin meta */
    $attr_meta = $db->getRow("SELECT * FROM ".$GLOBALS['ecs']->table('attribute_seo'). "WHERE id = $goods_attr[attribute_seo_id]");

    if($attr_meta){
        $smarty->assign('form_act', 'update_meta');
    }else{
        $smarty->assign('form_act', 'insert_meta');
        $attr_meta = array('id'=> 0, 'meta_title'=> '', 'meta_keywords'=> '','meta_desc'=> '', 'meta_robot'=>'INDEX,FOLLOW');
    }

    $smarty->assign('attr_meta',   $attr_meta);

    $smarty->assign('ur_here',   'Tạo Thẻ Meta cho Thuộc Tính');
    $smarty->assign('action_link', array('text' => 'Danh sách URL', 'href' => 'attribute_seo.php?act=list'));


    assign_query_info();
    $smarty->display('attribute_seo_meta.htm');

}

elseif ($_REQUEST['act'] == 'insert_meta' || $_REQUEST['act'] == 'update_meta')
{

    $id  = intval($_POST['id']);
    $attr_id  = isset($_POST['attr_id']) ? intval($_POST['attr_id']) : 0;
    $cat_id  = isset($_POST['cat_id']) ? intval($_POST['cat_id']) : 0;

    $meta = array(
        'meta_title'    => isset($_POST['meta_title'])    ? filter_var(trim($_POST['meta_title']),FILTER_SANITIZE_STRING) : '',
        'meta_keywords' => isset($_POST['meta_keywords']) ? filter_var(trim($_POST['meta_keywords']),FILTER_SANITIZE_STRING) : '',
        'meta_desc'     => isset($_POST['meta_desc'])     ? filter_var(trim($_POST['meta_desc']),FILTER_SANITIZE_STRING) : '',
        'meta_robot'    => isset($_POST['meta_robot'])    ? filter_var($_POST['meta_robot'],FILTER_SANITIZE_STRING) : 'INDEX,FOLLOW',
    );

    /* Check đầu vào */
    if(empty($attr_id)){
        sys_msg("Dữ liệu xử lí không hợp lệ.", 0, array(), false);
    }
    /* Nếu cả 3 trống thì ko làm gì cả */
    if(empty($meta['meta_title']) && empty($meta['meta_keywords']) && empty($meta['meta_desc'])){
        sys_msg("Tất cả dữ liệu đều để trống.", 0, array(), false);
    }

    $is_add = $_REQUEST['act'] == 'insert_meta';


    if ($is_add)
    {
        $meta['cat_id'] = $cat_id;
        $meta['attr_id'] = $attr_id;
        $meta['slug'] = $_POST['slug'];
        $db->autoExecute($ecs->table('attribute_seo'), $meta, 'INSERT');
        $attribute_seo_id = $db->insert_id();

        $db->query("UPDATE ".$ecs->table('goods_attr')." SET attribute_seo_id = $attribute_seo_id WHERE attr_id = $attr_id AND slug = '".$_POST['slug']."'");

        $note = 'Thêm Meta thành công !';
        //admin_log($meta['meta_title'], 'add', 'attribute_seo');
    }
    else
    {
        $db->autoExecute($ecs->table('attribute_seo'), $meta, 'UPDATE', "id = $id");
        $note = 'Cập nhật Meta thành công !';
        //admin_log($meta['meta_title'], 'edit', 'attribute_seo');
    }

    clear_cache_files();
    $link[] = array('text' => 'Danh sách URL', 'href' => 'attribute_seo.php?act=list');
    sys_msg($note, 0, $link);

}

/*------------------------------------------------------ */
//-- PRIVATE FUNCTIONS
/*------------------------------------------------------ */

/**
 * Lấy danh sách URL SEO của thuộc tính lọc, loại trừ trùng lặp
 *
 * @return  array
 */

function get_attr_seo_list()
{
    $result = get_filter();
    if ($result === false)
    {
        $filter = array();
        $filter['goods_type'] = empty($_REQUEST['goods_type']) ? 0 : intval($_REQUEST['goods_type']);
        $filter['sort_by']    = empty($_REQUEST['sort_by']) ? ' a.cat_id, g.attr_id ' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

        $where = "";

        if(!empty($filter['goods_type'])){
            $where = " AND a.cat_id = '$filter[goods_type]' ";
        }

        $sql = "SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('goods_attr') . " AS g ".
                " LEFT JOIN  " . $GLOBALS['ecs']->table('attribute') . " AS a ON g.attr_id =  a.attr_id ".
                " LEFT JOIN " . $GLOBALS['ecs']->table('goods_type') . " AS t ON a.cat_id = t.cat_id " .
                " WHERE a.attr_type = 3 ". $where." GROUP BY g.slug ";

        $filter['record_count'] = $GLOBALS['db']->getOne($sql);
        $filter = page_and_size($filter);

        $sql = "SELECT g.slug, g.goods_attr_id, g.attr_id, g.attr_value, a.attr_name, a.slug AS aslug, t.cat_name " .
            " FROM" . $GLOBALS['ecs']->table('goods_attr') . " AS g ".
            " LEFT JOIN  " . $GLOBALS['ecs']->table('attribute') . " AS a ON g.attr_id =  a.attr_id ".
            " LEFT JOIN " . $GLOBALS['ecs']->table('goods_type') . " AS t ON a.cat_id = t.cat_id " .
            " WHERE a.attr_type = 3 ". $where.
            " GROUP BY g.slug ".
            " ORDER BY $filter[sort_by] $filter[sort_order]";

        set_filter($filter, $sql);
    }
    else
    {
        $sql    = $result['sql'];
        $filter = $result['filter'];
    }
    $res = $GLOBALS['db']->selectLimit($sql, $filter['page_size'], $filter['start']);

    $arr = array();
    while ($rows = $GLOBALS['db']->fetchRow($res))
    {
        $arr[] = $rows;
    }

    return array('item' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
}
?>
