<?php
/**
 * Author   : Contus Support
 *
 * * NOTICE OF LICENSE
 *
 * This source file is subject to the CONTUS ADVERT SYSTEM (REFER A FRIEND)
 * License, which extends the  GNU General Public License (GPL).
 * The CONTUS ADVERT SYSTEM (REFER A FRIEND) License is available at this URL:
 *      http://www.contussupport.com/magento/CONTUS-ADVERT-SYSTEM-LICENSE-COMMUNITY.txt
 *
 * DISCLAIMER
 *
 * By adding to, editing, or in any way modifying this code, CONTUS is
 * not held liable for any inconsistencies or abnormalities in the
 * behaviour of this code.
 * By adding to, editing, or in any way modifying this code, the Licensee
 * terminates any agreement of support offered by CONTUS, outlined in the
 * provided Sweet Tooth License.
 * Upon discovery of modified code in the process of support, the Licensee
 * is still held accountable for any and all billable time CONTUS spent
 * during the support process.
 * CONTUS does not guarantee compatibility with any other framework extension.
 * CONTUS is not responsbile for any inconsistencies or abnormalities in the
 * behaviour of this code if caused by other framework extension.
 * Also distributing the code is prohibited ,It is accountable if violated license agreement.
 */
class Gclone_Advertsystem_Model_Observer {

    protected $_discount;

    public function processInvite($observer) {
        $processName = Mage::app()->getFrontController()->getRequest()->getActionName();
        if ($processName != 'logout') {
            $invite = Mage::helper('advertsystem/adverts')->inviteAvailable();
            $resource = Mage::getSingleton('core/resource');
            $read = $resource->getConnection('read');
            $tPrefix = (string) Mage::getConfig()->getTablePrefix();
            $inviteTable = $tPrefix . 'advert_system_invite';
            $ruleTable = $tPrefix . 'advert_system_rule';
            $discountTable = $tPrefix . 'advert_system_discount';
            $customerTable = $tPrefix . 'customer_entity';
            $customerTableVarchar = $tPrefix . 'customer_entity_varchar';
            $customer = $observer->getEvent()->getCustomer();
            $select = $read->select()
                            ->from(array('ct' => $ruleTable), array('ct.*'));
            $rule = $read->fetchAll($select);
            $ruleType = $rule[0]['rule_type'];
            $refLimit = $rule[0]['referral_limit'];
            $discountType = $rule[0]['discount_type'];

            $friendId = $customer->getId();


            $selectCustomer = $read->select()
                            ->from(array('ct' => $customerTable), array('ct.*'))
                            ->where('ct.entity_id =? ', $friendId);
            $customerRecord = $read->fetchAll($selectCustomer);
            $customerEmail = $customerRecord[0]['email'];

            $selectCustomerVarchar = $read->select()
                            ->from(array('ct' => $customerTableVarchar), array('ct.*'))
                            ->where('ct.entity_id =? ', $friendId)
                            ->where('ct.attribute_id =?', 5);
            $customerRecordVarchar = $read->fetchAll($selectCustomerVarchar);

            if ($ruleType == 1) {
                if ($invite) {

                    $selectInviteCustomer = $read->select()
                                    ->from(array('ct' => $inviteTable), array('ct.customer_id'))
                                    ->where('ct.invite_id =?', $invite);
                    $inviteCustomerRecord = $read->fetchAll($selectInviteCustomer);
                    $inviteCustomerId = $inviteCustomerRecord[0]['customer_id'];
                    $customerName = $customerRecordVarchar[0]['value'];

                    if ((count($inviteCustomerRecord) > 0)) {
                        $updateInvite = $read->query("update $inviteTable set friend_id = $friendId,friend_email = '$customerEmail' where invite_id = $invite");
                        Mage::helper('advertsystem/adverts')->deleteCookie();
                    }
                }
            } else {
                if ($invite) {
                    $selectInviteCustomer = $read->select()
                                    ->from(array('ct' => $inviteTable), array('ct.customer_id'))
                                    ->where('ct.invite_id =?', $invite);
                    $inviteCustomerRecord = $read->fetchAll($selectInviteCustomer);
                    $inviteCustomerId = $inviteCustomerRecord[0]['customer_id'];
                    $customerName = $customerRecordVarchar[0]['value'];
                    if ((count($inviteCustomerRecord) > 0)) {
                        $updateInvite = $read->query("update $inviteTable set friend_email = '$customerEmail' where invite_id = $invite");
                        Mage::helper('advertsystem/adverts')->deleteCookie();
                    }
                }
            }
        }
    }

