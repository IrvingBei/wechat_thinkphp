<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2017 http://www.drpeng.com.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: Haijiang Li
// +----------------------------------------------------------------------
// | This is not a free software, unauthorized no use and dissemination.
// +----------------------------------------------------------------------
/**
 * @file            Log.class.php
 * @version         1.0
 * @date            Thu, 14 Sep 2017 13:13:32 GMT
 * @description     This is the service class for data "log"
 */

namespace Admin\Service;

use \Think\Exception;

class Log
{
    /**
     * admin-log
     */
    public static function adminLog()
    {
        $adminRow = Session('admin');
        $data = array(
            'login_id'   => $adminRow['id'],
            'login_name' => $adminRow['name'],
            'login_ip'   => get_client_ip(),
            'login_time' => NOW_TIME,
        );
        return M('AdminLog')->data($data)->add();
    }

    /**
     * order-log
     */
    public static function orderLog($order_id, $desc, $info_before, $info_after, $is_show = true) {
        $logAttr = array(
            'order_id' => $order_id,
            'desc' => $desc,
            'info_before' => $info_before,
            'info_after' => $info_after,
            'is_show' => intval($is_show),
            'operator_id' => session(C('USER_AUTH_KEY')),
            'operator_name' => session('admin.name'),
            'created_at' =>  time(),
        );
        $Model = M();
        $n = $Model->table("xp_order_log")->add($logAttr);
        if (!$n) {
            throw new Exception('Order操作日志记录失败！'.json_encode($logAttr), 6001);
        }
    }

    /**
     * product-log
     */
    public static function productLog($product_id, $desc, $info_before, $info_after,$is_show = true) {
        $time = time();
        $logAttr = array(
            'product_id' => $product_id,
            'desc' => $desc,
            'created_at' => $time,
            'info_before' => $info_before,
            'info_after' => $info_after,
            'is_show' => intval($is_show),
            'operator_id' => session(C('USER_AUTH_KEY')),
            'operator_name' => session('admin.name'),
        );
        $n = M("product_log")->add($logAttr);
        if (!$n) {
            throw new Exception('Product操作日志记录失败！', 4001);
        }
    }

    /**
     * coupon-log
     */
    public static function couponLog($coupon_id, $desc, $info_before, $info_after) {
        $time = time();
        $logAttr = array(
            'coupon_id' => $coupon_id,
            'desc' => $desc,
            'add_time' => $time,
            'info_before' => $info_before,
            'info_after' => $info_after,
            'operator_id' => session(C('USER_AUTH_KEY')),
            'operator_name' => session('admin.name'),
        );
        $n = M("coupon_log")->add($logAttr);
        if (!$n) {
            throw new Exception('Coupon操作日志记录失败！', 5001);
        }
    }

    /**
     * activity-log
     */
    public static function activityLog($activity_id, $desc, $info_before, $info_after) {
        $time = time();
        $logAttr = array(
            'activity_id' => $activity_id,
            'desc' => $desc,
            'add_time' => $time,
            'info_before' => $info_before,
            'info_after' => $info_after,
            'operator_id' => session(C('USER_AUTH_KEY')),
            'operator_name' => session('admin.name'),
        );

        $n = M("activity_log")->add($logAttr);
        if (!$n) {
            throw new Exception('Activity操作日志记录失败！', 5001);
        }
    }


    /**
     * wechat-log
     */
    public static function weixinLog($function_name,$public_name, $desc, $info_before, $info_after) {
        $time = time();
        $logAttr = array(
            'function_name' => $function_name,
            'public_name' => $public_name,
            'desc' => $desc,
            'created_at' => $time,
            'info_before' => $info_before,
            'info_after' => $info_after,
            'operator_id' => session(C('USER_AUTH_KEY')),
            'operator_name' => session('admin.name'),
        );

        $n = M("log")->add($logAttr);
        if (!$n) {
            throw new Exception('Activity操作日志记录失败！', 5001);
        }
    }
}
