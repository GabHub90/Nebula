<?php

class concerto_gniva extends galileoTab {

    function __construct() {

        $this->tabName="[GC_AZI_GABELLINI_prod].[dbo].GN_IVA";
        $this->selectMap=array(
            "cod_iva",
            "des_iva",
            "prc_iva"
        );
        
    }

    function evaluate($tipo){
    }
}

?>