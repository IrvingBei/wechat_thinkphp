<?php
namespace Home\Service;
use Google\Cloud\Vision\VisionClient;
class GoogleAIService extends AIService
{
    protected $vision;
    public function __construct() {
        //google api
        vendor("GoogleCloud.autoload");
        $keyfile_path = CONF_PATH.'config.json';
        putenv('GOOGLE_APPLICATION_CREDENTIALS='.$keyfile_path);
        $this->type = '小语种';
    }

    public function result($data){

        $url = $data['PicUrl'];
        $request_time = time();
        $text = $this->text($url);
        $response_time = time();

        //记录接口日志
        $this->request_time = $request_time;
        $this->response_time = $response_time;
        $this->data = $data;
        $this->record_log();

        return $text;
    }

    protected function text($url){
        try{
            $vision = $this->vision = new VisionClient();
        }catch (Exception $e){
            exit('认证失败');
        }

        $image = $vision->image(file_get_contents($url), ['TEXT_DETECTION']);
        $result = $vision->annotate($image);
        $this->api_result = $result;

        $ar = (array) $result->text();
        if(empty($ar)){
            $text = '图片中不包含文字';
        }else{
            $text = $ar[0]->description();
        }

        return $text;
    }
}
