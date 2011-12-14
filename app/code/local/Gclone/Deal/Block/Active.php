<?php
/**
 * Contus Support Interactive.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file GCLONE-LICENSE.txt.
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento 1.4.1.1 COMMUNITY edition
 * Contus Support does not guarantee correct work of this package
 * on any other Magento edition except Magento 1.4.1.1 COMMUNITY edition.
 * =================================================================
 */

/*
 * Contus Support Interactive
 */

class Gclone_Deal_Block_Active extends Mage_Core_Block_Template {

    public function getFeaturedProduct($limit = null, $offset=null) {
        // instantiate database connection object
        $storeId = Mage::app()->getStore()->getId();        
        $resource = Mage::getSingleton('core/resource');
        $read = $resource->getConnection('catalog_read');
        $categoryProductTable = $resource->getTableName('catalog/category_product');
        $productEntityIntTable = (string) Mage::getConfig()->getTablePrefix() . 'catalog_product_entity_int';
        $ProductFlatTable = (string) Mage::getConfig()->getTablePrefix() . 'catalog_product_flat_1';
        $eavAttributeTable = $resource->getTableName('eav/attribute');

        $select = $read->select()
                        ->from(array('cp' => $categoryProductTable))
                        ->join(array('pei' => $productEntityIntTable), 'pei.entity_id=cp.product_id', array())
                        ->joinNatural(array('ea' => $eavAttributeTable))
                        ->joinNatural(array('or' => $ProductFlatTable))
                        ->where('pei.value=1')
                        ->group(array('cp.product_id'))
                        ->order(array('or.special_from_date desc'));
        $row = $read->fetchAll($select);
        $fetch_products = array();

        if (count($row) > 0)
            foreach ($row as $rows) {
                $fetch_products[] = Mage::getModel('catalog/product')->setStoreId($storeId)->load($rows['product_id']);
            }
        return $fetch_products;
    }
}
?>