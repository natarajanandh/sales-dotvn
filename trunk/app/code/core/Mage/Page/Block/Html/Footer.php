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
class Mage_Page_Block_Html_Footer extends Mage_Core_Block_Template {

    protected $_copyright;

    protected function _construct() {
        $this->addData(array(
            'cache_lifetime' => false,
            'cache_tags' => array(Mage_Core_Model_Store::CACHE_TAG, Mage_Cms_Model_Block::CACHE_TAG)
        ));
    }

    /**
     * Get cache key informative items
     *
     * @return array
     */
    public function getCacheKeyInfo() {
        return array(
            'PAGE_FOOTER',
            Mage::app()->getStore()->getId(),
            (int) Mage::app()->getStore()->isCurrentlySecure(),
            Mage::getDesign()->getPackageName(),
            Mage::getDesign()->getTheme('template')
        );
    }

    public function setCopyright($copyright) {
        $this->_copyright = $copyright;
        return $this;
    }

    public function getCopyright() {
        if (!$this->_copyright) {
            $this->_copyright = Mage::getStoreConfig('design/footer/copyright');
        }

        return $this->_copyright;
    }

    /**
     * Retrieve child block HTML, sorted by default
     *
     * @param   string $name
     * @param   boolean $useCache
     * @return  string
     */
    public function getChildHtml($name='', $useCache=true, $sorted=true) {
        return parent::getChildHtml($name, $useCache, $sorted);
    }

    /*
     * Total discounts
     */

    public function getDiscountsales() {
        $product_email = "";
        $_productCollection = Mage::getResourceModel('catalog/product_collection');       
        $totalDiscount = 0;
        $savedMoney = 0;
        $grandtotals = 0;
        $totalQtyPurchase = 0;
        $returnvalues = array();
        if (!$_productCollection->count()):

        else:
            foreach ($_productCollection as $_product){

                $model = Mage::getModel('catalog/product');
                $_product = $model->load($_product->getId());

                $productId = (int) $_product->getId();
                $product = Mage::getModel('catalog/product')->load($productId);
                $resource = Mage::getSingleton('core/resource');
                $orderTable = $resource->getTableName('sales/order');
                $read = $resource->getConnection('read');
                $tprefix = (string) Mage::getConfig()->getTablePrefix();
                $flatorderTable = $tprefix . "sales_flat_order_item";
                $total_orders = "0";
                $orderStatus[1] = "processing";
                $orderStatus[0] = "complete";
                $select = $read->select()
                                ->from(array('cp' => $orderTable))
                                ->join(array('pei' => $flatorderTable), 'pei.order_id=cp.entity_id', array())
                                ->where('pei.product_id=?', $productId)
                                ->where('cp.status IN (?)', $orderStatus);

                $orders_list = $read->fetchAll($select);
                
                foreach ($orders_list as $rows) {
                    $order_id = $rows['entity_id'];
                    $pri = $_product->getPrice() - $_product->getSpecialPrice();
                    $total_order = (int) $rows['total_qty_ordered'];

                    $savedMoney += $pri * $total_order;
                    $order = Mage::getModel('sales/order')->load($order_id);
                    $items = $order->getAllItems();

                    $qty = 0;
                    //calculate Quantity
                    foreach ($items as $itemId => $item) {
                        if ($productId == $item->getProductId()) {
                            $total_orders = $total_orders + 1;
                            $qty = $item->getQtyOrdered();
                            $totalQtyPurchase = $totalQtyPurchase + $qty;
                        }
                    }
                }
            }            
        endif;
        if (empty($totalQtyPurchase)) {
            $totalQtyPurchase = 0;
        }       
        $returnvalues[0] = $savedMoney;
        $returnvalues[1] = $totalQtyPurchase;
        return $returnvalues;
    }

}
