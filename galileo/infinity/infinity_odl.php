<?php

include_once(DROOT.'/nebula/galileo/infinity/infinity_veicoli.php');
include_once(DROOT.'/nebula/galileo/infinity/infinity_anagrafiche.php');
include_once(DROOT.'/nebula/galileo/infinity/infinity_ricambi.php');

include_once(DROOT.'/nebula/galileo/infinity/tabs/MDM_CLI.php');
include_once(DROOT.'/nebula/galileo/infinity/tabs/MDM_CLI_INC.php');
include_once(DROOT.'/nebula/galileo/infinity/tabs/MDM_CLI_LAV.php');
include_once(DROOT.'/nebula/galileo/infinity/tabs/MDM_CLI_VEICOLI.php');
include_once(DROOT.'/nebula/galileo/infinity/tabs/MDM_PRE_INC.php');
include_once(DROOT.'/nebula/galileo/infinity/tabs/MDM_PRE_CLI.php');
include_once(DROOT.'/nebula/galileo/infinity/tabs/MDM_PRE_LAV.php');
include_once(DROOT.'/nebula/galileo/infinity/tabs/MDM_PRE_VEICOLI.php');


include_once(DROOT.'/nebula/galileo/infinity/tabs/MDM_CLI_INC_OPERAI.php');
include_once(DROOT.'/nebula/galileo/infinity/tabs/OFF_MARCATURE_INT.php');

include_once(DROOT.'/nebula/galileo/infinity/tabs/O_QUALIFICA.php');
include_once(DROOT.'/nebula/galileo/infinity/tabs/O_OPERAI.php');
include_once(DROOT.'/nebula/galileo/infinity/tabs/O_CARICO.php');
include_once(DROOT.'/nebula/galileo/infinity/tabs/UTENTI.php');
include_once(DROOT.'/nebula/galileo/infinity/tabs/TIPI_DOC.php');

include_once(DROOT.'/nebula/galileo/infinity/tabs/TDO_CLI.php');
include_once(DROOT.'/nebula/galileo/infinity/tabs/TDO_PRE.php');

include_once(DROOT.'/nebula/galileo/infinity/tabs/N_DETTAGLIO_CONTRATTO.php');
include_once(DROOT.'/nebula/galileo/infinity/tabs/N_TESTATA_CONTRATTO.php');
include_once(DROOT.'/nebula/galileo/infinity/tabs/DETTAGLIO_CONTRATTO.php');
include_once(DROOT.'/nebula/galileo/infinity/tabs/TESTATA_CONTRATTO.php');
include_once(DROOT.'/nebula/galileo/infinity/tabs/GRUPPO_AGENTI.php');

class galileoInfinityODL extends galileoOps {

    function __construct() {

        $this->tabelle['MDM_CLI_INC']=new infinity_mdmcli_inc();
        $this->tabelle['MDM_CLI']=new infinity_mdmcli();
        $this->tabelle['MDM_CLI_LAV']=new infinity_mdmcli_lav();
        $this->tabelle['MDM_CLI_VEICOLI']=new infinity_mdmcli_veicoli();
        $this->tabelle['MDM_PRE_INC']=new infinity_mdmpre_inc();
        $this->tabelle['MDM_PRE_CLI']=new infinity_mdmprecli();
        $this->tabelle['MDM_PRE_LAV']=new infinity_mdmpre_lav();
        $this->tabelle['MDM_PRE_VEICOLI']=new infinity_mdmpre_veicoli();
        $this->tabelle['O_QUALIFICA']=new infinity_o_qualifica();
        $this->tabelle['TDO_CLI']=new infinity_tdocli();
        $this->tabelle['TDO_PRE']=new infinity_tdopre();

        $this->tabelle['MDM_CLI_INC_OPERAI']=new infinity_mdmcli_inc_operai();
        $this->tabelle['OFF_MARCATURE_INT']=new infinity_off_marcature_int();        

        $this->tabelle['O_OPERAI']=new infinity_o_operai();
        $this->tabelle['O_CARICO']=new infinity_o_carico();
        $this->tabelle['UTENTI']=new infinity_utenti();
        $this->tabelle['TIPI_DOC']=new infinity_tipidoc();

        $this->tabelle['OFF_VEICOLI']=new infinity_offveicoli();
        $this->tabelle['OFF_MODELLI']=new infinity_offmodelli();
        $this->tabelle['ANAGRAFICA']=new infinity_anagrafica();
        $this->tabelle['OFF_MARCHE']=new infinity_offmarche();
        $this->tabelle['N_VEICOLI']=new infinity_n_veicoli();
        $this->tabelle['N_TESTATA_CONTRATTO']=new infinity_n_testatacontratto();
        $this->tabelle['N_DETTAGLIO_CONTRATTO']=new infinity_n_dettagliocontratto();
        $this->tabelle['VEICOLI_USATI']=new infinity_veicoliusati();
        $this->tabelle['TESTATA_CONTRATTO']=new infinity_testatacontratto();
        $this->tabelle['DETTAGLIO_CONTRATTO']=new infinity_dettagliocontratto();
        $this->tabelle['GRUPPO_AGENTI']=new infinity_gruppoagenti();

        $this->tabelle['TORD_CLI']=new infinity_tordcli();
        $this->tabelle['ORD_CLI']=new infinity_ordcli();
		$this->tabelle['ORD_FOR']=new infinity_ordfor();
		$this->tabelle['OFF_RICHIESTE']=new infinity_offrichieste();
        $this->tabelle['GIACENZE']=new infinity_giacenze();

        $this->viste['CLIENTI']="dba.clienti";
        $this->viste['LISTA_LAMENTATI']="dba.vs_gabellini_lista_lamentati";
        $this->viste['SALDI_CLI']="dba.vs_gabellini_saldi_cli";

        $this->link['veicoli']=new galileoInfinityVeicoli();
        
    }

    function getAcaricoMarcatempo($arg) {

        $this->query="SELECT *";

        $this->query.=" FROM ".$this->tabelle['O_CARICO']->getTabName();

        $this->query.=" WHERE marcatempo='S'";

        return true;
    }

    function getOdlLamentati($arg) {

        if ($arg['lista']=='cli') return $this->getCliLamentati($arg);
        elseif ($arg['lista']=='pre') return $this->getPreLamentati($arg);
    }

    function getCliLamentati($arg) {
        //restituisce i lamentati di un ordine di lavoro (commessa) con l'intestazione dell'ordine stesso

        $this->query="SELECT
            odl.*,
            vei.cod_marca,
            isnull(mar.descrizione,'') as des_marca,
            vei.targa AS mat_targa,
            vei.telaio AS mat_telaio,
            vei.cod_modello AS cod_veicolo,
            CASE
                WHEN vei.cod_modello is null THEN vei.descrizione_aggiuntiva
                ELSE ofmod.descrizione
            END AS des_veicolo,
            vei.codice_motore AS mat_motore,
            ISNULL(util.rag_soc_1,'') AS util_ragsoc,
            ISNULL(intest.ragione_sociale,'') AS intest_ragsoc,
            '' AS pos,
            'N' AS pren
        ";

        //if (isset($arg['timeless'])) {

            if ( (isset($arg['officina']) && ($arg['officina']=='PN' || $arg['officina']=='CN') ) || (isset($arg['timeless']['officina']) && ($arg['timeless']['officina']=='PN' || $arg['timeless']['officina']=='CN') ) ) {
                
                $this->query.="
                    ,isnull(test.numero_contratto,0) AS numero_contratto,
                    convert(varchar(8),test.data_contratto,112) as d_contratto,
                    test.id_cliente AS id_cliente_contratto,
                    ISNULL(n_cli.ragione_sociale,'') AS intest_contratto,
                    test.status_contratto,
                    c.id_veicolo AS id_nuovo,
                    isnull(convert(varchar(8),c.data_uscita,112),'') as d_uscita,
                    isnull(CONVERT(varchar(8),c.data_arrivo,112),'') AS d_arrivo
                ";
            }
            elseif ( (isset($arg['officina']) && ($arg['officina']=='PU' || $arg['officina']=='CU') ) || (isset($arg['timeless']['officina']) && ($arg['timeless']['officina']=='PU' || $arg['timeless']['officina']=='CU') ) ) {
                
                $this->query.="
                    ,isnull(test.numero_contratto,0) AS numero_contratto,
                    convert(varchar(8),test.data_contratto,112) as d_contratto,
                    test.id_cliente AS id_cliente_contratto,
                    ISNULL(u_cli.ragione_sociale,'') AS intest_contratto,
                    test.status_contratto,
                    c.usato AS id_usato,
                    isnull(convert(varchar(8),c.data_consegna,112),'') as d_uscita
                ";
            }
        //}

        $this->query.="
            FROM (
        ";

        $this->query.=" 
                SELECT
                ISNULL(convert(varchar(8),t2.data_iniziolavori,112),'xxxxxxxx')+':'+SUBSTRING( ISNULL (CONVERT(varchar(5),t2.ora_iniziolavori,114),'xx:xx' ),1,5) as dat_inserimento,
                t6.punto_vendita AS cod_officina,
                CASE
                    WHEN t2.tipo_mov='LO01' OR t2.tipo_mov='LO02' OR t2.tipo_mov='LO03' OR t2.tipo_mov='LO04' OR t2.tipo_mov='LO05' OR t2.tipo_mov='LO42' OR t2.tipo_mov='LO44' OR t2.tipo_mov='LO45' THEN 'OOP'
                    WHEN t2.tipo_mov='LI01' OR t2.tipo_mov='LI02' OR t2.tipo_mov='LI03' OR t2.tipo_mov='LI04' OR t2.tipo_mov='LI05' OR t2.tipo_mov='LI42' OR t2.tipo_mov='LI44' OR t2.tipo_mov='LI45' THEN 'OOA'
                    WHEN t2.tipo_mov='LF01' OR t2.tipo_mov='LF02' OR t2.tipo_mov='LF03' OR t2.tipo_mov='LF04' OR t2.tipo_mov='LF42' OR t2.tipo_mov='LF44' THEN 'OOPN'
                END AS cod_movimento,
                t1.codice_carico AS acarico,
                oc.tipo AS tipo_carico,
                '' AS cg,
                t2.id_documento AS rif,
                convert (varchar(23),t2.data_creazione,121) as pratica,
                isnull(convert (varchar(23),pre.data_creazione,121),'0') as pratica_pren,
                t2.ordine_lavoro,
                'N' AS ind_preventivo,
                CASE
                WHEN ISNULL(t2.id_documento,'')!='' THEN
                    CASE
                        WHEN t2.data_fatt IS NULL THEN 'XX'
                        ELSE 'CH'
                    END
                    ELSE 'XX'
                END AS cod_stato_commessa,
                CASE 
                    WHEN t2.data_fatt IS NULL THEN 'N'
                    ELSE 'S'
                END AS ind_chiuso,
                '0' AS picking,
                isnull(sacli.saldo,0) AS saldo,
                t1.id_riga AS lam,
                t1.id_ordine AS rif_lam,
                ISNULL(t1.descr_inconveniente,'') AS des_riga,
                isnull(t1.note_agg,'') as note_d,
                '' as note_p,
                t1.inconveniente AS cod_pacchetto,
                ISNULL( CONVERT(varchar(8),t2.data_prevcons,112),'xxxxxxxx' )+':'+SUBSTRING( ISNULL (CONVERT(varchar(5),t2.ora_prevcons,114),'xx:xx' ),1,5) AS d_ricon,
                ISNULL(convert(varchar(8),t2.data_iniziolavori,112),'xxxxxxxx')+':'+SUBSTRING( ISNULL (CONVERT(varchar(5),t2.ora_iniziolavori,114),'xx:xx' ),1,5) AS d_entrata,
                t1.tempo_stimato AS ore,
                t3.descrizione AS subrep,
                ISNULL( CONVERT(varchar(8),t1.data_inizio,112),'xxxxxxxx' )+':'+SUBSTRING( ISNULL (CONVERT(varchar(5),t1.ora_inizio,114),'xx:xx' ),1,5) AS d_inc,
                t4.id_veicolo AS num_rif_veicolo,
                CAST(isnull(t2.codice_contatto,0) AS int) AS cod_anagra_util,
                CAST(isnull(t2.id_cliente,0) AS int) AS cod_anagra_intest,
                t5.id_utente AS cod_accettatore,
                t1.id_ordine AS id_inconveniente_infinity,
                t1.anno,
                t1.id_cliente,
                t1.data_doc,
                isnull(CONVERT(varchar(8),t1.data_doc,112),'') AS d_documento,
                t1.tipo_doc,
                t1.numero_doc,
                'infinity' as dms,
                isnull(ll.ind_inc_stato,'0') AS ind_inc_stato,
                isnull(ll.inc_marc_chiuse,0) AS inc_marc_chiuse
            ";

                //if (isset($arg['timeless'])) {
                    //$this->query.=",ISNULL( CONVERT(varchar(8),dp.d_ricon_pratica,112),'xxxxxxxx' )+':'+SUBSTRING( ISNULL (CONVERT(varchar(5),dp.d_ricon_pratica,114),'xx:xx' ),1,5) AS d_ricon_pratica";
                    $this->query.=",dp.d_ricon_pratica,dp.d_entrata_pratica,dp.apprip";
                //}
            
            $this->query.="
            
                FROM ".$this->tabelle['MDM_CLI_INC']->getTabName()." AS t1
                INNER JOIN ".$this->tabelle['TDO_CLI']->getTabName()." AS t2 ON t1.anno=t2.anno AND t1.id_cliente=t2.id_cliente AND t1.data_doc=t2.data_doc AND t1.tipo_doc=t2.tipo_doc AND t1.numero_doc=t2.num_doc
                LEFT JOIN ".$this->tabelle['TDO_PRE']->getTabName()." as pre on t2.anno=pre.anno AND t2.tipo_doc=pre.td_commessa AND pre.td_commessa!='XX' AND t2.num_doc=pre.num_commessa
                INNER JOIN ".$this->tabelle['MDM_CLI_VEICOLI']->getTabName()." AS t4 ON t1.anno=t4.anno AND t1.id_cliente=t4.id_cliente AND t1.data_doc=t4.data_doc AND t1.tipo_doc=t4.tipo_doc AND t1.numero_doc=t4.numero_doc
                LEFT JOIN ".$this->tabelle['O_QUALIFICA']->getTabName()." AS t3 ON t1.id_qualifica=t3.id_qualifica
                LEFT JOIN ".$this->tabelle['TIPI_DOC']->getTabName()." AS t6 ON t2.tipo_doc=t6.codice
                LEFT JOIN ".$this->tabelle['O_CARICO']->getTabName()." AS oc ON t1.codice_carico=oc.codice
                LEFT JOIN (
                    SELECT
                    a.matricola,
                    b.id_utente
                    FROM ".$this->tabelle['O_OPERAI']->getTabName()." AS a
                    INNER JOIN ".$this->tabelle['UTENTI']->getTabName()." AS b ON a.id_utenti=b.id_utenti
                )AS t5 ON t2.acc_code=t5.matricola

                LEFT JOIN (
                    SELECT
                    data_creazione,
                    SUM(CASE WHEN ISNULL(id_cliente,0)=30001 OR ISNULL(id_cliente,0)=30002 THEN 1 ELSE 0 END) AS apprip,
                    max(ISNULL( CONVERT(varchar(8),data_prevcons,112),'xxxxxxxx' )+':'+SUBSTRING( ISNULL (CONVERT(varchar(5),ora_prevcons,114),'xx:xx' ),1,5)) as d_ricon_pratica,
                    min(ISNULL( CONVERT(varchar(8),data_iniziolavori,112),'xxxxxxxx' )+':'+SUBSTRING( ISNULL (CONVERT(varchar(5),ora_iniziolavori,114),'xx:xx' ),1,5)) as d_entrata_pratica
                    FROM ".$this->tabelle['TDO_CLI']->getTabName()."
                    WHERE isnull(td_fatt,'')!='XO'
                    GROUP BY data_creazione
                ) AS dp ON dp.data_creazione=t2.data_creazione

                LEFT JOIN ".$this->viste['LISTA_LAMENTATI']." AS ll ON t1.id_riga=ll.inc AND t2.id_documento=ll.num_rif_movimento
                LEFT JOIN ".$this->viste['SALDI_CLI']." AS sacli ON t2.id_documento=sacli.id_commessa
        ";

        if (isset($arg['timeless'])) {

            if (isset($arg['timeless']['pratica'])) {
                //$this->query.=" WHERE  convert (varchar(23),t2.data_creazione,121)='".$arg['timeless']['pratica']."' AND t6.punto_vendita='".$arg['timeless']['officina']."' AND isnull(t2.td_fatt,'')!='XO'";
                $this->query.=" WHERE  convert (varchar(23),t2.data_creazione,121)='".$arg['timeless']['pratica']."' AND isnull(t2.td_fatt,'')!='XO'";
            }
            else {
                //$this->query.=" WHERE ( ISNULL(CONVERT(varchar(8),t2.data_prevcons,112),'xxxxxxxx' )='xxxxxxxx' OR (CONVERT(varchar(8),t2.data_prevcons,112)>='".$arg['timeless']['inizio']."' AND (CONVERT(varchar(8),t2.data_prevcons,112)<='".$arg['timeless']['fine']."') ) ) AND t6.punto_vendita='".$arg['timeless']['officina']."' AND isnull(t2.td_fatt,'')!='XO'";
                //$this->query.=" WHERE ( dp.d_ricon_pratica='xxxxxxxx:xx:xx' OR (substring(dp.d_ricon_pratica,1,8)>='".$arg['timeless']['inizio']."' AND (substring(dp.d_ricon_pratica,1,8)<='".$arg['timeless']['fine']."') ) ) AND t6.punto_vendita='".$arg['timeless']['officina']."' AND isnull(t2.td_fatt,'')!='XO'";
                $this->query.=" WHERE ( dp.d_ricon_pratica='xxxxxxxx:xx:xx' OR (substring(dp.d_ricon_pratica,1,8)>='".$arg['timeless']['inizio']."' AND (substring(dp.d_ricon_pratica,1,8)<='".$arg['timeless']['fine']."') ) ) AND isnull(t2.td_fatt,'')!='XO' AND t6.punto_vendita='".$arg['timeless']['officina']."'";
            }

            if (isset($arg['timeless']['rc']) && $arg['timeless']['rc']!='') $this->query.=" AND t5.id_utente='".$arg['timeless']['rc']."'";

            if (isset($arg['timeless']['tipo'])) {
                if ($arg['timeless']['tipo']=='chiusi')  $this->query.=" AND isnull(t2.data_fatt,'')!='' ";
                if ($arg['timeless']['tipo']=='aperti')  $this->query.=" AND t2.data_fatt IS NULL ";
            }

        }

        else {

            if (isset($arg['inizio']) && isset($arg['fine'])) {
                $this->query.=" WHERE ISNULL(convert(varchar(8),t2.data_iniziolavori,112),'')>='".$arg['inizio']."' AND ISNULL(convert(varchar(8),t2.data_iniziolavori,112),'')<='".$arg['fine']."' AND isnull(t2.td_fatt,'')!='XO'";
            }
            elseif(isset($arg['num_rif_movimento'])) $this->query.=" WHERE t2.id_documento='".$arg['num_rif_movimento']."' AND isnull(t2.td_fatt,'')!='XO'";
            else  $this->query.=" WHERE isnull(t2.td_fatt,'')!='XO'";

            if ($arg['tipo']=='chiusi')  $this->query.=" AND isnull(t2.data_fatt,'')!='' ";
            if ($arg['tipo']=='aperti')  $this->query.=" AND t2.data_fatt IS NULL ";

            if (isset($arg['officina']) && $arg['officina']!='') $this->query.=" AND t6.punto_vendita='".$arg['officina']."'";

            if (isset($arg['rc']) && $arg['rc']!='') $this->query.=" AND t5.id_utente='".$arg['rc']."'";

            if(isset($arg['tipo_carico'])) $this->query.=" AND oc.tipo IN (".$arg['tipo_carico'].")";
        }

        $this->query.="         
            ) AS odl
        ";


        $this->query.=" LEFT JOIN ".$this->tabelle['OFF_VEICOLI']->getTabName()." AS vei ON odl.num_rif_veicolo=vei.id_veicolo";
        $this->query.=" LEFT JOIN ".$this->tabelle['OFF_MODELLI']->getTabName()." AS ofmod ON vei.cod_modello=ofmod.cod_modello AND vei.cod_marca=ofmod.cod_marca AND ofmod.livello='4'";
        $this->query.=" LEFT JOIN ".$this->tabelle['OFF_MARCHE']->getTabName()." AS mar ON vei.cod_marca=mar.cod_marca";
        $this->query.=" LEFT JOIN ".$this->viste['CLIENTI']." AS intest ON odl.cod_anagra_intest=intest.codice_cliente";
        $this->query.=" LEFT JOIN ".$this->tabelle['ANAGRAFICA']->getTabName()." AS util ON odl.cod_anagra_util=util.id_anagrafica";

        ////////////////////////////////////////////////////////////

        if ( (isset($arg['officina']) && ($arg['officina']=='PN' || $arg['officina']=='CN') ) || (isset($arg['timeless']['officina']) && ($arg['timeless']['officina']=='PN' || $arg['timeless']['officina']=='CN') ) ) {

            $this->query.=" LEFT JOIN ".$this->tabelle['N_DETTAGLIO_CONTRATTO']->getTabName()." AS cont ON vei.telaio=cont.telaio";
            $this->query.=" LEFT JOIN ".$this->tabelle['N_VEICOLI']->getTabName()." AS c ON cont.id_veicolo=c.id_veicolo";
            $this->query.=" LEFT JOIN ".$this->tabelle['N_TESTATA_CONTRATTO']->getTabName()." AS test ON cont.id_contratto=test.id_contratto";
            $this->query.=" LEFT JOIN ".$this->viste['CLIENTI']." AS n_cli ON test.id_cliente=n_cli.codice_cliente";
        }
        elseif ( (isset($arg['officina']) && ($arg['officina']=='PU' || $arg['officina']=='CU') ) || (isset($arg['timeless']['officina']) && ($arg['timeless']['officina']=='PU' || $arg['timeless']['officina']=='CU') ) ) {

            /*$this->query.=" LEFT JOIN ".$this->tabelle['DETTAGLIO_CONTRATTO']->getTabName()." AS cont ON vei.id_usato=cont.veicolo";
            $this->query.=" LEFT JOIN ".$this->tabelle['VEICOLI_USATI']->getTabName()." AS c ON cont.veicolo=c.usato";
            $this->query.=" LEFT JOIN ".$this->tabelle['TESTATA_CONTRATTO']->getTabName()." AS test ON cont.id_contratto=test.id_contratto";
            $this->query.=" LEFT JOIN ".$this->viste['CLIENTI']." AS u_cli ON test.id_cliente=u_cli.codice_cliente";*/
            
            $this->query.=" LEFT JOIN ".$this->tabelle['VEICOLI_USATI']->getTabName()." AS c ON vei.targa=c.targa";
            $this->query.=" LEFT JOIN ".$this->tabelle['DETTAGLIO_CONTRATTO']->getTabName()." AS cont ON c.usato=cont.veicolo";
            $this->query.=" LEFT JOIN ".$this->tabelle['TESTATA_CONTRATTO']->getTabName()." AS test ON cont.id_contratto=test.id_contratto";
            $this->query.=" LEFT JOIN ".$this->viste['CLIENTI']." AS u_cli ON test.id_cliente=u_cli.codice_cliente";
        }

        ////////////////////////////////////////////////////////////

        if (isset($arg['timeless'])) {

            if ($arg['timeless']['officina']=='PN' || $arg['timeless']['officina']=='CN') {
                //APPRIP serve per non prendere gli odl che hanno un contratto ma sono aperti SOLO NON INTERNI
                //$this->query.=" WHERE isnull(test.status_contratto,'X')!='X' AND isnull(convert(varchar(8),c.data_uscita,112),'')='' AND odl.apprip>0";
                $this->query.=" WHERE isnull(test.numero_contratto,0)!=0 AND isnull(test.status_contratto,'X')!='X' AND isnull(convert(varchar(8),c.data_uscita,112),'')='' AND odl.apprip>0";

                if (isset($arg['timeless']['search'])) {
                    $tsc=trim($arg['timeless']['search']);
                    if ($tsc!="") {
                        $this->query.=" AND (vei.targa LIKE '%".$tsc."%' OR vei.telaio LIKE '%".$tsc."%' OR util.rag_soc_1 LIKE '%".$tsc."%' OR intest.ragione_sociale LIKE '%".$tsc."%' OR n_cli.ragione_sociale LIKE '%".$tsc."%')";
                    }
                }
            }
            if ($arg['timeless']['officina']=='PU' || $arg['timeless']['officina']=='CU') {
                //$this->query.=" WHERE isnull(test.status_contratto,'X')!='X' AND isnull(convert(varchar(8),c.data_consegna,112),'')='' AND odl.apprip>0";
                $this->query.=" WHERE isnull(test.numero_contratto,0)!=0 AND isnull(test.status_contratto,'X')!='X' AND isnull(convert(varchar(8),c.data_consegna,112),'')='' AND odl.apprip>0";

                if (isset($arg['timeless']['search'])) {
                    $tsc=trim($arg['timeless']['search']);
                    if ($tsc!="") {
                        $this->query.=" AND (vei.targa LIKE '%".$tsc."%' OR vei.telaio LIKE '%".$tsc."%' OR util.rag_soc_1 LIKE '%".$tsc."%' OR intest.ragione_sociale LIKE '%".$tsc."%' OR u_cli.ragione_sociale LIKE '%".$tsc."%')";
                    }
                }
            }
        }
        else {
            $wh="";

            if (isset($arg['officina']) && ($arg['officina']=='PN' || $arg['officina']=='CN') ) $wh=" WHERE (isnull(test.numero_contratto,0)=0 OR isnull(convert(varchar(8),c.data_uscita,112),'')!='')";
            if (isset($arg['officina']) && ($arg['officina']=='PU' || $arg['officina']=='CU') ) $wh=" WHERE (isnull(test.numero_contratto,0)=0 OR isnull(convert(varchar(8),c.data_consegna,112),'')!='')";

            $this->query.=$wh;

            if (isset($arg['search'])) {
                $tsc=trim($arg['search']);
                if ($tsc!="") {
                    if ($wh!="") $this->query.=' AND';
                    else $this->query.=' WHERE';
                    $this->query.=" (vei.targa LIKE '%".$tsc."%' OR vei.telaio LIKE '%".$tsc."%' OR util.rag_soc_1 LIKE '%".$tsc."%' OR intest.ragione_sociale LIKE '%".$tsc."%')";
                }
            }

        }

        $this->query.=" ORDER BY odl.pratica,odl.rif,odl.lam";

        return true;
    }

