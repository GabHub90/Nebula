<?php

include_once(DROOT.'/nebula/galileo/gab500/tabs/MAESTRO_reparti.php');
include_once(DROOT.'/nebula/galileo/gab500/tabs/MAESTRO_macroreparti.php');
include_once(DROOT.'/nebula/galileo/gab500/tabs/MAESTRO_area_rep.php');
include_once(DROOT.'/nebula/galileo/gab500/tabs/MAESTRO_aree.php');
include_once(DROOT.'/nebula/galileo/gab500/tabs/MAESTRO_sedi.php');
include_once(DROOT.'/nebula/galileo/gab500/tabs/MAESTRO_wormhole.php');

class galileoMaestroReparti extends galileoOps {

    function __construct() {

        $this->tabelle['MAESTRO_reparti']=new maestro_reparti();
        $this->tabelle['MAESTRO_macroreparti']=new maestro_macroreparti();
        $this->tabelle['MAESTRO_area_rep']=new maestro_area_rep();
        $this->tabelle['MAESTRO_aree']=new maestro_aree();
        $this->tabelle['MAESTRO_sedi']=new maestro_sedi();
        $this->tabelle['MAESTRO_wormhole']=new maestro_wormhole();
    }

    function getReparti($macrorep) {

        //ritorna tutti i reparti jointati con i relativi macroreparti

        $this->query="SELECT ";

        $this->query.="trep.tag AS reparto,
                trep.descrizione,
                trep.concerto,
                trep.infinity,
                trep.sede,
                trep.virtuale,
                trep.fore,
                tmrep.tipo AS macroreparto,
                tmrep.descrizione AS des_macroreparto,
                ar.area,
                isnull(ar.off_concerto,'') AS off_concerto,
                isnull(ar.mag_concerto,'') AS mag_concerto,
                aree.descrizione AS des_area     
        ";

        $this->query.=" FROM ".$this->tabelle['MAESTRO_reparti']->getTabName()." AS trep ";
        $this->query.=" INNER JOIN ".$this->tabelle['MAESTRO_macroreparti']->getTabName()." AS tmrep ON trep.tipo=tmrep.tipo ";
        $this->query.=" LEFT JOIN ".$this->tabelle['MAESTRO_area_rep']->getTabName()." AS ar ON trep.tag=ar.reparto ";
        $this->query.=" LEFT JOIN ".$this->tabelle['MAESTRO_aree']->getTabName()." AS aree ON aree.tag=ar.area ";

        $this->query.=" WHERE trep.virtuale='N' AND trep.stato='1'";

        if ($macrorep!="") {
            $this->query.=" AND trep.tipo='".$macrorep."' ";
        }

        if ($this->orderBy!="") {
            $this->query.="ORDER BY ".$this->orderBy;
        }

    }

    function getReparto($reparto) {
        //ritorna le caratteristiche di uno specifico reparto jointato con il macroreparto

        $this->query="SELECT ";

        $this->query.="trep.tag AS reparto,
                    trep.descrizione,
                    trep.concerto,
                    trep.infinity,
                    trep.sede,
                    trep.virtuale,
                    tmrep.tipo AS macroreparto,
                    tmrep.descrizione AS des_macroreparto       
        ";

        $this->query.="FROM ".$this->tabelle['MAESTRO_reparti']->getTabName()." AS trep ";
        $this->query.="LEFT JOIN ".$this->tabelle['MAESTRO_macroreparti']->getTabName()." AS tmrep ON trep.tipo=tmrep.tipo ";

        $this->query.="WHERE trep.tag='".$reparto."' ";
    }

    function getOfficine() {

        //ritorna tutte le officine con i relativi dati di reparto

        $this->query="SELECT ";

        $this->query.="trep.tag AS reparto,
                    trep.descrizione,
                    trep.concerto,
                    trep.infinity,
                    trep.sede,
                    trep.virtuale,
                    tmrep.tipo AS macroreparto,
                    tmrep.descrizione AS des_macroreparto       
        ";

        $this->query.="FROM ".$this->tabelle['MAESTRO_reparti']->getTabName()." AS trep ";
        $this->query.="INNER JOIN ".$this->tabelle['MAESTRO_macroreparti']->getTabName()." AS tmrep ON trep.tipo=tmrep.tipo ";
        $this->query.="WHERE trep.tipo='S' AND (isnull(trep.concerto,'')!='' OR isnull(trep.infinity,'')!='') ";
        $this->query.="ORDER BY trep.concerto";
    }

    function getMagazzini() {

        //ritorna tutti i magazzini con i relativi dati di reparto

        $this->query="SELECT ";

        $this->query.="trep.tag AS reparto,
                    trep.descrizione,
                    trep.concerto,
                    trep.sede,
                    trep.virtuale,
                    tmrep.tipo AS macroreparto,
                    tmrep.descrizione AS des_macroreparto       
        ";

        $this->query.="FROM ".$this->tabelle['MAESTRO_reparti']->getTabName()." AS trep ";
        $this->query.="INNER JOIN ".$this->tabelle['MAESTRO_macroreparti']->getTabName()." AS tmrep ON trep.tipo=tmrep.tipo ";
        $this->query.="WHERE trep.tipo='M' AND (isnull(trep.concerto,'')!='' OR isnull(trep.infinity,'')!='')";
        $this->query.="ORDER BY trep.tag";
    }

} 


?>