<?php

class tempo_sostituzioni extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.TEMPO_sostituzioni2";
        $this->selectMap=array(
            "collaboratore",
            "panorama",
            "tag",
            "skema",
            "turno",
            "azione"
        );

        $this->default=array(
            "collaboratore"=>"",
            "panorama"=>"",
            "tag"=>"",
            "skema"=>"",
            "turno"=>"",
            "azione"=>""
        );

        $this->checkMap=array(
            "collaboratore"=>array("NOTNULL"),
            "panorama"=>array("NOTNULL"),
            "tag"=>array("NOTNULL"),
            "skema"=>array("NOTNULL"),
            "turno"=>array("NOTNULL"),
            "azione"=>array("IN(CNC,AGG,)")
        );
        
    }

    function evaluate($tipo){
    }
}

?>