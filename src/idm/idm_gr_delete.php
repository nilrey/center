<?php
/*
Delete Group

DELETE /public/api/idm/31fdfd9e-2a54-4b90-9a5b-3986b17af2e5/account_group/60e90a36-ebb8-4108-a2d8-05e959eb18e2/ HTTP/1.1
Authorization: Explicit kraken@domain
Content-Type: application/json
Host: http://1adr-book02.dev.aorti.tech:8840
*/

include('sets.php');

$group_id ="3ece988c-9900-4575-9818-771c667ee495";
if(!empty($_GET["gd_id"])) $group_id = $_GET["gd_id"]; // group delete id

$path = "/public/api/idm/{$set_id}/group/{$group_id}/";
$url = $server_name.$path;

$error = array();
$http_method = 'DELETE';

// echo "<p>url={$url}</p>";

// use key 'http' even if you send the request to https://...
$options = array(
    'http' => array(
        'header'  => "Authorization:Explicit {$ald_login}\r\nContent-type: application/json\r\n",
        'method'  => $http_method
    )
);
$context  = stream_context_create($options);

// echo "<p>Begin of script</p>";
try {
	$result = file_get_contents($url, false, $context);

	if ($result === false) { 
		echo "<p>Error: Group was not deleted</p>";
		$error = error_get_last();
		var_dump($error);
	}else{
		echo "<p>Group deleted</p>";
		var_dump(json_decode($result)->data);
	}
}
catch (Exception $e) {
    echo $e->getMessage();
}


echo "<p>End of script</p>";
?>