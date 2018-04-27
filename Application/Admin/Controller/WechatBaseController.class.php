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

use Common\Controller\AdminController;
use \Org\Util\Page;
use \Think\Exception;

class WechatBaseController extends AdminController
{


    protected $token;
    protected $wpid;

    /**
     * [_initialize description]
     */
    public function __construct()
    {
        parent::__construct();
        C('DB_PREFIX', 'xp_weixin_');
        $params = I();
        $wpid = $params['wpid'];
        $this->wpid = $wpid;
        if(!empty($wpid)){
            $key = 'wpid_'.$wpid;
            $wx_info = S($key);
            if(empty($wx_info)){
                $wx_info = M('apps')->field(['id','token','public_name'])->find($wpid);
                S($key,$wx_info);
            }

            $this->token = $wx_info['token'];
            $this->assign ('public_name', $wx_info['public_name']);
            $this->assign ('wpid', $wx_info['id']);
            $this->assign ('token', $wx_info['token']);

            $url = U('Wechat/index');
            $this->assign ('back_url', $url);
        }
    }

    function _check_text_content($content) {
        if (empty ( $content )) {
            $this->error ( '110137:文本内容不能为空' );
        }
        if (strlen ( $content ) > 2048) {
            $this->error ( '110139:文本内容不超过600个字' );
        }
    }

}

?>