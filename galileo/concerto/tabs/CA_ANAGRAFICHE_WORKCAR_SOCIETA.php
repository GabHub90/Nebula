<?php

class concerto_caanagraficheworkcarsocieta extends galileoTab {

    function __construct() {

        $this->tabName="[GC_AZI_GABELLINI_prod].[dbo].CA_ANAGRAFICHE_WORKCAR_SOCIETA";
        $this->selectMap=array(
            "cod_anagra",
            "cod_societa",
            "dat_invio_inf_privacy",
            "dat_privacy_accettazione_1",
            "dat_privacy_accettazione_2",
            "dat_privacy_accettazione_3",
            "dat_blocco_pubblicita",
            "cod_vw_tipo_cliente_off",
            "ind_vgi_prv_profilazione",
            "ind_vgi_prv_marketing",
            "ind_vgi_prv_assicurativi",
            "ind_critico",
            "ind_critico_comm",
            "dat_ultima_var_critico",
            "cod_utente_ultima_var_critico"
        );
        
    }

    function evaluate($tipo){
    }
}

?>