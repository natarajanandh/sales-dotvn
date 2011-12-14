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

class Gclone_Constantcontact_Block_Adminhtml_Newsletter_Subscriber extends Mage_Adminhtml_Block_Newsletter_Subscriber
{

	public function __construct()
	{ 
		$this->setTemplate('newsletter/subscriber/list_contacts.phtml');
	}

	public function getConstantcontactSyn()
	{
		return $this->getUrl('constantcontact/index/index');
	}

}

?>
