<?php

class carb_responsabili extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.CARB_responsabili";

        $this->selectMap=array(
            "coll",
            "stato",
            "stampa",
            "annulla"
        );
        
    }

    function evaluate($tipo){
    }
    
}

?>