<?php

class centavos_freezed extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.CENTAVOS_freezed";
        $this->selectMap=array(
            "periodo",
            "collaboratore",
            "html"
        );

        $this->default=array(
            "periodo"=>"",
            "collaboratore"=>"",
            "html"=>""
        );

        $this->checkMap=array(
            "periodo"=>array("NOTNULL"),
            "collaboratore"=>array("NOTNULL"),
            "html"=>array("NOTNULL")
        );
        
    }

    function evaluate($tipo){
    }
}

?>