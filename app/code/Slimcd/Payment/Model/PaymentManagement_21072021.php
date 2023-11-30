<?php

namespace Slimcd\Payment\Model;

use Slimcd\Payment\Api\PaymentManagementInterface;

class PaymentManagement implements PaymentManagementInterface
{
    protected $request;
    protected $order;

    public function __construct(
        \Magento\Framework\Webapi\Rest\Request $request,
        \Magento\Sales\Model\Order $order
    ) {
        $this->request = $request;
        $this->order = $order;
    }
    public function paymentPostBackMethod()
    {
        try {
            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/Slimcd_api.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);
            $logger->info('Post data');
            $postdata = $this->request->getBodyParams();
            $logger->info('POSTDATA');
            $logger->info($postdata);
            $logger->info('POSTDATA');
            $logger->info($postdata['order_id']);
            if (isset($postdata['approved'])) {
                $order = $this->order->loadByIncrementId($postdata['order_id']);
                $slimcdType =  '';
                $slimcdFee =  '';
                if ($postdata['approved'] == "Y" || $postdata['approved'] == "B") {
                    if (isset($postdata['surcharge']) && $postdata['surcharge'] != 0.00 || isset($postdata['conveniencefee']) && $postdata['conveniencefee'] != 0.00) {
                        if ($postdata['surcharge'] != "0.00" && $postdata['conveniencefee'] != "0.00") {
                            $slimcdType = "Surcharge / convenience fee";
                            $slimcdFee =  (float)$postdata['surcharge'] + (float)$postdata['conveniencefee'];
                        } elseif ($postdata['surcharge'] != "0.00" && $postdata['conveniencefee'] == "0.00") {
                            $slimcdType = $postdata['receiptlabel'];
                            $slimcdFee =  (float)$postdata['surcharge'];
                        } elseif ($postdata['surcharge'] = "0.00" && $postdata['conveniencefee'] != "0.00") {
                            $slimcdType =  $postdata['receiptlabel'];
                            $slimcdFee =  (float)$postdata['conveniencefee'];
                        }
                    }

                    if ($slimcdType != '' && round($slimcdFee) > 0) {
                        $logger->info($slimcdType);
                        $logger->info($slimcdFee);
                        $order->setSlimFeeType($slimcdType);
                        $order->setSlimFee($slimcdFee);
                        $order->setBaseGrandTotal($order->getBaseGrandTotal() + $slimcdFee);
                        $order->setGrandTotal($order->getGrandTotal() + $slimcdFee);
                        $order->save();
                    }
                    $logger->info('before Ok');
                    echo 'ok';
                } else {
                    echo 'not ok';
                }
            } else {
                echo 'not ok';
            }
            die;
        } catch (\Exception $e) {
            $response = ['error' => $e->getMessage()];
        }
        return json_encode($response);
    }
}
