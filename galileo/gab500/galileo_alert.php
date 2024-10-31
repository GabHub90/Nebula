<?php

include_once(DROOT.'/nebula/galileo/gab500/tabs/AVALON_stato_lam.php');

class galileoAlert extends galileoOps {

    function __construct() {

        $this->tabelle['AVALON_stato_lam']=new avalon_statolam();
    }

    function updateAlert($arr) {

        $wclause="pratica='".$arr['pratica']."' AND dms='".$arr['dms']."' AND rif='".$arr['rif']."' AND lam='".$arr['lam']."' AND pren='".$arr['pren']."'";

        $check=substr($this->getSelect('AVALON_stato_lam',$wclause),0,-1);

        $this->clearQuery();
        $res=$this->doInsert('AVALON_stato_lam',$arr,"","query");
        $insert=substr($this->getQuery(),0,-1);

        $update="UPDATE ".$this->tabelle['AVALON_stato_lam']->getTabName()." SET alert='".$arr['alert']."' WHERE ".$wclause;

        //////////////////////////////////////////////////////////////////////////
        $this->query="IF NOT EXISTS (".$check.") ".$insert.' ELSE '.$update.';';

        return true;
    }

}


?>