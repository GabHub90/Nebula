<?php

class nebulaLogged {

    protected $stato=array(
        "auth"=>false,
        "app"=>false,
        "msg"=>""
    );

    protected $cookieLogged="";
    protected $mainLogged="";
    protected $mainPassword="";
    protected $mainApp="";

    protected $utenti=array();
    protected $configUtente=array(
        "generale"=>array(),
        "apps"=>array(),
        "funzioni"=>array()
    );

    //se si vuole impostare il cookie in giorni mettere il numero
    // "tilmid" significa fino a mezzanotte
    protected $giorni='tilmid';

    //elenco di tutte le galassie ed i relativi sistemi possibili
    protected $galassie=array();

    //codice di inizializzazione App (array di righe di codice)
    //include head,css,js
    //codice js (code)
    protected $initApp=array(
        "head"=>array(),
        "css"=>array(),
        "js"=>array(),
        "code"=>array(),
        "body"=>array()
    );

    protected $universe;
    protected $galileo;

    protected $log=array();

    function __construct($pw,$contesto,$galileo) {

        $this->universe=new nebulaUniverse();

        $this->galassie=$this->universe->getGalassie();

        //$nebulaContesto['cookieLogged'],$nebulaContesto['mainLogged'],$pw,$nebulaContesto['mainApp']
        $this->cookieLogged=$contesto['cookieLogged'];
        $this->mainLogged=$contesto['mainLogged'];
        $this->mainPassword=$pw;
        $this->mainApp=$contesto['mainApp'];

        $this->galileo=$galileo;

        /*$this->galileo->getUtenti();
        $result=$this->galileo->getResult();
        if ($result) {
            $fetID=$this->galileo->preFetchBase('utenti');
            while ($row=$this->galileo->getFetchBase('utenti',$fetID)) {
                $this->utenti[$row['cod_utente']]=$row;
            }
        }*/

        $this->galileo->getLogin();
        $result=$this->galileo->getResult();
        if ($result) {
            $fetID=$this->galileo->preFetchBase('maestro');
            while ($row=$this->galileo->getFetchBase('maestro',$fetID)) {
                $this->utenti[$row['cod_utente']]=$row;
            }
        }

        $this->checkLogged();

        //$this->log[]=$this->galileo->getLog('query');
    }

    function getLog() {
        return $this->log;
    }

    function getConfigUtente() {
        return $this->configUtente;
    }

    function buildContesto() {

        $gs=explode(':',$this->mainApp);

        $c=array(
            "mainLogged"=>$this->mainLogged,
            "mainApp"=>$this->mainApp,
            "mail"=>isset($this->utenti[$this->mainLogged])?$this->utenti[$this->mainLogged]['mail']:"",
            "mainGalaxie"=>$this->galassie[$gs[0]],
            "configUtente"=>$this->configUtente
        );

        return $c;
    }

    function initApp() {

        //carica la configurazione per l'applicazione da inserire in HEAD e BODY
        if ($this->stato['app']==false) return;

        $gs=explode(':',$this->mainApp);

        /////////////////////////////////
        $arr=array();
        //legge l'array arr
        include(DROOT.$this->galassie[$gs[0]][$gs[1]]['loc'].'init.php');
        /////////////////////////////////

        foreach($this->initApp as $k=>$v) {
            if ( array_key_exists($k,$arr) ) {

                foreach ($arr[$k] as $n) {
                    $this->initApp[$k][]=$n;
                }
            }
        }

    }

    function startApp($linkFunk) {
        //scrive l'app del DIV principale
        if ($this->stato['app']==false) return;

        $gs=explode(':',$this->mainApp);

        $ch = curl_init('http://'.SADDR.$this->galassie[$gs[0]][$gs[1]]['loc'].'main.php');
        curl_setopt($ch, CURLOPT_HEADER, 0);

        ///////////////////////////////////////////////////
        //PASSAGGIO DEGLI ARGOMENTI TRAMITE JSON
        $p=array();
        $p['nebulaContesto']=$this->buildContesto();
        $p['nebulaContesto']['linkFunk']=$linkFunk;

        $post=json_encode($p);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$post);
        //////////////////////////////////////////////////

