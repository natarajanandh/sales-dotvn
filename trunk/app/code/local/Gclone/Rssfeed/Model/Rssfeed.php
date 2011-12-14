<?php

class Gclone_Rssfeed_Model_Rssfeed extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('rssfeed/rssfeed');
    }
}