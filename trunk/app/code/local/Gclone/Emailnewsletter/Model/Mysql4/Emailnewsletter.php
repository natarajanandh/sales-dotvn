<?php

class Gclone_Emailnewsletter_Model_Mysql4_Emailnewsletter extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the emailnewsletter_id refers to the key field in your database table.
        $this->_init('emailnewsletter/emailnewsletter', 'emailnewsletter_id');
    }
}