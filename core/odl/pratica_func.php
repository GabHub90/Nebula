<?php

class nebulaPraticaFunc {

    //entrata=apertura commessa più vecchia, riconsegna=prevista riconsegna più alta, fine=prevista fine lamentato più alta 
    protected $info=array(
        "pratica"=>"",
        "pratica_pren"=>"0",
        "dms"=>"",
        "pren"=>"",
        "stato"=>"XX",
        "nota"=>"",
        "entrata"=>"xxxxxxxx:xx:xx",
        "riconsegna"=>"xxxxxxxx:xx:xx",
        "picking"=>0,
        "garanzia"=>0,
        "commesse"=>array(),
        "richieste"=>array(),
        "saldi"=>array()
    );

    protected $stati=array();

    //viene valorizzato solo nel caso si stiano elencando le commesse ed esista una prenotazione riferita alla pratica
    protected $alertPren=array();

    protected $alert=array();

    protected $alertPra=array();
    protected $alertCom=array();
    protected $alertLam=array();

    protected $actualCC=array();

    //se la vettura è in casa oppure no
    protected $presente=true;

    //rilevanza degli stati
    protected $statusLine=array('XX','RP','OK','LA','DL','PK','AT','RO','EX','SO','NP');

    protected $codiciGaranzie=array();

    protected $odlFunc;

    protected $log=array();

    function __construct($pratica,$pratica_pren,$dms,$pren,$odlFunc) {

        $this->info['pratica']=$pratica;
        $this->info['pratica_pren']=$pratica_pren;
        $this->info['dms']=$dms;
        $this->info['pren']=$pren;

        $this->odlFunc=$odlFunc;

        //###########################
        //recupera le definizioni degli alert
        $this->alert=array(
            "azioni"=>array(
                "ON"=>"azioni_ON.png",
                "OFF"=>"azioni_OFF.png",
                "set"=>array(
                    "ON"=>"Azioni da Fare",
                    "OFF"=>"NO Azioni",
                    "DV"=>"Da Verificare"
                )
            ),
            "ricambi"=>array(
                "ON"=>"ricambi_ON.png",
                "OFF"=>"ricambi_OFF.png",
                "set"=>false
            ),
            "diss"=>array(
                "ON"=>"diss_ON.png",
                "OFF"=>"diss_OFF.png",
                "set"=>array(
                    "ON"=>"Diss OK",
                    "OFF"=>"Diss non OK",
                    "NR"=>"Non rilevante"
                )
            ),
            "ripetuta"=>array(
                "ON"=>"ripetuta_ON.png",
                "OFF"=>"ripetuta_OFF.png",
                "set"=>array(
                    "ON"=>"Ripetuta",
                    "NR"=>"Non rilevante"
                )
            ),
            "pacman"=>array(
                "ON"=>"ripetuta_ON.png",
                "OFF"=>"ripetuta_OFF.png",
                "set"=>array(
                    "ON"=>"Autorizzato",
                    "DV"=>"Inserito",
                    "OFF"=>"NON inserito",
                    "NR"=>"Non rilevante"
                )
            )
        );
        //###########################

    }

    function getLog() {
        return $this->log;
    }

