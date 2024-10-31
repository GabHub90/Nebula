<?php

class quartet_pan_subrep extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.QUARTET2_pan_subrep";
        $this->selectMap=array(
            "panorama",
            "subrep",
            "cod_def",
            "pos"
        );

        $this->default=array(
            "panorama"=>"",
            "subrep"=>"",
            "cod_def"=>"",
            "pos"=>""
        );

        $this->checkMap=array(
            "panorama"=>array("NOTNULL"),
            "subrep"=>array("NOTNULL"),
            "cod_def"=>array("NOTNULL"),
            "pos"=>array("NOTNULL")
        );
        
    }

    function evaluate($tipo){
    }
}

?>