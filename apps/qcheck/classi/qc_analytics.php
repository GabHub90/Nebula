<?php

class qcAnalytics {

    protected $reparto="";
    protected $controllo="";

    protected $config=array(
        "data_i"=>"",
        "data_f"=>"",
        "stato"=>array(),
        "oggetto"=>"",
        "esecutore"=>"",
        "operatore"=>"",
        "modulo"=>"",
        "variante"=>"",
        "chiave"=>""
    );

    protected $galileo;

    function __construct($reparto,$controllo,$galileo) {

        $this->reparto=$reparto;
        $this->controllo=$controllo;

        $this->galileo=$galileo;

        $this->setDefault();
           
    }

    function setDefault() {

        //configurazione di base
        $this->config['data_i']=date('Ym01');
        $this->config['data_f']=date('Ymt');
        $this->config['stato']=array('chiuso');
        $this->config['oggetto']='controlli';

    }

    function loadConfig($a) {
        foreach ($this->config as $k=>$c) {
            if ( array_key_exists($k,$a) ) {
                $this->config[$k]=$a[$k];
            }
        }
    }

    function getLines() {
        
        switch($this->config['oggetto']) {

            case 'controlli':
                return $this->getControlli();
            break;
            case 'storico':
                return $this->getStorico();
            break;

        }
    }

    function getControlli() {

        $ret=array();

        $args=array(
            "reparto"=>$this->reparto,
            "controllo"=>$this->controllo,
            "data_i"=>$this->config['data_i'],
            "data_f"=>$this->config['data_f'],
            "stato"=>$this->config['stato']
        );

        $this->galileo->executeGeneric('qcheck','getStoricoControlli',$args,"");

        $fetID=$this->galileo->preFetch('qcheck');

        while ($row=$this->galileo->getFetch('qcheck',$fetID)) {
            $ret["".$row['ID_controllo']][$row['modulo']]=$row;
        }

        return $ret;
    }

    function getStorico() {

        $ret=array();

        $args=array(
            "reparto"=>$this->reparto,
            "controllo"=>$this->controllo,
            "data_i"=>$this->config['data_i'],
            "data_f"=>$this->config['data_f'],
            "esecutore"=>$this->config['esecutore'],
            "operatore"=>$this->config['operatore'],
            "modulo"=>$this->config['modulo'],
            "variante"=>$this->config['variante'],
            "chiave"=>$this->config['chiave'] 
        );

        $this->galileo->executeGeneric('qcheck','getStorico',$args,"");

        //echo json_encode($this->galileo->getLog('query'));

        $fetID=$this->galileo->preFetch('qcheck');

        while ($row=$this->galileo->getFetch('qcheck',$fetID)) {
            $ret[$row['ID_controllo']][$row['modulo']]=$row;

            if ($ret[$row['ID_controllo']][$row['modulo']]['varianti']!="") {
                $tv=$ret[$row['ID_controllo']][$row['modulo']]['varianti'];
                $ret[$row['ID_controllo']][$row['modulo']]['varianti']=json_decode($tv,true);
            }
            else $ret[$row['ID_controllo']][$row['modulo']]['varianti']=array();
        }

        return $ret;

    }

}

?>