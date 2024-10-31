<?php
include_once(DROOT.'/nebula/galileo/concerto/concerto_anagrafiche.php');
include_once(DROOT.'/nebula/galileo/concerto/concerto_veicoli.php');

include_once(DROOT.'/nebula/galileo/concerto/tabs/TB_OFF_OFFICINE.php');

include_once(DROOT.'/nebula/galileo/concerto/tabs/GN_MOVTES.php');
include_once(DROOT.'/nebula/galileo/concerto/tabs/GN_MOVTES_OFF.php');
include_once(DROOT.'/nebula/galileo/concerto/tabs/GN_MOVTES_DOC.php');
include_once(DROOT.'/nebula/galileo/concerto/tabs/GN_MOVDET.php');

include_once(DROOT.'/nebula/galileo/concerto/tabs/MG_ANAART.php');
include_once(DROOT.'/nebula/galileo/concerto/tabs/MG_RICHIESTE.php');
include_once(DROOT.'/nebula/galileo/concerto/tabs/MG_RICHIESTE_DET.php');
include_once(DROOT.'/nebula/galileo/concerto/tabs/MG_ANAART_SALDI.php');
include_once(DROOT.'/nebula/galileo/concerto/tabs/MG_ORDINI_DET.php');

class galileoConcertoRicambi extends galileoOps {

    function __construct() {

		$this->tabelle['GN_ANAGRAFICHE']=new concerto_gnanagrafiche();
		$this->tabelle['TB_OFF_OFFICINE']=new concerto_tboffofficine();

		$this->tabelle['GN_MOVTES']=new concerto_gnmovtes();
        $this->tabelle['GN_MOVTES_OFF']=new concerto_gnmovtesoff();
        $this->tabelle['GN_MOVTES_DOC']=new concerto_gnmovtesdoc();
        $this->tabelle['GN_MOVDET']=new concerto_gnmovdet();

		$this->tabelle['MG_ANAART']=new concerto_mganaart();
		$this->tabelle['MG_RICHIESTE_DET']=new concerto_mgrichieste_det();
        $this->tabelle['MG_RICHIESTE']=new concerto_mgrichieste();
        $this->tabelle['MG_ANAART_SALDI']=new concerto_mganaart_saldi();
        $this->tabelle['MG_ORDINI_DET']=new concerto_mgordini_det();
		
		$this->viste['GAB_ULTIMO_LISTINO']="[GC_AZI_GABELLINI_prod].[dbo].GAB_ULTIMO_LISTINO";
        
    }

	function getScarichiCodiciOfficina($arr) {

		$this->query="SELECT
			CONVERT(varchar(8),c.dat_documento_i,112) as d_fatt,
			c.num_documento_i AS num_fatt,
			d.cod_movimento,
			coalesce(e.cod_anagra_fattura,coalesce(e.cod_anagra_util,e.cod_anagra_intest)) AS cod_anagra,
			ISNULL(intest.des_ragsoc,'') AS ragsoc,
			md.cod_tipo_articolo AS precodice,
			md.cod_articolo AS articolo,
			art.des_articolo AS descr_articolo,
			md.qta_pezzi as quantita
		";

		$this->query.=" FROM ".$this->tabelle['GN_MOVDET']->getTabName()." AS md";
		$this->query.=" INNER JOIN ".$this->tabelle['GN_MOVTES_DOC']->getTabName()." AS c ON c.num_rif_movimento=md.num_rif_movimento";
		$this->query.=" INNER JOIN ".$this->tabelle['GN_MOVTES']->getTabName()." AS d ON d.num_rif_movimento=md.num_rif_movimento";
		$this->query.=" INNER JOIN ".$this->tabelle['GN_MOVTES_OFF']->getTabName()." AS e ON e.num_rif_movimento=md.num_rif_movimento";
		$this->query.=" INNER JOIN ".$this->tabelle['MG_ANAART']->getTabName()." AS art ON md.cod_tipo_articolo=art.cod_tipo_articolo AND md.cod_articolo=art.cod_articolo";
		$this->query.=" LEFT JOIN ".$this->tabelle['GN_ANAGRAFICHE']->getTabName()." AS intest ON coalesce(e.cod_anagra_fattura,coalesce(e.cod_anagra_util,e.cod_anagra_intest))=intest.cod_anagra";

		$this->query.=" WHERE d.ind_chiuso='S' AND d.cod_reparto='OFF' AND CONVERT(varchar(8),c.dat_documento_i,112)>='".$arr['da']."' AND CONVERT(varchar(8),c.dat_documento_i,112)<='".$arr['a']."' AND md.cod_articolo IN (".$arr['codici'].")";

		$this->query.=" ORDER BY c.dat_documento_i,md.cod_articolo";

		return true;
	}

