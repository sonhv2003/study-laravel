<?php

/**
 * ECSHOP 支付接口函数库
 * ============================================================================
 * 版权所有 2005-2018 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: yehuaixiao $
 * $Id: lib_payment.php 17218 2011-01-24 04:10:41Z yehuaixiao $
 */

if (!defined('IN_ECS'))
{
    die('Hacking attempt');
}

/**
 * 取得返回信息地址
 * @param   string  $code   支付方式代码
 */
function return_url($code)
{
    return $GLOBALS['ecs']->url() . 'callback_payment?code='. $code;
}

/**
 *  取得某支付方式信息
 *  @param  string  $code   支付方式代码
 */
function get_payment($code)
{
    $sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('payment').
        " WHERE pay_code = '$code' AND enabled = '1'";
    $payment = $GLOBALS['db']->getRow($sql);

    if ($payment)
    {
        $config_list = unserialize($payment['pay_config']);

        foreach ($config_list AS $config)
        {
            $payment[$config['name']] = $config['value'];
        }
    }

    return $payment;
}

/**
 *  通过订单sn取得订单ID
 *  @param  string  $order_sn   订单sn
 *  @param  blob    $voucher    是否为会员充值
 */
function get_order_id_by_sn($order_sn, $voucher = 'false')
{
    if ($voucher == 'true')
    {
        if(is_numeric($order_sn))
        {
            return $GLOBALS['db']->getOne("SELECT log_id FROM " . $GLOBALS['ecs']->table('pay_log') . " WHERE order_id=" . $order_sn . ' AND order_type=1');
        }
        else
        {
            return "";
        }
    }
    else
    {
        if(is_numeric($order_sn))
        {
            $sql = 'SELECT order_id FROM ' . $GLOBALS['ecs']->table('order_info'). " WHERE order_sn = '$order_sn'";
            $order_id = $GLOBALS['db']->getOne($sql);
        }
        if (!empty($order_id))
        {
            $pay_log_id = $GLOBALS['db']->getOne("SELECT log_id FROM " . $GLOBALS['ecs']->table('pay_log') . " WHERE order_id='" . $order_id . "'");
            return $pay_log_id;
        }
        else
        {
            return "";
        }
    }
}

/**
 *  通过订单ID取得订单商品名称
 *  @param  string  $order_id   订单ID
 */
function get_goods_name_by_id($order_id)
{
    $sql = 'SELECT goods_name FROM ' . $GLOBALS['ecs']->table('order_goods'). " WHERE order_id = '$order_id'";
    $goods_name = $GLOBALS['db']->getCol($sql);
    return implode(',', $goods_name);
}

/**
 * 检查支付的金额是否与订单相符
 *
 * @access  public
 * @param   string   $log_id      支付编号
 * @param   float    $money       支付接口返回的金额
 * @return  true
 */
function check_money($log_id, $money)
{
    if(is_numeric($log_id))
    {
        $sql = 'SELECT order_amount FROM ' . $GLOBALS['ecs']->table('pay_log') .
            " WHERE log_id = '$log_id'";
        //$sql = "SELECT o.order_amount FROM ".$GLOBALS['ecs']->table('order_info')." as o left join ".$GLOBALS['ecs']->table('pay_log')." as p on o.order_id=p.order_id WHERE p.log_id = '".$log_id."'";
        $amount = $GLOBALS['db']->getOne($sql);
    }
    else
    {
        return false;
    }
    if ($money == $amount)
    {
        return true;
    }
    else
    {
        return false;
    }
}

/**
 * 修改订单的支付状态
 *
 * @access  public
 * @param   string  $log_id     支付编号
 * @param   integer $pay_status 状态
 * @param   string  $note       备注
 * @return  void
 */
