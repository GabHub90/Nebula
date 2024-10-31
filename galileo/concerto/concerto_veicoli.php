<?php

include_once(DROOT.'/nebula/galileo/concerto/tabs/VE_ANAVEI.php');
include_once(DROOT.'/nebula/galileo/concerto/tabs/TB_VEI_MARCHE.php');
include_once(DROOT.'/nebula/galileo/concerto/tabs/TB_INF_MARCHE.php');
include_once(DROOT.'/nebula/galileo/concerto/tabs/TB_VEI_CODVEI.php');
include_once(DROOT.'/nebula/galileo/concerto/tabs/VE_ANAVEI_ANAGRA.php');
include_once(DROOT.'/nebula/galileo/concerto/tabs/TB_VGI_TIPO_VEICOLO.php');
include_once(DROOT.'/nebula/galileo/concerto/tabs/TB_PIT_TEMPARIO_VEICOLI.php');
include_once(DROOT.'/nebula/galileo/concerto/tabs/GN_ANAGRAFICHE.php');

class galileoConcertoVeicoli extends galileoOps {

    function __construct() {

        $this->tabelle['VE_ANAVEI']=new concerto_veanavei();
        $this->tabelle['TB_VEI_MARCHE']=new concerto_tbveimarche();
        $this->tabelle['TB_INF_MARCHE']=new concerto_tbinfmarche();
		$this->tabelle['TB_VEI_CODVEI']=new concerto_tbveicodvei();
		$this->tabelle['TB_VGI_TIPO_VEICOLO']=new concerto_tbvgitipoveicolo();
		$this->tabelle['TB_PIT_TEMPARIO_VEICOLI']=new concerto_tbpittemparioveicoli();
		$this->tabelle['VE_ANAVEI_ANAGRA']=new concerto_anaveianagra();
		$this->tabelle['GN_ANAGRAFICHE']=new concerto_gnanagrafiche();
        
    }

	function blocco() {

		$txt=" SELECT
			t1.num_rif_veicolo as rif,
			'concerto' as dms,
			isnull(t1.mat_targa,'') as targa,
			isnull(t1.mat_telaio,'') as telaio,
			isnull(t1.cod_marca,'') as cod_marca,
			isnull(t2.des_marca,'') as des_marca,
			isnull(t1.cod_veicolo,'') as modello,
			isnull(t1.des_veicolo,'') as des_veicolo,
			isnull(t1.cod_colore_interno,'') as cod_interno,
			isnull(t1.cod_colore_esterno,'') as cod_esterno,
			isnull(t1.des_colore_interno,'') as des_interno,
			isnull(t1.des_colore_esterno,'') as des_esterno,
			isnull(t1.cod_alimentazione,'') as cod_alim,
			isnull(t1.mat_motore,'') as mat_motore,
			t1.des_vw_modelyear AS anno_modello,
			isnull(t1.cod_vw_tipo_veicolo,'') as cod_vw_tipo_veicolo,
			isnull(t6.des_vw_tipo_veicolo,'') as des_vw_tipo_veicolo,
			isnull(tpit.cod_id_gruppo,'') AS cod_po_tipo_veicolo,
			isnull(tpit.des_veicolo,'') AS des_po_tipo_veicolo,
			isnull(t1.cod_ente_venditore,'') as cod_ente_venditore,
			isnull(CONVERT(varchar(8),t1.dat_immatricolazione,112),'') as d_imm,
			isnull(CONVERT(varchar(8),t1.dat_inizio_garanzia,112),'') as d_cons,
			isnull(CONVERT(varchar(8),t1.dat_fine_garanzia,112),'') as d_fine_gar,
			isnull(CONVERT(varchar(8),t1.dat_ultima_revisione,112),'') as d_rev,
			isnull(t1.des_note_off,'') as des_note_off,
			'' AS km,
			t1.cod_infocar,
			t1.cod_infocar_anno,
			t1.cod_infocar_mese,
			isnull(t5.cod_marca_infocar,'') as cod_marca_infocar
		";

		return $txt;
	}

    function veiSelect($arr) {

        $this->query=$this->blocco();
		
        $this->query.=" FROM ".$this->tabelle['VE_ANAVEI']->getTabName()." AS t1";
				
		$this->query.=" LEFT JOIN ".$this->tabelle['TB_VEI_MARCHE']->getTabName()." AS t2 ON t1.cod_marca=t2.cod_marca";
		
        $this->query.=" LEFT JOIN ".$this->tabelle['TB_INF_MARCHE']->getTabName()." AS t5 ON t1.cod_marca=t5.cod_marca";

		$this->query.=" LEFT JOIN ".$this->tabelle['TB_VGI_TIPO_VEICOLO']->getTabName()." AS t6 ON t1.cod_marca=t6.cod_vw_marca AND t1.cod_vw_tipo_veicolo=t6.cod_vw_tipo_veicolo";

		$this->query.=" LEFT JOIN ".$this->tabelle['TB_PIT_TEMPARIO_VEICOLI']->getTabName()." AS tpit ON t1.cod_veicolo=tpit.cod_veicolo AND t1.des_vw_modelyear=tpit.des_model_year";
				
		$this->query.=" WHERE t1.num_rif_veicolo='".$arr['rif']."' AND t1.ind_annullo='N'";

		return true;
    }

