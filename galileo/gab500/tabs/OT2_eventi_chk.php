<?php

class galileo_ot2_eventichk extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.OT2_eventi_chk";

        $this->selectMap=array(
            "dms",
            "odl",
            "codice",
            "new"
        );

        $this->default=array(
            "dms"=>"",
            "odl"=>"",
            "codice"=>"",
            "new"=>""
        );

        $this->checkMap=array(
            "dms"=>array("NOTNULL"),
            "odl"=>array("NOTNULL"),
            "codice"=>array("NOTNULL"),
            "new"=>array("NOTNULL")
        );
    }

    function evaluate($tipo){
    }
}

?>