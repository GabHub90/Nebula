<?php

class alan_timbrature_k extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.ALAN_timbrature_k";
        $this->selectMap=array(
            "IDDIP",
            "d",
            "IDTIMBRATURA",
            "forza_minuti"
        );

        $this->increment="IDTIMBRATURA";

        $this->default=array(
            "IDDIP"=>"",
            "d"=>"",
            "forza_minuti"=>""
        );

        $this->checkMap=array(
            "IDDIP"=>array("NOTNULL"),
            "d"=>array("NOTNULL"),
            "forza_minuti"=>array("NOTNULL")
        );
        
    }

    function evaluate($tipo){
    }
}

?>