<?php

class infinity_ndettaglio_bollef extends galileoTab {

    function __construct() {

        $this->tabName="dba.N_DETTAGLIO_BOLLE_F";

        $this->selectMap=array(         
            "id_bolla",
            "id_veicolo_u",
            "note",
            "ubicazione_precedente",
            "ubicazione_destinazione"
        );
        
    }

    function evaluate($tipo){
    }
}

?>