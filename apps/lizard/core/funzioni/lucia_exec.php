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

    $this->lista=new excalibur('lucia','Nominativi Lucia:');

    $tempLista=array();

    //########################
    //inizializzazione excalibur
    $conv=array(
        "nome"=>"nome",
        "telefono"=>"telefono"
    );

    $mappa=array(
        "nome"=>array("tag"=>"nome"),
        "telefono"=>array("tag"=>"telefono"),
    );

    $this->lista->build($conv,$mappa);

    $a=array(
        'eso'=>array(
            'tag'=>'Esosphera'
        )
    );

    $this->lista->loadFunc($a);

    //########################

    $obj=new galileoInfinityAnagrafiche();
    $nebulaDefault['anagra']=array("rocket",$obj);
    $this->galileo->setFunzioniDefault($nebulaDefault);

    $this->galileo->executeGeneric('anagra','getLucia',array(),'');

    $temp=array();

    if ($this->galileo->getResult()) {

        $fid=$this->galileo->preFetch('anagra');

        while ($row=$this->galileo->getFetch('anagra',$fid)) {

            $tel=trim(str_replace('+39','',$row['num_riferimento']));

            $tel=preg_replace("/[^0-9]/","",$tel);

            $nome=strtoupper(trim(str_replace(',','',$row['rag_soc_1'])));

            if ($tel!="" && strlen($nome)>5) {

                $temp["".$tel]=$nome;
            }
        }

        $this->galileo->clearQuery();
        $this->galileo->clearQueryOggetto('default','anagra');

        foreach ($temp as $t=>$n) {

            $this->lista->add(array('telefono'=>$t,'nome'=>$n));
        }

    }

};

$this->closure['excaliburFunc']=function() {

    echo '<script type="text/javascript" >';

        echo 'window._excalibur_lucia.extra_eso=function() {';
            echo <<<JS

                var param={
                    "liz_exec":"lucia"
                };

                $.ajax({
                    "url": 'http://'+location.host+'/nebula/apps/lizard/core/funzioni/lucia_func.php',
                    "async": true,
                    "cache": false,
                    "data": {"param": param},
                    "type": "POST",
                    "success": function(ret) {

                        var result={
                            download: {
                                mimetype: 'text/txt',
                                filename: 'lucia_tracciato.csv',
                                data: btoa(ret)
                            }
                        }

                        //console.log(res);
                        window._excalibur_lucia.download(result);

                    }
                });
JS;
        echo '};';

    echo '</script>';

};

?>