    function setDefaultAlert() {
        //in base alle caratteristiche, Officina, Marca, Data
        //decidi quali sono gli alert da considerare di default
        //nel caso il record esistesse già prenderebbe il sopravvento

        //stato: DV = da verificare , ON = accesa , OFF = spenta , NR = non rilevante
        //bubble: "XX" = non influenza lo stato generale , "pren" = se esite valo lo stato della prenotazione , "comm" = vale lo stato della commessa

        //non è detto che tutti gli alert abbiano tutte le opzioni - per esempio DISS non ha DV in quanto se è rilevante è da fare e basta
        //in base all'oggetto occorre anche definire se un certo stato è da SEGNALARE come ERRORE

        //"azioni" viene deciso a livello di PRATICA e viene ereditato da ogni commessa
        //"diss" viene deciso a livello di lamentato e viene ereditato dalla commessa

        //#########################################
        //17.11.2022 PER IL MOMENTO GLI ALERT SI DISTINGUONO PER LIVELLO MA CREDO CHE RIMANGA QUELLO PER LA COMMESSA e di conseguenza quello per la PRATICA che ne è la sintesi
        //#########################################

        //TEST
        if ($this->info['pren']=='S') {

            $this->alertPra=array(
                "azioni"=>array(
                    "stato"=>"DV",
                    "dataora"=>"",
                    "utente"=>"",
                    "bubble"=>"pren",
                    "error"=>"DV",
                    "ok"=>"ON_OFF"
                ),
                "diss"=>array(
                    "stato"=>"NR",
                    "dataora"=>"",
                    "utente"=>"",
                    "bubble"=>"pren",
                    "error"=>"OFF",
                    "ok"=>"ON"
                ),
                "ricambi"=>array(
                    "stato"=>"OFF",
                    "dataora"=>"",
                    "utente"=>"",
                    "bubble"=>"comm",
                    "error"=>"ON",
                    "ok"=>"OFF"
                ),
                "ripetuta"=>array(
                    "stato"=>"NR",
                    "dataora"=>"",
                    "utente"=>"",
                    "bubble"=>"XX",
                    "error"=>"ON",
                    "ok"=>""
                ),
                "pacman"=>array(
                    "stato"=>"NR",
                    "dataora"=>"",
                    "utente"=>"",
                    "bubble"=>"XX",
                    "error"=>"OFF",
                    "ok"=>"ON"
                )
            );

            $this->alertCom=array(
                "azioni"=>array(
                    "stato"=>"DV",
                    "dataora"=>"",
                    "utente"=>"",
                    "bubble"=>"pren",
                    "error"=>"DV",
                    "ok"=>"ON_OFF"
                ),
                "diss"=>array(
                    "stato"=>"NR",
                    "dataora"=>"",
                    "utente"=>"",
                    "bubble"=>"pren",
                    "error"=>"OFF",
                    "ok"=>"ON"
                ),
                "ricambi"=>array(
                    "stato"=>"OFF",
                    "dataora"=>"",
                    "utente"=>"",
                    "bubble"=>"comm",
                    "error"=>"ON",
                    "ok"=>"OFF"
                ),
                "ripetuta"=>array(
                    "stato"=>"NR",
                    "dataora"=>"",
                    "utente"=>"",
                    "bubble"=>"XX",
                    "error"=>"ON",
                    "ok"=>""
                ),
                "pacman"=>array(
                    "stato"=>"NR",
                    "dataora"=>"",
                    "utente"=>"",
                    "bubble"=>"XX",
                    "error"=>"OFF",
                    "ok"=>"ON"
                )
            );

            $this->alertLam=array(
                "diss"=>array(
                    "stato"=>"NR",
                    "dataora"=>"",
                    "utente"=>"",
                    "bubble"=>"XX",
                    "bufrom"=>"",
                    "error"=>"OFF",
                    "ok"=>"ON"
                ),
                "ricambi"=>array(
                    "stato"=>"OFF",
                    "dataora"=>"",
                    "utente"=>"",
                    "bubble"=>"XX",
                    "bufrom"=>"",
                    "error"=>"ON",
                    "ok"=>"OFF"
                ),
                "ripetuta"=>array(
                    "stato"=>"OFF",
                    "dataora"=>"",
                    "utente"=>"",
                    "bubble"=>"XX",
                    "bufrom"=>"",
                    "error"=>"",
                    "ok"=>""
                )
            );
        }

        if ($this->info['pren']=='N') {

            $this->alertPra=array(
                "azioni"=>array(
                    "stato"=>"DV",
                    "dataora"=>"",
                    "utente"=>"",
                    "bubble"=>"XX",
                    "error"=>"DV",
                    "ok"=>"ON_OFF"
                ),
                "diss"=>array(
                    "stato"=>"NR",
                    "dataora"=>"",
                    "utente"=>"",
                    "bubble"=>"XX",
                    "error"=>"OFF",
                    "ok"=>"ON"
                ),
                "ricambi"=>array(
                    "stato"=>"OFF",
                    "dataora"=>"",
                    "utente"=>"",
                    "bubble"=>"XX",
                    "error"=>"ON",
                    "ok"=>"OFF"
                ),
                "ripetuta"=>array(
                    "stato"=>"NR",
                    "dataora"=>"",
                    "utente"=>"",
                    "bubble"=>"XX",
                    "error"=>"ON",
                    "ok"=>""
                ),
                "pacman"=>array(
                    "stato"=>"NR",
                    "dataora"=>"",
                    "utente"=>"",
                    "bubble"=>"XX",
                    "error"=>"OFF",
                    "ok"=>"ON"
                )
            );
    
            $this->alertCom=array(
                "azioni"=>array(
                    "stato"=>"DV",
                    "dataora"=>"",
                    "utente"=>"",
                    "bubble"=>"XX",
                    "error"=>"DV",
                    "ok"=>"ON_OFF"
                ),
                "diss"=>array(
                    "stato"=>"NR",
                    "dataora"=>"",
                    "utente"=>"",
                    "bubble"=>"XX",
                    "error"=>"OFF",
                    "ok"=>"ON"
                ),
                "ricambi"=>array(
                    "stato"=>"OFF",
                    "dataora"=>"",
                    "utente"=>"",
                    "bubble"=>"XX",
                    "error"=>"ON",
                    "ok"=>"OFF"
                ),
                "ripetuta"=>array(
                    "stato"=>"NR",
                    "dataora"=>"",
                    "utente"=>"",
                    "bubble"=>"XX",
                    "error"=>"ON",
                    "ok"=>""
                ),
                "pacman"=>array(
                    "stato"=>"NR",
                    "dataora"=>"",
                    "utente"=>"",
                    "bubble"=>"XX",
                    "error"=>"OFF",
                    "ok"=>"ON"
                )
            );
    
            $this->alertLam=array(
                "diss"=>array(
                    "stato"=>"NR",
                    "dataora"=>"",
                    "utente"=>"",
                    "bubble"=>"XX",
                    "bufrom"=>"",
                    "error"=>"OFF",
                    "ok"=>"ON"
                ),
                "ricambi"=>array(
                    "stato"=>"OFF",
                    "dataora"=>"",
                    "utente"=>"",
                    "bubble"=>"XX",
                    "bufrom"=>"",
                    "error"=>"ON",
                    "ok"=>"OFF"
                ),
                "ripetuta"=>array(
                    "stato"=>"OFF",
                    "dataora"=>"",
                    "utente"=>"",
                    "bubble"=>"XX",
                    "bufrom"=>"",
                    "error"=>"",
                    "ok"=>""
                )
            );
        }

        //END TEST

        //###########################
        //recupera gli stati riferiti alla pratica sia commesse che relativi lamentati
        //se non esistono si prendono comunque i valori di DEFAULT
        $this->stati=$this->odlFunc->getStatiPratica("'".$this->info['pratica']."','".$this->info['pratica_pren']."'",$this->info['pren']);
        //###########################
        
        foreach ($this->stati as $k=>$s) {
            if ($s['pren']) {
                $this->alertPren=json_decode($s['pren']['alert'],true);
            }
        }
    }

