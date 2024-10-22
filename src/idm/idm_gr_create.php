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

$path = "/public/api/idm/{$set_id}/group/";
$url = $server_name.$path;

$arRoles = array("ROLE_ADMIN", "ROLE_NCUO", "ROLE_VDL", "ROLE_FOIV", "ROLE_ROIV");

foreach ($arRoles as $role):
	$data = array('name' => $role);
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
			echo "<p>Error: Group was not created</p>";
			$error = error_get_last();
			var_dump($error);
		}else{
			echo "<p>Group was created successfuly</p>";
			var_dump(json_decode($result)->data);
		}
	}
	catch (Exception $e) {
	    echo $e->getMessage();
	}

endforeach;
/*
$data = array('name' => 'GROUP_ADM');
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
		echo "<p>Error: Group was not created</p>";
		$error = error_get_last();
		var_dump($error);
	}else{
		echo "<p>Group was created successfuly</p>";
		var_dump(json_decode($result)->data);
	}
}
catch (Exception $e) {
    echo $e->getMessage();
}

$data = array('name' => 'GROUP_NCUO');
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
		echo "<p>Error: Group was not created</p>";
		$error = error_get_last();
		var_dump($error);
	}else{
		echo "<p>Group was created successfuly</p>";
		var_dump(json_decode($result)->data);
	}
}
catch (Exception $e) {
    echo $e->getMessage();
}

$data = array('name' => 'GROUP_ROIV');
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
		echo "<p>Error: Group was not created</p>";
		$error = error_get_last();
		var_dump($error);
	}else{
		echo "<p>Group was created successfuly</p>";
		var_dump(json_decode($result)->data);
	}
}
catch (Exception $e) {
    echo $e->getMessage();
}

$data = array('name' => 'GROUP_VLD');
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
		echo "<p>Error: Group was not created</p>";
		$error = error_get_last();
		var_dump($error);
	}else{
		echo "<p>Group was created successfuly</p>";
		var_dump(json_decode($result)->data);
	}
}
catch (Exception $e) {
    echo $e->getMessage();
}
*/

echo "<p>End of script</p>";
?>