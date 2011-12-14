<?php
class Bd_Bd_Block_Bd extends Mage_Core_Block_Template {
    public function _prepareLayout() {
        return parent::_prepareLayout();
    }

    public function getBd() {
        if(isset($_POST['bd'])) {
            if( isset($_POST['emailbusiness'])) {
                if (!eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$",$_POST['emailbusiness'])) {
                    echo " <span style='color:red'>* Please Enter a Valid email Address</span><br>";
                    return ;
                }else {
                    $mail = new Zend_Mail();
                    $bdemail = $_POST['emailbusiness'];
                    $body = "User  had recommended business for you ".$_POST['bdrecommend'];
                    $mail->setBodyText($body);
                    $mail->setFrom($bdemail, 'Groupclone');
                    $mail->addTo('nirmal@contus.in', 'Groupclone');
                    $mail->setSubject('Business Recommendations');
                    try {
                        $mail->send();
                        return "success";
                    }
                    catch(Exception $ex) {
                        return "failed";
                        Mage::getSingleton('core/session')->addError('Unable to send email.');
                    }
                }
            }
        }
    }
}