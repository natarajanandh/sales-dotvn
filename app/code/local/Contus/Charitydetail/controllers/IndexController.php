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
class Contus_Charitydetail_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
    	
    	/*
    	 * Load an object by id 
    	 * Request looking like:
    	 * http://site.com/charitydetail?id=15 
    	 *  or
    	 * http://site.com/charitydetail/id/15 	
    	 */
    	/* 
		$charitydetail_id = $this->getRequest()->getParam('id');

  		if($charitydetail_id != null && $charitydetail_id != '')	{
			$charitydetail = Mage::getModel('charitydetail/charitydetail')->load($charitydetail_id)->getData();
		} else {
			$charitydetail = null;
		}	
		*/
		
		 /*
    	 * If no param we load a the last created item
    	 */ 
    	/*
    	if($charitydetail == null) {
			$resource = Mage::getSingleton('core/resource');
			$read= $resource->getConnection('core_read');
			$charitydetailTable = $resource->getTableName('charitydetail');
			
			$select = $read->select()
			   ->from($charitydetailTable,array('charitydetail_id','title','content','status'))
			   ->where('status',1)
			   ->order('created_time DESC') ;
			   
			$charitydetail = $read->fetchRow($select);
		}
		Mage::register('charitydetail', $charitydetail);
		*/

			
		$this->loadLayout();     
		$this->renderLayout();
    }
}