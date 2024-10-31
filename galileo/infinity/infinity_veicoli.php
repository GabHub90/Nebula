<?php
include_once(DROOT.'/nebula/galileo/infinity/infinity_anagrafiche.php');

include_once(DROOT.'/nebula/galileo/infinity/tabs/OFF_VEICOLI.php');
include_once(DROOT.'/nebula/galileo/infinity/tabs/OFF_MODELLI.php');
include_once(DROOT.'/nebula/galileo/infinity/tabs/OFF_MARCHE.php');
include_once(DROOT.'/nebula/galileo/infinity/tabs/N_ALIMENTAZIONE.php');
include_once(DROOT.'/nebula/galileo/infinity/tabs/TIPI_VEICOLO_TEMPARIO.php');

include_once(DROOT.'/nebula/galileo/infinity/tabs/N_VEICOLI.php');
include_once(DROOT.'/nebula/galileo/infinity/tabs/VEICOLI_USATI.php');

include_once(DROOT.'/nebula/galileo/infinity/tabs/DETTAGLIO_CONTRATTO.php');
include_once(DROOT.'/nebula/galileo/infinity/tabs/TESTATA_CONTRATTO.php');

include_once(DROOT.'/nebula/galileo/infinity/tabs/MDM_CLI_VEICOLI.php');
include_once(DROOT.'/nebula/galileo/infinity/tabs/TDO_CLI.php');

include_once(DROOT.'/nebula/galileo/infinity/tabs/UBICAZIONI.php');
include_once(DROOT.'/nebula/galileo/infinity/tabs/CAUSALE_TRASPORTO.php');

include_once(DROOT.'/nebula/galileo/infinity/tabs/U_TESTATA_BOLLE_C.php');
include_once(DROOT.'/nebula/galileo/infinity/tabs/U_DETTAGLIO_BOLLE_C.php');
include_once(DROOT.'/nebula/galileo/infinity/tabs/U_TESTATA_BOLLE_I.php');
include_once(DROOT.'/nebula/galileo/infinity/tabs/U_DETTAGLIO_BOLLE_I.php');
include_once(DROOT.'/nebula/galileo/infinity/tabs/U_TESTATA_BOLLE_F.php');
include_once(DROOT.'/nebula/galileo/infinity/tabs/U_DETTAGLIO_BOLLE_F.php');
include_once(DROOT.'/nebula/galileo/infinity/tabs/N_TESTATA_BOLLE_C.php');
include_once(DROOT.'/nebula/galileo/infinity/tabs/N_DETTAGLIO_BOLLE_C.php');
include_once(DROOT.'/nebula/galileo/infinity/tabs/N_TESTATA_BOLLE_I.php');
include_once(DROOT.'/nebula/galileo/infinity/tabs/N_DETTAGLIO_BOLLE_I.php');
include_once(DROOT.'/nebula/galileo/infinity/tabs/N_TESTATA_BOLLE_F.php');
include_once(DROOT.'/nebula/galileo/infinity/tabs/N_DETTAGLIO_BOLLE_F.php');


class galileoInfinityVeicoli extends galileoOps {

    function __construct() {

        $this->tabelle['OFF_VEICOLI']=new infinity_offveicoli();
        $this->tabelle['OFF_MODELLI']=new infinity_offmodelli();
        $this->tabelle['OFF_MARCHE']=new infinity_offmarche();
        $this->tabelle['N_ALIMENTAZIONE']=new infinity_offalimentazione();
        $this->tabelle['TIPI_VEICOLO_TEMPARIO']=new infinity_tipiveicolotempario();
		$this->tabelle['N_VEICOLI']=new infinity_n_veicoli();
		$this->tabelle['VEICOLI_USATI']=new infinity_veicoliusati();
		$this->tabelle['ANAGRAFICA']=new infinity_anagrafica();
		$this->tabelle['TDO_CLI']=new infinity_tdocli();
		$this->tabelle['MDM_CLI_VEICOLI']=new infinity_mdmcli_veicoli();

		$this->tabelle['TESTATA_CONTRATTO']=new infinity_testatacontratto();
        $this->tabelle['DETTAGLIO_CONTRATTO']=new infinity_dettagliocontratto();

		$this->tabelle['UBICAZIONI']=new infinity_ubicazioni();
		$this->tabelle['CAUSALE_TRASPORTO']=new infinity_cautrasp();
		$this->tabelle['U_TESTATA_BOLLE_C']=new infinity_utestata_bollec();
		$this->tabelle['U_TESTATA_BOLLE_F']=new infinity_utestata_bollef();
		$this->tabelle['U_TESTATA_BOLLE_I']=new infinity_utestata_bollei();
		$this->tabelle['U_DETTAGLIO_BOLLE_C']=new infinity_udettaglio_bollec();
		$this->tabelle['U_DETTAGLIO_BOLLE_F']=new infinity_udettaglio_bollef();
		$this->tabelle['U_DETTAGLIO_BOLLE_I']=new infinity_udettaglio_bollei();
		$this->tabelle['N_TESTATA_BOLLE_C']=new infinity_ntestata_bollec();
		$this->tabelle['N_TESTATA_BOLLE_F']=new infinity_ntestata_bollef();
		$this->tabelle['N_TESTATA_BOLLE_I']=new infinity_ntestata_bollei();
		$this->tabelle['N_DETTAGLIO_BOLLE_C']=new infinity_ndettaglio_bollec();
		$this->tabelle['N_DETTAGLIO_BOLLE_F']=new infinity_ndettaglio_bollef();
		$this->tabelle['N_DETTAGLIO_BOLLE_I']=new infinity_ndettaglio_bollei();

		$this->viste['CLIENTI']="dba.clienti";
		$this->viste['VE_ANAVEI_ANAGRA']="dba.vs_gabellini_veanaveianagra";

    }

