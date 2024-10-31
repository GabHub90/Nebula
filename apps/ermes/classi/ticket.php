<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/apps/ermes/classi/chat.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/core/veicolo/classi/global_linker.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/core/chain/chain.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/core/dudu/dudu.php');

class ermesTicket {

    protected $path="/nebula/apps/ermes/img/";

    //JS per l'ambiente di gestione del ticket QUELLO DI DEFAULT VIENE SEMPRE CAMBIATO
    protected $contesto='_ermesTicket';

    protected $admin=array("TDD");

    protected $stati=array(
        "attesa"=>"/stati/attesa.png",
        "scaduto"=>"/stati/scaduto.png",
        "progress"=>"/stati/progress.png",
        "sospeso"=>"/stati/sospeso.png",
        "chiuso"=>"/stati/chiuso.png"
    );

    //urgemza 1|0 se è ammessa l'indicazione di urgenza
    //mittente: cliente (abilita campi cliente) - logged (utente)
    //box: macrorep (comune al macroreparto) - reparto (comune al reparto) - utente (diretto ad un utente specifico)
    //select: PUB (tutti possono entrarci) - PRI (solo il creatore ed un membro del reparto possono entrarci) - PTC (possono accedere i membri del reparto ed i colleghi del creatore)
    //gestione: se si può attribuire la gestione
    //kick: dopo quanto tempo appare il tasto SBLOCCA per liberare CHAIN
    //enabled: se è abilitata o no

    protected $categorie=array(
        "VWS"=>array(
            "INF"=>array(
                "titolo"=>"Telefonata Info",
                "scadenza"=>"00:00:20",
                "icon1"=>"officina.png",
                "icon2"=>"vw.png",
                "icon3"=>"pesaro.png",
                "shadow"=>"#4682b4",
                "urgenza"=>1,
                "mittente"=>"cliente",
                "box"=>"macrorep",
                "select"=>"PUB",
                "gestione"=>1,
                "kick"=>"00:00:30",
                "enabled"=>0
            )
        ),
        "AUS"=>array(
            "INF"=>array(
                "titolo"=>"Telefonata Info",
                "scadenza"=>"00:00:20",
                "icon1"=>"officina.png",
                "icon2"=>"audi.png",
                "icon3"=>"pesaro.png",
                "shadow"=>"#df385b",
                "urgenza"=>1,
                "mittente"=>"cliente",
                "box"=>"macrorep",
                "select"=>"PUB",
                "gestione"=>1,
                "kick"=>"00:00:30",
                "enabled"=>0
            )
        ),
        "PAS"=>array(
            "INF"=>array(
                "titolo"=>"Telefonata Info",
                "scadenza"=>"00:00:20",
                "icon1"=>"officina.png",
                "icon2"=>"porsche.png",
                "icon3"=>"pesaro.png",
                "shadow"=>"#c6bf00",
                "urgenza"=>1,
                "mittente"=>"cliente",
                "box"=>"macrorep",
                "select"=>"PUB",
                "gestione"=>1,
                "kick"=>"00:00:30",
                "enabled"=>0
            )
        ),
        "VGM"=>array(
            "DIS"=>array(
                "titolo"=>"Richiesta Distribuzione",
                "scadenza"=>"00:02:00",
                "icon1"=>"magazzino.png",
                "icon2"=>"vw.png",
                "icon3"=>"pesaro.png",
                "shadow"=>"#31c600",
                "urgenza"=>0,
                "mittente"=>"cliente",
                "box"=>"macrorep",
                "select"=>"PTC",
                "gestione"=>1,
                "kick"=>"00:00:30",
                "enabled"=>1
            )
        ),
        "RIT"=>array(
            "AHW"=>array(
                "titolo"=>"Assistenza Hardware",
                "scadenza"=>"00:00:10",
                "icon1"=>"pc.png",
                "icon2"=>"it.png",
                "icon3"=>"help.png",
                "shadow"=>"#9d4906",
                "urgenza"=>0,
                "mittente"=>"logged",
                "box"=>"reparto",
                "select"=>"PRI",
                "gestione"=>1,
                "kick"=>"00:00:30",
                "enabled"=>1
            ),
            "ASW"=>array(
                "titolo"=>"Assistenza Software",
                "scadenza"=>"00:00:10",
                "icon1"=>"pc.png",
                "icon2"=>"it.png",
                "icon3"=>"help.png",
                "shadow"=>"#9d4906",
                "urgenza"=>0,
                "mittente"=>"logged",
                "box"=>"reparto",
                "select"=>"PRI",
                "gestione"=>1,
                "kick"=>"00:00:30",
                "enabled"=>1
            )
        )
    );

