<?php 

class QueryMan {

	private $azione=array();
	private $reparto;

	function __construct($arr,$reparto) {
		$this->azione=$arr;
		$this->repato=$reparto;
	}
	
	function get_query($codice) {
		$res="";
		if(is_callable(array($this, $codice))){
			$res=$this->$codice();
		}
		
		return $res;
	}
	
	function completa($query) {
	
		$sost=explode(",",$this->azione['sostituzioni']);
		
		//echo json_encode($sost);
		
		foreach ($sost as $s) {
			$query=str_replace("[".$s."]", "'".$this->azione[$s]."'", $query);
		}
		
		return $query;
	}
	
	function avv_app() {
	
		$query="SELECT 
				t1.num_rif_movimento AS rif, 
				t1.pren AS d_prenotazione, 
				t1.num_rif_veicolo AS veicolo, 
				isnull(t1.lams,'') AS lamentati, 
				isnull(t4.des_ragsoc,'') AS ragsoc, 
				t4.cod_anagra AS anagrafica, 
				isnull(t4.contatto,'') AS contatto, 
				t3.mat_telaio AS telaio, 
				t3.mat_targa AS targa, 
				t3.des_veicolo AS descrizione, 
				isnull(t5.d_invio,'') AS d_invio, 
				t5.note AS note 
				FROM ( 
					SELECT num_rif_movimento, 
					CONVERT(VARCHAR(8),dat_prenotazione,112) as pren, 
					isnull(CONVERT(VARCHAR(8),dat_entrata_veicolo,112),'') as d_entrata, 
					num_rif_veicolo, 
					cod_anagra_util, 
					dbo.fnOT_lams(num_rif_movimento) as lams 
					FROM GN_movtes_off 
					WHERE cod_officina=[chime_concerto] AND isnull(CONVERT(VARCHAR(8),dat_prenotazione,112),'')=[chime_giorno] 
				) AS t1 
				
				LEFT JOIN GN_movtes AS t2 ON t1.num_rif_movimento=t2.num_rif_movimento 
				LEFT JOIN VE_anavei AS t3 ON t1.num_rif_veicolo=t3.num_rif_veicolo 
				
				LEFT JOIN ( 
					SELECT cod_anagra, 
					des_ragsoc, 
					indicatore, 
					contatto 
					FROM GN_anagrafiche 
					UNPIVOT ( 
						contatto FOR indicatore in (sig_tel_res,sig_tel1_res) 
					) unpiv 
				) AS t4 ON t1.cod_anagra_util=t4.cod_anagra 
				
				LEFT JOIN GN_GAB_CHIME_storico AS t5 ON t1.num_rif_movimento=t5.rif AND t4.contatto=t5.contatto AND t5.giorno=[chime_giorno] AND t5.azione=[ID]
				
				WHERE t2.cod_movimento='OOP' AND t2.ind_chiuso='N' AND t1.d_entrata='' AND t1.lams!=''
				
				order by t1.num_rif_movimento,contatto DESC
		";
		
		//echo $this->completa($query);
		
		return $this->completa($query);
	}
	
	function feedback_pass() {
	
		$query="SELECT
				t1.num_rif_movimento as rif,
				CONVERT(VARCHAR(8),t3.dat_documento_i,112) AS d_fatt,
				t1.cod_officina,
				t1.cod_accettatore,
				t7.des_accettatore,
				t5.cod_movimento,
				t1.num_km as km,
				t5.cod_anagra AS anagra,
				t2.mat_targa as targa,
				t2.mat_telaio as telaio,
				t2.cod_marca,
				t3.val_imponibile_tot as imponibile,
				t3.val_totale as ivato,
				t4.ind_tipo_addebito,
				isnull(t6.des_ragsoc,'') AS ragsoc, 
				isnull(t6.contatto,'') AS contatto,
				dbo.fnOT_lams(t1.num_rif_movimento) as lamentati,
				isnull(t8.d_invio,'') AS d_invio, 
				t8.note AS note
				
				from gn_movtes_off t1
				inner join ve_anavei t2 on t1.num_rif_veicolo = t2.num_rif_veicolo 
				inner join gn_movtes_doc t3 on t1.num_rif_movimento = t3.num_rif_movimento
				inner join gn_movtes t5 on t1.num_rif_movimento = t5.num_rif_movimento
				LEFT join tb_off_accettatori t7 on t1.cod_accettatore = t7.cod_accettatore
				
				inner join (
				    select 
				    t1.num_rif_movimento,
				    t3.ind_tipo_addebito
					
					from gn_movdet t1
					inner join tb_mag_caumov t3 on t1.cod_movimento = t3.cod_movimento
					
					group by 
				    t1.num_rif_movimento,
				    t3.ind_tipo_addebito
				) as t4 on t1.num_rif_movimento = t4.num_rif_movimento
				
				left JOIN (
				    SELECT 
				    cod_anagra, 
				    des_ragsoc, 
				    indicatore, 
				    contatto 
				    FROM GN_anagrafiche 
				    UNPIVOT ( 
				        contatto FOR indicatore in (sig_tel_res,sig_tel1_res) 
				    ) unpiv 
				) AS t6 ON t5.cod_anagra=t6.cod_anagra
				
				LEFT JOIN GN_GAB_CHIME_storico AS t8 ON t1.num_rif_movimento=t8.rif AND t6.contatto=t8.contatto AND t8.giorno=[chime_giorno] AND t8.azione=[ID]
				
				WHERE CONVERT(VARCHAR(8),t3.dat_documento_i,112)>=[chime_giorno] AND CONVERT(VARCHAR(8),t3.dat_documento_i,112)<=[chime_giorno] AND t1.cod_officina=[chime_concerto] and t4.ind_tipo_addebito='C'
				ORDER BY d_fatt,cod_accettatore,anagra,contatto DESC
		";
		
		//echo $this->completa($query);
		
		return $this->completa($query);
	}
	
}

?>