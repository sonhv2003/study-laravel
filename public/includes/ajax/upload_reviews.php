<?php
/**
 * Up hình đánh giá, chưa resize
 */
include(ROOT_PATH.'includes/cls_json.php');
$json   = new JSON;
$res    = array('message' => '', 'error' => 0);

if($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_FILES["file"]["name"])){
            // File path config
    $basename = basename($_FILES["file"]["name"]);
    $arr_name = explode('.', $basename);
    $filename = $arr_name[0].'-'.time();
    $targetFilePath = ROOT_PATH.'temp/reviews/';
    $fileType = pathinfo($targetFilePath. $basename, PATHINFO_EXTENSION);

    // Allow certain file formats
    $allowTypes = array('gif', 'jpg', 'png', 'jpeg');
    $file_size = $_FILES['file']['size']; // 1MB
    if(in_array($fileType, $allowTypes) && $file_size < 1043152){
        // Upload file to the server
        if(move_uploaded_file($_FILES["file"]["tmp_name"], $targetFilePath.$filename.'.'.$fileType)){
            $res['error'] = 0;
            $res['message'] = $filename.'.'.$fileType;
        }else{
            $res['error'] = 1;
            $res['message'] = 'Có lỗi trong quá trình upload';
        }
    }else{
        $res['error'] = 1;
        $res['message'] = 'Không hỗ trợ dịnh dạng file hoặc dung lượng quá lớn';
    }

    die($json->encode($res));
}else{
    die($json->encode(array('message' => 'File do not exits', 'error' => 1)));
}
 ?>