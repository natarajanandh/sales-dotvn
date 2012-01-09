<?php
class Gclone_Deal_Block_Products extends Mage_Core_Block_Template
{
	public function getProducts($catId, $dealsCity,$current_page = 1,$limit = 5)
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
			
			$pagination.=	"<div class='pagging-result'>";
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
	
	
}