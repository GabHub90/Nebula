<?php
function db_todata($db) {
	return substr($db,6,2)."/".substr($db,4,2)."/".substr($db,0,4);
}

function data_todb($data) {
	return substr($data,6,4).substr($data,3,2).substr($data,0,2);
}

function convert_date_tosql($txt) {
	//formato language ITALIAN
	return substr($txt,6,2)."-".substr($txt,4,2)."-".substr($txt,0,4);
}

function db2date($txt) {
	return substr($txt,0,4)."-".substr($txt,4,2)."-".substr($txt,6,2);
}

function date2db($txt) {
	return substr($txt,0,4).substr($txt,5,2).substr($txt,8,2);
}

function get_reparto_menu_lines($db) {
	$query="SELECT * FROM MAESTRO_reparti WHERE (tipo='S' OR tipo='X') AND virtuale='N' ORDER BY tipo,tag";
	if($result=sqlsrv_query($db,$query)) {
		while ($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
			$reparti[$row[tag]]=$row;
		}
	}
	return $reparti;
}

/*function delta_tempo ($data_iniziale,$data_finale,$unita) {
	//la data_iniziale è quella minore
 
	$data1 = strtotime(db2date($data_iniziale));
	$data2 = strtotime(db2date($data_finale));
	 
		switch($unita) {
			case "m": $unita = 1/60; break; 	//MINUTI
			case "h": $unita = 1; break;		//ORE
			case "g": $unita = 24; break;		//GIORNI
			case "a": $unita = 8760; break;         //ANNI
		}
	 
	$differenza = (($data2-$data1)/3600)/$unita;
	 
	return $differenza;
}

function get_collaboratori_menu_lines($db,$today) {
	$query="SELECT 
			t1.marcatempo,
			t2.tag AS gruppo,
			t2.reparto,
			t3.nome,
			t3.cognome 
			
			FROM MAESTRO_coll_gru AS t1 
			
			LEFT JOIN MAESTRO_gruppi AS t2 ON t1.gruppo=t2.ID 
			LEFT JOIN MAESTRO_collaboratori AS t3 ON t1.collaboratore=t3.ID 
			
			WHERE t1.marcatempo>'0' AND t1.marcatempo<'900' AND CAST(SUBSTRING(t1.data_i,1,6)AS INT)<='".$today."' AND CAST(SUBSTRING(t1.data_f,1,6)AS INT)>='".$today."' 
			ORDER BY t1.marcatempo";
				
	if($result=sqlsrv_query($db,$query)) {
		while ($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
			$collaboratori[$row[marcatempo]]=$row;
		}
	}
	return $collaboratori;
}

function get_gruppi_menu_lines($db,$reparti,$today) {
	//mysqli_select_db('maestro');
	$query="SELECT 
			t1.collaboratore,
			t2.tag,
			t2.reparto,
			t1.data_i,
			t1.data_f 
			
			FROM MAESTRO_coll_gru AS t1 
			
			INNER JOIN MAESTRO_gruppi AS t2 ON t1.gruppo=t2.ID";
			
	if($result=sqlsrv_query($db,$query)) {
		while ($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
			$gruppi[$row[reparto]][$row[tag]][$row[collaboratore]]=$row;
			//verifica la visibilità alla data di selezione
			//se il reparto è visibile verifica la visibilità del gruppo
			if ($reparti[$row[reparto]][dys]==1) {
			if ((int)substr($row[data_i],0,6)<=(int)$today && (int)substr($row[data_f],0,6)>=(int)$today) {
					$gruppi[$row[reparto]][$row[tag]][dys]=1;
				}
				else $gruppi[$row[reparto]][$row[tag]][dys]=0;
			}
			else $gruppi[$row[reparto]][$row[tag]][dys]=0;
		}
	}
	return $gruppi;
}

function get_addebiti($db) {
	//mysqli_select_db('croom');
	//addebiti (Codice addebito, Codice garanzia, risultato:PAGAMENTO,CORRENTEZZA,PACCHETTI,GARANZIA,INTERNO)
	$query="SELECT ca,cg,cm,descrizione FROM CROOM_addebiti";
	if($result=sqlsrv_query($db,$query)) {
		while ($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
			$addebiti[]=$row;
		}
	}
	//mysqli_select_db('maestro');
	return	$addebiti;
}

function get_odl_servizio($db) {
	//mysqli_select_db('croom');
	$query="SELECT * FROM CROOM_serv_rif WHERE CAST(data_i AS INT)<='".date("Ymd")."' AND CAST(data_f AS INT)>='".date("Ymd")."'";
	if($result=sqlsrv_query($db,$query)) {
		while ($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
			$rif[$row[rif]]=$row;
		}
	}
	//mysqli_select_db('maestro');
	return $rif;
}

function get_lam_fittizio($db) {
	//mysqli_select_db('croom');
	$query="SELECT * FROM CROOM_lam_fittizio";	
	if($result=sqlsrv_query($db,$query)) {
		while ($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
			$lines[]=$row;
		}
	}
	//mysqli_select_db('maestro');
	return $lines;
}

function get_tipo_chiusura($db) {
	//mysqli_select_db('ststop');
	$query="SELECT * FROM STSTOP_stato_chiusura";	
	if($result=sqlsrv_query($db,$query)) {
		while ($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
			$lines[$row[indice]]=$row[testo];
		}
	}
	//mysqli_select_db('maestro');
	return $lines;
}

function delta_h($now,$rif) {
	//in ambiente di prova $now viene modificato per aderire alle timbrature rilevate
	//$now='18:05';
	
	$now_min=((int)substr($now,0,2))*60+(int)substr($now,3,2);
	$rif_min=((int)substr($rif,0,2))*60+(int)substr($rif,3,2);
	
	$delta=($now_min-$rif_min)/60;
	
	return round($delta,2);
}

function convert_date_to_italian($str) {

	return substr($str,6,2)."/".substr($str,4,2)."/".substr($str,0,4);
}*/ 

?>