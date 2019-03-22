<?php

namespace Ranosys\Paytm\Controller;

abstract class Paytm extends \Magento\Framework\App\Action\Action {

    protected $_checkoutSession;
    protected $_orderFactory;
    protected $_customerSession;
    protected $_quote = false;
    protected $_paytmModel;
    protected $_paytmHelper;
    protected $_orderHistoryFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Ranosys\paytm\Model\paytm $twopaytmModel
     * @param \Ranosys\paytm\Helper\paytm $paytmHelper
     */
    public function __construct(
    \Magento\Framework\App\Action\Context $context, \Magento\Customer\Model\Session $customerSession, \Magento\Checkout\Model\Session $checkoutSession, \Magento\Sales\Model\OrderFactory $orderFactory, \Magento\Sales\Model\Order\Status\HistoryFactory $orderHistoryFactory, \Ranosys\Paytm\Model\Paytm $paytmModel, \Ranosys\Paytm\Helper\Data $paytmHelper
    ) {
        $this->_customerSession = $customerSession;
        $this->_checkoutSession = $checkoutSession;
        $this->_orderFactory = $orderFactory;
        $this->_orderHistoryFactory = $orderHistoryFactory;
        $this->_paytmModel = $paytmModel;
        $this->_paytmHelper = $paytmHelper;
        parent::__construct($context);
    }

    protected function getPaytmModel() {
        return $this->_paytmModel;
    }

    protected function getPaytmHelper() {
        return $this->_paytmHelper;
    }

    protected function getCheckoutSession() {
        return $this->_checkoutSession;
    }

    protected function getCustomerSession() {
        return $this->_customerSession;
    }

    protected function _cancelPayment($errorMsg = '') {
        $gotoPaymentSection = false;
        $this->_paytmHelper->cancelCurrentOrder($errorMsg);
        if ($this->_checkoutSession->restoreQuote()) {
            //Redirect to payment step
            $gotoPaymentSection = 'paymentMethod';
        }

        return $gotoPaymentSection;
    }

    protected function getOrderById($order_id) {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $order = $objectManager->get('Magento\Sales\Model\Order');
        $order_info = $order->loadByIncrementId($order_id);
        return $order_info;
    }

    protected function getOrder() {
        return $this->_orderFactory->create()->loadByIncrementId(
                        $this->_checkoutSession->getLastRealOrderId()
        );
    }

    protected function addOrderHistory($order, $comment) {
        $history = $this->_orderHistoryFactory->create()
                ->setComment($comment)
                ->setEntityName('order')
                ->setOrder($order);
        $history->save();
        return true;
    }

    protected function getQuote() {
        if (!$this->_quote) {
            $this->_quote = $this->_getCheckoutSession()->getQuote();
        }
        return $this->_quote;
    }

}
