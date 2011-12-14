<?php

class Gclone_Dealcoupon_Model_Mysql4_Dealcoupon extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the dealcoupon_id refers to the key field in your database table.
        $this->_init('dealcoupon/dealcoupon', 'dealcoupon_id');
    }
}