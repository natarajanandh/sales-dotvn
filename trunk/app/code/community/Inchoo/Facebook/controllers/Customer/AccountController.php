<?php

/**
 * Facebook Customer account controller
 *
 * @category   Inchoo
 * @package    Inchoo_Facebook
 * @author     Ivan Weiler, Inchoo <web@inchoo.net>
 * @copyright  Copyright (c) 2010 Inchoo d.o.o. (http://inchoo.net)
 * @license    http://opensource.org/licenses/gpl-license.php  GNU General Public License (GPL)
 */
/*
 * Modified @ Contus : 16.05.2011
 */
class Inchoo_Facebook_Customer_AccountController extends Mage_Core_Controller_Front_Action {

    public function connectAction() {
    
        $this->_getSession()->checkConnection();

        if (!$this->_getSession()->isConnected()) {
            $this->_getCustomerSession()->addError($this->__('Facebook Connection failed.'));
            $this->_redirect('customer/account'); //logged in ok?
            return;
        }

        //login or connect

        $customer = Mage::getModel('customer/customer');

        $collection = $customer->getCollection()
                        ->addAttributeToFilter('facebook_uid', (string) $this->_getSession()->getUid())
                        ->setPageSize(1);

        if ($customer->getSharingConfig()->isWebsiteScope()) {
            $collection->addAttributeToFilter('website_id', Mage::app()->getWebsite()->getId());
        }

        if ($this->_getCustomerSession()->isLoggedIn()) {
            $collection->addFieldToFilter('entity_id', array('neq' => $this->_getCustomerSession()->getCustomerId()));
        }

        $uidExist = (bool) $collection->count();

        if ($this->_getCustomerSession()->isLoggedIn() && $uidExist) {
            $existingCustomer = $collection->getFirstItem();
            $existingCustomer->setFacebookUid('');
            $existingCustomer->getResource()->saveAttribute($existingCustomer, 'facebook_uid');
        }

        if ($this->_getCustomerSession()->isLoggedIn()) {
            $currentCustomer = $this->_getCustomerSession()->getCustomer();
            $currentCustomer->setFacebookUid($this->_getSession()->getUid());
            $currentCustomer->getResource()->saveAttribute($currentCustomer, 'facebook_uid');

            $this->_getCustomerSession()->addSuccess(
                    $this->__('Your Facebook account has been successfully connected. Now you can fast login using Facebook Connect anytime.')
            );
            
            $this->_redirect('customer/account');
            return;
        }

        if ($uidExist) {
            $this->_getCustomerSession()->setCustomerAsLoggedIn($collection->getFirstItem());
            $this->_loginPostRedirect();
            //$this->_redirectReferer();
            return;
        }


        //let's go with e-mail

        try {
            $standardInfo = $this->_getSession()->getClient()->users->getInfo(array(
                        'uids' => $this->_getSession()->getUid(),
                        'fields' => 'first_name, last_name, contact_email, sex, birthday_date'
                    ));
        } catch (Mage_Core_Exception $e) {
            $this->_getCustomerSession()->addError($this->__('Facebook Connection failed. Service temporarily unavailable.'));
            $this->_redirect('customer/account/login');
            return;
        }

        $standardInfo = current($standardInfo);

        if (!$standardInfo['contact_email']) {
            try {
                //needs to be done to show perms dialog on next connect
                $this->_getSession()->getClient()->auth->revokeExtendedPermission(array(
                    'perm' => 'email'
                ));
            } catch (Mage_Core_Exception $e) {
                
            }

            $this->_getCustomerSession()->addError($this->__('Facebook Connection failed. Facebook contact email address is required.'));
            $this->_redirect('customer/account/login');
            return;
        }

        $customer
                ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                ->loadByEmail($standardInfo['contact_email']);

        if ($customer->getId()) {
            $customer->setFacebookUid($this->_getSession()->getUid());
            Mage::getResourceModel('customer/customer')->saveAttribute($customer, 'facebook_uid');
            $this->_getCustomerSession()->setCustomerAsLoggedIn($customer);
            $this->_getCustomerSession()->addSuccess(
                    $this->__('Your Facebook account has been successfully connected. Now you can fast login using Facebook Connect anytime.')
            );
             $this->_loginPostRedirect();
            //$this->_redirect('customer/account');
            return;
        }

        //registration needed

        $randomPassword = $customer->generatePassword(8);

        $customer->setId(null)
                ->setSkipConfirmationIfEmail($standardInfo['contact_email'])
                ->setFirstname($standardInfo['first_name'])
                ->setLastname($standardInfo['last_name'])
                ->setEmail($standardInfo['contact_email'])
                ->setPassword($randomPassword)
                ->setConfirmation($randomPassword)
                ->setFacebookUid($this->_getSession()->getUid());

        //FB: Show my sex in my profile.
        if ($standardInfo['sex'] && $gender = Mage::getResourceSingleton('customer/customer')->getAttribute('gender')) {
            $genderOptions = $gender->getSource()->getAllOptions();
            foreach ($genderOptions as $option) {
                if ($option['label'] == ucfirst($standardInfo['sex'])) {
                    $customer->setGender($option['value']);
                    break;
                }
            }
        }

        //FB: Show my full birthday in my profile.
        if (count(explode('/', $standardInfo['birthday_date'])) == 3) {

            $dob = $standardInfo['birthday_date'];

            if (method_exists($this, '_filterDates')) {
                $filtered = $this->_filterDates(array('dob' => $dob), array('dob'));
                $dob = current($filtered);
            }

            $customer->setDob($dob);
        }

        //$customer->getGroupId(); // needed in 1.3.x.x ?
        //for future versions and easy mods :)
        if ($this->getRequest()->getParam('is_subscribed', false)) {
            $customer->setIsSubscribed(1);
        }

        //registration will fail if tax required, also if dob, gender aren't allowed in profile
        $errors = array();
        $validationCustomer = $customer->validate();
        if (is_array($validationCustomer)) {
            $errors = array_merge($validationCustomer, $errors);
        }
        $validationResult = count($errors) == 0;

        if (true === $validationResult) {
            $customer->save();

            $this->_getCustomerSession()->addSuccess(
                    $this->__('Thank you for registering with %s', Mage::app()->getStore()->getFrontendName()) .
                    '. ' .
                    $this->__('You will recieve welcome email with registration info in a moment.')
            );
            //if not change password or click here forget password

            $customer->sendNewAccountEmail();

            $this->_getCustomerSession()->setCustomerAsLoggedIn($customer);
            $this->_loginPostRedirect();
            //$this->_redirect('customer/account');
            return;

            //else set form data and redirect to registration
        } else {
            $this->_getCustomerSession()->setCustomerFormData($customer->getData());
            $this->_getCustomerSession()->addError($this->__('Facebook profile can\'t provide all required info, please register and then connect with Facebook for fast login.'));
            if (is_array($errors)) {
                foreach ($errors as $errorMessage) {
                    $this->_getCustomerSession()->addError($errorMessage);
                }
            }

            $this->_redirect('customer/account/create');
        }
        
    }

