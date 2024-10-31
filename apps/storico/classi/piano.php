<?php
require_once(DROOT.'/nebula/core/odl/wormhole.php');
require_once(DROOT.'/nebula/core/divo/divo.php');

class nebulaStoricoPiano {

    protected $info=array(
        "marca"=>"",
        "modello"=>"",
        "telaio"=>""
    );

    //elenco dei gruppi di manutenzione legati alla marca
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

    protected $codiceForm=array(
        "marche"=>array(),
        "alim"=>array(),
        "traz"=>array(),
        "cambio"=>array(),
        "manut"=>array(),
        "split"=>array(
            "marca"=>"",
            "alim"=>"",
            "traz"=>"",
            "cambio"=>"",
            "manut"=>"",
            "suffix"=>""
        )
    );

    protected $packMarca=array();
    protected $packMM=array();

    //contiene gli oggetti BASE (interventi di manutenzione)
    protected $base=array();
    protected $oggettiDefault=array();

    protected $odlFunc;
    protected $galileo;

    function __construct($marca,$modello,$telaio,$odlFunc,$galileo) {

        $this->odlFunc=$odlFunc;
        $this->galileo=$galileo;

        $this->info['marca']=$marca;
        $this->info['modello']=$modello;
        $this->info['telaio']=$telaio;

        ///////////////////////////////////////////
        $map=$this->odlFunc->getOTBase();

        if ($map['result']) {
            $fid=$this->galileo->preFetchPiattaforma($map['piattaforma'],$map['result']);

            while ($row=$this->galileo->getFetchPiattaforma($map['piattaforma'],$fid)) {
                //vengono estratti in ordine di ambito e di posizione nell'ambito
                $this->base[$row['codice']]=$row;
            }
        }
        ///////////////////////////////////////////

        $this->buildCodiceForm();
    }

    function build() {

        if ($this->info['marca']!="") {

            //carica i gruppi OVERTURE riferiti alla marca
            $map=$this->odlFunc->getOTGruppi($this->info['marca']);

            if ($map['result']) {
                $fid=$this->galileo->preFetchPiattaforma($map['piattaforma'],$map['result']);

                while ($row=$this->galileo->getFetchPiattaforma($map['piattaforma'],$fid)) {
                    $this->gruppi[$row['codice'].'_'.$row['indice']]=$row;
                }
            }

            //carica gli oggetti di DEFAULT per la marca
            $map=$this->odlFunc->getOTDefault($this->info['marca']);

            if ($map['result']) {
                $fid=$this->galileo->preFetchPiattaforma($map['piattaforma'],$map['result']);

                while ($row=$this->galileo->getFetchPiattaforma($map['piattaforma'],$fid)) {
                    $this->oggettiDefault[$row['codice']]=$row;
                }
            }

            ////////////////////////////////////////////////////////////

            if ($this->info['modello']!="") {

                //carica eventuali specifiche per il modello
                $map=$this->odlFunc->getOTCriteriModello($this->info['marca'],$this->info['modello']);

                if ($map['result']) {
                    $fid=$this->galileo->preFetchPiattaforma($map['piattaforma'],$map['result']);

                    while ($row=$this->galileo->getFetchPiattaforma($map['piattaforma'],$fid)) {
                        $this->gruppo['oggettiModello']=json_decode($row['edit'],true);
                    }
                }
            }

            //################################
            //caricamento pacchetti di default per la marca in base alla data attuale
            //nel TEST la marca non fa distinzione

            //TEST
            $temp=array(
                array(
                    "codice"=>"MAN",
                    "marca"=>"V",
                    "inizio"=>"20120501",
                    "fine"=>"21001231",
                    "oggetto"=>'{"M":[],"R":[{"pre":"","codice":"","desc":"","alias":"olio motore","qta":0,"listino":"","opt":0},{"pre":"","codice":"","desc":"","alias":"filtro olio","qta":1,"listino":"","opt":0},{"pre":"","codice":"","desc":"","alias":"detergente","qta":1,"listino":"","opt":1},{"pre":"X","codice":"005.RLO","desc":"","alias":"lubrificazione","qta":1,"listino":"","opt":1}],"V":[{"pre":"","codice":"OSR","desc":"","alias":"smaltimento","qta":1,"listino":5.00,"opt":0}]}'
                ),
                array(
                    "codice"=>"FANT",
                    "marca"=>"V",
                    "inizio"=>"20120501",
                    "fine"=>"21001231",
                    "oggetto"=>'{"M":[],"R":[{"pre":"","codice":"","desc":"","alias":"filtro antipolline","qta":0,"listino":"","opt":0},{"pre":"","codice":"","desc":"","alias":"igienizzante","qta":1,"listino":"","opt":1}],"V":[{"pre":"","codice":"OSR","desc":"","alias":"smaltimento","qta":1,"listino":1.50,"opt":0}]}'
                )
            );
            //ENDTEST

            foreach ($temp as $a) {
                $this->packMarca[$a['codice']]=$a;
                if (!$this->packMarca[$a['codice']]['oggetto']=json_decode($a['oggetto'],true)) $this->packMarca[$a['codice']]['oggetto']=array();
            }

            //#################################################################################
        }

        //carica eventuali specifiche per il telaio
        if ($this->info['telaio']!="") {

            $map=$this->odlFunc->getOTCriteriTelaio($this->info['telaio']);

            if ($map['result']) {
                $fid=$this->galileo->preFetchPiattaforma($map['piattaforma'],$map['result']);

                while ($row=$this->galileo->getFetchPiattaforma($map['piattaforma'],$fid)) {
                    $this->gruppo['oggettiTelaio']=json_decode($row['edit'],true);
                }
            }

        }

    }

