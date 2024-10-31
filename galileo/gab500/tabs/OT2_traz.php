<?php

class galileo_ot2_traz extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.OT2_traz";
        $this->selectMap=array(
            "codice",
            "selezione",
            "descrizione"
        );
    }

    function evaluate($tipo){
    }
}

?>