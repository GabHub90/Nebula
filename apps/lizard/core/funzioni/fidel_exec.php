<?php

$this->closure['checkParam']=function() {

    //echo json_encode($this->param);

    $defp=array(
        "liz_stato",
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

    $this->lista=new excalibur('fidel','Voucher:');

    $tempLista=array();

    //########################
    //inizializzazione excalibur
    $conv=array(
        "tipo"=>"tipo",
        "tag"=>"tag",
        "utente"=>"utente",
        "creazione"=>"creazione",
        "stato"=>"stato",
        "titolo"=>"titolo",
        "offerta"=>"offerta",
        "nota"=>"nota",
        "scadenza"=>"scadenza",
        "ben1"=>"ben1",
        "ben2"=>"ben2",
        "chiusura"=>"chiusura",
        "utente_chiusura"=>"utente_chiusura",
        "dms_chiusura"=>"dms_chiusura",
        "odl_chiusura"=>"odl_chiusura"
    );

    $mappa=array(
        "tipo"=>array("tag"=>"Tipo"),
        "tag"=>array("tag"=>"Riferimento"),
        "creazione"=>array("tag"=>"Creato","tipo"=>"data"),
        "utente"=>array("tag"=>"Utente"),
        "stato"=>array("tag"=>"Stato"),
        "scadenza"=>array("tag"=>"Scade","tipo"=>"data"),
        "titolo"=>array("tag"=>"Titolo"),
        "offerta"=>array("tag"=>"Offerta"),
        "nota"=>array("tag"=>"Nota"),
        "ben1"=>array("tag"=>""),
        "ben2"=>array("tag"=>""),
        "chiusura"=>array("tag"=>"Chiuso","tipo"=>"data"),
        "utente_chiusura"=>array("tag"=>"Utente"),
        "dms_chiusura"=>array("tag"=>"Dms"),
        "odl_chiusura"=>array("tag"=>"Odl")
    );

    $this->lista->build($conv,$mappa);

    //########################

    $obj=new galileoFidel();
    $gdef['fidel']=array("gab500",$obj);
    $this->galileo->setFunzioniDefault($gdef);

    $p=array(
        "stato"=>$this->param['liz_stato']=='tutti'?"":$this->param['liz_stato'],
        "da"=>mainFunc::gab_input_to_db($this->param['liz_da']),
        "a"=>mainFunc::gab_input_to_db($this->param['liz_a'])
    );

    $today=date('Ymd');

    $this->galileo->executeGeneric('fidel','getLizard',$p,'');

    if ($this->galileo->getResult()) {
        $fid=$this->galileo->preFetch('fidel');
        while ($row=$this->galileo->getFetch('fidel',$fid)) {

            if ($row['stato']=='aperto') {

                if ($today>$row['scadenza']) {
                    //se è scaduto aggiorna il record
                    $a=array(
                        "stato"=>"scaduto"
                    );
                    $this->galileo->executeUpdate('fidel','FIDEL_voucher',$a,"ID='".$row['ID']."'");
                    $row['stato']='scaduto';
                }
            }
            
            $this->lista->add($row);
        }
    }

    

};

?>