<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

/**
 * 系统公共库文件
 * 主要定义系统公共函数库
 */

function getRedirectUrl(){
    return 'http://'.$_SERVER['HTTP_HOST'].'/home/page?url=';
}

/**
 * @param $type
 * @param string $record
 * @param bool $incr
 * @return int|mixed
 * 获取当前接口访问量
 */
function apiAccessCount($type,$record='day',$incr = true){

    //设置缓存记录不过期
    C('SESSION_EXPIRE',0);
    print_r($type);
    $date = date("Ymd");
    if($record == 'day'){
        //每天记录一次
        $key = "apiAccessCount:$date:$type";
    }else{
        //
        $key = "apiAccessCount:$type";
    }

    $count = S($key);
    if(empty($count)){
        $count = 0;
    }

    if($incr){
        //增加次数
        $count++;
        S($key, $count);
    }

    return $count;
}


/**
 * @param $type
 * @return mixed
 * 根据接口类型获取配置信息
 */
function getBaiduConfig($type){

    //查询当天该接口调用次数
    $requestCount = apiAccessCount($type);

    //使用第几个账号
    $acountNum = floor($requestCount/500) + 1;

    $key = 'account_num:'.$acountNum;
    $config = S($key);
    if(empty($config)){
        //缓存中没有，从数据库中取
        $param['status'] = 1;
        $param['type'] = 1;
        $config = M('api_account')->where($param)->page($acountNum,1)->find();
        S($key,$config);
    }
    return $config;
}

/**
 * @param $str
 * @return bool
 * 判断字符串中是否有手机号
 */
function isPhoneNum($str){
    $pattern = "/^1[3456789]\d{9}$/";
    if(preg_match($pattern,$str)){
        return true;
    }else{
        return false;
    }
}

// 过滤掉emoji表情
function emoji_reject($text)
{
    $len = mb_strlen($text);
    $new_text = '';
    for ($i = 0; $i < $len; $i++) {
        $word = mb_substr($text, $i, 1);
        if (strlen($word) <= 3) {
            $new_text .= $word;
        }
    }
    return $new_text;
}




/**
 * object 转 array
 * @param $obj
 * @return mixed
 *
 */
function object_to_array($obj){
    $_arr=is_object($obj)?get_object_vars($obj):$obj;
    foreach($_arr as $key=>$val){
        $val=(is_array($val))||is_object($val)?object_to_array($val):$val;
        $arr[$key]=$val;
    }
    return $arr;
}


/**
 * @param $score
 * @param $precision
 * @return string
 * 小数转百分数
 */
function double2percent($score, $precision = 1){
    $value = round($score * 100, $precision);
    return $value.'%';
}

/**
 * @return bool
 * 判断当前时间是否是工作时间
 * 工作时间9时到21时
 */
function isWorkTime(){
    $hour = date('G');
    if($hour >= 9 && $hour <= 21){
        return true;
    }else{
        return false;
    }
}






/**
 * 字符串转换为数组，主要用于把分隔符调整到第二个参数
 *
 * @param string $str
 *        	要分割的字符串
 * @param string $glue
 *        	分割符
 * @return array
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function str2arr($str = '', $glue = ',') {
	return explode ( $glue, $str );
}

/**
 * 数组转换为字符串，主要用于把分隔符调整到第二个参数
 *
 * @param array $arr
 *        	要连接的数组
 * @param string $glue
 *        	分割符
 * @return string
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function arr2str($arr = [], $glue = ',') {
	return implode ( $glue, $arr );
}

/**
 * 字符串截取，支持中文和其他编码
 *
 * @access public
 * @param string $str
 *        	需要转换的字符串
 * @param string $start
 *        	开始位置
 * @param string $length
 *        	截取长度
 * @param string $charset
 *        	编码格式
 * @param string $suffix
 *        	截断显示字符
 * @return string
 */