    function getPreLamentati($arg) {
        //restituisce i lamentati di un ordine di lavoro (commessa) con l'intestazione dell'ordine stesso

        $this->query="SELECT
            odl.*,
            vei.targa AS mat_targa,
            vei.telaio AS mat_telaio,
            vei.cod_modello AS cod_veicolo,
            CASE
                WHEN vei.cod_modello is null THEN vei.descrizione_aggiuntiva
                ELSE ofmod.descrizione
            END AS des_veicolo,
            vei.codice_motore AS mat_motore,
            ISNULL(util.rag_soc_1,'') AS util_ragsoc,
            ISNULL(intest.ragione_sociale,'') AS intest_ragsoc,
            '' AS pos,
            'S' AS pren
        ";

        if ( (isset($arg['officina']) && ($arg['officina']=='PN' || $arg['officina']=='CN') ) || (isset($arg['timeless']['officina']) && ($arg['timeless']['officina']=='PN' || $arg['timeless']['officina']=='CN') ) ) {
            
            $this->query.="
                ,test.numero_contratto,
                convert(varchar(8),test.data_contratto,112) as d_contratto,
                test.id_cliente AS id_cliente_contratto,
                ISNULL(n_cli.ragione_sociale,'') AS intest_contratto,
                test.status_contratto,
                c.id_veicolo AS id_nuovo,
                isnull(convert(varchar(8),c.data_uscita,112),'') as d_uscita
            ";
        }
        elseif ( (isset($arg['officina']) && ($arg['officina']=='PU' || $arg['officina']=='CU') ) || (isset($arg['timeless']['officina']) && ($arg['timeless']['officina']=='PU' || $arg['timeless']['officina']=='CU') ) ) {
            
            $this->query.="
                ,test.numero_contratto,
                convert(varchar(8),test.data_contratto,112) as d_contratto,
                test.id_cliente AS id_cliente_contratto,
                ISNULL(u_cli.ragione_sociale,'') AS intest_contratto,
                test.status_contratto,
                c.usato AS id_usato,
                isnull(convert(varchar(8),c.data_consegna,112),'') as d_uscita
            ";
        }

        $this->query.="

            FROM (
        ";

        $this->query.=" 
                SELECT
                ISNULL(convert(varchar(8),t2.data_creazione,112),'xxxxxxxx')+':'+SUBSTRING( ISNULL (CONVERT(varchar(5),t2.data_creazione,114),'xx:xx' ),1,5) as dat_inserimento,
                t6.punto_vendita AS cod_officina,
                CASE
                    WHEN t2.tipo_mov='PR01' OR t2.tipo_mov='PR02' OR t2.tipo_mov='PR03' THEN 'OOP'
                    WHEN t2.tipo_mov='PR04' THEN 'OOA'
                END AS cod_movimento,
                t1.codice_carico AS acarico,
                '' AS cg,
                t2.id_documento AS rif,
                CASE 
                    WHEN isnull(t2.num_preventivo,'')='' THEN 'N'
                    ELSE 'S'
                END AS ind_preventivo,
                CASE 
                    WHEN t7.data_fatt IS NULL THEN 'N'
                    ELSE 'S'
                END AS ind_chiuso,
                t2.confermato AS picking,
                t1.id_riga AS lam,
                t1.id_ordine AS rif_lam,
                ISNULL(t1.descr_inconveniente,'') AS des_riga,
                isnull(t1.note_agg,'') as note_d,
                '' as note_p,
                t1.inconveniente AS cod_pacchetto,
                ISNULL( CONVERT(varchar(8),t2.data_prenotazione,112),'xxxxxxxx' )+':'+SUBSTRING( ISNULL (CONVERT(varchar(5),t2.ora_prenotazione,114),'xx:xx' ),1,5) AS d_pren,
                ISNULL( CONVERT(varchar(8),t2.data_prevcons,112),'xxxxxxxx' )+':'+SUBSTRING( ISNULL (CONVERT(varchar(5),t2.ora_prevcons,114),'xx:xx' ),1,5) AS d_ricon,
                ISNULL( CONVERT(varchar(8),t2.data_prenotazione,112),'xxxxxxxx' )+':'+SUBSTRING( ISNULL (CONVERT(varchar(5),t2.ora_prenotazione,114),'xx:xx' ),1,5) AS d_entrata_pratica,
                ISNULL( CONVERT(varchar(8),t2.data_prevcons,112),'xxxxxxxx' )+':'+SUBSTRING( ISNULL (CONVERT(varchar(5),t2.ora_prevcons,114),'xx:xx' ),1,5) AS d_ricon_pratica,
                t1.tempo_stimato AS ore,
                t3.descrizione AS subrep,
                ISNULL( CONVERT(varchar(8),t1.data_inizio,112),'xxxxxxxx' )+':'+SUBSTRING( ISNULL (CONVERT(varchar(5),t1.ora_inizio,114),'xx:xx' ),1,5) AS d_inc,
                t4.id_veicolo AS num_rif_veicolo,
                CAST(isnull(t2.codice_contatto,0) AS int) AS cod_anagra_util,
                CAST(isnull(t2.id_cliente,0) AS int) AS cod_anagra_intest,
                t5.id_utente AS cod_accettatore,
                t1.id_ordine AS id_inconveniente_infinity,
                t7.id_documento AS rif_commessa,
                CASE
                    WHEN ISNULL(t7.id_documento,'')!='' THEN
                        CASE
                            WHEN t7.data_fatt IS NULL THEN 'AP'
                            ELSE 'CH'
                        END
                    ELSE 'XX'
                END AS cod_stato_commessa,
                CASE
                    WHEN t2.flag_clienteinsala='1' THEN 'ASP'
                    ELSE ''
                END AS cod_tipo_trasporto,
                t1.tempo_stimato AS ore_isla,
                t1.anno,
                t1.id_cliente,
                t1.data_doc,
                t1.tipo_doc,
                t1.numero_doc,
                '0' AS ind_inc_stato,
                CASE 
                    WHEN ISNULL(t2.id_cliente,0)=30001 OR ISNULL(t2.id_cliente,0)=30002 THEN 1 
                    ELSE 0 
                END AS apprip

                FROM ".$this->tabelle['MDM_PRE_INC']->getTabName()." AS t1
                INNER JOIN ".$this->tabelle['TDO_PRE']->getTabName()." AS t2 ON t1.anno=t2.anno AND t1.id_cliente=t2.id_cliente AND t1.data_doc=t2.data_doc AND t1.tipo_doc=t2.tipo_doc AND t1.numero_doc=t2.num_doc
                INNER JOIN ".$this->tabelle['MDM_PRE_VEICOLI']->getTabName()." AS t4 ON t1.anno=t4.anno AND t1.id_cliente=t4.id_cliente AND t1.data_doc=t4.data_doc AND t1.tipo_doc=t4.tipo_doc AND t1.numero_doc=t4.numero_doc
                LEFT JOIN ".$this->tabelle['TDO_CLI']->getTabName()." AS t7 ON t7.tipo_doc=t2.td_commessa AND t7.data_doc=t2.data_commessa AND t7.num_doc=t2.num_commessa
                LEFT JOIN ".$this->tabelle['O_QUALIFICA']->getTabName()." AS t3 ON t1.id_qualifica=t3.id_qualifica
                LEFT JOIN  ".$this->tabelle['TIPI_DOC']->getTabName()." AS t6 ON t2.tipo_doc=t6.codice
                LEFT JOIN (
                    SELECT
                    a.matricola,
                    b.id_utente
                    FROM ".$this->tabelle['O_OPERAI']->getTabName()." AS a
                    INNER JOIN ".$this->tabelle['UTENTI']->getTabName()." AS b ON a.id_utenti=b.id_utenti
                ) AS t5 ON t2.accettatore_prenotazione=t5.matricola

                WHERE t2.id_documento='".$arg['num_rif_movimento']."'

            ) AS odl
        ";


        $this->query.=" LEFT JOIN ".$this->tabelle['OFF_VEICOLI']->getTabName()." AS vei ON odl.num_rif_veicolo=vei.id_veicolo";
        $this->query.=" LEFT JOIN ".$this->tabelle['OFF_MODELLI']->getTabName()." AS ofmod ON vei.cod_modello=ofmod.cod_modello AND vei.cod_marca=ofmod.cod_marca AND ofmod.livello='4'";
        $this->query.=" LEFT JOIN ".$this->viste['CLIENTI']." AS intest ON odl.cod_anagra_intest=intest.codice_cliente";
        $this->query.=" LEFT JOIN ".$this->tabelle['ANAGRAFICA']->getTabName()." AS util ON odl.cod_anagra_util=util.id_anagrafica";

        ////////////////////////////////////////////////////////////

        if ( (isset($arg['officina']) && ($arg['officina']=='PN' || $arg['officina']=='CN') ) || (isset($arg['timeless']['officina']) && ($arg['timeless']['officina']=='PN' || $arg['timeless']['officina']=='CN') ) ) {

            $this->query.=" LEFT JOIN ".$this->tabelle['N_DETTAGLIO_CONTRATTO']->getTabName()." AS cont ON vei.telaio=cont.telaio";
            $this->query.=" LEFT JOIN ".$this->tabelle['N_VEICOLI']->getTabName()." AS c ON cont.id_veicolo=c.id_veicolo";
            $this->query.=" LEFT JOIN ".$this->tabelle['N_TESTATA_CONTRATTO']->getTabName()." AS test ON cont.id_contratto=test.id_contratto";
            $this->query.=" LEFT JOIN ".$this->viste['CLIENTI']." AS n_cli ON test.id_cliente=n_cli.codice_cliente";
        }
        elseif ( (isset($arg['officina']) && ($arg['officina']=='PU' || $arg['officina']=='CU') ) || (isset($arg['timeless']['officina']) && ($arg['timeless']['officina']=='PU' || $arg['timeless']['officina']=='CU') ) ) {

            $this->query.=" LEFT JOIN ".$this->tabelle['DETTAGLIO_CONTRATTO']->getTabName()." AS cont ON vei.id_usato=cont.veicolo";
            $this->query.=" LEFT JOIN ".$this->tabelle['VEICOLI_USATI']->getTabName()." AS c ON cont.veicolo=c.usato";
            $this->query.=" LEFT JOIN ".$this->tabelle['TESTATA_CONTRATTO']->getTabName()." AS test ON cont.id_contratto=test.id_contratto";
            $this->query.=" LEFT JOIN ".$this->viste['CLIENTI']." AS u_cli ON test.id_cliente=u_cli.codice_cliente";
        }

        ////////////////////////////////////////////////////////////

        /*if (isset($arg['timeless'])) {

            if ($arg['timeless']['officina']=='PN') {
                //$this->query.=" WHERE isnull(test.status_contratto,'X')!='X' AND isnull(convert(varchar(8),c.data_uscita,112),'')='' AND odl.apprip>0";
                //$this->query.=" WHERE isnull(test.status_contratto,'X')!='X' AND isnull(convert(varchar(8),c.data_uscita,112),'')=''";
                $this->query.=" WHERE isnull(test.numero_contratto,0)!=0 AND isnull(test.status_contratto,'X')!='X' AND isnull(convert(varchar(8),c.data_uscita,112),'')='' AND odl.apprip>0";
            }
            if ($arg['timeless']['officina']=='PU') {
                //$this->query.=" WHERE isnull(test.status_contratto,'X')!='X' AND isnull(convert(varchar(8),c.data_consegna,112),'')='' AND odl.apprip>0";
                //$this->query.=" WHERE isnull(test.status_contratto,'X')!='X' AND isnull(convert(varchar(8),c.data_consegna,112),'')=''";
                $this->query.=" WHERE isnull(test.numero_contratto,0)!=0 AND isnull(test.status_contratto,'X')!='X' AND isnull(convert(varchar(8),c.data_consegna,112),'')='' AND odl.apprip>0";
            }
        }
        else {
            if (isset($arg['officina']) && $arg['officina']=='PN') $this->query.=" WHERE isnull(test.numero_contratto,0)=0 OR isnull(convert(varchar(8),c.data_uscita,112),'')!=''";
            if (isset($arg['officina']) && $arg['officina']=='PU') $this->query.=" WHERE isnull(test.numero_contratto,0)=0 OR isnull(convert(varchar(8),c.data_consegna,112),'')!=''";
        }*/


        $this->query.=" ORDER BY odl.rif,odl.lam";

        return true;
    }

