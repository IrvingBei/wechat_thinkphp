<?php
namespace Home\Model;

use Home\Service\GoogleAIService;
use Think\Model;
use Home\Service\BaiduAIService;
class WechatModel extends Model
{


    protected $common;
    protected $receive;
    protected $media;
    protected $custom;
    protected $user;

    protected $token;

    protected $autoCheckFields  =   false;

    //微信公众号唯一标识
    public function __construct($token)
    {
        parent::__construct();
        vendor("WechatSDK.zoujingli.wechat-php-sdk.include");
        /*$options = array(
            'wechat_token'           => 'weiphp', // 填写你设定的key
            'token'     => 'gh_9fa87e5ecbd0', // 微信号原始id
            'appid'           => 'wx2a9675b3402c79cf', // 填写高级调用功能的app id, 请在微信开发模式后台查询
            'appsecret'       => '2e6e92ffdb1c9cf4132c66d2e5d501e0', // 填写高级调用功能的密钥
            'cachepath'       => LOG_PATH.'wechat/xiaopeng', // 设置SDK缓存目录（可选，默认位置在Wechat/Cache下，请保证写权限）
            'oauth_url'       => 'http://admin.bj95079.com.cn/get-weixin-code.html', //微信授权地址
        );*/

        //加载微信配置,先查看缓存中是否存在
        $key = 'wechat:config:'.$token;
        $options = S($key);
        if(empty($options)){
            $where['token'] = $token;
            $options = M('apps')->where($where)
                ->field(['token','wechat_token','appid','appsecret','oauth_url'])
                ->find();
            if(empty($options)){
                exit('success');
            }
            //echo M()->getLastSql();
            $options['cachepath'] = LOG_PATH.'wechat/'.$token;
            S($key, $options,5000);
        }

        $this->getconfig = \Wechat\Loader::config($options);
        $this->receive = new \Wechat\WechatReceive();
        $this->common = new \Wechat\Lib\Common();
        $this->custom = new \Wechat\WechatCustom();
        $this->user = new \Wechat\WechatUser();
        $this->media = new \Wechat\WechatMedia();

        $this->token = $token;

        //初始化$_receive对象
        $this->receive->getRev();

    }


    /**
     * @param $data
     * 被动回复微信消息
     */
    public function reply($data) {

        //需要初始化$_receive对象
        $this->receive->getRev();

        $key = $data ['Content'];

        $openId = $data['FromUserName'];
        $key = trim ( $key );
        $keywordArr = array ();

        /**
         * 微信事件转化成特定的关键词来处理
         * event可能的值：
         * subscribe : 关注公众号
         * unsubscribe : 取消关注公众号
         * scan : 扫描带参数二维码事件
         * location : 上报地理位置事件
         * click : 自定义菜单事件
         */
        if ($data ['MsgType'] == 'event' || $data ['MsgType'] == 'location') {
            $event = strtolower ( $data ['MsgType'] == 'location' ? $data ['MsgType'] : $data ['Event'] );

            //事件
            if($data ['MsgType'] == 'event'){
                if($event == 'click'){
                    //自定义菜单点击事件
                    $key = $data ['Content'] = $data ['EventKey'];
                }elseif ($event == 'location'){
                    //上报位置事件，没有EventKey
                    $loca ['Latitude'] = $data ['Latitude'];
                    $loca ['Longitude'] = $data ['Longitude'];
                    $loca ['Precision'] = $data ['Precision'];
                    $data ['Content'] = json_encode ( $loca );
                }elseif ($event == 'scan'){
                    //已关注扫描自定义二维码事件
                    $key = $data ['Content'] = $data ['EventKey'];
                }elseif($event == 'subscribe'){
                    //未关注扫描自定义二维码事件
                    $key = $data ['Content'] = ltrim ( $data ['EventKey'], 'qrscene_' );
                }elseif ($event == 'unsubscribe'){
                    //取消关注事件，没有EventKey
                }elseif ($event == 'view'){
                    //点击菜单跳转链接时的事件推送

                }elseif ($event == 'merchant_order'){
                    //微信小店支付成功消息，没有EventKey

                }else{
                    //其他类型消息

                }
            }else{
                //位置消息
                $loca ['Location_X'] = $data ['Location_X'];
                $loca ['Location_Y'] = $data ['Location_Y'];
                $loca ['Scale'] = $data ['Scale'];
                $loca ['Label'] = $data ['Label'];
                $data ['Content'] = json_encode($loca,JSON_UNESCAPED_UNICODE);
            }
        }
        // 数据保存到消息管理中
        M ( 'message' )->add ( $data );

        //将关键词加入到消息中
        $data['key'] = $key;

        // 关键词自动回复，文本，图片，图文等
        // 文本消息自动回复，点击事件自动回复
        if (! isset ( $addons [$key] )) {

            if($data['MsgType'] == 'text' || $data['Event'] == 'CLICK'){
                if($data['MsgType'] == 'text'){
                    $where['addons'] = 'AutoReply';
                }else{
                    $where['addons'] = 'Click';
                }
                $this->customReply($data,$where['addons']);
                //$keywordArr = M ( 'auto_reply' )->where ( $where )->order ( 'id desc' )->find ();
            }
        }

        //用户关注和取消关注的处理,包括扫描自定义二维码
        if (! isset ( $addons [$key] ) && ($data['Event'] == 'subscribe' || $data['Event'] == 'unsubscribe' || $data['Event'] == 'SCAN')){
            $addons [$key] = 'Welcome';
            $model = new WelcomeModel($this->token);
        }

        if (! isset ( $addons [$key] ) && ($data['MsgType'] == 'text')){
            $orc_key = C('OCR_KEYWORD');
            if(in_array($key,$orc_key)){
                S($openId,$data['Content']);
                $text = '当前图片识别内容为：'.$data['Content'];
                $this->replyText($text);
            }
        }

        if(! isset ( $addons [$key] ) && ($data['MsgType'] == 'image')){
            $type = S($openId);
            if(empty($type)){
                $type = '文字';
            }
            if($type == '小语种'){
                $client = new GoogleAIService();
            }else{
                $client = new BaiduAIService($type);
            }
            $text = $client->result($data);
            $this->replyText($text);
        }


        //NoAnswer
        if (! isset ( $addons [$key] )){

            $this->customReply($data,'NoAnswer');
        }

        // 最终也无法定位到插件，终止操作
        if (! isset ( $addons [$key] ) ) {
            echo 'success';
            exit ();
        }else{
            // 加载相应的插件来处理并反馈信息
            $model->reply ( $data, $keywordArr );
        }

    }

