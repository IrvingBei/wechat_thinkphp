<?php
namespace Org\Pay;

/**
 * paypal支付工具类
 * @author    luffy <luffyzhao@vip.126.com>
 */
class PayPal extends Pay
{

    protected $_gateway = 'https://www.sandbox.paypal.com/cgi-bin/webscr';

    protected $param = array(
        "rm"            => '2',
        "cmd"           => '_xclick',
        "charset"       => 'utf-8',
        "currency_code" => 'USD',
        'item_number'   => 1,
    );

    /**
     * 获取规范的支付表单数据
     * @author luffy<luffyzhao@vip.126.com>
     * @dateTime 2016-03-11T15:56:19+0800
     * @param    string                   $method [description]
     * @return   [type]                           [description]
     */
    protected function _createPayform($orderInfo, $method = '')
    {
        if (empty($orderInfo)) {
            return false;
        }
        $data = array(
            'method'  => ($method == '') ? 'POST' : $method,
            'gateway' => $this->_gateway,
        );

        $data['params'] = $this->param;
        // PayPal账户中的email地址
        $data['params']['business'] = $this->_config['app_key'];
        // 客户交易返回地址
        $data['params']['return'] = $this->_createReturnUrl($orderInfo['id']);
        // 客户取消交易后返回地址
        $data['params']['cancel_return'] = $this->_cancelReturn($orderInfo['id']);
        // 交易后paypal返回网站地址
        $data['params']['notify_url'] = $this->_createNotifyUrl($orderInfo['id']);
        // 订单描述
        $data['params']['item_name'] = $this->_getSubject($orderInfo['order_sn']);
        // 订单编号
        $data['params']['item_number'] = $this->_getTradeSn($orderInfo['order_sn']);
        // 价格
        $data['params']['amount'] = $orderInfo['order_amount'];

        return $data;
    }

    public function verifyNotify($value = '')
    {
        # code...
    }

    public function verifyResult($value = '')
    {
        # code...
    }
}
