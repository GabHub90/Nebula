<?php

class infinity_offmodelli extends galileoTab {

    function __construct() {

        $this->tabName="dba.off_modelli";

        $this->selectMap=array(
            "cod_modello",
            "descrizione",
            "livello",
            "cod_marca"
        );
        
    }

    function evaluate($tipo){
    }
}

?>