	function ttSelect($arr) {

        $this->query=$this->blocco();
		
        $this->query.=" FROM ".$this->tabelle['VE_ANAVEI']->getTabName()." AS t1";
				
		$this->query.=" LEFT JOIN ".$this->tabelle['TB_VEI_MARCHE']->getTabName()." AS t2 ON t1.cod_marca=t2.cod_marca";
		
        $this->query.=" LEFT JOIN ".$this->tabelle['TB_INF_MARCHE']->getTabName()." AS t5 ON t1.cod_marca=t5.cod_marca";

		$this->query.=" LEFT JOIN ".$this->tabelle['TB_VGI_TIPO_VEICOLO']->getTabName()." AS t6 ON t1.cod_marca=t6.cod_vw_marca AND t1.cod_vw_tipo_veicolo=t6.cod_vw_tipo_veicolo";

		$this->query.=" LEFT JOIN ".$this->tabelle['TB_PIT_TEMPARIO_VEICOLI']->getTabName()." AS tpit ON t1.cod_veicolo=tpit.cod_veicolo AND t1.des_vw_modelyear=tpit.des_model_year";

		if ($arr['targa']!="" || $arr['telaio']!="") {
				
			if (isset($arr['esatto']) && $arr['esatto']==1) {
				$this->query.=" WHERE ".($arr['targa']!=''?"isnull(t1.mat_targa,'')='".$arr['targa']."' ".($arr['telaio']!=""?$arr['operazione']:""):"")." ".($arr['telaio']!=""?"isnull(t1.mat_telaio,'')='".$arr['telaio']."'":"");
			}
			else {
				$this->query.=" WHERE ".($arr['targa']!=''?"t1.mat_targa LIKE '%".$arr['targa']."%' ".($arr['telaio']!=""?$arr['operazione']:""):"")." ".($arr['telaio']!=""?"t1.mat_telaio LIKE '%".$arr['telaio']."%'":"");
			}

			$this->query.=" AND t1.ind_annullo='N'";

			if ($this->orderBy!="") $this->query.="ORDER BY ".$this->orderBy;

			return true;

		}

		return false;
    }

	function getDesModello($arr) {

		$this->query="SELECT
			des_veicolo AS descrizione
		";

		$this->query.=" FROM ".$this->tabelle['TB_VEI_CODVEI']->getTabName();
		
		$this->query.=" WHERE cod_veicolo='".$arr['modello']."' AND cod_marca='".$arr['marca']."'";

		return true;
	}

    function getQcheck($arr) {

        //$arr è un array [] SEMPRE

        $this->query="SELECT
            vei.des_veicolo
        ";

        $this->query.=" FROM ".$this->tabelle['VE_ANAVEI']->getTabName()." AS t1";

        $this->query.=" WHERE vei.mat_targa='".$arr[0]."'";

        return true;
    }

