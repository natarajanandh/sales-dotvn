<?php
class Rchar_Rchar_Block_Rchar extends Mage_Core_Block_Template {
    public function _prepareLayout() {
        return parent::_prepareLayout();
    }

    public function getRchar() {
        if(isset($_POST['rchar'])) {
            if( isset($_POST['emailcharity'])) {
                if (!eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$",$_POST['emailcharity'])) {
                    echo " <span style='color:red'>* Please Enter a Valid email Address</span><br>";
                    return ;
                }else {
                    $mail = new Zend_Mail();
                    $email = $_POST['emailcharity'];
                    $body = "User  had recommended this charity for you  <br>".$_POST['content'];
                    $mail->setBodyText($body);
                    $mail->setFrom($email, 'Groupclone');
                    $mail->addTo('nirmal@contus.in', 'Groupclone');
                    $mail->setSubject('Refered a Charity');
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