<?php

include_once(DROOT.'/nebula/galileo/concerto/concerto_anagrafiche.php');
include_once(DROOT.'/nebula/galileo/concerto/concerto_veicoli.php');
include_once(DROOT.'/nebula/galileo/concerto/concerto_ricambi.php');

include_once(DROOT.'/nebula/galileo/concerto/tabs/GN_MOVTES.php');
include_once(DROOT.'/nebula/galileo/concerto/tabs/GN_MOVTES_OFF.php');
include_once(DROOT.'/nebula/galileo/concerto/tabs/GN_MOVTES_OFF_PRATICHE.php');
include_once(DROOT.'/nebula/galileo/concerto/tabs/GN_MOVTES_DOC.php');
include_once(DROOT.'/nebula/galileo/concerto/tabs/GN_MOVDET.php');
include_once(DROOT.'/nebula/galileo/concerto/tabs/GN_IVA.php');
include_once(DROOT.'/nebula/galileo/concerto/tabs/TB_OFF_OFFICINE.php');
include_once(DROOT.'/nebula/galileo/concerto/tabs/OF_RILTEM.php');
include_once(DROOT.'/nebula/galileo/concerto/tabs/MG_ANAART.php');
include_once(DROOT.'/nebula/galileo/concerto/tabs/TB_MAG_CAUMOV.php');
include_once(DROOT.'/nebula/galileo/concerto/tabs/TB_OFF_ACCETTATORI.php');
include_once(DROOT.'/nebula/galileo/concerto/tabs/GN_MOVDET_PRENOTAZIONI.php');

include_once(DROOT.'/nebula/galileo/gab500/tabs/MAESTRO_collaboratori.php');

include_once(DROOT.'/nebula/galileo/gab500/tabs/AVALON_lamextra.php');

class galileoConcertoODL extends galileoOps {

    function __construct() {

        $this->tabelle['GN_MOVTES']=new concerto_gnmovtes();
        $this->tabelle['GN_MOVTES_OFF']=new concerto_gnmovtesoff();
        $this->tabelle['GN_MOVTES_OFF_PRATICHE']=new concerto_gnmovtesoffpratiche();
        $this->tabelle['GN_MOVTES_DOC']=new concerto_gnmovtesdoc();
        $this->tabelle['GN_MOVDET']=new concerto_gnmovdet();
        $this->tabelle['GN_MOVDET_PRENOTAZIONI']=new concerto_gnmovdet_prenotazioni();
        $this->tabelle['GN_IVA']=new concerto_gniva();
        $this->tabelle['TB_OFF_OFFICINE']=new concerto_tboffofficine();
        $this->tabelle['OF_RILTEM']=new concerto_ofriltem();
        $this->tabelle['TB_MAG_CAUMOV']=new concerto_tbmagcaumov();
        $this->tabelle['TB_OFF_ACCETTATORI']=new concerto_tboffaccettatori();

        $this->tabelle['GN_ANAGRAFICHE']=new concerto_gnanagrafiche();
        $this->tabelle['CA_ANAGRAFICHE_WORKCAR_SOCIETA']=new concerto_caanagraficheworkcarsocieta();
        $this->tabelle['VE_ANAVEI']=new concerto_veanavei();
        $this->tabelle['TB_VEI_MARCHE']=new concerto_tbveimarche();

        $this->tabelle['MG_ANAART']=new concerto_mganaart();
		$this->tabelle['MG_RICHIESTE_DET']=new concerto_mgrichieste_det();
        $this->tabelle['MG_RICHIESTE']=new concerto_mgrichieste();
        $this->tabelle['MG_ANAART_SALDI']=new concerto_mganaart_saldi();
        $this->tabelle['MG_ORDINI_DET']=new concerto_mgordini_det();		

        $this->tabelle['MAESTRO_collaboratori']=new maestro_collaboratori();

        $this->tabelle['AVALON_lamextra']=new avalon_lamextra();

        $this->viste['LISTA_LAMENTATI']="[GC_AZI_GABELLINI_prod].[dbo].LISTA_LAMENTATI";

        //$this->link['anagrafiche']=new galileoConcertoAnagrafiche();
        //$this->link['veicoli']=new galileoConcertoVeicoli();

    }

    function getOdlHead($arg) {

        $this->query="SELECT
            t2.num_rif_movimento AS rif,
            t2.cod_officina,
            t2.cod_officina_prenotazione,
            CASE
                WHEN t4.ind_chiuso='S' THEN 'CH'
                WHEN t4.ind_chiuso='N' AND ISNULL(t2.num_commessa,0)!=0 THEN 'AP'
                ELSE 'XX'
            END AS cod_stato_commessa,
            ISNULL(t4.cod_movimento,'') AS cod_movimento,
            t2.num_commessa,
            t2.ind_preventivo,
            t2.cod_tipo_trasporto,
            t4.ind_chiuso,
            ISNULL( CONVERT(varchar(8),t4.dat_inserimento,112),'xxxxxxxx' ) AS d_inserimento,
            t4.cod_utente_inserimento,
            ISNULL( CONVERT(varchar(8),t2.dat_split,112),'xxxxxxxx' )+':'+SUBSTRING( ISNULL (CONVERT(varchar(5),t2.dat_split,114),'xx:xx' ),1,5) AS d_apertura,
            ISNULL( CONVERT(varchar(8),t2.dat_prenotazione,112),'xxxxxxxx' )+':'+SUBSTRING( ISNULL (CONVERT(varchar(5),t2.dat_prenotazione,114),'xx:xx' ),1,5) AS d_pren,
            ISNULL( CONVERT(varchar(8),t2.dat_promessa_consegna,112),'xxxxxxxx' )+':'+SUBSTRING( ISNULL (CONVERT(varchar(5),t2.dat_promessa_consegna,114),'xx:xx' ),1,5) AS d_ricon,
            ISNULL( CONVERT(varchar(8),t2.dat_entrata_veicolo,112),'xxxxxxxx' )+':'+SUBSTRING( ISNULL (CONVERT(varchar(5),t2.dat_entrata_veicolo,114),'xx:xx' ),1,5) AS d_entrata,
            ISNULL( CONVERT(varchar(8),t2.dat_fine_lavori,112),'xxxxxxxx' )+':'+SUBSTRING( ISNULL (CONVERT(varchar(5),t2.dat_fine_lavori,114),'xx:xx' ),1,5) AS d_fine,
            t2.num_rif_veicolo,
            t2.num_rif_veicolo_progressivo,
            vei.mat_telaio AS telaio,
            vei.mat_targa AS targa,
            vei.des_veicolo,
            vei.cod_marca,
            isnull(mar.des_marca,'') as des_marca,
            isnull(t2.cod_anagra_util,'') AS cod_anagra_util,
            isnull(t2.cod_anagra_intest,'') AS cod_anagra_intest,
            isnull(t2.cod_anagra_loc,'') AS cod_anagra_locat,
            isnull(t2.cod_anagra_fattura,'') AS cod_anagra_fatt,
            isnull(intest.des_ragsoc,'') as intest_ragsoc,
            isnull(util.des_ragsoc,'') as util_ragsoc,
            t2.cod_accettatore,
            isnull(t2.num_km,0) AS km,
            CASE
                WHEN ISNULL(t2.num_commessa,0)!=0 THEN 'N'
                ELSE 'S'
            END AS pren,
            'concerto' AS dms
        ";

        $this->query.=" FROM ".$this->tabelle['GN_MOVTES']->getTabName()." AS t4
            LEFT JOIN ".$this->tabelle['GN_MOVTES_OFF']->getTabName()." AS t2 ON t4.num_rif_movimento=t2.num_rif_movimento
            LEFT JOIN ".$this->tabelle['VE_ANAVEI']->getTabName()." AS vei ON t2.num_rif_veicolo=vei.num_rif_veicolo
            LEFT JOIN ".$this->tabelle['TB_VEI_MARCHE']->getTabName()." AS mar ON vei.cod_marca=mar.cod_marca
            LEFT JOIN ".$this->tabelle['GN_ANAGRAFICHE']->getTabName()." AS intest ON t2.cod_anagra_intest=intest.cod_anagra
            LEFT JOIN ".$this->tabelle['GN_ANAGRAFICHE']->getTabName()." AS util ON t2.cod_anagra_util=util.cod_anagra
        ";

        $this->query.=" WHERE t4.num_rif_movimento='".$arg['rif']."'";

        return true;

    }

    function getCliLamentati($arg) {
        return $this->getOdlLamentati($arg);
    }

