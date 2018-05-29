<?php

namespace Admin\Controller;

use \Org\Util\Page;
use Admin\Service\Log;
use \Think\Exception;



class WxCouponController extends WechatBaseController
{

    public function index(){

        $Model = M('coupon_activity');
        $where = "1 = 1";
        $filter['wechat_id'] = isset($_REQUEST['wechat_id']) ? trim($_REQUEST['wechat_id']) : '';;
        $filter['keyword'] = isset($_REQUEST['keyword']) ? trim($_REQUEST['keyword']) : '';

        if(!empty($filter['wechat_id'])){
            $where .= " and wechat_id like '%".$filter['wechat_id']."%' ";
        }
        if(!empty($filter['keyword'])){
            $where .= " and activity_name like '%".$filter['keyword']."%' or keyword like '%".$filter['keyword']."%'";
        }

        $total      = $Model->where("$where")->count();
        $pagesize   = C('PAGE_SIZE');
        $pageObject = new Page($total, $pagesize);
        $pages      = $pageObject->show();

        $page = I('get.p', 0);

        $activityRows = $Model->where("$where")->order("id asc")
            ->page($page)->limit($pagesize)
            ->select();

        foreach ($activityRows as &$activity){
            $wechat_ids = json_decode($activity['wechat_id'],true);
            foreach ($wechat_ids as $wechat_id){
                $wechat_name[] = get_wechat_info($wechat_id);
            }
            //$activity['wechat_id'] = $wechat_name;
            //$activity['wechat_id'] = json_encode($wechat_name,JSON_UNESCAPED_UNICODE);
            $activity['wechat_id'] = implode(" ",$wechat_name);
            unset($wechat_name);
        }

        $this->assign('filter', $filter);
        $this->assign('activityRows', $activityRows);
        $this->assign('pagesize', $pagesize);
        $this->assign('pages', $pages);

        $this->display();
    }

    public function info(){

        $params = I();
        $Model = M('coupon_activity');
        $create = true;
        $data = array();
        //新建还是编辑
        if(isset($params['id']) && !empty($params['id'])){
            $create = false;

            $activity = $Model->find($params['id']);
            if (empty($activity)) {
                $this->error('信息有误，请重新操作！',U('index'));
            }
            $wechat_info = json_decode($activity['wechat_id'],true);
            if(!empty($wechat_info)){
                $this->assign('wechat_ids', $wechat_info);
            }
            $activity['wechat_id'];
            $this->assign('activity', $activity);

            $data['id'] = $params['id'];
            $data['updated_at'] = time();
            $data['operator_id'] = Session('admin')['id'];
            $data['operator_name'] = Session('admin')['name'];

        }else{
            $data['operator_id'] = Session('admin')['id'];
            $data['operator_name'] = Session('admin')['name'];
            $data['created_at'] = time();
            $data['updated_at'] = time();
        }

        if(IS_POST){
            $data['activity_name'] = isset($params['activity_name']) ? trim($params['activity_name']) : '';
            $data['keyword'] = isset($params['keyword']) ? trim($params['keyword']) : '';
            $data['content'] = isset($params['content']) ? trim($params['content']) : '';
            $data['type'] = isset($params['type']) ? intval($params['type']) : 1;
            $data['status'] = isset($params['status']) ? intval($params['status']) : 1;
            $data['start_time'] = isset($params['start_time']) ? strtotime(trim($params['start_time'])) : null;
            $data['end_time'] = isset($params['end_time']) ? strtotime(trim($params['end_time'])) : null;
            $wechat_id = $params['wechat_id'];

            if(empty($wechat_id)){
                //至少选择一个公众号
                $this->error('优惠券活动添加失败-至少选中一个微信公众号');
            }else{
                $data['wechat_id'] = json_encode($wechat_id);
            }

            $info_after = $data;
            unset($info_after['__hash__']);
            $info_after = json_encode(($info_after),JSON_UNESCAPED_UNICODE);
            if($Model->create()){
                if($create){
                    $desc ='添加微信优惠券活动数据';
                    if(!$Model->add($data)){
                        $this->error('优惠券活动添加失败-'.$Model->getError());
                }
                }else{
                    $desc ='修改微信优惠券活动数据';
                    $where['id'] = $data['id'];

                    //修改活动数据，清空redis缓存
                    $key = $data['keyword'];
                    S($key,null);

                    $info_before = json_encode($Model->where($where)->find(),JSON_UNESCAPED_UNICODE);
                    if($Model->save($data) === false){
                        $this->error('微信优惠券活动数据更新失败-'.$Model->getError());
                    }
                }
            }
            $function_name = "WxCoupon";
            Log::weixinLog($function_name, $this->public_name,$desc, $info_before, $info_after);
            $this->success("保存成功",U('index'));
            die;
        }
        $this->display();
    }


