<?php

$this->closure['checkParam']=function() {

    $defp=array(
        "liz_da",
        "liz_a"
    );

    $txt="";

    foreach ($defp as $k=>$par) {
        if (!array_key_exists($par,$this->param)) $txt.='<div>Parametro '.$par.' mancante.</div>'; 
    }

    if ($txt!="") return $txt;

    if ($this->param['liz_a']<$this->param['liz_da']) $txt.="<div>Data di fine inferiore alla data di inizio.</div>";

    if ($txt!="") return $txt;

    return $txt;

    
};

$this->closure['buildDatas']=function() {

    $this->lista=new excalibur('olio','Movimentazione Olio:');

    //########################
    //inizializzazione excalibur
    $conv=array(
        "articolo"=>"articolo",
        "descr_articolo"=>"descr_articolo",
        "totale"=>"totale"
    );

    $mappa=array(
        "articolo"=>array("tag"=>"articolo"),
        "descr_articolo"=>array("tag"=>"descrizione"),
        "totale"=>array("tag"=>"tot","css"=>"text-align:right;")
    );

    $this->lista->build($conv,$mappa);

    //////////////////////////////////////////////////////////////////////////////

    $temp=array(
        "GENOILLONG4"=>array(
            "articolo"=>"GENOILLONG4",
            "descr_articolo"=>"",
            "totale"=>0
        ),
        "003.MO02"=>array(
            "articolo"=>"003.MO02",
            "descr_articolo"=>"",
            "totale"=>0
        ),
        "GENOILLONG3"=>array(
            "articolo"=>"GENOILLONG3",
            "descr_articolo"=>"",
            "totale"=>0
        ),
        "003.MO03"=>array(
            "articolo"=>"003.MO03",
            "descr_articolo"=>"",
            "totale"=>0
        ),
        "GENOILSPECG"=>array(
            "articolo"=>"GENOILSPECG",
            "descr_articolo"=>"",
            "totale"=>0
        ),
        "003.MO01"=>array(
            "articolo"=>"003.MO01",
            "descr_articolo"=>"",
            "totale"=>0
        ),
        "003.MOLP"=>array(
            "articolo"=>"003.MOLP",
            "descr_articolo"=>"",
            "totale"=>0
        )
    );

    $arr=array(
        "da"=>mainFunc::gab_input_to_db($this->param['liz_da']),
        "a"=>mainFunc::gab_input_to_db($this->param['liz_a']),
        "codici"=>""
    );

    foreach ($temp as $k=>$t) {
        $arr['codici'].="'".$k."',";
    }

    $arr['codici']=substr($arr['codici'],0,-1);

    //////////////////////////////////////////////////////////////////
    $nebulaDefault=array();
    $obj=new galileoInfinityRicambi();
    $nebulaDefault['ricambi']=array("rocket",$obj);
    $this->galileo->setFunzioniDefault($nebulaDefault);

    $this->galileo->executeGeneric('ricambi','getScarichiCodiciOfficina',$arr,"");

    if ($this->galileo->getResult()) {

        $fid=$this->galileo->preFetch('ricambi');

        while ($row=$this->galileo->getFetch('ricambi',$fid)) {

            if (array_key_exists($row['articolo'],$temp)) {
                $temp[$row['articolo']]['descr_articolo']=$row['descr_articolo'];
                $temp[$row['articolo']]['totale']+=$row['quantita'];
            }
        }

    }

    //////////////////////////////////////////////////////////////////
    //CONCERTO
    $nebulaDefault=array();
    $obj=new galileoConcertoRicambi();
    $nebulaDefault['ricambi']=array("maestro",$obj);
    $this->galileo->setFunzioniDefault($nebulaDefault);

    $this->galileo->executeGeneric('ricambi','getScarichiCodiciOfficina',$arr,"");

    if ($this->galileo->getResult()) {

        $fid=$this->galileo->preFetch('ricambi');

        while ($row=$this->galileo->getFetch('ricambi',$fid)) {

            if (array_key_exists($row['articolo'],$temp)) {
                $temp[$row['articolo']]['descr_articolo']=$row['descr_articolo'];
                $temp[$row['articolo']]['totale']+=$row['quantita']/10;
            }
        }

    }
    //////////////////////////////////////////////////////////////////

    foreach ($temp as $k=>$t) {

        $t['totale']=number_format($t['totale'],2,',','');
        $this->lista->add($t);
    }
};

?>