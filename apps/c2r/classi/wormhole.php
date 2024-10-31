<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/core/wormhole/wormhole.php');

include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/concerto/concerto_odl.php');
include_once($_SERVER['DOCUMENT_ROOT']."/nebula/galileo/infinity/infinity_odl.php");


class c2rWHole extends nebulaWHole {

    protected $actualDms="";

    function __construct($reparto,$galileo) {

        parent::__construct($reparto,$galileo); 
    }

    function setGalileo($dms) {

        if ($dms=='concerto') {

            $obj=new galileoConcertoODL();
            $nebulaDefault['odl']=array("maestro",$obj);

            $this->galileo->setFunzioniDefault($nebulaDefault);

            $this->actualDms='concerto';

        }

        elseif ($dms=='infinity') {

            $obj=new galileoInfinityODL();
            $nebulaDefault['odl']=array("rocket",$obj);

            $this->galileo->setFunzioniDefault($nebulaDefault);

            $this->actualDms='infinity';

        }
    }

    /*function getRiltem($param) {

        foreach ($this->map as $k=>$m) {

            if ($m['dms']!=$this->actualDms) $this->setGalileo($m['dms']);
  
            if($m['dms']=='concerto') {
                $oby="res.num_rif_movimento,res.cod_inconveniente,CAST (res.cod_operaio AS int),d_inizio,o_inizio";
            }
            if($m['dms']=='infinity') {
                $oby="m.num_rif_movimento,m.cod_inconveniente,m.d_inizio,m.o_inizio";
            }

            if ($param['inizio']<$m['inizio']) $param['inizio']=$m['inizio'];
            if ($param['fine']>$m['fine']) $param['fine']=$m['fine'];

            $this->galileo->clearQuery();
            $this->galileo->clearQueryOggetto('default','odl');

            $this->galileo->executeGeneric('odl','getRiltem',$param,$oby);
            $this->map[$k]['result']=$this->galileo->getResult();

        }

    }*/

    function getFattNonMarc($mapIndex,$param) {

        //foreach ($this->map as $k=>$m) {

            $m=$this->map[$mapIndex];

            if ($m['dms']!=$this->actualDms) $this->setGalileo($m['dms']);
  
            if($m['dms']=='concerto') {
                $oby="movdoc.dat_documento_i";
            }
            if($m['dms']=='infinity') {
                $oby="";
            }

            if ($param['inizio']<$m['inizio']) $param['inizio']=$m['inizio'];
            if ($param['fine']>$m['fine']) $param['fine']=$m['fine'];

            $this->galileo->clearQuery();
            $this->galileo->clearQueryOggetto('default','odl');

            $this->galileo->executeGeneric('odl','getFattNonMarc',$param,$oby);
            return $this->galileo->getResult();

        //}

    }

    function getOccupazioneAgenda($mapIndex,$param) {

        $m=$this->map[$mapIndex];

        if ($m['dms']!=$this->actualDms) $this->setGalileo($m['dms']);

        if ($param['inizio']<$m['inizio']) $param['inizio']=$m['inizio'];
        if ($param['fine']>$m['fine']) $param['fine']=$m['fine'];

        $this->galileo->clearQuery();
        $this->galileo->clearQueryOggetto('default','odl');

        $this->galileo->executeGeneric('odl','getOccupazioneAgenda',$param,'');
        return $this->galileo->getResult();
    }

    function getFatturatoS($mapIndex,$param) {

        $m=$this->map[$mapIndex];

        if ($m['dms']!=$this->actualDms) $this->setGalileo($m['dms']);

        if ($param['inizio']<$m['inizio']) $param['inizio']=$m['inizio'];
        if ($param['fine']>$m['fine']) $param['fine']=$m['fine'];

        $this->galileo->clearQuery();
        $this->galileo->clearQueryOggetto('default','odl');

        $this->galileo->executeGeneric('odl','fatturato_S',$param,'');

        //die(json_encode($this->galileo->getLog('query')));

        return $this->galileo->getResult();
    }

    function getGarOpen($dms,$temp) {
        
        if ($dms!=$this->actualDms) $this->setGalileo($dms);

        $this->galileo->clearQuery();
        $this->galileo->clearQueryOggetto('default','odl');

        $this->galileo->executeGeneric('odl','getCliLamentati',$temp,'');

        return $this->galileo->getResult();
    }

    function getCostoConcerto($row) {

        $temp=(float)$row['des_psa_commande'];

        if ($temp>=0) return $temp;

        //quando viene chiamata dovrebbe esserci già il contesto di CONCERTO
        $this->galileo->clearQuery();
        $this->galileo->clearQueryOggetto('default','odl');

        $res=0;

        $this->galileo->executeGeneric('odl','ultimoCosto',$row,'');
        if ($this->galileo->getResult()) {
            $fid=$this->galileo->preFetch('odl');
            while($r=$this->galileo->getFetch('odl',$fid)) {
                $res=$r['costo']/$r['qta'];
            }

            //setta il costo del movimento
            $arr=array(
                'des_psa_commande'=>number_format($res,2,'.','')
            );
            $this->galileo->executeUpdate('odl','GN_MOVDET',$arr,"num_rif_movimento='".$row['rif']."' AND num_riga='".$row['riga']."' AND cod_inconveniente='".$row['lam']."'");
        }

        return $res;
    }

}

?>