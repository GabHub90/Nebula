<?php

class  avalon_statolam extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.AVALON_stato_lam";

        $this->selectMap=array(
            "pratica",
            "dms",
            "rif",
            "pren",
            "lam",
            "stato",
            "dataora",
            "utente",
            "scadenza",
            "alert",
            "nota",
            "prevfine",
            "chime_app"
        );

        $this->default=array(
            "pratica"=>"",
            "dms"=>"",
            "rif"=>"",
            "pren"=>"",
            "lam"=>"",
            "stato"=>"XX",
            "dataora"=>"",
            "utente"=>"",
            "scadenza"=>"",
            "alert"=>"",
            "nota"=>"",
            "prevfine"=>"xxxxxxxx:xx:xx",
            "chime_app"=>""
        );

        $this->checkMap=array(
            "pratica"=>array("NOTNULL"),
            "dms"=>array("NOTNULL"),
            "rif"=>array("NOTNULL"),
            "pren"=>array("NOTNULL"),
            "stato"=>array("NOTNULL"),
            "dataora"=>array("NOTNULL")
        );
        
    }

    function evaluate($tipo){
    }
}

?>