    function blocco() {

        $txt=" SELECT
			t1.id_veicolo as rif,
			'infinity' as dms,
			isnull(t1.targa,'') as targa,
			isnull(t1.telaio,'') as telaio,
			isnull(t1.cod_marca,'') as cod_marca,
			isnull(t2.descrizione,'') as des_marca,
			isnull(t1.cod_modello,'') as modello,
            CASE
                WHEN t1.cod_modello is null THEN t1.descrizione_aggiuntiva
                ELSE t3.descrizione
            END AS des_veicolo,
			'' AS cod_interno,
			'' AS cod_esterno,
			'' AS des_interno,
			'' AS des_esterno,
			isnull(t1.alimentazione,'') as cod_alim,
			isnull(t1.codice_motore,'') as mat_motore,
			t1.anno_modello,
			isnull(t1.tipo_veicolo_tempario,'') as cod_vw_tipo_veicolo,
			isnull(t6.descrizione,'') as des_vw_tipo_veicolo,
			'' AS cod_po_tipo_veicolo,
			'' AS des_po_tipo_veicolo,
			'' AS cod_ente_venditore,
			isnull(CONVERT(varchar(8),t1.data_immatricolazione,112),'') as d_imm,
			isnull(CONVERT(varchar(8),t1.data_inizio_garanzia,112),'') as d_cons,
			isnull(CONVERT(varchar(8),t1.data_fine_garanzia,112),'') as d_fine_gar,
			isnull(CONVERT(varchar(8),t1.data_ultrev,112),'') as d_rev,
			isnull(t1.note,'') as des_note_off,
			t1.km,
			'' AS cod_infocar,
			'' AS cod_infocar_anno,
			'' AS cod_infocar_mese,
			'' AS cod_marca_infocar
		";

		return $txt;
    }

	function veanaveianagra($id) {

		$txt="SELECT distinct
			a.id_veicolo,
			a.progressivo,
			a.d_rif,
			v.targa,
			v.telaio,
			isnull(cast (a.codice_cliente AS varchar),'') AS codice_cliente,
			isnull(cast(a.codice_contatto AS varchar),'') AS codice_contatto,
			ISNULL(util.rag_soc_1,'') AS util_ragsoc,
			ISNULL(intest.ragione_sociale,'') AS intest_ragsoc
			
			FROM (
				SELECT 
				vei.id_veicolo,
				vei.codice_cliente,
				vei.codice_contatto,
				'1' AS progressivo,
				'0' AS d_rif
				FROM dba.off_veicoli as vei
				WHERE vei.id_veicolo='".$id."'
				union all
				select
				t4.id_veicolo,
				t1.id_cliente as codice_cliente,
				t1.codice_contatto,
				'2' AS progressivo,
				convert(varchar(8),t1.data_doc,112) as d_rif
				from dba.tdo_cli as t1
				inner join dba.mdm_cli_veicoli AS t4 ON t1.anno=t4.anno AND t1.id_cliente=t4.id_cliente AND t1.data_doc=t4.data_doc AND t1.tipo_doc=t4.tipo_doc AND t1.num_doc=t4.numero_doc
				where t4.id_veicolo='".$id."'		
			)as a
			
			LEFT JOIN dba.CLIENTI AS intest ON a.codice_cliente=intest.codice_cliente
			LEFT JOIN dba.ANAGRAFICA AS util ON a.codice_contatto=util.id_anagrafica
			LEFT JOIN dba.OFF_VEICOLI AS v ON a.id_veicolo=v.id_veicolo
		";

		return $txt;
	}

