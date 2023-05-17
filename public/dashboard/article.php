<?php  
define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
require_once(ROOT_PATH . 'includes/cls_image.php');
require_once(ROOT_PATH . 'languages/' . $_CFG['lang'] . '/admin/campaign.php');
$smarty->assign('lang', $_LANG);

/*初始化数据交换对象 */
$exc   = new exchange($ecs->table("article"), $db, 'article_id', 'title');
//$image = new cls_image();

/* 允许上传的文件类型 */
$allow_file_types = '|WEBP|GIF|JPG|PNG|BMP|SWF|DOC|XLS|PPT|MID|WAV|ZIP|RAR|PDF|CHM|RM|TXT|';
$allow_image_types = array("gif", "jpg", "jpeg", "png", "bmp", "webp");

/*------------------------------------------------------ */
//-- 文章列表
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'list') {
    /* 取得过滤条件 */
    $filter = array();
    $smarty->assign('cat_select',  article_cat_list(0));
    $smarty->assign('ur_here',      $_LANG['03_article_list']);
    $smarty->assign('action_link',  array('text' => $_LANG['article_add'], 'href' => 'article.php?act=add'));
    $smarty->assign('full_page',    1);
    $smarty->assign('filter',       $filter);

    $article_list = get_articleslist();

    $smarty->assign('article_list',    $article_list['arr']);
    $smarty->assign('filter',          $article_list['filter']);
    $smarty->assign('record_count',    $article_list['record_count']);
    $smarty->assign('page_count',      $article_list['page_count']);

    $sort_flag  = sort_flag($article_list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    assign_query_info();
    $smarty->display('article_list.htm');
}

/*------------------------------------------------------ */
//-- 翻页，排序
/*------------------------------------------------------ */ elseif ($_REQUEST['act'] == 'query') {
    check_authz_json('article_manage');

    $article_list = get_articleslist();

    $smarty->assign('article_list',    $article_list['arr']);
    $smarty->assign('filter',          $article_list['filter']);
    $smarty->assign('record_count',    $article_list['record_count']);
    $smarty->assign('page_count',      $article_list['page_count']);

    $sort_flag  = sort_flag($article_list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    make_json_result(
        $smarty->fetch('article_list.htm'),
        '',
        array('filter' => $article_list['filter'], 'page_count' => $article_list['page_count'])
    );
}

/*------------------------------------------------------ */
//-- 添加文章
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'add') {
    /* 权限判断 */
    admin_priv('article_manage');

    /* 创建 html editor */
    CKeditor('content', '');

    /*初始化*/
    $article = array();
    $article['is_open'] = 1;
    $article['meta_robots'] = 'INDEX,FOLLOW';

    /* 取得分类、品牌 */  
    $smarty->assign('goods_cat_list', cat_list());
    $smarty->assign('brand_list',     get_brand_list());

    /* Sản phẩm liên quan đến tin tức */
    $link_goods_list = array();
    $sql = "DELETE FROM " . $ecs->table('link_goods') .
        " WHERE (goods_id = 0 OR link_goods_id = 0)" .
        " AND admin_id = '$_SESSION[admin_id]' AND module = 1";
    $db->query($sql);

    if (isset($_GET['id'])) {
        $smarty->assign('cur_id',  $_GET['id']);
    }
    $smarty->assign('article',     $article);
    $smarty->assign('cat_select',  article_cat_list(0));
    $smarty->assign('ur_here',     $_LANG['article_add']);
    $smarty->assign('action_link', array('text' => $_LANG['03_article_list'], 'href' => 'article.php?act=list'));
    $smarty->assign('form_action', 'insert');

    assign_query_info();
    $smarty->display('article_info.htm');
}

/*------------------------------------------------------ */
//-- 添加文章
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'insert') {
    /* 权限判断 */
    admin_priv('article_manage');

    $title = isset($_POST['title']) ? filter_var($_POST['title'], FILTER_SANITIZE_STRING) : '';
    $article_cat = isset($_POST['article_cat']) ? intval($_POST['article_cat']) : 0;
    /*Check Exist title */
    $is_only = $exc->is_only('title', $title, 0, " cat_id ='$article_cat'");

    if (!$is_only) {
        sys_msg(sprintf($_LANG['title_exist'], stripslashes($title)), 1);
    }
    /* edit by ecshopvietnam.com */
    $article_type = isset($_POST['article_type']) ? intval($_POST['article_type']) : 0;
    $author = isset($_POST['author']) ? filter_var($_POST['author'], FILTER_SANITIZE_STRING) : '';
    $author_email = isset($_POST['author_email']) ? filter_var($_POST['author_email'], FILTER_SANITIZE_EMAIL) : '';
    $keywords = isset($_POST['keywords']) ? filter_var($_POST['keywords'], FILTER_SANITIZE_STRING) : '';
    $description = isset($_POST['description']) ? filter_var($_POST['description'], FILTER_SANITIZE_STRING) : '';
    $link_url = isset($_POST['link_url']) ? filter_var($_POST['link_url'], FILTER_SANITIZE_URL) : '';
    $open_type =  isset($_POST['is_open']) ? intval($_POST['is_open']) : 0;
    $disable_comment = isset($_POST['disable_comment']) ? intval($_POST['disable_comment']) : 0;
    $add_time = time();
    $user_id = $_SESSION['admin_id'];
    $user_name = $_SESSION['admin_name'];
    $custom_title = isset($_POST['custom_title']) ? filter_var($_POST['custom_title'], FILTER_SANITIZE_STRING) : '';
    $meta_title = isset($_POST['meta_title']) ? filter_var($_POST['meta_title'], FILTER_SANITIZE_STRING) : '';
    $meta_desc = isset($_POST['meta_desc']) ? filter_var($_POST['meta_desc'], FILTER_SANITIZE_STRING) : '';
    $meta_robots = isset($_POST['meta_robots']) ? $_POST['meta_robots'] : 'INDEX,FOLLOW';
    $content = isset($_POST['content']) ? $_POST['content'] : '';
    $template_file = isset($_POST['template_file']) ? filter_var($_POST['template_file'], FILTER_SANITIZE_STRING) : '';

    $thumb = upload_thumb();


    /* xử lí luu tag */
    $tags =  filterTags($_POST['tags']);
    $keywords = !empty($keywords) ? $keywords : trim($tags, ","); // Lấy keywords = tag



    if (empty($_POST['cat_id'])) {
        $_POST['cat_id'] = 0;
    }
    $sql = "INSERT INTO " . $ecs->table('article') . "(title, tags, disable_comment, custom_title, template_file, meta_robots, meta_title, meta_desc, article_thumb, article_sthumb, article_mthumb, cat_id, article_type, is_open, author, " .
        "author_email, keywords, content, add_time, open_type, link, description, user_id, user_name) " .
        "VALUES ('$title', '$tags', '$disable_comment', '$custom_title', '$template_file', '$meta_robots', '$meta_title', '$meta_desc', '$thumb[thumb]', '$thumb[sthumb]', '$thumb[mthumb]', '$article_cat', '$article_type', '$is_open','$author', '$author_email', '$keywords', '$content', " .
        "'$add_time', '$open_type', '$link_url', '$description', '$user_id', '$user_name')";
    $db->query($sql);

    /* 处理关联商品 */
    $article_id = $db->insert_id();

    if ($article_id) {
        create_slug($article_id, 'article');
        $sql = "UPDATE " . $ecs->table('goods_article') . " SET article_id = '$article_id' WHERE article_id = 0";
        $db->query($sql);


        /**
         * Cập nhật article_id, cho các sp liên quan đến bài viết vừa đăng mới
         */
        handle_link_goods($article_id);



        /* insert Tag */
        if (!empty($tags)) {
            $arrTag = explode(',', $tags);
            foreach ($arrTag as $tag) {
                $tag = trim($tag);
                /* tìm xem tag đã tồn tại chưa */
                $tags_id = $db->getOne("SELECT id FROM " . $ecs->table('tags') . " WHERE name = '" . $tag . "'");
                /* Nếu chưa thì thêm vào danh sách tag */
                if (!$tags_id) {
                    $slug = build_slug($tag);
                    $db->query("INSERT INTO " . $ecs->table('tags') . " (name, slug) VALUES ('$tag', '$slug')");
                    $tags_id = $db->insert_id();
                }
                /* Tạo liên kết cho tag với id bài viết mới */
                $db->query("INSERT INTO " . $ecs->table('article_tags') . " (tags_id, article_id) VALUES ($tags_id, $article_id)");
            }
        }
        /* End xử lí tag */
    }

    $link[0]['text'] = $_LANG['continue_add'];
    $link[0]['href'] = 'article.php?act=add';

    $link[1]['text'] = $_LANG['back_list'];
    $link[1]['href'] = 'article.php?act=list';

    admin_log($_POST['title'], 'add', 'article');

    clear_cache_files();

    sys_msg($_LANG['articleadd_succeed'], 0, $link);
}

