<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/core/calendario/calendario.php');
require_once('timeline.php');
require_once('ricline.php');

class quartetIntervallo {

    //contesto:         REPARTO / MACROREPARTO / AZIENDA -> determina la query dei collaboratori (per calcolo presenza in agenda)
    //presenza          totali / dettaglio (timeline per ogni giorno)
    //badge - schemi - agenda - brogliaccio:    determinano il livello di analisi
    //actualRepato:     reparto che è stato individuato come principale per l'analisi (VWS || S || AZ)
    //intervallo:       mese - semestre - trimestre - .... libero
    protected $config=array(
        "contesto"=>"reparto",
        "presenza"=>"totali",
        "badge"=>false,
        "schemi"=>false,
        "agenda"=>false,
        "brogliaccio"=>false,
        "intervallo"=>"libero",
        "data_i"=>"",
        "data_f"=>"",
        "actualReparto"=>""
    );

    //i reparti sono quelli da prendere in esame (di base quelli dello stesso macroreparto per i subrep condivisi)
    protected $reparti=array();

    protected $anni=array();

    //collaboratori suddivisi per i reparti in esame nell'intervallo
    protected $collaboratori=array();
    //array degli eventi collegati ai collaboratori
    protected $collEventi=array();
    //schemi legati ai collaboratori nell'intervallo
    protected $collSk=array();
    //schemi di TUTTI i reparti in esame nell'intervallo
    protected $panSk=array();
    //tutte le griglie degli schemi in PANSK sotto forma di ARRAY
    protected $actualGrid=array();
    //sequenza dei panorami di tutti i reparti con la data di inizio e fine di ciascuno inerenti all'intervallo (conseguenza di panSk)
    protected $panGrid=array();
    //tutti i subrep dei reparti in esame nell'intervallo
    protected $panSubrep=array();
    protected $actualSubrep=array();
    //tutti i turni coinvolti negli schemi panSk
    protected $turniSk=array();

    //contiene la dinamica dei giorni nell'intervallo per ogni reparto
    //  OK:     OK
    //  FS:     Festivo
    //  CH:     Chiusura totale
    //  CHK:    Da verificare (chiusura parziale o con esclusione di reparti)
    protected $grigliaCal=array();

    //contiene la dinamica di ogni skema nell'intervallo:
    //"salto"           TRUE / FALSE
    //"step"            NUMERO DEI SALTI DALL'INIZIO (inizio_schema)
    //"turno"           Turno valido da quel giorno (ha senso solo per EXCLUSIVE)
    //"chk"             Caratteristiche del giorno come in grigliaCal con CHK corretto in OK o CH in base alla chiusura ed agli orari del turno
    protected $grigliaSk=array();

    //contiene il turno per ogni skema exclusive in ogni giorno dell'intervallo ( [skema][day]=turno )
    protected $exclusiveDayInt=array();

    //contiene tutte le timeline per ogni giorno per ogni collaboratore
    protected $grigliaTline=array();
    protected $grigliaRicRic=array();

    //contiene i totali per ogni giorno dell'intervallo per ogni reparto coinvolto in CALCOLA
    protected $grigliaDaySub=array();
    protected $grigliaDayRic=array();

    //contiene i totali per l'intero intervallo per ogni reparto coinvolto in CALCOLA
    protected $grigliaIntSub=array();
    protected $grigliaIntRic=array();
    protected $collTotSubs=array();
    protected $collTotRics=array();
    protected $grigliaDayTotSub=array();
    protected $grigliaDayTotRic=array();

    //[reparto][coll][giorno][skema]={"actual","standard","flag"}
    protected $grigliaCollDaySkema=array();

    //01.06.2021 INFOCOLL
    //elenco del reparto e degli skemi per ogni giorno per collaboratore
    //protected $infoColl=array();

    //contiene il conteggio dei limiti dell'area da visualizzare nell'AGENDA
    //i riferimenti sono in minuti come gli indici dei Timeline
    //il conteggio viene condizionato dalla SOLA timeline principale (Es: non da RICRIC)
    protected $globalTrim=array(
        "min"=>1440,
        "max"=>0
    );

    //ferie, permessi, extra per ogni collaboratore nell'intervallo
    //sost:         sostituzione di schema
    //sposta:       spostamenti di subrep
    //scambi;       sostituzione con schema di altro reparto nello stesso macroreparto
    protected $eventi=array(
        "periodi"=>array(),
        "permessi"=>array(),
        "extra"=>array(),
        "sostituzioni"=>array(),
        "sposta"=>array(),
        "scambi"=>array()
    );

    //sono gli array che viene passato alle TIMELINE per immagazzinare le informazioni
    protected $subs=array();
    protected $rics=array();

    //è l'array che viene calcolato per ogni collaboratore per attribuire il giusto turno ad ogni giorno
    //per le turnazioni NON exclusive
    protected $collSkTurni=array();

    protected $res=array(
        "subs"=>15,
        "ricric"=>15
    );

    //se TRUE indica che stiamo analizzando SOLO i subrep condivisi per l'agenda
    protected $flagOthers=false;

    protected $calendario;
    protected $galileo;

    protected $log=array();

    function __construct($config,$reparti,$galileo) {

        $this->reparti=$reparti;
        $this->galileo=$galileo;

        foreach ($this->config as $k=>$v) {
            if (array_key_exists($k,$config)) {
                $this->config[$k]=$config[$k];
            }
        }

        $this->build();
    }

    function getLog() {
        return $this->log;
    }

    function getIntRange() {
        return array($this->config['data_i'],$this->config['data_f']);
    }

    function getCollaboratori() {

        if ($this->config['actualReparto']!="") {
            $a=array();
            $a[$this->config['actualReparto']]=array();
            if (isset($this->collaboratori[$this->config['actualReparto']])) {
                $a[$this->config['actualReparto']]=$this->collaboratori[$this->config['actualReparto']];
            }
            return $a;
        }
        else return $this->collaboratori;
    }

    function getCollRep($reparto) {
        if (isset($this->collaboratori[$reparto])) return $this->collaboratori[$reparto];
        else return array();
    }

    function getCollTot($tipo,$reparto,$coll) {
        switch ($tipo) {
            case 'subs': 
                if (!isset($this->collTotSubs[$reparto][$coll]) ) return false;
                else return $this->collTotSubs[$reparto][$coll]; 
            break;
            case 'rics': 
                if (!isset($this->collTotRics[$reparto][$coll]) )return false;
                else return $this->collTotRics[$reparto][$coll]; 
            break;
        }
    }

    function getPresenzaCollDay($reparto,$coll,$tag) {
        //ritorna i totali di un collaboratore un dato giorno (nominale e actual)
        //oppure FALSE

        if (!isset($this->grigliaTline[$reparto][$coll][$tag])) return false;
        return $this->grigliaTline[$reparto][$coll][$tag]->getPresenza();

    }

    function getEventiCollDay($reparto,$coll,$tag) {
        //ritorna i totali di un collaboratore un dato giorno (nominale e actual)
        //oppure FALSE

        if (!isset($this->grigliaTline[$reparto][$coll][$tag])) return false;
        return $this->grigliaTline[$reparto][$coll][$tag]->getEventi();

    }