	function rifGDM($arr) {

		$this->query="SELECT
			a.id_veicolo,
			a.progressivo,
			a.d_rif,
			v.targa,
			v.telaio,
			isnull(cast (a.codice_cliente AS varchar),'') AS codice_cliente,
			isnull(cast(a.codice_contatto AS varchar),'') AS codice_contatto,
			ISNULL(util.rag_soc_1,'') AS util_ragsoc,
			ISNULL(intest.ragione_sociale,'') AS intest_ragsoc,
			isnull(dba.gab_fn_telefoni_anagrafica(util.id_anagrafica),'') AS util_tel,
			isnull(dba.gab_fn_telefoni_anagrafica(intest.id_anagrafica),'') AS intest_tel

			FROM (
				SELECT 
				vei.id_veicolo,
				vei.codice_cliente,
				vei.codice_contatto,
				'1' AS progressivo,
				'0' AS d_rif
				FROM dba.off_veicoli as vei
				WHERE vei.id_veicolo=(SELECT id_veicolo FROM dba.off_veicoli WHERE telaio='".$arr['telaio']."')
			)as a

			LEFT JOIN dba.CLIENTI AS intest ON a.codice_cliente=intest.codice_cliente
			LEFT JOIN dba.ANAGRAFICA AS util ON a.codice_contatto=util.id_anagrafica
			LEFT JOIN dba.OFF_VEICOLI AS v ON a.id_veicolo=v.id_veicolo
		";

		return true;
	}

    function veiSelect($arr) {

        $this->query=$this->blocco();
		
        $this->query.=" FROM ".$this->tabelle['OFF_VEICOLI']->getTabName()." AS t1";
				
		$this->query.=" LEFT JOIN ".$this->tabelle['OFF_MARCHE']->getTabName()." AS t2 ON t1.cod_marca=t2.cod_marca";

        $this->query.=" LEFT JOIN ".$this->tabelle['OFF_MODELLI']->getTabName()." AS t3 ON t1.cod_modello=t3.cod_modello AND t1.cod_marca=t3.cod_marca AND t3.livello='4'";

		$this->query.=" LEFT JOIN ".$this->tabelle['TIPI_VEICOLO_TEMPARIO']->getTabName()." AS t6 ON t1.tipo_veicolo_tempario=t6.codice";
				
		$this->query.=" WHERE t1.id_veicolo='".$arr['rif']."' AND t1.provvisorio='0'";

		return true;
    }

    function ttSelect($arr) {

        $this->query=$this->blocco();
		
        $this->query.=" FROM ".$this->tabelle['OFF_VEICOLI']->getTabName()." AS t1";
				
		$this->query.=" LEFT JOIN ".$this->tabelle['OFF_MARCHE']->getTabName()." AS t2 ON t1.cod_marca=t2.cod_marca";

        $this->query.=" LEFT JOIN ".$this->tabelle['OFF_MODELLI']->getTabName()." AS t3 ON t1.cod_modello=t3.cod_modello AND t1.cod_marca=t3.cod_marca AND t3.livello='4'";

		$this->query.=" LEFT JOIN ".$this->tabelle['TIPI_VEICOLO_TEMPARIO']->getTabName()." AS t6 ON t1.tipo_veicolo_tempario=t6.codice";

		if ($arr['targa']!="" || $arr['telaio']!="") {
				
			if (isset($arr['esatto']) && $arr['esatto']==1) {
				$this->query.=" WHERE ".($arr['targa']!=''?"isnull(t1.targa,'')='".$arr['targa']."' ".($arr['telaio']!=""?$arr['operazione']:""):"")." ".($arr['telaio']!=""?"isnull(t1.telaio,'')='".$arr['telaio']."'":"");
			}
			else {
				$this->query.=" WHERE ".($arr['targa']!=''?"t1.targa LIKE '%".$arr['targa']."%' ".($arr['telaio']!=""?$arr['operazione']:""):"")." ".($arr['telaio']!=""?"t1.telaio LIKE '%".$arr['telaio']."%'":"");
			}

			$this->query.=" AND t1.provvisorio='0'";

			if ($this->orderBy!="") $this->query.="ORDER BY ".$this->orderBy;

			return true;

		}
        
        /*if (isset($arr['esatto']) && $arr['esatto']==1) {
		    $this->query.=" WHERE isnull(t1.targa,'')='".$arr['targa']."' ".$arr['operazione']."  isnull(t1.telaio,'')='".$arr['telaio']."' ";
        }
        else {
            $this->query.=" WHERE t1.targa LIKE '%".$arr['targa']."%' ".$arr['operazione']."  t1.telaio LIKE '%".$arr['telaio']."%' ";
        }*/

		return false;
    }

	function getDesModello($arr) {

		$this->query="SELECT
			descrizione
		";

		$this->query.=" FROM ".$this->tabelle['OFF_MODELLI']->getTabName();
		
		$this->query.=" WHERE cod_modello='".$arr['modello']."' AND cod_marca='".$arr['marca']."' AND livello='4'";

		return true;
	}

