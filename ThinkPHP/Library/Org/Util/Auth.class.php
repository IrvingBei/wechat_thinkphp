<?php
namespace Org\Util;

// 配置文件增加设置
// USER_AUTH_ON 是否需要认证
// USER_AUTH_TYPE 认证类型
// USER_AUTH_KEY 认证识别号
// NOT_AUTH_MODULE 无需认证模块
// USER_AUTH_GATEWAY 认证网关
//
// AUTH_ROLE_MODEL 角色表名称
// AUTH_ADMIN_MODEL 用户表名称
// AUTH_AUTH_RULE_MODEL 权限表名称

class Auth
{
    /**
     * 是否登录
     * @author luffy<luffyzhao@vip.126.com>
     * @dateTime 2016-02-29T11:17:59+0800
     * @return   [type]                   [description]
     */
    public static function checkLogin()
    {
        //检查当前操作是否需要认证
        if (self::checkAccess()) {
            //检查认证识别号
            $USER_AUTH_KEY = C('USER_AUTH_KEY');
            $isLogin       = empty($USER_AUTH_KEY) ? null : session($USER_AUTH_KEY);
            if (empty($isLogin)) {
                return false;
            }
        }
        return true;
    }

    /**
     * 是否登录 用于登录页面
     * @author luffy<luffyzhao@vip.126.com>
     * @dateTime 2016-02-18T13:51:59+0800
     * @return   boolean                  [description]
     */
    public static function isLogin()
    {
        if (!session(C('USER_AUTH_KEY'))) {
            return false;
        }
        return true;
    }

    /**
     * 是否需要验证
     * @author luffy<luffyzhao@vip.126.com>
     * @dateTime 2016-02-29T11:17:34+0800
     * @param    string                   $value [description]
     * @return   [type]                          [description]
     */
    public static function checkAccess()
    {
        //如果项目要求认证，并且当前模块需要认证，则进行权限认证
        if (C('USER_AUTH_ON')) {
            if (C('NOT_AUTH_MODULE') == '') {
                return true;
            }

            $notAuthModules = explode(',', strtolower(C('NOT_AUTH_MODULE')));
            if (!in_array(strtolower(CONTROLLER_NAME), $notAuthModules)) {
                return true;
            }
        }
        return false;
    }

    /**
     * 权限认证的过滤器方法
     * @author luffy<luffyzhao@vip.126.com>
     * @dateTime 2016-02-29T11:17:51+0800
     * @param    [type]                   $appName [description]
     */
    public static function AccessDecision($appName = MODULE_NAME)
    {
        //检查是否需要认证
        if (self::checkAccess()) {
            //存在认证识别号，则进行进一步的访问决策
            $accessGuid = md5(strtolower($appName . '/' . CONTROLLER_NAME . '/' . ACTION_NAME));

            if (C('USER_AUTH_TYPE') == 2) {
                //加强验证和即时验证模式 更加安全 后台权限修改可以即时生效
                $accessList = self::getAccessList(session(C('USER_AUTH_KEY')));
            } else {
                // 如果是管理员或者当前操作已经认证过，无需再次认证
                if (session($accessGuid) === true) {
                    return true;
                } else if (session($accessGuid) === false) {
                    return false;
                }
                //登录验证模式，比较登录后保存的权限访问列表
                $accessList = session('_ACCESS_LIST');
            }
            //判断是否为组件化模式，如果是，验证其全模块名
            if (!isset($accessList[$accessGuid])) {
                session($accessGuid, false);
                return false;
            } else {
                session($accessGuid, true);
            }
        }
        return true;
    }

    /**
     * =========================================================================
     * 重写获取权限列表
     * =========================================================================
     * @param $authId SESSION admin.id
     * @return array
     * @author leeong<9387524@gmail.com>
     */
    public static function getAccessList($authId)
    {
        $accessList = array();

        $adminModel    = D(C('AUTH_ADMIN_MODEL'));
        $roleModel     = D(C('AUTH_ROLE_MODEL'));
        $authRuleModel = D(C('AUTH_AUTH_RULE_MODEL'));

        $adminRow = $adminModel->field('role_id')->find($authId);

        if (empty($adminRow)) {
            return $accessList;
        }

        $roleRow = $roleModel->relation(true)->find($adminRow['role_id']);

        if (empty($roleRow) || !isset($roleRow['rules']) || empty($roleRow['rules'])) {
            return $accessList;
        }

        foreach ($roleRow['rules'] as $value) {
            $accessList[md5(strtolower(MODULE_NAME . '/' . $value['name']))] = $value;
        }

        return $accessList;
    }

    /**
     * 保存Access
     * @author luffy<luffyzhao@vip.126.com>
     * @dateTime 2016-02-29T11:57:37+0800
     * @param    [type]                   $authId [description]
     * @return   [type]                           [description]
     */
    public static function saveAccessList($authId)
    {
        $accessList = self::getAccessList();

        session('_ACCESS_LIST', $accessList);
    }
}
