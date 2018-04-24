<?php
return array(
    //登录&权限
    'action_begin' => array(
        'Admin\\Behaviors\\LoginBehavior',
        'Behavior\CheckLangBehavior',
    ),
    'view_filter' => array('Behavior\TokenBuildBehavior'),
);
