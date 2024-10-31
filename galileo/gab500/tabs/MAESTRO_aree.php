<?php

class maestro_aree extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.MAESTRO_aree";
        $this->selectMap=array(
            "ID",
            "tag",
            "descrizione"
        );
        
    }

    function evaluate($tipo){
    }
}

?>