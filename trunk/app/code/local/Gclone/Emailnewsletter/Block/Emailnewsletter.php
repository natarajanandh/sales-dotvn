<?php
class Gclone_Emailnewsletter_Block_Emailnewsletter extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getEmailnewsletter()     
     { 
        if (!$this->hasData('emailnewsletter')) {
            $this->setData('emailnewsletter', Mage::registry('emailnewsletter'));
        }
        return $this->getData('emailnewsletter');
        
    }
}