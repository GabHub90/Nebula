<?php

class concerto_tbmagcaumov extends galileoTab {

    function __construct() {

        $this->tabName="[GC_AZI_GABELLINI_prod].[dbo].TB_MAG_CAUMOV";
        $this->selectMap=array(
            "cod_movimento",
            "ind_annullo",
            "des_movimento",
            "des_reparto_movimento",
            "cod_classe_sconto_articoli",
            "cod_classe_sconto_manodopera",
            "des_tipo_sconto",
            "des_tipo_movimento_magazzino",
            "cod_documento_default",
            "ind_carrozzeria"
        );
        
    }

    function evaluate($tipo){
    }

}

?>