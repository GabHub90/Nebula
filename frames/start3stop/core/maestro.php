<?php
class Maestro {

	private $server="srvdb";
	private $user="sa";
	private $pw="Gns1999";
	private $db="GC_AZI_GABELLINI_prod";
	private $cset="UTF-8";
	
	private $db_handler;
	
	function __construct() {
		//$this->db_handler=mssql_connect($this->server,$this->user,$this->pw);
		//mssql_select_db($this->db,$this->db_handler);
		$connectionInfo = array("Database"=>$this->db, "UID"=>$this->user, "PWD"=>$this->pw, "CharacterSet" => $this->cset);
		$this->db_handler=sqlsrv_connect("srvdb",$connectionInfo);
		//$this->db_handler=mssql_connect($this->server,$this->user,$this->pw);
		//mssql_select_db($this->db,$this->db_handler);
		//settaggio della lingua del SERVER altrimenti sbaglia le date invertendo il mese con il giorno
		$query="SET LANGUAGE Italian";
		sqlsrv_query($this->db_handler,$query);
	}
	
	function __destruct() {
		sqlsrv_close($this->db_handler);
	}
	
	//FUNZIONI UTILITA'
	
	function ststop_query($query) {
		$result=sqlsrv_query($this->db_handler,$query);
		return $result;
	}
	
	//==================================================================
	// UTILITÀ
	//==================================================================
	
	function delta_h($now,$rif) {
		//calcola la differenza in ore tra ADESSO e la datra di INIZIO marcatura 
		
		$now_min=((int)substr($now,0,2))*60+(int)substr($now,3,2);
		$rif_min=((int)substr($rif,0,2))*60+(int)substr($rif,3,2);
		
		$delta=($now_min-$rif_min)/60;
		
		return round($delta,2);
	}
	
	function convert_tosql($txt) {
		//da AAAAMMDD HH:MM a DD-MM-AAAA HH:MM
		//formato language ITALIAN
		return substr($txt,6,2)."-".substr($txt,4,2)."-".substr($txt,0,4)." ".substr($txt,9);
	}
	
	//==================================================================
	// METODI START STOP
	//==================================================================
		
	//legge la Vista LISTA_TEMPI_APERTI dove ci sono le marcature non chiuse in riferimento ad uno specifico intervallo di collaboratori
	function st_get_marcature_aperte() {
	
		$lines=array();
		$query="SELECT 
				CONVERT(VARCHAR(8),t1.dat_ora_inizio,112)AS d,
				CONVERT(VARCHAR(5),t1.dat_ora_inizio,108)AS t,
				t1.* ,
				t2.cod_movimento AS tipo_commessa,
				t3.num_rif_veicolo,
                t4.mat_telaio as telaio,
                t4.mat_targa as targa,
                t4.des_veicolo
				FROM LISTA_TEMPI_APERTI AS t1
				LEFT JOIN GN_MOVTES AS t2 on t1.num_rif_movimento=t2.num_rif_movimento
				LEFT JOIN GN_MOVTES_OFF AS t3 on t1.num_rif_movimento=t3.num_rif_movimento
				LEFT JOIN VE_ANAVEI AS t4 on t3.num_rif_veicolo=t4.num_rif_veicolo
				";
		$result=sqlsrv_query($this->db_handler,$query);
		while ($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
			$lines[$row['cod_operaio']][]=$row;
		}
		return $lines;
	}
	
	//legge le ultime timbrature dalla VISTA specifica
	function st_get_ultime_marcature() {
	
		$lines=array();
		$query="SELECT 
				t1.num_rif_movimento AS mov,
				t1.cod_inconveniente AS inc,
				t1.cod_operaio,
				num_riga,
				CONVERT(VARCHAR(8),t1.dat_ora_fine,112)AS d2,
				CONVERT(VARCHAR(5),t1.dat_ora_fine,108)AS t2,
				t2.num_rif_veicolo,
				t3.mat_telaio as telaio,
				t3.mat_targa as targa,
				t3.des_veicolo
				FROM LISTA_ULTIME_TIMBRATURE as t1
				LEFT JOIN GN_MOVTES_OFF AS t2 on t1.num_rif_movimento=t2.num_rif_movimento
				LEFT JOIN VE_ANAVEI AS t3 on t2.num_rif_veicolo=t3.num_rif_veicolo
				";
		$result=sqlsrv_query($this->db_handler,$query);
		while ($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
			$lines[$row['cod_operaio']]=$row;
		}
		return $lines;
	}
	
