<?php

/*
$this->common['info']=array(
    "officina"=>""
);
*/

$this->common['fields']=array(
    "magazzino"=>array(
        "js_chk_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
        "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
    )
);

$this->common['tipi']=array(
    "magazzino"=>"none"
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
    "magazzino"=>array(
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
    )
);

//////////////////////////////////////////
//reperimento officine

//lettura reparto officina del collaboratore
//{"ID_coll":"1","nome":"Matteo","cognome":"Cecconi","concerto":"m.cecconi","reparto":"TDD","des_reparto":"Team di Direzione","macroreparto":"D","des_macroreparto":"Direzione","ID_gruppo":"32","gruppo":"TDD","des_gruppo":"Direttivo","pos_gruppo":"1","macrogruppo":"","des_macrogruppo":"","pos_macrogruppo":"0"}
$defrep=$this->id->getReparto('M');

//gruppi di appartenenza del collaboratore
$rif="";

foreach ($defrep as $reparto=>$d) {
    $rif=$reparto;
    break;
}

//lettura magazzini da GALILEO
$this->galileo->getMagazzini();
if ( $result=$this->galileo->getResult() ) {
    $fetID=$this->galileo->preFetchBase('reparti');
    while ($row=$this->galileo->getFetchBase('reparti',$fetID)) {
        $a['magazzino']['prop']['options'][$row['reparto']]=$row;
        $a['magazzino']['prop']['options'][$row['reparto']]['disabled']=false;

        if ($row['reparto']==$rif) $a['magazzino']['prop']['default']=$row['reparto'];
    }
}
////////////////////////////////////////

$this->common['mappa']=$a;

?>