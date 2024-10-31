<?php

$this->closure['checkParam']=function() {

    //echo json_encode($this->param);

    $defp=array(
        "liz_reparto"
    );

    $txt="";

    foreach ($defp as $k=>$par) {
        if (!array_key_exists($par,$this->param)) $txt.='<div>Parametro '.$par.' mancante.</div>'; 
    }

    if ($txt!="") return $txt;

    return $txt;

};

$this->closure['buildDatas']=function() {

    $this->lista=new excalibur('comGar','Commesse in garanzia:');

    $tempLista=array();

    //########################
    //inizializzazione excalibur
    $conv=array(
        "dms"=>"dms",
        "d_documento"=>"apertura",
        "giorni"=>"giorni",
        "cod_officina"=>"cod_officina",
        "cod_movimento"=>"cod_movimento",
        "rif"=>"rif",
        "mat_targa"=>"targa",
        "mat_telaio"=>"telaio",
        "cod_veicolo"=>"modello",
        "des_veicolo"=>"des_veicolo",
        "cod_anagra_util"=>"cod_util",
        "util_ragsoc"=>"util",
        "intest_ragsoc"=>"intest",
        "testo"=>"testo"
    );

    $mappa=array(
        "dms"=>array("tag"=>"dms"),
        "apertura"=>array("tag"=>"apertura"),
        "giorni"=>array("tag"=>"giorni"),
        "rif"=>array("tag"=>"rif"),
        "cod_officina"=>array("tag"=>"cod_officina"),
        "cod_movimento"=>array("tag"=>"cod_movimento"),
        "targa"=>array("tag"=>"targa"),
        "telaio"=>array("tag"=>"telaio"),
        "modello"=>array("tag"=>"modello"),
        "des_veicolo"=>array("tag"=>"veicolo"),
        "util"=>array("tag"=>"util"),
        "intest"=>array("tag"=>"intest"),
        "testo"=>array("tag"=>"lamentati"),
    );

    $this->lista->build($conv,$mappa);

    //########################

    $wh=new avalonWHole($this->param['liz_reparto'],$this->galileo);
    $odlFunc=new nebulaOdlFunc($this->galileo);

    $a=array(
        "inizio"=>date("Ymd"),
        "fine"=>date("Ymd")
    );

    $wh->build($a);

    $dms=$wh->getTodayDms(date('Ymd'));

    $odlFunc->setGalileo($dms);

    $officina=$odlFunc->getDmsRep($dms,$this->param['liz_reparto']);

    $this->galileo=$odlFunc->exportGalileo();

    $this->galileo->clearQuery();

    $temp=array(
        "officina"=>$officina,
        "tipo"=>"aperti",
        "tipo_carico"=>"'G'"
    );

    $this->galileo->executeGeneric('odl','getCliLamentati',$temp,'');

    if ($this->galileo->getResult()) {

        $fid=$this->galileo->preFetch('odl');

        $rif=0;
        $txt="";
        $actual=array();

        while ($row=$this->galileo->getFetch('odl',$fid)) {

            if ($rif!=$row['rif'] && $txt!="") {

                $actual['giorni']=mainFunc::gab_delta_tempo($actual['d_documento'],date('Ymd'),'g');

                $actual['d_documento']=mainFunc::gab_todata($actual['d_documento']);

                $actual['testo']=$txt;

                $this->lista->add($actual);

                $txt="";
            }

            $rif=$row['rif'];
            $txt.='-'.$row['des_riga'].'-';
            $actual=$row;

        }

        if ($txt!="") {

            $actual['giorni']=mainFunc::gab_delta_tempo($actual['d_documento'],date('Ymd'),'g');
            $actual['d_documento']=mainFunc::gab_todata($actual['d_documento']);
            $actual['testo']=$txt;

            $this->lista->add($actual);

        }
    }

};

?>