<?php
class Mage_Voucher_IndexController extends Mage_Core_Controller_Front_Action {
    //const XML_PATH_EMAIL_RECIPIENT = 'contacts/email/recipient_email';
    //const XML_PATH_EMAIL_SENDER = 'contacts/email/sender_email_identity';
    //const XML_PATH_EMAIL_TEMPLATE = 'contacts/email/email_template';
   /* const XML_PATH_ENABLED = 'contacts/contacts/enabled';

    public function preDispatch() {
        parent::preDispatch();

        if (!Mage::getStoreConfigFlag(self::XML_PATH_ENABLED)) {
            $this->norouteAction();
        }
    }*/

    public function indexAction() {

	
        $this->loadLayout();
		
        $this->getLayout()->getBlock('voucherForm')
               ->setFormAction(Mage::getUrl('*/*/post'));

        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('catalog/session');
        /* contus */
		
        $this->getLayout()->getBlock('head')->setTitle('Voucher Code');
        $this->renderLayout();
    }

    public function postAction() {
      
		$post = $this->getRequest()->getPost();
		
		if ($post) {
		
			$translate = Mage::getSingleton('core/translate');
			/* @var $translate Mage_Core_Model_Translate */
			$translate->setTranslateInline(false);
			
			try {
				$postObject = new Varien_Object();
				$postObject->setData($post);

				$error = false;
				$code = trim($post['code']);
				$email=trim($post['email']);
				$numbervoucher = trim(substr($post['numvoucher'],2));
				
				if (!Zend_Validate::is($code, 'NotEmpty')) {
					$error = 'Mã voucher không được trống';
				}
				
				if (!Zend_Validate::is($email, 'EmailAddress')) {
                        $error ='sai địa chỉ mail';
                    }

				if (!Zend_Validate::is($numbervoucher, 'NotEmpty')) {
					$error = 'Số voucher không được trống';
				}
				
				if ($error) {
			//	Mage::getSingleton('customer/session')->addError(Mage::helper('voucher')->__(''.$error));
					throw new Exception($error);
				}
				
				// check codevoucher
				$codevoucher = Mage::getModel('voucher/voucher')->getOrderCode($code);
				//echo $codevoucher;					die;
				// check mail and numbervoucher 
				$checkcustomer = Mage::getModel('voucher/voucher')->checkCustomer($email,$numbervoucher);
				//$codevoucher==2 voucher not use
				if($codevoucher==2 && !$checkcustomer){		
				
					//Mage::getModel('voucher/voucher')->updateCodeOrder(trim($post['code']));	
					$data = Mage::getModel('voucher/voucher')->getInformation($email,$numbervoucher);	
				
					Mage::getModel('voucher/voucher')->setData('data', $data);
					Mage::getSingleton('customer/session')->addSuccess(Mage::helper('voucher')->__('Cảm ơn bạn đã sử dụng dịch vụ của chúng tôi.'));					
					
				}elseif($codevoucher==2){				
					Mage::getSingleton('customer/session')->addError(Mage::helper('voucher')->__('Mã Voucher đã được sử dụng'));
				}elseif($codevoucher==3){
					Mage::getSingleton('customer/session')->addError(Mage::helper('voucher')->__('Mã Voucher không tồn tại'));
				}elseif($checkcustomer==1){
					Mage::getSingleton('customer/session')->addError(Mage::helper('voucher')->__('Địa chỉ email này không đúng'));
				}elseif($checkcustomer==2){
					Mage::getSingleton('customer/session')->addError(Mage::helper('voucher')->__('Số voucher Không đúng'));
				}
				
				$translate->setTranslateInline(true);				
				$this->getResponse()->setRedirect(Mage::getUrl('*/*/'));
				//$this->_redirect('*/*/');
				return;
			} catch (Exception $e){		
				$translate->setTranslateInline(true);
				Mage::getSingleton('customer/session')->addError(Mage::helper('voucher')->__($e->getMessage()));
				$this->_redirect('*/*/');
				return;
			}
		} else {
			$this->_redirect('*/*/');
		}       
    }	

}