	function getStatistica($arr) {

		$this->query="
			SELECT
			'".$arr['da']."' AS inizio,
			'".$arr['a']."' AS fine,
			'".$arr['mag']."' AS magazzino,
			a.precodice,
			a.articolo,
			a.descrizione as descr_articolo,
			dbo.fnMG_SALDO_ARTICOLO_MAG (CAST('".$arr['a']."' AS datetime), a.precodice, a.articolo,'GABELLINI','".$arr['mag']."' ) as rimanenze_iniziali,
			b.rimanenze_finali,
			b.vendite,
			b.acquisti,
			a.imp AS impegnato
		";

		$this->query.=" FROM (
			SELECT
			a.cod_tipo_articolo AS precodice,
			a.cod_articolo AS articolo,
			a.des_articolo AS descrizione,
			isnull(b.num_impegnato_officina,0)+isnull(b.num_impegnato_magazzino,0)AS imp
			FROM ".$this->tabelle['MG_ANAART']->getTabName()." AS a
			LEFT JOIN ".$this->tabelle['MG_ANAART_SALDI']->getTabName()." AS b ON a.cod_tipo_articolo=b.cod_tipo_articolo AND a.cod_articolo=b.cod_articolo
			WHERE a.cod_tipo_articolo='".$arr['precodice']."' and a.cod_articolo LIKE '%".$arr['articolo']."%'
		) AS a 
		cross apply
		dbo.fnGAB_MG_SALDO_ARTICOLO_MAG (CAST('".$arr['da']."' AS datetime),CAST('".$arr['a']."' AS datetime), a.precodice, a.articolo,'GABELLINI','".$arr['mag']."' ) as b";

		return true;

	}

	function pneumaticiZT($arr) {

		if (isset($arr['reg'])) {
			$arr['reg']=str_replace('.','_',$arr['reg']);
		}

		$this->query="SELECT
			a.deposito AS magazzino,
			a.precodice,
			a.articolo,
			a.descrizione as descr_articolo,
			a.giacenza,
			a.dispo,
			a.listino
		";

		$this->query.=" FROM (
			SELECT
			a.cod_magazzino AS deposito,
			a.cod_tipo_articolo AS precodice,
			a.cod_articolo AS articolo,
			b.des_articolo AS descrizione,
			isnull(a.num_giacenza_attuale,0) AS giacenza,
			isnull(a.num_giacenza_attuale,0)-isnull(a.num_impegnato_officina,0)-isnull(a.num_impegnato_magazzino,0) AS dispo,
			isnull(l.pre_listino_articolo,0) AS listino,
			CASE
				WHEN isnull(a.num_giacenza_attuale,0)<0 THEN 0
				ELSE isnull(a.num_giacenza_attuale,0)
			END AS pos
			FROM ".$this->tabelle['MG_ANAART_SALDI']->getTabName()." AS a
			INNER JOIN ".$this->tabelle['MG_ANAART']->getTabName()." AS b ON a.cod_tipo_articolo=b.cod_tipo_articolo AND a.cod_articolo=b.cod_articolo
			LEFT JOIN ".$this->viste['GAB_ULTIMO_LISTINO']." AS l ON a.cod_tipo_articolo=l.cod_tipo_articolo AND a.cod_articolo=l.cod_articolo
			WHERE a.cod_tipo_articolo='V' AND a.cod_articolo LIKE '".$arr['reg']."' AND a.cod_magazzino IN (".$arr['mag'].") AND cod_societa='GABELLINI'
		) AS a";

		$this->query.=" ORDER BY pos DESC,a.precodice,a.articolo,a.deposito";

		return true;

	}

	/*function getOrdiniOfficinaPren($arr) {

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
			isnull(convert(varchar(8),tofor.data_invio,112),'0') AS d_invio
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
	*/

}


?>