<?php

class infinity_veicoliusati extends galileoTab {

    function __construct() {

        $this->tabName="dba.veicoli_usati";

        $this->selectMap=array(
            "usato",
            "marca",
            "telaio",
            "data_arrivo"
        );
        
    }

    function evaluate($tipo){
    }
}

?>