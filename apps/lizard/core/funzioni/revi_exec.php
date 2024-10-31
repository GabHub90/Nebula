<?php

$this->closure['checkParam']=function() {

    $defp=array(
        "liz_data"
    );

    $txt="";

    foreach ($defp as $k=>$par) {
        if (!array_key_exists($par,$this->param)) $txt.='<div>Parametro '.$par.' mancante.</div>'; 
    }

    if ($txt!="") return $txt;

    $this->param['origin']=mainfunc::gab_input_to_db($this->param['liz_data']);

    //$this->param['liz_data']=mainfunc::gab_input_to_db($this->param['liz_data']);
    $this->param['liz_data']=((int)substr($this->param['origin'],0,4)-2).substr($this->param['origin'],4,4);

    $this->param['inizio']=substr($this->param['liz_data'],0,6).'01';
    $this->param['fine']=date('Ymt',mainFunc::gab_tots($this->param['inizio']));

    return $txt;
};

$this->closure['buildDatas']=function() {

    $this->lista=new excalibur('revi','Scadenza revisione: '.mainfunc::gab_todata($this->param['fine']));

    //########################
    //inizializzazione excalibur
    $conv=array(
        "count"=>"count",
        "scadenza"=>"scadenza",
        "tipo"=>"tipo",
        "flag"=>"flag",
        "targa"=>"targa",
        "telaio"=>"telaio",
        "ragsoc"=>"ragsoc",
        "mkt"=>"mkt",
        "mail"=>"mail",
        "tel"=>"tel",
        "indirizzo"=>"indirizzo",
        "localita"=>"localita",
        "cap"=>"cap",
        "provincia"=>"provincia"
    );

    $mappa=array(
        "count"=>array("tag"=>""),
        "scadenza"=>array("tag"=>"Scadenza","tipo"=>"data"),
        "tipo"=>array("tag"=>"Tipo"),
        "flag"=>array("tag"=>"Nominativo"),
        "targa"=>array("tag"=>"Targa"),
        "telaio"=>array("tag"=>"Telaio"),
        "ragsoc"=>array("tag"=>"Ragsoc"),
        "mkt"=>array("tag"=>"Consenso"),
        "mail"=>array("tag"=>"Mail"),
        "tel"=>array("tag"=>"Tel"),
        "indirizzo"=>array("tag"=>"Indirizzo"),
        "localita"=>array("tag"=>"Città"),
        "cap"=>array("tag"=>"CAP"),
        "provincia"=>array("tag"=>"Prov")
    );

    $this->lista->build($conv,$mappa);

    //////////////////////////////////////////////////////////////////////////////

    $nebulaDefault=array();
    $obj=new galileoInfinityODL();
    $nebulaDefault['odl']=array("rocket",$obj);
    $obj=new galileoInfinityVeicoli();
    $nebulaDefault['veicoli']=array("rocket",$obj);
    $this->galileo->setFunzioniDefault($nebulaDefault);

    $tarr=array();
    $tarr2=array();

    //si basa sulla data di fatturazione ultima revisione calcolata in checkParam
    $arr=array(
        "inizio"=>$this->param['inizio'],
        "fine"=>$this->param['fine']
    );

    $this->galileo->executeGeneric('odl','ultimaRevisione',$arr,"");

    if ($this->galileo->getResult()) {

        $fid=$this->galileo->preFetch('odl');

        while ($row=$this->galileo->getFetch('odl',$fid)) {

            //se è un ripristino
            //if ($row['intest_codice']=='30002') continue;

            //se la vettura è stata ritirata e non rivenduta saltala
            if ($row['usato']!=0) {
                
                if ($row['d_uscita']=='') {
                    if (isset($tarr[$row['telaio']])) unset($tarr[$row['telaio']]);
                    continue;
                }

                $row['ragsoc']=$row['u_cli_ragsoc'];
                $row['mail']=$row['u_cli_mail'];
                $row['tel']=$row['u_cli_tel'];
                $row['indirizzo']=$row['u_indirizzo'];
                $row['localita']=$row['u_localita'];
                $row['cap']=$row['u_cap'];
                $row['provincia']=$row['u_provincia'];
                $row['mkt']=$row['mkt_u_cli'];

                $row['flag']='da Contratto';
            }
            elseif ($row['util_ragsoc']!="") {
                $row['ragsoc']=$row['util_ragsoc'];
                $row['mail']=$row['util_mail'];
                $row['tel']=$row['util_tel'];
                $row['indirizzo']=$row['util_indirizzo'];
                $row['localita']=$row['util_localita'];
                $row['cap']=$row['util_cap'];
                $row['provincia']=$row['util_provincia'];
                $row['mkt']=$row['mkt_util'];

                $row['flag']='Utilizzatore';
            }
            else {
                $row['ragsoc']=$row['intest_ragsoc'];
                $row['mail']=$row['intest_mail'];
                $row['tel']=$row['intest_tel'];
                $row['indirizzo']=$row['intest_indirizzo'];
                $row['localita']=$row['intest_localita'];
                $row['cap']=$row['intest_cap'];
                $row['provincia']=$row['intest_provincia'];
                $row['mkt']=$row['mkt_intest'];

                $row['flag']='Intestatario';
            }

            $row['scadenza']=date('Ymt',mainFunc::gab_tots($this->param['origin']));
            $row['tipo']='revisione';

            $tarr[$row['telaio']]=$row;
        }
    }

    $this->galileo->clearQuery();

    //######################################################
    //CONCERTO
    $nebulaDefault=array();
    $obj=new galileoConcertoODL();
    $nebulaDefault['odl']=array("maestro",$obj);
    $this->galileo->setFunzioniDefault($nebulaDefault);

    $this->galileo->executeGeneric('odl','ultimaRevisione',$arr,"");

    if ($this->galileo->getResult()) {

        $fid=$this->galileo->preFetch('odl');

        while ($row=$this->galileo->getFetch('odl',$fid)) {

            if ($row['util_ragsoc']!='') {
                $row['ragsoc']=$row['util_ragsoc'];
                $row['mail']=$row['util_mail'];
                $row['tel']=$row['util_tel'];
                $row['indirizzo']=$row['util_indirizzo'];
                $row['localita']=$row['util_localita'];
                $row['cap']=$row['util_cap'];
                $row['provincia']=$row['util_provincia'];
                $row['mkt']=$row['util_mkt'];

                $row['flag']='Utilizzatore';
            }
            else {
                $row['ragsoc']=$row['intest_ragsoc'];
                $row['mail']=$row['intest_mail'];
                $row['tel']=$row['intest_tel'];
                $row['indirizzo']=$row['intest_indirizzo'];
                $row['localita']=$row['intest_localita'];
                $row['cap']=$row['intest_cap'];
                $row['provincia']=$row['intest_provincia'];
                $row['mkt']=$row['intest_mkt'];

                $row['flag']='Intestatario';
            }

            $row['scadenza']=date('Ymt',mainFunc::gab_tots($this->param['origin']));
            $row['tipo']='concerto';

            $tarr[$row['telaio']]=$row;

            ////////////////////////////////////////////////////
            //di solito sono meno di 10 e quindi posso interessare infinity senza chiudere e riaprire le connessioni

            $a=array(
                "telaio"=>$row['telaio'],
                "d"=>$row['d_fatt']
            );

            $this->galileo->clearQuery();

            $this->galileo->executeGeneric('veicoli','gestioniTelaioUsato',$a,'');

            if ($this->galileo->getResult()) {

                $fid2=$this->galileo->preFetch('veicoli');

                while ($row2=$this->galileo->getFetch('veicoli',$fid2)) {

                    if ($row2['d_uscita']=='') {
                        if (isset($tarr[$row2['telaio']])) unset($tarr[$row2['telaio']]);
                        continue;
                    }

                    $tarr[$row['telaio']]['ragsoc']=$row2['u_cli_ragsoc'];
                    $tarr[$row['telaio']]['mail']=$row2['u_cli_mail'];
                    $tarr[$row['telaio']]['tel']=$row2['u_cli_tel'];
                    $tarr[$row['telaio']]['indirizzo']=$row2['u_cli_indirizzo'];
                    $tarr[$row['telaio']]['localita']=$row2['u_cli_localita'];
                    $tarr[$row['telaio']]['cap']=$row2['u_cli_cap'];
                    $tarr[$row['telaio']]['provincia']=$row2['u_cli_provincia'];
                    $tarr[$row['telaio']]['mkt']=$row2['mkt_u_cli'];

                    $tarr[$row['telaio']]['flag']='da Contratto';

                }
            }
        }
    }

    //ci sono un sacco di date provenienti da CONCERTO sbagliate su infinity
    //05.02.2024 - la distribuzione inserisce in INFINITY la data dell'ultima revisione quindi i riferimenti sono come per la fatturazione
    $arr=array(
        "inizio"=>$this->param['inizio'],
        "fine"=>$this->param['fine']
    );

    //$arr['fine']=date('Ymt',mainFunc::gab_tots($arr['inizio']));

    $this->galileo->executeGeneric('veicoli','scadenzaRevisione',$arr,"");

    if ($this->galileo->getResult()) {

        $fid=$this->galileo->preFetch('veicoli');

        //$c=0;

        while ($row=$this->galileo->getFetch('veicoli',$fid)) {

            //se il telaio è già stato calcolato in precedenza in base alla fatturazione salta
            if (isset($tarr[$row['telaio']])) continue;

            if ($row['du_usato']!=0) {
                
                if ($row['du_uscita']=='') {
                    if (isset($tarr2[$row['telaio']])) unset($tarr2[$row['telaio']]);
                    continue;
                }

                $row['ragsoc']=$row['u_contratto'];
                $row['mail']=$row['u_mail'];
                $row['tel']=$row['u_tel'];
                $row['indirizzo']=$row['u_indirizzo'];
                $row['localita']=$row['u_localita'];
                $row['cap']=$row['u_cap'];
                $row['provincia']=$row['u_provincia'];
                $row['mkt']=$row['u_mkt'];

                $row['flag']='da Riscatto';
            }
            else {
                $row['ragsoc']=$row['intest_contratto'];
                $row['mail']=$row['intest_mail'];
                $row['tel']=$row['intest_tel'];
                $row['indirizzo']=$row['indirizzo'];
                $row['localita']=$row['localita'];
                $row['cap']=$row['cap'];
                $row['provincia']=$row['provincia'];
                $row['mkt']=$row['mkt'];

                $row['flag']='da Contratto';
            }

            $row['scadenza']=date('Ymt',mainFunc::gab_tots($this->param['origin']));
            $row['tipo']='usato';

            $tarr2[$row['telaio']]=$row;
        }

        ////////////////////
        $c=0;

        foreach ($tarr as $k=>$t) {
            $c++;
            $t['count']=$c;
            $this->lista->add($t);
        }
        foreach ($tarr2 as $k=>$t) {
            $c++;
            $t['count']=$c;
            $this->lista->add($t);
        }

    }

    //echo json_encode($this->galileo->getLog('query'));
};

?>