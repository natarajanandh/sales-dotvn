<?php

class Gclone_Dealcoupon_Block_Adminhtml_Dealcoupon_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'dealcoupon';
        $this->_controller = 'adminhtml_dealcoupon';
        
        $this->_updateButton('save', 'label', Mage::helper('dealcoupon')->__('Save Item'));
        $this->_updateButton('delete', 'label', Mage::helper('dealcoupon')->__('Delete Item'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('dealcoupon_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'dealcoupon_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'dealcoupon_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('dealcoupon_data') && Mage::registry('dealcoupon_data')->getId() ) {
            return Mage::helper('dealcoupon')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('dealcoupon_data')->getTitle()));
        } else {
            return Mage::helper('dealcoupon')->__('Add Item');
        }
    }
}