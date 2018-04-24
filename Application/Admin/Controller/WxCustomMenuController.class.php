<?php

// +----------------------------------------------------------------------
// | Copyright (c) 2017 http://www.drpeng.com.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: Haijiang Li <lihaijiang.1989@gmail.com>
// +----------------------------------------------------------------------
// | This is not a free software, unauthorized no use and dissemination.
// +----------------------------------------------------------------------
/**
 * @file            WechatController.class.php
 * @version         1.0
 * @date            Fri, 23 Feb 2018 11:12:07 GMT
 * @description     This is the controller class for data "user"
 */

namespace Admin\Controller;

use Admin\Model\WechatModel;
use \Org\Util\Page;
use \Think\Exception;
use \Admin\Service\Helper;

class WxCustomMenuController extends WechatBaseController
{

    public function index(){

        $this->list_content ();
        $this->display();

    }


    public function info() {
        if (IS_POST) {
            $data = I ( 'post.' );
            if (empty ( $data ['title'] )) {
                $this->error ( '400162:菜单名不能为空' );
            }
            if ($data ['from'] == 2 && empty ( $data ['url'] )) {
                $this->error ( '400163:请先填写URL地址' );
            }
            if ($data ['from'] == 1 && (empty ( $data ['material'] ) || $data ['material'] == 'text:')) {
                $this->error ( '400164:请先选择素材' );
            }
            if ($data ['from'] == 3 && empty ( $data ['keyword'] )) {
                $this->error ( '400165:关键词不能为空' );
            }
            unset ( $data ["material_material_type"], $data ["material_material_text_id"], $data ["material_material_news_id"], $data ["material_material_img_id"], $data ["material_material_voice_id"], $data ["material_material_video_id"] );
            /* if ($data['from'] == 1){
                $data['keyword']='custom_material_'.$data['material'];
            } */
            if (isset ( $data ['id'] ) && ! empty ( $data ['id'] )) {
                $map ['id'] = $data ['id'];
                $res = M ( 'custom_menu' )->where ( $map )->save ( $data );
            } else {
                $data ['token'] = $this->token;
                $res = M ( 'custom_menu' )->add ( $data );
            }
            if ($res !== false) {
                // 重置一级菜单
                if ($data ['pid'] > 0) {
                    $pmap ['id'] = $data ['pid'];
                    $from = M ( 'custom_menu' )->where ( $pmap )->getField ( 'from' );
                    if ($from != 0) {
                        M ( 'custom_menu' )->where ( $pmap )->setField ( 'from', 0 );
                    }
                }

                /*$url = empty ( $data ['rule_id'] ) ? U ( 'lists' ) : U ( 'custom_lists', [
                    'rule_id' => $data ['rule_id']
                ] );*/
                $url = U('index',array('wpid'=>$this->wpid));
                $this->success ( '保存菜单成功！', $url );
            } else {
                $this->error ( '400165:保存菜单失败' );
            }
        } else {
            // 获取一级菜单
            $map ['token'] = $this->token;
            $map ['pid'] = 0;
            $map ['status'] = 1;
            $map ['rule_id'] = I ( 'rule_id', 0 );
            $list = M ( 'custom_menu' )->where ( $map )->field ( 'id, title' )->select ();
            $this->assign ( 'pList', $list );

            $this->assign ( 'normal_tips', '可创建最多 3 个一级菜单，每个一级菜单下可创建最多 5 个二级菜单。编辑中的菜单不会马上被用户看到，请放心调试' );

            $data = [ ];
            $menu_map ['id'] = I ( 'id', 0 );
            if (! empty ( $menu_map ['id'] )) {
                $data = M ( 'custom_menu' )->where ( $menu_map )->find ();
            }
            // dump($data);
            $this->assign ( 'data', $data );

            $this->display ();
        }
    }

    public function del(){

        $params = I();
        $map ['id'] = $params['id'];
        $res = M('custom_menu')->where($map)->setField('status',0);
        if($res !== false){
            $url = U('index',array('wpid'=>$this->wpid));
            $this->success ( '删除菜单成功！', $url );
        }else{
            $this->error ( '400165:删除菜单失败' );
        }


    }



    // 发送菜单到微信
    public function sendMenu() {
        $data = $this->get_data ();

        foreach ( $data as $k => $d ) {
            if ($d ['pid'] != 0)
                continue;
            $treeArr [$d ['id']] = $this->_deal_data ( $d );
            unset ( $data [$k] );
        }
        foreach ( $data as $k => $d ) {
            $treeArr [$d ['pid']] ['sub_button'] [] = $this->_deal_data ( $d );
            unset ( $data [$k] );
        }
        $tree ['button'] = [ ];
        foreach ( $treeArr as $vo ) {
            $tree ['button'] [] = $vo;
        }

        // button 是 一级菜单数组，个数应为1~3个,二级菜单数组，个数应为1~5个
        $top_count = count ( $tree ['button'] );
        // dump($top_count);
        // dump($tree);
        // exit();
        if ($top_count == 0 || $top_count > 3) {
            $this->error ( '400160:一级菜单数组，个数应为1~3个' );
        }
        foreach ( $tree ['button'] as $vo ) {
            $sub_count = isset ( $vo ['sub_button'] ) ? count ( $vo ['sub_button'] ) : 0;
            if (count ( $vo ) < 3 && ($sub_count == 0 || $sub_count > 5)) {
                $this->error ( '400161:' . $vo ['name'] . '的二级菜单数组，个数应为1~5个' );
            }
        }

        /*echo '<pre>';
        print_r($tree);
        $url = U('index',array('wpid'=>$this->wpid));
        $this->success ( '发送菜单成功！', $url );
        die;*/

        $Wechat = new WechatModel($this->token);
        $res = $Wechat->sendMenu($tree);
        if (! isset ( $res ['errcode'] ) || $res ['errcode'] == 0) {
            $url = U('index',array('wpid'=>$this->wpid));
            $this->success ( '发送菜单成功！', $url );
        } else {
            $this->error ( error_msg ( $res ) );
        }
    }




