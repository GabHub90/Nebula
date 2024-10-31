<?php

class concerto_tboffaccettatori extends galileoTab {

    function __construct() {

        $this->tabName="[GC_AZI_GABELLINI_prod].[dbo].TB_OFF_ACCETTATORI";
        $this->selectMap=array(
            "cod_accettatore",
            "ind_annullo",
            "des_accettatore",
            "cod_utente"
        );
        
    }

    function evaluate($tipo){
    }

}

?>