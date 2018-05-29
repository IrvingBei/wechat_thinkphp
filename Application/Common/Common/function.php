<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2017 http://www.drpeng.com.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: Haijiang Li <lihaijiang.1989@gmail.com>
// +----------------------------------------------------------------------
// | This is not a free software, unauthorized no use and dissemination.
// +----------------------------------------------------------------------
/**
 * @file            Functions.php
 * @version         1.0
 * @date            Thu, 26 Oct 2017 11:34:46 GMT
 * @description     公共函数库
 */


function add_debug_log($data, $data_post = '', $log_type = 1) {
    $log ['create_time'] = time();
    $log ['log_type'] = $log_type;
    //$log ['data'] = is_array ( $data ) ? var_export ( $data, true ) : $data;
    $log ['data'] = is_array ( $data ) ? json_encode($data, JSON_UNESCAPED_UNICODE) : $data;
    if (strlen ( $log ['data'] >= 65535 )) {
        $log ['data'] = substr ( $log ['data'], 0, 65530 );
    }
    $log ['data_post'] = is_array ( $data_post ) ? var_export ( $data_post, true ) : $data_post;
    if (strlen ( $log ['data_post'] >= 65535 )) {
        $log ['data_post'] = substr ( $log ['data_post'], 0, 65530 );
    }

    $res = M ( 'debug_log' )->add ( $log );
}


