<?php
 /**
   *
   * @	Author			:	TroXx
   * @	Release on		:	25.07.2011
   * @	Official site	:	http://www.warshare.cz
   *
   */
  
  class Mage_Voucher_Model_Voucher extends Mage_Core_Model_Abstract
  {
		 public function _construct()
		  {
			  parent::_construct();
			  $this->_init("voucher/voucher");
		  }
		public function getOrderCode($code,$email)
			{			
				$resource     = Mage::getsingleton("core/resource");
				$read         = $resource->getConnection("read");			  
				$storeId      = Mage::app()->getStore()->getStoreId();
				$tPrefix      = ( boolean ) Mage::getconfig()->getTablePrefix();			 
				$rewriteTable = "magentoordercustomer";			 
				$urlRewrite   = $read->select()->from(array(
					  "ur" => $rewriteTable
					  ), array(
						  "ur.ordercustomer_id","ur.status"
					  ))->where("ur.uniqueid =? ", $code);
			  $codeArr   = $read->fetchRow($urlRewrite);	 
			  
			if($codeArr){
				if($codeArr['status']==2){
					return $error=1;
				}else{
					return $error=2;			 
				}
			}else{
				return $error=3;	
			}			
		 }
		 
	/*
		Check information of customer
		@Param: $email, $numbervoucher
		$return: true, false
		@author: hanhdt
	*/
		public function checkCustomer($email,$numberVoucher){
			$resource     = Mage::getsingleton("core/resource");
			$read         = $resource->getConnection("read");			  
			$storeId      = Mage::app()->getStore()->getStoreId();
			$tPrefix      = ( boolean ) Mage::getconfig()->getTablePrefix();		
			// get customer by email in voucher card
			  $rewriteTable = "magentocustomer_entity";			 
				$urlRewrite   = $read->select()->from(array(
              "ur" => $rewriteTable
			  ), array(
				  "ur.entity_id"
			  ))->where("ur.email =? ", $email);
			 
			  $Customer   = $read->fetchRow($urlRewrite);
			
			if($Customer['entity_id']>0){
			  //check number voucher of customer
			   $orderGridTable = "magentosales_flat_order_grid";			 
				$urlRewrite   = $read->select()->from(array(
              "ord" => $orderGridTable
			  ), array(
				  "ord.increment_id"
			  ))->where("ord.customer_id =? ", $Customer['entity_id'])->where("ord.increment_id=?",$numberVoucher);
			  $OrderGrid   = $read->fetchRow($urlRewrite);
			 // print_r($OrderGrid );die;
			  if(!$OrderGrid['increment_id']){
				return $error=2;
			  }
			}else{
				return $error=1;
			}
		}
		public function getInformation($email,$numberVoucher){
		
			$resource     = Mage::getsingleton("core/resource");
			$read         = $resource->getConnection("read");			  
			$storeId      = Mage::app()->getStore()->getStoreId();
			$tPrefix      = ( boolean ) Mage::getconfig()->getTablePrefix();		
			// get customer by email in voucher card
			$rewriteTable = "magentocustomer_entity";			 
			$urlRewrite   = $read->select()->from(array(
              "ur" => $rewriteTable
			  ), array(
				  "ur.entity_id"
			  ))->where("ur.email =? ",$email);
			  $Customer   = $read->fetchRow($urlRewrite);
			  
			if($Customer['entity_id']>0){
			  //check number voucher of customer
			    $orderGridTable = "magentocustomer_address_entity";			 
				$urlRewrite = $read->select()->from(array(
					"ord" => $orderGridTable
				), array(
					"ord.entity_id",
				))->where("ord.parent_id =? ", $Customer['entity_id']);
					$customer_entity = $read->fetchRow($urlRewrite);				
			}
			
			if($customer_entity){
				$table = "magentocustomer_address_entity_varchar";
				$urlRewrite = $read->select()->from(array(
					"ord" => $table
				), array(
					"ord.value",
					"ord.attribute_id",
				))->where("ord.entity_id =? ", $customer_entity['entity_id'])->where("ord.attribute_id in (19,21,30)");
					$customerInfo = $read->fetchAll($urlRewrite);
				
			}
			return $customerInfo;
		}
		public function updateCodeOrder($code){
			$resource     = Mage::getsingleton("core/resource");
			$read         = $resource->getConnection("core_write");
			$tPrefix      = ( boolean ) Mage::getconfig()->getTablePrefix();
			$storeId      = Mage::app()->getStore()->getStoreId();
			$rewriteTable = "magentoordercustomer";
			$updateInvite = $read->query("update {$rewriteTable} set status = 2 where uniqueid = '{$code}'");                 
		}
	}
?>
