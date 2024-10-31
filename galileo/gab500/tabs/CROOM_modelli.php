<?php

class croom_modelli extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.CROOM_modelli";
        $this->selectMap=array(
            "ID",
            "modello",
            "marca",
            "res_modello",
            "descrizione",
            "anno_i",
            "anno_f"
        );
        
    }

    function evaluate($tipo){
    }
}

?>