<?php

namespace Slimcd\Payment\Model\Config\Source;

class Transtype implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {

        return [
            ['value' => 'SALE', 'label' => __('SALE')],
            ['value' => 'AUTH', 'label' => __('AUTH')],
        ];
    }
}
