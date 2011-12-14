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
 * Checkout observer model
 *
 * @category   Mage
 * @package    Mage_Checkout
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Checkout_Model_Observer
{
    public function unsetAll()
    {
        Mage::getSingleton('checkout/session')->unsetAll();
    }

/* Contus Start Don't add the previous login cart items */

   /* public function loadCustomerQuote()
    {
        try {
            Mage::getSingleton('checkout/session')->loadCustomerQuote();
        }
        catch (Mage_Core_Exception $e) {
            Mage::getSingleton('checkout/session')->addError($e->getMessage());
        }
        catch (Exception $e) {
            Mage::getSingleton('checkout/session')->addException(
                $e,
                Mage::helper('checkout')->__('Load customer quote error')
            );
        }
    }*/

	public function loadCustomerQuote()
    {
        $lastQid = Mage::getSingleton('checkout/session')->getQuoteId(); //quote id during session before login;
        if ($lastQid) { //before login session exists means cart has items
            $customerQuote = Mage::getModel('sales/quote')
                ->loadByCustomer(Mage::getSingleton('customer/session')->getCustomerId()); //the cart from last login
            //set it to the session before login and remove its items if any
            $customerQuote->setQuoteId($lastQid);
            $this->_removeAllItems($customerQuote);

        } else { //no session before login, so empty the cart (current cart is the old cart)
            $quote = Mage::getModel('checkout/session')->getQuote();
            $this->_removeAllItems($quote);
        }
    }

    /**
     * iterate through quote and remove all items
     *
     * @return nothing
     */
    protected function _removeAllItems($quote){
        //reset all custom attributes in the quote object here, eg:
       // $quote->setDestinationCity('');

        foreach ($quote->getAllItems() as $item) {
            $item->isDeleted(true);
            if ($item->getHasChildren()) foreach ($item->getChildren() as $child) $child->isDeleted(true);
        }
        $quote->collectTotals()->save();        
    } //_removeAllItems


	/* Contus End */
    public function salesQuoteSaveAfter($observer)
    {
        $quote = $observer->getEvent()->getQuote();
        /* @var $quote Mage_Sales_Model_Quote */
        if ($quote->getIsCheckoutCart()) {
            Mage::getSingleton('checkout/session')->getQuoteId($quote->getId());
        }
    }
}