    protected $ticket=array(
        "ID"=>0,
        "categoria"=>'',
        "reparto"=>"",
        "des_reparto"=>"",
        "creatore"=>"",
        "d_creazione"=>"",
        "d_chiusura"=>"",
        "mittente"=>'',
        "gestore"=>"",
        "urgenza"=>0,
        "stato"=>"attesa",
        "scadenza"=>"",
        "padre"=>0,
        "react"=>"",
        "nota"=>""
    );

    //------------------------------------------
    //gruppi che possono gestire i ticket in un reparto
    //se non sono specificati significa tutti
    protected $permessi=array(
        'VWS'=>array("RC","RS"),
        'AUS'=>array("RC","RS"),
        'POS'=>array("RC","RS"),
        'PAS'=>array("RC","RS")
    );
    //------------------------------------------

    protected $chat=false;

    protected $galileo;

    function __construct($galileo) {

        $this->galileo=$galileo;

        $this->path='http://'.$_SERVER['SERVER_ADDR'].$this->path;
    }

    function setContesto($c) {
        $this->contesto=$c;
    }

    function calcolaScadenza($ts,$t) {
        return mainFunc::calcolaScadenza($ts,$this->categorie[$t['reparto']][$t['categoria']]['scadenza']);
    }

    function getPermesso($reparto,$gruppo) {

        if (!array_key_exists($reparto,$this->permessi)) return true;
        else {
            if (in_array($gruppo,$this->permessi[$reparto])) return true;
            else return false;
        }
    }

    function getCategoria($rep,$cat) {

        if (!isset($this->categorie[$rep][$cat])) return false;

        return $this->categorie[$rep][$cat];
    }

    function build($t) {

        if ($t['stato']=='attesa') {
            if ($t['scadenza']=='') {
                $ts=mainFunc::gab_totsmin($t['d_creazione']);
                $t['scadenza']=$this->calcolaScadenza($ts,$t);
            }

            if ($t['scadenza']<date('Ymd:H:i')) $t['stato']='scaduto';
        }

        foreach ($this->ticket as $k=>$tk) {
            if (array_key_exists($k,$t)) $this->ticket[$k]=$t[$k];
        }

        $css=array();

        if (isset($this->categorie[$this->ticket['reparto']][$this->ticket['categoria']]['shadow']) && $this->categorie[$this->ticket['reparto']][$this->ticket['categoria']]['shadow']!="") {
            //$css["AbColor"]=mainFunc::adjustBrightness($this->categorie[$this->ticket['reparto']][$this->ticket['categoria']]['shadow'],100);
            //$css["AbdColor"]=$this->categorie[$this->ticket['reparto']][$this->ticket['categoria']]['shadow'];
        }

        if ($this->ticket['ID']!=0) {
            $this->chat=new ermesChat($this->ticket['ID'],$css,$this->galileo);
        }
    }

    public function init() {
        echo '<link href="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/ermes/core/ermes_ticket.css" type="text/css" rel="stylesheet"/>';
        echo '<script type="text/javascript" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/ermes/core/ermes_ticket.js?v='.time().'"></script>';
        echo '<script type="text/javascript">';
            $temp=base64_encode(json_encode($this->categorie));
            echo 'window._ermesTicket=new ermesTicket(\''.$temp.'\');';
        echo '</script>';
    }