    function getCliHeadBudget($arg) {

        $this->query="SELECT
            t1.id_documento AS rif,
            t6.punto_vendita AS cod_officina,
            CASE
                WHEN t1.tipo_mov='LO01' OR t1.tipo_mov='LO02' OR t1.tipo_mov='LO03' OR t1.tipo_mov='LO04' OR t1.tipo_mov='LO05' OR t1.tipo_mov='LO42' OR t1.tipo_mov='LO44' OR t1.tipo_mov='LO45' THEN 'OOP'
                WHEN t1.tipo_mov='LI01' OR t1.tipo_mov='LI02' OR t1.tipo_mov='LI03' OR t1.tipo_mov='LI04' OR t1.tipo_mov='LI05' OR t1.tipo_mov='LI42' OR t1.tipo_mov='LI44' OR t1.tipo_mov='LI45' THEN 'OOA'
                WHEN t1.tipo_mov='LF01' OR t1.tipo_mov='LF02' OR t1.tipo_mov='LF03' OR t1.tipo_mov='LF04' OR t1.tipo_mov='LF42' OR t1.tipo_mov='LF44' THEN 'OOPN'
            END AS cod_movimento
        ";

        $this->query.=" FROM ".$this->tabelle['TDO_CLI']->getTabName()." AS t1";
        $this->query.=" LEFT JOIN ".$this->tabelle['TIPI_DOC']->getTabName()." AS t6 ON t1.tipo_doc=t6.codice";

        $this->query.=" WHERE t1.id_documento IN (".$arg['elenco'].")";

        //echo $this->query;

        return true;
    }

    function getOdlHead($arg) {

        if ($arg['lista']=='cli') return $this->getCliHead($arg);
        elseif ($arg['lista']=='pre') return $this->getPreHead($arg);
    }

    function getCliHead($arg) {

        $this->query="SELECT
            t1.id_documento AS rif,
            t6.punto_vendita AS cod_officina,
            t6.punto_vendita AS cod_officina_prenotazione,
            '' AS cod_stato_commessa,
            CASE
                WHEN t1.tipo_mov='LO01' OR t1.tipo_mov='LO02' OR t1.tipo_mov='LO03' OR t1.tipo_mov='LO04' OR t1.tipo_mov='LO05' OR t1.tipo_mov='LO42' OR t1.tipo_mov='LO44' OR t1.tipo_mov='LO45' THEN 'OOP'
                WHEN t1.tipo_mov='LI01' OR t1.tipo_mov='LI02' OR t1.tipo_mov='LI03' OR t1.tipo_mov='LI04' OR t1.tipo_mov='LI05' OR t1.tipo_mov='LI42' OR t1.tipo_mov='LI44' OR t1.tipo_mov='LI45' THEN 'OOA'
                WHEN t1.tipo_mov='LF01' OR t1.tipo_mov='LF02' OR t1.tipo_mov='LF03' OR t1.tipo_mov='LF04' OR t1.tipo_mov='LF42' OR t1.tipo_mov='LF44' THEN 'OOPN'
            END AS cod_movimento,
            t1.anno,
            t1.id_cliente,
            t1.data_doc,
            t1.tipo_doc,
            t1.num_doc,
            t1.num_doc AS num_commessa,
            'N' AS ind_preventivo,
            CASE
                WHEN t1.flag_clienteinsala='1' THEN 'ASP'
                ELSE ''
            END AS cod_tipo_trasporto,
            CASE 
                WHEN t1.data_fatt IS NULL THEN 'N'
                ELSE 'S'
            END AS ind_chiuso,
            ISNULL(convert(varchar(8),t1.data_creazione,112),'xxxxxxxx') AS d_inserimento,
            t1.id_utente AS cod_utente_inserimento,
            ISNULL( CONVERT(varchar(8),t1.data_prevcons,112),'xxxxxxxx' )+':'+SUBSTRING( ISNULL (CONVERT(varchar(5),t1.ora_prevcons,114),'xx:xx' ),1,5) AS d_ricon,
            ISNULL(convert(varchar(8),t1.data_iniziolavori,112),'xxxxxxxx')+':'+SUBSTRING( ISNULL (CONVERT(varchar(5),t1.ora_iniziolavori,114),'xx:xx' ),1,5) AS d_entrata,
            ISNULL(convert(varchar(8),t1.data_finelavori,112),'xxxxxxxx')+':'+SUBSTRING( ISNULL (CONVERT(varchar(5),t1.ora_finelavori,114),'xx:xx' ),1,5) AS d_fine, 
            t4.id_veicolo AS num_rif_veicolo,
            0 AS num_rif_veicolo_progressivo,
            vei.telaio,
            vei.targa,
            vei.cod_marca,
            CASE
                WHEN vei.cod_modello is null THEN vei.descrizione_aggiuntiva
                ELSE ofmod.descrizione
            END AS des_veicolo,
            m.descrizione as des_marca,
            isnull(CAST(util.id_anagrafica AS char),'') AS cod_anagra_util,
            isnull(CAST(intest.codice_cliente AS char),'') AS cod_anagra_intest,
            ISNULL(util.rag_soc_1,'') AS util_ragsoc,
            ISNULL(intest.ragione_sociale,'') AS intest_ragsoc,
            '' AS cod_anagra_locat,
            '' AS cod_anagra_fatt,
            t5.id_utente AS cod_accettatore,
            isnull(t4.km,0) AS km,
            'infinity' AS dms,
            convert (varchar(23),t1.data_creazione,121) as pratica,
            'N' AS pren,
            CASE
                WHEN t6.punto_vendita='PN' THEN ISNULL(n_cli.ragione_sociale,'')
                WHEN t6.punto_vendita='CN' THEN ISNULL(n_cli.ragione_sociale,'')
                WHEN t6.punto_vendita='PU' THEN ISNULL(u_cli.ragione_sociale,'')
                WHEN t6.punto_vendita='CU' THEN ISNULL(u_cli.ragione_sociale,'')
            END AS intest_contratto,
            CASE
                WHEN isnull(convert(varchar(8),cu.data_consegna,112),'') != '' THEN convert(varchar(8),cu.data_consegna,112)
                WHEN isnull(convert(varchar(8),cn.data_uscita,112),'') != '' THEN convert(varchar(8),cn.data_uscita,112)
                ELSE ''
            END d_uscita
        ";

        $this->query.=" FROM ".$this->tabelle['TDO_CLI']->getTabName()." AS t1";

        $this->query.=" LEFT JOIN ".$this->tabelle['TIPI_DOC']->getTabName()." AS t6 ON t1.tipo_doc=t6.codice";
        $this->query.=" INNER JOIN ".$this->tabelle['MDM_CLI_VEICOLI']->getTabName()." AS t4 ON t1.anno=t4.anno AND t1.id_cliente=t4.id_cliente AND t1.data_doc=t4.data_doc AND t1.tipo_doc=t4.tipo_doc AND t1.num_doc=t4.numero_doc";
        $this->query.=" LEFT JOIN ".$this->tabelle['OFF_VEICOLI']->getTabName()." AS vei ON t4.id_veicolo=vei.id_veicolo";
        $this->query.=" LEFT JOIN ".$this->tabelle['OFF_MARCHE']->getTabName()." AS m ON vei.cod_marca=m.cod_marca";
        $this->query.=" LEFT JOIN ".$this->tabelle['OFF_MODELLI']->getTabName()." AS ofmod ON vei.cod_modello=ofmod.cod_modello AND vei.cod_marca=ofmod.cod_marca AND ofmod.livello='4'";
        $this->query.=" LEFT JOIN ".$this->viste['CLIENTI']." AS intest ON CAST(isnull(t1.id_cliente,0) AS int)=intest.codice_cliente";
        $this->query.=" LEFT JOIN ".$this->tabelle['ANAGRAFICA']->getTabName()." AS util ON CAST(isnull(t1.codice_contatto,0) AS int)=util.id_anagrafica";
        $this->query.="
            LEFT JOIN (
                SELECT
                a.matricola,
                b.id_utente
                FROM ".$this->tabelle['O_OPERAI']->getTabName()." AS a
                INNER JOIN ".$this->tabelle['UTENTI']->getTabName()." AS b ON a.id_utenti=b.id_utenti
            ) AS t5 ON t1.acc_code=t5.matricola
        ";

        $this->query.=" LEFT JOIN ".$this->tabelle['N_DETTAGLIO_CONTRATTO']->getTabName()." AS n_cont ON vei.telaio=n_cont.telaio";
        $this->query.=" LEFT JOIN ".$this->tabelle['N_VEICOLI']->getTabName()." AS cn ON n_cont.id_veicolo=cn.id_veicolo";
        $this->query.=" LEFT JOIN ".$this->tabelle['N_TESTATA_CONTRATTO']->getTabName()." AS n_test ON n_cont.id_contratto=n_test.id_contratto";
        $this->query.=" LEFT JOIN ".$this->viste['CLIENTI']." AS n_cli ON n_test.id_cliente=n_cli.codice_cliente";

        $this->query.=" LEFT JOIN ".$this->tabelle['DETTAGLIO_CONTRATTO']->getTabName()." AS u_cont ON vei.id_usato=u_cont.veicolo";
        $this->query.=" LEFT JOIN ".$this->tabelle['VEICOLI_USATI']->getTabName()." AS cu ON u_cont.veicolo=cu.usato";
        $this->query.=" LEFT JOIN ".$this->tabelle['TESTATA_CONTRATTO']->getTabName()." AS u_test ON u_cont.id_contratto=u_test.id_contratto";
        $this->query.=" LEFT JOIN ".$this->viste['CLIENTI']." AS u_cli ON u_test.id_cliente=u_cli.codice_cliente";

        $this->query.=" WHERE t1.id_documento='".$arg['rif']."'";

        /*if (isset($arg['d_uscita'])) {
            $this->query.=" AND (
                CASE
                    WHEN isnull(convert(varchar(8),cu.data_consegna,112),'') != '' THEN convert(varchar(8),cu.data_consegna,112)
                    WHEN isnull(convert(varchar(8),cn.data_uscita,112),'') != '' THEN convert(varchar(8),cn.data_uscita,112)
                    ELSE ''
                END = ''
            )";
                
        }*/

        return true;
    }

    function getPreHead($arg) {

        $this->query="SELECT
            t1.id_documento AS rif,
            t6.punto_vendita AS cod_officina,
            t6.punto_vendita AS cod_officina_prenotazione,
            CASE
                WHEN ISNULL(t7.id_documento,'')!='' THEN
                    CASE
                        WHEN t7.data_fatt IS NULL THEN 'AP'
                        ELSE 'CH'
                    END
                ELSE 'XX'
            END AS cod_stato_commessa,
            CASE
                WHEN t1.tipo_mov='PR01' OR t1.tipo_mov='PR02' OR t1.tipo_mov='PR03' THEN 'OOP'
                WHEN t1.tipo_mov='PR04' THEN 'OOA'
            END AS cod_movimento,
            t1.anno,
            t1.id_cliente,
            t1.data_doc,
            t1.tipo_doc,
            t1.num_doc,
            t1.num_doc AS num_commessa,
            CASE 
                WHEN isnull(t1.num_preventivo,'')='' THEN 'N'
                ELSE 'S'
            END AS ind_preventivo,
            CASE
                WHEN t1.flag_clienteinsala='1' THEN 'ASP'
                ELSE ''
            END AS cod_tipo_trasporto,
            CASE 
                WHEN t7.data_fatt IS NULL THEN 'N'
                ELSE 'S'
            END AS ind_chiuso,
            ISNULL(convert(varchar(8),t1.data_creazione,112),'xxxxxxxx') AS d_inserimento,
            t1.id_utente AS cod_utente_inserimento,
            ISNULL( CONVERT(varchar(8),t1.data_prenotazione,112),'xxxxxxxx' )+':'+SUBSTRING( ISNULL (CONVERT(varchar(5),t1.ora_prenotazione,114),'xx:xx' ),1,5) AS d_pren,
            ISNULL( CONVERT(varchar(8),t1.data_prevcons,112),'xxxxxxxx' )+':'+SUBSTRING( ISNULL (CONVERT(varchar(5),t1.ora_prevcons,114),'xx:xx' ),1,5) AS d_ricon,  
            t4.id_veicolo AS num_rif_veicolo,
            0 AS num_rif_veicolo_progressivo,
            vei.telaio,
            vei.targa,
            vei.cod_marca,
            CASE
                WHEN vei.cod_modello is null THEN vei.descrizione_aggiuntiva
                ELSE ofmod.descrizione
            END AS des_veicolo,
            m.descrizione as des_marca,
            isnull(CAST(util.id_anagrafica AS char),'') AS cod_anagra_util,
            isnull(CAST(intest.codice_cliente AS char),'') AS cod_anagra_intest,
            ISNULL(util.rag_soc_1,'') AS util_ragsoc,
            ISNULL(intest.ragione_sociale,'') AS intest_ragsoc,
            '' AS cod_anagra_locat,
            '' AS cod_anagra_fatt,
            t5.id_utente AS cod_accettatore,
            isnull(t4.km,0) AS km,
            'infinity' AS dms,
            convert (varchar(23),t1.data_creazione,121) as pratica,
            'S' AS pren
        ";

        $this->query.=" FROM ".$this->tabelle['TDO_PRE']->getTabName()." AS t1";

        $this->query.=" LEFT JOIN ".$this->tabelle['TIPI_DOC']->getTabName()." AS t6 ON t1.tipo_doc=t6.codice";
        $this->query.=" LEFT JOIN ".$this->tabelle['TDO_CLI']->getTabName()." AS t7 ON t7.tipo_doc=t1.td_commessa AND t7.data_doc=t1.data_commessa AND t7.num_doc=t1.num_commessa";
        $this->query.=" INNER JOIN ".$this->tabelle['MDM_PRE_VEICOLI']->getTabName()." AS t4 ON t1.anno=t4.anno AND t1.id_cliente=t4.id_cliente AND t1.data_doc=t4.data_doc AND t1.tipo_doc=t4.tipo_doc AND t1.num_doc=t4.numero_doc";
        $this->query.=" LEFT JOIN ".$this->tabelle['OFF_VEICOLI']->getTabName()." AS vei ON t4.id_veicolo=vei.id_veicolo";
        $this->query.=" LEFT JOIN ".$this->tabelle['OFF_MODELLI']->getTabName()." AS ofmod ON vei.cod_modello=ofmod.cod_modello AND vei.cod_marca=ofmod.cod_marca AND ofmod.livello='4'";
        $this->query.=" LEFT JOIN ".$this->tabelle['OFF_MARCHE']->getTabName()." AS m ON vei.cod_marca=m.cod_marca";
        $this->query.=" LEFT JOIN ".$this->viste['CLIENTI']." AS intest ON CAST(isnull(t1.id_cliente,0) AS int)=intest.codice_cliente";
        $this->query.=" LEFT JOIN ".$this->tabelle['ANAGRAFICA']->getTabName()." AS util ON CAST(isnull(t1.codice_contatto,0) AS int)=util.id_anagrafica";
        $this->query.="
            LEFT JOIN (
                SELECT
                a.matricola,
                b.id_utente
                FROM ".$this->tabelle['O_OPERAI']->getTabName()." AS a
                INNER JOIN ".$this->tabelle['UTENTI']->getTabName()." AS b ON a.id_utenti=b.id_utenti
            ) AS t5 ON t1.accettatore_prenotazione=t5.matricola
        ";

        $this->query.=" WHERE t1.id_documento='".$arg['rif']."'";

        return true;
    }

    function getLamMovements($arg) {

        if ($arg['lista']=='cli') return $this->getCliMovements($arg);
        elseif ($arg['lista']=='pre') return $this->getPreMovements($arg);
    }

