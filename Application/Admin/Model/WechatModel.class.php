<?php
namespace Admin\Model;

use Think\Model;

class WechatModel extends Model
{

    protected $media;
    protected $extends;
    protected $menu;
    protected $config;
    protected $token;

    //关闭模型验证
    protected $autoCheckFields = false;

    public function __construct($token)
    {
        parent::__construct();
        vendor("WechatSDK.zoujingli.wechat-php-sdk.include");
        //加载微信配置,先查看缓存中是否存在
        $key = 'wechat:config:'.$token;
        $options = S($key);
        if(empty($options)){
            $where['token'] = $token;
            $options = M('apps')->where($where)
                ->field(['token','wechat_token','appid','appsecret','hot_line'])
                ->find();
            if(empty($options)){
                exit('error');
            }
            //echo M()->getLastSql();
            $options['cachepath'] = LOG_PATH.'wechat/'.$token;
            S($key, $options,5000);
        }

        $this->token = $token;
        $this->config = $options;
        $this->getconfig = \Wechat\Loader::config($options);
        $this->media = new \Wechat\WechatMedia();
        $this->extends = new \Wechat\WechatExtends();
        $this->menu = new \Wechat\WechatMenu();

    }

    public function getForeverList($type, $offset, $count){
        $data = $this->media->getForeverList($type,$offset,$count);
        return $data;
    }

    public function uploadImage($cover_url){
        $save_path = "./Public/upload/images/wechat/".$cover_url;
        $data['media'] = '@' . realpath ( $save_path );
        $result = $this->media->uploadForeverMedia($data,'image');
        return $result;
    }

    /**
     * @param $data
     * @return bool
     * 发送自定义菜单
     */
    public function sendMenu($data){
        return $this->menu->createMenu($data);
    }

    /**
     * @param $sucai_id
     * @param $openid
     * 发送客服图文消息
     */
    public function sendCustomNews($sucai_id,$openid){
        $param['touser'] = $openid;
        $param['msgtype'] = 'news';
        $key = 'Custom_replyNews_' . $sucai_id;
        $articles = S ( $key );
        if ($articles === false) {
            $map ['id'] = $sucai_id;
            $vo = M ( 'material_news' )->where ( $map )->find ();
            // 文章内容
            $art ['title'] = $vo ['title'];
            $art ['description'] = $vo ['intro'];
            $art ['url'] =  $vo ['url'] ;
            $pic_url = $vo ['pic_url'];
            $art ['picurl'] = $pic_url;
            $articles [] = $art;
            S ( $key, $articles );
        }
        $param ['news'] ['articles'] = $articles;

        $this->receive->sendCustomMessage($param);
    }


    public function sendCustomImage($sucai_id,$openid){

        $param['touser'] = $openid;
        $param['msgtype'] = 'image';
        $key = 'Custom_replyImage_' . $sucai_id;
        $media_id = S ( $key );
        if ($media_id === false) {
            $map ['id'] = $sucai_id;
            $vo = M ( 'material_image' )
                ->field(['cover_url','media_id','image_name','wechat_url','id'])
                ->where ( $map )->find ();
            // 图片资源id
            $media_id = $vo['media_id'];
            S ( $key, $media_id );
        }
        $param ['image'] ['media_id'] = $media_id;

        $this->receive->sendCustomMessage($param);

    }

    public function createQrCode($scene_str){
        return $res = $this->extends->getQRCode($scene_str, 1);
    }

}
