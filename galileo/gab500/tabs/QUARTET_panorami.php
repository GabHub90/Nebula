<?php

class quartet_panorami extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.QUARTET2_panorami";
        $this->selectMap=array(
            "ID",
            "inizio",
            "stato",
            "reparto",
            "orariOA",
            "actual"
        );

        $this->increment="ID";

        $this->default=array(
            "inizio"=>date('Ym'),
            "stato"=>"A",
            "reparto"=>"",
            "orariOA"=>"",
            "actual"=>"1"
        );

        $this->checkMap=array(
            "inizio"=>array("NOTNULL"),
            "stato"=>array("NOTNULL"),
            "reparto"=>array("NOTNULL"),
            "orariOA"=>array("NOTNULL"),
            "actual"=>array("NOTNULL")
        );
        
    }

    function evaluate($tipo){
    }
}

?>