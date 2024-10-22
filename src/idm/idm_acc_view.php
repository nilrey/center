<?php
/*
Get Account Info

GET /public/api/idm/31fdfd9e-2a54-4b90-9a5b-3986b17af2e5/account_group/ HTTP/1.1
Authorization: Explicit kraken@domain
Content-Type: application/json
Host: http://1adr-book02.dev.aorti.tech:8840
*/

include('sets.php');

if(empty($server_name)) die('Error server name');

$path = "/public/api/idm/{$set_id}/account_group/";
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

// echo "<p>Begin of script</p>";
try {
    $result = file_get_contents($url, false, $context);
    // var_dump(json_decode($result)->data);

    if ($result === false) { 
        // echo "<p>Error: Account was not found</p>";
        $error = error_get_last();
        var_dump($error);
        die();
    }else{
        // echo "<p>Account Info:</p>";
        // var_dump (json_decode($result)->data);
        $arData = json_decode($result)->data;
        foreach ($arData as $key => $value) {
            echo "<br>[{$value->group_id}] [{$value->uid}] {$value->account_id}";
        }
    }
}
catch (Exception $e) {
    echo $e->getMessage();
}
// echo $url;
// echo "<p>End of script</p>";
?>