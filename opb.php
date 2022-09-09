<?php

function random_ip(){
	$ips = ['46.227.123.', '37.110.212.', '46.255.69.', '62.209.128.', '37.110.214.', '31.135.209.', '37.110.213.'];
	$prefix = $ips[array_rand($ips)];
	return $prefix.rand(1, 255);
}

function api($method, $data){
	$ip = random_ip();
	$ch = curl_init( 'https://admin.openbudget.uz/api/v1/' . $method );
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
  	curl_setopt($ch, CURLOPT_REFERER, 'https://admin.openbudget.uz/api/v1/' . $method);
  	curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/105.0.0.0 Safari/537.36');
	curl_setopt( $ch, CURLOPT_HTTPHEADER, array("Host: admin.openbudget.uz", "REMOTE_ADDR: ".$ip, "HTTP_X_FORWARDED_FOR: ".$ip, "HTTP_X_REAL_IP: ".$ip, "X-Forwarded-For: " .$ip));
	
	$response = curl_exec($ch);
	$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	
	if(curl_errno($ch)){
    	$http_status == 0;
    	$response = [
    		'detail' => curl_error($ch)
    	];
	}else{
		$response = json_decode($response, TRUE);
	}
	curl_close($ch);

	return [
		'code' => $http_status,
		'data' => $response
	];
}

$data = api($_GET['method'], $_POST);
echo json_encode($data, JSON_PRETTY_PRINT);