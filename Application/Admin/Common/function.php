<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2017 http://www.drpeng.com.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: Haijiang Li <lihaijiang.1989@gmail.com>
// +----------------------------------------------------------------------
// | This is not a free software, unauthorized no use and dissemination.
// +----------------------------------------------------------------------
/**
 * @file            Functions.php
 * @version         1.0
 * @date            Thu, 26 Oct 2017 11:34:46 GMT
 * @description     公共函数库
 */

use \Admin\Service\HttpCurl;

/**
 * 获取返回页面URL
 */
function get_back_url($default_url=null){
    $back_url = empty($_REQUEST['back_url']) ? $_SERVER['HTTP_REFERER'] : $_REQUEST['back_url'];
    return $back_url;
}

/**
 * [sms 短信] 
 * @param  [string] $mobile [手机号]
 * @param  [string] $content [短信内容]
 */
function SMS($mobile, $content) {
    $url = "http://api.app2e.com/smsBigSend.api.php";
    if($site == '101'){
        $username = "hlwqudaokdt";
        $password = "FD344D4B337F339593DCD59A093C5DE9";
    }else{
        $username = "hlwqudao";
        $password = "94b06ab80d1573af035129e2f057db13";
    }
    $params = "pwd=$password&username=$username&p=$mobile&msg=$content";
    $params = iconv("UTF-8", "GBK", $params);

    $send = HttpCurl::PostUrl($url, $params);

    return json_decode($send, true);
}

/**
 * 获取列表序列号
 */
function get_sequence($page_row = 1, $idx){
    if(isset($_REQUEST['p']) and !empty($_REQUEST['p'])){
        $page_no = $_REQUEST['p'] - 1;
    }else{
        $page_no = 0;
    }
    $seq = $page_no * $page_row + $idx;
    return $seq;
}

/**
 * 获取城市信息
 */
function get_area($area_level, $parent_id, $area_id, $is_show = 0){
    if($is_open == 0){
        $where = "is_show in (0,1)";
    }else{
        $where = "is_show = 1";
    }

    if($area_level){
        $where .= " and level = $area_level";
    }

    if(isset($parent_id) && !empty($parent_id)){
        $where .= " and parent_id = '$parent_id'";
    }

    if(isset($area_id) && !empty($area_id)){
        $where .= " and id = '$area_id'";
    }

    $citys = M()->table("xp_area")->where("$where")->field("id,parent_id,area_name,short_name,level,is_show")->select();

    return $citys;
}

/**
 * 获取宽带品牌信息
 */
function get_sites($site_id = null) {
    if (is_null($site_id)) {
    	$res = M("site")->where("status = 1")->select();
    } else {
    	$res = M("site")->where("status = 1 and site_id = '$site_id'")->getField("site_name");
    }
    return $res;
}

/**
 * 获取渠道信息
 */
function get_channels($channel_code = null) {
    if (is_null($channel_code)) {
        $res = M("channel")->where("status = 1")->select();
    } else {
        $res = M("channel")->where("channel_code = '$channel_code'")->getField("channel_name");
    }
    return $res;
}

function get_channels_status($status = null, $field = null){
    $list = array(
        1 => array('key'=>'1', 'val'=>'已上线', 'tag'=>'navy'),
        2 => array('key'=>'2', 'val'=>'已下线', 'tag'=>''),
        3 => array('key'=>'3', 'val'=>'未上线', 'tag'=>'warning'),
    );
    if(empty($status) and empty($field)){
        return $list;
    }else{
        if(empty($field)){
            $field = 'val';
        }
        return $list[$status][$field];
    }
}

/**
 * 订单状态
 */
