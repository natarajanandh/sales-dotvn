<?php 
/*
Cong thanh toan truc tuyen NganLuong.vn
DUCLM-NGANLUONG.VN
*/
?>

<?php
	if (version_compare(phpversion(), '5.2.0', '<')===true) {
		echo  '<div style="font:12px/1.35em arial, helvetica, sans-serif;"><div style="margin:0 0 25px 0; border-bottom:1px solid #ccc;"><h3 style="margin:0; font-size:1.7em; font-weight:normal; text-transform:none; text-align:left; color:#2f2f2f;">Whoops, it looks like you have an invalid PHP version.</h3></div><p>Magento supports PHP 5.2.0 or newer. <a href="http://www.magentocommerce.com/install" target="">Find out</a> how to install</a> Magento using PHP-CGI as a work-around.</p></div>';
		exit;
	}
	
	include_once 'app/Mage.php';
	Mage::app();

$write = Mage::getSingleton('core/resource')->getConnection('wp_write');
// Func update 
function verifyPaymentUrl($transaction_info, $order_code, $price, $payment_id, $payment_type, $error_text, $secure_code, $merchant_site_code, $secure_pass)
{
	$str = '';
	$str .= ' ' . strval($transaction_info);
	$str .= ' ' . strval($order_code);
	$str .= ' ' . strval($price);
	$str .= ' ' . strval($payment_id);
	$str .= ' ' . strval($payment_type);
	$str .= ' ' . strval($error_text);
	$str .= ' ' . strval($merchant_site_code);
	$str .= ' ' . strval($secure_pass);

	$verify_secure_code = '';
	$verify_secure_code = md5($str);
	
	if ($verify_secure_code === $secure_code) return true;
	
	return false;
	}



	if(isset($_GET['secure_code']) && !empty($_GET['secure_code']))
	{
		$transaction_info = @$_GET['transaction_info'];
		$order_code = @$_GET['order_code'];
		$price = @$_GET['price'];
		$payment_id = @$_GET['payment_id'];
		$payment_type = @$_GET['payment_type'];
		$error_text = @$_GET['error_text'];
		$secure_code = @$_GET['secure_code'];
		$tb1 = Mage::getSingleton('core/resource')->getTableName('sales_flat_order_grid'); 
		$increment_id = intval($order_code);
		$orderInfo = $write->fetchAll("SELECT * FROM ".$tb1." WHERE increment_id=".$increment_id." LIMIT 0 , 1");
		
		$entity_id = $orderInfo[0]['entity_id'];
		$tb = Mage::getSingleton('core/resource')->getTableName('sales_flat_order_payment'); 	
		$query = "SELECT additional_data FROM ".$tb." WHERE entity_id=".$entity_id;
		$data = $write->fetchAll($query);
		
		$data_=unserialize($data['0']['additional_data']);
		$merchant_site_code = $data_["merchantID"];
		$secure_pass = $data_["secure_code"];
		
		$check = verifyPaymentUrl($transaction_info, $order_code, $price, $payment_id, $payment_type, $error_text, $secure_code, $merchant_site_code, $secure_pass);
			if($check === false)
			{
			echo 'Ket qua thanh toan khong hop le';
			}
			else
			{
		$table2 = Mage::getSingleton('core/resource')->getTableName('sales_flat_order_grid'); 	
		$table = Mage::getSingleton('core/resource')->getTableName('sales_flat_order'); 
		
		$tb = Mage::getSingleton('core/resource')->getTableName('sales_flat_order_grid'); 	
		$query = "SELECT * FROM ".$tb." WHERE entity_id=".$entity_id;
		$data = $write->fetchAll($query);
		
		$ordercode = $data[0]['increment_id'];
		$namebill = $data[0]['billing_name'];
		$pri = $data[0]['base_grand_total'];
				
		
		?>
		<style>
		*{ font-family:Arial, Helvetica, sans-serif;}
		a{ text-decoration:none; font-family:Arial, Helvetica, sans-serif; font-size:12px; color:#0000CC; }
		a:hover{ font-family:Arial, Helvetica, sans-serif; font-size:12px; color:#FF6600; }
		</style>
		<title>C&#7853;p nh&#7853;t thanh to&aacute;n</title>
        <link rel="shortcut icon" href="https://www.nganluong.vn/webskins/skins/nganluong/images/nl.gif" />
        <body style="text-align:center;vertical-align:top"><center>
		<table width="300" style="margin:100px;" border="0" cellspacing="0" cellpadding="0">
		  <tr>
			<td width="200"><a href="https://www.nganluong.vn/" title=""><img style="margin-bottom:20px;" src="https://www.nganluong.vn/data/images/developer/logo/logo-nganluong-179x39.gif" alt="Thanh toán an toàn, tiện lợi, được bảo vệ" border="0"/></a></td>
		    <td></td>
		  </tr>
		  <tr>
			<td align="right" height="40" colspan="2" style="border-bottom:#666666 dotted 1px; font-size:11px; color:#666666;">Tr&#7841;ng th&aacute;i thanh to&aacute;n</td>
		  </tr>
		  <tr>
			<td width="200" align="right" height="30"><strong style="font-size:12px;color:#666666;">Tr&#7841;ng th&aacute;i thanh to&aacute;n:</strong></td>
			 <td><i style="color:#006600; font-size:11px;">&nbsp; &radic; Th&agrave;nh c&ocirc;ng</i></td>
		  </tr>
		  <?php
		  
		  $u = $write->query("UPDATE {$table} SET status='complete' WHERE entity_id='{$entity_id}'");
		  $u1 = $write->query("UPDATE {$table2} SET status='complete' WHERE entity_id='{$entity_id}'");

		  if ($u) {
		  ?>
		  <tr>
			<td align="right" height="20"><strong style="font-size:12px;color:#666666;">Tr&#7841;ng th&aacute;i c&#7853;p nh&#7853;t h&oacute;a &#273;&#417;n:</strong></td>
			<td><i style="color:#006600; font-size:11px;">&nbsp; &radic;Th&agrave;nh c&ocirc;ng</i></td>
		  </tr>
		   <?php
		  } else {
		  ?>
		  <tr>
			<td align="right" height="20"><strong style="font-size:12px;color:#666666;">Tr&#7841;ng th&aacute;i c&#7853;p nh&#7853;t h&oacute;a &#273;&#417;n:</strong></td>
			<td><i style="color:#006600; font-size:11px; color:#FF6600;">&nbsp; &radic;M&aacute;y ch&#7911; t&#7915; ch&#7889;i k&#7871;t n&#7889;i</i></td>
		  </tr>
		  <?php
		  }
		  ?>
		  <tr>
			<td align="right" height="40" colspan="2" style="border-bottom:#666666 dotted 1px; font-size:11px; color:#666666;">Th&ocirc;ng tin thanh to&aacute;n</td>
		  </tr>
		   <tr>
			<td width="200" align="right" height="30"><strong style="font-size:12px;color:#666666;">M&atilde; h&oacute;a &#273;&#417;n:</strong></td>
			 <td><span style="color:#006600; font-size:11px;">&nbsp; <?php echo $ordercode ; ?></span></td>
		  </tr>
		  
		  <tr>
			<td align="right" height="20"><strong style="font-size:12px;color:#666666;">S&#7889; ti&#7873;n:</strong></td>
			<td><span style="color:#006600; font-size:11px;">&nbsp; <?php echo ceil($pri); ?>
		    VN&#272;</span></td>
		  </tr>
		  
		   <tr>
			<td align="right" height="20"><strong style="font-size:12px;color:#666666;">Lo&#7841;i thanh to&aacute;n:</strong></td>
			<td><span style="color:#006600; font-size:11px;">&nbsp; <?php echo ($payment_type == "2") ? "<span style='color:#FF6600'>T&#7841;m gi&#7919;</span>" : "Thanh to&aacute;n ngay"?></span></td>
		  </tr>
          <tr>
			<td align="right" height="20"><strong style="font-size:12px;color:#666666;">C&#7843;nh b&aacute;o:</strong></td>
			<td><span style="color:#006600; font-size:11px;">&nbsp; <?php echo ($error_text != "") ? "<span style='color:#FF6600'>".$error_text."</span>" : "Kh&ocirc;ng"?></span></td>
		  </tr>
		  
		  <tr>
			<td align="right" height="30"><a href="/" title="Trang chủ">&laquo; Trang ch&#7911;</a>&nbsp; </td>
			<td>&nbsp; <a href="https://www.nganluong.vn/" title="Cong thanh toan truc tuyen NganLuong.vn">Ng&acirc;n L&#432;&#7907;ng &raquo;</a> </td>
		  </tr>
		</table>
		 
        </center>
        <?php
		
	}
}

	else { ?>
       
    

        <p></p><p></p><p></p>
<div align="left" style="margin-left:500px;border:medium;border-color:#F00;color:#F00">
    <dl>
    <dt>The page you requested was not found, and we have a fine guess why.</dt>
    <dd>
    <ul class="disc">
    <li>If you typed the URL directly, please make sure the spelling is correct.</li>
    <li>If you clicked on a link to get here, the link is outdated.</li>
    </ul></dd>
    </dl><br>
         <a href='/' title='Trang chủ'>Quay v&#7873; trang ch&#7911;�</a></div>

<?php }?>

</body>
</html>
