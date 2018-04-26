<?php
namespace Home\Controller;

use Home\Service\GoogleAIService;
use Think\Controller;

/**
 * 前台公共控制器
 * 为防止多分组Controller名称冲突，公共Controller名称统一使用分组名称
 */
class HomeController extends Controller {


	function index(){

        echo '服务正常运行中';
    }

}