<?php

require_once ($_SERVER['DOCUMENT_ROOT']."/nebula/core/divo/divo.php");
require_once ($_SERVER['DOCUMENT_ROOT'].'/nebula/core/panorama/intervallo.php');

require('prod_tot.php');

class c2rProduttivita_S {

    //SCHEMA array $c2rProdRes['col']:
    // $c2rProdRes['col'] ['totale' || 'inprestito' ] ['col'] [ ID OPERAIO ] [ 'totale' || 'dettaglio' ]
    //nel caso di 'totale' --> [ 'proprio' || 'inprestito' ] ['totale'] --> oggetto TOTALE
    
    //oggetto intervallo
    protected $intervallo;

    protected $param=array();

    protected $c2rProdRes=array(
        "tot"=>array(
            "tag"=>"Totale",
            "totale"=>null
        ),
        "rep"=>array(),
        "col"=>array(
            "totale"=>array(
                "tag"=>"Contesto",
                "col"=>array()
            ),
            "inprestito"=>array(
                "tag"=>"In Prestito",
                "col"=>array()
            )
        ),
        "ext"=>array(
            "rc"=>array(
                "tag"=>'RC',
                "flag"=>true,
                "totale"=>null
            )
        )
    );

    protected $blocco=array(
        "totale"=>array(
            "tag"=>"Totale",
            "flag"=>false,
            "totale"=>null
        ),
        "proprio"=>array(
            "tag"=>"Proprio",
            "flag"=>false,
            "totale"=>null
        ),
        "inprestito"=>array(
            "tag"=>"In Prestito",
            "flag"=>false,
            "totale"=>null
        ),
        "prestato"=>array(
            "tag"=>"Prestato",
            "flag"=>false,
            "totale"=>null
        )
    );

    //conversione PV - VWS
    protected $convRep=array();
    //conversione VWS - PV
    protected $convOff=array();
    //ARRAY reparti interessati come da richiesta AJAX (vengono passati all'oggetto come stringa)
    protected $reparti=array();
    //collaboratori appartenenti al reparto nel periodo (come da TEMPO)
    protected $collRep=array();

    protected $addebiti=array();

    //tutti i ruoli ricoperti dai collaboratori dei reparti coinvolti
    // [ID_coll][ rows ] in ordine di data di inizio
    protected $ruoli=array();
    //tutti i collaboratori [ID_coll][info]
    protected $coll=array();
    //tutti gli operai [ID operaio][info]
    protected $operai=array();

    /////////
    protected $ruoliSpeciali=array();
    //array [rif.inc] che viene creato durante lo spoglio delle marcature
    protected $listaLam=array();
     //array [rif.inc] che contiene le marcature accodate per una successiva analisi
    protected $accoda=array();

    protected $galileo;

    protected $log=array();

    function __construct($param,$galileo) {

        $this->galileo=$galileo;

        $param['inizio']=str_replace("-","",$param['inizio']);
        $param['fine']=str_replace("-","",$param['fine']);

        //##################################
        //in questo momento viene considerato SOLO concerto,
        //successivamente occorrerà impostare le query per infinity che ritornino gli stessi campi
        //##################################

        $this->reparti=array();
        foreach (explode(',',substr($param['reparti'],0,-1)) as $x) {
            $x=str_replace("'","",$x);
            $this->reparti[$x]=$x;
        }

        $tempInfo=array(
            "agenda"=>true,
            "data_i"=>$param['inizio'],
            "data_f"=>$param['fine']
        );

        $this->intervallo=new quartetIntervallo($tempInfo,$this->reparti,$this->galileo);
        $this->intervallo->calcola();
        $this->intervallo->calcolaIntTot();

        //definisce tutti i collaboratori che appartengono ad ogni reparto in analisi nel periodo
        foreach ($this->reparti as $rep=>$r) {
            $this->collRep[$rep]=$this->intervallo->getCollRep($rep);
        }

        //$this->log[]=$this->galileo->getLog('query');

        //recuperare l'appartenenza dei collaboratori ai reparti (ed ai ruoli) nel periodo
        //$this->galileo->getCollaboratoriIntervallo(substr($param['reparti'],0,-1),$param['inizio'],$param['fine']);
        $this->galileo->getCollaboratoriIntervallo("",$param['inizio'],$param['fine']);
        $result=$this->galileo->getResult();
        if ($result) {
            $fetID=$this->galileo->preFetchBase('maestro');
            while ($row=$this->galileo->getFetchBase('maestro',$fetID)) {
                $this->coll[$row['ID_coll']]=$row;
                if ($row['cod_operaio']!="") {
                    $this->operai[$row['cod_operaio']]=$row;
                    $this->ruoli[$row['cod_operaio']][]=$row;
                }
            }
        }

        //$this->log[]=$this->ruoli;

        //sostituzione dei reparti con le officine di concerto
        $this->galileo->getOfficine();
        $result=$this->galileo->getResult();
        if ($result) {
            $fetID=$this->galileo->preFetchBase('reparti');
            while ($row=$this->galileo->getFetchBase('reparti',$fetID)) {
                $param['reparti']=str_replace("'".$row['reparto']."'","'".$row['concerto']."'",$param['reparti']);
                $this->convRep[$row['concerto']]=$row['reparto'];
                $this->convOff[$row['reparto']]=$row['concerto'];
            }
        }

        $this->param=$param;

        //TEST
        //gli addebiti dipendonbo dal DMS (per il momento consideriamo solo Concerto)
        $this->addebiti=array(
            "OOP"=>array(
                "OCO"=>array(
                    array("cg"=>"","tipo"=>"pag","gruppo"=>""),
                    array("cg"=>"E","tipo"=>"pag","gruppo"=>"pack")
                ),
                "OGO"=>array(
                    array("cg"=>"","tipo"=>"gar","gruppo"=>""),
                    array("cg"=>"O","tipo"=>"gar","gruppo"=>""),
                    array("cg"=>"G","tipo"=>"gar","gruppo"=>""),
                    array("cg"=>"R","tipo"=>"gar","gruppo"=>"")
                ),
                "OGOA"=>array(
                    array("cg"=>"","tipo"=>"gar","gruppo"=>""),
                    array("cg"=>"O","tipo"=>"gar","gruppo"=>""),
                    array("cg"=>"G","tipo"=>"gar","gruppo"=>""),
                    array("cg"=>"R","tipo"=>"gar","gruppo"=>"")
                ),
                "OPO"=>array(
                    array("cg"=>"P","tipo"=>"corr","gruppo"=>"corr")
                )
            ),
            "OOA"=>array(
                "OLP00"=>array(
                    array("cg"=>"","tipo"=>"app","gruppo"=>"")
                ),
                "OLP01"=>array(
                    array("cg"=>"","tipo"=>"app","gruppo"=>"")
                ),
                "OLRIP"=>array(
                    array("cg"=>"","tipo"=>"rip","gruppo"=>"")
                )
            ),
            "OOI"=>array(
                "OIG"=>array(
                    array("cg"=>"","tipo"=>"gzi","gruppo"=>"")
                )
            ),
            "OOPN"=>array(
                "ONCO"=>array(
                    array("cg"=>"","tipo"=>"nac","gruppo"=>"")
                )
            )
        );

        $this->ruoliSpeciali=array(
            "macrogruppo"=>array("RTS"),
            "gruppoxs"=>array()
        );

        //END TEST

        $this->c2rProdRes['tot']['totale']=$this->blocco;

    }



