<?php
define("CONN_HOST", "172.23.192.32");
define("TYPE_AMMOUNT", 500);

// DO NOT CHANGE
define("CONN_PORT", "5432");
define("CONN_DBNAME", "eif_db");
define("CONN_USER", "postgres");
define("CONN_PASSWORD", "postgres");
define("TYPE_BAT", "bat");
define("TYPE_PMI", "mi");

define("PARAM_OPERATION", "act");
define("PARAM_FILE", "rfile");
define("PARAM_TYPE", "rtype");
define("PARAM_DATE", "rdate");

define("OPERATION_RESTORE_FROM_FILE", "fromfile");
define("OPERATION_RESTORE_FROM_TABLE", "fromtable");
define("OPERATION_HELP", "help");

$foiv_id = $_REQUEST['id'];
$type_id = $_REQUEST['type'];

// if ( empty($foiv_id) || preg_match("/[^a-zа-я0-9_]/",$foiv_id)) die ("Ошибка: id не удовлетворяет условиям");

$conn_string = "host=".CONN_HOST." port=".CONN_PORT." dbname=".CONN_DBNAME." user=".CONN_USER." password=".CONN_PASSWORD;

try {
  $conn = pg_connect($conn_string);   
}
catch(PDOException $e)
{
  echo $e->getMessage();
}
if( !$conn ) die("Connection fail \n");


function getOivsList($c, $type_id){
	$arData = array();
    $result = pg_query($c, "SELECT * FROM oivs_passports.oivs WHERE id_oiv_type={$type_id}");
    if (!$result) {
      echo "Произошла ошибка 1001.\n";
      return false;
    }

    while ($row = pg_fetch_row($result)) {
      $arData[] = $row;
    }

    return $arData;
}

function getOivData($c, $oiv_id){
	$arData = array();
    $result = pg_query($c, "SELECT * FROM oivs_passports.oivs WHERE id_oiv = '{$oiv_id}'");
    if (!$result) {
      echo "Произошла ошибка 1001.\n";
      return false;
    }

    if ($row = pg_fetch_row($result)) {
      $arData = $row;
    }

    return $arData;
}

function getSectionsData($c, $oiv_id){
	$arData = array();
    $result = pg_query($c, "SELECT * FROM oivs_passports.oivs_pass_sections WHERE id_oiv = '{$oiv_id}' ORDER BY id_sec");
    if (!$result) {
      echo "Произошла ошибка 1001.\n";
      return false;
    }

    while ($row = pg_fetch_row($result)) {
      $arData[] = $row;
    }

    return $arData;
}

function getFieldsData($c, $oiv_id){
	$arData = array();
    $result = pg_query($c, "SELECT * FROM oivs_passports.oivs_pass_seсs_fields WHERE id_fld like '{$oiv_id}__%' ORDER BY id_sec");
    if (!$result) {
      echo "Произошла ошибка 1001.\n";
      return false;
    }

    while ($row = pg_fetch_row($result)) {
      $arData[] = $row;
    }

    return $arData;
}

if ( empty($foiv_id) && in_array($type_id, array('foiv', 'region') )):

	$arData = getOivsList($conn,  $type_id == 'foiv'? 1:2 );

	$doc = new DOMDocument('1.0', 'UTF-8');

	$listNode = $doc->createElement("foiv_list");

	$doc->appendChild($listNode);

	if( count($arData) > 0 ):
		foreach ($arData as $arValue) {
			$tmpNode = $doc->createElement( 'foiv' );

			$tmpAttr = $doc->createElement( 'id' , trim($arValue[0] ) );
			$tmpNode->appendChild($tmpAttr);
			$tmpAttr = $doc->createElement( 'name' , trim($arValue[1] ) );
			$tmpNode->appendChild($tmpAttr);

			$listNode->appendChild($tmpNode);
		}
	endif;

	header('Content-Type: text/xml');
	echo $doc->saveXML();

	die();

endif;


if( preg_match("/[^a-zа-я0-9_]/",$foiv_id)) die ("Ошибка: id не удовлетворяет условиям");


$doc = new DOMDocument('1.0', 'UTF-8');

$foivNode = $doc->createElement("foiv");
$headNode = $doc->createElement("foiv_passport");
$foivNode->appendChild($headNode);

$doc->appendChild($foivNode);

// ATTRIBUTES

$arData = getOivData($conn, $foiv_id);

if ( empty($arData[0]) ) die ("Ошибка: id не найден");

$domAttribute = $doc->createAttribute('id');
$domAttribute->value = $foiv_id;
$foivNode->appendChild($domAttribute);
$domAttribute = $doc->createAttribute('name');
$domAttribute->value = $arData[1];
$foivNode->appendChild($domAttribute);

$domAttribute = $doc->createAttribute('id');
$domAttribute->value = $foiv_id;
$headNode->appendChild($domAttribute);
// $domAttribute = $doc->createAttribute('name');
// $domAttribute->value = $arData[1];
// $headNode->appendChild($domAttribute);
// $domAttribute = $doc->createAttribute('description');
// $domAttribute->value = $arData[2];
// $headNode->appendChild($domAttribute);


// SECTIONS
$secNode = $doc->createElement("oivs_pass_sections");

$headNode->appendChild($secNode);

$arData = getSectionsData($conn, $foiv_id);

$tmpAttribute = $doc->createAttribute('ammount');
$tmpAttribute->value = count($arData);
$secNode->appendChild($tmpAttribute);

if( count($arData) > 0 ):
	foreach ($arData as $arValue) {
		$tmpNode = $doc->createElement( 'oivs_pass_section' );

		$tmpAttr = $doc->createElement( 'id' , trim($arValue[0] ) );
		$tmpNode->appendChild($tmpAttr);
		$tmpAttr = $doc->createElement( 'name' , trim($arValue[1] ) );
		$tmpNode->appendChild($tmpAttr);
		$tmpAttr = $doc->createElement( 'parent_section' , trim($arValue[7] ) );
		$tmpNode->appendChild($tmpAttr);

		$secNode->appendChild($tmpNode);
	}
endif;

// FIELDS

$fildsNode = $doc->createElement("oivs_pass_seсs_fields");

$headNode->appendChild($fildsNode);

$arData = getFieldsData($conn, $foiv_id);

$tmpAttribute = $doc->createAttribute('ammount');
$tmpAttribute->value = count($arData);
$fildsNode->appendChild($tmpAttribute);

if( count($arData) > 0 ):
	foreach ($arData as $arValue) {
		$tmpNode = $doc->createElement( 'oivs_pass_seсs_field' );

		$tmpAttr = $doc->createElement( 'id' , trim($arValue[0] ) );
		$tmpNode->appendChild($tmpAttr);
		$tmpAttr = $doc->createElement( 'name' , trim($arValue[1] ) );
		$tmpNode->appendChild($tmpAttr);
		$tmpCDataElem = $doc->createElement( 'value' );
		$tmpCData = $doc->createCDataSection( trim($arValue[2] ) );
		$tmpNode->appendChild($tmpCDataElem);
		$tmpCDataElem->appendChild($tmpCData);
		$tmpAttr = $doc->createElement( 'updated' , trim($arValue[3] ) );
		$tmpNode->appendChild($tmpAttr);
		$tmpAttr = $doc->createElement( 'parent_section' , trim($arValue[5] ) );
		$tmpNode->appendChild($tmpAttr);

		$fildsNode->appendChild($tmpNode);
	}
endif;


header('Content-Type: text/xml');
echo $doc->saveXML();
exit();

?>