    static function initJS() {

        echo '<script type="text/javascript" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/odl/core/alert.js?v='.time().'"></script>';
        echo '<script type="text/javascript">';
            echo 'window._nebulaPraticaAlert=new nebulaPraticaAlert();';
        echo '</script>';
    }

    //!!!!!!!!! la dinamica è stata spostata a livello di caricamento del lamentato !!!!!!!!!!!!!

    /*function buildPratica() {

        //se pren==S prende la prenotazione altrimenti tutte le commesse
        foreach ($this->odlFunc->getPraticaRif($this->info['pratica'],$this->info['dms'],$this->info['pren']) as $k=>$r) {

            $this->info['commesse'][$r['rif']]=array(
                "stato"=>"XX",
                "calcolato"=>"XX",
                "nota"=>"",
                "dataora"=>date('Ymd:H:i'),
                "utente"=>"system",
                "alert"=>$this->alertCom,
                "lam"=>array()
            );
        }
    }*/

    function getPresenza() {
        return $this->presente;
    }

    function addLam($row) {

        if (!isset($this->info['commesse'][$row['rif']])) {
            
            if (isset($row['id_nuovo']) && $row['id_nuovo']!=0  && isset($row['d_arrivo']) && $row['d_arrivo']=='') $this->presente=false;

            //se esiste uno stato "CC" riferito alla commessa (ESISTE SOLO SE È STATO IMPOSTATO MANUALMENTE)
            if (array_key_exists($row['rif'],$this->stati) && $this->stati[$row['rif']]['cc']) {

                if ($this->stati[$row['rif']]['cc']['prevfine']=='') $this->stati[$row['rif']]['cc']['prevfine']='xxxxxxxx:xx:xx';

                $this->info['commesse'][$row['rif']]['stato']=$this->stati[$row['rif']]['cc']['stato'];
                $this->info['commesse'][$row['rif']]['scadenza']=$this->stati[$row['rif']]['cc']['scadenza'];
                $this->info['commesse'][$row['rif']]['calcolato']='XX';
                $this->info['commesse'][$row['rif']]['calc_dataora']=date('Ymd:H:i');
                $this->info['commesse'][$row['rif']]['calc_utente']='system';
                $this->info['commesse'][$row['rif']]['nota']=$this->stati[$row['rif']]['cc']['nota'];
                $this->info['commesse'][$row['rif']]['dataora']=$this->stati[$row['rif']]['cc']['dataora'];
                $this->info['commesse'][$row['rif']]['utente']=$this->stati[$row['rif']]['cc']['utente'];
                $this->info['commesse'][$row['rif']]['alert']=$this->alertCom;
                $this->info['commesse'][$row['rif']]['prevfine']=$this->stati[$row['rif']]['cc']['prevfine'];
                $this->info['commesse'][$row['rif']]['calc_prevfine']='xxxxxxxx:xx:xx';
                $this->info['commesse'][$row['rif']]['lam']=array();

                //ricalcolo degli alert di default in base a quanto contenuto nel record
                if ($this->stati[$row['rif']]['cc']['alert']!='') {
                    $temp=json_decode($this->stati[$row['rif']]['cc']['alert'],true);

                    /*$ptp=false;

                    if ($this->stati[$row['rif']]['pren'] && $this->stati[$row['rif']]['pren']['alert']!='') {
                        $ptp=json_decode($this->stati[$row['rif']]['pren']['alert'],true);
                    }*/

                    if ($temp) {

                        //##################################
                        foreach ($this->info['commesse'][$row['rif']]['alert'] as $k=>$p) {
                            if (!array_key_exists($k,$temp)) continue;
                            
                            //if ($p['bubble']=='pren' && $ptp && array_key_exists($k,$ptp)) {
                            if ($p['bubble']=='pren' && array_key_exists($k,$this->alertPren)) {
                                $this->info['commesse'][$row['rif']]['alert'][$k]['stato']=$this->alertPren[$k]['stato'];
                                $this->info['commesse'][$row['rif']]['alert'][$k]['dataora']=$this->alertPren[$k]['dataora'];
                                $this->info['commesse'][$row['rif']]['alert'][$k]['utente']=$this->alertPren[$k]['utente'];
                            }
                            else {
                                $this->info['commesse'][$row['rif']]['alert'][$k]['stato']=$temp[$k]['stato'];
                                $this->info['commesse'][$row['rif']]['alert'][$k]['dataora']=$temp[$k]['dataora'];
                                $this->info['commesse'][$row['rif']]['alert'][$k]['utente']=$temp[$k]['utente'];
                            }

                            if (array_key_exists($k,$this->alertPren) && array_key_exists($k,$this->alertPra) && $this->alertPra[$k]['bubble']=='pren') {
                                $this->updateAlertPra($k,$p,$this->alertPren);
                            }
                            elseif (array_key_exists($k,$this->alertPra)) {
                                $this->updateAlertPra($k,$p,$temp);
                            }
                        }

                        /*ricalcola gli ALERT della PRATICA
                        foreach ($this->alertPra as $k=>$p) {
                            if (!array_key_exists($k,$temp)) continue;

                            if ($temp[$k]['stato']!=$p['stato']) {
                                if (strpos($p['ok'],$temp[$k]['stato'])>-1) {
                                    if (!strpos($p['error'],$p['stato'])) {
                                        $this->alertPra[$k]['stato']=$temp[$k]['stato'];
                                        $this->alertPra[$k]['stato']=$temp[$k]['stato'];
                                        $this->alertPra[$k]['stato']=$temp[$k]['stato'];
                                    }
                                }
                                elseif (strpos($p['error'],$temp[$k]['stato'])>-1) {
                                    $this->alertPra[$k]['stato']=$temp[$k]['stato'];
                                }
                            }
                        }*/
                        //##################################

                    }
                }
            }
            
            else {

                $this->info['commesse'][$row['rif']]=array(
                    "stato"=>"XX",
                    "calcolato"=>"XX",
                    "nota"=>"",
                    "scadenza"=>"",
                    "dataora"=>date('Ymd:H:i'),
                    "utente"=>"system",
                    "calc_dataora"=>date('Ymd:H:i'),
                    "calc_utente"=>"system",
                    "alert"=>$this->alertCom,
                    "prevfine"=>"xxxxxxxx:xx:xx",
                    "calc_prevfine"=>"xxxxxxxx:xx:xx",
                    "lam"=>array()
                );

                if ($this->alertPren && count($this->alertPren)>0) {

                    //##################################
                    foreach ($this->info['commesse'][$row['rif']]['alert'] as $k=>$p) {
                        if (!array_key_exists($k,$this->alertPren) || $p['bubble']!='pren' ) continue;
                            
                        $this->info['commesse'][$row['rif']]['alert'][$k]['stato']=$this->alertPren[$k]['stato'];
                        $this->info['commesse'][$row['rif']]['alert'][$k]['dataora']=$this->alertPren[$k]['dataora'];
                        $this->info['commesse'][$row['rif']]['alert'][$k]['utente']=$this->alertPren[$k]['utente'];

                        if (array_key_exists($k,$this->alertPra) && $this->alertPra[$k]['bubble']=='pren') {
                            $this->updateAlertPra($k,$p,$this->alertPren);
                        }
                    }
                }
            }

            //la commessa viene generata adesso
            //if ($row['d_fine']!='xxxxxxxx:xx:xx') $this->info['commesse'][$row['rif']]['fine']=$row['d_fine'];
            //else $this->info['commesse'][$row['rif']]['fine']='xxxxxxxx:xx:xx';

            //$this->info['commesse'][$row['rif']]['fine_calcolata']='xxxxxxxx:xx:xx';

            $this->info['entrata']=$row['d_entrata_pratica'];
            $this->info['riconsegna']=$row['d_ricon_pratica'];

            if($this->info['picking']==0) $this->info['picking']=$row['picking'];

        }

        /*se esiste un record per il lamentato (ESISTE SOLO SE È STATO IMPOSTATO MANUALMENTE)
        $this->info['commesse'][$r['rif']]['stato']=
        $this->info['commesse'][$r['rif']]['nota']=
        $this->info['commesse'][$r['rif']]['dataora']=
        $this->info['commesse'][$r['rif']]['utente']=
        $this->info['commesse'][$r['rif']]['alert']=json_decode
        in odlFunc c'è una tabella per convertire gli stati delle marcature/lam derivante dalle marcature in stato ODL
        ELSE
        */

        $this->info['commesse'][$row['rif']]['lam'][$row['lam']]=array(
            "stato"=>$this->odlFunc->convStatoLam($row['ind_inc_stato'],$row['dms']),
            "nota"=>"",
            "scadenza"=>"",
            "dataora"=>date('Ymd:H:i'),
            "utente"=>"system",
            "prevfine"=>"xxxxxxxx:xx:xx",
            "alert"=>$this->alertLam
        );

        //####################################
        //impostazione alert SALDI (il campo saldi in infinity viene valorizzato nel caso di COMMESSE)
        if (isset($row['saldo'])) {
            if ($row['saldo']>0) {
                if (isset($this->info['commesse'][$row['rif']]['alert']['ricambi'])) {
                    $this->info['commesse'][$row['rif']]['alert']['ricambi']['stato']='ON';
                }

                if (array_key_exists('ricambi',$this->alertPra)) {
                    $this->alertPra['ricambi']['stato']='ON';
                }
            }
        }
        //####################################

        //verifica alert DISS
        if (isset($this->alert['diss'])) {
            if ($ta=$this->odlFunc->getAddebito($row,$this->info['dms'])) {
                if ($ta['tipo']=='gar' && $ta['info']!='azione') {
                    //$this->info['garanzia']=$this->alertPra['diss']['stato'];

                    //se è una prenotazione
                    if ($this->alertPren && count($this->alertPren)>0 && $this->info['commesse'][$row['rif']]['alert']['diss']['bubble']=='pren' && $this->alertPren['diss']['stato']=='NR') {
                    
                        $this->alertPren['diss']['stato']='OFF';
                        $this->alertPren['diss']['dataora']=date('Ymd:H:i');
                        $this->alertPren['diss']['utente']='system';

                        if (array_key_exists('diss',$this->alertPra) && $this->alertPra['diss']['bubble']=='pren' && $this->alertPra['diss']['stato']=='NR') {
                            $this->alertPra['diss']['stato']='OFF';
                        }
                    }

                    //altrimenti
                    elseif ($this->info['commesse'][$row['rif']]['alert']['diss']['stato']=='NR') {
                        $this->info['commesse'][$row['rif']]['alert']['diss']['stato']='OFF';
                        $this->info['commesse'][$row['rif']]['alert']['diss']['dataora']=date('Ymd:H:i');
                        $this->info['commesse'][$row['rif']]['alert']['diss']['utente']='System';

                        if ($this->alertPra['diss']['stato']=='NR') {
                            $this->alertPra['diss']['stato']=='OFF';
                        }
                    }
                }
            }
        }

    }

