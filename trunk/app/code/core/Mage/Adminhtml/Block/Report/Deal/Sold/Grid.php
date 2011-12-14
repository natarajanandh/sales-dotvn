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
class Mage_Adminhtml_Block_Report_Deal_Sold_Grid extends Mage_Adminhtml_Block_Report_Griding
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
        $this->addColumn('end_date', array(
            'header'    =>Mage::helper('reports')->__('End Date'),
            'align'     =>'right',
            'index'     =>'end_date',
            'type'      =>'number'
            ));
         $this->addColumn('name', array(
            'header'    =>Mage::helper('reports')->__('Deal Name'),
            'align'     =>'right',
            'width'     =>'320px',
            'index'     =>'name',
            'type'      =>'number'
            ));
        $this->addColumn('deal_status', array(
            'header'    =>Mage::helper('reports')->__('Deal Achieved'),
            'index'     =>'deal_status',
            'type'      =>'number'
            ));
        $this->addColumn('target', array(
            'header'    =>Mage::helper('reports')->__('Target'),
            'index'     =>'target',
            'type'      =>'number'
            ));
        $this->addColumn('qty_purchased', array(
            'header'    =>Mage::helper('reports')->__('Qty Purchased'),          
            'index'     =>'qty_purchased',
            'type'      =>'number'
            ));
        $this->addColumn('price', array(
            'header'    =>Mage::helper('reports')->__('Price'),          
            'index'     =>'price',
            'type'      =>'number'
            ));
        $this->addColumn('city', array(
            'header'    =>Mage::helper('reports')->__('City'), 
            'index'     =>'city',
            'type'      =>'number'
            ));

        $this->addExportType('*/*/exportSoldCsv', Mage::helper('reports')->__('CSV'));
        $this->addExportType('*/*/exportSoldExcel', Mage::helper('reports')->__('Excel'));

        return parent::_prepareColumns();
    }
}
