<?php

// +----------------------------------------------------------------------
// | Copyright (c) 2017 http://www.drpeng.com.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: Haijiang Li <lihaijiang.1989@gmail.com>
// +----------------------------------------------------------------------
// | This is not a free software, unauthorized no use and dissemination.
// +----------------------------------------------------------------------
/**
 * @file            IndexController.class.php
 * @version         1.0
 * @date            Fri, 23 Feb 2018 11:12:07 GMT
 * @description     This is the controller class for data "index"
 */

namespace Admin\Controller;

use Common\Controller\AdminController;

class IndexController extends AdminController
{

    public function _initialize()
    {
        parent::_initialize();

        $currYear = date('Y');
        $this->site_id = I("get.site_id", 0);
        $this->year = I("get.year", $currYear);
        $this->month = I("get.month", date('m'));
        $this->day = I("get.day", date('d'));
        $this->type = I("get.type", 0);
        $this->assign("site_id", $this->site_id);
        $this->assign("year", $this->year);
        $this->assign("month", $this->month);
        $this->assign("day", $this->day);
        $this->assign("type", $this->type);

        $yearList = array();
        for($i = $currYear; $i <= $currYear; $i++){
            $yearList[] = $i;
        }

        $typeList = M()->table('xp_weixin_image_log')
            ->field('DISTINCT type_code,type')
            ->order('type_code')
            ->select();
        $this->assign("typeList", $typeList);
        $this->assign("yearList", $yearList);

    }


    /**
     * 后台框架
     */
    public function index()
    {
        $this->display();
    }

    public function main(){

        //$wheres = " and u.order_state = 1 and u.order_status in (5,6) and u.work_status in (5,9) ";
        $wheres = " and 1=1 ";

        if($this->type > 0){
            $wheres .= " and u.type_code = {$this->type}";
        }

        $Model = M()->table('xp_weixin_image_log u');

        if ($this->year > 0  && $this->day > 0) { //按小时统计

            if($this->month){
                $where = sprintf("year(FROM_UNIXTIME(u.api_request_time)) ='%s' AND month(FROM_UNIXTIME(u.api_request_time))='%s' AND day(FROM_UNIXTIME(u.api_request_time))='%s' {$wheres} ", $this->year, $this->month, $this->day);
            }else{
                $where = sprintf("year(FROM_UNIXTIME(u.api_request_time)) ='%s' AND day(FROM_UNIXTIME(u.api_request_time))='%s' {$wheres} ", $this->year, $this->day);
            }


            $info = $Model
                ->field("count(*) as c, count(*) as amount, hour(FROM_UNIXTIME(u.api_request_time)) as hour")
                ->where($where)
                ->order("hour ASC")
                ->group('hour')
                ->select();

            $list = $this->getAnyHour($info);

        }elseif($this->year > 0 && $this->month > 0) { //按日统计
            $info = $Model
                ->field("count(*) as c, day(FROM_UNIXTIME(u.api_request_time)) as day")
                ->where("year(FROM_UNIXTIME(u.api_request_time)) ='%s' AND month(FROM_UNIXTIME(u.api_request_time))='%s' {$wheres} ", $this->year, $this->month)
                ->order("day ASC")
                ->group('day')
                ->select();
            $list = $this->getAnyDay($info);
            //exit(json_encode($list));

        }elseif ($this->year > 0 && $this->month == 0) { //按月份统计
            $info = $Model
                ->field("count(*) as c, count(*) as amount, month(FROM_UNIXTIME(u.api_request_time)) as month")
                ->where("year(FROM_UNIXTIME(u.api_request_time)) = '%s' {$wheres} ", $this->year)
                ->order("month ASC")
                ->group('month')
                ->select();

            $list = $this->getAnyMonth($info);

        }elseif ($this->year == 0 && $this->month == 0) { //按年份统计
            $info = $Model
                ->field("count(*) as c, count(*) as amount, year(FROM_UNIXTIME(u.api_request_time)) as year")
                ->where("1=1 {$wheres}")
                ->order("year ASC")
                ->group('year')
                ->select();

            $list = $this->getAnyYear($info);
        }

        $axis = array();
        $total = array();
        $amount = array();
        foreach ($list as $key => $row) {
            $axis[] = $row['axis'];
            $total[] = $row['count'];
            $amount[] = $row['amount'];
        }

        $this->assign("axis", json_encode($axis));
        $this->assign("total", json_encode($total, JSON_NUMERIC_CHECK));
        $this->assign("amount", json_encode($amount, JSON_NUMERIC_CHECK));

        $this->display();



    }

