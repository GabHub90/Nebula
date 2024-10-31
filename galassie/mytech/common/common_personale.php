<?php

/*
$this->common['info']=array(
    "officina"=>""
);
*/

$this->common['fields']=array(
    "macroreparto"=>array(
        "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
        "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
    )
);

$this->common['tipi']=array(
    "macroreparto"=>"none"
);

//EXPO e CONV devono essere definiti dalle single funzioni assieme agli UNCOMMON

$this->common['mappa']=array(
    "macroreparto"=>array(
        "prop"=>array(
            "input"=>"select",
            "tipo"=>"",
            "maxlenght"=>"",
            "options"=>array(
                "S"=>"S - Service Officine"
            ),
            "rows"=>"",
            "default"=>"",
            "disabled"=>true
        ),
        "css"=>array()
    )
);

?>