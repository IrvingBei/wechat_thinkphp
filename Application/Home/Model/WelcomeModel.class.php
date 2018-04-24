<?php

namespace Home\Model;

/**
 * Welcome模型
 */
class WelcomeModel extends WechatModel {

	function reply($dataArr, $keywordArr = array()) {

	    //同步用户信息
        $this->init_follow ( $dataArr ['FromUserName'] );

		if ($dataArr ['Event'] == 'subscribe') {

			if (! empty ( $dataArr ['EventKey'] )) {
			    //用户未关注时，进行关注后的事件推送
				$this->scan ( $dataArr, $keywordArr );
			}else{
                //普通关注发送客服消息
                $this->customReply($dataArr,'Welcome');
            }

		} elseif ($dataArr ['Event'] == 'SCAN') {
		    //用户已关注时的事件推送
			$this->scan ( $dataArr, $keywordArr );
		} elseif ($dataArr ['Content'] == 'unsubscribe') {
		    //取消关注
			exit('success');
		}
	}

	function normalWelcome($data){
	    //普通关注回复客服消息
        $token = $data['ToUserName'];
        $where = "token = '$token' and addons = 'Welcome' ";
        $keywordArr = M ( 'auto_reply' )->where ( $where )->order ( 'sort' )->select ();
        if(!empty($keywordArr)){
            foreach ($keywordArr as $info){

                $openid = $data['FromUserName'];
                switch ($info['msg_type']) {
                    case 'text':/* 文本消息 */
                        $this->sendCustomText($info ['content'],$openid);
                        break;
                    case 'image':/* 图片消息 */
                        $this->sendCustomImage($info['image_material'],$openid);
                        break;
                    case 'news':/* 图文消息 */
                        $this->sendCustomNews($info['group_id'],$openid);
                        break;
                    default:
                        continue;
                }

            }
        }

    }

	function scan($dataArr, $keywordArr = array()) {
        $key = $map ['scene_id'] = ltrim ( $dataArr ['EventKey'], 'qrscene_' );

		//记录场景值信息
        //老带新
        //发送图文消息 电梯广告

        //扫码类型，默认为0，老带新为1
        $type = 0;
        if(strstr($key, 'OLD2NEW') || '网销'==$key || '电销'==$key){
            //老带新扫码
            $type = 1;
        }
        //记录扫描信息
        $data = array(
            'user_openid' => $dataArr['FromUserName'],
            'ticket' => $dataArr['Ticket'],
            'create_time' => $dataArr['CreateTime'],
            'token' => $dataArr['ToUserName'],
            'event' => $dataArr['Event'],
            'event_key_data' => $key,
            'type' => $type
        );
        $result = M('qrcode_record')->add($data);

        //扫描二维码自定义回复
        $this->customReply($dataArr,'Scan');

    }


}
