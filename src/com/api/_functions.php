<?php

function setConnection(){
  $conn_string = "host=".CONN_HOST." port=".CONN_PORT." dbname=".CONN_DBNAME." user=".CONN_USER." password=".CONN_PASSWORD;

  try {
    $conn = pg_connect($conn_string);   
  }
  catch(PDOException $e)
  {
    echo $e->getMessage();
  }
  return $conn;
}

$conn = setConnection();
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

function getOivData($c, $oiv_id, $type_id=0){
	$arData = array();
    $result = pg_query($c, "SELECT * FROM oivs_passports.oivs WHERE id_oiv_type={$type_id} AND id_oiv = '{$oiv_id}'");
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

function getUserData($c, $user_id){
  $arData = array();
    $result = pg_query($c, "SELECT * FROM cms.users WHERE id = {$user_id}");
    if (!$result) {
      echo "Произошла ошибка 1001.\n";
      return false;
    }

    while ($row = pg_fetch_row($result)) {
      $arData[] = $row;
    }

    return $arData;
}


?>