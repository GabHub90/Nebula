<?php 
include('chime_ini.php');
require('chime_func.php');

$db_handler=mysqli_connect($server_db,$user,$pw);
mysqli_select_db($db_handler,$db);

$function=$_POST[func];
$param=(array)json_decode($_POST[param]);

//la funzione riceve un array come argomento ma spedisce i singoli elementi come variabili singole
call_user_func_array($function,$param);

mysqli_close($db_handler);
?>