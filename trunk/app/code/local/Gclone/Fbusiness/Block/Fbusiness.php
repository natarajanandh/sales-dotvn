<?php
class Gclone_Fbusiness_Block_Fbusiness extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getFbusiness()     
     { 
        if (!$this->hasData('fbusiness')) {
            $this->setData('fbusiness', Mage::registry('fbusiness'));
        }
        return $this->getData('fbusiness');
        
    }
}