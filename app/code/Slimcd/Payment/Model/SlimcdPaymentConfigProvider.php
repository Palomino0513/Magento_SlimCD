<?php

namespace Slimcd\Payment\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class SlimcdPaymentConfigProvider implements ConfigProviderInterface
{
    const METHOD_CODE   = 'slimcdpayment';
    protected $scopeConfig;
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }
    
    public function getConfig()
    {
        $config = [
            'payment' => [
                self::METHOD_CODE => $this->scopeConfig->getValue("payment/slimcdpayment", ScopeInterface::SCOPE_STORE)
            ]
        ];
        return $config;
    }
}
