<?php

class nebula_Chain extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.CHAIN";

        $this->selectMap=array(
            "app",
            "chiave",
            "utente",
            "dataora"
        );

        $this->default=array(
            "app"=>"",
            "chiave"=>"",
            "utente"=>"",
            "dataora"=>""
        );

        $this->checkMap=array(
            "app"=>array("NOTNULL"),
            "chiave"=>array("NOTNULL"),
            "utente"=>array("NOTNULL"),
            "dataora"=>array("NOTNULL")
        );
        
    }

    function evaluate($tipo){
    }

}

?>