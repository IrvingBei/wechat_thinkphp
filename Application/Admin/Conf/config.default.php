<?php
return array(
    'DEFAULT_CONTROLLER'   => 'Index', //默认控制器名称
    'DEFAULT_ACTION'       => 'index', //默认操作名称
    'LAYOUT_NAME'          => 'layout',
    'LAYOUT_ON'            => false,
    'PAGE_SIZE'            => 20,
    'URL_HTML_SUFFIX'      => '',
    'DEFAULT_AJAX_RETURN'  => 'JSON',
    'TMPL_ACTION_ERROR'    => 'Default:dispatch_jump',
    'TMPL_ACTION_SUCCESS'  => 'Default:dispatch_jump',

    //登录验证
    'USER_AUTH_ON'         => true,
    'USER_AUTH_TYPE'       => 2,
    'USER_AUTH_KEY'        => 'admin.id',
    'NOT_AUTH_MODULE'      => 'Default,Ajax',
    'USER_AUTH_GATEWAY'    => '/Default/login',
    'USER_AUTH_NO_ACCESS'  => '/Default/noAccess',
    'AUTH_ROLE_MODEL'      => 'Role',
    'AUTH_ADMIN_MODEL'     => 'Admin',
    'AUTH_AUTH_RULE_MODEL' => 'AuthRule',

    'SESSION_AUTO_START'   => true,
    'SESSION_TYPE'         =>  'Redis',    //session类型
    'SESSION_PERSISTENT'   =>  1,        //是否长连接(对于php来说0和1都一样)
    'SESSION_CACHE_TIME'   =>  1,        //连接超时时间(秒)
    'SESSION_EXPIRE'       =>  3600,        //session有效期(单位:秒) 0表示永久缓存
    'SESSION_PREFIX'       =>  'drp_sess_',        //session前缀
    'SESSION_REDIS_HOST'   =>  '127.0.0.1', //分布式Redis,默认第一个为主服务器
    'SESSION_REDIS_PORT'   =>  '6379',           //端口,如果相同只填一个,用英文逗号分隔
    'SESSION_REDIS_AUTH'   =>  '',    //Redis auth认证(密钥中不能有逗号),如果相同只填一个,用英文逗号分隔

    //系统支持
    'SALE_GROUP_ID'        => 11,

    // 多语言支持
    'LANG_SWITCH_ON'       => true,
    'LANG_AUTO_DETECT'     => true,
    'DEFAULT_LANG'         => 'zh-cn',
    'LANG_LIST'            => 'zh-cn',
    'VAR_LANGUAGE'         => 'l',

    //form token 令牌
    'TOKEN_ON'             => true, //是否开启令牌验证 默认关闭
    'TOKEN_NAME'           => '__hash__', //令牌验证的表单隐藏字段名称，默认为__hash__
    'TOKEN_TYPE'           => 'md5', //令牌哈希验证规则 默认为MD5
    'TOKEN_RESET'          => true, //令牌验证出错后是否重置令牌 默认为true
);
