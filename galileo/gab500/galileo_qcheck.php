<?php

include_once(DROOT.'/nebula/galileo/gab500/tabs/QCHECK_abbinamenti.php');
include_once(DROOT.'/nebula/galileo/gab500/tabs/QCHECK_versioni.php');
include_once(DROOT.'/nebula/galileo/gab500/tabs/QCHECK_moduli.php');
include_once(DROOT.'/nebula/galileo/gab500/tabs/QCHECK_storico_controlli.php');
include_once(DROOT.'/nebula/galileo/gab500/tabs/QCHECK_storico_moduli.php');
include_once(DROOT.'/nebula/galileo/gab500/tabs/QCHECK_auth.php');

class galileoQcheck extends galileoOps {

    function __construct() {

        $this->tabelle['QCHECK_abbinamenti']=new qcheck_abbinamenti();
        $this->tabelle['QCHECK_versioni']=new qcheck_versioni();
        $this->tabelle['QCHECK_moduli']=new qcheck_moduli();
        $this->tabelle['QCHECK_storico_controlli']=new qcheck_storico_controlli();
        $this->tabelle['QCHECK_storico_moduli']=new qcheck_storico_moduli();
        $this->tabelle['QCHECK_auth']=new qcheck_auth();
    }

    function baseSelect() {

        $this->query="SELECT
                qc.ID AS ID_controllo,
                qc.controllo,
                qc.reparto,
                qc.d_controllo,
                qc.esecutore AS esecutore_controllo,
                qc.versione,
                qc.chiave,
                qc.intestazione,
                qc.stato AS stato_controllo,
                qc.ID_abbinamento,
                qm.modulo,
                qm.d_modulo,
                qm.esecutore,
                qm.operatore,
                qm.variante,
                qm.risposte,
                qm.punteggio,
                qm.stato AS stato_modulo,
                mod.titolo AS des_modulo,
                isnull(rif_mod.esecutore,'') AS des_rif_operatore
        ";

        $this->query.=" FROM ".$this->tabelle['QCHECK_storico_controlli']->getTabName()." AS qc";

        $this->query.=" INNER JOIN ".$this->tabelle['QCHECK_storico_moduli']->getTabName()." AS qm ON qc.ID=qm.ID_controllo";

        $this->query.=" INNER JOIN ".$this->tabelle['QCHECK_moduli']->getTabName()." AS mod ON qm.modulo=mod.ID";

        $this->query.=" LEFT JOIN ".$this->tabelle['QCHECK_storico_moduli']->getTabName()." AS rif_mod ON rif_mod.modulo=qm.rif_operatore AND rif_mod.ID_controllo=qm.ID_controllo";
    }

    function getForm($arr) {

        $this->baseSelect();

        $this->query.=" WHERE qc.ID='".$arr['ID']."' AND qm.modulo='".$arr['modulo']."'";

        return true;
    }

    function getListaAperti($arr) {

        $this->baseSelect();

        $this->query.=" WHERE qc.stato='aperto' AND qc.controllo='".$arr['controllo']."' AND qc.reparto='".$arr['reparto']."'";

        if ($arr['flag']=="1") {
            $this->query.=" AND qm.esecutore IN ('','".$arr['logged']."')";
        }

        $this->query.=" ORDER BY qc.d_controllo DESC,qc.ID DESC,qm.modulo";

        //serve perché viene chiamata da EXECUTE_GENERIC
        //è sempre true perché è una SELECT
        return true;

    }

    function insertNewCheck($arr) {

        $this->arrQuery=array();
        $this->errorFlag=true;
        //indica la tabella temporanea
        $this->incsetTab='#resvar';

        $incSet="ID_controllo";

        ///////////////////////////////////////////    
        $res=$this->doTransactionHead("QCHECK_storico_controlli",$this->incsetTab);

        if (!$res) {
            $this->errorFlag=false;
            return $this->errorFlag;
        }
        //////////////////////////////////////////

        /*
        $args=array(
            "controllo"=>array(),
            "moduli"=>array()
        );
        */

        //inserimento controllo:
        $this->query="";

        //doInsert($tabella,$arr,$wclause,$incset)
        $res=$this->doInsert("QCHECK_storico_controlli",$arr['controllo'],"",$incSet,"query");

        if (!$res) {
            $this->errorFlag=false;
            return $this->errorFlag;
        }

        //inserimento moduli:
        foreach ($arr['moduli'] as $m) {

            $m['ID_controllo']='@'.$incSet;

            $res=$this->doInsert("QCHECK_storico_moduli",$m,"","","query");

            if (!$res) {
                $this->errorFlag=false;
                return $this->errorFlag;
            }
        }

        /////////////////////////////////////////////
        $this->arrQuery[]=$this->query;
    
        $this->arrQuery[]=$this->tabelle['QCHECK_storico_controlli']->getResvar();

        //echo json_encode($this->arrQuery);

        return $this->errorFlag;
    }

    function getStoricoControlli($arr) {

        $this->query="";

        /*
        $args=array(
            "reparto"=>$this->reparto,
            "controllo"=>$this->controllo,
            "data_i"=>$this->config['data_i'],
            "data_f"=>$this->config['data_f'],
            "stato"=>$this->config['stato']
        );
        */

        $this->baseSelect();

        $stato="";
        foreach ($arr['stato'] as $s) {
            $stato.="'".$s."',";
        }

        $this->query.=" WHERE qc.stato IN (".substr($stato,0,-1).") AND qc.controllo='".$arr['controllo']."' AND qc.reparto='".$arr['reparto']."' AND d_controllo>='".$arr['data_i']."' AND d_controllo<='".$arr['data_f']."'";

        $this->query.=" ORDER BY qc.d_controllo,qc.ID,qm.modulo";

        return true;
    }

