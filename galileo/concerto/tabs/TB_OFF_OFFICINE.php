<?php

class concerto_tboffofficine extends galileoTab {

    function __construct() {

        $this->tabName="[GC_AZI_GABELLINI_prod].[dbo].TB_OFF_OFFICINE";
        $this->selectMap=array(
            "cod_officina",
            "ind_annullo",
            "des_officina",
            "cod_numeratore_commesse",
            "cod_magazzino_associato",
            "cod_ente_default",
            "cod_societa_ente_default",
            "cod_numeratore_preventivi"
        );
        
    }

    function evaluate($tipo){
    }
}

?>