    function cliBlock() {

        $txt=" FROM (
                SELECT
                a.anno,
                a.id_cliente,
                a.data_doc,
                a.tipo_doc,
                a.numero_doc,
                CASE 
                    WHEN precodice='ZZ' THEN 'V'
                    ELSE 'R'
                END AS t_riga,
                a.tipo_riga,
                a.precodice,
                CASE 
                    WHEN precodice='ZZ' THEN ''
                    ELSE a.articolo
                END AS articolo,
                a.cod_sconto_prod AS gr,
                a.famiglia,
                '' AS operazione,
                CASE 
                    WHEN precodice='ZZ' THEN a.articolo
                    ELSE ''
                END AS varie,
                a.descr_articolo AS descrizione,
                a.quantita AS qta,
                a.unita_misura AS unita,
                a.vu_lordo AS prezzo,
                (isnull(a.sconti_riga1,0)+isnull(a.sconti_riga2,0)+isnull(a.sconti_riga3,0)) AS prc_sconto,
                (a.vu_netto*a.quantita) AS importo,
                (a.vu_netto*a.quantita*(1+(a.perciva/100))) AS ivato,
                a.codice_iva AS cod_iva,
                a.perciva,
                isnull(a.costo_ultimo,0)*a.quantita AS costo,
                a.note_agg,
                '' AS note_p,
                a.id_inconveniente AS lam,
                a.id_riga,
                a.codice_carico

                FROM ".$this->tabelle['MDM_CLI']->getTabName()." AS a
        
                union all
        
                SELECT
                b.anno,
                b.id_cliente,
                b.data_doc,
                b.tipo_doc,
                b.numero_doc,
                CASE 
                    WHEN b.unita_misura='HF' THEN 'V'
                    ELSE 'M'
                END AS t_riga,
                b.tipo_riga,
                '' AS precodice,
                '' AS articolo,
                '' AS gr,
                '' AS famiglia,
                CASE 
                    WHEN b.unita_misura='HF' THEN ''
                    ELSE  b.lavorazione
                END AS operazione,
                CASE 
                    WHEN b.unita_misura='HF' THEN  b.lavorazione
                    ELSE ''
                END AS varie,
                b.descr_lavorazione AS descrizione,
                CASE 
                    WHEN b.unita_misura='HF' THEN b.quantita
                    ELSE b.tempo
                END AS qta,
                b.unita_misura AS unita,
                CASE 
                    WHEN b.unita_misura='HF' THEN b.vu_lordo
                    ELSE b.vu_lordo
                END AS prezzo,
                (isnull(b.sconti_riga1,0)+isnull(b.sconti_riga2,0)) AS prc_sconto,
                CASE 
                    WHEN b.unita_misura='HF' THEN b.totale
                    ELSE b.vu_netto
                END AS importo,
                CASE 
                    WHEN b.unita_misura='HF' THEN (b.totale*(1+(b.perciva/100)))
                    ELSE (b.vu_netto*(1+(b.perciva/100)))
                END AS ivato,
                b.codice_iva AS cod_iva,
                b.perciva,
                0 AS costo,
                b.note_agg,
                '' AS note_p,
                b.id_inconveniente AS lam,
                b.id_riga,
                b.codice_carico

                FROM ".$this->tabelle['MDM_CLI_LAV']->getTabName()." AS b

            ) AS t1
        ";

