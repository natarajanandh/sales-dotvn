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
 * Newsletter subscribe controller
 *
 * @category    Mage
 * @package     Mage_Newsletter
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Newsletter_SubscriberController extends Mage_Core_Controller_Front_Action {

    /**
     * New subscription action
     */
    public function newAction() {
        if ($this->getRequest()->isPost() && $this->getRequest()->getPost('email')) {
            $session = Mage::getSingleton('core/session');
            $customerSession = Mage::getSingleton('customer/session');
            $email = (string) $this->getRequest()->getPost('email');
            $city = (string) $this->getRequest()->getPost('city');
            
            $flag = 0;
            try {
                if (!Zend_Validate::is($email, 'EmailAddress')) {
                    Mage::throwException($this->__('Please enter a valid email address.'));
                }

                if (Mage::getStoreConfig(Mage_Newsletter_Model_Subscriber::XML_PATH_ALLOW_GUEST_SUBSCRIBE_FLAG) != 1 &&
                        !$customerSession->isLoggedIn()) {
                    Mage::throwException($this->__('Sorry, but administrator denied subscription for guests. Please <a href="%s">register</a>.', Mage::getUrl('customer/account/create/')));
                }

                $ownerId = Mage::getModel('customer/customer')
                                ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                                ->loadByEmail($email)
                                ->getId();
                if ($ownerId !== null && $ownerId != $customerSession->getId()) {
                    Mage::throwException($this->__('Sorry, but you cannot subscribe for the email address assigned to another user.'));
                }

                /*
                 * guest cannot subscribe second time and the customer can subscribe many times and can edit the subscription from dashboard.
                 * updated : sathish kmar
                 * on : 03.12.2010
                 */
                
                if (!$customerSession->isLoggedIn()) {
                    $subscriptionData = Mage_Customer_Block_Newsletter::getCustomerSubscription();
                    foreach ($subscriptionData as $item) {

                        if ($item['subscriber_email'] == $email) {
                            $session->addError($this->__('You are been already subscribed to') . ' ' . $item['subscriber_city'] . '. ' . $this->__('Sign-in to edit your subscription.'));
                            $flag = 1;
                        }
                    }
                }
                /*
                 * Subscription of newsletter.
                 */
                //Validating the city
                if ($city == '') {
                    $session->addError($this->__('Please enter a valid city.'));
                    $this->_redirectUrl(Mage::getBaseUrl());
                }
                else {
                    if ($flag != 1) {
                        $status = Mage::getModel('newsletter/subscriber')->subscribe($email, $city);

                        if ($status == Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE) {
                            $session->addSuccess($this->__('Confirmation request has been sent.'));
                        } else {
                            $session->addSuccess($this->__($this->__('You are been subscribed to') . ' ' . $this->__($city) . '. ' . $this->__('Please check your email for confirmation.')));
                        }
                    }
                }
//                $status = Mage::getModel('newsletter/subscriber')->subscribe($email, $city);
//                if ($status == Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE) {
//                    $session->addSuccess($this->__('Confirmation request has been sent.'));
//                } else {
//                    $session->addSuccess($this->__('Thank you for your subscription.'));
//                }
            } catch (Mage_Core_Exception $e) {
                $session->addException($e, $this->__('There was a problem with the subscription: %s', $e->getMessage()));
            } catch (Exception $e) {
                $session->addException($e, $this->__('There was a problem with the subscription.'));
            }
        }

        //subscribed city will be loaded in the site.
        $final = Mage::getSingleton('core/session')->getTotalCities();
        foreach ($final as $key => $scity) {
            if ($scity == $city) {
                Mage::getSingleton('core/session')->setCity($key);
            }
        }
        //newsletter subscription as default homepage - starts
        $defaultHome = Mage::getStoreConfig('newsletter/homepage/as_default_homepage');
        $isMobile = Mage::getSingleton('core/session')->getMobile();
        if ($defaultHome == 1 || isset($isMobile)) {
            $task = Mage::app()->getRequest()->getPost('task');
            if ($task == 'confirmSubscribe') {
                Mage::getSingleton('core/session')->setConfirmSubscribe('confirmSubscribe');
                $this->_redirectUrl(Mage::getBaseUrl());
            } else {
                $this->_redirectReferer();
            }
        } else {
            $this->_redirectReferer();
        }
        //newsletter subscription as default homepage - ends
    }

    /**
     * Subscription confirm action
     */
    public function confirmAction() {
        $id = (int) $this->getRequest()->getParam('id');
        $code = (string) $this->getRequest()->getParam('code');

        if ($id && $code) {
            $subscriber = Mage::getModel('newsletter/subscriber')->load($id);
            $session = Mage::getSingleton('core/session');

            if ($subscriber->getId() && $subscriber->getCode()) {
                if ($subscriber->confirm($code)) {
                    $session->addSuccess($this->__('Your subscription has been confirmed.'));
                } else {
                    $session->addError($this->__('Invalid subscription confirmation code.'));
                }
            } else {
                $session->addError($this->__('Invalid subscription ID.'));
            }
        }

        $this->_redirectUrl(Mage::getBaseUrl());
    }

    /**
     * Unsubscribe newsletter
     */
    public function unsubscribeAction() {
        $id = (int) $this->getRequest()->getParam('id');
        $code = (string) $this->getRequest()->getParam('code');

        if ($id && $code) {
            $session = Mage::getSingleton('core/session');
            try {
                Mage::getModel('newsletter/subscriber')->load($id)
                        ->setCheckCode($code)
                        ->unsubscribe();
                $session->addSuccess($this->__('You have been unsubscribed.'));
            } catch (Mage_Core_Exception $e) {
                $session->addException($e, $e->getMessage());
            } catch (Exception $e) {
                $session->addException($e, $this->__('There was a problem with the un-subscription.'));
            }
        }
        $this->_redirectReferer();
    }

}