    function getTurnoCollDay($reparto,$coll,$tag) {
        //ritorna i totali di un collaboratore un dato giorno (nominale e actual)
        //oppure FALSE

        if (!isset($this->grigliaTline[$reparto][$coll][$tag])) return false;
        return $this->grigliaTline[$reparto][$coll][$tag]->getTurno();

    }

    function getPresenzaCollAll() {
        return $this->grigliaTline;
    }

    /*function getInfoColl() {
        return $this->infoColl;
    }*/


    function getCollEventi() {
        return $this->collEventi;
    }

    function getGrigliaDaySub() {
        //ritorna le TL dei totali per reparto per giorno
        return $this->grigliaDaySub;
    }

    function getDayTotSub($tag) {
        //ritorna la TL dei totali per il giorno specificato
        if (isset($this->grigliaDayTotSub[$tag])) {
            return $this->grigliaDayTotSub[$tag];
        }
        else return false;
    }

    function getDayTotRic($tag) {
        //ritorna la TL ritiro e riconsegna totali per il giorno specificato
        if (isset($this->grigliaDayTotRic[$tag])) {
            return $this->grigliaDayTotRic[$tag];
        }
        else return false;
    }

    function getGlobalTrim() {
        return $this->globalTrim;
    }

    function getTl() {
        return $this->grigliaTline;
    }

    function getExclusive($skema,$tag) {

        if (isset($this->exclusiveDayInt[$skema][$tag])) return $this->exclusiveDayInt[$skema][$tag];
        else return "";
    }

    function getGrigliaCollDaySkema($reparto) {
        if (isset($this->grigliaCollDaySkema[$reparto])) {
            return $this->grigliaCollDaySkema[$reparto];
        }
        else return array();
    }

    function getGrigliaCal() {
        //restituisce la griglia dei giorni TRIMMATA
        $arr=array();

        foreach ($this->grigliaCal as $reparto=>$r) {

            $arr[$reparto]=array();

            foreach ($r as $tag=>$t) {
                if ($tag<$this->config['data_i']) continue;
                if ($tag>$this->config['data_f']) break;

                $arr[$reparto][$tag]=$t;
            }
        }

        return $arr;
    }

