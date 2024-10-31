<?php

class concerto_gnanagrafiche extends galileoTab {

    function __construct() {
        
        //#########################################
        //usare questa tabella SOLO per SELECT
        //#########################################

        $this->tabName="[GC_AZI_GABELLINI_prod].[dbo].GN_ANAGRAFICHE";
        $this->selectMap=array(
            "cod_anagra",
            "des_ragsoc",
            "cod_cfisc",
            "cod_piva",
            "des_email1",
            "des_localita_nasc",
            "cod_cap_nasc",
            "sig_prov_nasc",
            "dat_nasc",
            "des_indir_res",
            "des_localita_res",
            "cod_cap_res",
            "sig_prov_res",
            "sig_tel_res",
            "sig_tel1_res",
            "des_nome",
            "des_cogn",
            "cod_naz_nasc",
            "cod_naz_res",
            "cod_localita_res",
            "cod_localita_nas",
            "ind_sesso",
            "flg_pers_fis",
            "des_ricerca"   
        );
        
    }

    function evaluate($tipo){
    }
}

?>