/*------------------------------------------------------ */
//-- 编辑
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'edit') {
    /* 权限判断 */
    admin_priv('article_manage');

    /* 取文章数据 */
    $sql = "SELECT * FROM " . $ecs->table('article') . " WHERE article_id='$_REQUEST[id]'";
    $article = $db->GetRow($sql);

    /* 创建 html editor */
    CKeditor('content', $article['content']);

    /* 取得分类、品牌 */
    $smarty->assign('goods_cat_list', cat_list());
    $smarty->assign('brand_list', get_brand_list());

    /* Sản phẩm liên quan tin tức */
    $link_goods_list    = get_linked_goods($_REQUEST['id'], 1);;
    $smarty->assign('link_goods_list', $link_goods_list);

    $smarty->assign('slug', get_slug($_REQUEST['id'], 'article'));

    $smarty->assign('article',     $article);
    $smarty->assign('cat_select',  article_cat_list(0, $article['cat_id']));
    $smarty->assign('ur_here',     $_LANG['article_edit']);
    $smarty->assign('action_link', array('text' => $_LANG['03_article_list'], 'href' => 'article.php?act=list&' . list_link_postfix()));
    $smarty->assign('form_action', 'update');

    assign_query_info();
    $smarty->display('article_info.htm');
}

if ($_REQUEST['act'] == 'update') {
    /* 权限判断 */
    admin_priv('article_manage');

    $title = isset($_POST['title']) ? filter_var($_POST['title'], FILTER_SANITIZE_STRING) : '';
    $article_cat = isset($_POST['article_cat']) ? intval($_POST['article_cat']) : 0;
    /*Check Exist title */
    $is_only = $exc->is_only('title', $title, $_POST['id'], "cat_id = '$article_cat'");
    if (!$is_only) {
        sys_msg(sprintf($_LANG['title_exist'], stripslashes($title)), 1);
    }
    /* edit by ecshopvietnam.com */
    $article_type = isset($_POST['article_type']) ? intval($_POST['article_type']) : 0;
    $author = isset($_POST['author']) ? filter_var($_POST['author'], FILTER_SANITIZE_STRING) : '';
    $author_email = isset($_POST['author_email']) ? filter_var($_POST['author_email'], FILTER_SANITIZE_EMAIL) : '';
    $keywords = isset($_POST['keywords']) ? filter_var($_POST['keywords'], FILTER_SANITIZE_STRING) : '';
    $description = isset($_POST['description']) ? filter_var($_POST['description'], FILTER_SANITIZE_STRING) : '';
    $link_url = isset($_POST['link_url']) ? filter_var($_POST['link_url'], FILTER_SANITIZE_URL) : '';
    $modify_time = time();
    $custom_title = isset($_POST['custom_title']) ? filter_var($_POST['custom_title'], FILTER_SANITIZE_STRING) : '';
    $meta_title = isset($_POST['meta_title']) ? filter_var($_POST['meta_title'], FILTER_SANITIZE_STRING) : '';
    $meta_desc = isset($_POST['meta_desc']) ? filter_var($_POST['meta_desc'], FILTER_SANITIZE_STRING) : '';
    $template_file = isset($_POST['template_file']) ? filter_var($_POST['template_file'], FILTER_SANITIZE_STRING) : '';
    $meta_robots = isset($_POST['meta_robots']) ? $_POST['meta_robots'] : 'INDEX,FOLLOW';
    $content = isset($_POST['content']) ? $_POST['content'] : '';
    $open_type =  isset($_POST['is_open']) ? intval($_POST['is_open']) : 0;
    $disable_comment = isset($_POST['disable_comment']) ? intval($_POST['disable_comment']) : 0;

    if (empty($_POST['cat_id'])) {
        $_POST['cat_id'] = 0;
    }




    /* start tag */
    $tags =  filterTags($_POST['tags']);
    $oldtags = filterTags($_POST['oldtags']);
    /* End tag */
    /* check xem da tao lien ket tag cho article chua, TH table article co thong tin tag, nhung article_tags chua co */
    // $exits_article_link = $db->getOne("SELECT COUNT(*) FROM ".$ecs->table('article_tags)')." WHERE article_id=$_POST[id]");
    if ($tags != $oldtags && !empty($tags)) {
        $newt = makeTagsArr($tags);
        $oldt = makeTagsArr($oldtags);
        $del_tags = array_values(array_diff($oldt, $newt));
        $add_tags = array_values(array_diff($newt, $oldt));
        /* xóa tag cũ nếu thay đổi ko chứa giá trị cũ */
        if (!empty($del_tags[0])) {
            for ($i = 0; $i < count($del_tags); $i++) {
                $tag = trim($del_tags[$i]);
                $db->query("DELETE FROM " . $ecs->table('article_tags') . " WHERE tags_id=(SELECT id FROM " . $ecs->table('tags') . " WHERE name='$tag' AND type=1)");
            }
        }
        /* thêm tag mới nếu thay đổi có những giá trị mới */
        if (!empty($add_tags[0])) {
            for ($x = 0; $x < count($add_tags); $x++) {
                $tag = trim($add_tags[$x]);
                /* check xem đã tồn tại tag chưa */
                $tags_id = $db->getOne("SELECT id FROM " . $ecs->table('tags') . " WHERE name='$tag' AND type=1");
                /* nếu ko tồn tại > thêm tag */
                if (!$tags_id) {
                    $slug = build_slug($tag);
                    $db->query("INSERT INTO " . $ecs->table('tags') . " (name, slug) VALUES ('$tag', '$slug')");
                    $tags_id = $db->insert_id();
                }
                /* Tạo liên kết cho tag với id bài viết mới */
                $db->query("INSERT INTO " . $ecs->table('article_tags') . " (tags_id, article_id) VALUES ($tags_id, $_POST[id])");
            }
        }/* end Check have new tag */
    }
    /* end tags */
    $keywords = !empty($keywords) ? $keywords : trim($tags, ","); // Lấy keywords = tag


    $sql_update = " modify_time = '$modify_time', ";
    $sql_update .= " tags = '" . trim($tags, ",") . "', ";

    if (
        isset($_FILES['article_thumb']) && $_FILES['article_thumb']['tmp_name'] != '' &&
        isset($_FILES['article_thumb']['tmp_name']) && $_FILES['article_thumb']['tmp_name'] != 'none'
    ) {
        $thumb = upload_thumb();
        unlink_old_thumb();
        $sql_update .= "article_thumb ='$thumb[thumb]', article_sthumb ='$thumb[sthumb]', article_mthumb ='$thumb[mthumb]', ";
    }
    $sql_update .= " meta_title = '$meta_title', " .
        " meta_desc = '$meta_desc', " .
        " custom_title = '$custom_title', " .
        " template_file = '$template_file', " .
        " is_open = '$open_type', " .
        " disable_comment = '$disable_comment', ";

    if ($exc->edit("title='$title', meta_robots='$meta_robots', {$sql_update} cat_id='$article_cat', article_type='$article_type', author='$author', author_email='$author_email', keywords ='$keywords', content='$content', link='$link_url', description = '$description'", $_POST['id'])) {
        update_slug($_POST['id'], 'article');

        $link[0]['text'] = $_LANG['back_list'];
        $link[0]['href'] = 'article.php?act=list&' . list_link_postfix();

        $note = sprintf($_LANG['articleedit_succeed'], stripslashes($_POST['title']));
        admin_log($_POST['title'], 'edit', 'article');

        clear_cache_files();

        sys_msg($note, 0, $link);
    } else {
        die($db->error());
    }
}

