<?php

// +----------------------------------------------------------------------
// | Copyright (c) 2017 http://www.drpeng.com.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: Haijiang Li <lihaijiang.1989@gmail.com>
// +----------------------------------------------------------------------
// | This is not a free software, unauthorized no use and dissemination.
// +----------------------------------------------------------------------
/**
 * @file            WechatController.class.php
 * @version         1.0
 * @date            Fri, 23 Feb 2018 11:12:07 GMT
 * @description     This is the controller class for data "user"
 */

namespace Admin\Controller;

use \Org\Util\Page;
use Common\Controller\AdminController;

class WxScanController extends AdminController
{
    public function index(){

        C('DB_PREFIX', 'xp_weixin_');

        $params = I('get.');

        $page_row = C('PAGE_SIZE');
        $page = isset($params['p']) ? intval($params['p']) : 1;

        $token = $filter['token'] = isset($params['token']) ? trim($params['token']) : '';
        $key = $filter['event_key_data'] = isset($params['event_key_data']) ? trim($params['event_key_data']) : '';
        $event = $filter['event'] = isset($params['event']) ? trim($params['event']) : '';

        $where = " type = 0 ";
        $where .= " and ticket is not null ";
        if(!empty($filter['token'])){
            $where .= " and token = '$token' ";
        }
        if(!empty($key)){
            $where .= " and event_key_data = '$key' ";
        }
        if(!empty($event)){
            $where .= " and event = '$event' ";
        }


        $sql = "SELECT
                    FROM_UNIXTIME( create_time, '%Y-%m-%d' ) time_day,
                    event_key_data,
                    count(*) access_num
                FROM
                    xp_weixin_qrcode_record 
                WHERE
                    $where
                GROUP BY time_day,event_key_data";

        $count = count(M()->query($sql));

        $Page = new Page($count, $page_row);
        $show = $Page->show();

        $first =  ($page_row * ($page - 1));
        $last = $page_row - 1;

        $listArr = M()->query($sql." limit ".$first.",".$last);
        $this->assign('filter', $filter);

        $list_data ['list_data'] = $listArr;

        $this->assign('pages', $show);
        $this->assign('page_row', $page_row);
        $this->assign ( $list_data );

        $sql2 = 'SELECT DISTINCT event_key_data FROM xp_weixin_qrcode_record WHERE type = 0 and ticket is not null';
        $event_key = M()->query($sql2);
        $this->assign('event_key', $event_key);

        $apps = M('apps')->field(['token','public_name'])->select();
        $this->assign('apps', $apps);

        $this->display();

    }



}

?>