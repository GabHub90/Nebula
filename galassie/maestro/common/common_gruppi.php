<?php

$this->common['fields']=array(
    "macroreparto"=>array(
        "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
        "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
    ),
    "today"=>array(
        "js_chk_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
        "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
    ),
    "reparto"=>array(
        "js_chk_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
        "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
    )
);

$this->common['tipi']=array(
    "macroreparto"=>"none",
    "today"=>"none",
    "reparto"=>"none"
);

//EXPO e CONV devono essere definiti dalle single funzioni assieme agli UNCOMMON

//#################################################################
//caricamento del campo "mappa" che potrebbe richiedere GALILEO

$a=array(
    "macroreparto"=>array(
        "prop"=>array(
            "input"=>"select",
            "tipo"=>"",
            "maxlenght"=>"",
            "options"=>array(),
            "rows"=>"",
            "default"=>"",
            "disabled"=>false
        ),
        "css"=>array()
    ),
    "today"=>array(
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
    "reparto"=>array(
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

//////////////////////////////////////////
//reperimento macroreparti

//lettura officine da GALILEO
$this->galileo->getMacroreparti();

$fetID=$this->galileo->preFetchBase('maestro');
while ($row=$this->galileo->getFetchBase('maestro',$fetID)) {
    $a['macroreparto']['prop']['options'][$row['tipo']]=$row;
}

/*
if ( $result=$this->galileo->getResult() ) {
    //GAB500
    while ($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
        $a['macroreparto']['prop']['options'][$row['tipo']]=$row;
    }
}*/
////////////////////////////////////////

$this->common['mappa']=$a;

?>