/*------------------------------------------------------ */
//-- 编辑文章主题
/*------------------------------------------------------ */ elseif ($_REQUEST['act'] == 'edit_title') {
    check_authz_json('article_manage');

    $id    = intval($_POST['id']);
    $title = json_str_iconv(trim($_POST['val']));

    /* 检查文章标题是否重复 */
    if ($exc->num("title", $title, $id) != 0) {
        make_json_error(sprintf($_LANG['title_exist'], $title));
    } else {
        if ($exc->edit("title = '$title'", $id)) {
            clear_cache_files();
            admin_log($title, 'edit', 'article');
            make_json_result(stripslashes($title));
        } else {
            make_json_error($db->error());
        }
    }
}

/*------------------------------------------------------ */
//-- 切换是否显示
/*------------------------------------------------------ */ elseif ($_REQUEST['act'] == 'toggle_show') {
    check_authz_json('article_manage');

    $id     = intval($_POST['id']);
    $val    = intval($_POST['val']);

    $exc->edit("is_open = '$val'", $id);
    clear_cache_files();

    make_json_result($val);
}

/*------------------------------------------------------ */
//-- 切换文章重要性
/*------------------------------------------------------ */ elseif ($_REQUEST['act'] == 'toggle_type') {
    check_authz_json('article_manage');

    $id     = intval($_POST['id']);
    $val    = intval($_POST['val']);

    $exc->edit("article_type = '$val'", $id);
    clear_cache_files();

    make_json_result($val);
}



