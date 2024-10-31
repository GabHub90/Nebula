<?php

class infinity_n_veicoli extends galileoTab {

    function __construct() {

        $this->tabName="dba.n_veicoli";

        $this->selectMap=array(
            "id_veicolo",
            "marca",
            "telaio",
            "data_arrivo"
        );
        
    }

    function evaluate($tipo){
    }
}

?>