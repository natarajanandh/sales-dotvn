<?php
class Gclone_Dealcoupon_Block_Adminhtml_Dealcoupon extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_dealcoupon';
    $this->_blockGroup = 'dealcoupon';
    $this->_headerText = Mage::helper('dealcoupon')->__('Item Manager');
    $this->_addButtonLabel = Mage::helper('dealcoupon')->__('Add Item');
    parent::__construct();
  }
}