    public function redirectInvitation() {
        $advert = Mage::app()->getRequest()->getCookie('advertClicked', null);
        $advert = (int) base64_decode(rawurldecode($advert));
        $cookie = Mage::getSingleton('core/cookie');
        if ($advert == 1) {
            Mage::app()->getFrontController()->getResponse()->setRedirect('advertsystem/index/invitation');
        }
    }

    public function updatePurchaseDiscount() {
        $this->purchaseRuleDiscount();
        $this->processUpdateDiscount();
        $resource = Mage::getSingleton('core/resource');
        $read = $resource->getConnection('read');
        $tPrefix = (string) Mage::getConfig()->getTablePrefix();
        $inviteTable = $tPrefix . 'advert_system_invite';
        $ruleTable = $tPrefix . 'advert_system_rule';
        $discountTable = $tPrefix . 'advert_system_discount';
        $session = Mage::getSingleton('customer/session');

        $select = $read->select()
                        ->from(array('ct' => $ruleTable), array('ct.*'));
        $rule = $read->fetchAll($select);
        $refLimit = $rule[0]['referral_limit'];
        $inviteLimit = $rule[0]['max_invite_limit'];
        $discountAmount = $rule[0]['discount_amount'];
        $percentAmount = $rule[0]['percent_amount'];
        $MaxpercentAmount = $rule[0]['max_percent_limit'];
        $purchaseType = $rule[0]['purchase_type'];
        $discountType = $rule[0]['discount_type'];

        $custId = $session->getId();
        $selectInvite = $read->select()
                        ->from(array('ct' => $inviteTable), array('ct.*'))
                        ->where('ct.customer_id =? ', $custId)
                        ->where('ct.expired =?', 0);
        $inviteRecord = $read->fetchAll($selectInvite);

        $orderGridTable = $resource->getTableName('sales/order_grid');
        $orderItemTable = $resource->getTableName('sales/order_item');
        $orderStatus[0] = "processing";
        $orderStatus[1] = "complete";

        $quantity = 0;
        if (count($inviteRecord) > 0) {
            foreach ($inviteRecord as $invite) {
                $friendId = $invite['friend_id'];
                switch ($rule[0]['rule_type']) {
                    case 1:
                        if ($friendId != 0) {
                            $quantity += 1;
                        }
                        break;
                    case 2:
                        $orderDetails = $read->select()
                                        ->from(array('ot' => $orderGridTable), array('ot.*'))
                                        ->join(array('pei' => $orderItemTable), 'pei.order_id=ot.entity_id', array('pei.qty_ordered'))
                                        ->where('ot.customer_id =? ', $friendId);
                                        //->where('ot.status IN (?) ', $orderStatus);
                        $orderDetails = $read->fetchAll($orderDetails);
                        if (count($orderDetails) > 0) {
                            if ($purchaseType == 1) {
                                $quantity += count($orderDetails);
                            } elseif ($purchaseType == 2) {
                                $quantity += $orderDetails[0]['qty_ordered'];
                            }
                        }
                        break;
                    default:
                        break;
                }
            }
            if ($quantity >= $refLimit) {

                $total = floor($quantity / $refLimit);

                $selectDiscount = $read->select()
                                ->from(array('ct' => $discountTable), array('ct.*'))
                                ->where('ct.customer_id =? ', $custId);
                $discountRecord = $read->fetchAll($selectDiscount);

                if (count($discountRecord) != 0 && $total > $discountRecord[0]['remaining']) {
                    $total = $discountRecord[0]['remaining'];
                }

                $check = $total * $refLimit;
                foreach ($inviteRecord as $invite) {
                    if ($check) {
                        $friendId = $invite['friend_id'];
                        if ($friendId != 0) {
                            $executeQuery = $read->query("update $inviteTable set expired = 1 where customer_id = $custId and friend_id=$friendId");
                            $check -= 1;
                        }
                    }
                }

                if ($discountType == 2) {
                    $discountTotalAmount = $percentAmount * $total;
                    if ($discountRecord[0]['percent'] != 0) {
                        $discountTotalAmount = $discountRecord[0]['percent'] + ($percentAmount * $total);
                    }
                    if ($discountTotalAmount < $MaxpercentAmount) {
                        if (count($discountRecord) > 0) {
                            $dicountRemaining = $discountRecord[0]['remaining'] - $total;
                            $executeQuery = $read->query("update $discountTable set percent = $discountTotalAmount, remaining = $dicountRemaining where customer_id = $custId");
                        } else {
                            $dicountRemaining = $inviteLimit - $total;
                            $executeQuery = $read->query("insert into $discountTable values ('',$custId,0,$discountTotalAmount,0,$dicountRemaining,1)");
                        }
                    }
                } else {
                $discountTotalAmount = $discountAmount * $total;
                if ($discountRecord[0]['amount'] != 0) {
                    $discountTotalAmount = $discountRecord[0]['amount'] + ($discountAmount * $total);
                }
                if (count($discountRecord) > 0) {
                    $dicountRemaining = $discountRecord[0]['remaining'] - $total;
                    $executeQuery = $read->query("update $discountTable set amount = $discountTotalAmount, remaining = $dicountRemaining where customer_id = $custId");
                } else {
                    $dicountRemaining = $inviteLimit - $total;
                        $executeQuery = $read->query("insert into $discountTable values ('',$custId,$discountTotalAmount,0,0,$dicountRemaining,1)");
                }
            }
        }
    }
    }

