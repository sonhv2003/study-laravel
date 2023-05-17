<?php
/**
 * Xóa hình đã up khi đánh giá
 */
include(ROOT_PATH.'includes/cls_json.php');
$json   = new JSON;
$res    = array('message' => '', 'error' => 0);

if(isset($_POST['data']) && !empty($_POST['data'])){
    $data  = $json->decode($_POST['data']);
    $img = isset($data->img) ? $data->img : '';
    @unlink(ROOT_PATH.'temp/reviews/'. $img);
    $res['message'] = 'Đã xóa file';
}else{
    $res = array('message' => 'Request Không hợp lệ', 'error' => 1);
}

die($json->encode($res));

?>