	function getLinker($arr) {

		if (isset($arr['operatore'])) $op=$arr['operatore'];
		else $op='AND';

		$this->query=$this->blocco();

		$this->query.="
			,t4.progressivo,
			t4.d_rif,
			isnull(CAST(t4.codice_cliente AS varchar),'') cod_anagra_intest,
			isnull(CAST(t4.codice_contatto AS varchar),'') cod_anagra_util,
			'' AS cod_anagra_locat,
			ISNULL(util.rag_soc_1,'') AS ragsoc_util,
            ISNULL(intest.ragione_sociale,'') AS ragsoc_intest,
			'' AS ragsoc_locat,
			ISNULL(cliutil.email1,'') AS mail_util,
			ISNULL(intest.email1,'') AS mail_intest,
			ISNULL(cliutil.telefono1,'') AS tel1_util,
			ISNULL(intest.telefono1,'') AS tel1_intest,
			ISNULL(cliutil.telefono2,'') AS tel2_util,
			ISNULL(intest.telefono2,'') AS tel2_intest
		";

		$this->query.=" FROM ".$this->tabelle['OFF_VEICOLI']->getTabName()." AS t1";
				
		$this->query.=" LEFT JOIN ".$this->tabelle['OFF_MARCHE']->getTabName()." AS t2 ON t1.cod_marca=t2.cod_marca";

        $this->query.=" LEFT JOIN ".$this->tabelle['OFF_MODELLI']->getTabName()." AS t3 ON t1.cod_modello=t3.cod_modello AND t1.cod_marca=t3.cod_marca AND t3.livello='4'";

		/*if (isset($arr['telaio']) && $arr['telaio']!="" && (!isset($arr['targa']) || $arr['targa']=="") && (!isset($arr['anagra']) || $arr['anagra']=="") ) {
			$this->query.=" LEFT JOIN (".$this->veanaveianagra($arr['telaio']).") AS t4 ON t1.id_veicolo=t4.id_veicolo";
		}*/
		if (isset($arr['codice']) && $arr['codice']!="") {
			$this->query.=" LEFT JOIN (".$this->veanaveianagra($arr['codice']).") AS t4 ON t1.id_veicolo=t4.id_veicolo";
		}
		else {
			$this->query.=" LEFT JOIN ".$this->viste['VE_ANAVEI_ANAGRA']." AS t4 ON t1.id_veicolo=t4.id_veicolo";
		}

		$this->query.=" LEFT JOIN ".$this->tabelle['TIPI_VEICOLO_TEMPARIO']->getTabName()." AS t6 ON t1.tipo_veicolo_tempario=t6.codice";

		$this->query.=" LEFT JOIN ".$this->viste['CLIENTI']." AS intest ON t4.codice_cliente=intest.codice_cliente";
        $this->query.=" LEFT JOIN ".$this->tabelle['ANAGRAFICA']->getTabName()." AS util ON t4.codice_contatto=util.id_anagrafica";
		$this->query.=" LEFT JOIN ".$this->viste['CLIENTI']." AS cliutil ON util.id_anagrafica=cliutil.id_anagrafica";

		$this->query.=" WHERE t1.provvisorio='0' AND (";

		$o=false;

		if (isset($arr['targa']) && $arr['targa']!="") {
			if ($o) $this->query.=" ".$op;
			$this->query.=" t1.targa LIKE '%".$arr['targa']."%'";
			$o=true;
		}

		if (isset($arr['telaio']) && $arr['telaio']!="") {
			if ($o) $this->query.=" ".$op;
			$this->query.=" t1.telaio LIKE '%".$arr['telaio']."%'";
			$o=true;
		}

		if (isset($arr['codice']) && $arr['codice']!="") {
			if ($o) $this->query.=" ".$op;
			$this->query.=" t1.id_veicolo='".$arr['codice']."'";
			$o=true;
		}

		if (isset($arr['anagra']) && $arr['anagra']!="") {
			if ($o) $this->query.=" ".$op;
			$this->query.=" (util.rag_soc_1 LIKE '%".$arr['anagra']."%' OR intest.ragione_sociale LIKE '%".$arr['anagra']."%')";
			$o=true;
		}

		$this->query.=') ORDER BY t1.cod_marca,t1.id_veicolo DESC,t4.progressivo,t4.d_rif';

		return true;
	}

	function getComest($arr) {

		if (isset($arr['odl'])) $arr['odl']=(int)$arr['odl'];

		$this->query="SELECT
			'infinity' AS dms,
			lista.odl,
			lista.targa,
			lista.telaio,
			lista.descrizione
		";

		if ($arr['odl'] && $arr['odl']!="")  {
			$this->query.=" FROM (SELECT
				e.id_documento AS odl,
				g.targa,
				g.telaio,
				f.descrizione
				FROM ".$this->tabelle['TDO_CLI']->getTabName()." AS e
				INNER JOIN ".$this->tabelle['MDM_CLI_VEICOLI']->getTabName()." AS f ON e.anno=f.anno AND e.num_doc=f.numero_doc
				INNER JOIN ".$this->tabelle['OFF_VEICOLI']->getTabName()." AS g ON f.id_veicolo=g.id_veicolo
				WHERE e.id_documento='".number_format($arr['odl'],0,'','')."'
			) AS lista";
		}

