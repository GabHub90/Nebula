<?php

class tempo_sposta extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.TEMPO_sposta2";
        
        $this->selectMap=array(
            "ID",
            "coll",
            "data",
            "ora_i",
            "ora_f",
            "panorama",
            "sub_a"
        );

        $this->increment="ID";

        $this->default=array(
            "coll"=>"",
            "data"=>"",
            "ora_i"=>"",
            "ora_f"=>"",
            "panorama"=>"",
            "sub_a"=>""
        );

        $this->checkMap=array(
            "coll"=>array("NOTNULL"),
            "data"=>array("NOTNULL"),
            "ora_i"=>array("NOTNULL"),
            "ora_f"=>array("NOTNULL"),
            "panorama"=>array("NOTNULL")
        );
        
    }

    function evaluate($tipo){
    }
}

?>