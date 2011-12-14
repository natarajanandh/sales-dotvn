<?php
class Gclone_Rssfeed_Block_Upcoming extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getRssfeed()     
     { 
        if (!$this->hasData('rssfeed')) {
            $this->setData('rssfeed', Mage::registry('rssfeed'));
        }
        return $this->getData('rssfeed');
        
    }
}