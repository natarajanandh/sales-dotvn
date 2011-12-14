<?php

class Gclone_Couponvalidator_Block_Adminhtml_Couponvalidator_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('couponvalidator_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('couponvalidator')->__('Item Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('couponvalidator')->__('Item Information'),
          'title'     => Mage::helper('couponvalidator')->__('Item Information'),
          'content'   => $this->getLayout()->createBlock('couponvalidator/adminhtml_couponvalidator_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}