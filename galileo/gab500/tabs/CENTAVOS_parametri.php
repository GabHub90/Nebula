<?php

class centavos_parametri extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.CENTAVOS_parametri";
        $this->selectMap=array(
            "ID",
            "param",
            "griglia",
            "funzione"
        );

        $this->increment="ID";

        $this->default=array(
            "param"=>"{}",
            "griglia"=>"{}",
            "funzione"=>"{}"
        );

        $this->checkMap=array(
            "param"=>array("NOTNULL"),
            "griglia"=>array("NOTNULL"),
            "funzione"=>array("NOTNULL")
        );

    }

    function evaluate($tipo){
    }
}

?>