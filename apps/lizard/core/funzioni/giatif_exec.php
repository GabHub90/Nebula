<?php

$this->closure['checkParam']=function() {

    $defp=array(
        "liz_mag",
        "liz_cod",
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

    if (substr($this->param['liz_a'],0,4)!=substr($this->param['liz_da'],0,4)) $txt.="<div>Le date devono essere nello stesso anno.</div>";

    if ($txt!="") return $txt;

    return $txt;  
};

$this->closure['buildDatas']=function() {

    $this->lista=new excalibur('giatif','Giacenze Utif:');

    //########################
    //inizializzazione excalibur
    $conv=array(
        "inizio"=>"inizio",
        "fine"=>"fine",
        "magazzino"=>"magazzino",
        "nomen_intra"=>"nomen_intra",
        "precodice"=>"precodice",
        "articolo"=>"articolo",
        "descrizione"=>"descrizione",
        "rimanenze_iniziali"=>"rimanenze_iniziali",
        "rimanenze_finali"=>"rimanenze_finali",
        "acquisti"=>"acquisti",
        "vendite"=>"vendite"
    );

    $mappa=array(
        "inizio"=>array("tag"=>"Da","tipo"=>"data"),
        "fine"=>array("tag"=>"A","tipo"=>"data"),
        "magazzino"=>array("tag"=>"magazzino"),
        "nomen_intra"=>array("tag"=>"utif"),
        "precodice"=>array("tag"=>"precodice","css"=>"text-align:center;"),
        "articolo"=>array("tag"=>"articolo"),
        "descrizione"=>array("tag"=>"descrizione"),
        "rimanenze_iniziali"=>array("tag"=>"Inizio"),
        "rimanenze_finali"=>array("tag"=>"Fine"),
        "acquisti"=>array("tag"=>"Acquisti"),
        "vendite"=>array("tag"=>"Vendite")
    );

    $this->lista->build($conv,$mappa);

    $temp=array(
        'descrizione'=>array('op'=>"titolo","val"=>"Totale"),
        'rimanenze_iniziali'=>array('op'=>'sum','val'=>0,'dec'=>3),
        'rimanenze_finali'=>array('op'=>'sum','val'=>0,'dec'=>3),
        'acquisti'=>array('op'=>'sum','val'=>0,'dec'=>3),
        'vendite'=>array('op'=>'sum','val'=>0,'dec'=>3)
    );

    $this->lista->setFooter('somma',$temp);

    //////////////////////////////////////////////////////////////////////////////

    $nebulaDefault=array();
    $obj=new galileoInfinityRicambi();
    $nebulaDefault['ricambi']=array("rocket",$obj);
    $this->galileo->setFunzioniDefault($nebulaDefault);

    $arr=array(
        "mag"=>$this->param['liz_mag'],
        "cod"=>$this->param['liz_cod'],
        "da"=>mainFunc::gab_input_to_db($this->param['liz_da']),
        "a"=>mainFunc::gab_input_to_db($this->param['liz_a'])
    );

    $this->galileo->executeGeneric('ricambi','getCodiciUtif',$arr,"");

    if ($this->galileo->getResult()) {

        $fid=$this->galileo->preFetch('ricambi');

        while ($row=$this->galileo->getFetch('ricambi',$fid)) {

            $row['rimanenze_iniziali']=0;
            $row['rimanenze_finali']=0;
            $row['vendite']=0;
            $row['acquisti']=0;

            $this->galileo->clearQuery();
            $this->galileo->clearQueryOggetto('default','ricambi');

            $arr['precodice']=$row['precodice'];
            $arr['articolo']=$row['articolo'];

            $this->galileo->executeGeneric('ricambi','getGiacenza',$arr,"");

            $fi2=$this->galileo->preFetch('ricambi');

            while ($row2=$this->galileo->getFetch('ricambi',$fi2)) {
                $row['rimanenze_iniziali']=$row2['rimanenze_iniziali'];
                $row['rimanenze_finali']=$row2['rimanenze_finali'];
                $row['vendite']=$row2['vendite'];
                $row['acquisti']=$row2['acquisti'];
            }

            $this->lista->add($row);
        }

    }
};

?>