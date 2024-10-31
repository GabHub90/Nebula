<?php
include_once(DROOT.'/nebula/galileo/infinity/infinity_veicoli.php');

include_once(DROOT.'/nebula/galileo/infinity/tabs/ARTICOLI.php');
include_once(DROOT.'/nebula/galileo/infinity/tabs/LISTINO_BASE.php');

include_once(DROOT.'/nebula/galileo/infinity/tabs/MDM_CLI.php');
include_once(DROOT.'/nebula/galileo/infinity/tabs/TDO_CLI.php');
include_once(DROOT.'/nebula/galileo/infinity/tabs/TDO_PRE.php');
include_once(DROOT.'/nebula/galileo/infinity/tabs/MDM_FOR.php');

include_once(DROOT.'/nebula/galileo/infinity/tabs/TORD_CLI.php');
include_once(DROOT.'/nebula/galileo/infinity/tabs/ORD_CLI.php');
include_once(DROOT.'/nebula/galileo/infinity/tabs/ORD_FOR.php');
include_once(DROOT.'/nebula/galileo/infinity/tabs/TORD_FOR.php');
include_once(DROOT.'/nebula/galileo/infinity/tabs/OFF_RICHIESTE.php');
include_once(DROOT.'/nebula/galileo/infinity/tabs/GIACENZE.php');

include_once(DROOT.'/nebula/galileo/infinity/tabs/UTENTI.php');

include_once(DROOT.'/nebula/galileo/infinity/tabs/MDM_PRE_VEICOLI.php');
include_once(DROOT.'/nebula/galileo/infinity/tabs/MDM_CLI_VEICOLI.php');

class galileoInfinityRicambi extends galileoOps {

    function __construct() {

        $this->tabelle['ARTICOLI']=new infinity_articoli();
		$this->tabelle['LISTINO_BASE']=new infinity_listinobase();
        $this->tabelle['MDM_CLI']=new infinity_mdmcli();
		$this->tabelle['MDM_FOR']=new infinity_mdmfor();
		$this->tabelle['TDO_CLI']=new infinity_tdocli();
		$this->tabelle['TDO_PRE']=new infinity_tdopre();
		$this->tabelle['TORD_CLI']=new infinity_tordcli();
        $this->tabelle['ORD_CLI']=new infinity_ordcli();
		$this->tabelle['ORD_FOR']=new infinity_ordfor();
		$this->tabelle['TORD_FOR']=new infinity_tordfor();
		$this->tabelle['OFF_RICHIESTE']=new infinity_offrichieste();
        $this->tabelle['GIACENZE']=new infinity_giacenze();
		$this->tabelle['ANAGRAFICA']=new infinity_anagrafica();

		$this->tabelle['UTENTI']=new infinity_utenti();

		$this->tabelle['OFF_VEICOLI']=new infinity_offveicoli();

		$this->tabelle['MDM_PRE_VEICOLI']=new infinity_mdmpre_veicoli();
		$this->tabelle['MDM_CLI_VEICOLI']=new infinity_mdmcli_veicoli();

		$this->viste['CLIENTI']="dba.clienti";
		$this->viste['ULTIMO_LISTINO']="dba.vs_gabellini_ultimo_listino";
    }

    function getScarichiUtif($arr) {

		$this->query="SELECT
			CONVERT(varchar(8),c.data_fatt,112) as d_fatt,
			c.num_fatt,
			isnull(
			CASE
                WHEN c.tipo_mov='LO01' OR c.tipo_mov='LO02' OR c.tipo_mov='LO03' OR c.tipo_mov='LO04' THEN 'OOP'
                WHEN c.tipo_mov='LI01' OR c.tipo_mov='LI02' OR c.tipo_mov='LI03' OR c.tipo_mov='LI04' OR c.tipo_mov='LI05' THEN 'OOA'
				WHEN c.tipo_mov='L101' THEN 'OOP'
            END,'') AS cod_movimento,
			b.nomen_intra,
			b.peso,
			ISNULL(CAST(intest.codice_cliente AS char),'') AS cod_anagra_intest,
			ISNULL(intest.ragione_sociale,'') AS ragsoc_intest,
			CASE
				WHEN ISNULL(intest.partita_iva,'')!='' THEN intest.partita_iva
				ELSE
				CASE
					WHEN ISNULL(intest.codice_fiscale,'')!='' THEN intest.codice_fiscale
					ELSE ''
				END
			END AS pivacf,
			a.precodice,
			a.articolo,
			a.descr_articolo,
			a.quantita
		";

		$this->query.=" FROM ".$this->tabelle['MDM_CLI']->getTabName()." AS a";
		$this->query.=" INNER JOIN ".$this->tabelle['ARTICOLI']->getTabName()." AS b ON a.precodice=b.precodice AND a.articolo=b.articolo";
		$this->query.=" INNER JOIN ".$this->tabelle['TDO_CLI']->getTabName()." AS c ON a.anno=c.anno AND a.id_cliente=c.id_cliente AND a.data_doc=c.data_doc AND a.tipo_doc=c.tipo_doc AND a.numero_doc=c.num_doc";
		$this->query.=" LEFT JOIN ".$this->viste['CLIENTI']." AS intest ON CAST(isnull(c.id_cliente,0) AS int)=intest.codice_cliente";

		//$this->query.=" WHERE b.categoria_fiscale='O' AND CONVERT(varchar(8),c.data_fatt,112)>='".$arr['da']."' AND CONVERT(varchar(8),c.data_fatt,112)<='".$arr['a']."'";
		$this->query.=" WHERE isnull(b.nomen_intra,'')!='' AND CONVERT(varchar(8),c.data_fatt,112)>='".$arr['da']."' AND CONVERT(varchar(8),c.data_fatt,112)<='".$arr['a']."'";

		$this->query.=" ORDER BY d_fatt,a.precodice,a.articolo";

		return true;
	}

