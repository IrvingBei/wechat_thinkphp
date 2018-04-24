<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2017 http://www.drpeng.com.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: Haijiang Li <lihaijiang.1989@gmail.com>
// +----------------------------------------------------------------------
// | This is not a free software, unauthorized no use and dissemination.
// +----------------------------------------------------------------------
/**
 * @file            RoleController.class.php
 * @version         1.0
 * @date            Fri, 23 Feb 2018 11:12:07 GMT
 * @description     This is the controller class for data "role"
 */

namespace Admin\Controller;

use Common\Controller\AdminController;
use \Org\Util\Page;
use \Think\Exception;

class RoleController extends AdminController
{
    /**
     * 角色列表页面
     */
    public function index()
    {
        $roleMode = M('Role');

        $total      = $roleMode->count();
        $pagesize   = C('PAGE_SIZE');
        $pageObject = new Page($total, $pagesize);
        $pages      = $pageObject->show();

        $page = I('get.p', 0);

        $roleRows = $roleMode->page($page)->limit($pagesize)->select();

        $this->assign('roleRows', $roleRows);
        $this->assign('pages', $pages);
        $this->display();
    }

    /**
     * 角色详情页面
     */
    public function info()
    {
        $params    = I();
        $authRuleModel = D('AuthRule');
        $roleModel     = D('Role');

        $create = true;
        $data = array();
        //新建还是编辑
        if(isset($params['id']) && !empty($params['id'])){
            $create = false;
            $data['id'] = $params['id'];

            $roleRow = $roleModel->relation(true)->find($params['id']);
            if (empty($roleRow)) {
                $this->error('信息有误，请重新操作！','/Role/index');
            }
            $this->assign('roleRow', $roleRow);

            $roleAuthRuleRows = _array_column($roleRow['rules'], 'id');
            $this->assign('roleAuthRuleRows', $roleAuthRuleRows);
        }

        if (IS_POST) {
            $data['name'] = isset($params["name"]) ? trim($params["name"]) : '';
            $data['status'] = isset($params["status"]) ? intval($params["status"]) : 0;
            $data['remark'] = isset($params["remark"]) ? trim($params["remark"]) : '';;
            $data['rules'] = $params["rules"];

            if($roleModel->create()){
                if($create){
                    if(!$roleModel->relation(true)->add($data)){
                        throw new Exception('用户组添加失败-'.$roleModel->getError(), 6001);
                    }
                }else{
                    $name_exist = $roleModel->where("name = '".$data['name']."' and id != '".$params['id']."'")->count();
                    if($name_exist > 0){
                        throw new Exception('用户组名称已存在！');
                    }
                    if($roleModel->relation(true)->save($data) === false){
                        throw new Exception('用户组更新失败-'.$roleModel->getError(), 6001);
                    }
                }
            }else{
                throw new Exception('用户组数据校验失败-'.$roleModel->getError(), 6001);
            }

            $this->success("保存成功",'/Role/index');
        }else{
            $authRuleAll = $authRuleModel->getAuthRuleForPid();
            $this->assign('authRuleAll', $authRuleAll);
            $this->display();
        }
    }
}
