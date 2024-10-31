<?php

$this->closure['checkParam']=function() {

    //echo json_encode($this->param);

    $defp=array();

    $txt="";

    foreach ($defp as $k=>$par) {
        if (!array_key_exists($par,$this->param)) $txt.='<div>Parametro '.$par.' mancante.</div>'; 
    }

    if ($txt!="") return $txt;

    return $txt;

};

$this->closure['buildDatas']=function() {

    $this->lista=new excalibur('gdmS','Materiale da Stoccare:');

    //########################
    //inizializzazione excalibur
    $conv=array(
        "destinazione"=>"destinazione",
        "origine"=>"origine",
        "dataRi"=>"dataRi",
        "vettura"=>"vettura",
        "nomeCliente"=>"nomeCliente",
        "gomme"=>"gomme",
        "misura"=>"misura"
    );

    $mappa=array(
        "destinazione"=>array("tag"=>"A"),
        "origine"=>array("tag"=>"Da"),
        "dataRi"=>array("tag"=>"Data"),
        "vettura"=>array("tag"=>"Vettura"),
        "nomeCliente"=>array("tag"=>"Cliente"),
        "gomme"=>array("tag"=>"Gomme"),
        "misura"=>array("tag"=>"Misura")
    );

    $this->lista->build($conv,$mappa);

    //########################

    $obj=new galileoGDM();
    $nebulaDefault['gdm']=array("gab500",$obj);
    $this->galileo->setFunzioniDefault($nebulaDefault);

    $this->galileo->executeGeneric('gdm','getStoccaggioLizard',$this->param,'');

    if ($this->galileo->getResult()) {

        $fid=$this->galileo->preFetch('gdm');

        while($row=$this->galileo->getFetch('gdm',$fid)) {

            $temp=array(
                "destinazione"=>$row['destinazione'],
                "origine"=>$row['origine'],
                "dataRi"=>mainFunc::gab_todata($row['dataRi']),
                "vettura"=>'<div>'.$row['targa'].'</div><div>'.$row['idTelaio'].'</div>',
                "nomeCliente"=>$row['nomeCliente'],
                "gomme"=>'<div>'.$row['compoGomme'].'</div><div>'.$row['tipoGomme'].'</div>',
                "misura"=>'<div>'.$row['dimeASx'].' - '.$row['dimeADx'].'</div><div>'.$row['dimePSx'].' - '.$row['dimePDx'].'</div>'
            );

            $this->lista->add($temp);
        }
    }

    //echo json_encode($this->galileo->getLog('query'));

};

?>