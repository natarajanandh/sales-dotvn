<?php

/**
 */
class Gclone_Deal_Model_Observer {

    public function urlRewrite($observer) {
        $product = $observer->getEvent()->id;
        $storeId = Mage::app()->getStore()->getStoreId();
        $resource = Mage::getSingleton('core/resource');
        $read = $resource->getConnection('read');
        $tPrefix = (string) Mage::getConfig()->getTablePrefix();
        $rewriteTable = $tPrefix . 'core_url_rewrite';

        if (empty($storeId)) {
            $storeId = 1;
        }

        $model = Mage::getModel('catalog/product');
        $_product = $model->load($product);
        $productName = $_product->getName();
        $productId = $product;

        //Create Deal Url
       
        $productName = htmlspecialchars(trim($productName));
        $productName = str_replace(' ', '-', $productName);
        $productName = ereg_replace("[^A-Za-z0-9^-]", "", $productName);
        $requestPath = 'deal/' . $productName . '.html';
        $realPath = 'deal/index/index/id/' . $productId;

        $idPath = 'deal/index/index/id/' . $productId;
        $urlRewrite = $read->select()
                        ->from(array('ur' => $rewriteTable), array('ur.request_path'))
                        ->where('ur.id_path =? ', $idPath)
                        ->where('ur.store_id =? ', $storeId)
                        ->where('ur.is_system =?', 0);
        $productUrl = $read->fetchRow($urlRewrite);
        if (empty($productUrl)) {
            //check whether the request path is already found
            $urlCheck = $read->select()
                            ->from(array('ur' => $rewriteTable), array('ur.request_path'))
                            ->where('ur.request_path =? ', $requestPath)
                            ->where('ur.store_id =? ', $storeId)
                            ->where('ur.is_system =?', 0);
            $productUrlCheck = $read->fetchRow($urlCheck);
            //If request path found, then append the product id to the request path
            if (!empty($productUrlCheck)) {
                $requestPath = 'deal/' . $productName . '-' . $productId . '.html';
            }
            $executeQuery = $read->query("INSERT INTO $rewriteTable (`store_id`, `category_id`, `product_id`, `id_path`, `request_path`, `target_path`, `is_system`, `options`, `description`) VALUES ($storeId, NULL, NULL, '$realPath', '$requestPath', '$realPath', '0', '', NULL)");
        }
        return $requestPath;
    }

}