function get_order_status($status = null, $field = null){
    // text-navy 绿（正常、完成）
    // text-success 蓝（处理，流程）
    // text-danger 红（危险、警告）
    // text-info 青（补充、其他）
    // text-warning 橙色（提醒）
    $list = array(
        1 => array('key'=>'1', 'val'=>'未支付', 'tag'=>'danger'),
        5 => array('key'=>'5', 'val'=>'已支付', 'tag'=>'navy'),
        6 => array('key'=>'6', 'val'=>'线下收款', 'tag'=>'navy'),
        7 => array('key'=>'7', 'val'=>'申请退款', 'tag'=>'warning'),
        9 => array('key'=>'9', 'val'=>'已退款', 'tag'=>'danger'),
    );
    if(empty($status) and empty($field)){
        return $list;
    }else{
        if(empty($field)){
            $field = 'val';
        }
        return $list[$status][$field];
    }
}

/**
 * 订单施工状态
 */
function get_work_status($status = null, $field = null){
    $list = array(
        1 => array('key'=>'1', 'val'=>'未处理', 'tag'=>'danger'),
        3 => array('key'=>'3', 'val'=>'处理中', 'tag'=>'warning'),
        4 => array('key'=>'4', 'val'=>'关闭', 'tag'=>'danger'),
        5 => array('key'=>'5', 'val'=>'已派工', 'tag'=>'success'),
        9 => array('key'=>'9', 'val'=>'已完成', 'tag'=>'navy'),
    );
    if(empty($status) and empty($field)){
        return $list;
    }else{
        if(empty($field)){
            $field = 'val';
        }
        return $list[$status][$field];
    }
}

/**
 * 订单关闭原因
 */
function get_close_reason($key = null){
    $coupon = array(
        1 => '安装地址未覆盖',
        2 => '无法与客户取得联系',
        3 => '其他运营商客户',
        4 => '用户无安装需求',
        5 => '用户下错单',
        6 => '用户通过电销或线下办理',
        7 => '重复下单',
        8 => '内部测试订单',
        19 => '其它原因',
    );
    if(is_numeric($key)){
        if(array_key_exists($key, $coupon)){
            return $coupon[$key];
        }else{
            return '未知原因';
        }
    }
    return $coupon;
}

/**
 * 订单来源
 */
function get_order_source($key = null){
    //9 其他
    $sources = array(
        1 => array('key'=>'1', 'val'=>'官网', 'tag'=>'warning'),
        2 => array('key'=>'2', 'val'=>'移动', 'tag'=>'success'),
        3 => array('key'=>'3', 'val'=>'APP', 'tag'=>'danger'),
        4 => array('key'=>'4', 'val'=>'微信', 'tag'=>'navy'),
        5 => array('key'=>'5', 'val'=>'小程序', 'tag'=>'navy'),
    );
    if(!empty($key)){
        if(array_key_exists($key, $sources)){
            return $sources[$key];
        }else{
            return '未知';
        }
    }
    return $sources;
}

/**
 * 发票类型
 */
function get_invoice_type($key = null){
    $list = array(
        '0' => '不开发票',
        '1' => '个人',
        '2' => '公司',
    );
    if(is_numeric($key)){
        if(array_key_exists($key, $list)){
            return $list[$key];
        }else{
            return '未知';
        }
    }
    return $list;
}

/**
 *获取分类
 */
function get_shop_category(){
    return M('cat')->select();
}

/**
 * 产品状态
 */
function product_status($status=null, $field=null){
    $list = array(
        1 => array('key'=>'1', 'val'=>'上架', 'tag'=>'navy'),
        2 => array('key'=>'2', 'val'=>'下架', 'tag'=>'danger'),
        3 => array('key'=>'3', 'val'=>'缺货', 'tag'=>'success'),
        4 => array('key'=>'4', 'val'=>'已过期', 'tag'=>'success'),
    );
    if(empty($status) and empty($field)){
        return $list;
    }else{
        if(empty($field)){
            $field = 'val';
        }
        return $list[$status][$field];
    }
}


/**
 * 地址路径
 * @param $group
 * @param $img_name
 * @param string $prefix
 * @return string
 */
function get_img_url($group, $img_name, $prefix = ''){
    $path = '/Public/upload/images/'.$group;
    if($prefix) {
        $url = $path.'/'.$prefix.'_'.$img_name;
    }else{
        $url = $path.'/'.$img_name;
    }
    return $url;
}