    //////////////////////////////////////////////////////////////////
    function build() {

        if ($this->config['data_i']=="") $this->config['data_i']=date('Ymd');
        $anno=(int)substr($this->config['data_i'],0,4);
        $this->actualAnno=$anno;
        $this->calendario=new nebulaCalendario($anno,$this->galileo);

        if ($this->config['intervallo']!='libero') {
            $l=$this->calendario->getLimitiPeriodo($this->config['data_i'],$this->config['intervallo']);
            if (count($l)>0) {
                $this->config['data_i']=$l['inizio'];
                $this->config['data_f']=$l['fine'];
            }
            else $this->config['data_f']=$this->config['data_i'];
        }
        elseif ($this->config['data_f']=="" || $this->config['data_f']<$this->config['data_i']) {
            $this->config['data_f']=$this->config['data_i'];
        }

        ////////////////////////////////////////////////////////

        $intemp="";
        //$getclause="";

        foreach($this->reparti as $reparto=>$r) {
            $intemp.="'".$reparto."',";
        }
        $intemp=substr($intemp,0,-1);

        /*if ($this->config['contesto']=="reparto") {
            $getclause="'".$this->config['actualReparto']."'";
        }
        else {
            $getclause=$intemp;
        }*/

        //prende tutti i collaboratori nel periodo suddivisi per reparto
        $this->galileo->getCollaboratoriIntervallo($intemp,$this->config['data_i'],$this->config['data_f']);
        $result=$this->galileo->getResult();
		if($result) {
			$fetID=$this->galileo->preFetchBase('maestro');
			while ($row=$this->galileo->getFetchBase('maestro',$fetID)) {
                $row['flag_sostituzione']=false;
                $this->collaboratori[$row['reparto']][$row['ID_coll']][]=$row;
			}
		}

        //prende tutti gli schemi a cui sono collegati di default i collaboratori
        $this->galileo->getCollskIntervallo($intemp,$this->config['data_i'],$this->config['data_f']);
        $result=$this->galileo->getResult();
		if($result) {
			$fetID=$this->galileo->preFetchBase('schemi');
			while ($row=$this->galileo->getFetchBase('schemi',$fetID)) {
                $this->collSk[$row['reparto']][$row['collaboratore']][]=$row;
			}
		}

        //$this->log[]=$this->config['data_i'];
        //$this->log[]=$this->config['data_f'];


        $tempdata=$this->config['data_i'];
        $tempturni=array();
        //prende tutti gli schemi di tutti i reparti del macroreparto dei panorami attivi nel periodo
        $this->galileo->getPanskIntervallo($intemp,$this->config['data_i'],$this->config['data_f']);
        $result=$this->galileo->getResult();
		if($result) {
			$fetID=$this->galileo->preFetchBase('schemi');

            $trep="";
            $tpan="";

			while ($row=$this->galileo->getFetchBase('schemi',$fetID)) {
                $this->panSk[$row['reparto']][$row['ID']][$row['skema']]=$row;

                //PANGRID
                if ($trep!=$row['reparto']) {
                    $trep=$row['reparto'];
                    $tpan="";
                }

                if ($tpan!=$row['ID']) {

                    if ($tpan!="") {
                        $ta=(int)substr($row['inizio'],0,4);
                        $tm=(int)substr($row['inizio'],4,2);
                        $tm-=1;
                        if ($tm==0) {
                            $ta-=1;
                            $tm=12;
                        }
                        $this->panGrid[$tpan]['f']="".$ta.($tm<10?'0'.$tm:$tm);
                    }
                        

                    $this->panGrid[$row['ID']]=array(
                        "reparto"=>$trep,
                        "i"=>$row['inizio'],
                        "f"=>""
                    );
                    $tpan=$row['ID'];
                }

                //alimentazione di TURNISK
                try{
                    $g=json_decode($row['griglia'],true);
                }catch(Exception $e) {
                    $g=array();
                }

                $this->actualGrid[$row['skema']]=$g;

                foreach ($g as $turno=>$t) {
                    if ($turno=="0") continue;
                    if (!in_array($t['turno'],$tempturni)) $tempturni[]=$t['turno'];
                }

                //prepara i riferimenti per il calcolo degli ANNI
                if ($row['turnazione']==0) continue;
                $tempdata=($row['inizio_skema']<$tempdata)?$row['inizio_skema']:$tempdata;
                
			}
		}

        //#########################################################################################################
        //prende tutti gli eventi nel periodo
        //executeSelect($tipo,$tabella,$wclause,$order)
        $wclause="data_i<='".$this->config['data_f']."' AND data_f>='".$this->config['data_i']."'";
        $orderby="coll,data_i";
        $this->galileo->executeSelect("tempo","TEMPO_periodi",$wclause,$orderby);
        $result=$this->galileo->getResult();
		if($result) {
			$fetID=$this->galileo->preFetch('tempo');
			while ($row=$this->galileo->getFetch('tempo',$fetID)) {
                $this->eventi['periodi'][$row['coll']][]=$row;
			}
		}
        //clausole come sopra
        $wclause="data_i<='".$this->config['data_f']."' AND data_f>='".$this->config['data_i']."'";
        $orderby="collaboratore,data_i";
        $this->galileo->executeSelect("tempo","TEMPO_corsi",$wclause,$orderby);
        $result=$this->galileo->getResult();
		if($result) {
			$fetID=$this->galileo->preFetch('tempo');
			while ($row=$this->galileo->getFetch('tempo',$fetID)) {
                $tempry=array(
                    "ID"=>$row['ID_corso'],
                    "coll"=>$row['collaboratore'],
                    "tipo"=>'C',
                    "data_i"=>$row['data_i'],
                    "data_f"=>$row['data_f'],
                    "sigla"=>$row['sigla'],
                    "nota"=>$row['nota'],
                    "localita"=>$row['localita'],
                    "stato"=>$row['stato']
                );
                $this->eventi['periodi'][$row['collaboratore']][]=$tempry;
			}
		}

        $wclause="data>='".$this->config['data_i']."' AND data<='".$this->config['data_f']."'";
        $orderby="coll,data";
        $this->galileo->executeSelect("tempo","TEMPO_permessi",$wclause,$orderby);
        $result=$this->galileo->getResult();
		if($result) {
			$fetID=$this->galileo->preFetch('tempo');
			while ($row=$this->galileo->getFetch('tempo',$fetID)) {
                $this->eventi['permessi'][$row['coll']][]=$row;
			}
		}

        $wclause="data>='".$this->config['data_i']."' AND data<='".$this->config['data_f']."'";
        $orderby="coll,data";
        $this->galileo->executeSelect("tempo","TEMPO_extra",$wclause,$orderby);
        $result=$this->galileo->getResult();
		if($result) {
			$fetID=$this->galileo->preFetch('tempo');
			while ($row=$this->galileo->getFetch('tempo',$fetID)) {
                //if (is_null($row['panorama'])) $row['panorama']="";
                //if (is_null($row['skema'])) $row['skema']="";
                $this->eventi['extra'][$row['coll']][$row['data']][]=$row;
			}
		}

        $wclause="tag>='".$this->config['data_i']."' AND tag<='".$this->config['data_f']."'";
        $orderby="collaboratore,tag";
        $this->galileo->executeSelect("tempo","TEMPO_sostituzioni",$wclause,$orderby);
        $result=$this->galileo->getResult();
		if($result) {
			$fetID=$this->galileo->preFetch('tempo');
			while ($row=$this->galileo->getFetch('tempo',$fetID)) {
                //$this->eventi['sostituzioni'][$row['collaboratore']][$row['azione']][$row['tag']][$row['panorama']][$row['skema']][]=$row;
                $this->eventi['sostituzioni'][$row['collaboratore']][$row['azione']][$row['tag']][$row['panorama']][$row['skema']]=$row;
			}
		}

        //############################################
        //30.03.2021 leggere SCAMBI che ancora non esistono
        //############################################

        //###########################################
        //SOLO nel caso AGENDA sia TRUE
        if ($this->config['agenda']) {

            //prende tutti i subreps di tutti i reparti del macroreparto dei panorami attivi nel periodo
            //poi prende una sola volta i subreps duplicati sui vari panorami
            $this->galileo->getPanSubsIntervallo($intemp,$this->config['data_i'],$this->config['data_f']);
            $result=$this->galileo->getResult();
            if($result) {
                $fetID=$this->galileo->preFetchBase('schemi');
                while ($row=$this->galileo->getFetchBase('schemi',$fetID)) {
                    $this->panSubrep[$row['reparto']][$row['subrep']]=$row;
                }
            }

            //legge gli eventi SPOSTAMENTI tra subreps nell'intervallo
            $wclause="data>='".$this->config['data_i']."' AND data<='".$this->config['data_f']."'";
            $orderby="coll,data";
            $this->galileo->executeSelect("tempo","TEMPO_sposta",$wclause,$orderby);
            $result=$this->galileo->getResult();
            if($result) {
                $fetID=$this->galileo->preFetch('tempo');
                while ($row=$this->galileo->getFetch('tempo',$fetID)) {
                    if (is_null($row['sub_a'])) $row['sub_a']="";
                    $this->eventi['sposta'][$row['coll']][]=$row;
                }
            }
        }

        //###################################################################################################
  
        //alimenta l'array degli anni in base a data_i e data_f
        //$tempdata è stato valorizzato durante l'elaborazione di panSK
        $data_i=$tempdata;

        while ($anno<=(int)substr($this->config['data_f'],0,4)) {

            $this->anni[$anno]=array(
                "data_i"=>$data_i,
                "data_f"=>($this->config['data_f']<=$anno.'1231')?$this->config['data_f']:$anno.'1231'
            );
            $anno++;
            $data_i=$anno.'0101';
        }

        //alimenta l'array turniSk
        //$tempturni è stato valorizzato durante l'elaborazione di panSK
        $txt="";
        foreach ($tempturni as $t) {
            $txt.="'".$t."',";
        }
        $txt=substr($txt,0,-1);

        $this->galileo->getOrario($txt,"");
        $result=$this->galileo->getResult();
		if($result) {
			$fetID=$this->galileo->preFetchBase('schemi');
			while ($row=$this->galileo->getFetchBase('schemi',$fetID)) {
                $this->turniSk[$row['codice']][$row['wd']]=array(
                    "codice"=>$row['codice'],
                    "wd"=>$row['wd'],
                    "orari"=>json_decode($row['orari'],true)
                );
			}
		}

        //$this->log[]=$this->galileo->getLog('query');
        //$this->log[]=$this->collaboratori;
        //$this->log[]=$this->collSk;
        //$this->log[]=$this->panSubrep;
        //$this->log[]=$this->eventi;

    }

    function calcola() {

        //array presenza per ogni giorno per ogni collaboratore
        $this->subs=array(
            "nominale"=>array("flag"=>false,"qta"=>0),
            "actual"=>array("flag"=>false,"qta"=>0),
            "actualBro"=>array("flag"=>false,"qta"=>0),
            "eventi"=>array(),
            "schemi"=>array()
        );

        $this->rics=array(
            "ricric"=>array("flag"=>false,"qta"=>0),
            "schemi"=>array()
        );        

        ///////////////////////////////////////////////////////////////////

        if ($this->config['actualReparto']!="") {
            
            if (isset($this->panSubrep[$this->config['actualReparto']])) {
                $this->actualSubrep=$this->panSubrep[$this->config['actualReparto']];
            }
            else $this->actualSubrep=array();

            $this->subs['agenda']=array();

            foreach ($this->actualSubrep as $ksr=>$sr) {
                $this->subs['agenda'][$ksr]=array("flag"=>false,"qta"=>0);
            }

            $this->calcolaReparto($this->config['actualReparto']);

            if ($this->config['agenda']) {
                //schemi subrep condivisi se agenda

                //segnialiamo che stiamo analizzando SOLO i subrep condivisi
                $this->flagOthers=true;

                foreach ($this->reparti as $reparto=>$r) {
                    if ($reparto==$this->config['actualReparto']) continue;
                    $this->calcolaReparto($reparto);
                }
               
            }

        }

        else {
        
            foreach ($this->panSubrep as $reparto=>$r) { 
                foreach ($r as $subrep=>$s) {
                    $this->actualSubrep[$subrep]=$s;
                }
            }

            $this->subs['agenda']=array();

            foreach ($this->actualSubrep as $ksr=>$sr) {
                $this->subs['agenda'][$ksr]=array("flag"=>false,"qta"=>0);
            }

            //#############################
            //multireparto
            foreach ($this->reparti as $reparto=>$r) {
                //if ($reparto==$this->config['actualReparto']) continue;
                $this->calcolaReparto($reparto);
            }
            //#############################
        }

        //$this->log[]=$this->grigliaSk;
        if (!$this->config['brogliaccio']) {
            //grigliacal serve per più applicazioni
            //unset($this->grigliaCal);
        }

        unset($this->collSkTurni);
        unset($this->grigliaSk);
        unset($this->panGrid);
        unset($this->collSk);
        unset($this->panSk);
    }

