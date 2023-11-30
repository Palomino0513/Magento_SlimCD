<?php

namespace Slimcd\Payment\Block\Order;

class Totals extends \Magento\Framework\View\Element\AbstractBlock
{
    public function initTotals()
    {
        $orderTotalsBlock = $this->getParentBlock();
        $order = $orderTotalsBlock->getOrder();
        if ($order->getSlimFee() > 0) {
            $orderTotalsBlock->addTotal(new \Magento\Framework\DataObject ([
                    'code'       => 'Surcharge',
                    'label'      => __($order->getSlimFeeType()),
                    'value'      => $order->getSlimFee(),
                    'base_value' => $order->getSlimFee(),
                ]), 'subtotal');
        }
    }
}
