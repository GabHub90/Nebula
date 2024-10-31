<?php

$this->closure['checkParam']=function() {

    $defp=array(
        "liz_mag",
        "liz_precodice",
        "liz_articolo",
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

    $this->lista=new excalibur('statRic','Statistica ricambi:');

    //########################
    //inizializzazione excalibur
    $conv=array(
        "inizio"=>"inizio",
        "fine"=>"fine",
        "magazzino"=>"magazzino",
        "ubicazione"=>"ubicazione",
        "precodice"=>"precodice",
        "articolo"=>"articolo",
        "descr_articolo"=>"descrizione",
        "rimanenze_iniziali"=>"rimanenze_iniziali",
        "rimanenze_finali"=>"rimanenze_finali",
        "impegnato"=>"impegnato",
        "dispo"=>"dispo",
        "acquisti"=>"acquisti",
        "vendite"=>"vendite"
    );

    $mappa=array(
        "inizio"=>array("tag"=>"Da","tipo"=>"data"),
        "fine"=>array("tag"=>"A","tipo"=>"data"),
        "magazzino"=>array("tag"=>"Mag","css"=>"text-align:center;"),
        "ubicazione"=>array("tag"=>"Loc","css"=>"text-align:center;"),
        "precodice"=>array("tag"=>"precodice","css"=>"text-align:center;"),
        "articolo"=>array("tag"=>"articolo"),
        "descrizione"=>array("tag"=>"descrizione"),
        "rimanenze_iniziali"=>array("tag"=>"Inizio"),
        "rimanenze_finali"=>array("tag"=>"Fine"),
        "impegnato"=>array("tag"=>"Impegnato"),
        "dispo"=>array("tag"=>"Dispo"),
        "acquisti"=>array("tag"=>"Acquisti"),
        "vendite"=>array("tag"=>"Vendite")
    );

    $this->lista->build($conv,$mappa);

    //////////////////////////////////////////////////////////////////////////////

    $dms=substr($this->param['liz_mag'],0,1);
    
    $nebulaDefault=array();

    if ($dms=='i') {
        $obj=new galileoInfinityRicambi();
        $nebulaDefault['ricambi']=array("rocket",$obj);
    }
    if ($dms=='c') {
        $obj=new galileoConcertoRicambi();
        $nebulaDefault['ricambi']=array("maestro",$obj);
    }
    $this->galileo->setFunzioniDefault($nebulaDefault);

    $orda=mainFunc::gab_input_to_db($this->param['liz_da']);
    $ora=mainFunc::gab_input_to_db($this->param['liz_a']);

    $arr=array(
        "mag"=>substr($this->param['liz_mag'],1,2),
        "precodice"=>$this->param['liz_precodice'],
        "articolo"=>$this->param['liz_articolo'],
        "da"=>$orda,
        "a"=>$ora
    );

    $ada=(int)substr($arr['da'],0,4);
    $aa=(int)substr($arr['a'],0,4);

    //$orda=$arr['da'];
    //$ora=$arr['a'];

    $res=array();

    while ($ada<=$aa) {

        if ($ada>(int)substr($orda,0,4)) {
            $arr['da']=''.$ada.'0101';
        }

        if ($ada<(int)substr($ora,0,4)) {
            $arr['a']=''.$ada.'1231';
        }
        else {
            $arr['a']=$ora;
        }

        //echo json_encode($arr);

        $this->galileo->clearQuery();
        $this->galileo->clearQueryOggetto('default','ricambi');

        $this->galileo->executeGeneric('ricambi','getStatistica',$arr,"");

        if ($this->galileo->getResult()) {

            $fid=$this->galileo->preFetch('ricambi');

            while ($row=$this->galileo->getFetch('ricambi',$fid)) {

                if (!isset($res[$row['precodice'].'_'.$row['articolo']])) {
                    $res[$row['precodice'].'_'.$row['articolo']]=$row;
                    $res[$row['precodice'].'_'.$row['articolo']]['inizio']=$orda;
                    $res[$row['precodice'].'_'.$row['articolo']]['fine']=$ora;
                }
                else {
                    $res[$row['precodice'].'_'.$row['articolo']]['rimanenze_finali']=$row['rimanenze_finali'];
                    $res[$row['precodice'].'_'.$row['articolo']]['impegnato']=$row['impegnato'];
                    $res[$row['precodice'].'_'.$row['articolo']]['vendite']+=$row['vendite'];
                    $res[$row['precodice'].'_'.$row['articolo']]['acquisti']+=$row['acquisti'];
                }
            }
        }

        $ada++;
    }

    foreach ($res as $k=>$r) {
        $r['rimanenze_iniziali']=number_format($r['rimanenze_iniziali'],3,'.','');
        $r['rimanenze_finali']=number_format($r['rimanenze_finali'],3,'.','');
        $r['impegnato']=number_format($r['impegnato'],3,'.','');
        $r['vendite']=number_format($r['vendite'],3,'.','');
        $r['acquisti']=number_format($r['acquisti'],3,'.','');
        $r['dispo']=number_format($r['rimanenze_finali']-$r['impegnato'],3,'.','');
        $this->lista->add($r);
    }
};

?>