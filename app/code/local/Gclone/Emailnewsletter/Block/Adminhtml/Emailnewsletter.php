<?php
class Gclone_Emailnewsletter_Block_Adminhtml_Emailnewsletter extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_emailnewsletter';
    $this->_blockGroup = 'emailnewsletter';
    $this->_headerText = Mage::helper('emailnewsletter')->__('Item Manager');
    $this->_addButtonLabel = Mage::helper('emailnewsletter')->__('Add Item');
    parent::__construct();
  }
}