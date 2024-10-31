<?php

use Spipu\Html2Pdf\Tag\Html\I;

require_once(DROOT.'/nebula/core/divo/divo.php');
include_once($_SERVER['DOCUMENT_ROOT']."/nebula/core/blocklist/blocklist.php");
require_once(DROOT.'/nebula/core/panorama/intervallo.php');
require_once(DROOT.'/nebula/core/odl/odl_func.php');
require_once(DROOT.'/nebula/core/odl/timb_func.php');
//require_once(DROOT.'/nebula/core/alan/alan.php');
include_once($_SERVER['DOCUMENT_ROOT']."/nebula/core/odl/pratica_func.php");

require_once(DROOT.'/nebula/apps/workshop/classi/wormhole.php');
require_once(DROOT.'/nebula/apps/workshop/classi/ws_foto.php');

class workshopApp extends appBaseClass {

    protected $loggedAllow=array();
    protected $collaboratori=array();
    protected $globalTrim=array();

    protected $marcature=array();
    protected $linkOpColl=array();

    protected $abilitazioni=array();

    protected $odl=array();

    //wormhole
    protected $wh;
    protected $odlFunc;
    protected $timbFunc;
    //protected $alan;

    protected $wspIntervallo;
    protected $infoIntervallo=array();

    //è un array di intervalli che viene generato nel caso si debbano correggere timbrature in date differenti
    protected $intarray=array();

    protected $log=array();

    function __construct($param,$galileo) {
        
        parent::__construct($galileo);

        $this->loc='/nebula/apps/workshop/';

        $this->param['wsp_officina']="";
        $this->param['wsp_tecnico']="";
        $this->param['wsp_timb']="";
        $this->param['visuale']="";

        $this->loadParams($param);

        if ($this->param['wsp_officina']=='') die ('Officina non definita !!!');

        //////////////////////////////////////
        $this->odlFunc=new nebulaOdlFunc($this->galileo);
        $this->timbFunc=new nebulaTimbFunc($this->galileo);
        //$this->alan=new nebulaAlan('S','',null,$this->galileo);
        //$this->alan->importa();

        $this->timbFunc->calcolaMarcatureAperte();
        $this->marcature=$this->timbFunc->getMarcature();

        //$this->marcature=$this->timbFunc->correggiAlan($this->timbFunc->calcolaMarcatureAperte());

        //$this->marcature=$this->wh->calcolaMarcatureAperte();
        //$this->getMarcatureAperte();

        ////////////////////////////////////////////
        //TEST
        // "optSpecial" sono le abilitazioni riferite a se stesso
        // "allow" sono le abilitazioni sugli altri

        /*
        ATT         Attesa lavoro (marcatura speciale) - odl fittizio
        ANT         Chiusura anticipata (marcatura speciale) - odl fittizio
        CHI         Allineamento al fine turno (CHIUSURA)
        EXT         Fine della marcatura in quel momento (CHIUSURA)
        PRV         Prova vettura (marcatura speciale) - odl fittizio
        PUL         Tempo per pulizia posto di lavoro (marcatura speciale) - STESSO ODL
        //la marcatura PUL può essere chiusa anticipatamente ed in questo caso viene spostata nell'odl FITTIZIO sempre come PUL
        */

        $this->abilitazioni=array(
            "21"=>array(
                "ID_gruppo"=>21,
                "des_gruppo"=>"RT",
                "optSpecial"=>'{"CHI":1,"PRV":1,"EXT":1}',
                "allow"=>'{"EXT":1,"SER":1,"CHI":1}',
                "stato"=>1,
                "ability"=>array()
            ),
            "8"=>array(
                "ID_gruppo"=>8,
                "des_gruppo"=>"RT",
                "optSpecial"=>'{"CHI":1,"PRV":1,"EXT":1}',
                "allow"=>'{"EXT":1,"SER":1,"CHI":1}',
                "stato"=>1
            ),
            "5"=>array(
                "ID_gruppo"=>5,
                "des_gruppo"=>"RT",
                "optSpecial"=>'{"CHI":1,"PRV":1,"EXT":1}',
                "allow"=>'{"EXT":1,"SER":1,"CHI":1}',
                "stato"=>1
            ),
            "17"=>array(
                "ID_gruppo"=>17,
                "des_gruppo"=>"RT",
                "optSpecial"=>'{"CHI":1,"PRV":1,"EXT":1}',
                "allow"=>'{"EXT":1,"SER":1,"CHI":1}',
                "stato"=>1
            ),
            "10"=>array(
                "ID_gruppo"=>10,
                "des_gruppo"=>"RT",
                "optSpecial"=>'{"CHI":1,"PRV":1,"EXT":1}',
                "allow"=>'{"EXT":1,"SER":1,"CHI":1}',
                "stato"=>1
            ),
            "23"=>array(
                "ID_gruppo"=>23,
                "des_gruppo"=>"RT",
                "optSpecial"=>'{"CHI":1,"PRV":1,"EXT":1}',
                "allow"=>'{"EXT":1,"SER":1,"CHI":1}',
                "stato"=>1
            ),
            "75"=>array(
                "ID_gruppo"=>75,
                "des_gruppo"=>"RT",
                "optSpecial"=>'{"CHI":1,"PRV":1,"EXT":1}',
                "allow"=>'{"EXT":1,"SER":1,"CHI":1}',
                "stato"=>1
            ),
            "80"=>array(
                "ID_gruppo"=>80,
                "des_gruppo"=>"RT",
                "optSpecial"=>'{"CHI":1,"PRV":1,"EXT":1}',
                "allow"=>'{"EXT":1,"SER":1,"CHI":1}',
                "stato"=>1
            ),
            "28"=>array(
                "ID_gruppo"=>28,
                "des_gruppo"=>"TR",
                "optSpecial"=>'{"CHI":1,"PRV":1,"EXT":1}',
                "allow"=>'{"EXT":1,"SER":1,"CHI":1}',
                "stato"=>1
            ),
            "59"=>array(
                "ID_gruppo"=>59,
                "des_gruppo"=>"vRT",
                "optSpecial"=>'{"CHI":1,"SER":1,"EXT":1}',
                "allow"=>'{"EXT":1,"SER":1,"CHI":1}',
                "stato"=>1
            ),
            "1"=>array(
                "ID_gruppo"=>1,
                "des_gruppo"=>"RS",
                "optSpecial"=>'{}',
                "allow"=>'{"EXT":1,"SER":1,"ANT":1,"CHI":1}',
                "stato"=>1
            ),
            "3"=>array(
                "ID_gruppo"=>3,
                "des_gruppo"=>"RS",
                "optSpecial"=>'{}',
                "allow"=>'{"EXT":1,"SER":1,"ANT":1,"CHI":1}',
                "stato"=>1
            ),
            "22"=>array(
                "ID_gruppo"=>22,
                "des_gruppo"=>"RS",
                "optSpecial"=>'{}',
                "allow"=>'{"EXT":1,"SER":1,"ANT":1,"CHI":1}',
                "stato"=>1
            ),
            "35"=>array(
                "ID_gruppo"=>35,
                "des_gruppo"=>"RS",
                "optSpecial"=>'{}',
                "allow"=>'{"EXT":1,"SER":1,"ANT":1,"CHI":1}',
                "stato"=>1
            ),
            "81"=>array(
                "ID_gruppo"=>35,
                "des_gruppo"=>"RS",
                "optSpecial"=>'{}',
                "allow"=>'{"EXT":1,"SER":1,"ANT":1,"CHI":1}',
                "stato"=>1
            ),
            "2"=>array(
                "ID_gruppo"=>2,
                "des_gruppo"=>"RC",
                "optSpecial"=>'{}',
                "allow"=>'{"ANT":1}',
                "stato"=>1
            ),
            "15"=>array(
                "ID_gruppo"=>15,
                "des_gruppo"=>"RC",
                "optSpecial"=>'{}',
                "allow"=>'{"ANT":1}',
                "stato"=>1
            ),
            "16"=>array(
                "ID_gruppo"=>16,
                "des_gruppo"=>"RC",
                "optSpecial"=>'{}',
                "allow"=>'{"ANT":1}',
                "stato"=>1
            ),
            "29"=>array(
                "ID_gruppo"=>29,
                "des_gruppo"=>"RC",
                "optSpecial"=>'{}',
                "allow"=>'{"ANT":1}',
                "stato"=>1
            ),
            "4"=>array(
                "ID_gruppo"=>4,
                "des_gruppo"=>"TEC",
                "optSpecial"=>'{"ATT":1,"PUL":1}',
                "allow"=>'{}',
                "stato"=>1
            ),
            "7"=>array(
                "ID_gruppo"=>7,
                "des_gruppo"=>"TEC",
                "optSpecial"=>'{"ATT":1,"PUL":1}',
                "allow"=>'{}',
                "stato"=>1
            ),
            "11"=>array(
                "ID_gruppo"=>11,
                "des_gruppo"=>"TEC",
                "optSpecial"=>'{"ATT":1,"PUL":1}',
                "allow"=>'{}',
                "stato"=>1
            ),
            "12"=>array(
                "ID_gruppo"=>12,
                "des_gruppo"=>"TEC",
                "optSpecial"=>'{"ATT":1,"PUL":1}',
                "allow"=>'{}',
                "stato"=>1
            ),
            "13"=>array(
                "ID_gruppo"=>13,
                "des_gruppo"=>"TEC",
                "optSpecial"=>'{"ATT":1,"PUL":1}',
                "allow"=>'{}',
                "stato"=>1
            ),
            "20"=>array(
                "ID_gruppo"=>20,
                "des_gruppo"=>"TEC",
                "optSpecial"=>'{"ATT":1,"PUL":1}',
                "allow"=>'{}',
                "stato"=>1
            ),
            "24"=>array(
                "ID_gruppo"=>24,
                "des_gruppo"=>"TEC",
                "optSpecial"=>'{"ATT":1,"PUL":1}',
                "allow"=>'{}',
                "stato"=>1
            ),
            "73"=>array(
                "ID_gruppo"=>73,
                "des_gruppo"=>"TEC",
                "optSpecial"=>'{"ATT":1,"PUL":1}',
                "allow"=>'{}',
                "stato"=>1
            ),
            "74"=>array(
                "ID_gruppo"=>74,
                "des_gruppo"=>"TEC",
                "optSpecial"=>'{"ATT":1,"PUL":1}',
                "allow"=>'{}',
                "stato"=>1
            ),
            "34"=>array(
                "ID_gruppo"=>34,
                "des_gruppo"=>"ITR",
                "optSpecial"=>'{}',
                "allow"=>'{"ATT":1,"PUL":1,"EXT":1,"SER":1,"ANT":1,"PRV":1,"CHI":1}',
                "stato"=>1
            ),
            "58"=>array(
                "ID_gruppo"=>58,
                "des_gruppo"=>"RC",
                "optSpecial"=>'{}',
                "allow"=>'{"ATT":1,"PUL":1,"EXT":1,"SER":1,"ANT":1,"PRV":1,"CHI":1}',
                "stato"=>1
            ),
            "68"=>array(
                "ID_gruppo"=>68,
                "des_gruppo"=>"PRT",
                "optSpecial"=>'{"CHI":1,"PRV":1,"EXT":1}',
                "allow"=>'{}',
                "stato"=>1
            )

        );
        //END TEST

        $this->loggedAllow=$this->timbFunc->getSpeciali();

        if (isset($this->id)) {

            $gruppolog=$this->id->getGruppoID($this->param['wsp_officina'],array('S','A','D'));

            if (isset($this->abilitazioni[$gruppolog])) {
                if ($t=json_decode($this->abilitazioni[$gruppolog]['allow'],true)) {
                    foreach ($t as $special=>$s) {
                        if (array_key_exists($special,$this->loggedAllow)) {
                            $this->loggedAllow[$special]=$s;
                        }
                    }
                }
            }
        }

        ////////////////////////////////////////////////////////////////////////

        $this->init($this->param['wsp_officina']);
   
    }

