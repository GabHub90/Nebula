<?php

class concerto_caanagrafichenote extends galileoTab {

    function __construct() {

        $this->tabName="[GC_AZI_GABELLINI_prod].[dbo].CA_ANAGRAFICHE_NOTE";
        $this->selectMap=array(
            "cod_anagra",
			"des_nome_nota",
		    "des_note",
			"cod_utente_inserimento",
            "dat_inserimento"
        );
        
    }

    function evaluate($tipo){
    }
}

?>