    function calcolaReparto($repartoActual) {

        $actualAnno="";
        //[{"2021":{"data_i":"20210301","data_f":"20210331"}}]
        //############################################
        //31.03.2021 NON BASARSI SUGLI ANNI MA SULLE DATE DI INIZIO SKEMI DEGLI SKEMI CON TURNAZIONE
        //data finale sempre la fine dell'intervallo
        //data iniziale la data dell'intervallo solo se minore di quella calcolata
        //il calcolo dei riferimenti va fatto valutando tutti i reparti che ha senso valutare
        //############################################

        foreach ($this->anni as $anno=>$a) {

            if ($anno!=$actualAnno) {
                $actualAnno=$anno;
                $this->calendario=new nebulaCalendario($actualAnno,$this->galileo);
            }

            foreach ($this->reparti as $reparto=>$r) {

                if (!array_key_exists($reparto,$this->grigliaCal)) $this->grigliaCal[$reparto]=array();
                $this->calendario->setReparto($reparto);
                $this->setGrigliaCal($reparto,$a);
            }

        }

        //#####################
        // a questo punto ho la mappa (FS,CH,OK) di tutti i giorni dei reparti
        // dalla data di calcolo dello schema più vecchio alla data di fine intervallo
        //#####################

        if (array_key_exists($repartoActual,$this->panSk)) {
            
            foreach ($this->panSk[$repartoActual] as $panorama=>$p) {

                foreach ($p as $skema=>$s) {
                    if ( !array_key_exists($s['skema'],$this->grigliaSk) ) $this->setGrigliaSk($repartoActual,$s);
                }
            }
        }

        ////////////////////////////////////////////////////

        if (array_key_exists($repartoActual,$this->collSk)) {

            foreach ($this->collSk[$repartoActual] as $coll=>$c) {

                if ( !array_key_exists($coll,$this->grigliaTline) ) $this->grigliaTline[$repartoActual][$coll]=array();

                //conterrà gli eventuali schemi AGG validi
                //$tempAGG=array();

                //gestione delle sostituzioni AGG
                if ( isset($this->eventi['sostituzioni'][$coll]['AGG'] ) ) {

                    //#############################
                    //elaborare lo schema generando il record COLLSK($c) dalla sostituzione e da PANSK
                    /*  SOSTITUZIONE
                        "collaboratore": 169,
                        "panorama": 43,
                        "tag": "20210116",
                        "skema": "SB_PVTE2_11",
                        "turno": "11",
                        "azione": "AGG",
                        "suff": "11"
                    */
                    foreach ($this->eventi['sostituzioni'][$coll]['AGG'] as $sostag=>$s1) {
                        foreach ($s1 as $sospan=>$s2) {
                            foreach ($s2 as $soske=>$s3) {
                                //se lo skema da aggiungere esiste nel panorama del reparto in esame
                                //if ($coll=='169') $this->log[]=array($sospan,$soske);
                                if ( isset($this->panSk[$repartoActual][$sospan][$soske]) ) {

                                    //foreach ($s3 as $s4) {

                                        $c[]=array(
                                            "panorama"=> $sospan,
                                            "collaboratore"=> $coll,
                                            "skema"=>$soske,
                                            "turno"=>$s3['turno'],
                                            "data_i"=>$sostag,
                                            "data_f"=>$sostag,
                                            "codice"=>$soske,
                                            "reparto"=>$repartoActual,
                                            "titolo"=>$this->panSk[$repartoActual][$sospan][$soske]['titolo'],
                                            "turnazione"=>$this->panSk[$repartoActual][$sospan][$soske]['turnazione'],
                                            "flag_festivi"=>$this->panSk[$repartoActual][$sospan][$soske]['flag_festivi'],
                                            "flag_turno"=>$this->panSk[$repartoActual][$sospan][$soske]['flag_turno'],
                                            "on_flag"=>$this->panSk[$repartoActual][$sospan][$soske]['on_flag'],
                                            "mark"=>$this->panSk[$repartoActual][$sospan][$soske]['mark'],
                                            "exclusive"=>$this->panSk[$repartoActual][$sospan][$soske]['exclusive'],
                                            "overall"=>$this->panSk[$repartoActual][$sospan][$soske]['overall'],
                                            "griglia"=>$this->panSk[$repartoActual][$sospan][$soske]['griglia'],
                                            "data_rif"=>$this->panSk[$repartoActual][$sospan][$soske]['inizio_skema'],
                                            "turno_rif"=>$this->panSk[$repartoActual][$sospan][$soske]['blocco_inizio'],
                                            "flag_agg"=>true,
                                        );
                                    //}
                                }
                            }
                        }
                    }

                    //#############################

                    //aggiornare flag_sostituzione nell'array collaboratori
                    foreach ($this->collaboratori[$repartoActual][$coll] as $ktc=>$tc) {
                        $this->collaboratori[$repartoActual][$coll][$ktc]['flag_sostituzione']=true;
                    }

                }

                //if ($coll=='169') $this->log[]=$c;

                foreach ($c as $csk) {

                    /*$this->log[]=array(
                        "coll"=>$coll,
                        "schema"=>$csk['skema'],
                        "data_i"=>$csk['data_i'],
                        "turno"=>$csk['turno'],
                        "turnazione"=>$csk['turnazione'],
                        "exclusive"=>$csk['exclusive']
                    );*/

                    //se lo schema non è stato mai analizzato => genera grigliaSk 
                    //(non è stato fatto prima perché mancava panGrid e grigliaCal)
                    //SPOSTATO PRIMA DEL CICLO perché qui considera SOLO gli scchemi dove c'è qualche collaboratore assegnato
                    //if ( !array_key_exists($csk['skema'],$this->grigliaSk) ) $this->setGrigliaSk($repartoActual,$csk);

                    //###########################################
                    //se la turnazione >0 e NON è exclusive
                    //genera per ogni giorno utile la griglia dei turni per lo schema per una determinata data di inizio ed un turno di inizio
                    if ($csk['turnazione']>0 && $csk['exclusive']==0) {

                        if ( !isset($this->collSkTurni[$csk['skema']][$csk['data_i']][$csk['turno']]) ) {
                            $actur=$csk['turno'];
                            $acstep=0;
                            //$tempstart=true;
                            if (isset($this->grigliaSk[$csk['skema']])) {
                                foreach ($this->grigliaSk[$csk['skema']] as $tag=>$t) {
                                    if ($tag<$csk['data_i']) continue;
                                    //se c'è un salto calcola il nuovo turno
                                    if ($t['step']>$acstep) {

                                        while ($acstep<$t['step']) {
                                            $actur=$this->actualGrid[$csk['skema']][$actur]['next'];
                                            $acstep++;
                                        }
                                    }

                                    /*if ($t['salto']) {
                                        if (!$tempstart) {
                                            $actur=$this->actualGrid[$csk['skema']][$actur]['next'];
                                        }
                                    }*/

                                    $this->collSkTurni[$csk['skema']][$csk['data_i']][$csk['turno']][$tag]=$actur;

                                    //$tempstart=false;
                                }
                            }
                        }
                    }
                    //###########################################

                    //#########################################
                    // SE PRESENZA = TOTALI -> ANALIZZARE BLOCCHI MENSILI E SOMMARLI VIA VIA
                    // ALTRIMENTI LE TIMELINE SAREBBERO TROPPO ONEROSE ???????????????????
                    //#########################################

                    if (isset($this->grigliaSk[$csk['skema']])) {
                    
                        foreach ($this->grigliaSk[$csk['skema']] as $tag=>$t) {
                            //vengono scorsi tutti i giorni condizionati dallo skema nell'intevallo in analisi

                            /*if ( !isset($this->grigliaTline[$reparto][$coll][$tag]) ) {
                                $this->grigliaTline[$reparto][$coll][$tag]=new panoramaTimeline($this->res['subs'],$this->subs);
                            }*/

                            //verifica festivo o chiusura
                            if ($t['chk']!="OK") continue;

                            //##############################################
                            //verificare SCAMBI
                            //nel caso il collaboratore sia spostato in un altro reparto per questo giorno SALTA
                            //lo scambio viene accantionato per l'altro reparto
                            //##############################################
                            
                            //verifica limiti data di appatenenza allo schema per il collaboratore
                            if ( $tag>$csk['data_f'] ) break;
                            if ( $tag<$csk['data_i'] ) continue;

                            $temp=array(
                                "reparto"=>$repartoActual,
                                "coll"=>$coll,
                                "tag"=>$tag,
                                "skema"=>$csk['skema'],
                                "csk"=>$csk,
                                "gsk"=>$t,
                                "agg"=>false,
                                "blocco"=>""
                            );

                            //###################################################################
                            if ($temp['csk']['turnazione']==0) {
                                $temp['blocco']=$temp['gsk']['turno'];
                            }
                            else if ($temp['csk']['exclusive']==1) {
                                //se il turno del collaboratore NON corrisponde al turno dello skema RETURN
                                if ($temp['csk']['turno']!=$temp['gsk']['turno']) $temp['blocco']="";
                                else $temp['blocco']=$temp['gsk']['turno'];
                            }
                            //turnazione normale
                            else {
                                if (isset($temp['csk']['flag_agg'])) {
                                    $temp['blocco']=$temp['csk']['turno'];
                                }
                                else {
                                    $temp['blocco']=$this->collSkTurni[$temp['skema']][$temp['csk']['data_i']][$temp['csk']['turno']][$temp['tag']];
                                }
                            }

                            //###################################################################
                            if (!isset($this->grigliaCollDaySkema[$repartoActual][$coll][$tag][$csk['skema']])) {
                                $this->grigliaCollDaySkema[$repartoActual][$coll][$tag][$csk['skema']]=array(
                                    "panorama"=>$csk['panorama'],
                                    "actual"=>(isset($csk['flag_agg']))?$csk['turno']:$temp['blocco'],
                                    "standard"=>(isset($csk['flag_agg']))?$csk['turno']:$temp['blocco'],
                                    "flag"=>(isset($csk['flag_agg']))?'AGG':'STD'
                                );
                            }
                            else {
                                $this->grigliaCollDaySkema[$repartoActual][$coll][$tag][$csk['skema']]['actual']=$csk['turno'];
                                $this->grigliaCollDaySkema[$repartoActual][$coll][$tag][$csk['skema']]['flag']=(isset($csk['flag_agg']))?'AGG':'STD';
                            }
                            //###################################################################

                            //applicare SOSTITUZIONI (CNC)
                            //$this->eventi['sostituzioni'][$row['collaboratore']][$row['azione']][$row['tag']][$row['panorama']][$row['skema']][]
                            if ( isset($this->eventi['sostituzioni'][$coll]['CNC'][$tag][$csk['panorama']][$csk['skema']]) && !isset($csk['flag_agg']) ) {

                                //se ad essere cancellato è lo stesso turno che stiamo esaminando:
                                if ($this->eventi['sostituzioni'][$coll]['CNC'][$tag][$csk['panorama']][$csk['skema']]['turno']==$temp['blocco']) {

                                    foreach ($this->collaboratori[$repartoActual][$coll] as $ktc=>$tc) {
                                        $this->collaboratori[$repartoActual][$coll][$ktc]['flag_sostituzione']=true;
                                    }

                                    if($this->grigliaCollDaySkema[$repartoActual][$coll][$tag][$csk['skema']]['flag']!='AGG') {
                                        $this->grigliaCollDaySkema[$repartoActual][$coll][$tag][$csk['skema']]['flag']='CNC';
                                    }

                                    //se il turno è MARK==1
                                    if ($temp['csk']['mark']==1) {
                                        $this->collEventi[$temp['reparto']][$temp['coll']]['mark'][$temp['tag']][$temp['skema']]=array(
                                            "blocco"=>$temp['blocco'],
                                            "flag_agg"=>false,
                                            "flag_cnc"=>true
                                        ); 
                                    }

                                    continue;
                                }
                            }

                            /*if ( isset($this->eventi['sostituzioni'][$coll]['AGG'][$tag]) ) {
                                foreach ($this->eventi['sostituzioni'][$coll]['AGG'][$tag] as $kp=>$argg) {
                                    if ($kp!=$csk['panorama']) continue;
                                    foreach ($argg as $kg=>$sg) {
                                        $tempAGG[]=$sg;
                                    }
                                }
                            }*/

                            /*if ($coll=='106' && $tag>='20210830') {
                                $this->log[]=$temp;
                            }*/

                            $this->setGrigliaTline($temp);
                        }
                    }
                }

                //#####################################
                //gestione scambi in ENTRATA
                //#####################################

                $this->calcolaEventi($repartoActual,$coll);

            }
        
        }

        //$this->log[]=$this->galileo->getLog('query');

        /*foreach ($this->grigliaTline as $reparto=>$r) {
            foreach ($r as $coll=>$c) {
                if ($coll!=9) continue;
                foreach ($c as $tag=>$t) {
                    $this->log[]=array(
                        "reparto"=>$reparto,
                        "coll"=>$coll,
                        "tag"=>$tag,
                        "tl"=>$t->getTl()
                    );
                }
            }
        }*/

        //$this->log[]=$this->grigliaTline['VWS']["29"]["20210225"]->getTl();
        //$this->log[]=$this->grigliaSk['ST_PVRT4'];
    }