    function initClass() {
        return ' workshopCode(\''.$this->param['nebulaFunzione']['nome'].'\');';
    }

    /*function getMarcatureAperte() {

        $this->wh->getMarcatureAperte();

        foreach ($this->wh->exportMap() as $m) {

            if ($m['result']) {
                $fid=$this->galileo->preFetchPiattaforma($m['piattaforma'],$m['result']);

                while ($row=$this->galileo->getFetchPiattaforma($m['piattaforma'],$fid)) {

                    //se la marcatura è chiusa, è una marcatura speciale e la data non è quella odierna non prendere in considerazione
                    if ($row['d_fine']!="" && $row['des_note']!="" && $row['d_inizio']!=date('Ymd')) continue;

                    //potrebbe già esistere un'ultima marcatura per l'operaio
                    //attingendo le informazioni da DMS differenti
                    if (array_key_exists($row['cod_operaio'],$this->marcature)) {
                        $new=$row['d_inizio'].':'.$row['o_inizio'];
                        $actual=$this->marcature[$row['cod_operaio']]['d_inizio'].':'.$this->marcature[$row['cod_operaio']]['o_inizio'];
                        if ($new>$actual) $this->marcature[$row['cod_operaio']]=$row;
                        $this->marcature[$row['cod_operaio']]['dms']=$m['dms'];
                    }
                    else {
                        $this->marcature[$row['cod_operaio']]=$row;
                        $this->marcature[$row['cod_operaio']]['dms']=$m['dms'];
                    }
                }
            }
        }

    }*/

    function getLog() {
        return $this->log;
    }

    function init($officina) {

        //viene scritto per permettere la modifica dall'esterno
        //esempio:reset (verifica della chiusura delle marcature a fine turno)
        if(!$officina || $officina=='') return;

        $this->param['wsp_officina']=$officina;

        $this->collaboratori=array();

        $this->wh=new workshopWHole($this->param['wsp_officina'],$this->galileo);
        $a=array(
            'inizio'=>date('Ym'),
            'fine'=>date('Ym')
        );
        $this->wh->build($a);
        /////////////////////////////////////

        $this->infoIntervallo=array(
            "contesto"=>"reparto",
            "presenza"=>"totali",
            "badge"=>false,
            "schemi"=>false,
            "agenda"=>true,
            "brogliaccio"=>false,
            "intervallo"=>"libero",
            "data_i"=>date('Ymd'),
            "data_f"=>date('Ymd'),
            "actualReparto"=>$this->param['wsp_officina']
        );

        $tarr=array();
        $tarr[$this->param['wsp_officina']]=array();

        $this->wspIntervallo=new quartetIntervallo($this->infoIntervallo,$tarr,$this->galileo);
        $this->wspIntervallo->calcola();
        $this->wspIntervallo->calcolaIntTot();
        $this->globalTrim=$this->wspIntervallo->getGlobalTrim();
        $tempColl=$this->wspIntervallo->getCollaboratori();

        //$this->timbFunc->calcolaMarcatureAperte();
        //$this->marcature=$this->timbFunc->getMarcature();

        foreach ($tempColl[$this->param['wsp_officina']] as $collID=>$c) {

            foreach ($c as $cc) {

                $this->collaboratori[$collID]=$cc;

                $this->collaboratori[$collID]['ability']=$this->timbFunc->getSpeciali();
                $this->collaboratori[$collID]['allow']=$this->collaboratori[$collID]['ability'];

                if (isset($this->abilitazioni[$cc['ID_gruppo']]) && $this->abilitazioni[$cc['ID_gruppo']]['stato']==1) {

                    if ($t=json_decode($this->abilitazioni[$cc['ID_gruppo']]['optSpecial'],true)) {
                        foreach ($t as $special=>$s) {
                            if (array_key_exists($special,$this->collaboratori[$collID]['ability'])) {
                                $this->collaboratori[$collID]['ability'][$special]=$s;
                            }
                        }
                    }

                    if ($t=json_decode($this->abilitazioni[$cc['ID_gruppo']]['allow'],true)) {
                        foreach ($t as $special=>$s) {
                            if (array_key_exists($special,$this->collaboratori[$collID]['allow'])) {
                                $this->collaboratori[$collID]['allow'][$special]=$s;
                            }
                        }
                    }
                }

                break;
            }
        }

        $this->drawReset();

    }

    function trovaOfficinaDms() {

        $dms=false;

        //viene usato in riferimento ad un giorno specifico e in quel giorno un reparto apparterrà solo ad un DMS
        foreach ($this->wh->exportMap() as $m) {
            $dms=$m['dms'];
            break;
        }

        if (!$dms) return false;

        //trova l'officina del DMS riferita al reparto impostato
        $this->galileo->clearQuery();
        $this->galileo->getReparto($this->param['wsp_officina']);
        
        if (!$this->galileo->getResult()) return false;

        $fid=$this->galileo->preFetchBase('reparti');

        while ($row=$this->galileo->getFetchBase('reparti',$fid)) {
            $officinaDms=$row[$dms];
        }

        if (!$officinaDms || $officinaDms=="") return false;

        return array($dms,$officinaDms); 
    }

    function getOdl($rif,$dms) {

        $this->odl=array();

        //se è una marcatura speciale di infinity fermati qui
        if ($rif=='0k' && $dms=='infinity') {

            $this->odl=$this->odlFunc->getFittizioInfinity();
            return $this->odl;
        }

        $result=$this->odlFunc->getOdl($rif,$dms,'cli');

        if (!$result) return false;

        $fid=$this->galileo->preFetchPiattaforma($this->odlFunc->getPiattaforma(),$result);
        while ($row=$this->galileo->getFetchPiattaforma($this->odlFunc->getPiattaforma(),$fid)) {

            //fa già parte della query
            //if ($row['ind_chiuso']=='S') break;

            $this->odl[$row['rif']][$row['lam']]=$row;
            $this->odl[$row['rif']][$row['lam']]['dms']=$dms;
            $this->odl[$row['rif']][$row['lam']]['nebulaAddebito']=$this->odlFunc->getAddebito($row,$dms);
        }


        /*$this->wh->clearMap();
        $this->wh->getOdl($rif,$dms);

        foreach ($this->wh->exportMap() as $m) {

            if ($m['result']) {
                $fid=$this->galileo->preFetchPiattaforma($m['piattaforma'],$m['result']);

                while ($row=$this->galileo->getFetchPiattaforma($m['piattaforma'],$fid)) {

                    $this->odl[$row['rif']][$row['lam']]=$row;
                    $this->odl[$row['rif']][$row['lam']]['dms']=$m['dms'];
                    $this->odl[$row['rif']][$row['lam']]['nebulaAddebito']=$this->odlFunc->getAddebito($row,$m['dms']);
                }
            }
        }*/

        return $this->odl;

    }

    //////////////////////////////////////////////////////////
    //AZIONI SPECIALI

    function special_CHI($IDcoll,$statoLamentato,$checkID) {

        //checkID era l'ID della marcatura al momento del rendering della pagina web

        //"calcola marcature aperte" è già stato chiamato dalla classe chiamante
        if (!array_key_exists($IDcoll,$this->marcature)) return false;

        //checkID era l'ID della marcatura al momento del rendering della pagina web
        if ($this->marcature[$IDcoll]['num_rif_riltem']!=$checkID) return false;

        // ID è l'indice della marcatura nel DMS
        $res=array(
            "dms"=>"",
            "ID"=>"",
            "d_fine"=>"",
            "o_fine"=>"",
            "statoLamentato"=>$statoLamentato
        );

        $tl=$this->wspIntervallo->getCollTot('subs',$this->param['wsp_officina'],$IDcoll);

        if (!$tl) return false;

        //$temp=$this->timbFunc->special_CHI($IDcoll,$tl,$checkID);

        //if ($temp) {

            $fineTurno=mainfunc::gab_mintostring($tl->getFineTurno(mainFunc::gab_stringtomin($this->marcature[$IDcoll]['o_inizio'])));

            if ($fineTurno) {

                if ($fineTurno>$this->marcature[$IDcoll]['o_inizio']) return false;

                    $res["dms"]=$this->marcature[$IDcoll]['dms'];
                    $res["ID"]=$this->marcature[$IDcoll]['num_rif_riltem'];
                    $res["d_fine"]=$this->marcature[$IDcoll]['d_inizio'];
                    $res["o_fine"]=$fineTurno;
                return $res;
            }
            else return false;
            
            /*$res['dms']=$temp['dms'];
            $res['ID']=$temp['ID'];
            $res['d_fine']=$temp['d_fine'];
            $res['o_fine']=$temp['o_fine'];

            return $res;*/
        //}
        //else return false;

        return false;

    }

    function special_EXT($IDcoll,$statoLamentato,$checkID) {

        //checkID era l'ID della marcatura al momento del rendering della pagina web

        //"calcola marcature aperte" è già stato chiamato dalla classe chiamante
        if (!array_key_exists($IDcoll,$this->marcature)) return false;

        //checkID era l'ID della marcatura al momento del rendering della pagina web
        if ($this->marcature[$IDcoll]['num_rif_riltem']!=$checkID) return false;

         // ID è l'indice della marcatura nel DMS
         $res=array(
            "dms"=>$this->marcature[$IDcoll]['dms'],
            "ID"=>$this->marcature[$IDcoll]['num_rif_riltem'],
            "d_fine"=>date('Ymd'),
            "o_fine"=>date('H:i'),
            "statoLamentato"=>$statoLamentato,
            "ambito"=>$this->marcature[$IDcoll]['fittizio']==1?"speciale":"regolare"
        );

        return $res;

    }

    function special_ATT($IDcoll,$statoLamentato,$checkID) {

        $temp=$this->trovaOfficinaDms();

        if (!$temp) return false; 

        $param=array(
            "statoLamentato"=>$statoLamentato,
            "speciale"=>"ATT",
            "dms"=>$temp[0],
            "officinaDms"=>$temp[1]
        );

        return $this->timbFunc->inizioSpeciale($IDcoll,$checkID,$param);

    }

    function special_PRV($IDcoll,$statoLamentato,$checkID,$rifOdlPRV) {

        // "rifOdlPRV" è l'odl della vettura che si sta provando

        $temp=$this->trovaOfficinaDms();

        if (!$temp) return false; 

        $param=array(
            "statoLamentato"=>$statoLamentato,
            "speciale"=>"PRV",
            "dms"=>$temp[0],
            "officinaDms"=>$temp[1],
            "rifOdlPRV"=>$rifOdlPRV
        );

        return $this->timbFunc->inizioSpeciale($IDcoll,$checkID,$param);

    }

    function special_SER($IDcoll,$statoLamentato,$checkID) {

        $temp=$this->trovaOfficinaDms();

        if (!$temp) return false; 

        $param=array(
            "statoLamentato"=>$statoLamentato,
            "speciale"=>"SER",
            "dms"=>$temp[0],
            "officinaDms"=>$temp[1]
        );

        return $this->timbFunc->inizioSpeciale($IDcoll,$checkID,$param);

    }