    private function _getCustomerSession() {
        return Mage::getSingleton('customer/session');
    }

    private function _getSession() {
        return Mage::getSingleton('facebook/session');
    }

     protected function _loginPostRedirect()
    {
        $session = $this->_getCustomerSession();

        if (!$session->getBeforeAuthUrl() || $session->getBeforeAuthUrl() == Mage::getBaseUrl() ) {

            // Set default URL to redirect customer to
            $session->setBeforeAuthUrl(Mage::helper('customer')->getAccountUrl());

            // Redirect customer to the last page visited after logging in
            if ($session->isLoggedIn())
            {
                if (!Mage::getStoreConfigFlag('customer/startup/redirect_dashboard')) {
                    if ($referer = $this->getRequest()->getParam(Mage_Customer_Helper_Data::REFERER_QUERY_PARAM_NAME)) {
                        $referer = Mage::helper('core')->urlDecode($referer);
                        if ($this->_isUrlInternal($referer)) {
                            $session->setBeforeAuthUrl($referer);
                        }
                    }
                }
                else if ($session->getAfterAuthUrl()) {
                    $session->setBeforeAuthUrl($session->getAfterAuthUrl(true));
                }
            } else {
                $session->setBeforeAuthUrl(Mage::helper('customer')->getLoginUrl());
            }
        } else if ($session->getBeforeAuthUrl() == Mage::helper('customer')->getLogoutUrl()) {
            $session->setBeforeAuthUrl(Mage::helper('customer')->getDashboardUrl());
        }
        else {
            if (!$session->getAfterAuthUrl()) {
                $session->setAfterAuthUrl($session->getBeforeAuthUrl());
            }
            if ($session->isLoggedIn()) {
                $session->setBeforeAuthUrl($session->getAfterAuthUrl(true));
            }
        }
       
        $this->_redirectUrl($session->getBeforeAuthUrl(true));
    }

}
