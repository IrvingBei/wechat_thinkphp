<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2017 http://www.drpeng.com.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: Haijiang Li
// +----------------------------------------------------------------------
// | This is not a free software, unauthorized no use and dissemination.
// +----------------------------------------------------------------------
/**
 * @file            Helper.class.php
 * @version         1.0
 * @date            Thu, 14 Sep 2017 13:13:32 GMT
 * @description     This is the service class for data "helper"
 */

namespace Admin\Service;

class Helper {

    const IMG_PATH = "/upload/images/";

    public static function guid(){
        if (function_exists('com_create_guid')){
            return com_create_guid();
        }else{
            mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
            $charid = strtoupper(md5(uniqid(rand(), true)));
            $hyphen = '';//chr(45);// "-"
            $uuid = substr($charid, 0, 8).$hyphen
                    .substr($charid, 8, 4).$hyphen
                    .substr($charid,12, 4).$hyphen
                    .substr($charid,16, 4).$hyphen
                    .substr($charid,20,12);// "}"
            return $uuid;
        }
    }

    /**
     * 生成唯一的单据ID：16位
     */
    public static function getUUID($prefix=1){
        $time = time();
        return $prefix . date("ymd") . sprintf('%05d', (date('s', $time) + 60 * date('i', $time) + 3600 * date('H', $time))) . substr(microtime(), 5, 2) . rand(10, 99);
    }

    /**
     * 生成唯一的退款单据ID：10位
     */
    public static function getRUID(){
        return date("ymd") . substr(microtime(), 5, 2) . rand(10, 99);
    }
    

    public static function get_img_config($group){
        $config = array(
            'prefix' => 's_',
            'width' => '100',
            'height' => '100',
        );
        if($group == 'brand'){

            $config = array(
                '0' => array('prefix'=>'s_','width'=>'100','height'=>'100'),
                '1' => array('prefix'=>'m_','width'=>'200','height'=>'200'),
                '2' => array('prefix'=>'b_','width'=>'300','height'=>'300'),
            );

        }
        if($group == 'product'){

            $config = array(
                '0' => array('prefix'=>'s_','width'=>'100','height'=>'100'),
                '1' => array('prefix'=>'m_','width'=>'400','height'=>'400'),
                '2' => array('prefix'=>'b_','width'=>'800','height'=>'800'),
            );
        }
        if($group == 'sku'){
//            $config = array(
//                'prefix' => 's_,m_,b_',
//                'width' => '100,400,800',
//                'height' => '100,400,800',
//            );
            $config = array(
                '0' => array('prefix'=>'s_','width'=>'100','height'=>'100'),
                '1' => array('prefix'=>'m_','width'=>'400','height'=>'400'),
                '2' => array('prefix'=>'b_','width'=>'800','height'=>'800'),
            );
        }
        if($group == 'wechat'){
            $config = array(
                '0' => array('prefix'=>'s_','width'=>'100','height'=>'100'),
                '1' => array('prefix'=>'m_','width'=>'400','height'=>'400'),
                '2' => array('prefix'=>'b_','width'=>'800','height'=>'800'),
            );
        }
        return $config;
    }


    public static function UploadImg($file, $group, $thumb=true, $sub=null){
        $config = self::get_img_config($group);
        $upload = new \Think\Upload();
        //设置上传文件大小
        $upload->maxSize = 2048000;
        //设置上传文件类型
        $upload->allowExts = array('jpg', 'gif', 'png', 'jpeg');
        $upload->saveRule = uniqid;
        $upload->rootPath = './Public/'; // 设置附件上传根目录
        $upload->autoSub = false;
        //设置附件上传目录
        $sub_dir = self::IMG_PATH."$group/";
        $upload->savePath = $sub_dir;
        $info = $upload->uploadOne($file);
        if(!$info){
            //捕获上传异常
            throw new \Think\Exception($upload->getError(), 6001);
        }else{
            if($thumb) {
                foreach ($config as $value) {
                    $image = new \Think\Image();
                    $thumb_file = "./Public" . $info['savepath'] . $info['savename'];
                    $save_path = "./Public" . $info['savepath'] . $value['prefix'] . $info['savename'];
                    $image->open($thumb_file)->thumb($value['height'], $value['width'], \Think\Image::IMAGE_THUMB_SCALE)->save($save_path);
                }
            }
            $file_url = $info['savename'];

        }
        return $file_url;
    }
}

?>
