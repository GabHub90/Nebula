<?php

class galileo_ot2_manut extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.OT2_manut";
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