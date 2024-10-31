<?php

include_once(DROOT.'/nebula/galileo/gab500/tabs/CALENDARIO_feste.php');
include_once(DROOT.'/nebula/galileo/gab500/tabs/CALENDARIO_chiusure.php');

class galileoCalendario extends galileoOps {

    function __construct() {

        $this->tabelle['CALENDARIO_feste']=new calendario_feste();
        $this->tabelle['CALENDARIO_chiusure']=new calendario_chiusure();
    }

} 


?>