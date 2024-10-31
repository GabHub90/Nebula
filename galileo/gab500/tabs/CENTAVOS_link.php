<?php

class centavos_link extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.CENTAVOS_link";
        $this->selectMap=array(
            "ID",
            "coll",
            "piano",
            "variante",
            "data_i",
            "data_f",
            "grado",
            "periodo_inizio",
            "periodo_fine"
        );

        $this->increment="ID";

        $this->default=array(
            "coll"=>"",
            "piano"=>"",
            "variante"=>"",
            "data_i"=>"",
            "data_f"=>"",
            "grado"=>"{}",
            "periodo_inizio"=>"",
            "periodo_fine"=>""
        );

        $this->checkMap=array(
            "coll"=>array("NOTNULL"),
            "piano"=>array("NOTNULL"),
            "variante"=>array("NOTNULL"),
            "data_i"=>array("NOTNULL"),
            "data_f"=>array("NOTNULL"),
            "grado"=>array("NOTNULL"),
            "periodo_inizio"=>array("NOTNULL"),
            "periodo_fine"=>array("NOTNULL")
        );
        
    }

    function evaluate($tipo){
    }
}

?>