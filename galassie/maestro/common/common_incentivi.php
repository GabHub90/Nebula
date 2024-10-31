<?php

$this->common['fields']=array(
    "reparto"=>array(
        "js_chk_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
        "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
    )
);

$this->common['tipi']=array(
    "reparto"=>"none"
);

//EXPO e CONV devono essere definiti dalle single funzioni assieme agli UNCOMMON

//#################################################################
//caricamento del campo "mappa" che potrebbe richiedere GALILEO

$this->common['mappa']=array(
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

////////////////////////////////////////

//lettura reparto officina del collaboratore
//{"ID_coll":"1","nome":"Matteo","cognome":"Cecconi","concerto":"m.cecconi","reparto":"TDD","des_reparto":"Team di Direzione","macroreparto":"D","des_macroreparto":"Direzione","ID_gruppo":"32","gruppo":"TDD","des_gruppo":"Direttivo","pos_gruppo":"1","macrogruppo":"","des_macrogruppo":"","pos_macrogruppo":"0"}

//lettura TUTTI i reparti da GALILEO
$this->galileo->getReparti("","");
if ( $result=$this->galileo->getResult() ) {
    
    $fetID=$this->galileo->preFetchBase('reparti');

    while ($row=$this->galileo->getFetchBase('reparti',$fetID)) {
        $this->common['mappa']['reparto']['prop']['options'][$row['reparto']]=$row;
    }
}


?>