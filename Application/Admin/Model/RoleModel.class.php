<?php
namespace Admin\Model;

use Think\Model\RelationModel;

class RoleModel extends RelationModel
{
    protected $tableName = 'role';

    /* 关联扩展 */
    protected $_link = array(
        'AuthRule' => array(
            'mapping_type'         => self::MANY_TO_MANY,
            'class_name'           => 'AuthRule',
            'mapping_name'         => 'rules',
            'foreign_key'          => 'role_id',
            'relation_foreign_key' => 'auth_rule_id',
            'relation_table'       => 'xp_auth_role_auth_rule',
        ),
    );

    /* 模型验证 */
    protected $_validate = array(
        array('name','require','用户组名称不为空！'),
        array('name','','用户组名称已经存在！',0,'unique',1), //在新增的时候验证name字段是否唯一
    );

    /* 用户组列表 */
    public function getRoles($where = ''){
        return $this->where($where)->select();
    }

}
