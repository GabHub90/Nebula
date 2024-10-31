<?php

class grent_veicoli extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.GRENT_veicoli";

        $this->selectMap=array(
            "grent_id",
            "lista",
            "rif_id",
            "dms",
            "marca",
            "stato",
            "data_i",
            "data_f",
            "note"
        );

        $this->increment="grent_ID";

        $this->default=array(
            "lista"=>"",
            "rif_id"=>"",
            "dms"=>"infinity",
            "marca"=>"",
            "stato"=>"regolare",
            "data_i"=>"",
            "data_f"=>"",
            "note"=>""
        );

        $this->checkMap=array(
            "lista"=>array("NOTNULL"),
            "rif_id"=>array("NOTNULL"),
            "dms"=>array("NOTNULL"),
            "marca"=>array("NOTNULL"),
            "stato"=>array("NOTNULL"),
            "data_i"=>array("NOTNULL")
        );
        
    }

    function evaluate($tipo){
    }
}

?>