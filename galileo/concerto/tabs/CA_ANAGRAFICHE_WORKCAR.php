<?php

class concerto_caanagraficheworkcar extends galileoTab {

    function __construct() {
        
        $this->tabName="[GC_AZI_GABELLINI_prod].[dbo].CA_ANAGRAFICHE_WORKCAR";
        $this->selectMap=array(
            "cod_anagra",
            "des_patente_numero",
            "des_patente_rilasciata_da",
            "dat_patente_il",
            "dat_scadenza_patente",
            "des_email_pec_fe",
            "des_pa_destinatario",
            "ind_azienda",
            "ind_ditta_individuale",
            "ind_ente_pubblico",
            "cod_mail_motivazione"
        );
        
    }

    function evaluate($tipo){
    }
}

?>