/*------------------------------------------------------ */
//-- 删除文章主题
/*------------------------------------------------------ */ elseif ($_REQUEST['act'] == 'remove') {
    check_authz_json('article_manage');

    $id = intval($_GET['id']);


    $sql = "SELECT  article_thumb, article_sthumb, article_mthumb FROM " . $ecs->table('article') . " WHERE article_id = '$id'";
    $rm = $db->getRow($sql);

    @unlink(ROOT_PATH . CDN_PATH . '/' . $rm['article_thumb']);
    @unlink(ROOT_PATH . CDN_PATH . '/' . $rm['article_sthumb']);
    @unlink(ROOT_PATH . CDN_PATH . '/' . $rm['article_mthumb']);

    $name = $exc->get_name($id);
    if ($exc->drop($id)) {
        del_slug($id, 'article');
        /* Xóa Comment của tin */
        $db->query("DELETE FROM " . $ecs->table('comment') . " WHERE comment_type = 1 AND id_value = $id");
        /* Xoas bai viet lien quan */
        $db->query("DELETE FROM " . $ecs->table('link_goods') . " WHERE module = 1 AND goods_id = '$goods_id'");
        /* Xóa Tags */
        $db->query("DELETE FROM " . $ecs->table('article_tags') . " WHERE article_id = $id");


        admin_log(addslashes($name), 'remove', 'article');
        clear_cache_files();
    }

    $url = 'article.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);

    ecs_header("Location: $url\n");
    exit;
}

