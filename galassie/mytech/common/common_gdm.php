<?php

/*
$this->common['info']=array(
    "officina"=>""
);
*/

$this->common['fields']=array(
    "gdm_tt"=>array(
        "js_chk_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
        "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
    ),
    "gdm_dmstt"=>array(
        "js_chk_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
        "js_chk_ifreq"=>array("campo"=>"gdm_tt","op"=>"!=","val"=>"")
    ),
    "gdm_telaio"=>array(
        "js_chk_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
        "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
    ),
    "gdm_dms"=>array(
        "js_chk_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
        "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
    ),
    "gdm_pratica"=>array(
        "js_chk_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
        "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
    )
   
);

$this->common['tipi']=array(
    "gdm_tt"=>"tt",
    "gdm_dmstt"=>"none",
    "gdm_telaio"=>"none",
    "gdm_dms"=>"none",
    "gdm_pratica"=>"none"

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
    "gdm_tt"=>array(
        "prop"=>array(
            "input"=>"input",
            "tipo"=>"text",
            "maxlenght"=>"",
            "options"=>array(),
            "rows"=>"",
            "default"=>"",
            "disabled"=>false
        ),
        "css"=>array()
    ),
    "gdm_dmstt"=>array(
        "prop"=>array(
            "input"=>"select",
            "tipo"=>"",
            "maxlenght"=>"",
            "options"=>array(
                "infinity"=>"infinity",
                "concerto"=>"concerto",
                "tutti"=>"tutti"
            ),
            "rows"=>"",
            "default"=>"",
            "disabled"=>false
        ),
        "css"=>array()
    ),
    "gdm_telaio"=>array(
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
    ),
    "gdm_dms"=>array(
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
    ),
    "gdm_pratica"=>array(
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