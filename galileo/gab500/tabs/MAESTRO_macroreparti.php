<?php

class maestro_macroreparti extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.MAESTRO_macroreparti";
        $this->selectMap=array(
            "tipo",
            "descrizione"
        );
        
    }

    function evaluate($tipo){
    }
}

?>