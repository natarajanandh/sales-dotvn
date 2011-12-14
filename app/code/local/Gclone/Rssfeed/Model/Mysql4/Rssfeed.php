<?php

class Gclone_Rssfeed_Model_Mysql4_Rssfeed extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the rssfeed_id refers to the key field in your database table.
        $this->_init('rssfeed/rssfeed', 'rssfeed_id');
    }
}