<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Checkout
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * One page checkout success page
 *
 * @category   Mage
 * @package    Mage_Checkout
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Checkout_Block_Onepage_Success extends Mage_Core_Block_Template {
    /**
     * @deprecated after 1.4.0.1
     */
    private $_order;

    /**
     * Retrieve identifier of created order
     *
     * @return string
     * @deprecated after 1.4.0.1
     */
    public function getOrderId() {
        return $this->_getData('order_id');
    }

    /**
     * Check order print availability
     *
     * @return bool
     * @deprecated after 1.4.0.1
     */
    public function canPrint() {
        return $this->_getData('can_view_order');
    }

    /**
     * Get url for order detale print
     *
     * @return string
     * @deprecated after 1.4.0.1
     */
    public function getPrintUrl() {
        return $this->_getData('print_url');
    }

    /**
     * Get url for view order details
     *
     * @return string
     * @deprecated after 1.4.0.1
     */
    public function getViewOrderUrl() {
        return $this->_getData('view_order_id');
    }

    /**
     * See if the order has state, visible on frontend
     *
     * @return bool
     */
    public function isOrderVisible() {
        return (bool)$this->_getData('is_order_visible');
    }

    /**
     * Getter for recurring profile view page
     *
     * @param $profile
     */
    public function getProfileUrl(Varien_Object $profile) {
        return $this->getUrl('sales/recurring_profile/view', array('profile' => $profile->getId()));
    }

    /**
     * Initialize data and prepare it for output
     */
    protected function _beforeToHtml() {
        $this->_prepareLastOrder();
        $this->_prepareLastBillingAgreement();
        $this->_prepareLastRecurringProfiles();
        return parent::_beforeToHtml();
    }

    /**
     * Get last order ID from session, fetch it and check whether it can be viewed, printed etc
     */
    protected function _prepareLastOrder() {
        $orderId = Mage::getSingleton('checkout/session')->getLastOrderId();
        if ($orderId) {
            $order = Mage::getModel('sales/order')->load($orderId);
            if ($order->getId()) {
                $isVisible = !in_array($order->getState(),
                        Mage::getSingleton('sales/order_config')->getInvisibleOnFrontStates());
                $this->addData(array(
                        'is_order_visible' => $isVisible,
                        'view_order_id' => $this->getUrl('sales/order/view/', array('order_id' => $orderId)),
                        'print_url' => $this->getUrl('sales/order/print', array('order_id'=> $orderId)),
                        'can_print_order' => $isVisible,
                        'can_view_order'  => Mage::getSingleton('customer/session')->isLoggedIn() && $isVisible,
                        'order_id'  => $order->getIncrementId(),
                ));
            }
        }
    }

    /**
     * Prepare billing agreement data from an identifier in the session
     */
    protected function _prepareLastBillingAgreement() {
        $agreementId = Mage::getSingleton('checkout/session')->getLastBillingAgreementId();
        $customerId = Mage::getSingleton('customer/session')->getCustomerId();
        if ($agreementId && $customerId) {
            $agreement = Mage::getModel('sales/billing_agreement')->load($agreementId);
            if ($agreement->getId() && $customerId == $agreement->getCustomerId()) {
                $this->addData(array(
                        'agreement_ref_id' => $agreement->getReferenceId(),
                        'agreement_url' => $this->getUrl('sales/billing_agreement/view',
                        array('agreement' => $agreementId)
                        ),
                ));
            }
        }
    }

    /**
     * Prepare recurring payment profiles from the session
     */
    protected function _prepareLastRecurringProfiles() {
        $profileIds = Mage::getSingleton('checkout/session')->getLastRecurringProfileIds();
        if ($profileIds && is_array($profileIds)) {
            $collection = Mage::getModel('sales/recurring_profile')->getCollection()
                    ->addFieldToFilter('profile_id', array('in' => $profileIds))
            ;
            $profiles = array();
            foreach ($collection as $profile) {
                $profiles[] = $profile;
            }
            if ($profiles) {
                $this->setRecurringProfiles($profiles);
                if (Mage::getSingleton('customer/session')->isLoggedIn()) {
                    $this->setCanViewProfiles(true);
                }
            }
        }
    }/* contus */
    public function setGiftOrderId() {
        $sessiongift = Mage::getSingleton('core/session');
        if($sessiongift->getorderemail()) {
            $lastOrderId = Mage::getSingleton('checkout/session')->getLastOrderId();
            $customerId = Mage::getSingleton('customer/session')->getCustomerId();
            $tprefix = (string)Mage::getConfig()->getTablePrefix();
            $write = Mage::getSingleton('core/resource')->getConnection('core_write');
            $giftCouponCheck = $write->fetchRow("select max(gift_message_id) as idgift from ".$tprefix."gift_message");
            $giftid = '';
            if(isset($giftCouponCheck['idgift'])) {
                $giftid = $giftCouponCheck['idgift'];
            }
            $write->query("UPDATE ".$tprefix."gift_message set order_id ='".$lastOrderId."'where order_id  = '0' and customer_id = '".$customerId."' and gift_message_id = '".$giftid."'");
            $sessiongift->setorderemail('');
            $sessiongift->setorderfrom('');
            $sessiongift->setorderto('');
            $sessiongift->setordermessage('');
        }
    }

    /* Function to generate the facebook dialog feed link
     * Share the product you have purchased right now in your facebook profile
     *
     * Developer @ Sathish kumar
     * Updated @ 04.01.2011
    */
    public function getFacebookShare() {

        $orderid = $this->_getData('order_id');
        $resource = Mage::getSingleton('core/resource');
        $read = $resource->getConnection('catalog_read');
        $orderTable = $resource->getTableName('sales/order');
        $orderItemTable = $resource->getTableName('sales/order_item');

        $select = $read->select()
                        ->from(array('cp' => $orderTable), array('totalcount' => 'sum(cp.total_qty_ordered)', 'orderid' => 'pei.order_id', 'firstname' => 'cp.customer_firstname', 'lastname' => 'cp.customer_lastname'))
                        ->join(array('pei' => $orderItemTable), 'pei.order_id=cp.entity_id', array('productid' => 'pei.product_id'))
                        ->where('cp.increment_id in (?)', $orderid);
        $orders_list = $read->fetchAll($select);

        $quantity[0] = floor($orders_list[0]['totalcount']);
        $orderid = $orders_list[0]['orderid'];
        $customer_name = $orders_list[0]['firstname'] . ' ' . $orders_list[0]['lastname'];        
        $giftemail = $resource->getConnection('core_write');
        $tprefix = (string) Mage::getConfig()->getTablePrefix();
        $giftCouponCheck = $giftemail->fetchRow("select * from " . $tprefix . "ordercustomer where order_id ='$orderid'");

        $appId = Mage::getStoreConfig('customer/facebook/api_id');
        $baseUrl = Mage::getBaseUrl();
        //$logoImage = $this->getSkinUrl('images/logo.png');
        $productName = urlencode($giftCouponCheck['product_name']);
        $storeName = urlencode(Mage::getStoreConfig('general/store_information/name'));
        $model = Mage::getModel('catalog/product'); //getting product model
        $productId = $orders_list[0]['productid'];
        $_product = $model->load($productId);
        $_description = urlencode(strip_tags($_product->getdescription()));
        $logoImage=$this->helper('catalog/image')->init($_product, 'image');
        //prepare link for facebook dialog feed
        $url = 'http://www.facebook.com/dialog/feed?app_id=' . $appId . '&link=' . $baseUrl . '&description=' . $_description  . '&picture=' . $logoImage . '&name=' . $productName . '&message=This deal is Great. Check out!!!&redirect_uri=' . $baseUrl;
        return $url;
    }

}
