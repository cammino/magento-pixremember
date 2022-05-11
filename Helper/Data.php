<?php
class Cammino_Pixremember_Helper_Data extends Mage_Core_Helper_Abstract
{

    public function moduleIsActive() {
        return (bool) Mage::getStoreConfig('pixremember/general/active');
    }
    
    public function notifyByEmail() {
        return (bool) Mage::getStoreConfig('pixremember/email/notify');
    }
    
    public function notifyByWhatsapp() {
        return (bool) Mage::getStoreConfig('pixremember/whatsapp/notify');
    }
    
    public function notifyBySMS() {
        return (bool) Mage::getStoreConfig('pixremember/sms/notify');
    }

    public function logIsActive() {
        return (bool) Mage::getStoreConfig('pixremember/general/active_log');
    }

    public function log($message) {
        Mage::log($message, null, "pixremember.log");
    }

    public function getHours() {
        $hours = intval(Mage::getStoreConfig('pixremember/general/hours'));
        return $hours < 1 ? 24 : $hours;
    }

    public function getTwilioAccountSid() {
        $sid = Mage::getStoreConfig('pixremember/general/twilio_account_sid');
        return strlen($sid) > 14 ? $sid : false;
    }
    
    public function getTwilioAuthToken() {
        $token = Mage::getStoreConfig('pixremember/general/twilio_auth_token');
        return strlen($token) > 14 ? $token : false;
    }
    
    public function getTwilioWhatsappNumber() {
        $whatsapp = Mage::getStoreConfig('pixremember/whatsapp/twilio_number');
        return strlen($whatsapp) > 8 ? $whatsapp : false;
    }
    
    public function getTwilioSMSNumber() {
        $sms = Mage::getStoreConfig('pixremember/sms/twilio_number');
        return strlen($sms) > 8 ? $sms : false;
    }

    public function getCustomerCellphone($customer) {
        $cellphone = $customer->getPrimaryBillingAddress()->getFax();
        if (strlen($cellphone) < 8) {
            $cellphone = $customer->getPrimaryBillingAddress()->getTelephone();
            if (strlen($cellphone) < 8) {
                $cellphone = false;
            }
        }
        return $cellphone;
    }

    public function renderEmailSubject($customerName) {
        $subject = Mage::getStoreConfig('pixremember/email/subject');
        $subject = $this->renderStoreNameVar($subject);
        $subject = $this->renderCustomerNameVar($subject, $customerName);
        return $subject;
    }
    
    public function renderEmailBody($customerName) {
        $body = Mage::getStoreConfig('pixremember/email/body');
        $body = $this->renderStoreNameVar($body);
        $body = $this->renderCustomerNameVar($body, $customerName);
        return $body;
    }

    public function renderWhatsappBody($customerName, $pixUrl) {
        $body = Mage::getStoreConfig('pixremember/whatsapp/body');
        $body = $this->renderStoreNameVar($body);
        $body = $this->renderCustomerNameVar($body, $customerName);
        $body = $body . "\n\n $pixUrl";
        return $body;
    }

    public function renderSMSBody($customerName, $pixUrl) {
        $body = Mage::getStoreConfig('pixremember/sms/body');
        $body = $this->renderStoreNameVar($body);
        $body = $this->renderCustomerNameVar($body, $customerName);
        $body = $body . "\n\n $pixUrl";
        return $body;
    }

    public function renderStoreNameVar($string) {
        if (strpos($string, "{{nome_da_loja}}") !== false) {
            $storeName = Mage::getStoreConfig('general/store_information/name');
            return str_replace("{{nome_da_loja}}", $storeName ,$string);
        } return $string;
    }
    
    public function renderCustomerNameVar($string, $customerName) {
        if (strpos($string, "{{nome_do_cliente}}") !== false) {
            return str_replace("{{nome_do_cliente}}", $customerName ,$string);
        } return $string;
    }

    public function cellphoneToTwillioFormat($cellphone) {
        $cellphone = str_replace("(","",$cellphone);
        $cellphone = str_replace(")","",$cellphone);
        $cellphone = str_replace(" ","",$cellphone);
        $cellphone = str_replace("-","",$cellphone);
        $cellphone = str_replace(".","",$cellphone);
        return "+55" . $cellphone;
    }

}
