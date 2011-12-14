<?php

class Gclone_Dealcoupon_Model_Mysql4_Dealcoupon_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('dealcoupon/dealcoupon');
    }
}