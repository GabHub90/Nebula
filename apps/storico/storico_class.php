<?php

require_once(DROOT.'/nebula/core/divo/divo.php');
require_once(DROOT.'/nebula/core/blocklist/blocklist.php');
//require_once(DROOT.'/nebula/apps/storico/classi/pratica.php');
require_once(DROOT.'/nebula/core/veicolo/classi/veicolo_main.php');
require_once(DROOT.'/nebula/core/odl/odl_func.php');
//require_once(DROOT.'/nebula/core/odl/wormhole.php');
require_once(DROOT.'/nebula/core/odl/odl_body.php');
require_once(DROOT.'/nebula/core/divutil/divutil.php');

require_once(DROOT.'/nebula/apps/storico/classi/piano.php');

include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_comest.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/core/fidel/fidel.php');

class storicoApp extends appBaseClass {

    protected $veicolo=array(
        "infinity"=>false,
        "concerto"=>false
    );

    protected $quertt=array(
        "targa"=>"",
        "telaio"=>"",
        "operazione"=>"OR"
    );

    //dms che conserva i dati del veicolo
    protected $actualVei=array();

    protected $piano;
    protected $eventi=array();

    /*elenco dei gruppi di manutenzione legati alla marca
    protected $gruppi=array();

    //è il gruppo codice_indice del piano di manutenzione abbinato alla marca-modello
    protected $gruppo=array(
        "codice"=>"",
        "descrizione"=>"",
        "oggetti"=>false,
        "oggettiModello"=>false,
        "oggettiTelaio"=>false,
        "oggettiActual"=>array(),
        "eventi"=>array()
    );

    //contiene gli oggetti BASE (interventi di manutenzione)
    protected $oggettiDefault=array();
    */

    protected $pratiche=array();

    protected $flagEdit=true;

    //protected $odlFunc;
    protected $odlBody;
    protected $fidel;
    //wormhole
    protected $wh;

    protected $log=array();

    function __construct($param,$galileo) {

        parent::__construct($galileo);

        $this->loc='/nebula/apps/storico/';

        $this->param['sto_tt']="";
        $this->param['sto_marca']="";
        $this->param['sto_modello']="";

        $this->param['sto_km']="";
        $this->param['sto_consegna']="";

        $this->param['sto_ambito']="";

        $this->loadParams($param);

        $obj=new galileoComest();
        $nebulaDefault['comest']=array("gab500",$obj);
        $this->galileo->setFunzioniDefault($nebulaDefault);

        //####################
        $temp=explode(':',$this->id->getMainApp());
        if ($temp[0]!='isla') $this->flagEdit=false;
        //####################

        $this->odlFunc=new nebulaOdlFunc($this->galileo);
        //$this->odlBody=new nebulaOdlBody($this->odlFunc,$this->galileo);
        $this->veicolo['infinity']=new nebulaVeicolo('infinity',$this->galileo);

        $this->wh=new odielleWH('',$this->galileo);

        ///////////////////////////////////////////////////////////////////////////
        /*$map=$this->odlFunc->getOTBase();

        if ($map['result']) {
            $fid=$this->galileo->preFetchPiattaforma($map['piattaforma'],$map['result']);

            while ($row=$this->galileo->getFetchPiattaforma($map['piattaforma'],$fid)) {
                //vengono estratti in ordine di ambito e di posizione nell'ambito
                $this->base[$row['codice']]=$row;
            }
        }*/
        ///////////////////////////////////////////////////////////////////////////

        //se è specificato TT, carica il veicolo
        if ( $this->param['sto_tt'] && $this->param['sto_tt']!="") {

            $this->quertt['targa']=$this->param['sto_tt'];
            $this->quertt['telaio']=$this->param['sto_tt'];

            $primo=true;

            //infinity è il DB MASTER per i veicoli
            //prima viene cercato su infinity un veicolo che abbia o la targa o il telaio come indicato nel form di ricerca
            //se non è stato trovato nulla fa la stessa cosa su concerto
            //altrimenti cerca su concerto un veicolo con il telaio registrato su infinity (la targa potrebbe essere cambiata)
            //usa la targa se il telaio è nullo

            foreach ($this->veicolo as $dms=>$obj) {

                if ($dms!='infinity') {
                    $this->veicolo[$dms]=new nebulaVeicolo($dms,$this->galileo);
                }

                if ($this->quertt['targa']!="" || $this->quertt['telaio']!="" ) {
                    $this->veicolo[$dms]->loadTT($this->quertt,1);
                }

                $temp=$this->veicolo[$dms]->getInfo();

                //se il veicolo è stato trovato ed è il primo ad esserlo (quello ritenuto del DMS MASTER)
                if ($primo && $temp['rif']!="") {
                    $this->quertt['targa']=($temp['telaio']=='')?$temp['targa']:'';
                    $this->quertt['telaio']=$temp['telaio'];
                    $this->quertt['operazione']='AND';
                    $primo=false;
                }

                //$this->log[]=$this->veicolo[$dms]->getInfo();
                //$this->log[]=$this->veicolo[$dms]->getLog();
            }

        }

        //////////////////////////////////////////////////////////////////////////////////////////////

        foreach ($this->veicolo as $dms=>$obj) {

            if ($this->veicolo[$dms]) {
                $this->actualVei=$this->veicolo[$dms]->getInfo();
                if ($this->actualVei['rif']!="") break;
            }
        }

        if (!isset($this->actualVei['rif']) || $this->actualVei['rif']=="") {
            $this->actualVei['cod_marca']=$this->param['sto_marca'];
            $this->actualVei['modello']=$this->param['sto_modello'];
        }

        //#############################
        //inizializzazione piano
        $this->piano=new nebulaStoricoPiano($this->actualVei['cod_marca'],$this->actualVei['modello'],$this->actualVei['telaio'],$this->odlFunc,$this->galileo);
        /*$this->gruppo=array(
            'oggettiActual'=>$this->piano->getActual(),
            'eventi'=>array()
        );*/
        //#############################


        /*if ($this->actualVei['cod_marca']!="") {

            //carica i gruppi OVERTURE riferiti alla marca
            $map=$this->odlFunc->getOTGruppi($this->actualVei['cod_marca']);

            if ($map['result']) {
                $fid=$this->galileo->preFetchPiattaforma($map['piattaforma'],$map['result']);

                while ($row=$this->galileo->getFetchPiattaforma($map['piattaforma'],$fid)) {
                    $this->gruppi[$row['codice'].'_'.$row['indice']]=$row;
                }
            }

            //carica gli oggetti di DEFAULT per la marca
            $map=$this->odlFunc->getOTDefault($this->actualVei['cod_marca']);

            if ($map['result']) {
                $fid=$this->galileo->preFetchPiattaforma($map['piattaforma'],$map['result']);

                while ($row=$this->galileo->getFetchPiattaforma($map['piattaforma'],$fid)) {
                    $this->oggettiDefault[$row['codice']]=$row;
                }
            }

            ////////////////////////////////////////////////////////////

            if ($this->actualVei['modello']!="") {

                //identifica il record OVERTURE specifico per marca e modello
                $map=$this->odlFunc->getOTGruppoMM($this->actualVei['cod_marca'],$this->actualVei['modello']);

                if ($map['result']) {
                    $fid=$this->galileo->preFetchPiattaforma($map['piattaforma'],$map['result']);

                    while ($row=$this->galileo->getFetchPiattaforma($map['piattaforma'],$fid)) {
                        $this->gruppo['codice']=$row['gruppo'].'_'.$row['indice'];

                        if (isset($this->gruppi[$this->gruppo['codice']])) {
                            $this->gruppo['descrizione']=$this->gruppi[$this->gruppo['codice']]['descrizione'];
                            $this->gruppo['oggetti']=json_decode($this->gruppi[$this->gruppo['codice']]['oggetti'],true);
                        }

                        break;
                    }
                }

                //carica eventuali specifiche per il modello
                $map=$this->odlFunc->getOTCriteriModello($this->actualVei['cod_marca'],$this->actualVei['modello']);

                if ($map['result']) {
                    $fid=$this->galileo->preFetchPiattaforma($map['piattaforma'],$map['result']);

                    while ($row=$this->galileo->getFetchPiattaforma($map['piattaforma'],$fid)) {
                        $this->gruppo['oggettiModello']=json_decode($row['edit'],true);
                    }
                }
            }
        }

        //carica eventuali specifiche per il telaio
        if ($this->actualVei['telaio']!="") {

            $map=$this->odlFunc->getOTCriteriTelaio($this->actualVei['telaio']);

            if ($map['result']) {
                $fid=$this->galileo->preFetchPiattaforma($map['piattaforma'],$map['result']);

                while ($row=$this->galileo->getFetchPiattaforma($map['piattaforma'],$fid)) {
                    $this->gruppo['oggettiTelaio']=json_decode($row['edit'],true);
                }
            }

        }*/

        /*############################################
        //OGGETTI ACTUAL
        //le modifiche al modello ed al telaio NON sono incatenate tra loro ma la seconda ha eventualmente la precedenza sulla prima
        //entrambe fanno riferimento alle impostazioni del gruppo

        if ($this->gruppo['oggetti']) {

            foreach ($this->gruppo['oggetti'] as $oggetto=>$g) {

                //se l'oggetto è valido come oggetto di base e di default per la marca
                if (array_key_exists($oggetto,$this->base) && array_key_exists($oggetto,$this->oggettiDefault)) {

                    $this->gruppo['oggettiActual'][$oggetto]=$g;
                    $this->gruppo['oggettiActual'][$oggetto]['flag_mov']='ok';
                    $this->gruppo['oggettiActual'][$oggetto]['base']='gruppo';
                    $this->gruppo['oggettiActual'][$oggetto]['main']=$this->base[$oggetto]['main'];

                    if (!isset($this->gruppo['oggettiActual'][$oggetto]['stat'])) {
                        if (isset($this->oggettiDefault[$oggetto]['stat'])) {
                            $this->gruppo['oggettiActual'][$oggetto]['stat']=$this->oggettiDefault[$oggetto]['stat'];
                        }
                        else  $this->gruppo['oggettiActual'][$oggetto]['stat']=1;
                    }
                }

            }
        }

        if ($this->gruppo['oggettiTelaio']) {

            foreach ($this->gruppo['oggettiTelaio'] as $oggetto=>$g) {

                if (array_key_exists($oggetto,$this->base) && array_key_exists($oggetto,$this->oggettiDefault)) {

                    if(!isset($g['flag_mov'])) $g['flag_mov']='ok';

                    //non aggiorna DEL se in actual non esiste
                    if ($g['flag_mov']!='del' || array_key_exists($oggetto,$this->gruppo['oggettiActual'])) {

                        $this->gruppo['oggettiActual'][$oggetto]=$g;
                        $this->gruppo['oggettiActual'][$oggetto]['base']='telaio';
                        $this->gruppo['oggettiActual'][$oggetto]['main']=$this->base[$oggetto]['main'];

                        if (!isset($this->gruppo['oggettiActual'][$oggetto]['stat'])) {
                            if (isset($this->oggettiDefault[$oggetto]['stat'])) {
                                $this->gruppo['oggettiActual'][$oggetto]['stat']=$this->oggettiDefault[$oggetto]['stat'];
                            }
                            else  $this->gruppo['oggettiActual'][$oggetto]['stat']=0;
                        }
                    }
                }
            }
        }
        elseif ($this->gruppo['oggettiModello']) {

            foreach ($this->gruppo['oggettiModello'] as $oggetto=>$g) {

                if (array_key_exists($oggetto,$this->base) && array_key_exists($oggetto,$this->oggettiDefault)) {

                    if(!isset($g['flag_mov'])) $g['flag_mov']='ok';

                    //non aggiorna DEL se in actual non esiste
                    if ($g['flag_mov']!='del' || array_key_exists($oggetto,$this->gruppo['oggettiActual'])) {

                        $this->gruppo['oggettiActual'][$oggetto]=$g;
                        $this->gruppo['oggettiActual'][$oggetto]['base']='modello';
                        $this->gruppo['oggettiActual'][$oggetto]['main']=$this->base[$oggetto]['main'];

                        if (!isset($this->gruppo['oggettiActual'][$oggetto]['stat'])) {
                            if (isset($this->oggettiDefault[$oggetto]['stat'])) {
                                $this->gruppo['oggettiActual'][$oggetto]['stat']=$this->oggettiDefault[$oggetto]['stat'];
                            }
                            else  $this->gruppo['oggettiActual'][$oggetto]['stat']=0;
                        }
                    }
                }
            }
        }*/

    }

