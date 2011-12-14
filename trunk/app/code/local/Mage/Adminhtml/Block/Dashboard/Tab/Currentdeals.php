<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Mage_Adminhtml_Block_Dashboard_Tab_Currentdeals extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setTemplate('dashboard/currentdeals.phtml');
    }

    /* Returning the Current Deal List  */

    public function getcurrentdeals() {
        $_productCollection = Mage::getModel('catalog/product')->getCollection();
        $adminuser = Mage::getSingleton('admin/session')->getUser();
        $roleId = implode('', $adminuser->getRoles());
        $userId = $adminuser->getId();
        if ($roleId != 1) {
            $_productCollection->addAttributeToFilter('merchant', array('and' => $userId)); //for every merchants
        }
        $_listcurrentdeal = array();
        $i = 0;
        foreach ($_productCollection as $_product) {
            $current_product = $_product->getId();
            $categoryId = $_product->getCategoryIds();
            $model = Mage::getModel('catalog/product');
            $_product = $model->load($_product->getId());
            $inif = '0';
            $deal_status = $_product->getDealStatus();

            $cityname = $_product->getAttributeText('Cities');
            $Date = gmdate("Y-m-d  H:i:s", time()); //
            $start_date1 = strtotime($Date);

            /* Special fromdate */
            $fromDate = $_product->getResource()->formatDate($_product->getspecial_from_date(), false, true);
            $fromtime = strtotime($fromDate);
            $fromDate = gmdate('Y-m-d', $fromtime); //
            $fromDate = $fromDate . " 00:00:00";

            /* Special To date */
            $todayDate = $_product->getResource()->formatDate($_product->getspecial_to_date(), false);
            $todate = strtotime($todayDate);
            $expirydate = gmdate('Y-m-d', $todate); //
            $end_date1 = $expirydate . " " . $_product->gettime();
            $end_date1 = strtotime($end_date1);

            /* compare end Date */
            $compare_date = ($end_date1 - $start_date1) / (60 * 60 * 24);
            /* From Date Validation */
            $timeRemaining = $end_date1 - $start_date1;
            $startdate = strtotime($fromDate);
            $today = strtotime("now");
            $new_status = "not";

            if ($today > $startdate) {
                $new_status = "in";
            } elseif (($today - $startdate) >= 86400) {
                $new_status = "in";
            } else {
                $new_status = "not";
            }
            $productCategory = $_product->getCategoryIds();

            /* Calclulating Number of Upcoming,Current,Past For Main deal */
            if (in_array("3", $productCategory)) {
                if ($compare_date >= '0') {
                    if ($new_status == "in") {
                        $_listcurrentdeal[$current_product]['name'] = $_product->getname();
                        $_listcurrentdeal[$current_product]['id'] = $current_product;
                        $_listcurrentdeal[$current_product]['type'] = "Main Deal";
                        $_listcurrentdeal[$current_product]['target'] = $_product->gettarget_number();
                        $_listcurrentdeal[$current_product]['acheived'] = self::getOrderCount($current_product);
                        $_listcurrentdeal[$current_product]['remainingtime'] = self::timeConversion($timeRemaining);
                        $_listcurrentdeal[$current_product]['totalamount'] = self::getTotalAmount($current_product);
                    }
                }
            }

            /* Calclulating Number of Upcoming,Current,Past For Side deal */
            if (in_array("4", $productCategory)) {
                if ($compare_date >= '0') {
                    if ($new_status == "in") {
                        $_listcurrentdeal[$current_product]['name'] = $_product->getname();
                        $_listcurrentdeal[$current_product]['id'] = $current_product;
                        $_listcurrentdeal[$current_product]['type'] = "Side Deal";
                        $_listcurrentdeal[$current_product]['target'] = $_product->gettarget_number();
                        $_listcurrentdeal[$current_product]['acheived'] = self::getOrderCount($current_product);
                        $_listcurrentdeal[$current_product]['remainingtime'] = self::timeConversion($timeRemaining);
                        $_listcurrentdeal[$current_product]['totalamount'] = self::getTotalAmount($current_product);
                    }
                }
            }
            $i++;
        }
        return $_listcurrentdeal;
    }

    /*
     * Returning the Total Orders for a Particular product
     */

    public function getOrderCount($productId, $count=true) {
        $resource = Mage::getSingleton('core/resource');
        $read = $resource->getConnection('catalog_read');
        $orderTable = $resource->getTableName('sales/order');
        $orderItemTable = $resource->getTableName('sales/order_item');
        $dealstatus[0] = "processing";
        $dealstatus[1] = "pending";
        $dealstatus[2] = "complete";
        if ($count == true) {
            $select = $read->select()
                            ->from(array('cp' => $orderTable), array('totalcount' => 'sum(cp.total_qty_ordered)'))
                            ->join(array('pei' => $orderItemTable), 'pei.order_id=cp.entity_id', array())
                            ->where('cp.status in (?)', $dealstatus)
                            ->where('pei.product_id in (?)', $productId);
            $orders_list = $read->fetchAll($select);
            return floor($orders_list[0]['totalcount']);
        } else {
            $select = $read->select()
                            ->from(array('cp' => $orderTable))
                            ->join(array('pei' => $orderItemTable), 'pei.order_id=cp.entity_id', array())
                            ->where('cp.status in (?)', $dealstatus)
                            ->where('pei.product_id in (?)', $productId);
            $orders_list = $read->fetchAll($select);
            return $orders_list;
        }
    }

    /*
     * Converting the total mins into hrs and mins
     */

    public function timeConversion($gvnSeconds) {
        $hrs = (int) ( $gvnSeconds / 3600 );
        $mins = (int) ( ($gvnSeconds / 60) - ( $hrs * 60 ) );
        $secs = (int) ( $gvnSeconds % 60 );

        $daily = $hrs . " hrs: " . $mins . " mins";

        return $daily;
    }

    /* Returning Total Order Amount for a Particular Product */

    public function getTotalAmount($productId) {
        $orderList = $this->getOrderCount($productId, false);
        $total = 0;
        foreach ($orderList as $rows) {
            $order_id = $rows['entity_id'];
            $order = Mage::getModel('sales/order')->load($order_id);
            $total = $total + $order->getGrandTotal();
        }
        return $total;
    }

    /* URL to the Catalog Product Filter */

    public function getRowUrl($row) {
        return $this->getUrl('*/catalog_product/edit', array('id' => $row));
    }

}

?>
