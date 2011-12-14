<?php

class Gclone_Couponvalidator_Model_Mysql4_Couponvalidator_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('couponvalidator/couponvalidator');
    }
}