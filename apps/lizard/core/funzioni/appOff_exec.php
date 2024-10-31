<?php

$this->closure['checkParam']=function() {

    //echo json_encode($this->param);

    $defp=array(
        "liz_reparto",
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

    $this->lista=new excalibur('appOff','Appuntamenti di officina:');

    $tempLista=array();

    //########################
    //inizializzazione excalibur
    $conv=array(
        "cod_officina"=>"cod_officina",
        "cod_movimento"=>"cod_movimento",
        "rif"=>"rif",
        "testo_pren"=>"pren",
        "testo_trasporto"=>"trasp",
        "km"=>"km",
        "mat_targa"=>"targa",
        "mat_telaio"=>"telaio",
        "cod_veicolo"=>"modello",
        "des_veicolo"=>"des_veicolo",
        "testo_consegna"=>"consegna",
        "cod_anagra_util"=>"cod_util",
        "util_ragsoc"=>"util",
        "util_mail"=>"util_mail",
        "util_tel"=>"util_tel",
        "cod_anagra_intest"=>"cod_intest",
        "intest_ragsoc"=>"intest",
        "intest_mail"=>"intest_mail",
        "intest_tel"=>"intest_tel",
        "cod_venditore"=>"cod_venditore",
        "cod_finanziaria"=>"cod_finanziaria",
        "num_rate_totali"=>"num_rate_totali",
        "val_rata"=>"val_rata",
        "val_maxi_rata"=>"val_maxi_rata",
        "cod_prodotto_finanziario"=>"cod_prodotto_finanziario",
        "val_riscatto_leasing"=>"val_riscatto_leasing",
        "d_ultima_rata"=>"d_ultima_rata",
        "testo"=>"testo"
    );

    $mappa=array(
        "rif"=>array("tag"=>"rif"),
        "cod_officina"=>array("tag"=>"cod_officina"),
        "cod_movimento"=>array("tag"=>"cod_movimento"),
        "pren"=>array("tag"=>"prenotazione"),
        "trasp"=>array("tag"=>"trasporto"),
        "km"=>array("tag"=>"km"),
        "targa"=>array("tag"=>"targa"),
        "telaio"=>array("tag"=>"telaio"),
        "des_veicolo"=>array("tag"=>"veicolo"),
        "des_veicolo"=>array("tag"=>"veicolo"),
        "consegna"=>array("tag"=>"consegna"),
        "util"=>array("tag"=>"util"),
        "util_mail"=>array("tag"=>"util_mail"),
        "util_tel"=>array("tag"=>"util_tel"),
        "cod_intest"=>array("tag"=>"cod_intest"),
        "intest"=>array("tag"=>"intest"),
        "intest_mail"=>array("tag"=>"intest_mail"),
        "intest_tel"=>array("tag"=>"intest_tel"),
        "cod_venditore"=>array("tag"=>"cod_venditore"),
        "cod_finanziaria"=>array("tag"=>"cod_finanziaria"),
        "num_rate_totali"=>array("tag"=>"rate"),
        "val_rata"=>array("tag"=>"val_rata"),
        "val_maxi_rata"=>array("tag"=>"val_maxi_rata"),
        "cod_prodotto_finanziario"=>array("tag"=>"prodotto_finanziario"),
        "val_riscatto_leasing"=>array("tag"=>"riscatto"),
        "d_ultima_rata"=>array("tag"=>"ultima_rata"),
        "testo"=>array("tag"=>"lamentato")
    );

    $this->lista->build($conv,$mappa);

    //########################

    $wh=new avalonWHole($this->param['liz_reparto'],$this->galileo);
    $odlFunc=new nebulaOdlFunc($this->galileo);

    $a=array(
        "inizio"=>mainFunc::gab_input_to_db($this->param['liz_da']),
        "fine"=>mainFunc::gab_input_to_db($this->param['liz_a']),
    );

    $wh->build($a);

    $wh->getPrenotazioniLizard();

    foreach($wh->exportMap() as $k=>$m) {

        $fid=$this->galileo->preFetchPiattaforma($m['piattaforma'],$m['result']);

        $rif=0;
        $el=array();

        while ($row=$this->galileo->getFetchPiattaforma($m['piattaforma'],$fid)) {

            if ($m['dms']=='concerto') {
                if ($row['num_commessa']!=0) continue;
            }

            /* {
                "dat_inserimento":"20220211:15:34",
                "cod_officina":"PA",
                "cod_movimento":"OOP",
                "acarico":"GARA",
                "cg":"",
                "rif":780,
                "ind_preventivo":"N",
                "ind_chiuso":"N",
                "lam":1,
                "des_riga":"az 69br cr 02 8r",
                "d_pren":"20220328:08:30",
                "d_ricon":"20220328:12:00",
                "ore":"1.0000000",
                "subrep":"PUMECC",
                "d_inc":"xxxxxxxx:xx:xx",
                "id_veicolo":5023062,
                "cod_anagra_util":0,
                "cod_anagra_intest":68437,
                "cod_accettatore":"e.diluca",
                "id_inconveniente_infinity":1088,
                "rif_commessa":4529,
                "cod_stato_commessa":"AP",
                "cod_tipo_trasporto":"ASP",
                "ore_isla":"1.0000000",
                "km": 229870,
                "mat_targa":"EF543GG",
                "mat_telaio":"WAUZZZ8R9BA050961",
                "cod_veicolo":"8RB0MY",
                "des_veicolo":"Q5 2.0 TDI Q.S-TR.",
                "mat_motore":"",
                "util_ragsoc":"",
                "intest_ragsoc":"COSTRUZIONI TENOX DI TENTI ELISEO & C. S.A.S.",
                "util_mail": "giovannibolognini69@gmail.com",
                "intest_mail": "ordini@vwfs.com",
                "util_tel": "335306713",
                "intest_tel": "1-0233027502"
            }*/

            if ($row['rif']!=$rif) {

                if ($rif!=0) {
                    $el['testo']=utf8_encode(substr($el['testo'],0,-3));
                    $tempLista[]=$el;
                    //$this->lista->add($el);
                }

                ///////////////////////
                $row['testo_pren']=mainFunc::gab_todata(substr($row['d_pren'],0,8)).' '.substr($row['d_pren'],9,5);
                $row['testo_consegna']=mainFunc::gab_todata(substr($row['d_consegna'],0,8));
                $trasporto=$odlFunc->getTrasporto($row['cod_tipo_trasporto'],$m['dms']);
                $row['testo_trasporto']=($trasporto)?$trasporto['testo']:"";
                //////////////////////

                $el=$row;
                $el['testo']="";
                $rif=$row['rif'];
            }

            $el['testo']=$row['des_riga'].' - ';
        }

        if (count($el)>0) {
            $el['testo']=utf8_encode(substr($el['testo'],0,-3));
            $tempLista[]=$el;
            //$this->lista->add($el);
        }

    }

    foreach ($tempLista as $el) {

        //prende il venditore ed i dati finanziari da CONCERTO
        $el['cod_venditore']="";
        $el['cod_finanziaria']="";
        $el['num_rate_totali']="";
        $el['val_rata']="";
        $el['val_maxi_rata']="";
        $el['cod_prodotto_finanziario']="";
        $el['val_riscatto_leasing']="";
        $el['d_ultima_rata']="";

        $m=$wh->getDatiFinanziariConcerto($el['mat_telaio']);

        if ($m['result']) {

            $fid=$this->galileo->preFetchPiattaforma($m['piattaforma'],$m['result']);

            while ($row=$this->galileo->getFetchPiattaforma($m['piattaforma'],$fid)) {
                foreach ($row as $k=>$r) {
                    if (array_key_exists($k,$el)) $el[$k]=$r;
                }

                $el['val_rata']=number_format($el['val_rata'],0,'','.');
                $el['val_maxi_rata']=number_format($el['val_maxi_rata'],0,'','.');
                $el['val_riscatto_leasing']=number_format($el['val_riscatto_leasing'],0,'','.');
                if ($el['d_ultima_rata']!="") $el['d_ultima_rata']=mainFunc::gab_todata($el['d_ultima_rata']);
            }
        }

        $this->lista->add($el);
    }

};

?>