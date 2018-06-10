<?php
namespace Home\Service;

class BaiduAIService extends AIService
{
    protected $client;
    protected $image;
    protected $config;

    public function __construct($type) {

        vendor("BaiduAI.AipOcr");
        vendor("BaiduAI.AipImageClassify");

        $this->config = $config = getBaiduConfig ($type);

        $this->api_account = $config['account_id'];

        $app_id = $config["app_id'"];
        $app_key = $config["api_key"];
        $secret_key = $config["secret_key"];

        $this->type = $type;
        $this->client = new \AipOcr($app_id,$app_key,$secret_key);
        $this->image = new \AipImageClassify($app_id,$app_key,$secret_key);

    }

    public function result($data){
        if(empty($this->config)){
            $this->text = $text =  '今天的查询次数已用完';
        }else{
            $type = $this->type;
            $url = $data['PicUrl'];
            switch ($type){
                case '植物':
                case '动物':
                case '车辆':
                case '菜品':
                    $text = $this->imageDetect($url,$type);
                    break;
                default:
                    $text = $this->ocr($url);
                    break;
            }
        }

        //记录接口日志
        $this->data = $data;
        $this->record_log();
        return $text;
    }

    protected function imageDetect($url,$type='植物'){
        $image = file_get_contents($url);
        $api_result = 3;//默认失败
        $request_time = time();
        switch ($type){
            case '植物':
                $this->type_code = 2;
                $result = $this->image->plantDetect($image);
                break;
            case '车辆':
                $this->type_code = 4;
                $result = $this->image->carDetect($image);
                break;
            case '动物':
                $this->type_code = 3;
                $result = $this->image->animalDetect($image);
                break;
            case '商标':
                $this->type_code = 7;
                $result = $this->image->logoSearch($image);
                break;
            case '菜品':
                $this->type_code = 5;
                $result = $this->image->dishDetect($image);
                break;
            default:
                $result = $this->image->plantDetect($image);
        }
        $response_time = time();
        $this->result = json_encode($result,JSON_UNESCAPED_UNICODE);
        $text = $this->error($result);
        if(empty($text)){
            $api_result = 2;
            $result = $result['result'];
            $text = "";
            if(count($result) == count($result, COUNT_RECURSIVE)){
                //一维数组
                $name = $result['name'];
                $score = $result['score'];
                if(empty($score)){
                    $score = $result['probability'];
                }
                $text = $text.$name.' '.double2percent($score)."\n";
            }else{
                //二维数组
                foreach ($result as $data){
                    $name = $data['name'];
                    $score = $data['score'];
                    if(empty($score)){
                        $score = $data['probability'];
                    }
                    $prob = ' 可能性:'.double2percent($score);
                    if(!empty($data['calorie']) && $data['calorie'] > 0){
                        $calorie = $data['calorie'].'卡路里每100g';
                        $text = $text.$name.$prob."\n".$calorie ."\n"."\n";
                    }elseif (!empty($data['year'])){
                        $year = '年份:'.$data['year'];
                        $text = $text.$name.$prob."\n".$year ."\n"."\n";
                    }
                    else{
                        $text = $text.$name.$prob."\n";
                    }

                }
            }
        }

        $this->type = $type;

        $this->api_result = $api_result;
        $this->request_time = $request_time;
        $this->response_time = $response_time;
        $this->text = $text;

        return $text;
    }

    protected function ocr($url){
        $this->type_code = 1;
        $request_time = time();
        $result = $this->client->basicGeneralUrl($url);
        $this->result = json_encode($result,JSON_UNESCAPED_UNICODE);
        $response_time = time();
        $api_result = 3;
        $text = $this->error($result);
        if(empty($text)){
            //接口调用成功
            $api_result = 2;
            if($result['words_result_num']){
                //获取到值
                $words_result = $result['words_result'];
                $text = "";
                foreach ($words_result as $words){
                    $text = $text.$words['words']."\n";
                }
            }else{
                $text = "图片中不包含文字";
            }
        }

        $this->type = '文字';
        $this->api_result = $api_result;
        $this->request_time = $request_time;
        $this->response_time = $response_time;
        $this->text = $text;

        return $text;
    }

}
