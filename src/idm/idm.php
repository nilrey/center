<html>
<head></head>
	<body>
<?php

include('sets.php');
include('functions.php');
if(empty($server_name)) die('No set file found.');

if( empty($set_id))
	$form_type = 0;
else
	$form_type = 1;

$arGroups = array();
$arAccounts = array();
$sortAcc = "name";

if(!empty($_GET["sortAcc"])) $sortAcc = $_GET["sortAcc"];


if( isset($_POST["formType"] ) )
{
	if( intval($_POST["formType"]) == 1 ){

	}
	elseif ( intval($_POST["formType"]) == 2 && checkSet($ald_login, $server_name, $set_id) ){
		// Account create

		if( empty($_POST["group_id"]) )
		{
			echo "<h3>Error: Group id cannot be empty</h3>";
		}
		elseif( empty($_POST["accName"]) && empty($_FILES['accNames']['tmp_name']) )
		{
			echo "<h3>Error: Account name cannot be empty</h3>";
		}
		else
		{
			$path = "/public/api/idm/{$set_id}/account_group/";
			$url = $server_name.$path;
			$error = array();
			$http_method = 'POST';

			
			if ( !empty($_FILES['accNames']['tmp_name']) ):
				$handle = @fopen($_FILES['accNames']['tmp_name'], "r");
				if ($handle) {
				    while (($tmpAcc = fgets($handle, 4096)) !== false) {
				    	$arTmpAccounts[] = trim($tmpAcc);
				    }
				    if (!feof($handle)) {
				        echo "Error: unexpected fgets() fail\n";
				    }
				    fclose($handle);
				}
			endif;
			if ( !empty($_POST['accName']) ){
				$arTmpAccounts[] = trim($_POST['accName']);
			}

			foreach ($arTmpAccounts as $tmpAcc):

				$data = array(
				    'account_id' => $tmpAcc, 
				    // 'account_id' => 'nuso_d_gsh@dev.aorti.tech', 
				    'group_id' => $_POST["group_id"],
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

				// var_dump( $options);
				// echo "<p>Begin of script</p>";
				try {
					$result = file_get_contents($url, false, $context);

					if ($result === false) { 
						echo "<h3>Error: Account {$tmpAcc} was not added</h3>\r\n";
						$error = error_get_last();
						var_dump($error);
					}else{
						echo "<h3>Account {$tmpAcc} was added successfuly</h3>";
						// var_dump(json_decode($result)->data);
					}
				}
				catch (Exception $e) {
				    echo $e->getMessage();
				}

			endforeach;
		}
	}
}


//echo "<p>Searching set Id in SVIP ...</p>";
if( checkSet($ald_login, $server_name, $set_id, true) ){
	$form_type = 1;
	//echo "<hr><p>Searching groups in SVIP ...</p>";
	$arGroups = getGgroups($ald_login, $server_name, $set_id);
	foreach ($arGroups as $group_name => $group_id) {
		$arGroupsName[$group_id] = $group_name;
	}
	if(count($arGroups) > 0 ){
		$form_type = 2;
		$arTmpAccounts = getAccounts($ald_login, $server_name, $set_id, $arGroupsName);
		if( !empty($sortAcc)){
			foreach($arTmpAccounts as $uid => $arValue){
				$arSort[$uid] = $arValue[$sortAcc];
			}
			asort($arSort);
			foreach($arSort as $uid => $sortValue)
			{
				$arAccounts[$uid] = $arTmpAccounts[$uid];
			}
		}else
		{
			$arAccounts = $arTmpAccounts;
		}
	}
}else{
	$form_type = 0;
}

?>

<?php if($form_type == 0):?>
		<p><a target='_blank' href='idm_set_create.php' >Create set "CITIS_NCUO_USERS"</a></p>
	<!--<form method="POST" name="newSet">
		<input type="hidden" name="formType" value="0">
		<p>Set NAME: <input type="text" name="newSetName" style="width:400px" value="CITIS_NCUO_USERS"></p>
		<p><input type="submit"></p>
	</form>	-->

<?php elseif($form_type == 1):?>
		<p>Groups: </p>
		<p><a target='_blank' href='idm_gr_create.php' onclick="location.reload()">Create standart groups</a></p>

<?php elseif($form_type == 2):?>
	<form method="POST" enctype="multipart/form-data">
		<input type="hidden" name="formType" value="2">
		<input type="hidden" name="set_id" value="<?=$set_id?>">
		<hr>
		<? echo "<p>Groups count=".count($arGroups)."</p>"; ?>
		<p><a target='_blank' href='idm_gr_create.php' onclick="location.reload()">Create 5 standart roles</a></p><p>Select group: </p>
<?
foreach ($arGroups as $group_name => $group_id ){
	echo "<p><input type='radio' name='group_id' value='{$group_id}'>{$group_name} [{$group_id}] <a target='_blank' onclick='return confirm(\"Delete group {$group_name}?\")' style='margin-left: 15px' href='idm_gr_delete.php?gd_id={$group_id}'>Delete</a></p>";
} 
?>
		<br>
		<p>New Account: <input type="text" name="accName" style="width:400px" value="" placeholder="user@dev.aorti.tech"></p>
		<p>New Accounts: <input type="file" name="accNames" style="width:400px"></p>
		<p><input type="submit" value="Добавить аккаунт"></p>
	</form>	
	<? if(count($arAccounts) > 0 ): ?>
		<hr style="margin: 20px 0px;">
	<table cellpadding="5" cellspacing="0" style="border: 1px solid #cdcdcd">
		<tr>
			<td style="border: 1px solid #cdcdcd"><a href='idm.php?sortAcc=name'>Account name</a></td>
			<td style="border: 1px solid #cdcdcd"><a href='idm.php?sortAcc=uid'>Account uid</a></td>
			<td style="border: 1px solid #cdcdcd"><a href='idm.php?sortAcc=group_name'>Account group</a></td>
			<td style="border: 1px solid #cdcdcd">Delete</td>
		</tr>
		<? foreach ($arAccounts as $key => $arValue ){
		?>
		<tr>
			<td style="border: 1px solid #cdcdcd"><?=$arValue['name']?></td>
			<td style="border: 1px solid #cdcdcd"><?=$arValue['uid']?></td>
			<td style="border: 1px solid #cdcdcd"><?=$arGroupsName[$arValue['group_id']]?> [<?=$arValue['group_id']?>]</td>
			<td style="border: 1px solid #cdcdcd"><a target='_blank' onclick='return confirm("Delete account <?=$arValue['name']?>?")' style='margin-left: 15px' href='idm_acc_delete.php?acc_id=<?=$arValue['uid']?>'>Delete</a></td>
		</tr>
		<? } ?>
	</table>
	<? endif; ?>
<?php endif;?>


	</body>
</html>