/*------------------------------------------------------ */
//-- Thêm Sản phẩm liên quan
/*------------------------------------------------------ */ elseif ($_REQUEST['act'] == 'add_link_goods') {
    include_once(ROOT_PATH . 'includes/cls_json.php');
    $json = new JSON;

    check_authz_json('goods_manage');

    $linked_array   = $json->decode($_GET['add_ids']);
    $linked_goods   = $json->decode($_GET['JSON']);
    $goods_id       = $linked_goods[0];
    $is_double      = 0;

    foreach ($linked_array as $val) {

        $sql = "INSERT INTO " . $ecs->table('link_goods') . " (goods_id, link_goods_id, is_double, admin_id, module) " .
            "VALUES ('$goods_id', '$val', '$is_double', '$_SESSION[admin_id]', '1')";
        $db->query($sql, 'SILENT');
    }

    $linked_goods   = get_linked_goods($goods_id, 1);
    $options        = array();

    foreach ($linked_goods as $val) {
        $options[] = array(
            'value'  => $val['goods_id'],
            'text'      => $val['goods_name'],
            'data'      => ''
        );
    }

    clear_cache_files();
    make_json_result($options);
}

/*------------------------------------------------------ */
//-- Xóa sp liên quan tin tức
/*------------------------------------------------------ */ elseif ($_REQUEST['act'] == 'drop_link_goods') {
    include_once(ROOT_PATH . 'includes/cls_json.php');
    $json = new JSON;

    check_authz_json('goods_manage');

    $drop_goods     = $json->decode($_GET['drop_ids']);
    $drop_goods_ids = db_create_in($drop_goods);
    $linked_goods   = $json->decode($_GET['JSON']);
    $goods_id       = $linked_goods[0];
    $is_signle      = $linked_goods[1];

    $sql = "DELETE FROM " . $ecs->table('link_goods') .
        " WHERE module = 1 AND goods_id = '$goods_id' AND link_goods_id " . $drop_goods_ids;
    if ($goods_id == 0) {
        $sql .= " AND admin_id = '$_SESSION[admin_id]'";
    }
    $db->query($sql);

    $linked_goods = get_linked_goods($goods_id, 1);
    $options      = array();

    foreach ($linked_goods as $val) {
        $options[] = array(
            'value' => $val['goods_id'],
            'text'  => $val['goods_name'],
            'data'  => ''
        );
    }

    clear_cache_files();
    make_json_result($options);
}


