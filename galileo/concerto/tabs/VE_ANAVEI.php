<?php

class concerto_veanavei extends galileoTab {

    function __construct() {

        $this->tabName="[GC_AZI_GABELLINI_prod].[dbo].VE_ANAVEI";
        $this->selectMap=array(
            "num_rif_veicolo",
            "mat_targa",
            "mat_telaio",
            "cod_marca",
            "cod_veicolo",
            "des_veicolo",
            "cod_colore_interno",
            "cod_colore_esterno",
            "des_colore_interno",
            "des_colore_esterno",
            "cod_alimentazione",
            "mat_motore",
            "cod_vw_tipo_veicolo",
            "cod_ente_venditore",
            "dat_immatricolazione",
            "dat_inizio_garanzia",
            "dat_ultima_revisione",
            "des_note_off",
            "cod_infocar",
		    "cod_infocar_anno",
			"cod_infocar_mese"
        );
        
    }

    function evaluate($tipo){
    }
}

?>