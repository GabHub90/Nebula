<?php

class concerto_anaveianagra extends galileoTab {

    function __construct() {

        $this->tabName="[GC_AZI_GABELLINI_prod].[dbo].VE_ANAVEI_ANAGRA";
        $this->selectMap=array(
            "num_rif_veicolo",
            "num_rif_veicolo_progressivo",
            "cod_anagra_util",
            "cod_anagra_intest",
            "cod_anagra_loc"
        );
        
    }

    function evaluate($tipo){
    }
}

?>