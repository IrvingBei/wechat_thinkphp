<?php
namespace Org\Pay;

include VENDOR_PATH . 'autoload.php';
use \PayPal\Api\Amount;
use \PayPal\Api\Details;
use \PayPal\Api\Item;
use \PayPal\Api\ItemList;
use \PayPal\Api\Payer;
use \PayPal\Api\Payment;
use \PayPal\Api\PaymentExecution;
use \PayPal\Api\RedirectUrls;
use \PayPal\Api\Transaction;
use \PayPal\Exception\PayPalConnectionException;

class PayDome extends Pay
{
    private $paypal;
    private $itemList;
    private $payer;
    private $payment;

    public function __construct($paymentInfo = array())
    {
        parent::__construct($paymentInfo);

        $this->paypal = new \PayPal\Rest\ApiContext(
            new \PayPal\Auth\OAuthTokenCredential(
                $this->_config['app_key'],
                $this->_config['app_token']
            )
        );
        $configs = array(
            'mode'                   => 'LIVE',
            'http.ConnectionTimeOut' => 30,
            'service.EndPoint'       => "https://api.paypal.com",
        );
        $this->paypal->setConfig($configs);

        $this->payer = new Payer();
        $this->payer->setPaymentMethod('paypal');
    }

    /**
     * 支付
     * @author luffy<luffyzhao@vip.126.com>
     * @dateTime 2016-03-25T10:59:14+0800
     * @param    string                   $invoiceNumber 外部交易单号
     * @param    [type]                   $amount      支付金额,如果有物流费用 那个包含这个物流费用
     * @param    [type]                   $shippingFee [description]
     * @return   [type]                                [description]
     */
    public function pay($invoiceNumber, $amount, $shippingFee = 0, $type = 'order')
    {
        // 支付手续费
        $paymentRate = sprintf("%.2f", $this->_config['payment_rate'] * $amount);
        // 物流信息 手续费
        $details = new Details();
        $details->setShipping($shippingFee)
            ->setHandlingFee($paymentRate)
            ->setSubtotal($amount - $shippingFee);

        // 支付价格
        $amounts = new Amount();
        $amounts->setCurrency('USD')
            ->setTotal($amount + $paymentRate)
            ->setDetails($details);

        // 支付物品清单
        $items = new Item();
        $items->setName($invoiceNumber)
            ->setCurrency('USD')
            ->setQuantity(1)
            ->setPrice($amount - $shippingFee);

        // 支付物品清单合集
        $itemList = new ItemList();
        $itemList->setItems([$items]);

        // 支付信息
        $transaction = new Transaction();
        $transaction->setAmount($amounts)
            ->setItemList($itemList)
            ->setDescription($this->_getSubject($invoiceNumber, $type))
            ->setInvoiceNumber($invoiceNumber);
        // 返回URL
        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl($this->_createReturnUrl($invoiceNumber, $type))
            ->setCancelUrl($this->_cancelReturn($invoiceNumber, $type));

        //创建、处理和管理付款。
        $this->payment = new Payment();
        $this->payment->setIntent('sale')
            ->setPayer($this->payer)
            ->setRedirectUrls($redirectUrls)
            ->setTransactions([$transaction]);

        try {
            $this->payment->create($this->paypal);
            $approvalUrl = $this->payment->getApprovalLink();
        } catch (PayPalConnectionException $e) {
            // dump($e);die();
            $this->_error = $e->getMessage();
            return false;
        }

        return $approvalUrl;

    }

    /**
     * [verifyNotify description]
     * @author luffy<luffyzhao@vip.126.com>
     * @dateTime 2016-03-17T19:26:54+0800
     * @param    [type]                   $orderInfo [description]
     * @return   [type]                              [description]
     */
    public function verifyReturn($orderInfo)
    {
        $params = I('get.');

        if (!isset($params['paymentId'], $params['PayerID'])) {
            return false;
        }

        if ((bool) $params['success'] === 'false') {
            return false;
        }

        $paymentID = $params['paymentId'];
        $payerId   = $params['PayerID'];

        $payment = Payment::get($paymentID, $this->paypal);

        $execute = new PaymentExecution();
        $execute->setPayerId($payerId);

        try {
            $result = $payment->execute($execute, $this->paypal);
            return $paymentID;
        } catch (PayPalConnectionException $ex) {
            return false;
        }

    }

}