    function special_PUL($IDcoll,$statoLamentato,$checkID) {

        $param=array(
            "statoLamentato"=>$statoLamentato,
        );

        return $this->timbFunc->inizioPUL($IDcoll,$checkID,$param,$this->odlFunc);

    }

    function special_ANT($IDcoll,$checkID) {

        /*$temp=$this->trovaOfficinaDms();

        if (!$temp) return false; 

        $param=array(
            "speciale"=>"ANT",
            "dms"=>$temp[0],
            "officinaDms"=>$temp[1]
        );*/

        return $this->timbFunc->inizioANT($IDcoll,$checkID);

    }
    //////////////////////////////////////////////////////////

    function customDraw() {
        //echo '<iframe style="width:100%;height:100%" frameBorder="0" src="http://'.SADDR.'/nebula/frames/start3stop/index.php?reparto='.$this->param['wsp_reparto'].'"></iframe>';
        
        if ($this->param['visuale']=='') return;

        if ($this->param['visuale']=='reset') {
            //$this->drawReset();
            return;
        }

        echo '<script type="text/javascript" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/workshop/core/workshop.js?v='.time().'" ></script>';
        echo '<script type="text/javascript" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/fotone/fotone_js.js?v='.time().'" ></script>';

        nebulaPraticaFunc::initJS();
        BlockList::blockListInit();

        echo '<script type="text/javascript">';

            ob_start();
                include (DROOT.'/nebula/apps/workshop/core/default.js');
            ob_end_flush();
            
        echo '</script>';

        if ($this->param['wsp_tecnico']!="") $this->drawTecnico();

        elseif ($this->param['visuale']=='generale' || $this->param['visuale']=='tutto' ) $this->drawGenerale();

        elseif ($this->param['visuale']=='personale') $this->drawPersonale();

        
    }

    function drawGenerale() {

        //nebulaPraticaFunc::initJS();

        $divo=new Divo('workshop','5%','96%',true);

        $divo->setBk('#f1c4a5');

        $css=array(
            "font-weight"=>"bold",
            "font-size"=>"1.3em",
            "margin-left"=>"15px",
            "margin-top"=>"2px"
        );

        $css2=array(
            "width"=>"15px",
            "height"=>"15px",
            "top"=>"50%",
            "transform"=>"translate(0%,-50%)",
            "right"=>"5px"
        );

        $divo->setChkimgCss($css2);

        ////////////////////////////////////////////////////////
        echo '<div style="position:relative;display:inline-block;width:50%;height:98%;padding:3px;border-right:1px solid black;box-sizing:border-box;vertical-align:top;" >';
            
            $txt='<div style="height:9%;">';
                $txt.='<img style="width:30px;height:30px;margin-top:5px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/workshop/img/print.png" onclick="window._nebulaWS.graffaPdf(\''.$this->param['wsp_officina'].'\');""/>';
                $txt.='<span style="margin-left:20px;font-size:0.9em;">Cambio data:</span>';
                $txt.='<input id="wsp_generale_inarrivo_data" style="width:150px;margin-left:10px;" type="date" value="'.date('Y-m-d').'" />';
                $txt.='<button style="margin-left:10px;" onclick="window._nebulaWS.inarrivo(\''.$this->param['wsp_officina'].'\');">Cerca</button>';
             $txt.='</div>';
            $txt.='<div id="wsp_generale_inarrivo" style="height:91%;overflow:scroll;overflow-x:hidden;"></div>';
            $txt.='<script type="text/javascript">window._nebulaWS.inarrivo(\''.$this->param['wsp_officina'].'\');</script>';

            $divo->add_div('In Arrivo','black',0,"",$txt,0,$css);

            $txt='<div style="height:100%;width:100%;overflow:scroll;overflow-x:hidden;">';
                $txt.='<div id="wsp_generale_sospesi" style="width:95%;"></div>';
            $txt.='</div>';

            $divo->add_div('Sospesi','black',1,"Y",$txt,0,$css);

            $txt="";

            $divo->add_div('In attesa','black',0,"",$txt,0,$css);

            $txt='<div style="height:100%;width:100%;overflow:scroll;overflow-x:hidden;">';
                $txt.='<div id="wsp_generale_officina" style="width:95%;"></div>';
            $txt.='</div>';

            $txt.='<script type="text/javascript">window._nebulaWS.inofficina(\''.$this->param['wsp_officina'].'\',"");</script>';

            $divo->add_div('In Officina','black',0,"",$txt,1,$css);

            unset($txt);

            $divo->build();

            $divo->draw();
        
        echo '</div>';

        echo '<div style="position:relative;display:inline-block;width:50%;height:98%;padding:3px;box-sizing:border-box;vertical-align:top;" >';

            echo '<div style="height:5%;font-weight:bold;font-size:1.3em;margin-left:10px;">';
                echo date('d.m.Y   H:i');
                if ($this->galileo->checkHandler('solari')) {
                    echo '<span style="color:green;margin-left:5px;font-size:0.8em;" >(solari ON-line)</span>';
                }
                else {
                    echo '<span style="color:red;margin-left:5px;font-size:0.8em;" >(solari OFF-line)</span>';
                }
            echo '</div>';

            echo '<div style="height:95%;overflow:scroll;">';
                //{"VWS":{"9":
                //[{"ID_coll":9,"data_i":"20190301","data_f":"21001231","gruppo":"TEC","des_gruppo":"Tecnico","posizione":1,"macrogruppo":"TES","des_macrogruppo":"Tecnici Service","posizione_macrogruppo":3,"reparto":"VWS","macroreparto":"S","des_reparto":"Service Volkswagen","rep_concerto":"PV","des_macroreparto":"Service","nome":"Elia","cognome":"Amadori","concerto":"e.amadori","cod_operaio":"18","tel_interno":"","IDDIP":"51","IDMAT":"125","flag_sostituzione":false}],

                //foreach ($this->collaboratori[$this->param['wsp_officina']] as $collID=>$c) {

                    //echo '<div>'.json_encode($this->marcature).'</div>';

                    //all'interno di un reparto è possibile ci sia SOLO un ruolo
                    //foreach ($c as $cc) {
                    foreach ($this->collaboratori as $collID=>$cc) {

                        if ($cc['cod_operaio']=="") continue;

                        if (array_key_exists($cc['ID_coll'],$this->marcature)) {
                           
                            $this->getOdl($this->marcature[$cc['ID_coll']]['num_rif_movimento'],$this->marcature[$cc['ID_coll']]['dms']);
                        }

                        //echo '<div>'.json_encode($this->odl).'</div>';

                        /*$cc['optFine']=false;

                        if (isset($this->abilitazioni[$cc['ID_coll']])) {
                            if ($this->abilitazioni[$cc['ID_coll']]['optFine']==1) $cc['optFine']=true;
                        }*/

                        $tarrRif=$this->wspIntervallo->getCollTot('subs',$this->param['wsp_officina'],$cc['ID_coll']);

                        echo '<div style="width:98%;border-bottom:8px double #7c7a7a;padding:2px;box-sizing:border-box;margin-top:8px;padding-bottom:15px;">';

                            echo '<div style="font-weight:bold;color: white;height: 25px;line-height: 25px;padding-left: 5px;margin-bottom: 5px;" >';

                                echo '<div style="position:relative;display:inline-block;width:90%;vertical-align:top;height:25px;background-color: #444444;" >';
                                    echo '<span style="margin-left:5px;">('.$cc['ID_coll'].') '.$cc['cognome'].' '.$cc['nome'].'</span>';
                                echo '</div>';
                    
                                echo '<div style="position:relative;display:inline-block;width:10%;vertical-align:top;height:25px;text-align:right;" >';

                                    //$presenza=$tarrRif->getPresenza();
                                    //if ($presenza['actual']>0) {
                                        //echo '<img style="position:relative;width:60%;height:25px;top:50%;transform:translate(0%,-50%);" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/workshop/img/nuova.png" />';
                                        echo '<img style="position:relative;width:25px;height:25px;top:50%;transform:translate(0%,-50%);cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/workshop/img/attività.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].openTecnico(\''.$collID.'\',\'\');" />';
                                        //echo '<div class="divButton" style="position:relative;left:50%;top:50%;transform:translate(-50%,-50%);height:21px;">Nuova</div>';
                                    //}
                                echo '</div>';
                    
                            echo '</div>';
            
                            $this->drawCollTl($tarrRif,$cc,$this->param['wsp_officina']);

                        echo '</div>';
                    }
                //}

            echo '</div>';
        echo '</div>';
    }

    function drawPersonale() {

        if (array_key_exists($this->id->getCollID(),$this->collaboratori)) {
            $coll=$this->collaboratori[$this->id->getCollID()];
        }
        else {
            echo 'collaboratore non abilitato!';
            return;
        }

        if (array_key_exists($coll['ID_coll'],$this->marcature)) {

            $this->getOdl($this->marcature[$coll['ID_coll']]['num_rif_movimento'],$this->marcature[$coll['ID_coll']]['dms']);
        }


        $divo=new Divo('workshop','5%','96%',true);

        $divo->setBk('#f1c4a5');

        $css=array(
            "font-weight"=>"bold",
            "font-size"=>"1.3em",
            "margin-left"=>"15px",
            "margin-top"=>"2px"
        );

        $css2=array(
            "width"=>"15px",
            "height"=>"15px",
            "top"=>"50%",
            "transform"=>"translate(0%,-50%)",
            "right"=>"5px"
        );

        $divo->setChkimgCss($css2);

        echo '<div style="position:relative;display:inline-block;width:50%;height:98%;padding:3px;border-right:1px solid black;box-sizing:border-box;vertical-align:top;" >';

            $txt="";

            $divo->add_div('Sospesi','black',0,"",$txt,0,$css);

            $txt="";

            $divo->add_div('Coda','black',0,"",$txt,0,$css);

            $txt="";

            ///////////////////////////
            $txt='<div style="width:98%;" >';

                $txt.='<div style="width:98%;padding:2px;box-sizing:border-box;margin-top: 10px;">';

                    ob_start();
                        echo '<div style="font-weight:bold;color: white;height: 25px;line-height: 25px;padding-left: 5px;margin-bottom: 5px;" >';

                            echo '<div style="position:relative;display:inline-block;width:80%;vertical-align:top;height:25px;background-color: #444444;" >';
                                echo '<span style="margin-left:5px;">('.$coll['ID_coll'].') '.$coll['cognome'].' '.$coll['nome'].'</span>';
                                echo '<img style="position:absolute;width:20px;height:20px;top:2px;right:5px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/workshop/img/refresh.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].wspRefresh();" />';
                            echo '</div>';
                
                            echo '<div style="position:relative;display:inline-block;width:20%;vertical-align:top;height:25px;text-align:right;" >';
                                echo '<img style="position:relative;width:60%;height:25px;top:50%;transform:translate(0%,-50%);cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/workshop/img/nuova.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].openTimbratura(\'nuova\');" />';
                                //echo '<img style="position:relative;width:40px;height:30px;top:50%;transform:translate(0%,-50%);" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/workshop/img/tecnico.png" />';
                                //echo '<div class="divButton" style="position:relative;left:50%;top:50%;transform:translate(-50%,-50%);height:21px;">Nuova</div>';
                            echo '</div>';
                
                        echo '</div>';
                        
                        $tarrRif=$this->wspIntervallo->getCollTot('subs',$this->param['wsp_officina'],$coll['ID_coll']);
                        $this->drawCollTl($tarrRif,$coll,$this->param['wsp_officina']);

                    $txt.=ob_get_clean();

                $txt.='</div>';

                $txt.='<div style="position:relative;width:98%;min-height:60px;margin-top:10px;">';

                    /*$txt.='<div style="position:relative;font-weight:bold;height:28px;text-align:center;">';
                        //$txt.='<span>Marcatura attuale:</span>';
                        $txt.='<div class="divButton" style="position:relative;left:50%;transform:translate(-50%,0%);height:25px;">Nuova</div>';
                    $txt.='</div>';*/

                    if (isset($this->marcature[$coll['ID_coll']])) {
                        ob_start();
                            $this->drawActualLam($coll,$this->param['wsp_officina']);
                        $txt.=ob_get_clean();
                    }

                $txt.='</div>';

            $txt.='</div>';

            /////////////////////////////////

            $divo->add_div('Attuale','black',0,"",$txt,1,$css);

            $txt="";

            $divo->add_div('Richieste','black',1,"Y",$txt,0,$css);

            unset($txt);

            $divo->build();

            $divo->draw();

        echo '</div>';

        ////////////////////////////////////////////////////////////

        echo '<div style="position:relative;display:inline-block;width:50%;height:98%;padding:3px;box-sizing:border-box;vertical-align:top;" >';

            if ($this->param['wsp_timb']!="") {
                $this->drawMarcatura($coll,$tarrRif);
            }

            elseif (isset($this->marcature[$coll['ID_coll']]) && isset($this->odl[$this->marcature[$coll['ID_coll']]['num_rif_movimento']]) && $this->marcature[$coll['ID_coll']]['fittizio']==0) {

                $this->drawLavorazione($this->marcature[$coll['ID_coll']],$this->odl[$this->marcature[$coll['ID_coll']]['num_rif_movimento']]);
            }

            else if (isset($this->marcature[$coll['ID_coll']]) && substr($this->marcature[$coll['ID_coll']]['des_note'],0,3)=='PRV') {

                //echo 'czvvaevadxvcasdv';

                $tjs=json_decode(substr($this->marcature[$coll['ID_coll']]['des_note'],3),true);

                if ($tjs) {
                    $this->getOdl($tjs['rif'],$this->marcature[$coll['ID_coll']]['dms']);
                    $this->drawLavorazione($this->marcature[$coll['ID_coll']],$this->odl[$tjs['rif']]);
                }
            }
        
        echo '</div>';

    }

