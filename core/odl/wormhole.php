<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/core/wormhole/wormhole.php');

require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/concerto/concerto_odl.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/infinity/infinity_odl.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_odl.php');

class odielleWH extends nebulaWHole {

    protected $actualDms="";

    protected $OTeventi=array();

    function __construct($reparto,$galileo) {
        
        parent::__construct($reparto,$galileo);
    }

    function initGalileo($dms) {

        if ($this->actualDms==$dms) return;

        if ($dms=='nebula') {

            //in questo momento ci affidiamo alle informazioni di TEST

            $obj=new galileoODL();
            $nebulaDefault['odl']=array("gab500",$obj);

            $this->galileo->setFunzioniDefault($nebulaDefault);
        }

        elseif ($dms=='concerto') {
            //inizializzazione galileo con gli oggetti ODL
            $obj=new galileoConcertoODL();
            $nebulaDefault['odl']=array("maestro",$obj);

            $this->galileo->setFunzioniDefault($nebulaDefault);
        }

        elseif ($dms=='infinity') {
            //inizializzazione galileo con gli oggetti ODL
            $obj=new galileoInfinityODL();
            $nebulaDefault['odl']=array("rocket",$obj);

            $this->galileo->setFunzioniDefault($nebulaDefault);
        }

        $this->actualDms=$dms;
    }

    function getActualDms() {
        return $this->actualDms;
    } 

    function getOdl($rif,$lista) {
        //lista [cli,pre] serve per infinity

        foreach ($this->map as $k=>$m) {

            if ($m['dms']!=$this->actualDms) $this->initGalileo($m['dms']);

            $this->galileo->clearQuery();
            $this->galileo->clearQueryOggetto('default','odl');

            //esecuzione della funzione di GALILEO
            $this->galileo->executeGeneric('odl','getOdlHead',array('rif'=>$rif,'lista'=>$lista),'');
            ////////////////////////////////////////////////

            $this->map[$k]['result']=$this->galileo->getResult();
        }

    }

    function getBodyLamentati($rif,$lista) {

        foreach ($this->map as $k=>$m) {

            if ($m['dms']!=$this->actualDms) $this->initGalileo($m['dms']);

            $this->galileo->clearQuery();
            $this->galileo->clearQueryOggetto('default','odl');

            //esecuzione della funzione di GALILEO
            $this->galileo->executeGeneric('odl','getOdlLamentati',array('num_rif_movimento'=>$rif,'tipo'=>'tutti','lista'=>$lista),'');
            ////////////////////////////////////////////////

            $this->map[$k]['result']=$this->galileo->getResult();
        }

    }

    function getLamMovements($rif,$lista) {

        //lista [cli,pre] serve per infinity

        $ids=array();

        foreach ($this->map as $k=>$m) {

            if ($m['dms']!=$this->actualDms) $this->initGalileo($m['dms']);

                $this->galileo->clearQuery();
                $this->galileo->clearQueryOggetto('default','odl');
                $this->galileo->executeGeneric('odl','getLamMovements',array('rif'=>$rif,'lista'=>$lista),'');

                $ids[]=array(
                    "piattaforma"=>$this->piattaforma[$m['dms']],
                    "id"=>$this->galileo->preFetchPiattaforma($this->piattaforma[$m['dms']],$this->galileo->getResult())
                );
        }

        return $ids;

    }

    function insertExtra($a) {

        $a['dms']=substr($this->actualDms,0,1);

        return $this->galileo->executeInsert('odl','AVALON_lamextra',$a);

    }

    ////////////////////////////////////////////////////////////////////////

    function getEventi() {
        return  $this->OTeventi;
    }

    function OTaddEvento($row) {

        $this->OTeventi[$row['tipo']][$row['codice']]=$row;
    }

    function OTcheckEvento($row) {

        $row['flag_qta']=false;

        if (isset($row['cod_movimento']) && $row['cod_movimento']=='OOPN') {
            $row['evento']=false;
            return $row;
        }

        $arr=array(
            "riferimento"=>"",
            "oggetto"=>"",
            "chk_std"=>0,
            "chk_actual"=>'OK',
        );

        $str="";

        if ($row['t_riga']=='M') $str=$row['operazione'];
        elseif ($row['t_riga']=='V') $str=$row['varie'];
        elseif ($row['t_riga']=='R') $str=$row['tipo'].'_'.$row['articolo'];

        if (isset($this->OTeventi[$row['t_riga']]) && array_key_exists($str,$this->OTeventi[$row['t_riga']])) {

            if ($row['qta']>=$this->OTeventi[$row['t_riga']][$str]['min_qta']) {
                $arr['riferimento']=$str;
                $arr['oggetto']=$this->OTeventi[$row['t_riga']][$str]['oggetto'];
                $arr['chk_std']=$this->OTeventi[$row['t_riga']][$str]['chk'];
            }
            else $row['flag_qta']=$this->OTeventi[$row['t_riga']][$str]['oggetto'];
        }

        if ($arr['oggetto']!="") {
            $row['evento']=$arr;
        }
        else $row['evento']=false;

        //$row['prova']='xvsvzvdc';

        return $row;

    }

    function getStoricoObjChk($dms,$rif) {

        $res=array();

        $this->initGalileo('nebula');

        $this->galileo->clearQuery();
        $this->galileo->clearQueryOggetto('default','odl');
        $this->galileo->executeSelect('odl','OT2_eventi_chk',"dms='".$dms."' AND odl='".$rif."'",'');

        $result=$this->galileo->getResult();

        if ($result) {
            $fid=$this->galileo->preFetch('odl');

            while ($row=$this->galileo->getFetch('odl',$fid)) {
                $res[$row['codice']]=$row['new'];
            }
        }
        

        return $res;
    }

    function puntiFedelta($dms,$cliente,$d) {
        //ID CLIENTE
        //DATA maggiore di cui prendere i documenti

        //per il momento vale solo per INFINITY
        //if ($dms!='infinity') return 0;

        if ($this->actualDms!=$dms) $this->initGalileo($dms);

        $this->galileo->clearQuery();
        $this->galileo->clearQueryOggetto('default','odl');

        $this->galileo->executeGeneric('odl','totFattCliente',array('cliente'=>$cliente,'d'=>$d),'');

        $ret=0;

        if ($this->galileo->getResult()) {

            $fid=$this->galileo->preFetch('odl');

            while ($row=$this->galileo->getFetch('odl',$fid)) {
                $ret=(int) (($row['tot']-$row['nac'])*0.015);
            }
        }

        return $ret;

    }

}

?>