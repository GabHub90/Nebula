<?php

class concerto_gnmovtes extends galileoTab {

    function __construct() {

        $this->tabName="[GC_AZI_GABELLINI_prod].[dbo].GN_MOVTES";
        $this->selectMap=array(
            "num_rif_movimento",
            "ind_annullo",
            "cod_reparto",
            "dat_inserimento",
            "cod_utente_inserimento",
            "dat_modifica",
            "cod_utente_modifica",
            "cod_movimento",
            "ind_chiuso"
        );
        
    }

    function evaluate($tipo){
    }
}

?>