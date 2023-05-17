<?php

if(!defined("IN_ECS")){die("Have Hacking !");}
@require_once(ROOT_PATH . 'themes/' . $_CFG['template'] . '/options.php');

if (!defined('INIT_NO_SMARTY') && !defined('ECS_ADMIN')){
    $hu = $ecs->url();
    $theme_regions = siy_init_theme_regions();
    $smarty->assign('hu', $hu);
    $smarty->assign('render', $theme_regions);
    $smarty->assign('option', $_CFG);
}


function siy_function($atts) {
    return $atts['function']($atts['content']);
}


function siy_init_theme_regions() {
    // 全局区域
    $regions['before_html_header'] = '';
    $regions['after_html_header'] = '';
    $regions['before_html_footer'] = '';
    $regions['after_html_footer'] = '';
    $regions['before_header'] = '';
    $regions['after_header'] = '';
    $regions['before_footer'] = '';
    $regions['after_footer'] = '';
    $regions['before_col_main'] = '';
    $regions['after_col_main'] = '';
    $regions['before_col_sub'] = '';
    $regions['after_col_sub'] = '';
    $regions['before_col_extra'] = '';
    $regions['after_col_extra'] = '';
    // 特殊区域
    $regions['before_goods_info'] = '';
    $regions['after_goods_info'] = '';

    return $regions;
}


function siy_bought_count($atts) {
    $id = !empty($atts['id']) ? intval($atts['id']) : 0;
    $sql = 'SELECT count(*) ' .
        'FROM ' . $GLOBALS['ecs']->table('order_info') . ' AS oi LEFT JOIN ' . $GLOBALS['ecs']->table('users') . ' AS u ON oi.user_id = u.user_id, ' . $GLOBALS['ecs']->table('order_goods') . ' AS og ' .
        'WHERE oi.order_id = og.order_id AND ' . time() . ' - oi.add_time < 2592000 AND og.goods_id = ' . $id;
    $count = $GLOBALS['db']->getOne($sql);
    return $count;
}

function siy_build_uri($atts) {
    $app       = $atts['app'];
    $params    = $atts;
    $append    = $atts['append'];
    $page      = $atts['page'];
    $keywords  = $atts['keywords'];
    $size      = $atts['size'];

    $uri = build_uri($app, $params, $append, $page, $keywords, $size);

    return $uri;
}
function siy_calculate($atts) {
    eval('$result='.$atts['number'].$atts['formula'].';');
    return $result;
}

function siy_cal_percent($atts) {
    return $atts['total'] > 0 ? round($atts['item']*100/$atts['total'],0) : 0;
}

function siy_category_tree_3($atts) {
    $id = !empty($atts['id']) ? intval($atts['id']) : 0;

    $GLOBALS['smarty']->assign('categories', siy_get_categories_tree_3($id));
    $GLOBALS['smarty']->assign('categories_parent', siy_get_categories_parent($id));
    $form = (!empty($atts['form'])) ? $atts['form'] : 'library/siy_category_tree_3.lbi';
    $val= $GLOBALS['smarty']->fetch($form);

    return $val;
}

