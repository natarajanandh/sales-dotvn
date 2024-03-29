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
 * @package     Mage_Page
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Html page block
 *
 * @category   Mage
 * @package    Mage_Page
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Page_Block_Html_Header extends Mage_Core_Block_Template {

    public function _construct() {
        //Mage::getSingleton('core/session')->setConfirmSubscribe('');
        $defaultHome = Mage::getStoreConfig('newsletter/homepage/as_default_homepage');
        $isMobile = Mage::getSingleton('core/session')->getMobile();
        if ($defaultHome == 1 || isset($isMobile)) {
            $task = Mage::app()->getRequest()->getParam('task');
            if ($task == 'confirmSubscribe') {
                Mage::getSingleton('core/session')->setConfirmSubscribe('confirmSubscribe');
            }
            if (Mage::getSingleton('core/session')->getConfirmSubscribe() == '') {
                $url = $this->getBaseUrl() . 'subscribepage';
//            Mage::app()->getFrontController()->getResponse()->setRedirect($url);
                Mage::app()->getResponse()->setHeader("Location", $url)->sendHeaders();
            }
        }

        $request = Mage::app()->getFrontController()->getRequest();
        $actionName = $request->getActionName();
        if($actionName == 'noRoute'){
                $url = Mage::getBaseUrl() . 'no-route';
                Mage::app()->getResponse()->setHeader("Location", $url)->sendHeaders();

        }
        $this->setTemplate('page/html/header.phtml');
//       $this->setTemplate('page/html/header.phtml');
    }

    /**
     * Check if current url is url for home page
     *
     * @return true
     */
    public function getIsHomePage() {
        return $this->getUrl('') == $this->getUrl('*/*/*', array('_current' => true, '_use_rewrite' => true));
    }

    public function setLogo($logo_src, $logo_alt) {
        $this->setLogoSrc($logo_src);
        $this->setLogoAlt($logo_alt);
        return $this;
    }

    public function getLogoSrc() {
        if (empty($this->_data['logo_src'])) {
            $this->_data['logo_src'] = Mage::getStoreConfig('design/header/logo_src');
        }
        return $this->getSkinUrl($this->_data['logo_src']);
    }

    public function getLogoAlt() {
        if (empty($this->_data['logo_alt'])) {
            $this->_data['logo_alt'] = Mage::getStoreConfig('design/header/logo_alt');
        }
        return $this->_data['logo_alt'];
    }

    public function getWelcome() {
        if (empty($this->_data['welcome'])) {
            if (Mage::isInstalled() && Mage::getSingleton('customer/session')->isLoggedIn()) {
                $this->_data['welcome'] = $this->__('Welcome, %s!', $this->htmlEscape(Mage::getSingleton('customer/session')->getCustomer()->getName()));
            } else {
                $this->_data['welcome'] = Mage::getStoreConfig('design/header/welcome');
            }
        }

        return $this->_data['welcome'];
    }

}
