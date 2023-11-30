<?php

namespace Slimcd\Payment\Model\Invoice\Total;

use Magento\Sales\Model\Order\Invoice\Total\AbstractTotal;

class Fee extends AbstractTotal
{
    /**
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @return $this
     */
    public function collect(\Magento\Sales\Model\Order\Invoice $invoice)
    {
        $invoice->setSlimFee(0);
        $invoice->setSlimFeeType('');

        $amount = $invoice->getOrder()->getSlimFee();
        $invoice->setSlimFee($amount);

        $slimFeeType = $invoice->getOrder()->getSlimFeeType();
        $invoice->setSlimFeeType($slimFeeType);

        $invoice->setGrandTotal($invoice->getGrandTotal() + $invoice->getSlimFee());
        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $invoice->getSlimFee());

        return $this;
    }
}