function siy_get_categories_tree_3($cat_id = 0) {
    $cat_arr = array();
    if ($cat_id > 0) {
        $parent_id = $cat_id;
        $sql = 'SELECT parent_id FROM ' . $GLOBALS['ecs']->table('category') . " WHERE cat_id = '$cat_id'";
        $parent_id_current = $GLOBALS['db']->getOne($sql);
        if($parent_id_current > 0) {
            $parent_id = $parent_id_current;
            $sql = 'SELECT parent_id FROM ' . $GLOBALS['ecs']->table('category') . " WHERE cat_id = '$parent_id_current'";
            $parent_id_current = $GLOBALS['db']->getOne($sql);
            if($parent_id_current > 0) {
                $parent_id = $parent_id_current;
                $sql = 'SELECT parent_id FROM ' . $GLOBALS['ecs']->table('category') . " WHERE cat_id = '$parent_id_current'";
                $parent_id_current = $GLOBALS['db']->getOne($sql);
                if($parent_id_current > 0) {
                    $parent_id = $parent_id_current;
                }
            }
        }
    } else {
        $parent_id = 0;
    }

    $sql = 'SELECT count(*) FROM ' . $GLOBALS['ecs']->table('category') . " WHERE parent_id = '$parent_id' AND is_show = 1 ";
    if ($GLOBALS['db']->getOne($sql) || $parent_id == 0) {
        $sql = 'SELECT cat_id, cat_name, parent_id ' .
            'FROM ' . $GLOBALS['ecs']->table('category') .
            "WHERE parent_id = '$parent_id' AND is_show = 1 ORDER BY sort_order ASC, cat_id ASC";
        $res = $GLOBALS['db']->getAll($sql);
        foreach ($res AS $row) {
            $cat_arr[$row['cat_id']]['id']   = $row['cat_id'];
            $cat_arr[$row['cat_id']]['name'] = $row['cat_name'];
            $cat_arr[$row['cat_id']]['url']  = build_uri('category', array('cid' => $row['cat_id']), $row['cat_name']);
            if (isset($row['cat_id']) != NULL) {
                $cat_arr[$row['cat_id']]['cat_id'] = siy_get_child_tree_3($row['cat_id'], $cat_id);
            }
            $cat_arr[$row['cat_id']]['current'] = ($row['cat_id'] == $cat_id) ? 1 : 0;
            $cat_arr[$row['cat_id']]['parent'] = ($row['cat_id'] == $parent_id) ? 1 : 0;
        }
    }
    return $cat_arr;
}

function siy_get_child_tree_3($tree_id = 0, $cat_id = 0) {
    $three_arr = array();
    if ($cat_id > 0) {
        $sql = 'SELECT parent_id FROM ' . $GLOBALS['ecs']->table('category') . " WHERE cat_id = '$cat_id'";
        $parent_id = $GLOBALS['db']->getOne($sql);
    } else {
        $parent_id = 0;
    }
    $sql = 'SELECT count(*) FROM ' . $GLOBALS['ecs']->table('category') . " WHERE parent_id = '$tree_id' AND is_show = 1 ";
    if ($GLOBALS['db']->getOne($sql) || $tree_id == 0) {
        $child_sql = 'SELECT cat_id, cat_name, parent_id, is_show ' .
            'FROM ' . $GLOBALS['ecs']->table('category') .
            "WHERE parent_id = '$tree_id' AND is_show = 1 ORDER BY sort_order ASC, cat_id ASC";
        $res = $GLOBALS['db']->getAll($child_sql);
        foreach ($res AS $row) {
            $three_arr[$row['cat_id']]['id']   = $row['cat_id'];
            $three_arr[$row['cat_id']]['name'] = $row['cat_name'];
            $three_arr[$row['cat_id']]['url']  = build_uri('category', array('cid' => $row['cat_id']), $row['cat_name']);
            if (isset($row['cat_id']) != NULL) {
                $three_arr[$row['cat_id']]['cat_id'] = siy_get_child_tree_3($row['cat_id'], $cat_id);
            }
            $three_arr[$row['cat_id']]['current'] = ($row['cat_id'] == $cat_id) ? 1 : 0;
            $three_arr[$row['cat_id']]['parent'] = ($row['cat_id'] == $parent_id) ? 1 : 0;
        }
    }
    return $three_arr;
}

function siy_get_categories_parent($cat_id = 0) {
    $cat_arr = array();
    if ($cat_id > 0) {
        $parent_id = $cat_id;
        $sql = 'SELECT parent_id FROM ' . $GLOBALS['ecs']->table('category') . " WHERE cat_id = '$cat_id'";
        $parent_id_current = $GLOBALS['db']->getOne($sql);
        if($parent_id_current > 0) {
            $parent_id = $parent_id_current;
            $sql = 'SELECT parent_id FROM ' . $GLOBALS['ecs']->table('category') . " WHERE cat_id = '$parent_id_current'";
            $parent_id_current = $GLOBALS['db']->getOne($sql);
            if($parent_id_current > 0) {
                $parent_id = $parent_id_current;
                $sql = 'SELECT parent_id FROM ' . $GLOBALS['ecs']->table('category') . " WHERE cat_id = '$parent_id_current'";
                $parent_id_current = $GLOBALS['db']->getOne($sql);
                if($parent_id_current > 0) {
                    $parent_id = $parent_id_current;
                }
            }
        }
        $sql = 'SELECT cat_name FROM '.$GLOBALS['ecs']->table('category').' WHERE cat_id = '.$parent_id;
        $str = $GLOBALS['db']->getOne($sql);
    } else {
        $str = $GLOBALS['_LANG']['goods_category'];
    }
    return $str;
}