    function getLines() {

        //inizializza i reparti
        foreach ($this->reparti as $rep=>$r) {

            $this->c2rProdRes['rep'][$rep]=array(
                "totale"=>$this->blocco,
                "col"=>array(
                    "proprio"=>array(
                        "tag"=>"Proprio",
                        "col"=>array()
                    ),
                    "inprestito"=>array(
                        "tag"=>"In prestito",
                        "col"=>array()
                    )
                )
            );

            $this->c2rProdRes['rep'][$rep]['totale']['totale']['totale']=new c2rProdTot();
            /*
            $this->c2rProdRes['rep'][$rep]['totale']['proprio']['totale']=new c2rProdTot();
            $this->c2rProdRes['rep'][$rep]['totale']['inprestito']['totale']=new c2rProdTot();
            $this->c2rProdRes['rep'][$rep]['totale']['prestato']['totale']=new c2rProdTot();
            */


            foreach ($this->collRep[$rep] as $IDcoll=>$c) {

                if ($this->coll[$IDcoll]['cod_operaio']!="") {

                    //l'operaio viene marchiato come PROPRIO del contesto di analisi
                    $this->operaio[$this->coll[$IDcoll]['cod_operaio']]['flagContesto']=true;

                    $this->c2rProdRes['rep'][$rep]['col']['proprio']['col'][$this->coll[$IDcoll]['cod_operaio']]=$this->blocco;
                    $this->c2rProdRes['rep'][$rep]['col']['proprio']['col'][$this->coll[$IDcoll]['cod_operaio']]['totale']['totale']=new c2rProdTot();

                    //inizializza collaboratore proprio
                    if (!isset($this->c2rProdRes['col']['totale']['col'][$this->coll[$IDcoll]['cod_operaio']])) {
                        $this->c2rProdRes['col']['totale']['col'][$this->coll[$IDcoll]['cod_operaio']]=array(
                            "totale"=>$this->blocco,
                            "dettaglio"=>array()
                        );
                    }

                    //scrivi la presenza propria del collaboratore da solo e nel reparto
                    //TEMP è un oggetto TIMELINE
                    $temp=$this->intervallo->getCollTot('subs',$rep,$IDcoll);

                    if ($temp) {

                        $temppres=$temp->getPresenza();
                        $tempev=$temp->getEventi();

                        $this->log[]=array($tempev);

                        $this->c2rProdRes['rep'][$rep]['col']['proprio']['col'][$this->coll[$IDcoll]['cod_operaio']]['proprio']['flag']=true;
                        $this->c2rProdRes['rep'][$rep]['col']['proprio']['col'][$this->coll[$IDcoll]['cod_operaio']]['proprio']['totale']=new c2rProdTot();

                        $this->c2rProdRes['col']['totale']['col'][$this->coll[$IDcoll]['cod_operaio']]['totale']['proprio']['flag']=true;

                        if (!isset($this->c2rProdRes['col']['totale']['col'][$this->coll[$IDcoll]['cod_operaio']]['totale']['proprio']['totale'])) {
                            $this->c2rProdRes['col']['totale']['col'][$this->coll[$IDcoll]['cod_operaio']]['totale']['proprio']['totale']=new c2rProdTot();
                        }
                        
                        if ($temppres) {
                            $this->c2rProdRes['rep'][$rep]['col']['proprio']['col'][$this->coll[$IDcoll]['cod_operaio']]['proprio']['totale']->addPresenza($temppres);
                            $this->c2rProdRes['col']['totale']['col'][$this->coll[$IDcoll]['cod_operaio']]['totale']['proprio']['totale']->addPresenza($temppres);
                        }
                        if ($tempev) {
                            $this->c2rProdRes['rep'][$rep]['col']['proprio']['col'][$this->coll[$IDcoll]['cod_operaio']]['proprio']['totale']->addEventi($tempev);
                            $this->c2rProdRes['col']['totale']['col'][$this->coll[$IDcoll]['cod_operaio']]['totale']['proprio']['totale']->addEventi($tempev);
                        }

                        $this->presenzaSpeciale($rep,$this->coll[$IDcoll]['cod_operaio']);
                        
                    }

                }
            }

        }

        ////////////////////////////////////////////////////////////////////////////////////////////////
       
        //lettura della lista delle marcature

        $oby="res.num_rif_movimento,res.cod_inconveniente,CAST (res.cod_operaio AS int),d_inizio,o_inizio";

        $this->galileo->executeGeneric('odl','getRiltem',$this->param,$oby);
        $result=$this->galileo->getResult();

        //$this->log[]=$this->galileo->getLog('query');

        if ($result) {
            $fetID=$this->galileo->preFetch('odl');

            //raggruppamento delle marcature di ogni lamentato
            $tana=array(
                "odl"=>0,
                "lam"=>"",
                "chiuso"=>"",
                "tot_fatt"=>0,
                "tot_marc"=>0,
                "pos_lav"=>0,
                "addebito"=>"",
                "rif"=>"",
                "tot"=>0,
                "tec"=>array(
                    "tot"=>0,
                    "tec"=>array()
                ),
                "spe"=>array(
                    "tot"=>0,
                    "tec"=>array()
                )
            );

            $topo=array(
                "proprio"=>array(
                    "tot"=>0,
                    "lista"=>array()
                ),
                "inprestito"=>array(
                    "tot"=>0,
                    "lista"=>array()
                ),
                "prestato"=>array(
                    "tot"=>0,
                    "lista"=>array()
                )
            );

            while ($row=$this->galileo->getFetch('odl',$fetID)) {

                //se l'operaio non esiste tra gli operai
                if (!array_key_exists($row['cod_operaio'],$this->operai)) continue;

                $valid=false;
                $row['rep_proprio']="";
                $row['presenza']=null;
                
                //se l'operaio è presente in uno dei reparti in analisi nel giorno
                foreach ($this->collRep as $rep=>$c) {

                    $temp=$this->intervallo->getPresenzaCollDay($rep,$this->operai[$row['cod_operaio']]['ID_coll'],$row['d_inizio']);

                    if ($temp) {
                        $row['presenza']=$temp;
                        $row['rep_proprio']=$rep;
                        $valid=true;
                        break;
                    }
                }

                //se la marcatura appartiene ad un reparto in analisi
                if (!$valid) {
                    if (array_key_exists($this->convRep[$row['cod_officina']],$this->reparti)) {
                        $valid=true;
                    }
                }

                //se la marcatura non è coerente al contesto scartala
                if (!$valid) continue;


                //ADDEBITO
                $row['c2rTipo']='ind';

                //se il lamentato è già stato visionato non ricalcolare l'addebito
                if (isset($this->listaLam[$row['num_rif_movimento'].$row['cod_inconveniente']])) {
                    $row['c2rTipo']=$this->listaLam[$row['num_rif_movimento'].$row['cod_inconveniente']]['addebito'];
                }
                elseif (isset($this->addebiti[$row['cod_movimento']][$row['acarico']])) {

                    foreach ($this->addebiti[$row['cod_movimento']][$row['acarico']] as $a) {
                        if ($a['cg']=='' || $a['cg']==$row['cod_tipo_garanzia']) {
                            $row['c2rTipo']=$a['tipo'];
                            $row['c2rGruppo']=$a['gruppo'];

                            if ($row['c2rTipo']=='corr') $row['c2rTipo']='pag';
                            ///////////
                            $this->listaLam[$row['num_rif_movimento'].$row['cod_inconveniente']]['addebito']=$row['c2rTipo'];
                            ////////////
        
                            if($a['cg']==$row['cod_tipo_garanzia']) break;
                        }
                    }
                }

                //############################################

                $row['ruolo']=$this->trovaRuolo($row['cod_operaio'],$row['d_inizio']);

                //$tempcont=$row['presenza']?'totale':'inprestito';
                //in base al CONTESTO e NON al singolo reparto
                $row['c2rContesto']=isset($this->operaio[$row['cod_operaio']]['flagContesto'])?'totale':'inprestito';

                $speciale=$this->determinaSpeciale($row['ruolo']['mgruppo']);

                $rif=$this->getRif($row);

                //############################################
                
                //############################################

                //$row['c2rStato']='OK';

                //inizializza collaboratore
                //i PROPRI (contesto==totale) sono già stati inizializzati

                //inizializzazione COL
                if ($rif['contesto']=='inprestito') {
                    if(!isset($this->c2rProdRes['col']['inprestito']['col'][$row['cod_operaio']])) {

                        $this->c2rProdRes['col']['inprestito']['col'][$row['cod_operaio']]=array(
                            "totale"=>$this->blocco,
                            "dettaglio"=>array()
                        );
                        $this->c2rProdRes['col']['inprestito']['col'][$row['cod_operaio']]['totale']['inprestito']['flag']=true;
                        $this->c2rProdRes['col']['inprestito']['col'][$row['cod_operaio']]['totale']['inprestito']['totale']=new c2rProdTot();
                    }
                }

                //inizializzazione REP
                if ($rif['blocco']=='inprestito' && array_key_exists($tana['rif']['reparto'],$this->reparti) ) {

                    if( !isset($this->c2rProdRes['rep'][$tana['rif']['reparto']]['col']['inprestito']['col'][$row['cod_operaio']]) ) {
                        $this->c2rProdRes['rep'][$tana['rif']['reparto']]['col']['inprestito']['col'][$row['cod_operaio']]=$this->blocco;
                    }
                }

                //########################################

                //$this->feedCollab($row);

                //$this->feedExtra($row);

                //########################################
                //alimenta il calcolo del lamentato

                /*
                {   "num_rif_movimento":"1338828",
                    "cod_inconveniente":"A",
                    "cod_operaio":"18",
                    "num_riga":2,
                    "d_inizio":"20210607",
                    "o_inizio":"11:43",
                    "d_fine":"20210607",
                    "o_fine":"11:48",
                    "qta":".08",
                    "des_note":"PUL{\"coll\":\"18\",\"rif\":\"1338828\",\"lam\":\"A\",\"limite\":\"0.14\"}",
                    "ind_chiuso":"N",
                    "cod_officina":"PV",
                    "inc_pos_lav":"3.50",
                    "inc_marc_chiuse":"2.30",
                    "num_tecnici":1,
                    "tot_marc_odl":"3.70",
                    "tot_fatt_odl":"6.70",
                    "cod_accettatore":"e.giannotti",
                    "qta_ore_prenotazione":".00",
                    "acarico":"OCO",
                    "cod_movimento":"OOP",
                    "cod_tipo_garanzia":"",
                    "rep_proprio":"VWS",
                    "presenza":{"nominale":480,"actual":480},
                    "c2rTipo":"pag",
                    "c2rStato":"OK",
                    "ruolo":{"reparto":"VWS","mgruppo":"TES","gruppo":"TEC",
                    "c2rContesto":"totale"
                },
                */

                //aggiungi la marcatura al dettaglio
                $this->c2rProdRes['col'][$rif['contesto']]['col'][$row['cod_operaio']]['dettaglio'][]=$row;

                //###############################################################################################
                //SE È CAMBIATO ODL O INCONVENIENTE ALIMENTA IL RISULTATO
                if ($row['num_rif_movimento']!=$tana['odl'] || $row['cod_inconveniente']!=$tana['lam']) {

                    if ($tana['odl']!=0) $this->analizza($tana);

                    $tana['odl']=$row['num_rif_movimento'];
                    $tana['lam']=$row['cod_inconveniente'];
                    $tana['chiuso']=$row['ind_chiuso'];
                    $tana['tot_fatt']=$row['tot_fatt_odl'];
                    $tana['tot_marc']=$row['tot_marc_odl'];
                    $tana['pos_lav']=$row['inc_pos_lav'];
                    $tana['addebito']=$row['c2rTipo'];
                    $tana['rif']=$rif;
                    $tana['tot']=0;
                    $tana['tec']=array(
                        "tot"=>0,
                        "tec"=>array()
                    );
                    $tana['spe']=array(
                        "tot"=>0,
                        "tec"=>array()
                    );
                }
                //###############################################################################################

                if ($row['d_fine']=="") continue;

                //se è un RUOLO speciale ma NON è una marcatrura speciale
                if ($speciale && $row['des_note']=="") {

                    if (!array_key_exists($row['cod_operaio'],$tana['spe']['tec'])) {
                        $tana['spe']['tec'][$row['cod_operaio']]=$topo;
                    }

                    $tana['spe']['tot']+=$row['qta'];
                    $tana['spe']['tec'][$row['cod_operaio']][$rif['blocco']]['tot']+=$row['qta'];

                    if ($rif['blocco']=='inprestito') {
                        $tana['spe']['tec'][$row['cod_operaio']][$rif['blocco']]['lista'][]=$row;
                    }

                    //$tana['spe']['tec'][$row['cod_operaio']]['dettaglio'][]=$row;
                    //$this->c2rProdRes['col'][$rif['csp']]['col'][$row['cod_operaio']]['dettaglio'][]=$row;
                }
                elseif (!$speciale) {

                    if (!array_key_exists($row['cod_operaio'],$tana['tec']['tec'])) {
                        $tana['tec']['tec'][$row['cod_operaio']]=$topo;
                    }

                    //se non è una marcatura speciale
                    if ($row['des_note']=="") {

                        $tana['tec']['tot']+=$row['qta'];
                        $tana['tec']['tec'][$row['cod_operaio']][$rif['blocco']]['tot']+=$row['qta'];
                        if ($rif['blocco']=='inprestito') {
                            $tana['tec']['tec'][$row['cod_operaio']][$rif['blocco']]['lista'][]=$row;
                        }
                        //$tana['tec']['tec'][$row['cod_operaio']]['dettaglio'][]=$row;
                        //$this->c2rProdRes['col'][$rif['csp']]['col'][$row['cod_operaio']]['dettaglio'][]=$row;
                    }
                    else {
                        //ritorna FALSE se non è una marcatura speciale valida
                        //oppure ritorna $row modificato (tipo eccesso di pulizia)
                        $sp=$this->checkMarcaturaSpeciale($row);

                        if ($sp) {
                            
                            //scrittura SPECIALE
                            switch ($sp['speciale']['tipo']) {

                                case 'SER':
                                    //SER esiste SOLO se il contesto è TOTALE
                                    if(isset($this->c2rProdRes['rep'][$sp['ruolo']['reparto']])) {
                                        $this->c2rProdRes['rep'][$sp['ruolo']['reparto']]['col']['proprio']['col'][$sp['cod_operaio']]['proprio']['totale']->addPresenzaSpeciale('servizio',$sp['speciale']['valore']*60);
                                    }
                                    $this->c2rProdRes['col']['totale']['col'][$sp['cod_operaio']]['totale']['proprio']['totale']->addPresenzaSpeciale('servizio',$sp['speciale']['valore']*60);
                                break;

                                case 'ATT':
                                    //ATT esiste SOLO se il contesto è TOTALE
                                    if(isset($this->c2rProdRes['rep'][$sp['ruolo']['reparto']])) {
                                        $this->c2rProdRes['rep'][$sp['ruolo']['reparto']]['col']['proprio']['col'][$sp['cod_operaio']]['proprio']['totale']->addMarcaturaExtra('ATT',$sp['speciale']['valore']);
                                        $this->c2rProdRes['rep'][$sp['ruolo']['reparto']]['col']['proprio']['col'][$sp['cod_operaio']]['proprio']['totale']->addMarcaturaExtra('ATTM',$sp['speciale']['escluso']);
                                    }
                                    $this->c2rProdRes['col']['totale']['col'][$sp['cod_operaio']]['totale']['proprio']['totale']->addMarcaturaExtra('ATT',$sp['speciale']['valore']);
                                    $this->c2rProdRes['col']['totale']['col'][$sp['cod_operaio']]['totale']['proprio']['totale']->addMarcaturaExtra('ATTM',$sp['speciale']['escluso']);
                                break;

                                case 'PRV':
                                    //PRV esiste SOLO se il contesto è TOTALE
                                    if(isset($this->c2rProdRes['rep'][$sp['ruolo']['reparto']])) {
                                        $this->c2rProdRes['rep'][$sp['ruolo']['reparto']]['col']['proprio']['col'][$sp['cod_operaio']]['proprio']['totale']->addMarcaturaExtra('PRV',$sp['speciale']['valore']);
                                        $this->c2rProdRes['rep'][$sp['ruolo']['reparto']]['col']['proprio']['col'][$sp['cod_operaio']]['proprio']['totale']->addMarcaturaExtra('PRVM',$sp['speciale']['escluso']);
                                    }
                                    $this->c2rProdRes['col']['totale']['col'][$sp['cod_operaio']]['totale']['proprio']['totale']->addMarcaturaExtra('PRV',$sp['speciale']['valore']);
                                    $this->c2rProdRes['col']['totale']['col'][$sp['cod_operaio']]['totale']['proprio']['totale']->addMarcaturaExtra('PRVM',$sp['speciale']['escluso']);
                                break;

                                case 'PER':
                                    //PER esiste SOLO se il contesto è TOTALE
                                    if(isset($this->c2rProdRes['rep'][$sp['ruolo']['reparto']])) {

                                        $this->c2rProdRes['rep'][$sp['ruolo']['reparto']]['col']['proprio']['col'][$sp['cod_operaio']]['proprio']['totale']->addMarcaturaExtra('PER',$sp['speciale']['valore']);
                                        $this->c2rProdRes['rep'][$sp['ruolo']['reparto']]['col']['proprio']['col'][$sp['cod_operaio']]['proprio']['totale']->addMarcaturaExtra('PERM',$sp['speciale']['escluso']);
                                    }
                                    $this->c2rProdRes['col']['totale']['col'][$sp['cod_operaio']]['totale']['proprio']['totale']->addMarcaturaExtra('PER',$sp['speciale']['valore']);
                                    $this->c2rProdRes['col']['totale']['col'][$sp['cod_operaio']]['totale']['proprio']['totale']->addMarcaturaExtra('PERM',$sp['speciale']['escluso']);
                                break;

                                case 'PUL':
                                    //PUL esiste in tutti i casi
                                    
                                    if(isset($this->c2rProdRes['rep'][$tana['rif']['reparto']])) {
                                        $this->c2rProdRes['rep'][$tana['rif']['reparto']]['col'][$rif['csp']]['col'][$sp['cod_operaio']][$rif['blocco']]['flag']=true;
                                        if (is_null($this->c2rProdRes['rep'][$tana['rif']['reparto']]['col'][$rif['csp']]['col'][$sp['cod_operaio']][$rif['blocco']]['totale'])) {
                                            $this->c2rProdRes['rep'][$tana['rif']['reparto']]['col'][$rif['csp']]['col'][$sp['cod_operaio']][$rif['blocco']]['totale']=new c2rProdTot();
                                        }
                                        $this->c2rProdRes['rep'][$tana['rif']['reparto']]['col'][$rif['csp']]['col'][$sp['cod_operaio']][$rif['blocco']]['totale']->addMarcaturaExtra('PUL',$sp['speciale']['valore']-$sp['speciale']['escluso']);
                                    }
                                    
                                    //echo $sp['c2rContesto'].'-'.$rif['csp'].'-'.$rif['blocco'];
                                    if ($sp['c2rContesto']=='totale') {
                                        $this->c2rProdRes['col'][$sp['c2rContesto']]['col'][$sp['cod_operaio']]['totale']['proprio']['totale']->addMarcaturaExtra('PUL',$sp['speciale']['valore']-$sp['speciale']['escluso']);
                                    }
                                    else {
                                        $this->c2rProdRes['col'][$sp['c2rContesto']]['col'][$sp['cod_operaio']]['totale']['inprestito']['totale']->addMarcaturaExtra('PUL',$sp['speciale']['valore']-$sp['speciale']['escluso']);
                                    }

                                    $tana['tec']['tot']+=( $sp['qta']-($sp['speciale']['valore']-$sp['speciale']['escluso']) );
                                    $tana['tec']['tec'][$sp['cod_operaio']][$rif['blocco']]['tot']+= ( $sp['qta']-($sp['speciale']['valore']-$sp['speciale']['escluso']) );
                                    if ($rif['blocco']=='inprestito') {
                                        $tana['tec']['tec'][$sp['cod_operaio']][$rif['blocco']]['lista'][]=$sp;
                                    }
                                    //$tana['tec']['tec'][$sp['cod_operaio']]['dettaglio'][]=$sp;
                                break;
                            }
                            ///////////////////////////////////////////////

                            //$this->c2rProdRes['col'][$sp['c2rContesto']]['col'][$sp['cod_operaio']]['dettaglio'][]=$sp;
                        }
                        //se la marcatura è OOS (servizio) , e non esiste la marcatura speciale , allora viene ignorata
                        elseif ($row['cod_movimento']!='OOS') {
                            $tana['tec']['tot']+=$row['qta'];
                            $tana['tec']['tec'][$row['cod_operaio']][$rif['blocco']]['tot']+=$row['qta'];
                            if ($rif['blocco']=='inprestito') {
                                $tana['tec']['tec'][$row['cod_operaio']][$rif['blocco']]['lista'][]=$row;
                            }
                            //$tana['tec']['tec'][$row['cod_operaio']]['dettaglio'][]=$row;
                        }
                    }
                }

            }              
        }

        //############################################
        //ALIMENTA IL RISULTATO CON L'ULTIMO LAMENTATO ANALIZZATO
        $this->analizza($tana);
        //############################################

        /*concludi (definisci i totali)
        foreach ($this->analisi as $ka=>$a) {
            if ($a['flag']) {
                call_user_func_array(array($this, 'concludi_'.$ka), array() );
            }
        }*/

    }

