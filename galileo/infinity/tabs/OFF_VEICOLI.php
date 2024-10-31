<?php

class infinity_offveicoli extends galileoTab {

    function __construct() {

        $this->tabName="dba.off_veicoli";

        $this->selectMap=array(
            "id_veicolo",
            "targa",
            "telaio",
            "cod_modello",
            "data_inizio_garanzia",
            "data_fine_garanzia",
            "codice_motore",
            "data_ultrev",
            "data_immatricolazione",
            "codice_cliente",
            "cod_marca",
            "flag_vei_cortesia",
            "codice_contatto",
            "tipo_veicolo_tempario",
            "descrizione_aggiuntiva",
            "alimentazione",
            "note"
        );

        $this->default=array(
            "km"=>"0"
        );

        $this->checkMap=array(
        );
        
    }

    function evaluate($tipo){
    }
}

?>