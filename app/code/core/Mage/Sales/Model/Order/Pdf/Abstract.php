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
 * Sales Order PDF abstract model
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author     Magento Core Team <core@magentocommerce.com>
 */
abstract class Mage_Sales_Model_Order_Pdf_Abstract extends Varien_Object {

    public $y;
    /**
     * Item renderers with render type key
     *
     * model    => the model name
     * renderer => the renderer model
     *
     * @var array
     */
    protected $_renderers = array();

    const XML_PATH_SALES_PDF_INVOICE_PUT_ORDER_ID = 'sales_pdf/invoice/put_order_id';
    const XML_PATH_SALES_PDF_SHIPMENT_PUT_ORDER_ID = 'sales_pdf/shipment/put_order_id';
    const XML_PATH_SALES_PDF_CREDITMEMO_PUT_ORDER_ID = 'sales_pdf/creditmemo/put_order_id';

    /**
     * Zend PDF object
     *
     * @var Zend_Pdf
     */
    protected $_pdf;
    protected $_defaultTotalModel = 'sales/order_pdf_total_default';

    /**
     * Retrieve PDF
     *
     * @return Zend_Pdf
     */
    abstract public function getPdf();

    /**
     * Returns the total width in points of the string using the specified font and
     * size.
     *
     * This is not the most efficient way to perform this calculation. I'm
     * concentrating optimization efforts on the upcoming layout manager class.
     * Similar calculations exist inside the layout manager class, but widths are
     * generally calculated only after determining line fragments.
     *
     * @param string $string
     * @param Zend_Pdf_Resource_Font $font
     * @param float $fontSize Font size in points
     * @return float
     */
    public function widthForStringUsingFontSize($string, $font, $fontSize) {
        $drawingString = '"libiconv"' == ICONV_IMPL ? iconv('UTF-8', 'UTF-16BE//IGNORE', $string) : @iconv('UTF-8', 'UTF-16BE', $string);

        $characters = array();
        for ($i = 0; $i < strlen($drawingString); $i++) {
            $characters[] = (ord($drawingString[$i++]) << 8) | ord($drawingString[$i]);
        }
        $glyphs = $font->glyphNumbersForCharacters($characters);
        $widths = $font->widthsForGlyphs($glyphs);
        $stringWidth = (array_sum($widths) / $font->getUnitsPerEm()) * $fontSize;
        return $stringWidth;
    }

    /**
     * Calculate coordinates to draw something in a column aligned to the right
     *
     * @param string $string
     * @param int $x
     * @param int $columnWidth
     * @param Zend_Pdf_Resource_Font $font
     * @param int $fontSize
     * @param int $padding
     * @return int
     */
    public function getAlignRight($string, $x, $columnWidth, Zend_Pdf_Resource_Font $font, $fontSize, $padding = 5) {
        $width = $this->widthForStringUsingFontSize($string, $font, $fontSize);
        return $x + $columnWidth - $width - $padding;
    }

    /**
     * Calculate coordinates to draw something in a column aligned to the center
     *
     * @param string $string
     * @param int $x
     * @param int $columnWidth
     * @param Zend_Pdf_Resource_Font $font
     * @param int $fontSize
     * @return int
     */
    public function getAlignCenter($string, $x, $columnWidth, Zend_Pdf_Resource_Font $font, $fontSize) {
        $width = $this->widthForStringUsingFontSize($string, $font, $fontSize);
        return $x + round(($columnWidth - $width) / 2);
    }

    protected function insertLogo(&$page, $store = null) {
        $image = Mage::getStoreConfig('sales/identity/logo', $store);
        if ($image) {
            $image = Mage::getStoreConfig('system/filesystem/media', $store) . '/sales/store/logo/' . $image;
            if (is_file($image)) {
                $image = Zend_Pdf_Image::imageWithPath($image);
                $page->drawImage($image, 25, 800, 125, 825);
            }
        }
        //return $page;
    }

