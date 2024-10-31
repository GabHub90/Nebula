<?php
require_once('wormhole.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/core/divo/divo.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/core/blocklist/blocklist.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/core/fidel/fidel.php');

include('odl_func.php');
include('odl_linker.php');
include('odl_body.php');
include('pratica_func.php');

class nebulaOdielle extends appSlaveClass {

    protected $quickList=array();

    protected $odlFunc;
    protected $pratica=false;

    //rappresenta l'odl attuale
    protected $main=array(
        "dms"=>"",
        "rifOdl"=>"",
        "officinaOdl"=>"",
        "actualQuick"=>"",
        "mainView"=>"",
        "data_i"=>"",
        "data_f"=>"",
        "overall"=>array(),
        "wormhole"=>array(),
        "linker"=>array(),
        "dates"=>array(
            "d_pren"=>"",
            "d_ricon"=>"",
            "d_start"=>"",
            "d_stop"=>""
        ),
    );

    /*
    OVERALL
    [[{"i":"00:00","f":"00:00","info":{"reg":0,"str":0,"day":0}}],[{"i":"08:00","f":"20:00","info":{"reg":12,"str":0,"day":1}}],[{"i":"08:00","f":"20:00","info":{"reg":12,"str":0,"day":1}}],[{"i":"08:00","f":"20:00","info":{"reg":12,"str":0,"day":1}}],[{"i":"08:00","f":"20:00","info":{"reg":12,"str":0,"day":1}}],[{"i":"08:00","f":"20:00","info":{"reg":12,"str":0,"day":1}}],[{"i":"08:00","f":"13:00","info":{"reg":0,"str":0,"day":0}}]]
    */

    protected $struttura=array("odl","ricezione","linker");

    /*
    {"rif":"1387722","cod_officina":"PV","cod_officina_prenotazione":"PV","cod_stato_commessa":"RP","cod_movimento":"OOP","num_commessa":"0","ind_preventivo":"N","cod_tipo_trasporto":"NO","ind_chiuso":"N","d_inserimento":"20211227","cod_utente_inserimento":"e.giannotti","d_apertura":"xxxxxxxx:xx:xx","d_pren":"20220107:08:00","d_ricon":"20220107:18:15","d_entrata":"xxxxxxxx:xx:xx","d_fine":"xxxxxxxx:xx:xx","num_rif_veicolo":"51151","num_rif_veicolo_progressivo":1,"cod_anagra_util":"87507","cod_anagra_intest":"","cod_anagra_locat":"","cod_anagra_fatt":"","cod_accettatore":"e.giannotti","km":"60000","dms":"concerto"}
    */

    protected $info=array(
        "rif"=>"",
        "cod_officina"=>"",
        "cod_officina_prenotazione"=>"",
        "cod_stato_commessa"=>"",
        "cod_movimento"=>"",
        "num_commessa"=>"",
        "ind_preventivo"=>"",
        "cod_tipo_trasporto"=>"",
        "ind_chiuso"=>"",
        "d_inserimento"=>"",
        "cod_utente_inserimento"=>"",
        "d_apertura"=>"",
        "d_pren"=>"",
        "d_ricon"=>"",
        "d_entrata"=>"",
        "d_fine"=>"",
        "num_rif_veicolo"=>"",
        "num_rif_veicolo_progressivo"=>"",
        "telaio"=>"",
        "targa"=>"",
        "des_veicolo"=>"",
        "cod_marca"=>"",
        "des_marca"=>"",
        "cod_anagra_util"=>"",
        "cod_anagra_intest"=>"",
        "cod_anagra_locat"=>"",
        "cod_anagra_fatt"=>"",
        "util_ragsoc"=>"",
        "intest_ragsoc"=>"",
        "cod_accettatore"=>"",
        "km"=>"",
        "dms"=>"",
        "pratica"=>"",
        "pratica_pren"=>"0",
        "pren"=>"",
        "picking"=>0
    );

    protected $noFidel=array(108723,170983,29269,172257);

    protected $wh;
    //protected $linker;

    protected $galileo;

