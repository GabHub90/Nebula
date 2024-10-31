<?php

$this->closure['checkParam']=function() {

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

    $this->lista=new excalibur('utif','Scarichi Utif:');

    //########################
    //inizializzazione excalibur
    $conv=array(
        "d_fatt"=>"d_fatt",
        "num_fatt"=>"num_fatt",
        "cod_movimento"=>"cod_movimento",
        "nomen_intra"=>"nomen_intra",
        "peso"=>"peso",
        "precodice"=>"precodice",
        "articolo"=>"articolo",
        "descr_articolo"=>"descr_articolo",
        "quantita"=>"quantita",
        "cod_anagra_intest"=>"cod_anagra",
        "ragsoc_intest"=>"ragsoc",
        "pivacf"=>"pivacf"
    );

    $mappa=array(
        "d_fatt"=>array("tag"=>"Data Fattura","tipo"=>"data"),
        "num_fatt"=>array("tag"=>"Num Fattura"),
        "cod_movimento"=>array("tag"=>"Movim."),
        "nomen_intra"=>array("tag"=>"codice intra"),
        "peso"=>array("tag"=>"peso"),
        "precodice"=>array("tag"=>"precodice","css"=>"text-align:center;"),
        "articolo"=>array("tag"=>"articolo"),
        "descr_articolo"=>array("tag"=>"descrizione"),
        "quantita"=>array("tag"=>"Q.tà"),
        "cod_anagra"=>array("tag"=>"Cod Anagra"),
        "ragsoc"=>array("tag"=>"Ragione Sociale"),
        "pivacf"=>array("tag"=>"P.Iva / C.Fisc")
    );

    $this->lista->build($conv,$mappa);

    $a=array(
        'taweb'=>array(
            'tag'=>'TaWeb'
        )
    );

    $this->lista->loadFunc($a);

    //////////////////////////////////////////////////////////////////////////////

    $nebulaDefault=array();
    $obj=new galileoInfinityRicambi();
    $nebulaDefault['ricambi']=array("rocket",$obj);
    $this->galileo->setFunzioniDefault($nebulaDefault);

    $arr=array(
        "da"=>mainFunc::gab_input_to_db($this->param['liz_da']),
        "a"=>mainFunc::gab_input_to_db($this->param['liz_a'])
    );

    $this->galileo->executeGeneric('ricambi','getScarichiUtif',$arr,"");

    if ($this->galileo->getResult()) {

        $fid=$this->galileo->preFetch('ricambi');

        while ($row=$this->galileo->getFetch('ricambi',$fid)) {

            if ($row['cod_movimento']=='') continue;

            $row['peso']=number_format($row['peso'],3,'.','');
            $row['quantita']=number_format($row['quantita'],3,'.','');

            $this->lista->add($row);
        }

    }
};

$this->closure['excaliburFunc']=function() {

    echo '<script type="text/javascript" >';

        echo 'window._excalibur_utif.extra_taweb=function() {';
            echo <<<JS

                this.head=false;
                this.export=false;
                this.getLines('array');
                //console.log(JSON.stringify(this.export));

                var res="";
                var spc=" ";
                var zero="0";

                for (var x in this.export) {

                    res+="IT00PSB00022P"+window._nebulaMain.phpDate("YmdHis")+"00007SRECORDB ";

                    //RECORD - 1° parte (campi 1-6)
			        res+="I"+this.export[x].d_fatt;
			        res+=this.export[x].cod_movimento;
                    res+=spc.repeat(20-this.export[x].cod_movimento.length);
			        res+="I";
                    res+=spc.repeat(50);
                    res+="F";

                    //RECORD - 2° parte (campi 7-13)
                    res+=spc.repeat(10);
                    res+=this.export[x].nomen_intra;
                    res+=spc.repeat(30-this.export[x].nomen_intra.length);
                    res+='A';
                    res+=spc.repeat(131);

                    //RECORD - 3° parte (campi 14-22)
                    var litri=Math.floor(this.export[x].quantita*1000);
                    var kg=""+Math.ceil(litri*this.export[x].peso);

                    res+=zero.repeat(14-kg.length)+kg;
                    litri=""+litri;
                    res+=zero.repeat(14-litri.length)+litri;

                    res+=zero.repeat(5)+'S'+zero.repeat(14)+spc.repeat(71);

                    //RECORD - 4° parte (campi 23-30)
                    var tmp=""+this.export[x].num_fatt;
                    res+=tmp+spc.repeat(21-tmp.length)+this.export[x].d_fatt;

                    tmp=""+(parseInt(x)+1);
                    res+=zero.repeat(9-tmp.length)+tmp;
                    //?????????la nazionalità è impostata a IT perché l'estrazione dei dati esclude le linee differenti????????
                    res+=spc.repeat(21)+'IT'+spc.repeat(8)+'A'+spc.repeat(50);

                    tmp="";
                    if (this.export[x].cod_movimento!='OOA') {
                        tmp=this.export[x].pivacf;
                    }
                    
                    res+=tmp+spc.repeat(30-tmp.length);
                    
                    res+="\\n";
                }

                var result={
                    download: {
                        mimetype: 'text/txt',
                        filename: this.tag+'_tracciato.txt',
                        data: btoa(res)
                    }
                }
                //console.log(res);
                this.download(result);
JS;
        echo '};';

    echo '</script>';

};

?>