function siy_collection_goods($atts)
{
    $need_cache = $GLOBALS['smarty']->caching;
    $need_compile = $GLOBALS['smarty']->force_compile;
    if ($_SESSION['user_id'] > 0)
    {
        $user_id = $_SESSION['user_id'];
        $number = ($atts['number'] > 0) ? $atts['number'] : '3';
        $sql = 'SELECT g.goods_id, g.goods_name, g.goods_thumb, g.goods_img, g.market_price, g.shop_price AS org_price, '.
            "IFNULL(mp.user_price, g.shop_price * '$_SESSION[discount]') AS shop_price, ".
            'g.promote_price, g.promote_start_date,g.promote_end_date, c.rec_id, c.is_attention' .
            ' FROM ' . $GLOBALS['ecs']->table('collect_goods') . ' AS c' .
            " LEFT JOIN " . $GLOBALS['ecs']->table('goods') . " AS g ".
            "ON g.goods_id = c.goods_id ".
            " LEFT JOIN " . $GLOBALS['ecs']->table('member_price') . " AS mp ".
            "ON mp.goods_id = g.goods_id AND mp.user_rank = '$_SESSION[user_rank]' ".
            " WHERE c.user_id = '$user_id' ORDER BY c.rec_id DESC";
        $res = $GLOBALS['db'] -> selectLimit($sql, $number, 0);
        $goods = array();
        while ($row = $GLOBALS['db']->fetch_array($res))
        {
            if ($row['promote_price'] > 0)
            {
                $promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
            }
            else
            {
                $promote_price = 0;
            }

            $goods[$row['goods_id']]['rec_id']        = $row['rec_id'];
            $goods[$row['goods_id']]['is_attention']  = $row['is_attention'];
            $goods[$row['goods_id']]['id']            = $row['goods_id'];
            $goods[$row['goods_id']]['name']          = $row['goods_name'];
            $goods[$row['goods_id']]['short_name']    = $GLOBALS['_CFG']['goods_name_length'] > 0 ? sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
            $goods[$row['goods_id']]['market_price']  = $row['market_price'] > 0 ? price_format($row['market_price']) : '';
            $goods[$row['goods_id']]['shop_price']    = price_format($row['shop_price']);
            $goods[$row['goods_id']]['price']    = $row['shop_price'];
            $goods[$row['goods_id']]['promote_price'] = ($promote_price > 0) ? price_format($promote_price) : '';
            $goods[$row['goods_id']]['url']           = build_uri('goods', array('gid'=>$row['goods_id']), $row['goods_name']);
            $goods[$row['goods_id']]['thumb']         = get_image_path($row['goods_id'], $row['goods_thumb'], true);
            $goods[$row['goods_id']]['img']           = get_image_path($row['goods_id'], $row['goods_img']);
        }
    }
    $GLOBALS['smarty']->assign('collection_goods', $goods);
    $form = (!empty($atts['form'])) ? $atts['form'] : 'library/siy_collection_goods.lbi';
    $val= $GLOBALS['smarty']->fetch($form);
    $GLOBALS['smarty']->caching = $need_cache;
    $GLOBALS['smarty']->force_compile = $need_compile;
    return $val;
}

function siy_comment_count($atts) {
    $id = !empty($atts['id']) ? intval($atts['id']) : 0;
    $type = !empty($atts['type']) ? intval($atts['type']) : 0;
    $mod_type = isset($atts['mod_type']) && !empty($atts['mod_type']) ? intval($atts['mod_type']) : 0;
    $count = $GLOBALS['db']->getOne('SELECT COUNT(*) FROM ' .$GLOBALS['ecs']->table('comment').
        " WHERE id_value = '$id' AND comment_type = '$type' AND status = 1 AND parent_id = 0 AND mod_type = $mod_type");
    if($count == 0 && isset($atts['default'])){
        $count = $atts['default'];
    }
    return $count;
}