    function initClass() {
        return ' storicoCode(\''.$this->param['nebulaFunzione']['nome'].'\');';
    }

    function getLog() {
        return $this->log;
    }

    function readEventi() {

        //////////////////////////////////////////////////////////////////////////////////
        //recupero degli eventi per il calcolo dello storico
        $tempstr="";

        foreach ( $this->piano->getActual() as $oggetto=>$o) {
            $tempstr.="'".$oggetto."',";
            $this->eventi[$oggetto]=array();
        }

        if ($tempstr!="") {

            $map=$this->odlFunc->getOTeventi(substr($tempstr,0,-1));

            if ($map['result']) {
                $fid=$this->galileo->preFetchPiattaforma($map['piattaforma'],$map['result']);

                while ($row=$this->galileo->getFetchPiattaforma($map['piattaforma'],$fid)) {
                   $this->wh->OTaddEvento($row);
                }
            }

        }
    }

    function readCommesse() {

        $telaioPass="";
        
        foreach ($this->veicolo as $dms=>$v) {

            if (!$v) continue;

            $veiInfo=$v->getInfo();

            //il telaio serve per recuperare i passaggi manuali
            if ($telaioPass=="") $telaioPass=$veiInfo['telaio'];

            $map=$this->odlFunc->readCommesse($dms,$veiInfo);

            if ($map['result']) {

                $fid=$this->galileo->preFetchPiattaforma($map['piattaforma'],$map['result']);

                while ($row=$this->galileo->getFetchPiattaforma($map['piattaforma'],$fid)) {
                    //$this->log[]=$row;

                    $row['pratica']=str_replace(array(" ",".",":","-","/"), "",$row['pratica']);

                    if ($row['dms']=='concerto') {
                        $row['pratica']='c'.substr($row['pratica'],0,12);
                    }
                    elseif ($row['dms']=='infinity') {
                        $row['pratica']='i'.substr($row['pratica'],0,12);
                    }
                    else continue;

                    //correggi d_entrata
                    if ($row['d_entrata']=='') $row['d_entrata']=$row['d_rif'];
                    if ($row['d_entrata']<$row['d_rif']) $row['d_entrata']=$row['d_rif'];
                    if ($row['d_entrata']>$row['d_fatt'] && $row['d_fatt']!='') $row['d_entrata']=$row['d_fatt'];

                    //se la pratica non esiste creala
                    if (!array_key_exists($row['pratica'],$this->pratiche)) {
                        $this->pratiche[$row['pratica']]=array(
                            "d_rif"=>$row['d_rif'],
                            "dms"=>$row['dms'],
                            "commesse"=>array(),
                            "prenotazione"=>false,
                            "preventivo"=>false
                        );
                    }
                    //altrimenti modificane la data di riferimento
                    //non ha senso in quanto i record vengono già estratti in ordine crescente
                    //quindi quello che apre la pratica è sicuramente quello con la data inferiore
                    /*else {
                        if ($row['d_rif']<$this->pratiche[$row['pratica']]['d_rif']) {
                            $this->pratiche[$row['pratica']]['d_rif']=$row['d_rif'];
                        }
                    }*/

                    $tempf=array(
                        "inizio"=>$row['d_rif'],
                        "fine"=>$row['d_rif'],
                        'dms'=>$row['dms'],
                        'result'=>false
                    );

                    $this->wh->forceMap($tempf);

                    //////////////////////
                    if ($row['dms']=='concerto') {

                        if ($row['num_doc']=='0') {
                            $this->pratiche[$row['pratica']]['prenotazione']=array(
                                'info'=>$row,
                                'obj'=>new nebulaOdlBody($this->odlFunc,$this->galileo)
                            );
                            $this->pratiche[$row['pratica']]['prenotazione']['obj']->setStorico(true,true);
                            $this->pratiche[$row['pratica']]['prenotazione']['obj']->build($row['rif'],'pre',$this->wh);
                        }
                        else {
                            $this->pratiche[$row['pratica']]['commesse'][$row['rif']]=array(
                                "d_rif"=>$row['d_rif'],
                                "info"=>$row,
                                "obj"=>new nebulaOdlBody($this->odlFunc,$this->galileo)
                            );
                            $this->pratiche[$row['pratica']]['commesse'][$row['rif']]['obj']->setStorico(true,false);
                            $this->pratiche[$row['pratica']]['commesse'][$row['rif']]['obj']->build($row['rif'],'cli',$this->wh);
                        }
                    }

                    if ($row['dms']=='infinity') {

                        if ($row['prenotazione']!=0) {
                            $this->pratiche[$row['pratica']]['prenotazione']=array(
                                'info'=>$row,
                                'obj'=>new nebulaOdlBody($this->odlFunc,$this->galileo)
                            );
                            $this->pratiche[$row['pratica']]['prenotazione']['obj']->setStorico(true,true);
                            $this->pratiche[$row['pratica']]['prenotazione']['obj']->build($row['prenotazione'],'pre',$this->wh);
                        }
                       
                        $this->pratiche[$row['pratica']]['commesse'][$row['rif']]=array(
                            "d_rif"=>$row['d_rif'],
                            "info"=>$row,
                            "obj"=>new nebulaOdlBody($this->odlFunc,$this->galileo)
                        );
                        $this->pratiche[$row['pratica']]['commesse'][$row['rif']]['obj']->setStorico(true,false);
                        $this->pratiche[$row['pratica']]['commesse'][$row['rif']]['obj']->build($row['rif'],'cli',$this->wh);
                    }

                    //$this->pratiche[$row['pratica']]['commesse'][$row['rif']]=$row;
                }
            }
        }

        //recupero passaggi manuali

        $map=$this->odlFunc->readPassMan($telaioPass);

        if ($map['result']) {
            $fid=$this->galileo->preFetchPiattaforma($map['piattaforma'],$map['result']);

            $index=0;

            while ($row=$this->galileo->getFetchPiattaforma($map['piattaforma'],$fid)) {

                if ($index!=$row['indice']) {

                    $index=$row['indice'];

                    if ($obj=json_decode($row['obj'],true)) {

                        $obj['d_rif']=$obj['data_fatt'];
                        $obj['indice']=$row['indice'];
                        $obj['note']=$row['note'];

                        $this->pratiche['man_'.$row['indice']]=array(
                            "manuale"=>true,
                            "d_rif"=>$obj['data_fatt'],
                            "info"=>$obj,
                            "righe"=>array()
                        );
                    }
                }

                if (isset($this->pratiche['man_'.$row['indice']])) {
                    $this->pratiche['man_'.$row['indice']]['righe'][$row['tipo']]=$row;
                }
            }
        }

        //recupero commesse esterne
        if($this->actualVei['targa']!="" || $this->actualVei['telaio']!="") { 

            $param=array(
                'targa'=>$this->actualVei['targa'],
                'telaio'=>$this->actualVei['telaio'],
                'annullate'=>0
            );

            $this->galileo->executeGeneric('comest','getStorico',$param,'');

            if ($this->galileo->getResult()) {
                $fid=$this->galileo->preFetch('comest');

                $indice=0;

                while ($row=$this->galileo->getFetch('comest',$fid)) {

                    if ($indice!=$row['rif']) {

                        $indice=$row['rif'];

                        $this->pratiche['com_'.$row['rif']]=array(
                            "comest"=>true,
                            "d_rif"=>$row['d_apertura'],
                            "info"=>$row,
                            "righe"=>array()
                        );
                    }

                    if (isset($this->pratiche['com_'.$row['rif']])) {
                        $this->pratiche['com_'.$row['rif']]['righe'][]=$row;
                    }
                }

            }
        }

        //recupero Voucher
        $this->fidel=new nebulaFidel('storico',$this->galileo);

        $a=array(
            "tag"=>$this->actualVei['telaio'],
            "utente"=>$this->id->getLogged()
        );

        $this->fidel->build($a);

        ///////////////////////////////////////////////////////////////////
        usort($this->pratiche,function($d1,$d2) {
            return $d2['d_rif']-$d1['d_rif'];
        });

        foreach ($this->pratiche as $k=>$p) {
            if (isset($p['manuale'])) continue;
            if (isset($p['comest'])) continue;
            usort($this->pratiche[$k]['commesse'],function($d1,$d2) {
                return $d2['d_rif']-$d1['d_rif'];
            });
        }

        /////////////////////////////////////////////////////////////////////
        //aggiorna  $this->eventi[$oggetto]
        foreach ($this->pratiche as $pratica=>$p) {
            if (isset($p['commesse'])) {
                foreach ($p['commesse'] as $commessa=>$c) {
                    foreach ($c['obj']->getEventi() as $oggetto=>$o) {
                        if ($o!='DEL' && $o!='CHK') {
                            $this->eventi[$oggetto][]=$c['info'];
                        }
                    }
                }
            }
            else if (isset($p['manuale'])) {
                //##########################
                //alimenta eventi
                foreach ($p['righe'] as $oggetto=>$o) {
                    $this->eventi[$oggetto][]=$p['info'];
                }
            }
        }

        $this->normalizzaDeltaEventi();
    }

