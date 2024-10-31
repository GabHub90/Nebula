<?php

class infinity_ndettaglio_bollec extends galileoTab {

    function __construct() {

        $this->tabName="dba.N_DETTAGLIO_BOLLE_C";

        $this->selectMap=array(         
            "id_bolla",
            "id_veicolo_u",
            "ubicazione_precedente",
            "ubicazione_destinazione"
        );
        
    }

    function evaluate($tipo){
    }
}

?>