function siy_count($atts) {
    return count($atts['array']);
}
function siy_get_extra_image($atts) {
    $prefix = !empty($atts['prefix']) ? $atts['prefix'] : '';
    $fname = !empty($atts['fname']) ? $atts['fname'] : '';
    $suffix = !empty($atts['suffix']) ? $atts['suffix'] : '.gif';

    $img = 'static/img/extra/' . $prefix . $fname . $suffix;

    $str = (file_exists(ROOT_PATH.$img)) ? '<img src="'.$GLOBALS['_CFG']['static_path'].$img.'" />' : '';

    return $str;
}
function siy_goods($atts) {
    $need_cache = $GLOBALS['smarty']->caching;
    $need_compile = $GLOBALS['smarty']->force_compile;

    $GLOBALS['smarty']->caching = false;
    $GLOBALS['smarty']->force_compile = true;

    $number = ($atts['number'] > 0) ? $atts['number'] : '3';

    $brand_where = ($atts['brand'] > 0) ? " AND g.brand_id = " . $atts['brand'] : '';
    $price_where = ($atts['min'] > 0) ? " AND g.shop_price >= " . $atts['min'] : '';
    $price_where .= ($atts['max'] > 0) ? " AND g.shop_price <=" . $atts['max'] : '';

    $sql =  'SELECT g.goods_id, g.goods_name, g.goods_name_style, g.market_price, g.shop_price AS org_price, g.promote_price, ' .
            "IFNULL(mp.user_price, g.shop_price * '$_SESSION[discount]') AS shop_price, ".
            'promote_start_date, promote_end_date, g.goods_brief, g.goods_thumb, goods_img, b.brand_name ' .
        'FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ' .
        'LEFT JOIN ' . $GLOBALS['ecs']->table('brand') . ' AS b ON b.brand_id = g.brand_id ' .
        "LEFT JOIN " . $GLOBALS['ecs']->table('member_price') . " AS mp ".
        "ON mp.goods_id = g.goods_id AND mp.user_rank = '$_SESSION[user_rank]' ".
        'WHERE g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 ' . $brand_where . $price_where . $ext;

    switch ($atts['type'])
    {
    case 'best':
        $sql .= ' AND is_best = 1';
        break;
    case 'new':
        $sql .= ' AND is_new = 1';
        break;
    case 'hot':
        $sql .= ' AND is_hot = 1';
        break;
    case 'promote':
        $time = gmtime();
        $sql .= " AND is_promote = 1 AND promote_start_date <= '$time' AND promote_end_date >= '$time'";
        break;
    }

    if (!empty($atts['cats']))
    {
        $sql .= " AND (" . get_children($atts['cats']) . " OR " . get_extension_goods($atts['cats']) .")";
    }

    $order = ($atts['order'] == 1) ? 1 : 0;
    $sql .= ($order == 0) ? ' ORDER BY g.sort_order, g.last_update DESC' : ' ORDER BY RAND()';
    $res = $GLOBALS['db']->selectLimit($sql, $number);

    $idx = 0;
    $goods = array();
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        if ($row['promote_price'] > 0)
        {
            $promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
            $goods[$idx]['promote_price'] = $promote_price > 0 ? price_format($promote_price) : '';
        }
        else
        {
            $goods[$idx]['promote_price'] = '';
        }

        $goods[$idx]['id']           = $row['goods_id'];
        $goods[$idx]['name']         = $row['goods_name'];
        $goods[$idx]['brief']        = $row['goods_brief'];
        $goods[$idx]['brand_name']   = $row['brand_name'];
        $goods[$idx]['short_name']   = $GLOBALS['_CFG']['goods_name_length'] > 0 ? sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
        $goods[$idx]['market_price'] =$row['market_price'] > 0 ? price_format($row['market_price']) : '';
        $goods[$idx]['shop_price']   = price_format($row['shop_price']);
        $goods[$idx]['thumb']        = get_image_path($row['goods_id'], $row['goods_thumb'], true);
        $goods[$idx]['goods_img']    = get_image_path($row['goods_id'], $row['goods_img']);
        $goods[$idx]['url']          = build_uri('goods', array('gid' => $row['goods_id']), $row['goods_name']);
        $goods[$idx]['short_style_name'] = add_style($goods[$idx]['short_name'], $row['goods_name_style']);
        $idx++;
    }

    $GLOBALS['smarty']->assign('goods', $goods);

    $form = (!empty($atts['form'])) ? $atts['form'] : 'library/siy_goods.lbi';
    $val= $GLOBALS['smarty']->fetch($form);

    $GLOBALS['smarty']->caching = $need_cache;
    $GLOBALS['smarty']->force_compile = $need_compile;

    return $val;
}
function siy_goods_info($atts) {
    $id = !empty($atts['id']) ? intval($atts['id']) : 0;
    $type = $atts['type'];
    if (empty($type) or $id == '0') return false;
    $item = ($type == 'url') ? 'goods_name' : $type;

    if($item == 'goods_gifts'){
        $sql = 'SELECT goods_gifts, gift_start_date, time_gift, gift_end_date  FROM '.$GLOBALS['ecs']->table('goods').' WHERE goods_id = '.$id;
        $res = $GLOBALS['db']->getRow($sql);
    }else{
        $sql = 'SELECT '.$item.' FROM '.$GLOBALS['ecs']->table('goods').' WHERE goods_id = '.$id;
        $res = $GLOBALS['db']->getOne($sql);
    }

    switch ($type) {
        case 'url':
            $str = build_uri('goods', array('gid'=>$id), $res);
            break;
        case 'goods_gifts';
            /* qua tang theo thoi han */
            $time = time();
            if($res['time_gift'] == 1){
                if($time >= $res['gift_start_date'] && $time <= $res['gift_end_date']){
                    $str   = nl2p($res['goods_gifts']);
                }else{
                    $str = '';
                }
            }else{
                $str   = nl2p($res['goods_gifts']);
            }
            break;
        case 'goods_thumb':
        case 'goods_img':
        case 'original_img':
            $str = get_image_path($id, $res);
            break;
        default:
            $str = $res;
    }
    return $str;
}
function siy_history($atts) {
    $str = '';
    if (!empty($_COOKIE['ECS']['history'])) {
        $number = ($atts['number'] > 0) ? $atts['number'] : '5';
        $where = db_create_in($_COOKIE['ECS']['history'], 'goods_id');
        $sql   = 'SELECT goods_id, goods_name, goods_thumb, shop_price, promote_price, promote_start_date, promote_end_date FROM ' . $GLOBALS['ecs']->table('goods') .
                " WHERE $where AND is_on_sale = 1 AND is_alone_sale = 1 AND is_delete = 0";
        $res = $GLOBALS['db']->SelectLimit($sql, $number);
        $goods = array();
        while ($row = $GLOBALS['db']->fetchRow($res))
        {
            if ($row['promote_price'] > 0)
            {
                $promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
                $goods[$idx]['promote_price'] = $promote_price > 0 ? price_format($promote_price) : '';
            }
            else
            {
                $goods[$idx]['promote_price'] = '';
            }

            $comments = getRank($row['goods_id']);
            $goods[$idx]['comment_rank']= round($comments['comment_rank'],1);
            $goods[$idx]['comment_count'] = $comments['comment_count'];

            $goods[$idx]['id']           = $row['goods_id'];
            $goods[$idx]['name']         = $row['goods_name'];
            $goods[$idx]['short_name']   = $GLOBALS['_CFG']['goods_name_length'] > 0 ? sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
            $goods[$idx]['shop_price']   = price_format($row['shop_price']);
            $goods[$idx]['price']   = $row['shop_price'];
            $goods[$idx]['thumb']        = get_image_path($row['goods_id'], $row['goods_thumb'], true);
            $goods[$idx]['url']          = build_uri('goods', array('gid' => $row['goods_id']), $row['goods_name']);
            $goods[$idx]['short_style_name'] = add_style($goods[$idx]['short_name'], $row['goods_name_style']);
            $idx++;
        }
        $GLOBALS['smarty']->assign('history_goods', $goods);
        $form = (!empty($atts['form'])) ? $atts['form'] : 'library/siy_history.lbi';
        $str= $GLOBALS['smarty']->fetch($form);
    }
    return $str;
}

