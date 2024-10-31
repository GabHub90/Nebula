<?php

class infinity_n_testatacontratto extends galileoTab {

    function __construct() {

        $this->tabName="dba.n_testata_contratto";

        $this->selectMap=array(
            "id_contratto",
            "numero_contratto",
            "data_contratto",
            "status_contratto",
            "data_chiusura"
        );
        
    }

    function evaluate($tipo){
    }
}

?>