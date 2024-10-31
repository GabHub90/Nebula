<?php

class concerto_tbgenanaute extends galileoTab {

    function __construct() {

        $this->tabName="[GC_ADM].[dbo].TB_GEN_ANAUTE";
        $this->selectMap=array(
            "cod_utente",
            "ind_annullo",
            "des_utente",
            "des_pwd",
            "cod_gruppo",
            "cod_ditta"
        );
        
    }

    function evaluate($tipo){
    }
}

?>