<?php

class galileo_ot2_marche extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.OT2_marche";
        $this->selectMap=array(
            "codice",
            "marca"
        );
    }

    function evaluate($tipo){
    }
}

?>