	function st_get_lamentato($mov,$lam) {

		$lines=array();
		$query="SELECT
				*
				FROM LISTA_LAMENTATI
				WHERE mov='".$mov."' AND inc='".$lam."'
				";
		$result=sqlsrv_query($this->db_handler,$query);
		while ($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
			$lines[$row['inc']]=$row;
		}

		return $lines;
	}
	
	function st_get_lamentati($mov) {
			
		$lines=array();
		$query="SELECT
				*
				FROM LISTA_LAMENTATI
				WHERE mov='".$mov."'
				ORDER BY inc
				";
		$result=sqlsrv_query($this->db_handler,$query);
		while ($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
			$lines[$row['inc']]=$row;
		}

		return $lines;
	}
	
	function ststop_get_pitlane_open() {
		$ret=array();
		$query="SELECT * FROM LISTA_PITLANE_OPEN";
		if($result=sqlsrv_query($this->db_handler,$query)) {
			while ($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$ret[$row['collaboratore']][]=$row;
			}
		}
		
		return $ret;
	}

	
	//==================================================================
	// MARCATURE
	//==================================================================
	//{"d":"20181013","t":"08:20","num_rif_movimento":"1099882","cod_inconveniente":"A","cod_operaio":"10","num_riga":1,"dat_ora_inizio":{"date":"2018-10-13 08:20:00.000000","timezone_type":3,"timezone":"Europe\/Rome"},"dat_ora_fine":null,"qta_ore_lavorate":".00","des_note":"","cod_officina":null,"cod_tipo_manodopera":null,"ora_entrata_effettiva":{"date":"2018-10-13 08:20:34.000000","timezone_type":3,"timezone":"Europe\/Rome"},"ind_turno":" ","ind_straordinario":null,"num_rif_riltem":196066,"cod_off":"PV","flag_pitlane":"0","tipo_commessa":"OOP","num_rif_veicolo":"5014117","telaio":"WVWZZZAAZGD104386","targa":"FD463FC","des_veicolo":"ECO UP MOVE UP 1.0 68 CV"}
	
	//gestisci i casi speciali se necessario
	function st_chiudi_speciale($special,$marc) {
	
		$obj=json_decode(substr($marc['des_note'],3));
		
		if ($special=='ANT') {
			$marc['des_note']="";
			$this->st_sposta_marcatura_speciale($marc,$obj);
		}
		elseif ($special=='PUL') {
			$this->st_sposta_marcatura_speciale($marc,$obj);
		}
		
	}
	
