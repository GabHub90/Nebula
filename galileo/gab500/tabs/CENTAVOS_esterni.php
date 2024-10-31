<?php

class centavos_esterni extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.CENTAVOS_esterni";
        $this->selectMap=array(
            "ID",
            "tag",
            "collaboratore",
            "valore",
            "d_validita",
            "d_inserimento",
            "utente"
        );

        $this->increment="ID";

        $this->default=array(
            "tag"=>"",
            "collaboratore"=>"",
            "valore"=>"",
            "d_validita"=>"",
            "d_inserimento"=>date('Ymd'),
            "utente"=>""
        );

        $this->checkMap=array(
            "tag"=>array("NOTNULL"),
            "valore"=>array("NOTNULL"),
            "d_validita"=>array("NOTNULL"),
            "d_inserimento"=>array("NOTNULL")
        );
        
    }

    function evaluate($tipo){
    }
}

?>