<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/core/wormhole/wormhole.php');

include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/concerto/concerto_odl.php');
include_once($_SERVER['DOCUMENT_ROOT']."/nebula/galileo/infinity/infinity_odl.php");

include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_alert.php');


class avalonWHole extends nebulaWHole {

    protected $actualDms="";

    function __construct($reparto,$galileo) {

        parent::__construct($reparto,$galileo); 
    }

    function setGalileo($dms) {

        if ($dms=='concerto') {

            $obj=new galileoConcertoODL();
            $nebulaDefault['odl']=array("maestro",$obj);

            $obj=new galileoAlert();
            $nebulaDefault['alert']=array("gab500",$obj);

            $this->galileo->setFunzioniDefault($nebulaDefault);

            $this->actualDms=$dms;
        }

        elseif ($dms=='infinity') {

            $obj=new galileoInfinityODL();
            $nebulaDefault['odl']=array("rocket",$obj);

            $obj=new galileoAlert();
            $nebulaDefault['alert']=array("gab500",$obj);

            $this->galileo->setFunzioniDefault($nebulaDefault);

            $this->actualDms=$dms;
        }

        else {
            $this->actualDms='';
        }

    }

    function getOccu($inizio,$fine) {

        $a=array(
            "inizio"=>$inizio,
            "fine"=>$fine
        );

        $this->build($a);

        foreach ($this->map as $k=>$m) {
            //inizio - fine - dms - piattaforma - result=false

            if ($m['dms']!=$this->actualDms) $this->setGalileo($m['dms']);

            $this->map[$k]['piattaforma']=$this->getPiattaforma($m['dms']);

            //$tipo,$funzione,$args,$order
            $arg=array(
                "inizio"=>$m['inizio'],
                "fine"=>$m['fine']
            );

            $this->galileo->clearQuery();
            $this->galileo->clearQueryOggetto('default','odl'); 

            if( $this->galileo->executeGeneric('odl','getOccupazione',$arg,'') ) {
                $this->map[$k]['result']=$this->galileo->getResult();
            }

        }
    }

    function getPrenotazioni($inizio,$fine,$officina) {

        $a=array(
            "inizio"=>$inizio,
            "fine"=>$fine
        );

        $this->build($a);

        foreach ($this->map as $k=>$m) {
            //inizio - fine - dms - piattaforma - result=false

            if ($m['dms']!=$this->actualDms) $this->setGalileo($m['dms']);

            $this->map[$k]['piattaforma']=$this->getPiattaforma($m['dms']);

            $arg=array(
                "inizio"=>$m['inizio'],
                "fine"=>$m['fine'],
                "officina"=>$officina
            );

            $this->galileo->clearQuery();
            $this->galileo->clearQueryOggetto('default','odl'); 

            $this->galileo->executeGeneric('odl','getPrenotazioni',$arg,'');
            $this->map[$k]['result']=$this->galileo->getResult();
        }

    }

    function getFatture($inizio,$fine,$officina) {

        $a=array(
            "inizio"=>$inizio,
            "fine"=>$fine
        );

        $this->build($a);

        foreach ($this->map as $k=>$m) {
            //inizio - fine - dms - piattaforma - result=false

            if ($m['dms']!=$this->actualDms) $this->setGalileo($m['dms']);

            $this->map[$k]['piattaforma']=$this->getPiattaforma($m['dms']);

            $arg=array(
                "inizio"=>$m['inizio'],
                "fine"=>$m['fine'],
                "officina"=>$officina
            );

            $this->galileo->clearQuery();
            $this->galileo->clearQueryOggetto('default','odl'); 

            $this->galileo->executeGeneric('odl','Fatturato_S',$arg,'');
            $this->map[$k]['result']=$this->galileo->getResult();
        }
    }

    function searchPrenotazioni($inizio,$fine,$officina,$search) {

        $a=array(
            "inizio"=>$inizio,
            "fine"=>$fine
        );

        $this->build($a);

        foreach ($this->map as $k=>$m) {
            //inizio - fine - dms - piattaforma - result=false

            if ($m['dms']!=$this->actualDms) $this->setGalileo($m['dms']);

            $this->map[$k]['piattaforma']=$this->getPiattaforma($m['dms']);

            $arg=array(
                "inizio"=>$m['inizio'],
                "fine"=>$m['fine'],
                "officina"=>$officina,
                "search"=>$search
            );

            $this->galileo->clearQuery();
            $this->galileo->clearQueryOggetto('default','odl'); 

            $this->galileo->executeGeneric('odl','getPrenotazioni',$arg,'');
            $this->map[$k]['result']=$this->galileo->getResult();
        }

    }

