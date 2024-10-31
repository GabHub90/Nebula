<?php

class concerto_tbveicodvei extends galileoTab {

    function __construct() {

        $this->tabName="[GC_AZI_GABELLINI_prod].[dbo].TB_VEI_CODVEI";
        $this->selectMap=array(
            "cod_marca",
            "cod_veicolo",
            "des_veicolo"
        );
        
    }

    function evaluate($tipo){
    }
}

?>