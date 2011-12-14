<?php

class Gclone_Couponvalidator_Model_Mysql4_Couponvalidator extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the couponvalidator_id refers to the key field in your database table.
        $this->_init('couponvalidator/couponvalidator', 'couponvalidator_id');
    }
}