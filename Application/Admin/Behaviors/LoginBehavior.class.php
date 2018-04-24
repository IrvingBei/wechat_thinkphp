<?php
namespace Admin\Behaviors;

use \Org\Util\Auth;
use \Think\Behavior;

class LoginBehavior extends Behavior
{

    //行为执行入口
    public function run(&$param)
    {
        if (!Auth::checkLogin()) {
            redirect(PHP_FILE . C('USER_AUTH_GATEWAY'));
        }
        if (!Auth::AccessDecision()) {
            redirect(PHP_FILE . C('USER_AUTH_NO_ACCESS'));
        }
        // 登录超时
        if (session('admin.overtime') == true && Auth::checkAccess()) {
            redirect('/Admin/Default/loginlock');
        }

    }

}
