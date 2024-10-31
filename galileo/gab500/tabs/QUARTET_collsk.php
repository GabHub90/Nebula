<?php

class quartet_collsk extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.QUARTET2_collsk";
        $this->selectMap=array(
            "panorama",
            "collaboratore",
            "skema",
            "turno",
            "data_i",
            "data_f"
        );

        $this->default=array(
            "panorama"=>"",
            "collaboratore"=>"",
            "skema"=>"",
            "turno"=>"",
            "data_i"=>"",
            "data_f"=>""
        );

        $this->checkMap=array(
            "panorama"=>array("NOTNULL"),
            "collaboratore"=>array("NOTNULL"),
            "skema"=>array("NOTNULL"),
            "turno"=>array("NOTNULL"),
            "data_i"=>array("NOTNULL"),
            "data_f"=>array("NOTNULL")
        );
        
    }

    function evaluate($tipo){
    }
}

?>