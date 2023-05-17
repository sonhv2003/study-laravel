<?php

if($_url == ''){
    $_module = 'index';
}elseif($_url == 'new'){
    $_module = 'article_cat';
    $slug = $active_url = $_url;  $module_id = 4;
}elseif($_url == 'contact-us'){
    $_module = 'message'; $slug = $_url;
}
// elseif($_url == 'thanh-vien'){
//     $_module = 'user'; $slug = $_url;
// }
elseif($_url == 'ajax'){
    $_module = 'ajax';
}
// elseif($_url == 'gio-hang'){
//     $_module = 'flow'; $slug = $_url;
// }
elseif($_url == 'search'){
    $_module = 'search';
}
// elseif($_url == 'khuyen-mai'){
//     $_module = 'topic';
// }
elseif($_url == 'tracking'){
    $_module = 'affiche';
}
// elseif($_url == 'thuong-hieu'){
//     $_module = 'brand'; $slug = '';
// }elseif($_url == 'so-sanh'){
//     $_module = 'compare';
// }elseif($_url == 'callback_payment'){
//     $_module = 'respond';
// }elseif($_url == 'callback_shipping'){
//     $_module = 'receive';
// }elseif($_url == 'danh-muc'){
//     $_module = 'catalog';
// }
// elseif($_url == 'region'){
//     $_module = 'region';
// }
// elseif($_url == 'webhooks'){
//     $_module = 'webhooks';
// }
elseif($_url == '404'){
    $_module = '404';
}

/* Dynamic Sitemap  */
elseif($_url == 'sitemap.xml'){
    $_module = 'sitemap'; $types = 'index';
}
elseif(preg_match("/^sitemap-([a-z0-9_-]+).xml$/", $_url, $match)){
   $_module = 'sitemap'; $types = $match[1];
}
/**
 * Dynamic Route - Route động thay đổi theo slug
 * Trật tự bên dưới là có tính toán, xếp trước là match trước
 * Luôn đặt sau Static Route
 */

// elseif(preg_match("/^so-sanh\/([a-z0-9\-\/]+)-vs-([a-z0-9\-\/]+)$/", $_url, $match)){
//     $_module = 'compare'; $slug = $match[1]; $slug2 = $match[2];
// }
// elseif(preg_match("/^khuyen-mai\/([a-z0-9_-]+).html$/", $_url, $match)){
//    $_module = 'topic'; $slug = $match[1];
// }
// elseif(preg_match("/^([a-z0-9_-]+)\/hang-([a-z0-9_-]+)$/", $_url, $match)){
//     $_module = 'category'; $slug = $match[1]; $slug_brand = $match[2]; $active_url = $match[0];
// }
// elseif(preg_match("/^thuong-hieu-([a-z0-9_-]+)$/", $_url, $match)){
//    $_module = 'brand'; $slug = $match[1]; $slug_brand = '';
// }
// elseif(preg_match("/^([a-z0-9_-]+)\/hang-([a-z0-9_-]+)$/", $_url, $match)){
//    $_module = 'brand'; $slug = $match[2]; $slug_brand = $match[1];
// }
// elseif(preg_match("/^tra-gop\/([a-z0-9_-]+)$/", $_url, $match)){
//     $_module = 'installment'; $slug = $match[2];
// }

else{
    /* SQL Injection  */
    //$clean_url = preg_replace("/[^A-Za-z0-9\.\-\/]+/", "", $url);
    $active_url = $_url;
    $slug = strpos($_url,".html") > 0 ? substr($_url, 0, -5) : $_url;
    $get_modules  = $db->getRow("SELECT id, module FROM " . $ecs->table('slug') ." WHERE slug = '".$slug."'");
    if($get_modules){
        $_module = $get_modules['module'];
        $module_id = $get_modules['id'];

    }else{
        $_module = '404';
    }
}

 ?>