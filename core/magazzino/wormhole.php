<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/core/wormhole/wormhole.php');

require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/concerto/concerto_ricambi.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/infinity/infinity_ricambi.php');

class magazzinoWH extends nebulaWHole {

    protected $actualDms="";

    //sono in formato DB funzione IN
    protected $repMagDB=array(
        "infinity"=>array(
            "FCM"=>"'04','G4'",
            "VGM"=>"'01','G1'",
            "G1"=>"'G1'"
        ),
        "concerto"=>array(
            "FCP"=>"'05'",
            "PAM"=>"'04'",
            "POM"=>"'02'"
        )
    );

    function __construct($reparto,$galileo) {
        
        parent::__construct($reparto,$galileo);
    }

    function initGalileo($dms) {

        if ($this->actualDms==$dms) return;

        if ($dms=='infinity') {
            //inizializzazione galileo con gli oggetti ODL
            $obj=new galileoInfinityRicambi();
            $nebulaDefault['ricambi']=array("rocket",$obj);

            $this->galileo->setFunzioniDefault($nebulaDefault);
        }
        elseif ($dms=='concerto') {
            //inizializzazione galileo con gli oggetti ODL
            $obj=new galileoConcertoRicambi();
            $nebulaDefault['ricambi']=array("maestro",$obj);

            $this->galileo->setFunzioniDefault($nebulaDefault);
        }

        $this->actualDms=$dms;
    }

    function getActualDms() {
        return $this->actualDms;
    }

    function getDepDB($dms,$rep) {
        if (isset($this->repMagDB[$dms][$rep])) return $this->repMagDB[$dms][$rep];
        else return "";
    }

    /*function getDepositi() {
        $ret=array();
        foreach($this->map as $k=>$m) {
            foreach (explode(',',$this->repMagDB[$m['dms']][$this->reparto]) as $k1=>$dep) {
                $chiave=substr($m['dms'],0,1).$dep;
                if (!array_key_exists($chiave,$ret)) {
                    $ret[$chiave]=array(
                        "codice"=>$dep,
                        "dms"=>$m['dms']
                    );
                } 
            } 
        }

        return $ret;
    }*/

    function getOrdiniOfficinaPren() {

        foreach($this->map as $k=>$m) {

            if ($this->actualDms!=$m['dms']) $this->initGalileo($m['dms']);

            $this->galileo->clearQuery();
            $this->galileo->clearQueryOggetto('default','ricambi');

            $this->galileo->executeGeneric('ricambi','getOrdiniOfficinaPren',array(),'');

            $this->map[$k]['result']=$this->galileo->getResult();
            $this->map[$k]['piattaforma']=$this->getPiattaforma($m['dms']);
        }
    }

    function getOrdiniOfficinaComm() {

        foreach($this->map as $k=>$m) {

            if ($this->actualDms!=$m['dms']) $this->initGalileo($m['dms']);

            $this->galileo->clearQuery();
            $this->galileo->clearQueryOggetto('default','ricambi');

            $this->galileo->executeGeneric('ricambi','getOrdiniOfficinaComm',array(),'');

            $this->map[$k]['result']=$this->galileo->getResult();
            $this->map[$k]['piattaforma']=$this->getPiattaforma($m['dms']);
        }
    }

    function getOrdiniBanco($param) {

        foreach($this->map as $k=>$m) {

            if ($this->actualDms!=$m['dms']) $this->initGalileo($m['dms']);

            $this->galileo->clearQuery();
            $this->galileo->clearQueryOggetto('default','ricambi');

            $this->galileo->executeGeneric('ricambi','getOrdiniBanco',$param,'');

            $this->map[$k]['result']=$this->galileo->getResult();
            $this->map[$k]['piattaforma']=$this->getPiattaforma($m['dms']);
        }
    }

    function getListeVenditaBanco($param) {

        foreach($this->map as $k=>$m) {

            if ($this->actualDms!=$m['dms']) $this->initGalileo($m['dms']);

            //questo vale SOLO per INFINITY PESARO
            if ($m['dms']=='infinity') $param['lista']='L101';
            ////////////////////////////////////////////////////

            $this->galileo->clearQuery();
            $this->galileo->clearQueryOggetto('default','ricambi');

            $this->galileo->executeGeneric('ricambi','getListeVenditaBanco',$param,'');

            $this->map[$k]['result']=$this->galileo->getResult();
            $this->map[$k]['piattaforma']=$this->getPiattaforma($m['dms']);
        }

    }