    function check($config,$mono) {
        //in base alla configurazione dell'utente verifica se può accedere al ticket
        //config viene etratta della funzione di Galileo

        $auth=false;

        $select=$this->categorie[$this->ticket['reparto']][$this->ticket['categoria']]['select'];

        if ($select='PTC') {
            //se siamo in MONO accedono tutti quelli che hanno accesso all'interfaccia
            //altrienti accedono solo i membri del reparto destinatario
            if ($mono) $select='PUB';
            else $select='PRI';
        }

        if ($select=='PUB') return true;
        /*[
            {
                "ID_coll": 1,
                "nome": "Matteo",
                "cognome": "Cecconi",
                "concerto": "m.cecconi",
                "reparto": "TDD",
                "des_reparto": "Team di Direzione",
                "macroreparto": "D",
                "des_macroreparto": "Direzione",
                "officina": "",
                "ID_gruppo": 32,
                "gruppo": "TDD",
                "des_gruppo": "Direttivo",
                "pos_gruppo": 2,
                "macrogruppo": "",
                "des_macrogruppo": "",
                "pos_macrogruppo": 0,
                "sede": "PU"
            },
            {
                "ID_coll": 1,
                "nome": "Matteo",
                "cognome": "Cecconi",
                "concerto": "m.cecconi",
                "reparto": "RIT",
                "des_reparto": "IT",
                "macroreparto": "A",
                "des_macroreparto": "Amministrazione",
                "officina": "",
                "ID_gruppo": 34,
                "gruppo": "ITR",
                "des_gruppo": "Resp. IT",
                "pos_gruppo": 1,
                "macrogruppo": "",
                "des_macrogruppo": "",
                "pos_macrogruppo": 0,
                "sede": "PU"
            }
        ]*/
        
        elseif ($select=='PRI') {
            //scorre i reparti di cui fa parte il collaboratore loggato
            foreach ($config as $k=>$c) {
                if ($c['reparto']==$this->ticket['reparto']) $auth=true;
                if (in_array($c['reparto'],$this->admin)) $auth=true;
            }
        }

        return $auth;
    }

    function drawTodoIntest() {

        if (isset($this->categorie[$this->ticket['reparto']][$this->ticket['categoria']]['shadow']) && $this->categorie[$this->ticket['reparto']][$this->ticket['categoria']]['shadow']!="") {
            $temp=$this->categorie[$this->ticket['reparto']][$this->ticket['categoria']]['shadow'];
            $tempbk=mainFunc::adjustBrightness($this->categorie[$this->ticket['reparto']][$this->ticket['categoria']]['shadow'],100).'55';
        }
        else {
            $temp='transparent';
            $tempbk='#cccccc55';
        }

        $mitt=json_decode($this->ticket['mittente'],true);

        echo '<div style="padding:2px;background-color:'.$tempbk.';border: 2px solid '.$temp.';border-radius: 5px;">';
            echo $this->ticket['ID'];
            echo '<img class="ermes_ticket_head_icon" src="'.$this->path.$this->categorie[$this->ticket['reparto']][$this->ticket['categoria']]['icon1'].'" />';
            echo '<img class="ermes_ticket_head_icon" src="'.$this->path.$this->categorie[$this->ticket['reparto']][$this->ticket['categoria']]['icon2'].'" />';
            echo ' - '.(isset($mitt['ragsoc'])?strtoupper(substr($mitt['ragsoc'],0,25)):'');
        echo '</div>';
    }

    function drawHead($flag) {

        if (isset($this->categorie[$this->ticket['reparto']][$this->ticket['categoria']]['shadow']) && $this->categorie[$this->ticket['reparto']][$this->ticket['categoria']]['shadow']!="") {
            $temp=$this->categorie[$this->ticket['reparto']][$this->ticket['categoria']]['shadow'];
            $tempbk=mainFunc::adjustBrightness($this->categorie[$this->ticket['reparto']][$this->ticket['categoria']]['shadow'],100).'55';
        }
        else {
            $temp='transparent';
            $tempbk='#cccccc55';
        }

        echo '<div style="padding:2px;background-color:'.$tempbk.';border: 2px solid '.$temp.';border-radius: 5px;">';
            echo '<div style="position:relative;display:inline-block;vertical-align:top;width:50%;font-weight:bold;" >';
                echo $this->ticket['ID'];
                if ($flag) {
                    echo '<img class="ermes_ticket_head_icon" src="'.$this->path.$this->categorie[$this->ticket['reparto']][$this->ticket['categoria']]['icon3'].'" />';
                }
                echo '<img class="ermes_ticket_head_icon" src="'.$this->path.$this->categorie[$this->ticket['reparto']][$this->ticket['categoria']]['icon1'].'" />';
                echo '<img class="ermes_ticket_head_icon" src="'.$this->path.$this->categorie[$this->ticket['reparto']][$this->ticket['categoria']]['icon2'].'" />';
                echo '<span style="margin-left:10px;">'.$this->categorie[$this->ticket['reparto']][$this->ticket['categoria']]['titolo'].'</span>';
            echo '</div>';

            echo '<div style="position:relative;display:inline-block;vertical-align:bottom;width:50%;text-align:right;font-weight:bold;" >';
                echo $this->ticket['des_reparto'];

                if (!$flag) {
                    if ($this->ticket['gestore']=="") {
                        echo '<span style="color:red;"> - Non Gestito</span>';
                    }
                    else echo '<span style="font-size:0.9em"> - '.$this->ticket['gestore'].'</span>';
                }
            echo '</div>';

        echo '</div>';


    }

