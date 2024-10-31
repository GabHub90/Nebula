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
	
	function bf_query($query) {
		$result=sqlsrv_query($this->db_handler,$query);
		return $result;
	}
	
	
	function graffa_prenot($reparto,$d) {
	
		$res=array();
		
		$query="SELECT
                t1.num_rif_movimento AS odl,
                t1.num_commessa as commessa,
                t1.d_entrata,
                t1.stato,
                t1.pren,
                t1.ricon,
                t6.des_tipo_trasporto AS trasporto,
                datepart(hh,t1.dat_prenotazione) as hh,
                datepart(mi,t1.dat_prenotazione) as mm,
                datepart(hh,t1.dat_promessa_consegna) as hr,
                datepart(mi,dat_promessa_consegna) as mr,
                t1.num_rif_veicolo AS veicolo,
                t1.num_km AS km,
                isnull(t1.lams,'') AS lams,
                isnull(t4.des_ragsoc,'') AS util,
                isnull(t5.des_ragsoc,'') AS intest,
                t2.cod_movimento AS cm,
                t3.mat_telaio AS telaio,
                t3.mat_targa AS targa,
                t3.des_veicolo AS des,
                dbo.FnOT_ut(t1.num_rif_movimento) AS ut

                FROM (
                    SELECT 
                    num_rif_movimento,
                    num_commessa,
                    isnull(cod_stato_commessa,'') as stato,
                    CONVERT(VARCHAR(8),dat_prenotazione,112) as pren,
                    CONVERT(VARCHAR(8),dat_promessa_consegna,112) as ricon,
                    isnull(CONVERT(VARCHAR(8),dat_entrata_veicolo,112),'') as d_entrata,
                    dat_prenotazione,
                    dat_promessa_consegna,
                    cod_tipo_trasporto,
                    num_rif_veicolo,
                    num_km,
                    cod_anagra_util,
                    cod_anagra_intest,
                    dbo.fnOT_lams(num_rif_movimento) as lams		                                
                    
                    FROM GN_movtes_off 
                    
                    WHERE cod_officina='".$reparto."' AND isnull(CONVERT(VARCHAR(8),dat_prenotazione,112),'')='".$d."'
                    ) AS t1
                
                LEFT JOIN GN_movtes AS t2 ON t1.num_rif_movimento=t2.num_rif_movimento
                
                LEFT JOIN VE_anavei AS t3 ON t1.num_rif_veicolo=t3.num_rif_veicolo

                LEFT JOIN GN_anagrafiche AS t4 ON t1.cod_anagra_util=t4.cod_anagra

                LEFT JOIN GN_anagrafiche AS t5 ON t1.cod_anagra_intest=t5.cod_anagra
                
                LEFT JOIN TB_off_tipo_trasporto AS t6 ON t1.cod_tipo_trasporto=t6.cod_tipo_trasporto
                
                WHERE 
                t2.cod_movimento='OOP' AND t2.ind_chiuso='N' AND t1.d_entrata='' AND t1.lams!=''
                

                order by hh,mm,odl asc";
		
		if($result=sqlsrv_query($this->db_handler,$query)) {		
			/*while ($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$res[]=$row;
			}*/
		}
		
		//return $res;
		return $result;
	}
	
}
?>	