<?php

$this->closure['checkParam']=function() {

    $defp=array(
        "liz_sede",
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

    $this->lista=new excalibur('carb','Buoni Carburante:');

    //########################
    //inizializzazione excalibur
    $conv=array(
        "ID"=>"ID",
        "stato"=>"stato",
        "d_stampa"=>"d_stampa",
        "importo"=>"importo",
        "carburante"=>"carburante",
        "cod_reparto"=>"cod_reparto",
        "des_reparto"=>"des_reparto",
        "causale"=>"causale",
        "richiedente"=>"richiedente",
        "nota"=>"nota",
        "targa"=>"targa",
        "telaio"=>"telaio",
        "des_veicolo"=>"des_veicolo",
        "d_creazione"=>"d_creazione",
        "operatore"=>"operatore",
        "d_ris"=>"d_ris",
        "operatore_ris"=>"operatore_ris",
        "nota_ris"=>"nota_ris",
        "d_annullo"=>"d_annullo",
        "operatore_annullo"=>"operatore_annullo",
        "nota_annullo"=>"nota_annullo"
    );

    $mappa=array(
        "ID"=>array("tag"=>"numero"),
        "stato"=>array("tag"=>"stato"),
        "d_stampa"=>array("tag"=>"data stampa"),
        "importo"=>array("tag"=>"importo"),
        "carburante"=>array("tag"=>"carburante"),
        "cod_reparto"=>array("tag"=>"codice reparto"),
        "des_reparto"=>array("tag"=>"reparto"),
        "causale"=>array("tag"=>"causale"),
        "richiedente"=>array("tag"=>"richiedente"),
        "nota"=>array("tag"=>"nota"),
        "targa"=>array("tag"=>"targa"),
        "telaio"=>array("tag"=>"telaio"),
        "des_veicolo"=>array("tag"=>"veicolo"),
        "d_creazione"=>array("tag"=>"data creazione"),
        "operatore"=>array("tag"=>"operatore"),
        "d_ris"=>array("tag"=>"data risarcimento"),
        "operatore_ris"=>array("tag"=>"operatore ris."),
        "nota_ris"=>array("tag"=>"nota ris."),
        "d_annullo"=>array("tag"=>"data annullo"),
        "operatore_annullo"=>array("tag"=>"opertore annullo"),
        "nota_annullo"=>array("tag"=>"nota annullo")
    );

    $this->lista->build($conv,$mappa);

    //////////////////////////////////////////////////////////////////////////////

    $nebulaDefault=array();
    $obj=new galileoCarb();
    $nebulaDefault['carb']=array("gab500",$obj);
    $this->galileo->setFunzioniDefault($nebulaDefault);

    $arr=array(
        "da"=>mainFunc::gab_input_to_db($this->param['liz_da']),
        "a"=>mainFunc::gab_input_to_db($this->param['liz_a']),
        "sede"=>$this->param['liz_sede']
    );

    $this->galileo->executeGeneric('carb','lizard_buoni',$arr,"d_stampa");

    if ($this->galileo->getResult()) {

        $fid=$this->galileo->preFetch('carb');

        while ($row=$this->galileo->getFetch('carb',$fid)) {

            if ($row['d_stampa']!="") $row['d_stampa']=mainFunc::gab_todata($row['d_stampa']);
            if ($row['d_creazione']!="") $row['d_creazione']=mainFunc::gab_todata($row['d_creazione']);
            if ($row['d_ris']!="") $row['d_ris']=mainFunc::gab_todata($row['d_ris']);
            if ($row['d_annullo']!="") $row['d_annullo']=mainFunc::gab_todata($row['d_annullo']);

            $this->lista->add($row);
        }

    }
};

?>