/**
 * 生成页面的TR
 */
function draw_attr_head($attr_ids){
    $res = "";
    $list = explode('_', $attr_ids);
    $db = D('Attr')->getList();
    $arr = array();
    foreach ($list as $v) {
        $attr = $db[$v];
        $arr[$attr['group_id']] = $attr['group_name'];
    }
    foreach ($arr as $v) {
        $res .= "<th>$v</th>";
    }
    return $res;
}

function draw_attr_info($attr_ids){
    $res = "";
    $list = D('Attr')->getKVList($attr_ids);
    foreach ($list as $k=>$v) {
        $res .= "<td>$v</td>";
    }
    return $res;
}

function get_shop_brand_by_cate($cate_id){
    if(!empty($cate_id)) {
        $brand_info = M("brand b")->field('b.brand_id,b.brand_name,b.status,cb.id,cb.cat_id')->join("left join xp_cat_brand cb on b.brand_id=cb.brand_id")
            ->where("cb.cat_id = $cate_id")->select();
        return $brand_info;
    }
}

function get_staff_group($key = null){
    $group = array(
        1 => '销售',
        2 => '渠道',
    );
    if(!empty($key)){
        if(array_key_exists($key, $group)){
            return $group[$key];
        }else{
            return '未知';
        }
    }
    return $group;
}

/* 优惠券类型 */
function get_coupon_type($key = null){
    $coupon = array(
        1 => '折扣券',
        2 => '满减券',
        3 => '无门槛券',
    );
    if(!empty($key)){
        if(array_key_exists($key, $coupon)){
            return $coupon[$key];
        }else{
            return '未知';
        }
    }
    return $coupon;
}

/*
* 获得本月往前的12个月
*/
function getMonth(){

    $month = array();
    date_default_timezone_set('Asia/Shanghai');
    
    //得到系统的年月  
    $tmp_date = date("Ym");  
    //切割出年份  
    $tmp_year = substr($tmp_date,0,4);  
    //切割出月份  
    $tmp_mon = substr($tmp_date,4,2); 

    for($i = 11;$i >= 0; $i--){
        $month[] = date("Y-m",mktime(0,0,0,$tmp_mon-$i,1,$tmp_year));
    }
    
    return $month;
}
/**
 * 报装 状态
 */
function get_fix_status($status = null, $field = null){
    $list = array(
        1 => array('key'=>'1', 'val'=>'待处理', 'tag'=>'danger'),
        2 => array('key'=>'2', 'val'=>'已派工', 'tag'=>'navy'),
        3 => array('key'=>'3', 'val'=>'已关闭', 'tag'=>''),
    );
    if(empty($status) and empty($field)){
        return $list;
    }else{
        if(empty($field)){
            $field = 'val';
        }
        return $list[$status][$field];
    }
}



/**
 * 表单里初始化数据
 * @param $field
 * @param array $data
 * @param string $defalut
 * @return mixed|string
 */
function initValue($field, $data = [], $defalut = '') {
    return isset ( $data [$field] ) ? $data [$field] : $defalut;
}