    function normalizzaDeltaEventi() {

        //fissa come riferimento la data di consegna
        $t=($this->actualVei['rif'] && $this->actualVei['d_cons']!="")?$this->actualVei['d_cons']:false;
        $km=0;

        foreach ($this->eventi as $oggetto=>$o) {
            
            foreach ($o as $k=>$c) {

                if ($c['km']=="") $c['km']=0;
                else $c['km']=(int)$c['km'];

                if ($t && $c['d_rif'] && $t!="" && $c['d_rif']!="") $this->eventi[$oggetto][$k]['deltat']=round(mainFunc::gab_delta_tempo($t,$c['d_rif'],'g')/30);
                else $this->eventi[$oggetto][$k]['deltat']=false;

                //if ($km && $c['km'] && $c['km']!=0) $this->eventi[$oggetto][$k]['deltakm']=$c['km']-$km;
                //else $this->eventi[$oggetto][$k]['deltakm']=false;

                $this->eventi[$oggetto][$k]['deltakm']=$c['km']-$km;

                //adegua record precedente
                if ($k>0) {
                    if ($this->eventi[$oggetto][$k-1]['d_rif'] && $c['d_rif'] && $this->eventi[$oggetto][$k-1]['d_rif']!="" && $c['d_rif']!="") $this->eventi[$oggetto][$k-1]['deltat']=round(mainFunc::gab_delta_tempo($c['d_rif'],$this->eventi[$oggetto][$k-1]['d_rif'],'g')/30);
                    else $this->eventi[$oggetto][$k-1]['deltat']=false;

                    if ($this->eventi[$oggetto][$k-1]['km'] && $c['km'] && $this->eventi[$oggetto][$k-1]['km']!="" && $c['km']!=0) $this->eventi[$oggetto][$k-1]['deltakm']= $this->eventi[$oggetto][$k-1]['km']-$c['km'];
                    else $this->eventi[$oggetto][$k-1]['deltakm']=false;
                }

            }
        }

    }

