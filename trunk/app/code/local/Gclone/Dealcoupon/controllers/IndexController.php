<?php
class Gclone_Dealcoupon_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
    	
    	/*
    	 * Load an object by id 
    	 * Request looking like:
    	 * http://site.com/dealcoupon?id=15 
    	 *  or
    	 * http://site.com/dealcoupon/id/15 	
    	 */
    	/* 
		$dealcoupon_id = $this->getRequest()->getParam('id');

  		if($dealcoupon_id != null && $dealcoupon_id != '')	{
			$dealcoupon = Mage::getModel('dealcoupon/dealcoupon')->load($dealcoupon_id)->getData();
		} else {
			$dealcoupon = null;
		}	
		*/
		
		 /*
    	 * If no param we load a the last created item
    	 */ 
    	/*
    	if($dealcoupon == null) {
			$resource = Mage::getSingleton('core/resource');
			$read= $resource->getConnection('core_read');
			$dealcouponTable = $resource->getTableName('dealcoupon');
			
			$select = $read->select()
			   ->from($dealcouponTable,array('dealcoupon_id','title','content','status'))
			   ->where('status',1)
			   ->order('created_time DESC') ;
			   
			$dealcoupon = $read->fetchRow($select);
		}
		Mage::register('dealcoupon', $dealcoupon);
		*/

			
		$this->loadLayout();     
		$this->renderLayout();
    }
}