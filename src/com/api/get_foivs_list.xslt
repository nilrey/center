<?php
include_once('_set.php');
include_once('_functions.php');
include_once('_dbcon.php');

$arData = getOivsList($conn, 1 );

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

?>