		//l'esistenza di TXT dovrebbe essere già stata verificata dal chiamante
		else {

			$this->query.=" FROM (
				SELECT
				'0' AS odl,
				a.targa,
				a.telaio,
				b.descrizione
				FROM ".$this->tabelle['OFF_VEICOLI']->getTabName()." AS a
				LEFT JOIN ".$this->tabelle['OFF_MODELLI']->getTabName()." AS b ON a.cod_modello=b.cod_modello
				WHERE a.targa LIKE '%".$arr['txt']."%' OR a.telaio LIKE '%".$arr['txt']."%'
			";

			$this->query.=" UNION ALL SELECT
				'0' AS odl,
				c.targa,
				c.telaio,
				c.descrizione
				FROM ".$this->tabelle['N_VEICOLI']->getTabName()." AS c
				WHERE c.targa LIKE '%".$arr['txt']."%' OR c.telaio LIKE '%".$arr['txt']."%'
			";

			$this->query.=" UNION ALL SELECT
				'0' AS odl,
				d.targa,
				d.telaio,
				d.descrizione
				FROM ".$this->tabelle['VEICOLI_USATI']->getTabName()." AS d
				WHERE d.targa LIKE '%".$arr['txt']."%' OR d.telaio LIKE '%".$arr['txt']."%'
			";

			$this->query.=" ) AS lista";

		}

		$this->query.=" GROUP BY lista.telaio,lista.targa,lista.descrizione,lista.odl";

		//$this->query.=" ORDER BY lista.telaio,lista.odl";

