<?php

$this->closure['checkParam']=function() {

    //echo json_encode($this->param);

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

    $this->lista=new excalibur('fattint','Chiusure interne Concerto:');

    $tempLista=array();

    //########################
    //inizializzazione excalibur
    $conv=array(
        "cod_officina"=>"cod_officina",
        "cod_movimento"=>"cod_movimento",
        "mat_targa"=>"targa",
        "mat_telaio"=>"telaio",
        "man"=>"man",
        "ric"=>"ric",
        "one"=>"one",
        "tot"=>"tot"
    );

    $mappa=array(
        "cod_officina"=>array("tag"=>"cod_officina"),
        "cod_movimento"=>array("tag"=>"cod_movimento"),
        "targa"=>array("tag"=>"targa"),
        "telaio"=>array("tag"=>"telaio"),
        "man"=>array("tag"=>"manod."),
        "ric"=>array("tag"=>"ricambi"),
        "one"=>array("tag"=>"oneri"),
        "tot"=>array("tag"=>"totale")
    );

    $this->lista->build($conv,$mappa);

    //########################

    $wh=new c2rWHole('',$this->galileo);
    $odlFunc=new nebulaOdlFunc($this->galileo);

    $a=array(
        "inizio"=>mainFunc::gab_input_to_db($this->param['liz_da']),
        "fine"=>mainFunc::gab_input_to_db($this->param['liz_a']),
        "dms"=>"concerto"
    );

    $wh->forceMap($a);

    $result=$wh->getFatturatoS(0,$a);

    $fid=$this->galileo->preFetchPiattaforma($wh->getPiattaforma('concerto'),$result);

    $rif=0;
    $temp="";
    $el=array();

    while ($row=$this->galileo->getFetchPiattaforma($wh->getPiattaforma('concerto'),$fid)) {

        if ($row['cod_movimento']!='OOA') continue;

        if ($rif!=$row['rif']) {

            $temp=$row['mat_targa'].'_'.$row['mat_telaio'];
            if (!array_key_exists($temp,$el)) {
                $el[$temp]=$row;
                $el[$temp]['man']=0;
                $el[$temp]['ric']=0;
                $el[$temp]['one']=0;
                $el[$temp]['tot']=0;
            }
            $rif=$row['rif'];
        }

        switch ($row['ind_tipo_riga']) {
            case 'R':
                $el[$temp]['ric']+=$row['importo'];
                $el[$temp]['tot']+=$row['importo'];
            break;
            case 'M':
                $el[$temp]['man']+=$row['importo'];
                $el[$temp]['tot']+=$row['importo'];
            break;
            case 'V':
                $el[$temp]['one']+=$row['importo'];
                $el[$temp]['tot']+=$row['importo'];
            break;
        }
    }

    foreach ($el as $k=>$l) {
        $l['ric']=number_format($l['ric'],2,',','');
        $l['man']=number_format($l['man'],2,',','');
        $l['one']=number_format($l['one'],2,',','');
        $l['tot']=number_format($l['tot'],2,',','');
        $this->lista->add($l);
    }
};

?>