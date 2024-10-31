<?php
require("wormhole.php");

class nebulaMagFunc {

    protected $ordini=array(
        'officina'=>array(
            'saldi'=>array(),
            'pren'=>array(),
            'comm'=>array()
        ),
        'banco'=>array(
            'saldi'=>array(),
            'aperti'=>array(),
            'liste'=>array()
        )
    );

    protected $listaGar=array(
        "FCM"=>"'LCG4'",
        "FCP"=>"",
        "PAM"=>"",
        "POM"=>"",
        "VGM"=>"'LCG1'"
    );

    protected $wh=false;
    protected $galileo;

    protected $log=array();

    function __construct($galileo) {

        $this->galileo=$galileo;
    }

    function getLog() {
        return $this->log;
    }

    function setWH($reparto) {
        $this->wh=new magazzinoWH($reparto,$this->galileo);
    }

    function buildWH($inizio,$fine) {
        $this->wh->build(array("inizio"=>$inizio,"fine"=>$fine));
    }

    function getExportMap() {
        return $this->wh->exportMap();
    }

    function getDepDB($dms,$rep) {
        return $this->wh->getDepDB($dms,$rep);
    }

    function getOrdini($ambito,$tipo) {

        if ($ambito=='officina') return $this->ordini['officina'][$tipo];
        if ($ambito=='banco') return $this->ordini['banco'][$tipo];
    }

    function getOrdiniOfficinaPren() {

        $this->ordini['officina']=array(
            'saldi'=>array(),
            'pren'=>array(),
            'comm'=>array()
        );

        $this->wh->build(array("inizio"=>date('Ymd'),"fine"=>date('Ymd')));

        $this->wh->getOrdiniOfficinaPren();

        foreach($this->wh->exportMap() as $k=>$m) {

            if ($m['result']) {
                $fid=$this->galileo->preFetchPiattaforma($m['piattaforma'],$m['result']);

                while ($row=$this->galileo->getFetchPiattaforma($m['piattaforma'],$fid)) {
                    $this->ordini['officina']['pren'][]=$row;
                }
            }
        }
    }

    function getOrdiniOfficinaComm() {

        $this->ordini['officina']=array(
            'saldi'=>array(),
            'pren'=>array(),
            'comm'=>array()
        );

        $this->wh->build(array("inizio"=>date('Ymd'),"fine"=>date('Ymd')));

        $this->wh->getOrdiniOfficinaComm();

        foreach($this->wh->exportMap() as $k=>$m) {

            if ($m['result']) {
                $fid=$this->galileo->preFetchPiattaforma($m['piattaforma'],$m['result']);

                while ($row=$this->galileo->getFetchPiattaforma($m['piattaforma'],$fid)) {
                    $this->ordini['officina']['comm'][]=$row;
                }
            }
        }
    }

    function getOrdiniBanco($param) {

        $this->ordini['banco']=array(
            'saldi'=>array(),
            'aperti'=>array(),
            'liste'=>array()
        );

        $this->wh->build(array("inizio"=>date('Ymd'),"fine"=>date('Ymd')));

        $this->wh->getOrdiniBanco($param);

        foreach($this->wh->exportMap() as $k=>$m) {

            if ($m['result']) {
                $fid=$this->galileo->preFetchPiattaforma($m['piattaforma'],$m['result']);

                while ($row=$this->galileo->getFetchPiattaforma($m['piattaforma'],$fid)) {
                    $this->ordini['banco']['aperti'][]=$row;
                }
            }
        }
    }

    function getListeVenditaBanco($param) {

        $this->ordini['banco']=array(
            'saldi'=>array(),
            'aperti'=>array(),
            'liste'=>array()
        );

        $this->wh->build(array("inizio"=>date('Ymd'),"fine"=>date('Ymd')));

        $this->wh->getListeVenditaBanco($param);

        foreach($this->wh->exportMap() as $k=>$m) {

            if ($m['result']) {
                $fid=$this->galileo->preFetchPiattaforma($m['piattaforma'],$m['result']);

                while ($row=$this->galileo->getFetchPiattaforma($m['piattaforma'],$fid)) {
                    $this->ordini['banco']['liste'][]=$row;
                }
            }
        }
    }

    //////////////////////////////////////////////////////////////////////////////////////////

    function getNoloc($param) {

        $lista=array();

        //la lista riguarda SOLO la data di oggi quindi la MAPPA è sempre solo una
        $this->wh->build(array("inizio"=>date('Ymd'),"fine"=>date('Ymd')));

        $a=array(
            "reparto"=>$param['reparto'],
            "anno"=>date('Y'),
            "d"=>date('Ymd')
        );

        $this->wh->getNoloc($a);

        foreach($this->wh->exportMap() as $k=>$m) {

            if ($m['result']) {
                $fid=$this->galileo->preFetchPiattaforma($m['piattaforma'],$m['result']);

                while ($row=$this->galileo->getFetchPiattaforma($m['piattaforma'],$fid)) {
                    $lista[]=$row;
                }
            }
        }

        return $lista;
    }

    function getNegativi($param) {

        $lista=array();

        //la lista riguarda SOLO la data di oggi quindi la MAPPA è sempre solo una
        $this->wh->build(array("inizio"=>date('Ymd'),"fine"=>date('Ymd')));

        $a=array(
            "reparto"=>$param['reparto'],
            "anno"=>date('Y'),
            "d"=>date('Ymd')
        );

        $this->wh->getNegativi($a);

        foreach($this->wh->exportMap() as $k=>$m) {

            if ($m['result']) {
                $fid=$this->galileo->preFetchPiattaforma($m['piattaforma'],$m['result']);

                while ($row=$this->galileo->getFetchPiattaforma($m['piattaforma'],$fid)) {
                    $lista[]=$row;
                }
            }
        }

        return $lista;
    }

    function getOrdiniGaranzia($param) {

        $lista=array();

        $this->wh->build(array("inizio"=>$param['da'],"fine"=>$param['a']));

        $a=array(
            "lista"=>$this->listaGar[$param['reparto']],
            "da"=>$param['da'],
            "a"=>$param['a']
        );

        $this->wh->getOrdiniGaranzia($a);

        foreach($this->wh->exportMap() as $k=>$m) {

            if ($m['result']) {
                $fid=$this->galileo->preFetchPiattaforma($m['piattaforma'],$m['result']);

                $telaio="";
                $temp=array();

                while ($row=$this->galileo->getFetchPiattaforma($m['piattaforma'],$fid)) {

                    if ($row['telaio']=="") continue;

                    if ($row['td_fatt']!='XO' && $row['d_fatt']!="") $row['chiuso']='SI';
                    else $row['chiuso']='NO';

                    //se le quantità combaciano e l'odl è chiuso salta la riga
                    if ($row['quantità']=$row['qta_scarico'] && $row['chiuso']=='SI') continue;

                    /*se il telaio è diverso da quello attuale
                    if ($row['telaio']!=$telaio) {

                        //scrivi le righe attuali
                        foreach ($temp as $k=>$t) {
                            $lista[]=$t;
                        }

                        $telaio=$row['telaio'];
                        $temp=array();

                        $temp[]=$row;

                        continue;
                    }

                    foreach ($temp as $k=>$t) {
                    }*/

                    $lista[]=$row;
   
                }
            }
        }

        return $lista;
    }

    function getGiacenzaM($a,$m) {

        return $this->wh->getGiacenzaM($a,$m);
    }

}

?>