        if( ! $result = curl_exec($ch)) { 
            trigger_error(curl_error($ch)); 
        } 
        curl_close($ch); 
        return $result;

    }

    function checkLogged() {
        //verifica le condizioni di login

        $this->stato['msg']="";

        //se c'è un cookie
        if ($this->cookieLogged!="") {

            $ev=$this->checkDb();

            if ($ev) {
                $this->stato['auth']=true;
                $this->mainLogged=$this->cookieLogged;

                if($this->mainApp=="") $this->mainApp="home:home";
                
                //se esiste la configurazione dell'utente
                if ( $this->loadConfig() ) {
                    $this->checkApp();
                }
                
            }
            else {
                $this->stato['auth']=false;
                $this->stato['app']=false;
                $this->stato['msg']="login scaduto";
                $this->mainApp='home:login';
                $this->checkApp();
            }

        }

        //se non c'è un cookie
        else {

            //se non è specificato nessun utente
            if ($this->mainLogged=="") {
                $this->stato['auth']=false;
                $this->stato['app']=false;
                $this->stato['msg']="login necessario";
                $this->mainApp='home:login';
                $this->checkApp();
            }
            else {
                $ev=$this->checkPw();

                if ($ev) {
                    $this->stato['auth']=true;
                     //se esiste la configurazione dell'utente
                    if ( $this->loadConfig() ) {
                        $this->checkApp();
                    }
                }
                else {
                    $this->stato['auth']=false;
                    $this->stato['app']=false;
                    $this->stato['msg']="credenziali errate";
                    $this->mainApp='home:login';
                    $this->checkApp();
                }
            }
        }
    }

    function checkDb() {
        //verifica da DB che il login sia ancora valido
        return true;
    }

    function checkPw() {
        //verifica le credenziali di login
        $ret=false;

        //echo json_encode($this->galileo->getLog('query'));

        if ( array_key_exists($this->mainLogged,$this->utenti) ) {
            if ($this->utenti[$this->mainLogged]['des_pwd']==$this->mainPassword) $ret=true;
        }

        return $ret;
    }

    function checkApp() {

        //verifica nell'array galassie se l'applicazione selezionata è abilitata per l'utente
          
        $gs=explode(':',$this->mainApp);

        if ( $this->galassie[$gs[0]][$gs[1]]['chk'] ) {
            $this->stato['app']=true;
        }

    }

    function loadConfig() {
        //carica la configurazione dell'utente validato
        $this->galileo->clearQuery();
        $this->galileo->getConfigUtente($this->mainLogged,date('Ymd'));
        $result=$this->galileo->getResult();

        if ($result) {
            $fetID=$this->galileo->preFetchBase('maestro');
            //while ($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
            while ($row=$this->galileo->getFetchBase('maestro',$fetID)) {
                $this->configUtente['generale'][$row['reparto']][$row['gruppo']]=$row;
            }

            ///////////////////////////////////////
            //carica configurazione applicazioni per l'utente
            //ed aggiorn array GALASSIE
            $this->galileo->clearQuery();
            $this->galileo->getConfigAppUtente($this->configUtente['generale']);
            $result=$this->galileo->getResult();

            if ($result) {
                $fetID=$this->galileo->preFetchBase('applicazioni');
                //while ($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
                while ($row=$this->galileo->getFetchBase('applicazioni',$fetID)) {
                    //i record sono in ordine di focus: MACROREP , REPARTO , MACROGRU , GRUPPO , UTENTE
                    //identificano GALASSIA (obbligatorio) , SISTEMA (* = tutto) , FUNZIONE (null oppure funzione)
                    //se viene specificata la funzione => sistema e galassia NON possono essere *
                    //MODIFICATORE: 1=true / 0=false 
                    
                    //se non è specificato sistema e galassia il record non è accettabile
                    if ($row['sistema']=='' || $row['galassia']=='') continue;

                    //se funzione scrivi in array funzioni
                    if ($row['funzione']!="") {
                        if ($row['sistema']!='*' && $row['galassia']!='*') {
                            $this->configUtente['funzioni'][$row['galassia']][$row['sistema']][$row['funzione']]=($row['modificatore']==1)?true:false;
                        }
                    }
                    //altrimenti aggiorna array GALASSIE
                    else {
                        foreach ($this->galassie as $galassia=>$g) {
                            if ($row['galassia']=='*' || $galassia==$row['galassia']) {
                                foreach ($g as $sistema=>$s) {
                                    if ($row['sistema']=='*' || $row['sistema']==$sistema) {
                                        //se il modificatore è false , chk è true ma chg è true (rimani in true)
                                        if ($row['modificatore']==0 && $this->galassie[$galassia][$sistema]['chk'] && $this->galassie[$galassia][$sistema]['chg']) {
                                            $this->galassie[$galassia][$sistema]['chk']=true;
                                        }
                                        //in tutti gli altri casi imposta come da modificatore
                                        else {
                                            $this->galassie[$galassia][$sistema]['chk']=($row['modificatore']==1)?true:false;
                                            $this->galassie[$galassia][$sistema]['chg']=true;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            ///////////////////////////////////////

            //aggiorna la configurazione dell'utente
            //se la lettura del DB non è andata a buon fine la configurazione di default è comunque FALSE
            foreach ($this->galassie as $galassia=>$g) {
                foreach ($g as $sistema=>$s) {
                    if ($s['chk']) $this->configUtente['apps'][$galassia][$sistema]=$this->galassie[$galassia][$sistema]['tag'];
                }
            }
            //$this->configUtente['apps']=ARRAY RISULTANTE
        
            return true;
        }

        else {
            $this->stato['app']=false;
            $this->stato['msg']="nessuna configurazione";
            $this->mainApp='home:home';
            return false;
        }
    }

    function drawHead() {

        $nebulaContesto=$this->buildContesto();

        //gestisce il COOKIE di LOGIN
        // 86400 = 1 day

        //se l'autorizzazione è fallita
        if (!$this->stato['auth']) setcookie("nebulaLogged","deleted", time() - (86400 * 1), "/");
        else {
            //se il cookie non era stato fissato
            if ($this->cookieLogged=="") {

                if ($this->giorni=='tilmid') {
                    $d1=date('Ymd:H:i:s');
                    $d2=date('Ymd').':23:59:59';
                    setcookie("nebulaLogged", $this->mainLogged, time() + mainFunc::gab_delta_tempo_c($d1,$d2,'s'), "/");
                    //echo '<meta name="log" content="'.mainFunc::gab_delta_tempo_c($d1,$d2,'s').'" >';
                }
                else {
                    setcookie("nebulaLogged", $this->mainLogged, time() + (86400 * $this->giorni), "/");
                }
            }
        }
        echo '<meta http-equiv="Content-Type" content="text/html;charset=utf-8" >';

        //scelta della FAVICON
        $icon='nebula';
        $gs=explode(':',$this->mainApp);
        if ( array_key_exists($gs[1],$this->galassie[$gs[0]]) ) {
            $icon=$this->galassie[$gs[0]][$gs[1]]['icon'];
        }

        echo '<title>'.ucfirst($icon).'</title>';
        echo '<link rel="shortcut icon" href="main/img/favicons/'.$icon.'/favicon.ico">';
    
        echo '<link rel="stylesheet" type="text/css" href="http://'.SADDR.'/nebula/main/main.css?v='.time().'" />';
        
        foreach ($this->initApp['css'] as $c) {
            echo '<link rel="stylesheet" type="text/css" href="'.$c.'?v='.time().'" />';
        }

	    echo '<script type="text/javascript" src="http://'.SADDR.'/nebula/main/jquery-1.10.2.js"></script>';
        //echo '<script type="text/javascript" src="http://'.SADDR.'/nebula/main/base64_2.js"></script>';
        echo '<script type="text/javascript" src="http://'.SADDR.'/nebula/main/main_func.js?v='.time().'"></script>';
        echo '<script type="text/javascript" src="http://'.SADDR.'/nebula/main/nebula_system.js?v='.time().'"></script>';
        echo '<script type="text/javascript" src="http://'.SADDR.'/nebula/core/chekko/chekko.js?v='.time().'"></script>';
        echo '<script type="text/javascript" src="http://'.SADDR.'/nebula/core/chekko/multiform.js?v='.time().'"></script>';
        //echo '<script type="text/javascript" src="http://'.SADDR.'/nebula/core/jspdf/jspdf.umd.min.js"></script>';
        
        foreach ($this->initApp['js'] as $c) {
            echo '<script type="text/javascript" src="'.$c.'?v='.time().'"></script>';
        }

	    echo '<script type="text/javascript">';
            //Variabili globali
            echo 'window._nebulaMain = new mainFunc();';
            echo 'var obj='.json_encode($nebulaContesto).';';
            echo 'window._nebulaMain.loadContesto(obj);';

            foreach ($this->initApp['code'] as $c) {
                echo $c;
            }

            //echo 'window.jsPDF = window.jspdf.jsPDF;';

	    echo '</script>';
    }

    function drawTopLine() {
        //Se il log in è attivo scrivi la data e l'utente , altrimenti il bottone per il login
        //Nel caso sia stato forzato un logout scrivi una nota : login scaduto

        echo '<div class="nebulaNavigatorBlock" style="position:relative;width:200px;display:inline-block;margin-left:30px;">';
            echo mainFunc::gab_weektotag(date('w')).'&nbsp;'.date('d/m/Y');
        echo '</div>';

        //se l'utenticazione NON ha avuto successo
        if (!$this->stato['auth']) {
            echo '<div class="nebulaNavigatorBlock" style="position:relative;display:inline-block;color:red;font-weight:bold;">';
                echo $this->stato['msg'];
            echo '</div>';
        }
        else {
            echo '<div class="nebulaNavigatorBlock" style="position:relative;width:250px;display:inline-block;color:blue;font-weight:bold;">';
                echo '<span>'.$this->mainLogged.'</span>';
                echo '<img class="nebulaGalassia" style="margin-top:0px;top:4px;width:20px;height:20px;" src="http://'.SADDR.'/nebula/main/img/logout.png" onclick="window._nebulaMain.logout();" />';
                echo '<img id="nebulaMainBusy" class="nebulaGalassia" style="margin-top:0px;top:4px;width:30px;height:20px;visibility:hidden;" src="http://'.SADDR.'/nebula/main/img/busy2.gif" />';
            echo '</div>';
            /*echo '<div class="nebulaNavigatorBlock" style="position:relative;width:30px;display:inline-block;">';
                echo '<img class="nebulaGalassia" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/main/img/logout.png" onclick="window._nebulaMain.logout();" />';
            echo '</div>';*/

            echo '<div class="nebulaBarraGalassie" style="width:400px;">';
                foreach ($this->configUtente['apps'] as $galassia=>$a) {
                    echo '<img class="nebulaGalassia" src="http://'.SADDR.'/nebula/main/img/galassie/'.$galassia.'.png" onclick="window._nebulaMain.openGalaxy(\''.$galassia.'\');"/>';
                }
            echo '</div>';
        }
    }

}

?>