    function customDraw() {

        //echo '<script type="text/javascript" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/blocklist/blocklist_js.js" ></script>';
        BlockList::blockListInit();

        echo '<script type="text/javascript" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/storico/core/storico.js?v='.time().'"></script>';
        echo '<script type="text/javascript">';
            echo 'window._nebulaStorico=new nebulaStorico();';
        echo '</script>';

        echo '<div style="position:relative;width:100%;height:7%;">';
            $this->drawHead();
        echo '</div>';

        echo '<div style="position:relative;width:100%;height:93%;">';
            if ($this->param['sto_ambito']=='standard') {

                $this->piano->buildMM();
                $this->readEventi();
                $this->readCommesse();

                $this->drawBody();
            }
            else if ($this->param['sto_ambito']=='gruppi') {
                $this->piano->buildMM();
                $this->drawGruppi();
            }
        echo '</div>';

        echo '<script type="text/javascript">';

            ob_start();
                include (DROOT.'/nebula/apps/storico/core/default.js');
            ob_end_flush();

            //window._nebulaApp_storico -> storicoCode
            
        echo '</script>';

    }

    function drawHead() {

        echo '<div style="position:relative;width:100%;height:100%;padding:3px;box-sizing:border-box;background-color:#eeeeee;border:1px solid black;border-radius:5px;" >';

            echo '<div style="position:relative;display:inline-block;width:8%;vertical-align:top;" >';
                echo '<div style="font-weight:bold;font-size:0.9em;">Targa</div>';
                echo '<div style="font-size:1em;">'.$this->actualVei['targa'].'</div>';
            echo '</div>';

            echo '<div style="position:relative;display:inline-block;width:17%;vertical-align:top;" >';
                echo '<div style="font-weight:bold;font-size:0.9em;">Telaio<span style="margin-left:5px;font-weight:normal;font-size:0.9em;">('.$this->actualVei['dms'].' - '.$this->actualVei['rif'].')</span></div>';
                echo '<div style="font-size:1em;">'.$this->actualVei['telaio'].'</div>';
                echo '<input id="storico_marca_hidden" type="hidden" value="'.$this->actualVei['cod_marca'].'" />';
                echo '<input id="storico_modello_hidden" type="hidden" value="'.$this->actualVei['modello'].'" />';
                echo '<input id="storico_telaio_hidden" type="hidden" value="'.$this->actualVei['telaio'].'" />';
                echo '<input id="storico_ambito_hidden" type="hidden" value="'.$this->param['sto_ambito'].'" />';
                //echo '<input id="storico_piano_hidden" type="hidden" value="'.$this->piano->getCodice().'" />';
            echo '</div>';

            echo '<div style="position:relative;display:inline-block;width:17%;vertical-align:top;" >';
                echo '<div style="font-weight:bold;font-size:0.9em;">Marca</div>';
                if ($this->actualVei['rif']!='') {
                    echo '<div style="font-size:1em;">'.substr($this->actualVei['cod_marca'].' - '.$this->actualVei['des_marca'],0,18).'</div>';
                }
                else {
                    echo '<div style="font-size:1em;">'.$this->param['sto_marca'].' - '.$this->odlFunc->getDesNebulaMarca($this->actualVei['dms'],$this->param['sto_marca']).'</div>';
                    //echo '<div>'.$vei['dms'].'</div>';
                }
            echo '</div>';

            $desmodello='';

            echo '<div style="position:relative;display:inline-block;width:8%;vertical-align:top;" >';
                echo '<div style="font-weight:bold;font-size:0.9em;">Modello</div>';
                if ($this->actualVei['rif']!='') {
                    echo '<div style="font-size:1em;">'.$this->actualVei['modello'].'</div>';
                }
                else {

                    $desmodello=$this->veicolo[$this->actualVei['dms']]->getDesModello($this->odlFunc->getMarcaDms($this->actualVei['dms'],$this->param['sto_marca']),$this->param['sto_modello']);

                    echo '<div style="font-size:1em;">'.($desmodello!=""?strtoupper($this->param['sto_modello']):'').'</div>';
                }
            echo '</div>';

            echo '<div style="position:relative;display:inline-block;width:25%;vertical-align:top;" >';
                echo '<div style="font-weight:bold;font-size:0.9em;">Descrizione</div>';
                if ($this->actualVei['rif']!='') {
                    echo '<div style="font-size:1em;">'.substr($this->actualVei['des_veicolo'],0,30).'</div>';
                }
                else {
                    echo '<div style="font-size:1em;">'.($desmodello!=""?substr($desmodello,0,30):'<b style="color:red;">modello non valido</b>').'</div>';
                }
            echo '</div>';

            /*echo '<div style="position:relative;display:inline-block;width:15%;vertical-align:top;" >';
                echo '<div style="font-weight:bold;font-size:0.9em;">Tempario</div>';
                if ($this->actualVei['cod_marca']!='P') {
                    echo '<div style="font-size:1em;">'.$this->actualVei['cod_vw_tipo_veicolo'].' - '.substr($this->actualVei['des_vw_tipo_veicolo'],0,15).'</div>';
                }
                else {
                    echo '<div style="font-size:1em;">'.$this->actualVei['cod_po_tipo_veicolo'].' - '.substr($this->actualVei['des_po_tipo_veicolo'],0,15).'</div>';
                }
            echo '</div>';*/

            echo '<div style="position:relative;display:inline-block;width:15%;vertical-align:top;" >';
                echo '<div style="font-weight:bold;font-size:0.9em;">Consegna</div>';
                echo '<div style="font-size:1em;">'.($this->actualVei['rif']!=''?mainFunc::gab_todata($this->actualVei['d_cons']):'').'</div>';
            echo '</div>';

            if ($this->flagEdit) {

                echo '<div style="position:relative;display:inline-block;width:8%;text-align:right;vertical-align:top;" >';
                    echo '<img style="width:25px;height:25px;margin-top:5px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/storico/img/edit.png" onclick="" />';
                echo '</div>';
            }

        echo '</div>';

    }

