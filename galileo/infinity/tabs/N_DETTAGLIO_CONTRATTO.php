<?php

class infinity_n_dettagliocontratto extends galileoTab {

    function __construct() {

        $this->tabName="dba.n_dettaglio_contratto";

        $this->selectMap=array(
            "id_contratto",
            "id_riga",
            "telaio",
            "id_veicolo"
        );
        
    }

    function evaluate($tipo){
    }
}

?>