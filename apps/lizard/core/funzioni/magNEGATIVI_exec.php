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

    $this->lista=new excalibur('magNEGATIVI','Giacenze Negative:');

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
        "locazione"=>"locazione",
        "d_scarico"=>"d_scarico"
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
        "officina"=>array("tag"=>"ODL","css"=>"text-align:right;font-weight:bold;"),
        "interno"=>array("tag"=>"interno","css"=>"text-align:right;"),
        "locazione"=>array("tag"=>"locazione","css"=>"text-align:center;"),
        "d_scarico"=>array("tag"=>"ultimo scarico","css"=>"text-align:center;"),
    );

    $this->lista->build($conv,$mappa);

    //////////////////////////////////////////////////////////////////////////////

    $magFunc=new nebulaMagFunc($this->galileo);
    $magFunc->setWH($this->param['liz_reparto']);

    $a=array(
        "reparto"=>$this->param['liz_reparto']
    );

    foreach ($magFunc->getNegativi($a) as $k=>$l) {

        $l['listino']=number_format($l['listino'],2,',','');
        $l['giacenza']=number_format($l['giacenza'],2,',','');
        $l['impegnato']=number_format($l['impegnato'],2,',','');
        $l['officina']=number_format($l['officina'],2,',','');
        $l['interno']=number_format($l['interno'],2,',','');
        $l['d_scarico']=mainFunc::gab_todata($l['d_scarico']);

        $this->lista->add($l);
    }

};

?>