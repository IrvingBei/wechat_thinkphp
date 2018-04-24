<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2017 http://www.drpeng.com.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: Haijiang Li <lihaijiang.1989@gmail.com>
// +----------------------------------------------------------------------
// | This is not a free software, unauthorized no use and dissemination.
// +----------------------------------------------------------------------
/**
 * @file            AdminController.class.php
 * @version         1.0
 * @date            Fri, 23 Feb 2018 11:12:07 GMT
 * @description     This is the controller class for data "admin"
 */

namespace Admin\Controller;

use Common\Controller\AdminController;
use \Think\Exception;

class MenuController extends AdminController
{
    /**
     * 菜单列表
     */
    public function index()
    {
        $authRuleModel = D('AuthRule');

        $authRuleAll = $authRuleModel->getAuthRuleForPid();
        $this->assign('authRuleAll', $authRuleAll);
        $this->display();
    }

    /**
     * 菜单详情
     */
    public function info()
    {
        $authRuleModel = M('AuthRule');

        $params    = I();

        $create = true;
        $data = array();
        //新建还是编辑
        if(isset($params['id']) && !empty($params['id'])){
            $create = false;

            $authRuleRow = $authRuleModel->find($params['id']);
            if (empty($authRuleRow)) {
                $this->error('信息有误，请重新操作！','/Role/index');
            }
            $this->assign('authRuleRow', $authRuleRow);

            //下拉菜单
            if($authRuleRow['level'] == 1){
                $parentRow = $authRuleModel->field('id,title,pid')->where("level = 1")->select();
            }elseif($authRuleRow['level'] == 2){
                $authRuleRow['group_id'] = $authRuleRow['pid'];
                $parentRow = $authRuleModel->field('id,title,pid')->where("level = 1")->select();
            }elseif($authRuleRow['level'] == 3){
                $pid = $authRuleModel->where("id = '".$authRuleRow['pid']."'")->getField("pid");
                $authRuleRow['group_id'] = $pid;
                $authRuleRow['class_id'] = $authRuleRow['pid'];
                $parentRow = $authRuleModel->field('id,title,pid')->where("level = 1")->select();
                $classes = $authRuleModel->field('id,title,pid')->where("pid = '$pid' AND level = 2")->select();
            }

            $this->assign('classes', $classes);
            $this->assign('parentRow', $parentRow);
            $this->assign('authRuleRow', $authRuleRow);

            $data['id'] = $params['id'];
        }

        if (IS_POST) {
            $data['name'] = isset($params['name']) ? trim($params['name']) : '';
            $data['title'] = isset($params['title']) ? trim($params['title']) : '';
            $data['icon'] = isset($params['icon']) ? trim($params['icon']) : '';
            $data['islink'] = isset($params["islink"]) ? intval($params["islink"]) : 0;
            $data['sort'] = isset($params["sort"]) ? intval($params["sort"]) : 99;

            $group_id = $params['group_id'];
            $class_id = $params['class_id'];
            if($group_id > 0 && $class_id > 0){
                $data['pid'] = $class_id;
                $data['level'] = 3;
            }elseif($group_id > 0 && $class_id == 0){
                $data['pid'] = $group_id;
                $data['level'] = 2;
            }elseif($group_id == 0){
                $data['pid'] = $group_id;
                $data['level'] = 1;
            }
            
            if($authRuleModel->create()){
                if($create){
                    if(!$authRuleModel->add($data)){
                        throw new Exception('菜单添加失败-'.$authRuleModel->getError(), 6001);
                    }
                }else{
                    $name_exist = $authRuleModel->where("name = '".$data['name']."' and id != '".$params['id']."'")->count();
                    if($name_exist > 0){
                        throw new Exception('菜单URL已存在！');
                    }
                    if($authRuleModel->save($data) === false){
                        throw new Exception('菜单更新失败-'.$authRuleModel->getError(), 6001);
                    }
                }
            }else{
                throw new Exception('菜单数据校验失败-'.$adminModel->getError(), 6001);
            }

            $this->success("保存成功",'/Menu/index');
        }else{
            $parentRow = $authRuleModel->where("pid = 0")->order("sort ASC")->select();
            $this->assign('parentRow', $parentRow);

            $this->display();
        }
    }
}
