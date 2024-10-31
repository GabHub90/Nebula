<?php

class galileo_ot2_oggettibase extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.OT2_oggetti_base";
        $this->selectMap=array(
            "codice",
            "descrizione",
            "ambito",
            "pos",
            "stato",
            "main"
        );
    }

    function evaluate($tipo){
    }
}

?>