    function calcolaEventi($reparto,$coll) {

        /*
        "periodi"=>array(),
        "permessi"=>array(),
        "extra"=>array(),
        "sostituzioni"=>array(),
        "sposta"=>array(),
        "scambi"=>array()
        */

        //$this->eventi['periodi'][$row['coll']][]=$row;
        if (isset($this->eventi['periodi'][$coll])) {

            foreach ($this->eventi['periodi'][$coll] as $c) {

                $rif=mainFunc::gab_tots($c['data_i']);
                $end=mainFunc::gab_tots($c['data_f']);

                //abbina l'evento al record collaboratore che servirà per la GRIGLIA eventi
                $this->collEventi[$reparto][$coll]['periodi'][]=$c;

                while ($rif<=$end) {
                    
                    $tag=date('Ymd',$rif);

                    if (isset($this->grigliaTline[$reparto][$coll][$tag])) {
                        $this->grigliaTline[$reparto][$coll][$tag]->effectPeriodo($c['tipo']);
                    }

                    $rif=strtotime('+1 day',$rif);
                }

            }
        }

        //$this->eventi['permessi'][$row['coll']][]=$row;
        if (isset($this->eventi['permessi'][$coll])) {

            foreach ($this->eventi['permessi'][$coll] as $c) {

                if ( !isset($this->grigliaTline[$reparto][$coll][$c['data']]) ) continue;

                $tres=$this->grigliaTline[$reparto][$coll][$c['data']]->effectPermesso($c['ora_i'],$c['ora_f'],$c['tipo']);
                //se l'evento è stato inserito nella TIMELINE
                //viene scartato se NON c'è almeno un blocco ACTUAL TRUE
                if ($tres) {
                    //abbina l'evento al record collaboratore che servirà per la GRIGLIA eventi
                    $this->collEventi[$reparto][$coll]['permessi'][]=$c;
                }
            }

        }

        //$this->eventi['extra'][$row['coll']][$row['data']][]=$row;
        if (isset($this->eventi['extra'][$coll])) {

            foreach ($this->eventi['extra'][$coll] as $d=>$e) {

                if ( !isset($this->grigliaTline[$reparto][$coll][$d]) ) {
                    $this->grigliaTline[$reparto][$coll][$d]=new panoramaTimeline($this->res['subs'],$this->subs);
                }

                foreach ($e as $c) {

                    $tres=$this->grigliaTline[$reparto][$coll][$d]->effectExtra($c['ora_i'],$c['ora_f'],$c['tipo']);

                    //$this->log[]=json_encode($c);
                    
                    if ($tres) {
                        //abbina l'evento al record collaboratore che servirà per la GRIGLIA eventi
                        $this->collEventi[$reparto][$coll]['extra'][]=$c;
                    }
                }
            }
        }

        if (isset($this->eventi['sposta'][$coll])) {

            /*
            "sposta": {
                "9": [
                    {
                    "ID": 350,
                    "coll": 9,
                    "data": "20210315",
                    "ora_i": "15:00",
                    "ora_f": "16:30",
                    "panorama": 62,
                    "sub_da": "DIAPV",
                    "sub_a": "GOMPU"
                    }
                ],
            */

            foreach ($this->eventi['sposta'][$coll] as $c) {

                if ( !isset($this->grigliaTline[$reparto][$coll][$c['data']]) ) continue;

                //$tres=$this->grigliaTline[$reparto][$coll][$c['data']]->effectSposta($c['ora_i'],$c['ora_f'],$c['sub_da'],$c['sub_a']);
                $tres=$this->grigliaTline[$reparto][$coll][$c['data']]->effectSposta($c['ora_i'],$c['ora_f'],$c['sub_a']);
    
                if ($tres) {
                    //abbina l'evento al record collaboratore che servirà per la GRIGLIA eventi
                    $this->collEventi[$reparto][$coll]['sposta'][]=$c;
                }
            }
        }

        //$this->log[]=$this->eventi;
    }

