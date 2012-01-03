<?php
  /**
   *
   * @	Author			:	TroXx
   * @	Release on		:	25.07.2011
   * @	Official site	:	http://www.warshare.cz
   *
   */
  
  class Gclone_Deal_Model_Deal extends Mage_Core_Model_Abstract
  {
      public $lookupObj = array();
      public $chars_str = NULL;
      public $chars_array = array();
      public $message = "MP0EFIL9XEV8YZAL7KCIUK6NI5OREH4TSEB3TSRIF2SI1ROTAQIDALG-JW";
      
      public function _construct()
      {
          parent::_construct();
          $this->_init("deal/deal");
      }
      
      public function fetchDeals($cityId)
      {
          if ($this->isLicense() === true) {
              $id       = Mage::app()->getRequest()->getParam("id");
              $products = array();
              if (isset($id)) {
                  $products[0] = $id;
              } else {
                  $_productCollection = Mage::getmodel("catalog/product")->getCollection();
				  $_productCollection->getSelect()->join(array('items'=>'magentocatalog_product_flat_1'),'items.entity_id=e.entity_id',array('special_from_date' => 'special_from_date','special_to_date'=>'special_to_date'));
                  $_productCollection->addFieldToFilter(array(
                      array(
                          "attribute" => "Status",
                          "eq" => "1"
                      ),
					  
                  ));
				  $_productCollection->setOrder('special_from_date','DESC');
                  $currentTime = Mage_Core_Model_Locale::date(null, null, "vi_VN", true);
                  $_productCollection->addAttributeToFilter("City", array(
                      "finset" => $cityId
                  ));
                  $_productCollection->addCategoryFilter(Mage::getmodel("catalog/category")->load("6"));
                  $deal  = 0;
                  $meta  = array();
                  $model = Mage::getmodel("catalog/product");
                  if (0 < count($_productCollection)) {
                      $count = 0;
                      foreach ($_productCollection as $_product) {
                          $_product        = $model->load($_product->getId());
                          $ProductToDate   = $_product->getResource()->formatDate($_product->getspecial_to_date(), false);
                          $ProductFromDate = $_product->getResource()->formatDate($_product->getspecial_from_date(), false);
                          $Fromtime        = strtotime($ProductFromDate);
                          if ($Fromtime < strtotime($currentTime) && strtotime($currentTime) < strtotime($ProductToDate . " " . $_product->getTime())) {
                              $meta[0]          = $_product->getMetaTitle();
                              $deal             = 1;
                              $meta[1]          = htmlspecialchars($_product->getMetaDescription());
                              $meta[2]          = htmlspecialchars($_product->getMetaKeyword());
                              $products[$count] = $_product->getId();
                          }
                          $count = $count + 1;
                      }
                  } else {
                      $meta[0] = "No Deal Available";
                      $meta[1] = "";
                      Mage::getsingleton("core/session")->setProductID("");
                  }
                  if ($deal != 1) {
                      $meta[0] = "No Deal Available";
                      $meta[1] = "";
                      Mage::getsingleton("core/session")->setProductID("");
                  }
              }
              return $products;
          }
      }
      
      public function urlCollections()
      {
          if ($this->isLicense() === true) {
              $attribute = Mage::getmodel("eav/config")->getAttribute("catalog_product", "562");
              $cityValue = array();
              foreach ($attribute->getSource()->getAllOptions(true, true) as $option) {
                  $cityValue[$option['label']] = $option['value'];
                  $storeId                     = Mage::app()->getStore()->getStoreId();
                  $city                        = $option['value'];
                  if ($city != "") {
                      $cityName     = $option['label'];
                      $cityName     = ereg_replace("[^A-Za-z0-9^-]", "", $cityName);
                      $cityName     = str_replace(" ", "-", $cityName);
                      $cityName     = strtolower($cityName);
                      $requestPath  = "deal/" . $cityName . ".html";
                      $realPath     = "deal/index/index/city/" . $city;
                      $resource     = Mage::getsingleton("core/resource");
                      $read         = $resource->getConnection("read");
                      $tPrefix      = ( boolean ) Mage::getconfig()->getTablePrefix();
                      $rewriteTable = "magentocore_url_rewrite";
                      $idPath       = "deal/index/index/city/" . $city;
                      $urlRewrite   = $read->select()->from(array(
                          "ur" => $rewriteTable
                      ), array(
                          "ur.request_path"
                      ))->where("ur.id_path =? ", $idPath)->where("ur.store_id =? ", $storeId)->where("ur.is_system =?", 0);
                      $cityUrl      = $read->fetchRow($urlRewrite);
                      if (empty($cityUrl)) {
                          $urlCheck     = $read->select()->from(array(
                              "ur" => $rewriteTable
                          ), array(
                              "ur.request_path"
                          ))->where("ur.request_path =? ", $requestPath)->where("ur.store_id =? ", $storeId)->where("ur.is_system =?", 0);
                          $cityUrlCheck = $read->fetchRow($urlCheck);
                          if (!empty($cityUrlCheck)) {
                              $requestPath = "deal/" . $cityName . "-" . $city . ".html";
                          }
                          $executeQuery = $read->query("INSERT INTO {$rewriteTable} (`store_id`, `category_id`, `product_id`, `id_path`, `request_path`, `target_path`, `is_system`, `options`, `description`) VALUES ({$storeId}, NULL, NULL, '{$realPath}', '{$requestPath}', '{$realPath}', '0', '', NULL)");
                      }
                  }
              }
              return $cityValue;
          }
      }
      
      public function cityCollections()
      {
          $attribute = Mage::getmodel("eav/config")->getAttribute("catalog_product", "562");
          $cityValue = array();
          foreach ($attribute->getSource()->getAllOptions(true, true) as $option) {
              $cityValue[$option['value']] = $option['label'];
          }
          return $cityValue;
      }
      
      public function getDealCollections($catId, $dealsCity)
      {
          if ($this->isLicense() === true) {
              $products = Mage::getresourcemodel("catalog/product_collection")->addAttributeToSelect("image")->addCategoryFilter(Mage::getmodel("catalog/category")->load($catId));
              $products->addAttributeToFilter("City", array(
                  "finset" => $dealsCity
              ));
              $products->addFieldToFilter(array(
                  array(
                      "attribute" => "Status",
                      "eq" => "1"
                  )
              ));
              return $products;
          }
      }
      //lay random
	  public function getDealCollectionsRadom($dealsCity)
      {
          if ($this->isLicense() === true) {
			  $currentTime = Mage_Core_Model_Locale::date(null, null, "vi_VN", true);
			  $today = date('Y-m-d').' 00:00:00';
			  //echo $today;
              $products = Mage::getresourcemodel("catalog/product_collection")->addAttributeToSelect("image");
			  $products->getSelect()->join(array('items'=>'magentocatalog_product_flat_1'),'items.entity_id=e.entity_id',array('special_from_date' => 'special_from_date','special_to_date'=>'special_to_date'));
			  $products->getSelect()->order('rand()');
              $products->addAttributeToFilter("City", array(
                  "finset" => $dealsCity
              ));
			  $products->addAttributeToFilter('special_from_date', array("to" =>  $today, "datetime" => true));
			  $products->addAttributeToFilter('special_to_date', array("from" =>  $today, "datetime" => true));
              $products->addFieldToFilter(array(
                  array(
                      "attribute" => "Status",
                      "eq" => "1"
                  )
              ));
			  $products->setPageSize(4);
              return $products;
          }
      }
	  
	  
      public function fetchCity()
      {
          if ($this->isLicense() === true) {
              $cityCollection = $this->urlCollections();
              $attribute      = Mage::getmodel("eav/config")->getAttribute("catalog_product", "562");
              $i              = 0;
              foreach ($attribute->getSource()->getAllOptions(true, true) as $option) {
                  $cityValue = $option['value'];
                  if ($i == 1) {
                      break;
                  }
                  ++$i;
              }
              $defaultCity = $attribute->getDefaultValue();
              $requestCity = Mage::app()->getRequest()->getParam("city");
              if (isset($requestCity)) {
                  Mage::getsingleton("core/session")->setCity(Mage::app()->getRequest()->getParam("city"));
              }
              $sessionCity = Mage::getsingleton("core/session")->getCity();
              if (!isset($sessionCity) && $sessionCity == "") {
                  if (isset($defaultCity)) {
                      Mage::getsingleton("core/session")->setCity($defaultCity);
                      $cityName = $defaultCity;
                  } else {
                      Mage::getsingleton("core/session")->setCity($cityValue);
                      $cityName = $cityValue;
                  }
              } else {
                  $cityName = $sessionCity;
              }
              return $cityName;
          }
      }
      
      public function getProductUrl($productId)
      {
          $resource     = Mage::getsingleton("core/resource");
          $read         = $resource->getConnection("read");
          $tPrefix      = ( boolean ) Mage::getconfig()->getTablePrefix();
          $storeId      = Mage::app()->getStore()->getStoreId();
          $rewriteTable = "magentocore_url_rewrite";
          $idPath       = "deal/index/index/id/" . $productId;
          $urlRewrite   = $read->select()->from(array(
              "ur" => $rewriteTable
          ), array(
              "ur.request_path"
          ))->where("ur.id_path =? ", $idPath)->where("ur.store_id =? ", $storeId)->where("ur.is_system =?", 0);
          $productUrl   = $read->fetchRow($urlRewrite);
          if (empty($productUrl)) {
              $data          = array();
              $data['id']    = $productId;
              $dispatchedUrl = Mage::dispatchevent("rewrite_deal_url", $data);
              $productUrl    = Mage::getbaseurl() . $dispatchedUrl;
          } else {
              $productUrl = Mage::getbaseurl() . $productUrl['request_path'];
          }
          return $productUrl;
      }
      
      public function getCityUrl($cityId)
      {
          $resource     = Mage::getsingleton("core/resource");
          $read         = $resource->getConnection("read");
          $tPrefix      = ( boolean ) Mage::getconfig()->getTablePrefix();
          $storeId      = Mage::app()->getStore()->getStoreId();
          $rewriteTable = "magentocore_url_rewrite";
          $idPath       = "deal/index/index/city/" . $cityId;
          $urlRewrite   = $read->select()->from(array(
              "ur" => $rewriteTable
          ), array(
              "ur.request_path"
          ))->where("ur.id_path =? ", $idPath)->where("ur.store_id =? ", $storeId)->where("ur.is_system =?", 0);
          $cityUrl      = $read->fetchRow($urlRewrite);
          $cityUrl      = Mage::getbaseurl() . $cityUrl['request_path'];
          return $cityUrl;
      }
      
      public function isLicense()
      {
          $resource1   = Mage::getsingleton("core/resource");
          $dealLicense = $resource1->getConnection("core_write");
          $tprefix     = ( boolean ) Mage::getconfig()->getTablePrefix();
          $license     = $dealLicense->fetchRow("Select value from " . "magentocore_config_data WHERE path = 'license/license/license'");
          $licenseKey  = $license['value'];
          $domain      = Mage::getbaseurl();
          $domainurl     = $matches[2];
          $domainurl     = str_replace("www.", "", $domainurl);
          $domainurl     = str_replace(".", "D", $domainurl);
          $domainurl     = strtoupper($domainurl);
          $orglicenseKey = $this->generatekey($domainurl);
          $phpSelf       = $_SERVER['SCRIPT_FILENAME'];
          $link          = explode("index.php", $phpSelf);
          $url           = $link[0];
          
        
          return true;
      }
      
      public function generatekey($domain)
      {
          $this->encryption();
          $code = $this->encrypt($domain);
          $code = substr($code, 0, 25) . "CONTUS";
          return true;
      }
      
      public function encryption()
      {
          $chars_str = "WJ-SUPERFAST1IS2SECOND3BEST4HERO5IN6QUICK7LAZY8VEX9LIFEMP0";
          $i         = 0;
          while ($i < strlen($chars_str)) {
              $chars_array[] = $chars_str[$i];
              ++$i;
          }
          $this->chars_array = $chars_array;
          $i                 = count($chars_array) - 1;
          while (0 <= $i) {
              $lookupObj[ord($chars_array[$i])] = $i;
              --$i;
          }
          $this->lookupObj = $lookupObj;
      }
      
      public function encrypt($tkey)
      {
          $chars_array = $this->chars_array;
          $i           = 0;
          while ($i < strlen($tkey)) {
              $key_array[] = $tkey[$i];
              ++$i;
          }
          $enc_message = "";
          $kPos        = 0;
          $i           = 0;
          while ($i < strlen($this->message)) {
              $char   = substr($this->message, $i, 1);
              $offset = $this->getOffset($key_array[$kPos], $char);
              $enc_message .= $chars_array[$offset];
              ++$kPos;
              if (count($key_array) <= $kPos) {
                  $kPos = 0;
              }
              ++$i;
          }
          return $enc_message;
      }
      
      public function getOffset($start, $end)
      {
          $lookupObj   = $this->lookupObj;
          $chars_array = $this->chars_array;
          $sNum        = $lookupObj[ord($start)];
          $eNum        = $lookupObj[ord($end)];
          $offset      = $eNum - $sNum;
          if ($offset < 0) {
              $offset = count($chars_array) + $offset;
          }
          return $offset;
      }
      
  }
  
?>
