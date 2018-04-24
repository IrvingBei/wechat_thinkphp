<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2017 http://www.drpeng.com.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: Haijiang Li
// +----------------------------------------------------------------------
// | This is not a free software, unauthorized no use and dissemination.
// +----------------------------------------------------------------------
/**
 * @file            Curl.class.php
 * @version         1.0
 * @date            Thu, 14 Sep 2017 13:13:32 GMT
 * @description     This is the service class for data "curl"
 */

namespace Admin\Service;

class HttpCurl
{
    /**
     * 模拟HTTP请求
     */
    public static function makerequest($url, $method, $params, $headers = array()) {
        
        $ch = curl_init();
        
        curl_setopt ($ch, CURLOPT_URL, $url);
        
        if(!empty($headers)){
            curl_setopt ($ch, CURLOPT_HTTPHEADER, $headers);
        }else{  
            curl_setopt ($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        }

        //如果为0，则直接将返回的数据输出，为1，则将值付给$file_content
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);

        //设置cURL允许执行的最长秒数
        curl_setopt ($ch, CURLOPT_TIMEOUT, 30);
        
        //在发起连接前等待的时间，如果设置为0，则不等待
        curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 5);
        
        //http返回成功与否的状态
        curl_setopt ($ch, CURLOPT_HEADER, 0);
                
        switch($method){  
            case "GET" : 
                curl_setopt($ch, CURLOPT_HTTPGET, true);
                break;
            case "POST" : 
                curl_setopt($ch, CURLOPT_POST, true);   
                curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
                break;
            case "DELETE":
                curl_setopt ($ch, CURLOPT_CUSTOMREQUEST, "DELETE");   
                break;
        }
        
        $result = curl_exec($ch);//获得返回值
    
        $curl_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($curl_code === 204)
        {
            $result = $curl_code;
        }
        
        curl_close($ch);
        
        return $result;
    }
    
    public static function PostUrl($url, $params)
    {
        $headers = array('application/x-www-from-urlencoded');
        return self::makerequest($url, "POST", $params, $headers);
    }
    
    public static function DeleteUrl($url)
    {
        return self::makerequest($url, "DELETE", $params = array(), $headers = array());
    }

}
?>