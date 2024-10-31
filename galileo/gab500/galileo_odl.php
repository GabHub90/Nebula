<?php

include_once(DROOT.'/nebula/galileo/gab500/tabs/OT2_marche.php');
include_once(DROOT.'/nebula/galileo/gab500/tabs/OT2_alim.php');
include_once(DROOT.'/nebula/galileo/gab500/tabs/OT2_traz.php');
include_once(DROOT.'/nebula/galileo/gab500/tabs/OT2_cambio.php');
include_once(DROOT.'/nebula/galileo/gab500/tabs/OT2_manut.php');
include_once(DROOT.'/nebula/galileo/gab500/tabs/OT2_ambiti.php');
include_once(DROOT.'/nebula/galileo/gab500/tabs/OT2_gruppi.php');
include_once(DROOT.'/nebula/galileo/gab500/tabs/OT2_link_modgru.php');
include_once(DROOT.'/nebula/galileo/gab500/tabs/OT2_criteri_mod.php');
include_once(DROOT.'/nebula/galileo/gab500/tabs/OT2_criteri_tel.php');
include_once(DROOT.'/nebula/galileo/gab500/tabs/OT2_passman.php');
include_once(DROOT.'/nebula/galileo/gab500/tabs/OT2_passman_righe.php');
include_once(DROOT.'/nebula/galileo/gab500/tabs/OT2_oggetti_base.php');
include_once(DROOT.'/nebula/galileo/gab500/tabs/OT2_oggetti_default.php');
include_once(DROOT.'/nebula/galileo/gab500/tabs/OT2_eventi.php');
include_once(DROOT.'/nebula/galileo/gab500/tabs/OT2_eventi_chk.php');

class galileoOdl extends galileoOps {

    function __construct() {

        $this->tabelle['OT2_marche']=new galileo_ot2_marche();
        $this->tabelle['OT2_alim']=new galileo_ot2_alim();
        $this->tabelle['OT2_traz']=new galileo_ot2_traz();
        $this->tabelle['OT2_cambio']=new galileo_ot2_cambio();
        $this->tabelle['OT2_manut']=new galileo_ot2_manut();
        $this->tabelle['OT2_ambiti']=new galileo_ot2_ambiti();
        $this->tabelle['OT2_gruppi']=new galileo_ot2_gruppi();
        $this->tabelle['OT2_link_modgru']=new galileo_ot2_linkmodgru();
        $this->tabelle['OT2_criteri_mod']=new galileo_ot2_criterimod();
        $this->tabelle['OT2_criteri_tel']=new galileo_ot2_criteritel();
        $this->tabelle['OT2_passman']=new galileo_ot2_passman();
        $this->tabelle['OT2_passman_righe']=new galileo_ot2_passmanrighe();
        $this->tabelle['OT2_oggetti_base']=new galileo_ot2_oggettibase();
        $this->tabelle['OT2_oggetti_default']=new galileo_ot2_oggettidefault();
        $this->tabelle['OT2_eventi']=new galileo_ot2_eventi();
        $this->tabelle['OT2_eventi_chk']=new galileo_ot2_eventichk();
    }

    function getPassMan($arr) {

        $this->query="SELECT
            t1.telaio,
            t1.indice,
            t1.tipo,
            t2.obj,
            isnull(t2.note,'') AS note,
            t3.descrizione
        ";

        $this->query.=" FROM ".$this->tabelle['OT2_passman_righe']->getTabName()." AS t1";
        $this->query.=" INNER JOIN ".$this->tabelle['OT2_passman']->getTabName()." AS t2 ON t1.telaio=t2.telaio AND t1.indice=t2.indice";
        $this->query.=" INNER JOIN ".$this->tabelle['OT2_oggetti_base']->getTabName()." AS t3 ON t1.tipo=t3.codice";

        $this->query.=" WHERE t1.telaio='".$arr['telaio']."'";

        $this->query.=" ORDER BY t1.indice,t1.tipo";

        return true;

    }

    function getOggettiBase($arr) {

        $this->query="
            SELECT
            t1.codice,
            t1.descrizione,
            t1.ambito,
            t1.pos,
            t1.stato,
            t1.main,
            t2.pos AS ambitopos
        ";

        $this->query.=" FROM ".$this->tabelle['OT2_oggetti_base']->getTabName()." AS t1";
        $this->query.=" INNER JOIN ".$this->tabelle['OT2_ambiti']->getTabName()." AS t2 ON t2.ambito=t1.ambito";

        $this->query.=" WHERE t1.stato='1'";

        $this->query.=" ORDER BY ambitopos,pos";

        return true;
    }

    function getOggettiDefault($arr) {

        $this->query="
            SELECT
            t1.codice,
            t1.dt,
            t1.dkm,
            t1.mint,
            t1.maxt,
            t1.stet,
            t1.topt,
            t1.minkm,
            t1.maxkm,
            t1.stekm,
            t1.topkm,
            t1.pcx,
            t1.first_km,
            t1.first_t,
            t2.pos,
            t3.pos AS ambitopos
        ";

        $this->query.=" FROM ".$this->tabelle['OT2_oggetti_default']->getTabName()." AS t1";
        $this->query.=" LEFT JOIN ".$this->tabelle['OT2_oggetti_base']->getTabName()." AS t2 ON t1.codice=t2.codice";
        $this->query.=" LEFT JOIN ".$this->tabelle['OT2_ambiti']->getTabName()." AS t3 ON t2.ambito=t3.ambito";

        $this->query.=" WHERE t1.stato='1' AND t1.marca='".$arr['marca']."'";

        $this->query.=" ORDER BY ambitopos,pos";

        return true;
    }

    function insertPassman($arr) {
        //è stato flaggato transaction

        $this->doInsert('OT2_passman',$arr,"","");

        foreach ($arr['righe'] as $r) {
            $a=array(
                "telaio"=>$arr['telaio'],
                "indice"=>$arr['indice'],
                "tipo"=>$r
            );

            $this->doInsert('OT2_passman_righe',$a,"","");
        }

        return true;
    }

    function updatePassman($arr) {
        //è stato flaggato transaction

        $this->doUpdate('OT2_passman',$arr,"telaio='".$arr['telaio']."' AND indice='".$arr['indice']."'","");

        $stato=$this->doDelete('OT2_passman_righe',"telaio='".$arr['telaio']."' AND indice='".$arr['indice']."'","");

        if (!$stato) return false;

        foreach ($arr['righe'] as $r) {
            $a=array(
                "telaio"=>$arr['telaio'],
                "indice"=>$arr['indice'],
                "tipo"=>$r
            );

            $this->doInsert('OT2_passman_righe',$a,"","");
        }

        return true;
    }

    function deletePassman($arr) {

        $stato=$this->doDelete('OT2_passman',"telaio='".$arr['telaio']."' AND indice='".$arr['indice']."'","");

        $stato=$this->doDelete('OT2_passman_righe',"telaio='".$arr['telaio']."' AND indice='".$arr['indice']."'","");

        if (!$stato) return false;

        return true;

    }

} 


?>