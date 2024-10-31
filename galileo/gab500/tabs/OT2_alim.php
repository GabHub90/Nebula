<?php

class galileo_ot2_alim extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.OT2_alim";
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