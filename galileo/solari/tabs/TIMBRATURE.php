<?php

class solari_timbrature extends galileoTab {

    function __construct() {

        $this->tabName="[dbstart].[dbo].TIMBRATURE";
        $this->selectMap=array(
            "IDDIP",
            "DATAO",
            "VERSOO",
            "IDTIMBRATURA"
        );
        
    }

    function evaluate($tipo){
    }
}

?>