    /**
     * 修改登录密码
     */
    public function password()
    {
        if(IS_POST){
            $params    = I();
            $adminModel = D('Admin');

            $password = isset($params['password']) ? trim($params['password']) : '';
            $password_confirm = isset($params['password_confirm']) ? trim($params['password_confirm']) : '';

            $data['id'] = session('admin')['id'];
            if(!empty($password)){
                if($password != $password_confirm){
                    $this->error('密码不一致，请重新操作！','/Index/password');
                }
                $data['password'] = sha1(md5($password));
            }else{
                $this->error('密码不能为空','/Index/password');
            }

            if($adminModel->save($data) === false){
                throw new Exception('修改密码失败-'.$adminModel->getError(), 6001);
            }

            $this->success("保存成功");
        }else{
            $this->display();
        }
    }





    /**
     * 小时
     * @param $result
     * @param int $flag
     * @return array
     */
    public function getAnyHour($result,$flag=0){

        $list = array();
        for($i=0; $i<=23; $i++){
            $list[$i]["count"] = 0;
            $list[$i]["amount"] = 0;
            $list[$i]["axis"] = $i."h";
        }
        $allcount = 0;
        if(!empty($result)) {
            foreach ($result as $key => $value) {
                $list[$value['hour']]['count'] = $value['c'];
                $list[$value['hour']]['amount'] = $value['amount'];
                $allcount += $value['c'];
            }
        }
        if($flag != 1){
            $this->assign("list", $list);
        }
        return $list;
    }

    /**
     * 天
     * @param $result
     * @param int $flag
     * @return array
     */
    public function getAnyDay($result,$flag=0){

        $BeginDate=$this->year."/".$this->month."/01";
        $endday = date('d', strtotime("$BeginDate +1 month -1 day"));

        $list = array();
        for($i=1; $i<=$endday; $i++){
            $list[$i]["count"] = 0;
            $list[$i]["amount"] = 0;
            $list[$i]["axis"] = $i."日";
        }
        $allcount = 0;
        if(!empty($result)) {
            foreach ($result as $key => $value) {
                $list[$value['day']]['count'] = $value['c'];
                $list[$value['day']]['amount'] = $value['amount'];
                $allcount += $value['c'];
            }
        }

        if($flag != 1){
            $this->assign("list", $list);
        }
        return $list;
    }

    /**
     * 月
     * @param $result
     * @param int $flag
     * @return array
     */
    public function getAnyMonth($result,$flag=0){
        $list = array();
        for($i=1; $i<=12; $i++){
            $list[$i]["count"] = 0;
            $list[$i]["amount"] = 0;
            $list[$i]["axis"] = $this->year.'年'.$i."月";
        }
        $allcount = 0;
        if(!empty($result)) {
            foreach ($result as $key => $value) {
                $list[$value['month']]['count'] = $value['c'];
                $list[$value['month']]['amount'] = $value['amount'];
                $allcount += $value['c'];
            }
        }
        if($flag != 1){
            $this->assign("list", $list);
        }
        return $list;
    }

    /**
     * 年
     * @param $result
     * @param int $flag
     * @return array
     */
    public function getAnyYear($result,$flag=0){

        $list = array();
        $curryear = date('Y');
        for($i=$curryear-5; $i<=$curryear+3; $i++){
            $list[$i]["count"] = 0;
            $list[$i]["amount"] = 0;
            $list[$i]["axis"] = $i.'年';
        }
        $allcount = 0;
        if(!empty($result)) {
            foreach ($result as $key => $value) {
                $list[$value['year']]['count'] = $value['c'];
                $list[$value['year']]['amount'] = $value['amount'];
                $allcount += $value['c'];
            }
        }

        if($flag != 1){
            $this->assign("list", $list);
        }
        return $list;
    }

}
