<?php

require_once(DROOT."/nebula/core/calendario/calnav.php");
require_once("classi/controllo_class.php");

class qCheckApp extends appBaseClass {

    protected $collGruppo="";

    protected $qcControlli=array();

    //tiene le informazioni del controllo ACTUAL (specifico modulo e variazione selezionati)
    protected $qcInfo=array();

    //raccoglie la catena di riferimento per il form
    //$form=$a['ID_controllo'].':'.$a['versione'].':'.$a['modulo'].':'.$a['variante'];
    //IDabbinamento= ACTUAL
    protected $chain=array(
        "IDabbinamento"=>"0",
        "actualControllo"=>"0",
        "IDcontrollo"=>"0",
        "versione"=>"0",
        "modulo"=>"0",
        "variante"=>"0"
    );

    //classe navigatore del calendario
    protected $qcCalnav;

    //quando il controllo deve essere scritto,
    //serve per capire cosa l'utente è abilitato a fare
    // new , chiusura , elimina , storico , analisi
    //23.04.2021 prende il posto di (righe di qcheck_class)
    /*if ($this->collGruppo=='RS' || $this->collGruppo=='ITR') {
        $this->id->addFunzione('qcforzaChiusura',true);
        $this->id->addFunzione('qcelimina',true);
    }
    else {
        $this->id->addFunzione('qcforzaChiusura',false);
        $this->id->addFunzione('qcelimina',false);
    }*/
    protected $mainAuth=array();

    protected $navButtons=array(

    );

    protected $log=array();

    function __construct($param,$galileo) {
        
        parent::__construct($galileo);

        $this->loc='/nebula/apps/qcheck/';

        $this->param["qc_reparto"]="";
        $this->param["qc_openType"]="inserimento";
        $this->param["qc_today"]="";
        //qcheck= IDabbinamento:Controllo
        $this->param["qc_check"]="";

        $this->param['appArgs']['qc_form']="";

        $this->loadParams($param);

        //////////////////////////////
        $this->collGruppo=$this->id->getGruppo($this->param["qc_reparto"],array('A','V'));
        //////////////////////////////

        //####################################################
        //caricare i controlli

        //['VWS',"AUS"] reparti a cui è collegato l'utente e che sono attinenti alla galassia in cui si è
        //$reps=$this->id->getGalassiaRepTags();

        //carica QCHECK_abbinamenti { "reparto" IN ($reps) }
        //se il controllo è stato specificaro ( $this->param['appArgs']['qc_form'] ) carica SOLO quello 

        /*TEST
        $abb=array(
            1=>array(
                "ID"=>1,
                "reparto"=>"VWS",
                "titolo"=>"Test di officina",
                "auth"=>'{"RS":"1","RT":"1","RC":"1","ASS":"1"}',
                "data_i"=>"20210101",
                "data_f"=>"20211231",
                "versione"=>"1"
            )
        );
        //END TEST
        */

        //$d=date('Ymd');
        $sp=explode(':',$this->param["qc_check"]);
        $this->chain['IDabbinamento']=($this->param['qc_check']!=""?$sp[0]:0);
        $this->chain['actualControllo']=($this->param['qc_check']!=""?$sp[1]:0);

        if ($this->chain['actualControllo']!=0) {
            //executeSelect($tipo,$tabella,$wclause,$order) 
            $this->galileo->executeSelect('qcheck','QCHECK_auth',"controllo='".$this->chain['actualControllo']."'","");
            $result=$this->galileo->getResult();
            if ($result) {
                $fetID=$this->galileo->preFetch('qcheck');
                while ($row=$this->galileo->getFetch('qcheck',$fetID)) {
                    if ($row['gruppo']==$this->collGruppo) {
                        $this->mainAuth=json_decode($row['auth'],true);
                    }
                }
            }
        }

        $this->navButtons=array(
            'inserimento'=>array(
                "txt"=>'<button class="qc_nav_button" onclick="window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.navigator(\'inserimento\');">Inserimento</button>',
                "chk"=>false
            ),
            'storico'=>array(
                "txt"=>'<button class="qc_nav_button" onclick="window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.navigator(\'storico\');">Storico</button>',
                "chk"=>false
            ),
            'analisi'=>array(
                "txt"=>'<button class="qc_nav_button" onclick="window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.navigator(\'analisi\');">Analisi</button>',
                "chk"=>false
            )
        );

        foreach ($this->navButtons as $kb=>$b) {
            if (isset($this->mainAuth[$kb])) {
                $this->navButtons[$kb]['chk']=$this->mainAuth[$kb];
                //se l'attuale opentype NON è autorizzato
                if (!$this->mainAuth[$this->param["qc_openType"]]) {
                    if ($this->mainAuth[$kb]) $this->param["qc_openType"]=$kb;
                }
            }
        }

        /*if ($this->param["qc_check"]!="") {
            //$wclause="controllo='".$this->param["qc_check"]."' AND data_i<='".$d."' AND data_f>='".$d."'";
            $wclause="reparto='".$this->param["qc_reparto"]."' AND controllo='".$sp[1]."'";
        }
        else {*/
            //$wclause="reparto='".$this->param["qc_reparto"]."' AND data_i<='".$d."' AND data_f>='".$d."'";
            $wclause="reparto='".$this->param["qc_reparto"]."'";
        //}

        //executeSelect($tipo,$tabella,$wclause,$order) {
        $this->galileo->executeSelect("qcheck","QCHECK_abbinamenti",$wclause,"data_i DESC");

        //echo json_encode($this->galileo->getLog('query'));

        if ($this->galileo->getResult()) {

            $fetID=$this->galileo->preFetch('qcheck');

            while ($row=$this->galileo->getFetch('qcheck',$fetID)) {
                //$this->log[]=$row;
                $this->qcControlli['c'.$row['ID']]=new qcControllo($row,$this->param['nebulaFunzione'],$this->id,$this->mainAuth,$this->galileo);
                //$this->qcControlli['c'.$row['controllo'].'_'.$row['versione']]=new qcControllo($row,$this->param['nebulaFunzione'],$this->id,$this->galileo);
                //$this->qcControlli['c'.$row['controllo'].'_'.$row['versione']]->loadVersion();
                //if ($row['data_i']<=$d && $row['data_f']>=$d) $this->chain['IDabbinamento']=$row['ID'];
            }
        }

        //echo json_encode($this->qcControlli);

        /*
        $result=$this->galileo->getResult();
        //GAB500
        if ($result) {
            while ($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
				$this->qcControlli['c'.$row['ID']]=new qcControllo($row,$this->param['nebulaFunzione'],$this->galileo);
			}
        }*/

        /*tutti i controlli abbinati
        foreach ($abb as $k=>$t) {
            $this->qcControlli[$k]=new qcControllo($t,$this->param['nebulaFunzione'],$this->galileo);
        }*/

        //####################################################

        if ( isset($this->param['appArgs']['qc_form']) && $this->param['appArgs']['qc_form']!="" ) {

            $this->buildChain($this->param['appArgs']['qc_form']);

            $this->getForm();

            include('core/form_func.php');
            $this->loadClosure();
        }

        else {

            //azzera la catena del form
            $this->buildChain('0:0:0:0:0');

            include('core/'.$this->param["qc_openType"].'_func.php');
            $this->loadClosure();
        }
   
    }

