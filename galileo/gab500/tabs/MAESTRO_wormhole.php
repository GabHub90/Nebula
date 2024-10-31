<?php

class maestro_wormhole extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.MAESTRO_wormhole";
        $this->selectMap=array(
            "reparto",
            "inizio",
            "fine",
            "dms"
        );
        
    }

    function evaluate($tipo){
    }
}

?>