    function setGrigliaTline($a) {

        /*
        $a=array(
            "reparto"=>reparto,
            "coll"=>collaboratore,
            "tag"=>giorno,
            "skema"=>schema,
            "csk"=>array dello schema a cui appartiene il collaboratore,
            "gsk"=>array di grigliaSk dello skema,
            "agg"=>false
            "blocco"=>blocco calcolato
        );
        */

        $orario=array();
        $blocco=$a['blocco'];
        $infoSub=array(
            'subrep'=>"",
            'skema'=>"",
            'blocco'=>""
        );
        $infoRic=array(
            'ricric'=>0,
            'skema'=>"",
            'blocco'=>""
        );

        /*spostato in CALCOLA - riportato qui
        if ( !isset($this->grigliaTline[$a['reparto']][$a['coll']][$a['tag']]) ) {
            $this->grigliaTline[$a['reparto']][$a['coll']][$a['tag']]=new panoramaTimeline($this->res['subs'],$this->subs);
        }
        */

        /*
        if ($a['csk']['turnazione']==0) {
            $blocco=$a['gsk']['turno'];
        }
        else if ($a['csk']['exclusive']==1) {
            //se il turno del collaboratore NON corrisponde al turno dello skema RETURN
            if ($a['csk']['turno']!=$a['gsk']['turno']) return;
            $blocco=$a['gsk']['turno'];
        }
        //turnazione normale
        else {
            $blocco=$this->collSkTurni[$a['skema']][$a['csk']['data_i']][$a['csk']['turno']][$a['tag']];
        }
        */

        if (!$blocco || $blocco=='') return;

        ///////////////////////////////////////////////////////////////
        $orario=$this->actualGrid[$a['skema']][$blocco]['turno'];
        $arr=$this->turniSk[$orario][$a['gsk']['wd']]['orari'];

        if ( array_key_exists('agenda',$this->actualGrid[$a['skema']][$blocco]) ) {

            foreach ($this->actualGrid[$a['skema']][$blocco]['agenda'] as $sub=>$s) {
                //ne può esisere SOLO uno con impegno al 100%
                $infoSub['subrep']=$sub;
                $infoSub['skema']=$a['skema'];
                $infoSub['blocco']=$blocco;
            }

            /*
            //################
            //01.06.2021 definizione di un altro array
            $this->infoColl[$a['coll']][$a['tag']][]=array(
                "reparto"=>$a['reparto'],
                "subrep"=>$infoSub['subrep'],
                "skema"=>$infoSub['skema'],
                "blocco"=>$infoSub['blocco']
            );
            //################
            */

        }
        //se non c'è agenda e stiamo verificando i subrep condivisi abbandona
        elseif ($this->flagOthers) return;

        ////////////////////////////////////////////////////////////////
        //se stiamo verificando i subrep condivisi
        //i SUBREP definiti fuori dall' array agenda del blocco dello skema come RICRIC non sono mai condivisi
        //ed il SUBREP del blocco non fa parte di $this->actualSubrep abandona
        if ($this->flagOthers && !array_key_exists($infoSub['subrep'],$this->actualSubrep ) ) return;
        ///////////////////////////////////////////

        if ( !isset($this->grigliaTline[$a['reparto']][$a['coll']][$a['tag']]) ) {
            $this->grigliaTline[$a['reparto']][$a['coll']][$a['tag']]=new panoramaTimeline($this->res['subs'],$this->subs);
        }
        
        //i subrep definiti fuori dall'array agenda del blocco dello skema come RICRIC hanno senso solo nel caso del calcolo dell'AGENDA
        if ($this->config['agenda']) {
            if ( array_key_exists('ricric',$this->actualGrid[$a['skema']][$blocco]) ) {
                $infoRic['ricric']=$this->actualGrid[$a['skema']][$blocco]['ricric'];
                $infoRic['skema']=$a['skema'];
                $infoRic['blocco']=$blocco;

                if ( !isset($this->grigliaRicRic[$a['reparto']][$a['coll']][$a['tag']]) ) {
                    $this->grigliaRicRic[$a['reparto']][$a['coll']][$a['tag']]=new panoramaRicline( $this->res['ricric'],$this->rics);
                }
            }
        }

        foreach ($arr as $o) {
            if ($o['i']=='00:00' && $o['f']=='00:00') continue;

            ////////////////////////////////////////////
            //se il turno è MARK==1 segnalare l'evento nell'array collaboragtore per gestire in seguito la GRIGLIA EVENTI
            if ($a['csk']['mark']==1) {
                $this->collEventi[$a['reparto']][$a['coll']]['mark'][$a['tag']][$a['skema']]=array(
                    "blocco"=>$blocco,
                    "flag_agg"=>(isset($a['csk']['flag_agg']))?true:false
                ); 
            }

            $rewind=array();

            //se esiste un record EXTRA
            //se nel record è specificato Panorama e Skema che coincidono con quello che si sta gestendo (TOLTO CONTROLLO 30.06.2021)
            //se l'orario extra interseca (coincide con inizio o fine) con l'attuale orario
            //modifica l'orario di conseguenza
            //IN OGNI CASO L'EVENTO VERRA' SEMPRE GESTITO in calcola eventi
            if ( isset($this->eventi['extra'][$a['coll']][$a['tag']]) ) {

                foreach ($this->eventi['extra'][$a['coll']][$a['tag']] as $kex=>$e) {

                    //if ($a['csk']['panorama']==$e['panorama'] && $a['csk']['skema']==$e['skema']) {

                        if ($e['ora_i']<=$o['f'] && $e['ora_f']>=$o['i']) {

                            if ($e['ora_i']<$o['i']) {
                                $rewind[]=array("da"=>$e['ora_i'],"a"=>$o['i']);
                                $o['i']=$e['ora_i'];
                                
                            }
                            if ($e['ora_f']>$o['f']) {
                                $rewind[]=array("da"=>$o['f'],"a"=>$e['ora_f']);
                                $o['f']=$e['ora_f'];
                            }
                        }
                    //}
                }

            }
            /////////////////////////////////////////////////////////

            $this->grigliaTline[$a['reparto']][$a['coll']][$a['tag']]->includi($o['i'],$o['f'],$infoSub);

            if (count($rewind)>0) {
                //riporta "nominale" a false se l'evento extra aveva matchato lo skema (TOLTO CONTROLLO 30.06.2021)
                foreach ($rewind as $r) {
                    $this->grigliaTline[$a['reparto']][$a['coll']][$a['tag']]->escludi($r['da'],$r['a'],array());
                }
            }

            //i subrep definiti fuori dall'array agenda del blocco dello skema come RICRIC hanno senso solo nel caso del calcolo dell'AGENDA
            if ($this->config['agenda']) {
                if( $infoRic['ricric']>0 ) {
                    $this->grigliaRicRic[$a['reparto']][$a['coll']][$a['tag']]->preInclude($infoRic['ricric']);
                    $this->grigliaRicRic[$a['reparto']][$a['coll']][$a['tag']]->includi($o['i'],$o['f'],$infoRic);
                }
            }

        }

        /*$this->log[]=array(
            "reparto"=>$this->config['actualReparto'],
            "coll"=>$coll,
            "tag"=>$tag,
            "orario"=>$arr
        );*/
    }