    function drawLista($config,$mono) {

        if (!$this->ticket) return;

        if (!$config) $check=true;
        else $check=$this->check($config,$mono);

        //echo '<div class="ermes_ticket_panorama" style="box-shadow:2px 2px '.$this->categorie[$this->ticket['reparto']][$this->ticket['categoria']]['shadow'].';">';
        echo '<div class="ermes_ticket_panorama">';

            $this->drawHead(false);

            $mitt=json_decode($this->ticket['mittente'],true);

            echo '<div style="position:relative;height:15px;margin-top:5px;">';

                echo '<div style="position:relative;display:inline-block;vertical-align:top;width:42%;" >';

                    echo '<div style="height:15px;">'.(isset($mitt['ragsoc'])?strtoupper($mitt['ragsoc']):'').'</div>';
                    //echo '<div style="height:15px;">'.(isset($mitt['telefono'])?$mitt['telefono']:'').'</div>';
                    //echo '<div style="height:15px;">'.(isset($mitt['mail'])?$mitt['mail']:'').'</div>';

                echo '</div>';

                echo '<div style="position:relative;display:inline-block;vertical-align:top;width:16%;height:100%;text-align:center;top:-28px;cursor:pointer;" onclick="window.'.$this->contesto.'.apriTicket(\''.($check?$this->ticket['ID']:0).'\',\''.$this->contesto.'\')">';
                    echo '<div style="position:relative;width:50px;height:48px;border:1px solid black;border-radius:999em;left:50%;transform:translate(-50%,0);text-align:center;background-color:white;" >';
                        echo '<img style="position:relative;width:85%;height:85%;top:50%;transform:translate(0,-50%);" src="'.(isset($this->stati[$this->ticket['stato']])?$this->path.$this->stati[$this->ticket['stato']]:'').'" />';
                    echo '</div>';
                echo '</div>';

                echo '<div style="position:relative;display:inline-block;vertical-align:top;width:42%;text-align:right;" >';
                    //echo '<div style="height:15px;font-size:0.8em;">Creato: '.$this->ticket['creatore'].' - '.mainFunc::gab_todata(substr($this->ticket['d_creazione'],0,8)).' '.substr($this->ticket['d_creazione'],9,5).'</div>';
                    echo '<div style="height:15px;font-weight:bold;font-size:0.9em;';
                        if ($this->ticket['stato']=='scaduto') echo 'color:red;';
                    echo '">';
                        if ($this->ticket['stato']=="chiuso") {
                            echo 'Chiuso: '.mainFunc::gab_todata(substr($this->ticket['d_chiusura'],0,8)).' '.substr($this->ticket['d_chiusura'],9,5);
                        }
                        elseif ($this->ticket['scadenza']!="") {
                            echo 'Scadenza: '.mainFunc::gab_todata(substr($this->ticket['scadenza'],0,8)).' '.substr($this->ticket['scadenza'],9,5);
                        }
                    echo '</div>';
                echo '</div>';

            echo '</div>';

            if ($this->chat) {
                //$this->chat->buildPanorama();
                $this->chat->drawPanorama();
            }

        echo '</div>';
    }