    function __construct($param,$galileo) {

        parent::__construct($galileo);

        $this->odlFunc=new nebulaOdlFunc($galileo);
        $this->odlBody=new nebulaOdlBody($this->odlFunc,$galileo);

        //{"nebulaFunzione":{"nome":"avalon"},"appArgs":{"rif":"1370788","dms":"concerto"},"officina":"VWS"}
        $this->param['officina']="";

        //lista è fondamentale per Infinity [cli,pre]
        $this->param['appArgs']=array(
            "rif"=>"",
            "dms"=>"",
            "lista"=>""
        );

        $this->loadParams($param);

        if ($this->param['officina']=='') die ('Officina non configurata!!');
        if ($this->param['appArgs']['dms']=="") die ('DMS non definito!!');

        $this->main['dms']=$this->param['appArgs']['dms'];
        if ($this->param['appArgs']['rif']!="") $this->main['rifOdl']=$this->param['appArgs']['rif'];

        //echo json_encode($param['ribbon']);

        ///////////////////////////////////////////////////////////////////////////////////
        /*
        - se esiste rif e dms significa che siamo in un odl reale
            # deve essere letta dal dms e impostata l'occupazione della risorsa (per evitare modifiche da più utenti)

        - se non esiste rif e dms
            # leggere i riferimenti QUICK per il collaboratore e gestirli (indice 0 = provvisorio attuale NON salvato)
            # inizializzare l'odl QUICK
        */

        ////////////////////////////////////////////////////////////////////////////////////

        if ($this->main['rifOdl']!="") {

            $this->main['actualQuick']=0;
            //$this->main['officinaOdl']=$this->param['officina'];
            //$this->wh=new odielleWH($this->main['officinaOdl'],$this->galileo);
            //non si sa ancora a quale reparto corrisponde l'odl (wormhole viene forzato)
            $this->wh=new odielleWH('',$this->galileo);
            $this->wh->forceMap( array("dms"=>$this->param['appArgs']['dms']) );
            $this->main['wormhole']=$this->wh->exportMap();

            //leggi i dati dell'odl
            $this->wh->getOdl($this->main['rifOdl'],$this->param['appArgs']['lista']);

            //MI ASPETTO CHE CI SIA UN SOLO ORDINE DI LAVORO ma eseguo il wormhole per compatibilità
            foreach ($this->wh->exportMap() as $m) {

                if($m['result']) {
                    //$pf=$this->wh->getPiattaforma($m['dms']);
                    $fetID=$this->galileo->preFetch('odl');

                    while ($row=$this->galileo->getFetch('odl',$fetID)) {

                        //alimentazione array INFO
                        foreach ($this->info as $k=>$v) {
                            if (array_key_exists($k,$row)) {
                                $this->info[$k]=$row[$k];
                            }
                        }

                        $this->info['dms']=$this->param['appArgs']['dms'];

                        $temprep=$this->odlFunc->getNebulaRep($this->info['dms'],$this->info['cod_officina']);

                        if (!$temprep) die('officina '.$this->info['cod_officina'].' non identificata per il DMS '.$this->info['dms'].'!!!');

                        $this->main['officinaOdl']=$temprep;

                        //#####################
                        //provvisoriamente
                        $this->main['data_i']=date('Ymd');
                        $this->main['data_f']=date('Ymd');
                        //#################################

                    }
                }
            }

            if($this->info['cod_stato_commessa']=="") $this->info['cod_stato_commessa']='XX';

            //set linker
            $this->main['linker']=array(
                "veicolo"=>$this->info['num_rif_veicolo'],
                "abbinamento"=>$this->info['num_rif_veicolo_progressivo'],
                "util"=>$this->info['cod_anagra_util'],
                "intest"=>$this->info['cod_anagra_intest'],
                "locat"=>$this->info['cod_anagra_locat'],
                "fatt"=>$this->info['cod_anagra_fatt'],
                "km"=>$this->info['km'],
                "dms"=>$this->info['dms']
            );

            //set dates
            $this->main['dates']['d_pren']=$this->info['d_pren'];
            $this->main['dates']['d_ricon']=$this->info['d_ricon'];

            //######################################################
            //######################################################

            //PRATICA - ALERT
            $this->pratica=new nebulaPraticaFunc($this->info['pratica'],$this->info['pratica_pren'],$this->info['dms'],$this->info['pren'],$this->odlFunc);
            $this->pratica->setDefaultAlert();

            $this->odlBody->loadPratica($this->pratica);

            //caricamento lamentati (oggetto LAMENTATI) e definizione "d_start" e d_stop"
            $this->odlBody->build($this->main['rifOdl'],$this->param['appArgs']['lista'],$this->wh);

            //legge le richieste materiale di tutta la pratica
            $this->pratica->richiesteMateriale();

            $this->odlBody->loadRichieste($this->pratica->getRichiesteMaterialeRif($this->main['rifOdl'],$this->info['pren']));

            //######################################################
            //######################################################

            if ($this->main['dates']['d_pren']=="") $this->main['dates']['d_pren']=date('Ymd:H:i');
            if ($this->main['dates']['d_start']=="") $this->main['dates']['d_start']=$this->main['dates']['d_pren'];
            if ($this->main['dates']['d_stop']=="") $this->main['dates']['d_stop']=date('xxxxxxxx:xx:xx');

        }

        else {

            //echo '###'.$this->param['appArgs']['rif'].'###';

            if ($this->main['actualQuick']=="") {

                //se ci sono degli ordini quick aperti permettere la selezione, altrimenti...
                if ( count($this->quickList)==0 ) {

                    //$this->main['mainView']="linker";
                    $this->main['actualQuick']=0;
                    $this->main['officinaOdl']=$this->param['officina'];

                    if ($this->main['data_i']=="") $this->main['data_i']=date('Ymd');
                    if ($this->main['data_f']=="") $this->main['data_f']=date('Ymd');
                
                }
            }

            ///////////////////////////////////////////////////////////////////////////

            //inizializzare WH in base all'officina dell'ODL o a quella attuale di nebula
            //eventualmente a posteriori è possibile modificare l'officina SOLO all'interno dello stesso DMS
            //la data per il periodo di build è la data di riferimento
            //la funzione BUILD non è necessaria se conosciamo già il DMS nel caso di un odl già esistente
            $this->wh=new odielleWH($this->main['officinaOdl'],$this->galileo);
            $this->wh->build( array("inizio"=>$this->main['data_i'],"fine"=>$this->main['data_f']) );
            $this->main['wormhole']=$this->wh->exportMap();
            //$this->wh->build.......

            //????? serve davvero che linker abbia una map ???????
            //$this->linker=new nebulaOdlLinker($this->param['linker'],$this->param['wormhole'],$this->galileo);
        }

        //normalizziamo wormhole prendendo SOLO il primo intervallo considerato che in questo caso c'è per forza una sola occorrenza
        $this->main['wormhole']=$this->main['wormhole'][0];

        ///////////////////////////////
        //lettura degli orari overall del reparto in base al panorama attivo
        $this->galileo->getOrariOA($this->main['officinaOdl'],$this->main['data_i']);
        
        if ($result=$this->galileo->getResult()) {

            $fetID=$this->galileo->preFetchBase('schemi');

            $tempoa=array();

            while ($row=$this->galileo->getFetchBase('schemi',$fetID)) {

                $tempoa=json_decode($row['orari'],true);

                $this->main['overall'][$row['wd']]=array();           

                $tempb=0;

                if ($tempoa) {

                    foreach ($tempoa as $t) {
                        $tempb++;

                        $i=mainFunc::gab_stringtomin($t['i']);
                        $f=mainFunc::gab_stringtomin($t['f']);

                        while ($i<=$f) {

                            $this->main['overall'][$row['wd']][$i]=mainFunc::gab_mintostring($i);
                            $i+=15;
                        }

                        $this->main['overall'][$row['wd']]['b'.$tempb]='-----';
                    }
                }
            }
        }

    }

