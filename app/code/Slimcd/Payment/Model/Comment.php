<?php

namespace Slimcd\Payment\Model;

use Magento\Framework\View\Element\AbstractBlock;
use Magento\Config\Model\Config\CommentInterface;

class Comment extends AbstractBlock implements CommentInterface
{
    protected $_storeManager;
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_storeManager = $storeManager;
    }
    public function getCommentText($elementValue)
    {
        $redirectUrl = $this->_storeManager->getStore()->getBaseUrl() . 'payment';
        $postBackUrl = $this->_storeManager->getStore()->getBaseUrl() . 'rest/V1/slimcd-payment/postback';

        return "<b style='color:red'>POST BACK URL :</b>" . $postBackUrl . "<br/><b style='color:red'>REDRIECT URL :</b>" . $redirectUrl;
    }
}
