<?php 
include('../chime_ini.php');

$db_handler=mysqli_connect($server_db,$user,$pw);
mysqli_select_db($db_handler,$db);

//trova ID dell'indice da calcellare
$query='SELECT ID FROM indici WHERE report='.$_POST[report].' AND tag="'.$_POST[tag].'"';
$result=mysqli_query($query);
$row=mysqli_fetch_assoc($result);
$ID=$row[ID];

//cancella INDICE
$query='DELETE FROM indici WHERE ID='.$ID;
mysqli_query($query);

//cancella STORICO
$query='DELETE FROM storico WHERE indice='.$ID;
mysqli_query($query);

mysqli_close($db_handler);
?>