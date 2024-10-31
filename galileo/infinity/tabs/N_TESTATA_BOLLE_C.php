<?php

class infinity_ntestata_bollec extends galileoTab {

    function __construct() {

        $this->tabName="dba.N_TESTATA_BOLLE_C";

        $this->selectMap=array(         
            "id_bolla",
            "anno",
            "td_bolla",
            "data_bolla",
            "num_bolla",
            "id_cliente",
            "importo_doc",
            "note_doc",
            "id_vettore",
            "trasp_cura",
            "data_trasp_inizio",
            "ora_trasp_inizio",
            "note_trasp",
            "id_caus_trasp",
            "ubicazione",
            "ubicazione_partenza",
            "data_creazione"
        );
        
    }

    function evaluate($tipo){
    }
}

?>