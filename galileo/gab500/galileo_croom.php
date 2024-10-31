<?php

include_once(DROOT.'/nebula/galileo/gab500/tabs/CROOM_modelli.php');
include_once(DROOT.'/nebula/galileo/gab500/tabs/C2R_responsabili.php');

class galileoCroom extends galileoOps {

    function __construct() {

        $this->tabelle['CROOM_modelli']=new croom_modelli();
        $this->tabelle['C2R_responsabili']=new c2r_responsabili();
    }

} 


?>