    function getRif($a) {

        //REPARTO               reparto di appartenenza della marcatura
        //CONTESTO              TOTALE  || INPRESTITO --> contesto COL
        //CSP                   PROPRIO || INPRESTITO --> contesto REP
        //BLOCCO                blocco dell'oggetto "blocco"

        $res=array(
            "reparto"=>$this->convRep[$a['cod_officina']],
            "contesto"=>$a['c2rContesto'],
            "csp"=>"",
            "blocco"=>""
        );

        //se l'operaio NON appartiene al contesto
        if ($a['c2rContesto']=='inprestito') {
            $res['csp']='inprestito';
            $res['blocco']='inprestito';
        }
        //se l'operaio appartiene al contesto
        else {

            //se il reparto di marcatura corrisponde al ruolo in quel momento
            //si da per scontato che se il reparto non è nel contesto non saremmo a questo punto
            if ($res['reparto']==$a['ruolo']['reparto']) {
                $res['csp']='proprio';
                $res['blocco']='proprio';
            }
            else {
                if (array_key_exists($res['reparto'],$this->reparti)) {
                    $res['reparto']=$a['ruolo']['reparto'];
                    $res['csp']='inprestito';
                    $res['blocco']='inprestito';
                }
                else {
                    $res['csp']='proprio';
                    $res['blocco']='prestato';
                }
            }
        }

        return $res;
    }