    function drawReset() {

        foreach ($this->collaboratori as $collID=>$cc) {

            if ($cc['cod_operaio']=="") continue;

            if (isset($this->marcature[$cc['ID_coll']])) {

                $timb=$this->marcature[$cc['ID_coll']];

                //$this->drawCollTl($tarrRif,$cc,$this->param['wsp_officina']);

                ////////////////////////////////////////////////////
                //se c'è una timeline attiva e la marcatura è aperta
                //verificare attraverso ALAN se esiste una timbratura maggiore dell'inizio della marcatura 
                //verificare l'orario di fine turno con $tarrRif ed impostare la chiusura nel DB
                //aggiornare l'array $timb e proseguire
    
                if ($timb['d_fine']=="") {

                    $this->log[]=$this->param['wsp_officina'].'('.$cc['ID_coll'].')';

                    //$tarrRif=$this->wspIntervallo->getCollTot('subs',$this->param['wsp_officina'],$cc['ID_coll']);
    
                    $tl=false;
    
                    //se la marcatura è in data odierna la timeline è la stessa usata per disegnare il turno del tecnico
                    //if ($timb['d_inizio']==date('Ymd')) $tl=$tarrRif;
                    if ($timb['d_inizio']==date('Ymd')) $tl=$this->wspIntervallo->getCollTot('subs',$this->param['wsp_officina'],$cc['ID_coll']);
    
                    else {
                        if (array_key_exists($timb['d_inizio'],$this->intarray)) {
                            $tl=$this->intarray[$timb['d_inizio']];
                        }
                        else {
                            $tempConfig=$this->infoIntervallo;
                            $tempConfig['data_i']=$timb['d_inizio'];
                            $tempConfig['data_f']=$timb['d_inizio'];
    
                            $tarr[$this->param['wsp_officina']]=array();
    
                            $tempint=new quartetIntervallo($tempConfig,$tarr,$this->galileo);
                            $tempint->calcola();
                            $tempint->calcolaIntTot();
                            $this->intarray[$timb['d_inizio']]=$tempint->getCollTot('subs',$this->param['wsp_officina'],$cc['ID_coll']);
                            $tl=$this->intarray[$timb['d_inizio']];
                        }
                    }
    
                    //la correzione deve essere fatta sia che esista o no un turno per il tecnico nel giorno
                    //if ($tl) {
                        //echo '<div>'.json_encode($tl).'</div>';
                        $timb=$this->timbFunc->correggiAlan($timb,$tl);
                        $timb['fittizio']=$this->marcature[$cc['ID_coll']]['fittizio'];
                        //echo '<div>'.json_encode($timb).'</div>';
                        $this->marcature[$cc['ID_coll']]=$timb;
    
                    //}
                }
            }
        }

        //$this->log[]=$this->timbFunc->getLog();
    }

