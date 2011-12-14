<?php
/*
 * Created on Apr 23, 2009
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

#include 'Mage/Adminhtml/controllers/Newsletter/SubscriberController.php';

class Gclone_Mailchimp_IndexController extends Mage_Adminhtml_Controller_Action# Mage_Adminhtml_Newsletter_SubscriberController
{

	public function indexAction() {

		#collect all subscribers users
		$collectionarray = Mage::getResourceModel('newsletter/subscriber_collection')
										->showStoreInfo()
										->showCustomerInfo()
										->useOnlySubscribed()
										->toArray();
//echo '<pre>';print_r($collectionarray); die;
		if ( $collectionarray['totalRecords'] > 0 ) {
			#make the call
			Mage::getSingleton('mailchimp/mailchimp')->batchSubscribe($collectionarray['items']);
		}

		$this->_redirect('adminhtml/newsletter_subscriber/');
	}

}

?>
