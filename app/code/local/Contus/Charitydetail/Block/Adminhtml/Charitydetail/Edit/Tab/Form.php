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
class Contus_Charitydetail_Block_Adminhtml_Charitydetail_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('charitydetail_form', array('legend'=>Mage::helper('charitydetail')->__('Item information')));
     
      $fieldset->addField('title', 'text', array(
          'label'     => Mage::helper('charitydetail')->__('Title'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'title',
      ));

      $fieldset->addField('filename', 'file', array(
          'label'     => Mage::helper('charitydetail')->__('Image'),
          'required'  => false,
          'name'      => 'filename',
	  ));
		
      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('charitydetail')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('charitydetail')->__('Enabled'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('charitydetail')->__('Disabled'),
              ),
          ),
      ));
     
      $fieldset->addField('content', 'text', array(
          'name'      => 'content',
          'label'     => Mage::helper('charitydetail')->__('Content Link'),
          'wysiwyg'   => false,
          'required'  => true,
      ));
     
      if ( Mage::getSingleton('adminhtml/session')->getCharitydetailData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getCharitydetailData());
          Mage::getSingleton('adminhtml/session')->setCharitydetailData(null);
      } elseif ( Mage::registry('charitydetail_data') ) {
          $form->setValues(Mage::registry('charitydetail_data')->getData());
      }
      return parent::_prepareForm();
  }
}