    function updateAlertPra($k,$p,$temp) {

        //ricalcola gli ALERT della PRATICA
        //############################################
        //19.11.2022 In questo momento lo stato peggiore vince ma:
        //se è una prenotazione esiste solo quella e quindi in pratica lo stato del RIFERIMENTO coincide con lo stato della PRATICA
        //se è una commessa il comportamento è differente perché alcuni stati sono condivisi tra tutte le commesse tipo AZIONI e RIPETUTA
        //############################################
        //if ($temp[$k]['stato']!=$this->alertPra[$k]['stato']) {
            if (strpos($this->alertPra[$k]['ok'],$temp[$k]['stato'])>-1) {
                if (!strpos($this->alertPra[$k]['error'],$p['stato'])) {
                    $this->alertPra[$k]['stato']=$temp[$k]['stato'];
                    $this->alertPra[$k]['dataora']=$temp[$k]['dataora'];
                    $this->alertPra[$k]['utente']=$temp[$k]['utente'];
                }
            }
            elseif (strpos($this->alertPra[$k]['error'],$temp[$k]['stato'])>-1) {
                $this->alertPra[$k]['stato']=$temp[$k]['stato'];
                $this->alertPra[$k]['dataora']=$temp[$k]['dataora'];
                $this->alertPra[$k]['utente']=$temp[$k]['utente'];
            }
        //}
    }

