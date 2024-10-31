<?php

class concerto_tbpittemparioveicoli extends galileoTab {

    function __construct() {

        $this->tabName="[GC_AZI_GABELLINI_prod].[dbo].TB_PIT_TEMPARIO_VEICOLI";
        $this->selectMap=array(
            "cod_veicolo",
            "des_model_year",
            "cod_id_gruppo",
            "cod_modello",
            "des_modello",
            "des_veicolo"
        );
        
    }

    function evaluate($tipo){
    }
}

?>