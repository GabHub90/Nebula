<?php

$this->closure['checkParam']=function() {

    $defp=array(
        "liz_reparto",
        "liz_da",
        "liz_a"
    );

    $txt="";

    foreach ($defp as $k=>$par) {
        if (!array_key_exists($par,$this->param)) $txt.='<div>Parametro '.$par.' mancante.</div>'; 
    }

    if ($this->param['liz_a']<$this->param['liz_da']) {
        $txt.="<div>Data di fine inferiore alla data di inizio.</div>";
        return $txt;
    }

    if (mainFunc::gab_input_to_db($this->param['liz_a'])>date('Ymd')) {
        $txt.="<div>Data di fine maggiore ad oggi.</div>";
        return $txt;
    }

    return $txt;
};

$this->closure['buildDatas']=function() {

    $this->lista=new excalibur('magGAR','Ordini in Garanzia:');

    //########################
    //inizializzazione excalibur
    $conv=array(
        "deposito"=>"deposito",
        "precodice"=>"precodice",
        "articolo"=>"articolo",
        "descr_articolo"=>"descr_articolo",
        "quantita"=>"quantita",
        "ubicazione"=>"ubicazione",
        "d_carico"=>"d_carico",
        "telaio"=>"telaio",
        "id_documento"=>"id_documento",
        "qta_scarico"=>"qta_scarico",
        "ord_for"=>"ord_for",
        "ult_scar"=>"ult_scar",
        "giacenza"=>"giacenza",
        "pren"=>"pren",
        "d_pren"=>"d_pren"
    );

    $mappa=array(
        "deposito"=>array("tag"=>"deposito","css"=>"text-align:center;"),
        "precodice"=>array("tag"=>"pre","css"=>"text-align:center;"),
        "articolo"=>array("tag"=>"articolo"),
        "descr_articolo"=>array("tag"=>"descrizione"),
        "quantita"=>array("tag"=>"qta carico","css"=>"text-align:right;"),
        "ord_for"=>array("tag"=>"Ord. For.","css"=>"text-align:center;"),
        "ubicazione"=>array("tag"=>"locazione","css"=>"text-align:center;"),
        "d_carico"=>array("tag"=>"data carico","css"=>"text-align:center;"),
        "telaio"=>array("tag"=>"telaio","css"=>"text-align:center;"),
        "id_documento"=>array("tag"=>"rif.Odl","css"=>"text-align:center;"),
        "qta_scarico"=>array("tag"=>"qta scarico","css"=>"text-align:right;"),
        "ult_scar"=>array("tag"=>"data ultimo scarico","css"=>"text-align:center;"),
        "giacenza"=>array("tag"=>"giacenza","css"=>"text-align:center;"),
        "pren"=>array("tag"=>"ultima Pren.","css"=>"text-align:center;"),
        "d_pren"=>array("tag"=>"data Pren.","css"=>"text-align:center;")
    );

    $this->lista->build($conv,$mappa);

    //////////////////////////////////////////////////////////////////////////////

    $magFunc=new nebulaMagFunc($this->galileo);
    $magFunc->setWH($this->param['liz_reparto']);

    $a=array(
        "reparto"=>$this->param['liz_reparto'],
        "da"=>mainFunc::gab_input_to_db($this->param['liz_da']),
        "a"=>mainFunc::gab_input_to_db($this->param['liz_a'])
    );

    foreach ($magFunc->getOrdiniGaranzia($a) as $k=>$l) {

        $l['quantita']=number_format($l['quantita'],2,',','');
        $l['qta_scarico']=number_format($l['qta_scarico'],2,',','');
        //$l['d_carico']=mainFunc::gab_todata($l['d_carico']);

        $this->lista->add($l);
    }

};

?>