// 微信端的错误码转中文解释
function error_msg($return, $more_tips = '') {
    $msg = array (
        '-1' => '系统繁忙，此时请开发者稍候再试',
        '0' => '请求成功',
        '40001' => '获取access_token时AppSecret错误，或者access_token无效。请开发者认真比对AppSecret的正确性，或查看是否正在为恰当的公众号调用接口',
        '40002' => '不合法的凭证类型',
        '40003' => '不合法的OpenID，请开发者确认OpenID（该用户）是否已关注公众号，或是否是其他公众号的OpenID',
        '40004' => '不合法的媒体文件类型',
        '40005' => '不合法的文件类型',
        '40006' => '不合法的文件大小',
        '40007' => '不合法的媒体文件id',
        '40008' => '不合法的消息类型',
        '40009' => '不合法的图片文件大小',
        '40010' => '不合法的语音文件大小',
        '40011' => '不合法的视频文件大小',
        '40012' => '不合法的缩略图文件大小',
        '40013' => '不合法的AppID，请开发者检查AppID的正确性，避免异常字符，注意大小写',
        '40014' => '不合法的access_token，请开发者认真比对access_token的有效性（如是否过期），或查看是否正在为恰当的公众号调用接口',
        '40015' => '不合法的菜单类型',
        '40016' => '不合法的按钮个数',
        '40017' => '不合法的按钮个数',
        '40018' => '不合法的按钮名字长度',
        '40019' => '不合法的按钮KEY长度',
        '40020' => '不合法的按钮URL长度',
        '40021' => '不合法的菜单版本号',
        '40022' => '不合法的子菜单级数',
        '40023' => '不合法的子菜单按钮个数',
        '40024' => '不合法的子菜单按钮类型',
        '40025' => '不合法的子菜单按钮名字长度',
        '40026' => '不合法的子菜单按钮KEY长度',
        '40027' => '不合法的子菜单按钮URL长度',
        '40028' => '不合法的自定义菜单使用用户',
        '40029' => '不合法的oauth_code',
        '40030' => '不合法的refresh_token',
        '40031' => '不合法的openid列表',
        '40032' => '不合法的openid列表长度',
        '40033' => '不合法的请求字符，不能包含\uxxxx格式的字符',
        '40035' => '不合法的参数',
        '40038' => '不合法的请求格式',
        '40039' => '不合法的URL长度',
        '40050' => '不合法的分组id',
        '40051' => '分组名字不合法',
        '40117' => '分组名字不合法',
        '40118' => 'media_id大小不合法',
        '40119' => 'button类型错误',
        '40120' => 'button类型错误',
        '40121' => '不合法的media_id类型',
        '40132' => '微信号不合法',
        '40137' => '不支持的图片格式',
        '41001' => '缺少access_token参数',
        '41002' => '缺少appid参数',
        '41003' => '缺少refresh_token参数',
        '41004' => '缺少secret参数',
        '41005' => '缺少多媒体文件数据',
        '41006' => '缺少media_id参数',
        '41007' => '缺少子菜单数据',
        '41008' => '缺少oauth code',
        '41009' => '缺少openid',
        '42001' => 'access_token超时，请检查access_token的有效期，请参考基础支持-获取access_token中，对access_token的详细机制说明',
        '42002' => 'refresh_token超时',
        '42003' => 'oauth_code超时',
        '43001' => '需要GET请求',
        '43002' => '需要POST请求',
        '43003' => '需要HTTPS请求',
        '43004' => '需要接收者关注',
        '43005' => '需要好友关系',
        '44001' => '多媒体文件为空',
        '44002' => 'POST的数据包为空',
        '44003' => '图文消息内容为空',
        '44004' => '文本消息内容为空',
        '45001' => '多媒体文件大小超过限制',
        '45002' => '消息内容超过限制',
        '45003' => '标题字段超过限制',
        '45004' => '描述字段超过限制',
        '45005' => '链接字段超过限制',
        '45006' => '图片链接字段超过限制',
        '45007' => '语音播放时间超过限制',
        '45008' => '图文消息超过限制',
        '45009' => '接口调用超过限制',
        '45010' => '创建菜单个数超过限制',
        '45015' => '回复时间超过限制',
        '45016' => '系统分组，不允许修改',
        '45017' => '分组名字过长',
        '45018' => '分组数量超过上限',
        '46001' => '不存在媒体数据',
        '46002' => '不存在的菜单版本',
        '46003' => '不存在的菜单数据',
        '46004' => '不存在的用户',
        '47001' => '解析JSON/XML内容错误',
        '48001' => 'api功能未授权，请确认公众号已获得该接口，可以在公众平台官网-开发者中心页中查看接口权限',
        '50001' => '用户未授权该api',
        '50002' => '用户受限，可能是违规后接口被封禁',
        '61451' => '参数错误(invalid parameter)',
        '61452' => '无效客服账号(invalid kf_account)',
        '61453' => '客服帐号已存在(kf_account exsited)',
        '61454' => '客服帐号名长度超过限制(仅允许10个英文字符，不包括@及@后的公众号的微信号)(invalid kf_acount length)',
        '61455' => '客服帐号名包含非法字符(仅允许英文+数字)(illegal character in kf_account)',
        '61456' => '客服帐号个数超过限制(10个客服账号)(kf_account count exceeded)',
        '61457' => '无效头像文件类型(invalid file type)',
        '61450' => '系统错误(system error)',
        '61500' => '日期格式错误',
        '61501' => '日期范围错误',
        '9001001' => 'POST数据参数不合法',
        '9001002' => '远端服务不可用',
        '9001003' => 'Ticket不合法',
        '9001004' => '获取摇周边用户信息失败',
        '9001005' => '获取商户信息失败',
        '9001006' => '获取OpenID失败',
        '9001007' => '上传文件缺失',
        '9001008' => '上传素材的文件类型不合法',
        '9001009' => '上传素材的文件尺寸不合法',
        '9001010' => '上传失败',
        '9001020' => '帐号不合法',
        '9001021' => '已有设备激活率低于50%，不能新增设备',
        '9001022' => '设备申请数不合法，必须为大于0的数字',
        '9001023' => '已存在审核中的设备ID申请',
        '9001024' => '一次查询设备ID数量不能超过50',
        '9001025' => '设备ID不合法',
        '9001026' => '页面ID不合法',
        '9001027' => '页面参数不合法',
        '9001028' => '一次删除页面ID数量不能超过10',
        '9001029' => '页面已应用在设备中，请先解除应用关系再删除',
        '9001030' => '一次查询页面ID数量不能超过50',
        '9001031' => '时间区间不合法',
        '9001032' => '保存设备与页面的绑定关系参数错误',
        '9001033' => '门店ID不合法',
        '9001034' => '设备备注信息过长',
        '9001035' => '设备申请参数不合法',
        '9001036' => '查询起始值begin不合法'
    );

    if ($more_tips) {
        $res = $more_tips . ': ';
    } else {
        $res = '';
    }
    if (isset ( $msg [$return ['errcode']] )) {
        $res .= $msg [$return ['errcode']];
    } else {
        $res .= $return ['errmsg'];
    }

    $res .= ', 返回码：' . $return ['errcode'];

    return $res;
}