/*------------------------------------------------------ */
//-- 搜索商品
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'get_goods_list') {
    include_once(ROOT_PATH . 'includes/cls_json.php');
    $json = new JSON;

    $filters = $json->decode($_GET['JSON']);

    $arr = get_goods_list($filters);
    $opt = array();

    foreach ($arr as $key => $val) {
        $opt[] = array(
            'value' => $val['goods_id'],
            'text' => $val['goods_name'],
            'data' => $val['shop_price']
        );
    }

    make_json_result($opt);
}
/*------------------------------------------------------ */
//-- 批量操作
/*------------------------------------------------------ */ elseif ($_REQUEST['act'] == 'batch') {
    /* 批量删除 */
    if (isset($_POST['type'])) {
        if ($_POST['type'] == 'button_remove') {
            admin_priv('article_manage');

            if (!isset($_POST['checkboxes']) || !is_array($_POST['checkboxes'])) {
                sys_msg($_LANG['no_select_article'], 1);
            }

            $sql = "DELETE FROM " . $ecs->table('article_tags') . " WHERE article_id " . db_create_in(join(',', $_POST['checkboxes']));
            $db->query($sql);


            $sql = "SELECT  article_thumb, article_sthumb, article_mthumb FROM " . $ecs->table('article') .
                " WHERE article_id " . db_create_in(join(',', $_POST['checkboxes']));
            $res = $db->query($sql);
            while ($row = $db->fetchRow($res)) {

                @unlink(ROOT_PATH . CDN_PATH . '/' . $rm['article_thumb']);
                @unlink(ROOT_PATH . CDN_PATH . '/' . $rm['article_sthumb']);
                @unlink(ROOT_PATH . CDN_PATH . '/' . $rm['article_mthumb']);
            }

            foreach ($_POST['checkboxes'] as $key => $id) {
                del_slug($id, 'article');

                /* Xoas bai viet lien quan */
                $db->query("DELETE FROM " . $ecs->table('link_goods') . " WHERE module = 1 AND goods_id = '$goods_id'");


                if ($exc->drop($id)) {
                    $name = $exc->get_name($id);
                    admin_log(addslashes($name), 'remove', 'article');
                }
            }
        }

        /* 批量隐藏 */
        if ($_POST['type'] == 'button_hide') {
            check_authz_json('article_manage');
            if (!isset($_POST['checkboxes']) || !is_array($_POST['checkboxes'])) {
                sys_msg($_LANG['no_select_article'], 1);
            }

            foreach ($_POST['checkboxes'] as $key => $id) {
                $exc->edit("is_open = '0'", $id);
            }
        }

        /* 批量显示 */
        if ($_POST['type'] == 'button_show') {
            check_authz_json('article_manage');
            if (!isset($_POST['checkboxes']) || !is_array($_POST['checkboxes'])) {
                sys_msg($_LANG['no_select_article'], 1);
            }

            foreach ($_POST['checkboxes'] as $key => $id) {
                $exc->edit("is_open = '1'", $id);
            }
        }

        /* 批量移动分类 */
        if ($_POST['type'] == 'move_to') {
            check_authz_json('article_manage');
            if (!isset($_POST['checkboxes']) || !is_array($_POST['checkboxes'])) {
                sys_msg($_LANG['no_select_article'], 1);
            }

            if (!$_POST['target_cat']) {
                sys_msg($_LANG['no_select_act'], 1);
            }

            foreach ($_POST['checkboxes'] as $key => $id) {
                $exc->edit("cat_id = '" . $_POST['target_cat'] . "'", $id);
            }
        }
    }

    /* 清除缓存 */
    clear_cache_files();
    $lnk[] = array('text' => $_LANG['back_list'], 'href' => 'article.php?act=list');
    sys_msg($_LANG['batch_handle_ok'], 0, $lnk);
} elseif ($_REQUEST['act'] == 'autocomplete_tags') {

    clear_cache_files();
    $tag = json_str_iconv(trim($_REQUEST['term']));
    $sql = 'SELECT name FROM ' . $ecs->table('tags') . " WHERE name LIKE '%$tag%'";
    $result = $db->getCol($sql);
    die(json_encode($result));
}

