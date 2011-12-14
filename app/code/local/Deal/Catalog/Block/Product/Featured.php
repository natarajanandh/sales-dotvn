<?php
class Deal_Catalog_Block_Product_Featured extends Mage_Catalog_Block_Product_Abstract
{
    public function getFeaturedProduct()
    {
        // instantiate database connection object
        $storeId = Mage::app()->getStore()->getId();
        //$categoryId = $this->getRequest()->getParam('id', "3");
        $resource = Mage::getSingleton('core/resource');
        $read = $resource->getConnection('catalog_read');
        $categoryProductTable = $resource->getTableName('catalog/category_product');
        //$productEntityIntTable = $resource->getTableName('catalog/product_entity_int'); // doesn't work :(
        $productEntityIntTable = (string)Mage::getConfig()->getTablePrefix() . 'catalog_product_entity_int';
        $eavAttributeTable = $resource->getTableName('eav/attribute');

        // Query database for featured product
        $select = $read->select()
                       ->from(array('cp'=>$categoryProductTable))
                       ->join(array('pei'=>$productEntityIntTable), 'pei.entity_id=cp.product_id', array())
                       ->joinNatural(array('ea'=>$eavAttributeTable))
                       //->where('cp.category_id=?', $categoryId) //if you need Category Filter Uncomment this change the category id
                       ->where('pei.value=1')
                       ->where('ea.attribute_code="featured"')
                       ->group('cp.product_id');
        //print_r($read->fetchAll($select));

        $row = $read->fetchAll($select);
        $fetch_products = array();
        //echo $select;
     if( count($row)>0 ) foreach($row as $rows) {
           $fetch_products[] = Mage::getModel('catalog/product')->setStoreId($storeId)->load($rows['product_id']);
       }
       return $fetch_products;
    }
}
?>