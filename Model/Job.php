<?php
require __DIR__ . '/../lib/twilio/Twilio/autoload.php';

class Cammino_Pixremember_Model_Job
{
    public function notify() {

        $helper = Mage::helper("pixremember");
        
        $moduleIsActive     = $helper->moduleIsActive();
        $notifyByEmail      = $helper->notifyByEmail();
        $notifyByWhatsapp   = $helper->notifyByWhatsapp();
        $notifyBySMS        = $helper->notifyBySMS();
        
        if($moduleIsActive && ($notifyByEmail || $notifyByWhatsapp || $notifyBySMS)):

            $payments = $this->getPixOrders();

            if(empty($payments)) {
                return;
            }

            if($notifyByEmail):
                Mage::getModel("pixremember/email")->sendEmail($payments);
            endif;

            if($notifyByWhatsapp):
                Mage::getModel("pixremember/whatsapp")->sendMessage($payments);
            endif;
            
            if($notifyBySMS):
                Mage::getModel("pixremember/sms")->sendMessage($payments);
            endif;

        endif;
    }

    public function getPixOrders() {
        $payments = Mage::getModel( 'sales/order_payment' )->getCollection();
        $hours = Mage::helper("pixremember")->getHours();

        $payments->getSelect()
            ->where('amount_paid IS NULL')
            ->where('DATE_ADD(created_at, INTERVAL -'.$hours.' HOUR) < NOW()')
            ->where('((additional_data IS NULL) OR (additional_data NOT LIKE \'%pixremember%\'))');

        echo $payments->getSelect()->__toString();

        $payments->load();
        return $payments;
    }

    public function getPixUrl( $payment ) {

        if (strpos($payment->getMethod(), 'openpix_pix') !== false) {
            $additional = $payment->getAdditionalInformation();
            return $additional['openpix_brcode'];
        } else {
            return '';
        }

        
    }

}