function order_paid($log_id, $pay_status = PS_PAYED, $note = '')
{

    addlog('pay_status.log', date("c")."\t".'log_id:'.$log_id.";pay_status:".$pay_status.";\n\n");

    /* 取得支付编号 */
    $log_id = intval($log_id);
    if ($log_id > 0)
    {
        /* Xem đơn này đã thanh toán chưa */
        $sql = "SELECT * FROM " . $GLOBALS['ecs']->table('pay_log') .
            " WHERE log_id = '$log_id'";
        $pay_log = $GLOBALS['db']->getRow($sql);

        /* Nếu chưa thanh toán */
        if ($pay_log && $pay_log['is_paid'] == 0)
        {
            echo 'chưa thanh toan';
            /* Set đã thanh toán */
            $sql = 'UPDATE ' . $GLOBALS['ecs']->table('pay_log') .
                " SET is_paid = '1' WHERE log_id = '$log_id'";
            $GLOBALS['db']->query($sql);

            /* 根据记录类型做相应处理 */
            if ($pay_log['order_type'] == PAY_ORDER)
            {
                /* 取得订单信息 */
                $sql = 'SELECT order_id, user_id, order_sn, consignee, address, tel, shipping_id, extension_code, extension_id, goods_amount, order_amount, pay_id ' .
                    'FROM ' . $GLOBALS['ecs']->table('order_info') .
                    " WHERE order_id = '$pay_log[order_id]'";
                $order    = $GLOBALS['db']->getRow($sql);
                $order_id = $order['order_id'];
                $order_sn = $order['order_sn'];

                /* 修改订单状态为已付款 */
                $sql = 'UPDATE ' . $GLOBALS['ecs']->table('order_info') .
                    " SET order_status = '" . OS_CONFIRMED . "', " .
                    " confirm_time = '" . gmtime() . "', " .
                    " pay_status = '$pay_status', " .
                    " pay_time = '".gmtime()."', " .
                    " money_paid = order_amount," .
                    " order_amount = 0, ".
                    " lastmodify = '".gmtime()."' ".
                    "WHERE order_id = '$order_id'";
                $GLOBALS['db']->query($sql);

                /* cập nhật trạng thái đơn hàng */
                order_action($order_sn, OS_CONFIRMED, SS_UNSHIPPED, $pay_status, $note, $GLOBALS['_LANG']['buyer']);

                /* SMS. Xem Xét gửi Mã với SMS thanh toan 1 lần */
                if ($GLOBALS['_CFG']['sms_order_payed'] == '1'  && $GLOBALS['_CFG']['sms_shop_mobile'] != ''){
                    $phones = array($GLOBALS['_CFG']['sms_shop_mobile']);
                    include_once(ROOT_PATH.'includes/SpeedSMSAPI.v2.php');
                    $content = sprintf($GLOBALS['_LANG']['order_payed_sms'], $order_sn, $note, $order['consignee'], $order['tel']);
                    $sms = new SpeedSMSAPI();
                    @$sms->sendSMS($phones, $content, SpeedSMSAPI::SMS_TYPE_CSKH, $GLOBALS['_CFG']['shop_name']);
                }

                /* Lấy danh sách sản phẩm ảo, $order_sn */
                /**
                 * @param $order_id
                 * @param $shipping = false
                 * @param $order_sn
                 * @var [type]
                 */
                $virtual_goods = get_virtual_goods($order_id,false);
                if (!empty($virtual_goods))
                {
                    $msg = '';
                    /**
                     * Lam sao de virtual_goods_ship tra ve
                     *  Danh sach ma the ==> de gui mail, Sms
                     *  Check so luong the, co du ton kho ko
                     *  va co trang thai phu hop
                     */
                    if (!virtual_goods_ship($virtual_goods, $msg, $order_sn, true))
                    {
                        $GLOBALS['_LANG']['pay_success'] .= '<div style="color:red;">'.$msg.'</div>'.$GLOBALS['_LANG']['virtual_goods_ship_fail'];
                    }

                    /* 如果订单没有配送方式，自动完成发货操作 */
                    if ($order['shipping_id'] == -1)
                    {
                        /* 将订单标识为已发货状态，并记录发货记录 */
                        $sql = 'UPDATE ' . $GLOBALS['ecs']->table('order_info') .
                            " SET shipping_status = '" . SS_SHIPPED . "', shipping_time = '" . gmtime() . "'" .
                            " WHERE order_id = '$order_id'";
                        $GLOBALS['db']->query($sql);

                        /* 记录订单操作记录 */
                        order_action($order_sn, OS_CONFIRMED, SS_SHIPPED, $pay_status, $note, $GLOBALS['_LANG']['buyer']);
                        $integral = integral_to_give($order);
                        log_account_change($order['user_id'], 0, 0, intval($integral['rank_points']), intval($integral['custom_points']), sprintf($GLOBALS['_LANG']['order_gift_integral'], $order['order_sn']));
                    }else{
                        $pay_code = $GLOBALS['db']->getOne("SELECT pay_code FROM ".$GLOBALS['ecs']->table('payment')." WHERE pay_id='".$order['pay_id']."'");
                        $pay_code || $pay_code = '';
                        log_account_other_change($order['user_id'], $order['order_id'], $order['order_sn'], $order['order_amount'], $pay_code, gmtime());
                    }
                }else{
                    $pay_code = $GLOBALS['db']->getOne("SELECT pay_code FROM ".$GLOBALS['ecs']->table('payment')." WHERE pay_id='".$order['pay_id']."'");
                    $pay_code || $pay_code = '';
                    log_account_other_change($order['user_id'], $order['order_id'], $order['order_sn'], $order['order_amount'], $pay_code, gmtime());
                }

                
            }
            elseif ($pay_log['order_type'] == PAY_SURPLUS)
            {
                $sql = 'SELECT `id` FROM ' . $GLOBALS['ecs']->table('user_account') .  " WHERE `id` = '$pay_log[order_id]' AND `is_paid` = 1  LIMIT 1";
                $res_id=$GLOBALS['db']->getOne($sql);
                if(empty($res_id))
                {
                    /* 更新会员预付款的到款状态 */
                    $sql = 'UPDATE ' . $GLOBALS['ecs']->table('user_account') .
                        " SET paid_time = '" .gmtime(). "', is_paid = 1" .
                        " WHERE id = '$pay_log[order_id]' LIMIT 1";
                    $GLOBALS['db']->query($sql);

                    /* 取得添加预付款的用户以及金额 */
                    $sql = "SELECT user_id, amount FROM " . $GLOBALS['ecs']->table('user_account') .
                        " WHERE id = '$pay_log[order_id]'";
                    $arr = $GLOBALS['db']->getRow($sql);

                    /* 修改会员帐户金额 */
                    $_LANG = array();
                    include_once(ROOT_PATH . 'languages/' . $GLOBALS['_CFG']['lang'] . '/user.php');
                    log_account_change($arr['user_id'], $arr['amount'], 0, 0, 0, $_LANG['surplus_type_0'], ACT_SAVING);

                    $order = $GLOBALS['db']->getRow("select order_sn FROM ".$GLOBALS['ecs']->table('order_info')." WHERE order_id=".$pay_log['order_id']);
                    
                }
            }
        }
        /* Nếu Đã thanh toán */
        else
        {
            echo 'da thanh toan';
            /* lấy lại mã ảo */
            $post_virtual_goods = get_virtual_goods($pay_log['order_id'], true);

            /* Hàng hóa ảo được vận chuyển */
            if (!empty($post_virtual_goods))
            {
                $msg = '';
                /* 检查两次刷新时间有无超过12小时 */
                $sql = 'SELECT pay_time, order_sn FROM ' . $GLOBALS['ecs']->table('order_info') . " WHERE order_id = '$pay_log[order_id]'";
                $row = $GLOBALS['db']->getRow($sql);
                $intval_time = gmtime() - $row['pay_time'];
                if ($intval_time >= 0 && $intval_time < 3600 * 12)
                {
                    $virtual_card = array();
                    foreach ($post_virtual_goods as $code => $goods_list)
                    {
                        /* 只处理虚拟卡 */
                        if ($code == 'virtual_card')
                        {
                            foreach ($goods_list as $goods)
                            {
                                if ($info = virtual_card_result($row['order_sn'], $goods))
                                {
                                    $virtual_card[] = array('goods_id'=>$goods['goods_id'], 'goods_name'=>$goods['goods_name'], 'info'=>$info);
                                }
                            }

                            $GLOBALS['smarty']->assign('virtual_card',      $virtual_card);
                        }
                    }
                    /**
                     * Gửi mail + SMS nếu có
                     */
                }
                else
                {
                    $msg = '<div>' .  $GLOBALS['_LANG']['please_view_order_detail'] . '</div>';
                }

                $GLOBALS['_LANG']['pay_success'] .= $msg;
            }

            /* 取得未发货虚拟商品 */
            $virtual_goods = get_virtual_goods($pay_log['order_id'], false);
            if (!empty($virtual_goods))
            {
                $GLOBALS['_LANG']['pay_success'] .= '<br />' . $GLOBALS['_LANG']['virtual_goods_ship_fail'];
            }
        }
    }
}

?>