/* 把商品删除关联 */
function drop_link_goods($goods_id, $article_id)
{
    $sql = "DELETE FROM " . $GLOBALS['ecs']->table('goods_article') .
        " WHERE goods_id = '$goods_id' AND article_id = '$article_id' LIMIT 1";
    $GLOBALS['db']->query($sql);
    create_result(true, '', $goods_id);
}

/* 取得文章关联商品 */
function get_article_goods($article_id)
{
    $list = array();
    $sql  = 'SELECT g.goods_id, g.goods_name' .
        ' FROM ' . $GLOBALS['ecs']->table('goods_article') . ' AS ga' .
        ' LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ON g.goods_id = ga.goods_id' .
        " WHERE ga.article_id = '$article_id'";
    $list = $GLOBALS['db']->getAll($sql);

    return $list;
}

/* 获得文章列表 */
function get_articleslist()
{
    $result = get_filter();
    if ($result === false) {
        $filter = array();

        $filter['keyword']    = empty($_REQUEST['keyword']) ? '' : trim($_REQUEST['keyword']);
        if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1) {
            $filter['keyword'] = json_str_iconv($filter['keyword']);
        }
        $filter['cat_id'] = empty($_REQUEST['cat_id']) ? 0 : intval($_REQUEST['cat_id']);
        $filter['sort_by']    = empty($_REQUEST['sort_by']) ? 'a.article_id' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

        $where = '';
        if (!empty($filter['keyword'])) {
            $where = " AND a.title LIKE '%" . mysql_like_quote($filter['keyword']) . "%'";
        }
        if ($filter['cat_id']) {
            $where .= " AND a." . get_article_children($filter['cat_id']);
        }
        // $where .= " AND a.user_id='$_SESSION[admin_id]'"; // edit Kien

        /* 文章总数 */
        $sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('article') . ' AS a ' .
            'LEFT JOIN ' . $GLOBALS['ecs']->table('article_cat') . ' AS ac ON ac.cat_id = a.cat_id ' .
            'WHERE 1 ' . $where;
        $filter['record_count'] = $GLOBALS['db']->getOne($sql);

        $filter = page_and_size($filter);

        /* 获取文章数据 */
        $sql = 'SELECT a.* , ac.cat_name ' .
            'FROM ' . $GLOBALS['ecs']->table('article') . ' AS a ' .
            'LEFT JOIN ' . $GLOBALS['ecs']->table('article_cat') . ' AS ac ON ac.cat_id = a.cat_id ' .
            'WHERE 1 ' . $where . ' ORDER by ' . $filter['sort_by'] . ' ' . $filter['sort_order'];

        $filter['keyword'] = stripslashes($filter['keyword']);
        set_filter($filter, $sql);
    } else {
        $sql    = $result['sql'];
        $filter = $result['filter'];
    }
    $arr = array();
    $res = $GLOBALS['db']->selectLimit($sql, $filter['page_size'], $filter['start']);

    while ($rows = $GLOBALS['db']->fetchRow($res)) {
        $rows['date'] = local_date($GLOBALS['_CFG']['time_format'], $rows['add_time']);
        $rows['url'] = build_uri('article', array('aid' => $rows['article_id']));
        $arr[] = $rows;
    }
    // var_dump($arr);die;
    return array('arr' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
}

/* 上传文件 */
function upload_article_file($upload)
{
    if (!make_dir("../" . DATA_DIR . "/article")) {
        /* 创建目录失败 */
        return false;
    }

    $filename = cls_image::random_filename() . substr($upload['name'], strpos($upload['name'], '.'));
    $path     = ROOT_PATH . DATA_DIR . "/article/" . $filename;

    if (move_upload_file($upload['tmp_name'], $path)) {
        return DATA_DIR . "/article/" . $filename;
    } else {
        return false;
    }
}