    function getOdlLamentati($arg) {
        //restituisce i lamentati di un ordine di lavoro con l'intestazione dell'ordine stesso

        $this->query="SELECT
            odl.*,
            vei.cod_marca,
            isnull(mar.des_marca,'') as des_marca,
            vei.mat_targa,
            vei.mat_telaio,
            vei.cod_veicolo,
            vei.des_veicolo,
            vei.mat_motore,
            isnull(util.des_ragsoc,'') AS util_ragsoc,
            isnull(intest.des_ragsoc,'') AS intest_ragsoc,
            ISNULL(t9.pos,'') AS pos
        ";
     
        if ( (isset($arg['officina']) && ($arg['officina']=='PI' || $arg['officina']=='PU' || $arg['officina']=='PN') ) || (isset($arg['timeless']['officina']) && ($arg['timeless']['officina']=='PI' || $arg['timeless']['officina']=='PU' || $arg['timeless']['officina']=='PN') ) ) {
            
            $this->query.="
                ,isnull(t8.num_rif_contratto,'0') AS numero_contratto,
                t8.data_contratto as d_contratto,
                t8.cod_acquirente AS id_cliente_contratto,
                ISNULL(t10.des_ragsoc,'') AS intest_contratto,
                CASE
                    WHEN isnull(convert(varchar(8),t6.d_con,112),'')='' THEN 'A'
                    ELSE 'C'
                END AS status_contratto,
                isnull(convert(varchar(8),t6.d_con,112),'') as d_uscita,
                CASE
                    WHEN isnull(t8.num_rif_contratto,'')!='' AND isnull(convert(varchar(8),t6.d_con,112),'')='' THEN t8.num_rif_contratto
                    ELSE 0
                END AS id_nuovo
            ";
        }
        
        $this->query.="
            FROM (
        ";

            $this->dedalo();
            
            /*if (isset($arg['num_rif_movimento'])) {
                $this->query.=" AND t1.num_rif_movimento='".$arg['num_rif_movimento']."' AND t4.ind_chiuso='N' ";
            }
            else {
                $this->query.=" AND t4.ind_chiuso='N' ";
            }*/

            if (isset($arg['timeless'])) {

                if (isset($arg['timeless']['pratica'])) {
                    $this->query.=" AND pr.num_pratica='".$arg['timeless']['pratica']."'";
                }
                else {
                    $this->query.=" AND ( dp.d_ricon_pratica='xxxxxxxx:xx:xx' OR (substring(dp.d_ricon_pratica,1,8)>='".$arg['timeless']['inizio']."' AND (substring(dp.d_ricon_pratica,1,8)<='".$arg['timeless']['fine']."') ) )";
                    //$this->query.=" AND t4.ind_chiuso='N' ";
                    if (isset($arg['timeless']['officina'])) {
                        $this->query.=" AND t2.cod_officina='".$arg['timeless']['officina']."'";
                    }

                    if (isset($arg['timeless']['rc']) && $arg['timeless']['rc']!='') $this->query.=" AND t2.cod_accettatore='".$arg['timeless']['rc']."'";

                    if (isset($arg['timeless']['tipo'])) {
                        if ($arg['timeless']['tipo']=='chiusi')  $this->query.=" AND t4.ind_chiuso='S' ";
                        if ($arg['timeless']['tipo']=='aperti')  $this->query.=" AND t4.ind_chiuso='N' ";
                    }
                }
            }

            else {

                if (isset($arg['inizio']) && isset($arg['fine'])) {
                    $this->query.=" AND ISNULL(convert(varchar(8),t2.dat_entrata_veicolo,112),'')>='".$arg['inizio']."' AND ISNULL(convert(varchar(8),t2.dat_entrata_veicolo,112),'')<='".$arg['fine']."'";
                }
                elseif(isset($arg['num_rif_movimento'])) {
                    $this->query.=" AND t1.num_rif_movimento='".$arg['num_rif_movimento']."'";
                }
            
                if ($arg['tipo']=='chiusi')  $this->query.=" AND t4.ind_chiuso='S' ";
                if ($arg['tipo']=='aperti')  $this->query.=" AND t4.ind_chiuso='N' ";

                if (isset($arg['officina']) && $arg['officina']!='') $this->query.=" AND t2.cod_officina='".$arg['officina']."'";

                if (isset($arg['rc']) && $arg['rc']!='') $this->query.=" AND t2.cod_accettatore='".$arg['rc']."'";

                if(isset($arg['tipo_carico'])) $this->query.=" AND ll.inc_ca IN (".$arg['tipo_carico'].")";

                $this->query.=" AND isnull(t2.num_commessa,0)!=0";
            }

        $this->query.=") AS odl";

        $this->query.=" LEFT JOIN ".$this->tabelle['VE_ANAVEI']->getTabName()." AS vei ON odl.num_rif_veicolo=vei.num_rif_veicolo";

        $this->query.=" LEFT JOIN ".$this->tabelle['TB_VEI_MARCHE']->getTabName()." AS mar ON vei.cod_marca=mar.cod_marca";

        $this->query.=" LEFT JOIN ".$this->tabelle['GN_ANAGRAFICHE']->getTabName()." AS util ON odl.cod_anagra_util=util.cod_anagra";

        $this->query.=" LEFT JOIN ".$this->tabelle['GN_ANAGRAFICHE']->getTabName()." AS intest ON odl.cod_anagra_intest=intest.cod_anagra";

        $this->query.=" LEFT JOIN ".$this->tabelle['AVALON_lamextra']->getTabName()." as t9 on odl.rif=t9.rif AND odl.lam=t9.lam AND t9.dms='c'";

        if ( (isset($arg['officina']) && ($arg['officina']=='PI' || $arg['officina']=='PU' || $arg['officina']=='PN') ) || (isset($arg['timeless']['officina']) && ($arg['timeless']['officina']=='PI' || $arg['timeless']['officina']=='PU' || $arg['timeless']['officina']=='PN') ) ) {
            
            $this->query.="
                left join (
                    SELECT
                    t1.num_rif_veicolo,
                    t1.num_gestione,
                    t1.cod_natura_vendita as natura,
                    isnull(CONVERT(VARCHAR(8),t1.dat_arrivo_fisico,112),'') as d_arr,
                    isnull(CONVERT(VARCHAR(8),t1.dat_consegna,112),'') as d_con
                
                    FROM VE_anavei_ges as t1
                    WHERE (
                        SELECT count(*)
                        FROM VE_anavei_ges as t2
                        where t1.num_rif_veicolo=t2.num_rif_veicolo
                        and t2.num_gestione>t1.num_gestione
                    )=0
                    GROUP BY num_rif_veicolo,num_gestione,dat_arrivo_fisico,dat_consegna,cod_natura_vendita
                
                ) as t6 on vei.num_rif_veicolo=t6.num_rif_veicolo

                left join (
                    SELECT
                    t1.num_rif_contratto,
                    isnull(CONVERT(VARCHAR(8),t1.dat_contratto,112),'') as data_contratto,
                    t1.cod_intestatario,
                    t1.cod_acquirente,
                    t1.cod_venditore,
                    t1.num_rif_veicolo,
                    t1.num_gestione,
                    t1.ind_contratto
                    FROM ve_contra_tes as t1
                ) as t8 on t6.num_rif_veicolo=t8.num_rif_veicolo AND t6.num_gestione=t8.num_gestione AND t8.ind_contratto='S'

                LEFT JOIN ".$this->tabelle['GN_ANAGRAFICHE']->getTabName()." AS t10 ON t8.cod_acquirente=t10.cod_anagra
            ";
        }

        if (isset($arg['timeless'])) {

            if ($arg['timeless']['officina']=='PI' || $arg['timeless']['officina']=='PU' || $arg['timeless']['officina']=='PN') {
                //APPRIP serve per non prendere gli odl che hanno un contratto ma sono aperti SOLO NON INTERNI
                $this->query.=" WHERE isnull(t8.num_rif_contratto,'0')!='0' AND isnull(convert(varchar(8),t6.d_con,112),'')='' AND odl.apprip>0";
            }
        }
        else {
            if (isset($arg['officina']) && ($arg['officina']=='PI' || $arg['officina']=='PU' || $arg['officina']=='PN')) $this->query.=" WHERE isnull(t8.num_rif_contratto,'0')='0' OR isnull(convert(varchar(8),t6.d_con,112),'')!=''";
        }

        $this->query.=" ORDER BY odl.rif,odl.lam";

        return true;
    }

    function getPrenotazioni($arg) {
        //restituisce le prenotazioni ed i rispettivi lamentati

        $this->query="SELECT
            odl.*,
            vei.mat_targa,
            vei.mat_telaio,
            vei.cod_veicolo,
            vei.des_veicolo,
            CONVERT(varchar(8),vei.dat_inizio_garanzia,112) AS d_consegna,
            isnull(util.des_ragsoc,'') AS util_ragsoc,
            isnull(intest.des_ragsoc,'') AS intest_ragsoc,
            coalesce(util.des_email1,util.des_email2,'') AS util_mail,
            coalesce(intest.des_email1,intest.des_email2,'') AS intest_mail,
            coalesce(CASE WHEN util.sig_tel_res='' THEN null ELSE util.sig_tel_res END,util.sig_tel1_res,'') AS util_tel,
            coalesce(CASE WHEN intest.sig_tel_res='' THEN null ELSE intest.sig_tel_res END,intest.sig_tel1_res,'') AS intest_tel

            FROM (
        ";

        $this->otello();

        if (isset($arg['num_rif_movimento'])) {
            $this->query.="  WHERE t2.num_rif_movimento='".$arg['num_rif_movimento']."' AND t4.ind_annullo='N' ";
        }
        else {
            $this->query.=" WHERE CONVERT(varchar(8),t2.dat_prenotazione,112)>='".$arg['inizio']."' AND CONVERT(varchar(8),t2.dat_prenotazione,112)<='".$arg['fine']."' AND t2.cod_officina='".$arg['officina']."' AND t4.ind_annullo='N' ";
        }

        $this->query.=") AS odl";

        $this->query.=" LEFT JOIN ".$this->tabelle['VE_ANAVEI']->getTabName()." AS vei ON odl.num_rif_veicolo=vei.num_rif_veicolo";

        $this->query.=" LEFT JOIN ".$this->tabelle['GN_ANAGRAFICHE']->getTabName()." AS util ON odl.cod_anagra_util=util.cod_anagra";

        $this->query.=" LEFT JOIN ".$this->tabelle['GN_ANAGRAFICHE']->getTabName()." AS intest ON odl.cod_anagra_intest=intest.cod_anagra";

        $this->query.=" ORDER BY odl.d_pren,odl.rif,odl.lam";

        //echo $this->query;

        return true;
    }

    function getDatiFinanziariConcerto($arr) {

        $this->query="
            SELECT
            t1.*
            
            FROM (SELECT
                vei.mat_telaio,
                ges.num_rif_veicolo,
                ges.num_gestione,
                contra.num_rif_contratto,
                contra.cod_acquirente,
                contra.cod_venditore,
                surf.cod_finanziaria,
                surf.num_rate_totali,
                surf.val_rata,
                surf.val_maxi_rata,
                surf.cod_prodotto_finanziario,
                surf.val_riscatto_leasing,
                CONVERT(varchar(8),surf.dat_ultima_rata,112) AS d_ultima_rata

                FROM ve_anavei_ges AS ges

                LEFT JOIN VE_CONTRA_TES AS contra ON ges.num_rif_veicolo=contra.num_rif_veicolo AND ges.num_gestione=contra.num_gestione AND contra.ind_contratto='S'
                
                LEFT JOIN VE_SERFIN AS surf ON surf.num_rif_contratto=contra.num_rif_contratto AND surf.cod_finanziaria IN('VWB','FND','PFS') AND isnull(surf.cod_prodotto_finanziario,'')!='VWCV' AND CONVERT(varchar(8),surf.dat_ultima_rata,112)>'".date('Ymd')."'

                INNER JOIN (
                    SELECT
                    ges.num_rif_veicolo,
                    max(ges.num_gestione) as num_gestione
                    FROM VE_ANAVEI_GES AS ges
                    group by ges.num_rif_veicolo
                ) AS ultima_ges ON ges.num_rif_veicolo=ultima_ges.num_rif_veicolo AND ges.num_gestione=ultima_ges.num_gestione
                
                INNER JOIN VE_ANAVEI as vei ON ges.num_rif_veicolo=vei.num_rif_veicolo
            ) 
            AS t1

            WHERE t1.mat_telaio='".$arr['telaio']."'
        ";

        return true;
    }

    function getQcheck($arr) {

        $this->query="SELECT
                odl.num_rif_movimento,
                isnull(vei.mat_targa,'') as targa,
                ana.des_ragsoc,
                isnull(odl.cod_accettatore,'') as rc,
                isnull(coll.concerto,'') as operaio
        ";

        $this->query.=" FROM ".$this->tabelle['GN_MOVTES_OFF']->getTabName()." AS odl";

        $this->query.=" INNER JOIN ".$this->tabelle['GN_ANAGRAFICHE']->getTabName()." AS ana ON odl.cod_anagra_util=ana.cod_anagra";

        $this->query.=" INNER JOIN ".$this->tabelle['VE_ANAVEI']->getTabName()." AS vei ON odl.num_rif_veicolo=vei.num_rif_veicolo";

        $this->query.=" INNER JOIN ".$this->tabelle['OF_RILTEM']->getTabName()." AS ril ON odl.num_rif_movimento=ril.num_rif_movimento";
                
        $this->query.=" INNER JOIN ".$this->tabelle['MAESTRO_collaboratori']->getTabName()." AS coll ON ril.cod_operaio=coll.cod_operaio";

        $this->query.=" WHERE odl.num_rif_movimento='".$arr[0]."'";

        return true;
    }

    //isnull(dbo.fnMG_UCOSTO2(isnull(movd.cod_articolo,''),movd.cod_tipo_articolo,CONVERT(VARCHAR(8),movdoc.dat_documento_i,112)),0)*movd.qta_pezzi
    //isnull([dbo].[fnMG_COSTO_ACQUISTO](movd.cod_societa,movd.cod_tipo_articolo,movd.cod_articolo,movdoc.dat_documento_i),0)*movd.qta_pezzi
    //isnull(dbo.fnMG_UCOSTO3(isnull(movd.cod_articolo,''),movd.cod_tipo_articolo,CONVERT(VARCHAR(8),movdoc.dat_documento_i,112)),0)*movd.qta_pezzi
    //WHEN movd.ind_tipo_riga='R' and isnull(s.cod_articolo,'')!='' THEN (s.costo/s.qta)*movd.qta_pezzi
    function fatturato_S($arr) {

        $this->query="SELECT
            lista.*,
            ana.des_ragsoc AS intest_ragsoc,
            awks.cod_vw_tipo_cliente_off AS tipo_cliente,
            'concerto' AS dms
        ";

        $this->query.=" FROM (SELECT
            movd.num_rif_movimento as rif,
            movt.cod_movimento,
            CONVERT(varchar(8),movdoc.dat_documento_i,112) AS d_fatt,
            movd.ind_tipo_riga,
            isnull(movd.cod_tipo_articolo,'') AS cod_tipo_articolo,
            isnull(movd.cod_articolo,'') AS cod_articolo,
            isnull(movd.ind_prelevato_magazzino,'') AS flag_prelevato,
            art.cod_categoria_vendita,
            art.cod_famiglia_articolo AS famiglia,
            isnull(movd.cod_operazione,'') AS cod_operazione,
            isnull(movd.cod_varie,'') AS cod_varie,
            movd.des_riga AS descrizione,
            movd.qta_pezzi AS qta,
            '' AS unita,
            CASE
                WHEN movd.ind_tipo_riga='M' THEN movd.prz_prezzo_unitario*movd.qta_pezzi
                ELSE movd.prz_prezzo_unitario
            END AS listino,
            (movd.prc_sconto+movd.prc_sconto_add) AS prc_sconto,
            movd.val_importo as importo,
            CASE
                WHEN movd.ind_tipo_riga='R' THEN 0
                ELSE 0
            END AS costo,
            movd.val_fattura_lt AS val_costo,
            isnull(movd.des_psa_commande,-1) AS des_psa_commande,
            movd.val_importo_ivato AS ivato,
            movd.cod_iva,
            isnull(iva.prc_iva,0) AS prc_iva,
            movd.cod_inconveniente AS lam,
            movd.num_riga AS riga,
            isnull(movd.cod_movimento,'') AS acarico,
            isnull(movd.cod_tipo_garanzia,'') AS cg,
            movt.ind_chiuso,
            movoff.cod_officina,
            movoff.cod_accettatore,
            isnull(acc.des_accettatore,'') AS des_accettatore,
            isnull(acc.cod_utente,'') AS cod_utente,
            movoff.num_km AS km,
            isnull(movoff.num_commessa,0) AS num_commessa,
            CASE
                WHEN vei.cod_marca IN ('A','C','N','S','P','V','U') THEN vei.cod_marca
                ELSE 'X'
            END AS marca_veicolo,
            isnull(vei.cod_veicolo,'') AS  modello,
            isnull(CONVERT(varchar(8),vei.dat_inizio_garanzia,112),'') AS d_consegna,
            vei.mat_targa,
            vei.mat_telaio,
            CASE
                WHEN movt.ind_chiuso='S' THEN movdoc.cod_anagrafica 
                ELSE movoff.cod_anagra_util
            END AS id_cliente
        ";

        $this->query.=" FROM ".$this->tabelle['GN_MOVDET']->getTabName()." AS movd";

        $this->query.=" INNER JOIN ".$this->tabelle['GN_MOVTES']->getTabName()." AS movt ON movd.num_rif_movimento=movt.num_rif_movimento";
        $this->query.=" INNER JOIN ".$this->tabelle['GN_MOVTES_OFF']->getTabName()." AS movoff ON movd.num_rif_movimento=movoff.num_rif_movimento";
        $this->query.=" LEFT JOIN ".$this->tabelle['GN_MOVTES_DOC']->getTabName()." AS movdoc ON movd.num_rif_movimento=movdoc.num_rif_movimento";
        $this->query.=" LEFT JOIN ".$this->tabelle['VE_ANAVEI']->getTabName()." AS vei ON movoff.num_rif_veicolo=vei.num_rif_veicolo";
        $this->query.=" LEFT JOIN ".$this->tabelle['MG_ANAART']->getTabName()." AS art ON movd.cod_tipo_articolo=art.cod_tipo_articolo AND movd.cod_articolo=art.cod_articolo";
        $this->query.=" LEFT JOIN ".$this->tabelle['TB_MAG_CAUMOV']->getTabName()." AS cm ON cm.cod_movimento=movd.cod_movimento";
        $this->query.=" LEFT JOIN ".$this->tabelle['GN_IVA']->getTabName()." as iva on movd.cod_iva=iva.cod_iva";
        $this->query.=" LEFT JOIN ".$this->tabelle['TB_OFF_ACCETTATORI']->getTabName()." as acc on movoff.cod_accettatore=acc.cod_accettatore";

        //$this->query.=" LEFT JOIN [dbo].gab_mag_carichi AS s ON s.cod_tipo_articolo=movd.cod_tipo_articolo AND s.cod_articolo=movd.cod_articolo AND s.data=(
            //SELECT top 1 data from [dbo].gab_mag_carichi as k WHERE k.cod_tipo_articolo=movd.cod_tipo_articolo AND k.cod_articolo=movd.cod_articolo AND k.data<=CONVERT(VARCHAR(8),movdoc.dat_documento_i,112) ORDER BY k.data DESC,k.mov DESC)";
        
        $this->query.=" WHERE isnull(movoff.num_commessa,0)!=0 AND movoff.ind_preventivo!='S' AND movd.ind_tipo_riga!='I'";

        if (isset($arr['officina']) && $arr['officina']!='') {
            $this->query.=" AND movoff.cod_officina='".$arr['officina']."'";
        }
    
        $this->query.=") AS lista";

        $this->query.=" LEFT JOIN ".$this->tabelle['GN_ANAGRAFICHE']->getTabName()." AS ana ON lista.id_cliente=ana.cod_anagra";
        $this->query.=" LEFT JOIN ".$this->tabelle['CA_ANAGRAFICHE_WORKCAR_SOCIETA']->getTabName()." AS awks ON lista.id_cliente=awks.cod_anagra";

        $this->query.=" WHERE lista.d_fatt>='".$arr['inizio']."' AND lista.d_fatt<='".$arr['fine']."'";
        /*if (isset($arr['marche'])) {
            $this->query.=" AND lista.marca_veicolo IN (".substr($arr['marche'],0,-1).")";
        }*/

        $this->query.=" ORDER BY lista.d_fatt,lista.rif,lista.ind_tipo_riga";

        return true;

    }

    function ultimoCosto($arr) {
        //il costo è dell'intero movimento
        //per avere il valore unitario occorre calcolare COSTO / QTA
        $this->query="SELECT top 1
            k.cod_tipo_articolo,
            k.cod_articolo,
            k.qta_pezzi AS qta,
            k.val_importo AS costo,
            k.num_rif_movimento AS mov,
            k.num_riga AS riga,
            CONVERT(VARCHAR(8),k.dat_movimento,112) AS data
            FROM gn_movdet AS k
   	 	
            WHERE
            k.ind_tipo_riga='R' AND k.prc_sconto<>100 AND k.cod_movimento IN ('M01','M31','M41') AND
            k.cod_tipo_articolo='".$arr['cod_tipo_articolo']."' AND k.cod_articolo='".$arr['cod_articolo']."' AND CONVERT(VARCHAR(8),k.dat_movimento,112)<='".$arr['d_fatt']."'
            ORDER BY data DESC,mov DESC,riga
        ";

        return true;
    }

    function getRiltem($arr) {

        $this->query="SELECT
            res.*,
            isnull(ll.inc_pos_lav,0) AS inc_pos_lav,
            isnull(ll.inc_marc_chiuse,0) AS inc_marc_chiuse,
            isnull(ll.num_tecnici,0) AS num_tecnici,
            isnull(ll.tot_marc_odl,0) AS tot_marc_odl,
            isnull(ll.tot_fatt_odl,0) AS tot_fatt_odl,
            isnull(ll.cod_accettatore,'') AS cod_accettatore,
            isnull(ll.qta_ore_prenotazione,0) AS qta_ore_prenotazione,
            isnull(ll.inc_carico,'') AS acarico,
            isnull(ll.inc_cm,'') AS cod_movimento,
            isnull(ll.inc_cg,'') AS cod_tipo_garanzia,
            isnull(ll.inc_cg,'') AS cg,
            isnull(ll.des_ragsoc,'') AS des_ragsoc,
            'concerto' AS dms
        ";

        $this->query.=" FROM ( ";
            $this->query.=" SELECT
                ril.num_rif_movimento,
                ril.cod_inconveniente,
                ril.cod_operaio,
                ril.num_riga,
                CONVERT(varchar(8),ril.dat_ora_inizio,112) AS d_inizio,
                CONVERT(varchar(5),ril.dat_ora_inizio,114) AS o_inizio,
                isnull(CONVERT(varchar(8),ril.dat_ora_fine,112),'') AS d_fine,
                isnull(CONVERT(varchar(5),ril.dat_ora_fine,114),'') AS o_fine,
                ril.qta_ore_lavorate AS qta,
                ril.des_note,
                movt.ind_chiuso,
                movt.ind_annullo,
                ofc.cod_officina
            ";
            /*
                CASE
                    WHEN vei.cod_marca IN ('A','C','N','S','P','V') THEN vei.cod_marca
                    WHEN vei.cod_marca IS NULL OR vei.cod_marca='' THEN ''
                    ELSE 'X'
                END AS marca_veicolo
            ";
            */

            $this->query.=" FROM ".$this->tabelle['OF_RILTEM']->getTabName()." AS ril";     

            $this->query.=" INNER JOIN ".$this->tabelle['GN_MOVTES']->getTabName()." AS movt ON ril.num_rif_movimento=movt.num_rif_movimento";
            $this->query.=" INNER JOIN ".$this->tabelle['GN_MOVTES_OFF']->getTabName()." AS ofc ON ril.num_rif_movimento=ofc.num_rif_movimento";
            //$this->query.=" INNER JOIN ".$this->tabelle['VE_ANAVEI']->getTabName()." AS vei ON ofc.num_rif_veicolo=vei.num_rif_veicolo";

            $this->query.=" WHERE CONVERT(varchar(8),ril.dat_ora_inizio,112)>='".$arr['inizio']."' AND CONVERT(varchar(8),ril.dat_ora_inizio,112)<='".$arr['fine']."' ";
            //07.06.2021 tolto perché escludeva alcune marcature fittizie e la verifica viene fatta dal ciclo while
            //if ($arr['reparti']!='') $this->query.=" AND ofc.cod_officina IN (".substr($arr['reparti'],0,-1).")";
            if ($arr['operaio']!="") $this->query.=" AND ril.cod_operaio='".$arr['operaio']."'";
        
        $this->query.=" ) AS res ";

        $this->query.=" LEFT JOIN ".$this->viste['LISTA_LAMENTATI']." AS ll ON res.num_rif_movimento=ll.mov AND res.cod_inconveniente=ll.inc";

        //07.06.2021 tolto perché escludeva alcune marcature fittizie ed in fondo non ha molgo senso
        //if ($arr['marche']!='') $this->query.=" WHERE res.marca_veicolo IN (".substr($arr['marche'],0,-1).")";

        $this->query.=" WHERE res.ind_annullo='N'";

        if ($this->orderBy!="") {
            $this->query.=' ORDER BY '.$this->orderBy;
        }

        //$this->query.=" ORDER BY CAST (res.cod_operaio AS int),d_inizio,o_inizio";

        return true;
    }

    function getFattNonMarc($arr) {

        $this->query="SELECT
            CONVERT(varchar(8),movdoc.dat_documento_i,112) as d_doc,
            lam.mov AS num_rif_movimento,
            lam.inc AS cod_inconveniente,
            lam.inc_cod_off,
            isnull(lam.inc_pos_lav,0) AS inc_pos_lav,
            isnull(lam.inc_marc_chiuse,0) AS inc_marc_chiuse,
            isnull(lam.num_tecnici,0) AS num_tecnici,
            isnull(lam.tot_marc_odl,0) AS tot_marc_odl,
            isnull(lam.tot_fatt_odl,0) AS tot_fatt_odl,
            isnull(lam.cod_accettatore,'') AS cod_accettatore,
            isnull(lam.qta_ore_prenotazione,0) AS qta_ore_prenotazione,
            isnull(lam.inc_carico,'') AS acarico,
            isnull(lam.inc_cm,'') AS cod_movimento,
            isnull(lam.inc_cg,'') AS cod_tipo_garanzia,
            isnull(lam.inc_cg,'') AS cg,
            lam.inc_testo,
            lam.des_ragsoc,
            'concerto' AS dms
        ";

        $this->query.=" FROM ".$this->tabelle['GN_MOVTES_DOC']->getTabName()." AS movdoc";

        $this->query.=" LEFT JOIN ".$this->tabelle['GN_MOVTES_OFF']->getTabName()." AS movoff ON movdoc.num_rif_movimento=movoff.num_rif_movimento";

        $this->query.=" INNER JOIN ".$this->viste['LISTA_LAMENTATI']." AS lam ON movdoc.num_rif_movimento=lam.mov";

        $this->query.=" WHERE CONVERT(varchar(8),movdoc.dat_documento_i,112)>='".$arr['inizio']."' AND CONVERT(varchar(8),movdoc.dat_documento_i,112)<='".$arr['fine']."'";
        $this->query.=" AND lam.inc_cod_off IN (".substr($arr['reparti'],0,-1).") AND lam.inc_marc_chiuse=0 AND lam.inc_pos_lav!=0";

        if ($this->orderBy!="") {
            $this->query.=' ORDER BY '.$this->orderBy;
        }

        return true;
    }

    function dedalo() {
        
        $this->zoccolo();
        
        $this->query.=" FROM ".$this->tabelle['GN_MOVDET']->getTabName()." AS t1 
            LEFT JOIN ".$this->tabelle['GN_MOVTES_OFF']->getTabName()." AS t2 ON t1.num_rif_movimento=t2.num_rif_movimento
            LEFT JOIN ".$this->tabelle['GN_MOVTES']->getTabName()." AS t4 ON t2.num_rif_movimento=t4.num_rif_movimento
            LEFT JOIN ".$this->tabelle['GN_MOVTES_DOC']->getTabName()." AS doc ON t2.num_rif_movimento=doc.num_rif_movimento
            LEFT JOIN ".$this->tabelle['GN_MOVDET_PRENOTAZIONI']->getTabName()." AS t3 ON t1.num_rif_movimento=t3.num_rif_movimento AND t1.num_riga=t3.num_riga
            LEFT JOIN ".$this->tabelle['GN_MOVTES_OFF_PRATICHE']->getTabName()." AS pr ON t1.num_rif_movimento=pr.num_rif_movimento
            LEFT JOIN ".$this->viste['LISTA_LAMENTATI']." AS ll ON t1.num_rif_movimento=ll.mov AND t1.cod_inconveniente=ll.inc
            LEFT JOIN (
                SELECT
                p1.num_pratica,
                SUM(CASE WHEN p2.cod_movimento='OOI' OR p2.cod_movimento='OOA' THEN 1 ELSE 0 END) AS apprip,
                max(ISNULL( CONVERT(varchar(8),p3.dat_promessa_consegna,112),'xxxxxxxx' )+':'+SUBSTRING( ISNULL (CONVERT(varchar(5),p3.dat_promessa_consegna,114),'xx:xx' ),1,5)) as d_ricon_pratica,
                min(ISNULL( CONVERT(varchar(8),p3.dat_entrata_veicolo,112),'xxxxxxxx' )+':'+SUBSTRING( ISNULL (CONVERT(varchar(5),p3.dat_entrata_veicolo,114),'xx:xx' ),1,5)) as d_entrata_pratica
                FROM ".$this->tabelle['GN_MOVTES_OFF_PRATICHE']->getTabName()." AS p1
                INNER JOIN ".$this->tabelle['GN_MOVTES']->getTabName()." AS p2 ON p1.num_rif_movimento=p2.num_rif_movimento
                INNER JOIN ".$this->tabelle['GN_MOVTES_OFF']->getTabName()." AS p3 ON p1.num_rif_movimento=p3.num_rif_movimento
                GROUP BY p1.num_pratica
            ) AS dp ON dp.num_pratica=pr.num_pratica
            WHERE t1.ind_tipo_riga='I' AND t4.cod_movimento!='OOS' AND t2.ind_preventivo='N'
        ";
    }

    function otello() {
        
        $this->zoccolo();
        
        $this->query.=" FROM ".$this->tabelle['GN_MOVTES_OFF']->getTabName()." AS t2 
            LEFT JOIN ".$this->tabelle['GN_MOVDET']->getTabName()." AS t1 ON t1.num_rif_movimento=t2.num_rif_movimento AND t1.ind_tipo_riga='I'
            LEFT JOIN ".$this->tabelle['GN_MOVTES']->getTabName()." AS t4 ON t2.num_rif_movimento=t4.num_rif_movimento
            LEFT JOIN ".$this->tabelle['GN_MOVTES_DOC']->getTabName()." AS doc ON t2.num_rif_movimento=doc.num_rif_movimento
            LEFT JOIN ".$this->tabelle['GN_MOVDET_PRENOTAZIONI']->getTabName()." AS t3 ON t1.num_rif_movimento=t3.num_rif_movimento AND t1.num_riga=t3.num_riga
            LEFT JOIN ".$this->tabelle['GN_MOVTES_OFF_PRATICHE']->getTabName()." AS pr ON t1.num_rif_movimento=pr.num_rif_movimento
            LEFT JOIN ".$this->viste['LISTA_LAMENTATI']." AS ll ON t1.num_rif_movimento=ll.mov AND t1.cod_inconveniente=ll.inc
            LEFT JOIN (
                SELECT
                p1.num_pratica,
                SUM(CASE WHEN p2.cod_movimento='OOI' THEN 1 ELSE 0 END) AS apprip,
                max(ISNULL( CONVERT(varchar(8),p3.dat_promessa_consegna,112),'xxxxxxxx' )+':'+SUBSTRING( ISNULL (CONVERT(varchar(5),p3.dat_promessa_consegna,114),'xx:xx' ),1,5)) as d_ricon_pratica,
                min(ISNULL( CONVERT(varchar(8),p3.dat_entrata_veicolo,112),'xxxxxxxx' )+':'+SUBSTRING( ISNULL (CONVERT(varchar(5),p3.dat_entrata_veicolo,114),'xx:xx' ),1,5)) as d_entrata_pratica
                FROM ".$this->tabelle['GN_MOVTES_OFF_PRATICHE']->getTabName()." AS p1
                INNER JOIN ".$this->tabelle['GN_MOVTES']->getTabName()." AS p2 ON p1.num_rif_movimento=p2.num_rif_movimento
                INNER JOIN ".$this->tabelle['GN_MOVTES_OFF']->getTabName()." AS p3 ON p1.num_rif_movimento=p3.num_rif_movimento
                GROUP BY p1.num_pratica
            ) AS dp ON dp.num_pratica=pr.num_pratica
        ";
    }

    function zoccolo() {
        //comincia con il .= perchè viene usata come vista dinamica da altri metodi
        $this->query.="SELECT
            convert(varchar(8),t1.dat_inserimento,112) as dat_inserimento,
            t2.cod_officina,
            t2.cod_officina_prenotazione,
            CASE
                WHEN t4.ind_chiuso='S' THEN 'CH'
                WHEN t4.ind_chiuso='N' AND ISNULL(t2.num_commessa,0)!=0 THEN 'AP'
                ELSE 'XX'
            END AS cod_stato_commessa,
            CASE
                WHEN t2.cod_stato_commessa='RP' THEN 1
                ELSE 0
            END AS picking,
            CASE
                WHEN ISNULL(t2.num_commessa,0)!=0 THEN 'N'
                ELSE 'S'
            END AS pren,
            ISNULL(t4.cod_movimento,'') AS cod_movimento,
            ISNULL(t1.cod_movimento,'') AS acarico,
            ISNULL(t1.cod_tipo_garanzia,'') AS cg,
            isnull(t1.cod_vw_azione,'') as cod_azione,
            t2.num_rif_movimento AS rif,
            pr.num_pratica as pratica,
            ISNULL(t2.num_commessa,0) AS num_commessa,
            t2.ind_preventivo,
            t2.cod_tipo_trasporto,
            t1.num_riga,
            t1.cod_inconveniente AS lam,
            t1.cod_inconveniente AS rif_lam,
            ISNULL(t1.des_riga,'') AS des_riga,
            ISNULL(t1.ind_inc_stato,'L') AS ind_stato,
            t4.ind_chiuso,
            isnull(t1.des_note,'') as note_d,
            isnull(t1.des_note_prima,'') as note_p,
            isnull(t1.cod_pacchetto,'') AS cod_pacchetto,
            ISNULL( CONVERT(varchar(8),t2.dat_prenotazione,112),'xxxxxxxx' )+':'+SUBSTRING( ISNULL (CONVERT(varchar(5),t2.dat_prenotazione,114),'xx:xx' ),1,5) AS d_pren,
            ISNULL( CONVERT(varchar(8),t2.dat_promessa_consegna,112),'xxxxxxxx' )+':'+SUBSTRING( ISNULL (CONVERT(varchar(5),t2.dat_promessa_consegna,114),'xx:xx' ),1,5) AS d_ricon,
            ISNULL( CONVERT(varchar(8),t2.dat_entrata_veicolo,112),'xxxxxxxx' )+':'+SUBSTRING( ISNULL (CONVERT(varchar(5),t2.dat_entrata_veicolo,114),'xx:xx' ),1,5) AS d_entrata,
            ISNULL( CONVERT(varchar(8),t4.dat_apertura_movimento,112),'' ) AS d_documento,
            ISNULL( CONVERT(varchar(8),doc.dat_documento_i,112),'' ) AS d_fatt,
            t1.qta_ore_prenotazione AS ore,
            isnull(t1.qta_tempario,0.0) AS ore_isla,
            t1.cod_reparto_inc AS subrep,
            ISNULL( CONVERT(varchar(8),t1.dat_prenotazione_inc,112),'xxxxxxxx' )+':'+SUBSTRING( ISNULL (CONVERT(varchar(5),t1.dat_prenotazione_inc,114),'xx:xx' ),1,5) AS d_inc,
            ISNULL( CONVERT(varchar(8),t1.dat_chiusura_inc,112),'xxxxxxxx' )+':'+SUBSTRING( ISNULL (CONVERT(varchar(5),t1.dat_chiusura_inc,114),'xx:xx' ),1,5) AS d_fine_inc,
            ISNULL( CONVERT(varchar(8),t1.dat_inizio_lavoro,112),'xxxxxxxx' )+':'+SUBSTRING( ISNULL (CONVERT(varchar(5),t1.dat_inizio_lavoro,114),'xx:xx' ),1,5) AS d_fix,
            ISNULL(t1.gab_dist_inc,'') AS distribuzione,
            ISNULL(t3.num_progressivo,0) AS prog_spalm,
            ISNULL( CONVERT(varchar(8),t3.dat_prenotazione_det,112),'xxxxxxxx' )+':'+SUBSTRING( ISNULL (CONVERT(varchar(5),t3.dat_prenotazione_det,114),'xx:xx' ),1,5) AS d_spalm,
            ISNULL(t3.qta_prenotazione_det,0) AS ore_spalm,
            t1.dat_prenotazione_inc,
            t3.dat_prenotazione_det,
            t2.num_rif_veicolo,
            t2.num_rif_veicolo_progressivo,
            isnull(t2.cod_anagra_util,'') AS cod_anagra_util,
            isnull(t2.cod_anagra_intest,'') AS cod_anagra_intest,
            isnull(t2.cod_anagra_loc,'') AS cod_anagra_loc,
            isnull(t2.cod_anagra_fattura,'') AS cod_anagra_fattura,
            t2.cod_accettatore,
            t2.num_km AS km,
            t4.ind_annullo,
            'concerto' AS dms,
            isnull(ll.ind_inc_stato,'0') AS ind_inc_stato,
            isnull(ll.inc_marc_chiuse,0) AS inc_marc_chiuse,
            dp.d_ricon_pratica AS d_ricon_pratica,
            dp.d_entrata_pratica AS d_entrata_pratica,
            dp.apprip
        ";
    }

    function getOccupazione($arg) {

        $this->query="
            SELECT dedalo.*
        ";

        $this->query.=" FROM (";
        $this->dedalo();
        $this->query.=") AS dedalo";

        $this->query.=" WHERE dedalo.ind_chiuso!='S' AND dedalo.ind_stato!='T' AND ( (dedalo.dat_prenotazione_inc>=CAST('".substr($arg['inizio'],0,8)." 00:00' AS datetime) AND dedalo.dat_prenotazione_inc<=CAST('".substr($arg['fine'],0,8)." 23:59' AS datetime) ) OR (dedalo.dat_prenotazione_det>='".substr($arg['inizio'],0,8)." 00:00' AND dedalo.dat_prenotazione_det<='".substr($arg['fine'],0,8)." 23:59') )";

        $this->query.=" ORDER BY dedalo.rif,dedalo.lam,dedalo.prog_spalm";

        return true;
    }

    function dedaloMarcatura() {
        
        $txt="SELECT
            t2.num_rif_movimento,
            t2.cod_inconveniente,
            t3.cod_officina,
            t4.cod_movimento,
            t2.cod_operaio,
            t2.num_riga,
            CONVERT(varchar(8),t2.dat_ora_inizio,112) AS d_inizio,
            CONVERT(varchar(5),t2.dat_ora_inizio,114) AS o_inizio,
            isnull(CONVERT(varchar(8),t2.dat_ora_fine,112),'') AS d_fine,
            isnull(CONVERT(varchar(5),t2.dat_ora_fine,114),'') AS o_fine,
            t2.qta_ore_lavorate,
            t2.des_note,
            t2.num_rif_riltem,
            t4.ind_chiuso,
            'concerto' AS dms
        ";

        return $txt;
    }

    function getMarcatureAperte($arg) {
        //nonostante il nome del metodo non prende le marcature aperte ma l'ultima marcatura di ogni tecnico
        //se l'odl è ancora aperto
        //20.03.23 messa clausola di marcature solo aperte

        $this->query=$this->dedaloMarcatura();

        $this->query.=" FROM (
                SELECT
                cod_operaio,
                MAX(num_rif_riltem) AS massimo 

                FROM ".$this->tabelle['OF_RILTEM']->getTabName()."

                WHERE isnull(CONVERT(varchar(8),dat_ora_fine,112),'')=''
                
                GROUP BY cod_operaio
            ) AS t1
        ";
        
        $this->query.=" INNER JOIN ".$this->tabelle['OF_RILTEM']->getTabName()." AS t2 ON t1.massimo=t2.num_rif_riltem";

        $this->query.=" LEFT JOIN ".$this->tabelle['GN_MOVTES_OFF']->getTabName()." AS t3 ON t3.num_rif_movimento=t2.num_rif_movimento";

        $this->query.=" LEFT JOIN ".$this->tabelle['GN_MOVTES']->getTabName()." AS t4 ON t4.num_rif_movimento=t2.num_rif_movimento";

        //$this->query.=" WHERE isnull(t2.cod_operaio,'') != '' AND t4.ind_chiuso='N' ";
        $this->query.=" WHERE isnull(t2.cod_operaio,'') != '' ";

        $this->query.=" order by t2.cod_operaio";

        return true;
    }

    function getMarcaturaByID($arg) {

        $this->query=$this->dedaloMarcatura();
        
        $this->query.=" FROM ".$this->tabelle['OF_RILTEM']->getTabName()." AS t2";

        $this->query.=" LEFT JOIN ".$this->tabelle['GN_MOVTES_OFF']->getTabName()." AS t3 ON t3.num_rif_movimento=t2.num_rif_movimento";

        $this->query.=" LEFT JOIN ".$this->tabelle['GN_MOVTES']->getTabName()." AS t4 ON t4.num_rif_movimento=t2.num_rif_movimento";

        $this->query.=" WHERE  t2.num_rif_riltem='".$arg['ID']."'";

        return true;
    }

    function getMarcatureOdl($arg) {

        $this->query=$this->dedaloMarcatura();

        $this->query.=",(op.cognome+' '+op.nome) AS nome_operaio";
        
        $this->query.=" FROM ".$this->tabelle['OF_RILTEM']->getTabName()." AS t2";

        $this->query.=" LEFT JOIN ".$this->tabelle['GN_MOVTES_OFF']->getTabName()." AS t3 ON t3.num_rif_movimento=t2.num_rif_movimento";

        $this->query.=" LEFT JOIN ".$this->tabelle['GN_MOVTES']->getTabName()." AS t4 ON t4.num_rif_movimento=t2.num_rif_movimento";

        $this->query.=" LEFT JOIN  ".$this->tabelle['MAESTRO_collaboratori']->getTabName()." AS op ON t2.cod_operaio=op.cod_operaio";

        //si da per scontato che almeno l'ODL sia impostato
        $this->query.=" WHERE  t2.num_rif_movimento='".$arg['rif']."'";

        if (isset($arg['lam']) && $arg['lam']!="") $this->query.=" AND t2.cod_inconveniente='".$arg['lam']."'";

        $this->query.=" ORDER BY d_inizio,o_inizio";

        return true;
    }

    function updateTimbratura($arg) {

        //GALILEO È STATO IMPOSTATO IN MODALITÀ TRANSACTION

        $wclause="num_rif_riltem='".$arg['num_rif_riltem']."'";

        $this->doUpdate('OF_RILTEM',$arg,$wclause,'');

        ////////lamentato
        if (isset($arg['statoLamentato']) && $arg['statoLamentato']!='jump') {
            $a=array(
                "ind_inc_stato"=>$arg['statoLamentato']
            );

            $wclause="num_rif_movimento='".$arg['num_rif_movimento']."' AND cod_inconveniente='".$arg['cod_inconveniente']."' AND ind_tipo_riga='I'";

            $this->doUpdate('GN_MOVDET',$a,$wclause,'');
        }
        //////////////////////////////////////////////

        $temp="SELECT
            t2.num_rif_movimento,
            t2.cod_inconveniente,
            t2.cod_operaio,
            t2.num_riga,
            CONVERT(varchar(8),t2.dat_ora_inizio,112) AS d_inizio,
            CONVERT(varchar(5),t2.dat_ora_inizio,114) AS o_inizio,
            isnull(CONVERT(varchar(8),t2.dat_ora_fine,112),'') AS d_fine,
            isnull(CONVERT(varchar(5),t2.dat_ora_fine,114),'') AS o_fine,
            t2.qta_ore_lavorate,
            t2.des_note,
            t2.num_rif_riltem
        ";

        $temp.=" FROM ".$this->tabelle['OF_RILTEM']->getTabName()." AS t2";

        $temp.=" WHERE t2.num_rif_riltem='".$arg['num_rif_riltem']."'";

        //$this->arrQuery[]=$this->getSelect('OF_RILTEM',$wclause);

        $this->arrQuery[]=$temp;

        return true;
    }

    function apriTimbratura($arg) {

        $this->arrQuery=array();

        //GALILEO È STATO IMPOSTATO IN MODALITÀ TRANSACTION
        sleep(2);

        $temp="IF NOT EXISTS (
            SELECT * FROM ".$this->tabelle['OF_RILTEM']->getTabName()." WHERE cod_operaio='".$arg['cod_operaio']."' AND isnull(CONVERT(varchar(8),dat_ora_fine,112),'')=''
        ) BEGIN ";

        $this->doInsert('OF_RILTEM',$arg,'','query');

        ////////lamentato
        if (isset($arg['statoLamentato']) && $arg['statoLamentato']!='jump') {
            $a=array(
                "ind_inc_stato"=>$arg['statoLamentato']
            );

            $wclause="num_rif_movimento='".$arg['num_rif_movimento']."' AND cod_inconveniente='".$arg['cod_inconveniente']."' AND ind_tipo_riga='I'";

            $this->doUpdate('GN_MOVDET',$a,$wclause,'query');
        }

        $temp.=$this->getQuery().' END';

        $this->arrQuery[]=$temp;
        //////////////////////////////////////////////

        return true;
    }