        return $txt;
    }

    function getCliMovements($arr) {

        $this->query="SELECT 
            t2.id_documento as rif,
            CASE
                WHEN t2.tipo_mov='LO01' OR t2.tipo_mov='LO02' OR t2.tipo_mov='LO03' OR t2.tipo_mov='LO04' OR t2.tipo_mov='LO05' OR t2.tipo_mov='LO42' OR t2.tipo_mov='LO44' OR t2.tipo_mov='LO45' THEN 'OOP'
                WHEN t2.tipo_mov='LI01' OR t2.tipo_mov='LI02' OR t2.tipo_mov='LI03' OR t2.tipo_mov='LI04' OR t2.tipo_mov='LI05' OR t2.tipo_mov='LI42' OR t2.tipo_mov='LI44' OR t2.tipo_mov='LI45' THEN 'OOA'
                WHEN t2.tipo_mov='LF01' OR t2.tipo_mov='LF02' OR t2.tipo_mov='LF03' OR t2.tipo_mov='LF04' OR t2.tipo_mov='LF42' OR t2.tipo_mov='LF44' THEN 'OOPN'
            END AS cod_movimento,
            t1.t_riga,
            t1.tipo_riga,
            t1.precodice as tipo,
            t1.articolo,
            'S' AS flag_prelevato,
            t1.gr,
            t1.operazione,
            t1.varie,
            t1.descrizione,
            t1.qta,
            t1.unita,
            t1.prezzo,
            t1.prc_sconto,
            t1.importo,
            t1.ivato,
            t1.cod_iva,
            isnull(t1.perciva,0) as prc_iva,
            isnull(t1.note_agg,'') as note_d,
            t1.note_p,
            t1.lam,
            t1.id_riga as riga
        ";

        $this->query.=$this->cliBlock();

        $this->query.=" INNER JOIN ".$this->tabelle['TDO_CLI']->getTabName()." as t2 ON t1.anno=t2.anno AND t1.id_cliente=t2.id_cliente AND t1.data_doc=t2.data_doc AND t1.tipo_doc=t2.tipo_doc AND t1.numero_doc=t2.num_doc";
        
        $this->query.=" WHERE t2.id_documento='".$arr['rif']."'";
        
        $this->query.=" ORDER BY t1.t_riga,t1.lam,t1.id_riga";

        return true;
    }

    function getPreMovements($arr) {

        $this->query="SELECT 
            t2.id_documento as rif,
            t1.t_riga,
            t1.tipo_riga,
            t1.precodice as tipo,
            t1.articolo,
            'S' AS flag_prelevato,
            t1.gr,
            t1.operazione,
            t1.varie,
            t1.descrizione,
            t1.qta,
            t1.unita,
            t1.prezzo,
            t1.prc_sconto,
            t1.importo,
            t1.ivato,
            t1.cod_iva,
            isnull(t1.perciva,0) as prc_iva,
            isnull(t1.note_agg,'') as note_d,
            t1.note_p,
            t1.lam,
            t1.id_riga as riga
            
            FROM (
                SELECT
                a.anno,
                a.id_cliente,
                a.data_doc,
                a.tipo_doc,
                a.numero_doc,
                CASE 
                    WHEN precodice='ZZ' THEN 'V'
                    ELSE 'R'
                END AS t_riga,
                a.tipo_riga,
                a.precodice,
                CASE 
                    WHEN precodice='ZZ' THEN ''
                    ELSE a.articolo
                END AS articolo,
                a.cod_sconto_prod AS gr,
                '' AS operazione,
                CASE 
                    WHEN precodice='ZZ' THEN a.articolo
                    ELSE ''
                END AS varie,
                a.descr_articolo AS descrizione,
                a.quantita AS qta,
                a.unita_misura AS unita,
                a.vu_lordo AS prezzo,
                (a.sconti_riga1+a.sconti_riga2+a.sconti_riga3) AS prc_sconto,
                (a.vu_netto*a.quantita) AS importo,
	            (a.vu_netto*a.quantita*(1+(a.perciva/100))) AS ivato,
                a.codice_iva AS cod_iva,
                a.perciva,
                a.note_agg,
                '' AS note_p,
                a.id_inconveniente AS lam,
                a.id_riga
        ";
            
        $this->query.=" FROM ".$this->tabelle['MDM_PRE_CLI']->getTabName()." AS a";
            
        $this->query.=" union all
            
                SELECT
                b.anno,
                b.id_cliente,
                b.data_doc,
                b.tipo_doc,
                b.numero_doc,
                'M' AS t_riga,
                b.tipo_riga,
                '' AS precodice,
                '' AS articolo,
                '' AS gr,
                b.lavorazione AS operazione,
                '' AS varie,
                b.descr_lavorazione AS descrizione,
                b.tempo AS qta,
                b.unita_misura AS unita,
                CASE
                    WHEN b.tempo=0 THEN 0
                    ELSE (b.vu_lordo/b.tempo) 
                END AS prezzo,
                (b.sconti_riga1+b.sconti_riga2) AS prc_sconto,
                b.vu_netto AS importo,
	            (b.vu_netto*(1+(b.perciva/100))) AS ivato,
                b.codice_iva AS cod_iva,
                b.perciva,
                b.note_agg,
                '' AS note_p,
                b.id_inconveniente AS lam,
                b.id_riga
        ";

        $this->query.=" FROM ".$this->tabelle['MDM_PRE_LAV']->getTabName()." AS b";

        $this->query.=" ) AS t1";

        
        $this->query.=" INNER JOIN ".$this->tabelle['TDO_PRE']->getTabName()." as t2 ON t1.anno=t2.anno AND t1.id_cliente=t2.id_cliente AND t1.data_doc=t2.data_doc AND t1.tipo_doc=t2.tipo_doc AND t1.numero_doc=t2.num_doc";
        
        $this->query.=" WHERE t2.id_documento='".$arr['rif']."'";
        
        $this->query.=" ORDER BY t1.t_riga,t1.lam,t1.id_riga";

        return true;
    }

    function baseMarcatura() {

        return "SELECT
            t2.anno,
            t2.id_cliente,
            t2.data_doc,
            t2.tipo_doc,
            t2.numero_doc,
            t2.id_riga,
            t2.id_riga AS cod_inconveniente,
            t3.id_documento AS num_rif_movimento,
            t4.punto_vendita AS cod_officina,
            CASE
                WHEN t3.tipo_mov='LO01' OR t3.tipo_mov='LO02' OR t3.tipo_mov='LO03' OR t3.tipo_mov='LO04' OR t3.tipo_mov='LO05' OR t3.tipo_mov='LO42' OR t3.tipo_mov='LO44' OR t3.tipo_mov='LO45' THEN 'OOP'
                WHEN t3.tipo_mov='LI01' OR t3.tipo_mov='LI02' OR t3.tipo_mov='LI03' OR t3.tipo_mov='LI04' OR t3.tipo_mov='LI05' OR t3.tipo_mov='LI42' OR t3.tipo_mov='LI44' OR t3.tipo_mov='LI45' THEN 'OOA'
                WHEN t3.tipo_mov='LF01' OR t3.tipo_mov='LF02' OR t3.tipo_mov='LF03' OR t3.tipo_mov='LF04' OR t3.tipo_mov='LF42' OR t3.tipo_mov='LF44' THEN 'OOPN'
            END AS cod_movimento,
            t2.matricola AS cod_operaio,
            CONVERT(varchar(8),t2.data_inizio,112) AS d_inizio,
            isnull(CONVERT(varchar(5),t2.ora_inizio,114),'') AS o_inizio,
            isnull(CONVERT(varchar(8),t2.data_fine,112),'') AS d_fine,
            isnull(CONVERT(varchar(5),t2.ora_fine,114),'') AS o_fine,
            cast(isnull(t2.tempo_calcolato,0) AS decimal(5,2)) AS qta_ore_lavorate,
            isnull(t2.note,'') AS des_note,
            t2.id_ordine AS num_rif_riltem,
            CASE 
                WHEN t3.data_fatt IS NULL THEN 'N'
                ELSE 'S'
            END AS ind_chiuso,
            'infinity' AS dms
        ";
    }

    function baseSpeciale() {
        return "SELECT
            t2.matricola AS cod_operaio,
            CONVERT(varchar(8),t2.data_inizio,112) AS d_inizio,
            isnull(CONVERT(varchar(5),t2.ora_inizio,114),'') AS o_inizio,
            isnull(CONVERT(varchar(8),t2.data_fine,112),'') AS d_fine,
            isnull(CONVERT(varchar(5),t2.ora_fine,114),'') AS o_fine,
            cast(t2.tempo_calcolato AS decimal(5,2)) AS qta_ore_lavorate,
            CASE
                WHEN isnull(t2.note,'')!='' THEN t2.note
                ELSE
                CASE
                    WHEN t2.cod_spesa='MP' THEN 'PUL'
                    WHEN t2.cod_spesa='MM' THEN 'SER'
                    WHEN t2.cod_spesa='MA' THEN 'ATT'
                END
            END AS des_note,	
            t2.id AS num_rif_riltem,
            '0k' AS num_rif_movimento,
            'ZZ' AS cod_officina,
            t2.cod_spesa AS cod_inconveniente,
            'OOS' AS cod_movimento,
            'N' AS ind_chiuso,
            '' AS mat_targa,
            '' AS util_ragsoc,
            '' AS d_ricon,
            '' AS mat_telaio,
            '' AS des_veicolo,
            '' AS cod_accettatore
        ";
    }

    function getMarcatureAperte($arg) {
        //nonostante il nome del metodo non prende le marcature aperte ma l'ultima marcatura di ogni tecnico
        //se l'odl è ancora aperto

        $this->query=$this->baseMarcatura();

        $this->query.=" FROM (
                SELECT matricola,
                MAX(CONVERT(varchar(8),data_inizio,112)+':'+CONVERT(varchar(5),ora_inizio,114)+':'+cast(id_ordine as varchar)) AS massimo 

                FROM ".$this->tabelle['MDM_CLI_INC_OPERAI']->getTabName()."
  
  	            WHERE isnull(CONVERT(varchar(5),ora_inizio,114),'')!= '' AND flag_ins='M'
                
                GROUP BY matricola
            ) AS t1
        ";
    
        $this->query.=" INNER JOIN ".$this->tabelle['MDM_CLI_INC_OPERAI']->getTabName()." AS t2 ON t1.massimo=CONVERT(varchar(8),t2.data_inizio,112)+':'+CONVERT(varchar(5),t2.ora_inizio,114)+':'+cast(id_ordine as varchar)";

        $this->query.=" INNER JOIN ".$this->tabelle['TDO_CLI']->getTabName()." AS t3 ON t2.anno=t3.anno AND t2.id_cliente=t3.id_cliente AND t2.data_doc=t3.data_doc AND t2.tipo_doc=t3.tipo_doc AND t2.numero_doc=t3.num_doc";

        $this->query.=" LEFT JOIN  ".$this->tabelle['TIPI_DOC']->getTabName()." AS t4 ON t3.tipo_doc=t4.codice";

        //$this->query.=" isnull(CONVERT(varchar(5),t2.ora_inizio,114),'')!='' AND t3.data_fatt IS NULL";
        $this->query.=" WHERE isnull(CONVERT(varchar(5),t2.ora_inizio,114),'')!=''";

        $this->query.=" ORDER BY t2.matricola";

        return true;
    }

    function getMarcatureSpecialiAperte($arg) {

        $this->query=$this->baseSpeciale();

        $this->query.="FROM (
                SELECT
                matricola,
                MAX(id) AS massimo 

                FROM dba.off_marcature_int
                
                GROUP BY matricola
            ) AS t1
        ";
        
        $this->query.=" INNER JOIN ".$this->tabelle['OFF_MARCATURE_INT']->getTabName()." AS t2 ON t1.massimo=t2.id";

        $this->query.=" WHERE isnull(t2.matricola,'')!=''";

        $this->query.=" ORDER BY t2.matricola";

        return true;
    }

    function getMarcaturaByID($arg) {

        $this->query=$this->baseMarcatura();

        $this->query.=" FROM ".$this->tabelle['MDM_CLI_INC_OPERAI']->getTabName()." AS t2";

        $this->query.=" INNER JOIN ".$this->tabelle['TDO_CLI']->getTabName()." AS t3 ON t2.anno=t3.anno AND t2.id_cliente=t3.id_cliente AND t2.data_doc=t3.data_doc AND t2.tipo_doc=t3.tipo_doc AND t2.numero_doc=t3.num_doc";

        $this->query.=" LEFT JOIN  ".$this->tabelle['TIPI_DOC']->getTabName()." AS t4 ON t3.tipo_doc=t4.codice";

        $this->query.=" WHERE isnull(t2.matricola,'') != '' AND  t2.id_ordine='".$arg['ID']."'";

        return true;

    }

    function getMarcaturaSpecialeByID($arg) {

        $this->query=$this->baseSpeciale();

        $this->query.=" FROM ".$this->tabelle['OFF_MARCATURE_INT']->getTabName()." AS t2";

        $this->query.=" WHERE t2.id='".$arg['ID']."'";

        return true;

    }

    function getMarcatureOdl($arg) {

        $this->query=$this->baseMarcatura();

        $this->query.=",op.nome AS nome_operaio";

        $this->query.=" FROM ".$this->tabelle['MDM_CLI_INC_OPERAI']->getTabName()." AS t2";

        $this->query.=" INNER JOIN ".$this->tabelle['TDO_CLI']->getTabName()." AS t3 ON t2.anno=t3.anno AND t2.id_cliente=t3.id_cliente AND t2.data_doc=t3.data_doc AND t2.tipo_doc=t3.tipo_doc AND t2.numero_doc=t3.num_doc";

        $this->query.=" LEFT JOIN  ".$this->tabelle['TIPI_DOC']->getTabName()." AS t4 ON t3.tipo_doc=t4.codice";

        $this->query.=" LEFT JOIN  ".$this->tabelle['O_OPERAI']->getTabName()." AS op ON t2.matricola=op.matricola";

        //si da per scontato che almeno l'ODL sia impostato
        $this->query.=" WHERE isnull(t2.matricola,'')!='' AND t3.id_documento='".$arg['rif']."'";

        if (isset($arg['lam']) && $arg['lam']!="") $this->query.=" AND t2.id_riga='".$arg['lam']."'";

        $this->query.=" ORDER BY d_inizio,o_inizio";

        return true;
    }

    function getLamStats($arr) {
        
        $this->query="SELECT
            cli.id_documento AS num_rif_movimento,
            isnull(mvt.tempo_stimato,0) AS ore_prenotate_totali,
            isnull(fatt.ore_fatturate,0) AS ore_fatturate_totali,
            mar.*
        ";

        $this->query.=" FROM ".$this->tabelle['MDM_CLI_INC']->getTabName()." AS mvt";
        $this->query.=" INNER JOIN dba.tdo_cli AS cli ON mvt.anno=cli.anno AND mvt.id_cliente=cli.id_cliente AND mvt.data_doc=cli.data_doc AND mvt.tipo_doc=cli.tipo_doc AND mvt.numero_doc=cli.num_doc AND cli.id_documento='".$arr['rif']."' AND id_riga='".$arr['lam']."'";

        $this->query.=" LEFT JOIN (
                SELECT
                
                SUM(isnull(t1.tempo_calcolato,0)) AS ore_lavorate,
                t1.numero_doc AS numero_doc,
                t1.id_riga AS cod_inconveniente,
                t1.matricola AS cod_operaio
                
                FROM ".$this->tabelle['MDM_CLI_INC_OPERAI']->getTabName()." AS t1
                INNER JOIN ".$this->tabelle['TDO_CLI']->getTabName()." AS t3 ON t1.anno=t3.anno AND t1.id_cliente=t3.id_cliente AND t1.data_doc=t3.data_doc AND t1.tipo_doc=t3.tipo_doc AND t1.numero_doc=t3.num_doc
                WHERE t3.id_documento='".$arr['rif']."' AND t1.id_riga='".$arr['lam']."'
                GROUP BY t1.numero_doc,t1.id_riga,t1.matricola
                
            ) as mar ON mvt.numero_doc=mar.numero_doc AND mvt.id_riga=mar.cod_inconveniente
        ";

        $this->query.=" LEFT JOIN (
                SELECT
                t1.numero_doc AS numero_doc,
                t1.id_inconveniente AS cod_inconveniente,
                sum(isnull(t1.tempo,0)) AS ore_fatturate
                    
                FROM ".$this->tabelle['MDM_CLI_LAV']->getTabName()." AS t1
                INNER JOIN ".$this->tabelle['TDO_CLI']->getTabName()." AS t3 ON t1.anno=t3.anno AND t1.id_cliente=t3.id_cliente AND t1.data_doc=t3.data_doc AND t1.tipo_doc=t3.tipo_doc AND t1.numero_doc=t3.num_doc
            
                WHERE t3.id_documento='".$arr['rif']."' AND t1.id_inconveniente='".$arr['lam']."' AND isnull((SELECT b.esterna FROM dba.O_OPERAI AS a INNER JOIN dba.O_QUALIFICA AS b ON a.qualifica=b.id_qualifica WHERE a.matricola=t1.matricola_operaio),0)!='1'
                    
                GROUP BY t1.numero_doc,t1.id_inconveniente
                    
            ) AS fatt ON mvt.numero_doc=fatt.numero_doc AND mvt.id_riga=fatt.cod_inconveniente
        ";

        return true;
    }

    function getPrenotazioni($arg) {
        //restituisce le prenotazioni ed i rispettivi lamentati

        $this->query="SELECT
            odl.*,
            vei.targa AS mat_targa,
            vei.telaio AS mat_telaio,
            vei.cod_modello AS cod_veicolo,
            CASE
                WHEN vei.cod_modello is null THEN vei.descrizione_aggiuntiva
                ELSE ofmod.descrizione
            END AS des_veicolo,
            vei.codice_motore AS mat_motore,
        ";
            //isnull(vei.id_nuovo,0) AS id_nuovo,
        $this->query.="
            CONVERT(varchar(8),vei.data_inizio_garanzia,112) AS d_consegna,
            isnull(CONVERT(varchar(8),nvei.data_arrivo,112),'') AS d_arrivo,
            ISNULL(util.rag_soc_1,'') AS util_ragsoc,
            ISNULL(intest.ragione_sociale,'') AS intest_ragsoc,
            coalesce(dba.fn_get_last_email(util.id_anagrafica,0,0,1),dba.fn_get_last_email(util.id_anagrafica,0,0,2),'') as util_mail,
            coalesce(dba.fn_get_last_email(intest.id_anagrafica,0,0,1),dba.fn_get_last_email(intest.id_anagrafica,0,0,2),'') as intest_mail,
            isnull(dba.gab_fn_telefoni_anagrafica(util.id_anagrafica),'') AS util_tel,
            isnull(dba.gab_fn_telefoni_anagrafica(intest.id_anagrafica),'') AS intest_tel
        ";

        if ( (isset($arg['officina']) && ($arg['officina']=='PN' || $arg['officina']=='CN') ) || (isset($arg['timeless']['officina']) && ($arg['timeless']['officina']=='PN' || $arg['timeless']['officina']=='CN') ) ) {
                    
            $this->query.="
                ,test.numero_contratto,
                convert(varchar(8),test.data_contratto,112) as d_contratto,
                test.id_cliente AS id_cliente_contratto,
                ISNULL(n_cli.ragione_sociale,'') AS intest_contratto,
                test.status_contratto,
                c.id_veicolo AS id_nuovo,
                isnull(convert(varchar(8),c.data_uscita,112),'') as d_uscita
            ";
        }
        elseif ( (isset($arg['officina']) && ($arg['officina']=='PU' || $arg['officina']=='CU') ) || (isset($arg['timeless']['officina']) && ($arg['timeless']['officina']=='PU' || $arg['timeless']['officina']=='CU') ) ) {
            
            $this->query.="
                ,test.numero_contratto,
                convert(varchar(8),test.data_contratto,112) as d_contratto,
                test.id_cliente AS id_cliente_contratto,
                ISNULL(u_cli.ragione_sociale,'') AS intest_contratto,
                test.status_contratto,
                c.usato AS id_usato,
                isnull(convert(varchar(8),c.data_consegna,112),'') as d_uscita
            ";
        }

        $this->query.="
                
            FROM (
        ";

        $this->query.=" 
                SELECT
                ISNULL(convert(varchar(8),t2.data_creazione,112),'xxxxxxxx')+':'+SUBSTRING( ISNULL (CONVERT(varchar(5),t2.data_creazione,114),'xx:xx' ),1,5) as dat_inserimento,
                t6.punto_vendita AS cod_officina,
                CASE
                    WHEN t2.tipo_mov='PR01' OR t2.tipo_mov='PR02' OR t2.tipo_mov='PR03' THEN 'OOP'
                    WHEN t2.tipo_mov='PR04' THEN 'OOA'
                END AS cod_movimento,
                t1.codice_carico AS acarico,
                '' AS cg,
                t2.id_documento AS rif,
                convert (varchar(23),t2.data_creazione,121) as pratica,
                CASE 
                    WHEN isnull(t2.num_preventivo,'')='' THEN 'N'
                    ELSE 'S'
                END AS ind_preventivo,
                CASE 
                    WHEN t7.data_fatt IS NULL THEN 'N'
                    ELSE 'S'
                END AS ind_chiuso,
                t1.id_riga AS lam,
                t1.id_ordine AS rif_lam,
                ISNULL(t1.descr_inconveniente,'') AS des_riga,
                ISNULL( CONVERT(varchar(8),t2.data_prenotazione,112),'xxxxxxxx' )+':'+SUBSTRING( ISNULL (CONVERT(varchar(5),t2.ora_prenotazione,114),'xx:xx' ),1,5) AS d_pren,
                ISNULL( CONVERT(varchar(8),t2.data_prevcons,112),'xxxxxxxx' )+':'+SUBSTRING( ISNULL (CONVERT(varchar(5),t2.ora_prevcons,114),'xx:xx' ),1,5) AS d_ricon,
                'xxxxxxxx:xx:xx' AS d_fine,
                'xxxxxxxx:xx:xx' AS d_ricon_pratica,
                'xxxxxxxx:xx:xx' AS d_entrata_pratica,
                t1.tempo_stimato AS ore,
                t3.descrizione AS subrep,
                ISNULL( CONVERT(varchar(8),t1.data_inizio,112),'xxxxxxxx' )+':'+SUBSTRING( ISNULL (CONVERT(varchar(5),t1.ora_inizio,114),'xx:xx' ),1,5) AS d_inc,
                t4.id_veicolo,
                CAST(isnull(t2.codice_contatto,0) AS int) AS cod_anagra_util,
                CAST(isnull(t2.id_cliente,0) AS int) AS cod_anagra_intest,
                t5.id_utente AS cod_accettatore,
                t1.id_ordine AS id_inconveniente_infinity,
                t7.id_documento AS rif_commessa,
                CASE
                    WHEN ISNULL(t7.id_documento,'')!='' THEN
                        CASE
                            WHEN t7.data_fatt IS NULL THEN 'AP'
                            ELSE 'CH'
                        END
                    ELSE 'XX'
                END AS cod_stato_commessa,
                CASE
                    WHEN t2.flag_clienteinsala='1' THEN 'ASP'
                    ELSE ''
                END AS cod_tipo_trasporto,
                t1.tempo_stimato AS ore_isla,
                t4.km,
                CASE
                    WHEN isnull(t2.td_commessa,'')='xx' THEN 'S'
                    ELSE 'N'
                END AS ind_annullo,
                'infinity' AS dms,
                'XX' AS ind_inc_stato,
                CASE 
                    WHEN ISNULL(t2.id_cliente,0)=30001 OR ISNULL(t2.id_cliente,0)=30002 THEN 1 
                    ELSE 0 
                END AS apprip,
                t2.confermato AS picking

                FROM ".$this->tabelle['MDM_PRE_INC']->getTabName()." AS t1
                INNER JOIN ".$this->tabelle['TDO_PRE']->getTabName()." AS t2 ON t1.anno=t2.anno AND t1.id_cliente=t2.id_cliente AND t1.data_doc=t2.data_doc AND t1.tipo_doc=t2.tipo_doc AND t1.numero_doc=t2.num_doc
                INNER JOIN ".$this->tabelle['MDM_PRE_VEICOLI']->getTabName()." AS t4 ON t1.anno=t4.anno AND t1.id_cliente=t4.id_cliente AND t1.data_doc=t4.data_doc AND t1.tipo_doc=t4.tipo_doc AND t1.numero_doc=t4.numero_doc
                LEFT JOIN ".$this->tabelle['TDO_CLI']->getTabName()." AS t7 ON t7.tipo_doc=t2.td_commessa AND t7.data_doc=t2.data_commessa AND t7.num_doc=t2.num_commessa
                LEFT JOIN ".$this->tabelle['O_QUALIFICA']->getTabName()." AS t3 ON t1.id_qualifica=t3.id_qualifica
                LEFT JOIN  ".$this->tabelle['TIPI_DOC']->getTabName()." AS t6 ON t2.tipo_doc=t6.codice
                LEFT JOIN (
                    SELECT
                    a.matricola,
                    b.id_utente
                    FROM ".$this->tabelle['O_OPERAI']->getTabName()." AS a
                    INNER JOIN ".$this->tabelle['UTENTI']->getTabName()." AS b ON a.id_utenti=b.id_utenti
                )AS t5 ON t2.accettatore_prenotazione=t5.matricola
        ";

        if (isset($arg['num_rif_movimento'])) {
            $this->query.="  WHERE  t2.id_documento='".$arg['num_rif_movimento']."' AND isnull(t2.td_commessa,'')!='XX' ";
        }
        else {
            $this->query.="  WHERE CONVERT(varchar(8),t2.data_prenotazione,112)>='".$arg['inizio']."' AND CONVERT(varchar(8),t2.data_prenotazione,112)<='".$arg['fine']."' AND t6.punto_vendita='".$arg['officina']."' AND isnull(t2.td_commessa,'')!='XX' ";
        }

        $this->query.=" ) AS odl ";

        $this->query.=" LEFT JOIN ".$this->tabelle['OFF_VEICOLI']->getTabName()." AS vei ON odl.id_veicolo=vei.id_veicolo";
        $this->query.=" LEFT JOIN ".$this->tabelle['OFF_MODELLI']->getTabName()." AS ofmod ON vei.cod_modello=ofmod.cod_modello AND vei.cod_marca=ofmod.cod_marca AND ofmod.livello='4'";
        $this->query.=" LEFT JOIN ".$this->tabelle['N_VEICOLI']->getTabName()." AS nvei ON vei.id_nuovo=nvei.id_veicolo";
        $this->query.=" LEFT JOIN ".$this->viste['CLIENTI']." AS intest ON odl.cod_anagra_intest=intest.codice_cliente";
        $this->query.=" LEFT JOIN ".$this->tabelle['ANAGRAFICA']->getTabName()." AS util ON odl.cod_anagra_util=util.id_anagrafica";

        ////////////////////////////////////////////////////////////

        if ( (isset($arg['officina']) && ($arg['officina']=='PN' || $arg['officina']=='CN') ) || (isset($arg['timeless']['officina']) && ($arg['timeless']['officina']=='PN' || $arg['timeless']['officina']=='CN') ) ) {

            $this->query.=" LEFT JOIN ".$this->tabelle['N_DETTAGLIO_CONTRATTO']->getTabName()." AS cont ON vei.telaio=cont.telaio";
            $this->query.=" LEFT JOIN ".$this->tabelle['N_VEICOLI']->getTabName()." AS c ON cont.id_veicolo=c.id_veicolo";
            $this->query.=" LEFT JOIN ".$this->tabelle['N_TESTATA_CONTRATTO']->getTabName()." AS test ON cont.id_contratto=test.id_contratto";
            $this->query.=" LEFT JOIN ".$this->viste['CLIENTI']." AS n_cli ON test.id_cliente=n_cli.codice_cliente";
        }
        elseif ( (isset($arg['officina']) && ($arg['officina']=='PU' || $arg['officina']=='CU') ) || (isset($arg['timeless']['officina']) && ($arg['timeless']['officina']=='PU' || $arg['timeless']['officina']=='CU') ) ) {

            $this->query.=" LEFT JOIN ".$this->tabelle['DETTAGLIO_CONTRATTO']->getTabName()." AS cont ON vei.id_usato=cont.veicolo";
            $this->query.=" LEFT JOIN ".$this->tabelle['VEICOLI_USATI']->getTabName()." AS c ON cont.veicolo=c.usato";
            $this->query.=" LEFT JOIN ".$this->tabelle['TESTATA_CONTRATTO']->getTabName()." AS test ON cont.id_contratto=test.id_contratto";
            $this->query.=" LEFT JOIN ".$this->viste['CLIENTI']." AS u_cli ON test.id_cliente=u_cli.codice_cliente";
        }

        ////////////////////////////////////////////////////////////

        /*if (isset($arg['timeless'])) {

            if ($arg['timeless']['officina']=='PN') {
                //$this->query.=" WHERE isnull(test.status_contratto,'X')!='X' AND isnull(convert(varchar(8),c.data_uscita,112),'')='' AND intest.codice_cliente='30001'";
                $this->query.=" WHERE isnull(test.status_contratto,'X')!='X' AND isnull(convert(varchar(8),c.data_uscita,112),'')='' AND odl.apprip>0";
            }
            if ($arg['timeless']['officina']=='PU') {
                //$this->query.=" WHERE isnull(test.status_contratto,'X')!='X' AND isnull(convert(varchar(8),c.data_consegna,112),'')='' AND intest.codice_cliente='30002'";
                $this->query.=" WHERE isnull(test.status_contratto,'X')!='X' AND isnull(convert(varchar(8),c.data_consegna,112),'')='' AND odl.apprip>0";
            }
        }*/

        if (isset($arg['search'])) {
            $tsc=trim($arg['search']);
            if ($tsc!="") {
                $this->query.=' WHERE';
                $this->query.=" (vei.targa LIKE '%".$tsc."%' OR vei.telaio LIKE '%".$tsc."%' OR util.rag_soc_1 LIKE '%".$tsc."%' OR intest.ragione_sociale LIKE '%".$tsc."%'";
                
                if ( (isset($arg['officina']) && ($arg['officina']=='PN' || $arg['officina']=='CN') ) || (isset($arg['timeless']['officina']) && ($arg['timeless']['officina']=='PN' || $arg['timeless']['officina']=='CN') ) ) {
                    $this->query.=" OR ISNULL(n_cli.ragione_sociale,'') LIKE '%".$tsc."%'";
                }
                elseif ( (isset($arg['officina']) && ($arg['officina']=='PU' || $arg['officina']=='CU') ) || (isset($arg['timeless']['officina']) && ($arg['timeless']['officina']=='PU' || $arg['timeless']['officina']=='CU') ) ) {
                    $this->query.=" OR ISNULL(u_cli.ragione_sociale,'') LIKE '%".$tsc."%'";
                }

                $this->query.=")";
            }
        }

        $this->query.=" ORDER BY odl.d_pren,odl.rif,odl.lam";

        return true;
    }

    function getOccupazione($arg) {
        return false;
    }

    /////////////////////////////////////////////////////////////////////////////////////////////////

    function apriTimbratura($arg) {

        //GALILEO È STATO IMPOSTATO IN MODALITÀ TRANSACTION

        $this->doInsert('MDM_CLI_INC_OPERAI',$arg,'','');

        //aggiornamento lamentato
        $wclause="anno='".$arg['anno']."' AND id_cliente='".$arg['id_cliente']."' AND data_doc='".$arg['data_doc']."' AND tipo_doc='".$arg['tipo_doc']."' AND numero_doc='".$arg['numero_doc']."' AND id_riga='".$arg['id_riga']."'";
        $a=array('matricola_operaio'=>$arg['matricola']);
        $this->doUpdate('MDM_CLI_INC',$a,$wclause,'');

        //aggiornamento posizioni di lavoro
        $wclause="anno='".$arg['anno']."' AND id_cliente='".$arg['id_cliente']."' AND data_doc='".$arg['data_doc']."' AND tipo_doc='".$arg['tipo_doc']."' AND numero_doc='".$arg['numero_doc']."' AND id_inconveniente='".$arg['id_riga']."' AND ( isnull(matricola_operaio,'')='' OR ( (SELECT b.esterna FROM dba.O_OPERAI AS a INNER JOIN dba.O_QUALIFICA AS b ON a.qualifica=b.id_qualifica WHERE a.matricola=matricola_operaio)!='1') )";
        $a=array('matricola_operaio'=>$arg['matricola']);
        $this->doUpdate('MDM_CLI_LAV',$a,$wclause,'');

        return true;
    }

    function aggiornamentoTempoFatturato($arg) {

        $temp="UPDATE ".$this->tabelle['MDM_CLI_INC_OPERAI']->getTabName()."
            SET
                t0.tempo_fatturato = t1.calcolo_fatt
            FROM ".$this->tabelle['MDM_CLI_INC_OPERAI']->getTabName()."  AS t0
                INNER JOIN (
                    SELECT
                    a.id_ordine,
                    a.id_riga,
                    a.tempo_calcolato,
                    dba.gab_fn_tot_marcato_inc(a.anno,a.id_cliente,a.tipo_doc,a.data_doc,a.numero_doc,a.id_riga) AS tm,
                    dba.gab_fn_tot_fatturato_inc(a.anno,a.id_cliente,a.tipo_doc,a.data_doc,a.numero_doc,a.id_riga) AS tf,
                    CASE
                        WHEN tm=0 THEN 0
                        ELSE a.tempo_calcolato/tm
                    END AS perc_marc,
                    CAST (perc_marc*tf AS numeric(5,2)) AS calcolo_fatt
                    FROM ".$this->tabelle['MDM_CLI_INC_OPERAI']->getTabName()."  AS a
                    WHERE a.anno='".$arg['anno']."' AND a.id_cliente='".$arg['id_cliente']."' AND a.data_doc='".$arg['data_doc']."' AND a.tipo_doc='".$arg['tipo_doc']."' AND a.numero_doc='".$arg['numero_doc']."' AND a.id_riga='".$arg['id_riga']."'
                ) AS t1 ON t0.id_ordine = t1.id_ordine
                
            WHERE t0.anno='".$arg['anno']."' AND t0.id_cliente='".$arg['id_cliente']."' AND t0.data_doc='".$arg['data_doc']."' AND t0.tipo_doc='".$arg['tipo_doc']."' AND t0.numero_doc='".$arg['numero_doc']."' AND t0.id_riga='".$arg['id_riga']."'
        ;";

        return $temp;
    }

    function updateTimbratura($arg) {

        //GALILEO È STATO IMPOSTATO IN MODALITÀ TRANSACTION

        //SE FITTIZIO --> UPDATE OFF_MARCATURE_INT
        if ($arg['fittizio']==1) {
            return $this->updateTimbraturaSpeciale($arg);
        }

        //SE NON FITTIZIO continua

        $wclause="id_ordine='".$arg['id_ordine']."'";

        $this->doUpdate('MDM_CLI_INC_OPERAI',$arg,$wclause,'');

        //////////////////////////////////////////////
        //AGGIORNAMENTO TEMPO_FATTURATO
        $temp=$this->aggiornamentoTempoFatturato($arg);

        $this->arrQuery[]=str_replace("\n","",$temp);

        //////////////////////////////////////////////
        //ritorno della marcatura chiusa

        $temp=$this->baseMarcatura();

        $temp.=" FROM ".$this->tabelle['MDM_CLI_INC_OPERAI']->getTabName()." AS t2";

        $temp.=" INNER JOIN ".$this->tabelle['TDO_CLI']->getTabName()." AS t3 ON t2.anno=t3.anno AND t2.id_cliente=t3.id_cliente AND t2.data_doc=t3.data_doc AND t2.tipo_doc=t3.tipo_doc AND t2.numero_doc=t3.num_doc";

        $temp.=" LEFT JOIN  ".$this->tabelle['TIPI_DOC']->getTabName()." AS t4 ON t3.tipo_doc=t4.codice";

        $temp.=" WHERE isnull(t2.matricola,'') != '' AND  t2.id_ordine='".$arg['id_ordine']."';";

        $this->arrQuery[]=str_replace("\n","",$temp);

        foreach ($this->arrQuery as $k=>$q) {
            $this->arrQuery[$k]=stripcslashes($q);
        }

        return true;
    }

    function updateTimbraturaSpeciale($arg) {

        //################################
        //SE FITTIZIO + SPOSTAFITTIZIO ---> DELETE OFF_MARCATURE_INT e UPDATE (ora chiusura, calcolo ore...) MDM_CLI_INC_OPERAI
        //################################

        $wclause="id='".$arg['num_rif_riltem']."'";

        $this->doUpdate('OFF_MARCATURE_INT',$arg,$wclause,'');

        //////////////////////////////////////////////
        //ritorno della marcatura chiusa

        $temp=$this->baseSpeciale();

        $temp.=" FROM ".$this->tabelle['OFF_MARCATURE_INT']->getTabName()." AS t2 ";
        
        $temp.=" WHERE t2.id='".$arg['num_rif_riltem']."';";

        $this->arrQuery[]=str_replace("\n","",$temp);

        return true;
    }

    function apriTimbraturaSpeciale($arg) {

        /*{
        "specialTag": {
            "coll": "132",
            "rif": 279,
            "lam": 2
        },
        "speciale": "SER",
        "matricola": "038",
        "tempo_calcolato": 0,
        "data_inizio": "20220207",
        "ora_inizio": "18:01",
        "data_modifica": "2022-02-07 18:01:13",
        "id_utente": "57",
        "cod_spesa": "MM",
        "note": "SER{\"coll\":\"132\",\"rif\":279,\"lam\":2}"
        }*/

        $arg['note']=$arg['speciale'].json_encode($arg['specialTag']);

        //GALILEO È STATO IMPOSTATO IN MODALITÀ TRANSACTION

        $this->doInsert('OFF_MARCATURE_INT',$arg,'','');
        
        //echo json_encode($arg);
        //echo json_encode($this->arrQuery);
        //echo json_encode($this->getLog('errori'));

        return true;
    }

    function getLamMarcato($arg) {
        //operaio è il riferimento proprio del DMS

        $this->query="SELECT
            t2.*,
            t2.tempo_calcolato AS qta_ore_lavorate,
            t2.note AS des_note
        ";

        $this->query.=" FROM ".$this->tabelle['MDM_CLI_INC_OPERAI']->getTabName()." AS t2";

        $this->query.=" INNER JOIN ".$this->tabelle['TDO_CLI']->getTabName()." AS t3 ON t2.anno=t3.anno AND t2.id_cliente=t3.id_cliente AND t2.data_doc=t3.data_doc AND t2.tipo_doc=t3.tipo_doc AND t2.numero_doc=t3.num_doc";

        $this->query.=" WHERE t3.id_documento='".$arg['rif']."' AND t2.id_riga='".$arg['lam']."' AND t2.matricola='".$arg['operaio']."'";

        return true;
    }

    //////////////////////////////////////////////////////////////

    function getRiltem($arr) {

        $this->query="SELECT
            m.anno,
            m.id_cliente,
            m.data_doc,
            m.tipo_doc,
            m.numero_doc,
            m.cod_inconveniente,
            m.num_rif_movimento,
            m.cod_operaio,
            m.d_inizio,
            m.o_inizio,
            m.d_fine,
            m.o_fine,
            m.qta_ore_lavorate AS qta,
            m.des_note,
            m.cod_officina,
            m.ind_chiuso,
            m.inc_pos_lav,
            m.inc_marc_chiuse,
            m.cod_accettatore,
            m.qta_ore_prenotazione,
            m.acarico,
            m.cod_movimento,
            m.cg,
            m.des_ragsoc,
            m.cod_spesa,
            m.dms,
            m.num_rif_riltem

            FROM (
                SELECT
                t2.anno,
                t2.id_cliente,
                t2.data_doc,
                t2.tipo_doc,
                t2.numero_doc,
                t2.id_riga AS cod_inconveniente,
                ll.num_rif_movimento,
                t2.matricola AS cod_operaio,
                CONVERT(varchar(8),t2.data_inizio,112) AS d_inizio,
                isnull(CONVERT(varchar(5),t2.ora_inizio,114),'') AS o_inizio,
                CONVERT(varchar(8),t2.data_fine,112) AS d_fine,
                CONVERT(varchar(5),t2.ora_fine,114) AS o_fine,
                cast(isnull(t2.tempo_calcolato,0) AS decimal(7,2)) AS qta_ore_lavorate,
                isnull(t2.note,'') AS des_note,
                isnull(ll.inc_cod_off,'') AS cod_officina,
                isnull(ll.ind_chiuso,'') AS ind_chiuso,
                isnull(ll.inc_pos_lav,0) AS inc_pos_lav,
                isnull(ll.inc_marc_chiuse,0) AS inc_marc_chiuse,
                isnull(ll.cod_accettatore,'') AS cod_accettatore,
                isnull(ll.qta_ore_prenotazione,0) AS qta_ore_prenotazione,
                isnull(ll.inc_carico,'') AS acarico,
                isnull(ll.inc_cm,'') AS cod_movimento,
                isnull(ll.inc_cg,'') AS cg,
                isnull(ll.des_ragsoc,'') AS des_ragsoc,
                '' AS cod_spesa,
                'infinity' AS dms,
                t2.id_ordine as num_rif_riltem
              
                FROM ".$this->tabelle['MDM_CLI_INC_OPERAI']->getTabName()." AS t2
                INNER JOIN ".$this->viste['LISTA_LAMENTATI']." AS ll ON t2.anno=ll.anno AND t2.id_cliente=ll.id_cliente AND t2.data_doc=ll.data_doc AND t2.tipo_doc=ll.tipo_doc AND t2.numero_doc=ll.numero_doc AND t2.id_riga=ll.inc AND ll.ind_annullo='N'
                
                union all

                SELECT 
                null as anno,
                null as id_cliente,
                null as data_doc,
                null as tipo_doc,
                null as numero_doc,
                0 as cod_inconveniente,
                id as num_rif_movimento,
                matricola as cod_operaio,
                CONVERT(varchar(8),data_inizio,112) AS d_inizio,
                isnull(CONVERT(varchar(5),ora_inizio,114),'') AS o_inizio,
                CONVERT(varchar(8),data_fine,112) AS d_fine,
                CONVERT(varchar(5),ora_fine,114) AS o_fine,
                cast(isnull(tempo_calcolato,0) AS decimal(7,2)) AS qta_ore_lavorate,
                CASE
                    WHEN isnull(note,'')!='' THEN note
                    ELSE
                    CASE
                        WHEN cod_spesa='MP' THEN 'PUL'
                        WHEN cod_spesa='MM' THEN 'SER'
                        WHEN cod_spesa='MA' THEN 'ATT'
                    END
                END AS des_note,
                'ZZ' AS cod_officina,
                null AS ind_chiuso,
                null AS inc_pos_lav,
                null AS inc_marc_chiuse,
                null AS cod_accettatore,
                null AS qta_ore_prenotazione,
                null AS acarico,
                'OOS' AS cod_movimento,
                null AS cg,
                null AS des_ragsoc,
                cod_spesa,
                'infinity' AS dms,
                id AS num_rif_riltem
                from ".$this->tabelle['OFF_MARCATURE_INT']->getTabName()."
            ) AS m

            WHERE m.o_inizio!=''
        ";

        $this->query.=" AND m.d_inizio>='".$arr['inizio']."' AND m.d_inizio<='".$arr['fine']."' ";
                
        if ($arr['operaio']!="") $this->query.=" AND m.cod_operaio='".$arr['operaio']."'";

        if ($this->orderBy!="") {
            $this->query.=' ORDER BY '.$this->orderBy;
        }

        return true;
    }

    function getFattNonMarc($arr) {
        
        $this->query="SELECT
            ISNULL(CONVERT(varchar(8),t2.data_fatt,112),'') AS d_doc,
            ll.num_rif_movimento,
            ll.inc AS cod_inconveniente,
            ll.inc_cod_off,
            isnull(ll.ind_chiuso,'') AS ind_chiuso,
            isnull(ll.inc_pos_lav,0) AS inc_pos_lav,
            isnull(ll.inc_marc_chiuse,0) AS inc_marc_chiuse,
            isnull(ll.cod_accettatore,'') AS cod_accettatore,
            isnull(ll.qta_ore_prenotazione,0) AS qta_ore_prenotazione,
            isnull(ll.inc_carico,'') AS acarico,
            isnull(ll.inc_cm,'') AS cod_movimento,
            isnull(ll.inc_cg,'') AS cg,
            isnull(ll.inc_testo,'') AS inc_testo,
            isnull(ll.des_ragsoc,'') AS des_ragsoc,
            'infinity' AS dms
            
            FROM ".$this->viste['LISTA_LAMENTATI']." AS ll
            INNER JOIN ".$this->tabelle['TDO_CLI']->getTabName()." AS t2 ON ll.anno=t2.anno AND ll.id_cliente=t2.id_cliente AND ll.data_doc=t2.data_doc AND ll.tipo_doc=t2.tipo_doc AND ll.numero_doc=t2.num_doc
            
            WHERE ll.inc_marc_chiuse=0 AND ll.inc_pos_lav!=0
        ";

        $this->query.=" AND CONVERT(varchar(8),t2.data_fatt,112)>='".$arr['inizio']."' AND CONVERT(varchar(8),t2.data_fatt,112)<='".$arr['fine']."' AND ll.inc_cod_off IN (".substr($arr['reparti'],0,-1).")";

        return true;

    }

    //////////////////////////////////////////////////////////////////////

    function getStorico($arr) {

        $this->query="SELECT
            t1.id_documento AS rif,
            t6.punto_vendita AS cod_officina,
            t2.id_veicolo,
            convert (varchar(23),t1.data_creazione,121) as pratica,
            isnull(t3.id_documento,0) as prenotazione,
            CASE
                WHEN t1.data_fatt IS NULL THEN 'N'
                ELSE 'S'
            END AS ind_chiuso,
            t1.num_doc,
            CONVERT(varchar(8),isnull(t1.data_iniziolavori,t1.data_creazione),112) as d_rif,
            ISNULL(convert(varchar(8),t3.data_prenotazione,112),'') AS d_pren,
            ISNULL(convert(varchar(8),t1.data_iniziolavori,112),'') AS d_entrata,
            ISNULL(convert(varchar(8),t1.data_fatt,112),'') AS d_fatt,
            t1.tipo_doc AS cm,
            t5.id_utente AS cod_accettatore,
            ISNULL(util.rag_soc_1,'') AS util_ragsoc,
            ISNULL(intest.ragione_sociale,'') AS intest_ragsoc,
            t2.km,
            'infinity' AS dms
        ";

        $this->query.=" FROM ".$this->tabelle['TDO_CLI']->getTabName()." AS t1";
        $this->query.=" INNER JOIN  ".$this->tabelle['MDM_CLI_VEICOLI']->getTabName()." AS t2 ON t1.anno=t2.anno AND t1.data_doc=t2.data_doc AND t1.tipo_doc=t2.tipo_doc AND t1.num_doc=t2.numero_doc";
        $this->query.=" LEFT JOIN ".$this->tabelle['TDO_PRE']->getTabName()." AS t3 ON t1.order_id=t3.order_id";
        $this->query.=" LEFT JOIN ".$this->tabelle['TIPI_DOC']->getTabName()." AS t6 ON t1.tipo_doc=t6.codice";
        $this->query.=" LEFT JOIN (
                SELECT
                a.matricola,
                b.id_utente
                FROM ".$this->tabelle['O_OPERAI']->getTabName()." AS a
                INNER JOIN ".$this->tabelle['UTENTI']->getTabName()." AS b ON a.id_utenti=b.id_utenti
            ) AS t5 ON t1.acc_code=t5.matricola
        ";
        $this->query.=" LEFT JOIN ".$this->viste['CLIENTI']." AS intest ON CAST(isnull(t1.id_cliente,0) AS int)=intest.codice_cliente";
        $this->query.=" LEFT JOIN ".$this->tabelle['ANAGRAFICA']->getTabName()." AS util ON CAST(isnull(t1.codice_contatto,0) AS int)=util.id_anagrafica";

        $this->query.=" WHERE t2.id_veicolo='".$arr['rif']."' AND isnull(td_fatt,'')!='XO'";

        $this->query.=" ORDER BY CONVERT(varchar(8),isnull(t1.data_iniziolavori,t1.data_creazione),112),t1.id_documento";

        return true;

    }

    function getOccupazioneAgenda($arr) {

        $this->query="SELECT    
            SUM(a.tempo_stimato) as ore

            FROM ".$this->tabelle['MDM_PRE_INC']->getTabName()." as a
            INNER JOIN ".$this->tabelle['TDO_PRE']->getTabName()." as b on a.anno=b.anno AND a.id_cliente=b.id_cliente AND a.data_doc=b.data_doc AND a.tipo_doc=b.tipo_doc AND a.numero_doc=b.num_doc
            LEFT JOIN ".$this->tabelle['TIPI_DOC']->getTabName()." as c on a.tipo_doc=c.codice
            WHERE CONVERT(varchar(8),coalesce(a.data_inizio,b.data_prenotazione),112)>='".$arr['inizio']."' AND CONVERT(varchar(8),coalesce(a.data_inizio,b.data_prenotazione),112)<='".$arr['fine']."' AND c.punto_vendita IN (".substr($arr['reparti'],0,-1).")
        ";

        return true;
    }

    function fatturato_S($arr) {

        $this->query="WITH lista AS ( ";

        $this->query.="SELECT 
            t2.id_documento as rif,
            convert (varchar(23),t2.data_creazione,121) as pratica,
            CASE
                WHEN t2.tipo_mov='LO01' OR t2.tipo_mov='LO02' OR t2.tipo_mov='LO03' OR t2.tipo_mov='LO04' OR t2.tipo_mov='LO05' OR t2.tipo_mov='LO42' OR t2.tipo_mov='LO44' OR t2.tipo_mov='LO45' THEN 'OOP'
                WHEN t2.tipo_mov='LI01' OR t2.tipo_mov='LI02' OR t2.tipo_mov='LI03' OR t2.tipo_mov='LI04' OR t2.tipo_mov='LI05' OR t2.tipo_mov='LI42' OR t2.tipo_mov='LI44' OR t2.tipo_mov='LI45' THEN 'OOA'
                WHEN t2.tipo_mov='LF01' OR t2.tipo_mov='LF02' OR t2.tipo_mov='LF03' OR t2.tipo_mov='LF04' OR t2.tipo_mov='LF42' OR t2.tipo_mov='LF44' THEN 'OOPN'
            END AS cod_movimento,
            CONVERT(varchar(8),t2.data_fatt,112) AS d_fatt,
            t1.t_riga as ind_tipo_riga,
            t1.tipo_riga,
            t1.precodice as cod_tipo_articolo,
            isnull(t1.articolo,'') as cod_articolo,
            'S' AS flag_prelevato,
            t1.gr as cod_categoria_vendita,
            t1.famiglia,
            isnull(t1.operazione,'') as cod_operazione,
            isnull(t1.varie,'') as cod_varie,
            t1.descrizione,
            t1.qta,
            t1.unita,
            t1.prezzo as listino,
            t1.prc_sconto,
            t1.importo,
            t1.costo,
            t1.ivato,
            t1.cod_iva,
            isnull(t1.perciva,0) as prc_iva,
            t1.lam,
            t1.id_riga as riga,
            t1.codice_carico as acarico,
            '' AS cg,
            CASE 
                WHEN t2.data_fatt is null THEN 'S'
                ELSE 'N'
            END as ind_chiuso,
            isnull(t3.punto_vendita,'') AS cod_officina,
            isnull(t2.acc_code,'') AS cod_accettatore,
            t4.nome AS des_accettatore,
            isnull(t6.id_utente,'') AS cod_utente,
            isnull(vei.km,0) AS km,
            vei.id_veicolo,
            CASE
                WHEN vei.cod_marca IN ('A','C','N','S','P','V','U') THEN vei.cod_marca
                ELSE 'X'
            END AS marca_veicolo,
            vei.cod_modello AS modello,
            t5.targa AS mat_targa,
            t5.telaio AS mat_telaio,
            CASE
                WHEN t5.cod_modello is null THEN t5.descrizione_aggiuntiva
                ELSE ofmod.descrizione
            END AS des_veicolo,
            isnull(CONVERT(varchar(8),t5.data_inizio_garanzia,112),'') AS d_consegna,
            t2.id_cliente,
            ISNULL(intest.ragione_sociale,'') AS intest_ragsoc,
            coalesce(dba.fn_get_last_email(intest.id_anagrafica,0,0,1),dba.fn_get_last_email(intest.id_anagrafica,0,0,2),'') as intest_mail,
            isnull(dba.gab_fn_telefoni_anagrafica(intest.id_anagrafica),'') AS intest_tel,
            ISNULL(util.rag_soc_1,'') AS util_ragsoc,
            coalesce(dba.fn_get_last_email(util.id_anagrafica,0,0,1),dba.fn_get_last_email(util.id_anagrafica,0,0,2),'') as util_mail,
            isnull(dba.gab_fn_telefoni_anagrafica(util.id_anagrafica),'') AS util_tel,
            dba.fn_gab_CONCAT_cli_lams(t2.anno,t2.id_cliente,t2.data_doc,t2.tipo_doc,t2.num_doc) AS des_riga,
            'infinity' as dms
        ";

        $this->query.=$this->cliBlock();

        $this->query.=" INNER JOIN ".$this->tabelle['TDO_CLI']->getTabName()." as t2 ON t1.anno=t2.anno AND t1.id_cliente=t2.id_cliente AND t1.data_doc=t2.data_doc AND t1.tipo_doc=t2.tipo_doc AND t1.numero_doc=t2.num_doc";
        $this->query.=" INNER JOIN ".$this->tabelle['MDM_CLI_VEICOLI']->getTabName()." as vei ON vei.anno=t2.anno AND vei.id_cliente=t2.id_cliente AND vei.data_doc=t2.data_doc AND vei.tipo_doc=t2.tipo_doc AND vei.numero_doc=t2.num_doc";
        $this->query.=" INNER JOIN ".$this->tabelle['TIPI_DOC']->getTabName()." as t3 ON t1.tipo_doc=t3.codice AND t3.punto_vendita='".$arr['officina']."'";
        $this->query.=" LEFT JOIN ".$this->tabelle['O_OPERAI']->getTabName()." as t4 ON t2.acc_code=t4.matricola";
        $this->query.=" LEFT JOIN ".$this->tabelle['UTENTI']->getTabName()." as t6 ON t4.id_utenti=t6.id_utenti";
        $this->query.=" LEFT JOIN ".$this->tabelle['OFF_VEICOLI']->getTabName()." as t5 ON vei.id_veicolo=t5.id_veicolo";
        $this->query.=" LEFT JOIN ".$this->tabelle['OFF_MODELLI']->getTabName()." AS ofmod ON t5.cod_modello=ofmod.cod_modello AND t5.cod_marca=ofmod.cod_marca AND ofmod.livello='4'";
        $this->query.=" LEFT JOIN ".$this->viste['CLIENTI']." AS intest ON t2.id_cliente=intest.codice_cliente";
        $this->query.=" LEFT JOIN ".$this->tabelle['ANAGRAFICA']->getTabName()." AS util ON t2.codice_contatto=util.id_anagrafica";

        //valutando data_fatt è automatico che si guarda SOLO i documenti CHIUSI
        $this->query.=" WHERE ISNULL(CONVERT(varchar(8),t2.data_fatt,112),'')>='".$arr['inizio']."' AND CONVERT(varchar(8),t2.data_fatt,112)<='".$arr['fine']."' AND isnull(t2.td_fatt,'')!='XO'";
        /*if (isset($arr['marche'])) {
            $this->query.=" AND vei.cod_marca IN (".substr($arr['marche'],0,-1).")";
        }*/

        //$this->query.=" ORDER BY t2.data_fatt,t2.id_documento,t1.t_riga";
        $this->query.=" ORDER BY t2.id_documento,t1.t_riga";

        $this->query.=") SELECT lista.* FROM lista";
        /*$this->query.=" LEFT JOIN (
            SELECT mat_telaio, COUNT(*) AS conta
            FROM lista
            GROUP BY rif,mat_telaio
        ) AS conteggio_telai
        ON lista.rif = conteggio_telai.rif";*/

        return true;

    }

    function getPraticaRif($a) {
        //recupera i riferimenti delle commesse di una pratica ($pren=S=prenotazione)

        $this->query="SELECT
            t1.id_documento AS rif,
            '".$a['pren']."' AS pren
        ";

        if ($a['pren']=='S') {
            $this->query.=" FROM ".$this->tabelle['TDO_PRE']->getTabName()." AS t1";
        }
        else {
            $this->query.=" FROM ".$this->tabelle['TDO_CLI']->getTabName()." AS t1";
        }

        $this->query.=" WHERE convert (varchar(23),t1.data_creazione,121)='".$a['pratica']."'";

        return true;
    }

    function setRiconPratica($a) {

        $this->query="UPDATE ".$this->tabelle['TDO_CLI']->getTabName()."
            SET data_prevcons='".$a['d']."',
            ora_prevcons='".$a['ora']."'
        ";

        $this->query.=" WHERE convert (varchar(23),data_creazione,121)='".$a['pratica']."'";

        return true;
    }

    function getContrattoInfinity($arg) {
        //completa le informazioni sui contratti raccolte leggendo le prenotazioni e gli odl dai dms diversi da infinity

        $this->query="if exists (SELECT * FROM dba.N_DETTAGLIO_CONTRATTO AS cont LEFT JOIN dba.N_VEICOLI AS c ON cont.id_veicolo=c.id_veicolo LEFT JOIN dba.N_TESTATA_CONTRATTO AS test ON cont.id_contratto=test.id_contratto WHERE cont.telaio='".$arg['telaio']."' AND isnull(convert(varchar(8),c.data_uscita,112),'')='')";

        //if ($arg['tipo']=='nuovo') {
            $this->query.=" BEGIN SELECT
                test.numero_contratto,
                convert(varchar(8),test.data_contratto,112) as d_contratto,
                test.id_cliente AS id_cliente_contratto,
                ISNULL(n_cli.ragione_sociale,'') AS intest_contratto,
                test.status_contratto,
                c.id_veicolo AS id_nuovo,
                isnull(convert(varchar(8),c.data_uscita,112),'') as d_uscita
            ";

            $this->query.=" FROM ".$this->tabelle['N_DETTAGLIO_CONTRATTO']->getTabName()." AS cont";
            $this->query.=" LEFT JOIN ".$this->tabelle['N_VEICOLI']->getTabName()." AS c ON cont.id_veicolo=c.id_veicolo";
            $this->query.=" LEFT JOIN ".$this->tabelle['N_TESTATA_CONTRATTO']->getTabName()." AS test ON cont.id_contratto=test.id_contratto";
            $this->query.=" LEFT JOIN ".$this->viste['CLIENTI']." AS n_cli ON test.id_cliente=n_cli.codice_cliente";

            $this->query.=" WHERE cont.telaio='".$arg['telaio']."' AND isnull(test.status_contratto,'X')!='X'";

            $this->query.=' END';
        //}

        //if ($arg['tipo']=='usato') {

            $this->query.=' ELSE BEGIN';

            $this->query.=" SELECT
                test.numero_contratto,
                convert(varchar(8),test.data_contratto,112) as d_contratto,
                test.id_cliente AS id_cliente_contratto,
                ISNULL(u_cli.ragione_sociale,'') AS intest_contratto,
                test.status_contratto,
                c.usato AS id_usato,
                isnull(convert(varchar(8),c.data_consegna,112),'') as d_uscita
            ";
            
            $this->query.=" FROM ".$this->tabelle['DETTAGLIO_CONTRATTO']->getTabName()." AS cont";
            $this->query.=" LEFT JOIN ".$this->tabelle['VEICOLI_USATI']->getTabName()." AS c ON cont.veicolo=c.usato";
            $this->query.=" LEFT JOIN ".$this->tabelle['TESTATA_CONTRATTO']->getTabName()." AS test ON cont.id_contratto=test.id_contratto";
            $this->query.=" LEFT JOIN ".$this->viste['CLIENTI']." AS u_cli ON test.id_cliente=u_cli.codice_cliente";

            $this->query.=" WHERE cont.telaio='".$arg['telaio']."' AND isnull(convert(varchar(8),c.data_consegna,112),'')='' AND isnull(test.status_contratto,'X')!='X'";

            $this->query.=' END';
        //}

        return true;

    }

    function richiesteMateriale($arg) {

        if ($arg['pren']=='S') $this->richiestePre($arg);
        else $this->richiesteCli($arg);

        return true;
    }

    function richiestePre($arg) {

        $this->query="SELECT
            ric.status,
            ric.precodice,
            ric.articolo,
            ric.descrizione,
            ric.vu_lordo AS listino,
            ric.sconti_riga1 AS sconto1,
            ric.sconti_riga2 AS sconto2,
            ric.quantita AS quantita_ric,
            ric.id_prenotazione AS rif,
            ric.id_riga AS riga,
            precli.id_inconveniente AS lam,
            tocli.id_documento AS id_documento_cliente,
            ocli.id_ordine AS id_ordine_cliente,
            ric.id_richiesta AS id_richiesta,
            case when ocli.id_ordine_for is null then 'ST'
            when ric.id_commessa is not null then 'CO'
            when ric.id_prenotazione is not null then 'PO'
            when ric.id_preventivo is not null then 'PR'
            when UPPER(td.provenienza_ordine) = 'OFF' then 'CO' else 'OC' end AS canale,
            tocli.tipo_doc AS tipo_doc_ordcli,
            tocli.data_doc AS data_doc_ordcli,
            isnull(tocli.num_doc,0) AS num_doc_ordcli,
            tocli.anno AS anno_ordcli,
            tocli.id_cliente AS id_cliente_ordcli,
            ocli.id_riga AS id_riga_ordcli,
            ocli.deposito AS deposito_ordcli,
            ocli.quantita AS quantita_ordcli,
            ocli.qta_eva AS qta_eva_ordcli,
            ocli.qta_dev AS qta_dev_ordcli,
            ocli.deposito,
            gia.esist_att AS giacenza,
            gia.qta_imp AS impegnato,
            isnull(ocli.id_ordine_for,0) AS id_ordine_for,
            isnull(ofor.quantita,0) AS quantita_ordfor,
            isnull(ofor.qta_eva,0) AS qta_eva_ordfor,
            isnull(ofor.qta_dev,0) AS qta_dev_ordfor
        ";

        $this->query.=" FROM ".$this->tabelle['OFF_RICHIESTE']->getTabName()." AS ric";
        $this->query.=" LEFT JOIN ".$this->tabelle['ORD_CLI']->getTabName()." AS ocli ON ric.id_ordine_cli=ocli.id_ordine AND ocli.evssld_manuale=0";
        $this->query.=" LEFT JOIN ".$this->tabelle['TORD_CLI']->getTabName()." AS tocli ON tocli.tipo_doc=ocli.tipo_doc AND tocli.num_doc=ocli.numero_doc AND tocli.data_doc=ocli.data_doc AND tocli.anno=ocli.anno AND tocli.id_cliente=ocli.id_cliente";
        $this->query.=" LEFT JOIN ".$this->tabelle['TDO_PRE']->getTabName()." AS pre ON  ric.id_prenotazione=pre.id_documento";
        $this->query.=" INNER JOIN ".$this->tabelle['MDM_PRE_CLI']->getTabName()." AS precli ON precli.tipo_doc=pre.tipo_doc AND precli.numero_doc=pre.num_doc AND precli.data_doc=pre.data_doc AND precli.anno=pre.anno AND precli.id_cliente=pre.id_cliente AND ric.id_riga=precli.id_riga";
        $this->query.=" LEFT JOIN ".$this->tabelle['TIPI_DOC']->getTabName()." AS td ON tocli.tipo_doc=td.codice";
        $this->query.=" LEFT JOIN ".$this->tabelle['ORD_FOR']->getTabName()." AS ofor ON ocli.id_ordine_for=ofor.id_ordine";
        $this->query.=" LEFT JOIN ".$this->tabelle['GIACENZE']->getTabName()." AS gia ON ocli.precodice=gia.precodice AND ocli.articolo=gia.articolo AND ocli.deposito=gia.deposito AND ocli.anno=gia.anno";

        $this->query.=" WHERE ric.id_prenotazione='".$arg['rif']."' AND ric.precodice!='ZZ'";

    }

    function richiesteCli($arg) {

        $this->query="SELECT
            ric.precodice,
            ric.articolo,
            ric.descrizione,
            ric.vu_lordo AS listino,
            ric.sconti_riga1 AS sconto1,
            ric.sconti_riga2 AS sconto2,
            ric.quantita AS quantita_ric,
            ric.id_commessa AS rif,
            ric.id_riga AS riga,
            ric.id_inconveniente AS lam,
            tocli.id_documento AS id_documento_cliente,
            ocli.id_ordine AS id_ordine_cliente,
            ric.id_richiesta AS id_richiesta,
            case when ocli.id_ordine_for is null then 'ST'
            when ric.id_commessa is not null then 'CO'
            when ric.id_prenotazione is not null then 'PO'
            when ric.id_preventivo is not null then 'PR'
            when UPPER(td.provenienza_ordine) = 'OFF' then 'CO' else 'OC' end AS canale,
            tocli.tipo_doc AS tipo_doc_ordcli,
            tocli.data_doc AS data_doc_ordcli,
            isnull(tocli.num_doc,0) AS num_doc_ordcli,
            tocli.anno AS anno_ordcli,
            tocli.id_cliente AS id_cliente_ordcli,
            ocli.id_riga AS id_riga_ordcli,
            ocli.deposito AS deposito_ordcli,
            ocli.quantita AS quantita_ordcli,
            ocli.qta_eva AS qta_eva_ordcli,
            ocli.qta_dev AS qta_dev_ordcli,
            ocli.deposito,
            gia.esist_att AS giacenza,
            gia.qta_imp AS impegnato,
            isnull(ocli.id_ordine_for,0) AS id_ordine_for,
            isnull(ofor.quantita,0) AS quantita_ordfor,
            isnull(ofor.qta_eva,0) AS qta_eva_ordfor,
            isnull(ofor.qta_dev,0) AS qta_dev_ordfor
        ";

        $this->query.=" FROM ".$this->tabelle['OFF_RICHIESTE']->getTabName()." AS ric";
        $this->query.=" LEFT JOIN ".$this->tabelle['ORD_CLI']->getTabName()." AS ocli ON ric.id_ordine_cli=ocli.id_ordine";
        $this->query.=" LEFT JOIN ".$this->tabelle['TORD_CLI']->getTabName()." AS tocli ON tocli.tipo_doc=ocli.tipo_doc AND tocli.num_doc=ocli.numero_doc AND tocli.data_doc=ocli.data_doc AND tocli.anno=ocli.anno AND tocli.id_cliente=ocli.id_cliente";
        $this->query.=" LEFT JOIN ".$this->tabelle['TDO_CLI']->getTabName()." AS pre ON  ric.id_commessa=pre.id_documento";
        $this->query.=" LEFT JOIN ".$this->tabelle['TIPI_DOC']->getTabName()." AS td ON tocli.tipo_doc=td.codice";
        $this->query.=" LEFT JOIN ".$this->tabelle['ORD_FOR']->getTabName()." AS ofor ON ocli.id_ordine_for=ofor.id_ordine";
        $this->query.=" LEFT JOIN ".$this->tabelle['GIACENZE']->getTabName()." AS gia ON ocli.precodice=gia.precodice AND ocli.articolo=gia.articolo AND ocli.deposito=gia.deposito AND ocli.anno=gia.anno";

        $this->query.=" WHERE ric.id_commessa='".$arg['rif']."' AND ric.precodice!='ZZ'";

        return true;
        
    }

    function editLamTxt($arr) {

        if ($arr['pren']=='S') {
            $this->query="UPDATE dba.mdm_pre_inc SET a.descr_inconveniente='".str_replace("'","''",$arr['txt'])."'";
            
            $this->query.=" FROM dba.mdm_pre_inc as a INNER JOIN dba.tdo_pre as b on a.anno=b.anno AND a.data_doc=b.data_doc AND a.id_cliente=b.id_cliente AND a.tipo_doc=b.tipo_doc AND a.numero_doc=b.num_doc";
            $this->query.=" WHERE b.id_documento='".$arr['rif']."' AND a.id_riga='".$arr['lam']."'";
        }

        return true;
    }

    /////////////////////////////////////////////////////////////////////////

    function allineaDms($arr) {
        return false;
    }

    function ultimaRevisione($arr) {
        //verifica la scadenza in base alla fattura della revisione
        //verifica se la vettura è stata nel frattempo ritirata dall'azienda

        $this->query="SELECT
            isnull(vau.usato,0) AS usato,
            convert(varchar(8),vau.data_arrivo,112) as d_arrivo,
            convert(varchar(8),vau.data_revisione,112) as d_revisione,
            isnull(convert(varchar(8),vau.data_consegna,112),'') as d_uscita,
            b.id_documento,
            isnull(convert(varchar(8),b.data_fatt,112),'') as d,
            vei.targa AS targa,
            vei.telaio AS telaio,
            vei.cod_modello AS cod_veicolo,
            ISNULL(util.rag_soc_1,'') AS util_ragsoc,
            pri_util.consenso_marketing_dealer as mkt_util,
            coalesce(dba.fn_get_last_email(util.id_anagrafica,0,0,1),dba.fn_get_last_email(util.id_anagrafica,0,0,2),'') as util_mail,
            isnull(dba.gab_fn_telefoni_anagrafica(util.id_anagrafica),'') AS util_tel,
            util.indirizzo as util_indirizzo,
            util.localita as util_localita,
            util.cap as util_cap,
            util.provincia as util_provincia,
            ISNULL(intest.ragione_sociale,'') AS intest_ragsoc,
            pri_intest.consenso_marketing_dealer as mkt_intest,
            coalesce(dba.fn_get_last_email(intest.id_anagrafica,0,0,1),dba.fn_get_last_email(intest.id_anagrafica,0,0,2),'') as intest_mail,
            isnull(dba.gab_fn_telefoni_anagrafica(intest.id_anagrafica),'') AS intest_tel,
            intest.codice_cliente AS intest_codice,
            intest.indirizzo as intest_indirizzo,
            intest.localita as intest_localita,
            intest.cap as intest_cap,
            intest.provincia as intest_provincia,
            ISNULL(u_cli.ragione_sociale,'') AS u_cli_ragsoc,
            pri_u_cli.consenso_marketing_dealer as mkt_u_cli,
            coalesce(dba.fn_get_last_email(u_cli.id_anagrafica,0,0,1),dba.fn_get_last_email(u_cli.id_anagrafica,0,0,2),'') as u_cli_mail,
            isnull(dba.gab_fn_telefoni_anagrafica(u_cli.id_anagrafica),'') AS u_cli_tel,
            u_cli.indirizzo as u_indirizzo,
            u_cli.localita as u_localita,
            u_cli.cap as u_cap,
            u_cli.provincia as u_provincia
        ";

        $this->query.=" FROM ".$this->tabelle['MDM_CLI_LAV']->getTabName()." AS a";
        $this->query.=" INNER JOIN ".$this->tabelle['TDO_CLI']->getTabName()." AS b on a.anno=b.anno AND a.data_doc=b.data_doc AND a.numero_doc=b.num_doc AND a.tipo_doc=b.tipo_doc AND a.id_cliente=b.id_cliente";
        $this->query.=" INNER JOIN ".$this->tabelle['MDM_CLI_VEICOLI']->getTabName()." AS t4 ON b.anno=t4.anno AND b.id_cliente=t4.id_cliente AND b.data_doc=t4.data_doc AND b.tipo_doc=t4.tipo_doc AND b.num_doc=t4.numero_doc";
        $this->query.=" INNER JOIN ".$this->tabelle['OFF_VEICOLI']->getTabName()." AS vei ON t4.id_veicolo=vei.id_veicolo";
        $this->query.=" LEFT JOIN ".$this->viste['CLIENTI']." AS intest ON b.id_cliente=intest.codice_cliente";
        $this->query.=" LEFT JOIN ".$this->tabelle['ANAGRAFICA']->getTabName()." AS util ON b.codice_contatto=util.id_anagrafica";
        $this->query.=" LEFT JOIN ".$this->tabelle['VEICOLI_USATI']->getTabName()." AS vau ON vei.telaio=vau.telaio AND vau.data_arrivo>b.data_fatt";
        $this->query.=" LEFT JOIN ".$this->tabelle['DETTAGLIO_CONTRATTO']->getTabName()." AS cont ON vau.usato=cont.veicolo";
        $this->query.=" LEFT JOIN ".$this->tabelle['TESTATA_CONTRATTO']->getTabName()." AS test ON cont.id_contratto=test.id_contratto";
        $this->query.=" LEFT JOIN ".$this->viste['CLIENTI']." AS u_cli ON test.id_cliente=u_cli.codice_cliente";
        $this->query.=" LEFT JOIN (
            SELECT 
            id_anagrafica,
            max(id_consenso) as consenso
            from dba.consenso_privacy
            group by id_anagrafica
        ) as id_pri_util ON util.id_anagrafica=id_pri_util.id_anagrafica";
        $this->query.=" LEFT JOIN (
            SELECT 
            id_anagrafica,
            max(id_consenso) as consenso
            from dba.consenso_privacy
            group by id_anagrafica
        ) as id_pri_intest ON intest.id_anagrafica=id_pri_intest.id_anagrafica";
        $this->query.=" LEFT JOIN (
            SELECT 
            id_anagrafica,
            max(id_consenso) as consenso
            from dba.consenso_privacy
            group by id_anagrafica
        ) as id_pri_u_cli ON u_cli.id_anagrafica=id_pri_u_cli.id_anagrafica";
        $this->query.=" LEFT JOIN dba.consenso_privacy as pri_util ON pri_util.id_consenso=id_pri_util.consenso";
        $this->query.=" LEFT JOIN dba.consenso_privacy as pri_intest ON pri_intest.id_consenso=id_pri_intest.consenso";
        $this->query.=" LEFT JOIN dba.consenso_privacy as pri_u_cli ON pri_u_cli.id_consenso=id_pri_u_cli.consenso";

        $this->query.=" WHERE a.lavorazione='REV' AND b.td_fatt!='XO' AND isnull(convert(varchar(8),b.data_fatt,112),'')!='' AND isnull(convert(varchar(8),b.data_fatt,112),'')<='".$arr['fine']."' AND isnull(convert(varchar(8),b.data_fatt,112),'')>='".$arr['inizio']."'";
        $this->query.=" ORDER BY telaio,d_uscita DESC";

        return true;
    }

    function getUltimoPag($arr) {

        $this->query="SELECT
            vau.usato,
            convert(varchar(8),vau.data_arrivo,112) as d_arrivo,
            res.*,
            dba.fn_gab_CONCAT_cli_lams(res.anno,res.id_cliente,res.data_doc,res.tipo_doc,res.num_doc) AS lams
            FROM (
                SELECT
                doc.anno,
                doc.data_doc,
                doc.tipo_doc,
                doc.num_doc,
                doc.id_documento,
                doc.data_fatt,
                convert(varchar(8),doc.data_fatt,112) AS d_fatt,
                vei.id_veicolo,
                v.targa,
                v.telaio,
                v.cod_marca,
                mvei.descrizione AS des_veicolo,
                mvei.km,
                doc.id_cliente,
                ISNULL(cli.ragione_sociale,'') AS cli_ragsoc,
                coalesce(dba.fn_get_last_email(cli.id_anagrafica,0,0,1),dba.fn_get_last_email(cli.id_anagrafica,0,0,2),'') as cli_mail,
                isnull(dba.gab_fn_telefoni_anagrafica(cli.id_anagrafica),'') AS cli_tel,
                pri.consenso_marketing_dealer AS mkt_cli,
                doc.codice_contatto,
                ISNULL(util.rag_soc_1,'') AS util_ragsoc,
                coalesce(dba.fn_get_last_email(util.id_anagrafica,0,0,1),dba.fn_get_last_email(util.id_anagrafica,0,0,2),'') as util_mail,
                isnull(dba.gab_fn_telefoni_anagrafica(util.id_anagrafica),'') AS util_tel,
                pri_util.consenso_marketing_dealer AS mkt_util,
                doc.id_fatt AS dflag,
                vei.id_fatt AS vflag
                
                FROM (
                    SELECT
                    t4.id_veicolo,
                    (CAST(t1.anno AS varchar(4))+t1.td_fatt+CAST(t1.num_fatt AS varchar(8))) as id_fatt,
                    t1.*
                    FROM ".$this->tabelle['TDO_CLI']->getTabName()." AS t1
                    INNER JOIN ".$this->tabelle['MDM_CLI_VEICOLI']->getTabName()." AS t4 ON t1.anno=t4.anno AND t1.id_cliente=t4.id_cliente AND t1.data_doc=t4.data_doc AND t1.tipo_doc=t4.tipo_doc AND t1.num_doc=t4.numero_doc
                    WHERE convert(varchar(8),t1.data_fatt,112)<='".$arr['fine']."' AND convert(varchar(8),t1.data_fatt,112)>='".$arr['inizio']."' AND isnull(t1.td_fatt,'')!='XO'
                )AS doc
                INNER JOIN (
                    SELECT
                    t4.id_veicolo,
                    max(CAST(t1.anno AS varchar(4))+t1.td_fatt+CAST(t1.num_fatt AS varchar(8))) as id_fatt
                    FROM ".$this->tabelle['TDO_CLI']->getTabName()." AS t1
                    INNER JOIN ".$this->tabelle['MDM_CLI_VEICOLI']->getTabName()." AS t4 ON t1.anno=t4.anno AND t1.id_cliente=t4.id_cliente AND t1.data_doc=t4.data_doc AND t1.tipo_doc=t4.tipo_doc AND t1.num_doc=t4.numero_doc
                    WHERE isnull(t1.td_fatt,'')!='XO' AND t1.data_fatt IS NOT NULL
                    GROUP BY t4.id_veicolo
                )AS vei ON doc.id_veicolo=vei.id_veicolo
                
                INNER JOIN ".$this->tabelle['OFF_VEICOLI']->getTabName()." AS v ON vei.id_veicolo=v.id_veicolo
                INNER JOIN ".$this->tabelle['MDM_CLI_VEICOLI']->getTabName()." AS mvei ON doc.anno=mvei.anno AND doc.id_cliente=mvei.id_cliente AND doc.data_doc=mvei.data_doc AND doc.tipo_doc=mvei.tipo_doc AND doc.num_doc=mvei.numero_doc
                
                LEFT JOIN dba.CLIENTI AS cli ON doc.id_cliente=cli.codice_cliente
                LEFT JOIN (
                    SELECT 
                    id_anagrafica,
                    max(id_consenso) as consenso
                    FROM dba.consenso_privacy
                    GROUP BY id_anagrafica
                ) as id_pri ON cli.id_anagrafica=id_pri.id_anagrafica
                LEFT JOIN dba.consenso_privacy as pri ON pri.id_consenso=id_pri.consenso
                
                LEFT JOIN dba.ANAGRAFICA AS util ON doc.codice_contatto=util.id_anagrafica
                LEFT JOIN (
                    SELECT 
                    id_anagrafica,
                    max(id_consenso) as consenso
                    FROM dba.consenso_privacy
                    GROUP BY id_anagrafica
                ) as id_pri_util ON util.id_anagrafica=id_pri_util.id_anagrafica
                LEFT JOIN dba.consenso_privacy as pri_util ON pri_util.id_consenso=id_pri_util.consenso
                
                WHERE doc.id_fatt=vei.id_fatt AND substr(doc.tipo_doc,1,2)='LO'
            ) AS res

            LEFT JOIN ".$this->tabelle['VEICOLI_USATI']->getTabName()." AS vau ON res.telaio=vau.telaio AND vau.data_arrivo>res.data_fatt

            ORDER BY d_fatt,res.telaio,res.targa
        ";

        return true;
    }

    function getPassConsegna($arr) {

        $this->query="SELECT 
            vau.usato,
            convert(varchar(8),vau.data_arrivo,112) as d_arrivo,
            ag.descrizione AS vend_nuovo,
            vei.id_veicolo,
            vei.cod_marca,
            vei.targa,
            vei.telaio,
            vei.cod_modello,
            CONVERT(varchar(8),vei.data_inizio_garanzia,112) AS d_cons,
            vei.codice_cliente,
            ISNULL(cli.ragione_sociale,'') AS cli_ragsoc,
            cli.localita+' ('+cli.provincia+')' AS cli_localita,
            pri.consenso_marketing_dealer AS mkt_cli,
            coalesce(dba.fn_get_last_email(cli.id_anagrafica,0,0,1),dba.fn_get_last_email(cli.id_anagrafica,0,0,2),'') as intest_mail,
            isnull(dba.gab_fn_telefoni_anagrafica(cli.id_anagrafica),'') AS intest_tel,
            vei.codice_contatto,
            ISNULL(util.rag_soc_1,'') AS util_ragsoc,
            util.localita+' ('+util.provincia+')' AS util_localita,
            pri_util.consenso_marketing_dealer AS mkt_util,
            coalesce(dba.fn_get_last_email(util.id_anagrafica,0,0,1),dba.fn_get_last_email(util.id_anagrafica,0,0,2),'') as util_mail,
            isnull(dba.gab_fn_telefoni_anagrafica(util.id_anagrafica),'') AS util_tel,
            CASE
                WHEN vei.cod_modello is null THEN vei.descrizione_aggiuntiva
                ELSE ofmod.descrizione
            END AS des_veicolo, 
            isnull(doc.id_fatt,'') AS id_fatt,
            fatt.tipo_doc,
            CONVERT(varchar(8),fatt.data_fatt,112) AS d_fatt,
            doc.km,
            dba.fn_gab_CONCAT_cli_lams(fatt.anno,fatt.id_cliente,fatt.data_doc,fatt.tipo_doc,fatt.num_doc) AS lams
        ";

        $this->query.=" FROM ".$this->tabelle['OFF_VEICOLI']->getTabName()." AS vei";

        $this->query.=" LEFT JOIN ".$this->tabelle['OFF_MODELLI']->getTabName()." AS ofmod ON vei.cod_modello=ofmod.cod_modello AND vei.cod_marca=ofmod.cod_marca AND ofmod.livello='4'";

        $this->query.=" LEFT JOIN (
            SELECT
                t4.id_veicolo,
                max(CAST(t1.anno AS varchar(4))+t1.td_fatt+CAST(t1.num_fatt AS varchar(8))) as id_fatt,
                max(t4.km) AS km
                FROM ".$this->tabelle['TDO_CLI']->getTabName()." AS t1
                INNER JOIN ".$this->tabelle['MDM_CLI_VEICOLI']->getTabName()." AS t4 ON t1.anno=t4.anno AND t1.id_cliente=t4.id_cliente AND t1.data_doc=t4.data_doc AND t1.tipo_doc=t4.tipo_doc AND t1.num_doc=t4.numero_doc
                WHERE isnull(t1.td_fatt,'')!='XO' AND t1.data_fatt IS NOT NULL AND substr(t1.tipo_doc,1,2)='LO'
                GROUP BY t4.id_veicolo
            )AS doc ON doc.id_veicolo=vei.id_veicolo
        ";

        $this->query.=" LEFT JOIN ".$this->tabelle['TDO_CLI']->getTabName()." AS fatt ON CAST(fatt.anno AS varchar(4))+fatt.td_fatt+CAST(fatt.num_fatt AS varchar(8))=doc.id_fatt";

        $this->query.=" LEFT JOIN ".$this->tabelle['VEICOLI_USATI']->getTabName()." AS vau ON vei.telaio=vau.telaio AND vau.data_arrivo>fatt.data_fatt";

        $this->query.=" LEFT JOIN ".$this->viste['CLIENTI']." AS cli ON vei.codice_cliente=cli.codice_cliente";
        $this->query.=" LEFT JOIN (
            SELECT 
            id_anagrafica,
            max(id_consenso) as consenso
            FROM dba.consenso_privacy
            GROUP BY id_anagrafica
        ) as id_pri ON cli.id_anagrafica=id_pri.id_anagrafica
        LEFT JOIN dba.consenso_privacy as pri ON pri.id_consenso=id_pri.consenso";

        $this->query.=" LEFT JOIN ".$this->tabelle['ANAGRAFICA']->getTabName()." AS util ON vei.codice_contatto=util.id_anagrafica";
        $this->query.=" LEFT JOIN (
            SELECT 
            id_anagrafica,
            max(id_consenso) as consenso
            FROM dba.consenso_privacy
            GROUP BY id_anagrafica
        ) as id_pri_util ON util.id_anagrafica=id_pri_util.id_anagrafica
        LEFT JOIN dba.consenso_privacy as pri_util ON pri_util.id_consenso=id_pri_util.consenso";

        $this->query.=" LEFT JOIN ".$this->tabelle['N_DETTAGLIO_CONTRATTO']->getTabName()." AS cont ON vei.telaio=cont.telaio";
        $this->query.=" LEFT JOIN ".$this->tabelle['N_TESTATA_CONTRATTO']->getTabName()." AS test ON cont.id_contratto=test.id_contratto";
        $this->query.=" LEFT JOIN ".$this->tabelle['GRUPPO_AGENTI']->getTabName()." AS ag ON test.id_gruppo_agenti=ag.id_gruppo_agenti";

        $this->query.=" LEFT JOIN ( 
            SELECT
            max (id_contratto) AS id_contratto,
            telaio
            FROM ".$this->tabelle['N_DETTAGLIO_CONTRATTO']->getTabName()."
            group by telaio
        ) AS contratti ON vei.telaio=contratti.telaio";

        $this->query.=" WHERE vei.data_inizio_garanzia>='".$arr['inizio']."' AND vei.data_inizio_garanzia<='".$arr['fine']."' AND (isnull(contratti.id_contratto,0)=0 OR isnull(contratti.id_contratto,0)=test.id_contratto)";

        $this->query.=" ORDER BY vei.data_inizio_garanzia";

        return true;
    }

    function totFattCliente($arr) {

        $this->query="SELECT
            sum(CASE WHEN tipo_mov IN ('LO01','LO02','LO03','LO04','LO05','LO42','LO44','LO45') THEN importo_doc ELSE 0 END) AS tot,
            sum(CASE WHEN tipo_mov IN ('LF01','LF02','LF03','LF04','LF05','LF42','LF44','LF45') THEN importo_doc ELSE 0 END) AS nac
        ";

        $this->query.=" FROM ".$this->tabelle['TDO_CLI']->getTabName();

        $this->query.=" WHERE isnull(td_fatt,'')!='XO' AND id_cliente='".$arr['cliente']."'";
        
        if (isset($arr['d'])) $this->query.=" AND isnull(CONVERT(varchar(8),data_fatt,112),'')>'".$arr['d']."'";

        return true;
    }

    function getScontrillo($arg) {
        
        $this->query="SELECT
            t1.tipo_doc,
            t1.id_documento,
            t1.id_cliente,
            ISNULL(intest.ragione_sociale,'') AS intest_ragsoc,
            t1.importo_doc_iva AS importo,
            t2.descrizione AS desc_movimento,
            isnull(CONVERT(varchar(8),t1.data_fatt,112),'') AS d_fatt,
            t1.num_fatt,
            t2.deposito_default AS magazzino,
            isnull(t2.punto_vendita,'') AS officina,
            t2.gestione,
            isnull(t2.tipo_intervento,'') AS tipo_intervento,
            'infinity' AS dms
        ";

        $this->query.=" FROM ".$this->tabelle['TDO_CLI']->getTabName()." AS t1";
        $this->query.=" INNER JOIN ".$this->tabelle['TIPI_DOC']->getTabName()." AS t2 ON t1.tipo_doc=t2.codice";
        $this->query.=" LEFT JOIN ".$this->viste['CLIENTI']." AS intest ON t1.id_cliente=intest.codice_cliente";

        $this->query.=" WHERE t2.gestione IN ('O','M') AND isnull(t2.tipo_intervento,2)!=1 AND isnull(t1.td_fatt,'XO')!='XO' AND isnull(t1.td_bolla,'')='' 
            AND isnull(CONVERT(varchar(8),t1.data_fatt,112),'')>='".$arg['inizio']."' AND isnull(CONVERT(varchar(8),t1.data_fatt,112),'')<='".$arg['fine']."' 
            AND t1.importo_doc!=0
        ";

        if (isset($arg['officina'])) $this->query.=" AND t2.punto_vendita='".$arg['officina']."'";
        elseif (isset($arg['magazzino'])) $this->query.=" AND t2.deposito_default='".$arg['magazzino']."' AND isnull(t2.punto_vendita,'')=''";
        else return false;

        $this->query.=" ORDER BY t1.tipo_doc,t1.data_fatt,t1.num_fatt";

        //echo $this->query;

        return true;
    }

}

?>