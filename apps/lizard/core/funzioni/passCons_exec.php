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

    $this->lista=new excalibur('passCons','Passaggio dopo consegna (infinity):');

    //########################
    //inizializzazione excalibur
    $conv=array(
        "count"=>"count",
        "d_arrivo"=>"d_arrivo",
        "vend_nuovo"=>"vend_nuovo",
        "id_veicolo"=>"id_veicolo",
        "cod_marca"=>"cod_marca",
        "targa"=>"targa",
        "telaio"=>"telaio",
        "cod_modello"=>"cod_modello",
        "des_veicolo"=>"des_veicolo",
        "d_cons"=>"d_cons",
        "codice_cliente"=>"codice_cliente",
        "cli_ragsoc"=>"cli_ragsoc",
        "cli_localita"=>"cli_localita",
        "intest_mail"=>"intest_mail",
        "intest_tel"=>"intest_tel",
        "mkt_cli"=>"mkt_cli",
        "codice_contatto"=>"codice_contatto",
        "util_ragsoc"=>"util_ragsoc",
        "util_localita"=>"util_localita",
        "mkt_util"=>"mkt_util",
        "util_mail"=>"util_mail",
        "util_tel"=>"util_tel",
        "tipo_doc"=>"tipo_doc",
        "d_fatt"=>"d_fatt",
        "km"=>"km",
        "lams"=>"lams"
    );

    $mappa=array(
        "count"=>array("tag"=>""),
        "d_arrivo"=>array("tag"=>"d_ritiro usato","tipo"=>"data"),
        "vend_nuovo"=>array("tag"=>"venditore"),
        "id_veicolo"=>array("tag"=>"veicolo"),
        "cod_marca"=>array("tag"=>"marca"),
        "targa"=>array("tag"=>"targa"),
        "telaio"=>array("tag"=>"telaio"),
        "cod_modello"=>array("tag"=>"modello"),
        "des_veicolo"=>array("tag"=>"des_veicolo"),
        "d_cons"=>array("tag"=>"d_consegna","tipo"=>"data"),
        "codice_cliente"=>array("tag"=>"intest"),
        "cli_ragsoc"=>array("tag"=>"Intest_ragsoc"),
        "cli_localita"=>array("tag"=>"Intest_Località"),
        "mkt_cli"=>array("tag"=>"intest_mkt"),
        "intest_mail"=>array("tag"=>"intest_mail"),
        "intest_tel"=>array("tag"=>"intest_tel"),
        "codice_contatto"=>array("tag"=>"util"),
        "util_ragsoc"=>array("tag"=>"Util_ragsoc"),
        "util_localita"=>array("tag"=>"Util_Località"),
        "mkt_util"=>array("tag"=>"util_mkt"),
        "util_mail"=>array("tag"=>"util_mail"),
        "util_tel"=>array("tag"=>"util_tel"),
        "tipo_doc"=>array("tag"=>"Doc"),
        "d_fatt"=>array("tag"=>"d_fatt","tipo"=>"data"),
        "km"=>array("tag"=>"Km"),
        "lams"=>array("tag"=>"lamentati")
    );

    $this->lista->build($conv,$mappa);

    //########################

    $nebulaDefault=array();
    $obj=new galileoInfinityODL();
    $nebulaDefault['odl']=array("rocket",$obj);
    $this->galileo->setFunzioniDefault($nebulaDefault);

    $a=array(
        "inizio"=>mainFunc::gab_input_to_db($this->param['liz_da']),
        "fine"=>mainFunc::gab_input_to_db($this->param['liz_a']),
    );

    $this->galileo->executeGeneric('odl','getPassConsegna',$a,'');

    $c=0;

    if ($this->galileo->getResult()) {

        $fid=$this->galileo->preFetch('odl');

        while ($row=$this->galileo->getFetch('odl',$fid)) {

            $c++;
            $row['count']=$c;

            $this->lista->add($row);
        }
    }

};

?>