<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2017 http://www.drpeng.com.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: Haijiang Li <lihaijiang.1989@gmail.com>
// +----------------------------------------------------------------------
// | This is not a free software, unauthorized no use and dissemination.
// +----------------------------------------------------------------------
/**
 * @file            DefaultController.class.php
 * @version         1.0
 * @date            Fri, 23 Feb 2018 11:12:07 GMT
 * @description     This is the controller class for data "default"
 */

namespace Admin\Controller;

use Common\Controller\AdminController;
use Common\Model\AdminModel;
use \Org\Util\Auth;
use \Admin\Service\Log;

class DefaultController extends AdminController
{
    /**
     * [_initialize description]
     */
    public function _initialize()
    {
        parent::_initialize();
        clayout(false);
    }

    /**
     * [login description]
     */
    public function login()
    {
        if (Auth::isLogin()) {
            redirect('/Index/index');
        }
        if (IS_POST) {
            $adminModel = new AdminModel();
            $email      = I('post.email');
            $password   = I('post.password');

            if ($adminModel->doLogin($email, $password)) {
                Log::adminLog();
                $this->success('登录成功', '/Index/index');
            } else {
                $this->error('登录失败');
            }
            exit;

        }
        $this->display();
    }

    /**
     * [logout description]
     */
    public function logout()
    {
        if (!Auth::isLogin()) {
            redirect(PHP_FILE . C('USER_AUTH_GATEWAY'));
        }
        session(null);
        $this->success('登出成功', '/Default/login');
    }

    /**
     * 锁定登录
     */
    public function loginlock()
    {
        if (!Auth::isLogin()) {
            redirect(PHP_FILE . C('USER_AUTH_GATEWAY'));
        }

        if (IS_POST) {
            $adminModel = new AdminModel();
            $password   = I('post.password');

            if ($adminModel->doLoginLock($password)) {
                $this->success('登录成功', '/Index/index');
                exit;
            }
        }
        if (session('admin.overtime') === false) {
            session('admin.overtime', true);
        }
        $this->display('login_lock');
    }

    /**
     * [noAccess description]
     */
    public function noAccess()
    {
        $this->display('no_access');
    }

}
