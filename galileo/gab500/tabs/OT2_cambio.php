<?php

class galileo_ot2_cambio extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.OT2_cambio";
        $this->selectMap=array(
            "codice",
            "selezione",
            "descrizione",
            "marca"
        );
    }

    function evaluate($tipo){
    }
}

?>