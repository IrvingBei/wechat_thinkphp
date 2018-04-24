<?php
return array(

    //数据库配置
    'DB_TYPE' => 'mysql', //数据库类型
    'DB_HOST' => '127.0.0.1', //数据库主机
    'DB_NAME' => 'wechat_tp', //数据库名称
    'DB_USER' => 'root', //数据库用户名
    'DB_PWD' => '', //数据库密码
    'DB_PORT' => '3306', //数据库端口
    'DB_PREFIX' => 'xp_weixin_', //数据库前缀
    'DB_CHARSET'=> 'utf8', // 字符集
    'DB_DEBUG'  => '', // 数据库调试模式 开启后可以记录SQL日志


    'SHOW_PAGE_TRACE'      => false,
    'URL_CASE_INSENSITIVE' => false,

    'APP_KEY'              => '',

    'TMPL_L_DELIM'         => '{{',
    'TMPL_R_DELIM'         => '}}',
    'URL_MODEL'            => '2',

    //Redis存储
    'DATA_CACHE_PREFIX' => 'PHP:',//缓存前缀
    'DATA_CACHE_TYPE'=>'Redis',//默认动态缓存为Redis
    'REDIS_RW_SEPARATE' => false, //Redis读写分离 true 开启
    'REDIS_HOST'=>'127.0.0.1', //redis服务器ip，多台用逗号隔开；读写分离开启时，第一台负责写，其它[随机]负责读；
    'REDIS_PORT'=>'6379',//端口号
    'REDIS_TIMEOUT'=>'300',//超时时间
    'REDIS_PERSISTENT'=>false,//是否长连接 false=短连接
    'REDIS_AUTH_PASSWORD'=>'',//AUTH认证密码
    'DATA_CACHE_TIME'       => 120,      // 数据缓存有效期 0表示永久缓存
    'DATA_CACHE_TIMEOUT'       => 300,

    //由redis来管理session
    'SESSION_AUTO_START    '=> true,
    'SESSION_TYPE'          =>  'Redis',    //session类型
    'SESSION_PERSISTENT'    =>  1,        //是否长连接(对于php来说0和1都一样)
    'SESSION_CACHE_TIME'    =>  1,        //连接超时时间(秒)
    'SESSION_EXPIRE'        =>  3600,        //session有效期(单位:秒) 0表示永久缓存
    'SESSION_PREFIX'        =>  'PHP:session:',        //session前缀
    'SESSION_REDIS_HOST'    =>  '127.0.0.1', //分布式Redis,默认第一个为主服务器
    'SESSION_REDIS_PORT'    =>  '6379',           //端口,如果相同只填一个,用英文逗号分隔
    'SESSION_REDIS_AUTH'    =>  '',    //Redis auth认证(密钥中不能有逗号),如果相同只填一个,用英文逗号分隔

);