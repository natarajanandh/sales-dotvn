<?php

class Gclone_Emailnewsletter_Model_Mysql4_Emailnewsletter_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('emailnewsletter/emailnewsletter');
    }
}