    public function purchaseRuleDiscount() {
        $resource = Mage::getSingleton('core/resource');
        $read = $resource->getConnection('read');
        $tPrefix = (string) Mage::getConfig()->getTablePrefix();
        $inviteTable = $tPrefix . 'advert_system_invite';
        $ruleTable = $tPrefix . 'advert_system_rule';
        $customerTable = $tPrefix . 'customer_entity';
        $orderGridTable = $resource->getTableName('sales/order_grid');
        $orderItemTable = $resource->getTableName('sales/order_item');
        $session = Mage::getSingleton('customer/session');
        $custId = $session->getId();

        $select = $read->select()
                        ->from(array('ct' => $ruleTable), array('ct.*'));
        $rule = $read->fetchAll($select);
        $ruleType = $rule[0]['rule_type'];
        $refLimit = $rule[0]['referral_limit'];

        $orderStatus[0] = "processing";
        $orderStatus[1] = "complete";

        if ($ruleType == 2) {

            $selectInvite = $read->select()
                            ->from(array('ct' => $inviteTable), array('ct.*'))
                            ->where('ct.customer_id =? ', $custId)
                            ->where('ct.friend_id =?', 0);
            $inviteRecord = $read->fetchAll($selectInvite);

            foreach ($inviteRecord as $invite) {
                $selectCustomer = $read->select()
                                ->from(array('ct' => $customerTable), array('ct.*'))
                                ->where('ct.email =? ', $invite['friend_email']);
                $customerRecord = $read->fetchAll($selectCustomer);
                $friendId = $customerRecord[0]['entity_id'];
                $orderDetails = $read->select()
                                ->from(array('ot' => $orderGridTable), array('ot.*'))
                                ->join(array('pei' => $orderItemTable), 'pei.order_id=ot.entity_id', array('pei.*'))
                                ->where('ot.customer_id =? ', $friendId)
                                ->where('ot.status IN (?) ', $orderStatus);
                $orderDetails = $read->fetchAll($orderDetails);

                $selectInvite = $read->select()
                                ->from(array('ct' => $inviteTable), array('ct.*'))
                                ->where('ct.friend_email =? ', $invite['friend_email']);
                $inviteRecord = $read->fetchAll($selectInvite);
                $inviteId = $inviteRecord[0]['invite_id'];

                if ((count($inviteRecord) > 0) && (count($orderDetails) > 0)) {
                    $updateInvite = $read->query("update $inviteTable set friend_id = $friendId where invite_id = $inviteId");
                }
            }
        }
    }