    function apriTimbraturaSpeciale($arg) {

        $this->arrQuery=array();

        $arg['des_note']=$arg['speciale'].json_encode($arg['specialTag']);

        //GALILEO È STATO IMPOSTATO IN MODALITÀ TRANSACTION
        sleep(2);

        $temp="IF NOT EXISTS (
            SELECT * FROM ".$this->tabelle['OF_RILTEM']->getTabName()." WHERE cod_operaio='".$arg['cod_operaio']."' AND isnull(CONVERT(varchar(8),dat_ora_fine,112),'')=''
        ) BEGIN ";


        $this->doInsert('OF_RILTEM',$arg,'','query');

        $temp.=$this->getQuery().' END';

        $this->arrQuery[]=$temp;

        //echo json_encode($this->arrQuery);

        return true;
    }

    function getCodIncRiltem($arg) {
        $this->query="SELECT cod_inconveniente FROM ".$this->tabelle['GN_MOVDET']->getTabName()." WHERE num_rif_movimento='".$arg['num_rif_movimento']."' AND ind_tipo_riga='I' AND SUBSTRING(des_riga,1,3)='".$arg['speciale']."'";
        return true;
    }

    function getLamStats($arr) {
        
        $this->query="SELECT
            isnull(mvt.qta_tempario,0) AS ore_prenotate_totali,
            isnull(fatt.ore_fatturate,0) AS ore_fatturate_totali,
            mar.*
        ";

        $this->query.="
            FROM (
                SELECT
                
                SUM(isnull(t1.qta_ore_lavorate,0)) AS ore_lavorate,
                t1.num_rif_movimento,
                t1.cod_inconveniente,
                t1.cod_operaio
                
                FROM ".$this->tabelle['OF_RILTEM']->getTabName()." AS t1
                WHERE t1.num_rif_movimento='".$arr['rif']."' AND t1.cod_inconveniente='".$arr['lam']."'
                GROUP BY t1.num_rif_movimento,t1.cod_inconveniente,t1.cod_operaio
                
            ) as mar
        ";

        $this->query.="
            LEFT JOIN (
                SELECT
                t1.num_rif_movimento,
                t1.cod_inconveniente,
                sum(isnull(qta_pezzi,0)) AS ore_fatturate
                    
                FROM ".$this->tabelle['GN_MOVDET']->getTabName()." AS t1
                    
                WHERE t1.ind_tipo_riga = 'M' AND (t1.cod_operazione<>'LT' and t1.ind_operazione_esterna<>'S') AND t1.num_rif_movimento='".$arr['rif']."' AND t1.cod_inconveniente='".$arr['lam']."'
                    
                GROUP BY t1.num_rif_movimento,t1.cod_inconveniente
                    
            ) AS fatt ON mar.num_rif_movimento=fatt.num_rif_movimento
        ";

        $this->query.="LEFT JOIN ".$this->tabelle['GN_MOVDET']->getTabName()." AS mvt ON mvt.ind_tipo_riga = 'I' AND mar.num_rif_movimento=mvt.num_rif_movimento AND mar.cod_inconveniente=mvt.cod_inconveniente";

        return true;

    }

    function getLamMarcato($arg) {
        //operaio è il riferimento proprio del DMS

        $this->query="SELECT
            ofr.*
        ";

        $this->query.=" FROM ".$this->tabelle['OF_RILTEM']->getTabName()." AS ofr";

        $this->query.=" WHERE ofr.num_rif_movimento='".$arg['rif']."' AND ofr.cod_inconveniente='".$arg['lam']."' AND ofr.cod_operaio='".$arg['operaio']."'";
        
        /*if ($arg['speciale']!="") {
            $this->query.=" AND SUBSTRING(ofr.des_note,1,3)='".$arg['speciale']."'";
        }*/

        return true;
    }

    function getOdlBody($arr) {

        $this->query="SELECT 
            t1.num_rif_movimento as rif,
            isnull(t1.cod_movimento,'') as cc,
            isnull(t2.cod_movimento,'') as cm,
            CASE
                WHEN t6.ind_carrozzeria='S' THEN 'K'
                WHEN isnull(t6.ind_carrozzeria,'N')='N' THEN t6.ind_tipo_addebito
            END as ca,
            isnull(t6.ind_carrozzeria,'N') as ind_carrozzeria,
            isnull(t1.cod_tipo_garanzia,'') as tipo_gar,
            isnull(t1.cod_vw_azione,'') as cod_azione,
            t1.ind_tipo_riga as t_riga,
            t1.cod_tipo_articolo as tipo,
            t1.cod_articolo as articolo,
            isnull(t1.ind_prelevato_magazzino,'x') AS flag_prelevato,
            isnull(t5.cod_categoria_vendita,'') AS gr,
            t1.cod_operazione as operazione,
            t1.cod_varie as varie,
            t1.des_riga as descrizione,
            t1.qta_pezzi as qta,
            t1.prz_prezzo_unitario as prezzo,
            t1.prc_sconto as prc_sconto,
            t1.val_importo as importo,
            t1.val_importo_ivato as ivato,
            t1.cod_iva,
            isnull(t7.prc_iva,0) as prc_iva,
            isnull(t1.des_note,'') as note_d,
            isnull(t1.des_note_prima,'') as note_p,
            t2.ind_chiuso as chiuso,
            t4.num_rif_veicolo as veicolo,
            t4.num_km as km,
            isnull(CONVERT(VARCHAR(8),t2.dat_inserimento,112),'') as d_mov,
            isnull(CONVERT(VARCHAR(8),t4.dat_entrata_veicolo,112),'') as d_odl,
            isnull(CONVERT(VARCHAR(8),t4.dat_prenotazione,112),'') as d_pren,
            CASE
                WHEN isnull(t1.cod_inconveniente,'')='' THEN 'Z' 
                ELSE t1.cod_inconveniente
            END as lam,
            CASE
                WHEN isnull(t1.cod_inconveniente,'')='' THEN 'Z' 
                ELSE t1.cod_inconveniente
            END as rif_lam,
            t1.num_riga as riga,
            t4.num_commessa as commessa,
            t1.cod_reparto_inc AS subrep,
            isnull(t1.cod_pacchetto,'') AS cod_pacchetto,
            t1.qta_ore_prenotazione AS ore,
            ISNULL(t1.ind_inc_stato,'L') AS ind_stato
        ";
								
        $this->query.=" FROM ".$this->tabelle['GN_MOVTES']->getTabName()." as t2";
		
        $this->query.=" 
            LEFT JOIN ".$this->tabelle['GN_MOVDET']->getTabName()." as t1 on t1.num_rif_movimento=t2.num_rif_movimento
            INNER JOIN ".$this->tabelle['GN_MOVTES_OFF']->getTabName()." as t4 on t2.num_rif_movimento=t4.num_rif_movimento
            LEFT JOIN ".$this->tabelle['MG_ANAART']->getTabName()." as t5 on t1.cod_articolo=t5.cod_articolo AND t1.cod_tipo_articolo=t5.cod_tipo_articolo
            LEFT JOIN ".$this->tabelle['TB_MAG_CAUMOV']->getTabName()." as t6 on t1.cod_movimento=t6.cod_movimento
            LEFT JOIN ".$this->tabelle['GN_IVA']->getTabName()." as t7 on t1.cod_iva=t7.cod_iva
        ";
				
		$this->query.=" WHERE t1.num_rif_movimento='".$arr['rif']."' AND t1.ind_tipo_riga IN ('I','M','R','V')";
				
		$this->query.=" ORDER BY t1.ind_tipo_riga,t1.cod_inconveniente,t1.num_riga";

        return true;
    }
    
    /*function getBodyLamentati($arr) {

        $this->query="SELECT 
            t1.num_rif_movimento as rif,
            isnull(t1.cod_movimento,'') as cc,
            isnull(t2.cod_movimento,'') as cm,
            CASE
                WHEN t6.ind_carrozzeria='S' THEN 'K'
                WHEN isnull(t6.ind_carrozzeria,'N')='N' THEN t6.ind_tipo_addebito
            END as ca,
            isnull(t6.ind_carrozzeria,'N') as ind_carrozzeria,
            isnull(t1.cod_tipo_garanzia,'') as tipo_gar,
            isnull(t1.cod_vw_azione,'') as cod_azione,
            t1.ind_tipo_riga as t_riga,
            t1.des_riga as descrizione,
            isnull(t1.des_note,'') as note_d,
            isnull(t1.des_note_prima,'') as note_p,
            t2.ind_chiuso as chiuso,
            isnull(CONVERT(VARCHAR(8),t2.dat_inserimento,112),'') as d_mov,
            CASE
                WHEN isnull(t1.cod_inconveniente,'')='' THEN 'Z' 
                ELSE t1.cod_inconveniente
            END as lam,
            t1.num_riga as riga,
            t4.num_commessa as commessa,
            t1.cod_reparto_inc AS subrep,
            isnull(t1.cod_pacchetto,'') AS cod_pacchetto,
            t1.qta_ore_prenotazione AS ore,
            ISNULL(t1.ind_inc_stato,'L') AS ind_stato,
            ISNULL(t9.pos,'') AS pos
        ";
								
        $this->query.=" FROM ".$this->tabelle['GN_MOVTES']->getTabName()." as t2";
		
        $this->query.=" 
            LEFT JOIN ".$this->tabelle['GN_MOVDET']->getTabName()." as t1 on t1.num_rif_movimento=t2.num_rif_movimento
            INNER JOIN ".$this->tabelle['GN_MOVTES_OFF']->getTabName()." as t4 on t2.num_rif_movimento=t4.num_rif_movimento
            LEFT JOIN ".$this->tabelle['TB_MAG_CAUMOV']->getTabName()." as t6 on t1.cod_movimento=t6.cod_movimento
            LEFT JOIN ".$this->tabelle['AVALON_lamextra']->getTabName()." as t9 on t1.num_rif_movimento=t9.rif AND t1.cod_inconveniente=t9.lam AND t9.dms='c'
        ";
				
		$this->query.=" WHERE t1.num_rif_movimento='".$arr['rif']."' AND t1.ind_tipo_riga='I'";
				
		$this->query.=" ORDER BY t1.cod_inconveniente";

        return true;
    }*/

    function getLamMovements($arr) {

        $this->query="SELECT 
            t1.num_rif_movimento as rif,
            t1.ind_tipo_riga as t_riga,
            '' AS tipo_riga,
            t1.cod_tipo_articolo as tipo,
            t1.cod_articolo as articolo,
            isnull(t1.ind_prelevato_magazzino,'x') AS flag_prelevato,
            isnull(t5.cod_categoria_vendita,'') AS gr,
            t1.cod_operazione as operazione,
            t1.cod_varie as varie,
            t1.des_riga as descrizione,
            t1.qta_pezzi as qta,
            t1.des_unita_misura as unita,
            t1.prz_prezzo_unitario as prezzo,
            t1.prc_sconto as prc_sconto,
            t1.val_importo as importo,
            t1.val_importo_ivato as ivato,
            t1.cod_iva,
            isnull(t7.prc_iva,0) as prc_iva,
            isnull(t1.des_note,'') as note_d,
            isnull(t1.des_note_prima,'') as note_p,
            CASE
                WHEN isnull(t1.cod_inconveniente,'')='' THEN 'Z' 
                ELSE t1.cod_inconveniente
            END as lam,
            t1.num_riga as riga
        ";
								
        $this->query.=" FROM ".$this->tabelle['GN_MOVDET']->getTabName()." as t1";
		
        $this->query.=" 
            LEFT JOIN ".$this->tabelle['MG_ANAART']->getTabName()." as t5 on t1.cod_articolo=t5.cod_articolo AND t1.cod_tipo_articolo=t5.cod_tipo_articolo
            LEFT JOIN ".$this->tabelle['GN_IVA']->getTabName()." as t7 on t1.cod_iva=t7.cod_iva
        ";
				
		$this->query.=" WHERE t1.num_rif_movimento='".$arr['rif']."' AND t1.ind_tipo_riga IN ('M','R','V')";
				
		$this->query.=" ORDER BY t1.ind_tipo_riga,t1.cod_inconveniente,t1.num_riga";

        return true;
    }

    function getCliHeadBudget($arg) {

        $this->query="SELECT
            t1.num_rif_movimento AS rif,
            t1.cod_officina AS cod_officina,
            t4.cod_movimento
        ";

        $this->query.=" FROM ".$this->tabelle['GN_MOVTES_OFF']->getTabName()." AS t1";
        $this->query.=" LEFT JOIN ".$this->tabelle['GN_MOVTES']->getTabName()." AS t4 ON t1.num_rif_movimento=t4.num_rif_movimento";

        $this->query.=" WHERE t1.num_rif_movimento IN (".$arg['elenco'].")";

        //echo $this->query;

        return true;
    }


    /////////////////////////////////////////////////////////////////////////////////

    function getStorico($arr) {

        $this->query="SELECT
            t1.num_rif_movimento as rif,
            t1.cod_officina,
            t1.num_rif_veicolo AS id_veicolo,
            t3.num_pratica as pratica,
            t2.ind_chiuso,
            isnull(t1.num_commessa,0) as num_doc,
            CONVERT(varchar(8),isnull(t1.dat_entrata_veicolo,t2.dat_inserimento),112) as d_rif,
            isnull(CONVERT(varchar(8),t1.dat_entrata_veicolo,112),'') AS d_entrata,
            isnull(CONVERT(varchar(8),t1.dat_prenotazione,112),'') AS d_pren,
            isnull(CONVERT(varchar(8),t4.dat_documento_i,112),'') AS d_fatt,
            t2.cod_movimento AS cm,
            t1.cod_accettatore,
            t5.des_ragsoc AS util_ragsoc,
            t6.des_ragsoc AS intest_ragsoc,
            t1.num_km AS km,
            'concerto' AS dms
        ";

        $this->query.=" FROM ".$this->tabelle['GN_MOVTES_OFF']->getTabName()." AS t1";
        $this->query.=" INNER JOIN ".$this->tabelle['GN_MOVTES']->getTabName()." AS t2 ON t1.num_rif_movimento=t2.num_rif_movimento";
        $this->query.=" LEFT JOIN ".$this->tabelle['GN_MOVTES_DOC']->getTabName()." AS t4 ON t1.num_rif_movimento=t4.num_rif_movimento";
        $this->query.=" LEFT JOIN ".$this->tabelle['GN_MOVTES_OFF_PRATICHE']->getTabName()." AS t3 ON t1.num_rif_movimento=t3.num_rif_movimento";
        $this->query.=" LEFT JOIN ".$this->tabelle['GN_ANAGRAFICHE']->getTabName()." AS t5 ON t1.cod_anagra_util=t5.cod_anagra";
        $this->query.=" LEFT JOIN ".$this->tabelle['GN_ANAGRAFICHE']->getTabName()." AS t6 ON t1.cod_anagra_intest=t6.cod_anagra";
        
        $this->query.=" WHERE t1.num_rif_veicolo='".$arr['rif']."' AND t2.ind_annullo='N' AND t1.ind_preventivo='N'";
        
        $this->query.=" ORDER BY isnull(t1.dat_entrata_veicolo,t2.dat_inserimento),t2.num_rif_movimento";

        return true;

    }

    function getOccupazioneAgenda($arr) {
       
        $this->query="SELECT

            SUM(tot.ore) as ore

            FROM (
                SELECT
                SUM(md.qta_ore_prenotazione) as ore
                FROM ".$this->tabelle['GN_MOVDET']->getTabName()." AS md
                INNER JOIN ".$this->tabelle['GN_MOVTES_OFF']->getTabName()." AS mdo ON md.num_rif_movimento=mdo.num_rif_movimento
                WHERE md.ind_tipo_riga='I' AND CONVERT(varchar(8),md.dat_prenotazione_inc,112)>='".$arr['inizio']."' AND CONVERT(varchar(8),md.dat_prenotazione_inc,112)<='".$arr['fine']."' AND mdo.cod_officina IN (".substr($arr['reparti'],0,-1).")
                GROUP BY md.num_rif_movimento
                union all
                SELECT
                SUM(mdp.qta_prenotazione_det) as ore
                FROM ".$this->tabelle['GN_MOVDET_PRENOTAZIONI']->getTabName()." AS mdp
                INNER JOIN ".$this->tabelle['GN_MOVTES_OFF']->getTabName()." AS mdpo ON mdp.num_rif_movimento=mdpo.num_rif_movimento
                WHERE CONVERT(varchar(8),mdp.dat_prenotazione_det,112)>='".$arr['inizio']."' AND CONVERT(varchar(8),mdp.dat_prenotazione_det,112)<='".$arr['fine']."' AND mdpo.cod_officina IN (".substr($arr['reparti'],0,-1).")
                GROUP BY mdp.num_rif_movimento
            ) as tot
        ";

        return true;

    }

    function getPraticaRif($a) {
        //recupera i riferimenti delle commesse di una pratica ($pren=S=prenotazione)

        $this->query="SELECT
            t1.num_rif_movimento AS rif,
            '".$a['pren']."' AS pren
        ";

        $this->query.=" FROM ".$this->tabelle['GN_MOVTES_OFF_PRATICHE']->getTabName()." AS t1";
        $this->query.=" INNER JOIN".$this->tabelle['GN_MOVTES_OFF']->getTabName()." AS t2 ON t2.num_rif_movimento=t1.num_rif_movimento";

        $this->query.=" WHERE t1.num_pratica='".$a['pratica']."'";

        if ($a['pren']=='N') {
            $this->query.=" AND ISNULL(t2.num_commessa,0)!=0";
        }
        else {
            $this->query.=" AND ISNULL(t2.num_commessa,0)=0";
        }

        return true;

    }

    function richiesteMateriale($arg) {

        $this->query="SELECT
            '' as status,
            ric.cod_tipo_articolo as precodice,
            ric.cod_articolo as articolo,
            ric.des_riga as descrizione
            ric.val_prezzo_unitario AS listino,
            ric.prc_sconto AS sconto1,
            ric.prc_sconto_add AS sconto2,
            ric.num_qta AS quantita_ric,
            ric.num_rif_commessa AS rif,
            ric.num_riga AS riga,
            ric.cod_inconveniente_rich AS lam,
            '' AS id_documento_cliente,
            ''AS id_ordine_cliente,
            ric.num_rif_richiesta AS id_richiesta,
            '' AS canale,
            '' AS tipo_doc_ordcli,
            '' AS data_doc_ordcli,
            0 AS num_doc_ordcli,
            '' AS anno_ordcli,
            mtes.cod_anagra AS id_cliente_ordcli,
            '' AS id_riga_ordcli,
            '' AS deposito_ordcli,
            ric.num_qta AS quantita_ordcli,
            ric.num_qta_oc AS qta_eva_ordcli,
            ric.num_qta_mancate AS qta_dev_ordcli,
            ofc.cod_magazzino_associato AS deposito,
            gia.num_giacenza_attuale AS giacenza,
            (gia.num_impegnato_magazzino+gia.num_impegnato_officina) AS impegnato,
            isnull(ofor.num_ordine_numero,0) AS id_ordine_for,
            isnull(ofor.qta_ordine,0) AS quantita_ordfor,
            isnull(ofor.qta_consegnata,0) AS qta_eva_ordfor,
            isnull(ofor.qta_ordine-ofor.qta_consegnata,0) AS qta_dev_ordfor            
        ";

        $this->query.=" FROM ".$this->tabelle['MG_RICHIESTE_DET']->getTabName()." AS ric";
        $this->query.=" LEFT JOIN ".$this->tabelle['MG_RICHIESTE']->getTabName()." AS tocli on ric.num_rif_richiesta=tocli.num_rif_richiesta";
        $this->query.=" INNER JOIN ".$this->tabelle['GN_MOVTES_OFF']->getTabName()." AS c on ric.num_rif_commessa=c.num_rif_movimento";
        $this->query.=" INNER JOIN ".$this->tabelle['TB_OFF_OFFICINE']->getTabName()." AS ofc on ofc.cod_officina=c.cod_officina";
        $this->query.=" LEFT JOIN ".$this->tabelle['MG_ANAART_SALDI']->getTabName()." AS gia on ric.cod_tipo_articolo=gia.cod_tipo_articolo AND ric.cod_articolo=gia.cod_articolo AND ofc.cod_magazzino_associato=gia.cod_magazzino";
        $this->query.=" LEFT JOIN ".$this->tabelle['MG_ORDINI_DET']->getTabName()." AS ofor on ric.num_oc_numero=ofor.num_ordine_numero AND ric.num_oc_riga=ofor.num_riga AND ric.num_oc_anno=ofor.num_ordine_anno";
        $this->query.=" LEFT JOIN ".$this->tabelle['GN_MOVTES']->getTabName()." AS mtes on mtes.num_rif_movimento=c.num_rif_movimento";
        
        $this->query.=" WHERE ric.num_rif_commessa='".$arg['rif']."' AND ric.ind_annullo='N' AND ( (isnull(ric.ind_su_commessa,'N')='N' AND tocli.ind_evasa='N' AND ric.num_qta_mancate>0) OR (ofor.ind_evasa='N' AND isnull(ofor.qta_ordine-ofor.qta_consegnata,0)>0) )";

        return true;
    }

    /////////////////////////////////////////////////////////////////////////

    function allineaDms($arr) {
        
        //echo json_encode($arr);

        /*{"officina":"PC","tecnico":"CARR","d":20230228,"dispo":45}
        {"officina":"PP","tecnico":"DIAPP","d":20230228,"dispo":6}
        {"officina":"PP","tecnico":"MECPP","d":20230228,"dispo":19}
        {"officina":"PR","tecnico":"REVPU","d":20230228,"dispo":3}*/

        $this->query="IF (SELECT COUNT(*) FROM TB_OFF_OPERAI WHERE cod_operaio='".$arr['tecnico']."' AND cod_officina='".$arr['officina']."') > 0
            BEGIN
                DELETE OF_RILTEM_ENTRATE WHERE cod_operaio='".$arr['tecnico']."' AND cod_officina='".$arr['officina']."' AND CONVERT(varchar(8),dat_entrata,112)='".$arr['d']."'
                INSERT INTO OF_RILTEM_ENTRATE (num_rif_entrata,cod_operaio,dat_entrata,qta_ore_lavorate,cod_officina,prc_produttivita)
                VALUES(
                (SELECT max(num_rif_entrata)+1 FROM OF_RILTEM_ENTRATE),
                '".$arr['tecnico']."',
                '".$arr['d']."',
                '".$arr['dispo']."',
                '".$arr['officina']."',
                100	
                )
            END
        ";

        return true;

    }

    function ultimaRevisione($arr) {

        $this->query="SELECT
            a.cod_varie,
            convert(varchar(8),c.dat_documento_i,112) AS d_fatt,
            d.num_rif_veicolo,
            vei.mat_targa AS targa,
            vei.mat_telaio AS telaio,
            isnull(d.cod_anagra_util,0) AS cod_anagra_util,
            util.des_ragsoc AS util_ragsoc,
            util.des_email1 AS util_mail,
            util.des_indir_res AS util_indirizzo,
            util.des_localita_res AS util_localita,
            util.cod_cap_res AS util_cap,
            util.sig_prov_res AS util_provincia,
            '' AS util_mkt,
            util.sig_tel_res+' - '+util.sig_tel1_res AS util_tel,
            isnull(d.cod_anagra_intest,0) AS cod_anagra_intest,
            intest.des_ragsoc AS intest_ragsoc,
            intest.des_email1 AS intest_mail,
            intest.des_indir_res AS intest_indirizzo,
            intest.des_localita_res AS intest_localita,
            intest.cod_cap_res AS intest_cap,
            intest.sig_prov_res AS intest_provincia,
            '' AS intest_mkt,
            intest.sig_tel_res+' - '+intest.sig_tel1_res AS intest_tel
        ";

        $this->query.=" FROM ".$this->tabelle['GN_MOVDET']->getTabName()." AS a";
        $this->query.=" INNER JOIN ".$this->tabelle['GN_MOVTES']->getTabName()." AS b on a.num_rif_movimento=b.num_rif_movimento";
        $this->query.=" INNER JOIN ".$this->tabelle['GN_MOVTES_DOC']->getTabName()." AS c on b.num_rif_movimento=c.num_rif_movimento";
        $this->query.=" INNER JOIN ".$this->tabelle['GN_MOVTES_OFF']->getTabName()." AS d on b.num_rif_movimento=d.num_rif_movimento";
        $this->query.=" INNER JOIN ".$this->tabelle['VE_ANAVEI']->getTabName()." AS vei on d.num_rif_veicolo=vei.num_rif_veicolo";
        $this->query.=" LEFT JOIN ".$this->tabelle['GN_ANAGRAFICHE']->getTabName()." AS util on util.cod_anagra=d.cod_anagra_util";
        $this->query.=" LEFT JOIN ".$this->tabelle['GN_ANAGRAFICHE']->getTabName()." AS intest on intest.cod_anagra=d.cod_anagra_intest";

        $this->query.=" WHERE a.cod_varie='OREVIS' AND a.cod_movimento!='OOPN' AND b.ind_annullo='N' AND ind_chiuso='S' AND convert(varchar(8),c.dat_documento_i,112)>='".$arr['inizio']."' AND convert(varchar(8),c.dat_documento_i,112)<='".$arr['fine']."'";
        
        return true;
    }

    function totFattCliente($arr) {

        $this->query="SELECT
			sum(CASE WHEN b.cod_movimento='OOP' THEN a.val_imponibile_tot ELSE 0 END) AS tot,
            sum(CASE WHEN b.cod_movimento='OOPN' THEN a.val_imponibile_tot ELSE 0 END) AS nac
        ";

        $this->query.=" FROM ".$this->tabelle['GN_MOVTES_DOC']->getTabName()." as a";
        $this->query.=" INNER JOIN ".$this->tabelle['GN_MOVTES']->getTabName()." as b ON a.num_rif_movimento=b.num_rif_movimento";

        $this->query.=" WHERE b.cod_reparto='OFF' AND a.cod_anagrafica='".$arr['cliente']."'";
        
        if (isset($arr['d'])) $this->query.=" AND isnull(CONVERT(varchar(8),a.dat_documento_i,112),'')>'".$arr['d']."'";

        return true;
    }

} 


?>