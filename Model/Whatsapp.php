<?php
use Twilio\Rest\Client;

class Cammino_Pixremember_Model_Whatsapp
{
    public function sendMessage($payments) {

        header("Content-type: text/html; charset=utf-8");
        
        $helper = Mage::helper("pixremember");

        $sid = $helper->getTwilioAccountSid();
        $token = $helper->getTwilioAuthToken();
        $twilioWhatsapp = $helper->getTwilioWhatsappNumber();

        if($sid && $token && $twilioWhatsapp):
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
                        $message = $helper->renderWhatsappBody($customerName, $pixUrl);
                        $sent = $twilio->messages->create(
                            "whatsapp:" . $cellphone,
                            array(
                                "from" => "whatsapp:" . $twilioWhatsapp,
                                "body" => $message
                            )
                        );

                        if($sent->sid) {
                            $addata = unserialize($payment->getData("additional_data"));
                            $addata["pixremember"] = true;
                            $payment->setAdditionalData(serialize($addata))->save();

                            if($helper->logIsActive()) {
                                $helper->log("Whatsapp enviado, cliente: " . $customerId . ", pedido: " . $orderId);
                                $helper->log("Mensagem: " . $message);
                            }

                        } else { 
                            $helper->log("Whatsapp não pode ser enviado, cliente: " . $customerId . ", pedido: " . $orderId);
                            $helper->log($sent);
                        }
                    } else {
                        $helper->log("Telefone inválido para enviar whatsapp para o cliente: " . $customerId . ", para o pedido: " . $orderId);
                    }
                    
                } catch (Exception $e) {
                    $helper->log($e->getMessage());
                    return false;
                }

            endforeach;
        endif;
    }
}