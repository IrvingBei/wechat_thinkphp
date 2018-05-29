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

use Admin\Model\WechatModel;
use \Org\Util\Page;
use \Think\Exception;
use \Admin\Service\Helper;
use Admin\Service\Log;

class WxCustomQrController extends WechatBaseController
{
    public function index(){


        $token = $this->token;
        $where['token'] = $token;
        $listArr = M('custom_qrcode')->where($where)->field(['scene_str','activity_name','ticket','id'])->select();
        $list_data ['list_data'] = $listArr;
        $this->assign ( $list_data );
        $this->display();

    }

    public function info(){
        if(IS_POST){
            $params = I();
            $QRModel = M('custom_qrcode');
            $data['scene_str'] = isset($params['scene_str']) ? trim($params['scene_str']) : '';
            $data['activity_name'] = isset($params['activity_name']) ? trim($params['activity_name']) : '';
            $data['token'] = $this->token;
            $time = time();
            $data['created_at'] = $time;
            $data['updated_at'] = $time;
            $data['operator_id'] = Session('admin')['id'];
            $data['operator_name'] = Session('admin')['name'];

            if($QRModel->create()){

                $where['token'] = $data['token'];
                $where['scene_str'] = $data['scene_str'];
                $count = $QRModel->where($where)->count();
                if($count){
                    //已存在参数，不再添加
                    $this->error("二维码参数已经存在，请勿重复添加",U('index',array('wpid'=>$this->wpid)));
                    die;
                }
                $Wechat = new WechatModel($data['token']);
                $res = $Wechat->createQrCode($data['scene_str']);
                if(!empty($res['ticket'])){
                    $data['ticket'] = $res['ticket'];
                    if(!$QRModel->add($data)){
                        throw new Exception('二维码添加失败-'.$QRModel->getError(), 6001);
                    }
                    $function_name = 'CustomQr';
                    $desc = '添加自定义二维码';
                    $info_after = $data;
                    unset($info_after['__hash__']);
                    $info_after = json_encode(($info_after),JSON_UNESCAPED_UNICODE);
                    Log::weixinLog($function_name, $this->public_name,$desc, null, $info_after);
                    $this->success("保存成功",U('index',array('wpid'=>$this->wpid)));
                    die;
                }else{
                    throw new Exception('二维码添加失败-'.$QRModel->getError(), 6002);
                }

            }

            throw new Exception('二维码添加失败-'.$QRModel->getError(), 6003);

        }
        $this->display();

    }

}

?>