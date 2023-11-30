<?php

namespace Slimcd\Payment\Block\Adminhtml\Sales\Order\Creditmemo;

class Totals extends \Magento\Framework\View\Element\Template
{
    /**
     * Order invoice
     *
     * @var \Magento\Sales\Model\Order\Creditmemo|null
     */
    protected $_creditmemo = null;

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $_source;

    /**
     * OrderFee constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Get data (totals) source model
     *
     * @return \Magento\Framework\DataObject
     */
    public function getSource()
    {
        return $this->getParentBlock()->getSource();
    }

    public function getCreditmemo()
    {
        return $this->getParentBlock()->getCreditmemo();
    }
    /**
     * Initialize payment fee totals
     *
     * @return $this
     */
    public function initTotals()
    {
        $this->getParentBlock();
        $this->getCreditmemo();
        $this->getSource();

        $fee = new \Magento\Framework\DataObject(
            [
                'code' => 'surcharge',
                'strong' => false,
                'value' => $this->getCreditmemo()->getSlimFee(),
                'label' => $this->getCreditmemo()->getSlimFeeType(),
            ]
        );
        if ($this->getCreditmemo()->getSlimFee() > 0) {
            $this->getParentBlock()->addTotalBefore($fee, 'grand_total');
        }

        return $this;
    }
}
