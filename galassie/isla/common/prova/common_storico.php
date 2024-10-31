<?php

$this->common['fields']=array(
    "officina"=>array(
        "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
        "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
    )
);

$this->common['tipi']=array(
    "officina"=>"none"
);

//EXPO e CONV devono essere definiti dalle single funzioni assieme agli UNCOMMON

/*
$this->common['expo']=array(
    "qc_reparto"=>""
);

$this->common['conv']=array(
    "qc_reparto"=>"officina"
);
*/

//#################################################################
//caricamento del campo "mappa" che potrebbe richiedere GALILEO

$a=array(
    "officina"=>array(
        "prop"=>array(
            "input"=>"input",
            "tipo"=>"hidden",
            "maxlenght"=>"",
            "options"=>array(),
            "rows"=>"",
            "default"=>"",
            "disabled"=>false
        ),
        "css"=>array()
    )
);

$this->common['mappa']=$a;

?>