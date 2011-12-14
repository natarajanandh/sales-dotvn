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
 * Catalog product controller
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Catalog_ProductController extends Mage_Adminhtml_Controller_Action {

    /**
     * Array of actions which can be processed without secret key validation
     *
     * @var array
     */
    protected $_publicActions = array('edit');
    const XML_PATH_COUPON_TEMPLATE = 'dealcoupon/email/coupon_template';
    const XML_PATH_OWNER_TEMPLATE = 'dealcoupon/email/owner_template';
    const XML_PATH_NO_EMAIL_TEMPLATE = 'dealcoupon/email/email_template';
    const MERCHANT_PATH_ADDDEAL_TEMPLATE = 'merchant/email/adddeal_template';
    //const XML_PATH_EMAIL_SENDER     = 'catalog/email/sender_email_identity';
    const XML_PATH_EMAIL_TEMPLATE = 'catalog/email/email_template';
    const XML_PATH_EMAIL_RECIPIENT = 'contacts/email/recipient_email';
    const XML_PATH_EMAIL_SENDER = 'dealcoupon/email/sender_email_identity';

    protected function _construct() {
        // Define module dependent translate
        $this->setUsedModuleName('Mage_Catalog');
    }

    /**
     * Initialize product from request parameters
     *
     * @return Mage_Catalog_Model_Product
     */
    protected function _initProduct() {
        $this->_title($this->__('Catalog'))
                ->_title($this->__('Manage Products'));
        $productId = (int) $this->getRequest()->getParam('id');
        $product = Mage::getModel('catalog/product')
                        ->setStoreId($this->getRequest()->getParam('store', 0));

        if (!$productId) {
            if ($setId = (int) $this->getRequest()->getParam('set')) {
                $product->setAttributeSetId($setId);
            }
            if ($typeId = $this->getRequest()->getParam('type')) {
                $product->setTypeId($typeId);
            }
        }


        $product->setData('_edit_mode', true);
        if ($productId) {
            $product->load($productId);
        }



        $attributes = $this->getRequest()->getParam('attributes');
        if ($attributes && $product->isConfigurable() &&
                (!$productId || !$product->getTypeInstance()->getUsedProductAttributeIds())) {
            $product->getTypeInstance()->setUsedProductAttributeIds(
                    explode(",", base64_decode(urldecode($attributes)))
            );
        }

        // Required attributes of simple product for configurable creation
        if ($this->getRequest()->getParam('popup')
                && $requiredAttributes = $this->getRequest()->getParam('required')) {
            $requiredAttributes = explode(",", $requiredAttributes);
            foreach ($product->getAttributes() as $attribute) {
                if (in_array($attribute->getId(), $requiredAttributes)) {
                    $attribute->setIsRequired(1);
                }
            }
        }

        if ($this->getRequest()->getParam('popup')
                && $this->getRequest()->getParam('product')
                && !is_array($this->getRequest()->getParam('product'))
                && $this->getRequest()->getParam('id', false) === false) {
            $configProduct = Mage::getModel('catalog/product')
                            ->setStoreId(0)
                            ->load($this->getRequest()->getParam('product'))
                            ->setTypeId($this->getRequest()->getParam('type'));

            /* @var $configProduct Mage_Catalog_Model_Product */
            $data = array();
            foreach ($configProduct->getTypeInstance()->getEditableAttributes() as $attribute) {
                /* @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
                if (!$attribute->getIsUnique()
                        && $attribute->getFrontend()->getInputType() != 'gallery'
                        && $attribute->getAttributeCode() != 'required_options'
                        && $attribute->getAttributeCode() != 'has_options'
                        && $attribute->getAttributeCode() != $configProduct->getIdFieldName()) {
                    $data[$attribute->getAttributeCode()] = $configProduct->getData($attribute->getAttributeCode());
                }
            }

            $product->addData($data)
                    ->setWebsiteIds($configProduct->getWebsiteIds());
        }

        Mage::register('product', $product);
        Mage::register('current_product', $product);
        return $product;
    }

    /**
     * Create serializer block for a grid
     *
     * @param string $inputName
     * @param Mage_Adminhtml_Block_Widget_Grid $gridBlock
     * @param array $productsArray
     * @return Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Ajax_Serializer
     */
    protected function _createSerializerBlock($inputName, Mage_Adminhtml_Block_Widget_Grid $gridBlock, $productsArray) {
        return $this->getLayout()->createBlock('adminhtml/catalog_product_edit_tab_ajax_serializer')
                ->setGridBlock($gridBlock)
                ->setProducts($productsArray)
                ->setInputElementName($inputName);
    }

    /**
     * Output specified blocks as a text list
     */
    protected function _outputBlocks() {
        $blocks = func_get_args();
        $output = $this->getLayout()->createBlock('adminhtml/text_list');
        foreach ($blocks as $block) {
            $output->insert($block, '', true);
        }
        $this->getResponse()->setBody($output->toHtml());
    }

    /**
     * Product list page
     */
    public function indexAction() {
        $adminuser = Mage::getSingleton('admin/session')->getUser();
        $roleId = implode('', $adminuser->getRoles());
        $userId = $adminuser->getId();
        if ($roleId != 1) {
            $_productCollection = Mage::getResourceModel('catalog/product_collection')
                            ->addAttributeToFilter('merchant', $userId);
            foreach ($_productCollection as $_product) {
                $current_product = $_product->getId();

                $resource1 = Mage::getSingleton('core/resource');
                $dealPayment = $resource1->getConnection('core_write');
                $tprefix = (string) Mage::getConfig()->getTablePrefix();
                $productStatus = $dealPayment->fetchRow("Select product_id  from " . $tprefix . "deal_payment where product_id = " . $current_product . " and admin_approved='yes'");
                $count = count($productStatus['product_id']);
                if ($count == 0) {
                    $resultproductid = $dealPayment->query("update  " . $tprefix . "catalog_product_entity_int set value=2 where entity_id  = '$current_product' and attribute_id = '84' ");
                }
            }
        }
        $this->_title($this->__('Catalog'))
                ->_title($this->__('Manage Products'));
        $this->loadLayout();
        $this->_setActiveMenu('catalog/products');
        $this->renderLayout();
    }

    /*
     * Contus
     * Merchant Deal Activation action
     */

    public function activationAction() {
        $model = Mage::getModel('catalog/product'); //getting product model
        $dealId = $_REQUEST['id'];
        $row = $model->load($dealId);
        $dealName = $row->getName();
        $amount = floor($row->getSpecialPrice());
        $merchant = $row->getMerchant();
        $userId = Mage::getSingleton('admin/session')->getUser()->getId();
        $userEmail = Mage::getSingleton('admin/session')->getUser()->getEmail();
        $paymentStatus = 'Completed';
        $adminApproved = 'yes';
        $date = date("Y-m-d", time());

        $resource1 = Mage::getSingleton('core/resource');
        $dealPayment = $resource1->getConnection('core_write');
        $tprefix = (string) Mage::getConfig()->getTablePrefix();
        $productStatus = $dealPayment->fetchRow("Select product_id  from " . $tprefix . "deal_payment where product_id = " . $dealId);
        $count = count($productStatus['product_id']);
        if ($count == 0) {
            $insertQuery = "INSERT INTO magentodeal_payment VALUES ('','$userId','$userEmail','$dealId','$amount','$date','$paymentStatus','$adminApproved')";
            $dealPayment->query($insertQuery);
        }
        $this->getResponse()->setRedirect($this->getUrl('*/*/', array('store' => $this->getRequest()->getParam('store'))));
    }

    /*
     * Contus
     * Admin Approve action
     */

    public function approveAction() {
        $dealId = $_REQUEST['id'];
        $resource1 = Mage::getSingleton('core/resource');
        $dealApprove = $resource1->getConnection('core_write');
        $tprefix = (string) Mage::getConfig()->getTablePrefix();
        $updateQuery = "update magentodeal_payment set admin_approved='yes' where product_id=$dealId";
        $dealApprove->query($updateQuery);
        $this->getResponse()->setRedirect($this->getUrl('*/*/', array('store' => $this->getRequest()->getParam('store'))));
    }

    /**

     * Create new product page

     */
    public function newAction() {

        $product = $this->_initProduct();



        $this->_title($this->__('New Product'));



        Mage::dispatchEvent('catalog_product_new_action', array('product' => $product));



        if ($this->getRequest()->getParam('popup')) {

            $this->loadLayout('popup');
        } else {

            $_additionalLayoutPart = '';

            if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE
                    && !($product->getTypeInstance()->getUsedProductAttributeIds())) {

                $_additionalLayoutPart = '_new';
            }

            $this->loadLayout(array(
                'default',
                strtolower($this->getFullActionName()),
                'adminhtml_catalog_product_' . $product->getTypeId() . $_additionalLayoutPart
            ));

            $this->_setActiveMenu('catalog/products');
        }



        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);



        $block = $this->getLayout()->getBlock('catalog.wysiwyg.js');

        if ($block) {

            $block->setStoreId($product->getStoreId());
        }



        $this->renderLayout();
    }

    /**
     * Product edit form
     */
    public function editAction() {
        $productId = (int) $this->getRequest()->getParam('id');
        $product = $this->_initProduct();
        if ($productId && !$product->getId()) {
            $this->_getSession()->addError(Mage::helper('catalog')->__('This product no longer exists.'));
            $this->_redirect('*/*/');
            return;
        }
        $this->_title($product->getName());
        Mage::dispatchEvent('catalog_product_edit_action', array('product' => $product));
        $_additionalLayoutPart = '';
        if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE
                && !($product->getTypeInstance()->getUsedProductAttributeIds())) {
            $_additionalLayoutPart = '_new';
        }
        $this->loadLayout(array(
            'default',
            strtolower($this->getFullActionName()),
            'adminhtml_catalog_product_' . $product->getTypeId() . $_additionalLayoutPart
        ));
        $this->_setActiveMenu('catalog/products');
        if (!Mage::app()->isSingleStoreMode() && ($switchBlock = $this->getLayout()->getBlock('store_switcher'))) {
            $switchBlock->setDefaultStoreName($this->__('Default Values'))
                    ->setWebsiteIds($product->getWebsiteIds())
                    ->setSwitchUrl($this->getUrl('*/*/*', array('_current' => true, 'active_tab' => null, 'tab' => null, 'store' => null)));
        }
        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
        $block = $this->getLayout()->getBlock('catalog.wysiwyg.js');
        if ($block) {
            $block->setStoreId($product->getStoreId());
        }
        $this->renderLayout();
    }

    /**
     * WYSIWYG editor action for ajax request
     *
     */
    public function wysiwygAction() {
        $elementId = $this->getRequest()->getParam('element_id', md5(microtime()));
        $storeId = $this->getRequest()->getParam('store_id', 0);
        $storeMediaUrl = Mage::app()->getStore($storeId)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);
        $content = $this->getLayout()->createBlock('adminhtml/catalog_helper_form_wysiwyg_content', '', array(
                    'editor_element_id' => $elementId,
                    'store_id' => $storeId,
                    'store_media_url' => $storeMediaUrl,
                ));
        $this->getResponse()->setBody($content->toHtml());
    }

    /**
     * Product grid for AJAX request
     */
    public function gridAction() {
        $this->loadLayout();
        $this->getResponse()->setBody(
                $this->getLayout()->createBlock('adminhtml/catalog_product_grid')->toHtml()
        );
    }

    /**
     * Get specified tab grid
     */
    public function gridOnlyAction() {
        $this->_initProduct();
        $this->loadLayout();
        $this->getResponse()->setBody(
                        $this->getLayout()->createBlock('adminhtml/catalog_product_edit_tab_' . $this->getRequest()->getParam('gridOnlyBlock'))
                        ->toHtml()
        );
    }

    /**
     * Get categories fieldset block
     *
     */
    public function categoriesAction() {
        $this->_initProduct();
        $this->getResponse()->setBody(
                $this->getLayout()->createBlock('adminhtml/catalog_product_edit_tab_categories')->toHtml()
        );
    }

    /**
     * Get options fieldset block
     *
     */
    public function optionsAction() {
        $this->_initProduct();
        $this->getResponse()->setBody(
                $this->getLayout()->createBlock('adminhtml/catalog_product_edit_tab_options', 'admin.product.options')->toHtml()
        );
    }

    /**
     * Get related products grid and serializer block
     */
    public function relatedAction() {
        $this->_initProduct();
        $this->loadLayout();
        $this->getLayout()->getBlock('catalog.product.edit.tab.related')
                ->setProductsRelated($this->getRequest()->getPost('products_related', null));
        $this->renderLayout();
    }

    /**
     * Get upsell products grid and serializer block
     */
    public function upsellAction() {
        $this->_initProduct();
        $this->loadLayout();
        $this->getLayout()->getBlock('catalog.product.edit.tab.upsell')
                ->setProductsUpsell($this->getRequest()->getPost('products_upsell', null));
        $this->renderLayout();
    }

    /**
     * Get crosssell products grid and serializer block
     */
    public function crosssellAction() {
        $this->_initProduct();
        $this->loadLayout();
        $this->getLayout()->getBlock('catalog.product.edit.tab.crosssell')
                ->setProductsCrossSell($this->getRequest()->getPost('products_crosssell', null));
        $this->renderLayout();
    }

    /**
     * Get related products grid
     */
    public function relatedGridAction() {
        $this->_initProduct();
        $this->loadLayout();
        $this->getLayout()->getBlock('catalog.product.edit.tab.related')
                ->setProductsRelated($this->getRequest()->getPost('products_related', null));
        $this->renderLayout();
    }

    /**
     * Get upsell products grid
     */
    public function upsellGridAction() {
        $this->_initProduct();
        $this->loadLayout();
        $this->getLayout()->getBlock('catalog.product.edit.tab.upsell')
                ->setProductsRelated($this->getRequest()->getPost('products_upsell', null));
        $this->renderLayout();
    }

    /**
     * Get crosssell products grid
     */
    public function crosssellGridAction() {
        $this->_initProduct();
        $this->loadLayout();
        $this->getLayout()->getBlock('catalog.product.edit.tab.crosssell')
                ->setProductsRelated($this->getRequest()->getPost('products_crosssell', null));
        $this->renderLayout();
    }

    /**
     * Get associated grouped products grid and serializer block
     */
    public function superGroupAction() {
        $this->_initProduct();
        $this->loadLayout();
        $this->getLayout()->getBlock('catalog.product.edit.tab.super.group')
                ->setProductsGrouped($this->getRequest()->getPost('products_grouped', null));
        $this->renderLayout();
    }

    /**
     * Get associated grouped products grid only
     *
     */
    public function superGroupGridOnlyAction() {
        $this->_initProduct();
        $this->loadLayout();
        $this->getLayout()->getBlock('catalog.product.edit.tab.super.group')
                ->setProductsGrouped($this->getRequest()->getPost('products_grouped', null));
        $this->renderLayout();
    }

    /**
     * Get product reviews grid
     *
     */
    public function reviewsAction() {
        $this->_initProduct();
        $this->getResponse()->setBody(
                        $this->getLayout()->createBlock('adminhtml/catalog_product_edit_tab_reviews', 'admin.product.reviews')
                        ->setProductId(Mage::registry('product')->getId())
                        ->setUseAjax(true)
                        ->toHtml()
        );
    }

    /**
     * Get super config grid
     *
     */
    public function superConfigAction() {
        $this->_initProduct();
        $this->getResponse()->setBody(
                $this->getLayout()->createBlock('adminhtml/catalog_product_edit_tab_super_config_grid')->toHtml()
        );
    }

    /**
     * Deprecated since 1.2
     *
     */
    public function bundlesAction() {
        $product = $this->_initProduct();
        $this->getResponse()->setBody(
                        $this->getLayout()->createBlock('bundle/adminhtml_catalog_product_edit_tab_bundle', 'admin.product.bundle.items')
                        ->setProductId($product->getId())
                        ->toHtml()
        );
    }

    /**
     * Validate product
     *
     */
    public function validateAction() {
        $response = new Varien_Object();
        $response->setError(false);

        try {
            $productData = $this->getRequest()->getPost('product');
            if ($productData && !isset($productData['stock_data']['use_config_manage_stock'])) {
                $productData['stock_data']['use_config_manage_stock'] = 0;
            }

            $product = Mage::getModel('catalog/product');
            $product->setData('_edit_mode', true);
            if ($storeId = $this->getRequest()->getParam('store')) {
                $product->setStoreId($storeId);
            }
            if ($setId = $this->getRequest()->getParam('set')) {
                $product->setAttributeSetId($setId);
            }
            if ($typeId = $this->getRequest()->getParam('type')) {
                $product->setTypeId($typeId);
            }

            if ($productId = $this->getRequest()->getParam('id')) {
                $product->load($productId);
            }

            $product->addData($productData);
            $product->validate();

            /**
             * @todo implement full validation process with errors returning which are ignoring now
             */
            //            if (is_array($errors = $product->validate())) {
            //                foreach ($errors as $code => $error) {
            //                    if ($error === true) {
            //                        Mage::throwException(Mage::helper('catalog')->__('Attribute "%s" is invalid.', $product->getResource()->getAttribute($code)->getFrontend()->getLabel()));
            //                    }
            //                    else {
            //                        Mage::throwException($error);
            //                    }
            //                }
            //            }
        } catch (Mage_Eav_Model_Entity_Attribute_Exception $e) {
            $response->setError(true);
            $response->setAttribute($e->getAttributeCode());
            $response->setMessage($e->getMessage());
        } catch (Mage_Core_Exception $e) {
            $response->setError(true);
            $response->setMessage($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            $this->_initLayoutMessages('adminhtml/session');
            $response->setError(true);
            $response->setMessage($this->getLayout()->getMessagesBlock()->getGroupedHtml());
        }
        $this->getResponse()->setBody($response->toJson());
    }

    /** Contus
     * Send email to Deal Owner
     */
    public function sendemailAction() {

        $productId = (int) $this->getRequest()->getParam('id');
        $product = Mage::getModel('catalog/product')->load($productId);
        $current_product = $product->getId();
        $currentproductname = $product->getName();
        $customeremail = $product->getcustomeremail();
        $totalcoupon = $product->getTargetNumber();
        $resource = Mage::getSingleton('core/resource');
        $orderTable = $resource->getTableName('sales/order');
        $read = $resource->getConnection('read');
        $tprefix = (string) Mage::getConfig()->getTablePrefix();
        $flatorderTable = $tprefix . "sales_flat_order_item";
        $total_orders = "0";
        $dealStatus[1] = "processing";
        $dealStatus[0] = "complete";
        $select = $read->select()
                        ->from(array('cp' => $orderTable))
                        ->join(array('pei' => $flatorderTable), 'pei.order_id=cp.entity_id', array())
                        ->where('pei.product_id=?', $productId)
                        ->where('cp.status IN (?)', $dealStatus);

        $orders_list = $read->fetchAll($select);
        foreach ($orders_list as $rows) {
            $order_id = $rows['entity_id'];
            $orderId = $rows['increment_id'];
            $order = Mage::getModel('sales/order')->load($order_id);
            $items = $order->getAllItems();
            $totalQtyPurchase = 0;
            //calculate Quantity
            foreach ($items as $itemId => $item) {
                if ($current_product == $item->getProductId()) {
                    $total_orders = $total_orders + 1;
                    $qty = $item->getQtyOrdered();
                    $totalQtyPurchase = $totalQtyPurchase + $qty;
                }
            }
        }
        if ($totalQtyPurchase < $totalcoupon) {

            $post['content'] = $template;
            $postObject = new Varien_Object();
            $storeName = Mage::getStoreConfig("design/head/default_title");
            $postObject->setData(array('storename' => $storeName, 'purchasedqty' => $totalQtyPurchase, 'productname' => $currentproductname, 'quantity' => $totalcoupon));
            $mailTemplate = Mage::getModel('core/email_template');
            $mailTemplate->setSenderName(Mage::getStoreConfig('design/head/default_title'));
            $mailTemplate->setSenderEmail(Mage::getStoreConfig(self::XML_PATH_EMAIL_RECIPIENT));
            $mailTemplate->setTemplateSubject(Mage::getStoreConfig('design/head/default_title') . ": Today's deal is not achieved!!");

            /* @var $mailTemplate Mage_Core_Model_Email_Template */
            $mailTemplate->setDesignConfig(array('area' => 'frontend'))
                    ->sendTransactional(
                            Mage::getStoreConfig(self::XML_PATH_NO_EMAIL_TEMPLATE),
                            Mage::getStoreConfig(self::XML_PATH_EMAIL_SENDER),
                            $customeremail,
                            Mage::getStoreConfig('design/head/default_title'),
                            array('deallist' => $postObject)
            );

            if (!$mailTemplate->getSentSuccess()) {
                $this->_getSession()->addError("There is a problem in Sending Mail! Email not Sent!");
                $this->getResponse()->setRedirect($this->getUrl('*/*/', array('store' => $this->getRequest()->getParam('store'))));
                return;
            }

            $this->_getSession()->addSuccess('Deal List Sent to Owner.');
            $this->getResponse()->setRedirect($this->getUrl('*/*/', array('store' => $this->getRequest()->getParam('store'))));
            return;
        } else {

            $quantity = array();
            $quantity[0] = "0";
            $i = 0;
            $customerdetails = array();
            foreach ($orders_list as $rows) {
                $order_id = $rows['entity_id'];
                $orderId = $rows['increment_id'];
                $order = Mage::getModel('sales/order')->load($order_id);
                $items = $order->getAllItems();
                $qty = array();
                foreach ($items as $itemId => $item) {
                    if ($productId == $item->getProductId()) {
                        $total_orders = $total_orders + 1;
                        $qty[0] = $item->getQtyOrdered();
                        $customerdetails[$i][1] = $qty[0];
                        $customerdetails[$i][3] = Mage::getBaseUrl() . 'sales/order/print/order_id/' . $order_id . '/';
                        $customerId = $order->getCustomerId();
                        $resultcustomerid = $read->fetchRow("Select email,recipient  from " . $tprefix . "gift_message  where customer_id = '$customerId'  AND product_id ='$productId' AND order_id = $order_id ");
                        if (isset($resultcustomerid['email'])) {
                            $customerdetails[$i][0] = $resultcustomerid['email'];
                            $customerdetails[$i][2] = $resultcustomerid['recipient'];
                        } else {
                            $customerdetails[$i][0] = $order->getCustomerEmail();
                            $customerdetails[$i][2] = $order->getCustomerName();
                        }
                        $customerdetails[$i][4] = $order->getGrandTotal();
                        // $customerdetails[$i][5] = $customerId;
                        $customerdetails[$i][6] = $order_id;
                        $customerdetails[$i][7] = $item->getQtyOrdered();
                        $customerdetails[$i][8] = $orderId;
                        $quantity[0] = $quantity[0] + $qty[0];
                        $i++;
                    }
                }
            }
            $uniquelist = "";
            $template = "";
            $template .= '
<table border="1">
  <tr>
    <th>Order ID</th>
    <th>Customer name</th>
    <th>Coupon Code</th>
    <th>Deal Name</th>
    <th>No of coupons purchased</th>
    <th>Customer Email</th>
  </tr>
  ';
            $uniqeid = $tprefix . "ordercustomer";
            if (!empty($customerdetails)) {
                foreach ($customerdetails as $customerdetails1) {

                    $select = $read->select()
                                    ->from(array('cp' => $uniqeid))
                                    ->where('cp.order_id=?', $customerdetails1[6]);

                    $uniquelist = $read->fetchAll($select);
                    $k = 0;
                    foreach ($uniquelist as $uniqueCuslist) {
                        if ($k == 0) {
                            $k = 1;
                        } else {
                            $uniqueCuslist['quantity'] = ' ';
                        }

                        $template .= '
  <tr>
    <td align="center">' . $customerdetails1[8] . '</td>
    <td>' . $uniqueCuslist['customer_name'] . '</td>
    <td align="center">' . $uniqueCuslist['uniqueid'] . '</td>
    <td>' . $uniqueCuslist['product_name'] . '</td>
    <td align="center">' . $uniqueCuslist['quantity'] . '</td>
    <td>' . $customerdetails1[0] . '</td>
  </tr>
  ';
                    }
                }
            }

            $template .= '
</table>
';
            $emailto = "";
            $myFile = dirname(__FILE__) . "/New.csv";
            $fh = fopen($myFile, 'w') or die("can't open file");
            $stringData = $this->CSVFile();
            fwrite($fh, $stringData);
            fclose($fh);
            $dest = Mage::getBaseDir('export') . "/DealList.csv";
            copy($myFile, $dest);
            unlink($myFile);
            $post['content'] = $template;
            $emailTemplateVariables = array();
            $postObject = new Varien_Object();
            $postObject->setData(array('content' => $post['content']));

            if (empty($customerdetails)) {
                $this->_getSession()->addError("No orders Found for this Deal !");
            } elseif (empty($uniquelist)) {
                $this->_getSession()->addError("Please Send Coupons to Customers of product-id:$productId and then try it!!!");
            } else {
                $mailTemplate = Mage::getModel('core/email_template');
                $mailTemplate->setSenderName(Mage::getStoreConfig('design/head/default_title'));
                $mailTemplate->setSenderEmail(Mage::getStoreConfig(self::XML_PATH_EMAIL_RECIPIENT));
                $mailTemplate->setTemplateSubject('Coupon Confirmation From ' . Mage::getStoreConfig('design/head/default_title'));

                /* @var $mailTemplate Mage_Core_Model_Email_Template */
                $mailTemplate->setDesignConfig(array('area' => 'frontend'))
                        ->sendTransactional(
                                Mage::getStoreConfig(self::XML_PATH_OWNER_TEMPLATE),
                                Mage::getStoreConfig(self::XML_PATH_EMAIL_SENDER),
                                $customeremail,
                                Mage::getStoreConfig('design/head/default_title'),
                                array('deallist' => $postObject), null,
                                true);

                if (!$mailTemplate->getSentSuccess()) {
                    $this->_getSession()->addError("There is a problem in Sending Mail! Email not Sent!");
                    $this->getResponse()->setRedirect($this->getUrl('*/*/', array('store' => $this->getRequest()->getParam('store'))));
                    return;
                }

                $this->_getSession()->addSuccess('Deal List Sent to Owner.');
                $this->getResponse()->setRedirect($this->getUrl('*/*/', array('store' => $this->getRequest()->getParam('store'))));
                return;
            }
        }
        $this->getResponse()->setRedirect($this->getUrl('*/*/', array('store' => $this->getRequest()->getParam('store'))));
    }

    /* download deal list */

    public function downloadAction() {
        $template = $this->CSVFile();
        if ($template != '') {
            $fileName = 'dealList.csv';
            $content = $template;
            $this->_prepareDownloadResponse($fileName, $content);
        } else {
            $this->_getSession()->addError("No orders Found for that Deal !");
            $this->getResponse()->setRedirect($this->getUrl('*/*/', array('store' => $this->getRequest()->getParam('store'))));
        }
    }

    /* download deal list */

    public function couponAction() {
        $productId = $this->getRequest()->getParam('id');
        $storeId = $this->getRequest()->getParam('store');
        $redirectBack = $this->getRequest()->getParam('back', false);
        $model = Mage::getModel('catalog/product'); //getting product model
        $productdetail = $model->load($productId);
        $data = $this->getRequest()->getPost();
        $current_product = $productdetail->getId();
        $currentproductname = $productdetail->getName();
        $totalcoupon = $productdetail->getTargetNumber();
        $currentproductimage = Mage::helper('catalog/image')->init($productdetail, 'image')->resize(386, 338);

        //currency symbol
        $currencySymbol = Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol();

        $discount_price = $productdetail->getPrice() - $productdetail->getSpecialPrice();
        $discount = ($discount_price * 100) / $productdetail->getPrice();
        $discount = round($discount);
        $currentdiscount = $discount;
        $Couponvalid = $productdetail->getEnjoyby();
        $Companywebsite = $productdetail->getCustomer_website();
        $Companyname = $productdetail->getCustomersite();
        $product_worth = $productdetail->getSpecialPrice();

        $product_description = $productdetail->getDescription();
        $fineprint = $productdetail->getFineprint();

        $resource = Mage::getSingleton('core/resource');
        $giftemail = $resource->getConnection('core_write');



        $tprefix = (string) Mage::getConfig()->getTablePrefix();

        $total_orders = "0";
        $read = $resource->getConnection('catalog_read');
        $orderTable = $resource->getTableName('sales/order');
        // $dealStatus[2] ="pending";
        $dealStatus[1] = "processing";
        $dealStatus[0] = "complete";
        $select = $read->select()
                        ->from(array('cp' => $orderTable))
                        ->where('cp.status IN (?) ', $dealStatus);

        $orders_list = $read->fetchAll($select);
        $fetch_list = array();
        $quantity = array();
        $quantity[0] = "0";
        $cmail = array();

        if (count($orders_list) > 0) {
            $i = 0;
            foreach ($orders_list as $rows) {

                $order_id = $rows['entity_id'];
                $orderId = $rows['increment_id'];
                $order = Mage::getModel('sales/order')->load($order_id);
                $items = $order->getAllItems();
                $qty = array();

                foreach ($items as $itemId => $item) {
                    if ($current_product == $item->getProductId()) {
                        $total_orders = $total_orders + 1;
                        $qty[0] = $item->getQtyOrdered();
                        $cmail[$i][1] = $qty[0];
                        $cmail[$i][3] = Mage::getBaseUrl() . 'sales/order/print/order_id/' . $order_id . '/';
                        $customerId = $order->getCustomerId();

                        $resultcustomerid = $giftemail->fetchRow("Select *  from " . $tprefix . "gift_message  where customer_id = '$customerId'  AND product_id ='$productId' AND order_id = $order_id ");
                        if (isset($resultcustomerid['email'])) {
                            $cmail[$i][0] = $resultcustomerid['email'];
                            $cmail[$i][2] = $resultcustomerid['recipient'];
                            $cmail[$i][10] = $resultcustomerid['message'];
                            $cmail[$i][11] = $resultcustomerid['sender'] . ' has sent you a Gift.';
                        } else {
                            $cmail[$i][0] = $order->getCustomerEmail();
                            $cmail[$i][2] = $order->getCustomerName();
                            $cmail[$i][10] = "";
                            $cmail[$i][11] = '';
                        }
                        $cmail[$i][4] = $order->getGrandTotal();
                        $cmail[$i][6] = $order_id;
                        $cmail[$i][7] = $item->getQtyOrdered();
                        $cmail[$i][8] = $orderId;
                        $quantity[0] = $quantity[0] + $qty[0];
                        $i++;
                    }
                }
            }

            $emailto = "";
            if (count($cmail)) {
                foreach ($cmail as $cmail1) {

                    $customer_name = $cmail1[2];
                    $customerName = str_replace("'", "", $customer_name);
                    $tocustomer = $cmail1[0];
                    if ($quantity[0] < $totalcoupon) {

                        $postObject = new Varien_Object();
                        $storeName = Mage::getStoreConfig("design/head/default_title");
                        //$postObject->setData(array('content' => $post['content']));
                        $postObject->setData(array('storename' => $storeName, 'productname' => $currentproductname, 'purchasedqty' => $quantity[0], 'quantity' => $totalcoupon));
                        $mailTemplate = Mage::getModel('core/email_template');
                        $mailTemplate->setSenderName(Mage::getStoreConfig('design/head/default_title'));
                        $mailTemplate->setSenderEmail(Mage::getStoreConfig(self::XML_PATH_EMAIL_RECIPIENT));
                        $mailTemplate->setTemplateSubject('Coupon Confirmation From ' . Mage::getStoreConfig('design/head/default_title') . ' Order Quantity No:' . $i);

                        /* @var $mailTemplate Mage_Core_Model_Email_Template */
                        $mailTemplate->setDesignConfig(array('area' => 'frontend'))
                                ->sendTransactional(
                                        Mage::getStoreConfig(self::XML_PATH_NO_EMAIL_TEMPLATE),
                                        Mage::getStoreConfig(self::XML_PATH_EMAIL_SENDER),
                                        $tocustomer,
                                        $customerName,
                                        array('deallist' => $postObject)
                        );

                        if (!$mailTemplate->getSentSuccess()) {
                            $this->_getSession()->addError("There is a problem in Sending Mail! Email not Sent!");
                            $this->_redirect('*/*/');
                            return;
                        } else {
                            $emailto = "success";
                        }
                    } else {
                        $inserting = "";
                        $orderid = $cmail1[6];
                        $orderedqty = floor($cmail1[7]);
                        $message = $cmail1[11] . '<br>' . $cmail1[10] . '<br>';
                        $subName = $cmail1[11];
                        for ($i = 1; $i <= $orderedqty; $i++) {
                            $giftCouponCheck = $giftemail->fetchRow("select * from " . $tprefix . "ordercustomer where order_id ='$orderid' and quantitynumber = '$i' ");
                            if (isset($giftCouponCheck['uniqueid'])) {
                                $uniquenumber = $giftCouponCheck['uniqueid'];
                                $inserting = "No";
                            } else {
                                $random_chars = "";
                                $characters = array(
                                    "A", "B", "C", "D", "E", "F", "G", "H", "J", "K", "L", "M",
                                    "N", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z",
                                    "1", "2", "3", "4", "5", "6", "7", "8", "9");

                                $keys = array();
                                while (count($keys) < 9) {
                                    $x = mt_rand(0, count($characters) - 1);
                                    if (!in_array($x, $keys)) {
                                        $keys[] = $x;
                                    }
                                }

                                foreach ($keys as $key) {
                                    $random_chars .= $characters[$key];
                                }

                                $uniquenumber = $random_chars;
                                $inserting = "Yes";
                            }


                            $postObject = new Varien_Object();
                            if ($subName == '') {
                                $subjectname = 'Coupon Confirmation From ' . Mage::getStoreConfig('design/head/default_title') . ' Order Quantity No:' . $i;
                            } else {
                                $subjectname = $subName;
                            }


                            $postObject->setData(array('total' => $cmail1[4], 'realorderid' => $cmail1[8], 'product_description' => $product_description, 'customer_name' => $customer_name, 'productname' => $currentproductname, 'couponcode' => $uniquenumber, 'productimage' => $currentproductimage, 'discount' => $discount, 'couponvalid' => date('F j, Y', strtotime($Couponvalid)), 'companywebsite' => $Companywebsite, 'fineprint' => $fineprint, 'company_address' => $Companyname, 'currency_symbol' => $currencySymbol, 'product_worth' => floor($product_worth), 'quantity' => $orderedqty, 'message' => $message, 'subjectname' => $subjectname));

                            $mailTemplate = Mage::getModel('core/email_template');
                            $mailTemplate->setSenderName(Mage::getStoreConfig('design/head/default_title'));
                            $mailTemplate->setSenderEmail(Mage::getStoreConfig(self::XML_PATH_EMAIL_RECIPIENT));
                            $mailTemplate->setTemplateSubject('Coupon Confirmation From ' . Mage::getStoreConfig('design/head/default_title'));

                            /* @var $mailTemplate Mage_Core_Model_Email_Template */
                            $mailTemplate->setDesignConfig(array('area' => 'frontend'))
                                    ->sendTransactional(
                                            Mage::getStoreConfig(self::XML_PATH_COUPON_TEMPLATE),
                                            Mage::getStoreConfig(self::XML_PATH_EMAIL_SENDER),
                                            $tocustomer,
                                            $customerName,
                                            array('deallist' => $postObject)
                            );

                            if (!$mailTemplate->getSentSuccess()) {
                                $this->_getSession()->addError("There is a problem in Sending Mail! Email not Sent!");
                                $this->_redirect('*/*/');
                                return;
                            } else {
                                if ($inserting == "Yes") {
                                    $giftemail->query("insert into " . $tprefix . "ordercustomer (order_id,customer_name,product_name,quantity,uniqueid,quantitynumber,created_time,status )values ($orderid,'" . $customerName . "','" . addslashes($currentproductname) . "','" . $orderedqty . "','" . $uniquenumber . "'," . $i . ",now(), '1')");
                                }
                                $emailto = "success";
                            }
                        }
                    }
                }
            } else {
                $this->_getSession()->addError($this->__('No orders For this Product'));
                $this->_redirect('*/*/');
                return;
            }
            if ($emailto == "success") {

                $this->_getSession()->addSuccess($this->__('Deal Coupons sent to the Customers.'));
                $this->_redirect('*/*/');
                return;
            }
        } else {
            $this->_getSession()->addError($this->__('No orders'));
            $this->_redirect('*/*/');
            return;
        }


        unset($giftemail);
        unset($resource);
        unset($productdetail);

        if ($redirectBack) {
            $this->_redirect('*/*/edit', array(
                'id' => $productId,
                '_current' => true
            ));
        } else if ($this->getRequest()->getParam('popup')) {
            $this->_redirect('*/*/created', array(
                '_current' => true,
                'id' => $productId,
                'edit' => $isEdit
            ));
        } else {
            $this->_redirect('*/*/', array('store' => $storeId));
        }
    }

    /* CSV Format File */

    public function CSVFile() {
        $productId = (int) $this->getRequest()->getParam('id');
        $product = Mage::getModel('catalog/product')->load($productId);
        $resource = Mage::getSingleton('core/resource');
        $orderTable = $resource->getTableName('sales/order');
        $read = $resource->getConnection('read');
        $tprefix = (string) Mage::getConfig()->getTablePrefix();

        //checking if the coupons send for that particular product
        $flatorderTable = $tprefix . "sales_flat_order_item";
        $total_orders = "0";
        $dealStatus[1] = "processing";
        $dealStatus[0] = "complete";
        $select = $read->select()
                        ->from(array('cp' => $orderTable))
                        ->join(array('pei' => $flatorderTable), 'pei.order_id=cp.entity_id', array())
                        ->where('pei.product_id=?', $productId)
                        ->where('cp.status in (?)', $dealStatus);

        $orders_list = $read->fetchAll($select);
        $ordersListCount = count($orders_list);

        if ($ordersListCount > 0) {
            $quantity = array();
            $quantity[0] = "0";
            $i = 0;
            $customerdetails = array();
            foreach ($orders_list as $rows) {

                $order_id = $rows['entity_id'];
                $orderId = $rows['increment_id'];
                $order = Mage::getModel('sales/order')->load($order_id);
                $items = $order->getAllItems();
                $qty = array();

                foreach ($items as $itemId => $item) {
                    if ($productId == $item->getProductId()) {
                        $total_orders = $total_orders + 1;
                        $qty[0] = $item->getQtyOrdered();
                        $customerdetails[$i][1] = $qty[0];
                        $customerdetails[$i][3] = Mage::getBaseUrl() . 'sales/order/print/order_id/' . $order_id . '/';
                        $customerId = $order->getCustomerId();
                        $resultcustomerid = $read->fetchRow("Select email,recipient  from " . $tprefix . "gift_message  where customer_id = '$customerId'  AND product_id ='$productId' AND order_id = $order_id ");
                        if (isset($resultcustomerid['email'])) {
                            $customerdetails[$i][0] = $resultcustomerid['email'];
                            $customerdetails[$i][2] = $resultcustomerid['recipient'];
                        } else {
                            $customerdetails[$i][0] = $order->getCustomerEmail();
                            $customerdetails[$i][2] = $order->getCustomerName();
                        }
                        $customerdetails[$i][4] = $order->getGrandTotal();
                        // $customerdetails[$i][5] = $customerId;
                        $customerdetails[$i][6] = $order_id;
                        $customerdetails[$i][7] = $item->getQtyOrdered();
                        $customerdetails[$i][8] = $orderId;
                        $quantity[0] = $quantity[0] + $qty[0];
                        $i++;
                    }
                }
            }
            $uniquelist = "";
            $template = "";
            $template .= '"Order ID","Customer name","Unique Id","Deal Name","coupons purchased","Customer Email"' . "\n";
            $uniqeid = $tprefix . "ordercustomer";
            if (!empty($customerdetails)) {
                foreach ($customerdetails as $customerdetails1) {
                    $qtyselect = $read->select()
                                    ->from(array('cp' => $uniqeid), array('quantity' => 'cp.quantity'))
                                    ->where('cp.order_id=?', $customerdetails1[6]);
                    $itemqty = $read->fetchAll($qtyselect);
                    $total = $itemqty[0]['quantity'];

                    $select = $read->select()
                                    ->from(array('cp' => $uniqeid))
                                    ->where('cp.order_id=?', $customerdetails1[6]);

                    $uniquelist = $read->fetchAll($select);
                    $k = 0;
                    foreach ($uniquelist as $uniqueCuslist) {
                        if ($k == 0) {
                            $k = 1;
                        } else {
                            $uniqueCuslist['quantity'] = ' ';
                        }
                        $couponId = $read->fetchRow("select increment_id from " . $tprefix . "sales_flat_order where entity_id=" . $customerdetails1[6]);
                        $template .= '"' . $couponId['increment_id'] . '","' . $uniquelist['customer_name'] . '","' . $uniquelist['uniqueid'] . '","' . $uniquelist['product_name'] . '","' . $uniquelist['quantity'] . '","' . $customerdetails1[8] . '","' . $customerdetails1[0] . '"' . "\n";
                    }
                }
            }
        } else {
            $template = '';
        }
        return $template;
    }

    /**

     * Initialize product before saving

     */
    protected function _initProductSave() {
        $product = $this->_initProduct();
        $productData = $this->getRequest()->getPost('product');
        if ($productData && !isset($productData['stock_data']['use_config_manage_stock'])) {
            $productData['stock_data']['use_config_manage_stock'] = 0;
        }
        /**
         * Websites
         */
        if (!isset($productData['website_ids'])) {
            $productData['website_ids'] = array();
        }
        $wasLockedMedia = false;
        if ($product->isLockedAttribute('media')) {
            $product->unlockAttribute('media');
            $wasLockedMedia = true;
        }
        $product->addData($productData);
        if ($wasLockedMedia) {
            $product->lockAttribute('media');
        }
        if (Mage::app()->isSingleStoreMode()) {
            $product->setWebsiteIds(array(Mage::app()->getStore(true)->getWebsite()->getId()));
        }

        /**
         * Create Permanent Redirect for old URL key
         */
        if ($product->getId() && isset($productData['url_key_create_redirect'])) {

            // && $product->getOrigData('url_key') != $product->getData('url_key')
            $product->setData('save_rewrites_history', (bool) $productData['url_key_create_redirect']);
        }


        /**
         * Check "Use Default Value" checkboxes values
         */
        if ($useDefaults = $this->getRequest()->getPost('use_default')) {
            foreach ($useDefaults as $attributeCode) {
                $product->setData($attributeCode, false);
            }
        }



        /**
         * Init product links data (related, upsell, crosssel)
         */
        $links = $this->getRequest()->getPost('links');
        if (isset($links['related']) && !$product->getRelatedReadonly()) {
            $product->setRelatedLinkData(Mage::helper('adminhtml/js')->decodeGridSerializedInput($links['related']));
        }

        if (isset($links['upsell']) && !$product->getUpsellReadonly()) {
            $product->setUpSellLinkData(Mage::helper('adminhtml/js')->decodeGridSerializedInput($links['upsell']));
        }

        if (isset($links['crosssell']) && !$product->getCrosssellReadonly()) {
            $product->setCrossSellLinkData(Mage::helper('adminhtml/js')->decodeGridSerializedInput($links['crosssell']));
        }

        if (isset($links['grouped']) && !$product->getGroupedReadonly()) {
            $product->setGroupedLinkData(Mage::helper('adminhtml/js')->decodeGridSerializedInput($links['grouped']));
        }



        /**
         * Initialize product categories
         */
        $categoryIds = $this->getRequest()->getPost('category_ids');
        if (null !== $categoryIds) {
            if (empty($categoryIds)) {
                $categoryIds = array();
            }
            $product->setCategoryIds($categoryIds);
        }

        /**
         * Initialize data for configurable product
         */
        if (($data = $this->getRequest()->getPost('configurable_products_data')) && !$product->getConfigurableReadonly()) {
            $product->setConfigurableProductsData(Mage::helper('core')->jsonDecode($data));
        }

        if (($data = $this->getRequest()->getPost('configurable_attributes_data')) && !$product->getConfigurableReadonly()) {
            $product->setConfigurableAttributesData(Mage::helper('core')->jsonDecode($data));
        }
        $product->setCanSaveConfigurableAttributes((bool) $this->getRequest()->getPost('affect_configurable_product_attributes') && !$product->getConfigurableReadonly());
        /**
         * Initialize product options
         */
        if (isset($productData['options']) && !$product->getOptionsReadonly()) {
            $product->setProductOptions($productData['options']);
        }

        $product->setCanSaveCustomOptions((bool) $this->getRequest()->getPost('affect_product_custom_options') && !$product->getOptionsReadonly());
        Mage::dispatchEvent('catalog_product_prepare_save', array('product' => $product, 'request' => $this->getRequest()));
        return $product;
    }

    public function categoriesJsonAction() {
        $product = $this->_initProduct();
        $this->getResponse()->setBody(
                        $this->getLayout()->createBlock('adminhtml/catalog_product_edit_tab_categories')
                        ->getCategoryChildrenJson($this->getRequest()->getParam('category'))
        );
    }

    /**

     * Save product action

     */
    public function saveAction() {
        $storeId = $this->getRequest()->getParam('store');
        $redirectBack = $this->getRequest()->getParam('back', false);
        $productId = $this->getRequest()->getParam('id');
        $isEdit = (int) ($this->getRequest()->getParam('id') != null);

        $data = $this->getRequest()->getPost();
        if ($data) {
            if (!isset($data['product']['stock_data']['use_config_manage_stock'])) {
                $data['product']['stock_data']['use_config_manage_stock'] = 0;
            }
            $product = $this->_initProductSave();
            try {
                $product->save();
                if (!isset($productId)) {
                    $userId = Mage::getSingleton('admin/session')->getUser()->getId();

                    if ($userId != 1) {
                        $cities = array();
                        $attribute = Mage::getModel('eav/config')->getAttribute('catalog_product', '548'); // get the cities attribute id 562
                        // to get the multiple cities in pages(drop down)
                        foreach ($attribute->getSource()->getAllOptions(true, true) as $option) {
                            $value = $option['value'];
                            if ($value != '') {
                                $cities[$value] = $option['label'];
                            }
                        }
                        $userName = Mage::getSingleton('admin/session')->getUser()->getName();
                        $resource = Mage::getSingleton('core/resource');
                        $read = $resource->getConnection('read');
                        $tprefix = (string) Mage::getConfig()->getTablePrefix();
                        $cemail = $read->fetchRow("Select email  from " . $tprefix . "admin_user  where user_id = '1'");
                        $customeremail = $cemail['email'];
                        $productName = $data['product']['name'];
                        $city = $cities[$data['product']['cities']];

                        //mail to admin
                        $postObject = new Varien_Object();
                        $postObject->setData(array('merchantname' => $userName, 'name' => $productName, 'city' => $city));
                        $mailTemplate = Mage::getModel('core/email_template');
                        $mailTemplate->setSenderName(Mage::getStoreConfig('design/head/default_title'));
                        $mailTemplate->setSenderEmail(Mage::getStoreConfig(self::XML_PATH_EMAIL_RECIPIENT));
                        $mailTemplate->setTemplateSubject("A New Deal From Merchant!");

                        /* @var $mailTemplate Mage_Core_Model_Email_Template */
                        $mailTemplate->setDesignConfig(array('area' => 'frontend'))
                                ->sendTransactional(
                                        Mage::getStoreConfig(self::MERCHANT_PATH_ADDDEAL_TEMPLATE),
                                        Mage::getStoreConfig(self::XML_PATH_EMAIL_SENDER),
                                        $customeremail,
                                        Mage::getStoreConfig('design/head/default_title'),
                                        array('merchant' => $postObject)
                        );
                    }
                }
                $productId = $product->getId();
                /**
                 * Do copying data to stores
                 */
                if (isset($data['copy_to_stores'])) {
                    foreach ($data['copy_to_stores'] as $storeTo => $storeFrom) {
                        $newProduct = Mage::getModel('catalog/product')
                                        ->setStoreId($storeFrom)
                                        ->load($productId)
                                        ->setStoreId($storeTo)
                                        ->save();
                    }
                }
                $this->_getSession()->addSuccess($this->__('The product has been saved.'));
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage())
                        ->setProductData($data);
                $redirectBack = true;
            } catch (Exception $e) {
                Mage::logException($e);
                $this->_getSession()->addError($e->getMessage());
                $redirectBack = true;
            }
        }
        if ($redirectBack) {
            $this->_redirect('*/*/edit', array(
                'id' => $productId,
                '_current' => true
            ));
        } else if ($this->getRequest()->getParam('popup')) {
            $this->_redirect('*/*/created', array(
                '_current' => true,
                'id' => $productId,
                'edit' => $isEdit
            ));
        } else {
            $this->_redirect('*/*/', array('store' => $storeId));
        }
    }

    /**
     * Create product duplicate
     */
    public function duplicateAction() {
        $productId = (int) $this->getRequest()->getParam('id');
        $product = Mage::getModel('catalog/product')->load($productId);
        try {
            $newProduct = $product->duplicate();
            $this->_getSession()->addSuccess($this->__('The product has been duplicated.'));
            $this->_redirect('*/*/edit', array('_current' => true, 'id' => $newProduct->getId()));
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_getSession()->addError($e->getMessage());
            $this->_redirect('*/*/edit', array('_current' => true));
        }
    }

    /**
     * @deprecated since 1.4.0.0-alpha2
     */
    protected function _decodeInput($encoded) {
        parse_str($encoded, $data);
        foreach ($data as $key => $value) {
            parse_str(base64_decode($value), $data[$key]);
        }
        return $data;
    }

    /**
     * Delete product action
     */
    public function deleteAction() {
        if ($id = $this->getRequest()->getParam('id')) {
            $product = Mage::getModel('catalog/product')
                            ->load($id);
            $sku = $product->getSku();
            try {
                $product->delete();
                $this->_getSession()->addSuccess($this->__('The product has been deleted.'));
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->getResponse()->setRedirect($this->getUrl('*/*/', array('store' => $this->getRequest()->getParam('store'))));
    }

    /**
     * Get tag grid
     */
    public function tagGridAction() {
        $this->getResponse()->setBody(
                        $this->getLayout()->createBlock('adminhtml/catalog_product_edit_tab_tag', 'admin.product.tags')
                        ->setProductId($this->getRequest()->getParam('id'))
                        ->toHtml()
        );
    }

    /**
     * Get alerts price grid
     */
    public function alertsPriceGridAction() {
        $this->getResponse()->setBody(
                $this->getLayout()->createBlock('adminhtml/catalog_product_edit_tab_alerts_price')->toHtml()
        );
    }

    /**
     * Get alerts stock grid
     */
    public function alertsStockGridAction() {
        $this->getResponse()->setBody(
                $this->getLayout()->createBlock('adminhtml/catalog_product_edit_tab_alerts_stock')->toHtml()
        );
    }

    public function addCustomersToAlertQueueAction() {
        $alerts = Mage::getSingleton('customeralert/config')->getAlerts();
        ;
        $block = $this->getLayout()
                        ->createBlock('adminhtml/messages', 'messages');
        $collection = $block
                        ->getMessageCollection();
        foreach ($alerts as $key => $val) {
            try {
                if (Mage::getSingleton('customeralert/config')->getAlertByType($key)
                                ->setParamValues($this->getRequest()->getParams())
                                ->addCustomersToAlertQueue()) {
                    $collection->addMessage(Mage::getModel('core/message')->success($this->__('Customers for alert %s were successfuly added to queue', Mage::getSingleton('customeralert/config')->getTitleByType($key))));
                }
            } catch (Exception $e) {
                $collection->addMessage(Mage::getModel('core/message')->error($this->__('An error occurred while adding customers for the %s alert. Message: %s', Mage::getSingleton('customeralert/config')->getTitleByType($key), $e->getMessage())));
                continue;
            }
        }
        print $block->getGroupedHtml();
        return $this;
    }

    public function addAttributeAction() {
        $this->_getSession()->addNotice(Mage::helper('catalog')->__('Please click on the Close Window button if it is not closed automatically.'));
        $this->loadLayout('popup');
        $this->_initProduct();
        $this->_addContent(
                $this->getLayout()->createBlock('adminhtml/catalog_product_attribute_new_product_created')
        );
        $this->renderLayout();
    }

    public function createdAction() {
        $this->_getSession()->addNotice(Mage::helper('catalog')->__('Please click on the Close Window button if it is not closed automatically.'));
        $this->loadLayout('popup');
        $this->_addContent(
                $this->getLayout()->createBlock('adminhtml/catalog_product_created')
        );
        $this->renderLayout();
    }

    public function massDeleteAction() {
        $productIds = $this->getRequest()->getParam('product');
        if (!is_array($productIds)) {
            $this->_getSession()->addError($this->__('Please select product(s).'));
        } else {
            try {
                foreach ($productIds as $productId) {
                    $product = Mage::getSingleton('catalog/product')->load($productId);
                    Mage::dispatchEvent('catalog_controller_product_delete', array('product' => $product));
                    $product->delete();
                }

                $this->_getSession()->addSuccess(
                        $this->__('Total of %d record(s) have been deleted.', count($productIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * Update product(s) status action
     *
     */
    public function massStatusAction() {
        $productIds = (array) $this->getRequest()->getParam('product');
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        $status = (int) $this->getRequest()->getParam('status');

        try {
            Mage::getSingleton('catalog/product_action')
                    ->updateAttributes($productIds, array('status' => $status), $storeId);
            $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) have been updated.', count($productIds))
            );
        } catch (Mage_Core_Model_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('An error occurred while updating the product(s) status.'));
        }
        $this->_redirect('*/*/', array('store' => $storeId));
    }

    /**
     * Get tag customer grid
     *
     */
    public function tagCustomerGridAction() {
        $this->getResponse()->setBody(
                        $this->getLayout()->createBlock('adminhtml/catalog_product_edit_tab_tag_customer', 'admin.product.tags.customers')
                        ->setProductId($this->getRequest()->getParam('id'))
                        ->toHtml()
        );
    }

    public function quickCreateAction() {
        $result = array();
        /* @var $configurableProduct Mage_Catalog_Model_Product */
        $configurableProduct = Mage::getModel('catalog/product')
                        ->setStoreId(0)
                        ->load($this->getRequest()->getParam('product'));
        if (!$configurableProduct->isConfigurable()) {
            // If invalid parent product
            $this->_redirect('*/*/');
            return;
        }



        /* @var $product Mage_Catalog_Model_Product */
        $product = Mage::getModel('catalog/product')
                        ->setStoreId(0)
                        ->setTypeId(Mage_Catalog_Model_Product_Type::TYPE_SIMPLE)
                        ->setAttributeSetId($configurableProduct->getAttributeSetId());

        foreach ($product->getTypeInstance()->getEditableAttributes() as $attribute) {
            if ($attribute->getIsUnique()
                    || $attribute->getAttributeCode() == 'url_key'
                    || $attribute->getFrontend()->getInputType() == 'gallery'
                    || $attribute->getFrontend()->getInputType() == 'media_image'
                    || !$attribute->getIsVisible()) {
                continue;
            }



            $product->setData(
                    $attribute->getAttributeCode(),
                    $configurableProduct->getData($attribute->getAttributeCode())
            );
        }



        $product->addData($this->getRequest()->getParam('simple_product', array()));
        $product->setWebsiteIds($configurableProduct->getWebsiteIds());
        $autogenerateOptions = array();
        $result['attributes'] = array();

        foreach ($configurableProduct->getTypeInstance()->getConfigurableAttributes() as $attribute) {
            $value = $product->getAttributeText($attribute->getProductAttribute()->getAttributeCode());
            $autogenerateOptions[] = $value;
            $result['attributes'][] = array(
                'label' => $value,
                'value_index' => $product->getData($attribute->getProductAttribute()->getAttributeCode()),
                'attribute_id' => $attribute->getProductAttribute()->getId()
            );
        }

        if ($product->getNameAutogenerate()) {
            $product->setName($configurableProduct->getName() . '-' . implode('-', $autogenerateOptions));
        }

        if ($product->getSkuAutogenerate()) {
            $product->setSku($configurableProduct->getSku() . '-' . implode('-', $autogenerateOptions));
        }

        if (is_array($product->getPricing())) {
            $result['pricing'] = $product->getPricing();
            $additionalPrice = 0;
            foreach ($product->getPricing() as $pricing) {
                if (empty($pricing['value'])) {
                    continue;
                }

                if (!empty($pricing['is_percent'])) {
                    $pricing['value'] = ($pricing['value'] / 100) * $product->getPrice();
                }
                $additionalPrice += $pricing['value'];
            }

            $product->setPrice($product->getPrice() + $additionalPrice);
            $product->unsPricing();
        }

        try {
            /**
             * @todo implement full validation process with errors returning which are ignoring now
             */
            //            if (is_array($errors = $product->validate())) {
            //                $strErrors = array();
            //                foreach($errors as $code=>$error) {
            //                    $codeLabel = $product->getResource()->getAttribute($code)->getFrontend()->getLabel();
            //                    $strErrors[] = ($error === true)? Mage::helper('catalog')->__('Value for "%s" is invalid.', $codeLabel) : Mage::helper('catalog')->__('Value for "%s" is invalid: %s', $codeLabel, $error);
            //                }
            //                Mage::throwException('data_invalid', implode("\n", $strErrors));
            //            }
            $product->validate();
            $product->save();
            $result['product_id'] = $product->getId();
            $this->_getSession()->addSuccess(Mage::helper('catalog')->__('The product has been created.'));
            $this->_initLayoutMessages('adminhtml/session');
            $result['messages'] = $this->getLayout()->getMessagesBlock()->getGroupedHtml();
        } catch (Mage_Core_Exception $e) {
            $result['error'] = array(
                'message' => $e->getMessage(),
                'fields' => array(
                    'sku' => $product->getSku()
                )
            );
        } catch (Exception $e) {
            Mage::logException($e);
            $result['error'] = array(
                'message' => $this->__('An error occurred while saving the product. ') . $e->getMessage()
            );
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('catalog/products');
    }

    public function sendNewsletter($productId, $city) {
        $model = Mage::getModel('catalog/product'); //getting product model
        $productdetail = $model->load($productId); //print_r($productdetail);
        $current_product = $productdetail->getId();
        $currentproductname = $productdetail->getName();
        $currentproductimage = Mage::helper('catalog/image')->init($productdetail, 'image')->resize(386, 338);

        $discount_price = $productdetail->getPrice() - $productdetail->getSpecialPrice();
        $product_saveprice = $discount_price;
        $discount = ($discount_price * 100) / $productdetail->getPrice();
        $discount = round($discount);
        $currentdiscount = $discount;
        $Companywebsite = $productdetail->getCustomer_website();
        $Companyname = $productdetail->getCustomersite();
        $product_worth = $productdetail->getPrice();
        $product_price = $productdetail->getSpecialPrice();
        $product_description = $productdetail->getDescription();

        $deal_date = date('M d Y', strtotime(now()));
        $resource = Mage::getSingleton('core/resource');
        $newsletterEmail = $resource->getConnection('core_write');
        $tprefix = (string) Mage::getConfig()->getTablePrefix();
        $newsletterTable = $resource->getTableName('newsletter/subscriber');
        $email_list = $newsletterEmail->fetchAll("Select subscriber_email from " . $tprefix . "newsletter_subscriber
                                                  where subscriber_city = '" . $city . "' AND subscriber_status =1");
        $i = 0;
        $tocustomer = array();
        if (count($email_list) > 0) {
            foreach ($email_list as $rows) {
                $tocustomer[$i] = $rows['subscriber_email'];
                $i++;
            }
        }

        $i = 0;
        $tocustomer = array();
        if (count($email_list) > 0) {
            foreach ($email_list as $rows) {
                $tocustomer[$i] = $rows['subscriber_email'];
                $i++;
            }
        }
        $postObject = new Varien_Object();
        $postObject->setData(array('deal_date' => $deal_date, 'product_description' => $product_description, 'product_saveprice' => $product_saveprice, 'productname' => $currentproductname, 'product_price' => floor($product_price), 'productimage' => $currentproductimage, 'discount' => $discount, 'companywebsite' => $Companywebsite, 'product_worth' => floor($product_worth), 'product_city' => $city, 'company_address' => $Companyname));
        $mailTemplate = Mage::getModel('core/email_template');
        $mailTemplate->setSenderName(Mage::getStoreConfig('design/head/default_title'));
        $mailTemplate->setSenderEmail(Mage::getStoreConfig(self::XML_PATH_EMAIL_RECIPIENT));
        $mailTemplate->setTemplateSubject('Newsletter Subscription From ' . Mage::getStoreConfig('design/head/default_title'));
        $mailTemplate->addBcc($tocustomer);
        /* @var $mailTemplate Mage_Core_Model_Email_Template */
        $mailTemplate->setDesignConfig(array('area' => 'frontend'))
                ->sendTransactional(
                        '3',
                        Mage::getStoreConfig(self::XML_PATH_EMAIL_SENDER),
                        Mage::getStoreConfig(self::XML_PATH_EMAIL_SENDER),
                        Mage::getStoreConfig('design/head/default_title'),
                        array('deallist' => $postObject)
        );
        if (!$mailTemplate->getSentSuccess()) {
            //error Message
        }
    }

}