<?php

class ermes_chat extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.ERMES_chat";

        $this->selectMap=array(
            "ID",
            "riga",
            "tipo",
            "utente",
            "dataora",
            "testo",
            "stato"
        );

        $this->default=array(
            "ID"=>"",
            "riga"=>"",
            "tipo"=>"",
            "utente"=>"",
            "dataora"=>"",
            "testo"=>"",
            "stato"=>1
        );

        $this->checkMap=array(
            "ID"=>array("NOTNULL"),
            "riga"=>array("NOTNULL"),
            "tipo"=>array("NOTNULL"),
            "dataora"=>array("NOTNULL"),
            "testo"=>array("NOTNULL")
        );
        
    }

    function evaluate($tipo){
    }

}

?>