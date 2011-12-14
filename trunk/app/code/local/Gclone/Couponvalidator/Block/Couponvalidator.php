<?php
class Gclone_Couponvalidator_Block_Couponvalidator extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
            
		return parent::_prepareLayout();
    }
    
     public function getCouponvalidator()     
     { 
        if (!$this->hasData('couponvalidator')) {
            $this->setData('couponvalidator', Mage::registry('couponvalidator'));
        }
        return $this->getData('couponvalidator');
        
    }
}