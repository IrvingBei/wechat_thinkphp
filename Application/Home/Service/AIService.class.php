<?php
namespace Home\Service;

class AIService
{

    protected $type,$api_result,$result,$request_time,$response_time,$text,$api_account,$data;

    protected function error($result){
        //错误处理
        if(!empty($result['error_code'])){
            //记录错误日志
            add_debug_log($result,'BaiduAI');
            //print_r($result);
            $error_code = $result['error_code'];
            if($error_code < 20){
                //请求总量超限额
                $text = '今天的查询次数已用完，请明天再来查询吧';
            }else{
                //其他错误
                $text = '内部错误，请换张图试试吧';
            }
            return $text;
        }
    }

    //记录接口请求日志
    protected function record_log(){
        $data = $this->data;
        $api_log = array(
            'open_id' => $data['FromUserName'],
            'token' => $data['ToUserName'],
            'type' => $this->type,
            'create_time' => $data['CreateTime'],
            'image_url' => $data['PicUrl'],
            'api_status' => $this->api_result,
            'api_result' => $this->result,
            'api_request_time' => $this->request_time,
            'api_response_time' => $this->response_time,
            'result_text' => $this->text,
            'api_account' => $this->api_account,
        );
        M('image_log')->add($api_log);
    }

}
