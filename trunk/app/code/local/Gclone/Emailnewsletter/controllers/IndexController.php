<?php
class Gclone_Emailnewsletter_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
    	
    	/*
    	 * Load an object by id 
    	 * Request looking like:
    	 * http://site.com/emailnewsletter?id=15 
    	 *  or
    	 * http://site.com/emailnewsletter/id/15 	
    	 */
    	/* 
		$emailnewsletter_id = $this->getRequest()->getParam('id');

  		if($emailnewsletter_id != null && $emailnewsletter_id != '')	{
			$emailnewsletter = Mage::getModel('emailnewsletter/emailnewsletter')->load($emailnewsletter_id)->getData();
		} else {
			$emailnewsletter = null;
		}	
		*/
		
		 /*
    	 * If no param we load a the last created item
    	 */ 
    	/*
    	if($emailnewsletter == null) {
			$resource = Mage::getSingleton('core/resource');
			$read= $resource->getConnection('core_read');
			$emailnewsletterTable = $resource->getTableName('emailnewsletter');
			
			$select = $read->select()
			   ->from($emailnewsletterTable,array('emailnewsletter_id','title','content','status'))
			   ->where('status',1)
			   ->order('created_time DESC') ;
			   
			$emailnewsletter = $read->fetchRow($select);
		}
		Mage::register('emailnewsletter', $emailnewsletter);
		*/

			
		$this->loadLayout();     
		$this->renderLayout();
    }
}