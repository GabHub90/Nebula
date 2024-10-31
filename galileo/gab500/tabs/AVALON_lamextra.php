<?php

class  avalon_lamextra extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.AVALON_lamextra";
        $this->selectMap=array(
            "dms",
            "rif",
            "lam",
            "pos",
            "stamp"
        );

        $this->default=array(
            "dms"=>"",
            "rif"=>"",
            "lam"=>"",
            "pos"=>"",
            "stamp"=>""
        );

        $this->checkMap=array(
            "dms"=>array("NOTNULL"),
            "rif"=>array("NOTNULL"),
            "lam"=>array("NOTNULL"),
            "pos"=>array("NOTNULL")
        );
        
    }

    function evaluate($tipo){
    }
}

?>