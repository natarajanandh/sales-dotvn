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

/* Contus support
 * Created on Feb 28, 2011
 * Author @ Sathish kumar
 */

#include 'Mage/Adminhtml/controllers/Newsletter/SubscriberController.php';

class Gclone_Constantcontact_IndexController extends Mage_Adminhtml_Controller_Action# Mage_Adminhtml_Newsletter_SubscriberController
{

	public function indexAction() {

		#collect all subscribers users
		$collectionarray = Mage::getResourceModel('newsletter/subscriber_collection')
										->showStoreInfo()
										->showCustomerInfo()
										->useOnlySubscribed()
										->toArray();

		if ( $collectionarray['totalRecords'] > 0 ) {
			#make the call
			Mage::getSingleton('constantcontact/constantcontact')->batchSubscribe($collectionarray['items']);
		}

		$this->_redirect('adminhtml/newsletter_subscriber/');
	}

}

?>
