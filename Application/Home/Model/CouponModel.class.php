<?php

namespace Home\Model;
use Think\Model;

/**
 * 领券模型
 */
class CouponModel extends WechatModel {

	function reply($dataArr, $keywordArr = array()) {

        $open_id = $dataArr['FromUserName'];
        $union_id = null;

        //$user_info = $this->getUserInfo($open_id);
        //$union_id = $user_info['unionid'];

        $type = $keywordArr['type'];
        if($type == 1){
            $user_id = $open_id;
        }else{
            $user_id = $union_id;
        }
        $activity_id = $keywordArr['id'];

        $coupon_code = $this->getCouponCodeRecord($user_id,$activity_id,$type);

        if(empty($coupon_code)){
            //未查询到券信息，进入领券逻辑
            $time = time();
            if($time < $keywordArr['start_time']){
                $result = '活动尚未开始，敬请期待';
            }elseif ($time > $keywordArr['end_time']){
                $result = '活动已结束，感谢您的关注';
            }else{
                $params['open_id'] = $open_id;
                $params['union_id'] = $union_id;
                $params['wechat_id'] = $dataArr['ToUserName'];
                $params['activity_id'] = $activity_id;
                $coupon_result = $this->getCouponCodeResult($params);
                if($coupon_result == 1){
                    $result = '很抱歉券已领完，感谢您的关注';
                }elseif ($coupon_result == 2){
                    $result = '数据异常，请稍候再试';
                }else{
                    $result = sprintf($keywordArr['content'],strtoupper($coupon_result));
                    //$result = '您的优惠券是：'.strtoupper($coupon_result);
                }
            }
        }else{
            //查询到券，回复券号
            $result = '您已领取过优惠券，券号是：'.strtoupper($coupon_code);
        }
        $this->replyText($result);
        die;
	}

    /**
     * @param $user_id
     * @param $activity_id
     * @param int $type
     * @return bool|mixed
     * 根据用户唯一标识和活动名称查询用户领取的券号
     */
	private function getCouponCodeRecord($user_id,$activity_id,$type=1){
	    $where['activity_id'] = $activity_id;
	    if($type == 1){
	        //根据openId查找记录
            $where['open_id'] = $user_id;
        }else{
            //根据unionId查找记录
            $where['union_id'] = $user_id;
        }

        $coupon = M('coupon_record')->where($where)->getField('coupon_code');
	    if(empty($coupon)){
	        //未领券
            return false;
        }else{
	        return $coupon;
        }
    }

    //从优惠券表查出一条有效券号
    //插入优惠券记录表记录信息
    //更新券状态
    //返回券结果，无券或券号
    /**
     * @param $params
     * @return int
     * 根据参数查询领券结果
     */
    private function getCouponCodeResult($params){

	    $where['activity_id'] = $params['activity_id'];
	    $where['status'] = 1;
	    $coupon_info = M('coupon_code')->where($where)->find();
	    if(empty($coupon_info)){
	        //已领完
            return 1;
        }else{
	        //查询到券号
            $coupon_code = $coupon_info['coupon_code'];
            $time = time();
            $params['coupon_code'] = $coupon_code;
            $params['get_time'] = $time;
            $coupon_info['status'] = 2;
            $coupon_info['get_time'] = $time;

            //领券记录和更改券状态

            //开启事务
            $model = new Model();
            $model->startTrans();
            $result1 = M('coupon_code')->save($coupon_info);
            $result2 = M('coupon_record')->add($params);

            if($result1 && $result2){
                //同时成功提交
                $model->commit();
                return $coupon_code;
            }else{
                //数据异常回滚
                $model->rollback();
                return 2;
            }
        }

    }


}
