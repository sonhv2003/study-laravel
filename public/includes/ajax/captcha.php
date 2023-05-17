<?php
    /**
     * Tạo chuổi Captcha - act=captcha
     *
     */
    define('INIT_NO_SMARTY', true);
    require(ROOT_PATH . 'includes/cls_captcha.php');

    $img = new captcha(ROOT_PATH .DATA_DIR . '/captcha/', $_CFG['captcha_width'], $_CFG['captcha_height']);
    @ob_end_clean(); //清除之前出现的多余输入
    if (isset($_REQUEST['is_login']))
    {
        $img->session_word = 'captcha_login';
    }
    $img->generate_image();
    exit;
 ?>