    function drawCollTl($tarrRif,$coll,$reparto) {

        echo '<div style="width:95%;margin-left:2%;" >';

            $presenza=false;

            if ($tarrRif) {

                $presenza=$tarrRif->getPresenza();
                if ($presenza['actual']==0) $presenza=false;

                if ($presenza) {

                    $cfg=array(
                        "titolo"=>"NO",
                        "legenda"=>"NO",
                        "orari"=>"SI",
                        "corpo"=>"SI",
                        "valore"=>"NO",
                        "totale"=>"NO",
                        "totale_bk"=>"NO",
                        "totale_tag"=>"NO",
                        "flagTot"=>"NO",
                        "sottotitolo"=>"NO",
                        "popup"=>"NO"
                    );
                    $tarrRif->setSezioni($cfg);

                    $tarrRif->setRange($this->globalTrim);

                    $tarrRif->setMark(mainFunc::gab_stringtomin(date('H:i')));

                    $tempry=array(
                        "sub"=>'actual',
                        "scala"=>array(
                            "min"=>'red',
                            "0"=>'red',
                            "1"=>'green',
                            "2"=>'green',
                            "3"=>'green',
                            "4"=>'green'
                        ),
                        "limite"=>'VAL'
                    );

                    $tarrRif->drawHead(array());
                    $tarrRif->drawSubs('actual',$tempry);
                }
            }

        echo '</div>';

        if (!$presenza) {
            echo '<div style="border:1px solid black;background-color:#ffccd3;font-weight:bold;padding-left:10px;margin-bottom:10px;margin-top:10px;" >';
                echo 'Tecnico non presente';
            echo '</div>';

            //se non esiste un'ultima timbratura oppure è chiusa in una data precedente ESCI
            if (!isset($this->marcature[$coll['ID_coll']]) || ( $this->marcature[$coll['ID_coll']]['d_fine']!="" && $this->marcature[$coll['ID_coll']]['d_fine']!=date('Ymd') )) return;
        }

        //////////////////////////////////////////////////////////////

        $timb=false;

        if (isset($this->marcature[$coll['ID_coll']])) {
            $timb=$this->marcature[$coll['ID_coll']];
        }

        /*if (isset($this->marcature[$coll['ID_coll']])) {

            $timb=$this->marcature[$coll['ID_coll']];

            ////////////////////////////////////////////////////
            //se c'è una timeline attiva e la marcatura è aperta
            //verificare attraverso ALAN se esiste una timbratura maggiore dell'inizio della marcatura 
            //verificare l'orario di fine turno con $tarrRif ed impostare la chiusura nel DB
            //aggiornare l'array $timb e proseguire

            if ($timb['d_fine']=="") {

                $tl=false;

                //se la marcatura è in data odierna la timeline è la stessa usata per disegnare il turno del tecnico
                if ($timb['d_inizio']==date('Ymd')) $tl=$tarrRif;

                else {
                    if (array_key_exists($timb['d_inizio'],$this->intarray)) {
                        $tl=$this->intarray[$timb['d_inizio']];
                    }
                    else {
                        $tempConfig=$this->infoIntervallo;
                        $tempConfig['data_i']=$timb['d_inizio'];
                        $tempConfig['data_f']=$timb['d_inizio'];

                        $tarr[$this->param['wsp_officina']]=array();

                        $tempint=new quartetIntervallo($tempConfig,$tarr,$this->galileo);
                        $tempint->calcola();
                        $tempint->calcolaIntTot();
                        $this->intarray[$timb['d_inizio']]=$tempint->getCollTot('subs',$reparto,$coll['ID_coll']);
                        $tl=$this->intarray[$timb['d_inizio']];
                    }
                }

                //la correzione deve essere fatta sia che esista o no un turno per il tecnico nel giorno
                //if ($tl) {
                    $timb=$this->timbFunc->correggiAlan($timb,$tl);
                    $timb['fittizio']=$this->marcature[$coll['ID_coll']]['fittizio'];
 
                    $this->marcature[$coll['ID_coll']]=$timb;

                //}
            }
        }*/

        echo '<div style="height:20px;">';
            //lamentati abbinati

        echo '</div>';

        if (!isset($this->marcature[$coll['ID_coll']]) || !$this->marcature[$coll['ID_coll']]) {
            echo '<div style="border:1px solid black;background-color:#ffccd3;font-weight:bold;padding-left:10px;margin-bottom:10px;" >';
                echo 'non ci sono ordini aperti';
            echo '</div>';
        }

        elseif (!array_key_exists($this->marcature[$coll['ID_coll']]['num_rif_movimento'],$this->odl) ) {
            echo '<div style="border:1px solid black;background-color:#ffccd3;font-weight:bold;padding-left:10px;margin-bottom:10px;" >';
                echo 'non ci sono ordini aperti';
            echo '</div>';
        }

        else {

            if ($timb['d_fine']!="") {
                //echo '<div style="font-weight:bold;color:red;margin-bottom:5px;" >Nessuna Marcatura Aperta</div>';
                echo '<div style="border:1px solid black;background-color:#eddb83;font-weight:bold;padding-left:10px;margin-bottom:10px;" >';
                    echo 'Nessuna Marcatura iniziata';
                echo '</div>';
            }

            $primo=true;
            $tantobj=false;
            //$odl=$this->odl[$this->marcature[$coll['ID_coll']]['num_rif_movimento']];

            if (substr($timb['des_note'],0,3)=='ANT' || (substr($timb['des_note'],0,3)=='PUL' && $timb['cod_movimento']=='OOS') ) {
                $tantobj=json_decode(substr($timb['des_note'],3),true);
                if ($tantobj) {
                    $todl=$this->getOdl($tantobj['rif'],$timb['dms']);
                    $odl=$todl[$tantobj['rif']];
                }
                else $odl=$this->odl[$this->marcature[$coll['ID_coll']]['num_rif_movimento']];
            }
            else {
                $odl=$this->odl[$this->marcature[$coll['ID_coll']]['num_rif_movimento']];
            }

            //echo json_encode($tantobj);

            ////////////////////////////////////////////////////

            //if ($tarrRif) {

                foreach ($odl as $lam=>$l) {

                    /* {"num_rif_movimento":"1360428","cod_inconveniente":"A","cod_operaio":"18","num_riga":1,"d_inizio":"20210903","o_inizio":"15:23","d_fine":"","o_fine":"","qta_ore_lavorate":".00","des_note":"","num_rif_riltem":331694,"dms":"concerto"}

                    {"A":{"dat_inserimento":"20210811","cod_officina":"PV","cod_officina_prenotazione":"PV","cod_stato_commessa":"RP","cod_movimento":"OOP","rif":"1360428","num_commessa":"119220","ind_preventivo":"N","cod_tipo_trasporto":"ASP","num_riga":1,"lam":"A","des_riga":"ISPEZIONE CAMBIO OLIO","ind_stato":"L","ind_chiuso":"N","d_pren":"20210903:15:45","d_ricon":"20210903:17:45","d_entrata":"20210903:15:06","d_fine":"xxxxxxxx:xx:xx","ore":"1.75","ore_isla":"1.50","subrep":"MECPV","d_inc":"20210903:15:45","d_fine_inc":"20210903:17:30","d_fix":"xxxxxxxx:xx:xx","distribuzione":"DIS","prog_spalm":0,"d_spalm":"xxxxxxxx:xx:xx","ore_spalm":".00","dat_prenotazione_inc":{"date":"2021-09-03 15:45:00.000000","timezone_type":3,"timezone":"Europe\/Berlin"},"dat_prenotazione_det":null,"num_rif_veicolo":"5041818","num_rif_veicolo_progressivo":1,"cod_anagra_util":"95254","cod_anagra_intest":"","cod_anagra_loc":"","cod_anagra_fattura":"","cod_accettatore":"m.ghiandoni","mat_targa":"GA089ND","mat_telaio":"WVWZZZAUZLP024001","cod_veicolo":"BQ13FZ","des_veicolo":"Golf 1.5 TGI BlueMotion Technology Business 96 kW\/ 130 CV DSG","util_ragsoc":"FULVI UGOLINI NICOLA","intest_ragsoc":""},"C":{"dat_inserimento":"20210903","cod_officina":"PV","cod_officina_prenotazione":"PV","cod_stato_commessa":"RP","cod_movimento":"OOP","rif":"1360428","num_commessa":"119220","ind_preventivo":"N","cod_tipo_trasporto":"ASP","num_riga":15,"lam":"C","des_riga":" - Servizio Igienizzazione","ind_stato":"L","ind_chiuso":"N","d_pren":"20210903:15:45","d_ricon":"20210903:17:45","d_entrata":"20210903:15:06","d_fine":"xxxxxxxx:xx:xx","ore":".00","ore_isla":".00","subrep":"","d_inc":"xxxxxxxx:xx:xx","d_fine_inc":"xxxxxxxx:xx:xx","d_fix":"xxxxxxxx:xx:xx","distribuzione":"","prog_spalm":0,"d_spalm":"xxxxxxxx:xx:xx","ore_spalm":".00","dat_prenotazione_inc":null,"dat_prenotazione_det":null,"num_rif_veicolo":"5041818","num_rif_veicolo_progressivo":1,"cod_anagra_util":"95254","cod_anagra_intest":"","cod_anagra_loc":"","cod_anagra_fattura":"","cod_accettatore":"m.ghiandoni","mat_targa":"GA089ND","mat_telaio":"WVWZZZAUZLP024001","cod_veicolo":"BQ13FZ","des_veicolo":"Golf 1.5 TGI BlueMotion Technology Business 96 kW\/ 130 CV DSG","util_ragsoc":"FULVI UGOLINI NICOLA","intest_ragsoc":""}}
                    */

                    if ($primo) {

                        if ($timb['des_note']=="" || substr($timb['des_note'],0,3)=='PUL' || substr($timb['des_note'],0,3)=='ANT') {
                            echo $this->drawPrimo($l);
                        } 

                        $primo=false;
                    }

                    //////////////////////////////////////
                    if ($l['cod_movimento']=='OOS' && $l['lam']!=$timb['cod_inconveniente']) continue;
                    //////////////////////////////////////

                    $color='white';

                    /*$t=$this->odlFunc->getAddebito($l,$l['dms']);

                    ///////////////////////////////////////////////
                    $this->odl[$this->marcature[$coll['cod_operaio']]['num_rif_movimento']][$lam]['addebito']=$t;
                    //////////////////////////////////////////////*/

                    if ($l['nebulaAddebito']) {
                        $color=$l['nebulaAddebito']['colore'];
                    }

                    echo '<div style="position:relative;margin-top:2px;margin-bottom:2px;border:1px solid black;padding:2px;box-sizing:border-box;background-color:'.$color.';';
                        if ($timb['d_fine']!="") {
                            echo 'opacity:0.4;';
                        }
                    echo '">';

                        echo '<div style="height:18px;line-height:18px;';
                            if ($l['cod_movimento']=='OOS' || (substr($timb['des_note'],0,3)=='PUL' && $l['rif']==$timb['num_rif_movimento'] && $l['lam']==$timb['cod_inconveniente']) || (substr($timb['des_note'],0,3)=='ANT' && $l['rif']==$tantobj['rif'] && $l['lam']==$tantobj['lam']) ) echo 'background-color:#bfa838;';
                            else if ($tantobj && substr($timb['des_note'],0,3)=='PUL' && $l['rif']==$tantobj['rif'] && $l['lam']==$tantobj['lam']) echo 'background-color:#bfa838;';
                        echo '">';

                            echo '<div style="display:inline-block;width:5%;vertical-align:top;" >';
                                echo $lam.' - ';
                            echo '</div>';

                            echo '<div style="display:inline-block;width:75%;" >';
                                if ($tantobj && substr($timb['des_note'],0,3)=='ANT' && $l['rif']==$tantobj['rif'] && $l['lam']==$tantobj['lam']) {
                                    echo '<div><span style="font-weight:bold;font-size:0.9em;">Anticipato -&nbsp;</span>'.utf8_encode(substr($l['des_riga'],0,35)).'</div>';
                                }
                                else if($tantobj && substr($timb['des_note'],0,3)=='PUL' && $l['rif']==$tantobj['rif'] && $l['lam']==$tantobj['lam']) {
                                    echo '<div><span style="font-weight:bold;font-size:0.9em;">Anticipato Pulizia -&nbsp;</span>'.utf8_encode(substr($l['des_riga'],0,30)).'</div>';
                                }
                                else if (substr($timb['des_note'],0,3)=='PUL' && $l['rif']==$timb['num_rif_movimento'] && $l['lam']==$timb['cod_inconveniente']) {
                                    echo '<div><span style="font-weight:bold;font-size:0.9em;">Pulizia -&nbsp;</span>'.utf8_encode(substr($l['des_riga'],0,35)).'</div>';
                                }
                                else {
                                    echo '<div>'.utf8_encode(substr($l['des_riga'],0,45)).'</div>';
                                }
                            echo '</div>';

                            echo '<div style="display:inline-block;width:20%;font-weight:bold;font-size:0.8em;vertical-align:top;" >';
                                $t=$this->odlFunc->getStatoLam($l,$l['dms']);
                                if ($t) {
                                    echo $t;
                                }
                            echo '</div>';

                        echo '</div>';

                        if ($timb && $l['rif']==$timb['num_rif_movimento'] && $l['lam']==$timb['cod_inconveniente']) {

                            echo '<div style="height:25px;line-height:25px;">';

                                echo '<div style="position:relative;display:inline-block;width:7%;vertical-align:top;height:25px;" >';

                                    if ($timb['d_fine']!="") {
                                        echo '<img style="position:relative;width:20px;height:20px;top:50%;transform:translate(0%,-50%);" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/workshop/img/icon_pause.png" />';
                                    }
                                    else {
                                        echo '<img style="position:relative;width:20px;height:20px;top:50%;transform:translate(0%,-50%);" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/workshop/img/icon_play.png" />';
                                    }
                                    
                                echo '</div>';

                                echo '<div style="position:relative;display:inline-block;width:30%;vertical-align:top;height:25px;" >';
                                    echo mainFunc::gab_todata($timb['d_inizio']).'<span style="margin-left:5px;font-weight:bold;">'.$timb['o_inizio'].'</span>';
                                echo '</div>';

                                echo '<div style="position:relative;display:inline-block;width:7%;vertical-align:top;height:25px;" >';
                                    echo '<img style="position:relative;width:20px;height:15px;top:50%;transform:translate(0%,-50%);" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/main/img/blackarrowR.png" />';
                                echo '</div>';
                                
                                echo '<div style="position:relative;display:inline-block;width:30%;vertical-align:top;height:25px;" >';
                                    if ($timb['d_fine']!="") {
                                        echo mainFunc::gab_todata($timb['d_fine']).'<span style="margin-left:5px;font-weight:bold;">'.$timb['o_fine'].'</span>';
                                    }
                                echo '</div>';

                                echo '<div style="position:relative;display:inline-block;width:26%;vertical-align:top;height:25px;text-align:center;" >';

                                    //se NON è una marcatura speciale
                                    if ($timb['des_note']=='') {

                                        //se la marcatura è chiusa
                                        if ($timb['d_fine']!="" && $timb['ind_chiuso']=='N') {
                                            if ($this->param['visuale']=="personale" || $this->param['wsp_tecnico']!="") {
                                                echo '<img style="position:relative;width:50%;height:23px;top:50%;transform:translate(0%,-50%);cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/workshop/img/restart.png" ';
                                                    //if ($this->param['visuale']=="personale" || $this->param['wsp_tecnico']!="") echo ' onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].openTimbratura(\'restart\');" ';
                                                    //else echo ' onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].openTecnico(\''.$coll['ID_coll'].'\',\'restart\');" ';
                                                    echo ' onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].restartMarcatura(\''.$timb['dms'].'\',\''.$timb['num_rif_riltem'].'\');" ';
                                                echo ' />';
                                            }
                                        }
                
                                        elseif ($timb['d_fine']=="") {

                                            if (isset($coll['ability']['EXT']) && $coll['ability']['EXT']==1) {
                                                echo '<img style="position:relative;width:50%;height:23px;top:50%;transform:translate(0%,-50%);cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/workshop/img/fine.png" ';
                                                    echo ' onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].fineMarcatura(\''.$timb['dms'].'\',\''.$timb['num_rif_riltem'].'\');" ';
                                                    //else echo ' onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].openTecnico(\''.$coll['ID_coll'].'\',\'fine\');" ';
                                                echo '/>';
                                            }
                                            else {
                                                echo '<img style="position:relative;width:50%;height:23px;top:50%;transform:translate(0%,-50%);cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/workshop/img/stop.png" ';
                                                    if ($this->param['visuale']=="personale" || $this->param['wsp_tecnico']!="") echo ' onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].openTimbratura(\'stop\');" ';
                                                    else echo ' onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].openTecnico(\''.$coll['ID_coll'].'\',\'stop\');" ';
                                                echo '/>';
                                            }

                                        }

                                    }

                                echo '</div>';

                            echo '</div>';
                        }

                        /*if ($timb['d_fine']!="") {
                            echo '<div style="position:absolute;top:0px;left:0px;width:100%;height:100%;background-color:white;opacity:0.6;z-index:10;" ></div>';
                        }*/

                    echo '</div>';
                    /////////////////////////////////////

                }

            //}

        }

        /*echo '<div>';
                echo json_encode($coll);
        echo '</div>';*/

       /* echo '<div>';
            if (isset($timb)) {
                echo json_encode($timb);
            }
        echo '</div>';

        echo '<div>';
            echo json_encode($this->timbFunc->getLog());
        echo '</div>';
        */

        /*echo '<div>';
            if (isset($this->odl[$this->marcature[$coll['cod_operaio']]['num_rif_movimento']])) {
                echo json_encode($this->odl[$this->marcature[$coll['cod_operaio']]['num_rif_movimento']]);
            }
        echo '</div>';*/
        
        /*if ($tarrRif) {
            echo '<div>';
                echo json_encode($tarrRif->getTl());
            echo '</div>';
        }*/

    }