    protected function insertAddress(&$page, $store = null) {
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $this->_setFontRegular($page, 5);

        $page->setLineWidth(0.5);
        $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
        $page->drawLine(125, 825, 125, 790);

        $page->setLineWidth(0);
        $this->y = 820;
        foreach (explode("\n", Mage::getStoreConfig('sales/identity/address', $store)) as $value) {
            if ($value !== '') {
                $page->drawText(trim(strip_tags($value)), 130, $this->y, 'UTF-8');
                $this->y -=7;
            }
        }
        //return $page;
    }

    /**
     * Format address
     *
     * @param string $address
     * @return array
     */
    protected function _formatAddress($address) {
        $return = array();
        foreach (explode('|', $address) as $str) {
            foreach (Mage::helper('core/string')->str_split($str, 65, true, true) as $part) {
                if (empty($part)) {
                    continue;
                }
                $return[] = $part;
            }
        }
        return $return;
    }

    protected function insertOrder(&$page, $order, $putOrderId = true) {
        /* @var $order Mage_Sales_Model_Order */
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0.5));

        $page->drawRectangle(25, 790, 570, 755);

        $page->setFillColor(new Zend_Pdf_Color_GrayScale(1));
        $this->_setFontRegular($page);


        if ($putOrderId) {
            $page->drawText(Mage::helper('sales')->__('Order # ') . $order->getRealOrderId(), 35, 770, 'UTF-8');
        }
        //$page->drawText(Mage::helper('sales')->__('Order Date: ') . date( 'D M j Y', strtotime( $order->getCreatedAt() ) ), 35, 760, 'UTF-8');
        $page->drawText(Mage::helper('sales')->__('Order Date: ') . Mage::helper('core')->formatDate($order->getCreatedAtStoreDate(), 'medium', false), 35, 760, 'UTF-8');

        $page->setFillColor(new Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, 755, 275, 730);
        $page->drawRectangle(275, 755, 570, 730);

        /* Calculate blocks info */

        /* Billing Address */
        $billingAddress = $this->_formatAddress($order->getBillingAddress()->format('pdf'));

        /* Payment */
        $paymentInfo = Mage::helper('payment')->getInfoBlock($order->getPayment())
                        ->setIsSecureMode(true)
                        ->toPdf();
        $payment = explode('{{pdf_row_separator}}', $paymentInfo);
        foreach ($payment as $key => $value) {
            if (strip_tags(trim($value)) == '') {
                unset($payment[$key]);
            }
        }
        reset($payment);

        /* Shipping Address and Method */
        if (!$order->getIsVirtual()) {
            /* Shipping Address */
            $shippingAddress = $this->_formatAddress($order->getShippingAddress()->format('pdf'));

            $shippingMethod = $order->getShippingDescription();
        }

        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $this->_setFontRegular($page);
        $page->drawText(Mage::helper('sales')->__('SOLD TO:'), 35, 740, 'UTF-8');

        if (!$order->getIsVirtual()) {
            $page->drawText(Mage::helper('sales')->__('SHIP TO:'), 285, 740, 'UTF-8');
        } else {
            $page->drawText(Mage::helper('sales')->__('Payment Method:'), 285, 740, 'UTF-8');
        }

        if (!$order->getIsVirtual()) {
            $y = 730 - (max(count($billingAddress), count($shippingAddress)) * 10 + 5);
        } else {
            $y = 730 - (count($billingAddress) * 10 + 5);
        }

        $page->setFillColor(new Zend_Pdf_Color_GrayScale(1));
        $page->drawRectangle(25, 730, 570, $y);
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $this->_setFontRegular($page);
        $this->y = 720;

        foreach ($billingAddress as $value) {
            if ($value !== '') {
                $page->drawText(strip_tags(ltrim($value)), 35, $this->y, 'UTF-8');
                $this->y -=10;
            }
        }

        if (!$order->getIsVirtual()) {
            $this->y = 720;
            foreach ($shippingAddress as $value) {
                if ($value !== '') {
                    $page->drawText(strip_tags(ltrim($value)), 285, $this->y, 'UTF-8');
                    $this->y -=10;
                }
            }

            $page->setFillColor(new Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
            $page->setLineWidth(0.5);
            $page->drawRectangle(25, $this->y, 275, $this->y - 25);
            $page->drawRectangle(275, $this->y, 570, $this->y - 25);

            $this->y -=15;
            $this->_setFontBold($page);
            $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
            $page->drawText(Mage::helper('sales')->__('Payment Method'), 35, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('Shipping Method:'), 285, $this->y, 'UTF-8');

            $this->y -=10;
            $page->setFillColor(new Zend_Pdf_Color_GrayScale(1));

            $this->_setFontRegular($page);
            $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));

            $paymentLeft = 35;
            $yPayments = $this->y - 15;
        } else {
            $yPayments = 720;
            $paymentLeft = 285;
        }

        foreach ($payment as $value) {
            if (trim($value) !== '') {
                $page->drawText(strip_tags(trim($value)), $paymentLeft, $yPayments, 'UTF-8');
                $yPayments -=10;
            }
        }

        if (!$order->getIsVirtual()) {
            $this->y -=15;

            $page->drawText($shippingMethod, 285, $this->y, 'UTF-8');

            $yShipments = $this->y;


            $totalShippingChargesText = "(" . Mage::helper('sales')->__('Total Shipping Charges') . " " . $order->formatPriceTxt($order->getShippingAmount()) . ")";

            $page->drawText($totalShippingChargesText, 285, $yShipments - 7, 'UTF-8');
            $yShipments -=10;
            $tracks = $order->getTracksCollection();
            if (count($tracks)) {
                $page->setFillColor(new Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
                $page->setLineWidth(0.5);
                $page->drawRectangle(285, $yShipments, 510, $yShipments - 10);
                $page->drawLine(380, $yShipments, 380, $yShipments - 10);
                //$page->drawLine(510, $yShipments, 510, $yShipments - 10);

                $this->_setFontRegular($page);
                $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
                //$page->drawText(Mage::helper('sales')->__('Carrier'), 290, $yShipments - 7 , 'UTF-8');
                $page->drawText(Mage::helper('sales')->__('Title'), 290, $yShipments - 7, 'UTF-8');
                $page->drawText(Mage::helper('sales')->__('Number'), 385, $yShipments - 7, 'UTF-8');

                $yShipments -=17;
                $this->_setFontRegular($page, 6);
                foreach ($order->getTracksCollection() as $track) {

                    $CarrierCode = $track->getCarrierCode();
                    if ($CarrierCode != 'custom') {
                        $carrier = Mage::getSingleton('shipping/config')->getCarrierInstance($CarrierCode);
                        $carrierTitle = $carrier->getConfigData('title');
                    } else {
                        $carrierTitle = Mage::helper('sales')->__('Custom Value');
                    }

                    //$truncatedCarrierTitle = substr($carrierTitle, 0, 35) . (strlen($carrierTitle) > 35 ? '...' : '');
                    $truncatedTitle = substr($track->getTitle(), 0, 45) . (strlen($track->getTitle()) > 45 ? '...' : '');
                    //$page->drawText($truncatedCarrierTitle, 285, $yShipments , 'UTF-8');
                    $page->drawText($truncatedTitle, 300, $yShipments, 'UTF-8');
                    $page->drawText($track->getNumber(), 395, $yShipments, 'UTF-8');
                    $yShipments -=7;
                }
            } else {
                $yShipments -= 7;
            }

            $currentY = min($yPayments, $yShipments);

            // replacement of Shipments-Payments rectangle block
            $page->drawLine(25, $this->y + 15, 25, $currentY);
            $page->drawLine(25, $currentY, 570, $currentY);
            $page->drawLine(570, $currentY, 570, $this->y + 15);

            $this->y = $currentY;
            $this->y -= 15;
        }
    }

    /*
     * To create a PDF document
     * updated @ 02.12.2010
     * @ sathish kumar
     */

    //insert coupon pdf

    protected function insertCoupon(&$page, $_order, $putOrderId = true, $identifier) {
        $baseurl = Mage::getBaseUrl();
        $skinPath = $this->getSkinUrl();
        $currencySymbol = Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol();
        $baseurl = $this->getBaseUrl();
        $realOrderId = $_order->getRealOrderId();
        $displayOrderId = '#' . $realOrderId;
        $resource = Mage::getSingleton('core/resource');
        $couponemail = $resource->getConnection('core_write');
        $tprefix = (string) Mage::getConfig()->getTablePrefix();
        $uniquenumber = '';
        $giftCouponCheck = $couponemail->fetchRow("select * from " . $tprefix . "ordercustomer where order_id =" . $_order->getId() . " and quantitynumber=".$identifier);

        if (isset($giftCouponCheck['uniqueid'])) {
            $uniquenumber = $giftCouponCheck['uniqueid'];
            $customerName = $giftCouponCheck['customer_name'];
        }
        $store = Mage::app()->getStore();
        $order = Mage::getModel('sales/order')->load($_order->getId());
        $items = $order->getAllItems();
        $itemcount = count($items);
        $Productname = '';
        $unitPrice = '';
        $qty = '';
        foreach ($items as $itemId => $item) {
            $Productname = $item->getName();
            $unitPrice = $item->getPrice();
            $specialPrice = $item->getSpecialPrice();
            $qty = $item->getQtyOrdered();
            $model = Mage::getModel('catalog/product');
            $productId = $item->getProductId();
            $url = "productid" . $productId;
            $productdetail = $model->load($item->getProductId());
            $currentproductimage = Mage::helper('catalog/image')->init($productdetail, 'image')->resize(386, 338);
            $specialPrice = $productdetail->getSpecialPrice();
            $Productname = $productdetail->getName();

            $unitPrice = $productdetail->getPrice();
            $discount_price = $unitPrice - $specialPrice;
            $discount = ($discount_price * 100) / $unitPrice;
            $discount = round($discount);
            $currentdiscount = $discount;
            $couponValid = $productdetail->getEnjoyby();
            $couponValidDate = date("F j, Y", strtotime($couponValid));
            $couponCondition = $productdetail->getLimits();
            $phone = $productdetail->getQuestions();
            $Companywebsite = $productdetail->getCustomer_website();
            $Fineprint = $productdetail->getFineprint();
            $Fineprint = explode('.', $Fineprint);
            $j = 0;
            foreach ($Fineprint as $fitem) {
                $fineprint[$j] = strip_tags(trim($fitem));
                $j = $j + 1;
            }
            $Companyname = $productdetail->getCompanyName();
            $Companyaddress = $productdetail->getCustomersite();
            $Companyaddress = str_replace('<br/>', '$', $Companyaddress);
            $Companyaddress = str_replace('<br>', '$', $Companyaddress);
            $comAddress = explode('$', $Companyaddress);
            $i = 0;
            foreach ($comAddress as $item) {
                $address[$i] = trim($item);
                $i = $i + 1;
            }

            $product_worth = $productdetail->getPrice();
            $product_description = $productdetail->getDescription();
        }
        /* @var $order Mage_Sales_Model_Order */
        $page->setFillColor(new Zend_Pdf_Color_Rgb(0.30, 0.40, 0.42));

        // $page->drawRectangle(25, 810, 570, 725);
        $page->drawRectangle(75, 800, 520, 760);
        $page->setFillColor(new Zend_Pdf_Color_Rgb(1, 1, 1));
        $page->drawRectangle(75, 760, 520, 550);

        //$page->drawRectangle(25, 830, 570, 155);
        //orderid
        $page->setFillColor(new Zend_Pdf_Color_Rgb(1, 1, 1));
        $this->_setFontBold($page, 16);
        $page->drawText(Mage::helper('sales')->__($displayOrderId), 420, 770, 'UTF-8');
        //set color for body text
        $page->setFillColor(new Zend_Pdf_Color_Rgb(0, 0, 0));
        //product name
        $this->_setFontBold($page, 12);
        $page->drawText(Mage::helper('sales')->__($Productname), 85, 720, 'UTF-8');
        //recipient
        $page->drawText(Mage::helper('sales')->__('Recipient'), 85, 680, 'UTF-8');
        $this->_setFontRegular($page);
        $page->drawText(Mage::helper('sales')->__($customerName), 85, 670, 'UTF-8');
        //Expires On
        $this->_setFontBold($page, 12);
        $page->drawText(Mage::helper('sales')->__('Expires On'), 85, 650, 'UTF-8');
        $this->_setFontRegular($page);
        $page->drawText(Mage::helper('sales')->__($couponValidDate), 85, 640, 'UTF-8');
        //Fine Print
        $this->_setFontBold($page, 12);
        $page->drawText(Mage::helper('sales')->__('Fine Print'), 85, 620, 'UTF-8');
        $this->_setFontRegular($page);
        $y = 610;
        for ($k = 0; $k < $i; $k++) {
            $page->drawText(Mage::helper('sales')->__($fineprint[$k]), 85, $y, 'UTF-8');
            $y = $y - 10;
        }
        //Redeem at @ address
        $this->_setFontBold($page, 12);
        $page->drawText(Mage::helper('sales')->__('Redeem at'), 350, 680, 'UTF-8');
        $this->_setFontRegular($page);
        $y = 670;
        for ($k = 0; $k < $i; $k++) {
            $page->drawText(Mage::helper('sales')->__($address[$k]), 350, $y, 'UTF-8');
            $y = $y - 10;
        }
        //Worth
        $this->_setFontBold($page, 12);
        $y = $y - 10;
        $page->drawText(Mage::helper('sales')->__('Worth'), 350, $y, 'UTF-8');
        $this->_setFontBold($page, 11);
        $y = $y - 15;
        $page->drawText(Mage::helper('sales')->__($currencySymbol . floor($specialPrice)), 350, $y, 'UTF-8');

        //Worth
        $this->_setFontBold($page, 12);
        $y = $y - 20;
        $page->drawText(Mage::helper('sales')->__('Coupon Code'), 350, $y, 'UTF-8');
        $this->_setFontBold($page, 16);
        $y = $y - 15;
        $page->drawText(Mage::helper('sales')->__($uniquenumber), 350, $y, 'UTF-8');
        $f = Mage::app()->getLayout()->createBlock('cms/block')->setBlockId('coupon-use')->toHtml();

        $howTo = strip_tags($f);

        $how = explode('  ', $howTo);
        $h = 0;
        foreach ($how as $howitems) {
            $howItems[$h] = trim($howitems);
            $h = $h + 1;
        }


        $this->_setFontBold($page, 12);
        $y = 520;
        for ($s = 0; $s < $h; $s++) {
            $page->drawText(Mage::helper('sales')->__($howItems[$s]), 85, $y, 'UTF-8');
            $this->_setFontRegular($page);
            $y = $y - 10;
        }
        $r = Mage::app()->getLayout()->createBlock('cms/block')->setBlockId('coupon-footer')->toHtml();
        $footer = strip_tags($r);
        $footer = explode('&nbsp;', $footer);
        $e = 0;
        foreach ($footer as $footerItem) {
            $foot[$e] = trim($footerItem);
            $e = $e + 1;
        }
        $page->setFillColor(new Zend_Pdf_Color_Rgb(0.30, 0.40, 0.42));
        $y = $y - 20;
        $x = 85;
        for ($t = 1; $t < $e; $t++) {
            $page->drawText(Mage::helper('sales')->__($foot[$t]), $x, $y, 'UTF-8');
            $x = $x + 300;
        }

        $phpSelf=$_SERVER['PHP_SELF'];
        $link = explode('/',$phpSelf);
        foreach($link as $item){
            if($item != 'index.php'){
               $url=$item;
            }
        }
        if(isset($url)){
        $image=$_SERVER['DOCUMENT_ROOT'].'/'.$url.'/skin/frontend/default/inspire-blue/images/logo.png';
        }
        else{
         $image=$_SERVER['DOCUMENT_ROOT'].'/skin/frontend/default/inspire-blue/images/logo.png';
        }

        //$image = $_SERVER['DOCUMENT_ROOT'] . '/gcVersion5-beta/skin/frontend/default/inspire-blue/images/logo.png';
        $image = Zend_Pdf_Image::imageWithPath($image);
        $page->drawImage($image, 85, 765, 250, 785);
    }

    //insert coupon pdf

    protected function _sortTotalsList($a, $b) {
        if (!isset($a['sort_order']) || !isset($b['sort_order'])) {
            return 0;
        }

        if ($a['sort_order'] == $b['sort_order']) {
            return 0;
        }

        return ($a['sort_order'] > $b['sort_order']) ? 1 : -1;
    }

    protected function _getTotalsList($source) {
        $totals = Mage::getConfig()->getNode('global/pdf/totals')->asArray();
        usort($totals, array($this, '_sortTotalsList'));
        $totalModels = array();
        foreach ($totals as $index => $totalInfo) {
            if (!empty($totalInfo['model'])) {
                $totalModel = Mage::getModel($totalInfo['model']);
                if ($totalModel instanceof Mage_Sales_Model_Order_Pdf_Total_Default) {
                    $totalInfo['model'] = $totalModel;
                } else {
                    Mage::throwException(
                                    Mage::helper('sales')->__('PDF total model should extend Mage_Sales_Model_Order_Pdf_Total_Default')
                    );
                }
            } else {
                $totalModel = Mage::getModel($this->_defaultTotalModel);
            }
            $totalModel->setData($totalInfo);
            $totalModels[] = $totalModel;
        }

        return $totalModels;
    }

    protected function insertTotals($page, $source) {
        $order = $source->getOrder();
        $totals = $this->_getTotalsList($source);
        $lineBlock = array(
            'lines' => array(),
            'height' => 15
        );
        foreach ($totals as $total) {
            $total->setOrder($order)
                    ->setSource($source);

            if ($total->canDisplay()) {
                foreach ($total->getTotalsForDisplay() as $totalData) {
                    $lineBlock['lines'][] = array(
                        array(
                            'text' => $totalData['label'],
                            'feed' => 475,
                            'align' => 'right',
                            'font_size' => $totalData['font_size'],
                            'font' => 'bold'
                        ),
                        array(
                            'text' => $totalData['amount'],
                            'feed' => 565,
                            'align' => 'right',
                            'font_size' => $totalData['font_size'],
                            'font' => 'bold'
                        ),
                    );
                }
            }
        }

        $page = $this->drawLineBlocks($page, array($lineBlock));
        return $page;
    }

    protected function _parseItemDescription($item) {
        $matches = array();
        $description = $item->getDescription();
        if (preg_match_all('/<li.*?>(.*?)<\/li>/i', $description, $matches)) {
            return $matches[1];
        }

        return array($description);
    }

    /**
     * Before getPdf processing
     *
     */
    protected function _beforeGetPdf() {
        $translate = Mage::getSingleton('core/translate');
        /* @var $translate Mage_Core_Model_Translate */
        $translate->setTranslateInline(false);
    }

    /**
     * After getPdf processing
     *
     */
    protected function _afterGetPdf() {
        $translate = Mage::getSingleton('core/translate');
        /* @var $translate Mage_Core_Model_Translate */
        $translate->setTranslateInline(true);
    }

    protected function _formatOptionValue($value, $order) {
        $resultValue = '';
        if (is_array($value)) {
            if (isset($value['qty'])) {
                $resultValue .= sprintf('%d', $value['qty']) . ' x ';
            }

            $resultValue .= $value['title'];

            if (isset($value['price'])) {
                $resultValue .= " " . $order->formatPrice($value['price']);
            }
            return $resultValue;
        } else {
            return $value;
        }
    }

    protected function _initRenderer($type) {
        $node = Mage::getConfig()->getNode('global/pdf/' . $type);
        foreach ($node->children() as $renderer) {
            $this->_renderers[$renderer->getName()] = array(
                'model' => (string) $renderer,
                'renderer' => null
            );
        }
    }

    /**
     * Retrieve renderer model
     *
     * @throws Mage_Core_Exception
     * @return Mage_Sales_Model_Order_Pdf_Items_Abstract
     */
    protected function _getRenderer($type) {
        if (!isset($this->_renderers[$type])) {
            $type = 'default';
        }

        if (!isset($this->_renderers[$type])) {
            Mage::throwException(Mage::helper('sales')->__('Invalid renderer model'));
        }

        if (is_null($this->_renderers[$type]['renderer'])) {
            $this->_renderers[$type]['renderer'] = Mage::getSingleton($this->_renderers[$type]['model']);
        }

        return $this->_renderers[$type]['renderer'];
    }

    /**
     * Public method of protected @see _getRenderer()
     *
     * Retrieve renderer model
     *
     * @param string $type
     * @return Mage_Sales_Model_Order_Pdf_Items_Abstract
     */
    public function getRenderer($type) {
        return $this->_getRenderer($type);
    }

    /**
     * Draw Item process
     *
     * @param Varien_Object $item
     * @param Zend_Pdf_Page $page
     * @param Mage_Sales_Model_Order $order
     * @return Zend_Pdf_Page
     */
    protected function _drawItem(Varien_Object $item, Zend_Pdf_Page $page, Mage_Sales_Model_Order $order) {
        $type = $item->getOrderItem()->getProductType();
        $renderer = $this->_getRenderer($type);
        $renderer->setOrder($order);
        $renderer->setItem($item);
        $renderer->setPdf($this);
        $renderer->setPage($page);
        $renderer->setRenderedModel($this);

        $renderer->draw();

        return $renderer->getPage();
    }

    protected function _setFontRegular($object, $size = 7) {
        $font = Zend_Pdf_Font::fontWithPath(Mage::getBaseDir() . '/lib/LinLibertineFont/LinLibertineC_Re-2.8.0.ttf');
        $object->setFont($font, $size);
        return $font;
    }

    protected function _setFontBold($object, $size = 7) {
        $font = Zend_Pdf_Font::fontWithPath(Mage::getBaseDir() . '/lib/LinLibertineFont/LinLibertine_Bd-2.8.1.ttf');
        $object->setFont($font, $size);
        return $font;
    }

    protected function _setFontItalic($object, $size = 7) {
        $font = Zend_Pdf_Font::fontWithPath(Mage::getBaseDir() . '/lib/LinLibertineFont/LinLibertine_It-2.8.2.ttf');
        $object->setFont($font, $size);
        return $font;
    }

    /**
     * Set PDF object
     *
     * @param Zend_Pdf $pdf
     * @return Mage_Sales_Model_Order_Pdf_Abstract
     */
    protected function _setPdf(Zend_Pdf $pdf) {
        $this->_pdf = $pdf;
        return $this;
    }

    /**
     * Retrieve PDF object
     *
     * @throws Mage_Core_Exception
     * @return Zend_Pdf
     */
    protected function _getPdf() {
        if (!$this->_pdf instanceof Zend_Pdf) {
            Mage::throwException(Mage::helper('sales')->__('Please define PDF object before using.'));
        }

        return $this->_pdf;
    }

    /**
     * Create new page and assign to PDF object
     *
     * @param array $settings
     * @return Zend_Pdf_Page
     */
    public function newPage(array $settings = array()) {
        $pageSize = !empty($settings['page_size']) ? $settings['page_size'] : Zend_Pdf_Page::SIZE_A4;
        $page = $this->_getPdf()->newPage($pageSize);
        $this->_getPdf()->pages[] = $page;
        $this->y = 800;

        return $page;
    }

    /**
     * Draw lines
     *
     * draw items array format:
     * lines        array;array of line blocks (required)
     * shift        int; full line height (optional)
     * height       int;line spacing (default 10)
     *
     * line block has line columns array
     *
     * column array format
     * text         string|array; draw text (required)
     * feed         int; x position (required)
     * font         string; font style, optional: bold, italic, regular
     * font_file    string; path to font file (optional for use your custom font)
     * font_size    int; font size (default 7)
     * align        string; text align (also see feed parametr), optional left, right
     * height       int;line spacing (default 10)
     *
     * @param Zend_Pdf_Page $page
     * @param array $draw
     * @param array $pageSettings
     * @throws Mage_Core_Exception
     * @return Zend_Pdf_Page
     */
    public function drawLineBlocks(Zend_Pdf_Page $page, array $draw, array $pageSettings = array()) {
        foreach ($draw as $itemsProp) {
            if (!isset($itemsProp['lines']) || !is_array($itemsProp['lines'])) {
                Mage::throwException(Mage::helper('sales')->__('Invalid draw line data. Please define "lines" array.'));
            }
            $lines = $itemsProp['lines'];
            $height = isset($itemsProp['height']) ? $itemsProp['height'] : 10;

            if (empty($itemsProp['shift'])) {
                $shift = 0;
                foreach ($lines as $line) {
                    $maxHeight = 0;
                    foreach ($line as $column) {
                        $lineSpacing = !empty($column['height']) ? $column['height'] : $height;
                        if (!is_array($column['text'])) {
                            $column['text'] = array($column['text']);
                        }
                        $top = 0;
                        foreach ($column['text'] as $part) {
                            $top += $lineSpacing;
                        }

                        $maxHeight = $top > $maxHeight ? $top : $maxHeight;
                    }
                    $shift += $maxHeight;
                }
                $itemsProp['shift'] = $shift;
            }

            if ($this->y - $itemsProp['shift'] < 15) {
                $page = $this->newPage($pageSettings);
            }

            foreach ($lines as $line) {
                $maxHeight = 0;
                foreach ($line as $column) {
                    $fontSize = empty($column['font_size']) ? 7 : $column['font_size'];
                    if (!empty($column['font_file'])) {
                        $font = Zend_Pdf_Font::fontWithPath($column['font_file']);
                        $page->setFont($font);
                    } else {
                        $fontStyle = empty($column['font']) ? 'regular' : $column['font'];
                        switch ($fontStyle) {
                            case 'bold':
                                $font = $this->_setFontBold($page, $fontSize);
                                break;
                            case 'italic':
                                $font = $this->_setFontItalic($page, $fontSize);
                                break;
                            default:
                                $font = $this->_setFontRegular($page, $fontSize);
                                break;
                        }
                    }

                    if (!is_array($column['text'])) {
                        $column['text'] = array($column['text']);
                    }

                    $lineSpacing = !empty($column['height']) ? $column['height'] : $height;
                    $top = 0;
                    foreach ($column['text'] as $part) {
                        $feed = $column['feed'];
                        $textAlign = empty($column['align']) ? 'left' : $column['align'];
                        $width = empty($column['width']) ? 0 : $column['width'];
                        switch ($textAlign) {
                            case 'right':
                                if ($width) {
                                    $feed = $this->getAlignRight($part, $feed, $width, $font, $fontSize);
                                } else {
                                    $feed = $feed - $this->widthForStringUsingFontSize($part, $font, $fontSize);
                                }
                                break;
                            case 'center':
                                if ($width) {
                                    $feed = $this->getAlignCenter($part, $feed, $width, $font, $fontSize);
                                }
                                break;
                        }
                        $page->drawText($part, $feed, $this->y - $top, 'UTF-8');
                        $top += $lineSpacing;
                    }

                    $maxHeight = $top > $maxHeight ? $top : $maxHeight;
                }
                $this->y -= $maxHeight;
            }
        }

        return $page;
    }

}
