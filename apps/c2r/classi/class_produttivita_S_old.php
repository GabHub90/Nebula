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
                "col"=>array()
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
        ),
        "nomarc"=>array(
            "tag"=>"Non Marcato",
            "flag"=>false,
            "totale"=>null,
            "dettaglio"=>array()
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
    //collaboratori NON operai
    protected $rcCol=array();
    //conversione m.ghiandoni - 134
    protected $convRC=array(
        "00003"=>"40",
        "00001"=>"4",
        "00002"=>"98"
    );

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

    //totali INTERVALLO
    protected $intPresenza=array();
    protected $intEventi=array();
    protected $intCollDay=array();
    protected $colTurnoDay=array(); 

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

        //$this->log[]=$this->galileo->getLog('query');

        //recuperare l'appartenenza dei collaboratori ai reparti (ed ai ruoli) nel periodo
        //$this->galileo->getCollaboratoriIntervallo(substr($param['reparti'],0,-1),$param['inizio'],$param['fine']);
        $this->galileo->getCollaboratoriIntervallo("",$param['inizio'],$param['fine']);
        $result=$this->galileo->getResult();
        if ($result) {
            $fetID=$this->galileo->preFetchBase('maestro');
            while ($row=$this->galileo->getFetchBase('maestro',$fetID)) {
                //si da per scontato che un operaio possa cambiare reparto ma mantenga comunque il codice operaio
                $this->coll[$row['ID_coll']]=$row;
                if ($row['concerto']!="") {
                    $this->convRC[$row['concerto']]=$row['ID_coll'];
                }

                if ($row['cod_operaio']!="") {
                    $this->operai[$row['cod_operaio']]=$row;
                    $this->ruoli[$row['cod_operaio']][]=$row;
                }
            }
        }

        if ($param['default']['tipo']=='personale' && $param['default']['totali']=='false') $param['operaio']=$this->coll[$param['default']['collaboratore']]['cod_operaio'];
        else $param['operaio']="";

        /*if ($param['prodCollab']!="" && $this->coll[$param['prodCollab']]['cod_operaio']!="") {

            $param['operaio']=$this->coll[$param['prodCollab']]['cod_operaio'];
        }
        else $param['operaio']="";*/

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

        if ($param['prodTipo']=='') $param['prodTipo']='standard';

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

        //################################################

        //CICLO DEGLI INTERVALLI

        $index=mainFunc::gab_tots(substr($param['inizio'],0,6)."01");
        $mend=mainFunc::gab_tots($param['fine']);

        while ($index<=$mend) {

            $tempInfo=array(
                "contesto"=>"libero",
                "presenza"=>"totali",
                "agenda"=>true,
                "schemi"=>true,
                "data_i"=>(date('Ym',$index)==substr($param['inizio'],0,6))?$param['inizio']:date('Ymd',$index),
                "data_f"=>(date('Ym',$index)==substr($param['fine'],0,6))?$param['fine']:date('Ymt',$index)
            );

            $this->intervallo=new quartetIntervallo($tempInfo,$this->reparti,$this->galileo);
            $this->intervallo->calcola();
            $this->intervallo->calcolaIntTot();

            //definisce tutti i collaboratori che appartengono ad ogni reparto in analisi nel periodo
            foreach ($this->reparti as $rep=>$r) {

                foreach ($this->intervallo->getCollRep($rep) as $IDcoll=>$o) {

                    //se non è l'operaio specifico che stiamo analizzando
                    //if ($this->param['operaio']!="" && $this->param['operaio']!=$this->coll[$IDcoll]['cod_operaio']) continue;
                    if ($this->param['default']['tipo']=="personale" && $this->param['default']['totali']=='false' && $this->param['operaio']!=$this->coll[$IDcoll]['cod_operaio']) continue; 

                    
                    //se è un operaio
                    if ($this->coll[$IDcoll]['cod_operaio']!="") {

                        if (!array_key_exists($rep,$this->collRep)) {
                            $this->collRep[$rep]=array();
                        }

                        if (!array_key_exists($IDcoll,$this->collRep[$rep])) {
                            $this->collRep[$rep][$IDcoll]=$o;
                        }
                    }
                    else {

                        if (!array_key_exists($IDcoll,$this->rcCol)) {
                            $this->rcCol[$IDcoll]=$o;
                        }

                    }

                    //##############################
                    //inizializza intPresenza e intEventi
                    $temp=$this->intervallo->getCollTot('subs',$rep,$IDcoll);

                    if ($temp) {

                        $temppres=$temp->getPresenza();
                        $tempev=$temp->getEventi();

                        /*if ($IDcoll=='132') {
                            //$this->log[]=$temppres;
                            //$this->log[]=$tempev;
                            $this->log[]=$temp->getTl();
                        }*/
                    }

                    if (!isset($this->intPresenza[$rep])) $this->intPresenza[$rep]=array();

                    if (!array_key_exists($IDcoll,$this->intPresenza[$rep])) {
                        $this->intPresenza[$rep][$IDcoll]=array(
                            "nominale"=>0,
                            "actual"=>0
                        );
                    }

                    if (isset($temppres)) {
                        $this->intPresenza[$rep][$IDcoll]['nominale']+=$temppres['nominale'];
                        $this->intPresenza[$rep][$IDcoll]['actual']+=$temppres['actual'];
                    }

                    ///////////////////////////////////////////////////////

                    if (!isset($this->intEventi[$rep])) $this->intEventi[$rep]=array();

                    if (!array_key_exists($IDcoll,$this->intEventi[$rep])) {
                        $this->intEventi[$rep][$IDcoll]=array();
                    }

                    if (isset($tempev)) {
                        foreach ($tempev as $classe=>$c) {

                            if (!isset($this->intEventi[$rep][$IDcoll][$classe])) $this->intEventi[$rep][$IDcoll][$classe]=array();

                            foreach ($c as $tipo=>$t) {

                                if (!array_key_exists($tipo,$this->intEventi[$rep][$IDcoll][$classe])) {
                                    $this->intEventi[$rep][$IDcoll][$classe][$tipo]=array(
                                        'qta'=>0
                                    );
                                }

                                $this->intEventi[$rep][$IDcoll][$classe][$tipo]['qta']+=$t['qta'];
                            }

                        }
                    }

                    $temprif=mainFunc::gab_tots($tempInfo['data_i']);
                    $tempend=mainFunc::gab_tots($tempInfo['data_f']);

                    while ($temprif<=$tempend) {

                        $temptag=date('Ymd',$temprif);

                        $temp=$this->intervallo->getTurnoCollDay($rep,$IDcoll,$temptag);

                        if (!isset($this->intCollDay[$rep])) $this->intCollDay[$rep]=array();

                        if (!array_key_exists($IDcoll,$this->intCollDay[$rep])) $this->intCollDay[$rep][$IDcoll]=array();

                        $this->intCollDay[$rep][$IDcoll][$temptag]=$temp;

                        if ($temp) {
                            $this->colTurnoDay[$IDcoll][$temptag]=$temp['turno'];
                        }

                        $temprif=strtotime('+1 day',$temprif);
                    }

                }
                
            }

            $index=strtotime("+1 month",$index);

        }

        //#################################################

        if ($this->c2rProdRes['ext']['rc']['flag']) {
                
            foreach ($this->rcCol as $rc=>$c) {

                //inizializza RC
                // param {"reparti":"'PV',","marche":"'A','C','N','P','S','V','X',","inizio":"20210701","fine":"20210930","prodTipo":"standard","default":{"tipo":"standard","totali":"true","collab":"false","repcol":"false","responsabile":"false","collaboratore":"134"},"operaio":""}
                $this->c2rProdRes['ext']['rc']['col'][$rc]=array(
                    "totale"=>new c2rProdTot(),
                    "nomarc"=>null,
                    "dettaglio"=>array()
                );

                foreach ($this->reparti as $rep=>$r) {

                    if (isset($this->intPresenza[$rep][$rc])) {
                        $temppres=$this->intPresenza[$rep][$rc];
                    }
                    else $temppres=false;

                    if (isset($this->intEventi[$rep][$rc])) {
                        $tempev=$this->intEventi[$rep][$rc];
                    }
                    else $tempev=false;
                    

                    if ($temppres) {
                        $this->c2rProdRes['ext']['rc']['col'][$rc]['totale']->addPresenza($temppres);
                    }
                    if ($tempev) {
                        $this->c2rProdRes['ext']['rc']['col'][$rc]['totale']->addEventi($tempev);
                    }
                }
            }
        }

        //alla fine:
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

            if (isset($this->collRep[$rep])) {

                foreach ($this->collRep[$rep] as $operaio=>$c) {

                    $this->init($rep,$operaio);
                }
            }
                
        }

        

        /*$this->log[]=$tempInfo;
        $this->log[]=$this->intervallo->getTurnoCollDay('VWS','132','20210608');
        $this->log[]=$this->intervallo->getLog();*/

        unset($this->intervallo);

    }

    function checkRC($IDColl) {
        return isset($this->rcCol[$IDColl]);
    }

    function init($rep,$IDcoll) {

        //l'operaio viene marchiato come PROPRIO del contesto di analisi
        $this->operaio[$this->coll[$IDcoll]['cod_operaio']]['flagContesto']=true;

        if (!isset($this->c2rProdRes['rep'][$rep]['col']['proprio']['col'][$this->coll[$IDcoll]['cod_operaio']])) {
            $this->c2rProdRes['rep'][$rep]['col']['proprio']['col'][$this->coll[$IDcoll]['cod_operaio']]=$this->blocco;
            $this->c2rProdRes['rep'][$rep]['col']['proprio']['col'][$this->coll[$IDcoll]['cod_operaio']]['totale']['totale']=new c2rProdTot();
        }

        //inizializza collaboratore proprio
        if (!isset($this->c2rProdRes['col']['totale']['col'][$this->coll[$IDcoll]['cod_operaio']])) {
            $this->c2rProdRes['col']['totale']['col'][$this->coll[$IDcoll]['cod_operaio']]=array(
                "totale"=>$this->blocco,
                "dettaglio"=>array()
            );
        }

        //scrivi la presenza propria del collaboratore da solo e nel reparto
        //TEMP è un oggetto TIMELINE
        //$temp=$this->intervallo->getCollTot('subs',$rep,$IDcoll);

        //if ($temp) {

            $temppres=$this->intPresenza[$rep][$IDcoll];
            $tempev=$this->intEventi[$rep][$IDcoll];

            //$this->log[]=array($tempev);

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
            
        //}
    }

    private static function cmp($a,$b) {

        if ($b['d_inizio']>$a['d_inizio']) return -1;
        else if ($b['d_inizio']<$a['d_inizio']) return 1;
        else {
            if ($b['o_inizio']>$a['o_inizio']) return -1;
            else return 1;
        }
    }

    function exportRes() {
        return $this->c2rProdRes;
    }

    function getLines() {
       
        //lettura della lista delle marcature da CONCERTO

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
                "pos_lav"=>0,
                "inc_marc"=>0,
                "addebito"=>"",
                "accettatore"=>"",
                "tot"=>0,
                "c2rMarcAna"=>0,
                "tec"=>array(
                    "tot"=>0,
                    "lista"=>array()
                ),
                "spe"=>array(
                    "tot"=>0,
                    "lista"=>array()
                )
            );

            $topo=array(
                "tot"=>0,
                "perc"=>1,
                "lista"=>array()
            );

            while ($row=$this->galileo->getFetch('odl',$fetID)) {

                //se l'operaio non esiste tra gli operai
                if (!array_key_exists($row['cod_operaio'],$this->operai)) continue;

                $valid=false;
                $row['rep_proprio']="";
                $row['presenza']=null;
                
                //se l'operaio è presente in uno dei reparti in analisi nel giorno
                foreach ($this->collRep as $rep=>$c) {

                    //$temp=$this->intervallo->getPresenzaCollDay($rep,$this->operai[$row['cod_operaio']]['ID_coll'],$row['d_inizio']);
                    if (isset($this->intCollDay[$rep][$this->operai[$row['cod_operaio']]['ID_coll']][$row['d_inizio']])) {
                        $temp=$this->intCollDay[$rep][$this->operai[$row['cod_operaio']]['ID_coll']][$row['d_inizio']];
                    }
                    else $temp=false;

                    if ($temp) {
                        $row['presenza']=$temp;
                        $row['rep_proprio']=$rep;
                        $valid=true;
                        break;
                    }
                }

                if (!$valid) continue;

                //se la marcatura appartiene ad un reparto in analisi
                if (!$valid) {
                    if (array_key_exists($this->convRep[$row['cod_officina']],$this->reparti)) {
                        $valid=true;
                    }
                }

                //if ($row['cod_operaio']=='8' && $row['d_inizio']=='20210624') $this->log[]=$row;

                //se la marcatura non è coerente al contesto scartala
                if (!$valid) continue;


                $row=$this->calcolaAddebito($row);
                
                //############################################

                $row['ruolo']=$this->trovaRuolo($row['cod_operaio'],$row['d_inizio']);

                //$tempcont=$row['presenza']?'totale':'inprestito';
                //in base al CONTESTO e NON al singolo reparto (COL)
                $row['c2rContesto']=isset($this->operaio[$row['cod_operaio']]['flagContesto'])?'totale':'inprestito';

                $speciale=$this->determinaSpeciale($row['ruolo']['mgruppo']);

                //inizializza collaboratore
                //i PROPRI (contesto==totale) sono già stati inizializzati

                //inizializzazione COL
                if ($row['c2rContesto']=='inprestito') {
                    if(!isset($this->c2rProdRes['col']['inprestito']['col'][$row['cod_operaio']])) {

                        $this->c2rProdRes['col']['inprestito']['col'][$row['cod_operaio']]=array(
                            "totale"=>$this->blocco,
                            "dettaglio"=>array()
                        );
                        $this->c2rProdRes['col']['inprestito']['col'][$row['cod_operaio']]['totale']['inprestito']['flag']=true;
                        $this->c2rProdRes['col']['inprestito']['col'][$row['cod_operaio']]['totale']['inprestito']['totale']=new c2rProdTot();
                    }
                }

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
                    "ruolo":{"reparto":"VWS","mgruppo":"TES","gruppo":"TEC",
                    "c2rContesto":"totale",
                    "c2rQta":".08"
                },
                */

                //###############################################################################################
                //SE È CAMBIATO ODL O INCONVENIENTE ALIMENTA IL RISULTATO
                if ($row['num_rif_movimento']!=$tana['odl'] || $row['cod_inconveniente']!=$tana['lam']) {

                    if ($tana['odl']!=0) $this->analizza($tana);

                    $tana['odl']=$row['num_rif_movimento'];
                    $tana['lam']=$row['cod_inconveniente'];
                    $tana['chiuso']=$row['ind_chiuso'];
                    $tana['pos_lav']=$row['inc_pos_lav'];
                    $tana['inc_marc']=$row['inc_marc_chiuse'];
                    $tana['addebito']=$row['c2rTipo'];
                    $tana['accettatore']=$row['cod_accettatore'];
                    $tana['tot']=0;
                    $tana['c2rMarcAna']=0;
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

                if ($row['d_fine']=="") {
                    $this->c2rProdRes['col'][$row['c2rContesto']]['col'][$row['cod_operaio']]['dettaglio'][]=$row;
                    continue;
                }

                $row['c2rQta']=$row['qta'];
                //tiene il conto della quantità di marcature totali che sono state analizzate
                $tana['c2rMarcAna']+=$row['qta'];

                //se è un RUOLO speciale ma NON è una marcatrura speciale
                if ($speciale && $row['des_note']=="") {

                    if (!array_key_exists($row['cod_operaio'],$tana['spe']['tec']) ) {
                        $tana['spe']['tec'][$row['cod_operaio']]=$topo;
                    }

                    $tana['tot']+=$row['qta'];
                    $tana['spe']['tot']+=$row['qta'];
                    $tana['spe']['tec'][$row['cod_operaio']]['tot']+=$row['qta'];
                    $tana['spe']['tec'][$row['cod_operaio']]['lista'][]=$row;

                }
                elseif (!$speciale) {

                    //se non è una marcatura speciale
                    if ($row['des_note']=="") {

                        if (!array_key_exists($row['cod_operaio'],$tana['tec']['tec']) ) {
                            $tana['tec']['tec'][$row['cod_operaio']]=$topo;
                        }

                        $tana['tot']+=$row['qta'];
                        $tana['tec']['tot']+=$row['qta'];
                        $tana['tec']['tec'][$row['cod_operaio']]['tot']+=$row['qta'];
                        $tana['tec']['tec'][$row['cod_operaio']]['lista'][]=$row;
                       
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

                                    //scrivi dettaglio
                                    //$sp['c2rQta']=$sp['speciale']['valore']-$sp['speciale']['escluso'];
                                    $sp['c2rQta']=$sp['speciale']['valore'];
                                    $this->c2rProdRes['col'][$row['c2rContesto']]['col'][$sp['cod_operaio']]['dettaglio'][]=$sp;
                                break;

                                case 'ATT':
                                    //ATT esiste SOLO se il contesto è TOTALE
                                    if(isset($this->c2rProdRes['rep'][$sp['ruolo']['reparto']])) {
                                        //$this->c2rProdRes['rep'][$sp['ruolo']['reparto']]['col']['proprio']['col'][$sp['cod_operaio']]['proprio']['totale']->addMarcaturaExtra('ATT',$sp['speciale']['valore']);
                                        $this->c2rProdRes['rep'][$sp['ruolo']['reparto']]['col']['proprio']['col'][$sp['cod_operaio']]['proprio']['totale']->addMarcaturaExtra('ATT',$sp['qta']);
                                        $this->c2rProdRes['rep'][$sp['ruolo']['reparto']]['col']['proprio']['col'][$sp['cod_operaio']]['proprio']['totale']->addMarcaturaExtra('ATTM',$sp['speciale']['escluso']);
                                    }
                                    //$this->c2rProdRes['col']['totale']['col'][$sp['cod_operaio']]['totale']['proprio']['totale']->addMarcaturaExtra('ATT',$sp['speciale']['valore']);
                                    $this->c2rProdRes['col']['totale']['col'][$sp['cod_operaio']]['totale']['proprio']['totale']->addMarcaturaExtra('ATT',$sp['qta']);
                                    $this->c2rProdRes['col']['totale']['col'][$sp['cod_operaio']]['totale']['proprio']['totale']->addMarcaturaExtra('ATTM',$sp['speciale']['escluso']);

                                    //scrivi dettaglio
                                    //$sp['c2rQta']=$sp['speciale']['valore']-$sp['speciale']['escluso'];
                                    $sp['c2rQta']=$sp['speciale']['valore'];
                                    $this->c2rProdRes['col'][$row['c2rContesto']]['col'][$sp['cod_operaio']]['dettaglio'][]=$sp;
                                break;

                                case 'PRV':
                                    //PRV esiste SOLO se il contesto è TOTALE
                                    if(isset($this->c2rProdRes['rep'][$sp['ruolo']['reparto']])) {
                                        //$this->c2rProdRes['rep'][$sp['ruolo']['reparto']]['col']['proprio']['col'][$sp['cod_operaio']]['proprio']['totale']->addMarcaturaExtra('PRV',$sp['speciale']['valore']);
                                        $this->c2rProdRes['rep'][$sp['ruolo']['reparto']]['col']['proprio']['col'][$sp['cod_operaio']]['proprio']['totale']->addMarcaturaExtra('PRV',$sp['qta']);
                                        $this->c2rProdRes['rep'][$sp['ruolo']['reparto']]['col']['proprio']['col'][$sp['cod_operaio']]['proprio']['totale']->addMarcaturaExtra('PRVM',$sp['speciale']['escluso']);
                                    }
                                    //$this->c2rProdRes['col']['totale']['col'][$sp['cod_operaio']]['totale']['proprio']['totale']->addMarcaturaExtra('PRV',$sp['speciale']['valore']);
                                    $this->c2rProdRes['col']['totale']['col'][$sp['cod_operaio']]['totale']['proprio']['totale']->addMarcaturaExtra('PRV',$sp['qta']);
                                    $this->c2rProdRes['col']['totale']['col'][$sp['cod_operaio']]['totale']['proprio']['totale']->addMarcaturaExtra('PRVM',$sp['speciale']['escluso']);

                                    //scrivi dettaglio
                                    //$sp['c2rQta']=$sp['speciale']['valore']-$sp['speciale']['escluso'];
                                    $sp['c2rQta']=$sp['speciale']['valore'];
                                    $this->c2rProdRes['col'][$row['c2rContesto']]['col'][$sp['cod_operaio']]['dettaglio'][]=$sp;
                                break;

                                case 'PER':
                                    //PER esiste SOLO se il contesto è TOTALE
                                    if(isset($this->c2rProdRes['rep'][$sp['ruolo']['reparto']])) {
                                        //$this->c2rProdRes['rep'][$sp['ruolo']['reparto']]['col']['proprio']['col'][$sp['cod_operaio']]['proprio']['totale']->addMarcaturaExtra('PER',$sp['speciale']['valore']);
                                        $this->c2rProdRes['rep'][$sp['ruolo']['reparto']]['col']['proprio']['col'][$sp['cod_operaio']]['proprio']['totale']->addMarcaturaExtra('PER',$sp['qta']);
                                        $this->c2rProdRes['rep'][$sp['ruolo']['reparto']]['col']['proprio']['col'][$sp['cod_operaio']]['proprio']['totale']->addMarcaturaExtra('PERM',$sp['speciale']['escluso']);
                                    }
                                    //$this->c2rProdRes['col']['totale']['col'][$sp['cod_operaio']]['totale']['proprio']['totale']->addMarcaturaExtra('PER',$sp['speciale']['valore']);
                                    $this->c2rProdRes['col']['totale']['col'][$sp['cod_operaio']]['totale']['proprio']['totale']->addMarcaturaExtra('PER',$sp['qta']);
                                    $this->c2rProdRes['col']['totale']['col'][$sp['cod_operaio']]['totale']['proprio']['totale']->addMarcaturaExtra('PERM',$sp['speciale']['escluso']);

                                    //scrivi dettaglio
                                    //$sp['c2rQta']=$sp['speciale']['valore']-$sp['speciale']['escluso'];
                                    $sp['c2rQta']=$sp['speciale']['valore'];
                                    $this->c2rProdRes['col'][$row['c2rContesto']]['col'][$sp['cod_operaio']]['dettaglio'][]=$sp;
                                break;

                                case 'PUL':
                                    //PUL esiste in tutti i casi
                                    //la scrittura dei totali viene demandata all'ANALISI
                                    if (!array_key_exists($row['cod_operaio'],$tana['tec']['tec']) ) {
                                        $tana['tec']['tec'][$row['cod_operaio']]=$topo;
                                    }

                                    /*$sp['c2rQta']=( $sp['qta']-($sp['speciale']['valore']-$sp['speciale']['escluso']) );

                                    $tana['tot']+=( $sp['qta']-($sp['speciale']['valore']-$sp['speciale']['escluso']) );
                                    $tana['tec']['tot']+=( $sp['qta']-($sp['speciale']['valore']-$sp['speciale']['escluso']) );
                                    $tana['tec']['tec'][$row['cod_operaio']]['tot']+=( $sp['qta']-($sp['speciale']['valore']-$sp['speciale']['escluso']) );
                                    $tana['tec']['tec'][$row['cod_operaio']]['lista'][]=$sp;
                                    */

                                    $sp['c2rQta']=$sp['speciale']['escluso'];

                                    $tana['tot']+=$sp['speciale']['escluso'];
                                    $tana['tec']['tot']+=$sp['speciale']['escluso'];
                                    $tana['tec']['tec'][$row['cod_operaio']]['tot']+=$sp['speciale']['escluso'];
                                    $tana['tec']['tec'][$row['cod_operaio']]['lista'][]=$sp;

                                break;
                            }
                            ///////////////////////////////////////////////

                            //$this->c2rProdRes['col'][$sp['c2rContesto']]['col'][$sp['cod_operaio']]['dettaglio'][]=$sp;
                        }
                        //se la marcatura è OOS (servizio) , e non esiste la marcatura speciale , allora viene ignorata
                        elseif ($row['cod_movimento']!='OOS') {

                            if (!array_key_exists($row['cod_operaio'],$tana['tec']['tec']) ) {
                                $tana['tec']['tec'][$row['cod_operaio']]=$topo;
                            }

                            $tana['tot']+=$row['qta'];
                            $tana['tec']['tot']+=$row['qta'];
                            $tana['tec']['tec'][$row['cod_operaio']][]=$row;
                            $tana['tec']['tec'][$row['cod_operaio']]['tot']+=$row['qta'];
                            $tana['tec']['tec'][$row['cod_operaio']]['lista'][]=$row;
                        }
                    }
                }

            }              
        }

        //ALIMENTA IL RISULTATO CON L'ULTIMO LAMENTATO ANALIZZATO
        $this->analizza($tana);

        //########################################################
        //CARICA FATTURATI NON MARCATI

        $this->galileo->clearQuery();
        $this->galileo->clearQueryOggetto('default','odl');

        $oby="movdoc.dat_documento_i";
        $this->galileo->executeGeneric('odl','getFattNonMarc',$this->param,$oby);
        $result=$this->galileo->getResult();

        //$this->log[]=$this->galileo->getLog('query');

        if ($result) {
            $fetID=$this->galileo->preFetch('odl');

            while ($row=$this->galileo->getFetch('odl',$fetID)) {

                $row=$this->calcolaAddebito($row);

                if (is_null($this->c2rProdRes['rep'][$this->convRep[$row['inc_cod_off']]]['totale']['nomarc']['totale'])) {
                    $this->c2rProdRes['rep'][$this->convRep[$row['inc_cod_off']]]['totale']['nomarc']['flag']=true;
                    $this->c2rProdRes['rep'][$this->convRep[$row['inc_cod_off']]]['totale']['nomarc']['totale']=new c2rProdTot();
                }

                $this->c2rProdRes['rep'][$this->convRep[$row['inc_cod_off']]]['totale']['nomarc']['totale']->addMarcatura('chiuso',$row['c2rTipo'],$row['inc_pos_lav']);
                $this->c2rProdRes['rep'][$this->convRep[$row['inc_cod_off']]]['totale']['nomarc']['dettaglio'][]=$row;

                ///////////////////////////////////////////////////
                //RC
                if ($this->c2rProdRes['ext']['rc']['flag']) {

                    if (isset($this->convRC[$row['cod_accettatore']])) {
                        if (isset($this->c2rProdRes['ext']['rc']['col'][$this->convRC[$row['cod_accettatore']]]['totale'])) {
                            $this->c2rProdRes['ext']['rc']['col'][$this->convRC[$row['cod_accettatore']]]['totale']->addMarcatura('chiuso',$row['c2rTipo'],$row['inc_pos_lav']);

                            if (is_null($this->c2rProdRes['ext']['rc']['col'][$this->convRC[$row['cod_accettatore']]]['nomarc'])) {
                                $this->c2rProdRes['ext']['rc']['col'][$this->convRC[$row['cod_accettatore']]]['nomarc']=new c2rProdTot();
                            }

                            $this->c2rProdRes['ext']['rc']['col'][$this->convRC[$row['cod_accettatore']]]['nomarc']->addMarcatura('chiuso',$row['c2rTipo'],$row['inc_pos_lav']);
                            $this->c2rProdRes['ext']['rc']['col'][$this->convRC[$row['cod_accettatore']]]['dettaglio']=$row;
                        }
                    }
                }
            }
        }

        //######################################
        //TOTALI
        //per ogni oggetto totale calcola l'efficienza
        //alimenta il TOTALE del BLOCCO TOTALE per ogni collaboratore in ogni contesto
        //alimenta $this->c2rProdRes['rep'][$rep]['totale']['totale']['totale'] per il reparto
        //alimenta $this->c2rProdRes['rep']['tot]['totale']

        foreach ($this->c2rProdRes['rep'] as $reparto=>$r) {
            foreach ($r['col'] as $contesto=>$c) {
                foreach ($c['col'] as $operaio=>$o) {

                    //if ($this->param['operaio']!="" && $this->param['operaio']!=$operaio) continue; 

                    //aggiunto 01.07.2021
                    if (!isset($this->c2rProdRes['rep'][$reparto]['totale'])) {
                        $this->c2rProdRes['rep'][$reparto]['totale']=$this->blocco;
                    }
                    ////////////////////////////////////////////////////////////////

                    if ($o['proprio']['flag']) {
                        $o['proprio']['totale']->consolida();
                        if (!$this->c2rProdRes['rep'][$reparto]['totale']['proprio']['flag']) {
                            $this->c2rProdRes['rep'][$reparto]['totale']['proprio']['flag']=true;
                            $this->c2rProdRes['rep'][$reparto]['totale']['proprio']['totale']=new c2rProdTot();
                        }
                        $this->c2rProdRes['rep'][$reparto]['totale']['proprio']['totale']->write($o['proprio']['totale']->read(),'std');
                        $this->c2rProdRes['rep'][$reparto]['col'][$contesto]['col'][$operaio]['totale']['flag']=true;
                        $this->c2rProdRes['rep'][$reparto]['col'][$contesto]['col'][$operaio]['totale']['totale']->write($o['proprio']['totale']->read(),'std');
                    }

                    if ($o['inprestito']['flag']) {
                        $o['inprestito']['totale']->consolida();
                        if (!$this->c2rProdRes['rep'][$reparto]['totale']['inprestito']['flag']) {
                            $this->c2rProdRes['rep'][$reparto]['totale']['inprestito']['flag']=true;
                            $this->c2rProdRes['rep'][$reparto]['totale']['inprestito']['totale']=new c2rProdTot();
                        }
                        $this->c2rProdRes['rep'][$reparto]['totale']['inprestito']['totale']->write($o['inprestito']['totale']->read(),'std');
                        $this->c2rProdRes['rep'][$reparto]['totale']['totale']['flag']=true;
                        $this->c2rProdRes['rep'][$reparto]['totale']['totale']['totale']->write($o['inprestito']['totale']->read(),'std');
                    }

                    if ($o['prestato']['flag']) {
                        $o['prestato']['totale']->consolida();
                        if (!$this->c2rProdRes['rep'][$reparto]['totale']['prestato']['flag']) {
                            $this->c2rProdRes['rep'][$reparto]['totale']['prestato']['flag']=true;
                            $this->c2rProdRes['rep'][$reparto]['totale']['prestato']['totale']=new c2rProdTot();
                        }
                        $this->c2rProdRes['rep'][$reparto]['totale']['prestato']['totale']->write($o['prestato']['totale']->read(),'std');

                        ///////
                        //aggiunto 21.06.2021
                        if (!isset($this->c2rProdRes['rep'][$reparto]['totale']['proprio']['totale'])) {
                            $this->c2rProdRes['rep'][$reparto]['totale']['proprio']['flag']=true;
                            $this->c2rProdRes['rep'][$reparto]['totale']['proprio']['totale']=new c2rProdTot();
                        }
                        $this->c2rProdRes['rep'][$reparto]['totale']['proprio']['totale']->write($o['prestato']['totale']->read(),'rep');
                        ///////

                        ///////
                        //aggiunto 01.07.2021
                        if (!isset($this->c2rProdRes['rep'][$reparto]['col'][$contesto]['col'][$operaio]['totale']['totale'])) {
                            $this->c2rProdRes['rep'][$reparto]['col'][$contesto]['col'][$operaio]['totale']['totale']=new c2rProdTot();
                        }
                        ///////////////////////

                        $this->c2rProdRes['rep'][$reparto]['col'][$contesto]['col'][$operaio]['totale']['flag']=true;
                        $this->c2rProdRes['rep'][$reparto]['col'][$contesto]['col'][$operaio]['totale']['totale']->write($o['prestato']['totale']->read(),'rep');
                    }

                    //consolida totale
                    if ($this->c2rProdRes['rep'][$reparto]['col'][$contesto]['col'][$operaio]['totale']['flag']) {
                        $this->c2rProdRes['rep'][$reparto]['col'][$contesto]['col'][$operaio]['totale']['totale']->consolida();
                        
                        ///////
                        //aggiunto 01.07.2021
                        if(!isset($this->c2rProdRes['rep'][$reparto]['totale']['totale']['totale'])) {
                            $this->c2rProdRes['rep'][$reparto]['totale']['totale']['totale']=new c2rProdTot();
                        }
                        //////////////////////

                        $this->c2rProdRes['rep'][$reparto]['totale']['totale']['flag']=true;
                        $this->c2rProdRes['rep'][$reparto]['totale']['totale']['totale']->write($this->c2rProdRes['rep'][$reparto]['col'][$contesto]['col'][$operaio]['totale']['totale']->read(),'col');
                    }
                }
            }

            if ($this->c2rProdRes['rep'][$reparto]['totale']['proprio']['flag']) {
                $this->c2rProdRes['rep'][$reparto]['totale']['proprio']['totale']->consolida();
            }
            if ($this->c2rProdRes['rep'][$reparto]['totale']['prestato']['flag']) {
                $this->c2rProdRes['rep'][$reparto]['totale']['prestato']['totale']->consolida();
            }
            if ($this->c2rProdRes['rep'][$reparto]['totale']['inprestito']['flag']) {
                $this->c2rProdRes['rep'][$reparto]['totale']['inprestito']['totale']->consolida();
            }
            if ($this->c2rProdRes['rep'][$reparto]['totale']['nomarc']['flag']) {
                $this->c2rProdRes['rep'][$reparto]['totale']['nomarc']['totale']->consolida();
                
                if (is_null($this->c2rProdRes['tot']['totale']['nomarc']['totale'])) {
                    $this->c2rProdRes['tot']['totale']['nomarc']['flag']=true;
                    $this->c2rProdRes['tot']['totale']['nomarc']['totale']=new c2rProdTot();
                }
                $this->c2rProdRes['tot']['totale']['nomarc']['totale']->write($this->c2rProdRes['rep'][$reparto]['totale']['nomarc']['totale']->read(),'std');
            }
            if ($this->c2rProdRes['rep'][$reparto]['totale']['totale']['flag']) {
                $this->c2rProdRes['rep'][$reparto]['totale']['totale']['totale']->consolida();
            }
        }

        foreach ($this->c2rProdRes['col'] as $contesto=>$c) {

            foreach ($c['col'] as $operaio=>$o) {

                if ($contesto=='totale' && $o['totale']['proprio']['flag']) {

                    if ($o['totale']['prestato']['flag']) {
                        $this->c2rProdRes['col'][$contesto]['col'][$operaio]['totale']['proprio']['totale']->write($o['totale']['prestato']['totale']->read(),'col');
                    }

                    $o['totale']['proprio']['totale']->consolida();

                    if (!$this->c2rProdRes['tot']['totale']['proprio']['flag']) {
                        $this->c2rProdRes['tot']['totale']['proprio']['flag']=true;
                        $this->c2rProdRes['tot']['totale']['proprio']['totale']=new c2rProdTot();
                    }

                    $this->c2rProdRes['tot']['totale']['proprio']['totale']->write($o['totale']['proprio']['totale']->read(),'col');
                }

                if ($contesto=='inprestito' && $o['totale']['inprestito']['flag']) {
                    $o['totale']['inprestito']['totale']->consolida();

                    if (!$this->c2rProdRes['tot']['totale']['inprestito']['flag']) {
                        $this->c2rProdRes['tot']['totale']['inprestito']['flag']=true;
                        $this->c2rProdRes['tot']['totale']['inprestito']['totale']=new c2rProdTot();
                    }

                    $this->c2rProdRes['tot']['totale']['inprestito']['totale']->write($o['totale']['inprestito']['totale']->read(),'col');
                }
            }
        }

        /*if (!is_null($this->c2rProdRes['tot']['totale']['proprio']['totale'])) {
            $this->c2rProdRes['tot']['totale']['proprio']['totale']->consolida();
        }*/
        if (!is_null($this->c2rProdRes['tot']['totale']['inprestito']['totale'])) {
            $this->c2rProdRes['tot']['totale']['inprestito']['totale']->consolida();
        }
        if (!is_null($this->c2rProdRes['tot']['totale']['nomarc']['totale'])) {
            $this->c2rProdRes['tot']['totale']['nomarc']['totale']->consolida();
        }

        if (!$this->c2rProdRes['tot']['totale']['totale']['flag']) {
            $this->c2rProdRes['tot']['totale']['totale']['flag']=true;
            $this->c2rProdRes['tot']['totale']['totale']['totale']=new c2rProdTot();
        }
        
        if (!is_null($this->c2rProdRes['tot']['totale']['proprio']['totale'])) {
            $this->c2rProdRes['tot']['totale']['proprio']['totale']->consolida();
            $this->c2rProdRes['tot']['totale']['totale']['totale']->write($this->c2rProdRes['tot']['totale']['proprio']['totale']->read(),'col');
        }
        if (!is_null($this->c2rProdRes['tot']['totale']['inprestito']['totale'])) {
            $this->c2rProdRes['tot']['totale']['totale']['totale']->write($this->c2rProdRes['tot']['totale']['inprestito']['totale']->read(),'col');
        }

        $this->c2rProdRes['tot']['totale']['totale']['totale']->consolida();

        ///////////////////////////////////
        //RC
        if ($this->c2rProdRes['ext']['rc']['flag']) {

            foreach ($this->c2rProdRes['ext']['rc']['col'] as $rc=>$c) {
                $this->c2rProdRes['ext']['rc']['col'][$rc]['totale']->consolida();

                if (!is_null($this->c2rProdRes['ext']['rc']['col'][$rc]['nomarc'])) {
                    $this->c2rProdRes['ext']['rc']['col'][$rc]['nomarc']->consolida();
                }
            }
        }

    }

    function calcolaAddebito($row) {

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

        ////////////////////////////////////////////////////
        if ($row['c2rTipo']=='nac') $row['c2rTipo']='ind';
        ////////////////////////////////////////////////////

        return $row;
    }

    function getRif($a) {

        //per ogni marcatura
        /*decidere il totale dove salvare le informazioni in base a:
        la marcatura NON appartiene al contesto: (se non appartiene al contesto nemmeno l'operaio non dovremmo essere qui)
            (REP)   reparto (ruolo) - contesto proprio - blocco prestato
            (COL)   contesto totale - blocco prestato

        il tecnico NON appartiene al contesto: (se non appartiene al contesto nemmeno il reparto non dovremmo essere qui)
            (REP)   reparto (marcatura) - contesto inprestito - blocco inprestito
            (COL)   contesto inprestito - blocco inprestito
        
        il tecnico NON è presente nel reparto
            (REP)   reparto (marcatura) - contesto inprestito - blocco inprestito
            (REP)   reparto (ruolo) - contesto proprio - blocco prestato
            (COL)   contesto totale - blocco proprio

        il tecnico è presente nel reparto (ELSE)
            (REP)   reparto (marcatura) - contesto proprio - blocco proprio
            (COL)   contesto totale - blocco proprio

        */

        //REPARTO               reparto di appartenenza della marcatura
        //COLTESTO              TOTALE  || INPRESTITO --> contesto COL
        //REPTESTO              PROPRIO || INPRESTITO --> contesto REP
        //COLBLOCCO             blocco dell'oggetto "blocco" COL
        //REPBLOCCO             blocco dell'oggetto "blocco" REP
        //EXTRA                 è un secondo array REP (reparto - reptesto - repblocco)

        $res=array(
            "reparto"=>$this->convRep[$a['cod_officina']],
            "coltesto"=>"",
            "reptesto"=>"",
            "colblocco"=>"",
            "repblocco"=>"",
            "extra"=>null
        );

        //la marcatura NON appartiene al contesto
        if ( !array_key_exists($res['reparto'],$this->reparti) ) {
            $res['reparto']=$a['ruolo']['reparto'];
            $res['coltesto']='totale';
            $res['colblocco']='prestato';
            $res['reptesto']='proprio';
            $res['repblocco']='prestato';
        }
        //il tecnico NON appartiene al contesto
        elseif ( $a['c2rContesto']=='inprestito' ) {
            $res['coltesto']='inprestito';
            $res['colblocco']='inprestito';
            $res['reptesto']='inprestito';
            $res['repblocco']='inprestito';
        }
        //il tecnico NON è presente nel reparto
        elseif ($a['ruolo']['reparto']!=$res['reparto']) {
            $res['coltesto']='totale';
            $res['colblocco']='proprio';
            $res['reptesto']='inprestito';
            $res['repblocco']='inprestito';

            $res['extra']=array(
                "reparto"=>$a['ruolo']['reparto'],
                "reptesto"=>'proprio',
                "repblocco"=>'prestato'
            );
        }
        //il tecnico è presente nel reparto
        else {
            $res['coltesto']='totale';
            $res['colblocco']='proprio';
            $res['reptesto']='proprio';
            $res['repblocco']='proprio';
        }

        return $res;
    }

    function analizza($a) {
        
        //è il totale delle marcature nel lamentato (calcolate, quindi sono solo quelle del periodo in esame)
        if ($a['tot']==0) return;

        //$a è un array TANA
        //ogni elemento delle liste è una macatura aggiornata dal primo spoglio

        /*
        $tana['odl']=$row['num_rif_movimento'];
        $tana['lam']=$row['cod_inconveniente'];
        $tana['chiuso']=$row['ind_chiuso'];
        $tana['pos_lav']=$row['inc_pos_lav'];
        $tana['inc_marc']=$row['inc_marc_chiuse'];
        $tana['addebito']=$row['c2rTipo'];
        $tana['tot']=0;
        $tana['c2rMarcAna]=0
        $tana['tec']=array(
            "tot"=>0,
            "tec"=>array()
        );
        $tana['spe']=array(
            "tot"=>0,
            "tec"=>array()
        );
        */

        //un odl può anche essere lavorato in periodi appartenenti e non appartenenti all'intervallo
        //quindi occorre considerare la percentuale di fatturato in base alla percentuale di marcature nell'intervallo
        //ne segue che POS_LAV debba essere ricalcolato

        if ($a['inc_marc']==0) $a['percPL']=0;
        else {
            $a['percPL']=$a['tot']/$a['inc_marc'];
        }


        //se ci sono delle marcature speciali
        //ridurle fino al raggiungimento dell'efficienza del 105%
        $a['percSpe']=1;

        if ($a['spe']['tot']>0) {

            $rif105=( $a['pos_lav']*$a['percPL'] )/1.05;
            //$tesp=$a['spe']['tot']+$a['tec']['tot'];

            if ($a['tot']<$rif105) $a['percSpe']=1;
            //if ($tesp<$rif105) $a['percSpe']=1;
            elseif ($a['tec']['tot']>$rif105) $a['percSpe']=0;
            else {
                $a['percSpe']=($rif105-$a['tec']['tot'])/$a['spe']['tot'];
            }

            //ricalcolo TOT
            //[SPE][TOT] non viene più usata
            $a['tot']=$a['tec']['tot'] + ( $a['spe']['tot']*$a['percSpe'] );

        }

        foreach ($a['tec']['tec'] as $operaio=>$topo) {

            //topo[tot] è il totale della marcartura dell'operaio nel lamentato (calcolato e quindi considera solo le marcature del periodo in esame)
            if ($topo['tot']==0) continue;

            $this->load('tec',$a,$operaio,$topo);
        }

        if ($a['percSpe']>0) {

            foreach ($a['spe']['tec'] as $operaio=>$topo) {

                $topo['tot']=$topo['tot']*$a['percSpe'];

                if ($topo['tot']==0) continue;

                $this->load('spe',$a,$operaio,$topo);
            }

        }

    }

    function setRepBlocco($rif,$operaio) {

        if ( !isset($this->c2rProdRes['rep'][$rif['reparto']]['col'][$rif['reptesto']]['col'][$operaio]) ) {
            $this->c2rProdRes['rep'][$rif['reparto']]['col'][$rif['reptesto']]['col'][$operaio]=$this->blocco;
        }

        if ( is_null($this->c2rProdRes['rep'][$rif['reparto']]['col'][$rif['reptesto']]['col'][$operaio][$rif['repblocco']]['totale']) ) {
            $this->c2rProdRes['rep'][$rif['reparto']]['col'][$rif['reptesto']]['col'][$operaio][$rif['repblocco']]['flag']=true;
            $this->c2rProdRes['rep'][$rif['reparto']]['col'][$rif['reptesto']]['col'][$operaio][$rif['repblocco']]['totale']=new c2rProdTot();
        }

    }

    function load($tipo,$a,$operaio,$topo) {

        $percOpe=($topo['tot']/$a['tot']);

        $mtipo=($a['chiuso']=='S')?'chiuso':'aperto';

        foreach ($topo['lista'] as $l) {

            if ($tipo=='spe') {
                $l['c2rQta']=$l['c2rQta']*$a['percSpe'];
            }

            $rif=$this->getRif($l);

            //se il blocco REP non esiste crealo
            //il blocco COL è stato eventualmente già creato
            $this->setRepBlocco($rif,$operaio);

            //scrivi marcato
            $this->c2rProdRes['rep'][$rif['reparto']]['col'][$rif['reptesto']]['col'][$operaio][$rif['repblocco']]['totale']->addMarcaturaBase($l['c2rTipo'],$l['c2rQta']);

            if ($this->c2rProdRes['ext']['rc']['flag']) {
                if (isset($this->convRC[$a['accettatore']])) {
                    if (isset($this->c2rProdRes['ext']['rc']['col'][$this->convRC[$a['accettatore']]]['totale'])) {
                        $this->c2rProdRes['ext']['rc']['col'][$this->convRC[$a['accettatore']]]['totale']->addMarcaturaBase($l['c2rTipo'],$l['c2rQta']);
                    }
                }
            }

            //non ci sono marcature EXTRA in caso di SPE
            if (!is_null($rif['extra'])) {
                $this->setRepBlocco($rif['extra'],$operaio);
                $this->c2rProdRes['rep'][$rif['extra']['reparto']]['col'][$rif['extra']['reptesto']]['col'][$operaio][$rif['extra']['repblocco']]['totale']->addMarcaturaBase($l['c2rTipo'],$l['c2rQta']);

                //clausola PRESTATO
                if ($rif['extra']['repblocco']=='prestato') {
                    $this->c2rProdRes['rep'][$rif['extra']['reparto']]['col'][$rif['extra']['reptesto']]['col'][$operaio][$rif['extra']['repblocco']]['totale']->addPresenzaSpeciale('prestato',$l['c2rQta']*60);
                }
            }   

            //if ($tipo=='spe' && $rif['repblocco']!='prestato') {
            if ($tipo=='spe' && $rif['repblocco']!='inprestito' && $rif['repblocco']!='prestato') {
                $this->c2rProdRes['rep'][$rif['reparto']]['col'][$rif['reptesto']]['col'][$operaio][$rif['repblocco']]['totale']->addPresenzaSpeciale('inclusione',$l['c2rQta']*60);
            }

            //echo json_encode($rif);
            if (is_null($this->c2rProdRes['col'][$rif['coltesto']]['col'][$operaio]['totale'][$rif['colblocco']]['totale'])) {
                $this->c2rProdRes['col'][$rif['coltesto']]['col'][$operaio]['totale'][$rif['colblocco']]['flag']=true;
                $this->c2rProdRes['col'][$rif['coltesto']]['col'][$operaio]['totale'][$rif['colblocco']]['totale']=new c2rProdTot();
            }
            $this->c2rProdRes['col'][$rif['coltesto']]['col'][$operaio]['totale'][$rif['colblocco']]['totale']->addMarcaturaBase($l['c2rTipo'],$l['c2rQta']);

            //if ($tipo=='spe' && $rif['colblocco']!='prestato') {
            if ($tipo=='spe' && $rif['repblocco']!='inprestito') {
                $this->c2rProdRes['col'][$rif['coltesto']]['col'][$operaio]['totale'][$rif['colblocco']]['totale']->addPresenzaSpeciale('inclusione',$l['c2rQta']*60);
            }

            //clausola INPRESTITO
            if ($rif['repblocco']=='inprestito') {
                $this->c2rProdRes['rep'][$rif['reparto']]['col'][$rif['reptesto']]['col'][$operaio][$rif['repblocco']]['totale']->addPresenzaSpeciale('inprestito',$l['c2rQta']*60);
            }
            if ($rif['colblocco']=='inprestito') {
                $this->c2rProdRes['col'][$rif['coltesto']]['col'][$operaio]['totale'][$rif['colblocco']]['totale']->addPresenzaSpeciale('inprestito',$l['c2rQta']*60);
            }

            //clausola PRESTATO
            if ($rif['repblocco']=='prestato') {
                if ($tipo=='spe') {
                    //$this->c2rProdRes['rep'][$rif['reparto']]['col'][$rif['reptesto']]['col'][$operaio][$rif['repblocco']]['totale']->addPresenzaSpeciale('prestato',$l['c2rQta']*60);
                }
                else {
                    $this->c2rProdRes['rep'][$rif['reparto']]['col'][$rif['reptesto']]['col'][$operaio][$rif['repblocco']]['totale']->addPresenzaSpeciale('prestato',$l['c2rQta']*60);
                }
            }

            //scrivi aperto
            if ($mtipo=='aperto') {
                $this->c2rProdRes['rep'][$rif['reparto']]['col'][$rif['reptesto']]['col'][$operaio][$rif['repblocco']]['totale']->addMarcatura('aperto',$l['c2rTipo'],$l['c2rQta']);
                $this->c2rProdRes['col'][$rif['coltesto']]['col'][$operaio]['totale'][$rif['colblocco']]['totale']->addMarcatura('aperto',$l['c2rTipo'],$l['c2rQta']);

                if ($this->c2rProdRes['ext']['rc']['flag']) {
                    if (isset($this->convRC[$a['accettatore']])) {
                        if (isset($this->c2rProdRes['ext']['rc']['col'][$this->convRC[$a['accettatore']]]['totale'])) {
                            $this->c2rProdRes['ext']['rc']['col'][$this->convRC[$a['accettatore']]]['totale']->addMarcatura('aperto',$l['c2rTipo'],$l['c2rQta']);
                        }
                    }
                }

                if (!is_null($rif['extra'])) {
                    $this->c2rProdRes['rep'][$rif['extra']['reparto']]['col'][$rif['extra']['reptesto']]['col'][$operaio][$rif['extra']['repblocco']]['totale']->addMarcatura('aperto',$l['c2rTipo'],$l['c2rQta']);
                }
            }

            //scrivi chiuso
            if ($mtipo=='chiuso') {

                //fatturato * la percentuale di presenza dell'operaio nelle marcature del lamentato 
                // * il peso della marcatura attuale sul totale dell'operaio
                $mval=$a['pos_lav']*$percOpe*($l['c2rQta']/$topo['tot']);

                $mval=$mval*$a['percPL'];

                //$this->log[]=array($operaio,$a['pos_lav'],$percOpe,$topo['tot'],$a['tot'],$l['c2rQta']/$topo['tot']);

                $this->c2rProdRes['rep'][$rif['reparto']]['col'][$rif['reptesto']]['col'][$operaio][$rif['repblocco']]['totale']->addMarcatura('chiuso',$l['c2rTipo'],$mval);
                $this->c2rProdRes['col'][$rif['coltesto']]['col'][$operaio]['totale'][$rif['colblocco']]['totale']->addMarcatura('chiuso',$l['c2rTipo'],$mval);

                if ($this->c2rProdRes['ext']['rc']['flag']) {
                    if (isset($this->convRC[$a['accettatore']])) {
                        if (isset($this->c2rProdRes['ext']['rc']['col'][$this->convRC[$a['accettatore']]]['totale'])) {
                            $this->c2rProdRes['ext']['rc']['col'][$this->convRC[$a['accettatore']]]['totale']->addMarcatura('chiuso',$l['c2rTipo'],$mval);
                        }
                    }
                }

                if (!is_null($rif['extra'])) {
                    $this->c2rProdRes['rep'][$rif['extra']['reparto']]['col'][$rif['extra']['reptesto']]['col'][$operaio][$rif['extra']['repblocco']]['totale']->addMarcatura('chiuso',$l['c2rTipo'],$mval);
                }

                $l['c2rFatt']=$mval;
            }

            //scrivi dettaglio
            $l['percPL']=$a['percPL'];
            $this->c2rProdRes['col'][$rif['coltesto']]['col'][$operaio]['dettaglio'][]=$l;

            //scrivi la marcatura speciale se non è stato possibile nel primo spoglio
            if (isset($l['speciale'])) {
                if ($l['speciale']['tipo']=='PUL') {
                    $this->c2rProdRes['rep'][$rif['reparto']]['col'][$rif['reptesto']]['col'][$operaio][$rif['repblocco']]['totale']->addMarcaturaExtra('PUL',$l['speciale']['valore']);
                    $this->c2rProdRes['col'][$rif['coltesto']]['col'][$operaio]['totale'][$rif['colblocco']]['totale']->addMarcaturaExtra('PUL',$l['speciale']['valore']);
                }
            }

        }

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
                //$row['speciale']['valore']=$row['qta'];
                $row['speciale']['valore']=$obj['limite'];
                $row['speciale']['escluso']=$row['qta']-$obj['limite'];
            }
        }

        return $row;
    }

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

                //$temp=$this->intervallo->getPresenzaCollDay($reparto,$this->operai[$operaio]['ID_coll'],date('Ymd',$rif));
                if (isset($this->intCollDay[$reparto][$this->operai[$operaio]['ID_coll']][date('Ymd',$rif)])) {
                    $temp=$this->intCollDay[$reparto][$this->operai[$operaio]['ID_coll']][date('Ymd',$rif)];
                }
                else $temp=false;

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

    function draw() {

        //echo json_encode($this->operai);

        //new DIVO 1
        //($index,$htab,$minh,$fixed)
        $divo1=new Divo('c2rProdRes','5%','97%',1);
        $divo1->setBk('#a89dce');

        //se non siamo in una analisi specifica per collaboratore
        if ($this->param['default']['totali']=='true') {

            $txt='<div style="width:97%;margin-bottom:20px;">';

                $txt.='<div style="font-weight:bold;position:relative;margin-top:4px;margin-bottom:2px;color:red;">'.mainFunc::gab_todata($this->param['inizio']).' - '.mainFunc::gab_todata($this->param['fine']).'</div>';

                if ($this->c2rProdRes['tot']['totale']['nomarc']['flag']) {
                    $temptt=new c2rProdTot();
                    $temptt->write($this->c2rProdRes['tot']['totale']['totale']['totale']->read(),'std');
                    $temptt->write($this->c2rProdRes['tot']['totale']['nomarc']['totale']->read(),'std');
                    $temptt->consolida();

                    $txt.='<div style="margin-top:10px;">';
                        $txt.='<div style="background-color:#c9dcd7;">TOTALE contesto + non marcato</div>';
                        $txt.=$temptt->draw();
                        $txt.=$temptt->resoconto('ttcont',$this->param['prodTipo'],false);
                        $txt.=$temptt->statistica(count($this->c2rProdRes['col']['totale']['col']));
                    $txt.='</div>';

                }

                if ($this->c2rProdRes['tot']['totale']['totale']['flag']) {
                    $txt.='<div style="margin-top:10px;">';
                        //scrivi totale contesto
                        $txt.='<div style="background-color:bisque;">TOTALE contesto incluso prestate ed in prestito</div>';
                        $txt.=$this->c2rProdRes['tot']['totale']['totale']['totale']->draw();
                        $txt.=$this->c2rProdRes['tot']['totale']['totale']['totale']->resoconto('tot',$this->param['prodTipo'],false);
                    $txt.='</div>';
                }

                if ($this->c2rProdRes['tot']['totale']['proprio']['flag']) {
                    $txt.='<div style="margin-top:10px;">';
                        //scrivi totale proprio contesto
                        $txt.='<div style="background-color:bisque;">Proprio contesto incluso prestate</div>';
                        $txt.=$this->c2rProdRes['tot']['totale']['proprio']['totale']->draw();
                        $txt.=$this->c2rProdRes['tot']['totale']['proprio']['totale']->resoconto('totpp',$this->param['prodTipo'],false);
                    $txt.='</div>';
                }

                if ($this->c2rProdRes['tot']['totale']['inprestito']['flag']) {
                    $txt.='<div style="margin-top:10px;">';
                        //scrivi totale inprestito contesto
                        $txt.='<div>In prestito contesto</div>';
                        $txt.=$this->c2rProdRes['tot']['totale']['inprestito']['totale']->draw();
                    $txt.='</div>';
                }

                if ($this->c2rProdRes['tot']['totale']['nomarc']['flag']) {
                    $txt.='<div style="margin-top:10px;background-color:bisque;">';
                        //scrivi totale non marcato contesto
                        $txt.='<div>Fatturato non marcato contesto</div>';
                        $txt.=$this->c2rProdRes['tot']['totale']['nomarc']['totale']->draw();
                    $txt.='</div>';
                }

                /*$txt.='<div>';
                    //$txt.=json_encode($this->galileo->getLog('query'));
                    $txt.=json_encode($this->param['default']);
                $txt.='</div>';*/

            $txt.='</div>';

            $divo1->add_div($this->c2rProdRes['tot']['tag'],'black',0,'',$txt,1,array("margin-left"=>"10px","margin-right"=>"5px","font-weight"=>"bold","text-align"=>"center"));

            unset($txt);
        }

        //ksort($this->c2rProdRes['rep']);

        //se non siamo in una analisi specifica per collaboratore
        if ($this->param['default']['totali']=='true') {

            //scrivi i div dei reparti
            foreach ($this->c2rProdRes['rep'] as $reparto=>$r) {

                $txt='<div>';

                    $txt.=$this->drawColRep($reparto);
                    
                $txt.='</div>';

                //add DIV
                //add_div($titolo,$color,$chk,$stato,$codice,$selected,$css)
                $divo1->add_div($reparto,'black',0,'',$txt,0,array("margin-left"=>"10px","margin-right"=>"5px","font-weight"=>"bold","text-align"=>"center"));

                unset($txt);
            }

        }

        if ($this->param['default']['collab']=='true') {
            //scrivi il div dei collaboratori

            $txt='<div>';

                $txt.=$this->drawColl();
                
            $txt.='</div>';

            //add DIV
            //add_div($titolo,$color,$chk,$stato,$codice,$selected,$css)
            $divo1->add_div("Collab",'black',0,'',$txt,0,array("margin-left"=>"10px","margin-right"=>"5px","font-weight"=>"bold","text-align"=>"center"));

            unset($txt);
        }

        foreach ($this->c2rProdRes['ext'] as $ext=>$e) {

            if (!$e['flag']) continue;

            $txt='<div>';

                $tempext="drawExt_".$e['tag'];

                $txt.=$this->$tempext();

                //se non è stato aggiunto nulla
                if ($txt=="<div>") continue;
                
            $txt.='</div>';

            //add DIV
            //add_div($titolo,$color,$chk,$stato,$codice,$selected,$css)
            $divo1->add_div($e['tag'],'blue',0,'',$txt,0,array("margin-left"=>"10px","margin-right"=>"5px","font-weight"=>"bold","text-align"=>"center"));

            unset($txt);
        }

        $divo1->build();
        $divo1->draw();

        unset($divo1);

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

        $txt='<div style="width:97%;margin-bottom:20px;">';

            $txt.='<div style="font-weight:bold;position:relative;margin-top:4px;margin-bottom:2px;color:red;">'.mainFunc::gab_todata($this->param['inizio']).' - '.mainFunc::gab_todata($this->param['fine']).'</div>';

            if ($this->c2rProdRes['rep'][$reparto]['totale']['nomarc']['flag']) {
                $temptt=new c2rProdTot();
                $temptt->write($this->c2rProdRes['rep'][$reparto]['totale']['totale']['totale']->read(),'std');
                $temptt->write($this->c2rProdRes['rep'][$reparto]['totale']['nomarc']['totale']->read(),'std');
                $temptt->consolida();

                $txt.='<div style="margin-top:10px;">';
                    $txt.='<div style="background-color:#c9dcd7;">TOTALE reparto + non marcato</div>';
                    $txt.=$temptt->draw();
                    $txt.=$temptt->resoconto('tt_'.$reparto,$this->param['prodTipo'],false);
                    $txt.=$temptt->statistica(count($this->c2rProdRes['rep'][$reparto]['col']['proprio']['col']));
                $txt.='</div>';

            }

            if ($this->c2rProdRes['rep'][$reparto]['totale']['totale']['flag']) {
                $txt.='<div style="margin-top:10px;">';
                    $txt.='<div style="background-color:bisque;">TOTALE reparto</div>';
                    $txt.=$this->c2rProdRes['rep'][$reparto]['totale']['totale']['totale']->draw();
                    $txt.=$this->c2rProdRes['rep'][$reparto]['totale']['totale']['totale']->resoconto($reparto,$this->param['prodTipo'],false);
                $txt.='</div>';
            }

            if ($this->c2rProdRes['rep'][$reparto]['totale']['proprio']['flag']) {
                $txt.='<div style="margin-top:10px;">';
                    $txt.='<div>Proprio reparto</div>';
                    $txt.=$this->c2rProdRes['rep'][$reparto]['totale']['proprio']['totale']->draw();
                    $txt.=$this->c2rProdRes['rep'][$reparto]['totale']['proprio']['totale']->resoconto($reparto,$this->param['prodTipo'],false);
                $txt.='</div>';
            }

            if ($this->c2rProdRes['rep'][$reparto]['totale']['inprestito']['flag']) {
                $txt.='<div style="margin-top:10px;">';
                    $txt.='<div>In prestito reparto</div>';
                    $txt.=$this->c2rProdRes['rep'][$reparto]['totale']['inprestito']['totale']->draw();
                $txt.='</div>';
            }

            if ($this->c2rProdRes['rep'][$reparto]['totale']['prestato']['flag']) {
                $txt.='<div style="margin-top:10px;background-color:#dddddd;">';
                    $txt.='<div>Prestato reparto</div>';
                    $txt.=$this->c2rProdRes['rep'][$reparto]['totale']['prestato']['totale']->draw();
                $txt.='</div>';
            }

            if ($this->c2rProdRes['rep'][$reparto]['totale']['nomarc']['flag']) {
                $txt.='<div style="margin-top:10px;background-color:bisque;">';
                    $txt.='<div>Fatturato non marcato</div>';
                    $txt.=$this->c2rProdRes['rep'][$reparto]['totale']['nomarc']['totale']->draw();
                $txt.='</div>';
            }

            //per esempio il reparto CAR non apre ordini propri ma tutte le lavorazioni le fa in esterno
            //quindi occorre un'ulteriore analisi (PER IL MOMENTO NON VIENE PARAMETRIZZATA MA FISSAta per CAR)
            //NON PARE SERVIRE A NULLA
            /*
            if ($reparto=='CAR') {

                $temptot=new c2rProdTot();

                if (!is_null($this->c2rProdRes['rep'][$reparto]['totale']['proprio']['totale'])) {
                    $temptot->write($this->c2rProdRes['rep'][$reparto]['totale']['proprio']['totale']->read(),'std');
                }
                if (!is_null($this->c2rProdRes['rep'][$reparto]['totale']['inprestito']['totale'])) {
                    $temptot->write($this->c2rProdRes['rep'][$reparto]['totale']['inprestito']['totale']->read(),'std');
                }
                if (!is_null($this->c2rProdRes['rep'][$reparto]['totale']['prestato']['totale'])) {
                    $temptot->write($this->c2rProdRes['rep'][$reparto]['totale']['prestato']['totale']->read(),'std');
                }

                $temptot->consolida();
                $temptot->resocontoPrestato();
            }*/

        $txt.='</div>';

        $divo2->add_div('Totale','black',0,'',$txt,0,$css);
        
        if ($this->param['default']['repcol']=='true') {

            $txt='<div>';

                $txt.=$this->drawProprio($reparto);
                
            $txt.='</div>';

            $divo2->add_div($this->c2rProdRes['rep'][$reparto]['col']['proprio']['tag'],'black',0,'',$txt,0,$css);

            $txt='<div>';
                //scrivi collaboratori in prestito
                $txt.=$this->drawInprestito($reparto);

            $txt.='</div>';

            $divo2->add_div($this->c2rProdRes['rep'][$reparto]['col']['inprestito']['tag'],'black',0,'',$txt,0,$css);
        }
        
        if ($this->c2rProdRes['rep'][$reparto]['totale']['nomarc']['flag']) {

            $txt='<div>';
                //scrivi fatturato non marcato
                $txt.='<div style="font-weight:bold;position:relative;margin-top:4px;margin-bottom:2px;color:red;">'.mainFunc::gab_todata($this->param['inizio']).' - '.mainFunc::gab_todata($this->param['fine']).'</div>';
                $txt.=$this->drawRepNomarc($reparto);
            $txt.='</div>';

            $divo2->add_div($this->c2rProdRes['rep'][$reparto]['totale']['nomarc']['tag'],'black',0,'',$txt,0,$css);
        }

        $divo2->build();
        ob_start();
            $divo2->draw();
            unset($divo2);
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

            $txt='<div style="width:97%;">';

                $txt.='<div style="font-weight:bold;margin-top:5px;">';
                    $txt.= $operaio.' - '.$this->operai[$operaio]['cognome'].' '.$this->operai[$operaio]['nome'];
                $txt.= '</div>';

                $txt.='<div style="margin-top:10px;">';
                    $txt.='<div style="font-weight:bold;position:relative;margin-top:4px;margin-bottom:2px;color:red;">'.mainFunc::gab_todata($this->param['inizio']).' - '.mainFunc::gab_todata($this->param['fine']).'</div>';
                    $txt.='<div style="background-color:bisque;">TOTALE nel reparto</div>';
                    $txt.= $o['totale']['totale']->draw();
                    $txt.= $o['totale']['totale']->resoconto($reparto.$operaio,'standard',true);
                $txt.= '</div>';

                $txt.='<div style="margin-top:10px;">';
                    $txt.='<div>Proprio</div>';
                    //aggiunto IF 01.07.2021
                    if (isset($o['proprio']['totale'])) {
                        $txt.= $o['proprio']['totale']->draw();
                    }
                $txt.= '</div>';

                if ($o['prestato']['flag']) {
                    $txt.='<div style="margin-top:10px;background-color:#dddddd;">';
                        $txt.='<div>Prestato</div>';
                        $txt.= $o['prestato']['totale']->draw();
                    $txt.= '</div>';
                }
            
            $txt.='</div>';

            $divo5->add_div($operaio,'black',0,'',$txt,0,$css);

        }

        $divo5->build();
        ob_start();
            $divo5->draw();
            unset($divo5);
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

            $txt='<div style="width:97%;">';

                $txt.= '<div style="font-weight:bold;margin-top:5px;">';
                    $txt.= $operaio.' - '.$this->operai[$operaio]['cognome'].' '.$this->operai[$operaio]['nome'];
                $txt.= '</div>';

                $txt.= '<div style="margin-top:10px;">';
                    $txt.='<div style="font-weight:bold;position:relative;margin-top:4px;margin-bottom:2px;color:red;">'.mainFunc::gab_todata($this->param['inizio']).' - '.mainFunc::gab_todata($this->param['fine']).'</div>';
                    $txt.='<div>In prestito</div>';
                    $txt.=$o['inprestito']['totale']->draw();
                $txt.= '</div>';
            
            $txt.='</div>';

            $divo7->add_div($operaio,'black',0,'',$txt,0,$css);

        }

        $divo7->build();
        ob_start();
            $divo7->draw();
            unset($divo7);
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

        /*
        $txt='<div>';
            //scrivi totale collaboratore ( oggetto TOTALE )
            
        $txt.='</div>';
        */

        //$divo3->add_div('Valori','black',0,'',$txt,0,$css);

        $txt='<div>';
            //scrivi dettaglio collaboratore
            $txt.=$this->drawCollDet($officina,$operaio);
        $txt.='</div>';

        $divo3->add_div('Dettaglio','black',0,'',$txt,0,$css);

        $divo3->build();
        ob_start();
            $divo3->draw();
            unset($divo3);
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

        //se non siamo in una analisi specifica per collaboratore
        if ($this->param['default']['totali']=='true') {

            $txt='<div>';

                $txt.=$this->drawListaColl('inprestito');
                
            $txt.='</div>';

            $divo4->add_div($this->c2rProdRes['col']['inprestito']['tag'],'black',0,'',$txt,0,$css);
        }

        $divo4->build();
        ob_start();
            $divo4->draw();
            unset($divo4);
        return ob_get_clean();  

    }

    function drawListaColl($contesto) {

        $divo6=new Divo('c2rListaColl_'.$contesto,'4%','95%',1);
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
            unset($divo6);
        return ob_get_clean();  
    }

    function drawCollDet($contesto,$operaio) {

        $divo8=new Divo('c2rDetColl_'.$operaio,'5%','94%',1);
        $divo8->setBk('#c5c11a');

        $css=array(
            "font-weight"=>"bold",
            "font-size"=>"0.9em",
            "text-align"=>"center",
            "margin-left"=>"2px"
        );

        ob_start();

            echo '<div style="width:97%;">';

                //nell'analisi dei collaboratori il contesto è determinato a priori
                echo '<div style="margin-top:10px;">';
                    if ($contesto=='totale') {
                        echo $this->c2rProdRes['col'][$contesto]['col'][$operaio]['totale']['proprio']['totale']->draw();
                        echo $this->c2rProdRes['col'][$contesto]['col'][$operaio]['totale']['proprio']['totale']->resoconto($operaio,'standard',true);
                    }
                    else {
                        echo $this->c2rProdRes['col'][$contesto]['col'][$operaio]['totale']['inprestito']['totale']->draw();
                        echo $this->c2rProdRes['col'][$contesto]['col'][$operaio]['totale']['inprestito']['totale']->resoconto($operaio,'standard',true);
                    }
                echo '</div>';
                
                //echo json_encode($this->log);
                //echo json_encode($this->reparti);
            
            echo '</div>';

        $divo8->add_div('Totale','black',0,'',ob_get_clean(),0,$css);

        usort($this->c2rProdRes['col'][$contesto]['col'][$operaio]['dettaglio'], array('c2rProduttivita_S','cmp'));

        ob_start();

            echo '<div style="width:97%;">';

                echo '<table style="width:100%;border-collapse:collapse;font-size:0.9em;">';

                    echo '<colgroup>';

                        echo '<col style="width:4%;" />';
                        echo '<col style="width:15%;" />';
                        echo '<col style="width:6%;" />';
                        echo '<col style="width:6%;" />';
                        echo '<col style="width:16%;" />';
                        echo '<col style="width:16%;" />';
                        echo '<col style="width:11%;" />';
                        echo '<col style="width:5%;" />';
                        echo '<col style="width:5%;" />';
                        echo '<col style="width:16%;" />';

                    echo '</colgroup>';

                    echo '<tr>';

                        echo '<th>Off</th>';
                        echo '<th>Odl</th>';
                        echo '<th>Da</th>';
                        echo '<th>A</th>';
                        echo '<th>Marcato (calcolato)</th>';
                        echo '<th>Fatturato (Tot Lam)</th>';
                        echo '<th>Ruolo</th>';
                        echo '<th>Spec</th>';
                        echo '<th>Add</th>';
                        echo '<th>Ragsoc</th>';

                    echo '</tr>';

                    $index="";
                
                    foreach ($this->c2rProdRes['col'][$contesto]['col'][$operaio]['dettaglio'] as $m) {

                        if ($index!=$m['d_inizio']) {

                            $index=$m['d_inizio'];

                            $tempturno="";

                            if (isset($this->colTurnoDay[$this->operai[$operaio]['ID_coll']][$index])) {
                                foreach ($this->colTurnoDay[$this->operai[$operaio]['ID_coll']][$index] as $b) {
                                    $tempturno.=$b['i'].' - '.$b['f'].' / ';
                                }
                            }
                            
                            echo '<tr>';
                                echo '<td colspan="2" style="font-weight:bold;border-top:1px solid black;" >';
                                    echo '<div style="margin-left:10px;">';
                                        echo mainFunc::gab_todata($index);
                                    echo '</div>';
                                echo '</td>';
                                echo '<td colspan="8" style="font-weight:bold;border-top:1px solid black;" >';
                                    echo '<div style="text-align:left;">';
                                        echo substr($tempturno,0,-3);
                                    echo '</div>';
                                echo '</td>';
                            echo '</tr>';
                        }

                        echo '<div style="margin-top:10px;">';

                            //{"num_rif_movimento":"1337950","cod_inconveniente":"A","cod_operaio":"18","num_riga":2,
                            //"d_inizio":"20210601","o_inizio":"17:17","d_fine":"20210601","o_fine":"17:18","qta":".02",
                            //"des_note":"PUL{\"coll\":\"18\",\"rif\":\"1337950\",\"lam\":\"A\",\"limite\":\"0.05\"}",
                            //"ind_chiuso":"N","cod_officina":"PV","inc_pos_lav":"1.20","inc_marc_chiuse":".72","num_tecnici":1,"tot_marc_odl":".72","tot_fatt_odl":"1.20","cod_accettatore":"00001","qta_ore_prenotazione":"1.75",
                            //"acarico":"OCO","cod_movimento":"OOP","cod_tipo_garanzia":"",
                            //"rep_proprio":"VWS","presenza":{"nominale":480,"actual":480},"c2rTipo":"pag","ruolo":{"reparto":"VWS","mgruppo":"TES","gruppo":"TEC"},
                            //"c2rContesto":"totale","c2rQta":0,"speciale":{"tipo":"PUL","valore":".02","escluso":0}}

                            echo '<tr>';

                                echo '<td style="text-align:center;';
                                    if ($this->convRep[$m['cod_officina']]!=$m['rep_proprio']) echo 'color:goldenrod;';
                                echo '">'.$this->convRep[$m['cod_officina']].'</td>';

                                echo '<td style="text-align:center;">'.$m['num_rif_movimento'].' ('.$m['cod_inconveniente'].')</td>';
                                echo '<td style="text-align:center;">'.$m['o_inizio'].'</td>';
                                echo '<td style="text-align:center;">'.$m['o_fine'].'</td>';

                                if ($m['d_fine']!="") {
                                    echo '<td style="text-align:center;">'.number_format($m['qta'],2,',','').'  ( '.number_format($m['c2rQta'],2,',','').' )</td>';
                                }
                                else echo '<td></td>';

                                if (isset($m['c2rFatt'])) {
                                    echo '<td style="text-align:center;">'.number_format($m['c2rFatt'],2,',','').'  ( '.number_format($m['inc_pos_lav']*$m['percPL'],2,',','').' )</td>';
                                }
                                else echo '<td></td>';

                                echo '<td>';
                                    if (isset($m['ruolo'])) echo $m['ruolo']['reparto'].' ('.$m['ruolo']['gruppo'].'-'.$m['ruolo']['mgruppo'].')';
                                echo '</td>';

                                echo '<td style="text-align:center;">';
                                    if (isset($m['speciale'])) echo $m['speciale']['tipo'];
                                echo '</td>';

                                echo '<td style="text-align:center;">'.$m['c2rTipo'].'</td>';

                                echo '<td style="text-align:left;">'.substr($m['des_ragsoc'],0,16).'</td>';

                            echo '</tr>';
                            
                        echo '</div>';

                    }

                echo '</table>';

            echo '</div>';

        $divo8->add_div('Dettaglio','black',0,'',ob_get_clean(),0,$css);
        

        $divo8->build();

        ob_start();

            echo '<div style="font-weight:bold;margin-top:0.5%;margin-bottom:0.5%;">';
                echo $operaio.' - '.$this->operai[$operaio]['cognome'].' '.$this->operai[$operaio]['nome'];
            echo '</div>';

            echo '<div style="font-weight:bold;position:relative;margin-top:4px;margin-bottom:2px;color:red;">'.mainFunc::gab_todata($this->param['inizio']).' - '.mainFunc::gab_todata($this->param['fine']).'</div>';

            echo '<div style="height:94%;">';

                $divo8->draw();
                unset($divo8);
            
            echo '</div>';

        return ob_get_clean();  
    }

    function drawRepNomarc($reparto) {

        $divo9=new Divo('c2rProdNomarc_'.$reparto,'4%','96%',1);
        $divo9->setBk('#cea0a9');

        $css=array(
            "font-weight"=>"bold",
            "margin-top"=>"0px",
            "font-size"=>"0.8em",
            "text-align"=>"center"
        );

        $txt='<div style="margin-top:10px;">';
            $txt.=$this->c2rProdRes['rep'][$reparto]['totale']['nomarc']['totale']->draw();
        $txt.= '</div>';

        $divo9->add_div('Totale','black',0,'',$txt,0,$css);

        $txt=$this->drawRepNomarcDett($this->c2rProdRes['rep'][$reparto]['totale']['nomarc']['dettaglio']);

        $divo9->add_div('Dettaglio','black',0,'',$txt,0,$css);

        $divo9->build();
        ob_start();
            $divo9->draw();
            unset($divo9);
        return ob_get_clean();  
    }

    function drawRepNomarcDett($lines) {

        ob_start();

            echo '<div style="width:97%;">';

                echo '<table style="width:100%;border-collapse:collapse;font-size:0.9em;">';

                    echo '<colgroup>';

                        echo '<col style="width:4%;" />';
                        echo '<col style="width:15%;" />';
                        echo '<col style="width:10%;" />';
                        echo '<col style="width:38%;" />';
                        echo '<col style="width:33%;" />';

                    echo '</colgroup>';

                    echo '<tr>';

                        echo '<th>Off</th>';
                        echo '<th>Odl</th>';
                        echo '<th>Fatturato</th>';
                        echo '<th>Lamentato</th>';
                        echo '<th>Ragsoc</th>';

                    echo '</tr>';

                    echo '<tbody>';

                        foreach ($lines as $l) {

                            echo '<tr>';

                                echo '<td style="text-align:center;">'.$this->convRep[$l['inc_cod_off']].'</td>';

                                echo '<td style="text-align:center;">'.$l['num_rif_movimento'].' ('.$l['cod_inconveniente'].')</td>';

                                echo '<td style="text-align:center;">'.number_format($l['inc_pos_lav'],2,',','').'</td>';

                                echo '<td style="text-align:left;">'.substr($l['inc_testo'],0,35).'</td>';

                                echo '<td style="text-align:left;">'.substr($l['des_ragsoc'],0,30).'</td>';

                            echo '</tr>';
                        }

                    echo '</tbody>';
                
                echo '</table>';
            
            echo '</div>';

        return ob_get_clean();

    }

    function drawExt_RC() {
        
        $divo10=new Divo('c2rRC','4%','95%',1);
        $divo10->setBk('#cecda0');

        $css=array(
            "font-weight"=>"bold",
            "margin-top"=>"3px",
            "font-size"=>"0.8em",
            "text-align"=>"center",
            "margin-left"=>"2px"
        );

        foreach ($this->rcCol as $rc=>$c) {

            if ($this->param['default']['responsabile']=="false" && $this->param['default']['collaboratore']!=$rc) continue; 

            ob_start();

            echo '<div style="font-weight:bold;margin-top:0.5%;margin-bottom:0.5%;">';
                echo $rc.' - '.$this->coll[$rc]['cognome'].' '.$this->coll[$rc]['nome'];
            echo '</div>';

            echo '<div style="font-weight:bold;position:relative;margin-top:4px;margin-bottom:2px;color:red;">'.mainFunc::gab_todata($this->param['inizio']).' - '.mainFunc::gab_todata($this->param['fine']).'</div>';

            echo '<div style="height:94%;width:98%;">';

                //echo json_encode($this->param);
                echo '<div>';
                    echo $this->c2rProdRes['ext']['rc']['col'][$rc]['totale']->draw();
                echo '</div>';

                if (isset($this->c2rProdRes['ext']['rc']['col'][$rc]['nomarc'])) {
                    echo '<div style="margin-top:15px;">';
                        echo '<div style="font-weight:bold;">Fatturato non marcato:</div>';
                    echo '</div>';

                    echo '<div style="background-color:bisque;">';
                        echo $this->c2rProdRes['ext']['rc']['col'][$rc]['nomarc']->draw();
                    echo '</div>';
                }

            echo '</div>';
            
            $divo10->add_div($rc,'black',0,'',ob_get_clean(),0,$css);
        }

        $divo10->build();
        ob_start();
            $divo10->draw();
            unset($divo10);
        return ob_get_clean();  
    }

    //////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //interfacciamento CENTAVOS
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////

    function getRepParam($reparto,$ambito,$parametro) {
        //VWS - totale - effP
        /*
            $this->c2rProdRes['rep'][$rep]['totale']['totale']['totale']=new c2rProdTot();
        */

        if ($parametro=='effP' || $parametro=='effI' || $parametro=='effG') {

            $p='';

            switch ($parametro) {
                case "effP": $p='pag';
                break;
                case "effI": $p='app';
                break;
                case "effG": $p='gar';
                break;
            }

            if ($ambito=='totale') {

                $a=$this->subGetRepParam($p,$reparto,$ambito);

                if ($parametro=='effI') {
                    $temp=$this->subGetRepParam('rip',$reparto,$ambito);

                    $a['marcato']+=$temp['marcato'];
                    $a['fatturato']+=$temp['fatturato'];
                }

                if ($a['marcato']==0) return 1;

                return $a['fatturato']/$a['marcato'];
            }
        }

        else if ($parametro=='grUtil') {

            if ($ambito=='totale') {

                $res=$this->c2rProdRes['rep'][$reparto][$ambito]['totale']['totale']->getRes();

                if ($res['presenza']==0) return 1;
                else return $res['marcato']/$res['presenza'];
            }
        }

        else if ($parametro=='prodP' || $parametro=='prodI' || $parametro=='prodG') {

            if ($ambito=='totale') {

                $grut=$this->getRepParam($reparto,$ambito,'grUtil');

                $eff=$this->getRepParam($reparto,$ambito,'eff'.substr($parametro,-1));

                return $grut*$eff;
            }
        }

        else if ($parametro=='preNom') return $this->c2rProdRes['rep'][$reparto][$ambito]['totale']['totale']->getValore('presenza','nominale','valore');
        else if ($parametro=='preExt') return $this->c2rProdRes['rep'][$reparto][$ambito]['totale']['totale']->getValore('presenza','extra','valore');
        else if ($parametro=='preAss') return $this->c2rProdRes['rep'][$reparto][$ambito]['totale']['totale']->getValore('presenza','assenza','valore');
        else if ($parametro=='preMal') return $this->c2rProdRes['rep'][$reparto][$ambito]['totale']['totale']->getValore('presenza','malattia','valore');

    }

    function subGetRepParam($p,$reparto,$ambito) {

        $fatturato=0;
        $marcato=0;

        if ($this->c2rProdRes['rep'][$reparto][$ambito]['nomarc']['flag']) {
            $fatturato+=$this->c2rProdRes['rep'][$reparto][$ambito]['nomarc']['totale']->getValore('chiuso',$p,'valore');
        }
        if ($this->c2rProdRes['rep'][$reparto][$ambito]['totale']['flag']) {
            $fatturato+=$this->c2rProdRes['rep'][$reparto][$ambito]['totale']['totale']->getValore('aperto',$p,'valore');
            $fatturato+=$this->c2rProdRes['rep'][$reparto][$ambito]['totale']['totale']->getValore('chiuso',$p,'valore');
        }

        $marcato=$this->c2rProdRes['rep'][$reparto][$ambito]['totale']['totale']->getValore('marcato',$p,'valore');
        //return $this->c2rProdRes['rep'][$reparto][$ambito]['totale']['totale']->getValore('aperto','pag','eff');

        return array("fatturato"=>$fatturato,"marcato"=>$marcato);
    }

    function getColParam($IDcoll,$parametro) {
        //9 - effP
        /*
           $this->c2rProdRes['col']['totale']['col'][$this->coll[$IDcoll]['cod_operaio']]
        */

        if ($parametro=='effP' || $parametro=='effI' || $parametro=='effG') {

            $p='';
            $fatturato=0;
            $marcato=0;

            switch ($parametro) {
                case "effP": $p='pag';
                break;
                case "effI": $p='app';
                break;
                case "effG": $p='gar';
                break;
            }

            if (isset($this->c2rProdRes['col']['totale']['col'][$this->coll[$IDcoll]['cod_operaio']])) {

                $marcato=$this->c2rProdRes['col']['totale']['col'][$this->coll[$IDcoll]['cod_operaio']]['totale']['proprio']['totale']->getValore('marcato',$p,'valore');
                $marcato-=$this->c2rProdRes['col']['totale']['col'][$this->coll[$IDcoll]['cod_operaio']]['totale']['proprio']['totale']->getValore('aperto',$p,'valore');

                if ($parametro=='effI') {
                    $marcato+=$this->c2rProdRes['col']['totale']['col'][$this->coll[$IDcoll]['cod_operaio']]['totale']['proprio']['totale']->getValore('marcato','rip','valore');
                    $marcato-=$this->c2rProdRes['col']['totale']['col'][$this->coll[$IDcoll]['cod_operaio']]['totale']['proprio']['totale']->getValore('aperto','rip','valore');
                }
            }

            if ($marcato==0) return 1;

            $fatturato=$this->c2rProdRes['col']['totale']['col'][$this->coll[$IDcoll]['cod_operaio']]['totale']['proprio']['totale']->getValore('chiuso',$p,'valore');
            if ($parametro=='effI') {
                $fatturato+=$this->c2rProdRes['col']['totale']['col'][$this->coll[$IDcoll]['cod_operaio']]['totale']['proprio']['totale']->getValore('chiuso','rip','valore');
            }

            return $fatturato/$marcato;
        }

        else if ($parametro=='grUtil') {

            if (isset($this->c2rProdRes['col']['totale']['col'][$this->coll[$IDcoll]['cod_operaio']])) {
                $res=$this->c2rProdRes['col']['totale']['col'][$this->coll[$IDcoll]['cod_operaio']]['totale']['proprio']['totale']->getRes();
            }
            else return 1;

            if ($res['presenza']==0) return 1;
            else return $res['marcato']/$res['presenza'];
        }

        else if ($parametro=='preNom') {
            if (isset($this->c2rProdRes['col']['totale']['col'][$this->coll[$IDcoll]['cod_operaio']])) {
                return $this->c2rProdRes['col']['totale']['col'][$this->coll[$IDcoll]['cod_operaio']]['totale']['proprio']['totale']->getValore('presenza','nominale','valore');
            }
            else return 0;
        }

        else if ($parametro=='preExt') {
            if (isset($this->c2rProdRes['col']['totale']['col'][$this->coll[$IDcoll]['cod_operaio']])) {
                return $this->c2rProdRes['col']['totale']['col'][$this->coll[$IDcoll]['cod_operaio']]['totale']['proprio']['totale']->getValore('presenza','extra','valore');
            }
            else return 0;
        }

        else if ($parametro=='preAss') {
            if (isset($this->c2rProdRes['col']['totale']['col'][$this->coll[$IDcoll]['cod_operaio']])) {
                return $this->c2rProdRes['col']['totale']['col'][$this->coll[$IDcoll]['cod_operaio']]['totale']['proprio']['totale']->getValore('presenza','assenza','valore');
            }
            else return 0;
        }

        else if ($parametro=='preMal') {
            if (isset($this->c2rProdRes['col']['totale']['col'][$this->coll[$IDcoll]['cod_operaio']])) {
                return $this->c2rProdRes['col']['totale']['col'][$this->coll[$IDcoll]['cod_operaio']]['totale']['proprio']['totale']->getValore('presenza','malattia','valore');
            }
            else return 0;
        }

    }

    function getRcParam($IDcoll,$reparto,$parametro) {

        if (!isset($this->c2rProdRes['ext']['rc']['col'][$IDcoll])) return 0;

        if ($parametro=='prodP') {

            $grut=$this->getRepParam($reparto,'totale','grUtil');

            $fatturato=$this->c2rProdRes['ext']['rc']['col'][$IDcoll]['totale']->getValore('chiuso','pag','valore');
            $marcato=$this->c2rProdRes['ext']['rc']['col'][$IDcoll]['totale']->getValore('marcato','pag','valore');

            if ($marcato==0) $eff=0;
            else $eff=$fatturato/$marcato;

            return $grut*$eff*100;
        }

        else if ($parametro=='preNom') return $this->c2rProdRes['ext']['rc']['col'][$IDcoll]['totale']->getValore('presenza','nominale','valore');
        else if ($parametro=='preExt') return $this->c2rProdRes['ext']['rc']['col'][$IDcoll]['totale']->getValore('presenza','extra','valore');
        else if ($parametro=='preAss') return $this->c2rProdRes['ext']['rc']['col'][$IDcoll]['totale']->getValore('presenza','assenza','valore');
        else if ($parametro=='preMal') return $this->c2rProdRes['ext']['rc']['col'][$IDcoll]['totale']->getValore('presenza','malattia','valore');
        
    }

}

?>