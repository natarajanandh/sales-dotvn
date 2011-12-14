<?php
class Email_Email_Block_Email extends Mage_Core_Block_Template {
    public function _prepareLayout() {
        return parent::_prepareLayout();
    }

    public function getEmail() {


$storeName =Mage::getStoreConfig('general/store_information/name');


        if(isset($_POST['emailafriend'])) {
            $pieces = explode("@", $_POST['emailafriend']);
            $mail = new Zend_Mail();
            $body = "
Hi $pieces[0],

I thought you�d be interested in hearing about a great new site I�m a member of. This is the place for Great Deals(offers)!

Here�s what you can expect from ".$storeName.":

Get Deal at very low cost!

REGULAR Deals� Good ones too!

24/7 Customer Service &amp; Support Team

Fast Payouts

Click The Link ".Mage::helper('core/url')->getCurrentUrl()."

Thanks!"

.$storeName;
            $mail->setBodyText($body);
            $mail->setFrom('admin@groupclone.net', $storeName);
            $mail->addTo($_POST['emailfriend'], $_POST['emailfriend']);
            $mail->setSubject('Refer Your Friend Email');
            try {
                $mail->send();
                return "success";
            }
            catch(Exception $ex) {
                return "failed";
                //Mage::getSingleton('core/session')->addError('Unable to send email. Sample of a custom notification error from ActiveCodeline_SimpleContact.');
            }
        }else {
//
//            $product_email="";
//            $_productCollection = Mage::getResourceModel('catalog/product_collection');
//            //  $_productCollection=$this1->getLoadedProductCollection();
//            //print_r($collection);exit();
//            if(!$_productCollection->count()):
//                echo "No Deals";
//            else:
//                foreach ($_productCollection as $_product):
//                          $model = Mage::getModel('catalog/product'); //getting product model
//                $_product = $model->load( $_product->getId());
//                $inif = '0';
//                date_default_timezone_set("America/New_York");
//                $start_date1 = strtotime(date("Y-m-d  H:i:s", time()));
//                $todayDate  = $_product->getResource()->formatDate($_product->getspecial_to_date(),false);
//                $expirydate = date('Y-m-d',strtotime($todayDate));//
//                $end_date1 = $expirydate." ".$_product->gettime();
//                $end_date1 =  strtotime($end_date1);
//                $compare_date = ($end_date1 - $start_date1)/(60*60*24);
//
//                if($compare_date >= '0') {
//                     $product_email = $_product->getId(); //exit();
//                    $product_email = $this->helper('catalog/product')->getEmailToFriendUrl($_product);}
//
//                endforeach;
//            endif;
//            return $product_email;
        }
    }
}