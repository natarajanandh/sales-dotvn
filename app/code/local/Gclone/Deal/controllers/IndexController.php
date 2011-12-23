<?php

class Gclone_Deal_IndexController extends Mage_Core_Controller_Front_Action {

    public function indexAction() {
		//print_r('xxxxxxxxxxxxxxxxxx');die;
        $cityId = Mage::getModel('deal/deal')->fetchCity();
        $productIds = Mage::getModel('deal/deal')->fetchDeals($cityId);
        Mage::getSingleton('core/session')->setProductIds($productIds);
        Mage::getSingleton('core/session')->setDealModule('1');

        $this->loadLayout();
        $id = Mage::app()->getRequest()->getParam('id');
        if (isset($id)) {
            $productId = $id;
            $model = Mage::getModel('catalog/product'); //getting product model
            $_product = $model->load($productId);
            $this->getLayout()->getBlock('head')->setTitle(htmlspecialchars($_product->getMetaTitle()));
            $this->getLayout()->getBlock('head')->setKeywords(htmlspecialchars($_product->getMetaKeyword()));
            $this->getLayout()->getBlock('head')->setDescription(htmlspecialchars($_product->getMetaDescription()));

        } else {
            $this->getLayout()->getBlock('head')->setTitle("Today's Deal");
        }
        $this->renderLayout();
    }

    public function activeAction() {
        $this->loadLayout();
        $this->getLayout()->getBlock('head')->setTitle($this->__('Active Deals'));
        $this->renderLayout();
    }

    public function recentAction() {
        $this->loadLayout();
        $this->getLayout()->getBlock('head')->setTitle($this->__('Recent Deals'));
        $this->renderLayout();
    }

    public function upcomingAction() {
        $this->loadLayout();
        $this->getLayout()->getBlock('head')->setTitle($this->__('Upcoming Deals'));
        $this->renderLayout();
    }

}