    function getNoloc($param) {

        foreach($this->map as $k=>$m) {

            if ($this->actualDms!=$m['dms']) $this->initGalileo($m['dms']);

            $param['reparto']=$this->repMagDB[$m['dms']][$param['reparto']];

            $this->galileo->clearQuery();
            $this->galileo->clearQueryOggetto('default','ricambi');

            $this->galileo->executeGeneric('ricambi','getNolocat',$param,'');

            $this->map[$k]['result']=$this->galileo->getResult();
            $this->map[$k]['piattaforma']=$this->getPiattaforma($m['dms']);
        }
    
    }

    function getNegativi($param) {

        foreach($this->map as $k=>$m) {

            if ($this->actualDms!=$m['dms']) $this->initGalileo($m['dms']);

            $param['reparto']=$this->repMagDB[$m['dms']][$param['reparto']];

            $this->galileo->clearQuery();
            $this->galileo->clearQueryOggetto('default','ricambi');

            $this->galileo->executeGeneric('ricambi','getNegativi',$param,'');

            $this->map[$k]['result']=$this->galileo->getResult();
            $this->map[$k]['piattaforma']=$this->getPiattaforma($m['dms']);
        }
    
    }

    function getOrdiniGaranzia($param) {

        foreach($this->map as $k=>$m) {

            if ($this->actualDms!=$m['dms']) $this->initGalileo($m['dms']);

            $this->galileo->clearQuery();
            $this->galileo->clearQueryOggetto('default','ricambi');

            $this->galileo->executeGeneric('ricambi','getCarichiGaranzia',$param,'');

            $this->map[$k]['result']=$this->galileo->getResult();
            $this->map[$k]['piattaforma']=$this->getPiattaforma($m['dms']);
        }
    
    }

    function selectPneumaticiZT($param) {

        $reg="";

        $reg.=(isset($param['tipo']) && $param['tipo']!='')?$param['tipo']:'ZT.';

        $reg.=(isset($param['d']) && $param['d']!='')?$param['d'].'.':'...';

        $reg.='..';

        $reg.=(isset($param['r']) && $param['r']!='')?$param['r']:'.';

        $reg.='.';
    
        $reg.=(isset($param['marca']) && $param['marca']!='')?$param['marca']:'.';
        
        $reg.='...';

        foreach($this->map as $k=>$m) {

            if ($this->actualDms!=$m['dms']) $this->initGalileo($m['dms']);

            $this->galileo->clearQuery();
            $this->galileo->clearQueryOggetto('default','ricambi');

            //il flag GIAC serve per decidere se si vuole la giacenza nel risultato
            $a=array(
                'anno'=>date('Y'),
                'reg'=>$reg,
                'mag'=>"",
                "da"=>date('Ymd'),
                "a"=>date('Ymd')
            );

            //se magazzino è 'S' il risultato viene cercato solo nei magazzini del reparto
            //se magazzzino è 'T' il risultato viene cercato in tutti i magazzini del DMS

            if ($param['mag']=='S') $a['mag']=$this->repMagDB[$this->actualDms][$this->reparto];

            if ($param['mag']=='T') {
                $temp='';
                foreach ($this->repMagDB[$this->actualDms] as $k=>$t) {
                    $temp.=$t.',';
                }

                $a['mag']=substr($temp,0,-1);
            }

            $this->galileo->executeGeneric('ricambi','pneumaticiZT',$a,'');

            $this->map[$k]['result']=$this->galileo->getResult();
            $this->map[$k]['piattaforma']=$this->getPiattaforma($m['dms']);
        }
    }

    function getGiacenzaM($a,$m) {

        if ($this->actualDms!=$m['dms']) $this->initGalileo($m['dms']);

        $this->galileo->clearQuery();
        $this->galileo->clearQueryOggetto('default','ricambi');

        $this->galileo->executeGeneric('ricambi','getGiacenza_M',$a,'');

        $m['piattaforma']=$this->getPiattaforma($m['dms']);
        $m['result']=$this->galileo->getResult();

        return $m;
    }

}

?>