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
 * @package     Mage_Catalog
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Product list
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Catalog_Block_Product_List extends Mage_Catalog_Block_Product_Abstract {
    /**
     * Default toolbar block name
     *
     * @var string
     */
    protected $_defaultToolbarBlock = 'catalog/product_list_toolbar';

    /**
     * Product Collection
     *
     * @var Mage_Eav_Model_Entity_Collection_Abstract
     */
    protected $_productCollection;

    /**
     * Retrieve loaded category collection
     *
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    protected function _getProductCollection() {
        if (is_null($this->_productCollection)) {
            $layer = Mage::getSingleton('catalog/layer');
            /* @var $layer Mage_Catalog_Model_Layer */
            if ($this->getShowRootCategory()) {
                $this->setCategoryId(Mage::app()->getStore()->getRootCategoryId());
            }

            // if this is a product view page
            if (Mage::registry('product')) {
                // get collection of categories this product is associated with
                $categories = Mage::registry('product')->getCategoryCollection()
                        ->setPage(1, 1)
                        ->load();
                // if the product is associated with any category
                if ($categories->count()) {
                    // show products from this category
                    $this->setCategoryId(current($categories->getIterator()));
                }
            }

            $origCategory = null;
            if ($this->getCategoryId()) {
                $category = Mage::getModel('catalog/category')->load($this->getCategoryId());
                if ($category->getId()) {
                    $origCategory = $layer->getCurrentCategory();
                    $layer->setCurrentCategory($category);
                }
            }
            $this->_productCollection = $layer->getProductCollection();

            $this->prepareSortableFieldsByCategory($layer->getCurrentCategory());

            if ($origCategory) {
                $layer->setCurrentCategory($origCategory);
            }
        }
        return $this->_productCollection;
    }

    public function getQuantityPurchase($_product) {
        $current_product = $_product->getId();
        $resource = Mage::getSingleton('core/resource');
        $read = $resource->getConnection('catalog_read');
        $orderTable = $resource->getTableName('sales/order');
        $orderItemTable = $resource->getTableName('sales/order_item');
        $dealstatus[0] = "processing";
        $dealstatus[2] = "complete";
        $select = $read->select()
                ->from(array('cp'=>$orderTable),array( 'totalcount'  => 'sum(cp.total_qty_ordered)'))
                ->join(array('pei'=>$orderItemTable), 'pei.order_id=cp.entity_id',array())
                ->where('cp.status in (?)', $dealstatus)
                ->where('pei.product_id in (?)', $current_product);
        $orders_list = $read->fetchAll($select);
        $quantity[0] = floor($orders_list[0]['totalcount']);
        if($_product->gettarget_number() != '') {
            $percent_deal = ($quantity[0]/ $_product->gettarget_number())*100;
            //$paypalPaymentAction = $resource->getConnection('core_write');
            $tprefix = (string)Mage::getConfig()->getTablePrefix();
            
            if($_product->gettarget_number() > $quantity[0]) {
                //$paypalPaymentAction->query("update ".$tprefix."core_config_data set value = 'Authorization' where path = 'payment/paypal_standard/payment_action'" );
                $targetNumber = $_product->gettarget_number() - $quantity[0];
               $targetNumber .= $this->__(' more needed to get the deal');
            }  else {
                $targetNumber = $this->__('Deal Achieved! Keep Buying!');
               // $paypalPaymentAction->query("update ".$tprefix."core_config_data set value = 'Sale' where path = 'payment/paypal_standard/payment_action'" );
            }
        }else {
            $percent_deal = '100';
        }
        $percent_deal =  round($percent_deal);
        if($percent_deal >0   && $percent_deal <=99) {
        }
        if($percent_deal == 0 ) {
            $percent_deal = "0";
        }
        if($percent_deal >0   && $percent_deal <=15) {
            $percent_deal = "1";
        }
        if($percent_deal >=16  && $percent_deal <=33) {
            $percent_deal = "2";
        }
        if($percent_deal >=34  && $percent_deal <=54) {
            $percent_deal = "3";
        }
        if($percent_deal >=55  && $percent_deal <=73) {
            $percent_deal = "4";
        }
        if($percent_deal >=74  && $percent_deal <=86) {
            $percent_deal = "5";
        }
        if($percent_deal >87  && $percent_deal <=99) {
            $percent_deal = "6";
        }
        if($percent_deal >= 100 ) {
            $percent_deal = "7";
        }
        $result[] = $quantity[0];
        $result[] = $percent_deal;
        $result[] = $targetNumber;
        return $result;
    }
    /**
     * Retrieve loaded category collection
     *
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function getLoadedProductCollection() {
        return $this->_getProductCollection();
    }

    /**
     * Retrieve current view mode
     *
     * @return string
     */
    public function getMode() {
        return $this->getChild('toolbar')->getCurrentMode();
    }

    /**
     * Need use as _prepareLayout - but problem in declaring collection from
     * another block (was problem with search result)
     */
    protected function _beforeToHtml() {
        /*$toolbar = $this->getLayout()->createBlock('catalog/product_list_toolbar', microtime());
        if ($toolbarTemplate = $this->getToolbarTemplate()) {
            $toolbar->setTemplate($toolbarTemplate);
        }*/
        $toolbar = $this->getToolbarBlock();

        // called prepare sortable parameters
        $collection = $this->_getProductCollection();

        // use sortable parameters
        if ($orders = $this->getAvailableOrders()) {
            $toolbar->setAvailableOrders($orders);
        }
        if ($sort = $this->getSortBy()) {
            $toolbar->setDefaultOrder($sort);
        }
        if ($dir = $this->getDefaultDirection()) {
            $toolbar->setDefaultDirection($dir);
        }
        if ($modes = $this->getModes()) {
            $toolbar->setModes($modes);
        }

        // set collection to tollbar and apply sort
        $toolbar->setCollection($collection);

        $this->setChild('toolbar', $toolbar);
        Mage::dispatchEvent('catalog_block_product_list_collection', array(
                'collection'=>$this->_getProductCollection(),
        ));

        $this->_getProductCollection()->load();
        Mage::getModel('review/review')->appendSummary($this->_getProductCollection());
        return parent::_beforeToHtml();
    }

    /**
     * Retrieve Toolbar block
     *
     * @return Mage_Catalog_Block_Product_List_Toolbar
     */
    public function getToolbarBlock() {
        if ($blockName = $this->getToolbarBlockName()) {
            if ($block = $this->getLayout()->getBlock($blockName)) {
                return $block;
            }
        }
        $block = $this->getLayout()->createBlock($this->_defaultToolbarBlock, microtime());
        return $block;
    }

    /**
     * Retrieve list toolbar HTML
     *
     * @return string
     */
    public function getToolbarHtml() {
        return $this->getChildHtml('toolbar');
    }

    public function setCollection($collection) {
        $this->_productCollection = $collection;
        return $this;
    }

    public function addAttribute($code) {
        $this->_getProductCollection()->addAttributeToSelect($code);
        return $this;
    }

    public function getPriceBlockTemplate() {
        return $this->_getData('price_block_template');
    }

    /**
     * Retrieve Catalog Config object
     *
     * @return Mage_Catalog_Model_Config
     */
    protected function _getConfig() {
        return Mage::getSingleton('catalog/config');
    }

    /**
     * Prepare Sort By fields from Category Data
     *
     * @param Mage_Catalog_Model_Category $category
     * @return Mage_Catalog_Block_Product_List
     */
    public function prepareSortableFieldsByCategory($category) {
        if (!$this->getAvailableOrders()) {
            $this->setAvailableOrders($category->getAvailableSortByOptions());
        }
        $availableOrders = $this->getAvailableOrders();
        if (!$this->getSortBy()) {
            if ($categorySortBy = $category->getDefaultSortBy()) {
                if (!$availableOrders) {
                    $availableOrders = $this->_getConfig()->getAttributeUsedForSortByArray();
                }
                if (isset($availableOrders[$categorySortBy])) {
                    $this->setSortBy($categorySortBy);
                }
            }
        }


        return $this;
    }
}