    function drawActualLam($coll,$reparto) {

        $timb=$this->marcature[$coll['ID_coll']];

        //se la marcatura è chiusa non scrivere
        if ($timb['d_fine']!='') return;

        $stat=array(
            "flag"=>false,
            "ore_prenotate"=>0,
            "pos_lavoro"=>0,
            "ore_marcate"=>0,
            "tecnici"=>array()
        );

        //se esiste l'ordine di lavoro valido (aperto) ma la marcatura NON è speciale
        if (isset($this->odl[$this->marcature[$coll['ID_coll']]['num_rif_movimento']]) && $timb['des_note']=="") {
        
            $res=$this->odlFunc->getLamStats($timb['num_rif_movimento'],$timb['cod_inconveniente'],$timb['dms']);

            if ($res) {
                $pf=$this->odlFunc->getPiattaforma();
                $fid=$this->galileo->preFetchPiattaforma($pf,$res);

                while($row=$this->galileo->getFetchPiattaforma($pf,$fid)) {
                    $stat['flag']=true;
                    $stat['ore_prenotate']=$row['ore_prenotate_totali'];
                    $stat['pos_lavoro']=$row['ore_fatturate_totali'];
                    $stat['ore_marcate']+=$row['ore_lavorate'];
                    $stat['tecnici'][$row['cod_operaio']]=$row;
                }
            }
        }
 
        echo '<div style="margin-top:10px;border-bottom:1px solid #777777;">';

            echo '<table style="width:100%;font-size:0.9em;margin-bottom:10px;border-collapse:collapse;">';

                echo '<colgroup>';
                    echo '<col span="3" style="width:20%;" />';
                    echo '<col span="1" style="width:25%;" />';
                    echo '<col span="1" style="width:15%;" />';
                echo '</colgroup>';

                echo '<thead style="">';
                    echo '<tr>';
                        echo '<th style="border:1px solid black;">Prenotato</th>';
                        echo '<th style="border:1px solid black;">Pos. Lav.</th>';
                        echo '<th style="border:1px solid black;">Marcato</th>';
                        echo '<th style="border:1px solid black;">Tecnici</th>';
                        echo '<th style="border:1px solid black;">Eff.</th>';
                    echo '</tr>';
                echo '</thead>';

                echo '<tbody style="text-align:center;">';
                    echo '<tr>';

                        if (!$stat['flag']) {
                            echo '<td colspan="5" style="color:red;" >Statistica non disponibile</td>';
                        }
                        else {

                            $temptd="";

                            foreach ($stat['tecnici'] as $cod=>$c) {

                                //COD è il codice operaio proprio del DMS da cui è stata tratta la statistica 
                                $tempcod=$this->timbFunc->getRef($cod,$timb['dms']);

                                $temptd.='<div>';
                                    $xxx=($tempcod)?($tempcod.'<span style="font-size:0.8em;">&nbsp;('.$cod.')</span>'):'???';
                                    $temptd.='<div style="position:relative;display:inline-block;vertical-align:top;width:30%;">'.$xxx.'</div>';

                                    //calcolo ore lavorate attuali
                                    if (array_key_exists($tempcod,$this->marcature)) {

                                        if ($this->marcature[$tempcod]['d_fine']=="") {

                                            $now=date('H:i');
                                            if ($now>$this->marcature[$tempcod]['o_inizio']) {
                                                $delta=mainFunc::gab_delta_min($this->marcature[$tempcod]['o_inizio'],$now);
                                                $c['ore_lavorate']+=$delta/60;
                                                $stat['ore_marcate']+=$delta/60;
                                            }
                                        }
                                    }


                                    $temptd.='<div style="position:relative;display:inline-block;vertical-align:top;width:50%;text-align:right;">'.number_format($c['ore_lavorate'],2,'.','').'</div>';
                                    $temptd.='<div style="position:relative;display:inline-block;vertical-align:top;width:20%;">';
                                        //se il tecnico ha una marcatura estratta
                                        if (array_key_exists($tempcod,$this->marcature)) {
                                            //se la marcatura è del lamentato che stiamo esaminando (potrebbero essere marcati più tecnici e non solo l'utente loggato)
                                            if ($this->marcature[$tempcod]['num_rif_movimento']==$timb['num_rif_movimento'] && $this->marcature[$tempcod]['cod_inconveniente']==$timb['cod_inconveniente']) {
                                                if ($this->marcature[$tempcod]['d_fine']=="") {
                                                    $temptd.='<img style="position:relative;width:15px;height:15px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/workshop/img/icon_play.png" />';
                                                }
                                                else {
                                                    //echo '<img style="position:relative;width:15px;height:15px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/workshop/img/icon_pause.png" />';
                                                }
                                            }
                                        }
                                    $temptd.='</div>';
                                    $temptd.='</div>';
                            }

                            echo '<td>'.number_format($stat['ore_prenotate'],2,'.','').'</td>';
                            echo '<td>'.number_format($stat['pos_lavoro'],2,'.','').'</td>';
                            echo '<td>'.number_format($stat['ore_marcate'],2,'.','').'</td>';

                            echo '<td>';
                                echo $temptd;
                            echo '</td>';

                            echo '<td>'.number_format(($stat['ore_marcate']==0)?0:(($stat['pos_lavoro']/$stat['ore_marcate'])*100),2,'.','').'%</td>';
                        }
                    echo '</tr>';
                echo '</tbody>';

            echo '</table>';

        echo '</div>';

        if (!isset($this->odl[$this->marcature[$coll['ID_coll']]['num_rif_movimento']]) || $timb['des_note']!="") return;
        
        if ($this->param['wsp_tecnico']=="") {
            ///////////////////////////////////////
            //CREARE UN OGGETTO PITLANE APPOSITO PER IL DIALOGO CON IL MAGAZZINO
            echo '<div style="margin-top:10px;position:relative;height:60px;border-bottom:5px solid #777777;">';

                echo '<div style="margin-bottom:5px;">';

                    echo '<div style="position:relative;display:inline-block;width:85%;vertical-align:top;" >';
                        echo '<textarea style="width:100%;height:90%;resize:none;font-size:1.1em;"></textarea>';
                    echo '</div>';

                    echo '<div style="position:relative;display:inline-block;width:15%;height:100%;vertical-align:top;" >';
                        echo '<img style="position:relative;width:60%;margin-left:15%;cursor:pointer;top:50%;transform:translate(0%,-50%);" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/workshop/img/campana.png" />';
                        //echo '<img style="position:relative;width:30%;margin-left:15%;cursor:pointer;top:50%;transform:translate(0%,-50%);" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/workshop/img/preventivo.png" />';
                    echo '</div>';

                echo '</div>';

            echo '</div>';
            ///////////////////////////////////////
        }

        echo '<div style="position:relative;margin-top:10px;margin-bottom:15px;">';
            
            //LE FOTO SI RIFERISCONO ALL'INTERO ORDINE DI LAVORO
            //$fotone=new workshopFoto('lamAllegati',$timb['num_rif_movimento'],'10.55.99.54');
            //echo $fotone->draw();

            /*echo '<script type="text/javascript">';
                echo 'window._fotone_lamAllegati_obj.formatta();';
            echo '</script>';*/

        echo '</div>';

    }

    function drawLavorazione($timb,$odl) {

        $css=array(
            "width"=>"100%",
            "font-weight"=>"bold",
            "font-size"=>"1.3em",
            "margin-top"=>"2px",
            "text-align"=>"center"
        );

        $css2=array(
            "width"=>"15px",
            "height"=>"15px",
            "top"=>"50%",
            "transform"=>"translate(0%,-50%)",
            "right"=>"5px"
        );

        $divo2=new Divo('workshop2','5%','96%',true);

        $divo2->setBk('#f1c4a5');

        $divo2->setChkimgCss($css2);

        //foreach ($odl as $lam=>$l) {

            $txt='<div style="width:98%;">';

                ob_start();
                    echo '<div style="border-bottom:5px solid #777777;margin-top:5px;margin-bottom:5px;" >';

                        echo '<div>Header ordine di lavoro</div>';
            
                    echo '</div>';
            
                    echo '<div style="border-bottom:5px solid #777777;margin-top:5px;margin-bottom:5px;" >';
            
                        echo '<div>Link programmi esterni (GDM)</div>';
                        echo '<div>Link programmi esterni (QCHECK)</div>';
            
                    echo '</div>';
            
                    echo '<div style="border-bottom:5px solid #777777;margin-top:5px;margin-bottom:5px;" >';
            
                        echo '<div>Note lamentato originali + Note responsabili in preparazione e/o lavorazione</div>';
                        echo '<div>Campo per inserire note che cambiano colore quando vengono spuntate</div>';
            
                    echo '</div>';
            
                    echo '<div style="border-bottom:5px solid #777777;margin-top:5px;margin-bottom:5px;" >';
            
                        echo '<div>Fasi di lavorazione in base alla marca da spuntare (anche se non necessarie)</div>';
                        echo '<div>difetto meccanico</div>';
                        echo '<div>guasto non presente</div>';
                        echo '<div>ricerca guidata</div>';
                        echo '<div>controllo di funzionamento</div>';
                        echo '<div>DISS</div>';
                        echo '<div>TPI</div>';
                        echo '<div>Invio protocollo</div>';
            
                    echo '</div>';

                    echo '<div style="border-bottom:5px solid #777777;margin-top:5px;margin-bottom:5px;" >';
            
                        echo '<div>Note lavorazione</div>';
                        echo '<div>Campo per inserire note (private e PUBBLICHE per il cliente)</div>';
            
                    echo '</div>';

                    echo '<div style="border-bottom:5px solid #777777;margin-top:5px;margin-bottom:5px;" >';
            
                        echo '<div>Strumenti</div>';
                        echo '<div>Check List ufficiale (PDF da modificare)</div>';
            
                    echo '</div>';
            
                    echo '<div style="border-bottom:5px solid #777777;margin-top:5px;margin-bottom:5px;" >';
            
                        echo '<div>Preventivi</div>';
                        echo '<div>Campo Pit Lane per richiesta preventivi</div>';
                        echo '<div>Richiesta attrezzi speciali</div>';
                        echo '<div>Ricambi ordinati / ricambi da rendere</div>';
            
                    echo '</div>';
            
                    
                $txt.=ob_get_clean();

            $txt.='</div>';

            //$divo2->add_div($lam,'black',0,"",$txt,($timb['cod_inconveniente']==$l['lam'])?1:0,$css);
            $divo2->add_div('Pratica','black',0,"",$txt,0,$css);

            $telaio="";

            foreach ($odl as $lam=>$l) {
                $telaio=$l['mat_telaio'];
                break;
            }

            $txt='<div id="workshop_gdm" ></div>';

            if ($telaio && $telaio!="") {
                $txt.='<script type="text/javascript">window._nebulaWS.getGDM(\''.$telaio.'\');</script>'; 
            }

            $divo2->add_div('GDM','black',0,"",$txt,0,$css);

        //}

        unset($txt);

        $divo2->build();

        $divo2->draw();

    }

