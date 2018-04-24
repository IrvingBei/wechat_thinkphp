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

use Common\Controller\AdminController as C_AdminController;
use \Org\Util\Page;
use \Think\Exception;


class AdminController extends C_AdminController
{
    /**
     * 用户列表
     */
    public function index()
    {
        $adminModel = D('Admin');

        $total      = $adminModel->count();
        $pagesize   = C('PAGE_SIZE');
        $pageObject = new \Org\Util\Page($total, $pagesize);
        $pages      = $pageObject->show();

        $page = I('get.p', 0);

        $adminRows = $adminModel->page($page)->limit($pagesize)->relation(true)->select();
        $this->assign('adminRows', $adminRows);
        $this->assign('pages', $pages);
        $this->display();
    }

    /**
     * 用户详情
     */
    public function info()
    {
        $params    = I();
        $roleModel  = D('Role');
        $adminModel = D('Admin');

        $create = true;
        $data = array();
        $time = NOW_TIME;
        //新建还是编辑
        if(isset($params['id']) && !empty($params['id'])){
            $create = false;

            $adminRow = $adminModel->find($params['id']);
            if (empty($adminRow)) {
                $this->error('信息有误，请重新操作！','/Admin/index');
            }
            $this->assign('adminRowForEdit', $adminRow);

            $data['id'] = $params['id'];
            $data['updated_at'] = $time;
        }else{
            $data['created_at'] = $time;
            $data['updated_at'] = $time;
            
        }

        if(IS_POST){
            if(!empty($params['password'])){
                if($params['password'] != $params['password_confirm']){
                    $this->error('密码不一致，请重新操作！','/Admin/info');
                }
                $data['password'] = sha1(md5($params['password']));
            }

            $data['email'] = isset($params['email']) ? trim($params['email']) : '';
            $data['name'] = isset($params['name']) ? trim($params['name']) : '';
            $data['role_id'] = $params['role_id'];
            $data['status'] = $params['status'];

            if($adminModel->create()){
                if($create){
                    if(!$adminModel->add($data)){
                        throw new Exception('用户添加失败-'.$adminModel->getError(), 6001);
                    }
                }else{
                    $email_exist = $adminModel->where("email = '".$data['email']."' and id != '".$params['id']."'")->count();
                    if($email_exist > 0){
                        throw new Exception('用户帐号已存在！');
                    }
                    if($adminModel->save($data) === false){
                        throw new Exception('用户更新失败-'.$adminModel->getError(), 6001);
                    }
                }
            }else{
                throw new Exception('用户数据校验失败-'.$adminModel->getError(), 6001);
            }

            $this->success("保存成功",'/Admin/index');
        }else{
            $roleRows = $roleModel->getRoles("status = 1");
            $this->assign('roleRows', $roleRows);
            $this->display();
        }
    }
}
