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

class WxMaterialController extends WechatBaseController
{
    public function text(){
        $token = $this->token;
        $listArr = M('material_text')->where("token = '$token'")->field(['content','id'])->select();
        $list_data ['list_data'] = $listArr;
        $this->assign ( $list_data );
        $this->display();

    }

    public function textInfo(){

        $params = I();
        $materialTextModel = M('material_text');
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
            $data['content'] = isset($params['content']) ? trim($params['content']) : '';
            $data['token'] = $this->token;

            $this->_check_text_content($data['content']);

            if($materialTextModel->create()){
                if($create){
                    if(!$materialTextModel->add($data)){
                        throw new Exception('文本素材添加失败-'.$materialTextModel->getError(), 6001);
                    }
                }else{
                    if($materialTextModel->save($data) === false){
                        throw new Exception('文本素材更新失败-'.$materialTextModel->getError(), 6001);
                    }
                }
            }

            $this->success("保存成功",U('text',array('wpid'=>$this->wpid)));
            die;
        }
        $this->display();

    }

    public function image(){

        $token = $this->token;
        $params = I('get.');
        $page_row = C('PAGE_SIZE');
        $page = isset($params['p']) ? intval($params['p']) : 0;

        $count = M('material_image')->where("token = '$token'")->count();
        $Page = new Page($count, $page_row);
        $show = $Page->show();

        $listArr = M('material_image')->where("token = '$token'")
            ->field(['cover_url','media_id','image_name','wechat_url','id'])
            ->page($page . ", $page_row")
            ->select();
        $list_data ['list_data'] = $listArr;


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
        $Model = M('material_image');


        //根据是否传入id来判断是新建还是编辑
        if(isset($params['id']) && !empty($params['id'])){
            $image_id = trim($params['id']);
            $image_info = $Model->find($image_id);
            $this->assign('image_info', $image_info);
            $data['id'] = $params['id'];
            $data['updated_at'] = $time;
            $data['operator_id'] = Session('admin')['id'];
            $data['operator_name'] = Session('admin')['name'];
            $update = true;
        }else{
            $data['created_at'] = $time;
            $data['updated_at'] = $time;
            $data['operator_id'] = Session('admin')['id'];
            $data['operator_name'] = Session('admin')['name'];
        }

        if(IS_POST) {

            $data['token'] = $this->token;
            $data['image_name'] = isset($params['image_name']) ? trim($params['image_name']) : '';
            //图片处理
            if (!empty($_FILES['image_file']['name'])) {
                $data['cover_url'] = Helper::UploadImg($_FILES['image_file'], 'wechat');

                //上传素材到微信
                $Wechat = new WechatModel($data['token']);
                $result = $Wechat->uploadImage($data['cover_url']);
                $media_id = $result['media_id'];
            }


            if(empty($media_id)){
                $this->error ( '上传图文到微信出错，请稍后重试' );
            }else{
                $data['media_id'] = $media_id;
                $data['wechat_url'] = $result['url'];
            }

            if ($Model->create()) {
                if ($update) {
                    if (!$Model->save($data)) {
                        throw new Exception('图片素材更新失败-' . $Model->getError(), 6001);
                    }
                } else {
                    if (!$Model->add($data)) {
                        throw new Exception('图片素材添加失败-' . $Model->getError(), 6001);
                    }
                }
            } else {
                throw new Exception('品牌数据校验失败-' . $Model->getError(), 6001);
            }

            $this->success('添加图片素材操作成功', U('image',array('wpid'=>$this->wpid)));
            die;

        }
        $this->display();
    }


    public function news(){

        $token = $this->token;
        $params = I('get.');
        $page_row = C('PAGE_SIZE');
        $page = isset($params['p']) ? intval($params['p']) : 0;

        $count = M('material_news')->where("token = '$token'")->count();
        $Page = new Page($count, $page_row);
        $show = $Page->show();

        $listArr = M('material_news')->where("token = '$token'")
            ->field(['title','intro','url','pic_url','id','link'])
            ->page($page . ", $page_row")
            ->select();
        $list_data ['list_data'] = $listArr;


        $this->assign('pages', $show);
        $this->assign('page_row', $page_row);
        $this->assign ( $list_data );
        $this->display();

    }



    public function newsInfo(){


        $params = I();
        $time = time();
        $this->assign('time', $time);
        $data = array();
        $update = false;//是更新还是插入标记
        $Model = M('material_news');


        //根据是否传入id来判断是新建还是编辑
        if(isset($params['id']) && !empty($params['id'])){
            $id = trim($params['id']);
            $news_info = $Model->find($id);
            $this->assign('news_info', $news_info);
            $data['id'] = $params['id'];
            $data['updated_at'] = $time;
            $data['operator_id'] = Session('admin')['id'];
            $data['operator_name'] = Session('admin')['name'];
            $update = true;
        }else{
            $data['created_at'] = $time;
            $data['updated_at'] = $time;
            $data['operator_id'] = Session('admin')['id'];
            $data['operator_name'] = Session('admin')['name'];
        }

        if(IS_POST) {

            $data['token'] = $this->token;
            $data['title'] = isset($params['title']) ? trim($params['title']) : '';
            $data['intro'] = isset($params['intro']) ? trim($params['intro']) : '';
            $data['url'] = isset($params['url']) ? trim($params['url']) : '';
            $data['link'] = isset($params['link']) ? trim($params['link']) : '';
            //图片处理
            if (!empty($_FILES['image_file']['name'])) {
                $data['pic_url'] = Helper::UploadImg($_FILES['image_file'], 'wechat');
                $result = $this->uploadImage($data);
                if(empty($result)){
                    /*throw new Exception('图文素材添加失败-' . $Model->getError(), 6001);*/
                    $link = '/Public/upload/images/wechat/'.$data['pic_url'];
                    $data['link'] = $link;
                }else{
                    $data['link'] = $result['url'];
                }
            }

            if ($Model->create()) {
                if ($update) {
                    if (!$Model->save($data)) {
                        throw new Exception('图文素材更新失败-' . $Model->getError(), 6001);
                    }
                } else {
                    if (!$Model->add($data)) {
                        throw new Exception('图文素材添加失败-' . $Model->getError(), 6001);
                    }
                }
            } else {
                throw new Exception('图文数据校验失败-' . $Model->getError(), 6001);
            }

            $this->success('添加图文素材操作成功', U('news',array('wpid'=>$this->wpid)));
            die;

        }
        $this->display();
    }



    function uploadImage($data){

        $Wechat = new WechatModel($data['token']);
        $result = $Wechat->uploadImage($data['pic_url']);
        $media_id = $result['media_id'];
        if(empty($media_id)){
            return null;
        }else{
            return $result;
        }

    }


    function jump($url, $msg) {
        $this->assign ( 'url', $url );
        $this->assign ( 'msg', $msg );
        $this->display ('jump');
        exit ();
    }


    public function syncImage(){

        $list = D('Wechat')->uploadImage('5acc4a459950f.png');
        print_r($list);
        die;

        $offset = I ( 'offset', 0, 'intval' );
        $wpid = I('wpid');

        $list = D('Wechat')->getForeverList('image',$offset,20);

        if (isset ( $list ['errcode'] ) && $list ['errcode'] != 0) {
            $this->error ( '110115:' . error_msg ( $list ) );
        }
        if (empty ( $list ['item'] )) {
            echo '下载素材完成';
            die;
            $url = U ( 'text' );
            $this->jump ( $url, '下载素材完成' );
        }

        $map ['media_id'] = array (
            'in',
            getSubByKey ( $list ['item'], 'media_id' )
        );
        // $map ['manager_id'] = $this->mid;
        $map ['token'] = $this->token;
        $has = M ( 'material_image' )->where ( $map )->getField ( 'DISTINCT media_id,id' );
        // dump($map);
        // dump($has);

        foreach ( $list ['item'] as $item ) {
            $media_id = $item ['media_id'];
            if (isset ( $has [$media_id] ))
                continue;
            if ($item ['url']) {
                $ids = array ();
                //$data ['cover_id'] = $this->_download_imgage ( $media_id, $item ['url'] );
                //$data ['cover_url'] = get_cover_url ( $data ['cover_id'] );
                $data ['wechat_url'] = $item ['url'];
                $data ['media_id'] = $media_id;
                $data ['cTime'] = NOW_TIME;
                $data ['manager_id'] = $this->mid;
                $data ['token'] = $this->token;
                $ids [] = M ( 'material_image' )->add ( $data );
            }
        }
        $url = U ( 'syncImage', array (
            'offset' => $offset + $list ['item_count'],
            'wpid' => $wpid
        ) );
        $this->jump ( $url, '下载微信素材中，请勿关闭' );


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