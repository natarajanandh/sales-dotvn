<?php

class Gclone_Rssfeed_IndexController extends Mage_Core_Controller_Front_Action {

    public function indexAction() {
        $this->loadLayout();
        $this->getLayout()->getBlock('head')->setTitle($this->__('Rss Feed'));
        $this->renderLayout();
    }

}