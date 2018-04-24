<?php

// +----------------------------------------------------------------------
// | Copyright (c) 2017 http://www.drpeng.com.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: Haijiang Li <lihaijiang.1989@gmail.com>
// +----------------------------------------------------------------------
// | This is not a free software, unauthorized no use and dissemination.
// +----------------------------------------------------------------------
/**
 * @file            IndexController.class.php
 * @version         1.0
 * @date            Fri, 23 Feb 2018 11:12:07 GMT
 * @description     This is the controller class for data "index"
 */

namespace Admin\Controller;

use Common\Controller\AdminController;

class IndexController extends AdminController
{
    /**
     * 后台框架
     */
    public function index()
    {
        $this->display();
    }

    /**
     * 后台起始页面
     */
    public function main()
    {
            $this->display();
    }

    /**
     * 修改登录密码
     */
    public function password()
    {
        if(IS_POST){
            $params    = I();
            $adminModel = D('Admin');

            $password = isset($params['password']) ? trim($params['password']) : '';
            $password_confirm = isset($params['password_confirm']) ? trim($params['password_confirm']) : '';

            $data['id'] = session('admin')['id'];
            if(!empty($password)){
                if($password != $password_confirm){
                    $this->error('密码不一致，请重新操作！','/Index/password');
                }
                $data['password'] = sha1(md5($password));
            }else{
                $this->error('密码不能为空','/Index/password');
            }

            if($adminModel->save($data) === false){
                throw new Exception('修改密码失败-'.$adminModel->getError(), 6001);
            }

            $this->success("保存成功");
        }else{
            $this->display();
        }
    }

}
