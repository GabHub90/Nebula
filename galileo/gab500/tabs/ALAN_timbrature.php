<?php

class alan_timbrature extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.ALAN_timbrature";
        $this->selectMap=array(
            "IDDIP",
            "DATAO",
            "VERSOO",
            "IDTIMBRATURA",
            "forza_minuti"
        );

        $this->default=array(
            "IDDIP"=>"",
            "DATAO"=>"",
            "VERSOO"=>"",
            "IDTIMBRATURA"=>"",
            "forza_minuti"=>"-1"
        );

        $this->checkMap=array(
            "IDDIP"=>array("NOTNULL"),
            "DATAO"=>array("NOTNULL"),
            "VERSOO"=>array("NOTNULL"),
            "IDTIMBRATURA"=>array("NOTNULL")
        );
        
    }

    function evaluate($tipo){
    }
}

?>