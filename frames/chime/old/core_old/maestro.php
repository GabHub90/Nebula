<?php
class Maestro {

	private $server="srvdb";
	private $user="sa";
	private $pw="Gns1999";
	private $db="GC_AZI_GABELLINI_prod";
	
	private $db_handler;
	
	function __construct() {
		$this->db_handler=mssql_connect($this->server,$this->user,$this->pw);
		mssql_select_db($this->db,$this->db_handler);
		//settaggio della lingua del SERVER altrimenti sbaglia le date invertendo il mese con il giorno
		$query="SET LANGUAGE Italian";
		mssql_query($query,$this->db_handler);
	}
	
	function __destruct() {
		mssql_close($this->db_handler);
	}
	
	function get_intest_odl($ID) {
		$query='SELECT t1.num_rif_veicolo AS idvei,t1.cod_anagra_util AS idcli,t2.mat_telaio AS telaio,t2.mat_targa AS targa,t3.des_ragsoc AS cliente FROM GN_MOVTES_OFF t1 LEFT JOIN VE_ANAVEI t2 ON t1.num_rif_veicolo=t2.num_rif_veicolo LEFT JOIN GN_ANAGRAFICHE t3 ON t1.cod_anagra_util=t3.cod_anagra WHERE t1.num_rif_movimento='.$ID;
		$result=mssql_query($query,$this->db_handler);
		$row=mssql_fetch_assoc($result);
		return json_encode($row);
	}
	
	//ritorna un array contenente i passaggi di officina compresi tra due date
	function get_passaggi($anno,$data_i,$data_f,$reparto) {
		$lines=array();
		//$query="SELECT CONVERT(VARCHAR(5),dat_documento_i,111)+CONVERT(VARCHAR(5),dat_documento_i,103) AS d,num_rif_movimento,cod_marca,cod_veicolo,mat_telaio,ind_tipo_addebito,cod_tipo_garanzia,cod_movimento,val_listino_mdo,val_netto_mdo,val_listino_ricambi,val_netto_ricambi,val_ore_fatturate,qta_ore_lavorate FROM LISTA_REDD_OFF WHERE CONVERT(VARCHAR(5),dat_documento_i,111)+CONVERT(VARCHAR(5),dat_documento_i,103)>='".$data_i."' AND CONVERT(VARCHAR(5),dat_documento_i,111)+CONVERT(VARCHAR(5),dat_documento_i,103)<='".$data_f."' AND cod_officina='".$reparto."'";
		$query="SELECT CONVERT(VARCHAR(8),dat_documento_i,112) AS d,* FROM LISTA_REDD_OFF WHERE CONVERT(VARCHAR(8),dat_documento_i,112)>='".$data_i."' AND CONVERT(VARCHAR(8),dat_documento_i,112)<='".$data_f."' AND cod_officina='".$reparto."'";
		$result=mssql_query($query,$this->db_handler);
		while ($row=mssql_fetch_assoc($result)) {
			$lines[]=$row;
		}
		//$ret=json_encode($lines);
		//echo $ret;
		return $lines;
	}
	
	//ritorna un array contenente le marcature di officina comprese tra due date
	function get_marcature($data_i,$data_f) {
		$lines=array();
		$query="SELECT t1.num_rif_movimento,t1.cod_inconveniente,t1.cod_operaio,t1.num_riga, CONVERT(VARCHAR(8),t1.dat_ora_inizio,112) AS d1,CONVERT(VARCHAR(5),t1.dat_ora_inizio,108) AS t1,CONVERT(VARCHAR(8),t1.dat_ora_fine,112) AS d2,CONVERT(VARCHAR(5),t1.dat_ora_fine,108) AS t2,t1.qta_ore_lavorate AS ore,t1.des_note,t2.* FROM of_riltem AS t1 LEFT JOIN LISTA_LAMENTATI AS t2 ON t1.num_rif_movimento=t2.mov AND t1.cod_inconveniente=t2.inc WHERE CONVERT(VARCHAR(8),t1.dat_ora_inizio,112)>='".$data_i."' AND CONVERT(VARCHAR(8),t1.dat_ora_inizio,112)<='".$data_f."' AND t1.qta_ore_lavorate>'0' ORDER BY t1.num_rif_movimento, t1.cod_inconveniente,t1.cod_operaio,CONVERT(VARCHAR(8),t1.dat_ora_inizio,112)";
		$result=mssql_query($query,$this->db_handler);
		while ($row=mssql_fetch_assoc($result)) {
			$lines[]=$row;
		}
		//$ret=json_encode($lines);
		//echo $ret;
		return $lines;
	}
		
	function prova() {
		$lines=array();
		$query="SELECT CONVERT(VARCHAR(8),dat_documento_i,112) AS d,* FROM LISTA_REDD_OFF WHERE CONVERT(VARCHAR(8),dat_documento_i,112)>='20130701' AND CONVERT(VARCHAR(8),dat_documento_i,112)<='20130731' AND cod_officina='PP'";
		$result=mssql_query($query,$this->db_handler);
		while ($row=mssql_fetch_assoc($result)) {
			$lines[]=$row;
		}
		return $lines;
	}
	
	function get_prenotazioni($data,$off) {
		$lista=array();
		$query="SELECT t1.num_rif_movimento as odl,CONVERT(VARCHAR(8),t1.dat_prenotazione,112) as data,t3.des_ragsoc as intestazione,t2.mat_telaio as telaio,t1.num_km as km,ISNULL(CONVERT(VARCHAR(8),t2.dat_inizio_garanzia,112),'') as consegna,t3.sig_tel_res as tel1,t3.sig_tel1_res as tel2,ISNULL(t3.des_email1,'') as mail,t4.cod_movimento FROM GN_MOVTES_OFF t1 INNER JOIN VE_ANAVEI t2 ON t1.num_rif_veicolo=t2.num_rif_veicolo INNER JOIN GN_ANAGRAFICHE t3 ON t1.cod_anagra_util=t3.cod_anagra INNER JOIN GN_MOVTES t4 ON t1.num_rif_movimento=t4.num_rif_movimento WHERE CONVERT(VARCHAR(8),t1.dat_prenotazione,112)='".$data."' AND t1.cod_officina='".$off."' AND t1.num_commessa=0 AND t4.cod_movimento='OOP'";
		$result=mssql_query($query,$this->db_handler);
		while ($row=mssql_fetch_assoc($result)) {
			$lista[]=$row;
		}
		return $lista;
	}
	
	function get_lamentati($txt) {
		$lista=array();
		$query="SELECT * FROM LISTA_LAMENTATI WHERE mov IN (".$txt.") ORDER BY mov,inc";
		$result=mssql_query($query,$this->db_handler);
		while ($row=mssql_fetch_assoc($result)) {
			$lista[$row[mov]][$row[inc]]=$row[inc_testo];
		}
		
		return $lista;
	}
}
?>	