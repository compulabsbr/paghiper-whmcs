<?php
require("../../../init.php");
	$paramsboleto = $_SESSION['parametros'];
		
function httpPost($url,$params)
    {
        $postData = '';
        foreach($params as $k => $v) { $postData .= $k . '='.$v.'&'; }
        $postData = rtrim($postData, '&');
        $ch = curl_init();   
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch,CURLOPT_HEADER, false); 
        curl_setopt($ch, CURLOPT_POST, count($postData));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
        $output=curl_exec($ch); 
        curl_close($ch);
        return $output; 
    }

$boleto = httpPost("https://www.paghiper.com/checkout/",$paramsboleto);

echo $boleto;
?>
