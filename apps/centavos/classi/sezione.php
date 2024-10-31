<?php

class centaSezione {

    protected $sezione=array();
    //protected $varianti=array();
    protected $moduli=array();
    protected $parametri=array();

    //protected $actualVariante="";

    //colore di evidenza per i moduli
    protected $modColor='#a8ecd6';
    //contiene il valore in euro dell'incentivo dopo il calcolo
    protected $incentivo=0;

    //array degli oggetti "divo" dei moduli
    protected $divos=array();

    protected $printAnalisiBody="";

    //oggetto baseDati
    protected $base;
    protected $galileo;

    function __construct($arr,$base,$galileo) {

        $this->sezione=$arr;
        $this->base=$base;
        $this->galileo=$galileo;

        //if (!$this->sezione['varianti']=json_decode($this->sezione['varianti'],true)) $this->sezione['varianti']=array();
        if (!$this->sezione['coefficienti']=json_decode($this->sezione['coefficienti'],true)) $this->sezione['coefficienti']=array();
        if (!$this->sezione['moduli']=json_decode($this->sezione['moduli'],true)) $this->sezione['moduli']=array();
        if (!$this->sezione['limite']=json_decode($this->sezione['limite'],true)) $this->sezione['limite']=array();
        if (!$this->sezione['peso']=json_decode($this->sezione['peso'],true)) $this->sezione['peso']=array();
        if (!$this->sezione['gradi']=json_decode($this->sezione['gradi'],true)) $this->sezione['gradi']=array();

        //sort($this->sezione['moduli']);

        /*
        //TEST
        //abbinamento sezione moduli
        //BUDGET e coefficienti VENGONO duplicati rispetto alle definizione della sezione
        //per peremettere la loro eventuale SOVRASCRITTURA
        //indice=sezione
        $tempvarianti=array(
            "1"=>array(
                "TEC"=>'{
                    "moduli":["1","2"],
                    "eccedenza":1,
                    "peso":{"1":50,"2":50},
                    "flagGradi":true,
                    "gradi":[{"1":30,"2":30},{"1":30,"2":50},{"1":50,"2":50},{"1":50,"2":70},{"1":50,"2":100}],
                    "limite":{"1":120,"2":120},
                    "budget":350,
                    "coefficienti":{"pres":true,"redd":true,"gen":true}
                }'
            ),
            "2"=>array(
                "RC"=>'{
                    "moduli":["3"],
                    "eccedenza":0,
                    "peso":{"3":100},
                    "flagGradi":false,
                    "gradi":[],
                    "limite":{"3":100},
                    "budget":-1,
                    "coefficienti":{"pres":false,"redd":false,"gen":true}
                }'
            ),
            "3"=>array(
                "TEC"=>'{
                    "moduli":["4"],
                    "eccedenza":0,
                    "peso":{"4":100},
                    "flagGradi":true,
                    "gradi":[{"4":30},{"4":50},{"4":80},{"4":100},{"4":110}],
                    "limite":{"4":100},
                    "budget":150,
                    "coefficienti":{"pres":false,"redd":false,"gen":true}
                }'
            ),
            "4"=>array(
                "TEC"=>'{
                    "moduli":["5"],
                    "eccedenza":0,
                    "peso":{"5":100},
                    "flagGradi":true,
                    "gradi":[{"5":30},{"5":50},{"5":80},{"5":100},{"5":110}],
                    "limite":{"5":100},
                    "budget":150,
                    "coefficienti":{"pres":false,"redd":false,"gen":true}
                }'
            )
        );

        foreach ($tempvarianti[$this->sezione['sezione']] as $var=>$v) {
            
            $this->varianti[$var]=json_decode($v,true);

            //echo $this->sezione['sezione'];

            //BUDGET e coefficienti VENGONO duplicati rispetto alle definizione della sezione
            //per peremettere la loro eventuale SOVRASCRITTURA
            //se non sono valorizzati nella variante allora assumono il valore che hanno in sezione
            if ($this->varianti[$var]['budget']==-1) $this->varianti[$var]['budget']=$this->sezione['budget'];
            if (count($this->varianti[$var]['coefficienti'])==0) $this->varianti[$var]['coefficienti']=json_decode($this->sezione['coefficienti'],true);

        }

        //END TEST

        */

        $txt="";
        foreach ($this->sezione['moduli'] as $m) {
            $txt.="'".$m."',";
        }

        //////////////////////////////////////////////
        //valorizzare i moduli ed i parametri
        $wclause="ID IN (".substr($txt,0,-1).")";
        $this->galileo->executeSelect('centavos','CENTAVOS_moduli',$wclause,"");
        $result=$this->galileo->getResult();
        if ($result) {
            $txt="";
            $fetID=$this->galileo->preFetch('centavos');
            while ($row=$this->galileo->getFetch('centavos',$fetID)) {
                $this->moduli[$row['ID']]=$row;
                if (!$this->moduli[$row['ID']]['principali']=json_decode($row['principali'],true)) $this->moduli[$row['ID']]['principali']=array();
                if (!$this->moduli[$row['ID']]['modificatori']=json_decode($row['modificatori'],true)) $this->moduli[$row['ID']]['modificartori']=array();

                foreach ($this->moduli[$row['ID']]['principali'] as $x) {
                    $txt.="'".$x."',";
                }
                foreach ($this->moduli[$row['ID']]['modificatori'] as $x) {
                    $txt.="'".$x."',";
                }
            }
        }

        $wclause="ID IN (".substr($txt,0,-1).")";
        $this->galileo->executeSelect('centavos','CENTAVOS_parametri',$wclause,"");
        $result=$this->galileo->getResult();
        if ($result) {
            $fetID=$this->galileo->preFetch('centavos');
            while ($row=$this->galileo->getFetch('centavos',$fetID)) {
                if (!$this->parametri[$row['ID']]=json_decode($row['param'],true)) $this->parametri[$row['ID']]=array();
                if (!$this->parametri[$row['ID']]['griglia']=json_decode($row['griglia'],true)) $this->parametri[$row['ID']]['griglia']=array();
                if (!$this->parametri[$row['ID']]['funzione']=json_decode($row['funzione'],true)) $this->parametri[$row['ID']]['funzione']=array();

            }
        }
        //////////////////////////////////////////////
    }

    function getPrintAnalisiBody() {
        return $this->printAnalisiBody;
    }

    function getIncentivo() {
        return $this->incentivo;
    }

    function getVariante() {
        return $this->sezione['variante'];
    }

    /*function buildVariante($variante) {

        $this->actualVariante=$variante;

        //se la variante NON è contemplata dalla sezione allora salta
        if ( !in_array($variante,$this->sezione['varianti']) ) return;

        //TEST
        //il test è per la variante TEC
        //indice=modulo
        $temp=array(
            "1"=>'{
                "titolo":"Risultato quantitativo",
                "principali":{
                    "1":{
                        "titolo":"Efficienza Pagamento",
                        "classe":"individuale",
                        "sorgente":"effP",
                        "tipo":"esponenziale",
                        "parametri":{
                            "funzione":{"min":-100,"max":100,"fattore":1.5},
                            "griglia":{}
                        },
                        "peso":70,
                        "soglia":"70",
                        "paletto":false,
                        "rettifica":false
                    },
                    "2":{
                        "titolo":"Efficienza Interno",
                        "classe":"individuale",
                        "sorgente":"effI",
                        "tipo":"esponenziale",
                        "parametri":{
                            "funzione":{"min":-100,"max":100,"fattore":1.5},
                            "griglia":{}
                        },
                        "peso":30,
                        "soglia":"70",
                        "paletto":false,
                        "rettifica":false
                    }
                },
                "modificatori":{
                    "1":{
                        "titolo":"Grado di utilizzo",
                        "classe":"individuale",
                        "sorgente":"grUtil",
                        "tipo":"griglia",
                        "parametri":{
                            "funzione":{},
                            "griglia":{"0":[0,-20],"1":[80,-15],"2":[85,-5],"3":[90,0],"4":[94,5],"5":[96,15]}
                        },
                        "peso":100,
                        "soglia":"",
                        "paletto":false,
                        "rettifica":false
                    },
                    "2":{
                        "titolo":"Efficienza Garanzia",
                        "classe":"team",
                        "sorgente":"effG",
                        "tipo":"griglia",
                        "parametri":{
                            "funzione":{},
                            "griglia":{"0":[0,-50],"1":[50,-25],"2":[60,-15],"3":[70,0],"4":[75,5],"5":[80,15]}
                        },
                        "peso":100,
                        "soglia":"",
                        "paletto":false,
                        "rettifica":false
                    }
                }
            }',

            "2"=>'{
                "titolo":"Risultato qualitativo",
                "principali":{
                    "1":{
                        "titolo":"Qualità lavoro IQS",
                        "classe":"team",
                        "sorgente":"qlIQS",
                        "tipo":"griglia",
                        "parametri":{
                            "funzione":{},
                            "griglia":{"0":[0,0],"1":[4.3,20],"2":[4.4,40],"3":[4.5,60],"4":[4.6,80],"5":[4.7,100]}
                        },
                        "peso":20,
                        "soglia":"",
                        "paletto":false,
                        "rettifica":false
                    },
                    "2":{
                        "titolo":"Quality Check",
                        "classe":"individuale",
                        "sorgente":"QC_1",
                        "tipo":"griglia",
                        "parametri":{
                            "funzione":{},
                            "griglia":{"0":[0,0],"1":[90,0],"2":[92,40],"3":[94,60],"4":[98,80],"5":[99,100]}
                        },
                        "peso":40,
                        "soglia":"",
                        "paletto":true,
                        "rettifica":true
                    },
                    "3":{
                        "titolo":"CitNow",
                        "classe":"individuale",
                        "sorgente":"citNow",
                        "tipo":"griglia",
                        "parametri":{
                            "funzione":{},
                            "griglia":{"0":[0,0],"1":[20,20],"2":[30,40],"3":[40,60],"4":[60,80],"5":[80,100]}
                        },
                        "peso":40,
                        "soglia":"",
                        "paletto":false,
                        "rettifica":true
                    }
                },
                "modificatori":{
                }
            }'

        );
        //END TEST

        foreach ($this->sezione['moduli'] as $m) {
            if (array_key_exists($m,$temp)) {
                $this->moduli[$m]=json_decode($temp[$m],true);
            }
        }

    }*/

