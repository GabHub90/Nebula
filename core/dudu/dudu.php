<?php

class nebulaDudu {

    protected $caller="";
    
    protected $info=array(
        "ID"=>0,
        "titolo"=>"TO-DO",
        "stato"=>"nuovo",
        "lista"=>array()
    );

    protected $css=array(
        "headH"=>"10%",
        "headFont"=>"1.2em",
        "bodyH"=>"90%",
        "bodyW"=>"90%",
        "lineH"=>"18px",
        "lineFont"=>"1.1em",
        "lineFontW"=>"bold",
    );

    protected $galileo;
    
    function __construct($caller,$galileo) {

        $this->caller=$caller;
        $this->galileo=$galileo;
    }

    function loadLista($id,$lista) {
        $this->info['ID']=$id;
        $this->info['lista']=$lista;

        foreach ($this->info['lista'] as $k=>$r) {
            $this->checkStato($r);
        }
    }

    function loadLink($app,$rif) {

        //caricamento oggetto da DB
        $this->galileo->clearQuery();
        $this->galileo->clearQueryOggetto('default','dudu');

        $this->galileo->executeGeneric('dudu','loadLink',array('app'=>$app,'rif'=>$rif),'');
        if ($this->galileo->getResult()) {
            $fid=$this->galileo->preFetch('dudu');
            while($row=$this->galileo->getFetch('dudu',$fid)) {
                $this->info['lista'][$row['riga']]=$row;
                $this->info['ID']=$row['ID'];

                $this->checkStato($row);
            }
        }
    }

    function checkStato($row) {

        if ($row['d_chiusura']!='') {
            if ($this->info['stato']=='nuovo') $this->info['stato']='chiuso';
        }
        elseif ($row['d_scadenza']!='') {
            if ($row['d_scadenza']<date('Ymd:H:i')) $this->info['stato']='scaduto';
        }
        else {
            if ($this->info['stato']!='scaduto') $this->info['stato']='aperto';
        }
    }

    static function duduInit() {

        echo '<link href="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/dudu/style.css" type="text/css" rel="stylesheet"/>';
        echo '<script type="text/javascript" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/dudu/dudu.js?v='.time().'" />';
    }
    
    function draw($prefix,$enabled) {

        echo '<div style="position:relative;width:100%;height:'.$this->css['headH'].';font-weight:bold;font-size:'.$this->css['headFont'].';">';
            echo $this->info['titolo'].' - '.$this->info['stato'];
        echo '</div>';

        $this->drawLista($prefix,$enabled);
    }

    function drawLista($prefix,$enabled) {

        echo '<div id="nebulaDudu_body_'.$prefix.'_'.$this->info['ID'].'" style="position:relative;width:100%;height:'.$this->css['bodyH'].';overflow:scroll;overflow-x:hidden;" >';         
        echo '</div>';

        echo '<script type="text/javascript">';
            echo 'window._nebulaDudu_'.$prefix.'_'.$this->info['ID'].'=new duduObj('.$this->info['ID'].',\''.$prefix.'\','.$enabled.',\''.base64_encode(json_encode($this->info['lista'])).'\',\''.base64_encode(json_encode($this->css)).'\',\''.$this->caller.'\');';
            echo 'window._nebulaDudu_'.$prefix.'_'.$this->info['ID'].'.draw();';
        echo '</script>';

    }
}

?>