function siy_nav($atts) {
    $ctype = '';
    $catlist = array();
    $type = (!empty($atts['type']) && in_array($atts['type'], array('top', 'middle', 'bottom', 'home_mobile','header_mobile'))) ? $atts['type'] : 'middle';
    $sql = 'SELECT * FROM '. $GLOBALS['ecs']->table('nav') . '
        WHERE ifshow = "1" AND type = "'.$type.'" ORDER BY vieworder';
    $res = $GLOBALS['db']->query($sql);

    $cur_url = substr(strrchr($_SERVER['REQUEST_URI'],'/'),1);

    if (intval($GLOBALS['_CFG']['rewrite']) && strpos($cur_url, '-')) {
        preg_match('/([a-z]*)-([0-9]*)/',$cur_url,$matches);
        $ctype = ($matches[1] == 'category') ? 'c' : (($matches[1] == 'article_cat') ? 'a' : '');
        $catlist = array($matches[2]);
    }

    $active = 0;
    $navlist = array();
    while ($row = $GLOBALS['db']->fetchRow($res)) {
        $navlist[] = array(
            'name'      =>  $row['name'],
            'opennew'   =>  $row['opennew'],
            'url'       =>  $row['url'],
            'ctype'     =>  $row['ctype'],
            'cid'       =>  $row['cid'],
            'order'     =>  $row['vieworder'],
            'class'     =>  $row['class'],
            'icon'      =>  $row['icon'],
            'rel'       =>  $row['rel']
        );
    }
    foreach($navlist as $k=>$v) {
        $condition = empty($v['ctype']) ? (strpos($cur_url, $v['url']) === 0) : (strpos($cur_url, $v['url']) === 0 && strlen($cur_url) == strlen($v['url']));
        if ($condition) {
            $navlist[$k]['active'] = 1;
            $active += 1;
        }
    }

    if(!empty($ctype) && $active < 1) {
        foreach($catlist as $key => $val) {
            foreach($navlist as $k=>$v) {
                if(!empty($v['ctype']) && $v['ctype'] == $ctype && $v['cid'] == $val && $active < 1) {
                    $navlist[$k]['active'] = 1;
                    $active += 1;
                }
            }
        }
    }

    $nav = array();
    foreach($navlist as $k=>$v) {
        if(strlen(strtr($v['order'], '-', '')) == 1) {
            $nav[] = array(
                'name'      =>  $v['name'],
                'opennew'   =>  $v['opennew'],
                'url'       =>  $v['url'],
                'ctype'     =>  $v['ctype'],
                'cid'       =>  $v['cid'],
                'order'     =>  $v['order'],
                'active'    =>  $v['active'],
                'class'     =>  $v['class'],
                'icon'      =>  $v['icon'],
                'rel'       =>  $v['rel'],
                'children'  =>  _siy_nav_children($navlist, $v['order']),
            );
        }
    }
    $GLOBALS['smarty']->assign('nav', $nav);
    $form = (!empty($atts['form'])) ? $atts['form'] : 'library/siy_nav.lbi';
    $val= $GLOBALS['smarty']->fetch($form);
    return $val;
}

