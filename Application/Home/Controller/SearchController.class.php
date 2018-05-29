<?php
namespace Home\Controller;

use Home\Service\GoogleCustomSearch;
use Think\Controller;

/**
 * 前台公共控制器
 * 为防止多分组Controller名称冲突，公共Controller名称统一使用分组名称
 */
class SearchController extends Controller {


	function index(){
	    //$id = 'ss-service-194514';
	    $id = '008831264745389699959:velxvzrvlpi';
	    $key = 'AIzaSyBUcYG5BZzA7Ip1IELWuPOPHX9khDfb4KY';
	    $keyword = $_GET['keyword'];
	    if(empty($keyword)){
	        echo '参数错误';
        }else{
            $search = new GoogleCustomSearch($id, $key);
            $result = $search->search($keyword);
            exit(json_encode($result,JSON_UNESCAPED_UNICODE));
        }

    }

}