    function calcola() {

        //fattore:                  EXP: consigliato da 1.0 a 2.0 || LOG: consigliato da 1 a 4
        //scala:                    max - min
        // K                        (x - min) / scala
        //calcolo esponmenziale:    ( ( K ) ^ fattore ) * 100  (lineare=fattore=1)
        //calcolo logaritmico:
        // H                        ( log base 100 ( K * 100 ) ) *100
        // alpha                    fattore / 10
        // LIN                      calcolo lineare
        // risultato                H - alpha ( H - LIN )

        $ret=array(
            "punteggio"=>0,
            "incentivo"=>0,
            "moduli"=>array()
        );

        foreach ($this->moduli as $mod=>$m) {

            $ret['moduli'][$mod]=array(
                "punteggio"=>0,
                "principali"=>array(
                    "punteggio"=>0,
                    "paletto"=>false,
                    "parametri"=>array()
                ),
                "modificatori"=>array(
                    "punteggio"=>0,
                    "paletto"=>false,
                    "parametri"=>array()
                )
            );

            foreach ($m['principali'] as $par) {    

                $p=$this->parametri[$par];
                $valore=$this->base->getValore($p['sorgente'],$p['classe']);
                if(is_nan($valore))$valore=0;

                $ret['moduli'][$mod]['principali']['parametri'][$par]['valore']=$valore;

                if ($p['soglia']!="" && $valore<$p['soglia']) {
                    $temp=0;
                }
                else {

                    if ($p['tipo']=='griglia') {
                        $temp=$this->calcolaGriglia($p,$valore);
                    }
                    else {
                        $temp=$valore<=0?0:$this->calcolaFunzione($p,$valore);
                    }
                }

                $ret['moduli'][$mod]['principali']['parametri'][$par]['punteggio']=is_nan($temp)?0:$temp;

                //calcolo modulo principali
                if (!$ret['moduli'][$mod]['principali']['paletto']) {
                    if ($p['paletto']==1 && $temp==0) {
                        $ret['moduli'][$mod]['principali']['paletto']=true;
                        $ret['moduli'][$mod]['principali']['punteggio']=0;
                    }
                    else {
                        $ret['moduli'][$mod]['principali']['punteggio']+=(is_nan($temp)?0:$temp)*($p['peso']/100);
                    }
                }

            }

            foreach ($m['modificatori'] as $par) {    

                $p=$this->parametri[$par];
                $valore=$this->base->getValore($p['sorgente'],$p['classe']);
                if(is_nan($valore))$valore=0;

                $ret['moduli'][$mod]['modificatori']['parametri'][$par]['valore']=$valore;

                if ($p['soglia']!="" && $valore<$p['soglia']) {
                    $temp=0;
                }
                else {

                    if ($p['tipo']=='griglia') {
                        $temp=$this->calcolaGriglia($p,$valore);
                    }
                    else {
                        $temp=$valore<=0?0:$this->calcolaFunzione($p,$valore);
                    }
                }

                $ret['moduli'][$mod]['modificatori']['parametri'][$par]['punteggio']=is_nan($temp)?0:$temp;

                //calcolo modulo modificatori
                $ret['moduli'][$mod]['modificatori']['punteggio']+=is_nan($temp)?0:$temp;
            }

            //////////////////////////////////
            //punteggio totale del modulo
            if (isset($ret['moduli'][$mod]['principali']['punteggio'])) {
                $ret['moduli'][$mod]['punteggio']=$ret['moduli'][$mod]['principali']['punteggio'];
            }

            if (isset($ret['moduli'][$mod]['modificatori']['punteggio'])) {
                $ret['moduli'][$mod]['punteggio']=$ret['moduli'][$mod]['punteggio']*(1+($ret['moduli'][$mod]['modificatori']['punteggio']/100));
            }

            if ($ret['moduli'][$mod]['punteggio']>$this->sezione['limite'][$mod]) $ret['moduli'][$mod]['punteggio']=$this->sezione['limite'][$mod];

        }

        //############################
        //calcolo SEZIONE
        /*
        "punteggio": 0,
        "moduli": {
            "1": {
            "punteggio": 21.875000000000004,
            "principali": {
                "punteggio": 72.91666666666667,
                "paletto": false,
                "parametri": {
                "1": {
                    "valore": "125",
                    "punteggio": 104.16666666666667
                },
                "2": {
                    "valore": "87",
                    "punteggio": 0
                }
                }
            },
            "modificatori": {
                "punteggio": -70,
                "paletto": false,
                "parametri": {
                "3": {
                    "valore": "0",
                    "punteggio": -20
                },
                "4": {
                    "valore": "0",
                    "punteggio": -50
                }
                }
            }
            },
            "2": {
            "punteggio": 0,
            "principali": {
                "punteggio": 0,
                "paletto": true,
                "parametri": {
                "5": {
                    "valore": "4.5",
                    "punteggio": 60
                },
                "6": {
                    "valore": "0",
                    "punteggio": 0
                },
                "7": {
                    "valore": "0",
                    "punteggio": 0
                }
                }
            },
            "modificatori": {
                "punteggio": 0,
                "paletto": false,
                "parametri": []
            }
            }
        }*/

        $temp=0;

        foreach ($ret['moduli'] as $mod=>$m) {

            if ($this->sezione['flag_gradi']==0) {
                $peso=$this->sezione['peso'][$mod];
            }
            else {
                $peso=$this->sezione['gradi'][$this->sezione['livello']][$mod];
            }

            $temp+=($m['punteggio']>0?$m['punteggio']:0)*($peso/100);

        }

        $ret['punteggio']=($temp>0?$temp:0);
        $ret['incentivo']=$this->sezione['budget']*($ret['punteggio']/100);

        //############################

        return $ret;
    }

    function calcolaGriglia($p,$valore) {

        $temp=0;
        foreach ($p['griglia'] as $k=>$g) {
            //valore min
            if ($k==0) {
                $temp=(float)$g[1];
            }
            else {
                //se il valore + >= al riferimento
                if ((float)$valore>=(float)$g[0]) {
                    $temp=(float)$g[1];
                }
                else {
                    break;
                }
            }
        }

        return $temp;
    }

    function calcolaFunzione($p,$valore) {

        $k=($valore-$p['funzione']['min'])/($p['funzione']['max']-$p['funzione']['min']);

        if ($p['tipo']=='lineare') {
            return $k*100;
        }
        elseif ($p['tipo']=='esponenziale') {
            return pow($k,$p['funzione']['fattore'])*100;
        }
        elseif($p['tipo']=='logaritmica') {

            $h=log($k*100,100)*100;
            $f=$p['funzione']['fattore']/10;

            return $h-($f*($h-($k*100)));
        }
        else return 0;
    }


