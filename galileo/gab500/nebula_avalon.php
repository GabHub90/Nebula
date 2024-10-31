<?php

include_once(DROOT.'/nebula/galileo/gab500/tabs/AVALON_lamextra.php');
include_once(DROOT.'/nebula/galileo/gab500/tabs/AVALON_lamop.php');
include_once(DROOT.'/nebula/galileo/gab500/tabs/AVALON_stato_lam.php');

class nebulaAvalonDB extends galileoOps {

    function __construct() {

        $this->tabelle['AVALON_lamextra']=new avalon_lamextra();
        $this->tabelle['AVALON_lamop']=new avalon_lamop();
        $this->tabelle['AVALON_stato_lam']=new avalon_statolam();
    }

    function getLamExtra($dms,$rif,$lam) {

        $this->query="SELECT
            dms,
            rif,
            lam,
            pos
        ";

        $this->query.=" FROM ".$this->tabelle['AVALON_lamextra']->getTabName();

        $this->query.=" WHERE dms='".$dms."' AND rif='".$rif."' AND lam='".$lam."'";

        return true;
    }

}


?>