<?php

$this->closure['checkParam']=function() {

    //echo json_encode($this->param);

    $defp=array(
    );

    $txt="";

    foreach ($defp as $k=>$par) {
        if (!array_key_exists($par,$this->param)) $txt.='<div>Parametro '.$par.' mancante.</div>'; 
    }

    if ($txt!="") return $txt;

    return $txt;

};

$this->closure['buildDatas']=function() {

    $this->lista=new excalibur('gdm','Query GDM:');

    //########################
    //inizializzazione excalibur
    $conv=array(
        "id"=>"id",
        "tipologia"=>"tipologia",
        "locazione"=>"locazione",
        "annotazioni"=>"annotazioni",
        "nome"=>"nome",
        "descrizione"=>"descrizione",
        "dotASx"=>"dotASx",
        "dotADx"=>"dotADx",
        "dotPSx"=>"dotPSx",
        "dotPDx"=>"dotPDx",
        "marcaASx"=>"marcaASx",
        "marcaADx"=>"marcaADx",
        "marcaPSx"=>"marcaPSx",
        "marcaPDx"=>"marcaPDx",
        "usuraASx"=>"usuraASx",
        "usuraADx"=>"usuraADx",
        "usuraPSx"=>"usuraPSx",
        "usuraPDx"=>"usuraPDx",
        "compoGomme"=>"compoGomme",
        "tipoGomme"=>"tipoGomme",
        "colore"=>"colore",
        "idTelaio"=>"idTelaio",
        "proprietario"=>"proprietario",
        "isBusy"=>"isBusy",
        "dataCreazione"=>"dataCreazione",
        "dimeASx"=>"dimeASx",
        "dimeADx"=>"dimeADx",
        "dimePSx"=>"dimePSx",
        "dimePDx"=>"dimePDx",
        "isFull"=>"isFull",
        "ultimo"=>"ultimo",
        "aperta"=>"aperta",
        "util_ragsoc"=>"util_ragsoc",
        "intest_ragsoc"=>"intest_ragsoc",
        "util_tel"=>"util_tel",
        "intest_tel"=>"intest_tel"
    );

    $mappa=array(
        "id"=>array("tag"=>"id"),
        "dataCreazione"=>array("tag"=>"creazione"),
        "tipologia"=>array("tag"=>"tipologia"),
        "locazione"=>array("tag"=>"locazione"),
        "annotazioni"=>array("tag"=>"annotazioni"),
        "nome"=>array("tag"=>"nome"),
        "descrizione"=>array("tag"=>"descrizione"),
        "colore"=>array("tag"=>"colore"),
        "compoGomme"=>array("tag"=>"compoGomme"),
        "tipoGomme"=>array("tag"=>"tipoGomme"),
        "isFull"=>array("tag"=>"isFull"),
        "dimeASx"=>array("tag"=>"dimeASx"),
        "dotASx"=>array("tag"=>"dotASx"),
        "marcaASx"=>array("tag"=>"marcaASx"),
        "usuraASx"=>array("tag"=>"usuraASx"),
        "dimeADx"=>array("tag"=>"dimeADx"),
        "dotADx"=>array("tag"=>"dotADx"),
        "marcaADx"=>array("tag"=>"marcaADx"),
        "usuraADx"=>array("tag"=>"usuraADx"),
        "dimePSx"=>array("tag"=>"dimePSx"),
        "dotPSx"=>array("tag"=>"dotPSx"),
        "marcaPSx"=>array("tag"=>"marcaPSx"),
        "usuraPSx"=>array("tag"=>"usuraPSx"),
        "dimePDx"=>array("tag"=>"dimePDx"),
        "dotPDx"=>array("tag"=>"dotPDx"),
        "marcaPDx"=>array("tag"=>"marcaPDx"),
        "usuraPDx"=>array("tag"=>"usuraPDx"),
        "proprietario"=>array("tag"=>"proprietario"),
        "ultimo"=>array("tag"=>"ultimo movimento"),
        "idTelaio"=>array("tag"=>"Telaio"),
        "isBusy"=>array("tag"=>"isBusy"),
        "aperta"=>array("tag"=>"Richiesta Aperta"),
        "intest_ragsoc"=>array("tag"=>"Intestatario"),
        "intest_tel"=>array("tag"=>"Telefono"),
        "util_ragsoc"=>array("tag"=>"Utilizzatore"),
        "util_tel"=>array("tag"=>"Telefono")
    );

    $this->lista->build($conv,$mappa);

    ////////////////////////////////////////////////

    $obj=new galileoGDM();
    $nebulaDefault['gdm']=array("gab500",$obj);
    $obj=new galileoInfinityVeicoli();
    $nebulaDefault['veicoli']=array("rocket",$obj);
    $this->galileo->setFunzioniDefault($nebulaDefault);


    if ($this->param['liz_tipologia']=='TUTTO') unset($this->param['liz_tipologia']);
    if ($this->param['liz_compogomme']=='TUTTO') unset($this->param['liz_compogomme']);
    if ($this->param['liz_tipogomme']=='TUTTO') unset($this->param['liz_tipogomme']);
    if ($this->param['liz_proprietario']=='TUTTO') unset($this->param['liz_proprietario']);

    $this->param['liz_locazione']=trim($this->param['liz_locazione']);
    if ($this->param['liz_locazione']=="") unset($this->param['liz_locazione']);
    $this->param['liz_telaio']=trim($this->param['liz_telaio']);
    if ($this->param['liz_telaio']=="") unset($this->param['liz_telaio']);
    $this->param['liz_descrizione']=trim($this->param['liz_descrizione']);
    if ($this->param['liz_descrizione']=="") unset($this->param['liz_descrizione']);

    $this->param['liz_usura']=(float)str_replace(',','.',trim($this->param['liz_usura']));
    if (!$this->param['liz_usura'] || $this->param['liz_usura']=="") unset($this->param['liz_usura']);

    if ($this->param['liz_full']=='TUTTO') unset($this->param['liz_full']);
    elseif ($this->param['liz_full']=='F') $this->param['liz_full']='False';
    else $this->param['liz_full']='True';

    if ($this->param['liz_busy']=='TUTTO') unset($this->param['liz_busy']);
    elseif ($this->param['liz_busy']=='F') $this->param['liz_busy']='False';
    else $this->param['liz_busy']='True';

    $this->galileo->executeGeneric('gdm','getLizard',$this->param,'');

    $temp=array();

    if ($this->galileo->getResult()) {

        $fid=$this->galileo->preFetch('gdm');

        while ($row=$this->galileo->getFetch('gdm',$fid)) {

            if ($row['ultimo']!='') $row['ultimo']=mainFunc::gab_todata($row['ultimo']);
            if ($row['aperta']!='') $row['aperta']=mainFunc::gab_todata($row['aperta']);

            $temp[]=$row;
        }
    }

    //ho dovuto fare così perché sybase si bloccava per troppi indici di risultati
    //sasql_query(): SQLAnywhere: [-685] Resource governor for 'cursors' exceeded

    foreach ($temp as $k=>$row) {

        $telaio="";
        $vei=array();

        $c=0;

        if ($telaio!=$row['idTelaio']) {
            $this->galileo->clearQuery();
            $this->galileo->clearQueryOggetto('default','veicoli');

            if ($c==30) {
                //$this->galileo->resetHandler('rocket');
                $c=0;
            }

            $telaio=$row['idTelaio'];
            $vei=array();
            $c++;

            $this->galileo->executeGeneric('veicoli','rifGDM',array('telaio'=>$row['idTelaio']),'');

            if ($this->galileo->getResult()) {

                $fi2=$this->galileo->preFetch('veicoli');

                while ($row2=$this->galileo->getFetch('veicoli',$fi2)) {
                    $vei=$row2;
                }

                $this->galileo->freeHandler('rocket',$fi2);
            }
        }

        if (isset($vei['util_ragsoc'])) $row['util_ragsoc']=$vei['util_ragsoc'];
        else $row['util_ragsoc']="";
        if (isset($vei['intest_ragsoc'])) $row['intest_ragsoc']=$vei['intest_ragsoc'];
        else $row['intest_ragsoc']="";
        if (isset($vei['util_tel'])) $row['util_tel']=$vei['util_tel'];
        else $row['util_tel']="";
        if (isset($vei['intest_tel'])) $row['intest_tel']=$vei['intest_tel'];
        else $row['intest_tel']="";

        $this->lista->add($row);
    }

};

?>