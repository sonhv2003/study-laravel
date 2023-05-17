<?php

/*------------------------------------------------------ */
//-- Request Ajax for Review - act=reviews
/*------------------------------------------------------ */

    require(ROOT_PATH . 'includes/cls_json.php');
    $json   = new JSON;
    $result = array('error' => 0, 'message' => '', 'content' => '');

    $_REQUEST['cmt'] = isset($_REQUEST['cmt']) ? json_str_iconv($_REQUEST['cmt']) : '';
    $_REQUEST['method'] = isset($_REQUEST['method']) ? json_str_iconv($_REQUEST['method']) : 'get';
    /* Comment Post */
    if ($_REQUEST['method'] == 'post')
    {
        /*
         * act 参数为空
         */
        $cmt  = $json->decode($_REQUEST['cmt']);
        $cmt->page = 1;
        $cmt->id   = !empty($cmt->id)   ? intval($cmt->id) : 0;
        $cmt->type = !empty($cmt->type) ? intval($cmt->type) : 0;
        $cmt->parent_id = !empty($cmt->parent_id) ? intval($cmt->parent_id) : 0;
        $cmt->img = !empty($cmt->img) ? trim($cmt->img, ',') : '';

        if (empty($cmt) || !isset($cmt->type) || !isset($cmt->id))
        {
            $result['error']   = 1;
            $result['message'] = $_LANG['invalid_comments'];
        }
        elseif (!empty($cmt->email) && !is_email($cmt->email))
        {
            $result['error']   = 1;
            $result['message'] = $_LANG['error_email'];
        }
        else
        {
            /* có bật captahc */
            if ((intval($_CFG['captcha']) & CAPTCHA_COMMENT) && gd_version() > 0)
            {
                /* 检查验证码 */
                include_once('includes/cls_captcha.php');

                $validator = new captcha();
                if (!$validator->check_word($cmt->captcha))
                {
                    $result['error']   = 1;
                    $result['message'] = $_LANG['invalid_captcha'];
                }
                else
                {

                    if (empty($result['error']))
                    {
                        add_reviews($cmt);
                    }
                }
            }
            else
            {
                /* 没有验证码时，用时间来限制机器人发帖或恶意发评论 */
                if (!isset($_SESSION['send_time']))
                {
                    $_SESSION['send_time'] = 0;
                }

                $cur_time = gmtime();
                if (($cur_time - $_SESSION['send_time']) < 30) // 小于30秒禁止发评论
                {
                    $result['error']   = 1;
                    $result['message'] = $_LANG['cmt_spam_warning'];
                }
                else
                {
                    /* 无错误就保存留言 */
                    if (empty($result['error']))
                    {
                        add_reviews($cmt);
                        $_SESSION['send_time'] = $cur_time;
                    }
                }
            }
        }
    }
    /* Reviews Get */
    else
    {
        /*
         * act 参数不为空
         * 默认为评论内容列表
         * 根据 _GET 创建一个静态对象
         */
        $cmt = new stdClass();
        $cmt->id   = !empty($_GET['id'])   ? intval($_GET['id'])   : 0;
        $cmt->type = !empty($_GET['type']) ? intval($_GET['type']) : 0;
        $cmt->page = isset($_GET['page'])   && intval($_GET['page'])  > 0 ? intval($_GET['page'])  : 1;
    }

    if ($result['error'] == 0)
    {
        $comments = assign_reviews($cmt->id, $cmt->type, $cmt->page);

        $smarty->assign('reviews_type', $cmt->type);
        $smarty->assign('reviews_id',           $cmt->id);
        $smarty->assign('reviews_username',     $_SESSION['user_name']);
        $smarty->assign('reviews_email',        $_SESSION['email']);
        $smarty->assign('reviews',     $comments['reviews']);
        $smarty->assign('pager_reviews',        $comments['pager_reviews']);

        $smarty->assign('total_review',$comments['total_review']);
        $smarty->assign('number_rank',  getNumberRanks($cmt->id, $cmt->type));
        $getCountReview = getRank($cmt->id);
        $smarty->assign('review_rank', ceil($getCountReview['comment_rank']));

        /* 验证码相关设置 */
        if ((intval($_CFG['captcha']) & CAPTCHA_COMMENT) && gd_version() > 0)
        {
            $smarty->assign('enabled_captcha', 1);
            $smarty->assign('rand2', mt_rand());
        }

        $result['message'] = $_CFG['comment_check'] ? $_LANG['cmt_submit_wait'] : '';
        $result['content'] = $smarty->fetch("library/reviews_list.lbi");
    }

    die($json->encode($result));