function upload_thumb()
{
    $thumbs = array('thumb' => '', 'sthumb' => '');
    if (
        isset($_FILES['article_thumb']) && $_FILES['article_thumb']['tmp_name'] != '' &&
        isset($_FILES['article_thumb']['tmp_name']) && $_FILES['article_thumb']['tmp_name'] != 'none'
    ) {
        if (!check_type_allow($_FILES['article_thumb']['name'], $GLOBALS['allow_image_types'])) {
            sys_msg($GLOBALS['_LANG']['invalid_file']);
        }
        $image = new cls_image($GLOBALS['_CFG']['bgcolor']);
        $slug = build_slug($_POST['title']);
        $filename = $slug . '-thumb-' . time();
        $filename_small = $slug . '-sthumb-' . time();
        $filename_mobile = $slug . '-mthumb-' . time();
        $dir = ROOT_PATH . CDN_PATH . '/article_thumb/' . date('Ym') . '/';
        if (!$dir) {
            @mkdir($dir, 0777);
        }
        $thumbnail = $image->make_thumb($_FILES['article_thumb']['tmp_name'], $GLOBALS['_CFG']['article_thumb_width'],$GLOBALS['_CFG']['article_thumb_height'], $dir, '', $filename);
        $small_thumb = $image->make_thumb($_FILES['article_thumb']['tmp_name'], 250, 140, $dir, '', $filename_small);
        $mobile_thumb = $image->make_thumb($_FILES['article_thumb']['tmp_name'], 150, 84, $dir, '', $filename_mobile);
        
          /*convert to Webp*/
          $thumbnail_webp = $image->convertWebp(ROOT_PATH.CDN_PATH.'/'.$thumbnail);
          $image->convertWebp(ROOT_PATH.CDN_PATH.'/'.$small_thumb);
          $image->convertWebp(ROOT_PATH. CDN_PATH.'/'.$mobile_thumb);

        if ($thumbnail == false) {
            sys_msg($image->error_msg(), 1, array(), false);
        }
        if ($small_thumb == false) {
            sys_msg($image->error_msg(), 1, array(), false);
        }
        if ($mobile_thumb == false) {
            sys_msg($image->error_msg(), 1, array(), false);
        }

        $thumbnail = str_replace(CDN_PATH . '/', '', $thumbnail);
        $small_thumb = str_replace(CDN_PATH . '/', '', $small_thumb);
        $mobile_thumb = str_replace(CDN_PATH . '/', '', $mobile_thumb);

        $thumbs = array('thumb' => $thumbnail, 'sthumb' => $small_thumb, 'mthumb' => $mobile_thumb);
    }
    return $thumbs;
}
function unlink_thumb($thumb,$sthumb, $mthumb){
    $img_thumb  = ROOT_PATH. CDN_PATH.'/'. $thumb;
    $img_sthumb = ROOT_PATH. CDN_PATH.'/'. $sthumb;
    $img_mthumb = ROOT_PATH. CDN_PATH.'/'. $mthumb;
    /* Xóa phiên bản webp trước hình thường, vì còn convert tên */
    $webp_thumb  = convertExtension($img_thumb, 'webp');
    $webp_sthumb = convertExtension($img_sthumb, 'webp');
    $webp_mthumb = convertExtension($img_mthumb, 'webp');
    @unlink($webp_thumb); @unlink($webp_sthumb); @unlink($webp_mthumb);
    @unlink($img_thumb); @unlink($img_sthumb); @unlink($img_mthumb);
}


/**
 * Cập nhật ID cho sp liên quan đến tin vừa đăng
 * @param   int     $goods_id
 * @return  void
 */
function handle_link_goods($article_id)
{
    $sql = "UPDATE " . $GLOBALS['ecs']->table('link_goods') . " SET " .
        " goods_id = '$article_id' " .
        " WHERE goods_id = '0' AND module = 1" .
        " AND admin_id = '$_SESSION[admin_id]'";
    $GLOBALS['db']->query($sql);
}

function filterTags($str)
{
    $tags = !empty($str) ? strip_tags(trim($str)) : '';
    return mb_strtolower($tags, 'UTF-8');
}
function makeTagsArr($str)
{
    $arrTag = explode(',', $str);
    $tags = array();
    foreach ($arrTag as $tag) {
        $tags[] = trim($tag);
    }
    return $tags;
}
