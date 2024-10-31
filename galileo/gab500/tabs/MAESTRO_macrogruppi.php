<?php

class maestro_macrogruppi extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.MAESTRO_macrogruppi";
        $this->selectMap=array(
            "tag",
            "descrizione",
            "pos"
        );
        
    }

    function evaluate($tipo){
    }
}

?>