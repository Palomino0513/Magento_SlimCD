<?php

namespace Slimcd\Payment\Model;

class Payment extends \Magento\Payment\Model\Method\AbstractMethod
{
    const CODE = 'slimcdpayment';
    protected $_code = self::CODE;
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_isGateway = true;
    protected $_countryFactory;
    protected $_canOrder = true;
    protected $_isInitializeNeeded = false;
    protected $redirect_uri;
}