    function analizza($a) {

        //$a è un array TANA

        //$opcl=($a['chiuso']!='S')?'aperto':'chiuso';

        if ($a['chiuso']!='S') {

            foreach ($a['tec']['tec'] as $operaio=>$topo) {

                if (is_null($this->c2rProdRes['rep'][$a['rif']['reparto']]['col'][$a['rif']['csp']]['col'][$operaio][$a['rif']['blocco']]['totale'])) {
                    $this->c2rProdRes['rep'][$a['rif']['reparto']]['col'][$a['rif']['csp']]['col'][$operaio][$a['rif']['blocco']]['totale']=new c2rProdTot();
                }
                echo $a['rif']['reparto'].' '.$a['rif']['csp'].' '.$a['rif']['blocco'];
                $this->c2rProdRes['rep'][$a['rif']['reparto']]['col'][$a['rif']['csp']]['col'][$operaio][$a['rif']['blocco']]['flag']=true;
                $this->c2rProdRes['rep'][$a['rif']['reparto']]['col'][$a['rif']['csp']]['col'][$operaio][$a['rif']['blocco']]['totale']->addMarcatura('aperto',$a['addebito'],$topo[$a['rif']['blocco']]['tot']);
            }

            foreach ($a['spe']['tec'] as $operaio=>$topo) {

                if (is_null($this->c2rProdRes['rep'][$a['rif']['reparto']]['col'][$a['rif']['csp']]['col'][$operaio][$a['rif']['blocco']]['totale'])) {
                    $this->c2rProdRes['rep'][$a['rif']['reparto']]['col'][$a['rif']['csp']]['col'][$operaio][$a['rif']['blocco']]['totale']=new c2rProdTot();
                }
                //echo $a['rif']['reparto'].' '.$a['rif']['csp'].' '.$a['rif']['blocco'];
                $this->c2rProdRes['rep'][$a['rif']['reparto']]['col'][$a['rif']['csp']]['col'][$operaio][$a['rif']['blocco']]['flag']=true;
                $this->c2rProdRes['rep'][$a['rif']['reparto']]['col'][$a['rif']['csp']]['col'][$operaio][$a['rif']['blocco']]['totale']->addMarcatura('aperto',$a['addebito'],$topo[$a['rif']['blocco']]['tot']);
            }

        }

        //se ha marcato qualche operaio SPECIALE
        if (count($a['spe'])>0) {

        }

        /*
        if(isset($this->c2rProdRes['rep'][$this->convRep[$a['cod_officina']]])) {
            $this->c2rProdRes['rep'][$this->convRep[$a['cod_officina']]]['col'][$rif['csp']]['col'][$a['cod_operaio']][$rif['blocco']]['totale']->addMarcatura('chiuso',$a);
        }
        */

        /////////////////////////////////////////////////////////////////////////////////////////////

        /*
        foreach ($a['tec']['tec'] as $operaio=>$o) {
            foreach ($o['dettaglio'] as $k=>$r) {
                $this->c2rProdRes['col'][$r['c2rContesto']]['col'][$operaio]['dettaglio'][]=$r;
            }
        }

        foreach ($a['spe']['tec'] as $operaio=>$o) {
            foreach ($o['dettaglio'] as $k=>$r) {
                $this->c2rProdRes['col'][$r['c2rContesto']]['col'][$operaio]['dettaglio'][]=$r;
            }
        }
        */

        $this->log[]=$a;
    }

