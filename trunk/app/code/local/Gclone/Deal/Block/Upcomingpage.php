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
class Gclone_Deal_Block_Upcomingpage extends Mage_Core_Block_Template {

    public function _prepareLayout() {
        return parent::_prepareLayout();
    }

    public function getUpcomingProduct($limit=null) {
        $limit = 1;

        $p = $_GET['start'];
        if ($p != "") {
            $start = ($p - 1) * $limit;
        } else {
            $start = 0;
        }
        // instantiate database connection object
        $storeId = Mage::app()->getStore()->getId();

        $resource = Mage::getSingleton('core/resource');
        $currentdeal = $resource->getConnection('core_write');
        $read = $resource->getConnection('catalog_read');
        $city = Mage::getSingleton('core/session')->getCity();
        $categoryProductTable = $resource->getTableName('catalog/category_product');

        $productEntityIntTable = (string) Mage::getConfig()->getTablePrefix() . 'catalog_product_entity_int';
        $productEntityIntiTable = (string) Mage::getConfig()->getTablePrefix() . 'catalog_product_flat_1';
        $productEntitytimeTable = (string) Mage::getConfig()->getTablePrefix() . 'catalog_product_entity_varchar';
        $eavAttributeTable = $resource->getTableName('eav/attribute');
        $currentTime = Mage_Core_Model_Locale::date(null, null, "en_US", true);
        $d = strtotime($currentTime);
        $date = date("Y-m-d", $d);
        $time = date("h:i:s A", $d);
        $resultProductId = $currentdeal->fetchAll("SELECT `cp`.*, `ea`.* FROM `magentocatalog_category_product` AS `cp` INNER JOIN `magentocatalog_product_entity_int` AS `pei` ON pei.entity_id=cp.product_id INNER JOIN `magentocatalog_product_flat_1` AS `cei` ON cei.entity_id=cp.product_id NATURAL JOIN `magentoeav_attribute` AS `ea` WHERE (pei.value=1) AND (cei.cities = '$city') AND (cei.special_from_date > '$date') GROUP BY `cp`.`product_id` limit $start,$limit");

        if (count($resultProductId) > 0)
            foreach ($resultProductId as $rows) {
                $rows['product_id'];
                $fetch_products[] = Mage::getModel('catalog/product')->setStoreId($storeId)->load($rows['product_id']);
            }
        return $fetch_products;
    }

}