<?php

class grent_pratiche extends galileoTab {

    function __construct() {

        $this->tabName="[gab500].dbo.GRENT_pratiche";

        $this->selectMap=array(
            "nol_id",
            "rif_id",
            "targa", 
            "d_ins",
            "ute_ins",
            "d_mod",
            "ute_mod",
            "stato",
            "lista",
            "pren_i",
            "ora_pren_i",
            "pren_f",
            "ora_pren_f",
            "note_interne",
            "data_i",
            "ora_i",
            "data_f",
            "ora_f",
            "km_i",
            "km_f",
            "ute_i",
            "ute_f",
            "carb_i",
            "carb_f",
            "danni_i",
            "danni_f",
            "note_danni_i",
            "note_danni_f",
            "guida_1",
            "guida_2",
            "guida_3",
            "fatt_a",
            "d_fatt",
            "tel_fatt",
            "mail_fatt",
            "note_nol",
            "note_fatt",
            "info",
            "versione",
            "dms"
        );

        $this->increment="nol_id";

        $this->default=array(
            "rif_id"=>"",
            "targa"=>"", 
            "d_ins"=>"",
            "ute_ins"=>"",
            "d_mod"=>"",
            "ute_mod"=>"",
            "stato"=>"",
            "lista"=>"",
            "pren_i"=>"",
            "ora_pren_i"=>"",
            "pren_f"=>"",
            "ora_pren_f"=>"",
            "note_interne"=>"",
            "data_i"=>"",
            "ora_i"=>"",
            "data_f"=>"",
            "ora_f"=>"",
            "km_i"=>"",
            "km_f"=>"",
            "ute_i"=>"",
            "ute_f"=>"",
            "carb_i"=>"",
            "carb_f"=>"",
            "danni_i"=>"",
            "danni_f"=>"",
            "note_danni_i"=>"",
            "note_danni_f"=>"",
            "guida_1"=>"",
            "guida_2"=>"",
            "guida_3"=>"",
            "fatt_a"=>"",
            "d_fatt"=>"",
            "tel_fatt"=>"",
            "mail_fatt"=>"",
            "note_nol"=>"",
            "note_fatt"=>"",
            "info"=>"",
            "versione"=>"",
            "dms"=>""
        );

        $this->checkMap=array(
            "rif_id"=>array("NOTNULL"),
            "targa"=>array("NOTNULL"),
            "d_ins"=>array("NOTNULL"),
            "ute_ins"=>array("NOTNULL"),
            "d_mod"=>array("NOTNULL"),
            "ute_mod"=>array("NOTNULL"),
            "stato"=>array("NOTNULL"),
            "lista"=>array("NOTNULL"),
            "pren_i"=>array("NOTNULL"),
            "pren_f"=>array("NOTNULL"),
            "info"=>array("NOTNULL")
        );
        
    }

    function evaluate($tipo){
    }
}

?>