    function buildCodiceForm() {

        $map=$this->odlFunc->getOTmarche($this->info['marca']);

        if ($map['result']) {
            $fid=$this->galileo->preFetchPiattaforma($map['piattaforma'],$map['result']);

            while ($row=$this->galileo->getFetchPiattaforma($map['piattaforma'],$fid)) {
                $this->codiceForm['marche'][$row['codice']]=$row;
            }
        }

        ///////////////////////////////////

        $map=$this->odlFunc->getOTalim();

        if ($map['result']) {
            $fid=$this->galileo->preFetchPiattaforma($map['piattaforma'],$map['result']);

            while ($row=$this->galileo->getFetchPiattaforma($map['piattaforma'],$fid)) {
                $this->codiceForm['alim'][$row['codice']]=$row;
            }
        }

        ///////////////////////////////////

        $map=$this->odlFunc->getOTtraz();

        if ($map['result']) {
            $fid=$this->galileo->preFetchPiattaforma($map['piattaforma'],$map['result']);

            while ($row=$this->galileo->getFetchPiattaforma($map['piattaforma'],$fid)) {
                $this->codiceForm['traz'][$row['codice']]=$row;
            }
        }

         ///////////////////////////////////

         $map=$this->odlFunc->getOTCambio($this->info['marca']);

         if ($map['result']) {
             $fid=$this->galileo->preFetchPiattaforma($map['piattaforma'],$map['result']);
 
             while ($row=$this->galileo->getFetchPiattaforma($map['piattaforma'],$fid)) {
                 $this->codiceForm['cambio'][$row['codice']]=$row;
             }
         }
 
        ///////////////////////////////////

        $map=$this->odlFunc->getOTmanut($this->info['marca']);

        if ($map['result']) {
            $fid=$this->galileo->preFetchPiattaforma($map['piattaforma'],$map['result']);

            while ($row=$this->galileo->getFetchPiattaforma($map['piattaforma'],$fid)) {
                $this->codiceForm['manut'][$row['codice']]=$row;
            }
        }
    }

    function splitCodice() {

        $temp=explode("_",$this->gruppo['codice']);
        $temp2=explode("_",$this->gruppo['descrizione']);

        $this->codiceForm['split']['marca']=substr($temp[0],0,2);
        $this->codiceForm['split']['alim']=substr($temp[0],2,1);
        $this->codiceForm['split']['traz']=substr($temp[0],3,1);
        $this->codiceForm['split']['cambio']=substr($temp[0],4,1);
        $this->codiceForm['split']['manut']=substr($temp[0],5,2);
        $this->codiceForm['split']['suffix']=$temp2[1];

    }

    function buildMM() {

        $this->build();

        if ($this->info['marca']=="" || $this->info['modello']=="") return;

        //identifica il record OVERTURE specifico per marca e modello
        $map=$this->odlFunc->getOTGruppoMM($this->info['marca'],$this->info['modello']);

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

        //#############################################
        //carica i pacchetti relativi a marca - modello (packMM)
        //#############################################

        $this->calcolaActual();
    }

    function buildID($piano) {

        $this->build();
        //$this->buildCodiceForm();

        if (!$piano || $piano=="") return;

        $temp=explode("_",$piano);

        //identifica il record OVERTURE specifico per marca e modello
        $map=$this->odlFunc->getOTGruppoID($temp[0],$temp[1]);

        if ($map['result']) {
            $fid=$this->galileo->preFetchPiattaforma($map['piattaforma'],$map['result']);

            while ($row=$this->galileo->getFetchPiattaforma($map['piattaforma'],$fid)) {
                $this->gruppo['codice']=$row['codice'].'_'.$row['indice'];

                if (isset($this->gruppi[$this->gruppo['codice']])) {
                    $this->gruppo['descrizione']=$this->gruppi[$this->gruppo['codice']]['descrizione'];
                    $this->gruppo['oggetti']=json_decode($this->gruppi[$this->gruppo['codice']]['oggetti'],true);
                }
            }
        }

        $this->calcolaActual();

        $this->splitCodice();

    }

    function buildDescrizione($codice,$suffix) {

        $marca=substr($codice,0,2);
        $alim=substr($codice,2,1);
        $traz=substr($codice,3,1);
        $cambio=substr($codice,4,1);
        $manut=substr($codice,5,2);

        return $marca.' '.$this->codiceForm['alim'][$alim]['descrizione'].' '.$this->codiceForm['traz'][$traz]['descrizione'].' '.$this->codiceForm['cambio'][$cambio]['descrizione'].' '.$this->codiceForm['manut'][$manut]['descrizione'].' _'.$suffix;
    }

