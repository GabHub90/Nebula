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
		//settaggio della lingua del SERVER altrimenti sbaglia le date invertendo il mese con il giorno
		$query="SET LANGUAGE Italian";
		sqlsrv_query($this->db_handler,$query);
	}
	
	function __destruct() {
		sqlsrv_close($this->db_handler);
	}
	
	function carb_query($query) {
		$result=sqlsrv_query($this->db_handler,$query);
		return $result;
	}
	
	function carb_tt($txt) {
		$res=array();
		
		$query="select
				t1.num_rif_veicolo as rif,
				t1.mat_telaio as telaio,
				t1.mat_targa as targa,
				t1.des_veicolo as des,
				t1.cod_veicolo as modello,
				isnull(t6.natura,'') as cod_natura,
				isnull(t2.des_natura_vendita,'') as des_natura,
				isnull(t6.num_gestione,0) as gestione
				
				
				from ve_anavei as t1
				
				left join (
				    SELECT
				    t1.num_rif_veicolo,
				    t1.num_gestione,
				    t1.cod_natura_vendita as natura
				   
				    FROM VE_anavei_ges as t1
				    WHERE (
				        SELECT count(*)
				        FROM VE_anavei_ges as t2
				        where t1.num_rif_veicolo=t2.num_rif_veicolo
				        and t2.num_gestione>t1.num_gestione
				    )=0
				    GROUP BY num_rif_veicolo,num_gestione,cod_ubicazione,des_ubicazione,dat_arrivo_fisico,dat_consegna,cod_natura_vendita
				
				) as t6 on t1.num_rif_veicolo=t6.num_rif_veicolo
				
				left join TB_vei_natven as t2 on t6.natura=t2.cod_natura_vendita
				
				where
				
				t1.mat_telaio like '%".$txt."%' or t1.mat_targa like '%".$txt."%'
		";
		
		if($result=sqlsrv_query($this->db_handler,$query)) {		
			while ($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$res[$row[rif]]=$row;
				
				//trova se consegnata
				$res[$row[rif]][flag_cons]=$this->carb_consegnata($row[rif],$row[gestione]);
			}
		}

		return $res;
	}
	
	function carb_get_dv($id) {
		$ret=array();
		
		$query="select
				t1.num_rif_veicolo as rif,
				t1.mat_telaio as telaio,
				t1.mat_targa as targa,
				t1.des_veicolo as des
				
				from ve_anavei as t1
				
				where
				t1.num_rif_veicolo='".$id."'
		";
		
		if($result=sqlsrv_query($this->db_handler,$query)) {		
			while ($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$res[$row[rif]]=$row;
			}
		}
		
		return $res;
	}
	
	
	function carb_consegnata($veicolo,$gestione) {
	
		//$gestione="";
		$vc='';
	
		/*leggi ultima gestione
		$query="SELECT
		 		t1.num_gestione
		
		 		FROM VE_anavei_ges as t1
				WHERE t1.num_rif_veicolo='".$veicolo."'
				ORDER BY t1.num_gestione DESC
		 		";
		
		if($result=sqlsrv_query($this->db_handler,$query)) {		
			$row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC);
			$gestione=$row[num_gestione];
		}*/
		
		
		//leggi numero di movimenti VC (consegna a cliente)
		/*$query="select
				count(*) as c
		
				FROM VE_CONTRA_TES as t1
		
				INNER JOIN GN_MOVTES_VEI as t2 on t1.num_rif_contratto=t2.num_rif_contratto_padre
		
				INNER JOIN GN_MOVTES as t3 on t2.num_rif_movimento=t3.num_rif_movimento and t3.cod_movimento='VC'
		
				WHERE
				t1.num_rif_veicolo='".$veicolo."' AND t1.num_gestione='".$gestione."' AND isnull(t3.dat_chiusura_movimento,'')<>''
				";*/
			
			$query="SELECT
					isnull(t1.cod_acquirente,'') as acq,
					isnull(convert(varchar(8),t1.dat_consegna,112),'') as d_cons
					FROM VE_anavei_ges as t1
					WHERE t1.num_rif_veicolo='".$veicolo."' AND t1.num_gestione='".$gestione."'
					";
	
		if($result=sqlsrv_query($this->db_handler,$query)) {		
			$row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC);
			$vc=json_encode($row);
		}
		
		return $vc;
	}
	
	function carb_gestioni() {
		$gestioni=array();
		$query="SELECT * FROM TB_vei_natven";
		if($result=sqlsrv_query($this->db_handler,$query)) {
			while ($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$gestioni[$row[cod_natura_vendita]]=$row[des_natura_vendita];
			}
		}
	return $gestioni;
	}
	
	function carb_st_gestioni() {
		$gestioni=array();
		$query="SELECT * FROM TB_vei_natven";
		if($result=sqlsrv_query($this->db_handler,$query)) {
			while ($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$gestioni[$row[cod_natura_vendita]]=array("testo"=>$row[des_natura_vendita],"flag"=>0);
			}
		}
	return $gestioni;
	}
	
}


?>	