	function getCodiciUtif($arr) {
		$this->query="SELECT
			'".$arr['da']."' AS inizio,
			'".$arr['a']."' AS fine,
			'".$arr['mag']."' AS magazzino,
			nomen_intra,
			precodice,
			articolo,
			descrizione
			FROM dba.ARTICOLI
			WHERE nomen_intra='".$arr['cod']."'
		";

		return true;
	}

	function getGiacenza($arr) {

		$this->query="SELECT * FROM dba.sp_getdatigiac_bm_mag( '".$arr['mag']."','".$arr['precodice']."','".$arr['articolo']."','".$arr['da']."','".$arr['a']."' )";

		return true;
	}

	function getGiacenza_M($arr) {

		$this->query="SELECT
			a.deposito,
			a.precodice,
			a.articolo,
			a.gm,
			a.famiglia,
			a.descrizione as descr_articolo,
			coalesce(b.rimanenze_iniziali,a.esist_iniz) as rimanenze_iniziali,
			coalesce(b.rimanenze_finali,a.esist_iniz) as rimanenze_finali,
			isnull(b.vendite,0) as vendite,
			isnull(b.acquisti,0) as acquisti,
			a.prezzo_ufficiale AS listino,
			isnull(a.costo_ult,0) AS costo,
			isnull(a.costo_rif,-1) AS costo_rif,
			isnull(a.d_scarico,'') AS d_scarico,
			isnull(a.d_carico,'') AS d_carico,
			a.imp,
			a.ubicazione
		";

		$this->query.=" FROM (
			SELECT
			b.deposito,
			b.precodice,
			b.articolo,
			a.descrizione,
			isnull(a.categoria_sconto,'*') AS gm,
			a.famiglia AS famiglia,
			b.costo_ult,
			c.costo_ult AS costo_rif,
			l.prezzo_ufficiale,
			convert(varchar(8),b.data_ult_scar,112) AS d_scarico,
			convert(varchar(8),b.data_ult_car,112) AS d_carico,
			isnull(b.esist_iniz,0) as esist_iniz,
			isnull(b.qta_imp,0)+isnull(b.qta_imp_off,0)+isnull(b.qta_imp_int,0) AS imp,
			isnull(b.ubicazione,'') as ubicazione
			FROM ".$this->tabelle['GIACENZE']->getTabName()." AS b
			INNER JOIN ".$this->tabelle['ARTICOLI']->getTabName()." as a ON a.precodice=b.precodice AND a.articolo=b.articolo
			LEFT JOIN ".$this->viste['ULTIMO_LISTINO']." AS l ON b.precodice=l.precodice AND b.articolo=l.articolo
			LEFT JOIN  ".$this->tabelle['GIACENZE']->getTabName()." AS c ON b.deposito=c.deposito AND b.precodice=c.precodice AND b.articolo=c.articolo AND c.anno='".$arr['rifCosto']."'
			WHERE b.deposito IN (".$arr['dep'].") AND b.anno='".substr($arr['inizio'],0,4)."'
		) AS a
		outer apply dba.sp_getdatigiac_bm_mag(a.deposito,a.precodice,a.articolo,'".$arr['inizio']."','".$arr['fine']."') AS b
		";
		
		$this->query.=" WHERE coalesce(b.rimanenze_iniziali,a.esist_iniz)!=0 OR coalesce(b.rimanenze_finali,a.esist_iniz)!=0";

		$this->query.=" ORDER BY a.precodice,a.articolo";

		//echo '<div>'.$this->query.'</div>';