    function initClass() {
        return ' qCheckCode(\''.$this->param['nebulaFunzione']['nome'].'\');';
    }

    function buildChain($arr) {
        //$a['ID_controllo'].':'.$a['versione'].':'.$a['modulo'].':'.$a['variante'];
        $a=explode(":",$arr);

        //$this->chain['controllo']=($this->param['qc_check']!=""?$this->param['qc_check']:0);
        //$this->chain['IDabbinamento']=($this->param['qc_check']!=""?$this->param['qc_check']:0);
        $this->chain['IDcontrollo']=$a[0];
        $this->chain['versione']=$a[1];
        $this->chain['modulo']=$a[2];
        $this->chain['variante']=$a[3];

        if ($a[4]!='0') {
            $this->chain['IDabbinamento']=$a[4];
        }

        /*if ($this->chain['versione']!="0") {
            $this->loadVersion($this->chain['versione']);
        }
        elseif ($this->chain['controllo']!="0") {
            //carica la versione di default
            $this->loadVersion("");
            //###############################
        }*/
    }

    function getFormLog() {
        //return $this->qcControlli['c'.$this->param["qc_check"]]->getFormLog("".$this->chain['modulo']);
        return $this->qcControlli['c'.$this->chain["IDabbinamento"]]->getFormLog("".$this->chain['modulo']);
    }

    function getCheckTitle() {
        //il titolo è un array (titolo,descrizione)
        //return $this->qcControlli['c'.$this->param["qc_check"]]->getTitle();
        return $this->qcControlli['c'.$this->chain["IDabbinamento"]]->getTitle();
    }

    /*function loadVersion($versione) {
        //per la versione di default indicare ""
        $this->qcControlli['c'.$this->param["qc_check"]]->loadVersion($versione);
    }*/

