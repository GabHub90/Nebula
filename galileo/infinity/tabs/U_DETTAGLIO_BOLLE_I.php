<?php

class infinity_udettaglio_bollei extends galileoTab {

    function __construct() {

        $this->tabName="dba.U_DETTAGLIO_BOLLE_I";

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