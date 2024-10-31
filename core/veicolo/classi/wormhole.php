<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/core/wormhole/wormhole.php');

require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/concerto/concerto_veicoli.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/infinity/infinity_veicoli.php');

class veicoloWH extends nebulaWHole {

    protected $actualDms="";

    protected $log=array();

    function __construct($reparto,$galileo) {
        
        parent::__construct($reparto,$galileo);
    }

    function initGalileo($dms) {

        if ($this->actualDms==$dms) return;

        if ($dms=='concerto') {
            //inizializzazione galileo con gli oggetti ODL
            $obj=new galileoConcertoVeicoli();
            $nebulaDefault['veicoli']=array("maestro",$obj);

            $this->galileo->setFunzioniDefault($nebulaDefault);
        }

        elseif ($dms=='infinity') {
            //inizializzazione galileo con gli oggetti ODL
            $obj=new galileoInfinityVeicoli();
            $nebulaDefault['veicoli']=array("rocket",$obj);

            $this->galileo->setFunzioniDefault($nebulaDefault);
        }

        $this->actualDms=$dms;
    }

    function getLog() {
        return $this->log;
    }

    function getActualDms() {
        return $this->actualDms;
    }

    function linkerSearch($dms,$str) {

        $this->initGalileo($dms);

        //mappa forzata NON $this
        $map=array(
            "dms"=>$dms,
            "piattaforma"=>$this->getPiattaforma($dms),
            "result"=>false
        );

        $arr=array(
            "targa"=>$str,
            "telaio"=>$str,
            "anagra"=>$str,
            "operatore"=>"OR"
        );

        $this->galileo->executeGeneric('veicoli','getLinker',$arr,'');

        $map['result']=$this->galileo->getResult();

        //$this->log[]=$this->galileo->getLog('query');

        return $map;

    }

    function getTT($dms,$str) {

        $this->initGalileo($dms);

         //mappa forzata NON $this
         $map=array(
            "dms"=>$dms,
            "piattaforma"=>$this->getPiattaforma($dms),
            "result"=>false
        );

        $arr=array(
            "targa"=>$str,
            "telaio"=>$str,
            "operazione"=>"OR"
        );

        $this->galileo->executeGeneric('veicoli','ttSelect',$arr,'targa');

        $map['result']=$this->galileo->getResult();

        //$this->log[]=$this->galileo->getLog('query');

        return $map;

    }

}

?>