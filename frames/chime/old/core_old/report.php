<?php 
include('../chime_ini.php');

$db_handler=mysqli_connect($server_db,$user,$pw);
mysqli_select_db($db_handler,$db);

$query='SELECT * FROM report WHERE ID='.$_GET[ID];
$result=mysqli_query($query);
$row=mysqli_fetch_assoc($result);

$init_ts=strtotime($row[today]);
$init_tag=date("Ymd",$init_ts);

$row['init_ts']=$init_ts;
$row['init_tag']=$init_tag;
$row['rep_id']=$_GET[ID];

//PULIZIA STORICO
$indici=array();
//seleziona la data di riferimento per identifiarele date valide
if ($row[tipo]=="GIORNO") $rif=$init_tag;
else $rif=substr($init_tag,0,6)."01";

//Se INDICE=0 cancella tutti gli indici e tutti gli storici
if ($row[indice]==0) {
	$query='SELECT ID FROM indici WHERE report='.$_GET[ID];
	if($result=mysqli_query($query)) {
		if(mysqli_affected_rows()>0) {
			$indici=mysqli_fetch_array($result);
		}
	}
}

//Se INDICE=1 cancella gli indici obsoleti ed i relativi storici
if ($row[indice]==1) {
	if ($row[back]==0) {
		$query='SELECT ID FROM indici WHERE report='.$_GET[ID].' AND CAST(tag AS SIGNED)<'.$rif;
		if($result=mysqli_query($query)) {
			if(mysqli_affected_rows()>0) {
				$indici=mysqli_fetch_array($result);
			}
		}
	}
	//if ($row[forw]==0) {
	//	$query='SELECT ID FROM indici WHERE report='.$_GET[ID].' AND CAST(tag AS UNSIGNED)>'.$rif;
	//	if($result=mysqli_query($query)) {
	//		$indici=mysqli_fetch_array($result);
	//	}
	//}
}


//CANCELLA INDICI
foreach ($indici as $id) {
	$query='DELETE FROM indici WHERE ID='.$id;
	mysqli_query($query);
}

//CANCELLA STORICO
foreach ($indici as $id) {
	$query='DELETE FROM storico WHERE indice='.$id;
	mysqli_query($query);
}




//RETURN
echo json_encode($row);

mysqli_close($db_handler);
?>