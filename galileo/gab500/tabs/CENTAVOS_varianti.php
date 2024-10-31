<?php

class centavos_varianti extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.CENTAVOS_varianti";
        $this->selectMap=array(
            "ID",
            "variante",
            "piano",
            "titolo",
            "eccedenza",
            "moduli",
            "budget",
            "flag_gradi",
            "limite",
            "peso",
            "coefficienti",
            "gradi",
            "livello"
        );

        $this->increment="ID";

        $this->default=array(
            "variante"=>"",
            "piano"=>"",
            "titolo"=>"",
            "eccedenza"=>"0",
            "moduli"=>"[]",
            "budget"=>"0",
            "flag_gradi"=>1,
            "limite"=>"{}",
            "peso"=>"{}",
            "coefficienti"=>"{}",
            "gradi"=>"{}",
            "livello"=>"0"
        );

        $this->checkMap=array(
            "variante"=>array("NOTNULL"),
            "piano"=>array("NOTNULL"),
            "titolo"=>array("NOTNULL"),
            "eccedenza"=>array("NOTNULL"),
            "moduli"=>array("NOTNULL"),
            "budget"=>array("NOTNULL"),
            "flag_gradi"=>array("NOTNULL"),
            "limite"=>array("NOTNULL"),
            "peso"=>array("NOTNULL"),
            "coefficienti"=>array("NOTNULL"),
            "gradi"=>array("NOTNULL")
        );
        
    }

    function evaluate($tipo){

        //se uno dei limiti è > 100 setta eccedenza a 1
        $this->actual['eccedenza']="0";
        if ( $limite=json_decode($this->actual['limite'],true) ) {
            foreach ($limite as $l) {
                if ($l>100) {
                    //echo $l;
                    $this->actual['eccedenza']="1";
                }
            }
        }

    }
}

?>