    function calcolaActual() {

        //############################################
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

        if ($this->gruppo['oggettiModello']) {

            foreach ($this->gruppo['oggettiModello'] as $oggetto=>$g) {

                if (array_key_exists($oggetto,$this->base) && array_key_exists($oggetto,$this->oggettiDefault)) {

                    if(!isset($g['flag_mov'])) $g['flag_mov']='ok';

                    //non aggiorna DEL e se in actual non esiste
                    //if ($g['flag_mov']!='del' || array_key_exists($oggetto,$this->gruppo['oggettiActual'])) {
                    if (array_key_exists($oggetto,$this->gruppo['oggettiActual'])) {

                        if ($g['flag_mov']=='del') {
                            $g=$this->gruppo['oggettiActual'][$oggetto];
                            $g['flag_mov']='del';
                        }

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
        }

        if ($this->gruppo['oggettiTelaio']) {

            foreach ($this->gruppo['oggettiTelaio'] as $oggetto=>$g) {

                if (array_key_exists($oggetto,$this->base) && array_key_exists($oggetto,$this->oggettiDefault)) {

                    if(!isset($g['flag_mov'])) $g['flag_mov']='ok';

                    //non aggiorna DEL e se in actual non esiste
                    //if ($g['flag_mov']!='del' || array_key_exists($oggetto,$this->gruppo['oggettiActual'])) {
                    if (array_key_exists($oggetto,$this->gruppo['oggettiActual'])) {

                        if ($g['flag_mov']=='del') {
                            $g=$this->gruppo['oggettiActual'][$oggetto];
                            $g['flag_mov']='del';
                        }

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
        

        //#########################################
        if (isset($this->gruppo['oggettiActual'])) {

            foreach ($this->base as $codice=>$b) {

                if (isset($this->gruppo['oggettiActual'][$codice])) {

                    if (array_key_exists($codice,$this->packMM)) {
                        $this->gruppo['oggettiActual'][$codice]['pacchetto']=$this->packMM[$codice];
                    }
                    elseif (array_key_exists($codice,$this->packMarca)) {
                        $this->gruppo['oggettiActual'][$codice]['pacchetto']=$this->packMarca[$codice];
                    }
                    else {
                        $this->gruppo['oggettiActual'][$codice]['pacchetto']=array(
                            "codice"=>$codice,
                            "oggetto"=>array(
                                "M"=>array(),
                                "R"=>array(),
                                "V"=>array()
                            )
                        );
                    }

                }
            }
        }

    }

    function getCodice() {
        return $this->gruppo['codice'];
    }

    function checkCodice() {
        //verificsa se esiste il codice e fa parte dei codici validi
        if ($this->gruppo['codice']!="" && isset($this->gruppi[$this->gruppo['codice']])) return true;
        else return false;
    }

    function getDescrizione() {
        return $this->gruppo['descrizione'];
    }

    function checkActual($codice) {
        return isset($this->gruppo['oggettiActual'][$codice]);
    }

    function getActual() {
        return $this->gruppo['oggettiActual'];
    }

    function getBase() {
        return $this->base;
    }

    function drawActual($eventi) {

        $this->gruppo['eventi']=$eventi;

        if (isset($this->gruppo['oggettiActual'])) {

            foreach ($this->base as $codice=>$b) {

                if (isset($this->gruppo['oggettiActual'][$codice])) {

                    echo '<div style="position:relative;margin-top:6px;margin-bottom:6px;width:100%;">';

                        $this->drawOggetto($codice,$this->gruppo['oggettiActual'][$codice]);

                    echo '</div>';

                }
            }
        }
    }

    function drawPack() {

        if (isset($this->gruppo['oggettiActual'])) {

            foreach ($this->base as $codice=>$b) {

                if (isset($this->gruppo['oggettiActual'][$codice])) {

                    echo '<div id="storico_pacchetto_'.$codice.'" style="position:relative;margin-top:6px;margin-bottom:6px;width:95%;border:1px solid black;padding:3px;box-sizing:border-box;display:none;">';

                        echo '<div style="position:relative;background-color:#b6efdc;">';
                            echo '<div style="position:relative;display:inline-block;width:70%;font-weight:bold;" >'.$codice.' - '.$this->base[$codice]['descrizione'].'</div>';
                        echo '</div>';

                        if (isset($this->gruppo['oggettiActual'][$codice]['pacchetto'])) {

                            echo '<div style="position:relative;margin-top:5px;">';
                                echo '<div style="position:relative;font-size:0.9em;font-weight:bold;">Manodopera:</div>';

                                foreach ($this->gruppo['oggettiActual'][$codice]['pacchetto']['oggetto']['M'] as $m) {

                                    echo '<div style="position:relative;">';

                                        echo '<div style="position:relative;display:inline-block;vertical-align:top;width:10%;text-align:center;">';
                                            echo '<input type="checkbox" />';
                                        echo '</div>';
                                        echo '<div style="position:relative;display:inline-block;vertical-align:top;width:30%;text-align:left;">';
                                            if ($m['codice']!="") {
                                                echo '<div style="position:relative;font-weight:bold;font-size:0.9em;" >'.$m['codice'].'</div>';
                                            }
                                            echo '<div style="position:relative;font-size:0.9em;" >'.$m['alias'].'</div>';
                                        echo '</div>';
                                        echo '<div style="position:relative;display:inline-block;vertical-align:top;width:20%;text-align:right;">';
                                            echo '<div style="position:relative;" >'.number_format($m['ut'],2,',','').'</div>';
                                        echo '</div>';
                                        echo '<div style="position:relative;display:inline-block;vertical-align:top;width:20%;text-align:right;">';
                                            echo '<div style="position:relative;" >'.($m['listino']!=""?number_format($m['listino'],2,',',''):"").'</div>';
                                        echo '</div>';
                                        echo '<div style="position:relative;display:inline-block;vertical-align:top;width:15%;text-align:center;">';
                                            if ($m['opt']==1) {
                                                echo '<div style="position:relative;color:#999999;" >opt</div>';
                                            }
                                        echo '</div>';

                                    echo '</div>';
                                }

                            echo '</div>';

                            echo '<div style="position:relative;margin-top:5px;">';
                                echo '<div style="position:relative;font-size:0.9em;font-weight:bold;">Ricambi:</div>';

                                foreach ($this->gruppo['oggettiActual'][$codice]['pacchetto']['oggetto']['R'] as $m) {

                                    echo '<div style="position:relative;">';

                                        echo '<div style="position:relative;display:inline-block;vertical-align:top;width:10%;text-align:center;">';
                                            echo '<input type="checkbox" />';
                                        echo '</div>';
                                        echo '<div style="position:relative;display:inline-block;vertical-align:top;width:30%;text-align:left;">';
                                            if ($m['codice']!="") {
                                                echo '<div style="position:relative;font-weight:bold;font-size:0.9em;" >'.$m['pre'].' - '.$m['codice'].'</div>';
                                            }
                                            echo '<div style="position:relative;font-size:0.9em;" >'.$m['alias'].'</div>';
                                        echo '</div>';
                                        echo '<div style="position:relative;display:inline-block;vertical-align:top;width:20%;text-align:right;">';
                                            echo '<div style="position:relative;" >'.number_format($m['qta'],1,',','').'</div>';
                                        echo '</div>';
                                        echo '<div style="position:relative;display:inline-block;vertical-align:top;width:20%;text-align:right;">';
                                            echo '<div style="position:relative;" >'.($m['listino']!=""?number_format($m['listino'],2,',',''):"").'</div>';
                                        echo '</div>';
                                        echo '<div style="position:relative;display:inline-block;vertical-align:top;width:15%;text-align:center;">';
                                            if ($m['opt']==1) {
                                                echo '<div style="position:relative;color:#999999;" >opt</div>';
                                            }
                                        echo '</div>';

                                    echo '</div>';
                                }
                            echo '</div>';

                            echo '<div style="position:relative;margin-top:5px;">';
                                echo '<div style="position:relative;font-size:0.9em;font-weight:bold;">Oneri:</div>';

                                foreach ($this->gruppo['oggettiActual'][$codice]['pacchetto']['oggetto']['V'] as $m) {

                                    echo '<div style="position:relative;">';

                                        echo '<div style="position:relative;display:inline-block;vertical-align:top;width:10%;text-align:center;">';
                                            echo '<input type="checkbox" />';
                                        echo '</div>';
                                        echo '<div style="position:relative;display:inline-block;vertical-align:top;width:30%;text-align:left;">';
                                            if ($m['codice']!="") {
                                                echo '<div style="position:relative;font-weight:bold;font-size:0.9em;" >'.$m['codice'].'</div>';
                                            }
                                            echo '<div style="position:relative;font-size:0.9em;" >'.$m['alias'].'</div>';
                                        echo '</div>';
                                        echo '<div style="position:relative;display:inline-block;vertical-align:top;width:20%;text-align:right;">';
                                            echo '<div style="position:relative;" >'.number_format($m['qta'],1,',','').'</div>';
                                        echo '</div>';
                                        echo '<div style="position:relative;display:inline-block;vertical-align:top;width:20%;text-align:right;">';
                                            echo '<div style="position:relative;" >'.($m['listino']!=""?number_format($m['listino'],2,',',''):"").'</div>';
                                        echo '</div>';
                                        echo '<div style="position:relative;display:inline-block;vertical-align:top;width:15%;text-align:center;">';
                                            if ($m['opt']==1) {
                                                echo '<div style="position:relative;color:#999999;" >opt</div>';
                                            }
                                        echo '</div>';

                                    echo '</div>';
                                }
                            echo '</div>';
                        }

                        //echo json_encode($this->gruppo['oggettiActual'][$codice]['pacchetto']);

                    echo '</div>';

                }
            }
        }
    }

    function drawOggetto($codice,$c) {

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
                        echo '<div style="position:relative;display:inline-block;width:10%;">';
                            echo '<img id="storico_obj_sel_'.$codice.'" style="width:18px;height:18px;margin-left:3px;" data-codice="'.$codice.'" data-std="no" data-man="no" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/storico/img/no.png" onclick="window._nebulaStorico.clickEvent(this);"/>';
                        echo '</div>';
                        echo '<div style="position:relative;display:inline-block;width:65%;font-size:1.0em;">'.($this->base[$codice]['main']==1?'*':'').'('.$codice.') '.$this->base[$codice]['descrizione'].'</div>';
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
                            foreach ( $this->gruppo['eventi'][$codice] as $e) {
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
    }

    function drawElencoGruppi() {

        $colori=array('#dedbdb','#edefc9');
        $col=1;
        $index="";

        /*echo '<input id="storico_piano_marca_hidden" type="hidden" value="'.$this->info['marca'].'" />';
        echo '<input id="storico_piano_modello_hidden" type="hidden" value="'.$this->info['modello'].'" />';
        echo '<input id="storico_piano_telaio_hidden" type="hidden" value="'.$this->info['telaio'].'" />';*/

        echo '<div style="position:relative;margin-top:4px;margin-bottom:4px;padding:2px;width:90%;">';
            echo '<button style="border:1px solid black;border-radius:5px;background-color:aquamarine;font-size:1.1em;font-weight:bold;width:100%;" onclick="window._nebulaStorico.openNuovo(\'\',\'\');">NUOVO</button>';
        echo '</div>';

        foreach($this->gruppi as $codice=>$g) {

            if (substr($codice,0,3)!=$index) {
                $col=$col==1?0:1;
                $index=substr($codice,0,3);
            }

            echo '<div style="position:relative;margin-top:4px;margin-bottom:4px;border:1px solid black;border-radius:5px;background-color:'.$colori[$col].';font-size:1.1em;font-weight:bold;padding:2px;width:90%;">';
                echo '<input name="sto_gruppi_radio" type="radio" value="'.$codice.'" ';
                    if ($codice==$this->gruppo['codice']) echo 'checked';
                echo ' onclick="window._nebulaStorico.setPiano();" />';
                echo '<span style="margin-left:15px;">'.$g['descrizione'].'</span>';
            echo '</div>';
        }

    }

    function drawHeadPiano($flag,$same) {

        echo '<div style="position:relative;">';

            echo '<div style="position:relative;display:inline-block;width:10%;vertical-align:top;" >';
                echo '<div style="font-size:0.9em;font-weight:bold;">Marca</div>';
                //echo '<div style="width:100%;text-align:center;">'.$this->codiceForm['marche'][$this->codiceForm['split']['marca']]['marca'].'</div>';
                echo '<div style="width:100%;text-align:center;">'.$this->codiceForm['split']['marca'].'</div>';
            echo '</div>';

            echo '<div style="position:relative;display:inline-block;width:15%;vertical-align:top;" >';
                echo '<div style="font-size:0.9em;font-weight:bold;">Alimentazione</div>';
                echo '<div style="width:100%;text-align:left;">';
                    echo '<select ';
                        if ($flag) echo 'disabled';
                    echo ' >';
                        foreach ($this->codiceForm['alim'] as $k=>$o) {
                            echo '<option value="'.$k.'" ';
                                if ($k==$this->codiceForm['split']['alim']) echo 'selected';
                            echo ' >'.$o['selezione'].'</option>';
                        }
                    echo '</select>';
                echo '</div>';
            echo '</div>';

            echo '<div style="position:relative;display:inline-block;width:15%;vertical-align:top;" >';
                echo '<div style="font-size:0.9em;font-weight:bold;">Trazione</div>';
                echo '<div style="width:100%;text-align:left;">';
                    echo '<select ';
                        if ($flag) echo 'disabled';
                    echo ' >';
                        foreach ($this->codiceForm['traz'] as $k=>$o) {
                            echo '<option value="'.$k.'" ';
                                if ($k==$this->codiceForm['split']['traz']) echo 'selected';
                            echo ' >'.$o['selezione'].'</option>';
                        }
                    echo '</select>';
                echo '</div>';
            echo '</div>';

            echo '<div style="position:relative;display:inline-block;width:15%;vertical-align:top;" >';
                echo '<div style="font-size:0.9em;font-weight:bold;">Cambio</div>';
                echo '<div style="width:100%;text-align:left;">';
                    echo '<select ';
                        if ($flag) echo 'disabled';
                    echo ' >';
                        foreach ($this->codiceForm['cambio'] as $k=>$o) {
                            echo '<option value="'.$k.'" ';
                                if ($k==$this->codiceForm['split']['cambio']) echo 'selected';
                            echo ' >'.$o['selezione'].'</option>';
                        }
                    echo '</select>';
                echo '</div>';
            echo '</div>';

            echo '<div style="position:relative;display:inline-block;width:15%;vertical-align:top;" >';
                echo '<div style="font-size:0.9em;font-weight:bold;">Manutenzione</div>';
                echo '<div style="width:100%;text-align:left;">';
                    echo '<select ';
                        if ($flag) echo 'disabled';
                    echo '>';
                        foreach ($this->codiceForm['manut'] as $k=>$o) {
                            echo '<option value="'.$k.'" ';
                                if ($k==$this->codiceForm['split']['manut']) echo 'selected';
                            echo ' >'.$o['selezione'].'</option>';
                        }
                    echo '</select>';
                echo '</div>';
            echo '</div>';

            echo '<div style="position:relative;display:inline-block;width:20%;vertical-align:top;" >';
                echo '<div style="font-size:0.9em;font-weight:bold;">Suffisso</div>';
                echo '<div style="width:100%;text-align:left;">';
                    echo '<input style="width:80%;" type="text" maxlength="8" value="'.$this->codiceForm['split']['suffix'].'" ';
                        if ($flag) echo 'disabled';
                    echo ' />';
                echo '</div>';
            echo '</div>';

            echo '<div style="position:relative;display:inline-block;width:10%;vertical-align:top;text-align:center;top:15px;" >';
                if (!$same) echo '<button onclick="window._nebulaStorico.linkPiano();">Cambia</button>';
            echo '</div>';

        echo '</div>';
    }

    function drawHeadNuovo($copia,$titolo) {

        echo '<div id="storico_gruppo_nuovo_titolo" style="position:relative;font-size:1.1em;font-weight:bold;margin-bottom:20px;" >';
            if ($copia!="") {
                echo 'Nuovo gruppo da: '.$titolo;
            }
            else echo 'Nuovo gruppo:';
        echo '</div>';

        echo '<input id="storico_gruppo_nuovo_copia" type="hidden" value="'.$copia.'" />';
        echo '<input id="storico_gruppo_nuovo_marcaDms" type="hidden" value="'.$this->info['marca'].'" />';

        echo '<div style="position:relative;">';

            echo '<div style="position:relative;display:inline-block;width:10%;vertical-align:top;" >';
                echo '<div style="font-size:0.9em;font-weight:bold;">Marca</div>';
                echo '<div style="width:100%;text-align:center;">'.$this->codiceForm['marche'][$this->info['marca']]['marca'].'</div>';
                echo '<input id="storico_gruppo_nuovo_marca" type="hidden" value="'.$this->codiceForm['marche'][$this->info['marca']]['marca'].'" />';
            echo '</div>';

            echo '<div style="position:relative;display:inline-block;width:15%;vertical-align:top;" >';
                echo '<div style="font-size:0.9em;font-weight:bold;">Alimentazione</div>';
                echo '<div style="width:100%;text-align:left;">';
                    echo '<select id="storico_gruppo_nuovo_alim">';
                        foreach ($this->codiceForm['alim'] as $k=>$o) {
                            echo '<option value="'.$k.'" >'.$o['selezione'].'</option>';
                        }
                    echo '</select>';
                echo '</div>';
            echo '</div>';

            echo '<div style="position:relative;display:inline-block;width:15%;vertical-align:top;" >';
                echo '<div style="font-size:0.9em;font-weight:bold;">Trazione</div>';
                echo '<div style="width:100%;text-align:left;">';
                    echo '<select id="storico_gruppo_nuovo_traz" >';
                        foreach ($this->codiceForm['traz'] as $k=>$o) {
                            echo '<option value="'.$k.'" >'.$o['selezione'].'</option>';
                        }
                    echo '</select>';
                echo '</div>';
            echo '</div>';

            echo '<div style="position:relative;display:inline-block;width:15%;vertical-align:top;" >';
                echo '<div style="font-size:0.9em;font-weight:bold;">Cambio</div>';
                echo '<div style="width:100%;text-align:left;">';
                    echo '<select id="storico_gruppo_nuovo_cambio" >';
                        foreach ($this->codiceForm['cambio'] as $k=>$o) {
                            echo '<option value="'.$k.'" >'.$o['selezione'].'</option>';
                        }
                    echo '</select>';
                echo '</div>';
            echo '</div>';

            echo '<div style="position:relative;display:inline-block;width:15%;vertical-align:top;" >';
                echo '<div style="font-size:0.9em;font-weight:bold;">Manutenzione</div>';
                echo '<div style="width:100%;text-align:left;">';
                    echo '<select id="storico_gruppo_nuovo_manut" >';
                        foreach ($this->codiceForm['manut'] as $k=>$o) {
                            echo '<option value="'.$k.'" >'.$o['selezione'].'</option>';
                        }
                    echo '</select>';
                echo '</div>';
            echo '</div>';

            echo '<div style="position:relative;display:inline-block;width:20%;vertical-align:top;" >';
                echo '<div style="font-size:0.9em;font-weight:bold;">Suffisso</div>';
                echo '<div style="width:100%;text-align:left;">';
                    echo '<input id="storico_gruppo_nuovo_suffix" style="width:80%;" type="text" maxlength="8" value="" />';
                echo '</div>';
            echo '</div>';

            echo '<div style="position:relative;display:inline-block;width:10%;vertical-align:top;text-align:center;top:15px;" >';
                echo '<button onclick="window._nebulaStorico.nuovoPiano();">Crea nuovo</button>';
            echo '</div>';

        echo '</div>';
    }

    function drawPiano($piano,$actual) {
        
        echo '<div id="storico_gruppo_main" style="position:relative;width:100%;height:100%;">';

            echo '<div style="position:relative;height:15%;padding:5px;box-sizing:border-box;" >';

                if ($piano && $piano!="") {

                    //$same=($piano==$this->gruppo['codice'])?true:false;
                    $same=($piano==$actual)?true:false;

                    echo '<div style="font-weight:bold;">';
                        echo $this->gruppo['codice'].' ( '.$this->gruppo['descrizione'].' )';
                        echo '<button style="margin-left:30px;" onclick="window._nebulaStorico.openNuovo(\''.$this->gruppo['codice'].'\',\''.$this->gruppo['descrizione'].'\');" >Copia</button>';
                    echo '</div>';

                    echo '<div style="margin-top:5px;">';

                        $this->drawHeadPiano(true,$same);

                    echo '</div>';
                }
                else {
                    echo 'Nessun Piano Selezionato';
                }

            echo '</div>';

            if ($piano && $piano!="") {

                //echo '<div>'.json_encode($this->gruppi).'</div>';
                //echo '<div>'.json_encode($this->gruppo).'</div>';

                echo '<div style="position:relative;height:85%;" >';

                    //Divo::divoInit();

                    $divo=new Divo('storicoEdit','6%','93%',true);

                    $divo->setBk('#ecf1a5');

                    $css=array(
                        "font-weight"=>"bold",
                        "font-size"=>"1.3em",
                        "margin-left"=>"15px",
                        "margin-top"=>"-1px"
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

                    ob_start();
                    $this->drawActualEdit();

                    $divo->add_div('Attuale','black',0,"",ob_get_clean(),0,$css);

                    ob_start();
                    $this->drawBaseEdit();

                    $divo->add_div('Base','black',0,"",ob_get_clean(),0,$css);

                    ob_start();
                    $this->drawModelloEdit();

                    $divo->add_div('Modello','black',0,"",ob_get_clean(),0,$css);

                    ob_start();
                    $this->drawTelaioEdit();

                    $divo->add_div('Telaio','black',0,"",ob_get_clean(),0,$css);

                    ob_start();
                    $this->drawAltro();

                    $divo->add_div('Altro','black',0,"",ob_get_clean(),0,$css);

                    $divo->build();

                    $divo->draw();

                echo '</div>';
            }

        echo '</div>';

        $this->drawNuovo();
        
    }

    function drawNuovo() {

        echo '<div id="storico_gruppo_nuovo" style="position:relative;width:100%;height:100%;display:none;">';
            
            echo '<div style="position:relative;height:10%;text-align:right;" >';
                echo '<img style="width:30px;height:30px;cursor:pointer;margin-right:20px;margin-top:15px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/storico/img/back.png" onclick="window._nebulaStorico.closeNuovo();" />';
            echo '</div>';

            echo '<div>';
                $this->drawHeadNuovo('','');
            echo '</div>';

        echo '</div>';

    }

    function drawOggettoEdit($oggetto,$o) {

        echo '<div style="margin-top:5px;margin-bottom:10px;width:95%;border-top:2px solid #b86868;">';

            echo '<div style="font-weight:bold;background-color:'.($o['stat']==1?'#d5d2ce':'antiquewhite').';">';
                echo $oggetto.' - '.$this->base[$oggetto]['descrizione'];
                if ($o['stat']==1) echo ' (statistico)';
                echo ' - '.$o['base'];
            echo '</div>';

            echo '<table class="storico_edit_event_tab" style="width:100%;border-collapse:collapse;text-align:center;font-size:0.9em;">';
                echo '<colgroup>';
                    echo '<col span="1" style="width:5%;" />';
                    echo '<col span="6" style="width:15%;" />';
                    echo '<col span="1" style="width:5%;" />';
                echo '</colgroup>';
                echo '<thead>';
                    echo '<tr>';
                        echo '<th></th>';
                        echo '<th>delta</th>';
                        echo '<th>min</th>';
                        echo '<th>max</th>';
                        echo '<th>step</th>';
                        echo '<th>top</th>';
                        echo '<th>first</th>';
                        echo '<th>pcx</th>';
                    echo '</tr>';
                echo '</thead>';
                echo '<tbody style="border:1px solid black;">';
                    echo '<tr>';
                        echo '<td>T</td>';
                        echo '<td>'.$o['dt'].'</td>';
                        echo '<td>'.$o['mint'].'</td>';
                        echo '<td>'.$o['maxt'].'</td>';
                        echo '<td>'.$o['stet'].'</td>';
                        echo '<td>'.$o['topt'].'</td>';
                        echo '<td>'.$o['first_t'].'</td>';
                        echo '<td rowspan="2">';
                            echo '<input type="checkbox" '.($o['pcx']==1?'checked':'').' disabled/>';
                        echo '</td>';
                    echo '</tr>';
                    echo '<tr>';
                        echo '<td>Km</td>';
                        echo '<td>'.$o['dkm'].'</td>';
                        echo '<td>'.$o['minkm'].'</td>';
                        echo '<td>'.$o['maxkm'].'</td>';
                        echo '<td>'.$o['stekm'].'</td>';
                        echo '<td>'.$o['topkm'].'</td>';
                        echo '<td>'.$o['first_km'].'</td>';
                    echo '</tr>';
                echo '</tbody>';
            echo '</table>';

        echo '</div>';

    }

    function drawOggettoEditForm($oggetto) {

        if (array_key_exists($oggetto,$this->gruppo['oggettiActual'])) {
            $o=$this->gruppo['oggettiActual'][$oggetto];
            $selected=true;
        }
        else {
            $o=$this->oggettiDefault[$oggetto];
            $selected=false;
        }

        echo '<div id="storicoGruppoForm_evento_div_'.$oggetto.'" style="margin-top:5px;margin-bottom:10px;width:95%;border-top:2px solid #35a414;';
            if (!$selected) echo 'background-color:#dddddd;';
        echo '">';

            //echo '<div style="font-weight:bold;background-color:'.($o['stat']==1?'#d5d2ce':'transparent').';">';
            echo '<div style="font-weight:bold;background-color:'.($o['stat']==1?'transparent':'transparent').';">';
                echo '<input id="storicoGruppoForm_evento_'.$oggetto.'" style="margin-right:10px;" type="checkbox" value="'.$oggetto.'" ';
                    if ($selected) echo ' checked ';
                echo ' onclick="window._nebulaStorico.switchEventoForm(\''.$oggetto.'\');"/>';
                echo $oggetto.' - '.$this->base[$oggetto]['descrizione'];
                if ($o['stat']==1) echo ' (statistico)';
            echo '</div>';

            echo '<table class="storico_edit_event_tab" style="width:100%;border-collapse:collapse;text-align:center;font-size:0.9em;">';
                echo '<colgroup>';
                    echo '<col span="1" style="width:5%;" />';
                    echo '<col span="6" style="width:15%;" />';
                    echo '<col span="1" style="width:5%;" />';
                echo '</colgroup>';
                echo '<thead>';
                    echo '<tr>';
                        echo '<th></th>';
                        echo '<th>delta</th>';
                        echo '<th>min</th>';
                        echo '<th>max</th>';
                        echo '<th>step</th>';
                        echo '<th>top</th>';
                        echo '<th>first</th>';
                        echo '<th>pcx</th>';
                    echo '</tr>';
                echo '</thead>';
                echo '<tbody style="border:1px solid black;">';
                    echo '<tr>';
                        echo '<td>T</td>';
                        echo '<td>';
                            echo '<input id="storicoGruppoForm_campo_'.$oggetto.'_dt" style="width:96%;text-align:center;" type="text" value="'.$o['dt'].'" ';
                                if (!$selected) echo 'disabled';
                            echo ' />';
                            echo '<div id="storicoGruppoForm_campo_error_'.$oggetto.'_dt" style="position:relative;width:100%;color:red;text-align:center;" ></div>';
                        echo '</td>';
                        echo '<td>'.$o['mint'].'</td>';
                        echo '<td>'.$o['maxt'].'</td>';
                        echo '<td>'.$o['stet'].'</td>';
                        echo '<td>';
                            echo '<input id="storicoGruppoForm_campo_'.$oggetto.'_topt" style="width:96%;text-align:center;" type="text" value="'.$o['topt'].'" ';
                                if (!$selected) echo 'disabled';
                            echo ' />';
                            echo '<div id="storicoGruppoForm_campo_error_'.$oggetto.'_topt" style="position:relative;width:100%;color:red;text-align:center;" ></div>';
                        echo '</td>';
                        echo '<td>';
                            echo '<input id="storicoGruppoForm_campo_'.$oggetto.'_first_t" style="width:96%;text-align:center;" type="text" value="'.$o['first_t'].'" ';
                                if (!$selected) echo 'disabled';
                            echo ' />';
                            echo '<div id="storicoGruppoForm_campo_error_'.$oggetto.'_first_t" style="position:relative;width:100%;color:red;text-align:center;" ></div>';
                        echo '</td>';
                        echo '<td rowspan="2">';
                            echo '<input id="storicoGruppoForm_campo_'.$oggetto.'_pcx" type="checkbox" '.($o['pcx']==1?'checked':'').' ';
                                if (!$selected) echo 'disabled';
                            echo ' />';
                        echo '</td>';
                    echo '</tr>';
                    echo '<tr>';
                        echo '<td>Km</td>';
                        echo '<td>';
                            echo '<input id="storicoGruppoForm_campo_'.$oggetto.'_dkm" style="width:96%;text-align:center;" type="text" value="'.$o['dkm'].'" ';
                                if (!$selected) echo 'disabled';
                            echo ' />';
                            echo '<div id="storicoGruppoForm_campo_error_'.$oggetto.'_dkm" style="position:relative;width:100%;color:red;text-align:center;" ></div>';
                        echo '</td>';
                        echo '<td>'.$o['minkm'].'</td>';
                        echo '<td>'.$o['maxkm'].'</td>';
                        echo '<td>'.$o['stekm'].'</td>';
                        echo '<td>';
                            echo '<input id="storicoGruppoForm_campo_'.$oggetto.'_topkm" style="width:96%;text-align:center;" type="text" value="'.$o['topkm'].'" ';
                                if (!$selected) echo 'disabled';
                            echo ' />';
                            echo '<div id="storicoGruppoForm_campo_error_'.$oggetto.'_topkm" style="position:relative;width:100%;color:red;text-align:center;" ></div>';
                        echo '</td>';
                        echo '<td>';
                            echo '<input id="storicoGruppoForm_campo_'.$oggetto.'_first_km" style="width:96%;text-align:center;" type="text" value="'.$o['first_km'].'" ';
                                if (!$selected) echo 'disabled';
                            echo ' />';
                            echo '<div id="storicoGruppoForm_campo_error_'.$oggetto.'_first_km" style="position:relative;width:100%;color:red;text-align:center;" ></div>';
                        echo '</td>';
                    echo '</tr>';
                echo '</tbody>';
            echo '</table>';

        echo '</div>';

    }

    function drawOggettoEditSpecifico($oggetto,$tipo,$div) {

        if ($this->gruppo[$tipo] && array_key_exists($oggetto,$this->gruppo[$tipo])) {
            if ($this->gruppo[$tipo][$oggetto]['flag_mov']=='ok') {
                $o=$this->gruppo[$tipo][$oggetto];
            }
            else {
                $o=$this->gruppo['oggetti'][$oggetto];
                $o['flag_mov']='del';
            }
            $selected=true;
        }
        else {
            $o=$this->gruppo['oggetti'][$oggetto];
            $selected=false;
        }

        echo '<div style="margin-top:5px;margin-bottom:10px;width:95%;border-top:2px solid #35a414;';
            //if (!$selected) echo 'background-color:#dddddd;';
        echo '">';

            //echo '<div style="font-weight:bold;background-color:'.($o['stat']==1?'#d5d2ce':'transparent').';">';
            echo '<div style="font-weight:bold;background-color:'.($this->oggettiDefault[$oggetto]['stat']==1?'transparent':'transparent').';">';
                echo '<input id="storicoGruppo'.$div.'_evento_'.$oggetto.'" style="margin-right:10px;" type="checkbox" value="'.$oggetto.'" ';
                    if ($selected) echo ' checked ';
                echo ' onclick="window._nebulaStorico.switchEventoTipo(\''.$div.'\',\''.$oggetto.'\');"/>';
                echo $oggetto.' - '.$this->base[$oggetto]['descrizione'];
                if ($this->oggettiDefault[$oggetto]['stat']==1) echo ' (statistico)';
            echo '</div>';

            echo '<div id="storicoGruppo'.$div.'_evento_div_'.$oggetto.'" style="position:relative;';
                if (!$selected) echo 'display:none;';
            echo '">';

                echo '<div style="text-align:right;" >';
                    echo '<input id="storicoGruppo'.$div.'_escludi_'.$oggetto.'" type="checkbox" ';
                        if (isset($o['flag_mov']) && $o['flag_mov']=='del') echo 'checked';
                    echo ' />';
                    echo '<span style="color:red;margin-left:10px;font-weight:bold;">ESCLUDI</span>';
                echo '</div>';

                echo '<table class="storico_edit_event_tab" style="width:100%;border-collapse:collapse;text-align:center;font-size:0.9em;">';
                    echo '<colgroup>';
                        echo '<col span="1" style="width:5%;" />';
                        echo '<col span="6" style="width:15%;" />';
                        echo '<col span="1" style="width:5%;" />';
                    echo '</colgroup>';
                    echo '<thead>';
                        echo '<tr>';
                            echo '<th></th>';
                            echo '<th>delta</th>';
                            echo '<th>min</th>';
                            echo '<th>max</th>';
                            echo '<th>step</th>';
                            echo '<th>top</th>';
                            echo '<th>first</th>';
                            echo '<th>pcx</th>';
                        echo '</tr>';
                    echo '</thead>';
                    echo '<tbody style="border:1px solid black;">';
                        echo '<tr>';
                            echo '<td>T</td>';
                            echo '<td>';
                                echo '<input id="storicoGruppo'.$div.'_campo_'.$oggetto.'_dt" style="width:96%;text-align:center;" type="text" value="'.$o['dt'].'" ';
                                    //if (!$selected) echo 'disabled';
                                echo ' />';
                                echo '<div id="storicoGruppo'.$div.'_campo_error_'.$oggetto.'_dt" style="position:relative;width:100%;color:red;text-align:center;" ></div>';
                            echo '</td>';
                            echo '<td>'.$o['mint'].'</td>';
                            echo '<td>'.$o['maxt'].'</td>';
                            echo '<td>'.$o['stet'].'</td>';
                            echo '<td>';
                                echo '<input id="storicoGruppo'.$div.'_campo_'.$oggetto.'_topt" style="width:96%;text-align:center;" type="text" value="'.$o['topt'].'" ';
                                    //if (!$selected) echo 'disabled';
                                echo ' />';
                                echo '<div id="storicoGruppo'.$div.'_campo_error_'.$oggetto.'_topt" style="position:relative;width:100%;color:red;text-align:center;" ></div>';
                            echo '</td>';
                            echo '<td>';
                                echo '<input id="storicoGruppo'.$div.'_campo_'.$oggetto.'_first_t" style="width:96%;text-align:center;" type="text" value="'.$o['first_t'].'" ';
                                    //if (!$selected) echo 'disabled';
                                echo ' />';
                                echo '<div id="storicoGruppo'.$div.'_campo_error_'.$oggetto.'_first_t" style="position:relative;width:100%;color:red;text-align:center;" ></div>';
                            echo '</td>';
                            echo '<td rowspan="2">';
                                echo '<input id="storicoGruppo'.$div.'_campo_'.$oggetto.'_pcx" type="checkbox" '.($o['pcx']==1?'checked':'').' ';
                                    //if (!$selected) echo 'disabled';
                                echo ' />';
                            echo '</td>';
                        echo '</tr>';
                        echo '<tr>';
                            echo '<td>Km</td>';
                            echo '<td>';
                                echo '<input id="storicoGruppo'.$div.'_campo_'.$oggetto.'_dkm" style="width:96%;text-align:center;" type="text" value="'.$o['dkm'].'" ';
                                    //if (!$selected) echo 'disabled';
                                echo ' />';
                                echo '<div id="storicoGruppo'.$div.'_campo_error_'.$oggetto.'_dkm" style="position:relative;width:100%;color:red;text-align:center;" ></div>';
                            echo '</td>';
                            echo '<td>'.$o['minkm'].'</td>';
                            echo '<td>'.$o['maxkm'].'</td>';
                            echo '<td>'.$o['stekm'].'</td>';
                            echo '<td>';
                                echo '<input id="storicoGruppo'.$div.'_campo_'.$oggetto.'_topkm" style="width:96%;text-align:center;" type="text" value="'.$o['topkm'].'" ';
                                    //if (!$selected) echo 'disabled';
                                echo ' />';
                                echo '<div id="storicoGruppo'.$div.'_campo_error_'.$oggetto.'_topkm" style="position:relative;width:100%;color:red;text-align:center;" ></div>';
                            echo '</td>';
                            echo '<td>';
                                echo '<input id="storicoGruppo'.$div.'_campo_'.$oggetto.'_first_km" style="width:96%;text-align:center;" type="text" value="'.$o['first_km'].'" ';
                                    //if (!$selected) echo 'disabled';
                                echo ' />';
                                echo '<div id="storicoGruppo'.$div.'_campo_error_'.$oggetto.'_first_km" style="position:relative;width:100%;color:red;text-align:center;" ></div>';
                            echo '</td>';
                        echo '</tr>';
                    echo '</tbody>';
                echo '</table>';

            echo '</div>';

        echo '</div>';

    }

    function drawActualEdit() {
        echo '<div>';
            //echo json_encode($this->gruppo['oggettiActual']);
            foreach ($this->base as $oggetto=>$b) {

                if (array_key_exists($oggetto,$this->gruppo['oggettiActual']) && $this->gruppo['oggettiActual'][$oggetto]['flag_mov']=='ok') {
                    $this->drawOggettoEdit($oggetto,$this->gruppo['oggettiActual'][$oggetto]);
                }
            }
        echo '</div>';
    }

    function drawBaseEdit() {
        echo '<div>';
            
            echo '<div style="position:relative;border-bottom:1px solid black;height:40px;margin-bottom:15px;text-align:center;" >';
                echo '<button style="text-align:center;width:400px;font-weight:bold;margin-top:10px;background-color:aquamarine;" onclick="window._nebulaStorico.convalidaForm(\''.$this->gruppo['codice'].'\');">CONFERMA</button>';
            echo '</div>';

            foreach ($this->base as $oggetto=>$b) {

                if (array_key_exists($oggetto,$this->oggettiDefault)) {

                    $this->drawOggettoEditForm($oggetto);
                }
            }
        echo '</div>';
    }

    function drawModelloEdit() {
        echo '<div>';
            
            echo '<div style="position:relative;border-bottom:1px solid black;height:40px;margin-bottom:15px;text-align:center;" >';
                echo '<button style="text-align:center;width:400px;font-weight:bold;margin-top:10px;background-color:aquamarine;" onclick="window._nebulaStorico.convalidaTipo(\''.$this->info['marca'].'\',\''.$this->info['modello'].'\',\'\',\'Modello\');">CONFERMA</button>';
            echo '</div>';

            foreach ($this->base as $oggetto=>$b) {

                if (array_key_exists($oggetto,$this->gruppo['oggetti'])) {

                    $this->drawOggettoEditSpecifico($oggetto,'oggettiModello','Modello');
                }
            }
        echo '</div>';
    }

    function drawTelaioEdit() {
        echo '<div>';
            
            echo '<div style="position:relative;border-bottom:1px solid black;height:40px;margin-bottom:15px;text-align:center;" >';
                echo '<button style="text-align:center;width:400px;font-weight:bold;margin-top:10px;background-color:aquamarine;" onclick="window._nebulaStorico.convalidaTipo(\'\',\'\',\''.$this->info['telaio'].'\',\'Telaio\');">CONFERMA</button>';
            echo '</div>';

            foreach ($this->base as $oggetto=>$b) {

                if (array_key_exists($oggetto,$this->gruppo['oggetti'])) {

                    $this->drawOggettoEditSpecifico($oggetto,'oggettiTelaio','Telaio');
                }
            }
        echo '</div>';
    }

    function drawAltro() {

        $divo2=new Divo('storicoEditAltro','5%','94%',true);

        $divo2->setBk('#d8bfd8');

        $css=array(
            "font-weight"=>"bold",
            "font-size"=>"1em",
            "margin-left"=>"15px",
            "margin-top"=>"-1px"
        );

        ob_start();
            $this->drawLink();
        $divo2->add_div('Modelli Collegati','black',0,"",ob_get_clean(),1,$css);

        ob_start();
            $this->drawPacchetti();
        $divo2->add_div('Pacchetti Marca','black',0,"",ob_get_clean(),0,$css);

        $divo2->build();

        $divo2->draw();

    }

    function drawLink() {}

    function drawPacchetti() {}

}

?>