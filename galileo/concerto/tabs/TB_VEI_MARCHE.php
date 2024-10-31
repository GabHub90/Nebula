<?php

class concerto_tbveimarche extends galileoTab {

    function __construct() {

        $this->tabName="[GC_AZI_GABELLINI_prod].[dbo].TB_VEI_MARCHE";
        $this->selectMap=array(
            "cod_marca",
            "ind_annullo",
            "des_marca"
        );
        
    }

    function evaluate($tipo){
    }
}

?>