    function checkMarcaturaSpeciale($row) {

        $valide=array('ATT','SER','PUL','PRV','PER');

        $txt=substr($row['des_note'],0,3);

        if (!in_array($txt,$valide)) return false;

        //se il CONTESTO è in prestito
        //alcune marcature speciali non possono essere contemplate
        //in teoria non dovrebero nemmeno essere estratte ma per esempio PNP utilizza lo stesso odl fittizio di VWS
        if ($row['c2rContesto']=='inprestito' && $txt!='PUL') return false;

        $obj=json_decode(substr($row['des_note'],3),true);

        $row['speciale']=array(
            "tipo"=>$txt,
            "valore"=>0,
            "escluso"=>0
        );

        if ($txt=='SER') $row['speciale']['valore']=$row['qta'];
        else {
            if ($obj['limite']>=$row['qta']) $row['speciale']['valore']=$row['qta'];
            else {
                $row['speciale']['valore']=$row['qta'];
                $row['speciale']['escluso']=$row['qta']-$obj['limite'];
            }
        }

        return $row;
    }

    /*
    function feedCollab($row) {

        //se l'operaio appartiene al contesto oppure no (non dipende dalla macatura in esame ma viene deciso all'inizio)
        $tempcont=isset($this->operaio[$row['cod_operaio']]['flagContesto'])?'totale':'inprestito';
        //$tempcont=$row['presenza']?'totale':'inprestito';

        $this->c2rProdRes['col'][$tempcont]['col'][$row['cod_operaio']]['dettaglio'][]=$row;

        //imposta il blocco da alimentare
        //il blocco proprio è stato già inizializzato
        if ($tempcont=='inprestito') {
            $this->c2rProdRes['col'][$tempcont]['col'][$row['cod_operaio']]['totale'][$tempcont]['flag']=true;

            //se non è stato ancora instanziato l'oggetto totale instanzialo
            if (!$this->c2rProdRes['col'][$tempcont]['col'][$row['cod_operaio']]['totale']['inprestito']['totale']) {
                $this->c2rProdRes['col'][$tempcont]['col'][$row['cod_operaio']]['totale']['inprestito']['totale']=new c2rProdTot();
            }
        }

        if ($tempcont=='totale') {
            $this->c2rProdRes['col'][$tempcont]['col'][$row['cod_operaio']]['totale']['proprio']['totale']->add($row);
        }
        else {
            $this->c2rProdRes['col'][$tempcont]['col'][$row['cod_operaio']]['totale']['inprestito']['totale']->add($row);
        }
        
    }*/

