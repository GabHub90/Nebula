<?php

class maestro_app extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.MAESTRO_app";
        $this->selectMap=array(
            "livello",
            "tag",
            "galassia",
            "sistema",
            "funzione",
            "stato",
            "modificatore"
        );
        
    }

    function evaluate($tipo){
    }
}

?>