    function getModuliUtente($arr) {

        $this->query="SELECT
            sm.*,
            sc.controllo,
            abb.titolo,
            isnull(rif_mod.esecutore,'') AS des_rif_operatore
        ";

        $this->query.=" FROM ".$this->tabelle['QCHECK_storico_moduli']->getTabName()." AS sm";

        $this->query.=" INNER JOIN ".$this->tabelle['QCHECK_storico_controlli']->getTabName()." AS sc ON sc.ID=sm.ID_controllo";

        $this->query.=" INNER JOIN ".$this->tabelle['QCHECK_abbinamenti']->getTabName()." AS abb ON abb.controllo=sc.controllo AND abb.versione=sc.versione";

        $this->query.=" LEFT JOIN ".$this->tabelle['QCHECK_storico_moduli']->getTabName()." AS rif_mod ON rif_mod.modulo=sm.rif_operatore AND rif_mod.ID_controllo=sm.ID_controllo";

        $this->query.=" WHERE (sm.operatore='".$arr['utente']."' OR sm.esecutore='".$arr['utente']."' OR isnull(rif_mod.esecutore,'')='".$arr['utente']."')";

        $this->query.=" AND ( SUBSTRING(sm.d_modulo,1,8)>='".$arr['data_i']."' AND SUBSTRING(sm.d_modulo,1,8)<='".$arr['data_f']."' )";

        $this->query.=" AND sm.stato='chiuso' AND sc.stato!='annullato'";

        return true;
    }

    function getRepartiControllo($arr) {

        $this->query="SELECT
            ab.reparto
        ";

        $this->query.=" FROM ".$this->tabelle['QCHECK_abbinamenti']->getTabName()." AS ab";

        $this->query.=" WHERE ab.controllo='".$arr['controllo']."'";

        $this->query.=" GROUP BY ab.reparto";

        return true;
    }

    function getStoricoBase() {

        $this->query="SELECT
            sm.*,
            isnull(rif_mod.esecutore,'') AS des_rif_operatore,
            sc.reparto,
            sc.chiave,
            sc.intestazione,
            abb.titolo,
            ver.descrizione,
            ver.versione,
            md.titolo AS titolo_modulo,
            md.varianti
        ";

        $this->query.=" FROM ".$this->tabelle['QCHECK_storico_moduli']->getTabName()." AS sm";

        $this->query.=" INNER JOIN ".$this->tabelle['QCHECK_storico_controlli']->getTabName()." AS sc ON sc.ID=sm.ID_controllo";

        $this->query.=" INNER JOIN ".$this->tabelle['QCHECK_abbinamenti']->getTabName()." AS abb ON abb.controllo=sc.controllo AND abb.versione=sc.versione";

        $this->query.=" INNER JOIN ".$this->tabelle['QCHECK_versioni']->getTabName()." AS ver ON ver.controllo=sc.controllo AND ver.versione=sc.versione";

        $this->query.=" INNER JOIN ".$this->tabelle['QCHECK_moduli']->getTabName()." AS md ON md.ID=sm.modulo";

        $this->query.=" LEFT JOIN ".$this->tabelle['QCHECK_storico_moduli']->getTabName()." AS rif_mod ON rif_mod.modulo=sm.rif_operatore AND rif_mod.ID_controllo=sm.ID_controllo";

    }

    function getStorico($arr) {

        $this->getStoricoBase();

        $this->query.=" WHERE abb.controllo='".$arr['controllo']."' AND sm.stato='chiuso' AND substring(sm.d_modulo,1,8)>='".$arr['data_i']."' AND substring(sm.d_modulo,1,8)<='".$arr['data_f']."'";

        if ($arr['reparto']!="") $this->query.=" AND sc.reparto='".$arr['reparto']."'";
        if ($arr['modulo']!="") $this->query.=" AND sm.modulo='".$arr['modulo']."'";
        if ($arr['variante']!="") $this->query.=" AND sm.variante='".$arr['variante']."'";

        if ($arr['esecutore']!="" && $arr['esecutore']==$arr['operatore']) {
            $this->query.=" AND (sm.esecutore='".$arr['esecutore']."' OR (sm.operatore='".$arr['operatore']."' OR rif_mod.esecutore='".$arr['operatore']."') )";
        }
        else {
            if ($arr['esecutore']!="") $this->query.=" AND sm.esecutore='".$arr['esecutore']."'";
            if ($arr['operatore']!="") $this->query.=" AND (sm.operatore='".$arr['operatore']."' OR rif_mod.esecutore='".$arr['operatore']."')";
        }

        if ($arr['chiave']!="") $this->query.=" AND (sc.chiave LIKE '%".$arr['chiave']."%' OR sc.intestazione LIKE '%".$arr['chiave']."%')";

        $this->query.=" ORDER BY sm.ID_controllo,sm.modulo";

        return true;
    }

    function getView($arr) {

        $this->getStoricoBase();

        $this->query.=" WHERE sm.ID_controllo='".$arr['controllo']."' AND sm.modulo='".$arr['modulo']."'";

        return true;
    }


} 


?>