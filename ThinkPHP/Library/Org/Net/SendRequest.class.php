<?php

/*
 * 发送get post 请求封装类。使用方法 use Org\Net\SendRequest; SendRequest::get($url); SendRequest::post($url, $data);
 *
 * @veter
 */
namespace Org\Net;

use \Think\Log;

class SendRequest
{

    public static function send($url, $method = 'GET', $post_data = '', $timeout = 10)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, $method == 'POST' ? 1 : 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('content-type: application/x-www-form-urlencoded; charset=UTF-8'));
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0); //php5.3 的bug

        if ($post_data != '') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $contents = curl_exec($ch);

        curl_close($ch);
        return $contents;
    }

    public static function get($url, $data = '', $timeout = 10)
    {
        $query_str = '';
        if (is_array($data)) {
            $tmp_arr = array();
            foreach ($data as $k => $v) {
                $tmp_arr[] = $k . '=' . $v;
            }
            $query_str = implode('&', $tmp_arr);
        } else {
            $query_str = $data;
        }
        $has_query   = strrpos($url, '?');
        $last_is_amp = $url{strlen($url) - 1} == '&' ? true : false;
        if (!empty($data)) {
            if ($has_query !== false) {
                if (!$last_is_amp) {
                    $url .= '&';
                }
            } else {
                $url .= '?';
            }
        }

        $url .= $query_str;
        Log::record("对外请求URL:{$url}", Log::INFO);
        return self::send($url, 'GET', '', $timeout);
    }

    public static function post($url, $data, $timeout = 10)
    {
        return self::send($url, 'POST', $data, $timeout);
    }
}
