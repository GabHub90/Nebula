<?php

class grent_fasce extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.GRENT_fasce";

        $this->selectMap=array(
            "codice",
            "lista",
            "testo",
            "kasko",
            "manut",
            "gomme",
            "ipt",
            "bollo"
        );

        $this->default=array(
            "codice"=>"",
            "lista"=>"",
            "testo"=>"",
            "kasko"=>0,
            "manut"=>0,
            "gomme"=>0,
            "ipt"=>0,
            "bollo"=>0
        );

        $this->checkMap=array(
            "codice"=>array("NOTNULL"),
            "lista"=>array("NOTNULL")
        );
        
    }

    function evaluate($tipo){
    }
}

?>