	function getLinker($arr) {

		if (isset($arr['operatore'])) $op=$arr['operatore'];
		else $op='AND';

		$this->query=$this->blocco();

		$this->query.="
			,isnull(t3.num_rif_veicolo_progressivo,'999') as progressivo,
			isnull(t3.cod_anagra_util,'') cod_anagra_util,
			isnull(t3.cod_anagra_intest,'') cod_anagra_intest,
			isnull(t3.cod_anagra_loc,'') cod_anagra_locat,
			isnull(t7.des_ragsoc,'') as ragsoc_util,
			isnull(t8.des_ragsoc,'') as ragsoc_intest,
			isnull(t9.des_ragsoc,'') as ragsoc_locat,
			isnull(t7.des_email1,'') as email_util,
			isnull(t8.des_email1,'') as email_intest,
			isnull(t9.des_email1,'') as email_locat,
			isnull(t7.sig_tel_res,'') as tel1_util,
			isnull(t8.sig_tel_res,'') as tel1_intest,
			isnull(t9.sig_tel_res,'') as tel1_locat,
			isnull(t7.sig_tel1_res,'') as tel2_util,
			isnull(t8.sig_tel1_res,'') as tel2_intest,
			isnull(t9.sig_tel1_res,'') as tel2_locat
		";

		$this->query.=" FROM ".$this->tabelle['VE_ANAVEI']->getTabName()." AS t1";

		$this->query.=" LEFT JOIN ".$this->tabelle['TB_VEI_MARCHE']->getTabName()." AS t2 ON t1.cod_marca=t2.cod_marca";
		
        $this->query.=" LEFT JOIN ".$this->tabelle['TB_INF_MARCHE']->getTabName()." AS t5 ON t1.cod_marca=t5.cod_marca";

		$this->query.=" LEFT JOIN ".$this->tabelle['VE_ANAVEI_ANAGRA']->getTabName()." AS t3 ON t1.num_rif_veicolo=t3.num_rif_veicolo";

		$this->query.=" LEFT JOIN ".$this->tabelle['TB_VGI_TIPO_VEICOLO']->getTabName()." AS t6 ON t1.cod_marca=t6.cod_vw_marca AND t1.cod_vw_tipo_veicolo=t6.cod_vw_tipo_veicolo";

		$this->query.=" LEFT JOIN ".$this->tabelle['TB_PIT_TEMPARIO_VEICOLI']->getTabName()." AS tpit ON t1.cod_veicolo=tpit.cod_veicolo AND t1.des_vw_modelyear=tpit.des_model_year";

		$this->query.=" LEFT JOIN ".$this->tabelle['GN_ANAGRAFICHE']->getTabName()." AS t7 ON t3.cod_anagra_util=t7.cod_anagra";
		$this->query.=" LEFT JOIN ".$this->tabelle['GN_ANAGRAFICHE']->getTabName()." AS t8 ON t3.cod_anagra_intest=t8.cod_anagra";
		$this->query.=" LEFT JOIN ".$this->tabelle['GN_ANAGRAFICHE']->getTabName()." AS t9 ON t3.cod_anagra_loc=t9.cod_anagra";

		//si dà per scontato che sia stato passato almeno un criterio
		//non metto alcun criterio sicuramente TRUE per evitare che venga estratto tutto il mondo in caso di errore
		$this->query.=" WHERE ";

		$a=false;

		if (isset($arr['targa']) && $arr['targa']!="") {
			if ($a) $this->query.=' '.$op;
			$this->query.=" t1.mat_targa LIKE '%".$arr['targa']."%'";
			$a=true;
		}

		if (isset($arr['telaio']) && $arr['telaio']!="") {
			if ($a) $this->query.=' '.$op;
			$this->query.=" t1.mat_telaio LIKE '%".$arr['telaio']."%'";
			$a=true;
		}

		if (isset($arr['codice']) && $arr['codice']!="") {
			if ($a) $this->query.=' '.$op;
			$this->query.=" t1.num_rif_veicolo='".$arr['codice']."'";
			$a=true;
		}

		if (isset($arr['anagra']) && $arr['anagra']!="") {
			if ($a) $this->query.=' '.$op;
			$this->query.=" (t7.des_ragsoc LIKE '%".$arr['anagra']."%' OR t8.des_ragsoc LIKE '%".$arr['anagra']."%' OR t9.des_ragsoc LIKE '%".$arr['anagra']."%')";
			$a=true;
		}

		$this->query.=' ORDER BY t1.cod_marca,t1.num_rif_veicolo DESC,progressivo DESC';

		return true;
	}

	function getComest($arr) {

		if (isset($arr['odl'])) $arr['odl']=(int)$arr['odl'];

		$this->query="SELECT
			'concerto' AS dms,
			lista.odl,
			lista.targa,
			lista.telaio,
			lista.descrizione
		";

		if ($arr['odl'] && $arr['odl']!="")  {
			$this->query.=" FROM (SELECT
				e.num_rif_movimento AS odl,
				g.mat_targa AS targa,
				g.mat_telaio AS telaio,
				g.des_veicolo AS descrizione
				FROM ".$this->tabelle['GN_MOVTES_OFF']->getTabName()." AS e
				INNER JOIN ".$this->tabelle['VE_ANAVEI']->getTabName()." AS g ON e.num_rif_veicolo=g.num_rif_veicolo
				WHERE e.num_rif_movimento='".number_format($arr['odl'],0,'','')."'
			) AS lista";
		}

		//l'esistenza di TXT dovrebbe essere già stata verificata dal chiamante
		else {

			$this->query.=" FROM (
				SELECT
				'0' AS odl,
				a.mat_targa AS targa,
				a.mat_telaio AS telaio,
				a.des_veicolo AS descrizione
				FROM ".$this->tabelle['VE_ANAVEI']->getTabName()." AS a
				WHERE a.mat_targa LIKE '%".$arr['txt']."%' OR a.mat_telaio LIKE '%".$arr['txt']."%'
			";

			$this->query.=" ) AS lista";

		}

		$this->query.=" GROUP BY lista.telaio,lista.targa,lista.descrizione,lista.odl";

		//$this->query.=" ORDER BY lista.telaio,lista.odl";

		return true;
	}

} 


?>