    function draw() {

        blockList::blockListInit();

        echo '<script type="text/javascript" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/odl/js/nebula_odl_js.js?v='.time().'" ></script>';
        echo '<script type="text/javascript" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/odl/js/nebula_body_js.js?v='.time().'" ></script>';
        echo '<script type="text/javascript" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/odl/js/nebula_linker_js.js?v='.time().'" ></script>';
        echo '<script type="text/javascript" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/odl/js/nebula_horse_js.js?v='.time().'" ></script>';

        echo '<script type="text/javascript" >';
            echo 'var temp='.json_encode($this->main).';';
            echo 'window._nebulaOdl=new nebulaOdl(temp);';
        echo '</script>';

        echo '<div id="nebulaOdlContainerDiv_container" style="position:relative;width:100%;height:100%;" >';
        
            echo '<div style="position:relative;width:100%;height:22%;border-bottom:2px solid black;box-sizing:border-box;padding:5px;" >';

                echo '<div style="position:relative;display:inline-block;width:5%;height:100%;box-sizing:border-box;vertical-align:top;text-align:center;" >';
                    //la funzione di chiusura richiama il contenitore in cui è stato aperto l'ordine
                    echo '<img style="position:relative;width:30px;cursor:pointer;top:50%;transform:translate(0px,-50%);" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/odl/img/chiudi.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].closeOdl();" />';
                echo '</div>';

                echo '<div style="position:relative;display:inline-block;width:95%;height:100%;box-sizing:border-box;vertical-align:top;padding:3px;box-sizing:border-box;" >';
                    
                    echo '<div id="nebulaOdlLinkerDiv" style="position:relative;display:inline-block;width:74%;height:100%;vertical-align:top;">';
                        //$this->linker->drawHead();

                    echo '</div>';

                    echo '<div style="position:relative;display:inline-block;width:25%;height:100%;vertical-align:top;margin-left:1%;box-sizing:border-box;padding:2px;border-image: url(\'http://'.$_SERVER['SERVER_ADDR'].'/nebula/main/img/bordo_div3.png\') 3;border-width:3px;border-style:solid;">';
                        
                        echo '<div id="nebulaOdlInfoDiv" style="background-color:#f5f5dc;border-radius: 10px;width:100%;height:100%;" >';
                        
                            echo '<div id="odielleInfoHeadDiv_odl">';

                                if($this->info['rif']=='') { 
                                    echo '<div style="text-align:center;font-weight:bold;" >Ordine temporaneo</div>';
                                }
                                else {
                                    echo '<div style="text-align:center;" >';
                                        //echo '<span style="font-weight:bold;">'.($this->info['num_commessa']!=0?'Ordine':'Prenotazione').' '.$this->info['rif'].'</span>';
                                        echo '<span style="font-weight:bold;">'.($this->info['pren']=='N'?'Ordine':'Prenotazione').' '.$this->info['rif'].'</span>';
                                        echo '<span style="font-weight:normal;margin-left:5px;">('.$this->info['dms'].')</span>';
                                    echo '</div>';

                                    echo '<div style="text-align:center;height:15px;" >';
                                        if ($this->pratica) {
                                            $tempstato=$this->odlFunc->getStatoOdl($this->pratica->getStato($this->info['rif'],''),$this->info['dms']);
                                        }
                                        else {
                                            $tempstato=$this->odlFunc->getStatoOdl($this->info['cod_stato_commessa'],$this->info['dms']);
                                        }
                                        if ($tempstato) echo '<div style="background-color:'.$tempstato['colore'].'" >'.$tempstato['testo'].'</div>';
                                    echo '</div>';
                                    
                                    echo '<div style="position:relative;text-align:left;margin-top:5px;" >';
                                        echo 'Officina: '.$this->main['officinaOdl'];
                                        if ($this->info['num_commessa']!=0) echo ' (id: '.$this->info['num_commessa'].')';
                                    echo '</div>';

                                    echo '<div style="position:relative;text-align:left;font-size:0.9em;" >';
                                        echo 'Inserito: '.mainFunc::gab_todata($this->info['d_inserimento']).' '.$this->info['cod_utente_inserimento'];
                                    echo '</div>';

                                    if ($this->info['num_commessa']!=0) {
                                        echo '<div style="position:relative;text-align:left;font-size:0.9em;" >';
                                            echo 'Aperto: '.mainFunc::gab_todata($this->info['d_apertura']);
                                        echo '</div>';
                                    }
                                }

                                echo '<input id="odielle_refresh_hidden_rif" type="hidden" value="'.$this->info['rif'].'" />';
                                echo '<input id="odielle_refresh_hidden_dms" type="hidden" value="'.$this->info['dms'].'" />';
                                echo '<input id="odielle_refresh_hidden_lista" type="hidden" value="'.$this->param['appArgs']['lista'].'" />';
                                
                            echo '</div>';

                            echo '<div id="odielleInfoHeadDiv_linker" style="display:none;">';
                            echo '</div>';

                            echo '<div id="odielleInfoHeadDiv_dedalo" style="display:none;">';
                            echo '</div>';

                        echo '</div>';

                    echo '</div>';

                echo '</div>';
                
            echo '</div>';

            echo '<div style="position:relative;display:inline-block;width:5%;height:87%;border-right:1px solid black;box-sizing:border-box;vertical-align:top;" >';

                echo '<input id="avalon_sto_ambito" type="hidden" value="standard" />';

                /*echo '<div style="position:relative;text-align:center;margin-top:20px;">';
                    echo '<img style="position:relative;width:35px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/odl/img/quick.png" onclick="" />';
                echo '</div>';*/

                echo '<div style="position:relative;text-align:center;margin-top:15px;">';
                    echo '<img style="position:relative;width:35px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/odl/img/odl.png" onclick="window._nebulaOdl.setOdl(0);" />';
                echo '</div>';

                echo '<div style="position:relative;text-align:center;margin-top:30px;">';
                    echo '<img style="position:relative;width:35px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/odl/img/dedalo2.png" onclick="window._nebulaOdl.setDedalo();" />';
                echo '</div>';

                /*echo '<div style="position:relative;text-align:center;margin-top:40px;">';
                    echo '<img style="position:relative;width:35px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/odl/img/storico.png" onclick="" />';
                echo '</div>';*/

                echo '<div style="position:relative;text-align:center;margin-top:30px;">';
                    echo '<img style="position:relative;width:35px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/odl/img/ricezione.png" onclick="" />';
                echo '</div>';

                echo '<div style="position:relative;text-align:center;margin-top:30px;">';
                    echo '<img id="odielle_apriGDM_icon" style="position:relative;width:35px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/odl/img/gdm.png" data-dms="'.$this->info['dms'].'" data-telaio="'.$this->info['telaio'].'" onclick="window._nebulaOdl.apriGDM();" />';
                echo '</div>';

                $tcomest=array(
                    "ribbon"=>array(
                        "comest_tt"=>(isset($this->info['telaio']) && $this->info['telaio']!="")?$this->info['telaio']:((isset($this->info['targa']) && $this->info['targa']!="")?$this->info['targa']:"")
                    ),
                    "ambito"=>"odl",
                    "new"=>array(
                        "telaio"=>$this->info['telaio'],
                        "targa"=>$this->info['targa'],
                        "descrizione"=>$this->info['des_veicolo'],
                        "dms"=>$this->info['dms'],
                        "odl"=>$this->info['rif'],
                        "utente"=>$this->id->getLogged()
                    )
                );

                echo '<div style="position:relative;text-align:center;margin-top:30px;">';
                    echo '<img id="odielle_apriComest_icon" style="position:relative;width:35px;cursor:pointer;" data-info="'.base64_encode(json_encode($tcomest)).'" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/odl/img/comest.png" onclick="window._nebulaOdl.apriComest(this);" />';
                echo '</div>';

                $thorse=array(
                    "telaio"=>$this->info['telaio'],
                    "marca"=>$this->info['des_marca'],
                    "targa"=>$this->info['targa'],
                    "descrizione"=>$this->info['des_veicolo'],
                    "cavaliere_intest"=>$this->info['intest_ragsoc'],
                    "cavaliere_util"=>$this->info['util_ragsoc']
                );

                echo '<div style="position:relative;text-align:center;margin-top:30px;">';
                    echo '<img id="odielle_apriHorse_icon" style="position:relative;width:35px;cursor:pointer;" data-info="'.base64_encode(json_encode($thorse)).'" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/odl/img/horse.png" onclick="window._nebulaOdl.apriHorse(this);" />';
                echo '</div>';
                
            echo '</div>';

            echo '<div id="nebulaOdlMain" style="position:relative;display:inline-block;width:94%;height:87%;vertical-align:top;padding:5px;box-sizing:border-box;" >';
                
                foreach ($this->struttura as $s) {
                    
                        echo '<div id="nebulaOdlMainDiv_'.$s.'" class="nebulaOdlMainDiv" style="width:100%;height:100%;display:none;">';

                            switch($s) {

                                case 'odl':
                                    //formattazione ODL
                                    $this->drawOdl();
                                break;
                            }

                        echo '</div>';
                }

            echo '</div>';

        echo '</div>';

        echo '<div id="nebulaOdlContainerDiv_utility" style="position:relative;width:100%;height:100%;display:none;" >';
            
            echo '<img style="position:absolute;top:5px;right:20px;width:30px;height:30px;z-index:20;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/odl/img/back.png" onclick="window._nebulaOdl.chiudiUtility();" />';
            echo '<div id="nebulaOdlContainerDiv_utility_body" style="width:100%;height:100%;" ></div>';

        echo '</div>';

        echo '<script type="text/javascript" >';
            echo 'window._nebulaOdl.drawAll();';
        echo '</script>';
    }

