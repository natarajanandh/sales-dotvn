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
 * Shopping cart controller
 */
class Mage_Checkout_CartController extends Mage_Core_Controller_Front_Action {
    /**
     * Action list where need check enabled cookie
     *
     * @var array
     */
    protected $_cookieCheckActions = array('add');

    /**
     * Retrieve shopping cart model object
     *
     * @return Mage_Checkout_Model_Cart
     */
    protected function _getCart() {
        return Mage::getSingleton('checkout/cart');
    }

    /**
     * Get checkout session model instance
     *
     * @return Mage_Checkout_Model_Session
     */
    protected function _getSession() {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Get current active quote instance
     *
     * @return Mage_Sales_Model_Quote
     */
    protected function _getQuote() {
        return $this->_getCart()->getQuote();
    }

    /**
     * Set back redirect url to response
     *
     * @return Mage_Checkout_CartController
     */
    protected function _goBack() {
        if ($returnUrl = $this->getRequest()->getParam('return_url')) {
            // clear layout messages in case of external url redirect
            if ($this->_isUrlInternal($returnUrl)) {
                $this->_getSession()->getMessages(true);
            }
            $this->getResponse()->setRedirect($returnUrl);
        } elseif (!Mage::getStoreConfig('checkout/cart/redirect_to_cart')
                && !$this->getRequest()->getParam('in_cart')
                && $backUrl = $this->_getRefererUrl()) {

            $this->getResponse()->setRedirect($backUrl);
        } else {
            if (($this->getRequest()->getActionName() == 'add') && !$this->getRequest()->getParam('in_cart')) {
                $this->_getSession()->setContinueShoppingUrl($this->_getRefererUrl());
            }
            $this->_redirect('checkout/cart');
        }
        return $this;
    }

    /**
     * Initialize product instance from request data
     *
     * @return Mage_Catalog_Model_Product || false
     */
    protected function _initProduct() {
        $productId = (int) $this->getRequest()->getParam('product');
        if ($productId) {
            $product = Mage::getModel('catalog/product')
                    ->setStoreId(Mage::app()->getStore()->getId())
                    ->load($productId);
            if ($product->getId()) {
                return $product;
            }
        }
        return false;
    }

    /**
     * Shopping cart display action
     */
    public function indexAction() {
        $cart = $this->_getCart();
        if ($cart->getQuote()->getItemsCount()) {
            $cart->init();
            $cart->save();

            if (!$this->_getQuote()->validateMinimumAmount()) {
                $warning = Mage::getStoreConfig('sales/minimum_order/description');
                $cart->getCheckoutSession()->addNotice($warning);
            }
        }

        foreach ($cart->getQuote()->getMessages() as $message) {
            if ($message) {
                $cart->getCheckoutSession()->addMessage($message);
            }
        }

        /**
         * if customer enteres shopping cart we should mark quote
         * as modified bc he can has checkout page in another window.
         */
        $this->_getSession()->setCartWasUpdated(true);

        Varien_Profiler::start(__METHOD__ . 'cart_display');
        $this
                ->loadLayout()
                ->_initLayoutMessages('checkout/session')
                ->_initLayoutMessages('catalog/session')
                ->getLayout()->getBlock('head')->setTitle($this->__('Shopping Cart'));
        $this->renderLayout();
        Varien_Profiler::stop(__METHOD__ . 'cart_display');
    }

    /**
     * Add product to shopping cart action
     */
    public function addAction() {
        $cart   = $this->_getCart();
        $params = $this->getRequest()->getParams();
        /* start
         * coding used for restricting user to add more than one 1 item in the cart.
         * 
         *
         */
         $session = Mage::getSingleton('checkout/session');

        $productId = "";

        foreach ($session->getQuote()->getAllItems() as $item) {

            $productId = $item->getProductId();
        }

        $cartItems = Mage::helper('checkout/cart')->getCart()->getItemsCount();
        if ($params['product'] != $productId) {
            if ($cartItems >= 1) {
                $this->_getSession()->addError($this->__('Maximum one item to add the shopping cart.'));
                $this->_goBack();
                return;
            }
        }
        /* end */
        try {
            if (isset($params['qty'])) {
                $filter = new Zend_Filter_LocalizedToNormalized(
                        array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                $params['qty'] = $filter->filter($params['qty']);
            }

            $product = $this->_initProduct();
            $related = $this->getRequest()->getParam('related_product');

            /**
             * Check product availability
             */
            if (!$product) {
                $this->_goBack();
                return;
            }

            $cart->addProduct($product, $params);
            if (!empty($related)) {
                $cart->addProductsByIds(explode(',', $related));
            }

            $cart->save();

            $this->_getSession()->setCartWasUpdated(true);

            /**
             * @todo remove wishlist observer processAddToCart
             */
            Mage::dispatchEvent('checkout_cart_add_product_complete',
                    array('product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse())
            );

            if (!$this->_getSession()->getNoCartRedirect(true)) {
                if (!$cart->getQuote()->getHasError()) {
                    $message = $this->__('%s was added to your shopping cart.', Mage::helper('core')->htmlEscape($product->getName()));
                    $this->_getSession()->addSuccess($message);
                }
                if(isset($_POST['giftoption']) && $_POST['giftoption'] == "gift" ) {
                    $this->_redirect('checkout/cart/#gift');
                }else {
                    $this->_goBack();
                }
            }
        }
        catch (Mage_Core_Exception $e) {
            if ($this->_getSession()->getUseNotice(true)) {
                $this->_getSession()->addNotice($e->getMessage());
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $this->_getSession()->addError($message);
                }
            }

            $url = $this->_getSession()->getRedirectUrl(true);
            if ($url) {
                $this->getResponse()->setRedirect($url);
            } else {
                $this->_redirectReferer(Mage::helper('checkout/cart')->getCartUrl());
            }
        }
        catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('Cannot add the item to shopping cart.'));
            $this->_goBack();
        }
    }

