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
 * @package     Mage_Newsletter
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Customers newsletter subscription controller
 *
 * @category   Mage
 * @package    Mage_Newsletter
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Newsletter_ManageController extends Mage_Core_Controller_Front_Action
{
    /**
     * Action predispatch
     *
     * Check customer authentication for some actions
     */
    public function preDispatch()
    {
        parent::preDispatch();
        if (!Mage::getSingleton('customer/session')->authenticate($this)) {
            $this->setFlag('', 'no-dispatch', true);
        }
    }

    public function indexAction()
    {
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('catalog/session');

        if ($block = $this->getLayout()->getBlock('customer_newsletter')) {
            $block->setRefererUrl($this->_getRefererUrl());
        }
        $this->getLayout()->getBlock('head')->setTitle($this->__('Newsletter Subscription'));
        $this->renderLayout();
    }

//    public function saveAction()
//    {
//        if (!$this->_validateFormKey()) {
//            return $this->_redirect('customer/account/');
//        }
//        try {
//            Mage::getSingleton('customer/session')->getCustomer()
//            ->setStoreId(Mage::app()->getStore()->getId())
//            ->setIsSubscribed((boolean)$this->getRequest()->getParam('is_subscribed', false))
//            ->save();
//            if ((boolean)$this->getRequest()->getParam('is_subscribed', false)) {
//                Mage::getSingleton('customer/session')->addSuccess($this->__('The subscription has been saved.'));
//            } else {
//                Mage::getSingleton('customer/session')->addSuccess($this->__('The subscription has been removed.'));
//            }
//        }
//        catch (Exception $e) {
//            Mage::getSingleton('customer/session')->addError($this->__('An error occurred while saving your subscription.'));
//        }
//        $this->_redirect('customer/account/');
//    }


     /*
     * newsletter subscription save action, in edit newsletter page.
     * update by : sathish kumar
     * on : 03.12.2010
     */

    public function saveAction()
    {
        if (!$this->_validateFormKey()) {
            return $this->_redirect('customer/account/');
        }
        try {

            $city= $this->getRequest()->getParam('city');
            $email = $this->getRequest()->getParam('email');
            if($city != '0'){
            $status=Mage::getModel('newsletter/subscriber')->subscribe($email,$city);
            if ($status == Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE) {
                    Mage::getSingleton('customer/session')->addSuccess($this->__('Confirmation request has been sent.'));
                }
                else {
                    Mage::getSingleton('customer/session')->addSuccess($this->__('Thank you for your subscription.'));
                }
            }
            else{
               Mage::getSingleton('customer/session')->addError($this->__('Save operation cannot be performed, because no change has been made to save.'));
            }
        }
        catch (Exception $e) {
            Mage::getSingleton('customer/session')->addError($this->__('An error occurred while saving your subscription.'));
        }
        $this->_redirect('customer/account/');
    }
}
