<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Gclone_Mailchimp_Model_Mailchimp_Listids
{
    protected $_options;
    protected $_lists = array();

	protected function getMailChimp()
	{
		return Mage::getModel('mailchimp/mailchimp');
	}

    public function toOptionArray($isMultiselect=false)
    {
        if (!$this->_options) {

			$client = new Zend_XmlRpc_Client($this->getMailChimp()->getXMLGeneralConfig('url'));
			$apikey = $this->getMailChimp()->getXMLGeneralConfig('apikey');

            if(!$apikey) return '';
            if(substr($apikey, -4) != '-us1' && substr($apikey, -4) != '-us2'){
            	Mage::getSingleton('adminhtml/session')->addError('The API key is not well formed');
            	return '';
            }

			$api_host = $this->getMailChimp()->newMailChimpHost($apikey);
	 		$client = new Zend_XmlRpc_Client($api_host);

			$lists = '';
			try {
			    $lists = $client->call('lists', $apikey);
			} catch( Exception $e ) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}

			if(!is_array($lists)) return '';

			foreach ($lists as $list)
			{
				$this->_lists[] = array('value'=>$list['web_id'],
										'label'=>$list['name']);
			}

            $this->_options = $this->_lists;
        }

        $options = $this->_options;
        if(!$isMultiselect){
            array_unshift($options, array('value'=>'', 'label'=> Mage::helper('adminhtml')->__('--Please Select--')));
        }
		return $options;
    }
}