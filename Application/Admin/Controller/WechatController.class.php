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
use \Think\Exception;

class WechatController extends WechatBaseController
{
    //公众号列表
    public function index(){
        $listArr = M('apps')->field(['id','public_name','token','appid'])->order('id')->select();
        $list_data ['list_data'] = $listArr;
        $this->assign ( $list_data );
        $this->display();
    }

    public function main(){
        $this->display();
    }

    public function info(){


        $params = I();
        $appsModel = M('apps');
        $create = true;
        $data = array();
        //新建还是编辑
        if(isset($params['id']) && !empty($params['id'])){
            $create = false;

            $apps = $appsModel->find($params['id']);
            if (empty($apps)) {
                $this->error('信息有误，请重新操作！',U('index'));
            }
            $this->assign('apps', $apps);
            //规则类型，文本消息自动回复，关注自动回复，扫码自动回复，点击事件自动回复

            $data['id'] = $params['id'];
            $data['updated_at'] = time();
            $data['operator_id'] = Session('admin')['id'];
            $data['operator_name'] = Session('admin')['name'];

        }else{
            $data['operator_id'] = Session('admin')['id'];
            $data['operator_name'] = Session('admin')['name'];
            $data['created_at'] = time();
            $data['updated_at'] = time();
        }

        if(IS_POST){
            $data['public_name'] = isset($params['public_name']) ? trim($params['public_name']) : '';
            $data['token'] = $data['public_id'] = isset($params['public_id']) ? trim($params['public_id']) : '';
            $data['appid'] = isset($params['appid']) ? trim($params['appid']) : '';
            $data['appsecret'] = isset($params['appsecret']) ? trim($params['appsecret']) : '';

            if($appsModel->create()){
                if($create){
                    if(!$appsModel->add($data)){
                        throw new Exception('公众号添加失败-'.$appsModel->getError(), 6001);
                    }
                }else{
                    if($appsModel->save($data) === false){
                        throw new Exception('公众号更新失败-'.$appsModel->getError(), 6001);
                    }
                }
            }

            $this->success("保存成功",U('index'));
            die;
        }
        $this->display();
    }

}

?>