<?php

class quartet_schemi extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.QUARTET2_schemi";
        $this->selectMap=array(
            "codice",
            "reparto",
            "titolo",
            "turnazione",
            "flag_festivi",
            "flag_turno",
            "on_flag",
            "mark",
            "exclusive",
            "overall",
            "griglia"
        );
        
        $this->default=array(
            "codice"=>"",
            "reparto"=>"",
            "titolo"=>"",
            "turnazione"=>0,
            "flag_festivi"=>0,
            "flag_turno"=>0,
            "on_flag"=>0,
            "mark"=>0,
            "exclusive"=>0,
            "overall"=>"",
            "griglia"=>""
        );

        $this->checkMap=array(
            "codice"=>array("NOTNULL"),
            "reparto"=>array("NOTNULL"),
            "titolo"=>array("NOTNULL"),
            "turnazione"=>array("NOTNULL"),
            "flag_festivi"=>array("NOTNULL"),
            "flag_turno"=>array("NOTNULL"),
            "on_flag"=>array("NOTNULL"),
            "mark"=>array("NOTNULL"),
            "exclusive"=>array("NOTNULL"),
            "overall"=>array("NOTNULL"),
            "griglia"=>array("NOTNULL")
        );
    }

    function evaluate($tipo){
    }
}

?>