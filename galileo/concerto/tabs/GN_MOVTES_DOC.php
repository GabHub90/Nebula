<?php

class concerto_gnmovtesdoc extends galileoTab {

    function __construct() {

        $this->tabName="[GC_AZI_GABELLINI_prod].[dbo].GN_MOVTES_DOC";
        $this->selectMap=array(
            "num_rif_movimento",
            "cod_societa",
            "cod_tipo_documento_i",
            "cod_numeratore_i",
            "dat_documento_i",
            "num_documento_i",
            "cod_pag",
            "cod_anagrafica",
            "val_imponibile_tot",
            "val_imposta_tot",
            "val_totale"
        );
        
    }

    function evaluate($tipo){
    }
}

?>