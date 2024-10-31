<?php

class centavos_moduli extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.CENTAVOS_moduli";
        $this->selectMap=array(
            "ID",
            "titolo",
            "principali",
            "modificatori"
        );

        $this->increment="ID";

        $this->default=array(
            "titolo"=>"",
            "principali"=>"[]",
            "modificatori"=>"[]"
        );

        $this->checkMap=array(
            "titolo"=>array("NOTNULL"),
            "principali"=>array("NOTNULL"),
            "modificatori"=>array("NOTNULL")
        );
        
    }

    function evaluate($tipo){
    }
}

?>