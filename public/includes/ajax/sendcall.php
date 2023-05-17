<?php

require(ROOT_PATH . 'includes/cls_json.php');
$json   = new JSON;
$result = array('error' => 0, 'message' => '', 'content' => '');

$input  = $json->decode($_POST['data']);
$cname =  filter_var($input->cname, FILTER_SANITIZE_STRING);
$ctel =  addslashes(trim($input->ctel));
$url =  filter_var($input->url, FILTER_SANITIZE_URL);
$cemail =  filter_var($input->cemail, FILTER_SANITIZE_STRING);

$cname = strip_tags($cname);
$ctel = strip_tags($ctel);
$url = strip_tags($url);
$cemail = strip_tags($cemail);

if(empty($cname)){
    $result['content'] = "Tên không hợp lệ.";
    $result['error'] = 1;
    die(json_encode($result));
}
/* Chekc số ĐT */
if(!is_tel($ctel)){
    $result['content'] = "Số ĐT Không hợp lệ.";
    $result['error'] = 1;
    die(json_encode($result));
}


/* Chekc số ĐT */
if(!is_email($cemail)){
    $result['content'] = "Email Không hợp lệ.";
    $result['error'] = 1;
    die(json_encode($result));
}


$msg_content = "<p>Khách hàng: $cname, Số ĐT: $ctel. Email: $cemail. Đăng ký Tư Vấn về dịch vụ <a target='_blank' href='".$url."'>Xem Link</a></p>";

$feedback = array(
    'parent_id' => 0,
    'user_id' => 0,
    'user_tel' => $ctel,
    'user_name' => $cname,
    'user_email' => $cemail,
    'msg_title' => 'Tôi muốn gọi lại tư vấn',
    'msg_typ'=> 2,
    'msg_content' => addslashes($msg_content),
    'msg_time'=> time(),
    'message_img' => '',
    'order_id' => 0,
    'msg_area' => 0
);
$db->autoExecute($ecs->table('feedback'), $feedback, 'INSERT');
$err = $db->error();
if(!empty($err)){
    $result['content'] = $err;
    $result['error'] = 1;
}else{
    $result['content'] = 'Gửi thành công, chúng tôi sẻ gọi lại sớm !';
}
die(json_encode($result));

 ?>