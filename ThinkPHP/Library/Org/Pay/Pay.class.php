<?php
namespace Org\Pay;

define('BASE_URL', 'http://' . $_SERVER['HTTP_HOST']);

/**
 * 支付工具类
 * @author    luffy <luffyzhao@vip.126.com>
 */
abstract class Pay
{

    /**
     *  外部处理网关
     * @var string
     */
    protected $_gateway = '';
    /**
     * 支付方式唯一标识
     * @var string
     */
    protected $_code = '';
    /**
     * 支付方式配置
     * @var array
     */
    protected $_config = array();
    /**
     * [$_error description]
     * @var [type]
     */
    protected $_error;

    /**
     * [__construct description]
     * @author luffy<luffyzhao@vip.126.com>
     * @dateTime 2016-03-11T14:52:41+0800
     * @param    array                    $payment_info [description]
     */
    public function __construct($paymentInfo = array())
    {
        $this->_config = $paymentInfo;
    }

    /**
     * 获取错误信息
     * @author luffy<luffyzhao@vip.126.com>
     * @dateTime 2016-03-25T11:16:56+0800
     * @return   [type]                   [description]
     */
    public function getError()
    {
        return $this->getError;
    }

    /**
     * 获取通知地址
     * @author luffy<luffyzhao@vip.126.com>
     * @dateTime 2016-03-11T14:54:20+0800
     * @param    [type]                   $order_id [description]
     * @return   [type]                             [description]
     */
    protected function _createNotifyUrl($orderCode, $type = 'order')
    {
        switch ($type) {
            case 'order':
                return BASE_URL . "/index.php/pay/notify/orderCode/{$orderCode}";
                break;
            case 'purchaseDeposit':
                return BASE_URL . "/index.php/pay/purchaseDepositNotify/purchaseSn/{$orderCode}";
                break;
            case 'purchaseBalance':
                return BASE_URL . "/index.php/pay/purchaseBalanceNotify/purchaseSn/{$orderCode}";
                break;
            default:
                return BASE_URL . "/index.php/pay/notify/orderCode/{$orderCode}";
                break;
        }
    }

    /**
     * 获取返回地址
     * @author luffy<luffyzhao@vip.126.com>
     * @dateTime 2016-03-11T14:54:48+0800
     * @param    [type]                   $order_id [description]
     * @return   [type]                             [description]
     */
    protected function _createReturnUrl($orderCode, $type = 'order')
    {
        switch ($type) {
            case 'order':
                return BASE_URL . "/index.php/pay/payReturn/orderCode/{$orderCode}";
                break;
            case 'purchaseDeposit':
                return BASE_URL . "/index.php/pay/purchaseDepositPayReturn/purchaseSn/{$orderCode}";
                break;
            case 'purchaseBalance':
                return BASE_URL . "/index.php/pay/purchaseBalancePayReturn/purchaseSn/{$orderCode}";
                break;
            default:
                return BASE_URL . "/index.php/pay/payReturn/orderCode/{$orderCode}";
                break;
        }

    }

    /**
     * 客户取消交易后返回地址
     * @author luffy<luffyzhao@vip.126.com>
     * @dateTime 2016-03-11T16:11:22+0800
     * @param    [type]                   $order_id [description]
     * @return   [type]                             [description]
     */
    protected function _cancelReturn($orderCode, $type = 'order')
    {
        switch ($type) {
            case 'order':
                return BASE_URL . "/index.php/pay/payCancel/orderCode/{$orderCode}";
                break;
            case 'purchaseDeposit':
                return BASE_URL . "/index.php/pay/purchaseDepositPayCancel/purchaseSn/{$orderCode}";
                break;
            case 'purchaseBalance':
                return BASE_URL . "/index.php/pay/purchaseBalancePayCancel/purchaseSn/{$orderCode}";
                break;
            default:
                return BASE_URL . "/index.php/pay/payCancel/orderCode/{$orderCode}";
                break;
        }

    }

    /**
     * 获取外部交易号
     * @author luffy<luffyzhao@vip.126.com>
     * @dateTime 2016-03-11T14:55:18+0800
     * @param    [type]                   $order_info [description]
     * @return   [type]                               [description]
     */
    protected function _getTradeSn($orderSn)
    {
        return $orderSn;
    }

    /**
     * 获取订单简介
     * @author luffy<luffyzhao@vip.126.com>
     * @dateTime 2016-03-11T14:56:27+0800
     * @param    [type]                   $order_info [description]
     * @return   [type]                               [description]
     */
    protected function _getSubject($orderSn, $type = 'order')
    {
        switch ($type) {
            case 'order':
                return 'sinob2b英文站订单:' . $orderSn;
                break;
            case 'purchaseDeposit':
            case 'purchaseBalance':
                return 'sinob2b英文站采购单:' . $orderSn;
                break;
            default:
                return 'sinob2b英文站订单:' . $orderSn;
                break;
        }

    }

    /**
     * 获取通知信息
     * @author luffy<luffyzhao@vip.126.com>
     * @dateTime 2016-03-11T14:58:17+0800
     * @return   [type]                   [description]
     */
    protected function _getNotify()
    {
        /* 如果有POST的数据，则认为POST的数据是通知内容 */
        if (!empty($_POST)) {
            return $_POST;
        }

        /* 否则就认为是GET的 */
        return $_GET;
    }

}
