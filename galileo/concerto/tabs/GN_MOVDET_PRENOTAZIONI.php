<?php

class concerto_gnmovdet_prenotazioni extends galileoTab {

    function __construct() {

        $this->tabName="[GC_AZI_GABELLINI_prod].[dbo].GN_MOVDET_PRENOTAZIONI";
        $this->selectMap=array(
            "num_rif_movimento",
            "num_riga",
            "num_progressivo",
            "dat_pronotazione_det",
            "qta_prenotazione_det"
        );
        
    }

    function evaluate($tipo){
    }
}

?>