    function drawTitoloPiano() {

        echo '<div style="position:relative;display:inline-block;width:70%;vertical-align:top;" >';

            echo '<div style="font-size:0.9em;font-weight:bold;">Piano di manutenzione</div>';

            if (!isset($this->actualVei['rif']) || $this->actualVei['cod_marca']=="" || $this->actualVei['modello']=="") {
                echo '<div style="font-size:1em;font-weight:bold;color:red;">Marca o Modello non corretti</div>';
            }
            elseif ($this->piano->checkCodice()) {
                /*
                //15.03.2022 per il momento lascio il codice qui
                //andrà messo dove c'è la gestione dei gruppi

                echo '<select id="nebula_storico_gruppo" style="font-size:1.2em;" >';

                    echo '<option value="">Scegli un gruppo...</option>';

                    foreach ($this->gruppi as $k=>$v) {

                        echo '<option value="'.$k.'"';
                            if ($k==$this->gruppo) echo ' selected';
                        echo '>'.$v['descrizione'].'</option>';

                    }

                echo '</select>';
                */
                
                echo '<div style="font-size:1.2em;border: 1px solid black;padding:2px;border-radius:10px;text-align:center;background-color:thistle;" >'.$this->piano->getDescrizione().'</div>';
                echo '<input id="sto_actual_piano_hidden" type="hidden" value="'.$this->piano->getCodice().'" />';

            }
            else {
                echo '<div style="font-size:1em;font-weight:bold;color:red;">Nessun Piano Associato</div>';
            }

        echo '</div>';
    }

    function drawBody() {

        // echo '<div>'.json_encode($this->log).'</div>';

        $this->fidel->initJS();

        echo '<div style="position:relative;width:100%;height:100%;">';

            echo '<div style="position:relative;display:inline-block;width:35%;height:100%;padding:3px;box-sizing:border-box;//border-right:1px solid black;vertical-align:top;">';

                echo '<div style="position:relative;height:10%;" >';

                    $this->drawTitoloPiano();

                    echo '<div style="position:relative;display:inline-block;width:30%;vertical-align:top;text-align:center;" >';

                        if ($this->flagEdit) {

                            if (isset($this->actualVei['rif']) && $this->actualVei['cod_marca']!="" && $this->actualVei['modello']!="") {
                                //echo '<img style="width:25px;height:25px;margin-top:10px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/storico/img/edit.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].selectPiano(\''.$this->actualVei['cod_marca'].'\',\''.$this->actualVei['modello'].'\',\''.$this->piano->getCodice().'\');" />';
                                echo '<img style="width:25px;height:25px;margin-top:10px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/storico/img/edit.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].setAmbito(\'gruppi\');" />';
                            }

                            if (isset($this->actualVei['rif']) && $this->actualVei['rif']!="") {
                                //echo '<img style="width:25px;height:25px;margin-top:10px;margin-left:20px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/storico/img/add.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].openPassman(false);" />';
                                echo '<img style="width:25px;height:25px;margin-top:10px;margin-left:20px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/storico/img/add.png" onclick="window._nebulaStorico.openPassman(false);" />';
                            }
                        }

                    echo '</div>';

                echo '</div>';

                echo '<div style="height:90%;overflow:scroll;overflow-x:hide;" >';

                    echo '<div id="sto_leftDiv" style="width:90%;">';

                        $this->piano->drawActual($this->eventi);

                        /*if (isset($this->gruppo['oggettiActual'])) {

                            foreach ($this->base as $codice=>$b) {

                                if (isset($this->gruppo['oggettiActual'][$codice])) {

                                    echo '<div style="position:relative;margin-top:6px;margin-bottom:6px;width:100%;">';

                                        $this->drawOggetto($codice,$this->gruppo['oggettiActual'][$codice]);

                                    echo '</div>';

                                }
                            }
                        }*/

                        //echo '<div>'.json_encode($this->pratiche).'</div>';
                    
                    echo '</div>';

                echo '</div>';

            echo '</div>';

            /////////////////////////////////////////////////////

            $divo=new Divo('storico','5%','94%',true);

            $divo->setBk('#ecf1a5');

            $css=array(
                "font-weight"=>"bold",
                "font-size"=>"1.2em",
                "margin-left"=>"15px",
                "margin-top"=>"0px"
            );

            /*$css2=array(
                "width"=>"15px",
                "height"=>"15px",
                "top"=>"50%",
                "transform"=>"translate(0%,-50%)",
                "right"=>"5px"
            );*/

            //$divo->setChkimgCss($css2);

            ////////////////////////////////////////////////////////

            echo '<div style="position:relative;display:inline-block;width:65%;height:100%;padding:3px;box-sizing:border-box;vertical-align:top;">';

                ob_start();
                $this->drawPratiche();

                $divo->add_div('Storico','black',0,"",ob_get_clean(),0,$css);

                ob_start();
                $this->drawPrevisione();

                $divo->add_div('Previsione','black',0,"",ob_get_clean(),0,$css);

                $divo->build();

                $divo->draw();

            echo '</div>';

        echo '</div>';

        echo '<script type="text/javascript" >';

            //if (isset($this->gruppo['oggettiActual'])) {
                echo 'var base="'.base64_encode(json_encode($this->piano->getBase())).'";';
                echo 'var actual="'.base64_encode(json_encode($this->piano->getActual())).'";';
            //}
            /*else {
                echo 'var base="'.base64_encode('{}').'";';
                echo 'var actual="'.base64_encode('{}').'";';
            }*/

            //echo 'window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].setOggettiPassman(base,actual);';
            echo 'window._nebulaStorico.setOggettiPassman(base,actual);';

        echo '</script>';

    }

    function drawGruppi() {

        echo '<div style="position:relative;width:100%;height:100%;">';

            echo '<div style="position:relative;display:inline-block;width:35%;height:100%;padding:3px;box-sizing:border-box;//border-right:1px solid black;vertical-align:top;">';

                echo '<div style="position:relative;height:10%;" >';

                    $this->drawTitoloPiano();

                    echo '<div style="position:relative;display:inline-block;width:30%;vertical-align:top;text-align:center;" >';
                        echo '<img style="width:25px;height:25px;margin-top:10px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/storico/img/chiudi.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].setAmbito(\'standard\');" />';
                    echo '</div>';
                
                echo '</div>';

                echo '<div style="height:90%;overflow:scroll;overflow-x:hide;" >';

                    echo '<div id="sto_leftDiv" style="width:90%;">';

                        $this->piano->drawElencoGruppi();
                    
                    echo '</div>';


                echo '</div>';

            echo '</div>';

            echo '<div id="sto_rightDiv" style="position:relative;display:inline-block;width:65%;height:100%;padding:3px;box-sizing:border-box;vertical-align:top;">';
            echo '</div>';

            echo '<script type="text/javascript" >';
                //echo 'window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].setPiano();';
                echo 'window._nebulaStorico.setPiano();';
            echo '</script>';

        echo '</div>';

    }

