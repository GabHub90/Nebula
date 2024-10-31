<?php

class infinity_udettaglio_bollec extends galileoTab {

    function __construct() {

        $this->tabName="dba.U_DETTAGLIO_BOLLE_C";

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