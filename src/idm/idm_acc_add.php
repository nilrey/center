<?php
/*
Add Account to Group

POST /public/api/idm/31fdfd9e-2a54-4b90-9a5b-3986b17af2e5/account_group/ HTTP/1.1
Authorization: Explicit kraken@domain
Content-Type: application/json
Host: http://1adr-book02.dev.aorti.tech:8840
Content-Length: 104 
{
    "account_id": "gip@domain",
    "group_id": "0dbf965f-6575-4fe4-8a92-3f7d52e614c5",
    "is_allow": false
}

*/

include('sets.php');
if(empty($server_name)) die('No settings file found.');
if(empty($set_id)) die('No set defined.');

$group_id = '38410e9f-d4de-4e90-8a81-deea609ef846';
$account_add = 'knd_miv2@dev.aorti.tech';

$path = "/public/api/idm/{$set_id}/account_group/";
$url = $server_name.$path;
$error = array();
$http_method = 'POST';

$data = array(
    'account_id' => $account_add, 
    // 'account_id' => 'nuso_d_gsh@dev.aorti.tech', 
    'group_id' => $group_id,
    'set_id'=>$set_id,
    'is_allow'=> "false"
);
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

var_dump( $options);
// echo "<p>Begin of script</p>";
try {
	$result = file_get_contents($url, false, $context);

	if ($result === false) { 
		echo "<p>Error: Account was not added</p>\r\n";
		$error = error_get_last();
		var_dump($error);
	}else{
		echo "<p>Account was added successfuly</p>";
		var_dump(json_decode($result)->data);
	}
}
catch (Exception $e) {
    echo $e->getMessage();
}


//echo "<p>End of script</p>";
?>