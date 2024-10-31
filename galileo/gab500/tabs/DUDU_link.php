<?php

class dudu_link extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.DUDU_link";

        $this->selectMap=array(
            "app",
            "rif",
            "dudu"
        );

        $this->default=array(
            "app"=>"",
            "rif"=>"",
            "dudu"=>""
        );

        $this->checkMap=array(
            "app"=>array("NOTNULL"),
            "rif"=>array("NOTNULL"),
            "dudu"=>array("NOTNULL")
        );   
    }

    function evaluate($tipo){
    }

}

?>