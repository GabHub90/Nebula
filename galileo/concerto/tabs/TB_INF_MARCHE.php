<?php

class concerto_tbinfmarche extends galileoTab {

    function __construct() {

        $this->tabName="[GC_AZI_GABELLINI_prod].[dbo].TB_INF_MARCHE";
        $this->selectMap=array(
            "cod_marca_infocar",
            "cod_marca",
            "desc"
        );
        
    }

    function evaluate($tipo){
    }
}

?>