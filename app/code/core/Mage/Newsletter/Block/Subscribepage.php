<?php
 /*
 * Created by selva 24/11/2010
 * email : selvakumar@contus.in
 * To mention the subscribe page template view.
 * */

class Mage_Newsletter_Block_Subscribepage extends Mage_Core_Block_Template
{
    public function _construct()
    {
        
        $this->setTemplate('newsletter/subscribepage.phtml');
    }
}

?>