    function drawSezione($contesto) {

        //se la variante NON è contemplata dalla sezione allora salta
        //if ( !in_array($this->actualVariante,$this->sezione['varianti']) ) return;

        /*SEZIONE
        "1"=>array(
            "sezione"=>"1",
            "titolo"=>"Incentivazione personale",
            "varianti"=>'["TEC","RT","RC","ASS"]',
            "budget"=>350,
            "coefficienti"=>'{"pres":true,"redd":true,"gen":true}'
        ),
        */

        echo '<div style="border-top:1px solid black;font-size:1.1em;margin-top:5px;margin-bottom:3px;">';

            echo '<div style="font-weight:bold;">'.$this->sezione['titolo'].'</div>';

            //if ($this->actualVariante!="") {

                echo '<div style="position:relative;">';

                    echo '<img style="width:20px;height:20px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/centavos/img/budget.png" />';
                    //echo '<span style="margin-left:4px;">'.number_format($this->sezione['budget'],2,',','.').'</span>';
                    echo '<span style="margin-left:4px;">'.number_format($this->sezione['budget'],2,',','.').'</span>';

                    //if ($this->sezione['eccedenza']==1) {
                    if ($this->sezione['eccedenza']==1) {
                        echo '<img style="position: relative;width:10px;height:10px;margin-left:8px;top: -1px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/centavos/img/piu.png" />';
                    }

                    echo '<img style="position:absolute;width:15px;height:15px;top:50%;right:10px;transform: translate(0px, -50%);cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/centavos/img/edit.png" onclick="window._nebulaApp.callOwnFunction(\'ctvChangeSezione\',\''.$this->sezione['ID'].'\');" />';

                echo '</div>';

                foreach ($this->sezione['moduli'] as $mod) {

                    //$m=$this->moduli[$mod];
                    //$m=json_decode($m,true);
                    //echo json_encode($this->galileo->getLog('query'));

                    echo '<div style="margin-top:5px;background-color:'.$this->modColor.';">'.$this->moduli[$mod]['titolo'].'</div>';

                    foreach ($this->moduli[$mod]['principali'] as $km=>$kv) {
                        echo '<div class="ctv_moduloP_tag" style="" >';
                            echo '<div style="">'.$this->parametri[$kv]['titolo'].'</div>';
                            if ($contesto=='analisi' || $contesto=='ext') {
                                $this->base->setSorgente($this->parametri[$kv]['sorgente'],$contesto,$this->parametri[$kv]['rettifica']);
                            }
                        echo '</div>';
                    }

                    foreach ($this->moduli[$mod]['modificatori'] as $km=>$kv) {
                        echo '<div class="ctv_moduloM_tag" style="" >';
                            echo $this->parametri[$kv]['titolo'];
                            if ($contesto=='analisi' || $contesto=='ext') {
                                $this->base->setSorgente($this->parametri[$kv]['sorgente'],$contesto,$this->parametri[$kv]['rettifica']);
                            }
                        echo '</div>';
                    }
                }
            //}

        echo '</div>';

    }