	function st_apri_speciale($speciale,$coll,$info) {
	
		//info è un campo che può essere usato in qualche circostanza
	
		$ret=array("note"=>"","lamentato"=>"");
		$note="";
		$line=array();
		$lines=$this->st_get_ultime_marcature();
		$line=$lines[$coll];
		
		//serve per non dare la possibilità di aprire una marcatura speciale su un'altra marcatura speciale
		$old_spec="";
		$old_spec=substr($line['des_note'],0,3);
		
		if ($speciale=="ATT" && $old_spec=="") {
			//attesa lavoro
			$note='ATT';
			$limite='0.10';
			$note.='{"coll":"'.$coll.'","rif":"'.$line['mov'].'","lam":"'.$line['inc'].'","limite":"'.$limite.'"}';
			$ret['lamentato']='A';
		}
		
		elseif ($speciale=="ANT" && $old_spec=="") {
			//chiusura anticipata della marcatura
			$note='ANT';
			$limite='';
			$note.='{"coll":"'.$coll.'","rif":"'.$line['mov'].'","lam":"'.$line['inc'].'","limite":"'.$limite.'"}';
			$ret['lamentato']='D';
		}
		elseif ($speciale=="SER") {
			//Servizio
			$note='SER';
			$limite='';
			$note.='{"coll":"'.$coll.'","rif":"'.$line['mov'].'","lam":"'.$line['inc'].'","limite":"'.$limite.'"}';
			$ret['lamentato']='G';
		}
		
		elseif ($speciale=="PUL") {
			//pulizia
			$note='PUL';
			$limite='';
			//recupera il tempo marcato nell'ultimo odl chiuso dal collaboratore (PULIZIA non è attivabile da NUOVO)
			//$odl=$maestro->get_ultima_timbratura($coll]);
			$tot_marcato=$this->get_totale_marcato($line['mov']);
			//$tot_marcato=10.5;
			$l=round(($tot_marcato*0.04),2);
			if ($l<0.05) $limite=0.05;
			elseif ($l>0.5) $limite=0.5;
			else $limite=''.$l;
			$note.='{"coll":"'.$coll.'","rif":"'.$line['mov'].'","lam":"'.$line['inc'].'","limite":"'.$limite.'"}';
			$ret['lamentato']='F';
		}
		
		elseif ($speciale=="PRV") {
			//Prova Vettura
			$note='PRV';
			$limite='0.3';
			//odl è l'ordine della vettura in prova
			$note.='{"coll":"'.$coll.'","rif":"'.$line['mov'].'","lam":"'.$line['inc'].'","limite":"'.$limite.'","odl":"'.$info.'"}';
			$ret['lamentato']='I';
		}
		
		elseif ($speciale=="CIN") {
			//citnow
			$note='CIN';
			$limite='0.05';
			$note.='{"coll":"'.$coll.'","rif":"'.$line['mov'].'","lam":"'.$line['inc'].'","limite":"'.$limite.'"}';
			$ret['lamentato']='J';
		}
		
		elseif ($speciale=="PER") {
			//citnow
			$note='PER';
			$limite='0.40';
			$note.='{"coll":"'.$coll.'","rif":"'.$line['mov'].'","lam":"'.$line['inc'].'","limite":"'.$limite.'"}';
			$ret['lamentato']='K';
		}
		
		/*elseif ($param[note]=="MCQ") {
			//1500
			$note='MCQ';
			$note.='{"coll":"'.$param[coll].'","rif":"'.$open[0][num_rif_movimento].'","lam":"'.$open[0][cod_inconveniente].'","telaio":"'.$param[telaio].'","km":"'.$param[km].'"}';
			$lam->inc='B';
		}
		
		elseif ($param[note]=="EXT") {
			//Lavoro senza odl
			$note='EXT';
			$note.='{"coll":"'.$param[coll].'","rif":"'.$open[0][num_rif_movimento].'","lam":"'.$open[0][cod_inconveniente].'","code":"'.$param[code].'"}';
			$lam->inc='C';
		}
		
		elseif ($param[note]=="VRT") {
			//Sostituzione RT
			$note='VRT';
			$note.='{"coll":"'.$param[coll].'","rif":"'.$open[0][num_rif_movimento].'","lam":"'.$open[0][cod_inconveniente].'"}';
			$lam->inc='E';
		}*/
			
		$ret['note']=$note;
		return $ret;
	}
	
	function st_cancella_marcatura($coll) {
		//prendi la marcatura aperta
		$lines=$this->st_get_marcature_aperte();
		$marc=$lines[$coll][0];
		
		$query="DELETE 
				of_riltem 
				WHERE num_rif_movimento='".$marc['num_rif_movimento']."' AND cod_inconveniente='".$marc['cod_inconveniente']."' AND cod_operaio='".$marc['cod_operaio']."' AND num_riga='".$marc['num_riga']."'
				";
		$result=sqlsrv_query($this->db_handler,$query);
	}
	
