<?php
/*
вызов скрипта осуществляется следующими командами:
для восстановления из файла бекапа (пример):
php restore_citis_oivs.php --act=fromfile --rfile=bkp_oivs_bat_20211231235859.dump
или
php restore_citis_oivs.php --act=fromfile --rfile=bkp_oivs_mi_20211231235859.dump

для восстановления из раннего среза таблиц в базе (пример):
php restore_citis_oivs.php --act=fromtable --rtype=bat --rdate=20211231235859
или
php restore_citis_oivs.php --act=fromtable --rtype=mi --rdate=20211231235859

скрипт заменяет таблицы в базе на таблицы из бекапа
предварительно проводиться сохранение текущего состояния базы
сохранение осуществляется путем создания бекапа следующих таблиц: 
- cms.users, 
- oivs_passports.oivs, 
- oivs_passports.oivs_pass_sections, 
- oivs_passports.oivs_pass_seсs_fields, 
- oivs_passports.oiv_id_mapping.
также в базе создается срез текущих таблиц в виде копий с названием TABLENAME_YmdHis
бекапу присваевается префикс в соответствии с оценкой, скрипт оценивает по количеству пользователей тип базы 
при количестве более 500 пользователей присваивается тип bat (Баталюк), иначе mi 
*/
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

$conn_string = "host=".CONN_HOST." port=".CONN_PORT." dbname=".CONN_DBNAME." user=".CONN_USER." password=".CONN_PASSWORD;

try {
  $conn = pg_connect($conn_string);   
}
catch(PDOException $e)
{
  echo $e->getMessage();
}
if( !$conn ) die("Connection fail \n");

// при получении параметра из командной строки добавляется суффикс двоеточия
$params = getopt(null, [PARAM_OPERATION.":", PARAM_FILE.":", PARAM_TYPE.":", PARAM_DATE.":"]);

// получим переменные из командной строки
if( !empty($params[PARAM_OPERATION]) 
  && in_array( $params[PARAM_OPERATION] , array(OPERATION_RESTORE_FROM_FILE, OPERATION_RESTORE_FROM_TABLE, OPERATION_HELP ) )
 ):
  $ar_tables[] = "cms.roles";
  $ar_tables[] = "oivs_passports.oivs";
  $ar_tables[] = "oivs_passports.oivs_pass_sections";
  $ar_tables[] = "oivs_passports.oivs_pass_seсs_fields";
  $ar_tables[] = "oivs_passports.oiv_id_mapping";

  if( $params[PARAM_OPERATION] == OPERATION_RESTORE_FROM_FILE ):
    echo "\nВыбрана операция восстановления из файла";
    exec("pwd", $res);
    $cur_path = $res[0];

    if( file_exists($cur_path."/".$params[PARAM_FILE]) ){
      // echo "\nВыбрана текущая директория. Полный путь к файлу:";
      // echo "\n".$_SERVER["PWD"]."/".$params[PARAM_FILE];
      $rfile_path = $cur_path."/".$params[PARAM_FILE];
    }elseif( file_exists( $params[PARAM_FILE] ) ){
      // echo "\nВыбран полный путь к файлу:";
      // echo "\n".$params[PARAM_FILE];
      $rfile_path = $params[PARAM_FILE];
    }else{
      die("\nОшибка 1012. Файл не найден");
    }

    $vals = countUsers($conn, "users");
    extract($vals, EXTR_OVERWRITE);
    $dfile_type = $type;
    $dfile_date = date('YmdHis');
    $dfile_name = "bkp_oivs_{$dfile_type}_{$dfile_date}.dump";

    $dfile_tables = ' --table=' .implode(' --table=', $ar_tables);
    // dump таблиц текущей базы
    exec('pg_dump -Fc --dbname=postgresql://'.CONN_USER.':'.CONN_PASSWORD.'@'.CONN_HOST.':'.CONN_PORT.'/'.CONN_DBNAME.' '.$dfile_tables.' > '.$cur_path.'/'.$dfile_name, $output);
    echo "\nВыполнен резервный дамп таблиц (".implode(', ', $ar_tables).") текущей базы дынных в файл '{$dfile_name}'.\n";

    // создаем срез текущих таблиц в виде копий с названием TABLENAME_YmdHis
    renameTables($conn, $dfile_type, $dfile_date, $ar_tables);
    // удаляем таблицы 
    deleteTables($conn, $ar_tables);
    // восстанавливаем таблицы из файла бекапа
    exec('pg_restore --dbname=postgresql://'.CONN_USER.':'.CONN_PASSWORD.'@'.CONN_HOST.':'.CONN_PORT.'/'.CONN_DBNAME.' "'.$rfile_path.'"', $output);

  elseif( $params[PARAM_OPERATION] == OPERATION_RESTORE_FROM_TABLE ):
    echo "\nВыбрана операция восстановления из архивных таблиц базы данных, данный функционал находится в разработке, переименуйте таблицы в ручном режиме.";
    foreach ($ar_tables as $value) {
      echo "\n{$value}";
    }
  elseif( $params[PARAM_OPERATION] == OPERATION_HELP ):
    echo "
