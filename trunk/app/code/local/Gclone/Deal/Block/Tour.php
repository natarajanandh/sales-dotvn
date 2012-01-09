<?php
class Gclone_Deal_Block_Tour extends Mage_Core_Block_Template
{
     /**
     * Initialize template
     *
     */
    protected function _construct() {
        //$this->setTemplate('deal/deal.phtml');
    }

   
    /*
     *Contus Share Links
    */
    public function getsharelinks() {
        $resource1 = Mage::getSingleton('core/resource');
        $backgroundImage = $resource1->getConnection('core_write');
        $tprefix = (string)Mage::getConfig()->getTablePrefix();
        $currentImage = $backgroundImage->fetchRow("Select facebook,twitter,linkedin,facebookfans  from ".$tprefix."share ");
        return $currentImage;
    }
     
	public function getTours($offset = 0,$limit = 2)
    {
    	$data = array();
    	$_producs = array();
    	$city = Mage::getSingleton('core/session')->getCity();
    	$cat_id = $this->getCategoryId();;
		$attr = Mage::getModel('catalog/product')->getAttributes();
	    $collection = Mage::getModel('catalog/category')->load($cat_id)
	        ->getProductCollection()
	        ->addAttributeToSort('name', 'ASC');
	        
	    foreach($collection->getAllIds() as $lol)
		{
		    $_product=Mage::getModel('catalog/product')->load($lol);
		    $arr = explode(',',$_product->getData('city'));
		    foreach($arr as $_city){
		    	if($city==$_city){
		    		$_products[]=$_product;
		    	}
		    }
		}
		$data['total'] = count($_products);
		$data['rows'] = array_slice($_products,$offset,$limit);
		return $data;
    }
    
	public function getTourByCity($catId, $dealsCity,$current_page = 1,$limit = 5)
	{
		$data = array();
		
		$stockCollection = Mage::getModel('cataloginventory/stock_item')->getCollection()->addFieldToFilter('is_in_stock', 1);
    	$productIds = array();
            
	    foreach ($stockCollection as $item) {
	        $productIds[] = $item->getOrigData('product_id');
	    }
	    
	    //print_r($productIds);die();
        //$products = Mage::getresourcemodel("catalog/product_collection")->addAttributeToSelect("image")->addCategoryFilter(Mage::getmodel("catalog/category")->load($catId));
        $products = Mage::getresourcemodel("catalog/product_collection")->addCategoryFilter(Mage::getmodel("catalog/category")->load($catId));
        $products->addAttributeToFilter("City", array(
        	"finset" => $dealsCity
        ));
        $products->addFieldToFilter(array(
        	array(
            	"attribute" => "Status",
                "eq" => "1"
            )
        ));
        
        $products->addIdFilter($productIds);
        //$product->joinField('inventory_in_stock', 'cataloginventory_stock_item', 'is_in_stock', 'product_id=entity_id','is_in_stock>=0', 'left');
        $data['num_rows']	=	$products->getSize();
        $products->setPage($current_page,$limit);
        //echo '<pre>';
        $data['products']	=	$products;
        //var_dump($products);die();
        return $data;
	}
    
	public function pagination($url = '', $num_rows = 100, $limit = 10, $cur_page = 1, $show_num = 'yes'){
		$pagination = '';
		$total = ceil($num_rows/$limit);
		if($total > 1){
			$first = 0;
			$end = 0;
			if ($cur_page != 1) 
			{
				$first = $cur_page - 3;
				if ($first<=0) {$first = 1;$end = 4 -  $cur_page;}
				$end = $end + $cur_page + 3;
				if ($end > $total) 
				{
					$end = $total;
					$first = $total - 6;
					if ($first <= 0) {$first = 1;}
				}
			}
			else {
				$first = 1;$end = 7;
				if ($end > $total) {$end = $total;}
			}
			
			$pagination.=	"<div id='pagnav'>";
			$pagination.= "<ul>";
				
				if($cur_page>1)
				{
					$pagination .=	"<li>";
					if(($cur_page-1)==1)
						$pagination .= "<a class='' href='".$url.(strpos($url,'?') ? '&' : '?')."p=1"."'>Trang trước</a>";
					else
						$pagination .= "<a class='' href='".$url.(strpos($url,'?') ? '&' : '?')."p=". ($cur_page-1) ."'>Trang trước</a>";
					$pagination .=	"</li>";
				}
				else
				{
					$pagination .=	"<li><b>Trang trước</b></li>";
				}
				
				if($show_num == 'yes'){
					$j=0;
					for($i = $first; $i <= $end; $i++)
					{
						$j++;
						if($i==$cur_page)
						{
							$pagination .= "<li>";
							$pagination .= '<a class="current">'.$i.'</a>';
							$pagination .= "</li>";
						}
						else
						{
							$pagination .= "<li>";
							if($i==1)
								$pagination .= "<a title='' class='' href='".$url.(strpos($url,'?') ? '&' : '?')."p=1"."'>".$i."</a>";
							else
								$pagination .= "<a title='' class='' href='".$url.(strpos($url,'?') ? '&' : '?')."p=".$i."'>".$i."</a>";
							$pagination .= "</li>";
						}
						
					}
				}else{
					if($cur_page > 1 && $cur_page < $total)
						$pagination .= "";
				}
				
				
				
				if($cur_page<$total)
				{
					$pagination.= "<li>";
					$pagination.=	"<a class='' href='".$url.(strpos($url,'?') ? '&' : '?')."p=".($cur_page+1)."'>Trang sau</a>";
					$pagination.= "</li>";
				}
				else
				{
					$pagination .=	"<li><b>Trang sau</b></li>";
				}
				
				
			$pagination.= "</ul>";
			$pagination.= "</div>";
		}	
		return $pagination;
	}
    
	public function getFeaturedProduct($limit = null,$offset=null)
    {
        // instantiate database connection object
        $storeId = Mage::app()->getStore()->getId();
        $resource = Mage::getSingleton('core/resource');
        $read = $resource->getConnection('catalog_read');
        $categoryProductTable = $resource->getTableName('catalog/category_product');       
        $productEntityIntTable = (string)Mage::getConfig()->getTablePrefix() . 'catalog_product_entity_int';
        $ProductFlatTable = (string)Mage::getConfig()->getTablePrefix() . 'catalog_product_flat_1';
        $eavAttributeTable = $resource->getTableName('eav/attribute');
        // Query database for featured product
        $select = $read->select()
                       ->from(array('cp'=>$categoryProductTable))
                       ->join(array('pei'=>$productEntityIntTable), 'pei.entity_id=cp.product_id', array())
                       ->joinNatural(array('ea'=>$eavAttributeTable))
                       ->joinNatural(array('or'=>$ProductFlatTable))
                       ->where('pei.value=1')
                       ->where('ea.attribute_code="featured"')
                      ->group(array('cp.product_id'))
                     ->order(array('or.special_to_date desc'));

        $row = $read->fetchAll($select);
        $fetch_products = array();
     if( count($row)>0 ) foreach($row as $rows)
       {
           $fetch_products[] = Mage::getModel('catalog/product')->setStoreId($storeId)->load($rows['product_id']);    
       }      
       return $fetch_products;
    }
}
?>