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
use \Admin\Service\Helper;

class WxAutoReplyController extends WechatBaseController
{

    public function __construct()
    {
        parent::__construct();
        $addonList[] = array('addons'=>'AutoReply','addonsName'=> '文本消息自动回复');
        $addonList[] = array('addons'=>'Welcome','addonsName'=> '关注自动回复');
        $addonList[] = array('addons'=>'Scan','addonsName'=> '扫码自动回复');
        $addonList[] = array('addons'=>'Click','addonsName'=> '点击事件自动回复');
        $addonList[] = array('addons'=>'NoAnswer','addonsName'=> '无应答自动回复');
        $this->assign ( 'addonList', $addonList );
    }

    public function text(){


        $token = $this->token;
        $where['token'] = $token;
        $where['msg_type'] = 'text';

        $params = I('get.');

        $page_row = C('PAGE_SIZE');
        $page = isset($params['p']) ? intval($params['p']) : 0;


        $filter['addons'] = isset($params['addons']) ? trim($params['addons']) : '';
        if(!empty($filter['addons'])){
            $where['addons'] = $filter['addons'];
        }
        $filter['status'] = isset($params['status']) ? intval($params['status']) : 0;
        if($filter['status']){
            $where['status'] = $filter['status'];
        }

        $this->assign('filter', $filter);

        $count = M('auto_reply')->where($where)->count();
        $Page = new Page($count, $page_row);
        $show = $Page->show();

        $listArr = M('auto_reply')->where($where)
            ->page($page . ", $page_row")
            ->select();
        $list_data ['list_data'] = $listArr;

        $this->assign('pages', $show);
        $this->assign('page_row', $page_row);
        $this->assign ( $list_data );

        $this->display();

    }

    public function textInfo(){

        $params = I();
        $materialTextModel = M('auto_reply');
        $create = true;
        $data = array();
        //新建还是编辑
        if(isset($params['id']) && !empty($params['id'])){
            $create = false;

            $materialTextRow = $materialTextModel->find($params['id']);
            if (empty($materialTextRow)) {
                $this->error('信息有误，请重新操作！',U('text',array('wpid'=>$this->wpid)));
            }
            $this->assign('materialTextRow', $materialTextRow);
            //规则类型，文本消息自动回复，关注自动回复，扫码自动回复，点击事件自动回复

            $data['id'] = $params['id'];
            $data['updated_at'] = time();
            $data['operator_id'] = Session('admin')['id'];
            $data['operator_name'] = Session('admin')['name'];

        }else{
            $data['msg_type'] = 'text';
            $data['operator_id'] = Session('admin')['id'];
            $data['operator_name'] = Session('admin')['name'];
            $data['created_at'] = time();
            $data['updated_at'] = time();
        }

        if(IS_POST){
            $data['content'] = isset($params['content']) ? trim($params['content']) : '';

            $data['addons'] = isset($params['addons']) ? trim($params['addons']) : '';
            if($data['addons'] == 'Welcome'){
                $data['keyword'] = 'Welcome';
            }else{
                $data['keyword'] = isset($params['keyword']) ? trim($params['keyword']) : '';
            }
            $data['sort'] = isset($params['sort']) ? intval($params['sort']) : 0;
            $data['status'] = isset($params['status']) ? intval($params['status']) : 2;

            $data['token'] = $this->token;

            $this->_check_text_content($data['content']);

            if($materialTextModel->create()){
                if($create){
                    if(!$materialTextModel->add($data)){
                        throw new Exception('文本消息添加失败-'.$materialTextModel->getError(), 6001);
                    }
                }else{
                    if($materialTextModel->save($data) === false){
                        throw new Exception('文本消息更新失败-'.$materialTextModel->getError(), 6001);
                    }
                }
            }

            $this->success("保存成功",U('text',array('wpid'=>$this->wpid)));
            die;
        }
        $this->display();

    }

    public function image(){

        $Model = M();
        $token = $this->token;
        $params = I('get.');
        $page_row = C('PAGE_SIZE');
        $page = isset($params['p']) ? intval($params['p']) : 0;

        $where = "ar.token = '$token' and ar.msg_type = 'image' ";

        $filter['addons'] = isset($params['addons']) ? trim($params['addons']) : '';
        if(!empty($filter['addons'])){
            $addons = $filter['addons'];
            $where .= " and ar.addons = '$addons' ";
        }

        $filter['status'] = isset($params['status']) ? intval($params['status']) : 0;
        if($filter['status']){
            $status = $filter['status'];
            $where .= " and ar.status = '$status' ";
        }

        $this->assign('filter', $filter);

        $count = $Model->table("xp_weixin_auto_reply ar")
            ->where("$where")->count();
        $Page = new Page($count, $page_row);
        $show = $Page->show();

        $listArr = $Model->table("xp_weixin_auto_reply ar")
            ->join("left join xp_weixin_material_image mi on ar.image_material = mi.id")
            ->field("ar.*,mi.id mid,mi.cover_url,mi.media_id,mi.wechat_url")
            ->where("$where")
            ->page($page . ", $page_row")
            //->order("t.created_at desc")
            ->select();
        $list_data ['list_data'] = $listArr;

        //echo M()->getLastSql();
        //print_r($list_data);


        $this->assign('pages', $show);
        $this->assign('page_row', $page_row);
        $this->assign ( $list_data );
        $this->display();
    }

