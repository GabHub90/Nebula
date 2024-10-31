<?php

class centavos_rettifiche extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.CENTAVOS_rettifiche";
        $this->selectMap=array(
            "ID",
            "piano",
            "periodo",
            "parametro",
            "collaboratore",
            "valore",
            "d_inserimento",
            "utente"
        );

        $this->increment="ID";

        $this->default=array(
            "piano"=>"",
            "periodo"=>"",
            "parametro"=>"",
            "collaboratore"=>"",
            "valore"=>"",
            "d_inserimento"=>date('Ymd'),
            "utente"=>""
        );

        $this->checkMap=array(
            "piano"=>array("NOTNULL"),
            "periodo"=>array("NOTNULL"),
            "parametro"=>array("NOTNULL"),
            "valore"=>array("NOTNULL"),
            "d_inserimento"=>array("NOTNULL")
        );
        
    }

    function evaluate($tipo){
    }
}

?>