    function feedExtra($row) {

    }

    function presenzaSpeciale($reparto,$operaio) {

        $rif=mainFunc::gab_tots($this->param['inizio']);
        $end=mainFunc::gab_tots($this->param['fine']);

        foreach ($this->ruoli[$operaio] as $index=>$s) {

            //se il ruolo non combacia con il reparto attuale di appartenenza
            //succede solo per collaboratori che nel periodo hanno cambiato reparto e ricoperto ruoli diversi
            if ($s['reparto']!=$reparto) continue;

            //################################################
            //in questo momento i ruoli speciali sono:
            //macrogruppo=='RTS'
            //if (!in_array($s['macrogruppo'],$this->ruoliSpeciali['macrogruppo'])) continue;
            if (!$this->determinaSpeciale($s['macrogruppo'])) continue;
            //################################################

            $ruolo_i=mainFunc::gab_tots($s['data_i']);
            $ruolo_f=mainFunc::gab_tots($s['data_f']);

            if ($rif<$ruolo_i) $rif=$ruolo_i;

            while ($rif<=$end) {

                //se il ruolo è finito nella data di riferimento
                if ($rif>$ruolo_f) break;

                $temp=$this->intervallo->getPresenzaCollDay($reparto,$this->operai[$operaio]['ID_coll'],date('Ymd',$rif));

                //se il collaboratore in questo giorno è stato presente
                // e se siamo a questo punto appartiene ad un ruolo speciale
                if ($temp) {
                    $this->c2rProdRes['rep'][$reparto]['col']['proprio']['col'][$operaio]['proprio']['totale']->addPresenzaSpeciale('esclusione',$temp['actual']);
                    $this->c2rProdRes['col']['totale']['col'][$operaio]['totale']['proprio']['totale']->addPresenzaSpeciale('esclusione',$temp['actual']);
                }


                $rif=strtotime("+1 day",$rif);
            }

        }

    }

    function trovaRuolo($operaio,$tag) {

        //I ruoli sono in ordine di DATA_I
        //####################################
        //SI DA PER SCONTATO CHE UN "OPERAIO" NON POSSA RICOPRIRE PIU' RUOLI CONTEMPORANEAMENTE
        //####################################

        /*
        "26": [
            {
                "ID_coll": 93,
                "data_i": "20171001",
                "data_f": "21001231",
                "gruppo": "RT",
                "des_gruppo": "Resp. Tecnico",
                "posizione": 2,
                "macrogruppo": "RTS",
                "des_macrogruppo": "Responsabili Tecnici",
                "posizione_macrogruppo": 2,
                "reparto": "PAS",
                "macroreparto": "S",
                "des_reparto": "Service Porsche Ancona",
                "rep_concerto": "AP",
                "des_macroreparto": "Service",
                "nome": "Danny",
                "cognome": "Alba",
                "concerto": "d.alba",
                "cod_operaio": "26",
                "tel_interno": ""
            }
        ]
        */

        $res=array(
            "reparto"=>"",
            "mgruppo"=>"",
            "gruppo"=>""
        );

        foreach ($this->ruoli[$operaio] as $r) {

            if ($r['macroreparto']!='S') continue;

            if ($tag>=$r['data_i'] && $tag<=$r['data_f']) {
                $res['reparto']=$r['reparto'];
                $res['mgruppo']=$r['macrogruppo'];
                $res['gruppo']=$r['gruppo'];
            }

        }

        return $res;

    }

