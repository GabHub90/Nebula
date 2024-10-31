<?php

class centavos_periodi extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.CENTAVOS_periodi";
        $this->selectMap=array(
            "ID",
            "piano",
            "d_inizio",
            "d_fine",
            "stato",
            "hidden"
        );

        $this->increment="ID";

        $this->default=array(
            "piano"=>"",
            "d_inizio"=>"",
            "d_fine"=>"",
            "stato"=>"",
            "hidden"=>""
        );

        $this->checkMap=array(
            "piano"=>array("NOTNULL"),
            "d_inizio"=>array("NOTNULL"),
            "d_fine"=>array("NOTNULL"),
            "stato"=>array("NOTNULL"),
            "hidden"=>array("NOTNULL")
        );
        
    }

    function evaluate($tipo){
    }
}

?>