    /*function drawOggetto($codice,$c) {

        //echo '<table style="position:relative;width:100%;text-align:center;border-collapse:collapse;border:2px solid #2a2a2a;box-shadow: 5px 3px #fbb696;" >';
        echo '<table style="position:relative;width:100%;text-align:center;border-collapse:collapse;border:2px solid #2a2a2a;" >';

            echo '<colgroup>';
                echo '<col span="1" style="width:11%;" />';
                echo '<col span="1" style="width:10%;" />';
                echo '<col span="1" style="width:12%;" />';
                echo '<col span="1" style="width:11%;" />';
                echo '<col span="1" style="width:15%;" />';
                echo '<col span="1" style="width:20%;" />';
                echo '<col span="1" style="width:12%;" />';
                echo '<col span="1" style="width:12%;" />';
            echo '</colgroup>';

            echo '<tbody>';

                //#efc39d marroncino / #b9cf84 verde
                $bk=($c['stat']==1)?'#dedbdb':'#edefc9';
                $bkh=($c['stat']==1)?'#cbcbcb':'#f9e9bd';

                echo '<tr style="background-color:'.$bkh.';">';
                    echo '<td colspan="8" style="text-align:left;font-weight:bold;">';
                        echo '<div style="position:relative;display:inline-block;width:75%;font-size:1.1em;">'.($this->base[$codice]['main']==1?'*':'').'('.$codice.') '.$this->base[$codice]['descrizione'].'</div>';
                        echo '<div style="position:relative;display:inline-block;width:25%;text-align:center;font-size:0.8em;font-weight:normal;vertical-align:top;';
                            if ($c['base']!='gruppo') echo "background-color:yellow;";
                        echo '">- '.$c['base'].' -</div>';
                    echo '</td>';
                echo '</tr>';

                echo '<tr style="font-size:1em;background-color:'.$bk.';font-weight:bold;">';

                    if ($c['flag_mov']!='del') {

                        echo '<td>';
                            if ($c['pcx']==1) echo '<img style="position:relative;width:15px;height:15px;opacity:0.7;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/storico/img/linedot.png" />';
                        echo '</td>';

                        echo '<td>Δt</td>';
                        echo '<td>';
                                echo ($c['dt']==0)?'---':$c['dt'];
                        echo '</td>';
                        echo '<td style="text-align:left;" >';
                            echo ($c['mint']!=0 || $c['first_t']!=0 || $c['topt']!=0)?'<div style="width:15px;height:100%;border:2px solid black;border-radius:5px;text-align:center;">±</div>':'';
                        echo '</td>';

                        echo '<td>Δkm</td>';
                        echo '<td>';
                            echo ($c['dkm']==0)?'---':number_format($c['dkm'],0,'','.');
                        echo '</td>';
                        echo '<td  style="text-align:left;" >';
                            echo ($c['minkm']!=0 || $c['first_km']!=0 || $c['topkm']!=0)?'<div style="width:15px;height:100%;border:2px solid black;border-radius:5px;text-align:center;">±</div>':'';
                        echo '</td>';

                        echo '<td>';
                            if ($c['stat']==1) echo '<img style="position:relative;width:15px;height:15px;opacity:0.7;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/storico/img/stat.png" />';
                        echo '</td>';

                    }
                    else {
                        echo '<td colspan="8" style="color:red;">Escluso</td>';
                    }

                echo '</tr>';

                echo '<tr>';
                    //echo '<td colspan="8" style="background-color:bisque;">';
                    echo '<td colspan="8" style="height:20px;">';
                            foreach ( $this->eventi[$codice] as $e) {
                                echo '<div style="position:relative;font-size:0.8em;border: 1px solid #555555;border-radius: 8px;padding: 2px;margin-top: 2px;background-color:#f0ede3;">';

                                    echo '<div style="position:relative;display:inline-block;width:55%;">';
                                        echo '<div style="position:relative;display:inline-block;width:45%;">'.mainFunc::gab_todata($e['d_rif']).'</div>';
                                        echo '<div style="position:relative;display:inline-block;width:20%;">Km:</div>';
                                        echo '<div style="position:relative;display:inline-block;width:30%;">'.$e['km'].'</div>';
                                    echo '</div>';

                                    echo '<div style="position:relative;display:inline-block;width:45%;">(';
                                        echo '<div style="position:relative;display:inline-block;width:14%;font-size:0.9em">Δt:</div>';
                                        echo '<div style="position:relative;display:inline-block;width:22%;">'.($e['deltat']?$e['deltat']:"").'</div>';
                                        echo '<div style="position:relative;display:inline-block;width:18%;font-size:0.9em;">Δkm:</div>';
                                        echo '<div style="position:relative;display:inline-block;width:37%;">'.($e['deltakm']?$e['deltakm']:"").'</div>';
                                    echo ')</div>';
                                    
                                echo '</div>';
                            }
                    echo '</td>';
                echo '</tr>';

            echo '</tbody>';

        echo '</table>';
    }*/