/*------------------------------------------------------ */
//-- Private Function for request ajax
/*------------------------------------------------------ */

/**
 * add_reviews
 *
 * @access  public
 * @param   object  $cmt
 * @return  void
 */
function add_reviews($cmt)
{
    /* 评论是否需要审核 */
    $status = 1 - $GLOBALS['_CFG']['comment_check'];

    $user_id = empty($_SESSION['user_id']) ? 0 : $_SESSION['user_id'];
    $email = empty($cmt->email) ? $_SESSION['email'] : trim($cmt->email);
    $user_name = empty($cmt->username) ? $_SESSION['user_name'] : '';
    $user_tel = !empty($cmt->user_tel) ? htmlspecialchars($cmt->user_tel) : '';
    $user_name = empty($cmt->user_name) ? $_SESSION['user_name'] : trim($cmt->user_name);

    $email = htmlspecialchars($email);
    $user_name = htmlspecialchars($user_name);
    $content = htmlspecialchars($cmt->content);

    $user_tel = strip_tags($user_tel);
    $user_name = strip_tags($user_name);
    $content = strip_tags($content);

    /* Edit Nobj thêm parent_id, user_tel */
    $sql = "INSERT INTO " .$GLOBALS['ecs']->table('comment') .
           "(comment_type, id_value, email, user_tel, user_name, content, comment_rank, add_time, ip_address, status, parent_id, user_id, mod_type) VALUES " .
           "('" .$cmt->type. "', '" .$cmt->id. "', '$email', '$user_tel', '$user_name', '" .$cmt->content."', '".$cmt->rank."', ".time().", '".real_ip()."', '$status', '".$cmt->parent_id."', '$user_id', 1)";

    $result = $GLOBALS['db']->query($sql);
    $comment_id = $GLOBALS['db']->insert_id();


    /* Gửi mail thông báo cho Shop */
    if ($GLOBALS['_CFG']['service_email'] != '')
    {
        $msg_content = '<p>Tên Khách hàng: '.$user_name.'</p>';
        $msg_content .= '<p>Số ĐT: '.$user_tel.' </p>';
        $msg_content .= '<p>Nội dung:'.addslashes($content).'</p>';
        @send_mail($GLOBALS['_CFG']['shop_name'], $GLOBALS['_CFG']['service_email'], $user_name.' - Gửi bình luận đánh giá', $msg_content, 1);
    }

    clear_cache_files('reviews_list.lbi');

    if(!empty($cmt->img)){
        $arr_img = explode(',', $cmt->img);
        require_once(ROOT_PATH . 'includes/cls_image.php');
        $image = new cls_image($GLOBALS['_CFG']['bgcolor']);
        foreach ($arr_img as $k => $img) {
            /* resize */
            $dir = ROOT_PATH.DATA_DIR.'/feedbackimg/';
            $arr_name = explode('.', $img);
            $filename = $arr_name[0];
            $thumbnail = $image->make_thumb(ROOT_PATH.'temp/reviews/'.$img, 0, 650, $dir, '', $filename);
            if($thumbnail){
                /* Đổi tên hình */
                $thumbnail = str_replace(CDN_PATH.'/', '', $thumbnail);
                $comment_img = array(
                    'comment_id'=> $comment_id,
                    'img'=>$thumbnail,
                    'mod_type'=> 1
                );
                $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('comment_photo'), $comment_img, 'INSERT');
            }
            @unlink(ROOT_PATH.'temp/reviews/'.$img);
        }
    }

    return $result;
}
 ?>