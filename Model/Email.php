<?php
class Cammino_Pixremember_Model_Email
{
    public function sendEmail($payments) {
        header("Content-type: text/html; charset=utf-8");
        
        foreach($payments as $payment):
            try {
                $orderId = $payment->getParentId();
                $order = Mage::getModel("sales/order")->load($orderId);
                $pixUrl = Mage::getModel("pixremember/job")->getPixUrl($payment);

                $mailer = $this->prepareEmail($order, $pixUrl);
                $sent = $mailer->send();

                if($sent) {
                    $addata = unserialize($payment->getData("additional_data"));
                    $addata["pixremember"] = true;
                    $payment->setAdditionalData(serialize($addata))->save();
                }

            } catch (Exception $e) {
                return false;
            }
        endforeach;
    }

    public function prepareEmail($order, $pixUrl) {
        $storeId = $order->getStore()->getId();

        $customerName = $order->getCustomerFirstname();
        $customerEmail = $order->getCustomerEmail();

        $subject = Mage::helper("pixremember")->renderEmailSubject($customerName);
        $body = Mage::helper("pixremember")->renderEmailBody($customerName);

        $mailer = Mage::getModel("core/email_template_mailer");
        $emailInfo = Mage::getModel("core/email_info");

        $emailInfo->addTo($customerEmail, $customerName);
        $mailer->addEmailInfo($emailInfo);
        $mailer->setSender(Mage::getStoreConfig( Mage_Sales_Model_Order::XML_PATH_EMAIL_IDENTITY, $storeId));
        $mailer->setStoreId($storeId);
        $mailer->setTemplateId("pixremember_email");
        $mailer->setTemplateParams(array(
            'subject'   => $subject,
            'body'      => nl2br($body),
            'pixUrl' => $pixUrl
        ));

        return $mailer;
    }
}