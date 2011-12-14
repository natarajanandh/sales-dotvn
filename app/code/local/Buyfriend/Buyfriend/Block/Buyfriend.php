<?php
class Buyfriend_Buyfriend_Block_Buyfriend extends Mage_Core_Block_Template {
    public function _prepareLayout() {
        return parent::_prepareLayout();
    }

    public function getBuyfriend() {
        $product_email="";
        $_productCollection = Mage::getResourceModel('catalog/product_collection');
        //  $_productCollection=$this1->getLoadedProductCollection();
        //print_r($collection);exit();
        if(!$_productCollection->count()):
            echo "No Deals";
        else:
          //  $cProd = Mage::getModel('catalog/product');

            foreach ($_productCollection as $_product):

                $model = Mage::getModel('catalog/product');
                $_product = $model->load( $_product->getId());
                $inif = '0';
                date_default_timezone_set("America/New_York");
                $start_date1 = strtotime(date("Y-m-d  H:i:s", time()));
                $todayDate  = $_product->getResource()->formatDate($_product->getspecial_to_date(),false);
                $expirydate = date('Y-m-d',strtotime($todayDate));//
                $end_date1 = $expirydate." ".$_product->gettime();
                $end_date1 =  strtotime($end_date1);
                $compare_date = ($end_date1 - $start_date1)/(60*60*24);

                if($compare_date >= '0') {
                    $id = $model->getIdBySku($_product->getId());
                    $product_email="";
                    $product_email .= Mage::getUrl('checkout/cart/add', array('product' => $_product->getId()));
                    return $product_email;
                }
            endforeach;
        endif;


    }
}