    function loadForm() {
        //$this->qcControlli['c'.$this->param["qc_check"]]->loadForm($this->chain,$this->qcInfo['risposte'],$this->qcInfo['stato_modulo']);
        $this->qcControlli['c'.$this->chain["IDabbinamento"]]->loadForm($this->chain,$this->qcInfo['risposte'],$this->qcInfo['stato_modulo']);
    }

    function customDraw() {

        echo '<div id="qc_top_div" class="qc_top_div">';
            ob_start();
                $this->openNav();
            ob_end_flush();
        echo '</div>';

        //echo json_encode($this->param);

        if ($this->param['qc_check']=="") return;

        echo '<div class="qc_main_div">';

            echo '<div id="qc_left_div" class="qc_left_div" style="background-image:url(http://'.$_SERVER['SERVER_ADDR'].'/nebula/main/img/systemBack2.png);" >';
                $this->drawLeft();
            echo '</div>';

            echo '<div id="qc_right_div" class="qc_right_div" style="border-image:url(http://'.$_SERVER['SERVER_ADDR'].'/nebula/main/img/bordo_div.png) 3;">';
                //echo '<div>'.json_encode($this->qcCalnav->getFeste()).'</div>';
                //echo '<div>'.json_encode($this->qcCalnav->getChiusure()).'</div>';
                //echo '<div>'.json_encode($this->galileo->getLog('query')).'</div>';
                $this->drawRight();
            echo '</div>';

        echo '</div>';

    }

    function openNav() {

        echo '<div style="position:relative;margin-top:5px;margin-left:20px;">';

            //echo json_encode($this->galileo->getLog('query'));
            //echo json_encode($this->mainAuth);

            echo '<div style="width:42%;display:inline-block;">';
                foreach ($this->navButtons as $k=>$c) {
                    if ($c['chk'] && $k!=$this->param['qc_openType']) {
                        echo $c['txt'];
                    }
                }
            echo '</div>';

            echo '<div style="width:40%;display:inline-block;">';

                echo '<div style="width:20%;display:inline-block;">';
                    echo '<label style="font-weight:bold;" >Check:</label>';
                echo '</div>';

                echo '<div style="width:80%;display:inline-block;">';
                    echo '<select style="width:95%;font-size:1em;" onchange="window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.navigatorCheck(this.value);" >';
                        
                        echo '<option value="">Scegli un controllo...</option>';

                        foreach ($this->qcControlli as $k=>$o) {
                            $arr=$o->getSelectParam($this->collGruppo);
                            //se le date non sono valide ritorna "0"
                            if ($arr['val']=="0") continue;

                            echo '<option value="'.$arr['val'].'"';
                                if ($arr['disabled']) echo ' disabled="disabled" ';
                                //echo ' birro="'.($o->getAuthControllo($this->collGruppo)?"T":"F").'" ';
                                //echo ' birro="'.($o->getLog()).'" ';
                                if ($this->param['qc_check']==$arr['val']) echo ' selected="selected"';
                            echo '>'.$arr['testo'].'</option>';
                            //echo '>'.json_encode($this->qcControlli[$k]->getLog()).'</option>';
                        }

                    echo '</select>';  
                echo '</div>';  
            
            echo '</div>';

            //echo json_encode($this->log);
        
        echo '</div>';
    }