    function calcoloStato($actual,$new) {

        $flag=false;

        foreach ($this->statusLine as $k=>$s) {

            //se sto valutando il nuovo stato ma il vecchio è maggiore ritorna il vecchio alrimenti ritorna il nuovo
            if ($s==$new) {
                if ($flag) return $new;
                else return $actual;
            }

            if ($s==$actual) $flag=true;
        }

        return $actual;
    }

    function richiesteMateriale() {
        //in base ai lamentati già caricati ricerca se esistono RICHIESTE MATERIALE / ORDINI TECNICI aperti ed il loro stato
        foreach ($this->info['commesse'] as $rif=>$c) {

            //$this->log[]=$rif.' '.$this->info['dms'].' '.$this->info['pren'];

            $this->info['richieste'][$rif]=$this->odlFunc->richiesteMateriale($this->info['dms'],$this->info['pren'],$rif);

            if (count($this->info['richieste'][$rif])>0) {

                foreach($this->info['richieste'][$rif] as $ko=>$o) {
                    
                    foreach ($o as $kl=>$l) {

                        foreach ($l as $k=>$r) {

                            //esegue il calcolo al volo delle richieste materiale
                            //se ci fossero problemi di TEMPO di esecuzione occorrerà scrivere il DB in modo che l'informazione venga recuperata da CONSTRUCT (getStatiPratica)
                            //if ((float)$r['qta_dev_ordcli']>0) {
                            
                            $dispo=$r['giacenza']-$r['impegnato'];
                
                            if ($dispo<0) {

                                if (!isset($rif,$this->info['saldi'])) $this->info['saldi'][$rif]=array();
                                if (!isset($kl,$this->info['saldi'][$rif])) $this->info['saldi'][$rif][$kl]=array();
                                //if (!isset($k,$this->info['saldi'][$rif][$kl])) $this->info['saldi'][$rif][$kl][$k]=array();
                                $this->info['saldi'][$rif][$kl][$k]=$r;
                                //echo $r['qta_dev_ordcli'];

                                if (array_key_exists('ricambi',$this->alertPra)) {
                                    $this->alertPra['ricambi']['stato']='ON';
                                }

                                //################################
                                //17.11.2022 scrivo anche l'alert a livello commessa ma credo rimarrà SOLO quello a livello pratica
                                //################################
                                if (isset($this->info['commesse'][$rif]['alert']['ricambi'])) {
                                    $this->info['commesse'][$rif]['alert']['ricambi']['stato']='ON';
                                }
                            }
                        }
                    }
                }
            }

        }

        //$this->log=$this->odlFunc->getLog();

    }

