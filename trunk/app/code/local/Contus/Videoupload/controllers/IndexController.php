<?php
/**
 * Contus Support Interactive.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file GCLONE-LICENSE.txt.
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento 1.4.1.1 COMMUNITY edition
 * Contus Support does not guarantee correct work of this package
 * on any other Magento edition except Magento 1.4.1.1 COMMUNITY edition.
 * =================================================================
 */

/*
 * Contus Support Pvt Ltd.
 * created by Vasanth on nov 10 2010.
 * vasanth@contus.in
 * In this page used to create config,language,playlist xmls for hdflv player
 */
?>
<?php
class Contus_Videoupload_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }
    public function getSkinUrl($file=null, array $params=array())
    {
        return Mage::getDesign()->getSkinUrl($file, $params);
    }
    /*Create playlist Xml*/
    public function showAction()
    {
       /*Retrive video Details from videoupload table using property id*/
       
        $pid = $this->getRequest()->getParam('pid');
      
        $_videoCollection = Mage::getModel('videoupload/videoupload');
        $_videoCollection = $_videoCollection->getCollection();
       /*Filter videoCollection using product id and status is enable*/
        $_videoCollection->addFieldToFilter('entity_id',Array('eq'=>$pid));
        $_videoCollection->addFieldToFilter('status',Array('eq'=>'1'));        
          
        $playxml ='';
        /*Create playlist xml file for hdflv player*/
        ob_clean();
        header ("content-type: text/xml");
        echo '<?xml version="1.0" encoding="utf-8"?>';
        echo '<playlist autoplay="true" random="false">';
        $current_path= Mage::getBaseURL('media');

        foreach($_videoCollection as $rows)
        {
            $timage= $rows->getThumname();
            /*Check the video type from videoupload table. if video type uploaded*/
            if($rows->getvideoType()=='0')
            {
                $video = $current_path.$rows->getVideoname();
                $previewimage = $current_path.$rows->getThumname();
                $timage = $current_path.$rows->getThumname();

            }
            /*if video type url*/
            elseif($rows->getvideoType()=='1')
            {
                $video = $rows->getVideoname();
                $previewimage = $current_path.$rows->getThumname();
                $timage = $rows->getThumname();
            }
           
            $playxml .= '<mainvideo  url="'.$video.'" isLive ="false" allow_download="false" preroll_id="" postroll_id="" postroll="false" preroll="false" streamer="" Preview="'.$timage.'" hdpath="" thu_image="'.$timage.'" id="'.$rows->id.'" hd="false" >';
            $playxml .= '<title>';
            $playxml .= '<![CDATA['.$rows->getTitle().']]>';
            $playxml .= '</title>';
            $playxml .= '<tagline targeturl="'.$targeturl.'">';
            $playxml .= '</tagline>';
            $playxml .= '</mainvideo>';
           
        }
        echo $playxml1;
        echo $playxml;
        echo '</playlist>';
        exit();
    }
    /*Create Configuration Xml*/
    public function configAction()
    {

        /*Retrive Config Details from config table*/
        $id = $this->getRequest()->getParam('id');
        $pid = $this->getRequest()->getParam('pid');
        $licenseKey = Mage::getStoreConfig('license/license/license');
        $playlistOpen = Mage::getStoreConfig('videoupload/videoupload/playlist_open');
        $autoplay = Mage::getStoreConfig('videoupload/videoupload/autoplay');
        $normalScale =  Mage::getStoreConfig('videoupload/videoupload/normal_scale');
        $fullScreencale = Mage::getStoreConfig('videoupload/videoupload/full_scale');
        $logoPath = Mage::getStoreConfig('videoupload/logo/logo_src');
        $logoTarget = Mage::getStoreConfig('videoupload/logo/logo_target');
        $logoAlign = Mage::getStoreConfig('videoupload/logo/logo_align');
        $logoAlpha = Mage::getStoreConfig('videoupload/logo/logo_alpha');
        $volume = Mage::getStoreConfig('videoupload/videoupload/volume');
        $download = Mage::getStoreConfig('videoupload/videoupload/download');
        $skinAutohide = Mage::getStoreConfig('videoupload/videoupload/skin_autohide');
        $youtubeLogo = Mage::getStoreConfig('videoupload/videoupload/youtube');
        $stagecolor = '';
        /*get skin path fom js folder*/
        $check=Mage::getStoreConfig('videoupload/videoupload/skin');
        if(!empty($check)){
        $skin = $this->getSkinUrl('hdflvplayer/skin/'.Mage::getStoreConfig('videoupload/videoupload/skin').'.swf');
        }else{
           $skin = $this->getSkinUrl('hdflvplayer/skin/skin_black.swf');
        }
        $adXML = $this->getSkinUrl('hdflvplayer/xml/ads.xml');
        $midrollXML =$this->getSkinUrl('hdflvplayer/xml/midroll.xml');
        $playlist = Mage::getStoreConfig('videoupload/videoupload/show_playlist');
        $empeded = Mage::getStoreConfig('videoupload/videoupload/empeded');
        $timer = Mage::getStoreConfig('videoupload/videoupload/timer');
        $zoom = Mage::getStoreConfig('videoupload/videoupload/zoom');
        $fullScreen = Mage::getStoreConfig('videoupload/videoupload/full_screen');
        $email = Mage::getStoreConfig('videoupload/videoupload/email');
        $baseUrl = Mage::getStoreConfig('web/secure/base_url');
        $language = Mage::getBaseUrl().'videoupload/index/language';
        $playlistXML = Mage::getBaseUrl().'videoupload/index/show?pid='.$pid;
        /*Create config xml file for hdflv player*/
        ob_clean();
        header("content-type:text/xml;charset=utf-8");
        echo '<?xml version="1.0" encoding="utf-8"?>';
        echo '<config
                        license="' . $licenseKey . '"
                        autoplay="' . $autoplay . '"
                        playlist_open="' .$playlistOpen. '"
                        buffer="false"
                        normalscale="' . $normalScale . '"
                        fullscreenscale="' . $fullScreencale . '"
                        logopath="' . $logoPath . '"
                        logo_target="' . $logoTarget . '"
                        logoalign="' . $logoAlign . '"
                        logoalpha="'.$logoAlpha.'"
                        Volume="'.$volume.'"
                        preroll_ads="false"
                        midroll_ads="false"
                        postroll_ads="false"
                        HD_default="false"
                        Download="'.$download.'"
                        skin_autohide="' . $skinAutohide . '"
                        stagecolor="' . $stagecolor . '"
                        skin="' . $skin . '"
                        embed_visible="'.$empeded.'"
                        playlistXML="'.$playlistXML.'"
                        adXML = "' . $adXML . '"
                        scaleToHideLogo = "' . $youtubeLogo . '"
                        midrollXML = "' . $midrollXML . '"
                        debug="true"
                        languageXML="'.$language.'"
                        shareURL="'.Mage::getBaseUrl().'skin/frontend/default/default/hdflvplayer/email.php"
                        videoshareURL="'.Mage::getBaseUrl().'skin/frontend/default/default/hdflvplayer/videourl.php"
                        showPlaylist="' . $playlist . '"
                        vast_partnerid=""
                        vast="false"                       
                        UseYouTubeApi="flash">';
        echo '<timer>' . $timer . '</timer>';
        echo '<zoom>' . $zoom . '</zoom>';
        echo '<email>'.$email. '</email>';
        echo '<fullscreen>' . $fullScreen . '</fullscreen>';
        echo '</config>';
        exit;

    }
     /*Create Language Xml*/
    function languageAction()
    {
        ob_clean();
        header ("content-type: text/xml");
        echo '<?xml version="1.0" encoding="utf-8"?>';
        echo '<language>';
        echo'<play>';
        echo '<![CDATA['.$this->__('play').']]>';
        echo  '</play>';
        echo '<pause>';
        echo '<![CDATA['.$this->__('pause').']]>';
        echo '</pause>';
        echo '<hdison>';
        echo '<![CDATA['.$this->__('hdison').']]>';
        echo '</hdison>';
        echo '<hdisoff>';
        echo '<![CDATA['.$this->__('hdisoff').']]>';
        echo '</hdisoff>';
        echo '<zoom>';
        echo '<![CDATA['.$this->__('zoom').']]>';
        echo '</zoom>';
        echo'<share>';
        echo '<![CDATA['.$this->__('share').']]>';
        echo '</share>';
        echo'<fullscreen>';
        echo '<![CDATA['.$this->__('fullscreen').']]>';
        echo '</fullscreen>';
        echo'<relatedvideos>';
        echo '<![CDATA['.$this->__('relatedvideos').']]>';
        echo '</relatedvideos>';
        echo'<sharetheword>';
        echo '<![CDATA['.$this->__('sharetheword').']]>';
        echo '</sharetheword>';
        echo'<sendanemail>';
        echo '<![CDATA['.$this->__('sendanemail').']]>';
        echo '</sendanemail>';
        echo'<to>';
        echo '<![CDATA['.$this->__('to').']]>';
        echo '</to>';
        echo'<from>';
        echo '<![CDATA['.$this->__('from').']]>';
        echo '</from>';
        echo'<note>';
        echo '<![CDATA['.$this->__('note').']]>';
        echo '</note>';
        echo'<send>';
        echo '<![CDATA['.$this->__('send').']]>';
        echo '</send>';
        echo'<copylink>';
        echo '<![CDATA['.$this->__('copylink').']]>';
        echo '</copylink>';
        echo'<copyembed>';
        echo '<![CDATA['.$this->__('copyembed').']]>';
        echo '</copyembed>';
        echo'<facebook>';
        echo '<![CDATA['.$this->__('facebook').']]>';
        echo '</facebook>';
        echo'<reddit>';
        echo '<![CDATA['.$this->__('reddit').']]>';
        echo '</reddit>';
        echo'<friendfeed>';
        echo '<![CDATA['.$this->__('friendfeed').']]>';
        echo '</friendfeed>';
        echo'<slashdot>';
        echo '<![CDATA['.$this->__('slashdot').']]>';
        echo '</slashdot>';
        echo'<delicious>';
        echo '<![CDATA['.$this->__('delicious').']]>';
        echo '</delicious>';
        echo'<myspace>';
        echo '<![CDATA['.$this->__('myspace').']]>';
        echo '</myspace>';
        echo'<wong>';
        echo '<![CDATA['.$this->__('wong').']]>';
        echo '</wong>';
        echo'<digg>';
        echo '<![CDATA['.$this->__('digg').']]>';
        echo '</digg>';
        echo'<blinklist>';
        echo '<![CDATA['.$this->__('blinklist').']]>';
        echo '</blinklist>';
        echo'<bebo>';
        echo '<![CDATA['.$this->__('bebo').']]>';
        echo '</bebo>';
        echo'<fark>';
        echo '<![CDATA['.$this->__('fark').']]>';
        echo '</fark>';
        echo'<tweet>';
        echo '<![CDATA['.$this->__('tweet').']]>';
        echo '</tweet>';
        echo'<furl>';
        echo '<![CDATA['.$this->__('furl').']]>';
        echo '</furl>';
        echo '<adindicator><![CDATA[Your selection will follow this sponsorss message in - seconds]]>';
        echo '</adindicator>';
        echo '<Skip><![CDATA[Skip this Video]]></Skip>';
        echo '<errormessage><![CDATA['.$this->__('errormessage').']]></errormessage>';
        echo '<buttonname><![CDATA['.$this->__('buttonname').']]></buttonname>';
        echo '</language>';
        exit();
    }
    public function categoryAction()
    {

        $id = $this->getRequest()->getParam('id');
        if($id == '')
        {
            echo "<select id='category_id' name='category_id' class='required-entry required-entry select' onchange='validate(this.value)'>";
            echo "<option value=''>Select</option>";
            echo "</select>";
        }else
        {
           echo "<select id='category_id' name='category_id' class='required-entry required-entry select' onchange='validate(this.value)'>";
           echo "<option value=''>Select Category</option>";
           $cat = Mage::getModel('catalog/category')->getCollection();;
                                        $cat->addFieldToFilter('parent_id',Array('eq'=>$id));
                                        foreach ($cat as $_cat)
                                        {
                                            $model = Mage::getModel('catalog/category');
                                            $_cat = $model->load($_cat->getId());
                                            echo "<option value=".$_cat->getId().">". $_cat->getName()."</option>";
                                        }
          echo "</select>";
                   }
    }
    public function productAction()
    {

        $id = $this->getRequest()->getParam('id');
        
        if($id == '')
        {
            echo "<select id='entity_id' name='entity_id' class='required-entry required-entry select'>";
            echo "<option value=''>Select</option>";
            echo "</select>";
        }else
        {
            echo "<select id='entity_id' name='entity_id' class='required-entry required-entry select'>";
            $_productCollection =Mage::getResourceModel('catalog/product_collection')->addCategoryFilter(Mage::getModel('catalog/category')->load($id));
echo "<option value=''>Select Product</option>";
            foreach ($_productCollection as $_product)
            {
                $model = Mage::getModel('catalog/product');
                $_product = $model->load($_product->getId());
                $selected = '';
                echo "<option value=".$_product->getId().">". $_product->getName()."</option>";
            }
            echo "</select>";
        }
    }
}

