<?php

class infinity_offalimentazione extends galileoTab {

    function __construct() {

        $this->tabName="dba.n_alimentazione";

        $this->selectMap=array(
            "codice",
            "descrizione"
        );
        
    }

    function evaluate($tipo){
    }
}

?>