<?php

namespace Ranosys\Paytm\Controller\Standard;

class Response extends \Ranosys\Paytm\Controller\Paytm {

    public function execute() {
        $comments = "";
        $request = $_POST;
        if (!empty($_POST)) {
            foreach ($_POST as $key => $value) {
                if ($key != "CHECKSUMHASH") {
                    $comments .= $key . "=" . $value . ", \n <br />";
                }
            }
        }
        $errorMsg = '';
        $successFlag = false;
        $resMsg = $this->getRequest()->getParam('RESPMSG');
        $globalErrorMass = "Your payment has been failed!";
        if (trim($resMsg) != '') {
            $globalErrorMass.=" Reason: " . $resMsg;
        }
        $orderMID = $this->getRequest()->getParam('MID'); // Get mechant Id
        $orderId = $this->getRequest()->getParam('ORDERID'); // Get Order Id
        $orderTXNID = $this->getRequest()->getParam('TXNID'); // Get Transaction Id
        $returnUrl = $this->getPaytmHelper()->getUrl('/');
        if (trim($orderId) != '' && trim($orderMID) != '') {
            $order = $this->getOrderById($orderId);
            $orderTotal = round($order->getGrandTotal(), 2);

            $orderStatus = $this->getRequest()->getParam('STATUS'); // Get order status
            $resCode = $this->getRequest()->getParam('RESPCODE'); // Get order status
            $orderTxnAmount = $this->getRequest()->getParam('TXNAMOUNT'); // Get Transaction amount

            if ($this->getPaytmModel()->validateResponse($request, $orderId)) {
                if ($orderStatus == "TXN_SUCCESS" && $orderTotal == $orderTxnAmount) {
                    // Creating an array with having all required parameters for status			
                    $requestParamList = array("MID" => $orderMID, "ORDERID" => $orderId);
                    $StatusCheckSum = $this->getPaytmModel()->generateStatusChecksum($requestParamList);
                    $requestParamList['CHECKSUMHASH'] = $StatusCheckSum;

                    // Calling the Payment Gateway getTxnStatus() function for verifying the transaction status.
                    $check_status_url = $this->getPaytmModel()->getNewStatusQueryUrl();
                    $responseParamList = $this->getPaytmHelper()->callNewAPI($check_status_url, $requestParamList);
                    if ($responseParamList['STATUS'] == "PENDING") {
                        $errorMsg = 'Paytm Transaction Pending!';
                        if (trim($resMsg) == '') {
                            $errorMsg.=" Reason: " . $resMsg;
                        }
                        $comments .= "Pending";
                        $order->setState("pending_payment")->setStatus("pending_payment");
                        $returnUrl = $this->getPaytmHelper()->getUrl('checkout/onepage/failure');
                    } else {
                        if ($responseParamList['STATUS'] == 'TXN_SUCCESS' && $responseParamList['TXNAMOUNT'] == $orderTxnAmount) {
                            $autoInvoice = $this->getPaytmModel()->autoInvoiceGen();
                            if ($autoInvoice == 'authorize_capture') {
                                $payment = $order->getPayment();
                                $payment->setTransactionId($orderTXNID)
                                        ->setPreparedMessage(__('Paytm transaction has been successful.'))
                                        ->setShouldCloseParentTransaction(true)
                                        ->setIsTransactionClosed(0)
                                        ->setAdditionalInformation(['Ranosys', 'paytm'])
                                        ->registerCaptureNotification($responseParamList['TXNAMOUNT'], true
                                );
                                $invoice = $payment->getCreatedInvoice();
                            }

                            $successFlag = true;
                            $comments .= "Success";
                            $order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING);
                            $order->setStatus($order::STATE_PROCESSING);
                            $order->setExtOrderId($orderId);
                            $returnUrl = $this->getPaytmHelper()->getUrl('checkout/onepage/success');
                        } else {
                            $errorMsg = 'It seems some issue in server to server communication. Kindly connect with admin.';
                            $comments .= "Fraud Detucted";
                            $order->setStatus($order::STATUS_FRAUD);
                            $returnUrl = $this->getPaytmHelper()->getUrl('checkout/onepage/failure');
                        }
                    }
                } else {
                    if ($orderStatus == "PENDING") {
                        $errorMsg = 'Paytm Transaction Pending!';
                        if (trim($resMsg) == '') {
                            $errorMsg.=" Reason: " . $resMsg;
                        }
                        $comments .= "Pending";
                        $order->setState("pending_payment")->setStatus("pending_payment");
                    } else {
                        $errorMsg = $globalErrorMass;
                        $comments .= $globalErrorMass;
                        $order->setStatus($order::STATE_CANCELED);
                    }
                    $returnUrl = $this->getPaytmHelper()->getUrl('checkout/onepage/failure');
                }
            } else {
                $errorMsg = $globalErrorMass . " Reason: Checksum Mismatched.";
                $comments .= "Fraud Detucted";
                $order->setStatus($order::STATUS_FRAUD);
                $returnUrl = $this->getPaytmHelper()->getUrl('checkout/onepage/failure');
            }
            $order->addStatusToHistory($order->getStatus(), $comments);
            $order->save();
            if ($successFlag) {
                $this->messageManager->addSuccess(__('Paytm transaction has been successful.'));
            } else {
                $this->messageManager->addError(__($errorMsg));
            }
        }
        $this->getResponse()->setRedirect($returnUrl);
    }

}
