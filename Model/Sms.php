<?php
use Twilio\Rest\Client;

class Cammino_Pixremember_Model_Sms
{
    public function sendMessage($payments) {

        header("Content-type: text/html; charset=utf-8");
        
        $helper = Mage::helper("pixremember");

        $sid = $helper->getTwilioAccountSid();
        $token = $helper->getTwilioAuthToken();
        $twilioSMS = $helper->getTwilioSMSNumber();

        if($sid && $token && $twilioSMS):
            $twilio = new Client($sid, $token);
            
            foreach($payments as $payment):

                try {
                    $orderId = $payment->getParentId();
                    $order = Mage::getModel("sales/order")->load($orderId);
                    $pixUrl = Mage::getModel("pixremember/job")->getPixUrl($payment);
                    
                    $customerId = $order->getCustomerId();
                    $customer = Mage::getModel('customer/customer')->load($customerId);
                    
                    $customerName = $customer->getFirstname();
                    $cellphone = $helper->getCustomerCellphone($customer);

                    if($cellphone != false) {
                        $cellphone = $helper->cellphoneToTwillioFormat($cellphone);
                        $message = $helper->renderSMSBody($customerName, $pixUrl);
                        $sent = $twilio->messages->create(
                            $cellphone,
                            array(
                                "from" => $twilioSMS,
                                "body" => $message
                            )
                        );

                        if($sent->sid) {
                            $addata = unserialize($payment->getData("additional_data"));
                            $addata["pixremember"] = true;
                            $payment->setAdditionalData(serialize($addata))->save();

                            if($helper->logIsActive()) {
                                $helper->log("SMS enviado, cliente: " . $customerId . ", pedido: " . $orderId);
                                $helper->log("Mensagem: " . $message);
                            }

                        } else { 
                            $helper->log("SMS não pode ser enviado, cliente: " . $customerId . ", pedido: " . $orderId);
                            $helper->log($sent);
                        }
                    } else {
                        $helper->log("Telefone inválido para enviar sms para o cliente: " . $customerId . ", para o pedido: " . $orderId);
                    }
                    
                } catch (Exception $e) {
                    $helper->log($e->getMessage());
                    return false;
                }

            endforeach;
        endif;
    }
}