    function determinaSpeciale($valore) {

        //in questo momento i ruoli speciali sono:
        //macrogruppo=='RTS'

        return in_array($valore,$this->ruoliSpeciali['macrogruppo']);

    }

    /*
    function trovaRuolo($operaio,$tag) {

        //lo spoglio delle marcature avviene in ordine di operaio e di data dalla minore alla maggiore

        //nel record dell'operaio viene registrato:
        //index             indice del ruolo
        //ruolo_f           data_f del ruolo
        //ruolo {
            //reparto           ruolo attuale
            //mgruppo           ruolo attuale
            //gruppo            ruolo attuale
        //}

        if (!isset($this->operai[$operaio]['index']) ) {
            $this->operai[$operaio]['index']=-1;
            $this->operai[$operaio]['ruolo_f']="";
            $this->operai[$operaio]['ruolo']['reparto']="";
            $this->operai[$operaio]['ruolo']['mgruppo']="";
            $this->operai[$operaio]['ruolo']['gruppo']="";
        }

        //se non siamo più nel ruolo che avevamo già analizzato
        while ($tag>$this->operai[$operaio]['ruolo_f']) {

            $this->operai[$operaio]['index']++;

            //se l'indice non esiste
            if (!isset($this->ruoli[$operaio][$this->operai[$operaio]['index']])) {
                $this->operai[$operaio]['ruolo_f']="21001231";
                $this->operai[$operaio]['ruolo']['reparto']="";
                $this->operai[$operaio]['ruolo']['mgruppo']="";
                $this->operai[$operaio]['ruolo']['gruppo']="";
            }
            else {
                $this->operai[$operaio]['ruolo_f']=$this->ruoli[$operaio][$this->operai[$operaio]['index']]['data_f'];
                $this->operai[$operaio]['ruolo']['reparto']=$this->ruoli[$operaio][$this->operai[$operaio]['index']]['reparto'];
                $this->operai[$operaio]['ruolo']['gruppo']=$this->ruoli[$operaio][$this->operai[$operaio]['index']]['gruppo'];
                $this->operai[$operaio]['ruolo']['mgruppo']=$this->ruoli[$operaio][$this->operai[$operaio]['index']]['macrogruppo'];
            }
        }

        return $this->operai[$operaio]['ruolo'];   

    }*/









    

    function concludi_tot() {   

        foreach ($this->analisi['tot']['blocco']['marche'] as $marca=>$mk) {

            foreach ($mk['modelli'] as $modello=>$m) {

                $this->analisi['tot']['blocco']['marche'][$marca]['totale']->sum($m->getGrid());

            }

            $this->analisi['tot']['blocco']['totale']->sum($this->analisi['tot']['blocco']['marche'][$marca]['totale']->getGrid());

        }
    }

    private static function cmp($a,$b) {

        if ($b['d_inizio']>$a['d_inizio']) return -1;
        else if ($b['d_inizio']<$a['d_inizio']) return 1;
        else {
            if ($b['o_inizio']>$a['o_inizio']) return -1;
            else return 1;
        }
    }

    function draw() {

        //echo json_encode($this->operai);

        //new DIVO 1
        //($index,$htab,$minh,$fixed)
        $divo1=new Divo('c2rProdRes','5%','97%',1);
        $divo1->setBk('#a89dce');

        $txt='<div>';
            //scrivi totale generale ( oggetto TOTALE )
            $txt.=json_encode($this->log);
            //$txt.=json_encode($this->intervallo->getPresenzaCollAll());
            
        $txt.='</div>';

        $divo1->add_div($this->c2rProdRes['tot']['tag'],'black',0,'',$txt,1,array());

        //ksort($this->c2rProdRes['rep']);

        //scrivi i div dei reparti
        foreach ($this->c2rProdRes['rep'] as $reparto=>$r) {

            $txt='<div>';

                $txt.=$this->drawColRep($reparto);
                
            $txt.='</div>';

            //add DIV
            //add_div($titolo,$color,$chk,$stato,$codice,$selected,$css)
            $divo1->add_div($reparto,'black',0,'',$txt,0,array());
        }

        //scrivi il div dei collaboratori

        $txt='<div>';

            $txt.=$this->drawColl();
            
        $txt.='</div>';

        //add DIV
        //add_div($titolo,$color,$chk,$stato,$codice,$selected,$css)
        $divo1->add_div("Collab",'black',0,'',$txt,0,array("margin-left"=>"10px","margin-right"=>"5px","font-weight"=>"bold","text-align"=>"center"));


        foreach ($this->c2rProdRes['ext'] as $ext=>$e) {

            if (!$e['flag']) continue;

            $txt='<div>';

                //scrivi EXTRA
                
            $txt.='</div>';

            //add DIV
            //add_div($titolo,$color,$chk,$stato,$codice,$selected,$css)
            $divo1->add_div($e['tag'],'black',0,'',$txt,0,array("margin-left"=>"10px","margin-right"=>"5px","font-weight"=>"bold","text-align"=>"center"));
        }

        $divo1->build();
        $divo1->draw();

        //echo json_encode($this->log);
    }


    function drawColRep($reparto) {

        $divo2=new Divo('c2rProdColRep_'.$reparto,'4%','96%',1);
        $divo2->setBk('#a8cead');

        $css=array(
            "font-weight"=>"bold",
            "margin-top"=>"0px",
            "font-size"=>"0.8em",
            "text-align"=>"center"
        );

        $txt='<div>';
            //scrivi totale reparto ( oggetto TOTALE )
            
        $txt.='</div>';

        $divo2->add_div('Totale','black',0,'',$txt,0,$css);

        $txt='<div>';

            //$txt.=json_encode($this->collRep[$reparto]);
            //$txt.=json_encode($this->intervallo->getCollaboratori());
            //$txt.=json_encode($this->log);

            $txt.=$this->drawProprio($reparto);
            
        $txt.='</div>';

        $divo2->add_div($this->c2rProdRes['rep'][$reparto]['col']['proprio']['tag'],'black',0,'',$txt,0,$css);

        $txt='<div>';
            //scrivi collaboratori in prestito
            $txt.=$this->drawInprestito($reparto);

        $txt.='</div>';

        $divo2->add_div($this->c2rProdRes['rep'][$reparto]['col']['inprestito']['tag'],'black',0,'',$txt,0,$css);


        /*foreach ($this->c2rProdRes['rep'][$officina]['col'] as $operaio=>$o) {

            $txt='<div>';

                $txt.='<div>';
                    $txt.=$this->convRep[$officina].' - '.$this->operai[$operaio]['cognome'].' '.$this->operai[$operaio]['nome'];
                $txt.='</div>';

                $txt.=$this->drawColRepTot($officina,$operaio);
                
            $txt.='</div>';

            //add DIV
            //add_div($titolo,$color,$chk,$stato,$codice,$selected,$css)
            $divo2->add_div($operaio,'black',0,'',$txt,0,$css);
        }*/

        $divo2->build();
        ob_start();
            $divo2->draw();
        return ob_get_clean();
    }