вызов скрипта осуществляется следующими командами:
для восстановления из файла бекапа (пример):
php restore_citis_oivs.php --act=fromfile --rfile=bkp_oivs_bat_20211231235859.dump
или
php restore_citis_oivs.php --act=fromfile --rfile=bkp_oivs_mi_20211231235859.dump

для восстановления из раннего среза таблиц в базе (пример):
php restore_citis_oivs.php --act=fromtable --rtype=bat --rdate=20211231235859
или
php restore_citis_oivs.php --act=fromtable --rtype=mi --rdate=20211231235859

скрипт заменяет таблицы в базе на таблицы из бекапа
предварительно проводиться сохранение текущего состояния базы
сохранение осуществляется путем создания бекапа следующих таблиц: 
- cms.users, 
- oivs_passports.oivs, 
- oivs_passports.oivs_pass_sections, 
- oivs_passports.oivs_pass_seсs_fields, 
- oivs_passports.oiv_id_mapping.
также в базе создается срез текущих таблиц в виде копий с названием TABLENAME_YmdHis
бекапу присваевается префикс в соответствии с оценкой, скрипт оценивает по количеству пользователей тип базы 
при количестве более 500 пользователей присваивается тип bat (Баталюк), иначе mi 
    ";
    
  endif;
/*
  $result = pg_query($conn, "SELECT table_name FROM information_schema.tables WHERE table_schema = 'cms' ");
  if (!$result) {
    echo "Произошла ошибка 1001.\n";
    exit;
  }

  while ($row = pg_fetch_row($result)) {
    $type = '';
    $cnt = -1;
    if(!empty($row[0])){
      $vals = countUsers($conn, "users");
      extract($vals, EXTR_OVERWRITE);
    }
    echo "{$row[0]} Текущая версия - {$type}. Количество пользователей {$cnt}\n";
  }*/

  echo "\nПроцесс восстановления закончен.";

else:
  echo "\nНе определены параметры запуска скрипта. Для вызова помощи задайте параметр --act=help\n";
/*
  $vals = countUsers($conn, "users");
  extract($vals, EXTR_OVERWRITE);
  echo "\nТекущая версия - {$type}. Количество пользователей {$cnt}";
*/
endif;
echo "\n";

function countUsers($c, $t_name){
    $cur_type = '';
    $cnt_rec = -1;
    $result = pg_query($c, "SELECT count(*) AS cnt FROM cms.{$t_name}");
    if (!$result) {
      echo "Произошла ошибка 1001.\n";
      return false;
      // exit;
    }

    if ($row = pg_fetch_row($result)) {
      $cur_type = TYPE_PMI;
      if( intval($row[0]) > TYPE_AMMOUNT){
        $cur_type = TYPE_BAT;
      }
      $cnt_rec = $row[0];
    }else{
      return false;
    }
    return array("type"=>$cur_type, "cnt"=>$cnt_rec);
}

function renameTable($c, $t_name, $t_name_new){
    $sql = "SELECT * into {$t_name_new} FROM {$t_name}";
    $result = pg_query($c, $sql);
    if (!$result) {
      echo "\nПроизошла ошибка 1021.";
      return false;
    }
    return true;
}

function renameTables($c, $t_type, $t_date, $ar_tables){
    if(is_array($ar_tables) && count($ar_tables) > 0 ){
      foreach ($ar_tables as $t_name) {
        $t_name_new = "{$t_name}_{$t_type}_{$t_date}";
        $res = renameTable($c, $t_name, $t_name_new);
        if( !$res) return false;
      }
    }else{
      echo "\nПроизошла ошибка 1022.";
      return false;
    }
    return true;
}
function deleteTable($c, $t_name){
    $result = pg_query($c, "DROP TABLE {$t_name} CASCADE");
    if (!$result) {
      echo "\nПроизошла ошибка 1031.";
      return false;
    }
    return true;
}

function deleteTables($c, $ar_tables){
    if(is_array($ar_tables) && count($ar_tables) > 0 ){
      foreach ($ar_tables as $t_name) {
        $res = deleteTable($c, $t_name);
        if( !$res) return false;
      }
    }else{
      echo "\nПроизошла ошибка 1032.";
      return false;
    }
    return true;
}
?>
