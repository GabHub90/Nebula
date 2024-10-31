<?php

class carb_caurep extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.CARB_caurep";

        $this->selectMap=array(
            "reparto",
            "causale"
        );
        
    }

    function evaluate($tipo){
    }
}

?>