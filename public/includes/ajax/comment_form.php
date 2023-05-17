<?php
    /*------------------------------------------------------ */
    //-- Request Ajax for Comments - act=comment_form
    /*------------------------------------------------------ */

    require(ROOT_PATH . 'includes/cls_json.php');
    $json   = new JSON;
    $result = array('error' => 0, 'message' => '', 'content' => '');
    $cmt  = $json->decode($_REQUEST['data']);

    $cmt_id_value  = !empty($cmt->id_value)   ? intval($cmt->id_value)   : 0;
    $cmt_type      = !empty($cmt->type) ? intval($cmt->type) : 0;
    $cmt_parent_id = isset($cmt->parent_id) && !empty($cmt->parent_id) ? intval($cmt->parent_id) : 0;
    $cmd_id = isset($cmt->cmd_id) && !empty($cmt->cmd_id) ? intval($cmt->cmd_id) : 0;

    /* Kiem tra du lieu dau vao */
    if($cmt_id_value == 0){
        $result['error'] = 1;
        $result['message'] = 'Thông tin không hợp lệ';
        die($json->encode($result));
    }

    $tag_name = '';
    if($cmd_id > 0){
        $getname = $db->getOne("SELECT user_name FROM ".$ecs->table('comment')." WHERE comment_id = $cmd_id");
        $tag_name = "@{$getname}:";
    }
    $smarty->assign('tag_name', $tag_name);

    $smarty->assign('comment_type', $cmt_type);
    $smarty->assign('id',           $cmt_id_value);
    $smarty->assign('parent_id',    $cmt_parent_id);

    if ((intval($_CFG['captcha']) & CAPTCHA_COMMENT) && gd_version() > 0)
    {
        $smarty->assign('enabled_captcha', 0);
        $smarty->assign('rand', mt_rand());
    }

    $_SESSION['cm_csrf_token'] = md5($_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT'].mt_rand());
    $smarty->assign('cm_csrf_token', $_SESSION['cm_csrf_token']);

    $result['content'] = $smarty->fetch("library/comment_form.lbi");
    die($json->encode($result));

 ?>