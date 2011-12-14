<?php
/**
 * Example View block
 *
 * @codepool   Local
 * @category   Fido
 * @package    Fido_Example
 * @module     Example
 */
class Fido_Example_Block_View extends Mage_Core_Block_Template {
    private $message;
    private $att;

    protected function createMessage($msg) {
        $this->message = $msg;
    }

    public function receiveMessage() {
        if($this->message != '') {
            return $this->message;
        } else {
            $resource = Mage::getSingleton('core/resource');
            $read = $resource->getConnection('charitydetail');
            $charity  =  $resource->getTableName('charitydetail');
          //   Query database for featured product
            $select = $read->select()
                    ->from(array('cp'=>$charity))
                    ->where('cp.status 	=1');
            $row = $read->fetchAll($select);

            $this->createMessage($row);
            return $this->message;


        }
    }

}
