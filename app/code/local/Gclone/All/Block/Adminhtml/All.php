<?php
/**
 * Contus Support Interactive.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file GCLONE-LICENSE.txt.
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento 1.4.1.1 COMMUNITY edition
 * Contus Support does not guarantee correct work of this package
 * on any other Magento edition except Magento 1.4.1.1 COMMUNITY edition.
 * =================================================================
 */

class Gclone_All_Block_Adminhtml_All extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_all';
    $this->_blockGroup = 'all';
    $this->_headerText = Mage::helper('all')->__('Item Manager');
    $this->_addButtonLabel = Mage::helper('all')->__('Add Item');
    parent::__construct();
  }
}