	function st_chiudi_marcatura($dt,$coll,$stato_lam) {
		// "dt" è la data e tempo (AAAAMMDD HH:MM) di chiusura
		// se "" allora ADESSO
		if ($dt=="") $dt=date('Ymd H:i');
		
		//stato del lamentato (R,M,I,T -  L ?)
		//if($stato_lam=="") $stato_lam="R";
		
		//prendi la marcatura aperta
		$marc=array();
		$lines=$this->st_get_marcature_aperte();
		$marc=$lines[$coll][0];
		
		//se $marc è un array marcatura (1 per via della funzione count)
		if (count($marc)>1) {
		
			//LEGGI NOTE = INFO SPECIAL
			$special="";
			$special=substr($marc['des_note'],0,3);
			
			//CHIUDI MARCATURA
			$delta=$this->delta_h(substr($dt,9,5),$marc['t']);
			$query="UPDATE 
					of_riltem 
					SET dat_ora_fine=CAST('".$this->convert_tosql($dt)."' AS DATETIME), 
					qta_ore_lavorate='".$delta."'
					WHERE num_rif_movimento='".$marc['num_rif_movimento']."' AND cod_inconveniente='".$marc['cod_inconveniente']."' AND cod_operaio='".$marc['cod_operaio']."' AND num_riga='".$marc['num_riga']."'
					";
					
			if ($result=sqlsrv_query($this->db_handler,$query)) {
			
				//AGGIORNA STATO LAMENTATO
				if ($stato_lam!="") {
					$query="UPDATE 
							gn_movdet 
							SET ind_inc_stato='".$stato_lam."' 
							WHERE num_rif_movimento='".$marc['num_rif_movimento']."' AND cod_inconveniente='".$marc['cod_inconveniente']."' AND ind_tipo_riga='I'
							";
					$result=sqlsrv_query($this->db_handler,$query);
				}
			
				//GESTISCI SPECIALE
				if ($special!="") {
					$this->st_chiudi_speciale($special,$marc);
				}
			}
		}
	}
	
	//cambia ora inizio
	function st_chg_inizio($dt,$coll) {
	
		//prendi la marcatura aperta
		$marc=array();
		$lines=$this->st_get_marcature_aperte();
		$marc=$lines[$coll][0];
		
		$query="UPDATE 
				of_riltem 
				SET dat_ora_inizio=CAST('".$this->convert_tosql($dt)."' AS DATETIME) 
				WHERE num_rif_movimento='".$marc[num_rif_movimento]."' AND cod_inconveniente='".$marc[cod_inconveniente]."' AND cod_operaio='".$marc[cod_operaio]."' AND num_riga='".$marc[num_riga]."'
				";
				
		$result=sqlsrv_query($this->db_handler,$query);
	}
	
	//chiudi e sposta in base alle inidcazioni nelle NOTE
	function st_sposta_marcatura_speciale($marc,$obj) {

		$query="UPDATE 
				of_riltem 
				SET num_rif_movimento='".$obj->rif."',
				cod_inconveniente='".$obj->lam."',
				num_riga=(
					SELECT
					isnull(MAX(num_riga),0)
					FROM of_riltem 
					WHERE num_rif_movimento='".$obj->rif."' AND cod_inconveniente='".$obj->lam."'
				)+1,
				des_note='".$marc['des_note']."' 
				WHERE num_rif_movimento='".$marc['num_rif_movimento']."' AND cod_inconveniente='".$marc['cod_inconveniente']."' AND num_riga='".$marc['num_riga']."'";
				
		$result=sqlsrv_query($this->db_handler,$query);
	}
	