function _siy_nav_children($navlist, $order) {
    foreach($navlist as $k=>$v) {
        if(strlen(strtr($v['order'], '-', '')) == 2 and (substr($v['order'], 0, 1) == $order or substr($v['order'], 0, 2) == $order)) {
            $children[] = array(
                'name'      =>  $v['name'],
                'opennew'   =>  $v['opennew'],
                'url'       =>  $v['url'],
                'ctype'     =>  $v['ctype'],
                'cid'       =>  $v['cid'],
                'order'     =>  $v['order'],
                'active'    =>  $v['active'],
                'class'     =>  $v['class'],
                'icon'      =>  $v['icon'],
                'rel'       =>  $v['rel']
            );
        }
    }
    return $children;
}

function siy_price_format($price, $change_price = true, $price_format = 0)
{
    if ($change_price)
    {
        switch ($price_format)
        {
            case 0:
                $price = preg_replace('/(.*)(\\.)([0-9]{2})$/', '\1<sub>\2\3</sub>', number_format($price, 2, '.', ''));
                break;
            case 1: // 保留不为 0 的尾数
                $price = preg_replace('/(.*)(\\.)([0-9]*?)0+$/', '\1<sub>\2\3</sub>', number_format($price, 2, '.', ''));

                if (substr($price, -12) == '<sub>.</sub>')
                {
                    $price = substr($price, 0, -12);
                }
                break;
            case 2: // 不四舍五入，保留1位
                $price = preg_replace('/(.*)(\\.)([0-9]{1})$/', '\1<sub>\2\3</sub>', substr(number_format($price, 2, '.', ''), 0, -1));
                break;
            case 3: // 直接取整
                $price = intval($price);
                break;
            case 4: // 四舍五入，保留 1 位
                $price = preg_replace('/(.*)(\\.)([0-9]{1})$/', '\1<sub>\2\3</sub>', number_format($price, 1, '.', ''));
                break;
            case 5: // 先四舍五入，不保留小数
                $price = round($price);
                break;
        }
    }
    else
    {
        $price = preg_replace('/(.*)(\\.)([0-9]{2})$/', '\1<sub>\2\3</sub>', number_format($price, 2, '.', ''));
    }

    return sprintf($GLOBALS['_CFG']['currency_format'], $price);
}

