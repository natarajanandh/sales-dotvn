<?php

class Gclone_Dealcoupon_Model_Dealcoupon extends Mage_Core_Model_Abstract {
    const XML_PATH_COUPON_TEMPLATE = 'dealcoupon/email/coupon_template';
    const XML_PATH_OWNER_TEMPLATE = 'dealcoupon/email/owner_template';
    const XML_PATH_NO_EMAIL_TEMPLATE = 'dealcoupon/email/email_template';
    const XML_PATH_EMAIL_RECIPIENT = 'contacts/email/recipient_email';
    const XML_PATH_EMAIL_SENDER = 'dealcoupon/email/sender_email_identity';

    public function _construct() {
        parent::_construct();
        $this->_init('dealcoupon/dealcoupon');
    }

    /*
     * Contus
     * Cron job method to sendcoupons to the buyers
     */

    public function sendCoupon() {

        $_productCollection = Mage::getModel('catalog/product')->getCollection();
        $_productCollection->addFieldToFilter(array(
            array('attribute' => 'Status', 'eq' => '1'),
        ));

        $count = count($_productCollection); //counting the total no of products
        $model = Mage::getModel('catalog/product');
        $tprefix = (string) Mage::getConfig()->getTablePrefix();
        $resource = Mage::getSingleton('core/resource');
        $currentdeal = $resource->getConnection('core_write');
        $read = $resource->getConnection('catalog_read');
        $orderTable = $resource->getTableName('sales/order');
        $orderItemTable = $resource->getTableName('sales/order_item');

        $currentTime = Mage_Core_Model_Locale::date(null, null, "en_US", true);

        //currency symbol
        $currencySymbol = Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol();

        //payment status for that product
        $dealstatus[0] = "processing";
        $dealstatus[1] = "complete";
        if ($count > 0) {
            foreach ($_productCollection as $_product) {
                $_product = $model->load($_product->getId());
                $productId = $_product->getId();
                
                $ProductToDate = $_product->getResource()->formatDate($_product->getspecial_to_date(), false);
                $ToDate = strtotime($ProductToDate);
                //checking for Time Expires

                $totalCoupon = $_product->getTargetNumber();

                $selectOrders = $read->select()
                                ->from(array('cp' => $orderTable), array('totalcount' => 'sum(cp.total_qty_ordered)'))
                                ->join(array('pei' => $orderItemTable), 'pei.order_id=cp.entity_id', array())
                                ->where('cp.status in (?)', $dealstatus)
                                ->where('pei.product_id in (?)', $productId);
                $Qtyorders_list = $read->fetchAll($selectOrders);

                $totalQtyPurchase = floor($Qtyorders_list[0]['totalcount']); //getting total no of purchase for that product

//                $resultProductId = $currentdeal->fetchRow("Select prod_id  from " . $tprefix . "cron_send_coupon  where prod_id = '$productId' ");
//                $prodCouponCount = count($resultProductId['prod_id']);

                //checking whether the deal is achieved
                //If not send the coupon for this product
                //if ($prodCouponCount != 0) {

                $currentproductimage = Mage::helper('catalog/image')->init($_product, 'image')->resize(386, 338);

                $discount_price = $_product->getPrice() - $_product->getSpecialPrice();
                $discount = ($discount_price * 100) / $_product->getPrice();
                $discount = round($discount);
                $currentdiscount = $discount;
                $Couponvalid = $_product->getEnjoyby();
                $Companywebsite = $_product->getCustomer_website();
                $Companyname = $_product->getCustomersite();
                $product_worth = $_product->getSpecialPrice();
                $currentproductname = $_product->getName();
                $product_description = $_product->getDescription();
                $fineprint = $_product->getFineprint();
                $total_orders = "0";
                $selectOrders = $read->select()
                                ->from(array('cp' => $orderTable))
                                ->join(array('pei' => $orderItemTable), 'pei.order_id=cp.entity_id', array())
                                ->where('cp.status in (?)', $dealstatus)
                                ->where('pei.product_id in (?)', $productId);

                $orders_list = $read->fetchAll($selectOrders);
                // $orders_list = $read->fetchAll($select);
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
                        $checkOrderCustomer = $currentdeal->fetchAll("select * from " . $tprefix . "ordercustomer where order_id ='$order_id'");
                        if (count($checkOrderCustomer) == 0) {
                            foreach ($items as $itemId => $item) {
                                if ($productId == $item->getProductId()) {
                                    $total_orders = $total_orders + 1;
                                    $qty[0] = $item->getQtyOrdered();
                                    $cmail[$i][1] = $qty[0];
                                    $cmail[$i][3] = Mage::getBaseUrl() . 'sales/order/print/order_id/' . $order_id . '/';
                                    $customerId = $order->getCustomerId();

                                    $resultcustomerid = $currentdeal->fetchRow("Select email,recipient  from " . $tprefix . "gift_message  where customer_id = '$customerId'  AND product_id ='$productId' AND order_id = $order_id ");

                                    if (isset($resultcustomerid['email'])) {
                                        $cmail[$i][0] = $resultcustomerid['email'];
                                        $cmail[$i][2] = $resultcustomerid['recipient'];
                                    } else {
                                        $cmail[$i][0] = $order->getCustomerEmail();
                                        $cmail[$i][2] = $order->getCustomerName();
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
                    }

                    $emailto = "";
                    if (count($cmail)) {
                        foreach ($cmail as $cmail1) {

                            $customer_name = $cmail1[2];
                            $customerName = str_replace("'", "", $customer_name);
                            $tocustomer = $cmail1[0];
                            //Deal Not Achieved
                            if ($quantity[0] < $totalCoupon) {
                                if (strtotime(date("Y-m-d", $ToDate) . ' ' . $_product->getTime()) < strtotime($currentTime)) {
                                    $resultProductId = $currentdeal->fetchRow("Select prod_id  from " . $tprefix . "cron_send_coupon  where prod_id = '$productId' ");
                                    $prodCouponCount = count($resultProductId['prod_id']);
                                    if ($prodCouponCount == 0) {
                                        //Dispatch Event for Discount in advert system, when deal is not acheived
                                        $discountAmount = $order->getDiscountAmount();
                                        $customerId = $order->getCustomerID();
                                        $discountAmount = preg_replace('/^-/', '', $discountAmount);
                                        $refundArray['discount'] = $discountAmount;
                                        $refundArray['customerId'] = $customerId;
                                        Mage::dispatchEvent('advert_credit_refund', $refundArray);

                                        $achieved = "no";
                                        $postObject = new Varien_Object();
                                        $storeName = Mage::getStoreConfig("design/head/default_title");

                                        $postObject->setData(array('storename' => $storeName, 'productname' => $currentproductname, 'purchasedqty' => $totalQtyPurchase, 'quantity' => $totalCoupon));

                                        $mailTemplate = Mage::getModel('core/email_template');
                                        $mailTemplate->setSenderName(Mage::getStoreConfig('design/head/default_title'));
                                        $mailTemplate->setSenderEmail(Mage::getStoreConfig(self::XML_PATH_EMAIL_RECIPIENT));
                                        $mailTemplate->setTemplateSubject('Coupon Confirmation From ' . Mage::getStoreConfig('design/head/default_title'));

                                        /* @var $mailTemplate Mage_Core_Model_Email_Template */
                                        $mailTemplate->setDesignConfig(array('area' => 'frontend'))
                                                ->sendTransactional(
                                                        Mage::getStoreConfig(self::XML_PATH_NO_EMAIL_TEMPLATE),
                                                        Mage::getStoreConfig(self::XML_PATH_EMAIL_SENDER),
                                                        $tocustomer,
                                                        $customerName,
                                                        array('deallist' => $postObject)
                                        );

                                        if ($mailTemplate->getSentSuccess()) {
                                            $cronCouponSend[] = "success";
                                        } else {
                                            $cronCouponSend[] = "failed";
                                        }
                                    }//check for product_id in cron send
                                }//Time Expiers
                            }
                            //Deal Achieved
                            else {
                                $achieved = "yes";
                                $inserting = "";
                                $orderid = $cmail1[6];
                                $orderedqty = floor($cmail1[7]);
                                $message = $cmail1[11] . '<br>' . $cmail1[10] . '<br>';
                                $subName = $cmail1[11];
                                for ($i = 1; $i <= $orderedqty; $i++) {
//                                    $giftCouponCheck = $currentdeal->fetchRow("select * from " . $tprefix . "ordercustomer where order_id ='$orderid' and quantitynumber = '$i' ");
//                                    if (isset($giftCouponCheck['uniqueid'])) {
//                                        $uniquenumber = $giftCouponCheck['uniqueid'];
//                                        $inserting = "No";
//                                    } else {
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
//                                    }


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
                                    $mailTemplate->setRecipientName('');
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

                                    if ($mailTemplate->getSentSuccess()) {

                                        if ($inserting == "Yes") {
                                            $currentdeal->query("insert into " . $tprefix . "ordercustomer (order_id,customer_name,product_name,quantity,uniqueid,quantitynumber,created_time,status )values ($orderid,'" . $customerName . "','" . addslashes($currentproductname) . "','" . $orderedqty . "','" . $uniquenumber . "',$i,now(),'1' )");
                                        }
                                        $cronCouponSend[] = "success";
                                    } else {
                                        $cronCouponSend[] = "failed";
                                    }
                                }
                            }//deal Achieved
                        }
                    }
                    if (in_array('success', $cronCouponSend)) {
                        $dealOwner = $_product->getcustomeremail();
                        //sending e-mail to DealOwner
                        if ($achieved == "no") {

                            $postObject = new Varien_Object();

                            $storeName = Mage::getStoreConfig("design/head/default_title");

                            $postObject->setData(array('storename' => $storeName, 'productname' => $currentproductname, 'purchasedqty' => $totalQtyPurchase, 'quantity' => $totalCoupon));
                            $mailTemplate = Mage::getModel('core/email_template');
                            $mailTemplate->setSenderName(Mage::getStoreConfig('design/head/default_title'));
                            $mailTemplate->setSenderEmail(Mage::getStoreConfig(self::XML_PATH_EMAIL_RECIPIENT));
                            $mailTemplate->setTemplateSubject(Mage::getStoreConfig('design/head/default_title') . ": Today's deal is not achieved!!");

                            /* @var $mailTemplate Mage_Core_Model_Email_Template */
                            $mailTemplate->setDesignConfig(array('area' => 'frontend'))
                                    ->sendTransactional(
                                            Mage::getStoreConfig(self::XML_PATH_NO_EMAIL_TEMPLATE),
                                            Mage::getStoreConfig(self::XML_PATH_EMAIL_SENDER),
                                            $dealOwner,
                                            Mage::getStoreConfig('design/head/default_title'),
                                            array('deallist' => $postObject)
                            );
                        } else {
                            $customerdetails = $cmail;
                            $uniquelist = "";
                            $Ownertemplate = "";
                            $Ownertemplate .= '
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
                                        $Ownertemplate .= '
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

                            $Ownertemplate .= '
</table>
';
                            $emailto = "";

                            $myFile = Mage::getBaseDir('export') . "/DealList.csv";
                            $fh = fopen($myFile, 'w') or die("can't open file");
                            $stringData = $this->CSVFile($productId);
                            fwrite($fh, $stringData);
                            fclose($fh);

                            $post['content'] = $Ownertemplate;
                            $emailTemplateVariables = array();
                            $postObject = new Varien_Object();

                            $postObject->setData(array('content' => $post['content']));

                            $mailTemplate = Mage::getModel('core/email_template');
                            $mailTemplate->setSenderName(Mage::getStoreConfig('design/head/default_title'));
                            $mailTemplate->setSenderEmail(Mage::getStoreConfig(self::XML_PATH_EMAIL_RECIPIENT));
                            $mailTemplate->setTemplateSubject('Coupon Confirmation From ' . Mage::getStoreConfig('design/head/default_title'));


                            $mailTemplate->setDesignConfig(array('area' => 'frontend'))
                                    ->sendTransactional(
                                            Mage::getStoreConfig(self::XML_PATH_OWNER_TEMPLATE),
                                            Mage::getStoreConfig(self::XML_PATH_EMAIL_SENDER),
                                            $dealOwner,
                                            Mage::getStoreConfig('design/head/default_title'),
                                            array('deallist' => $postObject), null,
                                            true);
                        }

                        $resultCoupon = $currentdeal->query("INSERT INTO " . $tprefix . "cron_send_coupon VALUES ('','" . $productId . "','1','" . $achieved . "')");
                    }
                }
                // } //already send
            }//checking date
        }//foreach
        
    }

//function sendCoupon

    /* CSV Format File */

    public function CSVFile($productId) {
        $product = Mage::getModel('catalog/product')->load($productId);
        $resource = Mage::getSingleton('core/resource');
        $orderTable = $resource->getTableName('sales/order');
        $read = $resource->getConnection('read');
        $tprefix = (string) Mage::getConfig()->getTablePrefix();

        //checking if the coupons send for that particular product
        $flatorderTable = $tprefix . "sales_flat_order_item";
        $total_orders = "0";

        $dealStatus[0] = "processing";
        $dealStatus[1] = "complete";
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
                        $resultcustomerid = $read->fetchRow("Select email,recipient  from " . $tprefix . "gift_message  where customer_id = '$customerId'");
                        if (isset($resultcustomerid['email'])) {
                            $customerdetails[$i][0] = $resultcustomerid['email'];
                            $customerdetails[$i][2] = $resultcustomerid['recipient'];
                        } else {
                            $customerdetails[$i][0] = $order->getCustomerEmail();
                            $customerdetails[$i][2] = $order->getCustomerName();
                        }
                        $customerdetails[$i][4] = $order->getGrandTotal();
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
            $template .= '"Order ID","Customer name","Coupon Code","Deal Name","coupons purchased","Customer Email"' . "\n";
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
                        $template .= '"' . $customerdetails1[8] . '","' . $uniqueCuslist['customer_name'] . '","' . $uniqueCuslist['uniqueid'] . '","' . $uniqueCuslist['product_name'] . '","' . $uniqueCuslist['quantity'] . '","' . $customerdetails1[0] . '"' . "\n";
                    }
                }
            }
        } else {
            $template = '';
        }
        return $template;
    }

}