    function drawPratiche() {

        echo '<div id="storicoElencoPratiche">';

            $this->fidel->drawLista();

            foreach ($this->pratiche as $k=>$p) {

                echo '<div style="position:relative;margin-top:10px;">';

                    if (isset($p['manuale'])) $this->drawPassMan($p);
                    elseif (isset($p['comest'])) $this->drawComest($p);

                    else {
                        echo '<div style="position:relative;border-left:2px solid #830aa1;border-top:2px solid #830aa1;width:30%;overflow-x:visible;padding:2px;box-sizing:border-box;" >';

                            echo '<div style="position:relative;font-weight:bold;font-size:0.9em;width:300%;" >';
                                echo 'Pratica: '.mainFunc::gab_todata($p['d_rif']).' ('.$p['dms'].')';
                            echo '</div>';

                        echo '</div>';

                        echo '<div style="position:relative;border-left:2px solid #830aa1;width:100%;padding:2px;box-sizing:border-box;">';

                            echo '<div style="position:relative;margin-top:5px;">';

                                if ($p['prenotazione']) {

                                    $blkl=new BlockList('pre_'.$k,0);

                                    $txt='<div style="position:relative;transform:translate(0px,-20%);">Prenotazione:<span style="margin-left:10px;">'.($p['prenotazione']['info']['d_pren']!=""?mainFunc::gab_todata($p['prenotazione']['info']['d_pren']):"").'</span></div>';

                                    $blkl->setHead($txt);

                                    ob_start();
                                        echo '<div style="opacity:0.7;background-color:#fdface;width:95%;" >';
                                            $p['prenotazione']['obj']->drawBody();
                                        echo '</div>';

                                    $blkl->setBody(ob_get_clean());

                                    echo $blkl->draw();
                                }

                                foreach ($p['commesse'] as $c) {

                                    //###################################
                                    //dati commessa
                                    /*{
                                        "d_rif": "20220303",
                                        "info": {
                                        "rif": 2632,
                                        "cod_officina": "PV",
                                        "id_veicolo": 5028041,
                                        "pratica": "i202203030902",
                                        "prenotazione": 431,
                                        "ind_chiuso": "S",
                                        "num_doc": 101617,
                                        "d_rif": "20220303",
                                        "d_pren":"20220303",
                                        "d_entrata": "20220303",
                                        "d_fatt": "20220303",
                                        "cm": "LO01",
                                        "cod_accettatore": "a.giampaolo",
                                        "util_ragsoc": "",
                                        "intest_ragsoc": "CECCARELLI ANTONIO",
                                        "km":56782,
                                        "dms": "infinity"
                                        }
                                    */
                                    //###################################

                                    //intestazione documento

                                    $valori=$c['obj']->getValori();

                                    echo '<div style="border:1px solid #777777;width:95%;';
                                        if ($c['info']['d_fatt']=='') echo 'background-color:#ffc65f;';
                                    echo '">';

                                        echo '<div style="position:relative;display:inline-block;vertical-align:top;width:15%;border-right:1px solid #777777;box-sizing:border-box;padding:2px;">';
                                            echo '<div style="width:100%;text-align:center;">'.$c['info']['cm'].'</div>';
                                            echo '<div style="width:100%;text-align:center;">'.$c['info']['cod_officina'].' -<span style="margin-left:3px;font-size:0.9em;">'.$c['info']['rif'].'</span></div>';
                                            echo '<div style="width:100%;text-align:center;">Km:<span style="margin-left:3px;font-weight:bold;font-size:0.9em;">'.number_format($c['info']['km'],0,"",".").'</span></div>';
                                        echo '</div>';

                                        echo '<div style="position:relative;display:inline-block;vertical-align:top;width:62%;border-right:1px solid #777777;box-sizing:border-box;padding:2px;">';
                                            echo '<div>';
                                                echo '<div style="position:relative;display:inline-block;vertical-align:top;width:35%;text-align:left;font-weight:bold;">Ordine: '.mainFunc::gab_todata($c['info']['d_entrata']).'</div>';
                                                echo '<div style="position:relative;display:inline-block;vertical-align:top;width:35%;text-align:left;">Fattura: '.($c['info']['d_fatt']!=''?mainFunc::gab_todata($c['info']['d_fatt']):'<b>APERTO</b>').'</div>';
                                                //echo '<div style="position:relative;display:inline-block;vertical-align:top;width:30%;text-align:left;">Km: '.$c['info']['km'].'</div>';
                                            echo '</div>';
                                            echo '<div style="width:100%;text-align:left;">'.($c['info']['intest_ragsoc']==''?($c['info']['util_ragsoc']==''?'&nbsp;':$c['info']['util_ragsoc']):$c['info']['intest_ragsoc']).'</div>';
                                            echo '<div style="width:100%;text-align:left;font-size:0.9em;">'.($c['info']['intest_ragsoc']==''?'&nbsp;':($c['info']['util_ragsoc']==''?'&nbsp;':$c['info']['util_ragsoc'])).'</div>';
                                        echo '</div>';

                                        echo '<div style="position:relative;display:inline-block;vertical-align:top;width:23%;box-sizing:border-box;padding:2px;">';
                                            echo '<div style="width:100%;text-align:center;font-size:0.9em;">'.$c['info']['cod_accettatore'].'</div>';
                                            echo '<div style="width:100%;text-align:left;">';
                                                echo '<div style="position:relative;display:inline-block;width:40%;font-size:0.9em;">Imponibile:</div>';
                                                echo '<div style="position:relative;display:inline-block;width:60%;text-align:right;">'.number_format($valori['tot']-$valori['sconto']['tot'],2,',','').'</div>';
                                            echo '</div>';
                                            echo '<div style="width:100%;text-align:left;">';
                                                echo '<div style="position:relative;display:inline-block;width:40%;font-size:0.9em;">Ivato:</div>';
                                                echo '<div style="position:relative;display:inline-block;width:60%;text-align:right;font-weight:bold;">'.number_format($valori['ivato']['tot'],2,',','').'</div>';
                                            echo '</div>';
                                        echo '</div>';

                                    echo '</div>';

                                    echo '<div>';
                                        $c['obj']->drawBody();
                                    echo '</div>';
                                }

                            echo '</div>';

                        echo '</div>';

                        echo '<div style="position:relative;border-top:2px solid #830aa1;width:100%;padding:2px;box-sizing:border-box;width:95%;">';
                        
                        echo '</div>';
                    }

                echo '</div>';
            }

        echo '</div>';

        $div=new nebulaUtilityDiv('storico','window._nebulaStorico.closeStoricoUtil()');
        $div->draw();

        /*echo '<div id="storicoUtilDiv" style="width:100%;-height:100%;display:none;">';

            echo '<div style="width:100%;height:40px;text-align:right;">';
                //echo '<img style="width:30px;height:30px;position:relative;top:50%;transform:translate(0px,-50%);margin-right:10px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/storico/img/chiudi.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].closeStoricoUtil();" />';
                echo '<img style="width:30px;height:30px;position:relative;top:50%;transform:translate(0px,-50%);margin-right:10px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/storico/img/chiudi.png" onclick="window._nebulaStorico.closeStoricoUtil();" />';
            echo '</div>';

            echo '<div id="storicoUtilDivBody" ></div>';

        echo '</div>';*/

        /*echo '<div>';
            echo json_encode($this->pratiche);
        echo '</div>':*/
    }

    function drawPrevisione() {

        $dprev=new Divo('sto_prev','5%','94%',true);

        $dprev->setBk('#d8bfd8');

        $css=array(
            "font-weight"=>"bold",
            "font-size"=>"1em",
            "margin-left"=>"15px",
            "margin-top"=>"0px"
        );

        ob_start();

            echo '<div style="position:relative;width:100%;height:10%;border-bottom:1px solid black;" >';

                $error="";

                if ($this->piano->getCodice()=="") $error.=' - Nessun piano di manutenzione';

                //se esiste il veicolo ma non c'è una data di consegna
                if (isset($this->actualVei['rif']) && (!isset($this->actualVei['d_cons']) || $this->actualVei['d_cons']=="") ) $error.=' - Nessuna data di consegna';

                if ($error!="") {
                    echo '<div style="font-weight:bold;color:red;" >'.$error.'</div>';
                }
                else {

                    $dCons=(isset($this->actualVei['d_cons']) && $this->actualVei['d_cons']!="")?$this->actualVei['d_cons']:( (isset($this->param['sto_consegna']) && $this->param['sto_consegna']!="")?$this->param['sto_consegna']:"");

                    echo '<div style="position:relative;display:inline-block;width:50%;vertical-align:top;margin-top:3px;" >';

                        echo '<div style="position:relative;display:inline-block;width:35%;vertical-align:top;" >';
                            echo '<div style="font-weight:bold;font-size:0.9em;">Km attuali:</div>';
                            echo '<div style="position:relative;" >';
                                //echo '<input id="sto_prev_actualKm" type="text" style="width:100px;" value="'.$this->param['sto_km'].'" onkeydown="if(event.keyCode==13) window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].calcolaPrevisione();"/>';
                                echo '<input id="sto_prev_actualKm" type="text" style="width:100px;" value="'.$this->param['sto_km'].'" onkeydown="if(event.keyCode==13) window._nebulaStorico.calcolaPrevisione();"/>';
                            echo '</div>';
                        echo '</div>';

                        echo '<div style="position:relative;display:inline-block;width:45%;vertical-align:top;" >';
                            echo '<div style="font-weight:bold;;font-size:0.9em;">Data di consegna:</div>';
                            echo '<div style="position:relative;" >';
                                echo '<input id="sto_prev_actualCons" type="date" style="width:150px;" value="'.($dCons==''?$dCons:mainFunc::gab_toinput($dCons)).'" ';
                                    if (isset($this->actualVei['d_cons']) && $this->actualVei['d_cons']!="") echo ' disabled ';
                                echo '/>';
                            echo '</div>';
                        echo '</div>';

                        echo '<div style="position:relative;display:inline-block;width:20%;vertical-align:top;" >';
                            //echo '<button style="margin-top:15px;" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].calcolaPrevisione();" >Calcola</button>';
                            echo '<button style="margin-top:15px;" onclick="window._nebulaStorico.calcolaPrevisione();" >Calcola</button>';
                        echo '</div>';


                        $temp=array();

                        foreach ($this->eventi as $codice=>$c) {
                            foreach ($c as $k=>$e) {
                                $temp[$codice]=array(
                                    'd_rif'=>$e['d_rif'],
                                    'km'=>$e['km']
                                );
                                break;
                            }
                        }

                        echo '<input id="sto_prev_eventi" type="hidden" value="'.base64_encode(json_encode($temp)).'" />';

                        /*echo '<script type="text/javascript">';
                            echo 'windows._nebulaApp_storico.setPrevisione($("#sto_prev_gruppo").val());';
                        echo '</script>';*/

                    echo '</div>';

                    echo '<div style="position:relative;display:inline-block;width:29%;vertical-align:top;margin-top:3px;" >';
                        
                        echo '<div style="position:relative;display:inline-block;width:90%;vertical-align:top;margin-left:3%;">';
                            echo '<div style="font-weight:bold;" >Km al mese calcolati:</div>';
                            echo '<div id="sto_prev_mese" style="position:relative;display:inline-block;width:60%;height:20px;vertical-align:top;border:1px solid black;text-align:center;font-weight:bold;background-color: #ecf1a5;"></div>';
                        echo '</div>'; 
                    
                    echo '</div>';

                    echo '<div style="position:relative;display:inline-block;width:15%;vertical-align:top;" >';
                        echo '<img id="storico_print_icon" style="position:relative;width:40px;height:40px;margin-top:5px;cursor:pointer;display:none;" data-vei="'.base64_encode(json_encode($this->actualVei)).'" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/storico/img/print.png" onclick="window._nebulaStorico.printPrevisione();" />';
                    echo '</div>';
                }

            echo '</div>';

            echo '<div id="sto_prev_div" style="position:relative;width:100%;height:88%;" >';
                //###################
                //previsione
                //###################
            echo '</div>';
        
        $dprev->add_div('Calcolo','black',0,"",ob_get_clean(),0,$css);

        ob_start();
            
            echo '<div style="position:relative;display-inline-block;vertical-align:top;height:100%;width:60%;border-right:1px solid black;box-sizing:border-box;overflow:scroll;overflow-x:hidden;padding:2px;" >';
                $this->piano->drawPack();
            echo '</div>';
            echo '<div style="position:relative;display-inline-block;vertical-align:top;height:100%;width:40%;box-sizing:border-box;overflow:scroll;overflow-x:hidden;padding:2px;" >';
            echo '</div>';
        $dprev->add_div('Pacchetti','black',0,"",ob_get_clean(),0,$css);

        $dprev->build();

        $dprev->draw();
        
    }
    
