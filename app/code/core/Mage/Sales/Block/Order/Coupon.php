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
 * @package     Mage_Sales
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Order information for print
 *
 * @category   Mage
 * @package    Mage_Sales
 */
class Mage_Sales_Block_Order_Coupon extends Mage_Sales_Block_Items_Abstract {

    protected function _prepareLayout() {
        if ($headBlock = $this->getLayout()->getBlock('head')) {
            $headBlock->setTitle($this->__('Print Order # %s', $this->getOrder()->getRealOrderId()));
        }
        $this->setChild(
                'payment_info',
                $this->helper('payment')->getInfoBlock($this->getOrder()->getPayment())
        );
    }

    public function getPaymentInfoHtml() {
        return $this->getChildHtml('payment_info');
    }

    public function getOrder() {
        return Mage::registry('current_order');
    }

    protected function _prepareItem(Mage_Core_Block_Abstract $renderer) {
        $renderer->setPrintStatus(true);

        return parent::_prepareItem($renderer);
    }

    public function getCouponCode($orderId) {
        $resource = Mage::getSingleton('core/resource');
        $orderTable = $resource->getTableName('ordercustomer');
        $read = $resource->getConnection('read');
        $tprefix = (string) Mage::getConfig()->getTablePrefix();
        $select = $read->select()
                        ->from(array('cp' => $orderTable))
                        ->where('cp.product_id=?', $orderId);

        $orders_list = $read->fetchAll($select);
        return $orders_list;
    }

    public function getProductDetail($productId) {
        $product = Mage::getModel('catalog/product')->load($productId);
        return $product;
    }

    /*
     * To create a PDF document
     * updated @ 02.12.2010
     * @ sathish kumar
     */
    public function getCouponPdf($_order, $no, $identifier) {
        $pdf = Mage::getModel('sales/order_pdf_invoice')->getCouponPdf($_order, $identifier);
        return Mage_Adminhtml_Controller_Action::_prepareDownloadResponse('Coupon #' . $no . '.pdf', $pdf->render(), 'application/pdf');
    }

}