    function drawTecnico() {

        if (array_key_exists($this->param['wsp_tecnico'],$this->collaboratori)) {
            $coll=$this->collaboratori[$this->param['wsp_tecnico']];
        }
        else {
            echo 'collaboratore non abilitato!';
            return;
        }

        if (array_key_exists($coll['ID_coll'],$this->marcature)) {
            $this->getOdl($this->marcature[$coll['ID_coll']]['num_rif_movimento'],$this->marcature[$coll['ID_coll']]['dms']);
        }

        echo '<div style="position:relative;display:inline-block;width:50%;height:98%;padding:3px;border-right:1px solid black;box-sizing:border-box;vertical-align:top;" >';

            ///////////////////////////
            $txt='<div style="width:98%;overflow:scroll;overflow-x:hidden;" >';

                $txt.='<div style="width:98%;padding:2px;box-sizing:border-box;margin-top: 10px;">';

                    ob_start();
                        echo '<div style="font-weight:bold;color: white;height: 25px;line-height: 25px;padding-left: 5px;margin-bottom: 5px;" >';

                            echo '<div style="position:relative;display:inline-block;width:10%;vertical-align:top;height:25px;text-align:center;" >';
                                echo '<img style="position:relative;width:25px;height:25px;top:50%;transform:translate(0%,-50%);cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/workshop/img/back.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].closeTecnico();" />';
                            echo '</div>';

                            echo '<div style="position:relative;display:inline-block;width:70%;vertical-align:top;height:25px;background-color: #444444;" >';
                                echo '<span style="margin-left:5px;">('.$coll['ID_coll'].') '.$coll['cognome'].' '.$coll['nome'].'</span>';
                                echo '<img style="position:absolute;width:20px;height:20px;top:2px;right:5px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/workshop/img/refresh.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].wspRefresh();" />';
                            echo '</div>';
                
                            echo '<div style="position:relative;display:inline-block;width:20%;vertical-align:top;height:25px;text-align:right;" >';
                                echo '<img style="position:relative;width:60%;height:25px;top:50%;transform:translate(0%,-50%);cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/workshop/img/nuova.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].openTimbratura(\'nuova\');" />';
                                //echo '<img style="position:relative;width:40px;height:30px;top:50%;transform:translate(0%,-50%);" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/workshop/img/tecnico.png" />';
                                //echo '<div class="divButton" style="position:relative;left:50%;top:50%;transform:translate(-50%,-50%);height:21px;">Nuova</div>';
                            echo '</div>';
                
                        echo '</div>';
                        
                        $tarrRif=$this->wspIntervallo->getCollTot('subs',$this->param['wsp_officina'],$coll['ID_coll']);
                        $this->drawCollTl($tarrRif,$coll,$this->param['wsp_officina']);

                    $txt.=ob_get_clean();

                $txt.='</div>';

                $txt.='<div style="position:relative;width:98%;min-height:60px;margin-top:10px;">';

                    if (isset($this->marcature[$coll['ID_coll']])) {
                        ob_start();
                            $this->drawActualLam($coll,$this->param['wsp_officina']);
                        $txt.=ob_get_clean();
                    }

                $txt.='</div>';

            $txt.='</div>';

            echo $txt;

        echo '</div>';
        
        echo '<div style="position:relative;display:inline-block;width:50%;height:98%;padding:3px;box-sizing:border-box;vertical-align:top;" >';

            if ($this->param['wsp_timb']!="") {
                $this->drawMarcatura($coll,$tarrRif);
            }

            elseif (isset($this->marcature[$coll['ID_coll']])) {

                //echo json_encode($this->marcature[$coll['ID_coll']]);

                if (isset($this->odl[$this->marcature[$coll['ID_coll']]['num_rif_movimento']]) && $this->marcature[$coll['ID_coll']]['fittizio']==0) {

                    $this->drawLavorazione($this->marcature[$coll['ID_coll']],$this->odl[$this->marcature[$coll['ID_coll']]['num_rif_movimento']]);
                }
                else if (substr($this->marcature[$coll['ID_coll']]['des_note'],0,3)=='PRV') {

                    $tjs=json_decode(substr($this->marcature[$coll['ID_coll']]['des_note'],3),true);

                    if ($tjs) {
                        $this->getOdl($tjs['rif'],$this->marcature[$coll['ID_coll']]['dms']);
                        $this->drawLavorazione($this->marcature[$coll['ID_coll']],$this->odl[$tjs['rif']]);
                    }
                }
            }

        echo '</div>';

    }

    function drawMarcatura($coll,$tl) {

        $tempactual="";
        $timb=false;

        if ($coll['ID_coll']!=$this->id->getCollID()) {
            $aby=$this->loggedAllow;
        }
        else $aby=$coll['ability'];

        echo '<div style="text-align:right;height:2px;">';
            echo '<img style="width:25px;margin-right:20px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/workshop/img/chiudi.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].closeTimbratura();" />';
        echo '</div>';

        if ( isset($this->marcature[$coll['ID_coll']]) ) {

            $timb=$this->marcature[$coll['ID_coll']];

            $presenza=true;

            //$tempp=$tl->getPresenza();
            //if($tempp['actual']==0) $presenza=false;

            if ($presenza && isset($this->odl[$this->marcature[$coll['ID_coll']]['num_rif_movimento']])) {

                if ($timb['d_fine']=="" && $this->odl[$this->marcature[$coll['ID_coll']]['num_rif_movimento']][$timb['cod_inconveniente']]['cod_movimento']!='OOS') {

                    echo '<div style="text-align:center;font-size:1.2em;margin-top:10px;border-bottom:5px solid #777777;">';

                        echo '<div style="font-weight:bold;" >Nuovo stato per il Lamentato corrente</div>';

                        echo '<div style="margin-top:10px;margin-bottom:10px;" >';

                            echo '<select id="wsp_timbratura_lastlam" style="font-size:1.2em;" >';
                                
                                echo '<option value="">Seleziona...</option>';

                                foreach ($this->odlFunc->getStatiLam($timb['dms']) as $k=>$v) {
                                    echo '<option value="'.$k.'">'.$v.'</option>';
                                }

                            echo '</select>';
                        echo '</div>';

                    echo '</div>';

                }
                else {
                    echo '<div>';
                        echo '<input id="wsp_timbratura_lastlam" type="hidden" value="jump" />';
                    echo '</div>';
                }

                //preparazione dei lamentati per dopo
                if ($this->odl[$this->marcature[$coll['ID_coll']]['num_rif_movimento']][$timb['cod_inconveniente']]['cod_movimento']!='OOS') {

                    foreach ($this->odl[$this->marcature[$coll['ID_coll']]['num_rif_movimento']] as $lam=>$l) {

                        if ($lam==$timb['cod_inconveniente']) continue;

                        $tempactual.=$this->drawNextOdl($l,$coll['ID_coll']);

                    }

                    //lamentato PROVA
                    if ($coll['ability']['PRV']==1) $tempactual.=$this->drawPRV($l,$timb,$coll['ID_coll']);
                }

            }

            else {
                echo '<div>';
                    echo '<input id="wsp_timbratura_lastlam" type="hidden" value="jump" />';
                echo '</div>';
            }

        }

        else {
            echo '<div>';
                echo '<input id="wsp_timbratura_lastlam" type="hidden" value="jump" />';
            echo '</div>';
        }

        echo '<div style="margin-top:15px;">';

            echo '<div style="font-weight:bold;text-align:center;font-size:1.5em;" >Nuova Marcatura</div>';

            echo '<div style="margin-top:10px;text-align:center;border:2px solid #af7900;padding:5px;">';

                $w='50';

                //echo '<div>'.json_encode($aby).'</div>';

                echo '<table style="text-align:center;font-size:0.8em;font-weight:bold;transform: translate(-50%, 0px);left: 50%;position: relative;">';

                    if ($tl) {
                        $valido=$tl->checkFlag(mainFunc::gab_stringtomin(date('H:i')));
                    }
                    else $valido=false;

                    echo '<tr>';

                        if ($valido) {

                            if ($aby['ATT']==1 && (!$timb || $timb['d_fine']!="" || ($timb && $timb['d_fine']=="" && $timb['des_note']=="") ) ) {
                                //se la timbratura è attiva NON deve essere speciale
                                echo '<td>';    
                                    echo '<img style="width:'.$w.'px;margin-left:10px;margin-right:10px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/workshop/img/speciale/ATT.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].special_ATT(\''.$coll['ID_coll'].'\',\''.($timb?$timb['num_rif_riltem']:'').'\',\''.$this->param['wsp_officina'].'\');" />';
                                    echo '<div style="text-align:center;">Attesa</div>';
                                echo '</td>';
                            }

                            if ($aby['PUL']==1 && $timb && $timb['d_fine']=="" && $timb['des_note']=="") {
                                echo '<td>';
                                    echo '<img style="width:'.$w.'px;margin-left:10px;margin-right:10px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/workshop/img/speciale/PUL.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].special_PUL(\''.$coll['ID_coll'].'\',\''.$timb['num_rif_riltem'].'\',\''.$this->param['wsp_officina'].'\');" />';
                                    echo '<div style="text-align:center;">Pulizia</div>';
                                echo '</td>';
                            }

                            /*echo '<td>';
                                echo '<img style="width:'.$w.'px;margin-left:10px;margin-right:10px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/workshop/img/speciale/PRV.png" />';
                            echo '</td>';*/
                            $tempspe=($timb && $timb['d_fine']=="")?substr($timb['des_note'],0,3):'';

                            if ($aby['SER']==1 && $tempspe!="SER") {
                                echo '<td>';
                                    echo '<img style="width:'.$w.'px;margin-left:10px;margin-right:10px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/workshop/img/speciale/SER.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].special_SER(\''.$coll['ID_coll'].'\',\''.($timb?$timb['num_rif_riltem']:'').'\',\''.$this->param['wsp_officina'].'\');" />';
                                    echo '<div style="text-align:center;">Servizio</div>';
                                echo '</td>';
                            }
                        }

                        if ($aby['EXT']==1 && $timb && $timb['d_fine']=="") {
                            echo '<td>';
                                echo '<img style="width:'.$w.'px;margin-left:10px;margin-right:10px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/workshop/img/speciale/EXT.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].special_EXT(\''.$coll['ID_coll'].'\',\''.$timb['num_rif_riltem'].'\',\''.$this->param['wsp_officina'].'\');"/>';
                                echo '<div style="text-align:center;">Fine</div>';
                            echo '</td>';
                        }

                        /*if ($aby['ANT']==1 && $timb && $timb['d_fine']=="" && ($timb['des_note']=="" || (substr($timb['des_note'],0,3)=='PUL') && $timb['cod_movimento']!='OOS' ) ) {
                            echo '<td>';
                                echo '<img style="width:'.$w.'px;margin-left:10px;margin-right:10px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/workshop/img/speciale/ANT.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].special_ANT(\''.$coll['ID_coll'].'\',\''.$timb['num_rif_riltem'].'\',\''.$this->param['wsp_officina'].'\');"/>';
                                echo '<div style="text-align:center;">Anticipo</div>';
                            echo '</td>';
                        }*/

                        if (!$valido) {

                            //se non c'è un turno puoi forzare la chiusura della marcatura per chiunque
                            if (!$tl) {
                                if ($timb && $timb['d_fine']=="") {
                                    echo '<td>';
                                        echo '<img style="width:'.$w.'px;margin-left:10px;margin-right:10px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/workshop/img/speciale/EXT.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].special_EXT(\''.$coll['ID_coll'].'\',\''.$timb['num_rif_riltem'].'\',\''.$this->param['wsp_officina'].'\');"/>';
                                        echo '<div style="text-align:center;">Fine</div>';
                                    echo '</td>';
                                }
                            }

                            if ($tl) {

                                if ($aby['CHI']==1 && $timb && $timb['d_fine']=="") {
                                    //se c'è una timbratura aperta
                                    echo '<td>';
                                        echo '<img style="width:'.$w.'px;margin-left:10px;margin-right:10px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/workshop/img/speciale/CHI.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].special_CHI(\''.$coll['ID_coll'].'\',\''.$timb['num_rif_riltem'].'\',\''.$this->param['wsp_officina'].'\');"/>';
                                        echo '<div style="text-align:center;">Allinea</div>';
                                    echo '</td>';
                                }
                            }
                        }

                    echo '</tr>';

                    /*echo '<tr>';

                        echo '<td>';
                            echo 'Attesa';
                        echo '</td>';

                        echo '<td>';
                            echo 'Pulizia'; 
                        echo '</td>';

                        echo '<td>';
                            echo 'Prova';
                        echo '</td>';

                        echo '<td>';
                            echo 'Servizio';
                        echo '</td>';

                        echo '<td>';
                            echo 'Fine';
                        echo '</td>';

                        echo '<td>';
                            echo 'Anticipo';
                        echo '</td>';
                        
                        echo '<td>';
                            echo 'Allinea';
                        echo '</td>';

                    echo '</tr>';*/
                
                echo '</table>';

                //echo json_encode($valido);

            echo '</div>';

            echo '<div style="margin-top:20px;">';

                echo '<div style="position:relative;display:inline-block;width:15%;vertical-align:top;height:50px;text-align:center;">';

                    if ($tempactual!="") {
                        echo '<img style="position:relative;width:40px;height:40px;top:50%;transform:translate(0%,-50%);line-height:50px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/workshop/img/now.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].selectActualOdl();" />';
                    }

                echo '</div>';

                echo '<div style="position:relative;display:inline-block;width:85%;vertical-align:top;height:50px;text-align:center;line-height:50px;margin-top:10px;">';
                    
                    echo '<div style="position:relative;display:inline-block;width:25%;vertical-align:top;height:50px;text-align:right;line-height:50px;">';

                        $dms="";
                        foreach ($this->wh->exportMap() as $m) {
                            $dms=$m['dms'];
                            break;
                        }

                        echo '<select id="wsp_timbratura_dms" style="position:relative;font-weight:bold;font-size:0.8em;top:50%;transform:translate(0%,-50%);" >';
                            foreach ($this->wh->getDmss() as $d) {
                                echo '<option value="'.$d.'" ';
                                    if ($d==$dms) echo '    selected';
                                echo '>'.$d.'</option>';
                            }
                        echo '</select>';

                    echo '</div>';

                    echo '<div style="position:relative;display:inline-block;width:40%;vertical-align:top;height:50px;text-align:center;line-height:50px;">';
                        echo '<input id="wsp_timbratura_nextlam" type="text" style="position:relative;text-align:center;font-weight:bold;font-size:1.4em;width:150px;margin-left:5px;top:50%;transform:translate(0%,-50%);" onkeydown="if(event.keyCode==13) window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].selectNextOdl(\''.$this->param['wsp_officina'].'\',\''.$coll['ID_coll'].'\');" />';
                    echo '</div>';

                    echo '<div style="position:relative;display:inline-block;width:30%;vertical-align:top;height:50px;text-align:center;line-height:50px;">';
                        echo '<div class="divButton" style="position:relative;top:50%;transform:translate(-0%,-50%);height:25px;" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].selectNextOdl(\''.$this->param['wsp_officina'].'\',\''.$coll['ID_coll'].'\');" >Cerca</div>';
                    echo '</div>';

                echo '</div>';

            echo '</div>';

            echo '<div style="position:relative;left:3%;width:97%;margin-top:15px;height:300px;overflow:scroll;overflow-x:hidden;" >';

                echo '<div id="wsp_timbratura_actualDiv" style="width:93%;" >'.$tempactual.'</div>';

                echo '<div id="wsp_timbratura_nextDiv" style="width:93%;display:none;" ></div>';

            echo '</div>';

        echo '</div>';

    }

