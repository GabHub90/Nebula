<?php

class grent_reset extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.GRENT_reset";

        $this->selectMap=array(
            "grent_id",
            "reset_id",
            "d",
            "km_reset",
            "fascia",
            "franchigia",
            "valore_i",
            "valore_f",
            "sva_km",
            "sva_tempo",
            "coeff",
            "coeff_km",
            "incent",
            "cop_fisso",
            "chiusura"
        );

        $this->increment="reset_id";

        $this->default=array(
            "grent_id"=>"",
            "d"=>"",
            "km_reset"=>"",
            "fascia"=>"",
            "franchigia"=>"",
            "valore_i"=>"",
            "valore_f"=>"",
            "sva_km"=>"",
            "sva_tempo"=>"",
            "coeff"=>"",
            "coeff_km"=>"",
            "incent"=>"",
            "cop_fisso"=>"",
            "chiusura"=>0
        );

        $this->checkMap=array(
            "grent_id"=>array("NOTNULL"),
            "d"=>array("NOTNULL"),
            "km_reset"=>array("NOTNULL"),
            "fascia"=>array("NOTNULL"),
            "franchigia"=>array("NOTNULL"),
            "valore_i"=>array("NOTNULL"),
            "valore_f"=>array("NOTNULL"),
            "sva_km"=>array("NOTNULL"),
            "sva_tempo"=>array("NOTNULL"),
            "coeff"=>array("NOTNULL"),
            "coeff_km"=>array("NOTNULL"),
            "incent"=>array("NOTNULL"),
            "cop_fisso"=>array("NOTNULL"),
            "chiusura"=>array("NOTNULL")
        );
        
    }

    function evaluate($tipo){
    }
}

?>