    function drawLine($rif,$lam,$edit,$pren,$call) {
        // $lam=="" -> Commessa
        // $lam && $rif =="" -> Pratica

        if ($rif=="" && $lam=="") {
            $temp=&$this->alertPra;
            $ambito=&$this->alertPra;
            $suffix='pratica';
        }
        elseif ($lam=="") {
            $temp=&$this->info['commesse'][$rif];
            $ambito=&$this->alertCom;
            $suffix='odl';
        }
        else {
            $temp=&$this->info['commesse'][$rif]['lam'][$lam];
            $ambito=&$this->alertLam;
            $suffix='lamentato';
        }

        if (is_null($temp)) return;

        echo '<div id="nebula_alert_line_'.$call.'_'.$rif.'_'.$lam.'" style="position:relative;width:100%;min-height:30px;top:3px;" >';

            echo '<div style="position:relative;display:inline-block;width:27%;text-align:left;vertical-align:top;" >';

                $tempAlert=array();

                foreach ($ambito as $k=>$a) {

                    //echo json_encode($temp);

                    if (array_key_exists($k,$temp['alert']) && array_key_exists($k,$this->alert)) {

                        if ($this->alert[$k]['set']) {

                            $tempAlert[$k]=array(
                                "stato"=>$temp['alert'][$k]['stato'],
                                "dataora"=>date('Ymd:H:i'),
                                "utente"=>""
                            );
                        }

                        if ($temp['alert'][$k]['stato']=='NR') continue;

                        echo '<div style="position:relative;display:inline-block;width:20%;height:0px;padding-bottom:20%;text-align:center;margin-left:2%;vertical-align:top;background-position: center;background-size: contain; background-repeat:no-repeat;';
                            //echo $temp['alert'][$k]['error'].' '.$temp['alert'][$k]['stato'];
                            if (strpos($temp['alert'][$k]['error'],$temp['alert'][$k]['stato'])>-1) echo 'background-image:url(\'http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/odl/img/alert/errore.png\');';
                            elseif (strpos($temp['alert'][$k]['ok'],$temp['alert'][$k]['stato'])>-1) echo 'background-image:url(\'http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/odl/img/alert/ok.png\');';
                            elseif ($temp['alert'][$k]['stato']=='DV') echo 'background-image:url(\'http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/odl/img/alert/DV.png\');';
                        echo '">';

                            echo '<img style="position:relative;width:60%;margin-top:50%;transform:translate(0px,-50%);" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/odl/img/alert/'.($temp['alert'][$k]['stato']=='ON'?$k.'_ON.png':$k.'_OFF.png').'" />';

                        echo '</div>';
                    }
                }

                //echo $this->info['garanzia'];

                echo '<input id="nebula_alert_array_'.$call.'_'.$rif.'_'.$lam.'" type="hidden" data-pratica="'.$this->info['pratica'].'" data-dms="'.$this->info['dms'].'" data-pren="'.$this->info['pren'].'" data-rif="'.$rif.'" data-lam="'.$lam.'" value="'.base64_encode(json_encode($tempAlert)).'" />';

            echo '</div>';

            echo '<div style="position:relative;display:inline-block;width:73%;text-align:left;" >';
                
                echo '<div style="position:relative;font-size:0.9em;">';
                    if ($tempstato=$this->odlFunc->getStatoOdl($this->calcoloStato($temp['stato'],$temp['calcolato']),$this->info['dms'])) {
                        echo '<div style="position:relative;display:inline-block;vertical-align:top;width:25px;font-size:0.9em;">odl:</div>';
                        echo '<span style="height: 10px;width: 10px;background-color:'.$tempstato['colore'].';border-radius: 50%;display: inline-block;"></span>';
                        echo '<div style="position:relative;display:inline-block;vertical-align:top;width:55px;margin-left:5px;font-size:0.9em;" >'.substr($tempstato['testo'],0,8).'</div>';
                    }
                    echo '<div style="position:relative;display:inline-block;vertical-align:top;width:40px;margin-left:5px;font-size:0.9em;">'.substr($temp['utente'],0,6).'</div>';
                    echo '<span style="margin-left:5px;font-size:0.9em;">'.($temp['dataora']!=""?mainFunc::gab_todata(substr($temp['dataora'],0,8)).' '.substr($temp['dataora'],9,5):'').'</span>';

                    //echo '<span>'.$temp['stato'].$temp['calcolato'].'</span>';

                    if ($edit) {
                        echo '<img id="pratica_odl_block_'.$call.'_icon_'.$rif.'" style="position:absolute;top:2px;right:2px;width:12px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/odl/img/edit.png" data-pratica="'.$this->info['pratica'].'" data-dms="'.$this->info['dms'].'" data-rif="'.$rif.'" data-lam="'.$lam.'" data-pren="'.$this->info['pren'].'" data-edit="'.$edit.'" data-d="'.(substr($temp['prevfine'],0,8)=='xxxxxxxx'?'':mainFunc::gab_toinput(substr($temp['prevfine'],0,8))).'" data-o="'.(substr($temp['prevfine'],9,2)=='xx'?'':substr($temp['prevfine'],9,2)).'" data-m="'.(substr($temp['prevfine'],12,2)=='xx'?'':substr($temp['prevfine'],12,2)).'" data-call="'.$call.'" data-scadenza="'.$temp['scadenza'].'" onclick="window._nebulaPraticaAlert.openEdit(this,\''.$suffix.'\');" />';
                    }
                echo '</div>';

                if ($suffix=='odl') {

                    echo '<div style="position:relative;font-size:0.9em;">';
                        if ($tempstato=$this->odlFunc->getStatoOdl($temp['calcolato'],$this->info['dms'])) {
                            echo '<div style="position:relative;display:inline-block;vertical-align:top;width:25px;font-size:0.9em;">lam:</div>';
                            echo '<span style="height: 10px;width: 10px;background-color:'.$tempstato['colore'].';border-radius: 50%;display: inline-block;"></span>';
                            echo '<div style="position:relative;display:inline-block;vertical-align:top;width:55px;margin-left:5px;font-size:0.9em;" >'.substr($tempstato['testo'],0,8).'</div>';
                        }
                        echo '<div style="position:relative;display:inline-block;vertical-align:top;width:40px;margin-left:5px;font-size:0.9em;">'.substr($temp['calc_utente'],0,6).'</div>';
                        echo '<span style="margin-left:5px;font-size:0.9em;">'.($temp['calc_dataora']!=""?mainFunc::gab_todata(substr($temp['calc_dataora'],0,8)).' '.substr($temp['calc_dataora'],9,5):'').'</span>';
                    echo '</div>';
                }

                /*echo '<div id="nebula_alert_nota_div_'.$rif.'" style="position:relative;top:-3px;font-size:0.9em;font-weight:bold;color:#ff00a4;" data-stato="'.$tempstato['codice'].'" >';
                    //echo substr('EWUYRWVATO FBYUNZAEORUYFGN AEYRFGLDVZ YFCTCXZBTRY FXUYFTGSB FYGTFGNAUFKYGZ YFGTFBYGZSYTFGSZEVFYGCBXZJSXX',0,80);
                    echo substr($temp['nota'],0,80);
                echo '</div>';*/

            echo '</div>';

            /*echo '<div>';
                echo json_encode($this->stati);
            echo '</div>';*/

        echo '</div>';

    }

