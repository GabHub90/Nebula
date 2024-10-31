<?php

class c2r_responsabili extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.C2R_responsabili";
        $this->selectMap=array(
            "gruppo",
            "stato",
            "prod_totali",
            "prod_collab",
            "modulo_prod",
            "modulo_fatt",
            "modulo_budget",
            "responsabile",
            "tag",
            "prod_repcol"
        );
        
    }

    function evaluate($tipo){
    }
}

?>