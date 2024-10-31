<?php

include_once(DROOT.'/nebula/galileo/concerto/tabs/GN_ANAGRAFICHE.php');
include_once(DROOT.'/nebula/galileo/concerto/tabs/CA_ANAGRAFICHE_WORKCAR.php');
include_once(DROOT.'/nebula/galileo/concerto/tabs/CA_ANAGRAFICHE_WORKCAR_SOCIETA.php');
include_once(DROOT.'/nebula/galileo/concerto/tabs/CA_ANAGRAFICHE_NOTE.php');
include_once(DROOT.'/nebula/galileo/concerto/tabs/VE_ANAVEI.php');
include_once(DROOT.'/nebula/galileo/concerto/tabs/VE_ANAVEI_ANAGRA.php');

class galileoConcertoAnagrafiche extends galileoOps {

    function __construct() {

        $this->tabelle['GN_ANAGRAFICHE']=new concerto_gnanagrafiche();
        $this->tabelle['GN_ANAGRAFICHE_NOTE']=new concerto_caanagrafichenote();
        $this->tabelle['CA_ANAGRAFICHE_WORKCAR']=new concerto_caanagraficheworkcar();
        $this->tabelle['CA_ANAGRAFICHE_WORKCAR_SOCIETA']=new concerto_caanagraficheworkcarsocieta();
        $this->tabelle['VE_ANAVEI']=new concerto_veanavei();
        $this->tabelle['VE_ANAVEI_ANAGRA']=new concerto_anaveianagra();
        
    }

    function blocco() {

        $txt="SELECT
		    isnull(t2.cod_anagra,'') as cod_anagra,
            isnull(t2.des_ragsoc,'') as ragsoc,
            isnull(t2.cod_cfisc,'') as cfisc,
            isnull(t2.cod_piva,'') as piva,
            isnull(t2.des_email1,'') as email,
            isnull(t2.des_localita_nasc,'') as loc_nasc,
            isnull(t2.cod_cap_nasc,'') as cap_nasc,
            isnull(t2.sig_prov_nasc,'') as prov_nasc,
            isnull(CONVERT(VARCHAR(8),t2.dat_nasc,112),'') as d_nasc,
            isnull(t2.des_indir_res,'') as indir_res,
            isnull(t2.des_localita_res,'') as loc_res,
            isnull(t2.cod_cap_res,'') as cap_res,
            isnull(t2.sig_prov_res,'') as prov_res,
            isnull(t2.sig_tel_res,'') as tel1,
            isnull(t2.sig_tel1_res,'') as tel2,
            isnull(t3.des_patente_numero,'') as des_patente_numero,
            isnull(t3.des_patente_rilasciata_da,'') as des_patente_rilasciata_da,
            isnull(CONVERT(VARCHAR(8),t3.dat_patente_il,112),'') as d_patente,
            isnull(CONVERT(VARCHAR(8),t3.dat_scadenza_patente,112),'') as d_scadenza_patente,
            isnull(t2.des_cogn,'') as cognome,
            isnull(t2.des_nome,'') as nome,
            isnull(t2.cod_naz_nasc,'') as cod_naz_nasc,
            isnull(t2.cod_naz_res,'') as cod_naz_res,
            isnull(t2.cod_localita_res,'') as cod_localita_res,
            isnull(t2.cod_localita_nas,'') as cod_localita_nasc,
            isnull(t2.ind_sesso,'') as ind_sesso,
            isnull(t3.des_email_pec_fe,'') as pec_fe,
            isnull(t3.des_pa_destinatario,'') as cod_des_fe,
            isnull(CONVERT(VARCHAR(8),t6.dat_invio_inf_privacy,112),'') AS pr_a,
            isnull(CONVERT(VARCHAR(8),t6.dat_privacy_accettazione_1,112),'') AS pr_b,
            isnull(CONVERT(VARCHAR(8),t6.dat_privacy_accettazione_2,112),'') AS pr_c,
            isnull(CONVERT(VARCHAR(8),t6.dat_privacy_accettazione_3,112),'') AS pr_d,
            isnull(CONVERT(VARCHAR(8),t6.dat_blocco_pubblicita,112),'') AS pr_e,
            isnull(t6.cod_vw_tipo_cliente_off,'') AS vgi_tipo_cli,
            isnull(t6.ind_vgi_prv_profilazione,'') AS vgi_1,
            isnull(t6.ind_vgi_prv_marketing,'') AS vgi_2,
            isnull(t6.ind_vgi_prv_assicurativi,'') AS vgi_3,
            t6.ind_critico AS vgi_critico,
            t6.ind_critico_comm AS vgi_critico_comm,
            isnull(CONVERT(VARCHAR(8),t6.dat_ultima_var_critico,112),'0') AS vgi_d_cri,
            t6.cod_utente_ultima_var_critico AS vgi_utente_cri,
            t2.flg_pers_fis as pfis,
            t3.ind_azienda as fazi,
            t3.ind_ditta_individuale as fdin,
            t3.ind_ente_pubblico as fepu,
            isnull(t3.cod_mail_motivazione,'') as mail_flag
        ";

        return $txt;

    }