function siy_load_user_info($atts) {
    $user_id = !empty($atts['user_id']) ? $atts['user_id'] : $_SESSION['user_id'];
    $user_info = siy_get_user_info($user_id);
    $GLOBALS['smarty']->assign('siy_user_info', $user_info);
}

function siy_get_user_info($user_id) {

    $sql = "SELECT SUM(bt.type_money) AS bonus_value, COUNT(*) AS bonus_count ".
            "FROM " .$GLOBALS['ecs']->table('user_bonus'). " AS ub, ".
                $GLOBALS['ecs']->table('bonus_type') . " AS bt ".
            "WHERE ub.user_id = '$user_id' AND ub.bonus_type_id = bt.type_id AND ub.order_id = 0";
    $user_bonus = $GLOBALS['db']->getRow($sql);

    $sql = "SELECT pay_points, user_money, credit_line, last_login, is_validated FROM " .$GLOBALS['ecs']->table('users'). " WHERE user_id = '$user_id'";
    $row = $GLOBALS['db']->getRow($sql);
    $info = array();
    $info['username']  = stripslashes($_SESSION['user_name']);
    $info['points']  = $row['pay_points'];
    $info['is_validate'] = ($GLOBALS['_CFG']['member_email_validate'] && !$row['is_validated'])?0:1;
    $info['credit_line'] = $row['credit_line'];
    $info['formated_credit_line'] = price_format($info['credit_line'], false);
    $last_time = !isset($_SESSION['last_time']) ? $row['last_login'] : $_SESSION['last_time'];
    if ($last_time == 0) $_SESSION['last_time'] = $last_time = gmtime();
    $info['last_time'] = local_date($GLOBALS['_CFG']['time_format'], $last_time);
    $info['surplus']   = price_format($row['user_money'], false);
    $info['bonus']     = $user_bonus['bonus_count'];
    $info['bonus_value']     = price_format($user_bonus['bonus_value'], false);

    $sql = "SELECT COUNT(*) FROM " .$GLOBALS['ecs']->table('order_info').
            " WHERE user_id = '" .$user_id. "' AND add_time > '" .local_strtotime('-1 months'). "'";
    $info['order_count'] = $GLOBALS['db']->getOne($sql);

    include_once(ROOT_PATH . 'includes/lib_order.php');
    $sql = "SELECT order_id, order_sn ".
            " FROM " .$GLOBALS['ecs']->table('order_info').
            " WHERE user_id = '" .$user_id. "' AND shipping_time > '" .$last_time. "'". order_query_sql('shipped');
    $info['shipped_order'] = $GLOBALS['db']->getAll($sql);

    return $info;
}

function siy_user_orders($atts)
{
    $user_id = !empty($atts['id']) ? intval($atts['id']) : ($_SESSION['user_id'] > 0 ? $_SESSION['user_id'] : 0);
    $number = !empty($atts['number']) ? intval($atts['number']) : 10;
    $start = !empty($atts['start']) ? intval($atts['start']) : 0;
    if ($user_id == '0'){
        return false;
    } else {
        $orders = siy_get_user_orders($user_id, $number, $start);
    }
    $GLOBALS['smarty']->assign('orders', $orders);
    $form = (!empty($atts['form'])) ? $atts['form'] : 'library/siy_order_list.lbi';
    $val= $GLOBALS['smarty']->fetch($form);
    return $val;
}

