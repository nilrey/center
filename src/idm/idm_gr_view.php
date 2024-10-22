<?php
/*
Get Group Info

GET /public/api/idm/31fdfd9e-2a54-4b90-9a5b-3986b17af2e5/group/61cef22d-3a2c-4286-a97d-54d550685348/ HTTP/1.1
Authorization: Explicit kraken@domain
Content-Type: application/json
Host: http://1adr-book02.dev.aorti.tech:8840
*/

include('sets.php');

$path = "/public/api/idm/{$set_id}/group/";
$url = $server_name.$path;

$error = array();
$http_method = 'GET';
echo "<p>url={$url}</p>";

// use key 'http' even if you send the request to https://...
$options = array(
    'http' => array(
        'header'  => "Authorization:Explicit {$ald_login}\r\nContent-type: application/json\r\n",
        'method'  => $http_method,
    )
);
$context  = stream_context_create($options);

// echo "<p>Begin of script</p>";
try {
	$result = file_get_contents($url, false, $context);

	if ($result === false) { 
		echo "<p>Error: Group was not created</p>";
		$error = error_get_last();
		var_dump($error);
	}else{
		echo "<p>Group Info:</p>";
		var_dump(json_decode($result)->data);
	}
}
catch (Exception $e) {
    echo $e->getMessage();
}


echo "<p>End of script</p>";
?>