    function getNextOdl($rif,$dms,$ID_coll) {

        $this->getOdl($rif,$dms);

        if (!isset($this->odl[$rif])) return "";

        $tempactual="";

        $primo=true;

        foreach ($this->odl[$rif] as $lam=>$l) {

            if ($l['cod_movimento']=='OOS') return "";

            if ($l['ind_chiuso']=='S') {
                return '<div>Oridne di lavoro Chiuso.</div>';
            }

            if ($primo) {

                $tempactual.=$this->drawPrimo($l);                

                $primo=false;
            }

            $tempactual.=$this->drawNextOdl($l,$ID_coll);
        }

        //lamentato PROVA
        if (array_key_exists($ID_coll,$this->collaboratori)) {

            $timb=false;
            if ( isset($this->marcature[$ID_coll]) ) $timb=$this->marcature[$ID_coll];

            if ($this->collaboratori[$ID_coll]['ability']['PRV']==1) $tempactual.=$this->drawPRV($l,$timb,$ID_coll);
        }

        return $tempactual;
    }

    function drawPrimo($l) {

        $tempactual="";

        if (isset($l['cod_officina'])) {

            $tempactual= '<div style="position:relative;" >';

                $tempactual.= '<div style="display:inline-block;width:20%;" >';
                    $tempactual.= (isset($l['cod_officina'])?$l['cod_officina'].' ':'').substr($l['dms'],0,1).$l['rif'];
                $tempactual.= '</div>';

                $tempactual.= '<div style="display:inline-block;width:15%;" >';
                    $tempactual.= isset($l['mat_targa'])?$l['mat_targa']:'';
                $tempactual.= '</div>';

                $tempactual.= '<div style="display:inline-block;width:40%;font-weight:bold;" >';
                    $tempactual.= isset($l['util_ragsoc'])?substr($l['util_ragsoc'],0,25):'';
                $tempactual.= '</div>';

                $tempactual.= '<div style="display:inline-block;width:25%;text-align:right;" >';
                    $tempactual.= isset($l['d_ricon'])?( (substr($l['d_ricon'],0,8)==date('Ymd'))?'Oggi':mainFunc::gab_todata(substr($l['d_ricon'],0,8)) ).'<span style="font-weight:bold;margin-left:5px;">'.substr($l['d_ricon'],9,5):''.'</span>';
                $tempactual.= '</div>';

            $tempactual.= '</div>';

            $tempactual.= '<div style="position:relative;font-size:0.9em;" >';

                $tempactual.= '<div style="display:inline-block;width:35%;" >';
                    $tempactual.= isset($l['mat_telaio'])?$l['mat_telaio']:'';
                $tempactual.= '</div>';

                $tempactual.= '<div style="display:inline-block;width:40%;" >';
                    $tempactual.= isset($l['des_veicolo'])?substr($l['des_veicolo'],0,26):'';
                $tempactual.= '</div>';

                $tempactual.= '<div style="display:inline-block;width:25%;text-align:right;" >';
                    $tempactual.= isset($l['cod_accettatore'])?$l['cod_accettatore']:'';
                $tempactual.= '</div>';

            $tempactual.= '</div>';
        
        }

        return $tempactual;
    }

    function drawNextOdl($l,$ID_coll) {

        $color='white';

        if ($l['nebulaAddebito']) {
            $color=$l['nebulaAddebito']['colore'];
        }

        $tempactual='<div style="margin-top:4px;margin-bottom:4px;border:1px solid black;padding:2px;box-sizing:border-box;background-color:'.$color.';">';

            $tempactual.='<div style="height:18px;line-height:18px;';
                if ($l['cod_movimento']=='OOS') $tempactual.='background-color:#ffccd3;';
            $tempactual.='">';

                $tempactual.='<div style="display:inline-block;width:5%;vertical-align:top;" >';
                    $tempactual.=$l['lam'].' - ';
                $tempactual.='</div>';

                $tempactual.='<div style="display:inline-block;width:60%;" >';
                    $tempactual.='<div>'.utf8_encode(substr($l['des_riga'],0,30)).'</div>';
                $tempactual.='</div>';

                $tempactual.='<div style="display:inline-block;width:20%;font-weight:bold;font-size:0.8em;vertical-align:top;" >';
                    $t=$this->odlFunc->getStatoLam($l,$l['dms']);
                    if ($t) {
                        $tempactual.=$t;
                    }
                $tempactual.='</div>';

                $tempactual.='<div style="position:relative;display:inline-block;width:15%;vertical-align:top;height:18px;text-align:center;" >';

                    $tempactual.='<img style="position:relative;width:70%;height:18px;top:50%;transform:translate(0%,-50%);cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/workshop/img/start.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].inizioMarcatura(\''.$l['dms'].'\',\''.$ID_coll.'\',\''.$l['rif'].'\',\''.$l['lam'].'\'';
                        //$tempactual.=isset($l['anno'])?'\''.$l['anno'].'\'':'\'\'';
                        //$tempactual.=isset($l['id_cliente'])?'\''.$l['id_cliente'].'\'':'\'\'';
                        //$tempactual.=isset($l['tipo_doc'])?'\''.$l['tipo_doc'].'\'':'\'\'';
                        //$tempactual.=isset($l['data_doc'])?'\''.$l['data_doc'].'\'':'\'\'';
                        //$tempactual.=isset($l['num_doc'])?'\''.$l['num_doc'].'\'':'\'\'';
                    $tempactual.=');" />';
                    
                $tempactual.='</div>';

            $tempactual.='</div>';

        $tempactual.='</div>';

        //$tempactual.=json_encode($l);

        return $tempactual;
    }

    function drawPRV($l,$timb,$ID_coll) {

        $color='#4eabab';

        $tempactual='<div style="margin-top:4px;margin-bottom:4px;border:1px solid black;padding:2px;box-sizing:border-box;background-color:'.$color.';">';

            $tempactual.='<div style="height:18px;line-height:18px;">';

                $tempactual.='<div style="display:inline-block;width:5%;vertical-align:top;" >';
                    $tempactual.='';
                $tempactual.='</div>';

                $tempactual.='<div style="display:inline-block;width:60%;" >';
                    $tempactual.='<div><b>Prova vettura</b> - '.($l['mat_targa']!=""?$l['mat_targa']:$l['mat_telaio']).'</div>';
                $tempactual.='</div>';

                $tempactual.='<div style="display:inline-block;width:20%;font-weight:bold;font-size:0.8em;vertical-align:top;" >';
                    
                $tempactual.='</div>';

                $tempactual.='<div style="position:relative;display:inline-block;width:15%;vertical-align:top;height:18px;text-align:center;" >';

                    $tempactual.='<img style="position:relative;width:70%;height:18px;top:50%;transform:translate(0%,-50%);cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/workshop/img/prova.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].special_PRV(\''.$ID_coll.'\',\''.($timb?$timb['num_rif_riltem']:'').'\',\''.$this->param['wsp_officina'].'\',\''.$l['rif'].'\');"/>';
                    
                $tempactual.='</div>';

            $tempactual.='</div>';

        $tempactual.='</div>';

        //$tempactual.=json_encode($l);

        return $tempactual;
    }

}
?>