/**
 * 取一个二维数组中的每个数组的固定的键知道的值来形成一个新的一维数组
 *
 * @param $pArray 一个二维数组
 * @param $pKey 数组的键的名称
 * @return 返回新的一维数组
 */
function getSubByKey($pArray, $pKey = "", $pCondition = "") {
    $result = array ();
    if (is_array ( $pArray )) {
        foreach ( $pArray as $temp_array ) {
            if (is_object ( $temp_array )) {
                $temp_array = ( array ) $temp_array;
            }
            if (("" != $pCondition && $temp_array [$pCondition [0]] == $pCondition [1]) || "" == $pCondition) {
                $result [] = ("" == $pKey) ? $temp_array : isset ( $temp_array [$pKey] ) ? $temp_array [$pKey] : "";
            }
        }
        return $result;
    } else {
        return false;
    }
}



/**
 * 日志类型
 */
function get_log_type($key = null){
    $type = array(
        1 => array('key'=>'1', 'val'=>'消息'),
        2 => array('key'=>'2', 'val'=>'接口'),
    );
    if(!empty($key)){
        if(array_key_exists($key, $type)){
            return $type[$key];
        }else{
            return '未知';
        }
    }
    return $type;
}

/**
 * 获取公众号信息
 */
function get_wechat_info($wechat_id = null) {
    if (is_null($wechat_id)) {
        $res = M("apps")->select();
    } else {
        $res = M("apps")->where(" public_id = '$wechat_id'")->getField("public_name");
    }
    return $res;
}
