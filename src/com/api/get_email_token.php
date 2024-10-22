<?php
include_once('_set.php');
include_once('_functions.php');
include_once('_dbcon.php');

$arData = getUserData($conn, $_GET['param1'] );

var_dump($arData);

die();

?>