<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/core/wormhole/wormhole.php');

include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/concerto/concerto_odl.php');
include_once($_SERVER['DOCUMENT_ROOT']."/nebula/galileo/infinity/infinity_odl.php");


class workshopWHole extends nebulaWHole {

    protected $actualDms="";
    protected $odlFunc;

    function __construct($reparto,$galileo) {

        parent::__construct($reparto,$galileo); 
    }

    function setOdlfunc($obj) {
        $this->odlFunc=$obj;
    }

    function setGalileo($dms) {

        if ($dms=='concerto') {

            $obj=new galileoConcertoODL();
            $nebulaDefault['odl']=array("maestro",$obj);

            $this->galileo->setFunzioniDefault($nebulaDefault);

            $this->actualDms=$dms;
        }

        elseif ($dms=='infinity') {

            $obj=new galileoInfinityODL();
            $nebulaDefault['odl']=array("rocket",$obj);

            $this->galileo->setFunzioniDefault($nebulaDefault);

            $this->actualDms=$dms;
        }

        else {
            $this->actualDms='';
        }

    }

    /*function getOdl($rif,$dms) {

        //non è necessario chiamare il metodi BUILD
        //in quanto sappiamo già quale dms è coinvolto

        if ($dms=='concerto') {

            $this->map[0]=array(
                "inizio"=>"201205",
                "fine"=>"210012",
                "dms"=>"concerto",
                "piattaforma"=>'maestro',
                "result"=>false
            );
    
            $obj=new galileoConcertoODL();
            $nebulaDefault['odl']=array("maestro",$obj);
    
            $this->galileo->setFunzioniDefault($nebulaDefault);
    
            //$tipo,$funzione,$args,$order
            $arg=array(
                "num_rif_movimento"=>$rif
            );
    
            $this->galileo->clearQuery();
            $this->galileo->clearQueryOggetto('default','odl'); 
    
            $this->galileo->executeGeneric('odl','getOdlLamentati',$arg,'');
            $this->map[0]['result']=$this->galileo->getResult();
        }

    }*/

    function getPrenotazioni($reparto) {

        foreach ($this->map as $k=>$m) {
            //inizio - fine - dms - piattaforma - result=false

            if ($m['dms']!=$this->actualDms) $this->setGalileo($m['dms']);

            $this->map[$k]['piattaforma']=$this->getPiattaforma($m['dms']);

            $arg=array(
                "inizio"=>$m['inizio'],
                "fine"=>$m['fine'],
                "officina"=>$this->odlFunc->getDmsRep($m['dms'],$reparto)
            );

            $this->galileo->clearQuery();
            $this->galileo->clearQueryOggetto('default','odl'); 

            $this->galileo->executeGeneric('odl','getPrenotazioni',$arg,'');
            $this->map[$k]['result']=$this->galileo->getResult();
        }

    }

}

?>