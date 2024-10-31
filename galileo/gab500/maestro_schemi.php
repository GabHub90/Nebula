<?php

include_once(DROOT.'/nebula/galileo/gab500/tabs/QUARTET_panorami.php');
include_once(DROOT.'/nebula/galileo/gab500/tabs/QUARTET_schemi.php');
include_once(DROOT.'/nebula/galileo/gab500/tabs/QUARTET_pansk.php');
include_once(DROOT.'/nebula/galileo/gab500/tabs/QUARTET_turni.php');
include_once(DROOT.'/nebula/galileo/gab500/tabs/QUARTET_collsk.php');
include_once(DROOT.'/nebula/galileo/gab500/tabs/QUARTET_subrep.php');
include_once(DROOT.'/nebula/galileo/gab500/tabs/QUARTET_pan_subrep.php');

class galileoMaestroSchemi extends galileoOps {

    function __construct() {

        $this->tabelle['QUARTET_panorami']=new quartet_panorami();
        $this->tabelle['QUARTET_schemi']=new quartet_schemi();
        $this->tabelle['QUARTET_pan_sk']=new quartet_pansk();
        $this->tabelle['QUARTET_turni']=new quartet_turni;
        $this->tabelle['QUARTET_coll_sk']=new quartet_collsk;
        $this->tabelle['QUARTET_subrep']=new quartet_subrep;
        $this->tabelle['QUARTET_pan_subrep']=new quartet_pan_subrep;
        
    }

    function getSchemi($panorama) {

        $this->query="SELECT
            sk.*,
            pansk.data_i,
            pansk.blocco_inizio,
            pansk.posizione,
            pansk.pan as panorama
        ";

        $this->query.=" FROM ".$this->tabelle['QUARTET_schemi']->getTabName()." AS sk";
        $this->query.=" INNER JOIN ".$this->tabelle['QUARTET_pan_sk']->getTabName()." AS pansk ON pansk.skema=sk.codice AND pansk.pan='".$panorama."'";

        $this->query.=" ORDER BY pansk.posizione;";

    }

    function getCollSk($panorama,$rif) {
        //ritorna gli abbinamento tra collaboratori e skemi del panorama

        $this->query="SELECT
            csk.*,
            psk.posizione
        ";

        $this->query.=" FROM ".$this->tabelle['QUARTET_coll_sk']->getTabName()." AS csk";
        $this->query.=" INNER JOIN ".$this->tabelle['QUARTET_pan_sk']->getTabName()." AS psk ON csk.skema=psk.skema AND csk.panorama=psk.pan";

        $this->query.=" WHERE csk.panorama='".$panorama."' AND csk.data_i<='".$rif."' AND csk.data_f>='".$rif."'";

        $this->query.=" ORDER BY csk.collaboratore,psk.posizione";
    }

    function getSubrep($panorama) {

        $this->query="SELECT
            sub.*,
            isnull(sub.reparto,'') AS reparto,
            ps.panorama,
            ps.cod_def,
            ps.pos
        ";

        $this->query.=" FROM ".$this->tabelle['QUARTET_pan_subrep']->getTabName()." AS ps";
        $this->query.=" INNER JOIN ".$this->tabelle['QUARTET_subrep']->getTabName()." AS sub ON ps.subrep=sub.subrep";

        $this->query.=" WHERE ps.panorama='".$panorama."'";

        $this->query.=" ORDER BY ps.pos";
    }

    function getCollSkIntervallo($wclause,$inizio,$fine) {

        $this->query="SELECT
            csk.*,
            sk.*,
            psk.data_i AS data_rif,
            psk.blocco_inizio AS turno_rif
        ";

        $this->query.=" FROM ".$this->tabelle['QUARTET_coll_sk']->getTabName()." AS csk";
        $this->query.=" INNER JOIN ".$this->tabelle['QUARTET_schemi']->getTabName()." AS sk ON csk.skema=sk.codice";
        $this->query.=" INNER JOIN ".$this->tabelle['QUARTET_pan_sk']->getTabName()." AS psk ON psk.pan=csk.panorama AND psk.skema=csk.skema";
        $this->query.=" INNER JOIN ".$this->tabelle['QUARTET_panorami']->getTabName()." AS pan ON csk.panorama=pan.ID";

        $this->query.=" WHERE pan.stato='A' AND csk.data_i<='".$fine."' AND csk.data_f>='".$inizio."'";

        if ($wclause!="") $this->query.=" AND ".$wclause;

        $this->query.=" ORDER BY csk.collaboratore,csk.data_i";
        
    }