    /**
     * @param array $keywordArr
     * 自动回复消息
     */
    public function autoReply($keywordArr = array()) {
        $info = $keywordArr;
        switch ($info['msg_type']) {
            case 'text':/* 文本消息 */
                $this->replyText($info ['content']);
                break;
            case 'image':/* 图片消息 */
                $this->replyImage($info['image_material']);
                break;
            case 'news':/* 图文消息 */
                $this->replyNews($info['group_id']);
                break;
            default:
                exit('success');
        }
        die;
    }

    public function customReply($dataArr,$addons){
        //自动回复
        //$where = "token = '$token' and addons = '$addons' and status = 1";
        $where['token'] = $dataArr['ToUserName'];
        $where['status'] = 1;
        $where['addons'] = $addons;
        //关注时自动回复不需要关键词
        $exclude = array("Welcome","NoAnswer");
        if(!in_array($addons, $exclude)){
            $where['keyword'] = $dataArr['key'];
        }

        $keywordArr = M ( 'auto_reply' )->where ( $where )->order ( 'sort' )->select ();
        if(!empty($keywordArr)){

            //未认证只能发送一条消息，无法发送客服消息
            $this->autoReply($keywordArr[0]);

            if(count($keywordArr) == 1){
                //回复数据只有一个，发送自动回复
                $this->autoReply($keywordArr[0]);
            }else{
                //多个回复数据，调用接口发送客服消息
                foreach ($keywordArr as $info){
                    $openid = $dataArr['FromUserName'];
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
            die;
        }

    }

    /**
     * @param $openid
     * @return mixed
     * 先同步用户信息
     * 用户未关注直接返回空
     * 用户已关注，返回用户数据
     */
    public function get_wechat_fans($openid){

        $result = $this->init_follow($openid);
        if($result){
            //用户已关注
            $info = M('fans')->where(['openid' => $openid])->find();
            return $info;
        }else{
            //用户未关注
            return null;
        }
    }


    /**
     * @param $openid
     * @return bool
     * 创建或更新粉丝
     */
    public function init_follow($openid) {
        $fansInfo = $this->user->getUserInfo($openid);
        $table_name = 'fans';
        $time = time();
        $info = M($table_name)->where(['openid' => $openid])->find();
        if($fansInfo['subscribe']){
            //用户已关注
            $fansInfo['nickname'] = emoji_reject($fansInfo['nickname']);
            $fansInfo['wechat_name'] = '';
            $fansInfo['token'] = $this->token;
            $fansInfo['update_time'] = $time;
            $fansInfo['un_subscribe_time'] = 0;

            if(empty($info)){
                //创建用户
                $fansInfo['create_time'] = $time;
                $uid = M($table_name)->add($fansInfo);
                //log_message("创建用户信息：$openid");
            }else{
                //更新用户信息
                M($table_name)->where(['id'=>$info['id']])->save($fansInfo);
                //log_message("更新用户信息：$openid");
            }
            return true;
        }else{
            //用户未关注
            if(empty($info)){
                //未关注也未保存用户，不做处理
            }else{
                //更新用户状态为取消关注
                $info['un_subscribe_time'] = $time;
                $info['update_time'] = $time;
                $info['subscribe'] = 0;
                M($table_name)->where(['id'=>$info['id']])->save($info);
            }
            return false;
        }
    }



    /**将文本交给客服处理
     * @param $value
     * @return bool|string
     */
    public function send2customer(){
        if(isWorkTime()){
            //工作时间，将消息转发到客服系统
            return $this->receive->transfer_customer_service()->reply();
        }else{
            //非工作时间
            return $this->receive->text('您好！请您在9:00-21:00进行咨询，或者留下您的联系方式，我们将尽快与您联系！')->reply();
        }


    }



    /*********被动回复消息********************/

    /**
     * @param $content
     * 回复文本消息
     */
    public function replyText($content){
        $text = htmlspecialchars_decode($content);

        $this->receive->text($text)->reply();
        die;
    }

    /**
     * @param int $image_id
     * 回复图片消息
     */
    public function replyImage($image_id = 0){

        $map_image['id'] = $image_id;
        $media_id = M ( 'material_image' )->where($map_image)->getField ('media_id');
        if(empty($media_id)){
            exit('success');
        }
        $this->receive->image($media_id)->reply();
    }

    /**
     * @param int $news_id
     * 回复图文
     */
    public function replyNews($news_id = 0) {
        $map_news ['id'] = $news_id;
        $list = M ( 'material_news' )->where ( $map_news )->select ();
        if (! empty ( $list )) {
            foreach ( $list as $k => $vo ) {
                if ($k > 8)
                    continue;
                //从本地跳转
                $url = getRedirectUrl().$vo ['url'];
                $articles [] = array (
                    'Title' => $vo ['title'],
                    'Description' => $vo ['intro'],
                    'PicUrl' =>  $vo ['link'],
                    'Url' => $url
                );
            }

        }
        if (!empty($articles)){
            $this->receive->news($articles)->reply();
        }else{
            exit('success');
        }
    }



    /************被动回复消息结束****************/


    /*************发送客服消息**********************/


    /**
     * @param $content
     * @param $openid
     * 发送客服文本消息
     */
    public function sendCustomText($content,$openid){
        $param['touser'] = $openid;
        $param['msgtype'] = 'text';
        $param['text'] ['content'] = $content;
        $this->receive->sendCustomMessage($param);
    }




    /**
     * @param $sucai_id
     * @param $openid
     * 发送客服图片消息
     */
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

            //从本地跳转
            $url = getRedirectUrl().$vo ['url'];

            // 文章内容
            $art ['title'] = $vo ['title'];
            $art ['description'] = $vo ['intro'];
            $art ['url'] =  $url ;
            $pic_url = $vo ['link'];
            $art ['picurl'] = $pic_url;
            $articles [] = $art;
            S ( $key, $articles );
        }
        $param ['news'] ['articles'] = $articles;

        $this->receive->sendCustomMessage($param);
    }

    /*************发送客服消息结束**********************/


    /**
     * @param $user
     * @param $phone
     * 保存手机号
     */
    public function save_phone_record($user, $phone){

        $record = array(
            'openid' => $user['openid'],
            'nickname' => $user['nickname'],
            'city' => $user['city'],
            'province' => $user['province'],
            'country' => $user['country'],
            'sex' => $user['sex'],
            'record_time' => time(),
            'phone' => $phone);
        M('phone_record')->add($record);
    }



    public function getForeverList($type, $offset, $count){
        $data = $this->media->getForeverList($type,$offset,$count);
        return $data;
    }

    /**
     * @return bool|string
     * 获取accessToken
     */
    public function getAccessToken(){
       return $this->common->getAccessToken();
    }


}
