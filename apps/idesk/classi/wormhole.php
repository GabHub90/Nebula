<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/apps/avalon/classi/wormhole.php');

include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/concerto/concerto_odl.php');
include_once($_SERVER['DOCUMENT_ROOT']."/nebula/galileo/infinity/infinity_odl.php");


class ideskWHole extends avalonWHole {

    function __construct($reparto,$galileo) {

        parent::__construct($reparto,$galileo); 
    }

    function getInofficina($inizio,$fine,$officina,$rc,$cliente) {
        //recupera tutti gli ordini di lavoro relativi ad una specifica officina e/o RC

        //la mappa è solo una perché è stata creata in un giorno specifico
        foreach ($this->map as $k=>$m) {

            if ($m['dms']!=$this->actualDms) $this->setGalileo($m['dms']);

            /*$a=array(
                'inizio'=>$m['inizioDms'],
                'fine'=>$m['fineDms'],
                'officina'=>$officina,
            );*/

            $a=array(
                'inizio'=>$m['inizioDms'],
                'fine'=>$m['fineDms'],
                'officina'=>$officina,
                'rc'=>$rc,
                'tipo'=>'aperti'
            );

            /*if ($cliente=='apprip') {
                $a['timeless']=array(
                    'inizio'=>$m['inizioDms'],
                    'fine'=>$m['fineDms'],
                    'officina'=>$officina,
                );
            }*/

            if ($cliente=='apprip') {
                $a['timeless']=array(
                    'inizio'=>$m['inizioDms'],
                    'fine'=>$m['fineDms'],
                    'officina'=>$officina,
                    'rc'=>$rc,
                    'tipo'=>'aperti'
                );
            }

            $this->galileo->clearQuery();
            $this->galileo->clearQueryOggetto('default','odl');

            $this->galileo->executeGeneric('odl','getCliLamentati',$a,'');
            $this->map[$k]['result']=$this->galileo->getResult();
            $this->map[$k]['piattaforma']=$this->getPiattaforma($m['dms']);
        }
    }

    function getTimeless($inizio,$fine,$officina) {

        foreach ($this->map as $k=>$m) {

            if ($m['dms']!=$this->actualDms) $this->setGalileo($m['dms']);

            $a=array(
                "timeless"=>array(
                    'inizio'=>($inizio>$m['inizio'])?$inizio:$m['inizio'],
                    'fine'=>($fine>$m['fine'])?$m['fine']:$fine,
                    'officina'=>$officina
                )
            );

            //die(json_Encode($a));

            $this->galileo->clearQuery();
            $this->galileo->clearQueryOggetto('default','odl');

            $this->galileo->executeGeneric('odl','getCliLamentati',$a,'');
            $this->map[$k]['result']=$this->galileo->getResult();
            $this->map[$k]['piattaforma']=$this->getPiattaforma($m['dms']);
        }
    }

    function getTimelessSearch($inizio,$fine,$officina,$txt) {

        foreach ($this->map as $k=>$m) {

            if ($m['dms']!=$this->actualDms) $this->setGalileo($m['dms']);

            $a=array(
                "timeless"=>array(
                    'inizio'=>($inizio>$m['inizio'])?$inizio:$m['inizio'],
                    'fine'=>($fine>$m['fine'])?$m['fine']:$fine,
                    'officina'=>$officina,
                    'search'=>$txt
                )
            );

            $this->galileo->clearQuery();
            $this->galileo->clearQueryOggetto('default','odl');

            $this->galileo->executeGeneric('odl','getCliLamentati',$a,'');
            $this->map[$k]['result']=$this->galileo->getResult();
            $this->map[$k]['piattaforma']=$this->getPiattaforma($m['dms']);
        }
    }

    function getIdeskSearch($officina,$txt,$cliente) {

        foreach ($this->map as $k=>$m) {

            if ($m['dms']!=$this->actualDms) $this->setGalileo($m['dms']);

            if ($cliente=='apprip') {

                $a=array(
                    "timeless"=>array(
                        'inizio'=>$m['inizioDms'],
                        'fine'=>$m['fineDms'],
                        'officina'=>$officina,
                        'tipo'=>'aperti',
                        'search'=>$txt
                    )
                );
            }
            else {
                $a=array(
                    'inizio'=>$m['inizioDms'],
                    'fine'=>$m['fineDms'],
                    'officina'=>$officina,
                    'tipo'=>'aperti',
                    'search'=>$txt
                );
            }

            $this->galileo->clearQuery();
            $this->galileo->clearQueryOggetto('default','odl');

            $this->galileo->executeGeneric('odl','getCliLamentati',$a,'');
            $this->map[$k]['result']=$this->galileo->getResult();
            $this->map[$k]['piattaforma']=$this->getPiattaforma($m['dms']);
        }
    }

    function getContrattoInfinity($telaio) {

        if ($this->actualDms!='infinity') $this->setGalileo('infinity');

        $map=array(
            "dms"=>"infinity",
            "piattaforma"=>$this->getPiattaforma('infinity'),
            "result"=>false
        );

        $this->galileo->clearQuery();
        $this->galileo->clearQueryOggetto('default','odl'); 

        $this->galileo->executeGeneric('odl','getContrattoInfinity',array('telaio'=>$telaio),'');
        $map['result']=$this->galileo->getResult();

        return $map;

    }

    function getPratica() {

    }
    
}

?>