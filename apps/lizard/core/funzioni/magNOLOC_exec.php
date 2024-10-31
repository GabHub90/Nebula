<?php

$this->closure['checkParam']=function() {

    $defp=array(
        "liz_reparto"
    );

    $txt="";

    foreach ($defp as $k=>$par) {
        if (!array_key_exists($par,$this->param)) $txt.='<div>Parametro '.$par.' mancante.</div>'; 
    }

    return $txt;
};

$this->closure['buildDatas']=function() {

    $this->lista=new excalibur('magNOLOC','Giacenti NON locati:');

    //########################
    //inizializzazione excalibur
    $conv=array(
        "deposito"=>"deposito",
        "precodice"=>"precodice",
        "articolo"=>"articolo",
        "descr_articolo"=>"descr_articolo",
        "listino"=>"listino",
        "gm"=>"gm",
        "giacenza"=>"giacenza",
        "impegnato"=>"impegnato",
        "officina"=>"officina",
        "interno"=>"interno",
        "dispo"=>"dispo",
        "d_carico"=>"d_carico"
    );

    $mappa=array(
        "deposito"=>array("tag"=>"deposito","css"=>"text-align:center;"),
        "precodice"=>array("tag"=>"pre","css"=>"text-align:center;"),
        "articolo"=>array("tag"=>"articolo"),
        "descr_articolo"=>array("tag"=>"descrizione"),
        "listino"=>array("tag"=>"listino","css"=>"text-align:right;"),
        "gm"=>array("tag"=>"GM","css"=>"text-align:center;"),
        "giacenza"=>array("tag"=>"giacenza","css"=>"text-align:right;"),
        "impegnato"=>array("tag"=>"OT","css"=>"text-align:right;"),
        "officina"=>array("tag"=>"ODL","css"=>"text-align:right;color:red;"),
        "interno"=>array("tag"=>"interno","css"=>"text-align:right;"),
        "dispo"=>array("tag"=>"disponibilità","css"=>"text-align:right;font-weight:bold;"),
        "d_carico"=>array("tag"=>"ultimo carico","css"=>"text-align:center;"),
    );

    $this->lista->build($conv,$mappa);

    //////////////////////////////////////////////////////////////////////////////

    $magFunc=new nebulaMagFunc($this->galileo);
    $magFunc->setWH($this->param['liz_reparto']);

    $a=array(
        "reparto"=>$this->param['liz_reparto']
    );

    foreach ($magFunc->getNoloc($a) as $k=>$l) {

        $l['listino']=number_format($l['listino'],2,',','');
        $l['giacenza']=number_format($l['giacenza'],2,',','');
        $l['impegnato']=number_format($l['impegnato'],2,',','');
        $l['officina']=number_format($l['officina'],2,',','');
        $l['interno']=number_format($l['interno'],2,',','');
        $l['dispo']=number_format($l['dispo'],2,',','');
        $l['d_carico']=mainFunc::gab_todata($l['d_carico']);

        $this->lista->add($l);
    }

};

?>