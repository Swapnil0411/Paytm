<?php

namespace Ranosys\Paytm\Controller\Standard;

class Cancel extends \Ranosys\Paytm\Controller\Paytm
{

    public function execute()
    {
        $this->_cancelPayment();
        $this->_checkoutSession->restoreQuote();
        $this->getResponse()->setRedirect(
            $this->getPaytmHelper()->getUrl('checkout')
        );
    }

}
