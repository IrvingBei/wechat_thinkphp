<?php

namespace Common\Model;

use Think\Model\RelationModel;
use \Org\Util\Auth;

class AdminModel extends RelationModel
{

    /* 关联扩展 */
    protected $_link = array(
        'RoleRow' => array(
            'mapping_type'   => self::BELONGS_TO,
            'class_name'     => 'Role',
            'foreign_key'    => 'role_id',
            'mapping_name'   => 'roleRow',
            'mapping_fields' => 'name',
        ),
    );

    /* 模型验证 */
    protected $_validate = array(
        array('email','email','邮箱格式不正确！'),
        array('email','','用户邮箱已经存在！',0,'unique',1), //在新增的时候验证email字段是否唯一
        array('name','require','用户名称不为空！'),
    );

    /**
     * 登录
     */
    public function doLogin($email, $password)
    {
        $admin = $this->where(array('email' => $email))->find();

        if (empty($admin)) {
            return false;
        }

        if ($admin['password'] != $this->shaByPassword($password)) {
            return false;
        }
        $admin['role_name'] = M('role')->where("id = '".$admin['role_id']."'")->getField('name');
        $admin['overtime'] = false;

        session('admin', $admin);
        session('last_access_time', time());
        
        Auth::saveAccessList($admin['id']);
        
        return true;
    }

    /**
     * 登录锁定
     */
    public function doLoginLock($password)
    {

        if ($this->shaByPassword($password) == session('admin.password')) {
            session('admin.overtime', false);
            return true;
        } else {
            return false;
        }
    }

    /**
     * [getMenuList description]
     */
    public function getMenuList()
    {
        $roleId = session('admin.role_id');

        $roleModel     = D('Role');
        $authRuleModel = D('AuthRule');
        $roleRow       = $roleModel->relation(true)->find($roleId);

        // 权限内的菜单
        $roleAuthRuleRows = _array_column($roleRow['rules'], 'id');

        return $authRuleModel->getAuthRuleForRoleAuthRule($roleAuthRuleRows, 0);
    }
    
    /**
     * [shaByPassword description]
     */
    private function shaByPassword($password)
    {
        return sha1(md5($password));
    }

}
