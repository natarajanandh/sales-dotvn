<?php
/*
Simple IPN processing script
based on code from the "PHP Toolkit" provided by PayPal
 * Author  : Contussupport
 * Created by : Vijayakumar
*/

$url = 'https://www.paypal.com/cgi-bin/webscr';
$postdata = '';
$host='localhost'; //enter hosting details URL/IP
$user='user_group'; // enter the username to access the database
$password='welcome123'; //enter the password to access the database
$database='database_group'; //enter the database name

foreach($_POST as $i => $v) {
    $postdata .= $i.'='.urlencode($v).'&';
}
$postdata .= 'cmd=_notify-validate';

$web = parse_url($url);
if ($web['scheme'] == 'https') {
    $web['port'] = 443;
    $ssl = 'ssl://';
} else {
    $web['port'] = 80;
    $ssl = '';
}
$fp = @fsockopen($ssl.$web['host'], $web['port'], $errnum, $errstr, 30);

if (!$fp) {
    echo $errnum.': '.$errstr;

} else {
    fputs($fp, "POST ".$web['path']." HTTP/1.1\r\n");
    fputs($fp, "Host: ".$web['host']."\r\n");
    fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
    fputs($fp, "Content-length: ".strlen($postdata)."\r\n");
    fputs($fp, "Connection: close\r\n\r\n");
    fputs($fp, $postdata . "\r\n\r\n");

    while(!feof($fp)) {
        $info[] = @fgets($fp, 1024);
    }
    fclose($fp);

    $info = implode(',', $info);
    if (eregi('VERIFIED', $info)) {
             $con = mysql_connect($host,$user,$password);
        if (!$con)
        {
            die('Could not connect: ' . mysql_error());
        }
        mysql_select_db($database, $con);

        //Collecting data from Paypal
        $date = date("Y-m-d",strtotime(urldecode($_POST["payment_date"])));
        $payerId = $_POST["option_name1"];
        $payerEmail = $_POST["option_name2"];
        $transactionId = $_POST["txn_id"];
        $itemName = $_POST['item_name'];
        $itemId = $_POST['item_number'];
        $amount = $_POST["payment_gross"];
        $paymentStatus = $_POST["payment_status"];
        $payerStatus = $_POST["payer_status"];
        $currency = $_POST["mc_currency"];

        if($paymentStatus=='Completed' || $paymentStatus=='Processing'):
        $insertQuery = "INSERT INTO magentodeal_payment VALUES ('','$payerId','$payerEmail','$itemId','$amount','$date','$paymentStatus','no')";
        mysql_query($insertQuery);
       
        endif;
 
        mysql_close($con);

        // yes valid, f.e. change payment status
    } else {
        // invalid, log error or something
    }
    
    
}
?>