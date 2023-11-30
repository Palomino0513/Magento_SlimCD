<?php

namespace Slimcd\Payment\Block\Adminhtml\Sales;

class Totals extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Directory\Model\Currency
     */
    protected $_currency;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Directory\Model\Currency $currency,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_currency = $currency;
    }

    /**
     * Retrieve current order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->getParentBlock()->getOrder();
    }

    /**
     * @return mixed
     */
    public function getSource()
    {
        return $this->getParentBlock()->getSource();
    }

    /**
     * @return string
     */
    public function getCurrencySymbol()
    {
        return $this->_currency->getCurrencySymbol();
    }

    /**
     *
     *
     * @return $this
     */
    public function initTotals()
    {
        $this->getParentBlock();
        $this->getOrder();
        $this->getSource();

        $total = new \Magento\Framework\DataObject(
            [
                'code' => 'surcharge',
                'value' => $this->getOrder()->getSlimFee(),
                'label' => $this->getOrder()->getSlimFeeType(),
            ]
        );
        if ($this->getOrder()->getSlimFee() > 0) {
            $this->getParentBlock()->addTotalBefore($total, 'grand_total');
        }

        return $this;
    }
}