		return true;
	}

	function inventarioVeicoli($arr) {

		if ($arr['tipo']=='u') return $this->inventarioVeicoli_u($arr);
		if ($arr['tipo']=='n') return $this->inventarioVeicoli_n($arr);
	}

	function inventarioVeicoli_u($arr) {

		$this->query="SELECT
			vei.usato as id_veicolo,
			vei.targa,
			vei.telaio,
			convert(varchar(8),vei.data_arrivo,112) AS d_entrata,
			convert(varchar(8),vei.data_consegna,112) AS d_uscita,
			vei.ubicazione,
			ubi.descrizione AS des_ubi,
			isnull(ddd.id_bolla,0) as id_bolla_f,
			convert(varchar(8),bf.data_bolla,112)+':'+substring(convert(varchar(8),bf.data_bolla,114),1,5) AS d_bolla_f,
			bf.id_caus_trasp as cau_f,
			cauf.descrizione as des_tf,
			bf.ubicazione AS ubi_f,
			uf.descrizione AS des_f,
			isnull(ddt.id_bolla,0) as id_bolla_i,
			convert(varchar(8),bi.data_bolla,112)+':'+substring(convert(varchar(8),bi.data_bolla,114),1,5) AS d_bolla_i,
			bi.id_caus_trasp as cau_i,
			caui.descrizione as des_ti,
			bi.ubicazione AS ubi_i,
			ui.descrizione AS des_i,
			isnull(ddc.id_bolla,0) as id_bolla_c,
			convert(varchar(8),bc.data_bolla,112)+':'+substring(convert(varchar(8),bc.data_bolla,114),1,5) AS d_bolla_c,
			bc.id_caus_trasp as cau_c,
			cauc.descrizione as des_tc,
			bc.ubicazione AS ubi_c,
			uc.descrizione AS des_c,
			'".$arr['d']."' AS d_inventario
		";

		$this->query.=" FROM ".$this->tabelle['VEICOLI_USATI']->getTabName()." AS vei";

		$this->query.=" LEFT JOIN ".$this->tabelle['UBICAZIONI']->getTabName()." AS ubi ON ubi.ubicazione=vei.ubicazione";

		$this->query.=" LEFT JOIN (
			SELECT
			a.id_veicolo_u,
			max(a.id_bolla) as id_bolla
			
			FROM ".$this->tabelle['U_DETTAGLIO_BOLLE_F']->getTabName()." as a
			INNER JOIN ".$this->tabelle['U_TESTATA_BOLLE_F']->getTabName()." as b ON a.id_bolla=b.id_bolla AND b.data_bolla<='".$arr['d']."'
			GROUP BY a.id_veicolo_u

		) as ddd ON vei.usato=ddd.id_veicolo_u";


		$this->query.=" LEFT JOIN ".$this->tabelle['U_TESTATA_BOLLE_F']->getTabName()." AS bf ON ddd.id_bolla=bf.id_bolla";
		$this->query.=" LEFT JOIN ".$this->tabelle['UBICAZIONI']->getTabName()." AS uf ON bf.ubicazione=uf.ubicazione";
		$this->query.=" LEFT JOIN ".$this->tabelle['CAUSALE_TRASPORTO']->getTabName()." AS cauf ON cauf.id_caus_trasp=bf.id_caus_trasp";

		$this->query.=" LEFT JOIN (
			SELECT
			a.id_veicolo_u,
			max(a.id_bolla) as id_bolla
			
			FROM ".$this->tabelle['U_DETTAGLIO_BOLLE_I']->getTabName()." as a
			INNER JOIN ".$this->tabelle['U_TESTATA_BOLLE_I']->getTabName()." as b ON a.id_bolla=b.id_bolla AND b.data_bolla<='".$arr['d']."'
			GROUP BY a.id_veicolo_u

		) as ddt ON vei.usato=ddt.id_veicolo_u";

		$this->query.=" LEFT JOIN ".$this->tabelle['U_TESTATA_BOLLE_I']->getTabName()." AS bi ON ddt.id_bolla=bi.id_bolla";
		$this->query.=" LEFT JOIN ".$this->tabelle['UBICAZIONI']->getTabName()." AS ui ON bi.ubicazione=ui.ubicazione";
		$this->query.=" LEFT JOIN ".$this->tabelle['CAUSALE_TRASPORTO']->getTabName()." AS caui ON caui.id_caus_trasp=bi.id_caus_trasp";

		$this->query.=" LEFT JOIN (
			SELECT
			a.id_veicolo_u,
			max(a.id_bolla) as id_bolla
			
			FROM ".$this->tabelle['U_DETTAGLIO_BOLLE_C']->getTabName()." as a
			INNER JOIN ".$this->tabelle['U_TESTATA_BOLLE_C']->getTabName()." as b ON a.id_bolla=b.id_bolla AND b.data_bolla<='".$arr['d']."'
			GROUP BY a.id_veicolo_u

		) as ddc ON vei.usato=ddc.id_veicolo_u";

		$this->query.=" LEFT JOIN ".$this->tabelle['U_TESTATA_BOLLE_C']->getTabName()." AS bc ON ddc.id_bolla=bc.id_bolla";
		$this->query.=" LEFT JOIN ".$this->tabelle['UBICAZIONI']->getTabName()." AS uc ON bc.ubicazione=uc.ubicazione";
		$this->query.=" LEFT JOIN ".$this->tabelle['CAUSALE_TRASPORTO']->getTabName()." AS cauc ON cauc.id_caus_trasp=bc.id_caus_trasp";

		$this->query.=" WHERE isnull(convert(varchar(8),vei.data_consegna,112),'99999999')>'".$arr['d']."' AND vei.data_arrivo<='".$arr['d']."'";

		$this->query.=" order by vei.targa";

		return true;
	}

	function inventarioVeicoli_n($arr) {

		$this->query="SELECT
			vei.id_veicolo as id_veicolo,
			vei.targa,
			vei.telaio,
			convert(varchar(8),vei.data_fattura_a,112) AS d_entrata,
			convert(varchar(8),coalesce(vei.data_uscita,vei.data_inizio_garanzia),112) AS d_uscita,
			vei.ubicazione,
			ubi.descrizione AS des_ubi,
			isnull(ddd.id_bolla,0) as id_bolla_f,
			convert(varchar(8),bf.data_bolla,112)+':'+substring(convert(varchar(8),bf.data_bolla,114),1,5) AS d_bolla_f,
			bf.id_caus_trasp as cau_f,
			cauf.descrizione as des_tf,
			bf.ubicazione AS ubi_f,
			uf.descrizione AS des_f,
			isnull(ddt.id_bolla,0) as id_bolla_i,
			convert(varchar(8),bi.data_bolla,112)+':'+substring(convert(varchar(8),bi.data_bolla,114),1,5) AS d_bolla_i,
			bi.id_caus_trasp as cau_i,
			caui.descrizione as des_ti,
			bi.ubicazione AS ubi_i,
			ui.descrizione AS des_i,
			isnull(ddc.id_bolla,0) as id_bolla_c,
			convert(varchar(8),bc.data_bolla,112)+':'+substring(convert(varchar(8),bc.data_bolla,114),1,5) AS d_bolla_c,
			bc.id_caus_trasp as cau_c,
			cauc.descrizione as des_tc,
			bc.ubicazione AS ubi_c,
			uc.descrizione AS des_c,
			'".$arr['d']."' AS d_inventario
		";

		$this->query.=" FROM ".$this->tabelle['N_VEICOLI']->getTabName()." AS vei";

		$this->query.=" LEFT JOIN ".$this->tabelle['UBICAZIONI']->getTabName()." AS ubi ON ubi.ubicazione=vei.ubicazione";

		$this->query.=" LEFT JOIN (
			SELECT
			a.id_veicolo_n,
			max(a.id_bolla) as id_bolla
			
			FROM ".$this->tabelle['N_DETTAGLIO_BOLLE_F']->getTabName()." as a
			INNER JOIN ".$this->tabelle['N_TESTATA_BOLLE_F']->getTabName()." as b ON a.id_bolla=b.id_bolla AND b.data_bolla<='".$arr['d']."'
			GROUP BY a.id_veicolo_n

		) as ddd ON vei.id_veicolo=ddd.id_veicolo_n";


		$this->query.=" LEFT JOIN ".$this->tabelle['N_TESTATA_BOLLE_F']->getTabName()." AS bf ON ddd.id_bolla=bf.id_bolla";
		$this->query.=" LEFT JOIN ".$this->tabelle['UBICAZIONI']->getTabName()." AS uf ON bf.ubicazione=uf.ubicazione";
		$this->query.=" LEFT JOIN ".$this->tabelle['CAUSALE_TRASPORTO']->getTabName()." AS cauf ON cauf.id_caus_trasp=bf.id_caus_trasp";

		$this->query.=" LEFT JOIN (
			SELECT
			a.id_veicolo_n,
			max(a.id_bolla) as id_bolla
			
			FROM ".$this->tabelle['N_DETTAGLIO_BOLLE_I']->getTabName()." as a
			INNER JOIN ".$this->tabelle['N_TESTATA_BOLLE_I']->getTabName()." as b ON a.id_bolla=b.id_bolla AND b.data_bolla<='".$arr['d']."'
			GROUP BY a.id_veicolo_n

		) as ddt ON vei.id_veicolo=ddt.id_veicolo_n";

		$this->query.=" LEFT JOIN ".$this->tabelle['N_TESTATA_BOLLE_I']->getTabName()." AS bi ON ddt.id_bolla=bi.id_bolla";
		$this->query.=" LEFT JOIN ".$this->tabelle['UBICAZIONI']->getTabName()." AS ui ON bi.ubicazione=ui.ubicazione";
		$this->query.=" LEFT JOIN ".$this->tabelle['CAUSALE_TRASPORTO']->getTabName()." AS caui ON caui.id_caus_trasp=bi.id_caus_trasp";

		$this->query.=" LEFT JOIN (
			SELECT
			a.id_veicolo_n,
			max(a.id_bolla) as id_bolla
			
			FROM ".$this->tabelle['N_DETTAGLIO_BOLLE_C']->getTabName()." as a
			INNER JOIN ".$this->tabelle['U_TESTATA_BOLLE_C']->getTabName()." as b ON a.id_bolla=b.id_bolla AND b.data_bolla<='".$arr['d']."'
			GROUP BY a.id_veicolo_n

		) as ddc ON vei.id_veicolo=ddc.id_veicolo_n";

		$this->query.=" LEFT JOIN ".$this->tabelle['N_TESTATA_BOLLE_C']->getTabName()." AS bc ON ddc.id_bolla=bc.id_bolla";
		$this->query.=" LEFT JOIN ".$this->tabelle['UBICAZIONI']->getTabName()." AS uc ON bc.ubicazione=uc.ubicazione";
		$this->query.=" LEFT JOIN ".$this->tabelle['CAUSALE_TRASPORTO']->getTabName()." AS cauc ON cauc.id_caus_trasp=bc.id_caus_trasp";

		$this->query.=" WHERE isnull(vei.telaio,'')!='' AND isnull(convert(varchar(8),coalesce(vei.data_uscita,vei.data_inizio_garanzia),112),'99999999')>'".$arr['d']."' AND isnull(convert(varchar(8),vei.data_fattura_a,112),'99999999')<='".$arr['d']."'";

		$this->query.=" order by vei.telaio";

		return true;
		
	}

	function scadenzaRevisione($arr) {

		$this->query="SELECT
			vei.usato,
			vei.targa,
			vei.telaio,
			convert(varchar(8),vei.data_arrivo,112) as d_arrivo,
			isnull(convert(varchar(8),vei.data_revisione,112),'') AS d_revisione,
			isnull(convert(varchar(8),vei.data_consegna,112),'') as d_uscita,
			pri.consenso_marketing_dealer AS mkt,
			test.id_cliente AS id_cliente_contratto,
			ISNULL(u_cli.ragione_sociale,'') AS intest_contratto,
			coalesce(dba.fn_get_last_email(u_cli.id_anagrafica,0,0,1),dba.fn_get_last_email(u_cli.id_anagrafica,0,0,2),'') as intest_mail,
			isnull(dba.gab_fn_telefoni_anagrafica(u_cli.id_anagrafica),'') AS intest_tel,
			u_cli.indirizzo,
			u_cli.localita,
			u_cli.cap,
			u_cli.provincia,
			test.status_contratto,
			isnull(vau.usato,0) as du_usato,
			convert(varchar(8),vau.data_arrivo,112) as du_arrivo,
			isnull(convert(varchar(8),vau.data_consegna,112),'') as du_uscita,
			uu_cli.codice_cliente,
			ISNULL(uu_cli.ragione_sociale,'') AS u_contratto,
			coalesce(dba.fn_get_last_email(uu_cli.id_anagrafica,0,0,1),dba.fn_get_last_email(uu_cli.id_anagrafica,0,0,2),'') as u_mail,
			isnull(dba.gab_fn_telefoni_anagrafica(uu_cli.id_anagrafica),'') AS u_tel,
			u_pri.consenso_marketing_dealer AS u_mkt,
			uu_cli.indirizzo as u_indirizzo,
			uu_cli.localita as u_localita,
			uu_cli.cap as u_cap,
			uu_cli.provincia as u_provincia
		";

		$this->query.=" FROM ".$this->tabelle['VEICOLI_USATI']->getTabName()." AS vei";
		$this->query.=" LEFT JOIN ".$this->tabelle['DETTAGLIO_CONTRATTO']->getTabName()." AS cont ON vei.usato=cont.veicolo";
		$this->query.=" LEFT JOIN ".$this->tabelle['TESTATA_CONTRATTO']->getTabName()." AS test ON cont.id_contratto=test.id_contratto";
		$this->query.=" LEFT JOIN ".$this->viste['CLIENTI']." AS u_cli ON test.id_cliente=u_cli.codice_cliente";

		$this->query.=" LEFT JOIN (
			SELECT 
			id_anagrafica,
			max(id_consenso) as consenso
			FROM dba.consenso_privacy
			GROUP BY id_anagrafica
		) as id_pri ON u_cli.id_anagrafica=id_pri.id_anagrafica";

		$this->query.=" LEFT JOIN dba.consenso_privacy as pri ON pri.id_consenso=id_pri.consenso";

		$this->query.=" LEFT JOIN ".$this->tabelle['VEICOLI_USATI']->getTabName()." AS vau ON vei.telaio=vau.telaio AND vau.data_arrivo>vei.data_consegna";
		$this->query.=" LEFT JOIN ".$this->tabelle['DETTAGLIO_CONTRATTO']->getTabName()." AS u_cont ON vau.usato=u_cont.veicolo";
		$this->query.=" LEFT JOIN ".$this->tabelle['TESTATA_CONTRATTO']->getTabName()." AS u_test ON u_cont.id_contratto=u_test.id_contratto AND u_test.status_contratto='C'";
		$this->query.=" LEFT JOIN ".$this->viste['CLIENTI']." AS uu_cli ON u_test.id_cliente=uu_cli.codice_cliente";

		$this->query.="	LEFT JOIN (
			SELECT 
			id_anagrafica,
			max(id_consenso) as consenso
			FROM dba.consenso_privacy
			GROUP BY id_anagrafica
		) as u_id_pri ON uu_cli.id_anagrafica=u_id_pri.id_anagrafica";

		$this->query.=" LEFT JOIN dba.consenso_privacy as U_pri ON u_pri.id_consenso=u_id_pri.consenso";

		$this->query.=" WHERE isnull(convert(varchar(8),vei.data_consegna,112),'')!='' AND test.status_contratto='C' AND isnull(convert(varchar(8),vei.data_revisione,112),'')<='".$arr['fine']."' AND isnull(convert(varchar(8),vei.data_revisione,112),'')>='".$arr['inizio']."' AND vei.data_consegna<vei.data_revisione";
		$this->query.=" ORDER BY vei.telaio,vei.data_arrivo,vei.data_consegna";

		return true;

	}

	function gestioniTelaioUsato($arr) {
		//prende le gestioni dei veicoli usati dopo una certa data ed eventuali contratti 'C'
		
		$this->query="SELECT
			vei.usato,
			vei.targa,
			vei.telaio,
			convert(varchar(8),vei.data_arrivo,112) as d_arrivo,
			isnull(convert(varchar(8),vei.data_consegna,112),'') as d_uscita,
			pri.consenso_marketing_dealer AS mkt_u_cli,
			test.id_cliente AS id_cliente_contratto,
			ISNULL(u_cli.ragione_sociale,'') AS u_cli_ragsoc,
			coalesce(dba.fn_get_last_email(u_cli.id_anagrafica,0,0,1),dba.fn_get_last_email(u_cli.id_anagrafica,0,0,2),'') as u_cli_mail,
			isnull(dba.gab_fn_telefoni_anagrafica(u_cli.id_anagrafica),'') AS u_cli_tel,
			u_cli.indirizzo AS u_cli_indirizzo,
			u_cli.localita AS u_cli_localita,
			u_cli.cap AS u_cli_cap,
			u_cli.provincia AS u_cli_provincia,
			test.status_contratto
		";

		$this->query.=" FROM ".$this->tabelle['VEICOLI_USATI']->getTabName()." AS vei";
		$this->query.=" LEFT JOIN ".$this->tabelle['DETTAGLIO_CONTRATTO']->getTabName()." AS cont ON vei.usato=cont.veicolo";
		$this->query.=" LEFT JOIN ".$this->tabelle['TESTATA_CONTRATTO']->getTabName()." AS test ON cont.id_contratto=test.id_contratto";
		$this->query.=" LEFT JOIN ".$this->viste['CLIENTI']." AS u_cli ON test.id_cliente=u_cli.codice_cliente";

		$this->query.=" LEFT JOIN (
			SELECT 
			id_anagrafica,
			max(id_consenso) as consenso
			FROM dba.consenso_privacy
			GROUP BY id_anagrafica
		) as id_pri ON u_cli.id_anagrafica=id_pri.id_anagrafica";

		$this->query.=" LEFT JOIN dba.consenso_privacy as pri ON pri.id_consenso=id_pri.consenso";

		$this->query.=" WHERE vei.telaio='".$arr['telaio']."' AND test.status_contratto='C'"; 
		
		if (isset($arr['d'])) $this->query.=" AND convert(varchar(8),vei.data_arrivo,112)>'".$arr['d']."'";

		$this->query.=" ORDER BY vei.telaio,vei.data_arrivo,vei.data_consegna";

		return true;
	}

}

?>