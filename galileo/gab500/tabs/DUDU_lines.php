<?php

class dudu_lines extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.DUDU_lines";

        $this->selectMap=array(
            "ID",
            "riga",
            "testo",
            "d_creazione",
            "d_scadenza",
            "d_chiusura"
        );

        $this->default=array(
            "ID"=>"",
            "riga"=>"",
            "testo"=>"",
            "d_creazione"=>"",
            "d_scadenza"=>"",
            "d_chiusura"=>""
        );

        $this->checkMap=array(
            "ID"=>array("NOTNULL"),
            "riga"=>array("NOTNULL"),
            "testo"=>array("NOTNULL")
        );   
    }

    function evaluate($tipo){

    }

}

?>