    function drawPassMan($p) {

        echo '<div style=";width:95%;" >';

            echo '<div style="position:relative;border-left:2px solid pink;border-top:2px solid pink;width:30%;overflow-x:visible;padding:2px;box-sizing:border-box;" >';

                echo '<div style="position:relative;font-weight:bold;font-size:0.9em;color:black;width:300%;" >';
                    echo 'Passaggio manuale: ('.$p['info']['indice'].') '.mainFunc::gab_todata($p['d_rif']).' (km: '.number_format($p['info']['km'],0,'','.').')';

                    $obj=$p['info'];
                    $obj['righe']=array();
                    foreach ($p['righe'] as $cod=>$r) {
                        $obj['righe'][]=$cod;
                    }
                    //echo '<img style="position:absolute;right:10px;top:0px;width:15px;height:15px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/storico/img/edit.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].openPassman(\''.base64_encode(json_encode($obj)).'\');" />';
                    echo '<img style="position:absolute;right:10px;top:0px;width:15px;height:15px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/storico/img/edit.png" onclick="window._nebulaStorico.openPassman(\''.base64_encode(json_encode($obj)).'\');" />';
                echo '</div>';
            
            echo '</div>';

            echo '<div style="font-size:0.9em;border-left:2px solid pink;">';

                echo '<div style="position:relative;display:inline-block;width:2%;"></div>';

                echo '<div style="position:relative;display:inline-block;width:96%;">';
                    echo $p['info']['note'];
                echo '</div>';

            echo '</div>';

            echo '<div style="position:relative;width:100%;border-left:2px solid pink;padding:2px;box-sizing:border-box;">';

                foreach ($this->piano->getBase() as $codice=>$b) {

                    if ($this->piano->checkActual($codice) && array_key_exists($codice,$p['righe'])) {
                    //if (isset($this->gruppo['oggettiActual'][$codice]) && array_key_exists($codice,$p['righe'])) {

                        //foreach ($p['righe'] as $r) {
                    
                        echo '<div style="position:relative;">';

                            echo '<div style="position:relative;display:inline-block;width:9%;text-align:right;font-size:0.9em;">';
                            echo '</div>';
                            
                            echo '<div style="position:relative;display:inline-block;width:1%;">&nbsp;</div>';
                            
                            echo '<div style="position:relative;display:inline-block;width:20%;">';
                                echo $codice;
                            echo '</div>';
                
                            echo '<div style="position:relative;display:inline-block;width:25%;">';
                                echo substr(ucfirst(strtolower($p['righe'][$codice]['descrizione'])),0,24);
                            echo '</div>';

                        echo '</div>';
                    }
                }

            echo '</div>';

            echo '<div style="position:relative;border-top:2px solid pink;width:100%;padding:2px;box-sizing:border-box;">';
            echo '</div>';

        echo '</div>';

    }

    function drawComest($p) {
        //echo '<div>'.json_encode($p).'</div>';

        /*
        {"comest":true,"d_rif":"20230530","info":"","righe":[{"rif":45,"versione":1,"targa":"FX677YG","telaio":"WVGZZZA1ZKV162515","descrizione":"T-ROC 1.0 TSI STYLE 115CV BMT MY 18","dms":"infinity","odl":"36840","fornitore":"{\"ID\":\"7\",\"ragsoc\":\"Autocarrozzeria Baioni Attilio Srl\",\"indirizzo\":\"Strada Montefeltro 77 int. 6 Pesaro 61122 Fornace Pica (PU)\",\"mail\":\"\",\"tel1\":\"072125712\",\"nota1\":\"\",\"tel2\":\"3348881987\",\"nota2\":\"Matteo\"}","d_apertura":"20230530","utente_apertura":"m.ghiandoni","d_annullo":"","utente_annullo":"","controllo":"[{\"titolo\":\"Lavoro eseguito correttamente?\",\"opzioni\":[\"SI\",\"NO\"],\"valore\":\"\"},{\"titolo\":\"Scadenza mantenuta?\",\"opzioni\":[\"SI\",\"NO\"],\"valore\":\"\"}]","utente_controllo":"","d_controllo":"","nota":"","riconsegna":"20230607"}]}
        */

        $fornitore=json_decode($p['info']['fornitore'],true);

        echo '<div style="position:relative;border-left:2px solid orange;border-top:2px solid orange;width:30%;overflow-x:visible;padding:2px;box-sizing:border-box;" >';

                echo '<div style="position:relative;font-weight:bold;font-size:0.9em;color:black;width:300%;" >';
                    echo 'Commessa Esterna: ('.$p['info']['rif'].') '.mainFunc::gab_todata($p['d_rif']).' - '.$fornitore['ragsoc'];
                echo '</div>';

                echo '<div style="position:relative;font-size:0.9em;color:black;width:300%;';
                    if ($p['info']['chiusura']=='') echo 'color:red;font-weight:bold;';
                echo '" >';
                    if ($p['info']['chiusura']=='') echo 'APERTA';
                    else echo 'Chiusura: '.mainFunc::gab_todata($p['info']['chiusura']);
                echo '</div>';

                echo '<div style="position:relative;font-size:1em;color:black;width:300%;" >';

                    foreach ($p['righe'] as $x=>$r) {
                        echo '<div style="position:relative;width:100%;">';
                            echo '<div style="position:relative;display:inline-block;vertical-align:top;width:40%;" >'.$r['titolo'].'</div>';
                            echo '<div style="position:relative;display:inline-block;vertical-align:top;width:60%;" >'.$r['descrizione'].'</div>';
                        echo '</div>';
                    }

                echo '</div>';

        echo '</div>';
    }

}

?>