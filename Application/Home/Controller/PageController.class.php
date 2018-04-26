<?php
namespace Home\Controller;

use Home\Service\GoogleAIService;
use Think\Controller;

/**
 * 前台公共控制器
 * 为防止多分组Controller名称冲突，公共Controller名称统一使用分组名称
 */
class PageController extends Controller {


    //页面跳转
    function index(){
	    $url = $_GET['url'];
	    if(!empty($url)){
            header("Location: $url");
            exit;
        }else{
	        echo '参数错误';
        }

    }





}