    public function processUpdateDiscount($observer) {
        $resource = Mage::getSingleton('core/resource');
        $read = $resource->getConnection('read');
        $tPrefix = (string) Mage::getConfig()->getTablePrefix();
        $quoteItemTable = $tPrefix.'sales_flat_quote_item';
        
        $quoteId = Mage::getSingleton('checkout/session')->getLastQuoteId();
        $selectQuote = $read->select()
                        ->from(array('ct' => $quoteItemTable), array('ct.discount_amount'))
                        ->where('ct.quote_id =? ', $quoteId);
        $customerQuote = $read->fetchAll($selectQuote);
       
       $discountQuoteAmount = ($customerQuote[0]['discount_amount']);
        
        $session = Mage::getSingleton('customer/session');
        $resource = Mage::getSingleton('core/resource');
        $read = $resource->getConnection('write');
        $tPrefix = (string) Mage::getConfig()->getTablePrefix();
        $discountTable = $tPrefix . 'advert_system_discount';
        $ruleTable = $tPrefix . 'advert_system_rule';
        $customerId = $session->getId();
        $selectRule = $read->select()
                        ->from(array('ct' => $ruleTable), array('ct.*'));
        $fetchRule = $read->fetchAll($selectRule);
        $limit = $fetchRule[0]['referral_limit'];
        $minAmount = $fetchRule[0]['min_order_amount'];

        $selectCustomer = $read->select()
                        ->from(array('ct' => $discountTable), array('ct.*'))
                        ->where('ct.customer_id = (?)', $customerId)
                        ->where('ct.used = (?)', 0);

        $fetchCustomer = $read->fetchAll($selectCustomer);
        (int) $discountId = $fetchCustomer[0]['discount_id'];
        $discountAmount = $fetchCustomer[0]['amount'];
        $percentAmount = $fetchCustomer[0]['percent'];
        $discountType = $fetchCustomer[0]['discount_type_priority'];
        if($discountType == 1){
            $amount = $fetchCustomer[0]['amount'] - $discountQuoteAmount;
            $setField = "amount =  '$amount'";
        }else{
            $amount = $fetchCustomer[0]['percent'] - $percentAmount;
            $setField = "percent =  '$amount'";
        }
       
        if (count($fetchCustomer) > 0 && $discountQuoteAmount != 0) {
            $up = $read->query("UPDATE $discountTable SET  $setField WHERE discount_id =" . $discountId);
            $this->discountCheckUpdate($discountId);
        }

        return $this;
    }

    public function discountCheckUpdate($discountId)
    {
        $resource = Mage::getSingleton('core/resource');
        $read = $resource->getConnection('read');
        $tPrefix = (string) Mage::getConfig()->getTablePrefix();
        $discountTable = $tPrefix . 'advert_system_discount';
        $selectDiscount = $read->select()
                        ->from(array('ct' => $discountTable), array('ct.*'))
                        ->where('ct.discount_id = (?)', $discountId);
        $fetchDiscount = $read->fetchAll($selectDiscount);
        if($fetchDiscount[0]['percent'] != 0){
            $read->query("UPDATE $discountTable SET  discount_type_priority = 2 WHERE discount_id =" . $discountId);
        }else{
            $read->query("UPDATE $discountTable SET  discount_type_priority = 1 WHERE discount_id =" . $discountId);
}
    }

}