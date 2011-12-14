<?php
class Gclone_Couponvalidator_Block_Adminhtml_Couponvalidator extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
     
    $this->_controller = 'adminhtml_couponvalidator';
    $this->_blockGroup = 'couponvalidator';
    //$this->_headerText = Mage::helper('couponvalidator')->__('Item Manager');
    //$this->_addButtonLabel = Mage::helper('couponvalidator')->__('Add Item');
    
    parent::__construct();
    $this->_removeButton('add');
  }
}