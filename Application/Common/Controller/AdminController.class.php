<?php
namespace Common\Controller;

use Think\Controller;

class AdminController extends Controller
{
    /**
     * [_initialize description]
     */
    protected function _initialize()
    {
        //登录是否过期
        if(session(C('USER_AUTH_KEY'))){
            $now = time();
            $expire = C('SESSION_EXPIRE');
            //超过时间没有更新，退出登陆
            if(($now-session('last_access_time')) > $expire)
            {
                if(IS_AJAX){
                    $this->ajaxReturn("", "", 40001);
                }else{
                    session(null);
                    redirect('/Default/logout');
                }
                exit();
            }

            //刷新最后访问时间
            session('last_access_time', time());
        }

        // 登录用户信息
        $adminRow = session('admin');
        $this->assign('adminRow', $adminRow);

        // 菜单
        $menuList = D('Admin')->getMenuList();
        $this->assign('menuList', $menuList);
    }

}
