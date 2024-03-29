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
 * @package     Mage_Customer
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Customer front  newsletter manage block
 *
 * @category   Mage
 * @package    Mage_Customer
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Customer_Block_Newsletter extends Mage_Customer_Block_Account_Dashboard // Mage_Core_Block_Template
{

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('customer/form/newsletter.phtml');
    }

    public function getIsSubscribed()
    {
        return $this->getSubscriptionObject()->isSubscribed();
    }

    public function getAction()
    {
        return $this->getUrl('*/*/save');
    }

    /*
     * get customer id and newsletter subscription details for the customers
     * update by : sathish kumar
     * on @ 03.12.2010
     */

     public function getCustomerSubscription() {
        $customerId = 0;
        $customerId = Mage::getSingleton('customer/session')->getId();
        $resource = Mage::getSingleton('core/resource');
        $read = $resource->getConnection('newsletter_read');
        $subscriberTable = $resource->getTableName('newsletter/subscriber');

        $select = $read->select()
                        ->from(array('cp' => $subscriberTable))
                        ->where('cp.customer_id in (?)', $customerId);
        $subscriber = $read->fetchAll($select);

        return $subscriber;
    }

    public function getCityToSubscribe($city){
        $attribute = Mage::getModel('eav/config')->getAttribute('catalog_product', '548'); // get the cities attribute id 548
        foreach ($attribute->getSource()->getAllOptions(true, true) as $option) {
            $value = $option['value'];
            if ($value != '') {
                if ($city != $option['label']) {
                    $cities[$value] = $option['label'];
                    $select .= "<option value='" . $option['label'] . "'>" . $option['label'] . "</option>";
                }
            }
        }
        return $select;
    }

    public function getCustomerEmail(){
        $customerId = Mage::getSingleton('customer/session')->getId();
        $resource = Mage::getSingleton('core/resource');
        $read = $resource->getConnection('customer_read');
        $customerTable = $resource->getTableName('customer/entity');

        $select = $read->select()
                        ->from(array('cp' => $customerTable))
                        ->where('cp.entity_id in (?)', $customerId);
        $email = $read->fetchAll($select);
        return $email;
    }


}
