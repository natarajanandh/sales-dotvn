<?php

/**
 * Contus Support
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
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml dashboard last search keywords block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
/* contus */
class Mage_Adminhtml_Block_Dashboard_Summary_Deal extends Mage_Adminhtml_Block_Dashboard_Griding {

    protected $_collection;

    public function __construct() {
        parent::__construct();
        $this->setId('dealSummaryGrid');
    }

    /* Returns Requied details(rows) to the griding.phtml page */

    public function getdetails() {
        $city = $this->getRequest()->getParam('city');

        $mainActive = 0;
        $mainPast = 0;
        $sideActive = 0;
        $sidePast = 0;

        //Getting Admin Time Zone
        $currentTime = Mage_Core_Model_Locale::date(null, null, "en_US", true);
        $model = Mage::getModel('catalog/product');
        $adminuser = Mage::getSingleton('admin/session')->getUser();
        $roleId = implode('', $adminuser->getRoles());
        $userId = $adminuser->getId();
        //Maindeals
        $_productCollection = Mage::getModel('catalog/product')->getCollection();
        $_productCollection->addAttributeToFilter('city', array('finset' => $city));
        if ($roleId != 1) {
            $_productCollection->addAttributeToFilter('merchant', array('and' => $userId)); //for every merchants
        }
        $_productCollection->addCategoryFilter(Mage::getModel('catalog/category')->load('6'));

        if (count($_productCollection) > 0) {
            foreach ($_productCollection as $_product) {
                $_product = $model->load($_product->getId());

                $ProductToDate = $_product->getResource()->formatDate($_product->getspecial_to_date(), false);
                $ProductFromDate = $_product->getResource()->formatDate($_product->getspecial_from_date(), false);
                $Fromtime = strtotime($ProductFromDate);
                if ($Fromtime < strtotime($currentTime)) {
                    if (strtotime($ProductToDate . ' ' . $_product->getTime()) > strtotime($currentTime)) {
                        //increment the current main deal
                        $mainActive = $mainActive + 1;
                    } else {
                        //increment the past main deal
                        $mainPast = $mainPast + 1;
                    }
                }
            }
        }
        //sidedeals
        $_productCollection = Mage::getModel('catalog/product')->getCollection();
        $_productCollection->addAttributeToFilter('city', array('finset' => $city));
        if ($roleId != 1) {
            $_productCollection->addAttributeToFilter('merchant', array('and' => $userId)); //for every merchants
        }
        $_productCollection->addCategoryFilter(Mage::getModel('catalog/category')->load('7'));

        if (count($_productCollection) > 0) {
            foreach ($_productCollection as $_product) {
                $_product = $model->load($_product->getId());

                $ProductToDate = $_product->getResource()->formatDate($_product->getspecial_to_date(), false);
                $ProductFromDate = $_product->getResource()->formatDate($_product->getspecial_from_date(), false);
                $Fromtime = strtotime($ProductFromDate);
                if ($Fromtime < strtotime($currentTime)) {
                    if (strtotime($ProductToDate . ' ' . $_product->getTime()) > strtotime($currentTime)) {
                        //increment the current side deal
                        $sideActive = $sideActive + 1;
                    } else {
                        //increment the past side deal
                        $sidePast = $sidePast + 1;
                    }
                }
            }
        }


        $Listofdeal['mainActive'] = "$mainActive";

        $Listofdeal['mainPast'] = "$mainPast";
        $Listofdeal['sideActive'] = "$sideActive";

        $Listofdeal['sidePast'] = "$sidePast";

        return $Listofdeal;
    }

    /* Collection return Nothing -(Not used) */

    protected function _prepareCollection() {

        $this->_collection = $this->getResourceCollection();
        $this->setCollection($this->_collection);

        return parent::_prepareCollection();
    }

    /* Column Required for the Grid */

    protected function _prepareColumns() {

        $this->addColumn('deals', array(
            'header' => $this->__('Deals'),
            'sortable' => false,
        ));


        $this->addColumn('numbers', array(
            'header' => $this->__('Number Of Deals'),
            'sortable' => false,
        ));


        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);

        return parent::_prepareColumns();
    }

    /* URL to the Catalog Product Filter */

//    public function getRowUrl($row) {
//        $filter['filter'] = $row;
//        $city = $this->getRequest()->getParam('city');
//        if(isset($city) && $city != '0') {
//            $filter['city'] = $city;
//        }
//        return $this->getUrl('*/catalog_product', $filter);
//    }

    /* Display details in the Dashboard Returns row and city-(Not implemented) */
    /*  public function getCityFilter($row) {
      $filter['filter'] = $row;
      $city = $this->getRequest()->getParam('city');
      if(isset($city) && $city != '0') {
      return $row."=".$city;
      }
      return $row;
      } */

    protected function _getStore() {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

}
