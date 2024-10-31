<?php

class concerto_gnmovtesoffpratiche extends galileoTab {

    function __construct() {

        $this->tabName="[GC_AZI_GABELLINI_prod].[dbo].GN_MOVTES_OFF_PRATICHE";
        $this->selectMap=array(
            "num_pratica",
            "num_progressivo",
            "num_rif_movimento",
            "des_progressivo",
            "des_path_allegato",
            "ind_allegato",
            "img_allegato",
            "gab_header",
            "gab_header_txt",
            "gab_catcher_txt",
            "gab_rif_ricezione",
            "gab_preventivo_txt",
            "gab_alert"
        );
        
    }

    function evaluate($tipo){
    }
}

?>