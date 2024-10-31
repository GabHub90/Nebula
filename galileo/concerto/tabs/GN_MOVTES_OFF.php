<?php

class concerto_gnmovtesoff extends galileoTab {

    function __construct() {

        $this->tabName="[GC_AZI_GABELLINI_prod].[dbo].GN_MOVTES_OFF";
        $this->selectMap=array(
            "num_rif_movimento",
            "cod_officina",
            "num_commessa_numeratore",
            "num_commessa_anno",
            "num_commessa",
            "ind_preventivo",
            "dat_entrata_veicolo",
            "dat_uscita_veicolo",
            "dat_promessa_consegna",
            "dat_fine_lavori",
            "dat_prenotazione",
            "dat_split",
            "num_rif_veicolo",
            "num_rif_veicolo_progressivo",
            "num_gestione",
            "num_km",
            "cod_accettatore",
            "cod_officina_prenotazione",
            "cod_anagra_util",
            "cod_anagra_intest",
            "cod_anagra_loc",
            "cod_anagra_fattura",
            "des_messaggio",
            "cod_stato_commessa",
            "cod_tipo_trasporto",
        );
        
    }

    function evaluate($tipo){
    }
}

?>