    function getNota($rif,$lam) {

        if ($rif=="" && $lam=="") {
            $temp=&$this->info;
        }
        elseif ($lam=="") {
            $temp=&$this->info['commesse'][$rif];
        }
        else {
            $temp=&$this->info['commesse'][$rif]['lam'][$lam];
        }

        if (is_null($temp)) return;

        $tempstato=$this->odlFunc->getStatoOdl($temp['stato'],$this->info['dms']);

        $txt='<div style="position:relative;font-size:0.9em;font-weight:bold;color:#ff00a4;" >';
            if ($temp['nota']!="") {
                $txt.='<div id="nebula_alert_nota_div_'.$rif.'_'.$lam.'"  style="width:100%;border: 1px dotted violet;box-sizing:border-box;padding:2px;margin-top:5px;" data-stato="'.$tempstato['codice'].'" >';
                    $txt.=substr($temp['nota'],0,80);
                $txt.='</div>';
            }
            else {
                $txt.='<div id="nebula_alert_nota_div_'.$rif.'_'.$lam.'" data-stato="'.$tempstato['codice'].'"></div>';
            }
        $txt.='</div>';

        return $txt;
    }

    function getStato($rif,$lam) {

        if ($rif=="" && $lam=="") {
            
            $stato='XX';

            //è il record di stato che determina il valore della PRATICA
            $this->actualCC=array();

            foreach ($this->info['commesse'] as $kc=>$c) {
                $n=$this->getStato($kc,'');
                $stato=$this->calcoloStato($stato,$n);
                if ($stato==$n) {
                    //se lo stato è quello nuovo aggiorna actualCC
                    $this->actualCC=$this->info['commesse'][$kc];
                    $this->actualCC['rif']=$kc;
                }
            }

            //$this->log[]=$rif.'_'.$stato;

            //####################################
            //calcola lo stato degli ALERT
            foreach ($this->alertPra as $k=>$p) {
                if ($p['stato']=='NR') continue;
                if ($p['bubble']=='XX') continue;
                if (strpos($p['ok'],$p['stato'])===false) $stato='AT';
            }
            //####################################

            if ($stato=='PK' && $this->info['picking']==1) $stato='RP';

            if (!$this->presente) $stato='NP';

            return $stato;
        }
        elseif ($lam=="") {
            $temp=&$this->info['commesse'][$rif];

            $stato='XX';

            //cambia lo stato in base agli stati dei lamentati
            $stato=$this->calcoloStato($temp['stato'],$temp['calcolato']);

            //$this->log[]=$rif.'_'.$stato;

            //####################################
            //18.11.2022 calcola lo stato degli ALERT (il calcolo per il momento è SEMPRE a livello di PRATICA)
            foreach ($this->info['commesse'][$rif]['alert'] as $k=>$p) {

                //$this->log[]=$k.'_'.$p['stato'].'_'.strpos($p['ok'],$p['stato']);

                if ($p['stato']=='NR') continue;
                if ($p['bubble']=='XX') continue;
                if (strpos($p['ok'],$p['stato'])===false) $stato='AT';
            }
            //####################################

            if ($stato=='PK' && $this->info['picking']==1) $stato='RP';

            if (!$this->presente) $stato='NP';

            return $stato;
        }
        else {
            $temp=&$this->info['commesse'][$rif]['lam'][$lam];

            return $temp['stato'];
        }

        return false;

    }

