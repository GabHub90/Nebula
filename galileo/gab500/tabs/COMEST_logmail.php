<?php

class comest_logmail extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.COMEST_logmail";

        $this->selectMap=array(
            "commessa",
            "d",
            "mitt",
            "dest"
        );

        $this->default=array(
            "commessa"=>"",
            "d"=>"",
            "mitt"=>"",
            "dest"=>""
        );

        $this->checkMap=array(
            "commessa"=>array("NOTNULL"),
            "d"=>array("NOTNULL"),
            "mitt"=>array("NOTNULL"),
            "dest"=>array("NOTNULL")
        );
        
    }

    function evaluate($tipo){
    }
}

?>