<?php
/**
 *权限Model类
 */

namespace Admin\Model;

use Think\Model;

class AuthRuleModel extends Model
{

    /**
     * [getAuthRuleForPid description]
     */
    public function getAuthRuleForPid($pid = 0)
    {
        $authRuleRows = $this->where(array(
            'pid' => $pid,
        ))->order('sort Asc')->select();
        $authRuleAll = array();

        if ($authRuleRows) {
            foreach ($authRuleRows as $key => $value) {
                $authRuleAll[$value['id']]          = $value;
                $authRuleAll[$value['id']]['child'] = $this->getAuthRuleForPid($value['id']);
            }
        }

        return $authRuleAll;
    }

    /**
     * [getAuthRuleForRoleAuthRule description]
     */
    public function getAuthRuleForRoleAuthRule($roleAuthRuleRows, $pid = 0)
    {
        $authRuleRows = $this->where(array(
            'pid'    => $pid,
            'islink' => 1,
            'id'     => array('IN', $roleAuthRuleRows),
        ))->order('sort Asc')->select();
        $authRuleAll = array();

        if ($authRuleRows) {
            foreach ($authRuleRows as $key => $value) {
                $authRuleAll[$key]          = $value;
                $authRuleAll[$key]['name']  = '/' . $value['name'];
                $authRuleAll[$key]['child'] = $this->getAuthRuleForRoleAuthRule($roleAuthRuleRows, $value['id']);
            }
        }

        return $authRuleAll;
    }

    /**
     * [getBreadcrumb description]
     */
    public function getBreadcrumb()
    {
        $name           = CONTROLLER_NAME . '/' . ACTION_NAME;
        $postionLastRow = $this->getByName($name);

        if ($postionLastRow['pid'] != 0) {
            $postionFirstRow = $this->find($postionLastRow['pid']);
        }
        return array($postionFirstRow, $postionLastRow);
    }

}
