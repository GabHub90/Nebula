<?php

class quartet_pansk extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.QUARTET2_pansk";
        $this->selectMap=array(
            "skema",
            "pan",
            "data_i",
            "blocco_inizio",
            "posizione"
        );

        $this->default=array(
            "skema"=>"",
            "pan"=>"",
            "data_i"=>"",
            "blocco_inizio"=>"",
            "posizione"=>""
        );

        $this->checkMap=array(
            "skema"=>array("NOTNULL"),
            "pan"=>array("NOTNULL"),
            "data_i"=>array("NOTNULL"),
            "blocco_inizio"=>array("NOTNULL")
        );
        
    }

    function evaluate($tipo){

        if ($tipo=='insert') {
            $this->actual['posizione']="###(SELECT max(posizione)+1 FROM ".$this->tabName." WHERE pan='".$this->actual['pan']."')";
        }
    }
}

?>