	///////////////////////////////////////////////////////
	//CHIUDE LA PRECEDENTE ED APRE LA NUOVA SE NECESSARIO
	//////////////////////////////////////////////////////
	function st_apri_marcatura($mov,$lam,$coll,$speciale,$stato_lam,$odl_fittizio) {
		
		//chiudi eventuale marcatura aperta
		$this->st_chiudi_marcatura("",$coll,$stato_lam);
		
		//se la marcatura speciale identifica l'uscita
		if ($speciale=='EXI') return;
		
		//echo $odl_fittizio.' '.$speciale;
		
		$nota="";
		$sp=array("note"=>"","lamentato"=>"");
		if ($speciale!="") {
			$sp=$this->st_apri_speciale($speciale,$coll,$mov);
			if ($sp['lamentato']!="" && $odl_fittizio!="") {
				if ($sp['note']!="") $nota=$sp['note'];
				$lam=$sp['lamentato'];
				$mov=$odl_fittizio;
			}
			else {
				//significa che l'identificazione della marcatura speciale non è andata a buon fine (Speciale!="" ma nessun lamentato)
				return;
			}
			
		}
		
		//se a questo punto "mov" o "lam" sono "" RETURN
		if ($mov=="" || $lam=="") return;

		//previene l'apertura di una marcatura se ce n'è un'altra aperta
		$aperte=$this->st_get_marcature_aperte();
		if ( array_key_exists($coll,$aperte) ) return;
		
		$query="INSERT INTO 
				of_riltem (num_rif_movimento,cod_inconveniente,cod_operaio,num_riga,dat_ora_inizio,qta_ore_lavorate,des_note,ora_entrata_effettiva,ind_turno) 
				VALUES ('".$mov."','".$lam."','".$coll."',
				(SELECT
					isnull(MAX(num_riga),0)
					FROM of_riltem 
					WHERE num_rif_movimento='".$mov."' AND cod_inconveniente='".$lam."'
				)+1,
				CAST('".date('d-m-Y H:i')."' AS DATETIME),'0.0','".$nota."',CAST('".date('d-m-Y H:i:s')."' AS DATETIME),'')
				";
				
		$result=sqlsrv_query($this->db_handler,$query);

		//setta il lamentato in stato L (in lavorazione)
		$query="UPDATE GN_MOVDET 
				SET ind_inc_stato='L' 
				WHERE num_rif_movimento='".$mov."' AND cod_inconveniente='".$lam."' AND ind_tipo_riga='I'
		";

		$result=sqlsrv_query($this->db_handler,$query);
		
		//echo $query;
	}
	
	//ritorna il totale delle marcature per un odl
	function get_totale_marcato($odl) {
		$ret=0;
		$query="SELECT sum(inc_marc_chiuse)AS tot FROM LISTA_LAMENTATI WHERE mov='".$odl."'";
		$result=sqlsrv_query($this->db_handler,$query);
		$row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC);
		$ret=$row[tot];
		return $ret;
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	//FUNZIONI VARIE DI INTERFACCIAMENTO SQL
	
	/*function chiudi_timbratura_prova($coll,$stato,$odl,$esito) {
		$res="error";
	
		//leggi timbratura aperta e chiudila se esiste
		$int_coll="('".$coll."')";
		$open=$this->get_tempi_aperti($int_coll);
		if(count($open)==1) {
			$now=date('d-m-Y H:i');
			$delta=$this->delta_h(date('H:i'),$open[0][t]);
			$special=$open[0][des_note];
			$query="UPDATE of_riltem SET dat_ora_fine=CAST('".$now."' AS DATETIME), qta_ore_lavorate='".$delta."' WHERE num_rif_movimento='".$open[0][num_rif_movimento]."' AND cod_inconveniente='".$open[0][cod_inconveniente]."' AND cod_operaio='".$open[0][cod_operaio]."' AND num_riga='".$open[0][num_riga]."'";
		}
	*/	
		/*modifica lo stato del lamentato (R,M,I,T -  L ?)
		if($result=mssql_query($query,$this->db_handler)) {
			$query="UPDATE gn_movdet SET ind_inc_stato='".$stato."' WHERE num_rif_movimento='".$open[0][num_rif_movimento]."' AND cod_inconveniente='".$open[0][cod_inconveniente]."' AND ind_tipo_riga='I'";
			if($result=mssql_query($query,$this->db_handler)) $res='ok';
			else $res='error';
		}*/
		
		/*if($result=sqlsrv_query($this->db_handler,$query)) {
			//AGGIORNA ODL
			$query="UPDATE gn_movtes_off SET cod_stato_commessa='".$esito."' WHERE num_rif_movimento='".$odl."'";
			if($result=sqlsrv_query($this->db_handler,$query)) $res='ok';
		}
		
		//ritorna
		return $res;
	}*/
	
}
?>	