function siy_get_user_orders($user_id, $number = 10, $start = 0) {
    $arr = array();
    $sql = "SELECT order_id, order_sn, order_status, shipping_status, pay_status, add_time, " .
        "(goods_amount + shipping_fee + insure_fee + pay_fee + pack_fee + card_fee + tax - discount) AS total_fee ".
        " FROM " .$GLOBALS['ecs']->table('order_info') .
        " WHERE user_id = '$user_id' ORDER BY add_time DESC";
    $res = $GLOBALS['db']->SelectLimit($sql, $number, $start);

    while ($row = $GLOBALS['db']->fetchRow($res)) {
        if ($row['order_status'] == OS_UNCONFIRMED) {
            $row['handler'] = "<a href=\"thanh-vien?act=cancel_order&order_id=" .$row['order_id']. "\" onclick=\"if (!confirm('".$GLOBALS['_LANG']['confirm_cancel']."')) return false;\" class=\"button dim_button\"><span>".$GLOBALS['_LANG']['cancel']."</span></a>";
        } else if ($row['order_status'] == OS_SPLITED) {
            if ($row['shipping_status'] == SS_SHIPPED) {
                @$row['handler'] = "<a href=\"thanh-vien?act=affirm_received&order_id=" .$row['order_id']. "\" onclick=\"if (!confirm('".$GLOBALS['_LANG']['confirm_received']."')) return false;\" class=\"button\"><span>".$GLOBALS['_LANG']['received']."</span></a>";
            } elseif ($row['shipping_status'] == SS_RECEIVED) {
                @$row['handler'] = '<span class="status shipping_status_2">'.$GLOBALS['_LANG']['ss_received'] .'</span>';
            } else {
                if ($row['pay_status'] == PS_UNPAYED) {
                    @$row['handler'] = "<a href=\"thanh-vien?act=order_detail&order_id=" .$row['order_id']. '" class="button"><span>' .$GLOBALS['_LANG']['pay_money']. '</span></a>';
                } else {
                    @$row['handler'] = "<a href=\"thanh-vien?act=order_detail&order_id=" .$row['order_id']. '" class="button dim_button"><span>' .$GLOBALS['_LANG']['view_order']. '</span></a>';
                }
            }
        } else {
            $row['handler'] = '<span class="status order_status_'.$row['order_status'].'">'.$GLOBALS['_LANG']['os'][$row['order_status']] .'</span>';
        }

        $row['shipping_status'] = ($row['shipping_status'] == SS_SHIPPED_ING) ? SS_PREPARING : $row['shipping_status'];
        $row['order_status'] = '<em class="order_status_'.$row['order_status'].'">'.$GLOBALS['_LANG']['os'][$row['order_status']].'</em>
                                <em class="pay_status_'.$row['pay_status'].'">'.$GLOBALS['_LANG']['ps'][$row['pay_status']].'</em>
                                <em class="shipping_status_'.$row['shipping_status'].'">'.$GLOBALS['_LANG']['ss'][$row['shipping_status']].'</em>';

        $arr[] = array('order_id'       => $row['order_id'],
            'order_sn'       => $row['order_sn'],
            'order_time'     => local_date($GLOBALS['_CFG']['time_format'], $row['add_time']),
            'order_status'   => $row['order_status'],
            'total_fee'      => price_format($row['total_fee'], false),
            'handler'        => $row['handler']);
    }

    return $arr;
}
function siy_date_format($atts) {
    $date = !empty($atts['date']) ? (is_numeric($atts['date']) ? $atts['date'] : strtotime($atts['date'])) : gmtime();
    $timezone = isset($atts['timezone']) ? $atts['timezone'] : (isset($_SESSION['timezone']) ? $_SESSION['timezone'] : $GLOBALS['_CFG']['timezone']);
    $date += ($timezone * 3600);
    $format = !empty($atts['format']) ? str_replace('&nbsp;', ' ', $atts['format']) : 'Y-m-d H:i:s';
    $date_out = date($format, $date);
    return $date_out;
}