function msubstr($str, $start = 0, $length, $charset = "utf-8", $suffix = true) {
	if (function_exists ( "mb_substr" ))
		$slice = mb_substr ( $str, $start, $length, $charset );
	elseif (function_exists ( 'iconv_substr' )) {
		$slice = iconv_substr ( $str, $start, $length, $charset );
		if (false === $slice) {
			$slice = '';
		}
	} else {
		$re ['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
		$re ['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
		$re ['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
		$re ['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
		preg_match_all ( $re [$charset], $str, $match );
		$slice = join ( "", array_slice ( $match [0], $start, $length ) );
	}
	
	return $suffix && $str != $slice ? $slice . '...' : $slice;
}
/**
 * 方法增强，根据$length自动判断是否应该显示...
 * 字符串截取，支持中文和其他编码
 * QQ:125682133
 *
 * @access public
 * @param string $str
 *        	需要转换的字符串
 * @param string $start
 *        	开始位置
 * @param string $length
 *        	截取长度
 * @param string $charset
 *        	编码格式
 * @param string $suffix
 *        	截断显示字符
 * @return string
 */
function msubstr_local($str, $start = 0, $length, $charset = "utf-8") {
	if (function_exists ( "mb_substr" ))
		$slice = mb_substr ( $str, $start, $length, $charset );
	elseif (function_exists ( 'iconv_substr' )) {
		$slice = iconv_substr ( $str, $start, $length, $charset );
		if (false === $slice) {
			$slice = '';
		}
	} else {
		$re ['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
		$re ['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
		$re ['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
		$re ['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
		preg_match_all ( $re [$charset], $str, $match );
		
		$slice = join ( "", array_slice ( $match [0], $start, $length ) );
	}
	return (strlen ( $str ) > strlen ( $slice )) ? $slice . '...' : $slice;
}
/**
 * 系统加密方法
 *
 * @param string $data
 *        	要加密的字符串
 * @param string $key
 *        	加密密钥
 * @param int $expire
 *        	过期时间 单位 秒
 * @return string
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function think_encrypt($data, $key = '', $expire = 0) {
	$key = md5 ( empty ( $key ) ? C ( 'DATA_AUTH_KEY' ) : $key );
	
	$data = base64_encode ( $data );
	$x = 0;
	$len = strlen ( $data );
	$l = strlen ( $key );
	$char = '';
	
	for($i = 0; $i < $len; $i ++) {
		if ($x == $l)
			$x = 0;
		$char .= substr ( $key, $x, 1 );
		$x ++;
	}
	
	$str = sprintf ( '%010d', $expire ? $expire + time () : 0 );
	
	for($i = 0; $i < $len; $i ++) {
		$str .= chr ( ord ( substr ( $data, $i, 1 ) ) + (ord ( substr ( $char, $i, 1 ) )) % 256 );
	}
	return str_replace ( array (
			'+',
			'/',
			'=' 
	), array (
			'-',
			'_',
			'' 
	), base64_encode ( $str ) );
}

/**
 * 系统解密方法
 *
 * @param string $data
 *        	要解密的字符串 （必须是think_encrypt方法加密的字符串）
 * @param string $key
 *        	加密密钥
 * @return string
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function think_decrypt($data, $key = '') {
	$key = md5 ( empty ( $key ) ? C ( 'DATA_AUTH_KEY' ) : $key );
	$data = str_replace ( array (
			'-',
			'_' 
	), array (
			'+',
			'/' 
	), $data );
	$mod4 = strlen ( $data ) % 4;
	if ($mod4) {
		$data .= substr ( '====', $mod4 );
	}
	$data = base64_decode ( $data );
	$expire = substr ( $data, 0, 10 );
	$data = substr ( $data, 10 );
	
	if ($expire > 0 && $expire < time ()) {
		return '';
	}
	$x = 0;
	$len = strlen ( $data );
	$l = strlen ( $key );
	$char = $str = '';
	
	for($i = 0; $i < $len; $i ++) {
		if ($x == $l)
			$x = 0;
		$char .= substr ( $key, $x, 1 );
		$x ++;
	}
	
	for($i = 0; $i < $len; $i ++) {
		if (ord ( substr ( $data, $i, 1 ) ) < ord ( substr ( $char, $i, 1 ) )) {
			$str .= chr ( (ord ( substr ( $data, $i, 1 ) ) + 256) - ord ( substr ( $char, $i, 1 ) ) );
		} else {
			$str .= chr ( ord ( substr ( $data, $i, 1 ) ) - ord ( substr ( $char, $i, 1 ) ) );
		}
	}
	return base64_decode ( $str );
}

/**
 * 数据签名认证
 *
 * @param array $data
 *        	被认证的数据
 * @return string 签名
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function data_auth_sign($data) {
	// 数据类型检测
	if (! is_array ( $data )) {
		$data = ( array ) $data;
	}
	ksort ( $data ); // 排序
	$code = http_build_query ( $data ); // url编码并生成query字符串
	$sign = sha1 ( $code ); // 生成签名
	return $sign;
}

/**
 * 对查询结果集进行排序
 *
 * @access public
 * @param array $list
 *        	查询结果
 * @param string $field
 *        	排序的字段名
 * @param array $sortby
 *        	排序类型
 *        	asc正向排序 desc逆向排序 nat自然排序
 * @return array
 *
 */
function list_sort_by($list, $field, $sortby = 'asc') {
	if (is_array ( $list )) {
		$refer = $resultSet = array ();
		foreach ( $list as $i => $data )
			$refer [$i] = &$data [$field];
		switch ($sortby) {
			case 'asc' : // 正向排序
				asort ( $refer );
				break;
			case 'desc' : // 逆向排序
				arsort ( $refer );
				break;
			case 'nat' : // 自然排序
				natcasesort ( $refer );
				break;
		}
		foreach ( $refer as $key => $val )
			$resultSet [] = &$list [$key];
		return $resultSet;
	}
	return false;
}

/**
 * 把返回的数据集转换成Tree
 *
 * @param array $list
 *        	要转换的数据集
 * @param string $pid
 *        	parent标记字段
 * @param string $level
 *        	level标记字段
 * @return array
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function list_to_tree($list, $pk = 'id', $pid = 'pid', $child = '_child', $root = 0) {
	// 创建Tree
	$tree = array ();
	if (is_array ( $list )) {
		// 创建基于主键的数组引用
		$refer = array ();
		foreach ( $list as $key => $data ) {
			$refer [$data [$pk]] = & $list [$key];
		}
		foreach ( $list as $key => $data ) {
			// 判断是否存在parent
			$parentId = $data [$pid];
			if ($root == $parentId) {
				$tree [] = & $list [$key];
			} else {
				if (isset ( $refer [$parentId] )) {
					$parent = & $refer [$parentId];
					$parent [$child] [] = & $list [$key];
				}
			}
		}
	}
	return $tree;
}

/**
 * 将list_to_tree的树还原成列表
 *
 * @param array $tree
 *        	原来的树
 * @param string $child
 *        	孩子节点的键
 * @param string $order
 *        	排序显示的键，一般是主键 升序排列
 * @param array $list
 *        	过渡用的中间数组，
 * @return array 返回排过序的列表数组
 * @author 凡星
 */
function tree_to_list($tree, $child = '_child', $order = 'id', &$list = array()) {
	if (is_array ( $tree )) {
		$refer = array ();
		foreach ( $tree as $key => $value ) {
			$reffer = $value;
			if (isset ( $reffer [$child] )) {
				unset ( $reffer [$child] );
				tree_to_list ( $value [$child], $child, $order, $list );
			}
			$list [] = $reffer;
		}
		$list = list_sort_by ( $list, $order, $sortby = 'asc' );
	}
	return $list;
}
/**
 * 树形列表
 *
 * @param array $list
 *        	数据库原始数据
 * @param array $res_list
 *        	返回的结果数组
 * @param int $pid
 *        	上级ID
 * @param int $level
 *        	当前处理的层级
 */
function list_tree($list, &$res_list, $pid = 0, $level = 0) {
	foreach ( $list as $k => $v ) {
		if (intval ( $v ['pid'] ) != $pid)
			continue;
		
		if ($level > 0) {
			$space = '';
			for($i = 1; $i < $level; $i ++) {
				$space .= '──';
			}
			$v ['title'] = '├──' . $space . $v ['title'];
		}
		
		$v ['level'] = $level;
		$res_list [] = $v;
		unset ( $list [$k] );
		
		list_tree ( $list, $res_list, $v ['id'], $level + 1 );
	}
}
/**
 * 格式化字节大小
 *
 * @param number $size
 *        	字节数
 * @param string $delimiter
 *        	数字和单位分隔符
 * @return string 格式化后的带单位的大小
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function format_bytes($size, $delimiter = '') {
	$units = array (
			'B',
			'KB',
			'MB',
			'GB',
			'TB',
			'PB' 
	);
	for($i = 0; $size >= 1024 && $i < 5; $i ++)
		$size /= 1024;
	return round ( $size, 2 ) . $delimiter . $units [$i];
}

/**
 * 设置跳转页面URL
 * 使用函数再次封装，方便以后选择不同的存储方式（目前使用cookie存储）
 *
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function set_redirect_url($url) {
	cookie ( 'redirect_url', $url );
}

/**
 * 获取跳转页面URL
 *
 * @return string 跳转页URL
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function get_redirect_url() {
	$url = cookie ( 'redirect_url' );
	return empty ( $url ) ? __APP__ : $url;
}



/**
 * 时间戳格式化
 *
 * @param int $time        	
 * @return string 完整的时间显示
 * @author huajie <banhuajie@163.com>
 */
function time_format($time = NULL, $format = 'Y-m-d H:i') {
	if (empty ( $time ))
		return '';
	
	$time = $time === NULL ? NOW_TIME : intval ( $time );
	return date ( $format, $time );
}
function day_format($time = NULL) {
	return time_format ( $time, 'Y-m-d' );
}
function hour_format($time = NULL) {
	return time_format ( $time, 'H:i' );
}
function time_offset($time = NULL) {
	if (empty ( $time ))
		return '00:00';
	
	$mod = $time % 60;
	$min = ($time - $mod) / 60;
	
	$mod < 10 && $mod = '0' . $mod;
	$min < 10 && $min = '0' . $min;
	
	return $min . ':' . $mod;
}


// 处理带Emoji的数据，type=0表示写入数据库前的emoji转为HTML，为1时表示HTML转为emoji码
function deal_emoji($msg, $type = 1) {
	if ($type == 0) {
		$msg = urlencode ( $msg );
		$msg = json_encode ( $msg );
	} else {
		
		$msg = preg_replace_callback ( "#\\\u([0-9a-f]+)#i", function ($r) {
			return iconv ( 'UCS-2', 'UTF-8', pack ( 'H4', '\\1' ) );
		}, $msg );
		$msg = urldecode ( $msg );
		// $msg = json_decode ( $msg );
		// dump($msg);
		$msg = str_replace ( '"', "", $msg );
	}
	
	return $msg;
}
function get_mult_username($uids) {
	is_array ( $uids ) || $uids = explode ( ',', $uids );
	
	$uids = array_filter ( $uids );
	if (empty ( $uids )) {
		return;
	}
	
	foreach ( $uids as $uid ) {
		$name = get_truename ( $uid );
		if ($name) {
			$nameArr [] = $name;
		}
	}
	
	return implode ( ', ', $nameArr );
}

/**
 * 获取数据的所有子孙数据的id值
 *
 * @author 朱亚杰 <xcoolcc@gmail.com>
 */
function get_stemma($pids, Model &$model, $field = 'id') {
	$collection = array ();
	
	// 非空判断
	if (empty ( $pids )) {
		return $collection;
	}
	
	if (is_array ( $pids )) {
		$pids = trim ( implode ( ',', $pids ), ',' );
	}
	$result = $model->field ( $field )->where ( array (
			'pid' => array (
					'IN',
					( string ) $pids 
			) 
	) )->select ();
	$child_ids = array_column ( ( array ) $result, 'id' );
	
	while ( ! empty ( $child_ids ) ) {
		$collection = array_merge ( $collection, $result );
		$result = $model->field ( $field )->where ( array (
				'pid' => array (
						'IN',
						$child_ids 
				) 
		) )->select ();
		$child_ids = array_column ( ( array ) $result, 'id' );
	}
	return $collection;
}

/**
 * 判断关键词是否唯一
 *
 * @author weiphp
 */
function keyword_unique($keyword) {
	if (empty ( $keyword ))
		return false;
	
	$map ['keyword'] = $keyword;
	$info = M ( 'keyword' )->where ( $map )->find ();
	return empty ( $info );
}
// 分析枚举类型配置值 格式 a:名称1,b:名称2
// weiphp 该函数是从admin的function的文件里提取这到里
function parse_config_attr($string, $preg = '/[\s;\r\n]+/') {
	$array = preg_split ( $preg, trim ( $string, ",;\s\r\n" ) );
	if (strpos ( $string, ':' )) {
		$value = array ();
		foreach ( $array as $val ) {
			list ( $k, $v ) = explode ( ':', $val, 2 );
			$value [$k] = $v;
		}
	} else {
		$value = $array;
	}
	foreach ( $value as &$vo ) {
		$vo = clean_hide_attr ( $vo );
	}
	// dump($value);
	return $value;
}
function clean_hide_attr($str) {
	$arr = explode ( '|', $str );
	return $arr [0];
}
function get_hide_attr($str) {
	$arr = explode ( '|', $str );
	return isset ( $arr [1] ) ? $arr [1] : '';
}
// 分析枚举类型字段值 格式 a:名称1,b:名称2
function parse_field_attr($string, $preg = '/[\s;\r\n]+/') {
	if (0 === strpos ( $string, ':' )) {
		// 采用函数定义
		return eval ( substr ( $string, 1 ) . ';' );
	}
	$array = preg_split ( $preg, trim ( $string, ",;\r\n" ) );
	// dump($array);
	if (strpos ( $string, ':' )) {
		$value = array ();
		foreach ( $array as $val ) {
			list ( $k, $v ) = explode ( ':', $val );
			empty ( $v ) && $v = $k;
			$k = clean_hide_attr ( $k );
			$value [$k] = $v;
		}
	} else {
		$value = $array;
	}
	// dump($value);
	return $value;
}
function addWeixinLog($data, $data_post = '') {
	add_debug_log ( $data, $data_post );
}

// 判断是否是在微信浏览器里
function isWeixinBrowser($from = 0) {
	if ((! $from && defined ( 'IN_WEIXIN' ) && IN_WEIXIN) || isset ( $_GET ['is_stree'] ))
		return true;
	
	$agent = $_SERVER ['HTTP_USER_AGENT'];
	if (! strpos ( $agent, "icroMessenger" )) {
		return false;
	}
	return true;
}
// php获取当前访问的完整url地址
function GetCurUrl() {
	$url = HTTP_PREFIX;
	if ($_SERVER ['SERVER_PORT'] != '80' && $_SERVER ['SERVER_PORT'] != '443') {
		$url .= $_SERVER ['HTTP_HOST'] . ':' . $_SERVER ['SERVER_PORT'] . $_SERVER ['REQUEST_URI'];
	} else {
		$url .= $_SERVER ['HTTP_HOST'] . $_SERVER ['REQUEST_URI'];
	}
	// 兼容后面的参数组装
	if (stripos ( $url, '?' ) === false) {
		$url .= '?t=' . time ();
	}
	return $url;
}
// 获取当前用户的OpenId
function get_openid($openid = NULL) {
	$token = get_token ();
	$request_openid = I ( 'openid' );
	if ($openid !== NULL && $openid != '-1' && $openid != '-2') {
		session ( 'openid_' . $token, $openid );
	} elseif (! empty ( $request_openid ) && $request_openid != '-1' && $request_openid != '-2') {
		session ( 'openid_' . $token, $request_openid );
	}
	$openid = session ( 'openid_' . $token );
	
	$isWeixinBrowser = isWeixinBrowser ();
	if ((empty ( $openid ) || $openid == '-1') && $isWeixinBrowser && $request_openid != '-2' && IS_GET && ! IS_AJAX) {
		$callback = GetCurUrl ();
		$openid = OAuthWeixin ( $callback, $token, true );
		if ($openid != false && $openid != '-2') {
			session ( 'openid_' . $token, $openid );
		}
	}
	if (empty ( $openid )) {
		return '-1';
	}
	return $openid;
}

/**
 * 执行SQL文件
 */
function execute_sql_file($sql_path) {
	// 读取SQL文件
	$sql = wp_file_get_contents ( $sql_path );
	$sql = str_replace ( "\r", "\n", $sql );
	$sql = explode ( ";\n", $sql );
	
	// 替换表前缀
	$orginal = 'wp_';
	$prefix = C ( 'DB_PREFIX' );
	$sql = str_replace ( "{$orginal}", "{$prefix}", $sql );
	
	// 开始安装
	foreach ( $sql as $value ) {
		$value = trim ( $value );
		if (empty ( $value ) || strpos ( $sql, $prefix . 'attribute ' ) !== false)
			continue;
		
		$res = M ()->execute ( $value );
		// dump($res);
		// dump(M()->getLastSql());
	}
}


// 截取内容
function getShort($str, $length = 40, $ext = '') {
	$str = filter_line_tab ( $str );
	$str = htmlspecialchars ( $str );
	$str = strip_tags ( $str );
	$str = htmlspecialchars_decode ( $str );
	$strlenth = 0;
	$out = '';
	preg_match_all ( "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/", $str, $match );
	$output = '';
	foreach ( $match [0] as $v ) {
		preg_match ( "/[\xe0-\xef][\x80-\xbf]{2}/", $v, $matchs );
		if (! empty ( $matchs [0] )) {
			$strlenth += 1;
		} elseif (is_numeric ( $v )) {
			$strlenth += 0.5; // 字符字节长度比例 汉字为1
		} else {
			$strlenth += 0.5; // 字符字节长度比例 汉字为1
		}
		
		if ($strlenth > $length) {
			$output .= $ext;
			break;
		}
		
		$output .= $v;
	}
	return $output;
}
// 过滤非法html标签 去掉换行符
function filter_line_tab($text) {
	$text = str_replace ( array (
			"\r\n",
			"\r",
			"\n",
			" " 
	), '', $text );
	// 过滤标签
	$text = nl2br ( $text );
	$text = real_strip_tags ( $text );
	$text = addslashes ( $text );
	$text = trim ( $text );
	return addslashes ( $text );
}
function real_strip_tags($str, $allowable_tags = "") {
	$str = stripslashes ( htmlspecialchars_decode ( $str ) );
	return strip_tags ( $str, $allowable_tags );
}
// 防超时的file_get_contents改造函数
function wp_file_get_contents($url) {
	return get_data ( $url, 30 );
}

// 全局的安全过滤函数
function safe($text, $type = 'html') {
	// 无标签格式
	$text_tags = '';
	// 只保留链接
	$link_tags = '<a>';
	// 只保留图片
	$image_tags = '<img>';
	// 只存在字体样式
	$font_tags = '<i><b><u><s><em><strong><font><big><small><sup><sub><bdo><h1><h2><h3><h4><h5><h6>';
	// 标题摘要基本格式
	$base_tags = $font_tags . '<p><br><hr><a><img><map><area><pre><code><q><blockquote><acronym><cite><ins><del><center><strike><section><header><footer><article><nav><audio><video>';
	// 兼容Form格式
	$form_tags = $base_tags . '<form><input><textarea><button><select><optgroup><option><label><fieldset><legend>';
	// 内容等允许HTML的格式
	$html_tags = $base_tags . '<meta><ul><ol><li><dl><dd><dt><table><caption><td><th><tr><thead><tbody><tfoot><col><colgroup><div><span><object><embed><param>';
	// 全HTML格式
	$all_tags = $form_tags . $html_tags . '<!DOCTYPE><html><head><title><body><base><basefont><script><noscript><applet><object><param><style><frame><frameset><noframes><iframe>';
	// 过滤标签
	$text = html_entity_decode ( $text, ENT_QUOTES, 'UTF-8' );
	$text = strip_tags ( $text, ${$type . '_tags'} );
	
	// 过滤攻击代码
	if ($type != 'all') {
		// 过滤危险的属性，如：过滤on事件lang js
		while ( preg_match ( '/(<[^><]+)(ondblclick|onclick|onload|onerror|unload|onmouseover|onmouseup|onmouseout|onmousedown|onkeydown|onkeypress|onkeyup|onblur|onchange|onfocus|codebase|dynsrc|lowsrc)([^><]*)/i', $text, $mat ) ) {
			$text = str_ireplace ( $mat [0], $mat [1] . $mat [3], $text );
		}
		while ( preg_match ( '/(<[^><]+)(window\.|javascript:|js:|about:|file:|document\.|vbs:|cookie)([^><]*)/i', $text, $mat ) ) {
			$text = str_ireplace ( $mat [0], $mat [1] . $mat [3], $text );
		}
	}
	return $text;
}
// 创建多级目录
function mkdirs($dir) {
	if (! is_dir ( $dir )) {
		if (! mkdirs ( dirname ( $dir ) )) {
			return false;
		}
		if (! mkdir ( $dir, 0777 )) {
			return false;
		}
	}
	return true;
}
// 组装查询条件
function getIdsForMap($ids, $map = array(), $field = 'id') {
	$ids = safe ( $ids );
	$ids = preg_split ( '/[\s,;]+/', $ids ); // 支持以空格tab逗号分号分割ID
	$ids = array_filter ( $ids );
	if (empty ( $ids ))
		return $map;
	
	$map [$field] = array (
			'in',
			$ids 
	);
	
	return $map;
}
// 获取通用分类级联菜单的标题，改方法仅支持级联的数据源配置成数据表common_category的情况，其它情况需要使用下面的getCascadeTitle方法
function getCommonCategoryTitle($ids) {
	$extra = 'type=db&table=common_category';
	
	return getCascadeTitle ( $ids, $extra );
}
// 获取级联菜单的标题的通用处理方法
function getCascadeTitle($ids, $extra) {
	$idArr = explode ( ',', $ids );
	$idArr = array_filter ( $idArr );
	if (empty ( $idArr ))
		return '';
	
	parse_str ( $extra, $arr );
	if ($arr ['type'] == 'db') {
		$table = $arr ['table'];
		unset ( $arr ['type'], $arr ['table'] );
		
		$arr ['token'] = get_token ();
		$arr ['id'] = array (
				'in',
				$idArr 
		);
		$list = M ( $table )->where ( $arr )->field ( 'title' )->select ();
		$titleArr = getSubByKey ( $list, 'title' );
	} else {
		$str = str_replace ( '，', ',', $extra );
		$str = str_replace ( '【', '[', $str );
		$str = str_replace ( '】', ']', $str );
		$str = str_replace ( '：', ':', $str );
		
		$arr = StringToArray ( $str );
		$str = '';
		foreach ( $arr as $v ) {
			if ($v == '[' || $v == ']' || $v == ',') {
				if ($str) {
					$block = explode ( ':', trim ( $str ) );
					if (in_array ( $block [0], $idArr )) {
						$titleArr [] = isset ( $block [1] ) ? $block [1] : $block [0];
					}
				}
				$str = '';
			} else {
				$str .= $v;
			}
		}
	}
	return implode ( ' > ', $titleArr );
}
// 把字符串转成数组，支持汉字，只能是utf-8格式的
function StringToArray($str) {
	$result = array ();
	$len = strlen ( $str );
	$i = 0;
	while ( $i < $len ) {
		$chr = ord ( $str [$i] );
		if ($chr == 9 || $chr == 10 || (32 <= $chr && $chr <= 126)) {
			$result [] = substr ( $str, $i, 1 );
			$i += 1;
		} elseif (192 <= $chr && $chr <= 223) {
			$result [] = substr ( $str, $i, 2 );
			$i += 2;
		} elseif (224 <= $chr && $chr <= 239) {
			$result [] = substr ( $str, $i, 3 );
			$i += 3;
		} elseif (240 <= $chr && $chr <= 247) {
			$result [] = substr ( $str, $i, 4 );
			$i += 4;
		} elseif (248 <= $chr && $chr <= 251) {
			$result [] = substr ( $str, $i, 5 );
			$i += 5;
		} elseif (252 <= $chr && $chr <= 253) {
			$result [] = substr ( $str, $i, 6 );
			$i += 6;
		}
	}
	return $result;
}

function outExcel($dataArr, $fileName = '', $sheet = false) {
	require_once VENDOR_PATH . 'download-xlsx.php';
	export_csv ( $dataArr, $fileName, $sheet );
	unset ( $sheet );
	unset ( $dataArr );
}

// 阿拉伯数字转中文表述，如101转成一百零一
function num2cn($number) {
	$number = intval ( $number );
	$capnum = array (
			"零",
			"一",
			"二",
			"三",
			"四",
			"五",
			"六",
			"七",
			"八",
			"九" 
	);
	$capdigit = array (
			"",
			"十",
			"百",
			"千",
			"万" 
	);
	
	$data_arr = str_split ( $number );
	$count = count ( $data_arr );
	for($i = 0; $i < $count; $i ++) {
		$d = $capnum [$data_arr [$i]];
		$arr [] = $d != '零' ? $d . $capdigit [$count - $i - 1] : $d;
	}
	$cncap = implode ( "", $arr );
	
	$cncap = preg_replace ( "/(零)+/", "0", $cncap ); // 合并连续“零”
	$cncap = trim ( $cncap, '0' );
	$cncap = str_replace ( "0", "零", $cncap ); // 合并连续“零”
	$cncap == '一十' && $cncap = '十';
	$cncap == '' && $cncap = '零';
	// echo ( $data.' : '.$cncap.' <br/>' );
	return $cncap;
}
function week_name($number = null) {
	if ($number === null)
		$number = date ( 'w' );
	
	$arr = array (
			"日",
			"一",
			"二",
			"三",
			"四",
			"五",
			"六" 
	);
	
	return '星期' . $arr [$number];
}
// 日期转换成星期几
function daytoweek($day = null) {
	$day === null && $day = date ( 'Y-m-d' );
	if (empty ( $day ))
		return '';
	
	$number = date ( 'w', strtotime ( $day ) );
	
	return week_name ( $number );
}
/**
 * select返回的数组进行整数映射转换
 *
 * @param array $map
 *        	映射关系二维数组 array(
 *        	'字段名1'=>array(映射关系数组),
 *        	'字段名2'=>array(映射关系数组),
 *        	......
 *        	)
 * @author 朱亚杰 <zhuyajie@topthink.net>
 * @return array array(
 *         array('id'=>1,'title'=>'标题','status'=>'1','status_text'=>'正常')
 *         ....
 *         )
 *        
 */
function int_to_string(&$data, $map = array('status'=>array(1=>'正常',-1=>'删除',0=>'禁用',2=>'未审核',3=>'草稿'))) {
	if ($data === false || $data === null) {
		return $data;
	}
	$data = ( array ) $data;
	foreach ( $data as $key => $row ) {
		foreach ( $map as $col => $pair ) {
			if (isset ( $row [$col] ) && isset ( $pair [$row [$col]] )) {
				$data [$key] [$col . '_text'] = $pair [$row [$col]];
			}
		}
	}
	return $data;
}
function importFormExcel($attach_id, $column, $dateColumn = array()) {
	$attach_id = intval ( $attach_id );
	$res = array (
			'status' => 0,
			'data' => '' 
	);
	if (empty ( $attach_id ) || ! is_numeric ( $attach_id )) {
		$res ['data'] = '112001:上传文件ID无效！';
		return $res;
	}
	$file = M ( 'file' )->where ( 'id=' . $attach_id )->find ();
	$root = C ( 'DOWNLOAD_UPLOAD.rootPath' );
	$filename = SITE_PATH . '/Uploads/Download/' . $file ['savepath'] . $file ['savename'];
	// dump($filename);
	if (! file_exists ( $filename )) {
		$res ['data'] = '112002:上传的文件失败';
		return $res;
	}
	$extend = $file ['ext'];
	if (! ($extend == 'xls' || $extend == 'xlsx' || $extend == 'csv')) {
		$res ['data'] = '112003:文件格式不对，请上传xls,xlsx格式的文件';
		return $res;
	}
	
	vendor ( 'PHPExcel' );
	vendor ( 'PHPExcel.PHPExcel_IOFactory' );
	vendor ( 'PHPExcel.Reader.Excel5' );
	
	switch (strtolower ( $extend )) {
		case 'csv' :
			$format = 'CSV';
			$objReader = \PHPExcel_IOFactory::createReader ( $format )->setDelimiter ( ',' )->setInputEncoding ( 'GBK' )->setEnclosure ( '"' )->setLineEnding ( "\r\n" )->setSheetIndex ( 0 );
			break;
		case 'xls' :
			$format = 'Excel5';
			$objReader = \PHPExcel_IOFactory::createReader ( $format );
			break;
		default :
			$format = 'Excel2007';
			$objReader = \PHPExcel_IOFactory::createReader ( $format );
	}
	
	$objPHPExcel = $objReader->load ( $filename );
	$objPHPExcel->setActiveSheetIndex ( 0 );
	$sheet = $objPHPExcel->getSheet ( 0 );
	$highestRow = $sheet->getHighestRow (); // 取得总行数
	for($j = 2; $j <= $highestRow; $j ++) {
		$addData = array ();
		foreach ( $column as $k => $v ) {
			if ($dateColumn) {
				foreach ( $dateColumn as $d ) {
					if ($k == $d) {
						$addData [$v] = gmdate ( "Y-m-d H:i:s", PHPExcel_Shared_Date::ExcelToPHP ( $objPHPExcel->getActiveSheet ()->getCell ( "$k$j" )->getValue () ) );
					} else {
						$addData [$v] = trim ( ( string ) $objPHPExcel->getActiveSheet ()->getCell ( $k . $j )->getValue () );
					}
				}
			} else {
				$addData [$v] = trim ( ( string ) $objPHPExcel->getActiveSheet ()->getCell ( $k . $j )->getValue () );
			}
		}
		
		$isempty = true;
		foreach ( $column as $v ) {
			$isempty && $isempty = empty ( $addData [$v] );
		}
		
		if (! $isempty)
			$result [$j] = $addData;
	}
	$res ['status'] = 1;
	$res ['data'] = $result;
	return $res;
}

/**
 * 根据两点间的经纬度计算距离
 *
 * @param float $lat
 *        	纬度值
 * @param float $lng
 *        	经度值
 */
function getDistance($lat1, $lng1, $lat2, $lng2) {
	$earthRadius = 6367000; // approximate radius of earth in meters
	                        
	// Convert these degrees to radians to work with the formula
	$lat1 = ($lat1 * pi ()) / 180;
	$lng1 = ($lng1 * pi ()) / 180;
	
	$lat2 = ($lat2 * pi ()) / 180;
	$lng2 = ($lng2 * pi ()) / 180;
	
	// Using the Haversine formula http://en.wikipedia.org/wiki/Haversine_formula calculate the distance
	
	$calcLongitude = $lng2 - $lng1;
	$calcLatitude = $lat2 - $lat1;
	$stepOne = pow ( sin ( $calcLatitude / 2 ), 2 ) + cos ( $lat1 ) * cos ( $lat2 ) * pow ( sin ( $calcLongitude / 2 ), 2 );
	$stepTwo = 2 * asin ( min ( 1, sqrt ( $stepOne ) ) );
	$calculatedDistance = $earthRadius * $stepTwo;
	
	return round ( $calculatedDistance );
}
function getMyDistance($shopGPS) {
	$arr = explode ( ',', $shopGPS );
	if (empty ( $arr [0] ) || empty ( $arr [1] ) || ! empty ( $_SESSION ['my_location_' . $GLOBALS ['mid']] ))
		return ''; // 无法计算
	
	$my = explode ( ',', $_SESSION ['my_location_' . $GLOBALS ['mid']] );
	return getDistance ( $arr [0], $arr [1], $my [0], $my [1] );
}
function GPS2Address($location) {
	$url = '//api.map.baidu.com/geocoder/v2/?ak=' . BAIDU_GPS_AK . '&coordtype=wgs84ll&location=' . $location . '&output=json&pois=0';
	$res = wp_file_get_contents ( $url );
	// dump ( $url );
	$res = json_decode ( $res, true );
	// dump ( $res );
	return $res ['result'] ['formatted_address'];
}
function xml_to_array($xml) {
	$reg = "/<(\\w+)[^>]*?>([\\x00-\\xFF]*?)<\\/\\1>/";
	if (preg_match_all ( $reg, $xml, $matches )) {
		$count = count ( $matches [0] );
		$arr = array ();
		for($i = 0; $i < $count; $i ++) {
			$key = $matches [1] [$i];
			$val = xml_to_array ( $matches [2] [$i] ); // 递归
			if (array_key_exists ( $key, $arr )) {
				if (is_array ( $arr [$key] )) {
					if (! array_key_exists ( 0, $arr [$key] )) {
						$arr [$key] = array (
								$arr [$key] 
						);
					}
				} else {
					$arr [$key] = array (
							$arr [$key] 
					);
				}
				$arr [$key] [] = $val;
			} else {
				$arr [$key] = $val;
			}
		}
		return $arr;
	} else {
		return $xml;
	}
}
// Xml 转 数组, 不包括根键
function xmltoarray($xml) {
	$arr = xml_to_array ( $xml );
	$key = array_keys ( $arr );
	return $arr [$key [0]];
}

/**
 * ************************************************************
 *
 * 使用特定function对数组中所有元素做处理
 *
 * @param
 *        	string &$array 要处理的字符串
 * @param string $function
 *        	要执行的函数
 * @return boolean $apply_to_keys_also 是否也应用到key上
 * @access public
 *        
 *         ***********************************************************
 */
function arrayRecursive(&$array, $function, $apply_to_keys_also = false) {
	static $recursive_counter = 0;
	if (++ $recursive_counter > 1000) {
		die ( 'possible deep recursion attack' );
	}
	foreach ( $array as $key => $value ) {
		if (is_array ( $value )) {
			arrayRecursive ( $array [$key], $function, $apply_to_keys_also );
		} else {
			$array [$key] = $function ( $value );
		}
		
		if ($apply_to_keys_also && is_string ( $key )) {
			$new_key = $function ( $key );
			if ($new_key != $key) {
				$array [$new_key] = $array [$key];
				unset ( $array [$key] );
			}
		}
	}
	$recursive_counter --;
}

/**
 * ************************************************************
 *
 * 将数组转换为JSON字符串（兼容中文）
 *
 * @param array $array
 *        	要转换的数组
 * @return string 转换得到的json字符串
 * @access public
 *        
 *         ***********************************************************
 */
function JSON($array) {
	arrayRecursive ( $array, 'urlencode', true );
	$json = json_encode ( $array );
	return urldecode ( $json );
}

/**
 * 检查是否是以手机浏览器进入(IN_MOBILE)
 */
function isMobile() {
	$mobile = array ();
	static $mobilebrowser_list = 'Mobile|iPhone|Android|WAP|NetFront|JAVA|OperasMini|UCWEB|WindowssCE|Symbian|Series|webOS|SonyEricsson|Sony|BlackBerry|Cellphone|dopod|Nokia|samsung|PalmSource|Xphone|Xda|Smartphone|PIEPlus|MEIZU|MIDP|CLDC';
	// note 获取手机浏览器
	if (preg_match ( "/$mobilebrowser_list/i", $_SERVER ['HTTP_USER_AGENT'], $mobile )) {
		return true;
	} else {
		if (preg_match ( '/(mozilla|chrome|safari|opera|m3gate|winwap|openwave)/i', $_SERVER ['HTTP_USER_AGENT'] )) {
			return false;
		} else {
			if ($_GET ['mobile'] === 'yes') {
				return true;
			} else {
				return false;
			}
		}
	}
}
function isiPhone() {
	return strpos ( $_SERVER ['HTTP_USER_AGENT'], 'iPhone' ) !== false;
}
function isiPad() {
	return strpos ( $_SERVER ['HTTP_USER_AGENT'], 'iPad' ) !== false;
}
function isiOS() {
	return isiPhone () || isiPad ();
}
function isAndroid() {
	return strpos ( $_SERVER ['HTTP_USER_AGENT'], 'Android' ) !== false;
}


/**
 * 用SHA1算法生成安全签名
 */
function getSHA1($array) {
	// 排序
	sort ( $array, SORT_STRING );
	$str = implode ( $array );
	return sha1 ( $str );
}

/**
 * 输出xml字符
 */
function ToXml($arr = []) {
	if (! is_array ( $arr ) || count ( $arr ) <= 0) {
		exit ( "数组数据异常！" );
	}
	
	$xml = "<xml>";
	foreach ( $arr as $key => $val ) {
		if (is_numeric ( $val )) {
			$xml .= "<" . $key . ">" . $val . "</" . $key . ">";
		} else {
			$xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
		}
	}
	$xml .= "</xml>";
	return $xml;
}


/**
 * 获取随机字符串
 *
 * @param number $length        	
 * @return string
 */
function createNonceStr($length = 16) {
	$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
	$str = "";
	for($i = 0; $i < $length; $i ++) {
		$str .= substr ( $chars, mt_rand ( 0, strlen ( $chars ) - 1 ), 1 );
	}
	return $str;
}




// 二维数组根据键排序
function array_sort($arr, $keys, $type = 'desc') {
	$keysvalue = $new_array = array ();
	foreach ( $arr as $k => $v ) {
		$keysvalue [$k] = $v [$keys];
	}
	if ($type == 'asc') {
		asort ( $keysvalue );
	} else {
		arsort ( $keysvalue );
	}
	reset ( $keysvalue );
	foreach ( $keysvalue as $k => $v ) {
		$new_array [$k] = $arr [$k];
	}
	return $new_array;
}

// 转换得到含emoji表情的代码 注意引入css文件
function parseHtmlemoji($text) {
	vendor ( "emoji" );
	$tmpStr = json_encode ( $text );
	$tmpStr = preg_replace ( "#(\\\ue[0-9a-f]{3})#ie", "addslashes('\\1')", $tmpStr );
	$text = json_decode ( $tmpStr );
	preg_match_all ( "#u([0-9a-f]{4})+#iUs", $text, $rs );
	if (empty ( $rs [1] )) {
		return $text;
	}
	foreach ( $rs [1] as $v ) {
		$test_iphone = '0x' . trim ( strtoupper ( $v ) );
		$test_iphone = $test_iphone + 0;
		$utbytes = utf8_bytes ( $test_iphone );
		$emji = emoji_softbank_to_unified ( $utbytes );
		$t = emoji_unified_to_html ( $emji );
		$text = str_replace ( "\u$v", $t, $text );
	}
	return $text;
}
function utf8_bytes($cp) {
	if ($cp > 0x10000) {
		// 4 bytes
		return chr ( 0xF0 | (($cp & 0x1C0000) >> 18) ) . chr ( 0x80 | (($cp & 0x3F000) >> 12) ) . chr ( 0x80 | (($cp & 0xFC0) >> 6) ) . chr ( 0x80 | ($cp & 0x3F) );
	} else if ($cp > 0x800) {
		// 3 bytes
		return chr ( 0xE0 | (($cp & 0xF000) >> 12) ) . chr ( 0x80 | (($cp & 0xFC0) >> 6) ) . chr ( 0x80 | ($cp & 0x3F) );
	} else if ($cp > 0x80) {
		// 2 bytes
		return chr ( 0xC0 | (($cp & 0x7C0) >> 6) ) . chr ( 0x80 | ($cp & 0x3F) );
	} else {
		// 1 byte
		return chr ( $cp );
	}
}
function curl_post($url, $data = null) {
	$ch = curl_init ();
	curl_setopt ( $ch, CURLOPT_URL, $url ); // 设置访问的url地址
	curl_setopt ( $ch, CURLOPT_CUSTOMREQUEST, "POST" );
	curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
	curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, FALSE );
	curl_setopt ( $ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/49.0.2623.87 Safari/537.36' );
	/*
	 * if($data){
	 * curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
	 * curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	 * curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
	 *
	 * }
	 */
	$data && curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data );
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
	$tmpInfo = curl_exec ( $ch );
	if (curl_errno ( $ch )) {
		return curl_error ( $ch );
	}
	curl_close ( $ch );
	return $tmpInfo;
}
function matchImages($content = '') {
	$src = array ();
	preg_match_all ( '/<img.*src=\s*[\'"](.*)[\s>\'"]/isU', $content, $src );
	if (count ( $src [1] ) > 0) {
		foreach ( $src [1] as $v ) {
			$images [] = trim ( $v, "\"'" ); // 删除首尾的引号 ' "
		}
		return $images;
	} else {
		return false;
	}
}
function getEditorImages($content) {
	preg_match_all ( '/<img.*src=\s*[\'"](.*)[\s>\'"]/isU', $content, $matchs );
	$image = '';
	foreach ( $matchs [1] as $match ) {
		$isFace = strpos ( $match, '/emotion/' ) === false ? false : true;
		if ($isFace) {
			continue;
		}
		if (preg_match ( '/http:\/\/[\w.]+[\w\/]*[\w.]*\??[\w=&\+\%]*/is', $match ) && ! $isFace) {
			$image = $match;
		} else if (! $isFace) {
			$url = implode ( '/', array_slice ( explode ( '/', $match ), - 4 ) );
			$image = getImageUrl ( $url, 200, 200, true );
		}
		break;
	}
	
	return $image;
}
function matchReplaceImages($content = '') {
	$image = preg_replace_callback ( '/<img.*src=\s*[\'"](.*)[\s>\'"]/isU', "matchReplaceImagesOnce", $content );
	return $image;
}
function matchReplaceImagesOnce($matches) {
	$matches [1] = str_replace ( '"', '', $matches [1] );
	return sprintf ( "<a class='thickbox'  href='%s'>%s</a>", $matches [1], $matches [0] );
}

// 数字ID加密成不连续的字母，主要用于URL上，防止用户通过连续数字ID随意关联
function id_encode($id) {
	$code = 'abcdefghijklmnopqrstuvwxyz';
	$str = ( string ) $id;
	// 不足3位补全3位
	if ($id < 10) {
		$str = '00' . $id;
	} elseif ($id < 10) {
		$str = '0' . $id;
	}
	
	$rand = rand ( 0, 25 );
	$res = $code [$rand];
	// dump ( $res );
	$len = strlen ( $str );
	// dump ( $len );
	for($i = 0; $i < $len; $i ++) {
		$j = $str [$i] + $i + $rand;
		$j = $j % 26;
		$res .= $code [$j];
	}
	return $res;
}

// 表单里初始化数据
/*function initValue($field, $data = [], $defalut = '') {
	return isset ( $data [$field] ) ? $data [$field] : $defalut;
}*/
/**
 * ************************************************************
 *
 * 将数组转换为JSON字符串（兼容中文）
 *
 * @param array $array
 *        	要转换的数组
 * @return string 转换得到的json字符串
 * @access public
 *        
 *         ***********************************************************
 */
function json_url($array) {
	header ( 'Content-Type:application/json; charset=utf-8' );
	$json = json_encode ( $array, JSON_UNESCAPED_UNICODE );
	return $json;
}
