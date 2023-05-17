<?php
    $cat_id = isset($_REQUEST['cat_id']) ? intval($_REQUEST['cat_id']) : 0;
    $size = isset($_REQUEST['size']) ? intval($_REQUEST['size']) : 4;
    $slug = isset($_REQUEST['slug']) ? $_REQUEST['slug'] : '';

    $smarty->caching = false;
    include_once(ROOT_PATH.'includes/cls_json.php');
    $json  = new JSON;
    $result = array('error' => 1, 'message' => 'Có lỗi, vui lòng kiểm tra lại yêu cầu', 'content' => '');
    /* Load sản phẩm cho danh mục con */
    if($cat_id > 0){

        $goods_list = get_goods_byID($cat_id,$size);
        $smarty->assign('goods_list', $goods_list);
        $smarty->assign('slug', $slug);

        $lib = 'library/goods_list_child_ajax.lbi';
        $result['content'] =  $smarty->fetch($lib);
        $result['error'] = 0; $result['message'] = 'Thành công';
    }
    die($json->encode($result));
 ?>

