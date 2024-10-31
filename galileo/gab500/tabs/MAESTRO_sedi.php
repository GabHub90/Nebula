<?php

class maestro_sedi extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.MAESTRO_sedi";
        $this->selectMap=array(
            "codice",
            "nome",
            "indirizzo"
        );
        
    }

    function evaluate($tipo){
    }
}

?>