    public function addgroupAction() {
        $orderItemIds = $this->getRequest()->getParam('order_items', array());
        if (is_array($orderItemIds)) {
            $itemsCollection = Mage::getModel('sales/order_item')
                    ->getCollection()
                    ->addIdFilter($orderItemIds)
                    ->load();
            /* @var $itemsCollection Mage_Sales_Model_Mysql4_Order_Item_Collection */
            $cart = $this->_getCart();
            foreach ($itemsCollection as $item) {
                try {
                    $cart->addOrderItem($item, 1);
                }
                catch (Mage_Core_Exception $e) {
                    if ($this->_getSession()->getUseNotice(true)) {
                        $this->_getSession()->addNotice($e->getMessage());
                    } else {
                        $this->_getSession()->addError($e->getMessage());
                    }
                }
                catch (Exception $e) {
                    $this->_getSession()->addException($e, $this->__('Cannot add the item to shopping cart.'));
                    $this->_goBack();
                }
            }
            $cart->save();
            $this->_getSession()->setCartWasUpdated(true);
        }
        $this->_goBack();
    }

    /**
     * Update shoping cart data action
     */
    public function updatePostAction() {
        try {
            $cartData = $this->getRequest()->getParam('cart');
            if (is_array($cartData)) {
                $filter = new Zend_Filter_LocalizedToNormalized(
                        array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                foreach ($cartData as $index => $data) {
                    if (isset($data['qty'])) {
                        $cartData[$index]['qty'] = $filter->filter($data['qty']);
                    }
                }
                $cart = $this->_getCart();
                if (! $cart->getCustomerSession()->getCustomer()->getId() && $cart->getQuote()->getCustomerId()) {
                    $cart->getQuote()->setCustomerId(null);
                }
                $cart->updateItems($cartData)
                        ->save();
            }
            $this->_getSession()->setCartWasUpdated(true);
        }
        catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('Cannot update shopping cart.'));
        }
        $this->_goBack();
    }

    /**
     * Delete shoping cart item action
     */
    public function deleteAction() {
        $id = (int) $this->getRequest()->getParam('id');
        if ($id) {
            try {
                $this->_getCart()->removeItem($id)
                        ->save();
            } catch (Exception $e) {
                $this->_getSession()->addError($this->__('Cannot remove the item.'));
            }
        }
        $this->_redirectReferer(Mage::getUrl('*/*'));
    }

    /**
     * Initialize shipping information
     */
    public function estimatePostAction() {
        $country    = (string) $this->getRequest()->getParam('country_id');
        $postcode   = (string) $this->getRequest()->getParam('estimate_postcode');
        $city       = (string) $this->getRequest()->getParam('estimate_city');
        $regionId   = (string) $this->getRequest()->getParam('region_id');
        $region     = (string) $this->getRequest()->getParam('region');

        $this->_getQuote()->getShippingAddress()
                ->setCountryId($country)
                ->setCity($city)
                ->setPostcode($postcode)
                ->setRegionId($regionId)
                ->setRegion($region)
                ->setCollectShippingRates(true);
        $this->_getQuote()->save();
        $this->_goBack();
    }

    public function estimateUpdatePostAction() {
        $code = (string) $this->getRequest()->getParam('estimate_method');
        if (!empty($code)) {
            $this->_getQuote()->getShippingAddress()->setShippingMethod($code)/*->collectTotals()*/->save();
        }
        $this->_goBack();
    }

    /**
     * Initialize coupon
     */
    public function couponPostAction() {
        /**
         * No reason continue with empty shopping cart
         */
        if (!$this->_getCart()->getQuote()->getItemsCount()) {
            $this->_goBack();
            return;
        }

        $couponCode = (string) $this->getRequest()->getParam('coupon_code');
        if ($this->getRequest()->getParam('remove') == 1) {
            $couponCode = '';
        }
        $oldCouponCode = $this->_getQuote()->getCouponCode();

        if (!strlen($couponCode) && !strlen($oldCouponCode)) {
            $this->_goBack();
            return;
        }

        try {
            $this->_getQuote()->getShippingAddress()->setCollectShippingRates(true);
            $this->_getQuote()->setCouponCode(strlen($couponCode) ? $couponCode : '')
                    ->collectTotals()
                    ->save();

            if ($couponCode) {
                if ($couponCode == $this->_getQuote()->getCouponCode()) {
                    $this->_getSession()->addSuccess(
                            $this->__('Coupon code "%s" was applied.', Mage::helper('core')->htmlEscape($couponCode))
                    );
                }
                else {
                    $this->_getSession()->addError(
                            $this->__('Coupon code "%s" is not valid.', Mage::helper('core')->htmlEscape($couponCode))
                    );
                }
            } else {
                $this->_getSession()->addSuccess($this->__('Coupon code was canceled.'));
            }

        }
        catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        catch (Exception $e) {
            $this->_getSession()->addError($this->__('Cannot apply the coupon code.'));
        }

        $this->_goBack();
    }

    /* Contus */
    public function giftPostAction() {
        /* Retriving Session values */
        $customerId = Mage::getSingleton('customer/session')->getCustomerId();
        $sessiongift = Mage::getSingleton('core/session');
        /* Table Prefix */
        $tprefix = (string)Mage::getConfig()->getTablePrefix();
        /* Current product Id */
        //$productId=Mage::getSingleton('core/session')->getProductId();
        $productId=Mage::getSingleton('core/session')->getBuyProductId();

        if($customerId !='') {
            /* fetch write database connection that is used in Mage_Core module */
            $write = Mage::getSingleton('core/resource')->getConnection('core_write');
            $read  = Mage::getSingleton('core/resource')->getConnection('read');

            /*Storing the session Values */
            if( $this->getRequest()->getParam('order_gift_from_name') !='') $sessiongift->setorderfrom((string) $this->getRequest()->getParam('order_gift_from_name'));
            if( $this->getRequest()->getParam('order_gift_to_name') !='') $sessiongift->setorderto((string) $this->getRequest()->getParam('order_gift_to_name'));
            if( $this->getRequest()->getParam('order_gift_message') !='') $sessiongift->setordermessage((string) $this->getRequest()->getParam('order_gift_message'));
            if( $this->getRequest()->getParam('order_gift_recipient_email') !='') $sessiongift->setorderemail((string) $this->getRequest()->getParam('order_gift_recipient_email'));

            $getGiftDetail = $read->fetchRow("Select gift_message_id ,email,recipient,order_id  from ".$tprefix."gift_message  where customer_id = '$customerId' AND product_id ='$productId' AND order_id = 0" );

            /* Gift Option Already exist Update else Insert as New Record */
            if(isset($getGiftDetail['email'])) {
                $write->query("UPDATE ".$tprefix."gift_message set sender ='".$sessiongift->getorderfrom()."',recipient='".$sessiongift->getorderto()."',message='".$sessiongift->getordermessage()."',email='".$sessiongift->getorderemail()."' where customer_id = '".$customerId."' AND product_id ='$productId' AND order_id = 0");
                $this->_getSession()->addSuccess($this->__('Gift was Updated successfully.'));
            }
            else {
                $write->query("insert into ".$tprefix."gift_message (customer_id,sender,recipient,message,email,product_id)values ($customerId,'".$sessiongift->getorderfrom()."','".$sessiongift->getorderto()."','".$sessiongift->getordermessage()."','".$sessiongift->getorderemail()."','".$productId."')");
                $this->_getSession()->addSuccess($this->__('Gift was Added successfully.'));
            }
            $this->_goBack();
            /* Redirect */
        }else {
            $this->_getSession()->addError($this->__('Please Login to make use of Gift Option.'));
            $this->_goBack();

        }

    }


    public function giftDeleteAction() {
        $customerId = Mage::getSingleton('customer/session')->getCustomerId();
        $sessiongift = Mage::getSingleton('core/session');
        $tprefix = (string)Mage::getConfig()->getTablePrefix();
        $productId=Mage::getSingleton('core/session')->getProductId();
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        if($customerId !='') {
            $write->query("Delete from ".$tprefix."gift_message  where customer_id = '".$customerId."' AND product_id ='$productId' AND order_id = 0");
            $this->_getSession()->addSuccess($this->__('Gift was Deleted successfully.'));
            $this->_goBack();
            $sessiongift->setorderemail('');
            $sessiongift->setorderfrom('');
            $sessiongift->setorderto('');
            $sessiongift->setordermessage('');

        }

    }

}
