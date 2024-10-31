<?php

class infinity_utestata_bollei extends galileoTab {

    function __construct() {

        $this->tabName="dba.U_TESTATA_BOLLE_I";

        $this->selectMap=array(         
            "id_bolla",
            "anno",
            "td_bolla",
            "data_bolla",
            "num_bolla",
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