<?
function checkSet($ald_login, $server_name, $set_id, $debug=false)
{
	$path = '/public/api/idm/set/';
	$url = $server_name.$path;

	$error = array();
	$http_method = 'GET';
	// echo "<p>url={$url}</p>";

	$options = array(
	    'http' => array(
	        'header'  => "Authorization:Explicit {$ald_login}\r\nContent-type: application/json\r\n",
	        'method'  => $http_method,
	    )
	);
	$context  = stream_context_create($options);

	try {
		$result = file_get_contents($url, false, $context);

		if ($result === false) { 
			echo "<p>Error: Set was not created</p>";
			$error = error_get_last();
			var_dump($error);
		}else{
			$data =json_decode( $result )->data;
			foreach ( $data as $arValue) {
				if($set_id == $arValue->uid){
					// if ($debug) echo "<p>Set Id in SVIP exists</p>";
					if ($debug) echo "<p>Set id=\"".$arValue->uid."\"</p><p>Set Name=\"".$arValue->name."\"</p>";
					return true;
				}
			}
			if ($debug) echo "<p>Set Id not found in SVIP</p>";
		}
		return false;
	}
	catch (Exception $e) {
		echo "<p>Error found at searching set Id in SVIP</p>";
	    echo $e->getMessage();
		return false;
	}

}

function getGgroups($ald_login, $server_name, $set_id, $debug=false)
{
	$arGr = array();
	$path = "/public/api/idm/{$set_id}/group/";
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

		if ($result === false) { 
			echo "<p>Error: Groups do not created</p>";
			$error = error_get_last();
			var_dump($error);
		}else{
			$data =json_decode( $result )->data;
			if ($debug) echo "<p>Groups Info:</p>";
			foreach ( $data as $arValue) {
				if($set_id == $arValue->set_id){
					$arGr[$arValue->name] = $arValue->uid;
				}
			}
		}
		return $arGr;
	}
	catch (Exception $e) {
		echo "<p>Error found at searching groups in SVIP</p>";
	    echo $e->getMessage();
		return $arGr;
	}

}


function getAccounts($ald_login, $server_name, $set_id, $arGroupsName, $debug=false)
{
	$arAcc = array();

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
	        echo "<p>Error: access Accounts error</p>";
	        $error = error_get_last();
	        var_dump($error);
	        // die();
	    }else{
	        if ($debug) echo "<p>Accounts Info:</p>";
	        // var_dump (json_decode($result)->data);
	        $data = json_decode($result)->data;
	        foreach ($data as $arValue) {
				if($set_id == $arValue->set_id){
					$arAcc[$arValue->uid] = array("set_id" => $arValue->set_id, "group_id" => $arValue->group_id, "group_name" => $arGroupsName[$arValue->group_id], "uid" => $arValue->uid, "name" => $arValue->account_id );
				}
	            // echo "<br>[{$arValue->group_id}] [{$arValue->uid}] {$arValue->	}";
	        }
	    }
		return $arAcc;
	}
	catch (Exception $e) {
	    echo $e->getMessage();
		return $arAcc;
	}
}
?>