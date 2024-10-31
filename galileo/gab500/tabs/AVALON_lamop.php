<?php

class  avalon_lamop extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.AVALON_lamextra";
        $this->selectMap=array(
            "dms",
            "rif",
            "lam",
            "lamop",
            "ut",
            "dispo",
            "subrep",
            "dat_fisso",
            "risorse",
            "dat_limite"
        );
        
    }

    function evaluate($tipo){
    }
}

?>