    function getListOpen() {
        //estrae tutti i MODULI aperti o salvati dal punto di vista dell'ESECUTORE del controllo
        //il cui controllo appartiene al reparto selezionato nel ribbon
        //con l'autorizzazione data dalla VERSIONE (* tutti,1 solo miei,0 nessuno)

        $ret=array();

        /*$auth=$this->qcControlli['c'.$this->param["qc_check"]]->getAuthVersione($this->collGruppo);

        if ($auth=="0") return;
        */

        $args=array(
            "controllo"=>$this->chain["actualControllo"],
            "reparto"=>$this->param['qc_reparto'],
            "logged"=>$this->id->getLogged(),
            "flag"=>"*"
        );

        //executeGeneric($tipo,$funzione,$args,$order)
        $this->galileo->executeGeneric('qcheck','getListaAperti',$args,"");

        if (!$this->galileo->getResult()) return $ret;

        $fetID=$this->galileo->preFetch('qcheck');

        while ($row=$this->galileo->getFetch('qcheck',$fetID)) {
    
            $auth=$this->qcControlli['c'.$row['ID_abbinamento']]->getAuthVersione($this->collGruppo);
            if ($auth=="0") continue;

            $ret["".$row['ID_controllo']]['ID_abbinamento']=$row['ID_abbinamento'];
            $ret["".$row['ID_controllo']]['moduli'][$row['modulo']]=$row;
        }

        /*TEST
        $ret=array(
            "1"=>array(
                "1"=>array(
                    "ID_controllo"=>"1",
                    "controllo"=>"1",
                    "reparto"=>"VWS",
                    "d_controllo"=>"20210221",
                    "versione"=>"1",
                    "chiave"=>"123456",
                    "intestazione"=>"AB123CD - Mario Rossi",
                    "stato_controllo"=>"aperto",
                    "modulo"=>"1",
                    "d_modulo"=>"20210221",
                    "esecutore"=>"s.salucci",
                    "operatore"=>"n.gjura",
                    "variante"=>"1",
                    "risposte"=>'{"qc1":"0","qc2":"1","qc3":"1","qc4":"1","qc5":"1","qc1n":"gdhdrfdbd","qc2n":"","qc3n":"","qc4n":"","qc5n":""}',
                    "punteggio"=>'{"punteggio":80,"risposte":4,"domande":4}',
                    "stato_modulo"=>"salvato"
                ),
                "2"=>array(
                    "ID_controllo"=>"1",
                    "controllo"=>"1",
                    "reparto"=>"VWS",
                    "d_controllo"=>"20210221",
                    "versione"=>"1",
                    "chiave"=>"123456",
                    "intestazione"=>"AB123CD - Mario Rossi",
                    "stato_controllo"=>"aperto",
                    "modulo"=>"2",
                    "d_modulo"=>"",
                    "esecutore"=>"",
                    "operatore"=>"",
                    "variante"=>"1",
                    "risposte"=>"",
                    "punteggio"=>'',
                    "stato_modulo"=>"aperto"
                ),
                "3"=>array(
                    "ID_controllo"=>"1",
                    "controllo"=>"1",
                    "reparto"=>"VWS",
                    "d_controllo"=>"20210221",
                    "versione"=>"1",
                    "chiave"=>"123456",
                    "intestazione"=>"AB123CD - Mario Rossi",
                    "stato_controllo"=>"aperto",
                    "modulo"=>"3",
                    "d_modulo"=>"",
                    "esecutore"=>"",
                    "operatore"=>"",
                    "variante"=>"1",
                    "risposte"=>"",
                    "punteggio"=>'',
                    "stato_modulo"=>"aperto"
                )
            )
        );
    */

        //echo json_encode($this->galileo->getLog('query')); 
        return $ret;

    }

    function getFormTag() {
        //return "qc_form_".$this->qcControlli['c'.$this->chain['controllo']]->getFormTag($this->chain['modulo']);
        return "qc_form_".$this->qcControlli['c'.$this->chain['IDabbinamento']]->getFormTag($this->chain['modulo']);
    }

    function getForm() {
        //recupera il controllo ed il modulo così come sono scritti in CHAIN
        //executeGeneric($tipo,$funzione,$args,$order)
        $args=array(
            "ID"=>$this->chain['IDcontrollo'],
            "modulo"=>$this->chain['modulo']
        );

        $this->galileo->executeGeneric('qcheck','getForm',$args,"");
        if (!$this->galileo->getResult()) return;

        $fetID=$this->galileo->preFetch('qcheck');

        while ($row=$this->galileo->getFetch('qcheck',$fetID)) {
            $this->qcInfo=$row;
            //manca il campo des_variante
        }

        //echo json_encode($this->log);

        $v=$this->qcControlli['c'.$this->chain['IDabbinamento']]->getVarianti($this->chain['modulo']);
        $this->qcInfo['des_variante']=$v[$this->chain['variante']]['tag'];


        /*TEST

        $ret=array(
            "ID_controllo"=>"1",
            "controllo"=>"1",
            "reparto"=>"VWS",
            "d_controllo"=>"20210221",
            "versione"=>"1",
            "chiave"=>"123456",
            "intestazione"=>"AB123CD - Mario Rossi",
            "stato_controllo"=>"aperto",
            "modulo"=>"1",
            "d_modulo"=>"20210221",
            "esecutore"=>"s.salucci",
            "operatore"=>"n.gjura",
            "variante"=>"1",
            "risposte"=>'{"qc1":"0","qc2":"1","qc3":"1","qc4":"1","qc5":"1","qc1n":"gdhdrfdbd","qc2n":"","qc3n":"","qc4n":"","qc5n":""}',
            "punteggio"=>'{"punteggio":80,"risposte":4,"domande":4}',
            "stato_modulo"=>"salvato",
            "des_modulo"=>"modulo",
            "des_variante"=>"variante"
        );

        //END TEST
        */
    }

    function drawForm() {
        $this->qcControlli['c'.$this->chain['IDabbinamento']]->drawForm();
    }

    function drawNew() {
        $this->qcControlli['c'.$this->chain['IDabbinamento']]->drawNew();
    }

}
?>