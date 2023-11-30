<?php

namespace Slimcd\Payment\Controller\Index;

use Magento\Sales\Model\Order;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $redirect;
    protected $onePage;
    protected $order;
    protected $scopeConfig;
    protected $curl;
    protected $messageManager;
    protected $orderFactory;
    protected $transactionRepository;
    protected $cart;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Type\Onepage $onePage,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Sales\Model\Order $order,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Sales\Api\TransactionRepositoryInterface $transactionRepository
    ) {
        parent::__construct($context);
        $this->redirect = $redirect;
        $this->order = $order;
        $this->onePage = $onePage;
        $this->scopeConfig = $scopeConfig;
        $this->curl = $curl;
        $this->cart = $cart;
        $this->messageManager = $messageManager;
        $this->transactionRepository = $transactionRepository;
    }
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/Slimcd_redirect_api.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('params');
        $logger->info($params);

        $paymentStatus = $this->slimcdPaymentStatus($params['sessionid']);
        $logger->info($paymentStatus);
        $orderId = $params['order_id'];

        $order = $this->order->loadByIncrementId($orderId);
        $payment = $order->getPayment();
        if ($paymentStatus && $paymentStatus['response'] === "Success") {
            $paymentId = $paymentStatus['gateid'];
            if ($order) {
                $order->setState(Order::STATE_PROCESSING)
                    ->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_PROCESSING));
                $transaction = $this->transactionRepository->getByTransactionId(
                    "-1",
                    $payment->getId(),
                    $order->getId()
                );
                if ($transaction) {
                    $transaction->setTxnId($paymentId);
                    $transaction->setAdditionalInformation(
                        "Slimcd Transaction Id",
                        $paymentId
                    );
                    $transaction->setAdditionalInformation(
                        "status",
                        "successful"
                    );
                    $transaction->setIsClosed(1);
                    $transaction->save();
                }
                $payment->addTransactionCommentsToOrder(
                    $transaction,
                    "Transaction is completed successfully"
                );
                $payment->setParentTransactionId(null);

                # send new email
                $order->setCanSendNewEmailFlag(true);
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $objectManager->create('Magento\Sales\Model\OrderNotifier')->notify($order);
              
                $payment->save();
                $order->save();

                $logger->info("Payment for $paymentId  was credited.");

                $this->_redirect('checkout/onepage/success/');
                return;
            } else {
                $this->messageManager->addError(__("Issue in payment, Please choose another payment method"));
                $this->_redirect('checkout/cart');
                return;
            }
        } else {
            try {
                $items = $order->getItemsCollection();
                foreach ($items as $item) {
                    $this->cart->addOrderItem($item);
                }
                $this->cart->save();
            } catch (Exception $e) {
                $message = $e->getMessage();
                $this->logger->info("Not able to add Items to cart Exception MEssage" . $message);
            }
            $order->cancel();

            $payment->setParentTransactionId(null);
            $payment->save();
            $order->save();
            $this->messageManager->addError(__("Issue in payment, Please choose another payment method"));
            $this->_redirect('checkout/cart');
            return;
        }
    }
    /**
     * Check the status of payment
     *
     * @param string $sessionid
     * @return null|string
     */
    private function slimcdPaymentStatus($sessionid = "")
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

        $slimConfigData = $this->scopeConfig->getValue("payment/slimcdpayment", $storeScope);
        $PayLoad = [
            "username" => trim($slimConfigData['slimcd_API']['api_username']),
            "password" => is_null($slimConfigData['slimcd_API']['password']) ? '' : $slimConfigData['slimcd_API']['password'],
            "sessionid" => $sessionid,
            "wait" => "5",
            "waitforcompleted" => "no",
        ];
        $this->curl->setOption(CURLOPT_HEADER, 0);
        $this->curl->setOption(CURLOPT_RETURNTRANSFER, true);
        //set curl header
        $this->curl->addHeader("Content-Type", "application/json");
        //post request with url and data
        $this->curl->post('https://stats.slimcd.com/soft/json/jsonscript.asp?service=CheckSession', json_encode($PayLoad));
        //read response
        $response = $this->curl->getBody();
        $responseData = json_decode($response);
        if ($responseData->reply->response === "Success") {
            return [
                "response" => $responseData->reply->response,
                "gateid" => $responseData->reply->datablock->gateid
            ];
        } else {
            return [
                "response" => $responseData->reply->response,
                "description" => $responseData->reply->description
            ];
        }
    }
}
