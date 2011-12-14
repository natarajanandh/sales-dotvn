<?php
class Gclone_Dealcoupon_Block_Dealcoupon extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getDealcoupon()     
     { 
        if (!$this->hasData('dealcoupon')) {
            $this->setData('dealcoupon', Mage::registry('dealcoupon'));
        }
        return $this->getData('dealcoupon');
        
    }
}