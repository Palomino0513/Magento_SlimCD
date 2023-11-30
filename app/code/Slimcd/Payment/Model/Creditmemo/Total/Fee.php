<?php

namespace Slimcd\Payment\Model\Creditmemo\Total;

use Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal;

class Fee extends AbstractTotal
{
    /**
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @return $this
     */
    public function collect(\Magento\Sales\Model\Order\Creditmemo $creditmemo)
    {
        $creditmemo->setSlimFee(0);
        $creditmemo->setSlimFeeType('');

        $amount = $creditmemo->getOrder()->getSlimFee();
        $creditmemo->setSlimFee($amount);

        $slimFeeType = $creditmemo->getOrder()->getSlimFeeType();
        $creditmemo->setSlimFeeType($slimFeeType);

        $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $creditmemo->getSlimFee());
        $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $creditmemo->getSlimFee());

        return $this;
    }
}
