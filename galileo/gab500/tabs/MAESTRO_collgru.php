<?php

class maestro_collgru extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.MAESTRO2_collgru";
        $this->selectMap=array(
            "ID",
            "gruppo",
            "collaboratore",
            "data_i",
            "data_f"
        );

        $this->increment="ID";

        $this->default=array(
            "gruppo"=>"",
            "collaboratore"=>"",
            "data_i"=>"",
            "data_f"=>"21001231"
        );

        $this->checkMap=array(
            "gruppo"=>array("NOTNULL"),
            "collaboratore"=>array("NOTNULL"),
            "data_i"=>array("NOTNULL"),
            "data_f"=>array("NOTNULL")
        );
        
    }

    function evaluate($tipo){
    }
}

?>