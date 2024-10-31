<?php

class maestro_gruppi extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.MAESTRO_gruppi";
        $this->selectMap=array(
            "ID",
            "tag",
            "descrizione",
            "reparto",
            "posizione",
            "macrogruppo",
            "stato"
        );
        
    }

    function evaluate($tipo){
    }
}

?>