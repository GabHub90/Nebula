<?php

class concerto_mganaart extends galileoTab {

    function __construct() {

        $this->tabName="[GC_AZI_GABELLINI_prod].[dbo].MG_ANAART";
        $this->selectMap=array(
            "cod_tipo_articolo",
            "cod_articolo",
            "ind_annullo",
            "des_articolo",
            "cod_categoria_vendita",
            "cod_categoria_acquisto",
            "cod_marchio",
            "cod_famiglia_articolo"
        );
        
    }

    function evaluate($tipo){
    }
}

?>