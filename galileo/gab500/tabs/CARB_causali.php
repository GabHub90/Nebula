<?php

class carb_causali extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.CARB_causali";

        $this->selectMap=array(
            "codice",
            "causale",
            "ris",
            "nota"
        );
        
    }

    function evaluate($tipo){
    }
}

?>