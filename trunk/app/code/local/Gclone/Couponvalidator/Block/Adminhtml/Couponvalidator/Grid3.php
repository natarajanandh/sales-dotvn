<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Report Sold Products Grid Block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Gclone_Couponvalidator_Block_Adminhtml_Couponvalidator_Grid extends Gclone_Couponvalidator_Block_Adminhtml_Couponvalidator_Griding
{
    /**
     * Sub report size
     *
     * @var int
     */
    protected $_subReportSize = 0;

    /**
     * Initialize Grid settings
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('gridDealsSold');
    }

    /**
     * Prepare collection object for grid
     *
     * @return Mage_Adminhtml_Block_Report_Product_Sold_Grid
     */
    protected function _prepareCollection()
    {
        parent::_prepareCollection();
        //        $this->getCollection()
        //            ->initReport('reports/deal_sold_collection');
        return $this;
    }

    /**
     * Prepare Grid columns
     *
     * @return Mage_Adminhtml_Block_Report_Product_Sold_Grid
     */
    protected function _prepareColumns()
    {
//        $this->addColumn('ordercustomer_id', array(
//          'header'    => Mage::helper('reports')->__('ID'),
//          'align'     =>'right',
//          'width'     => '50px',
//          'index'     => 'ordercustomer_id',
//      ));

      $this->addColumn('increment_id', array(
			'header'    => Mage::helper('reports')->__('Order Id'),
			'width'     => '120px',
			'index'     => 'increment_id',
      ));

      $this->addColumn('customer_name', array(
          'header'    => Mage::helper('reports')->__('Customer Name'),
          'align'     =>'left',
          'index'     => 'customer_name',
      ));


       $this->addColumn('product_name', array(
          'header'    => Mage::helper('reports')->__('Product name'),
          'align'     =>'left',
          'index'     => 'product_name',
      ));

	  $this->addColumn('quantity', array(
          'header'    => Mage::helper('reports')->__('Quantity'),
          'align'     =>'left',
              'width'     => '90px',
          'index'     => 'quantity',
      ));

	  $this->addColumn('quantitynumber', array(
          'header'    => Mage::helper('reports')->__('Coupon Number#'),
          'align'     =>'left',
          'index'     => 'quantitynumber',
      ));

	  $this->addColumn('uniqueid', array(
          'header'    => Mage::helper('reports')->__('Coupon Code'),
          'align'     =>'left',
          'index'     => 'uniqueid',
      ));

	   $this->addColumn('created_time', array(
          'header'    => Mage::helper('reports')->__('Created Date'),
          'align'     =>'left',
          'type' => 'datetime',
               'width'     => '170px',
          'index'     => 'created_time',
      ));

            $this->addColumn('status', array(
          'header'    => Mage::helper('reports')->__('Status'),
          'align'     =>'left',
          
               'width'     => '170px',
          'index'     => 'status',
      ));
//           $this->addColumn('payment_actions', array(
//                'header'    => Mage::helper('reports')->__('Payment Status'),
//                'width'     => '10px',
//                'sortable'  => false,
//                'filter'    => false,
//                'renderer'  => 'adminhtml/catalog_product_grid_renderer_action',
//        ));

         

//        $this->addExportType('*/*/exportSoldCsv', Mage::helper('reports')->__('CSV'));
//        $this->addExportType('*/*/exportSoldExcel', Mage::helper('reports')->__('Excel'));

        return parent::_prepareColumns();
    }
//protected function _prepareMassaction() {
//        $this->setMassactionIdField('increment_id');
//        $this->getMassactionBlock()->setFormFieldName('ordercustomer');
//
//        $this->getMassactionBlock()->addItem('delete', array(
//                'label'=> Mage::helper('catalog')->__('Delete'),
//                'url'  => $this->getUrl('*/*/massDelete'),
//                'confirm' => Mage::helper('catalog')->__('Are you sure?')
//        ));
//
//        $statuses = Mage::getSingleton('ordercustomer/status')->getOptionArray();
//print_r($statuses);
//        array_unshift($statuses, array('label'=>'', 'value'=>''));
//        $this->getMassactionBlock()->addItem('status', array(
//                'label'=> Mage::helper('catalog')->__('Change status'),
//                'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
//                'additional' => array(
//                        'visibility' => array(
//                                'name' => 'status',
//                                'type' => 'select',
//                                'class' => 'required-entry',
//                                'label' => Mage::helper('catalog')->__('Status'),
//                                'values' => $statuses
//                        )
//                )
//        ));
//
//
//
//        return $this;
//    }


    
    
}