    public function imageInfo(){


        $params = I();
        $time = time();
        $data = array();
        $update = false;//是更新还是插入标记
        $Model = M('auto_reply');

        //根据是否传入id来判断是新建还是编辑
        if(isset($params['id']) && !empty($params['id'])){
            $id = trim($params['id']);
            $auto_reply = $Model->find($id);
            $this->assign('auto_reply', $auto_reply);
            $data['id'] = $params['id'];
            $data['updated_at'] = $time;
            $data['operator_id'] = Session('admin')['id'];
            $data['operator_name'] = Session('admin')['name'];
            $update = true;
        }else{
            $data['msg_type'] = 'image';
            $data['created_at'] = $time;
            $data['updated_at'] = $time;
            $data['operator_id'] = Session('admin')['id'];
            $data['operator_name'] = Session('admin')['name'];
        }

        if(IS_POST) {

            $data['token'] = $this->token;

            $data['addons'] = isset($params['addons']) ? trim($params['addons']) : '';
            if($data['addons'] == 'Welcome'){
                $data['keyword'] = 'Welcome';
            }else{
                $data['keyword'] = isset($params['keyword']) ? trim($params['keyword']) : '';
            }
            $data['sort'] = isset($params['sort']) ? intval($params['sort']) : 0;

            $data['image_material'] = isset($params['image_material']) ? intval($params['image_material']) : 0;;
            $data['status'] = isset($params['status']) ? intval($params['status']) : 2;

            if ($Model->create()) {
                if ($update) {
                    if (!$Model->save($data)) {
                        throw new Exception('图片消息更新失败-' . $Model->getError(), 6001);
                    }
                } else {
                    if (!$Model->add($data)) {
                        throw new Exception('图片消息添加失败-' . $Model->getError(), 6001);
                    }
                }
            } else {
                throw new Exception('数据校验失败-' . $Model->getError(), 6001);
            }

            $this->success('添加图片消息操作成功', U('image',array('wpid'=>$this->wpid)));
            die;

        }
        $this->display();
    }


    public function news(){

        $Model = M();
        $token = $this->token;
        $params = I('get.');
        $page_row = C('PAGE_SIZE');
        $page = isset($params['p']) ? intval($params['p']) : 0;

        $where = "ar.token = '$token' and ar.msg_type = 'news' ";

        $filter['addons'] = isset($params['addons']) ? trim($params['addons']) : '';
        if(!empty($filter['addons'])){
            $addons = $filter['addons'];
            $where .= " and ar.addons = '$addons' ";
        }

        $filter['status'] = isset($params['status']) ? intval($params['status']) : 0;
        if($filter['status']){
            $status = $filter['status'];
            $where .= " and ar.status = '$status' ";
        }

        $this->assign('filter', $filter);

        $count = $Model->table("xp_weixin_auto_reply ar")
            ->where("$where")->count();
        $Page = new Page($count, $page_row);
        $show = $Page->show();

        $listArr = $Model->table("xp_weixin_auto_reply ar")
            ->join("left join xp_weixin_material_news mn on ar.group_id = mn.id")
            ->field("ar.*,mn.id mid,mn.title,mn.intro,mn.url,mn.pic_url,mn.link")
            ->where("$where")
            ->page($page . ", $page_row")
            //->order("t.created_at desc")
            ->select();
        $list_data ['list_data'] = $listArr;

        //echo M()->getLastSql();
        //print_r($list_data);


        $this->assign('pages', $show);
        $this->assign('page_row', $page_row);
        $this->assign ( $list_data );
        $this->display();

    }



    public function newsInfo(){


        $params = I();
        $time = time();
        $data = array();
        $update = false;//是更新还是插入标记
        $Model = M('auto_reply');

        //根据是否传入id来判断是新建还是编辑
        if(isset($params['id']) && !empty($params['id'])){
            $id = trim($params['id']);
            $auto_reply = $Model->find($id);
            $this->assign('auto_reply', $auto_reply);
            $data['id'] = $params['id'];
            $data['updated_at'] = $time;
            $data['operator_id'] = Session('admin')['id'];
            $data['operator_name'] = Session('admin')['name'];
            $update = true;
        }else{
            $data['msg_type'] = 'news';
            $data['created_at'] = $time;
            $data['updated_at'] = $time;
            $data['operator_id'] = Session('admin')['id'];
            $data['operator_name'] = Session('admin')['name'];
        }

        if(IS_POST) {

            $data['token'] = $this->token;

            $data['addons'] = isset($params['addons']) ? trim($params['addons']) : '';
            if($data['addons'] == 'Welcome'){
                $data['keyword'] = 'Welcome';
            }else{
                $data['keyword'] = isset($params['keyword']) ? trim($params['keyword']) : '';
            }
            $data['sort'] = isset($params['sort']) ? intval($params['sort']) : 0;

            $data['group_id'] = isset($params['group_id']) ? intval($params['group_id']) : 0;;
            $data['status'] = isset($params['status']) ? intval($params['status']) : 2;

            if ($Model->create()) {
                if ($update) {
                    if (!$Model->save($data)) {
                        throw new Exception('图文消息更新失败-' . $Model->getError(), 6001);
                    }
                } else {
                    if (!$Model->add($data)) {
                        throw new Exception('图文消息添加失败-' . $Model->getError(), 6001);
                    }
                }
            } else {
                throw new Exception('数据校验失败-' . $Model->getError(), 6001);
            }

            $this->success('添加图文消息操作成功', U('news',array('wpid'=>$this->wpid)));
            die;

        }
        $this->display();
    }


}

?>