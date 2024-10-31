<?php

include_once(DROOT.'/nebula/galileo/gab500/tabs/FIDEL_tipi.php');
include_once(DROOT.'/nebula/galileo/gab500/tabs/FIDEL_offerte.php');
include_once(DROOT.'/nebula/galileo/gab500/tabs/FIDEL_voucher.php');

class galileoFidel extends galileoOps {

    function __construct() {

        $this->tabelle['FIDEL_tipi']=new fidel_tipi();
        $this->tabelle['FIDEL_offerte']=new fidel_offerte();
        $this->tabelle['FIDEL_voucher']=new fidel_voucher();
    }

    function getLizard($arr) {

        $this->query="SELECT
            t1.*,
            coalesce(t3.titolo,'Generico') AS tipo
        ";

        $this->query.=" FROM ".$this->tabelle['FIDEL_voucher']->getTabName()." AS t1";
        $this->query.=" LEFT JOIN ".$this->tabelle['FIDEL_offerte']->getTabName()." AS t2 ON t1.template=t2.ID";
        $this->query.=" LEFT JOIN ".$this->tabelle['FIDEL_tipi']->getTabName()." AS t3 ON t2.tipo=t3.tag";

        $this->query.=" WHERE t1.scadenza>='".$arr['da']."' AND t1.scadenza<='".$arr['a']."'";

        if (isset($arr['stato']) && $arr['stato']!="") $this->query.=" AND stato='".$arr['stato']."'";

        return true;

    }

}


?>