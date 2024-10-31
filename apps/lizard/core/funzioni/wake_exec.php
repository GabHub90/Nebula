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

    $this->lista=new excalibur('wake','Ultimo passaggio a pagamento (infinity):');

    //########################
    //inizializzazione excalibur
    $conv=array(
        "count"=>"count",
        "d_arrivo"=>"d_arrivo",
        "id_documento"=>"rif",
        "tipo_doc"=>"lista",
        "d_fatt"=>"d_fatt",
        "km"=>"km",
        "targa"=>"targa",
        "telaio"=>"telaio",
        "cod_marca"=>"cod_marca",
        "des_veicolo"=>"des_veicolo",
        "codice_contatto"=>"cod_util",
        "util_ragsoc"=>"util",
        "util_mail"=>"util_mail",
        "util_tel"=>"util_tel",
        "mkt_util"=>"mkt_util",
        "id_cliente"=>"cod_intest",
        "cli_ragsoc"=>"intest",
        "cli_mail"=>"intest_mail",
        "cli_tel"=>"intest_tel",
        "mkt_cli"=>"mkt_intest",
        "lams"=>"lams"
    );

    $mappa=array(
        "count"=>array("tag"=>""),
        "d_arrivo"=>array("tag"=>"d_ritiro usato"),
        "rif"=>array("tag"=>"rif"),
        "lista"=>array("tag"=>"lista"),
        "d_fatt"=>array("tag"=>"d_fatt","tipo"=>"data"),
        "km"=>array("tag"=>"km"),
        "targa"=>array("tag"=>"targa"),
        "telaio"=>array("tag"=>"telaio"),
        "cod_marca"=>array("tag"=>"marca"),
        "des_veicolo"=>array("tag"=>"des_veicolo"),
        "cod_intest"=>array("tag"=>"intest"),
        "intest"=>array("tag"=>"Intest_ragsoc"),
        "intest_mail"=>array("tag"=>"intest_mail"),
        "intest_tel"=>array("tag"=>"intest_tel"),
        "mkt_intest"=>array("tag"=>"intest_mkt"),
        "cod_util"=>array("tag"=>"util"),
        "util"=>array("tag"=>"Util_ragsoc"),
        "util_mail"=>array("tag"=>"util_mail"),
        "util_tel"=>array("tag"=>"util_tel"),
        "mkt_util"=>array("tag"=>"util_mkt"),
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

    $this->galileo->executeGeneric('odl','getUltimoPag',$a,'');

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