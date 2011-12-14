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
class Contus_Background_Block_Adminhtml_Background_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('background_form', array('legend'=>Mage::helper('background')->__('Item information')));

      $fieldset->addField('title', 'text', array(
          'label'     => Mage::helper('background')->__('Name'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'title',
      ));

      $fieldset->addField('filename', 'file', array(
          'label'     => Mage::helper('background')->__('Image'),
          'required'  => false,
          'name'      => 'filename',
	  ));

      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('background')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('background')->__('Enabled'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('background')->__('Disabled'),
              ),
          ),
      ));

//      $fieldset->addField('content', 'editor', array(
//          'name'      => 'content',
//          'label'     => Mage::helper('background')->__('Content'),
//          'title'     => Mage::helper('background')->__('Content'),
//          'style'     => 'width:700px; height:500px;',
//          'wysiwyg'   => false,
//          'required'  => true,
//      ));

      if ( Mage::getSingleton('adminhtml/session')->getBackgroundData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getBackgroundData());
          Mage::getSingleton('adminhtml/session')->setBackgroundData(null);
      } elseif ( Mage::registry('background_data') ) {
          $form->setValues(Mage::registry('background_data')->getData());
      }
      return parent::_prepareForm();
  }
}