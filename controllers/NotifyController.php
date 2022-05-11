<?php

class Cammino_Pixremember_NotifyController extends Mage_Core_Controller_Front_Action
{
    /**
    * Function responsible for start notify process
    *
    * @return null
    */
    public function indexAction()
    {
        $model = Mage::getModel("pixremember/job");
        $model->notify();
        die;
    }

}