<?php
/*
 * Created on Apr 1, 2009
 */

class Gclone_MailChimp_Model_MailChimp_Mailchimp extends Varien_Object
{

	public function getXMLGeneralConfig($field) {
		return Mage::getStoreConfig('mailchimp/general/'.$field);
	}

	public function newMailChimpHost($apikey){

		list($key, $dc) = explode('-',$apikey,2);
		if (!$dc) $dc = 'us1';
		list($aux, $host) = explode('http://',$this->getXMLGeneralConfig('url'));
		$api_host = 'http://'.$dc.'.'.$host;

		return $api_host;
	}

	private function getXMLSubscribeConfig($field) {
		return Mage::getStoreConfig('mailchimp/subscribe/'.$field);
	}


	private function getXMLUnsubscribeConfig($field) {
		return Mage::getStoreConfig('mailchimp/unsubscribe/'.$field);
	}

	private function mailChimpAvailable() {
		if (  	$this->getXMLGeneralConfig('active') == true &&
				$this->getXMLGeneralConfig('apikey') != '' &&
				$this->getXMLGeneralConfig('listid') != ''
				&&
				    (
				    strstr($_SERVER['REQUEST_URI'], 'newsletter/') ||
				    strstr($_SERVER['REQUEST_URI'], 'newsletter_subscriber/') ||
				    strstr($_SERVER['REQUEST_URI'], 'customer/') ||
				    strstr($_SERVER['REQUEST_URI'], 'mailchimp/index') ||
					strstr($_SERVER['REQUEST_URI'], 'checkout/onepage/')
				    )
				) {
			return true;
		}
		if (Mage::app()->getStore()->getId() == 0){
			if($this->getXMLGeneralConfig('active') != true) Mage::getSingleton('adminhtml/session')->addError('MailChimp Configuration Error: Mail Chimp is innactive');
			if($this->getXMLGeneralConfig('apikey') == '' ) Mage::getSingleton('adminhtml/session')->addError('MailChimp Configuration Error: API Key field is empty');
			if($this->getXMLGeneralConfig('listid') == '' ) Mage::getSingleton('adminhtml/session')->addError('MailChimp Configuration Error: Mail Chimp list field is empty');
		}
		return false;
	}

    private function getCustomerByEmail($email)
    {
			if (($email instanceof Mage_Customer_Model_Customer)) {

           		 $customer = $email;

            	return $customer;
        	}

			$collection = Mage::getResourceModel('newsletter/subscriber_collection');
            $collection
	            ->showCustomerInfo(true)
	            ->addSubscriberTypeField()
	            ->showStoreInfo()
	            ->addFieldToFilter('subscriber_email',$email);

			return $collection->getFirstItem();
    }

	private function getListIdByStoreId($storeId)
	{
		$store = Mage::getModel('core/store')->load($storeId);
		$list_id = $store->getConfig('mailchimp/general/listid');

		return $list_id;

	}