    function drawProprio($reparto) {

        $divo5=new Divo('c2rProdProprio_'.$reparto,'4%','96%',1);
        $divo5->setBk('#cea0a9');

        $css=array(
            "font-weight"=>"bold",
            "margin-top"=>"0px",
            "font-size"=>"0.8em",
            "text-align"=>"center"
        );

        //echo $reparto;
        ksort($this->c2rProdRes['rep'][$reparto]['col']['proprio']['col']);

        foreach ($this->c2rProdRes['rep'][$reparto]['col']['proprio']['col'] as $operaio=>$o) {

            $txt='<div>';

                $txt.='<div style="font-weight:bold;margin-top:5px;">';
                    $txt.= $operaio.' - '.$this->operai[$operaio]['cognome'].' '.$this->operai[$operaio]['nome'];
                $txt.= '</div>';

                $txt.='<div style="margin-top:10px;">';
                    $txt.= $o['proprio']['totale']->draw();
                $txt.= '</div>';
            
            $txt.='</div>';

            $divo5->add_div($operaio,'black',0,'',$txt,0,$css);

        }

        $divo5->build();
        ob_start();
            $divo5->draw();
        return ob_get_clean();  

    }

    function drawInprestito($reparto) {

        $divo7=new Divo('c2rProdPrestito_'.$reparto,'4%','96%',1);
        $divo7->setBk('#cea0a9');

        $css=array(
            "font-weight"=>"bold",
            "margin-top"=>"0px",
            "font-size"=>"0.8em",
            "text-align"=>"center"
        );

        //echo $reparto;
        ksort($this->c2rProdRes['rep'][$reparto]['col']['inprestito']['col']);

        foreach ($this->c2rProdRes['rep'][$reparto]['col']['inprestito']['col'] as $operaio=>$o) {

            $txt='<div>';

                $txt.= '<div style="font-weight:bold;margin-top:5px;">';
                    $txt.= $operaio.' - '.$this->operai[$operaio]['cognome'].' '.$this->operai[$operaio]['nome'];
                $txt.= '</div>';

                $txt.= '<div style="margin-top:10px;">';
                    //echo $o['inprestito']['totale']->draw();
                $txt.= '</div>';
            
            $txt.='</div>';

            $divo7->add_div($operaio,'black',0,'',$txt,0,$css);

        }

        $divo7->build();
        ob_start();
            $divo7->draw();
        return ob_get_clean();  

    }

    function drawColRepTot($officina,$operaio) {

        $divo3=new Divo('c2rCT_'.$officina.'_'.$operaio,'4%','96%',1);
        $divo3->setBk('#cea0a9');

        $css=array(
            "font-weight"=>"bold",
            "margin-top"=>"0px",
            "font-size"=>"0.8em",
            "text-align"=>"center"
        );

        $txt='<div>';
            //scrivi totale collaboratore ( oggetto TOTALE )
            
        $txt.='</div>';

        $divo3->add_div('Valori','black',0,'',$txt,0,$css);

        $txt='<div>';
            //scrivi dettaglio collaboratore
            $txt.=$this->drawCollDet($officina,$operaio);
        $txt.='</div>';

        $divo3->add_div('Dettaglio','black',0,'',$txt,0,$css);

        $divo3->build();
        ob_start();
            $divo3->draw();
        return ob_get_clean();  
    }

    function drawColl() {

        $divo4=new Divo('c2rColl','4%','96%',1);
        $divo4->setBk('#a8cead');

        $css=array(
            "font-weight"=>"bold",
            "margin-top"=>"0px",
            "font-size"=>"0.9em",
            "text-align"=>"center",
            "margin-left"=>"2px"
        );

        $txt='<div>';

            $txt.=$this->drawListaColl('totale');
            
        $txt.='</div>';

        $divo4->add_div($this->c2rProdRes['col']['totale']['tag'],'black',0,'',$txt,0,$css);

        $txt='<div>';

            $txt.=$this->drawListaColl('inprestito');
            
        $txt.='</div>';

        $divo4->add_div($this->c2rProdRes['col']['inprestito']['tag'],'black',0,'',$txt,0,$css);

        $divo4->build();
        ob_start();
            $divo4->draw();
        return ob_get_clean();  

    }

    function drawListaColl($contesto) {

        $divo6=new Divo('c2rListaColl_'.$contesto,'4%','96%',1);
        $divo6->setBk('#cea0a9');

        $css=array(
            "font-weight"=>"bold",
            "margin-top"=>"3px",
            "font-size"=>"0.6em",
            "text-align"=>"center",
            "margin-left"=>"2px"
        );

        ksort($this->c2rProdRes['col'][$contesto]['col']);

        //scrivi i div dei collaboratori
        foreach ($this->c2rProdRes['col'][$contesto]['col'] as $coll=>$c) {

            $txt='<div>';

                $txt.=$this->drawCollDet($contesto,$coll);
                
            $txt.='</div>';

            //add DIV
            //add_div($titolo,$color,$chk,$stato,$codice,$selected,$css)
            $divo6->add_div($coll,'black',0,'',$txt,0,$css);
        }

        $divo6->build();
        ob_start();
            $divo6->draw();
        return ob_get_clean();  
    }

    function drawCollDet($contesto,$operaio) {

        ob_start();

            echo '<div style="font-weight:bold;margin-top:5px;">';
                echo $operaio.' - '.$this->operai[$operaio]['cognome'].' '.$this->operai[$operaio]['nome'];
            echo '</div>';

            //nell'analisi dei collaboratori il contesto è determinato a priori
            echo '<div style="margin-top:10px;">';
                if ($contesto=='totale') {
                    echo $this->c2rProdRes['col'][$contesto]['col'][$operaio]['totale']['proprio']['totale']->draw();
                }
                else {
                    echo $this->c2rProdRes['col'][$contesto]['col'][$operaio]['totale']['inprestito']['totale']->draw();
                }
            echo '</div>';

            usort($this->c2rProdRes['col'][$contesto]['col'][$operaio]['dettaglio'], array('c2rProduttivita_S','cmp'));

            echo '<div style="margin-top:10px;">';
                echo json_encode($this->c2rProdRes['col'][$contesto]['col'][$operaio]['dettaglio']);
            echo '</div>';

        return ob_get_clean();
    }

}

?>