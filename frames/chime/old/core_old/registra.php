<?php 
include('../chime_ini.php');

$db_handler=mysqli_connect($server_db,$user,$pw);
mysqli_select_db($db_handler,$db);

$param=(array)json_decode($_POST[param]);
$parametri=(array)json_decode($param[parametri]);
$elementi=$param[elementi];
$tag=$parametri[form_data];
$today=date("Ymd");
$id_indice=0;

//INDICE
//verifica se esiste già un indice per il giorno corrente
$query='SELECT * FROM indici WHERE report='.$param[report].' AND tag="'.$tag.'"';
if ($result=mysqli_query($query)) {
	if($row=mysqli_fetch_array($result)) {
		$num=mysqli_affected_rows($db_handler);
	}
	else $num=0;
}

//echo $num;

if ($num>0) {
	$query='UPDATE indici SET data="'.$today.'", stato="'.$param[stato_indice].'", parametri=\''.$param[parametri].'\' WHERE ID='.$row[ID];
	$id_indice=$row[ID];
}
else {
	$query='INSERT INTO indici (report,tag,data,stato,parametri) VALUES('.$param[report].',"'.$tag.'","'.$today.'","'.$param[stato_indice].'",\''.$param[parametri].'\')';
}

if(mysqli_query($query)) $txt='Indice registrato';
else $txt='ERRORE indice';

if ($id_indice==0) $id_indice=mysqli_insert_id();

//STORICO
if($param[storico]>0 || $param[stato_indice]=="saved") {

	//verifica se esiste già un record storico per l'indice corrente
	$query='SELECT * FROM storico WHERE indice='.$id_indice;
	if ($result=mysqli_query($query)) {
		if($row=mysqli_fetch_array($result)) {
			$num=mysqli_affected_rows($db_handler);
		}
		else $num=0;
	}
	
	if ($num>0) $query='UPDATE storico SET elementi=\''.$elementi.'\' WHERE indice='.$id_indice;
	
	else $query='INSERT INTO storico (indice,elementi) VALUES('.$id_indice.',\''.$elementi.'\')';
		
	if(mysqli_query($query)) $txt.=' / Storico registrato';
	else $txt.=' / ERRORE storico';
}


//RETURN
echo $txt;

mysqli_close($db_handler);
?>