    private function list_content($rule_id = 0) {
        $eventArr = [
            'text' => '文本素材',
            'img' => '图片素材',
            'news' => '图文素材',
            'voice' => '语音素材',
            'video' => '视频素材',
            'click' => '点击推事件 ',
            'scancode_push' => '扫码推事件 ',
            'scancode_waitmsg' => '扫码带提示 ',
            'pic_sysphoto' => '弹出系统拍照发图  ',
            'pic_photo_or_album' => '弹出拍照或者相册发图 ',
            'pic_weixin' => '弹出微信相册发图器 ',
            'location_select' => '弹出地理位置选择器',
            'none' => ''
        ];
        // 搜索条件
        $map ['rule_id'] = $rule_id;

        $list_data = $this->get_data ( $map );
        foreach ( $list_data as &$vo ) {
            $vo ['content'] = '';
            if ($vo ['from'] == 1) {
                $arr = explode ( ':', $vo ['material'] );
                $vo ['content'] = isset ( $eventArr [$arr [0]] ) ? $eventArr [$arr [0]] : '';
            } elseif ($vo ['from'] == 2) {
                $vo ['content'] = $vo ['url'];
            } elseif ($vo ['from'] == 3) {
                $vo ['content'] = $eventArr [$vo ['type']] . ': ' . $vo ['keyword'];
            } elseif ($vo ['from'] == 4) {
                $vo ['content'] = '小程序：' . $vo ['pagepath'];
            }
        }

        $this->assign ( 'list_data', $list_data );
        $this->assign ( 'rule_id', $rule_id );
    }

    private function get_data($map = []) {
        $map ['token'] = $this->token;
        $map ['status'] = 1;
        $list = M ( 'custom_menu' )->where ( $map )->order ( 'pid asc, sort asc' )->select ();


        // 取一级菜单
        $one_arr = [ ];
        foreach ( $list as $k => $vo ) {
            if ($vo ['pid'] != 0)
                continue;

            $one_arr [$vo ['id']] = $vo;
            unset ( $list [$k] );
        }
        $data = [ ];
        foreach ( $one_arr as $p ) {
            $data [] = $p;

            $two_arr = array ();
            foreach ( $list as $key => $l ) {
                if ($l ['pid'] != $p ['id'])
                    continue;

                $two_arr [] = $l;
                unset ( $list [$key] );
            }

            $data = array_merge ( $data, $two_arr );
        }

        return $data;
    }


    private function _deal_data($d, $pid = 0) {
        $res ['name'] = trim ( str_replace ( '├──', '', $d ['title'] ) );
        $len = mb_strlen ( $res ['name'], 'UTF-8' );
        $max_len = empty ( $pid ) ? 16 : 60;
        if ($len > $max_len) {
            $this->error ( '400155:' . $res ['name'] . '菜单已超过' . $max_len . '个字节的限制' );
        }

        switch ($d ['from']) {
            case 0 : // 一级无事件
                break;
            case 1 : // 素材
                $res ['type'] = 'click';
                $res ['key'] = 'material::/' . trim ( $d ['material'] );
                break;
            case 2 : // URL11
                $res ['type'] = 'view';
                $res ['url'] = $d ['url'];

                $len = mb_strlen ( $res ['url'], 'UTF-8' );
                if ($len > 1024) {
                    $this->error ( '400156:' . $res ['name'] . ' 的URL已超过1024个字节的限制：' . $res ['url'] );
                }
                break;
            case 3 : // 自定义
                $res ['type'] = trim ( $d ['type'] );
                $res ['key'] = trim ( $d ['keyword'] );

                $len = mb_strlen ( $res ['key'], 'UTF-8' );
                if ($len > 128) {
                    $this->error ( '400157:' . $res ['name'] . ' 的关键词已超过128个字节的限制：' . $res ['key'] );
                }
                break;
            case 4 : // 小程序
                $res ['type'] = 'miniprogram';
                $res ['appid'] = trim ( $d ['appid'] );
                $res ['pagepath'] = trim ( $d ['pagepath'] );
                $res ['url'] = ( $d ['appurl'] );

                $len = mb_strlen ( $res ['url'], 'UTF-8' );
                if ($len > 1024) {
                    $this->error ( '400158:' . $res ['name'] . ' 的URL已超过1024个字节的限制：' . $res ['url'] );
                }
                break;
        }

        return $res;
    }

}

?>