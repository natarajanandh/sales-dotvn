<?php
class Tsdeal_Tsdeal_Block_Tsdeal extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getTsdeal()     
     { 
        if (!$this->hasData('tsdeal')) {
            $this->setData('tsdeal', Mage::registry('tsdeal'));
        }
        return $this->getData('tsdeal');
        
    }
}