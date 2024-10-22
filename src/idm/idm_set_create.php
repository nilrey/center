<?php
/*
Create Set

POST /public/api/idm/set/ HTTP/1.1
Authorization: Explicit kraken@domain
Content-Type: application/json
Host: http://1adr-book02.dev.aorti.tech:8840
Content-Length: 24
{
  "name": "TestSet"
}
*/

include('sets.php');



$path = '/public/api/idm/set/';
$url = $server_name.$path;

$error = array();
$http_method = 'GET';
// echo "<p>url={$url}</p>";

// use key 'http' even if you send the request to https://...
$options = array(
    'http' => array(
        'header'  => "Authorization:Explicit {$ald_login}\r\nContent-type: application/json\r\n",
        'method'  => $http_method,
    )
);
$context  = stream_context_create($options);

$set_exists = false;
// echo "<p>Begin of script</p>";
try {
	$result = file_get_contents($url, false, $context);

	if ($result === false) { 
		echo "<p>Error: Set was not created</p>";
		$error = error_get_last();
		var_dump($error);
	}else{
		echo "<p>Set Info:</p>";
		$data =json_decode( $result )->data;
		foreach ( $data as $arValue) {
			if('CITIS_NCUO_USERS' == $arValue->name){
				// if ($debug) echo "<p>Set Id in SVIP exists</p>";
				$set_exists = true;
				echo "<p>".$arValue->name." [".$arValue->uid."]</p>";
			}
		}
	}
}
catch (Exception $e) {
    echo $e->getMessage();
}

if( !$set_exists ):
	echo "<p>Start to create set 'CITIS_NCUO_USERS' in SVIP...</p>";
	$path = '/public/api/idm/set/';
	$url = $server_name.$path;
	$data = array('name' => 'CITIS_NCUO_USERS');
	$error = array();
	$http_method = 'POST';
	// echo "<p>url={$url}</p>";

	// use key 'http' even if you send the request to https://...
	$options = array(
	    'http' => array(
	        'header'  => "Authorization:Explicit {$ald_login}\r\nContent-type: application/json\r\n",
	        'method'  => $http_method,
	        'content' => json_encode($data)
	    )
	);
	$context  = stream_context_create($options);

	// echo "<p>Begin of script</p>";
	try {
		$result = file_get_contents($url, false, $context);

		if ($result === false) { 
			echo "<p>Error: Set was not created</p>";
			$error = error_get_last();
			var_dump($error);
		}else{
			echo "<p>Set was created successfuly</p>";
			var_dump(json_decode($result)->data);
		}
	}
	catch (Exception $e) {
	    echo $e->getMessage();
	}
else:
	echo "<p>Set 'CITIS_NCUO_USERS' found in SVIP</p>";
endif;

echo "<p>End of script</p>";
?>