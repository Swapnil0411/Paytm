<?php

namespace Ranosys\Paytm\Observer;

use Magento\Framework\Event\ObserverInterface;

class SendMailOnOrderSuccess implements ObserverInterface
{

    protected $orderModel;

    protected $orderSender;

    protected $checkoutSession;
    
    public function __construct(
        \Magento\Sales\Model\OrderFactory $orderModel,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        \Magento\Checkout\Model\Session $checkoutSession
    )
    {
        $this->orderModel = $orderModel;
        $this->orderSender = $orderSender;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $orderIds = $observer->getEvent()->getOrderIds();
        if(count($orderIds))
        {
            $this->checkoutSession->setForceOrderMailSentOnSuccess(true);
            $order = $this->orderModel->create()->load($orderIds[0]);
            $this->orderSender->send($order, true);
        }
    }
}