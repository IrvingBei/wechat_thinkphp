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
use Admin\Service\Log;

class WechatController extends WechatBaseController
{
    //公众号列表
    public function index(){
        $listArr = M('apps')->field(['id','public_name','token','appid','hot_line'])->order('id')->select();
        $list_data ['list_data'] = $listArr;
        $this->assign ( $list_data );
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
            $data['hot_line'] = isset($params['hot_line']) ? trim($params['hot_line']) : '';

            $info_after = $data;
            unset($info_after['__hash__']);
            $info_after = json_encode(($info_after),JSON_UNESCAPED_UNICODE);
            if($appsModel->create()){
                if($create){
                    $desc ='添加公众号数据';
                    if(!$appsModel->add($data)){
                        throw new Exception('公众号添加失败-'.$appsModel->getError(), 6001);
                    }
                }else{
                    $desc ='修改公众号数据';
                    $where['id'] = $data['id'];

                    //修改公众号数据，清空redis缓存
                    $token = $data['token'];
                    $key = 'wechat:config:'.$token;
                    S($key,null);

                    $info_before = json_encode($appsModel->where($where)->find(),JSON_UNESCAPED_UNICODE);
                    if($appsModel->save($data) === false){
                        throw new Exception('公众号更新失败-'.$appsModel->getError(), 6001);
                    }
                }
            }
            $function_name = "Wechat";
            Log::weixinLog($function_name, $this->public_name,$desc, $info_before, $info_after);
            $this->success("保存成功",U('index'));
            die;
        }
        $this->display();
    }

    public function log(){
        $params = I('get.');
        $page_row = C('PAGE_SIZE');
        $page = isset($params['p']) ? intval($params['p']) : 0;

        $where = "1=1";

        $filter['log_type'] = isset($params['log_type']) ? intval($params['log_type']) : 0;
        if($filter['log_type'] > 0){
            $where .= " and log_type = '".$filter['log_type']."'";
        }else{
            $where .= " and log_type = 2 ";
        }

        $count = M('debug_log')->where($where)->count();
        $Page = new Page($count, $page_row);
        $show = $Page->show();

        $listArr = M('debug_log')->where($where)
            ->page($page . ", $page_row")
            ->order('create_time desc')
            ->select();
        $list_data ['list_data'] = $listArr;
        $this->assign('filter', $filter);
        $this->assign('pages', $show);
        $this->assign('page_row', $page_row);
        $this->assign ( $list_data );
        $this->display();
    }

}

?>