    function drawStructSection() {

        //setta i coefficienti
        if (!is_null($this->base)) $this->base->setCoeff($this->sezione['coefficienti']);

        $countModuli=count($this->moduli);
        $h=16+7+(6*$countModuli);

        //#####################################
        echo '<script type="text/javascript">';
            echo 'window._ctv_ckMulti=new chekkoMultiForm("ctv");';
            echo 'window._ctv_ckMulti.addForm("ctv_FS");';

            if (count($this->moduli)>0) {
                echo 'window._ctv_ckMulti.addForm("ctv_FSG");';
            }
        echo '</script>';
        //#####################################

        $ctvFS=new ctChekkoSezione("ctv_FS");

        $a=array(
            "budget"=>array(
                "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
            "flagGradi"=>array(
                "js_chk_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
            "coefficienti"=>array(
                "js_chk_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
            "limite"=>array(
                "js_chk_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
            "peso"=>array(
                "js_chk_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
            "livello"=>array(
                "js_chk_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            )
        );
        $ctvFS->add_fields($a);

        $a=array(
            "budget"=>"digit",
            "flagGradi"=>"none",
            "coefficienti"=>"none",
            "limite"=>"none",
            "peso"=>"none",
            "livello"=>"none"
        );
        $ctvFS->load_tipi($a);

        //"coefficienti","peso","limite" vengono raccolti con collect_data_proprietario
        $a=array(
            "budget"=>"",
            "flag_gradi"=>"",
            "livello"=>""
        );
        $ctvFS->load_expo($a);

        $a=array(
            "budget"=>"budget",
            "flag_gradi"=>"flagGradi",
            "livello"=>"livello"
        );
        $ctvFS->load_conv($a);

        echo '<div style="width:100%;height:'.$h.'%;border-bottom:1px solid darkgray;font-size:1.2em;min-height:26%;" ckMulti_ctv="head" >';

            echo '<div style="margin-top:5px;">';

                echo '<div style="display:inline-block;width:85%;">';

                    echo '<div>';

                        echo '<div style="display:inline-block;width:50%;font-weight:bold;">';
                            echo $this->sezione['titolo'].' ('.$this->sezione['ID'].')';
                        echo '</div>';

                        echo '<div id="js_chk_ctv_FS_elem_budget" style="display:inline-block;width:30%;font-weight:bold;border:2px solid transparent;box-sizing:border-box;padding:2px;">';
                            echo '<img style="width:20px;height:20px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/centavos/img/budget.png" />';
                            //ESEMPIO <input id="ribbon_ens_today" class="js_chk_'.$this->form_tag.'" js_chk_'.$this->form_tag.'_tipo="today" />
                            echo '<input id="ctv_FS_budget" style="margin-left:4px;width:70%;text-align:center;font-weight:bold;font-size:1.2em;" type="text" value="'.number_format($this->sezione['budget'],0,'','').'" class="js_chk_ctv_FS" js_chk_ctv_FS_tipo="budget" onchange="window._js_chk_ctv_FS.js_chk();" />';
                        echo '</div>';

                        echo '<div style="display:inline-block;width:20%;vertical-align:top;">';
                            echo '<div id="ctv_simula_incentivo" style="position:relative;box-sizing:border-box;border:1px solid black;width:90%;left:10%;height:30px;background-color:#fdbfa785;text-align:center;line-height:30px;font-weight:bold;font-size:0.9em;">';
                            echo '</div>';
                        echo '</div>';

                    echo '</div>';

                    echo '<div style="height:30px;">';

                        echo '<div style="position:relative;top:30%;">';

                            //echo '<img style="width:25px;height:25px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/centavos/img/coeff.png" />';
                            $tempcoeff=$this->base->getCoeff();
                            foreach ( $tempcoeff as $kc=>$c) {

                                echo '<div style="position:relative;display:inline-block;margin-left:10px;vertical-align:top;top:50%;ransform:translate(0px,-50%);">';

                                    echo '<img style="width:18px;height:18px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/centavos/img/'.$c['icon'].'" />'; 
                                    echo '<input id="ctv_FS_coeff_'.$kc.'" type="checkbox" style="margin-left:5px;" value="'.$kc.'" ';
                                        if ($c['stato']) echo 'checked="checked"';
                                    echo ' onclick="window._js_chk_ctv_FS.js_chk();"/>';
                                    //echo '<span style="margin-left:5px;">'.$c['titolo'].'</span>';

                                echo '</div>';

                            }

                        echo '</div>';

                        echo '<input type="hidden" value="" data-txt="" class="js_chk_ctv_FS" js_chk_ctv_FS_tipo="coefficienti" />';

                    echo '</div>';
                
                echo '</div>';

                echo '<div style="display:inline-block;width:15%;vertical-align:top;">';

                    echo '<div id="ctv_simula_punteggio_sezione" style="position:relative;box-sizing:border-box;border:1px solid black;width:90%;left:10%;height:30px;background-color:#e6e6e6;text-align:center;line-height:30px;font-weight:bold;font-size:0.9em;">';
                    echo '</div>';

                    echo '<div style="height:30px;margin-top: 8px;left:30px;position:relative;">';
                            echo '<img style="width:25px;height:25px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/centavos/img/save.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].ctvSave();" />';
                            echo '<img style="width:25px;height:25px;margin-left:25px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/centavos/img/valuta.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].ctvValuta();"/>';
                    echo '</div>';

                echo '</div>';

            echo '</div>';
        
            echo '<div>';

                echo '<div style="display:inline-block;width:100%;">';

                    echo '<div style="margin-top:5px;font-weight:bold;font-size:0.8em;text-align:center;">';
                        echo '<div style="display:inline-block;width:35%;"></div>';
                        echo '<div style="display:inline-block;width:20%;">range</div>';

                        echo '<div style="display:inline-block;width:10%;">peso';
                            echo '<input id="ctv_FS_flagGradi" style="margin-left:5px;" type="checkbox" value="" ';
                                //if (count($this->sezione['gradi'])>0) echo 'checked="checked"';
                                if ($this->sezione['flag_gradi']) echo 'checked="checked"';
                            echo ' onclick="window._js_chk_ctv_FS.chg_flagCkb_std(\'flagGradi\',this.checked)"/>';
                            echo '<input type="hidden" value="';
                                if ($this->sezione['flag_gradi']) echo '1';
                                else echo '0';
                            echo '" class="js_chk_ctv_FS" js_chk_ctv_FS_tipo="flagGradi"/>';
                        echo '</div>';
                        echo '<div style="display:inline-block;width:35%;text-align:center;">';
                            
                            for ($k=0;$k<=4;$k++) {
                                echo '<div class="cvt_div_flagGradi" style="display:';
                                    if ($this->sezione['flag_gradi']) echo 'inline-block;';
                                    else echo 'none;';
                                echo 'width:20%;text-align:center;">';
                                    echo '<input name="ctv_form_livello" style="" type="radio" value="'.$k.'" ';
                                        if ($k==$this->sezione['livello']) echo 'checked="checked" ';
                                    echo ' onclick="window._js_chk_ctv_FS.chg_radio_std(\'livello\',this.value)" />';
                                echo '</div>';
                            }

                        echo '</div>';

                        echo '<input type="hidden" value="'.$this->sezione['livello'].'" class="js_chk_ctv_FS" js_chk_ctv_FS_tipo="livello"/>';

                    echo '</div>';

                    if (count($this->moduli)>0) {

                        $ctvFSG=new ctChekkoGradi("ctv_FSG");
                        $ctvFSG->setCssflag(false);

                        $a=array(
                            "gradi"=>array(
                                "js_chk_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
                                "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
                            )
                        );
                        $ctvFSG->add_fields($a);

                        $a=array(
                            "gradi"=>"none"
                        );
                        $ctvFSG->load_tipi($a);
                        //I valori vengono raccolti con collect_proprietario
                    }
                
                    //foreach ($this->moduli as $mod=>$m) {
                    foreach ($this->sezione['moduli'] as $mod) {

                        $m=$this->moduli[$mod];

                        echo '<div style="display:table;height:25px;width:100%;">';

                            echo '<div style="display:inline-block;width:35%;">('.$mod.') '.$m['titolo'].'</div>';

                            echo '<div id="js_chk_ctv_FS_elem_range_'.$mod.'" style="display:inline-block;width:20%;text-align:center;border:2px solid transparent;padding:2px;box-sizing:border-box;">';
                                echo '<img style="position:relative;top:3px;width:15px;height:15px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/centavos/img/blackarrowR.png" />';
                                echo '<input id="ctv_FS_range_'.$mod.'" style="width:70px;text-align:center;margin-left:5px;/*font-size:1em;*/" maxlength="3" type="text" data-id="'.$mod.'" value="'.(isset($this->sezione['limite'][$mod])?$this->sezione['limite'][$mod]:'').'" onchange="window._js_chk_ctv_FS.js_chk();" />';
                            echo '</div>';

                            echo '<div id="js_chk_ctv_FS_elem_peso_'.$mod.'" style="display:inline-block;width:10%;text-align:center;border:2px solid transparent;padding:2px;box-sizing:border-box;">';
                                echo '<input id="ctv_FS_peso_'.$mod.'" style="width:90%;text-align:center;" type="text" maxlength="3" data-id="'.$mod.'" value="'.(isset($this->sezione['peso'][$mod])?$this->sezione['peso'][$mod]:'').'" onchange="window._js_chk_ctv_FS.js_chk();" />';
                            echo '</div>';

                            echo '<div style="display:inline-block;width:35%;">';

                                //##### ##### #######

                                echo '<div style="width:100%;';
                                    //if (count($this->sezione['gradi'])==0) echo 'visibility:hidden;';
                                echo '">';

                                    for ($k=0;$k<=4;$k++) {

                                        $v=(isset($this->sezione['gradi'][$k][$mod]))?$this->sezione['gradi'][$k][$mod]:0;
                                        echo '<div id="js_chk_ctv_FSG_elem_gradi_'.$mod.'_'.$k.'" class="cvt_div_flagGradi" style="display:';
                                            //if ($this->sezione['flagGradi']) echo 'inline-block;';
                                            //else echo 'none;';
                                            echo 'inline-block;';
                                        echo 'width:20%;text-align:center;border:2px solid transparent;padding:2px;box-sizing:border-box;">';

                                            echo '<input id="ctv_FSG_gradi_'.$k.'" style="width:90%;text-align:center;" maxlength="3" type="text" data-id="'.$mod.'" value="'.$v.'" onchange="window._js_chk_ctv_FSG.js_chk();" />';

                                        echo '</div>';
                                    }

                                echo '</div>';     

                            echo '</div>';

                        echo '</div>';

                    }

                    //nuovo MODULO
                    echo '<div style="display:table;height:25px;width:100%;">';

                            echo '<div style="display:inline-block;width:35%;">';
                                echo '<img style="position:relative;top:5px;width:20px;height:20px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/centavos/img/add.png"" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].ctvAddModulo();" />';
                                echo '<input id="ctv_new_modulo_titolo" style="margin-left:5px;width:90%;" type="text" onkeydown="if(event.keyCode==13) window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].ctvAddModulo();" />';
                            echo '</div>';

                            echo '<div style="display:inline-block;width:63%;text-align:right;font-weight:bold;color:red;" ckMulti_ctv="txt" ></div>';

                    echo '</div>';

                echo '</div>';

            echo '</div>';
        
        echo '</div>';

        echo '<input type="hidden" value="" data-txt="" class="js_chk_ctv_FS" js_chk_ctv_FS_tipo="limite" />';
        echo '<input type="hidden" value="" data-txt="" class="js_chk_ctv_FS" js_chk_ctv_FS_tipo="peso" />';
        echo '<input type="hidden" value="" data-txt="" class="js_chk_ctv_FSG" js_chk_ctv_FSG_tipo="gradi" />';

        $ctvFS->draw_js_base();

        if (count($this->moduli)>0) {
            $ctvFSG->draw_js_base();
        }

        ////////////////////////////////////////////////////////////////////////////////

        echo '<div style="width:100%;height:'.(99-$h).'%;overflow:scroll;">';

            echo '<div style="width:95%;">';

                //foreach ($this->moduli as $mod=>$m) {
                foreach ($this->sezione['moduli'] as $mod) {

                    $m=$this->moduli[$mod];

                    echo '<div style="border-top:2px solid '.$this->modColor.';margin-top:5px;margin-bottom:5px;">';

                        echo '<div style="margin-top:5px;">';

                            echo '<div style="display:inline-block;width:80%;font-size: 1.2em;background-color:'.$this->modColor.';">';
                                echo '('.$mod.') '.$m['titolo'];
                            echo '</div>';

                            echo '<div style="display:inline-block;width:5%;">';
                                echo '<img style="position:relative;top:3px;left:5px;width:18px;height:18px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/centavos/img/sub.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].ctvDelModulo(\''.$mod.'\')"/>';
                            echo '</div>';

                            echo '<div style="display:inline-block;width:15%;vertical-align:top;">';
                                echo '<div id="ctv_simula_punteggio_modulo_'.$mod.'" style="position:relative;box-sizing:border-box;border:1px solid black;width:90%;left:10%;height:30px;background-color:'.$this->modColor.'88;text-align:center;line-height:30px;font-weight:bold;font-size:0.9em;">';
                                echo '</div>';
                            echo '</div>';

                        echo '</div>';

                        echo '<div style="width:100%;margin-top:10px;">';
                            
                            $divo=new Divo('centavos_'.$mod,'30px;','200px',0);
                            $divo->setBk('#fdbea7');

                            $txt="";

                            ob_start();
                                $this->drawParametri($mod,'principali');
                            $txt=ob_get_clean();

                            $divo->add_div('Parametri Principali','black',0,0,$txt,1,array());

                            ob_start();
                                $this->drawParametri($mod,'modificatori');
                            $txt=ob_get_clean();

                            //if ( count($m['modificatori'])>0 ) {
                                $divo->add_div('Modificatori','black',0,0,$txt,0,array());
                            //}

                            $divo->build();

                            $divo->draw();

                        echo '</div>';

                    echo '</div>';

                }
            
            echo '</div>';

        echo '</div>';

        echo '<script type="text/javascript">';
            echo 'window._ctv_ckMulti.setChg(false);';
        echo '</script>';

    }

    function drawParametri($mod,$tipo) {

        //totale
        echo '<div>';

            echo '<div class="'.($tipo=='principali'?"ctv_moduloP_tag":"ctv_moduloM_tag").'" style="display:inline-block;width:85%;vertical-align:top;">';
                echo '<button style="margin-left:10px;" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].ctvAddParametro(\''.$tipo.'\',\''.$mod.'\');" >Nuovo parametro</button>';
            echo'</div>';

            echo '<div style="display:inline-block;width:15%;vertical-align:top;">'; 
                
                echo '<div id="ctv_simula_punteggio_'.$tipo.'_'.$mod.'" style="position:relative;box-sizing:border-box;border:1px solid black;width:90%;left:10%;height:30px;background-color:#e6e6e6;text-align:center;line-height:30px;font-weight:bold;font-size:0.9em;">';
                echo '</div>';

            echo '</div>';

        echo '</div>';

        echo '<hr/>';

        foreach ($this->moduli[$mod][$tipo] as $kp=>$idp) {

            $suffix=($tipo=='principali')?'P':'M';
            //$name='ctm'.$mod.'_'.$suffix.$kp;
            $name='ctm_'.$idp;

            $p=$this->parametri[$idp];

            echo '<script type="text/javascript">';
                // CTMx_(P/M)x
                echo 'window._ctv_ckMulti.addForm("'.$name.'");';
            echo '</script>';

            $ckm=new ctChekkoModulo($name);
            $ckm->setCssflag(false);

            /*{
            "titolo":"Qualità lavoro IQS",
            "classe":"team",
            "sorgente":"qlIQS",
            "tipo":"griglia",
            "parametri":{
                "funzione":{},
                "griglia":{"min":[0,0],"1":[4.3,20],"2":[4.4,40],"3":[4.5,60],"4":[4.6,80],"5":[4.7,100]}
            },
            "peso":20,
            "soglia":"",
            "paletto":false,
            "rettifica":false
            }*/

            $a=array(
                "titolo"=>array(
                    "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
                    "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
                ),
                "classe"=>array(
                    "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
                    "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
                ),
                "sorgente"=>array(
                    "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
                    "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
                ),
                "tipo"=>array(
                    "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
                    "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
                ),
                "peso"=>array(
                    "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
                    "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
                ),
                "soglia"=>array(
                    "js_chk_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
                    "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
                ),
                "paletto"=>array(
                    "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
                    "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
                ),
                "rettifica"=>array(
                    "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
                    "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
                ),
                "funzione"=>array(
                    "js_chk_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
                    "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
                ),
                "griglia"=>array(
                    "js_chk_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
                    "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
                ),
                "valore"=>array(
                    "js_chk_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
                    "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
                )
            );

            $ckm->add_fields($a);
    
            $a=array(
                "titolo"=>"text",
                "classe"=>"none",
                "sorgente"=>"none",
                "tipo"=>"none",
                "peso"=>"digit",
                "soglia"=>"digit",
                "paletto"=>"none",
                "rettifica"=>"none",
                "funzione"=>"none",
                "griglia"=>"none",
                "valore"=>"digit"
            );
            $ckm->load_tipi($a);

            //funzione e grliglia vengono compilati da collect_proprietario
            $a=array(
                "titolo"=>"",
                "classe"=>"",
                "sorgente"=>"",
                "tipo"=>"",
                "peso"=>"",
                "soglia"=>"",
                "paletto"=>"",
                "rettifica"=>"",
                "valore"=>""
            );
            $ckm->load_expo($a);
    
            $a=array(
                "titolo"=>"titolo",
                "classe"=>"classe",
                "sorgente"=>"sorgente",
                "tipo"=>"tipo",
                "peso"=>"peso",
                "soglia"=>"soglia",
                "paletto"=>"paletto",
                "rettifica"=>"rettifica",
                "valore"=>"valore"
            );
            $ckm->load_conv($a);

            //////////////////////////////////////////////

            echo '<div class="ctv_parametro_div" style="margin-top:20px;" >';

                //riga 1
                echo '<div style="margin-top:5px;">';

                    echo '<div style="display:inline-block;width:85%;height:58px;">';

                        echo '<div id="js_chk_'.$name.'_elem_titolo" style="display:inline-block;width:35%;vertical-align:top;border:2px solid transparent;padding:2px;box-sizing:border-box;">';

                            echo '<div style="font-weight:bold;text-align:left;font-size:0.9em;position:relative;">';
                                echo '<div style="position:relative;width:fit-content;">';
                                    echo 'parametro '.$idp;
                                    echo '<img style="position:absolute;top:-5px;right:-10px;width:10px;height:10px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/centavos/img/sub.png"" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].ctvDelParametro(\''.$tipo.'\',\''.$idp.'\',\''.$mod.'\');" >';
                                echo '</div>';
                            echo '</div>';
                            echo '<div class="'.($tipo=='principali'?"ctv_moduloP_tag":"ctv_moduloM_tag").'" style="height: 15px;line-height: 15px;text-align:center;">';
                                //esempio echo '<input id="ctv_FSG_gradi_'.$k.'" class="js_chk_ctv_FS" js_chk_ctv_FS_tipo="gradi" onchange="window._js_chk_ctv_FS.js_chk();/>';
                                echo '<input id="'.$name.'_titolo" style="width:99%;" type="text" value="'.$p['titolo'].'" class="js_chk_'.$name.'" js_chk_'.$name.'_tipo="titolo" onchange="window._js_chk_'.$name.'.js_chk();" />';
                            echo '</div>';

                        echo '</div>';

                        echo '<div id="js_chk_'.$name.'_elem_peso" style="display:inline-block;width:15%;text-align:center;height:95%;vertical-align:top;border:2px solid transparent;padding:2px;box-sizing:border-box;">';
                            echo '<div style="font-weight:bold;text-align:center;font-size:0.9em;">peso</div>';
                            echo '<div style="">';
                                echo '<input id="'.$name.'_peso" style="width:60%;text-align:center;font-size:1.2em;" type="text" maxlength="3" value="';
                                echo ($tipo=='modificatori')?'100':(isset($p['peso'])?number_format($p['peso'],0,',','.'):"");
                            echo '" class="js_chk_'.$name.'" js_chk_'.$name.'_tipo="peso" onchange="window._js_chk_'.$name.'.js_chk();" ';
                                //un parametro modificatore ha sempre peso 100 (in realtà nel calcolo è ininfluente)
                                if($tipo=='modificatori') echo 'disabled="disabled" ';
                            echo '/>';
                            echo '</div>';
                        echo '</div>';

                        echo '<div id="js_chk_'.$name.'_elem_sorgente" style="display:inline-block;width:35%;text-align:left;height:95%;vertical-align:top;border:2px solid transparent;padding:2px;box-sizing:border-box;">';
                            echo '<div style="font-weight:bold;text-align:left;font-size:0.9em;">sorgente</div>';
                            echo '<div style="height:28px;">';
                                echo '<select id="'.$name.'_sorgente" style="width:90%;text-align:center;font-size:1em;top: 50%;position: relative;transform: translate(0px,-50%);" class="js_chk_'.$name.'" js_chk_'.$name.'_tipo="sorgente" onchange="window._js_chk_'.$name.'.js_chk();" >';

                                    if (!is_null($this->base)) {

                                        echo '<option value="">Seleziona...</option>';

                                        echo '<optgroup label="Interni">';
                                            foreach ($this->base->getSourceInt() as $ks=>$s) {
                                                echo '<option value="'.$ks.'" ';
                                                    if (isset($p['sorgente'])) {
                                                        if ($ks==$p['sorgente']) echo 'selected="selected"';
                                                    }
                                                echo '>'.$s['titolo'].'</option>';
                                            }
                                        echo '</optgroup>';

                                        echo '<optgroup label="Esterni">';
                                            foreach ($this->base->getSourceExt() as $ks=>$s) {
                                                echo '<option value="'.$ks.'" ';
                                                    if (isset($p['sorgente'])) {
                                                        if ($ks==$p['sorgente']) echo 'selected="selected"';
                                                    }
                                                echo '>'.$s['titolo'].'</option>';
                                            }
                                        echo '</optgroup>';
                                    }
                                echo '</select>';
                            echo '</div>';
                        echo '</div>';

                        echo '<div style="display:inline-block;width:15%;text-align:left;height:45px;vertical-align:top;border:2px solid transparent;padding:2px;box-sizing:border-box;">';
                            echo '<div style="font-weight:bold;text-align:left;font-size:0.9em;text-align:center;">classe</div>';
                            echo '<div style="height:28px;">';
                                $tempsel=array("individuale","team");
                                echo '<select id="'.$name.'_classe" style="width:98%;text-align:center;font-size:0.8em;top: 50%;position: relative;transform: translate(0px,-50%);" class="js_chk_'.$name.'" js_chk_'.$name.'_tipo="classe">';
                                    foreach ($tempsel as $ts) {
                                        echo '<option value="'.$ts.'" ';
                                            if ($ts==$p['classe']) echo 'selected="selected"';
                                        echo '>'.$ts.'</option>';
                                    }
                                echo '</select>';
                            echo '</div>';

                        echo '</div>';
                    
                    echo '</div>';

                    echo '<div style="display:inline-block;width:15%;vertical-align:top;">';

                        //echo '<div style="font-weight:bold;text-align:left;font-size:0.9em;text-align:center;"></div>';
                        echo '<div id="ctv_simula_punteggio_parametro_'.$idp.'"style="position:relative;box-sizing:border-box;border:1px solid black;width:90%;left:10%;height:28px;background-color:#e6e6e6;text-align:center;line-height:28px;font-weight:bold;font-size:0.9em;">';
                        echo '</div>';

                    echo '</div>'; 

                echo '</div>';
                
                //riga2
                echo '<div style="margin-top:2px;display:table;width:100%;">';

                    echo '<img style="width:18px;height:18px;position:relative;top:2px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/centavos/img/coeff.png" />';
                    echo '<input style="margin-left:5px;position:relative;top:2px;" type="checkbox"';
                        if ($p['rettifica']) echo ' checked="checked"';
                    echo 'onclick="window._js_chk_'.$name.'.chg_flagCkb_std(\'rettifica\',this.checked);" />';
                    echo '<input type="hidden" value="';
                        if ($p['rettifica']) echo '1';
                        else echo '0';
                    echo '" class="js_chk_'.$name.'" js_chk_'.$name.'_tipo="rettifica" />';

                    echo '<img style="width:18px;height:18px;margin-left:10px;position:relative;top:2px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/centavos/img/paletto.png" />';
                    echo '<input style="margin-left:5px;position:relative;top:2px;" type="checkbox"';
                        if ($p['paletto']) echo ' checked="checked"';
                    echo 'onclick="window._js_chk_'.$name.'.chg_flagCkb_std(\'paletto\',this.checked);" ';
                        //un parametro modificatore non può essere un paletto
                        if ($tipo=='modificatori') echo 'disabled="disabled" ';
                    echo '/>';
                    echo '<input type="hidden" value="';
                        if ($p['paletto']) echo '1';
                        else echo '0';
                    echo '" class="js_chk_'.$name.'" js_chk_'.$name.'_tipo="paletto" />';
                    
                    echo '<div id="js_chk_'.$name.'_elem_soglia" style="display:inline-block;border:2px solid transparent;padding:2px;box-sizing:border-box;">';
                        echo '<img style="width:18px;height:18px;margin-left:10px;position:relative;top:2px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/centavos/img/soglia.png" />';
                        echo '<input id="js_chk_'.$name.'_soglia" style="margin-left:5px;width:70px;font-size:0.8em;text-align:center;" type="text"';
                            echo ' value="'.(isset($p['soglia'])?$p['soglia']:"").'"';
                        echo ' class="js_chk_'.$name.'" js_chk_'.$name.'_tipo="soglia" onchange="window._js_chk_'.$name.'.js_chk();" ';
                            //un parametro modificatore non può avere una soglia
                            if ($tipo=='modificatori') echo 'disabled="disabled" ';
                        echo '/>';
                    echo '</div>';

                echo '</div>';

                //riga 3
                echo '<div style="height:80px;margin-top:5px;">';

                    echo '<div style="display:inline-block;width:85%;height:45px;">';

                        echo '<div style="display:inline-block;width:35%;vertical-align:top;">';

                            $tempsel=array(
                                array('lineare','lineare'),
                                array('esponenziale','esponenziale'),
                                array('logaritmica','logaritmica'),
                                array('griglia','griglia')
                            );

                            for ($i=0;$i<=3;$i++) {

                                echo '<div style="display:inline-block;width:23%;height:100%;">';

                                    echo '<div style="display:table;width:100%;text-align:center;height:50%;">';
                                        echo '<input name="js_chk_'.$name.'_elem_tipo" type="radio" value="'.$tempsel[$i][0].'"';
                                            if ( ($tipo=='modificatori' && $tempsel[$i][0]=='griglia') || $tempsel[$i][0]==$p['tipo']) echo ' checked="checked" ';
                                            if ($tipo=='modificatori' && $tempsel[$i][0]!='griglia') echo 'disabled="disabled" ';
                                        echo ' onclick="window._js_chk_'.$name.'.chkTipo(this.value)"/>';
                                    echo '</div>';

                                    echo '<div style="display:table;width:100%;text-align:center;height:50%;">';
                                        echo '<img style="width:25px;height:25px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/centavos/img/'.$tempsel[$i][1].'.png" />';
                                    echo '</div>';

                                echo '</div>';
                                
                            }

                            echo '<input type="hidden" value="'.$p['tipo'].'" class="js_chk_'.$name.'" js_chk_'.$name.'_tipo="tipo" />';

                        echo '</div>';

                        $tempsel=array("funzione","griglia");

                        echo '<div style="display:inline-block;width:65%;vertical-align:top;height:50px;">';
                            //DIVS in base al tipo di parametro

                            $riftipo='funzione';
                            if ($p['tipo']=='griglia') $riftipo='griglia';

                            foreach ($tempsel as $t) {

                                echo '<div id="ctv_div_tipo_'.$name.'_'.$t.'" style="height:100%;';
                                    if ($t==$riftipo) echo 'display:block;';
                                    else echo 'display:none;';
                                echo '">';

                                    switch ($t) {

                                        case "funzione":
                                            $this->drawFunzione($p,$name);
                                        break;
                                        case "griglia":
                                            $this->drawGriglia($p,$name);
                                        break;
                                    }

                                    if ($t=='funzione') {
                                        echo '<div style="font-size:smaller" > EXP: maggiore il fattore maggiore l\'effetto (consigliato da 1.0 a 2.0)</div>';
                                        echo '<div style="font-size:smaller" > LOG: maggiore il fattore minore l\'effetto (consigliato da 1 a 4)</div>';
                                    }

                                echo '</div>';
                            }

                        echo '</div>';

                    echo '</div>';

                    echo '<div style="display:inline-block;width:15%;vertical-align:top;">';
                        
                        echo '<div style="font-weight:bold;text-align:left;font-size:0.9em;text-align:center;">valore</div>';
                        echo '<div style="position:relative;box-sizing:border-box;border:3px solid #a7d9fd;width:90%;left:10%;height:35px;text-align:center;padding:2px;">';
                            if (!(isset($p['valore']))) $p['valore']="";
                            echo '<input id="ctv_valuta_'.$idp.'" type="text" style="width:95%;height:25px;transform:translate(0px,-50%);position:relative;top:50%;text-align:center;font-weight:bold;" data-id="'.$idp.'" value="'.$p['valore'].'" class="js_chk_'.$name.'" js_chk_'.$name.'_tipo="valore" onchange="window._js_chk_'.$name.'.js_chk();" />';
                        echo '</div>';

                        echo '<div id="js_chk_'.$name.'_error_valore" class="js_chk_'.$name.'_error" style="text-align:center;font-weight:bold;color:red;width:90%;margin-left:10%;" ></div>';

                    echo '</div>';

                echo '</div>';

                echo '<hr style="width:100%;" />';

            echo '</div>';

            $ckm->draw_js_base();
        }
    }

    function drawFunzione($p,$name) {

        echo '<div>';

            echo '<div id="js_chk_'.$name.'_elem_funzione_min" style="display:inline-block;width:32%;height:100%;text-align:center;border:2px solid transparent;padding:2px;box-sizing:border-box;">';
                echo '<div style="font-weight:bold;font-size:0.9em;">minimo</div>';
                echo '<div>';
                    $tval=(isset($p['funzione']['min']))?$p['funzione']['min']:'';
                    echo '<input id="'.$name.'_funzione_min" style="width:90%;font-size:1em;text-align:center;" type="text" data-id="min" value="'.$tval.'" onchange="window._js_chk_'.$name.'.js_chk();" />';
                echo '</div>';
            echo '</div>';

            echo '<div id="js_chk_'.$name.'_elem_funzione_max" style="display:inline-block;width:32%;height:100%;text-align:center;border:2px solid transparent;padding:2px;box-sizing:border-box;">';
                echo '<div style="font-weight:bold;font-size:0.9em;">massimo</div>';
                echo '<div>';
                    $tval=(isset($p['funzione']['max']))?$p['funzione']['max']:'';
                    echo '<input id="'.$name.'_funzione_max" style="width:90%;font-size:1em;text-align:center;" type="text" data-id="max" value="'.$tval.'" onchange="window._js_chk_'.$name.'.js_chk();" />';
                echo '</div>';
            echo '</div>';

            echo '<div id="js_chk_'.$name.'_elem_funzione_fattore" style="display:inline-block;width:32%;height:100%;text-align:center;border:2px solid transparent;padding:2px;box-sizing:border-box;">';
                echo '<div style="font-weight:bold;font-size:0.9em;">fattore</div>';
                echo '<div>';
                    if ($p['tipo']=='lineare') $tval=1;
                    else $tval=(isset($p['funzione']['fattore']))?$p['funzione']['fattore']:'';
                    echo '<input id="'.$name.'_funzione_fattore" style="width:90%;font-size:1em;text-align:center;" type="text" data-id="fattore" value="'.$tval.'" onchange="window._js_chk_'.$name.'.js_chk();" ';
                        if ($p['tipo']=='lineare') echo 'disabled="disabled"';
                    echo '/>';
                echo '</div>';
            echo '</div>';

        echo '</div>';

        echo '<input type="hidden" value="" data-txt="" class="js_chk_'.$name.'" js_chk_'.$name.'_tipo="funzione" />';

    }

    function drawGriglia($p,$name) {

        for ($i=0;$i<=5;$i++) {

            $rif=($i==0?'min':"".$i);

            echo '<div style="display:inline-block;width:16%;text-align:center;">';

                echo '<div style="font-weight:bold;font-size:0.9em;">'.$rif.'</div>';

                echo '<div id="js_chk_'.$name.'_elem_griglia_R_'.$i.'" style="border:2px solid transparent;padding:2px;box-sizing:border-box;" >';
                    $tval=(isset($p['griglia'][$i][0]))?$p['griglia'][$i][0]:'';
                    echo '<input id="'.$name.'_griglia_R_'.$i.'" style="width:90%;font-size:0.9em;text-align:center;';
                        if ($rif=='min') echo 'visibility:hidden;';
                    echo '" type="text" data-id="'.$i.'" value="'.$tval.'" onchange="window._js_chk_'.$name.'.js_chk();" />';
                echo '</div>';

                echo '<div id="js_chk_'.$name.'_elem_griglia_V_'.$i.'" style="border:2px solid transparent;padding:2px;box-sizing:border-box;">';
                    $tval=(isset($p['griglia'][$i][1]))?$p['griglia'][$i][1]:'';
                    echo '<input id="'.$name.'_griglia_V_'.$i.'" style="width:90%;font-size:0.9em;text-align:center;" type="text" data-id="'.$i.'" value="'.$tval.'" onchange="window._js_chk_'.$name.'.js_chk();" />';
                echo '</div>';

            echo '</div>';

            echo '<input type="hidden" value="" data-txt="" class="js_chk_'.$name.'" js_chk_'.$name.'_tipo="griglia" />';

        }

    }

    function drawAnalisiColl($coll) {

        //######################
        //modificare i pesi in base al LIVELLO
        //In "coll" riferito al modulo "grado":{"1":3,"2":3,"3":3}
        if (!isset($coll['grado'][$this->sezione['ID']])) return;
        $this->sezione['livello']=$coll['grado'][$this->sezione['ID']];
        //######################

        $val=$this->base->getActual();
        $calc=$this->calcola();

        $coeff=1;

        $this->printAnalisiBody="";

        $res='<div style="position:relative;width:100%;">';

            $res.='<div style="position:relative;display:inline-block;width:45%;vertical-align:top;" >';
                $res.='<div style="font-size:1.1em;font-weight:bold;color:#0984a0;">'.$this->sezione['titolo'].' (Livello:'.($coll['grado'][$this->sezione['ID']]+1).')';
                $res.='</div>';
                $this->printAnalisiBody=$this->sezione['titolo'].' (Livello:'.($coll['grado'][$this->sezione['ID']]+1).') : ';
                //[{"1":30,"2":30},{"1":30,"2":50},{"1":50,"2":50},{"1":50,"2":70},{"1":50,"2":100}]
                //coefficienti {"pres":true,"redd":true,"gen":true}
                //$res.='<div>'.json_encode($this->sezione['coefficienti']).'</div>';
                $res.='<div class="centavosAnalisiCollCoeff">';
                    foreach ($this->base->getCoeff() as $kc=>$cc) {
                        if (isset($this->sezione['coefficienti'][$kc]) && $this->sezione['coefficienti'][$kc]) {
                            $res.='<div style="position:relative;display:inline-block;width:25%;text-align:center;border:3px solid white;box-sizing:border-box;background-color:#f2c2f38a;border-radius:10px;">';
                                $temp=$this->base->getCoefVal($kc);
                                $res.='<div>'.substr($temp['classe'],0,5).': '.number_format($temp['valore'],0,'','').'%</div>';
                                $res.='<div style="font-size:0.9em;font-weight:bold;" >'.$cc['titolo'].'</div>';
                            $res.='</div>';

                            $coeff=$coeff*($temp['valore']/100);
                        }
                    }
                $res.='</div>';
            $res.='</div>';

            $res.='<div style="position:relative;display:inline-block;width:15%;vertical-align:top;text-align:center;" >';
                $res.='<img style="width:20px;height:20px;margin-left:10px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/centavos/img/budget.png" />';
                $res.='<span style="font-weight:bold;color:black;">'.number_format($this->sezione['budget'],2,',','.').'</span>';
                /*if ($this->sezione['eccedenza']==1) {
                    $res.='<img style="position: relative;width:10px;height:10px;margin-left:8px;top: -1px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/centavos/img/piu.png" />';
                }*/
            $res.='</div>';
            
            //###############################################
            $calc['incentivo']=$calc['incentivo']*$coeff;
            $this->incentivo=$calc['incentivo'];
            //###############################################

            /*
            {"effP":{"team":122.22126687155959,"individuale":116.8246553708306},"effI":{"team":0,"individuale":0},"effG":{"team":0,"individuale":0},"grUtil":{"team":0,"individuale":0},"QC_1":{"team":0,"individuale":0},"qlIQS":{"team":0,"individuale":0},"citNow":{"team":0,"individuale":0},"cemVW":{"team":0,"individuale":0},"ricVW":{"team":0,"individuale":0}}
            {"punteggio":12.412619633150753,"incentivo":43.44416871602763,"moduli":{"1":{"punteggio":24.825239266301505,"principali":{"punteggio":82.75079755433833,"paletto":false,"parametri":{"1":{"valore":116.8246553708306,"punteggio":97.35387947569217},"2":{"valore":0,"punteggio":0}}},"modificatori":{"punteggio":-70,"paletto":false,"parametri":{"3":{"valore":0,"punteggio":-20},"4":{"valore":0,"punteggio":-50}}}},"2":{"punteggio":0,"principali":{"punteggio":0,"paletto":true,"parametri":{"5":{"valore":0,"punteggio":0},"6":{"valore":0,"punteggio":0},"7":{"valore":0,"punteggio":0}}},"modificatori":{"punteggio":0,"paletto":false,"parametri":[]}}}}
            */

            $res.='<div style="position:relative;display:inline-block;width:20%;vertical-align:top;">';
                $res.='<div id="ctv_simula_punteggio_modulo_1" style="position:relative;box-sizing:border-box;border:2px solid #1580bf;width:90%;height:30px;margin-left:5%;background-color:#91d2f188;text-align:center;line-height:30px;font-weight:bold;font-size:1.2em;">';
                    $res.=number_format($calc['incentivo'],2,'.','').' €';
                    $this->printAnalisiBody.=number_format($calc['incentivo'],2,'.','');
                $res.='</div>';
            $res.='</div>';

            $res.='<div style="position:relative;display:inline-block;width:20%;vertical-align:top;">';
                $res.='<div id="ctv_simula_punteggio_modulo_1" style="position:relative;box-sizing:border-box;border:2px solid #1580bf;width:90%;height:30px;margin-left:5%;background-color:#91d2f188;text-align:center;line-height:30px;font-weight:bold;font-size:1.2em;">';
                    $res.=number_format($calc['punteggio'],2,'.','');
                    $this->printAnalisiBody.=' ('.number_format($calc['punteggio'],2,'.','').')';
                $res.='</div>';
            $res.='</div>';

            //$res.='<div id="centavosAnalisiMainSez_'.$coll.'_'.$this->sezione['ID'].'" data-body="'.base64_encode($txt).'" ></div>';

        $res.='</div>';

        $res.='<div class="centavosAnalisiCollModulo" >';

            foreach ($this->sezione['moduli'] as $m) {

                $res.='<div style="position:relative;width:100%;margin-top:3px;">';
                    $res.='<div style="position:relative;display:inline-block;width:80%;font-weight:bold;color:#07845b;font-size:1.2em;">'.$this->moduli[$m]['titolo'].'</div>';
                    $res.='<div style="position:relative;display:inline-block;width:20%;vertical-align:bottom;">';
                        $res.='<div id="ctv_simula_punteggio_modulo_1" style="position:relative;box-sizing:border-box;border:2px solid #07845b;width:90%;height:30px;margin-left:5%;background-color:#a8ecd688;text-align:center;line-height:30px;font-weight:bold;font-size:1.2em;">';
                            $res.=number_format($calc['moduli'][$m]['punteggio'],2,'.','');
                        $res.='</div>';
                    $res.='</div>';
                $res.='</div>';

                $res.='<table style="position:relative;width:100%;height:fit-content;">';

                    $res.='<tr>';

                        $res.='<td style="position:relative;width:80%;">';

                            foreach ($this->moduli[$m]['principali'] as $km=>$kv) {

                                $res.= '<div class="ctv_moduloP_tag" style="line-height:15px;margin-top:1px;margin-bottom:1px;" >';

                                    $res.=$this->drawAnalisiParam($calc,$val,$kv,$m,'principale');

                                    $res.='<div style="position:relative;display:inline-block;width:13%;vertical-align:top;text-align:center;height:30px;">';

                                        $res.= '<div style="width:100%;top: 50%;position: relative;transform: translate(0px, -50%);">'.number_format($calc['moduli'][$m]['principali']['parametri'][$kv]['punteggio'],2,'.','').'</div>';
                            
                                    $res.='</div>';

                                $res.= '</div>';
                            }

                        $res.='</td>';

                        $res.='<td style="position:relative;width:20%;text-align:center;">';
                            $res.='<div style="position:relative;width:100%;height:100%;text-align:center;border: 2px solid #dea400;box-sizing:border-box;">';
                                //totale parametri principali
                                $res.='<div style="position:relative;top:50%;transform:translate(0px,-50%);font-size:1.2em;">'.number_format($calc['moduli'][$m]['principali']['punteggio'],2,'.','').'</div>';
                            $res.='</div>';
                        $res.='</td>';

                    $res.='</tr>';

                $res.='</table>';

                ///////////////////////////////////////////

                $res.='<table style="position:relative;width:100%;height:fit-content;">';

                    $res.='<tr>';

                        $res.='<td style="position:relative;width:80%;">';

                            foreach ($this->moduli[$m]['modificatori'] as $km=>$kv) {

                                $res.= '<div class="ctv_moduloM_tag" style="line-height:15px;margin-top:1px;margin-bottom:1px;" >';

                                    $res.=$this->drawAnalisiParam($calc,$val,$kv,$m,'modificatore');

                                    $res.='<div style="position:relative;display:inline-block;width:13%;vertical-align:top;text-align:center;height:30px;">';

                                        $res.= '<div style="width:100%;top: 50%;position: relative;transform: translate(0px, -50%);">'.number_format($calc['moduli'][$m]['modificatori']['parametri'][$kv]['punteggio'],0,'.','').' %</div>';

                                    $res.='</div>';

                                $res.= '</div>';
                            }

                        $res.='</td>';

                        $res.='<td style="position:relative;width:20%;text-align:center;">';

                            if (count($this->moduli[$m]['modificatori'])>0) {
                                $res.='<div style="position:relative;width:100%;height:100%;text-align:center;border:2px solid #616060;box-sizing:border-box;">';
                                    //totale parametri modificatori
                                    $res.='<div style="position:relative;top:50%;transform:translate(0px,-50%);font-size:1.2em;">'.number_format($calc['moduli'][$m]['modificatori']['punteggio'],0,'.','').' %</div>';
                                $res.='</div>';
                            }

                        $res.='</td>';

                    $res.='</tr>';

                $res.='</table>';

            }

        $res.='</div>';

        /*$res.='<div>';
            $res.=json_encode($this->sezione['moduli']);
        $res.='</div>';*/

        /*$res.='<div>';
            $res.=json_encode($calc);
        $res.='</div>';

        $res.='<div>';
            $res.=json_encode($this->base->getLog());
        $res.='</div>';*/


        return $res;

    }

    function drawAnalisiParam($calc,$val,$kv,$m,$tipo) {

        $res="";

        $res.='<div style="position:relative;display:inline-block;width:29%;vertical-align:top;font-size:0.9em;">';

                                

            $res.= '<div style="">'.$this->parametri[$kv]['titolo'].'</div>';
            //{"titolo":"Efficienza Pagamento","classe":"individuale","sorgente":"effP","tipo":"lineare","peso":"85","soglia":"88","paletto":"0","rettifica":"0","valore":"120","griglia":[[0,0],["",""],["",""],["",""],["",""],["",""]],"funzione":{"min":0,"max":120,"fattore":1}}
            //$res.='<div>'.json_encode($this->parametri[$kv]).'</div>';
            $res.= '<div style="font-size:0.9em;font-weight:bold;">';
                $res.=$this->parametri[$kv]['classe'];
                if ($tipo=='principale') $res.=' ('.$this->parametri[$kv]['peso'].'%)';
                if ($this->parametri[$kv]['paletto']==1) $res.='<img style="margin-left:10px;width:15px;height:15px;transform: translate(0px, 2px);" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/centavos/img/paletto.png" />';
            $res.='</div>';

        $res.='</div>';

        $res.='<div style="position:relative;display:inline-block;width:16%;vertical-align:top;text-align:center;">';

            $res.= '<div style="width:100%;">'.number_format($val[$this->parametri[$kv]['sorgente']][$this->parametri[$kv]['classe']],2,'.','').'</div>';

            $res.='<div style="font-size:0.9em;font-weight:bold;">';
                if ($this->parametri[$kv]['soglia']!="") $res.='Soglia: '.$this->parametri[$kv]['soglia'];
            $res.='</div>';

        $res.='</div>';

        $res.='<div style="position:relative;display:inline-block;width:17%;vertical-align:top;text-align:center;">';

            $res.= '<div style="width:100%;text-align:center;">';
                $res.= '<img style="width:15px;height:15px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/centavos/img/'.$this->parametri[$kv]['tipo'].'.png" />';
            $res.= '</div>';

            $res.='<div style="font-size:0.8em;font-weight:normal;">';  
                if ($this->parametri[$kv]['tipo']=='griglia') {
                    $min="";
                    $max="";
                    $ct=5;
                    //$vl=( ($this->parametri[$kv]['rettifica']==1 && $val[$this->parametri[$kv]['sorgente']]['rettifica'][$this->parametri[$kv]['classe']] )?$val[$this->parametri[$kv]['sorgente']]['rettifica'][$this->parametri[$kv]['classe']]:$val[$this->parametri[$kv]['sorgente']][$this->parametri[$kv]['classe']]);
                    $vl=$this->base->getValore($this->parametri[$kv]['sorgente'],$this->parametri[$kv]['classe']);

                    while ($ct>=1) {

                        if ((float)$vl>=(float)$this->parametri[$kv]['griglia'][$ct][0]) {
                            $min=$this->parametri[$kv]['griglia'][$ct][0];
                            break;
                        }
                        else {
                            $max=$this->parametri[$kv]['griglia'][$ct][0];
                        }

                        $ct--;
                    }

                    $res.=$min.' - '.($max!=""?$max:"");
                }
                else {
                    $res.=$this->parametri[$kv]['funzione']['min'].' - '.$this->parametri[$kv]['funzione']['max'];
                }
            $res.='</div>';

        $res.='</div>';

        $res.='<div style="position:relative;display:inline-block;width:25%;vertical-align:top;text-align:center;">';

            if ($this->parametri[$kv]['rettifica']==1) {

                if ($val[$this->parametri[$kv]['sorgente']]['rettifica'][$this->parametri[$kv]['classe']]) {

                    $res.= '<div style="width:100%;font-weight:bold;">';
                        $res.='Rettifica: '.$val[$this->parametri[$kv]['sorgente']]['rettifica'][$this->parametri[$kv]['classe']]['valore'];
                    $res.='</div>';

                    $res.= '<div style="width:100%;font-weight:normal;font-size:0.8em;margin-top:2px;">';
                        $res.= mainFunc::gab_todata($val[$this->parametri[$kv]['sorgente']]['rettifica'][$this->parametri[$kv]['classe']]['d_inserimento']).' '.$val[$this->parametri[$kv]['sorgente']]['rettifica'][$this->parametri[$kv]['classe']]['utente'];
                    $res.='</div>';

                }
            }

        $res.='</div>';

        return $res;
    }

}