<?php
class Gclone_Couponvalidator_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
    	
    	/*
    	 * Load an object by id 
    	 * Request looking like:
    	 * http://site.com/couponvalidator?id=15 
    	 *  or
    	 * http://site.com/couponvalidator/id/15 	
    	 */
    	/* 
		$couponvalidator_id = $this->getRequest()->getParam('id');

  		if($couponvalidator_id != null && $couponvalidator_id != '')	{
			$couponvalidator = Mage::getModel('couponvalidator/couponvalidator')->load($couponvalidator_id)->getData();
		} else {
			$couponvalidator = null;
		}	
		*/
		
		 /*
    	 * If no param we load a the last created item
    	 */ 
    	/*
    	if($couponvalidator == null) {
			$resource = Mage::getSingleton('core/resource');
			$read= $resource->getConnection('core_read');
			$couponvalidatorTable = $resource->getTableName('couponvalidator');
			
			$select = $read->select()
			   ->from($couponvalidatorTable,array('couponvalidator_id','title','content','status'))
			   ->where('status',1)
			   ->order('created_time DESC') ;
			   
			$couponvalidator = $read->fetchRow($select);
		}
		Mage::register('couponvalidator', $couponvalidator);
		*/

			
		$this->loadLayout();     
		$this->renderLayout();
    }
     public function updateAction()
    {
         $update = Mage::app()->getRequest()->getParam('update');
        if(isset($update)){
            $currentTime = Mage_Core_Model_Locale::date(null, null, "en_US", true);
            $updatedDate = strtotime($currentTime);
            $date = date("Y-m-d, H:i:s", $updatedDate);
            $resource = Mage::getSingleton('core/resource');
            $couponUpdate = $resource->getConnection('core_write');
            $tprefix = (string)Mage::getConfig()->getTablePrefix();
            $statusUpdate = $couponUpdate->query("update ".$tprefix."ordercustomer set status = '2', updated_on='$date' where ordercustomer_id = '$update'" );

        }
        $this->_redirectReferer();
     }
}