    function setGrigliaCal($reparto,$a) {

        //{"data_i":"20210301","data_f":"20210331"}
        $rif=mainFunc::gab_tots($a['data_i']);
        $end=mainFunc::gab_tots($a['data_f']);

        while ($rif<=$end) {
            $tag=date('Ymd',$rif);
            $this->grigliaCal[$reparto][$tag]=$this->calendario->checkDay($tag,$reparto);
            $rif=strtotime("+1 day",$rif);
        }
    }

    function setGrigliaSk($reparto,$csk) {
        //$sk sono i dati contenuti in "collSk"
        //scrive la dinamica dello schema all'interno dell'intervallo basandosi sui dati di "panSK"
        //occorre avere la definizione dello schema nel panorama che si sta attraversando all'interno dell'intervallo

        $this->grigliaSk[$csk['skema']]=array();

        $tempanno="";

        foreach ($this->panSk[$reparto] as $panorama=>$p) {

            foreach ($p as $skema=>$s) {
                //TEST
                //if ($skema!='SB_PVRTC') continue;
                //END TEST
                if ($skema!=$csk['skema']) continue;

                //$this->log[]=array($reparto,$panorama,$skema);

                $rif=array(
                    "int_i"=>$this->config['data_i'],
                    "int_f"=>$this->config['data_f'],
                    "pan_i"=>$this->panGrid[$panorama]['i'].'01',
                    "pan_f"=>"",
                    "inizioSkema"=>$s['inizio_skema'],
                    "bloccoInizio"=>$s['blocco_inizio'],
                    "actualBlocco"=>"",
                    "griglia"=>json_decode($s['griglia'],true),
                    "actualLimit"=>mainFunc::gab_tots($this->config['data_f']),
                    "step"=>0
                );

                if ($this->panGrid[$panorama]['f']=="") $rif['pan_f']='21001231';
                else $rif['pan_f']=date('Ymt',mainFunc::gab_tots($this->panGrid[$panorama]['f'].'01'));

                if ($s['turnazione']==0) $rif['inizioSkema']=$rif['int_i']; 

                $cursor=mainFunc::gab_tots($rif['inizioSkema']);

                $rif_end=($rif['int_f']<=$rif['pan_f'])?$rif['int_f']:$rif['pan_f'];

                $end=mainFunc::gab_tots($rif_end);

                if ($s['turnazione']!=0) {
                    $rif['actualLimit']=strtotime("+".$s['turnazione']." day",$cursor);
                    if ($s['exclusive']==1) $rif['actualBlocco']=$rif['bloccoInizio'];
                }

                ////////////////////////////////////////////////////////

                while ($cursor<=$end) {

                    $tag=date('Ymd',$cursor);
                    $wd=date('w',$cursor);
                    $temp=array();

                    //#################################

                    $tchk=$this->grigliaCal[$reparto][$tag]['chk'];

                    if ($tchk=='CHK') {

                        if ($s['exclusive']==1) {
                            $ta=substr($tag,0,4);
                            if ($ta!=$tempanno) {
                                $cal=new nebulaCalendario($ta,$this->galileo);
                                $cal->setReparto($reparto);
                                $tempanno=$ta;
                            }

                            $tchk=$cal->verificaLimitiChiusura($tag,$rif['griglia'][$rif['actualBlocco']]['turno']);
                        }
                        else $tchk='OK';
                    }

                    ///////////////////////////////

                    if ($s['turnazione']==0) {
                        $temp=array(
                            "salto"=>false,
                            "step"=>$rif['step'],
                            "turno"=>"11",
                            "chk"=>$tchk,
                            "wd"=>$wd
                        );
                    }
                    else {

                        //se non siamo arrivati al punto di jump
                        if ($cursor<$rif['actualLimit']) {
                            $temp=array(
                                "salto"=>false,
                                "step"=>$rif['step'],
                                "turno"=>($s['exclusive']==1?$rif['actualBlocco']:""),
                                "chk"=>$tchk,
                                "wd"=>$wd
                            );
                        }
                        //se siamo al punto di jump
                        else {

                            $tj=true;

                            if ($tchk=='FS') {
                                if ($s['flag_festivi']==0) {
                                    $rif['actualLimit']=strtotime('+'.$s['turnazione'].' day',$rif['actualLimit']);
                                }
                                else {
                                    $tj=false;
                                    $rif['actualLimit']=strtotime('+'.$s['on_flag'].' day',$rif['actualLimit']);
                                }
                            }

                            if ($tchk=='CH') {
                                if ($s['flag_turno']==0) {
                                    $rif['actualLimit']=strtotime('+'.$s['turnazione'].' day',$rif['actualLimit']);
                                }
                                else {
                                    $tj=false;
                                    $rif['actualLimit']=strtotime('+'.$s['on_flag'].' day',$rif['actualLimit']);
                                }
                            }

                            if ($tj) {
                                if($s['exclusive']==1) $rif['actualBlocco']=$rif['griglia'][$rif['actualBlocco']]['next'];
                                $rif['actualLimit']=strtotime("+".$s['turnazione']." day",$cursor);
                                $rif['step']++;
                            } 

                            $temp=array(
                                "salto"=>$tj,
                                "step"=>$rif['step'],
                                "turno"=>$rif['actualBlocco'],
                                "chk"=>$tchk,
                                "wd"=>$wd
                            );
                        }
                    }

                    //TEST
                    $temp['limit']=date('Ymd',$rif['actualLimit']);
                    //TEST

                    //scrivi in "grigliaSk" se siamo nell'intervallo
                    if ($tag>=$rif['int_i']) {
                        $this->grigliaSk[$csk['skema']][$tag]=$temp;
                        if ($s['exclusive']==1) $this->exclusiveDayInt[$csk['skema']][$tag]=$temp['turno'];
                    }
                    //TEST
                    //$this->grigliaSk[$csk['skema']][$tag]=$temp;
                    //END TEST

                    $cursor=strtotime("+1 day",$cursor);

                }

            }

        }
    
    }