    function getPrenotazioniLizard() {

        $rep=array();

        $this->galileo->clearQuery();
        $this->galileo->clearQueryOggetto('base','reparti');

        $this->galileo->getReparto($this->reparto);

        $result=$this->galileo->getResult();

        if ($result) {

            $fid=$this->galileo->preFetchBase('reparti');

            while ($row=$this->galileo->getFetchBase('reparti',$fid)) {
                $rep=$row;
            }

        }
        else return;

        foreach ($this->map as $k=>$m) {

            if ($m['dms']!=$this->actualDms) $this->setGalileo($m['dms']);

            $this->map[$k]['piattaforma']=$this->getPiattaforma($m['dms']);

            $arg=array(
                "inizio"=>$m['inizio'],
                "fine"=>$m['fine'],
                "officina"=>$rep[$m['dms']]
            );

            $this->galileo->clearQuery();
            $this->galileo->clearQueryOggetto('default','odl'); 

            $this->galileo->executeGeneric('odl','getPrenotazioni',$arg,'');
            $this->map[$k]['result']=$this->galileo->getResult();
        }    

    }

    function getDatiFinanziariConcerto($telaio) {

        if ($this->actualDms!='concerto') $this->setGalileo('concerto');

        $map=array(
            "dms"=>"concerto",
            "piattaforma"=>$this->getPiattaforma('concerto'),
            "result"=>false
        );

        $this->galileo->clearQuery();
        $this->galileo->clearQueryOggetto('default','odl'); 

        $this->galileo->executeGeneric('odl','getDatiFinanziariConcerto',array('telaio'=>$telaio),'');
        $map['result']=$this->galileo->getResult();

        return $map;
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

    function setChimeApp($a) {

        if ($this->actualDms!=$a['dms']) $this->setGalileo($a['dms']);

        $this->galileo->clearQuery();
        $this->galileo->clearQueryOggetto('default','alert'); 

        $arr=array(
            "pratica"=>$a['pratica'],
            "dms"=>$a['dms'],
            "rif"=>$a['rif'],
            "pren"=>$a['pren'],
            "lam"=>'',
            "stato"=>isset($a['stato'])?$a['stato']:'XX',
            "dataora"=>date('Ymd:H:i'),
            "utente"=>$a['utente'],
            "chime_app"=>json_encode($a['chime'])
        );
        
        $wclause="pratica='".$a['pratica']."' AND dms='".$a['dms']."' AND rif='".$a['rif']."' AND isnull(lam,'')=''";
        
        $this->galileo->executeUpsert('alert','AVALON_stato_lam',$arr,$wclause);
        
        //DOVREBBE ESSERCI SOLO UN RECORD CHE FA RIFERIMENTO ALLA COMMESSA DI APPUNTAMENTO SENZA SPECIFICA DEL LAMENTATO
    }

    /*function getOdl($rif) {
        //non è necessario chiamare il metodi BUILD
        //in quanto sappiamo già quale dms è coinvolto

        $this->map=array();

        if ($rif['dms']!=$this->actualDms) $this->setGalileo($rif['dms']);

        if ($rif['dms']=='concerto') {

            $this->map[0]=array(
                "inizio"=>"201205",
                "fine"=>"210012",
                "dms"=>"concerto",
                "piattaforma"=>$this->getPiattaforma('concerto'),
                "result"=>false
            );
    
            //$tipo,$funzione,$args,$order
            $arg=array(
                "num_rif_movimento"=>$rif['rif'],
                "lista"=>'cli'
            );
        }
    
        $this->galileo->clearQuery();
        $this->galileo->clearQueryOggetto('default','odl'); 

        $this->galileo->executeGeneric('odl','getOdlLamentati',$arg,'');
        $this->map[0]['result']=$this->galileo->getResult();
    }*/

}

?>