    function drawNew($reparti,$logged,$padre,$rep,$cat) {

        $this->init();

        nebulaGlobalLinker::drawJS();

        echo '<div style="position:relative;display:inline-block;width:70%;height:100%;vertical-align:top;padding:3px;box-sizing:border-box;border-right:1px solid black;" >';

            echo '<div style="position:relative;font-size:1.3em;font-weight:bold;" >Apertura nuovo ticket da: ( '.$logged.' )</div>';

                echo '<input id="ermes_ticket_form_logged" type="hidden" value="'.$logged.'" />';
                echo '<input id="ermes_ticket_form_padre" type="hidden" value="'.$padre.'" />';

            echo '<div style="position:relative;margin-top:10px;">';

                //$first=true;
                //$tempRep="";

                echo '<div style="position:relative;display:inline-block;width:60%;vertical-align:top;">';
                    echo '<div class="ermes_ticket_form_lable">';
                        echo '<lable>Reparto destinatario:</lable>';
                    echo '</div>';
                    echo '<div>';
                        echo '<select id="ermes_ticket_form_reparto" class="ermesTicketSelect" style="width:95%;" data-mono="'.$cat.'" ';
                            if ($rep=='') echo ' onchange="window._ermesTicket.chgReparto(this.value);"';
                        echo '>';
                        

                            if ($rep!='') {
                                foreach ($reparti as $sede=>$s) {
                                    foreach($s as $mrep=>$m) {
                                        foreach ($m as $reparto=>$r) {
                                            if ($reparto==$rep)  echo '<option value="'.$reparto.'" data-mrep="'.$mrep.'" data-des="'.$r.'">'.$reparto.' - '.$r.'</option>';
                                        }
                                    }
                                }
                            }
                            else {

                                echo '<option value="">Seleziona un reparto...</option>';
                                foreach ($reparti as $sede=>$s) {
                                    echo '<option value="" disabled>'.$sede.' --------------------------</option>';

                                    foreach($s as $mrep=>$m) {
                                        foreach ($m as $reparto=>$r) {
                                            if (isset($this->categorie[$reparto]) && count($this->categorie[$reparto])>0) {

                                                foreach ($this->categorie[$reparto] as $kat=>$o) {
                                                    if ($o['enabled']==1) {
                                                        echo '<option value="'.$reparto.'" data-mrep="'.$mrep.'" data-des="'.$r.'">'.$reparto.' - '.$r.'</option>';
                                                        break;
                                                    }
                                                }
                                            }
                                            /*if ($first) {
                                                $tempRep=$reparto;
                                                $first=false;
                                            }*/
                                        }
                                    }
                                }
                            }
                        echo '</select>';
                    echo '</div>';
                echo '</div>';

                echo '<div id="ermes_ticket_form_icons" style="position:relative;display:inline-block;width:30%;vertical-align:bottom;">';
                echo '</div>';
                
            echo '</div>';

            echo '<div id="ermes_ticket_form_head" style="position:relative;margin-top:10px;height:180px;">';
            echo '</div>';

            echo '<div style="position:relative;margin-top:10px;width:95%;">';

                echo '<div class="ermes_ticket_form_lable">';
                    echo '<lable>Nota Interna:</lable>';
                echo '</div>';

                echo '<div>';
                    echo '<input id="ermes_ticket_form_nota" type="text" style="width:100%;" />';
                echo '</div>';

            echo '</div>';

            echo '<div style="position:relative;margin-top:10px;width:95%;text-align:center;">';

                echo '<div class="ermes_ticket_form_lable">';
                    echo '<lable>Messaggio</lable>';
                echo '</div>';

                echo '<div style="position:relative;width:100%;border:3px solid orange;border-radius:10px;padding:10px;box-sizing:border-box;" >';
                    echo '<textarea id="ermes_ticket_form_messaggio" style="text-align:center;width:90%;resize:none;" rows="3" onkeyup="window._ermesTicket.check();" />';
                echo '</div>';

            echo '</div>';

            echo '<div style="position:relative;margin-top:10px;width:90%;text-align:right;">';
                echo '<span id="ermes_ticket_form_error" style="color:red;font-weight:bold;margin-right:10px;"></span>';
                echo '<button id="ermes_ticket_form_button" style="display:none;" onclick="window._ermesTicket.confirm(\''.$this->contesto.'\');" >Apri Ticket</button>';
            echo '</div>';

        echo '</div>';

        echo '<div id="ermes_ticket_util" style="position:relative;display:inline-block;width:30%;height:100%;vertical-align:top;padding:3px;box-sizing:border-box;border-right:1px solid black;" >';
            echo '<div style="position:relative;height:10%;width:100%;text-align:center;">';
                echo '<button id="ermes_ticket_util_cerca" style="margin-top:5px;background-color: thistle;display:none;" onclick="window._globalLinker.readListaErmes();" >Cerca</button>';
                echo '<script type="text/javascript" >';
                    echo 'window._globalLinker=new nebulaGlobalLinker();';
                    echo 'window._globalLinker.setContesto(\'_ermesTicket\');';
                echo '</script>';
            echo '</div>';
            echo '<div id="global_linker_lista_div" style="position:relative;height:90%;width:100%;" ></div>';
        echo '</div>';

        if ($rep!="") {
            echo '<script type="text/javascript" >';
                echo 'window._ermesTicket.chgReparto(\''.$rep.'\');';
            echo '</script>';
        }

    }

