<?php

include_once(DROOT.'/nebula/galileo/infinity/tabs/ANAGRAFICA.php');
include_once(DROOT.'/nebula/galileo/infinity/tabs/TELEFONO.php');

class galileoInfinityAnagrafiche extends galileoOps {

    function __construct() {

        $this->tabelle['ANAGRAFICA']=new infinity_anagrafica();
        $this->tabelle['TELEFONO']=new infinity_telefono();

        $this->viste['CLIENTI']="dba.clienti";
        
    }

	function anaSelect($arg) {

		if ($arg['tipo']=='util') $this->anaUtil($arg);
		else if ($arg['tipo']=='intest') $this->anaIntest($arg);
		else return false;

		return true;
	}

	function anaIntest($arg) {

        $this->query="SELECT
		    isnull(t2.codice_cliente,'') as cod_anagra,
			'intest' AS tipo_anagrafica,
            isnull(t2.ragione_sociale,'') as ragsoc,
            isnull(t2.codice_fiscale,'') as cfisc,
            isnull(t2.partita_iva,'') as piva,
            isnull(t2.email1,'') as email,
            isnull(t2.localita_nascita,'') as loc_nasc,
            '' as cap_nasc,
            isnull(t2.provincia_nascita,'') as prov_nasc,
            isnull(CONVERT(VARCHAR(8),t2.data_nascita,112),'') as d_nasc,
            isnull(t2.indirizzo,'') as indir_res,
            isnull(t2.localita,'') as loc_res,
            isnull(t2.cap,'') as cap_res,
            isnull(t2.provincia,'') as prov_res,
            isnull(t2.telefono1,'') as tel1,
            isnull(t2.telefono2,'') as tel2,
            '' as des_patente_numero,
            '' as des_patente_rilasciata_da,
            '' as d_patente,
            '' as d_scadenza_patente,
            isnull(t1.cognome,'') as cognome,
            isnull(t1.nome,'') as nome,
            '' as cod_naz_nasc,
            '' as cod_naz_res,
            '' as cod_localita_res,
            '' as cod_localita_nasc,
            isnull(t1.sesso,'') as ind_sesso,
            '' as pec_fe,
            '' as cod_des_fe,
            t1.persona_fisica as pfis,
            t1.id_azienda as fazi,
            '' as fdin,
            t1.ente_pubblico as fepu,
            '' as mail_flag
        ";

		$this->query.=" FROM ".$this->viste['CLIENTI']." AS t2";
		$this->query.=" INNER JOIN ".$this->tabelle['ANAGRAFICA']->getTabName()." AS t1 ON t2.id_anagrafica=t1.id_anagrafica";

		$this->query.=" WHERE t2.codice_cliente='".$arg['rif']."'";
    }

	function anaUtil($arg) {

        $this->query="SELECT
		    isnull(t1.id_anagrafica,'') as cod_anagra,
			'util' AS tipo_anagrafica,
            isnull(t1.rag_soc_1,'') as ragsoc,
            isnull(t1.cod_fiscale,'') as cfisc,
            isnull(t1.par_iva,'') as piva,
            (select top 1 a.num_riferimento from dba.telefono as a INNER JOIN dba.anagrafica as b ON b.id_anagrafica='".$arg['rif']."' AND a.id_anagrafica = b.id_anagrafica WHERE a.tipo IN ('E') and a.usa_per_inviodoc = 'S' order by a.id_telefono desc) as email,
            isnull(t2.localita_nascita,'') as loc_nasc,
            '' as cap_nasc,
            isnull(t2.provincia_nascita,'') as prov_nasc,
            isnull(CONVERT(VARCHAR(8),t2.data_nascita,112),'') as d_nasc,
            isnull(t1.indirizzo,'') as indir_res,
            isnull(t1.localita,'') as loc_res,
            isnull(t1.cap,'') as cap_res,
            isnull(t1.provincia,'') as prov_res,
            (select top 1 a.num_riferimento from dba.telefono as a INNER JOIN dba.anagrafica as b ON b.id_anagrafica='".$arg['rif']."' AND a.id_anagrafica = b.id_anagrafica WHERE a.tipo IN ('T','C') and a.valido= 1 order by a.id_telefono desc) as tel1,
			(select top 1 start at 2 a.num_riferimento from dba.telefono as a INNER JOIN dba.anagrafica as b ON b.id_anagrafica='".$arg['rif']."' AND a.id_anagrafica = b.id_anagrafica WHERE a.tipo IN ('T','C') and a.valido= 1 order by a.id_telefono desc) as tel2,
            '' as des_patente_numero,
            '' as des_patente_rilasciata_da,
            '' as d_patente,
            '' as d_scadenza_patente,
            isnull(t1.cognome,'') as cognome,
            isnull(t1.nome,'') as nome,
            '' as cod_naz_nasc,
            '' as cod_naz_res,
            '' as cod_localita_res,
            '' as cod_localita_nasc,
            isnull(t1.sesso,'') as ind_sesso,
            '' as pec_fe,
            '' as cod_des_fe,
            t1.persona_fisica as pfis,
            t1.id_azienda as fazi,
            '' as fdin,
            t1.ente_pubblico as fepu,
            '' as mail_flag
        ";

		$this->query.=" FROM ".$this->tabelle['ANAGRAFICA']->getTabName()." AS t1";
		$this->query.=" LEFT JOIN ".$this->viste['CLIENTI']." AS t2 ON t2.id_anagrafica=t1.id_anagrafica";

		$this->query.=" WHERE t1.id_anagrafica='".$arg['rif']."'";
    }

    function getLinker($arr) {

        return false;

		/*$this->query=$this->blocco();

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

		return true;*/
	}

    function getLucia($arr) {

        $this->query="SELECT
            a.id_anagrafica,
            a.rag_soc_1,
            t.num_riferimento
        ";

        $this->query.=" FROM ".$this->tabelle['ANAGRAFICA']->getTabName()." as a";
        $this->query.=" LEFT JOIN ".$this->tabelle['TELEFONO']->getTabName()." as t ON a.id_anagrafica=t.id_anagrafica AND t.num_riferimento!='0' AND t.tipo IN ('T','C')";

        $this->query.=" WHERE isnull(t.num_riferimento,'')!=''";

        $this->query.=" ORDER BY t.num_riferimento,a.id_anagrafica";


        return true;
    }

}


?>