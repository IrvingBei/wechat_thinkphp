<?php

namespace Home\Controller;
use Think\Controller;
use Home\Model\WechatModel;

/**
 * 微信交互控制器
 * 主要获取和反馈微信平台的数据
 */
class WeixinController extends Controller{
	/**
	 * 微信 入口
	 */
	public function index() {

        $content = file_get_contents ( 'php://input' );
        ! empty ( $content ) || die ( '这是微信请求的接口地址，直接在浏览器里无效' );


        $data_obj = new \SimpleXMLElement ( $content );
        $data = array();
        foreach ( $data_obj as $key => $value ) {
            $data [$key] = safe ( strval ( $value ) );
        }
        $token = $data['ToUserName'];
        $Wechat = new WechatModel($token);

		// 记录日志
		$content = file_get_contents ( 'php://input' );
		addWeixinLog ( $data, $content );

        //print_r($Wechat->receive->getRev());
        //die;

        //创建或更新用户信息
        //$Wechat->init_follow ( $data ['FromUserName'] );

		// 回复数据
        $Wechat->reply ($data);
		// 结束程序。防止oneThink框架的调试信息输出
		$length = ob_get_length ();
		if (empty ( $length )) {
			exit ( 'success' );
		} else {
			exit ();
		}
	}

}