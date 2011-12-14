<?php

class Gclone_Rssfeed_Model_Mysql4_Rssfeed_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('rssfeed/rssfeed');
    }
}