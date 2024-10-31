<?php

class infinity_dettagliocontratto extends galileoTab {

    function __construct() {

        $this->tabName="dba.dettaglio_contratto";

        $this->selectMap=array(
            "id_contratto",
            "id_riga",
            "telaio",
            "veicolo"
        );
        
    }

    function evaluate($tipo){
    }
}

?>