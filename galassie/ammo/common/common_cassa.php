<?php

$this->common['fields']=array(
    "strillo_cassa"=>array(
        "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
        "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
    )
);

$this->common['tipi']=array(
   "strillo_cassa"=>"none"
);


//#################################################################
//caricamento del campo "mappa" che potrebbe richiedere GALILEO

$a=array(
    "strillo_cassa"=>array(
        "prop"=>array(
            "input"=>"select",
            "tipo"=>"",
            "maxlenght"=>"",
            "options"=>array(
                "C1"=>array(
                    "tag"=>"Info VW"
                ),
                "C2"=>array(
                    "tag"=>"Info Audi"
                )
            ),
            "rows"=>"",
            "default"=>"",
            "disabled"=>false
        ),
        "css"=>array()
    )
);

////////////////////////////////////////

$this->common['mappa']=$a;

?>