    function getActualCC() {
        return $this->actualCC;
    }

    function getStatoCC($rif) {

        $temp='XX';

        //restituisce lo stato della commessa letto dal DB(array stati) se esiste
        if (array_key_exists($rif,$this->stati) && $this->stati[$rif]['cc']) {
            $temp=$this->stati[$rif]['cc']['stato'];
        }

        return $temp;

    }

    function getFine($rif,$lam) {
        //restituisce la data di fine lavori più alta

        if ($rif!="" && $lam!="") return $this->info['commesse'][$rif]['lam'][$lam]['prevfine'];
        else if ($rif!="") {

            if ($this->info['commesse'][$rif]['calc_prevfine']!='xxxxxxxx:xx:xx' && $this->info['commesse'][$rif]['calc_prevfine']>$this->info['commesse'][$rif]['prevfine']) return $this->info['commesse'][$rif]['calc_prevfine'];
            else return $this->info['commesse'][$rif]['prevfine'];
        }
        else {
            $ret='xxxxxxxx:xx:xx';
            foreach ($this->info['commesse'] as $kc=>$c) {

                //strcmp Returns <0 if string1 is less than string2; > 0 if string1 is greater than string2, and 0 if they are equal.}
                $temp=($c['calc_prevfine']!='xxxxxxxx:xx:xx' && $c['calc_prevfine']>$c['prevfine'])?$c['calc_prevfine']:$c['prevfine'];

                if ($temp!='xxxxxxxx:xx:xx') {
                    if ($ret=='xxxxxxxx:xx:xx' || $temp>$ret) $ret=$temp;
                }
            }

            return $ret;
        }

        return 'xxxxxxxx:xx:xx';
    }

    function getChimeApp($rif) {
        
        $ret=false;

        if (array_key_exists($rif,$this->stati) && $this->stati[$rif]['cc']) {

            if (isset($this->stati[$rif]['cc']['chime_app']) && $this->stati[$rif]['cc']['chime_app']!='') {
                $ret=json_decode($this->stati[$rif]['cc']['chime_app'],true);
            }
        }
        return $ret;
    }

    function getRichiesteMateriale() {
        return $this->info['richieste'];
    }

    function getRichiesteMaterialeRif($rif,$pren) {

        if ($pren=='S' && array_key_exists($rif,$this->info['richieste'])) return $this->info['richieste'][$rif];
        elseif ($pren=='N' && array_key_exists($rif,$this->info['saldi'])) return $this->info['saldi'][$rif];
        else return array();
    }

    /////////////////////////////////////////////////////////////////////////////////

    function drawSetAlert($rif,$lam) {

        foreach ($this->info['commesse'][$rif]['alert'] as $k=>$p) {

            if ($this->alert[$k]['set']) {

                echo '<div style="position:relative;margin-top:10px;" >';

                    echo '<div style="position:relative;display:inline-block;vertical-align:top;width:15%;text-align:left;font-weight:bold;">';
                        echo $k;
                    echo '</div>';

                    foreach ($this->alert[$k]['set'] as $ks=>$s) {

                        echo '<div style="position:relative;display:inline-block;vertical-align:top;width:20%;text-align:center;font-size:0.9em;">';

                            echo '<div style="width:100%;" >';

                                echo '<div style="position:relative;display:inline-block;vertical-align:top;">';
                                    echo '<input name="nebulaAlertUpdate_'.$k.'" style="margin-right:10px;" type="radio" data-alert="'.$k.'" value="'.$ks.'"';
                                        if ($ks==$p['stato']) echo 'checked';
                                    echo ' />';
                                echo '</div>';

                                if ($ks!='NR') {
                                    echo '<div style="position:relative;display:inline-block;width:25px;height:5px;padding-bottom:20%;text-align:center;margin-top:-3px;vertical-align:top;background-position: center;background-size: contain; background-repeat:no-repeat;';
                                        if (strpos($p['error'],$ks)>-1) echo 'background-image:url(\'http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/odl/img/alert/errore.png\');';
                                        elseif (strpos($p['ok'],$ks)>-1) echo 'background-image:url(\'http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/odl/img/alert/ok.png\');';
                                        elseif ($ks=='DV') echo 'background-image:url(\'http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/odl/img/alert/DV.png\');';
                                    echo '">';

                                        echo '<img style="position:relative;width:60%;margin-top:50%;transform:translate(0px,-50%);" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/odl/img/alert/'.($ks=='ON'?$k.'_ON.png':$k.'_OFF.png').'" />';

                                    echo '</div>';
                                }

                            echo '</div>';

                            echo '<div style="width:100%;" >';
                                echo $s;
                            echo '</div>';

                        echo '</div>';
                    }

                echo '</div>';
            }

        }

        echo '<div style="position:relative;width:100%;top:10px;text-align:right;" >';
            echo '<button style="position:relative;margin-right:20px;" onclick="window._nebulaPraticaAlert.updateAlertArray(\''.$rif.'\',\''.$lam.'\');">Conferma Alerts</button>';
        echo '</div>';

        /*echo '<div>';
            echo json_encode($this->log);
        echo '</div>';*/
    }

}

?>