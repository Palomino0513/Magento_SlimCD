<?php

namespace Slimcd\Payment\Controller\Index;

class OrderPlace extends \Magento\Framework\App\Action\Action
{
    protected $checkoutSession;
    protected $redirect;
    protected $cartRepositoryInterface;
    protected $cartManagementInterface;
    protected $order;
    protected $scopeConfig;
    protected $curl;
    protected $messageManager;
    protected $orderFactory;
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepositoryInterface,
        \Magento\Quote\Api\CartManagementInterface $cartManagementInterface,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Sales\Model\Order $order,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Sales\Model\OrderFactory $orderFactory
    ) {
        parent::__construct($context);
        //$this->_pageFactory = $pageFactory;
        $this->checkoutSession = $checkoutSession;
        $this->redirect        = $redirect;
        $this->cartRepositoryInterface = $cartRepositoryInterface;
        $this->cartManagementInterface = $cartManagementInterface;
        $this->order = $order;
        $this->scopeConfig = $scopeConfig;
        $this->curl = $curl;
        $this->messageManager = $messageManager;
        $this->orderFactory = $orderFactory;
    }

    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $orderId = $this->checkoutSession->getLastOrderId();
        $order = $this->orderFactory->create()->load($orderId);
        if ($order) {

            $billing = $order->getBillingAddress();

            $payment = $order->getPayment();

            $payment->setTransactionId("-1");
            $payment->setAdditionalInformation(
                [\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS => ["Transaction is yet to complete"]]
            );
            $trn = $payment->addTransaction(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_CAPTURE, null, true);
            $trn->setIsClosed(0)->save();
            $payment->addTransactionCommentsToOrder(
                $trn,
                "The transaction is yet to complete."
            );
            $payment->setParentTransactionId(null);
            $payment->save();
            $order->save();

            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

            $configData = $this->scopeConfig->getValue("payment/slimcdpayment", $storeScope);
            $payLoad = [
                "username" => trim($configData['slimcd_API']['api_username']),
                "clientid" => trim((string)$configData['slimcd_API']['client_id']),
                "siteid" => trim((string)$configData['slimcd_API']['site_id']),
                "priceid" => trim((string)$configData['slimcd_API']['price_id']),
                "password" => trim((string)$configData['slimcd_API']['password']),
                "formname" => $params['seloption'],
                "transtype" => trim($configData['slimcd_card']['transtype']),
                "amount" => round((int)$order->getGrandTotal(), 2),
                "first_name" => $billing->getFirstname(),
                "last_name" => $billing->getLastname(),
                "address" => $billing->getStreet()[0],
                "city"  => $billing->getCity(),
                "state" =>  $billing->getRegion(),
                "zip" =>  $billing->getPostcode(),
                "order_id" => $order->getRealOrderId(),
                'receiptlabel' => $params['receiptlabel'],
                'email' => $billing->getEmail(),
                'company' => is_null($billing->getCompany()) ? '' : $billing->getCompany(),
                'customer_notes' => $params['customernoteslim'],
            ];
            $paymentSession = $this->getSession($payLoad);
            if ($paymentSession && $paymentSession['response'] === "Success") {
                $redirect = 'https://stats.slimcd.com/soft/showsession.asp?sessionid=' . $paymentSession['sessionid'];
                $this->_redirect($redirect);
                return;
            } else {
                $this->messageManager->addError(__($paymentSession['response'] . ' : ' .  $paymentSession['description'] . ' - Unable to process Slimcd payment gateway '));
                $this->_redirect('checkout/cart');
                return;
            }
        }
    }

    /**
     * Get session
     *
     * @param array $payLoad
     * @return array 
     */
    private function getSession($payLoad)
    {
        // print_r(json_encode($payLoad));
        $this->curl->setOption(CURLOPT_HEADER, 0);
        $this->curl->setOption(CURLOPT_RETURNTRANSFER, true);
        //set curl header
        $this->curl->addHeader("Content-Type", "application/json");
        //post request with url and data
        $this->curl->post("https://stats.slimcd.com/soft/json/jsonscript.asp?service=CreateSession", json_encode($payLoad));
        //read response
        $response = $this->curl->getBody();
        $responseData = json_decode($response);
        if ($responseData->reply->response === "Success") {
            return [
                "response" => $responseData->reply->response,
                "sessionid" => $responseData->reply->datablock->sessionid
            ];
        } else {
            return [
                "response" => $responseData->reply->response,
                "description" => $responseData->reply->description
            ];
        }
    }
}