    public function couponList(){

        $params = I();
        $activity_id =  $params['id'];
        if(empty($activity_id)){
            $this->error("活动id不能为空");
        }
        $this->assign('id', $activity_id);
        $Model = M('coupon_code');
        $where = "1 = 1";
        $filter['status'] = isset($_REQUEST['status']) ? intval($_REQUEST['status']) : 0;

        $where .= " and activity_id = ". $activity_id;
        if($filter['status']){
            $where .= " and status = ". $filter['status'];
        }

        $total      = $Model->where("$where")->count();
        $pagesize   = C('PAGE_SIZE');
        $pageObject = new Page($total, $pagesize);
        $pages      = $pageObject->show();

        $page = I('get.p', 0);

        $couponRows = $Model->where("$where")
            ->page($page)->limit($pagesize)
            ->select();

        $activity = M('coupon_activity')->select();
        $this->assign('activity', $activity);
        $this->assign('filter', $filter);
        $this->assign('couponRows', $couponRows);
        $this->assign('pagesize', $pagesize);
        $this->assign('pages', $pages);
        $this->assign('back_url', U('index'));

        $this->display();
    }

    public function couponImport(){
        //$fid = Helper::getFirmIdOfUser();
        //如果是上传的文件，先解析文件内容输出到页面
        $params = I();
        $activity_id =  $params['id'];
        if(empty($activity_id)){
            $this->error("活动id不能为空");
        }
        $this->assign('id', $activity_id);
        if(isset($_FILES["input"]) && ($_FILES["input"]["error"] == 0)){
            //解析excel文件
            import("PHPExcel");
            ini_set("memory_limit", "1024M");
            $filePath = $_FILES['input']['tmp_name'];
            vendor("PHPExcel.PHPExcel");
            $objPHPExcel = \PHPExcel_IOFactory::load($filePath);
            $sheet_count = $objPHPExcel->getSheetCount();
            $data =array();//excel的数据集合
            for ($s = 0; $s < $sheet_count; $s++)
            {
                $currentSheet = $objPHPExcel->getSheet($s);// 当前页
                $row_num = $currentSheet->getHighestRow();// 当前页行数
                $col_max = $currentSheet->getHighestColumn(); // 当前页最大列号
                // 循环从第二行开始，第一行往往是表头
                for($i = 1; $i <= $row_num; $i++)
                {
                    $cell_values = array();
                    for($j = 'A'; $j <= $col_max; $j++)
                    {
                        $address = $j . $i; // 单元格坐标
                        $cell_values[$j] = $currentSheet->getCell($address)->getFormattedValue();
                    }
                    $data[] = $cell_values;
                }
            }
            //对data进行验证
            $cnt = count($data);
            if($cnt > 10001){
                $this->error('一次最多能导入10000条数据:');
            }
            $title = $data[0];
            $need = array(
                'A'=>'优惠券号',
            );
            $diff = array_diff($need, $title);
            if(!empty($diff)){
                $this->error('和给定模板的抬头不一致，差异:'.json_encode($diff));
            }
            unset($data[0]);
            $this->assign('data',$data);
           /* $res = M('coupon_activity')->select();
            $this->assign('activity_list',$res);*/
        }
        //如果上传的是内容：存入DB
        if(isset($_REQUEST['data_json']) and !empty($_REQUEST['data_json'])){
            $data = json_decode(trim($_REQUEST['data_json']), true);
            if(empty($data)){
                $this->error("传入数据为空-".count($data));
            }
            $Model = M('coupon_code');


            $time = time();
            $sum = 0;
            $error = 0;
            foreach ($data as $k => $v) {
                $code = trim($v['A']);
                $coupon_info['coupon_code'] = $code;
                $coupon_info['activity_id'] = $activity_id;
                $coupon_info['create_time'] = $time;
                //优惠券和活动id联合唯一索引，捕获数据库异常，保证不重复
                try{
                    $Model->add($coupon_info);
                    $sum++;
                }catch (\Exception $e){
                    $error++;
                }
            }

            $this->success('成功导入优惠券条数:'.$sum.' 失败优惠券条数:'.$error,U('couponList',array('id'=>$activity_id)));
            die;

        }
        $this->assign('back_url', U('couponList',array('id'=>$activity_id)));
        $this->display();
    }

}

?>