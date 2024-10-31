<?php

class infinity_cautrasp extends galileoTab {

    function __construct() {

        $this->tabName="dba.CAUSALE_TRASPORTO";

        $this->selectMap=array(         
            "id_caus_trasp",
            "codice",
            "descrizione",
            "tipo_uso",
            "genere"
        );
        
    }

    function evaluate($tipo){
    }
}

?>