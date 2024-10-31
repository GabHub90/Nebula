<?php

class infinity_offmarche extends galileoTab {

    function __construct() {

        $this->tabName="dba.off_marche";

        $this->selectMap=array(
            "cod_marca",
            "descrizione"
        );
        
    }

    function evaluate($tipo){
    }
}

?>