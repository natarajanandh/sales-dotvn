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
 * @copyright   Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/*contus*/
/**
 * Product status functionality model
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Catalog_Model_Product_Dealcategory extends Mage_Core_Model_Abstract
{

    /**
     * Reference to the attribute instance
     *
     * @var Mage_Catalog_Model_Resource_Eav_Attribute
     */
    protected $_attribute;

    /**
     * Initialize resource model
     *
     */
    protected function _construct()
    {
        $this->_init('catalog/product_Dealcategory');
    }

    /**
     * Retrieve resource model wraper
     *
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Status
     */
    protected function _getResource()
    {
        return parent::_getResource();
    }

    /**
     * Retrieve Product Attribute by code
     *
     * @param string $attributeCode
     * @return Mage_Eav_Model_Entity_Attribute_Abstract
     */
    public function getProductAttribute($attributeCode)
    {
        return $this->_getResource()->getProductAttribute($attributeCode);
    }

    /**
     * Add visible filter to Product Collection
     *
     * @deprecated remove on new builds
     * @param Mage_Eav_Model_Entity_Collection_Abstract $collection
     * @return Mage_Catalog_Model_Product_Status
     */
    public function addVisibleFilterToCollection(Mage_Eav_Model_Entity_Collection_Abstract $collection)
    {
        //$collection->addAttributeToFilter('status', array('in'=>$this->getVisibleStatusIds()));
        return $this;
    }

    /**
     * Add saleable filter to Product Collection
     *
     * @deprecated remove on new builds
     * @param Mage_Eav_Model_Entity_Collection_Abstract $collection
     * @return Mage_Catalog_Model_Product_Status
     */
    public function addSaleableFilterToCollection(Mage_Eav_Model_Entity_Collection_Abstract $collection)
    {
        //$collection->addAttributeToFilter('status', array('in'=>$this->getSaleableStatusIds()));
        return $this;
    }

    /**
     * Retrieve Visible Status Ids
     *
     * @return array
     */
    public function getVisibleStatusIds()
    {
        return array(self::DEALCATEGORY);
    }

    /**
     * Retrieve Saleable Status Ids
     * Default Product Enable status
     *
     * @return array
     */
    public function getSaleableStatusIds()
    {
        return array(self::DEALCATEGORY);
    }

    /**
     * Retrieve option array
     *
     * @return array
     */
    static public function getOptionArray()
    {
        $productCollection = Mage::getModel('catalog/product')->getCollection();
        $productCount = array();
        foreach($productCollection as $_product)
        {
            $cat = $_product->getCategoryIds();
            if($cat[0]== 6)
            {
                $catName = 'Main Deal';
            }
            elseif($cat[0]== 7)
            {
                $catName = 'Side Deal';
            }else
            {
                $catName = "";
            }
            $productCount[$_product->getId()] = $catName;
        }
        return $productCount;
    }

    /**
     * Retrieve option array with empty value
     *
     * @return array
     */
    static public function getAllOption()
    {
        $options = self::getOptionArray();
        array_unshift($options, array('value'=>'', 'label'=>''));
        return $options;
    }

    /**
     * Retrieve option array with empty value
     *
     * @return array
     */
    static public function getAllOptions()
    {
        $res = array(
            array(
                'value' => '',
                'label' => Mage::helper('catalog')->__('-- Please Select --')
            )
        );
        foreach (self::getOptionArray() as $index => $value) {
            $res[] = array(
               'value' => $index,
               'label' => $value
            );
        }
        return $res;
    }

    /**
     * Retrieve option text by option value
     *
     * @param string $optionId
     * @return string
     */
    static public function getOptionText($optionId)
    {
        $options = self::getOptionArray();
        return isset($options[$optionId]) ? $options[$optionId] : null;
    }

    /**
     * Update status value for product
     *
     * @param   int $productId
     * @param   int $storeId
     * @param   int $value
     * @return  Mage_Catalog_Model_Product_Status
     */
    public function updateProductStatus($productId, $storeId, $value)
    {
        Mage::getSingleton('catalog/product_action')
        ->updateAttributes(array($productId), array('status' => $value), $storeId);

        // add back compatibility event
        $status = $this->_getResource()->getProductAttribute('status');
        if ($status->isScopeWebsite()) {
            $website = Mage::app()->getStore($storeId)->getWebsite();
            $stores  = $website->getStoreIds();
        } else if ($status->isScopeStore()) {
            $stores = array($storeId);
        } else {
            $stores = array_keys(Mage::app()->getStores());
        }

        foreach ($stores as $storeId) {
            Mage::dispatchEvent('catalog_product_status_update', array(
                'product_id'    => $productId,
                'store_id'      => $storeId,
                'status'        => $value
                ));
        }

        return $this;
    }

    /**
     * Retrieve Product(s) status for store
     * Return array where key is product, value - status
     *
     * @param int|array $productIds
     * @param int $storeId
     * @return array
     */
    public function getProductStatus($productIds, $storeId = null)
    {
        return $this->getResource()->getProductStatus($productIds, $storeId);
    }

    /**
     * ---------------- Eav Source methods for Flat data -----------------------
     */

    /**
     * Retrieve Column(s) for Flat
     *
     * @return array
     */
    public function getFlatColums()
    {
        return array();
    }

    /**
     * Retrieve Indexes for Flat
     *
     * @return array
     */
    public function getFlatIndexes()
    {
        return array();
    }

    /**
     * Retrieve Select For Flat Attribute update
     *
     * @param Mage_Eav_Model_Entity_Attribute_Abstract $attribute
     * @param int $store
     * @return Varien_Db_Select|null
     */
    public function getFlatUpdateSelect($store)
    {
        return null;
    }

    /**
     * Set attribute instance
     *
     * @param Mage_Catalog_Model_Resource_Eav_Attribute $attribute
     * @return Mage_Eav_Model_Entity_Attribute_Frontend_Abstract
     */
    public function setAttribute($attribute)
    {
        $this->_attribute = $attribute;
        return $this;
    }

    /**
     * Get attribute instance
     *
     * @return Mage_Catalog_Model_Resource_Eav_Attribute
     */
    public function getAttribute()
    {
        return $this->_attribute;
    }

    /**
     * Add Value Sort To Collection Select
     *
     * @param Mage_Eav_Model_Entity_Collection_Abstract $collection
     * @param string $dir direction
     * @return Mage_Eav_Model_Entity_Attribute_Source_Abstract
     */
    public function addValueSortToCollection($collection, $dir = 'asc')
    {
        if ($this->getAttribute()->isScopeGlobal()) {
            $tableName = $this->getAttribute()->getAttributeCode() . '_t';
            $collection->getSelect()
            ->joinLeft(
                array($tableName => $this->getAttribute()->getBackend()->getTable()),
                    "`e`.`entity_id`=`{$tableName}`.`entity_id`"
                . " AND `{$tableName}`.`attribute_id`='{$this->getAttribute()->getId()}'"
                . " AND `{$tableName}`.`store_id`='0'",
                array());
            $valueExpr = $tableName . '.value';
        }
        else {
            $valueTable1    = $this->getAttribute()->getAttributeCode() . '_t1';
            $valueTable2    = $this->getAttribute()->getAttributeCode() . '_t2';
            $collection->getSelect()
            ->joinLeft(
                array($valueTable1 => $this->getAttribute()->getBackend()->getTable()),
                    "`e`.`entity_id`=`{$valueTable1}`.`entity_id`"
                . " AND `{$valueTable1}`.`attribute_id`='{$this->getAttribute()->getId()}'"
                . " AND `{$valueTable1}`.`store_id`='0'",
                array())
            ->joinLeft(
                array($valueTable2 => $this->getAttribute()->getBackend()->getTable()),
                    "`e`.`entity_id`=`{$valueTable2}`.`entity_id`"
                . " AND `{$valueTable2}`.`attribute_id`='{$this->getAttribute()->getId()}'"
                . " AND `{$valueTable2}`.`store_id`='{$collection->getStoreId()}'",
                array()
            );
            $valueExpr = new Zend_Db_Expr("IF(`{$valueTable2}`.`value_id`>0, `{$valueTable2}`.`value`, `{$valueTable1}`.`value`)");
        }

        $collection->getSelect()->order($valueExpr . ' ' . $dir);
        return $this;
    }
}