    function anaSelect($arr) {

        $this->query=$this->blocco();

        $this->query.=",'".$arr['tipo']."' AS tipo_anagrafica";

        $this->query.=" FROM ".$this->tabelle['GN_ANAGRAFICHE']->getTabName()." AS t2";

        $this->query.=" LEFT JOIN ".$this->tabelle['CA_ANAGRAFICHE_WORKCAR']->getTabName()." AS t3 ON t2.cod_anagra=t3.cod_anagra";

        $this->query.=" LEFT JOIN ".$this->tabelle['CA_ANAGRAFICHE_WORKCAR_SOCIETA']->getTabName()." AS t6 ON t2.cod_anagra=t6.cod_anagra";

		$this->query.=" WHERE t2.cod_anagra='".$arr['rif']."'";

        return true;

    }

    function getLinker($arr) {

		$this->query=$this->blocco();

		$this->query.="
			,isnull(t7.num_rif_veicolo_progressivo,'999') as progressivo,
            isnull(t8.num_rif_veicolo,'') AS num_rif_veicolo,
			t8.mat_targa as targa,
			t8.des_veicolo
		";

        $this->query.=" FROM ".$this->tabelle['GN_ANAGRAFICHE']->getTabName()." AS t2";

        $this->query.=" LEFT JOIN ".$this->tabelle['CA_ANAGRAFICHE_WORKCAR']->getTabName()." AS t3 ON t2.cod_anagra=t3.cod_anagra";

        $this->query.=" LEFT JOIN ".$this->tabelle['CA_ANAGRAFICHE_WORKCAR_SOCIETA']->getTabName()." AS t6 ON t2.cod_anagra=t6.cod_anagra";

        $this->query.=" LEFT JOIN ".$this->tabelle['VE_ANAVEI_ANAGRA']->getTabName()." AS t7 ON t2.cod_anagra IN (t7.cod_anagra_util,t7.cod_anagra_intest,t7.cod_anagra_loc)";

        $this->query.=" LEFT JOIN ".$this->tabelle['VE_ANAVEI']->getTabName()." AS t8 ON t7.num_rif_veicolo=t8.num_rif_veicolo";

		//si dà per scontato che sia stato passato almeno un criterio
		//non metto alcun criterio sicuramente TRUE per evitare che venga estratto tutto il mondo in caso di errore
		$this->query.=" WHERE ";

		$and=false;

		if (isset($arr['nominativo']) && $arr['nominativo']!="") {
			if ($and) $this->query.=' AND ';
			$this->query.="t2.des_ragsoc LIKE '%".$arr['nominativo']."%'";
			$and=true;
		}

		if (isset($arr['mail']) && $arr['mail']!="") {
			if ($and) $this->query.=' AND ';
			$this->query.="t2.des_email1 LIKE '%".$arr['mail']."%'";
			$and=true;
		}

        if (isset($arr['telefono']) && $arr['telefono']!="") {
			if ($and) $this->query.=' AND ';
			$this->query.="(t2.sig_tel_res LIKE '%".$arr['telefono']."%' OR t2.sig_tel1_res LIKE '%".$arr['telefono']."%')";
			$and=true;
		}

		if (isset($arr['codice']) && $arr['codice']!="") {
			if ($and) $this->query.=' AND ';
			$this->query.="t2.cod_anagra='".$arr['codice']."'";
			$and=true;
		}

		$this->query.=' ORDER BY t2.des_ragsoc,t2.cod_anagra,t8.mat_targa,progressivo DESC';

		return true;
	}

} 


?>