		return true;
	}

	function getGiacenzaUtif($arr) {

		$this->query="SELECT
			'".$arr['da']."' AS inizio,
			'".$arr['a']."' AS fine,
			'".$arr['mag']."' AS magazzino,
			a.nomen_intra,
			a.precodice,
			a.articolo,
			a.descrizione as descr_articolo,
			b.rimanenze_iniziali,
			b.rimanenze_finali,
			b.vendite,
			b.acquisti
		";

		$this->query.=" FROM (
			SELECT
			nomen_intra,
			precodice,
			articolo,
			descrizione
			FROM ".$this->tabelle['ARTICOLI']->getTabName()."
			WHERE nomen_intra='".$arr['cod']."'
		) AS a";

		$this->query.=" LEFT JOIN dba.sp_getdatigiac_bm_mag( '".$arr['mag']."',a.precodice,a.articolo,'".$arr['da']."','".$arr['a']."' ) AS b ON a.precodice=b.rs_precodice AND a.articolo=b.rs_articolo";

		return true;
	}

	function getStatistica($arr) {

		$this->query="SELECT
			'".$arr['da']."' AS inizio,
			'".$arr['a']."' AS fine,
			'".$arr['mag']."' AS magazzino,
			a.precodice,
			a.articolo,
			a.descrizione as descr_articolo,
			coalesce(b.rimanenze_iniziali,a.esist_iniz) as rimanenze_iniziali,
			coalesce(b.rimanenze_finali,a.esist_iniz) as rimanenze_finali,
			b.vendite,
			b.acquisti,
			a.imp AS impegnato,
			a.ubicazione
		";

		$this->query.=" FROM (
			SELECT
			a.nomen_intra,
			a.precodice,
			a.articolo,
			a.descrizione,
			b.ubicazione,
			b.esist_iniz,
			isnull(b.qta_imp,0)+isnull(b.qta_imp_off,0)+isnull(b.qta_imp_int,0) AS imp
			FROM ".$this->tabelle['ARTICOLI']->getTabName()." AS a
			LEFT JOIN ".$this->tabelle['GIACENZE']->getTabName()." AS b ON a.precodice=b.precodice AND a.articolo=b.articolo AND b.deposito='".$arr['mag']."' AND b.anno='".substr($arr['a'],0,4)."'
			WHERE a.precodice='".$arr['precodice']."' AND a.articolo LIKE '%".$arr['articolo']."%'
		) AS a";

		$this->query.=" outer apply dba.sp_getdatigiac_bm_mag('".$arr['mag']."',a.precodice,a.articolo,'".$arr['da']."','".$arr['a']."' ) AS b";

		$this->query.=" ORDER BY a.precodice,a.articolo";

		return true;
	}

	function getScarichiCodiciOfficina($arr) {

		$this->query="SELECT
			CONVERT(varchar(8),c.data_fatt,112) as d_fatt,
			c.num_fatt,
			CASE
                WHEN c.tipo_mov='LO01' OR c.tipo_mov='LO02' OR c.tipo_mov='LO03' OR c.tipo_mov='LO04' THEN 'OOP'
                WHEN c.tipo_mov='LI01' OR c.tipo_mov='LI02' OR c.tipo_mov='LI03' OR c.tipo_mov='LI04' OR c.tipo_mov='LI05' THEN 'OOA'
				WHEN c.tipo_mov='L101' THEN 'OOP'
            END AS cod_movimento,
			ISNULL(CAST(intest.codice_cliente AS char),'') AS cod_anagra,
			ISNULL(intest.ragione_sociale,'') AS ragsoc,
			a.precodice,
			a.articolo,
			b.descrizione AS descr_articolo,
			a.quantita
		";

		$this->query.=" FROM ".$this->tabelle['MDM_CLI']->getTabName()." AS a";
		$this->query.=" INNER JOIN ".$this->tabelle['ARTICOLI']->getTabName()." AS b ON a.precodice=b.precodice AND a.articolo=b.articolo";
		$this->query.=" INNER JOIN ".$this->tabelle['TDO_CLI']->getTabName()." AS c ON a.anno=c.anno AND a.id_cliente=c.id_cliente AND a.data_doc=c.data_doc AND a.tipo_doc=c.tipo_doc AND a.numero_doc=c.num_doc";
		$this->query.=" LEFT JOIN ".$this->viste['CLIENTI']." AS intest ON CAST(isnull(c.id_cliente,0) AS int)=intest.codice_cliente";

		$this->query.=" WHERE CONVERT(varchar(8),c.data_fatt,112)>='".$arr['da']."' AND CONVERT(varchar(8),c.data_fatt,112)<='".$arr['a']."' AND a.articolo IN (".$arr['codici'].")";

		$this->query.=" ORDER BY d_fatt,a.articolo";

		return true;
	}

	function getOrdiniOfficinaPren($arr) {

		$this->query="SELECT
			'pren' AS tipo,
			ocli.numero_doc AS rif_ot,
			ute.id_utente,
			convert(varchar(8),ocli.data_doc,112) AS d_ordine, 
			ocli.precodice, ocli.articolo, 
			ocli.descr_articolo, 
			ocli.deposito, 
			ocli.quantita, 
			ocli.qta_dev,
			gia.esist_att AS giacenza,
			gia.qta_imp AS impegnato,
			isnull(gia.qta_imp_off,0) as officina,
			isnull(gia.qta_imp_int,0) as interno,
			convert(varchar(8),pre.data_prenotazione,112) AS d_prenotazione, 
			substring(pre.ora_prenotazione,1,5) AS ora_prenotazione, 
			isnull(pre.td_commessa,'') AS td_commessa, 
			pre.num_commessa, 
			pre.id_documento AS rif, 
			v.id_veicolo, 
			v.targa, 
			v.telaio, 
			vei.descrizione AS des_veicolo,
			pre.id_cliente,
			pre.codice_contatto,
			ISNULL(cli.ragione_sociale,'') AS ragsoc,
			ISNULL(util.rag_soc_1,'') AS util_ragsoc,
			isnull(ocli.id_ordine_for,0) AS id_ordine_for,
			isnull(ofor.quantita,0) AS quantita_ordfor,
			isnull(ofor.qta_eva,0) AS qta_eva_ordfor,
			isnull(ofor.qta_dev,0) AS qta_dev_ordfor,
			ofor.id_ordine AS id_ofor,
			tofor.tipo_doc As tipo_ofor,
			isnull(convert(varchar(8),tofor.data_invio,112),'0') AS d_invio
		";

		$this->query.=" FROM ".$this->tabelle['ORD_CLI']->getTabName()." AS ocli";
		$this->query.=" LEFT JOIN ".$this->tabelle['OFF_RICHIESTE']->getTabName()." AS ric ON ric.id_ordine_cli=ocli.id_ordine";
		$this->query.=" LEFT JOIN ".$this->tabelle['TDO_PRE']->getTabName()." AS pre ON ric.id_prenotazione=pre.id_documento";
		$this->query.=" LEFT JOIN ".$this->tabelle['MDM_PRE_VEICOLI']->getTabName()." AS vei ON pre.anno=vei.anno AND pre.id_cliente=vei.id_cliente AND pre.data_doc=vei.data_doc AND pre.tipo_doc=vei.tipo_doc AND pre.num_doc=vei.numero_doc";
		$this->query.=" LEFT JOIN ".$this->tabelle['OFF_VEICOLI']->getTabName()." AS v ON vei.id_veicolo=v.id_veicolo";
		$this->query.=" LEFT JOIN ".$this->tabelle['TORD_CLI']->getTabName()." AS tocli ON tocli.tipo_doc=ocli.tipo_doc AND tocli.num_doc=ocli.numero_doc AND tocli.data_doc=ocli.data_doc AND tocli.anno=ocli.anno AND tocli.id_cliente=ocli.id_cliente";
		$this->query.=" LEFT JOIN ".$this->tabelle['UTENTI']->getTabName()." AS ute ON tocli.id_utente=ute.id_utenti";
		$this->query.=" LEFT JOIN ".$this->viste['CLIENTI']." AS cli ON pre.id_cliente=cli.codice_cliente";
		$this->query.=" LEFT JOIN ".$this->tabelle['ANAGRAFICA']->getTabName()." AS util ON pre.codice_contatto=util.id_anagrafica";
		$this->query.=" LEFT JOIN ".$this->tabelle['GIACENZE']->getTabName()." AS gia ON ocli.precodice=gia.precodice AND ocli.articolo=gia.articolo AND ocli.deposito=gia.deposito AND ocli.anno=gia.anno";
		$this->query.=" LEFT JOIN ".$this->tabelle['ORD_FOR']->getTabName()." AS ofor ON ocli.id_ordine_for=ofor.id_ordine";
		$this->query.=" LEFT JOIN ".$this->tabelle['TORD_FOR']->getTabName()." AS tofor ON tofor.tipo_doc=ofor.tipo_doc AND tofor.num_doc=ofor.numero_doc AND tofor.anno=ofor.anno";
		
		$this->query.=" WHERE ocli.tipo_doc='OT01' AND ocli.qta_dev>0 and ocli.precodice!='ZZ' AND ric.id_commessa is null AND ric.id_prenotazione is not null AND ocli.tipo_riga='N'";

		$this->query.=" ORDER BY pre.data_prenotazione,pre.ora_prenotazione,ric.id_prenotazione,tocli.num_doc";

		return true;
	}

	function getOrdiniOfficinaComm($arr) {

		$this->query="SELECT
			'comm' AS tipo,
			ocli.numero_doc AS rif_ot,
			ute.id_utente,
			convert(varchar(8),
			ocli.data_doc,112) AS d_ordine, 
			ocli.precodice, ocli.articolo, 
			ocli.descr_articolo, 
			ocli.deposito, 
			ocli.quantita, 
			ocli.qta_dev,
			gia.esist_att AS giacenza,
			gia.qta_imp AS impegnato,
			isnull(gia.qta_imp_off,0) as officina,
			isnull(gia.qta_imp_int,0) as interno,
			convert(varchar(8),com.data_doc,112) AS d_commessa,
			isnull(convert(varchar(8),com.data_fatt,112),'') AS d_fatt,
			com.id_documento AS rif, 
			v.id_veicolo, 
			v.targa, 
			v.telaio, 
			vei.descrizione AS des_veicolo,
			com.id_cliente,
			com.codice_contatto,
			ISNULL(cli.ragione_sociale,'') AS ragsoc,
			ISNULL(util.rag_soc_1,'') AS util_ragsoc,
			isnull(ocli.id_ordine_for,0) AS id_ordine_for,
			isnull(ofor.quantita,0) AS quantita_ordfor,
			isnull(ofor.qta_eva,0) AS qta_eva_ordfor,
			isnull(ofor.qta_dev,0) AS qta_dev_ordfor,
			ofor.id_ordine AS id_ofor,
			tofor.tipo_doc As tipo_ofor,
			isnull(convert(varchar(8),tofor.data_invio,112),'0') AS d_invio
		";

		$this->query.=" FROM ".$this->tabelle['ORD_CLI']->getTabName()." AS ocli";
		$this->query.=" LEFT JOIN ".$this->tabelle['OFF_RICHIESTE']->getTabName()." AS ric ON ric.id_ordine_cli=ocli.id_ordine";
		$this->query.=" LEFT JOIN ".$this->tabelle['TDO_CLI']->getTabName()." AS com ON ric.id_commessa=com.id_documento";
		$this->query.=" LEFT JOIN ".$this->tabelle['MDM_CLI_VEICOLI']->getTabName()." AS vei ON com.anno=vei.anno AND com.id_cliente=vei.id_cliente AND com.data_doc=vei.data_doc AND com.tipo_doc=vei.tipo_doc AND com.num_doc=vei.numero_doc";
		$this->query.=" LEFT JOIN ".$this->tabelle['OFF_VEICOLI']->getTabName()." AS v ON vei.id_veicolo=v.id_veicolo";
		$this->query.=" LEFT JOIN ".$this->tabelle['TORD_CLI']->getTabName()." AS tocli ON tocli.tipo_doc=ocli.tipo_doc AND tocli.num_doc=ocli.numero_doc AND tocli.data_doc=ocli.data_doc AND tocli.anno=ocli.anno AND tocli.id_cliente=ocli.id_cliente";
		$this->query.=" LEFT JOIN ".$this->tabelle['UTENTI']->getTabName()." AS ute ON tocli.id_utente=ute.id_utenti";
		$this->query.=" LEFT JOIN ".$this->viste['CLIENTI']." AS cli ON com.id_cliente=cli.codice_cliente";
		$this->query.=" LEFT JOIN ".$this->tabelle['ANAGRAFICA']->getTabName()." AS util ON com.codice_contatto=util.id_anagrafica";
		$this->query.=" LEFT JOIN ".$this->tabelle['GIACENZE']->getTabName()." AS gia ON ocli.precodice=gia.precodice AND ocli.articolo=gia.articolo AND ocli.deposito=gia.deposito AND ocli.anno=gia.anno";
		$this->query.=" LEFT JOIN ".$this->tabelle['ORD_FOR']->getTabName()." AS ofor ON ocli.id_ordine_for=ofor.id_ordine";
		$this->query.=" LEFT JOIN ".$this->tabelle['TORD_FOR']->getTabName()." AS tofor ON tofor.tipo_doc=ofor.tipo_doc AND tofor.num_doc=ofor.numero_doc AND tofor.anno=ofor.anno";
		
		$this->query.=" WHERE ocli.tipo_doc='OT01' AND ocli.qta_dev>0 and ocli.precodice!='ZZ' AND ric.id_commessa is not null";

		$this->query.=" ORDER BY com.data_doc,ric.id_commessa,tocli.num_doc";

		return true;
	}

	function getOrdiniBanco($arr) {

		$this->query="SELECT
			ocli.numero_doc AS rif_ot,
			ute.id_utente,
			convert(varchar(8),ocli.data_doc,112) AS d_ordine, 
			ocli.precodice, ocli.articolo, 
			ocli.descr_articolo, 
			ocli.deposito, 
			ocli.quantita, 
			ocli.qta_dev,
			gia.esist_att AS giacenza,
			gia.qta_imp AS impegnato,
			isnull(gia.qta_imp_off,0) as officina,
			isnull(gia.qta_imp_int,0) as interno,
			ocli.id_cliente,
			ISNULL(cli.ragione_sociale,'') AS ragsoc,
			isnull(ocli.id_ordine_for,0) AS id_ordine_for,
			isnull(ofor.quantita,0) AS quantita_ordfor,
			isnull(ofor.qta_eva,0) AS qta_eva_ordfor,
			isnull(ofor.qta_dev,0) AS qta_dev_ordfor,
			ofor.id_ordine AS id_ofor,
			tofor.tipo_doc As tipo_ofor,
			isnull(convert(varchar(8),tofor.data_invio,112),'0') AS d_invio,
			isnull(tocli.note_doc,'') AS note
		";

		$this->query.=" FROM ".$this->tabelle['ORD_CLI']->getTabName()." AS ocli";
		$this->query.=" LEFT JOIN ".$this->tabelle['TORD_CLI']->getTabName()." AS tocli ON tocli.tipo_doc=ocli.tipo_doc AND tocli.num_doc=ocli.numero_doc AND tocli.data_doc=ocli.data_doc AND tocli.anno=ocli.anno AND tocli.id_cliente=ocli.id_cliente";
		$this->query.=" LEFT JOIN ".$this->tabelle['UTENTI']->getTabName()." AS ute ON tocli.id_utente=ute.id_utenti";
		$this->query.=" LEFT JOIN ".$this->viste['CLIENTI']." AS cli ON ocli.id_cliente=cli.codice_cliente";
		$this->query.=" LEFT JOIN ".$this->tabelle['GIACENZE']->getTabName()." AS gia ON ocli.precodice=gia.precodice AND ocli.articolo=gia.articolo AND ocli.deposito=gia.deposito AND ocli.anno=gia.anno";
		$this->query.=" LEFT JOIN ".$this->tabelle['ORD_FOR']->getTabName()." AS ofor ON ocli.id_ordine_for=ofor.id_ordine";
		$this->query.=" LEFT JOIN ".$this->tabelle['TORD_FOR']->getTabName()." AS tofor ON tofor.tipo_doc=ofor.tipo_doc AND tofor.num_doc=ofor.numero_doc AND tofor.anno=ofor.anno";
		
		$this->query.=" WHERE ocli.tipo_doc='OC01' AND ocli.qta_dev>0 and ocli.precodice!='ZZ' AND ocli.tipo_riga='N'";

		if (isset($arr['operatore']) && $arr['operatore']!="") {
			$this->query.=" AND ute.id_utente='".$arr['operatore']."'";
		}

		$this->query.=" ORDER BY tocli.num_doc";

		return true;
	}

	function getListeVenditaBanco($arr) {

		$this->query=" SELECT
			mc.numero_doc as rif,
			CONVERT(varchar(8),mc.data_doc,112) AS d_lista,
			mc.id_cliente,
			ISNULL(cli.ragione_sociale,'') AS ragsoc,
			ute.id_utente,
			mc.precodice,
			mc.articolo,
			mc.descr_articolo AS decr,
			mc.quantita,
			mc.deposito,
			gia.esist_att AS giacenza,
			tc.note_doc AS note
		";

		$this->query.=" FROM ".$this->tabelle['MDM_CLI']->getTabName()." AS mc";
		$this->query.=" INNER JOIN ".$this->tabelle['TDO_CLI']->getTabName()." AS tc ON mc.anno=tc.anno AND mc.numero_doc=tc.num_doc AND mc.tipo_doc=tc.tipo_doc AND tc.tipo_doc='".$arr['lista']."' AND tc.td_fatt is null";
		$this->query.=" LEFT JOIN ".$this->tabelle['UTENTI']->getTabName()." AS ute ON mc.id_utente=ute.id_utenti";
		$this->query.=" LEFT JOIN ".$this->viste['CLIENTI']." AS cli ON mc.id_cliente=cli.codice_cliente";
		$this->query.=" LEFT JOIN ".$this->tabelle['GIACENZE']->getTabName()." AS gia ON mc.precodice=gia.precodice AND mc.articolo=gia.articolo AND mc.deposito=gia.deposito AND mc.anno=gia.anno";
		$this->query.=" WHERE mc.tipo_riga='N' AND mc.precodice!='ZZ'";

		if (isset($arr['operatore']) && $arr['operatore']!="") {
			$this->query.=" AND ute.id_utente='".$arr['operatore']."'";
		}

		$this->query.=" ORDER BY mc.data_doc,mc.numero_doc";

		return true;
	}

	function getNolocat($arr) {

		$this->query="SELECT
			g.deposito,
			g.precodice,
			g.articolo,
			a.descrizione AS descr_articolo,
			isnull(l.prezzo_ufficiale,0) AS listino,
			a.categoria_sconto AS gm,
			isnull(g.esist_att,0)-isnull(g.qta_imp_off,0)-isnull(g.qta_imp_int,0) AS giacenza,
			isnull(g.qta_imp,0) as impegnato,
			isnull(g.qta_imp_off,0) as officina,
			isnull(g.qta_imp_int,0) as interno,
			isnull(g.esist_att,0)-isnull(g.qta_imp,0)-isnull(g.qta_imp_off,0)-isnull(g.qta_imp_int,0) AS dispo,
			convert(varchar(8),g.data_ult_car,112) AS d_carico
		";

		$this->query.=" FROM ".$this->tabelle['GIACENZE']->getTabName()." AS g";
		$this->query.=" INNER JOIN ".$this->tabelle['ARTICOLI']->getTabName()." AS a ON a.precodice=g.precodice AND a.articolo=g.articolo";

		$this->query.=" LEFT JOIN ".$this->viste['ULTIMO_LISTINO']." AS l ON g.precodice=l.precodice AND g.articolo=l.articolo";
		
		$this->query.=" WHERE g.anno='".$arr['anno']."' AND g.deposito IN (".$arr['reparto'].") AND isnull(g.esist_att,0)>0 AND isnull(g.ubicazione,'0')='0'";

		$this->query.=" ORDER BY g.deposito,g.precodice,a.categoria_sconto,g.articolo";

		return true;
	}

	function getNegativi($arr) {

		$this->query="SELECT
			g.deposito,
			g.precodice,
			g.articolo,
			a.descrizione AS descr_articolo,
			isnull(l.prezzo_ufficiale,0) AS listino,
			a.categoria_sconto AS gm,
			isnull(g.esist_att,0)-isnull(g.qta_imp_off,0)-isnull(g.qta_imp_int,0) AS giacenza,
			isnull(g.qta_imp,0) as impegnato,
			isnull(g.qta_imp_off,0) as officina,
			isnull(g.qta_imp_int,0) as interno,
			isnull(g.ubicazione,'') as locazione,
			convert(varchar(8),g.data_ult_scar,112) AS d_scarico
		";

		$this->query.=" FROM ".$this->tabelle['GIACENZE']->getTabName()." AS g";
		$this->query.=" INNER JOIN ".$this->tabelle['ARTICOLI']->getTabName()." AS a ON a.precodice=g.precodice AND a.articolo=g.articolo";

		$this->query.=" LEFT JOIN ".$this->viste['ULTIMO_LISTINO']." AS l ON g.precodice=l.precodice AND g.articolo=l.articolo";
		
		$this->query.=" WHERE g.anno='".$arr['anno']."' AND g.deposito IN (".$arr['reparto'].") AND (isnull(g.esist_att,0)-isnull(g.qta_imp_off,0)-isnull(g.qta_imp_int,0))<0";

		$this->query.=" ORDER BY g.deposito,g.precodice,a.categoria_sconto,g.articolo";

		return true;
	}

	function getCarichiGaranzia($arr) {
		//prende tutti i ricambi ordinati in garanzia (LCG1) e li abbina al primo scarico successivo all'arrivo relativo allo stesso telaio
		//prende solo i carichi da una certa data in poi

		$this->query="SELECT
			c.anno,
			c.id_ordine,
			c.deposito,
			c.precodice,
			c.articolo,
			c.descr_articolo,
			c.quantita,
			convert(varchar(8),c.data_doc,112) as d_carico,
			isnull(ofo.telaio,'') AS telaio,
			info.id_documento,
			info.quantita AS qta_scarico,
			info.d_scarico,
			info.td_fatt,
			isnull(info.d_fatt,'') AS d_fatt,
			g.ubicazione,
			ofo.numero_doc AS ord_for,
			isnull(convert(varchar(8),g.data_ult_scar,112),'') AS ult_scar,
			g.esist_att AS giacenza,
			k.id_documento AS pren,
			k.d_pren
		";

		$this->query.=" FROM ".$this->tabelle['MDM_FOR']->getTabName()." AS c";
		$this->query.=" INNER JOIN ".$this->tabelle['ORD_FOR']->getTabName()." AS ofo ON c.anno=ofo.anno AND c.id_ordine=ofo.id_ordine";
		$this->query.=" LEFT JOIN ".$this->tabelle['GIACENZE']->getTabName()." AS g ON g.anno='".substr($arr['a'],0,4)."' AND g.deposito=c.deposito AND g.articolo=c.articolo AND g.precodice=c.precodice";

		//ATTENZIONE!!!!! - i riferimenti alle tabelle sono statici
		$this->query.=" LEFT JOIN (
			SELECT
			b.id_documento,
			a.precodice,
			a.articolo,
			a.deposito,
			a.quantita,
			convert(varchar(8),a.data_inserimento_riga,112) as d_scarico,
			b.td_fatt,
			convert(varchar(8),b.data_fatt,112) as d_fatt,
			vei.telaio
			FROM dba.mdm_cli AS a
			INNER JOIN dba.tdo_cli as b on a.anno=b.anno AND a.numero_doc=b.num_doc AND a.tipo_doc=b.tipo_doc
			INNER JOIN dba.mdm_cli_veicoli as v on v.anno=b.anno AND v.numero_doc=b.num_doc AND v.tipo_doc=b.tipo_doc
			INNER JOIN dba.off_veicoli as vei on v.id_veicolo=vei.id_veicolo
			where a.tipo_riga='N'
		) AS info ON c.precodice=info.precodice AND c.articolo=info.articolo AND c.deposito=info.deposito AND ofo.telaio=info.telaio AND convert(varchar(8),c.data_doc,112)<=info.d_scarico";
		
		//ATTENZIONE!!!!! - i riferimenti alle tabelle sono statici
		$this->query.=" LEFT JOIN (
			SELECT
			vei.telaio,
			isnull(convert(varchar(8),t1.data_prenotazione,112),'') AS d_pren,
			t1.id_documento
			FROM dba.TDO_PRE AS t1
			INNER JOIN dba.MDM_PRE_VEICOLI AS t4 ON t1.anno=t4.anno AND t1.id_cliente=t4.id_cliente AND t1.data_doc=t4.data_doc AND t1.tipo_doc=t4.tipo_doc AND t1.num_doc=t4.numero_doc
			INNER JOIN dba.OFF_VEICOLI AS vei ON t4.id_veicolo=vei.id_veicolo
		) AS k ON ofo.telaio=k.telaio AND k.d_pren=(SELECT
				max(isnull(convert(varchar(8),tt1.data_prenotazione,112),'')) AS d_pren
				FROM dba.TDO_PRE AS tt1
				INNER JOIN dba.MDM_PRE_VEICOLI AS tt4 ON tt1.anno=tt4.anno AND tt1.id_cliente=tt4.id_cliente AND tt1.data_doc=tt4.data_doc AND tt1.tipo_doc=tt4.tipo_doc AND tt1.num_doc=tt4.numero_doc
				INNER JOIN dba.OFF_VEICOLI AS v ON tt4.id_veicolo=v.id_veicolo
				WHERE v.telaio=k.telaio
				group by v.telaio)
		";

		$this->query.=" WHERE c.tipo_doc IN(".$arr['lista'].") AND convert(varchar(8),c.data_doc,112)>='".$arr['da']."' AND convert(varchar(8),c.data_doc,112)<='".$arr['a']."'";

		//$this->query.=" ORDER BY ofo.telaio,convert(varchar(8),c.data_doc,112) DESC,c.precodice,c.articolo";
		$this->query.=" ORDER BY c.data_doc,c.precodice,c.articolo";

		//echo $this->query;

		return true;
	}

	function pneumaticiZT($arr) {

		$this->query="SELECT
			a.deposito AS magazzino,
			a.precodice,
			a.articolo,
			a.descrizione as descr_articolo,
			a.giacenza,
			a.dispo,
			a.listino
		";

		/*if (isset($arr['giac']) && $arr['giac']==1) {
			$this->query.=",b.rimanenze_finali";
		}*/

		$this->query.=" FROM (
			SELECT
			a.deposito,
			a.precodice,
			a.articolo,
			b.descrizione,
			isnull(a.esist_att,0)-isnull(a.qta_imp_off,0)-isnull(a.qta_imp_int,0) AS giacenza,
			isnull(a.qta_imp,0) as impegnato,
			isnull(a.qta_imp_off,0) as officina,
			isnull(a.qta_imp_int,0) as interno,
			isnull(a.esist_att,0)-isnull(a.qta_imp,0)-isnull(a.qta_imp_off,0)-isnull(a.qta_imp_int,0) AS dispo,
			isnull(l.prezzo_ufficiale,0) AS listino,
			CASE
				WHEN isnull(a.esist_att,0)-isnull(a.qta_imp_off,0)-isnull(a.qta_imp_int,0)<0 THEN 0
				ELSE isnull(a.esist_att,0)-isnull(a.qta_imp_off,0)-isnull(a.qta_imp_int,0)
			END AS pos
			FROM ".$this->tabelle['GIACENZE']->getTabName()." AS a
			INNER JOIN ".$this->tabelle['ARTICOLI']->getTabName()." AS b ON a.precodice=b.precodice AND a.articolo=b.articolo
			LEFT JOIN ".$this->viste['ULTIMO_LISTINO']." AS l ON a.precodice=l.precodice AND a.articolo=l.articolo
			WHERE a.precodice='V' AND a.articolo REGEXP '".$arr['reg']."' AND a.anno='".$arr['anno']."' AND a.deposito IN (".$arr['mag'].")
		) AS a";
		
		/*if (isset($arr['giac']) && $arr['giac']==1) {
			$this->query.=",lateral (dba.sp_getdatigiac_bm_mag(a.deposito,a.precodice,a.articolo,'".$arr['da']."','".$arr['a']."' ) ) AS b";
		}*/

		$this->query.=" ORDER BY pos DESC,a.precodice,a.articolo,a.deposito";

		return true;

	}

}


?>