    function drawTicket($logged) {

        //echo '<div>'.json_encode($this->ticket).'</div>';

        //#######################
        //CHAIN
        $chain=new nebulaChain('ermes',$this->galileo);
        $c=$chain->execute($this->ticket['ID'],$logged);
        //#######################
        
        $this->init();

        echo '<script type="text/javascript">';
            echo 'window._ermesTicket.actualID="'.$this->ticket['ID'].'";';
            echo 'window._ermesTicket.caller="'.$this->contesto.'";';
        echo '</script>';

        nebulaChain::drawJS();

        $mitt=json_decode($this->ticket['mittente'],true);

        echo '<div style="position:relative;display:inline-block;width:70%;height:100%;vertical-align:top;padding:3px;box-sizing:border-box;border-right:1px solid black;" >';

            echo '<div id="ermes_ticket_body_main" style="width:100%;height:100%" >';

                echo '<div style="position:relative;height:22%;border-bottom:1px dotted black;box-sizing:border-box;" >';

                    $this->drawHead(true);

                    echo '<div style="position:relative;height:70%;margin-top:0.5%;font-size:1.1em;" >';

                        echo '<div style="position:relative;display:inline-block;vertical-align:top;width:45%;" >';

                            echo '<div style="height:25%;"><b>'.(isset($mitt['ragsoc'])?strtoupper($mitt['ragsoc']):'').'</b></div>';
                            echo '<div style="height:25%;">'.(isset($mitt['telefono'])?$mitt['telefono']:'').'</div>';
                            echo '<div style="height:25%;">'.(isset($mitt['mail'])?$mitt['mail']:'').'</div>';

                        echo '</div>';

                        echo '<div style="position:relative;display:inline-block;vertical-align:top;width:10%;text-align:center;" >';

                            echo '<div style="position:relative;width:50px;height:48px;border:1px solid black;border-radius:999em;left:50%;transform:translate(-50%,0);text-align:center;background-color:white;" >';
                                echo '<img style="position:relative;width:85%;height:85%;top:50%;transform:translate(0,-50%);" src="'.(isset($this->stati[$this->ticket['stato']])?$this->path.$this->stati[$this->ticket['stato']]:'').'" />';
                            echo '</div>';

                        echo '</div>';

                        echo '<div style="position:relative;display:inline-block;vertical-align:top;width:45%;text-align:right;" >';

                            echo '<div style="height:25%;font-size:0.9em;">';
                                echo 'creato il: '.mainFunc::gab_todata(substr($this->ticket['d_creazione'],0,8)).' '.substr($this->ticket['d_creazione'],9,5).' - '.$this->ticket['creatore'];
                            echo '</div>';

                            echo '<div style="height:25%;">';
                                if ($this->ticket['gestore']=="") {
                                    echo '<span style="color:red;">Non Gestito</span>';
                                }
                                else echo '<span style="">Gestore:&nbsp;<b>'.$this->ticket['gestore'].'</b></span>';
                            echo '</div>';

                            echo '<div style="height:25%;';
                                if ($this->ticket['stato']=='scaduto') echo 'color:red;';
                            echo '">';  

                                if ($this->ticket['scadenza']!="") {
                                    if ($this->ticket['urgenza']==1) echo '<b>URGENTE -&nbsp;</b>';
                                    echo 'Scadenza: '.mainFunc::gab_todata(substr($this->ticket['scadenza'],0,8)).' '.substr($this->ticket['scadenza'],9,5);
                                }
                            
                            echo '</div>';

                        echo '</div>';

                        echo '<div style="text-align:center;font-weight:bold;color:chocolate;">';  

                            echo $this->ticket['nota'];
                        
                        echo '</div>';

                    echo '</div>';

                echo '</div>';

                echo '<div id="ermes_ticket_mainChat" style="position:relative;height:65%;overflow:scroll;overflow-x:hidden;box-sizing:border-box;" >';
                    
                    echo '<div style="width:95%;">'.$this->chat->draw().'</div>';

                    if ($c['ok'] && $this->ticket['stato']!='chiuso') {

                        echo '<div style="width:95%;">';

                            if ($logged==$this->ticket['creatore']) {
                                $this->chat->newBubble('Q',$logged,'');
                            }
                            else {
                                $this->chat->newBubble('A',$logged,$this->ticket['gestore']);
                            }

                        echo '</div>';
                    }
                    else {
                        echo '<div style="width:95%;color:red;font-weight:bold;text-align:center;">';
                            echo 'TICKET BLOCCATO';
                        echo '</div>';
                    }

                echo '</div>';

                echo '<script type="text/javascript">';
                    echo '$("#ermes_ticket_mainChat").scrollTop(document.getElementById("ermes_ticket_mainChat").scrollHeight);';
                echo '</script>';

                echo '<div style="position:relative;height:13%;border-top:1px dotted black;box-sizing:border-box;" >';
                echo '</div>';
            
            echo '</div>';

            echo '<div id="ermes_ticket_body_util" style="position:relative;width:100%;height:100%;display:none;" >';
                    echo '<div style="width:100%;height:10%;text-align:right;" >';
                        echo '<img style="width:30px;height:30px;" src="'.$this->path.'back.png" onclick="window._ermesTicket.chiudiUtil();" />';
                    echo '</div>';
                    echo '<div id="ermes_ticket_body_util_main" style="width:100%;height:90%;" ></div>';
            echo '</div>';

        echo '</div>';

        //////////////////////////////////////////////////////////////////////////

        echo '<div style="position:relative;display:inline-block;width:30%;height:100%;vertical-align:top;padding:10px;box-sizing:border-box;" >';
            
            /*CHAIN
            $res=array(
                "ok"=>false,
                "error"=>"",
                "row"=> {"app":"ermes","chiave":"1","utente":"m.cecconi","dataora":"20230927:10:42"}
            );*/

            echo '<div style="height:22%;">';
            
                echo '<div style="position:relative;width:98%;font-weight:bold;color:#777777;" >';

                    echo '<div>Ticket in uso da:</div>';

                    echo '<div>';

                        if (count($c['row'])>0) {
                            if(isset($this->categorie[$this->ticket['reparto']][$this->ticket['categoria']]['kick'])) {
                                $tempkick=mainFunc::calcolaScadenza(mainFunc::gab_totsmin($c['row']['dataora']),$this->categorie[$this->ticket['reparto']][$this->ticket['categoria']]['kick']);
                            }
                            else $tempkick='xxxxxxxx:xx:xx';

                            echo '<div style="margin-right:10px;" >'.$c['row']['utente']. ' - '.mainFunc::gab_todata(substr($c['row']['dataora'],0,8)).' '.substr($c['row']['dataora'],9,5).'</div>';
                            
                        }
                        else {
                            echo '<div style="margin-right:10px;" > Non è stato possibile recuparere le informazioni</div>';
                        }

                        //echo '<div>'.json_encode($c).'</div>';
                        //echo '<div>'.$tempkick.'</div>';
                    
                        if (!$c['ok']) {

                            if ($tempkick=='xxxxxxxx:xx:xx' || $tempkick<date('Ymd:H:i')) {

                                echo '<div>';

                                    $chain->drawSblockButton('Sblocca',$c['row']['chiave']);

                                    echo '<script type="text/javascript" >';
                                        echo 'window._nebulaChain.postExecute=function(){window.'.$this->contesto.'.apriTicket('.$this->ticket['ID'].',\''.$this->contesto.'\');};';
                                    echo '</script>';
                                echo '</div>';
                            }
                        }

                    echo '</div>';
                    
                echo '</div>';

                echo '<div style="position:relative;width:98%;margin-top:20px;" >';

                    if ($this->ticket['stato']!='chiuso') {

                        echo '<div style="position:relative;display:inline-block;width:20%;padding:5px;vertical-align:top;box-sizing:border-box;text-align:center;" >';
                            if ($this->chat->getStat('numA')==0 && $logged!=$this->ticket['creatore']) {
                                echo '<img style="width:35px;height:35px;cursor:pointer;" src="'.$this->path.'stati/inoltro.png" onclick="window._ermesTicket.setInoltra(\''.$logged.'\');"/>';
                            }
                        echo '</div>';

                        echo '<div style="position:relative;display:inline-block;width:20%;padding:5px;vertical-align:top;box-sizing:border-box;text-align:center;" >';
                            if($this->categorie[$this->ticket['reparto']][$this->ticket['categoria']]['gestione']==1 && $logged!=$this->ticket['creatore']) {
                                echo '<img style="width:35px;height:35px;cursor:pointer;" src="'.$this->path.'stati/gestione.png" onclick="window._ermesTicket.setForzaGestione(\''.$this->ticket['reparto'].'\',\''.$logged.'\');"/>';
                            }
                        echo '</div>';

                        echo '<div style="position:relative;display:inline-block;width:20%;padding:5px;vertical-align:top;box-sizing:border-box;text-align:center;" >';
                            if ($logged!=$this->ticket['creatore']) {
                                if ($this->ticket['stato']=='progress') {
                                    echo '<img style="width:35px;height:35px;cursor:pointer;" src="'.$this->path.'stati/sospeso.png" onclick="window._ermesTicket.cambiaStato(\'sospeso\');" />';
                                }
                                elseif ($this->ticket['stato']=='sospeso') {
                                    echo '<img style="width:35px;height:35px;cursor:pointer;" src="'.$this->path.'stati/progress.png" onclick="window._ermesTicket.cambiaStato(\'progress\');" />';
                                }
                            }
                        echo '</div>';

                        echo '<div style="position:relative;display:inline-block;width:20%;padding:5px;vertical-align:top;box-sizing:border-box;text-align:center;" >';
                            /*if ($logged==$this->ticket['gestore']) {
                                echo '<img style="width:35px;height:35px;cursor:pointer;" src="'.$this->path.'stati/link.png" />';
                            }*/ 
                        echo '</div>';

                        echo '<div style="position:relative;display:inline-block;width:20%;padding:5px;vertical-align:top;box-sizing:border-box;text-align:center;" >';
                            echo '<img style="width:35px;height:35px;cursor:pointer;" src="'.$this->path.'stati/chiuso.png" onclick="window._ermesTicket.concludi(\''.$logged.'\')" />';
                        echo '</div>';
                    }

                echo '</div>';

            echo '</div>';

            echo '<div style="position:relative;width:100%;height:10%;">';

                if ($this->ticket['stato']!='chiuso') {

                    echo '<div style="margin-top:10px;">';
                        echo '<div style="position:relative;display:inline-block;vertical-align:top;width:20%;text-align:center;" >';
                            echo '<img style="width:35px;height:35px;cursor:pointer;" src="'.$this->path.'stati/scaduto.png" />';
                        echo '</div>';
                        echo '<div style="position:relative;display:inline-block;vertical-align:top;width:50%;text-align:left;" >';
                            echo '<input id="ermesTicket_set_scadenza" style="width:150px;margin-top:5px;" type="date" />';
                        echo '</div>';
                        echo '<div style="position:relative;display:inline-block;vertical-align:top;width:20%;text-align:left;" >';
                            echo '<button style="margin-top:5px;" onclick="window._ermesTicket.setScadenza();" >Scadenza</button>';
                        echo '</div>';
                    echo '</div>';
                }

            echo '</div>';

            echo '<div id="ermesTicketDudu" style="position:relative;width:100%;height:68%;">';

                if ($logged!=$this->ticket['creatore']) {

                    $this->galileo->clearQuery();

                    /*$id=0;
                    $this->galileo->executeSelect('dudu','DUDU_link',"app='ermes' AND rif='".$this->ticket['ID']."'",'');

                    if ($this->galileo->getResult()) {
                        //selezione ID del TODO
                        $fid=$this->galileo->preFetch('dudu');
                        while($row=$this->galileo->getFetch('dudu',$fid)) {
                            $id=$row['dudu'];
                        }

                    }*/

                    $todo=new nebulaDudu("_ermesTicket",$this->galileo);
                    $todo->loadLink('ermes',$this->ticket['ID']);
                    $todo->draw('ermes',($this->ticket['stato']=='chiuso')?0:1);
                }

            echo '</div>';

        echo '</div>';

        echo '<script type="text/javascript" >';
            echo 'window._ermesTicket.resetTime();';
        echo '</script>';

    }

}