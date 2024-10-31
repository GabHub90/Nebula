<?php

class concerto_tbvgitipoveicolo extends galileoTab {

    function __construct() {

        $this->tabName="[GC_AZI_GABELLINI_prod].[dbo].TB_VGI_TIPO_VEICOLO";
        $this->selectMap=array(
            "cod_vw_marca",
            "cod_vw_tipo_veicolo",
            "ind_annullo",
            "des_vw_tipo_veicolo"
        );
        
    }

    function evaluate($tipo){
    }
}

?>