    function getPanSkIntervallo($wclause,$inizio,$fine) {
        //ritorna tutti i subreps collegati ad un panorama a sua volta collegato ad un reparto
        //ATTENZIONE - i subrep collegati a panorami differenti possono anche essere gli stessi

        $this->query="SELECT
            ps.*,
            psk.skema,
            psk.data_i AS inizio_skema,
            psk.blocco_inizio,
            psk.posizione,
            sk.titolo,
            sk.turnazione,
            sk.flag_festivi,
            sk.flag_turno,
            sk.on_flag,
            sk.mark,
            sk.exclusive,
            sk.overall,
            sk.griglia
        ";

        $this->query.=" FROM (
                SELECT
                pan.*,
                b.inizio AS rif_inizio
                FROM ".$this->tabelle['QUARTET_panorami']->getTabName()." AS pan
                INNER JOIN (
                    SELECT
                    max(inizio) AS inizio,
                    reparto
                    FROM ".$this->tabelle['QUARTET_panorami']->getTabName()."
                    WHERE inizio<='".$inizio."' AND stato='A'
                    GROUP BY reparto
                ) AS b ON pan.reparto=b.reparto

            WHERE pan.stato='A' AND pan.inizio>=b.inizio AND pan.inizio<='".$fine."' 
        ";

        if ($wclause!="") $this->query.=" AND ".$wclause;
        
        $this->query.=") AS ps";

        $this->query.=" INNER JOIN ".$this->tabelle['QUARTET_pan_sk']->getTabName()." AS psk ON psk.pan=ps.ID";

        $this->query.=" INNER JOIN ".$this->tabelle['QUARTET_schemi']->getTabName()." AS sk ON psk.skema=sk.codice";

        $this->query.=" ORDER BY ps.reparto,ps.ID,psk.posizione";
    }

    function getPanSubsIntervallo($wclause,$inizio,$fine) {

        $this->query="SELECT
            ps.reparto,
            ps.ID AS panorama,
            psr.subrep,
            psr.cod_def,
            psr.pos,
            isnull(sr.reparto,'') AS rep_proprietario,
            sr.macroreparto AS rep_macroreparto,
            sr.descrizione,
            sr.concerto,
            sr.off_concerto,
            sr.infinity,
            sr.off_infinity
        ";

        $this->query.=" FROM (
            SELECT
            pan.*,
            b.inizio AS rif_inizio
            FROM ".$this->tabelle['QUARTET_panorami']->getTabName()." AS pan
            INNER JOIN (
                SELECT
                max(inizio) AS inizio,
                reparto
                FROM ".$this->tabelle['QUARTET_panorami']->getTabName()."
                WHERE inizio<='".$inizio."' AND stato='A'
                GROUP BY reparto
            ) AS b ON pan.reparto=b.reparto

            WHERE pan.stato='A' AND pan.inizio>=b.inizio AND pan.inizio<='".$fine."'
        ";
        
        if ($wclause!="") $this->query.=" AND ".$wclause; 

        $this->query.=") AS ps";

        $this->query.=" INNER JOIN ".$this->tabelle['QUARTET_pan_subrep']->getTabName()." AS psr ON psr.panorama=ps.ID";
        $this->query.=" INNER JOIN ".$this->tabelle['QUARTET_subrep']->getTabName()." AS sr ON psr.subrep=sr.subrep AND sr.stato='1'";

        $this->query.=" ORDER BY ps.reparto,ps.ID,psr.pos";

    }

    function getRepSk($reparto) {

        $this->query="SELECT
            sk.*,
            isnull(elc.elem,0) AS elem
        ";

        $this->query.=" FROM ".$this->tabelle['QUARTET_schemi']->getTabName()." AS sk";

        $this->query.=" LEFT JOIN (SELECT 
            csk.skema,count(*) AS elem
            FROM ".$this->tabelle['QUARTET_coll_sk']->getTabName()." AS csk
            INNER JOIN ".$this->tabelle['QUARTET_panorami']->getTabName()." AS pan ON csk.panorama=pan.ID AND pan.stato='A'
            GROUP BY csk.skema
        ) AS elc ON elc.skema=sk.codice";

        $this->query.=" WHERE sk.reparto='".$reparto."'";

        $this->query.=" ORDER BY sk.codice";

        return true;
    }

    function getCollskMaxDate($panorama,$collaboratore,$skema) {

        $this->query="SELECT 
        isnull(max(data_f),'') AS fine,
        isnull(max(data_i),'') AS inizio
        ";

        $this->query.=" FROM ".$this->tabelle['QUARTET_coll_sk']->getTabName()." AS csk";

        $this->query.=" WHERE panorama='".$panorama."' AND collaboratore='".$collaboratore."' AND skema='".$skema."'";

        return true;
    }

    function getSubsPerRep($reparto) {

        $this->query="SELECT
            sbr.subrep,
            isnull(sbr.reparto,'') AS reparto,
            sbr.macroreparto,
            sbr.descrizione
        ";

        $this->query.=" FROM ".$this->tabelle['QUARTET_subrep']->getTabName()." AS sbr";

        $this->query.=" WHERE sbr.reparto='".$reparto."' OR sbr.reparto IS NULL";

        $this->query.=" ORDER BY reparto,subrep";

        return true;
    }

    function getOrariOA($reparto,$d) {

        $this->query="SELECT *";

        $this->query.=" FROM ".$this->tabelle['QUARTET_panorami']->getTabName()." AS pan";
        $this->query.=" INNER JOIN ".$this->tabelle['QUARTET_turni']->getTabName()." AS tur ON pan.orariOA=tur.codice";
        $this->query.=" INNER JOIN (
                SELECT 
                max(ID) as maxID
                FROM QUARTET2_panorami
                WHERE stato='A' AND reparto='".$reparto."' AND inizio<='".$d."'
            ) AS t3 on pan.ID=t3.maxID
        ";

        $this->query.="ORDER BY wd";

        return true;
    }

} 


?>