<?php

class centavos_piani extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.CENTAVOS_piani";
        $this->selectMap=array(
            "ID",
            "reparto",
            "macroreparto",
            "descrizione",
            "base_dati",
            "parametri",
            "cadenza",
            "data_i",
            "data_f",
            "varianti",
            "stato"
        );
        
    }

    function evaluate($tipo){
    }
}

?>