	public function subscribe($email) {
            
		if ( $this->mailChimpAvailable() ) {
			$customer = $this->getCustomerByEmail($email);
			$customerOldMail = $this->getCustomerOldEmail();

			$merge_vars = array();

			if (($email instanceof Mage_Customer_Model_Customer)) {

					$email = $customer->getEmail();

					$merge_vars['FNAME'] = $customer->getFirstname();
					$merge_vars['LNAME'] = $customer->getLastname();

			}elseif ($customer->getCustomerId() !=0 ) {
				$merge_vars['FNAME'] = $customer->getCustomerFirstname();
				$merge_vars['LNAME'] = $customer->getCustomerLastname();
			} else {
				$merge_vars['FNAME'] = 'Guest';
				$merge_vars['LNAME'] = 'Guest';
			}

			if ( $this->getXMLSubscribeConfig('interests') != '' ) {
				$merge_vars['INTERESTS'] = $this->getXMLSubscribeConfig('interests');
			}

			if ( $this->getXMLSubscribeConfig('opt-in') != '' ) {
				$merge_vars['OPTINIP'] = $this->getXMLSubscribeConfig('opt-in');
			}

			try {
				$apikey = $this->getXMLGeneralConfig('apikey');

				$api_host = $this->newMailChimpHost($apikey);
	 			$client = new Zend_XmlRpc_Client($api_host);

				$lists = $client->call('lists', $apikey);

				$listId = $this->getListIdByStoreId($customer->getStoreId());
				foreach( $lists as $weblist ) {
					if ( $weblist['web_id'] == $listId) {
						$listId = $weblist['id'];
					}
				}

				if ( $listId != '') {

					if((isset($customerOldMail) && $customerOldMail !='')  && ($email != $customerOldMail)) {

						$response =  $client->call('listUnsubscribe', array($apikey, $listId, $customerOldMail,true));
						unset($_SESSION['customer_old_email']);

					}

					$response = $client->call('listSubscribe', array($apikey, $listId, $email, $merge_vars,
										$this->getXMLSubscribeConfig('email_type'), $this->getXMLSubscribeConfig('double_optin'), $this->getXMLSubscribeConfig('update_existing') ));


					if ( $response == false ) {
						Mage::getSingleton('adminhtml/session')->addError('Mail Chimp error');
					}
				} else {
					Mage::getSingleton('adminhtml/session')->addError('Your MailChimp List id: '. $listId .' is wrong or does not exist');
				}

			} catch ( exception $e ) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}

		}
	}

	public function unsubscribe($email) { 
		if ( $this->mailChimpAvailable() ) {

			try {

				$apikey = $this->getXMLGeneralConfig('apikey');

				$api_host = $this->newMailChimpHost($apikey);
	 			$client = new Zend_XmlRpc_Client($api_host);

				$lists = $client->call('lists', $apikey);

				$customer = $this->getCustomerByEmail($email);

				if (($email instanceof Mage_Customer_Model_Customer)) {

					$email = $customer->getEmail();
				}

				$listId = $this->getListIdByStoreId($customer->getStoreId());

				foreach( $lists as $weblist ) {
					if ( $weblist['web_id'] == $listId) {
						$listId = $weblist['id'];
					}
				}

				if ( $listId != '' ) {

					try {
						$aux = $client->call('listMemberInfo', array($apikey, $listId, $email));
					}catch ( exception $e)
					{
						return;
					}
					if($aux['status'] != 'unsubscribed') {
						$response = $client->call('listUnsubscribe', array($apikey, $listId, $email,
										(bool)$this->getXMLUnsubscribeConfig('delete_member'), $this->getXMLUnsubscribeConfig('send_goodbye')
										, $this->getXMLUnsubscribeConfig('send_notify') ));

						if ( $response == false ) {
							Mage::getSingleton('adminhtml/session')->addError('Mail Chimp error');
						}
					}
				} else {
					Mage::getSingleton('adminhtml/session')->addError('Your MailChimp List id '. $listId .' is wrong or does not exist');
				}
			} catch ( exception $e ) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
		}
	}
	public function batchSubscribe($items) { 

		if ( $this->mailChimpAvailable() ) {
			$batch = array();
			$customerInList = array();
			foreach($items as $item) {

				$merge_vars = array();

				if($item['customer_id'] !=0) {
					$merge_vars['FNAME'] = $item['customer_firstname'];
					$merge_vars['LNAME'] = $item['customer_lastname'];


				} else {
					$merge_vars['FNAME'] = 'Guest';
					$merge_vars['LNAME'] = 'Guest';
				}

				$merge_vars['EMAIL'] = $item['subscriber_email'];

				if ( $this->getXMLSubscribeConfig('interests') != '' ) {
					$merge_vars['INTERESTS'] = $this->getXMLSubscribeConfig('interests');
				}
                                //$merge_vars['FOLLOWING'] = $item['subscriber_city'];
				//$batch[] = $merge_vars;
				$customerInList[$item['store_id']][]= $merge_vars;

			}

	 		try {
				$apikey = $this->getXMLGeneralConfig('apikey');
	            if(substr($apikey, -4) != '-us1' && substr($apikey, -4) != '-us2'){
    	        	Mage::getSingleton('adminhtml/session')->addError('MailChimp Configuration Error: The API key is not well formed');
					return false;
            	}
				$api_host = $this->newMailChimpHost($apikey);

	 			$client = new Zend_XmlRpc_Client($api_host);
				$lists = $client->call('lists', $apikey);
				$success_count = 0;

				$error = false;

				foreach ($customerInList as $store => $customers)
				{
					$listId = $this->getListIdByStoreId($store);

					foreach( $lists as $weblist ) {
						if ( $weblist['web_id'] == $listId) {
							$listId = $weblist['id'];
						}
					}

					if ( $listId != '' ) {
						$response = $client->call('listBatchSubscribe', array($apikey, $listId, $customers,
										$this->getXMLSubscribeConfig('double_optin'), $this->getXMLSubscribeConfig('update_existing')
										 ));

						$success_count = $success_count + $response['success_count'];

						if ($response == false) {
							Mage::throwException('Mail Chimp error');
							break;
						}elseif (($response['error_count'] > 0) || (count($response['errors']) > 0)) {
							$error = true;
							break;
						}
					} else {
						Mage::getSingleton('adminhtml/session')->addError('Your MailChimp List id '. $listId .' is wrong or does not exist');
					}
				}

				if(!$error)
				{
					Mage::getSingleton('adminhtml/session')->addSuccess($success_count.' was success added to Mail Chimp');
				}else
				{
					if($success_count >0) Mage::getSingleton('adminhtml/session')->addSuccess($success_count.' was success added to Mail Chimp');

					if ($response['error_count'] > 0 ) {
						Mage::getSingleton('adminhtml/session')->addError('Qty of errors: '.$response['error_count']);
					}
					if (isset($response['errors']) && count($response['errors']) > 0 ) {
						foreach( $response['errors'] as $error ) {
							Mage::getSingleton('adminhtml/session')->addError($error['code'].': '.$error['message']);
						}
					}
				}
			} catch ( exception $e ) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
	 	}
	}

	public function getCustomerOldEmail()
    {
        if(isset($_SESSION['customer_old_email']))
   		 {
            $customer_old_email = $_SESSION['customer_old_email'];
            return $customer_old_email;
	    }else
	    {
            return '';

	    }

    }
}
?>
