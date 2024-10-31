<?php

/*
$this->common['info']=array(
    "officina"=>""
);
*/

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
$defrep=$this->id->getReparto('A');
if (count($defrep)==0) $defrep=$this->id->getReparto('S');
if (count($defrep)==0) $defrep=$this->id->getReparto('V');

//gruppi di appartenenza del collaboratore
$rifGruppi=array();

foreach ($defrep as $reparto=>$d) {
    $rifGruppi[]=$d['gruppo'];
}

//lettura officine da GALILEO
$this->galileo->getOfficine();
if ( $result=$this->galileo->getResult() ) {
    $fetID=$this->galileo->preFetchBase('reparti');
    while ($row=$this->galileo->getFetchBase('reparti',$fetID)) {

        if ($row['virtuale']=='S') continue;
        
        $a['officina']['prop']['options'][$row['reparto']]=$row;
        $a['officina']['prop']['options'][$row['reparto']]['disabled']=true;

        //se SONO il responsabile IT o un responsabile Vendite o un responsabile Service - ENABLE
        if ( in_array('ITR',$rifGruppi) || in_array('RV',$rifGruppi) || in_array('RV',$rifGruppi) ) {
            $a['officina']['prop']['options'][$row['reparto']]['disabled']=false;
        }
        elseif (array_key_exists($row['reparto'],$defrep)) {
            $a['officina']['prop']['options'][$row['reparto']]['disabled']=false;
        }

        //se il reparto è tra quelli di appartenenza
        if (array_key_exists($row['reparto'],$defrep)) {
            $a['officina']['prop']['default']=$row['reparto'];
        }


        /*considera solo la prima occorrenza nel reparto di appartenenza
        foreach ($defrep as $reparto=>$d) {
            if ( array_key_exists('officina',$d) ) {

                if ($this->classe!='RS') {
                    $a['officina']['prop']['options'][$row['reparto']]['disabled']=true;
                }
                else {
                    $a['officina']['prop']['options'][$row['reparto']]['disabled']=false;
                }

                if ($row['reparto']==$d['reparto']) {
                    $a['officina']['prop']['default']=$row['reparto'];
                    $a['officina']['prop']['options'][$row['reparto']]['disabled']=false;
                }
                
            }
        }*/
    }
}
////////////////////////////////////////

$this->common['mappa']=$a;

?>