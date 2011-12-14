<?php

class Gclone_Emailnewsletter_Model_Emailnewsletter extends Mage_Core_Model_Abstract {
    const XML_PATH_EMAIL_RECIPIENT = 'contacts/email/recipient_email';
    const XML_PATH_EMAIL_SENDER = 'emailnewsletter/email/sender_email_identity';
    const XML_PATH_EMAIL_TEMPLATE = 'emailnewsletter/email/email_template';

    public function _construct() {
        parent::_construct();
        $this->_init('emailnewsletter/emailnewsletter');
    }

    /* Contus
     * Deciding on the current product
     */

    public function getCurrentProducts() {
        $timezone = explode(" ", Mage::helper('core')->formatDate(null, 'long', true));
        $currentTime = Mage_Core_Model_Locale::date(null, null, "en_US", true);
        $resource1 = Mage::getSingleton("core/resource");
        $currentdeal = $resource1->getConnection('core_write');
        $tprefix = (string) Mage::getConfig()->getTablePrefix();
        $_productCollection = Mage::getModel('catalog/product')->getCollection();
        //filter for products status is equal (eq) to enable, and deal status equal (eq) to current
        $_productCollection->addFieldToFilter(array(
            array('attribute' => 'Status', 'eq' => '1'),
        ));
        $model = Mage::getModel('catalog/product');
        $cityname = '';
        foreach ($_productCollection as $_product) {
            $_product = $model->load($_product->getId());
            $current_product = $_product->getId();

            $city = $_product->getCity();
            $ProductToDate = $_product->getResource()->formatDate($_product->getspecial_to_date(), false);
            $ProductFromDate = $_product->getResource()->formatDate($_product->getspecial_from_date(), false);
            $Fromtime = strtotime($ProductFromDate);
            if ($Fromtime < strtotime($currentTime)) {
                if (strtotime($ProductToDate . ' ' . $_product->getTime()) > strtotime($currentTime)) {
                    //Query to Checking whether the newsletter had already sent
                    $resultproductid = $currentdeal->fetchRow("select product_id from " . $tprefix . "product_newsletter where product_id = '$current_product'");
                    if (!isset($resultproductid['product_id'])) {
                        // Call Newsletter Function to send Newsletter
                        self::sendProductNewsletter($current_product, $city);
                    }
                }
            }
        }
    }

    /* Contus
     * Sending Newsletter Function
     */

    private function sendProductNewsletter($current_product, $city) {
        $resource1 = Mage::getSingleton('core/resource');
        $currentdeal = $resource1->getConnection('core_write');
        $tprefix = (string) Mage::getConfig()->getTablePrefix();
        $urlCollection = Mage::getModel('deal/deal')->cityCollections();
        $city = explode(',', $city);

        //getting product model
        $model = Mage::getModel('catalog/product');

        //currency symbol
        $currencySymbol = Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol();

        /* Gathering Newsletter detail */
        $productdetail = $model->load($current_product);
        $currentproductname = $productdetail->getName();
        $currentproductimage = Mage::helper('catalog/image')->init($productdetail, 'image');
        $discount_price = $productdetail->getPrice() - $productdetail->getSpecialPrice();
        $product_saveprice = $discount_price;
        $discount = ($discount_price * 100) / $productdetail->getPrice();
        $discount = round($discount);
        $currentdiscount = $discount;
        $Companywebsite = $productdetail->getCustomer_website();
        $Companyname = $productdetail->getCustomersite();
        $product_worth = $productdetail->getPrice();
        $product_price = $productdetail->getSpecialPrice();
        $product_description = $productdetail->getDescription();
        $deal_date = date('M d Y', strtotime(now()));
        $productUrl= Mage::getModel('deal/deal')->getProductUrl($current_product);
        $resource = Mage::getSingleton('core/resource');
        $newsletterEmail = $resource->getConnection('core_write');
        $tprefix = (string) Mage::getConfig()->getTablePrefix();
        $newsletterTable = $resource->getTableName('newsletter/subscriber');

        foreach ($urlCollection as $key => $item) {
            foreach ($city as $c) {
                if ($key == $c) {
                    $cityToCheck = $item;

                    //Fetching the customer how had subscribed for that particular City
                    $email_list = $newsletterEmail->fetchAll("Select * from " . $tprefix . "newsletter_subscriber
                                                  where subscriber_city = '" . $cityToCheck . "' AND subscriber_status =1");

                    //Customer Email address In an Array
                    $i = 0;

                    $tocustomer = array();
                    if (count($email_list) > 0) {
                        foreach ($email_list as $rows) {
                            $tocustomer = $rows['subscriber_email'];

                            $subscribeId = $rows['subscriber_id'];
                            $subscribeCode = $rows['subscriber_confirm_code'];
                            //Gathering details for the Newsletter Sending(Template)
                            $postObject = new Varien_Object();
                            $postObject->setData(array('deal_date' => $deal_date, 'product_description' => $product_description, 'product_saveprice' => $product_saveprice, 'productname' => $currentproductname, 'product_price' => floor($product_price), 'productimage' => $currentproductimage, 'discount' => $discount, 'companywebsite' => $Companywebsite, 'product_worth' => floor($product_worth), 'product_city' => $cityToCheck, 'subscribe_id' => $subscribeId, 'subscribe_code' => $subscribeCode, 'company_address' => $Companyname, 'currency_symbol' => $currencySymbol, 'product_url' => $productUrl));
                            $mailTemplate = Mage::getModel('core/email_template');
                            $mailTemplate->setSenderName(Mage::getStoreConfig('design/head/default_title'));
                            $mailTemplate->setSenderEmail(Mage::getStoreConfig(self::XML_PATH_EMAIL_RECIPIENT));
                            $mailTemplate->setTemplateSubject('Newsletter Subscription From ' . Mage::getStoreConfig('design/head/default_title'));
                            $mailTemplate->addBcc($tocustomer);
                            /* @var $mailTemplate Mage_Core_Model_Email_Template */
                            $mailTemplate->setDesignConfig(array('area' => 'frontend'))
                                    ->sendTransactional(
                                            Mage::getStoreConfig(self::XML_PATH_EMAIL_TEMPLATE),
                                            Mage::getStoreConfig(self::XML_PATH_EMAIL_SENDER),
                                            $tocustomer,
                                            Mage::getStoreConfig('design/head/default_title'),
                                            array('deallist' => $postObject)
                            );
                            $i++;
                        }
                    }
                }
            }
        }
        if ($mailTemplate->getSentSuccess()) {
            //Insterting product ID into the table after sending hre newsletter
            $resultproductid = $currentdeal->query("INSERT INTO " . $tprefix . "product_newsletter (product_id,email_sent) VALUES ('$current_product','1')");
        }
    }

}