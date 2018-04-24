<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2017 http://www.drpeng.com.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: Haijiang Li <lihaijiang.1989@gmail.com>
// +----------------------------------------------------------------------
// | This is not a free software, unauthorized no use and dissemination.
// +----------------------------------------------------------------------
/**
 * @file            AjaxController.class.php
 * @version         1.0
 * @date            Fri, 23 Feb 2018 11:12:07 GMT
 * @description     This is the controller class for data "ajax"
 */

namespace Admin\Controller;
use \Think\Model;
use \Think\Exception;

use Common\Controller\AdminController;

class AjaxController extends AdminController
{
    // 获取二级菜单
    public function get_child_menus(){
        $authRuleModel = M('AuthRule');
        $pid = I('post.group_id');

        $ajax_data = $authRuleModel->field('id, title')->where("pid = $pid")->order("sort")->select();

        $this->ajaxReturn($ajax_data);
    }

    public function get_wx_text(){

        C('DB_PREFIX', 'xp_weixin_');
        $Model = M('material_text');
        $params = I('post.');

        $keyword = isset($params['keyword']) ? trim($params['keyword']) : '';
        $token = isset($params['token']) ? trim($params['token']) : '';

        $where = "1=1";

        if(!empty($keyword)){
            $where .= " and content like '%".$keyword."%' ";
        }
        $where .= " and token = '$token'";

        $ajax_data = array();
        $list = $Model->where($where)->field(['id','content'])->select();
        foreach ($list as $key => $row) {
            $ajax_data[] = array(
                'id' => $row['id'],
                'content' => $row['content'],
            );
        }
        // echo M()->getLastSql();exit;
        if($ajax_data){
            $this->ajaxReturn(array('status' => 1, 'info' => '', 'data' => $ajax_data));
        }else{
            $this->ajaxReturn("");
        }

    }

    public function get_wx_image(){

        C('DB_PREFIX', 'xp_weixin_');
        $Model = M('material_image');
        $params = I('post.');

        $keyword = isset($params['keyword']) ? trim($params['keyword']) : '';
        $token = isset($params['token']) ? trim($params['token']) : '';

        $where = "1=1";

        if(!empty($keyword)){
            $where .= " and image_name like '%".$keyword."%' ";
        }
        $where .= " and token = '$token'";

        $ajax_data = array();
        $list = $Model->where($where)->field(['id','image_name','cover_url','wechat_url','media_id'])->select();
        foreach ($list as $key => $row) {
            $ajax_data[] = array(
                'id' => $row['id'],
                'image_name' => $row['image_name'],
                'cover_url' => $row['cover_url'],
                'wechat_url' => $row['wechat_url'],
                'media_id' => $row['media_id'],
            );
        }
        // echo M()->getLastSql();exit;
        if($ajax_data){
            $this->ajaxReturn(array('status' => 1, 'info' => '', 'data' => $ajax_data));
        }else{
            $this->ajaxReturn("");
        }

    }

    public function get_wx_news(){

        C('DB_PREFIX', 'xp_weixin_');
        $Model = M('material_news');
        $params = I('post.');
        $keyword = isset($params['keyword']) ? trim($params['keyword']) : '';
        $token = isset($params['token']) ? trim($params['token']) : '';

        $where = "1=1";

        if(!empty($keyword)){
            $where .= " and (title like '%".$keyword."%' or intro like '%".$keyword."%') ";
        }


        $where .= " and token = '$token'";

        $ajax_data = array();
        $list = $Model->where($where)->field(['id','title','intro','url','pic_url','link'])->select();
        foreach ($list as $key => $row) {
            $ajax_data[] = array(
                'id' => $row['id'],
                'title' => $row['title'],
                'intro' => $row['intro'],
                'url' => $row['url'],
                'pic_url' => $row['pic_url'],
                'link' => $row['link'],
            );
        }
        // echo M()->getLastSql();exit;
        if($ajax_data){
            $this->ajaxReturn(array('status' => 1, 'info' => '', 'data' => $ajax_data));
        }else{
            $this->ajaxReturn("");
        }

    }

}