    function drawOdl() {

        echo '<div style="position:relative;height:10%;padding:3px;box-sizing:border-box;" >';

            //dat_inserimento (MOVTES) è la data di creazione del movimento
            //dat_prenotazione e dat_promessa_riconsegna sono le date di accordo con il cliente
            //dat_entrata e dat_uscita sono le date che definiscono al disponibilità del veicolo in officina
            //dat_fine è la data in cui è prevista la fine dei lavori
            //dat_split è la data di ricezione (iniziale) del veicolo

            echo '<div style="position:relative;display:inline-block;vertical-align:top;width:10%;text-align:center;" >';
                echo '<img style="position:relative;width:25px;height:25px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/odl/img/reload.png" onclick="window._nebulaOdl.refreshOdl();" />';
                echo '<img style="position:relative;width:25px;height:25px;cursor:pointer;margin-left:20px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/odl/img/save.png" />';
            echo '</div>';

            echo '<div style="position:relative;display:inline-block;vertical-align:top;width:10%;" >';

                echo '<div style="position:relative;font-size:0.8em;font-weight:bold;text-align:center;">Trasporto</div>';

                echo '<div style="position:relative;text-align:center;">';

                    echo '<select style="font-size:0.8em;">';
                        echo '<option value="NO" >NESSUNO</option>';
                        foreach ($this->odlFunc->exportTrasporto($this->main['wormhole']['dms']) as $k=>$t) {
                            if ($k=='NO') continue;
                            echo '<option value="'.$k.'" '.($k==$this->info['cod_tipo_trasporto']?'selected':'').' >'.$t['testo'].'</option>';
                        }
                    echo '</select>';
                echo '</div>';
            echo '</div>';

            echo '<div style="position:relative;display:inline-block;vertical-align:top;width:20%;height: 45px;" >';

                echo '<img style="position:absolute;left:0px;top:-10%;width:100%;height:120%;z-index:0;opacity:0.4;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/odl/img/backerror.png" />';

                echo '<div style="">';
                    echo '<div style="position:relative;font-size:0.8em;font-weight:bold;text-align:center;">Appuntamento:</div>';
                    echo '<div style="position:relative;text-align:center;">';
                        echo '<input id="odielleMainDate_pren" style="width:65%;font-size:0.9em;background-color:white;" type="date" data-suff="pren" value="'.mainFunc::gab_toinput(substr($this->main['dates']['d_pren'],0,8)).'" />';
                        echo '<select id="odielleMainDateSelect_pren" style="width:25%;font-size:0.9em;margin-left:3%;" >';
                        echo '</select>';
                    echo '</div>';
                echo '</div>';

            echo '</div>';

            echo '<div style="position:relative;display:inline-block;vertical-align:top;width:20%;height: 45px;" >';
                echo '<div style="position:relative;font-size:0.8em;font-weight:bold;text-align:center;">Promessa Riconsegna:</div>';
                echo '<div style="position:relative;text-align:center;">';
                    echo '<input id="odielleMainDate_ricon" style="width:65%;font-size:0.9em;" type="date" data-suff="ricon" value="'.mainFunc::gab_toinput(substr($this->main['dates']['d_ricon'],0,8)).'" />';
                    echo '<select id="odielleMainDateSelect_ricon" style="width:25%;font-size:0.9em;margin-left:3%;" >';
                    echo '</select>';
                echo '</div>';
            echo '</div>';

            echo '<div style="position:relative;display:inline-block;vertical-align:top;width:20%;height: 45px;" >';
                echo '<div style="position:relative;font-size:0.8em;font-weight:bold;text-align:center;">Inizio Officina:</div>';
                echo '<div style="position:relative;text-align:center;">';
                    echo '<input id="odielleMainDate_start" style="width:65%;font-size:0.9em;" data-suff="start" type="date" value="'.mainFunc::gab_toinput(substr($this->main['dates']['d_start'],0,8)).'" />';
                    echo '<select id="odielleMainDateSelect_start" style="width:25%;font-size:0.9em;margin-left:3%;" >';
                    echo '</select>';
                echo '</div>'; 
            echo '</div>';

            echo '<div style="position:relative;display:inline-block;vertical-align:top;width:20%;height: 45px;" >';
                echo '<div style="position:relative;font-size:0.8em;font-weight:bold;text-align:center;">Fine Prevista:</div>';
                echo '<div style="position:relative;text-align:center;">';
                    echo '<div style="position:relative;display:inline-block;width:55%;font-size:1.1em;" >'.mainFunc::gab_todata(substr($this->main['dates']['d_stop'],0,8)).'</div>';
                    echo '<div style="position:relative;display:inline-block;width:25%;font-size:1.1em;" >'.substr($this->main['dates']['d_stop'],9,5).'</div>';
                echo '</div>';
            echo '</div>';

        echo '</div>';

        /////////////////////////////////////////////////////

        echo '<div id="odielleBodyMain" style="position:relative;display:inline-block;width:55%;height:90%;border-right:1px solid #bbbbbb;vertical-align:top;padding:3px;box-sizing:border-box;" >';

            echo '<div id="odielleBodyMain_1" style="position:relative;padding:2px;box-sizing:border-box;height:99%;overflow:scroll;overflow-x:hidden;margin-top:1%;" >';

                $divo=new Divo('odlMain','6%','93%',true);

                $divo->setBk('#f5f5db');

                $css=array(
                    "font-weight"=>"bold",
                    "font-size"=>"0.9em",
                    "margin-left"=>"8px",
                    "margin-top"=>"0px"
                );

                $css2=array(
                    "width"=>"10px",
                    "height"=>"10px",
                    "top"=>"50%",
                    "transform"=>"translate(0%,-50%)",
                    "right"=>"3px"
                );

                $divo->setChkimgCss($css2);

                ob_start();
                $this->odlBody->drawBody();

                $divo->add_div('Lamentati','black',0,'',ob_get_clean(),1,$css);

                $txt=json_encode($this->pratica->getRichiesteMateriale());
                
                //$txt=json_encode($this->pratica->getLog());

                $divo->add_div('Richieste/Preventivi','black',1,'Y',$txt,0,$css);

                $txt="";

                $divo->add_div('Sostitutiva','black',1,'Y',$txt,0,$css);

                $divo->build();

                $divo->draw();

            echo '</div>';

            echo '<div id="odielleBodyMain_2" style="position:relative;padding:2px;box-sizing:border-box;height:99%;margin-top:1%;display:none;" >';
                echo '<div style="position:relative;height:8%;text-align:right;" >';
                    echo '<img style="position:relative;right:25px;width:30px;height:30px;z-index:20;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/odl/img/back.png" onclick="window._nebulaOdl.chiudiMain2();" />';
                echo '</div>';
                echo '<div id="odielleBodyMain_2_content" style="position:relative;height:92%;" >';
                echo '</div>';
            echo '</div>';

        echo '</div>';

        echo '<div id="odielleBodySide" style="position:relative;display:inline-block;width:45%;height:90%;vertical-align:top;padding:3px;box-sizing:border-box;" >';

            echo '<div id="odielleSideMain" style="position:relative;width:100%;height:100%;" >';
            
                echo '<div style="position:relative;padding:2px;box-sizing:border-box;margin-bottom:15px" >';
                    //echo 'Header + Alert';
                    if ($this->pratica) {
                        $this->pratica->drawLine($this->info['rif'],'',true,$this->info['pren'],'odielle');
                        echo '<div style="font-size:0.9em;font-weight:bold;" >Nota:</div>';
                        echo $this->pratica->getNota($this->info['rif'],'');
                    }
                echo '</div>';

                echo '<div id="odielle_odl_block_edit_'.$this->info['rif'].'" style="background-color: beige;display: none;"></div>';

                echo '<div style="margin-bottom:15px;" >';

                    $bl=new BlockList('alerts',0);
                    $bl->setHead('Alerts');

                    ob_start();
                    
                        echo '<div style="position:relative;padding:2px;box-sizing:border-box;" >';
                            $this->pratica->drawSetAlert($this->info['rif'],'');
                        echo '</div>';

                    $bl->setBody(ob_get_clean());

                    echo $bl->draw();

                echo '</div>';

                /*echo '<div style="position:relative;padding:2px;box-sizing:border-box;border-top:1px solid #777777;" >';
                    echo '<div style="font-weight:bold;">TODO</div>';
                echo '</div>';*/

                $tempcli=($this->info['dms']=='infinity')?$this->main['linker']['intest']:($this->main['linker']['intest']!=''?$this->main['linker']['intest']:$this->main['linker']['util']);

                if (in_array($tempcli,$this->noFidel)) $puntifedelta='Escluso';
                else {
                    $puntifedelta=$this->wh->puntiFedelta($this->info['dms'],$tempcli,date('Ymd',strtotime('-3 year',mainFunc::gab_tots($this->info['d_inserimento']))));
                }

                echo '<div style="position:relative;border-top:1px solid #777777;" >';
                    echo '<div style="position:relative;padding:2px;box-sizing:border-box;font-weight:bold;background-color:wheat;margin-top:5px;" >';
                        echo '<div style="position:relative;display:inline-block;vertical-align:top;width:50%;">PUNTI FEDELTÀ:</div>';
                        echo '<div style="position:relative;display:inline-block;vertical-align:top;width:45%;">'.$puntifedelta.'</div>';
                        //echo '<div>'.$this->info['dms'].' '.$tempcli. ' '.date('Ymd',strtotime('-3 year',mainFunc::gab_tots($this->info['d_inserimento']))).'</div>';
                    echo '</div>';
                echo '</div>';

                echo '<div style="position:relative;padding:2px;box-sizing:border-box;margin-top:5px;width:100%;height:250px;" >';

                    $a=array(
                        "tag"=>$this->info['telaio'],
                        "ben1"=>$this->info['des_veicolo'],
                        "ben2"=>($tempcli==$this->info['cod_anagra_util'])?$this->info['util_ragsoc']:$this->info['intest_ragsoc'],
                        "dms"=>$this->info['dms'],
                        "utente"=>$this->id->getLogged(),
                        "marca"=>$this->info['cod_marca'],
                        "punti"=>(int)$puntifedelta,
                        "odl"=>$this->info['rif']
                    );
                    
                    $fidel=new nebulaFidel('odl',$this->galileo);

                    $fidel->build($a);

                    $fidel->initJS();

                    $fidel->draw();

                echo '</div>';

            echo '</div>';

            echo '<div id="odielleSideDedalo" style="position:relative;width:100%;height:100%;overflow:scroll;overflow-x:hidden;display:none;" >';
            echo '</div>';

        echo '</div>';

    }

}

?>