    function calcolaDayTot() {
        //calcola i totali per reparto per giorno

        foreach ($this->grigliaTline as $reparto=>$r) {

            foreach ($r as $coll=>$c) {

                foreach ($c as $tag=>$t) {
                    
                    if ( !isset($this->grigliaDaySub[$reparto][$tag]) ) {
                        $this->grigliaDaySub[$reparto][$tag]=new panoramaTimeline($this->res['subs'],$this->subs);
                    }

                    $this->grigliaDaySub[$reparto][$tag]->addTl($t->getTl());

                    if ( !isset($this->collTotSubs[$reparto][$coll]) ) {
                        $this->collTotSubs[$reparto][$coll]=new panoramaTimeline($this->res['subs'],$this->subs);
                    }

                    $this->collTotSubs[$reparto][$coll]->addTl($t->getTl());
                }
            }
        }

        foreach ($this->grigliaRicRic as $reparto=>$r) {

            foreach ($r as $coll=>$c) {

                foreach ($c as $tag=>$t) {

                    if ( !isset($this->grigliaDayRic[$reparto][$tag]) ) {
                        $this->grigliaDayRic[$reparto][$tag]=new panoramaRicline($this->res['ricric'],$this->rics);
                    }

                    $this->grigliaDayRic[$reparto][$tag]->addTl( $t->getTl() );

                    if ( !isset($this->collTotRics[$reparto][$coll]) ) {
                        $this->collTotRics[$reparto][$coll]=new panoramaRicline($this->res['ricric'],$this->rics);
                    }

                    $this->collTotRics[$reparto][$coll]->addTl($t->getTl());
                }
            }
        }

        /*foreach ($this->grigliaDaySub as $reparto=>$r) {

            foreach ($r as $tag=>$t) {
            
                $this->log[]=array(
                    "reparto"=>$reparto,
                    "tag"=>$tag,
                    "tl"=>$t->getTl()
                );
            }
        }*/
    }

    function calcolaIntTot() {
        //calcola i totali per reparto
        // e i totali per il giorno

        $this->calcolaDayTot();

        foreach ($this->grigliaDaySub as $reparto=>$r) {

            foreach ($r as $tag=>$t) {
                
                if ( !isset($this->grigliaIntSub[$reparto]) ) {
                    $this->grigliaIntSub[$reparto]=new panoramaTimeline($this->res['subs'],$this->subs);
                }

                $this->grigliaIntSub[$reparto]->addTl($t->getTl());

                if ($this->config['agenda']) {

                    if ( !isset($this->grigliaDayTotSub[$tag]) ) {
                        $this->grigliaDayTotSub[$tag]=new panoramaTimeline($this->res['subs'],$this->subs);
                    }
                    $this->grigliaDayTotSub[$tag]->addTl($t->getTl());

                    $temptrim=$this->grigliaDayTotSub[$tag]->getTrim();

                    if ($temptrim['min']<$this->globalTrim['min']) $this->globalTrim['min']=$temptrim['min'];
                    if ($temptrim['max']>$this->globalTrim['max']) $this->globalTrim['max']=$temptrim['max'];
                }
                
            }
        }

        foreach ($this->grigliaDayRic as $reparto=>$r) {

            foreach ($r as $tag=>$t) {
                
                if ( !isset($this->grigliaIntRic[$reparto]) ) {
                    $this->grigliaIntRic[$reparto]=new panoramaRicline($this->res['ricric'],$this->rics);
                }

                $this->grigliaIntRic[$reparto]->addTl($t->getTl());

                if ($this->config['agenda']) {

                    if ( !isset($this->grigliaDayTotRic[$tag]) ) {
                        $this->grigliaDayTotRic[$tag]=new panoramaRicline($this->res['ricric'],$this->rics);
                    }
                    $this->grigliaDayTotRic[$tag]->addTl($t->getTl());
                }
            }
        }

        /*foreach ($this->grigliaIntSub as $reparto=>$r) {
            
            $this->log[]=array(
                "reparto"=>$reparto,
                "tl"=>$r->getTl()
            );
        }*/

    }

}

?>