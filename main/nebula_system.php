<?php
class nebulaSystem {

    //etichetta della galassia
    protected $tag="";
    protected $galassia="";
    protected $sistema="";
    protected $menuTag="";
    protected $menuTagColor="white";

    protected $ribbonClass="nebulaRibbon";
    protected $bordoRibbon='http://'.SADDR.'/nebula/main/img/bordo_ribbon.png';

    protected $contesto=array(
        "versione"=>"",
        "mainLogged"=>"",
        "mainApp"=>"",
        "mail"=>"",
        "mainGalaxie"=>array(),
        "configUtente"=>array(),
        "ribbon"=>array(),
        "args"=>array(),
        "linkFunk"=>""
    );

    //descrive la lista delle funzioni a "menu" che determinano anche i DIV
    protected $funcMenu=array();

    //------------------
    //galassia - sistema - funzioni
    // tag è il nome dell'applicazione reale che appare nel ribbon della stessa
    protected $funzioni=array();
    
    protected $universe;
    protected $galileo;

    protected $log=array();

    function __construct($tag,$con,$versione,$galileo) {

        $this->universe=new nebulaUniverse();
        $this->funzioni=$this->universe->getSistemi();

        $this->galileo=$galileo;
        $this->contesto['versione']=$versione;
        $this->tag=$tag;

        //"CON":{
            //"mainLogged":"m.cecconi",
            //"mainApp":"isla:home",
            //"mainGalaxie:
            //"configUtente":{
                //"generale":{"TDD":{"TDD":{"ID_coll":1,"nome":"Matteo","cognome":"Cecconi","concerto":"m.cecconi","reparto":"TDD","des_reparto":"Team di Direzione","macroreparto":"D","des_macroreparto":"Direzione","ID_gruppo":32,"gruppo":"TDD","des_gruppo":"Direttivo","pos_gruppo":1,"macrogruppo":"","des_macrogruppo":"","pos_macrogruppo":0}}},
                //"apps":{"home":["home","login"],"isla":["home"],"mytech":["home"],"ammo":["home"],"sthor":["home"],"vendor":["home"],"maestro":["home","ensamble","tempo"]},
                //"funzioni":[]
            //}
        //}
        foreach ($this->contesto as $k=>$o) {
            if ( isset($con[$k]) ) $this->contesto[$k]=$con[$k];
        }

        $gs=explode(':',$this->contesto['mainApp']);
        $this->galassia=$gs[0];
        $this->sistema=$gs[1];

        $this->menuTag=$this->contesto['configUtente']['apps'][$this->galassia][$this->sistema];

        if (isset($this->contesto['configUtente']['funzioni'])) {
        
            //ELABORAZIONE DELLE MODIFICHE ALLE FUNZIONI ABILITATE PER L'UTENTE IN BASE AI SISTEMI
            foreach ($this->contesto['configUtente']['funzioni'] as $galassia=>$g) {

                if (!array_key_exists($galassia,$this->funzioni)) continue;

                foreach ($g as $sistema=>$s) {

                    if (!array_key_exists($sistema,$this->funzioni[$galassia])) continue;

                    //if (isset($this->funzioni[$galassia][$sistema]['home']['chk']) && !$this->funzioni[$galassia][$sistema]['home']['chk']) continue;

                    foreach ($s as $funzione=>$v) {

                        if (array_key_exists($funzione,$this->funzioni[$galassia][$sistema])) {

                            //se il modificatore è false , chk è true ma chg è true (rimani in true)
                            if (!$v && $this->funzioni[$galassia][$sistema][$funzione]['chk'] && $this->funzioni[$galassia][$sistema][$funzione]['chg']) {
                                $this->funzioni[$galassia][$sistema][$funzione]['chk']=true;
                            }
                            //in tutti gli altri casi imposta come da modificatore
                            else {
                                //$this->galassie[$galassia][$sistema][$funzione]['chk']=$v;
                                //$this->galassie[$galassia][$sistema][$funzione]['chg']=true;
                                $this->funzioni[$galassia][$sistema][$funzione]['chk']=$v;
                                $this->funzioni[$galassia][$sistema][$funzione]['chg']=true;
                            }
                        }
                    }
                }
            }

        }
    }

    function setRibbonclass($class) {
        $this->ribbonClass=$class;
    }

    function setBorderibbon($url) {
        $this->bordoRibbon=$url;
    }

    function buildContesto() {

        $c=array(
            "mainLogged"=>$this->contesto['mainLogged'],
            "mainApp"=>$this->contesto['mainApp'],
            "mail"=>$this->contesto['mail'],
            "configUtente"=>$this->contesto['configUtente'],
            "configFunzioni"=>array()
        );

        foreach ($this->funzioni as $galassia=>$g) {
            foreach ($g as $sistema=>$s) {
                foreach($s as $funzione=>$f) {
                    $c['configFunzioni'][$galassia][$sistema][$funzione]['chk']=$f['chk'];
                }
            }
        }

        return $c;
    }

    function drawNebumenu() {

        echo '<div class="nebulaMenu" style="background-image: url(\'http://'.SADDR.'/nebula/main/img/starred.png\');" >';
            
            //05.02.2021 i div hallo larghezza fissa 215+250=465px;

            echo '<div class="" style="display:inline-block;width:215px;height:40px;line-height:40px;" >';
                echo '<img class="galIcon" src="http://'.SADDR.'/nebula/main/img/galassie/'.$this->galassia.'.png" onclick="window._nebulaMain.openSystem(\''.$this->galassia.'\',\'home\');"/>';
                //echo '<img class="galIcon" src="http://'.SADDR.'/nebula/main/img/galassie/'.$this->galassia.'.png" onclick="window._nebulaApp.setFunction(\'home\',true);"/>';
                echo '<span style="font-size:18pt;margin-left:10px;vertical-align:middle;" >'.$this->tag.'</span>';
                echo '<span style="font-size:7pt;margin-left:10px;vertical-align:sub;" >v&nbsp;'.$this->contesto['versione'].'</span>';
            echo '</div>';

            echo '<div class="nebulaSystemTag" style="display:inline-block;width:250px;height:38px;line-height:40px;background-image: url(\'http://'.SADDR.'/nebula/main/img/systemBack.png\');" >';
                echo '<img id="nebulaSystemMenuArrow" class="galIcon" style="width:28px;height:28px;" src="http://'.SADDR.'/nebula/main/img/whitearrowR.png" onclick="window._nebulaMain.openSystemMenu();" />';
                echo '<span style="font-size:18pt;margin-left:10px;vertical-align:middle;color:'.$this->menuTagColor.';" >'.$this->menuTag.'</span>';

                $txt="";
                $height=40;
                $item_h=55;
                $min_h=50;

                foreach ($this->contesto['configUtente']['apps'][$this->galassia] as $sistema=>$s) {
                    if ($sistema=='login') continue;
                    if ($sistema==$this->sistema) continue;
                    if (isset($this->funzioni[$this->galassia][$sistema]['home']['chk']) && !$this->funzioni[$this->galassia][$sistema]['home']['chk']) continue;
                    
                    $txt.='<div class="nebulaSystemMenuItem" style="color:'.$this->menuTagColor.';" onclick="window._nebulaMain.openSystem(\''.$this->galassia.'\',\''.$sistema.'\');">';
                        $txt.='<div style="vertical-align:middle;" >'.$s.'</div>';
                    $txt.='</div>';
                    $height+=$item_h;
                }

                if ($height<$min_h) $height=$min_h;

                echo '<div id="nebulaSystemMenu" class="nebulaSystemMenu" style="height:'.$height.'px;background-image: url(\'http://'.SADDR.'/nebula/main/img/starred2.png\');" >';
                    
                    echo '<div id="nebulaSystemMenuList" class="nebulaSystemMenuList" >';
                        //===================================
                        //inserire elenco sistemi come configurati per l'utente
                        echo $txt;
                        //===================================
                    echo '</div>';

                echo '</div>';

            echo '</div>';

            $txt="";

            foreach ($this->funzioni[$this->galassia][$this->sistema] as $k=>$o) {

                //if ($k=='home' || ($o['chk'] && $o['tipo']=='menu') ) {
                if ( ($o['chk'] && $o['tipo']=='menu') ) {

                    $this->funcMenu[$k]=$o["menuTag"];

                    //if ($k!='home') {
                        $txt.='<td id="nebulaFunctionMenuTD_'.$k.'" class="nebulaFunctionMenuTD" style="';
                            if ($k=='home') $txt.='display:none;';
                        $txt.='" onclick="window._nebulaApp.setFunction(\''.$k.'\',false);">'.$o['menuTag'].'</td>';
                    //}
                    /*else {
                        $txt.='<td style="width:25px;">*</td>';
                    }*/
                }
            }

            echo '<div style="position:absolute;display:inline-block;height:40px;line-height:40px;overflow-y:hidden;" >';

                echo '<table id="nebulaFunctionTable" class="nebulaFunctionTable" >';

                    echo '<tr>';
                        //===================================
                        //scrivere il menu delle funzioni "menu"
                        //05.02.2021 per il momento non mi preoccupo del ridimensionamento in base alla larghezza dello schermo
                        //perché nn sembra che le funzioni siano un numero troppo alto.
                        //===================================
                        echo $txt;

                    echo '</tr>';

                echo '</table>';

            echo '</div>';

        echo '</div>';
    }

    function drawSystem() {

        echo '<div class="galMainDiv" style="background-image: url(\'http://'.SADDR.'/nebula/main/img/mainback.png\');" >';

            echo '<style>';
                echo '.divButton {';
                    echo 'background-image: url(http://'.SADDR.'/nebula/main/img/galassie/buttons/'.$this->galassia.'.png);';
                echo '}';
                echo '.'.$this->ribbonClass.' {';
                    echo 'border-image: url('.$this->bordoRibbon.') 3;';
                    echo 'background-image: url(http://'.SADDR.'/nebula/main/img/lether.png);';
                echo '}';
                /*echo '.nebulaNoBorderRibbon {';
                    echo 'background-image: url(http://'.SADDR.'/nebula/main/img/lether.png);';
                echo '}';*/
            echo '</style>';

            foreach ($this->funcMenu as $f=>$tag) {
                //scrivere i div necessari per le funzioni "menu"
                echo '<div id="nebulaFuncion_'.$f.'" class="nebulaFunctionDiv" ></div>';
            }

            echo '<script type="text/javascript" >';

                //#########################################################
                //Nel caso stessi passando da una galassia ad un'altra è questo il punto in cui
                //inervenire. Se ho passato nel contesto delle informazioni JUMP
                //allora setFunction non sarà Home
                //e potrei avere degli argomenti per window._nebulaApp.setArgs(arr)
                //o window._nebulaApp.setRibbonArgs(arr)
                //#########################################################
                echo 'window._nebulaApp.setFunction('.($this->contesto['linkFunk']==""?'"home"':'"'.$this->contesto['linkFunk'].'"').',true);';
                //echo '$(".galMainDiv").css("visibility","visible");';

                //update contesto _nebulaMain
                echo 'var obj='.json_encode($this->buildContesto()).';';
                echo 'window._nebulaMain.loadContesto(obj);';
            echo '</script>';

        echo '</div>';
    }

    function startFunction($funzione,$ribbon,$args) {

        //se la funzione non è abilitata ritorna. Non dovrebbe accadere comunque.
        if (!$this->funzioni[$this->galassia][$this->sistema][$funzione]['chk']) return false;

        if ($this->funzioni[$this->galassia][$this->sistema][$funzione]['loc']=='') {
            echo '<b>Funzione non definita</b>';
            return false;
        }

        $ch = curl_init('http://'.SADDR.$this->funzioni[$this->galassia][$this->sistema][$funzione]['loc'].'main.php');
        curl_setopt($ch, CURLOPT_HEADER, 0);

        /* {
                "nebulaContesto":{
                    "mainLogged":"m.cecconi",
                    "mainApp":"isla:home",
                    "configUtente":{
                        "generale":{"TDD":{"TDD":{"ID_coll":"1","nome":"Matteo","cognome":"Cecconi","concerto":"m.cecconi","reparto":"TDD","des_reparto":"Team di Direzione","macroreparto":"D","des_macroreparto":"Direzione","ID_gruppo":"32","gruppo":"TDD","des_gruppo":"Direttivo","pos_gruppo":"1","macrogruppo":"","des_macrogruppo":"","pos_macrogruppo":"0"}}},
                        "apps":{"home":{"home":"Overview","login":"Login"},"isla":{"home":"iDesk"},"mytech":{"home":"Officina"},"ammo":{"home":"Office"},"sthor":{"home":"Magazzino"},"vendor":{"home":"Workplace"},"maestro":{"home":"Tempo","gruppi":"Gruppi","diagnosi":"Diagnosi"}
                    },
                    //ESEPIO MAESTRO:ANALISI
                    "configFunzioni": {
                        "home":{"chk":true},
                        "phone":{"chk":true}
                    }
                },
                "nebulaFunzione":{"nome":"home","loc": "/nebula/funzioni/idesk/"},
                "args":[]
            }
        */

        ///////////////////////////////////////////////////
        //PASSAGGIO DEGLI ARGOMENTI TRAMITE JSON
        $p=array();
        $p['nebulaContesto']=$this->buildContesto();
        $p['nebulaFunzione']=array(
            "nome"=>$funzione,
            "loc"=>$this->funzioni[$this->galassia][$this->sistema][$funzione]['loc']
        );
        $p['ribbon']=$ribbon;
        $p['args']=$args;

        $post=json_encode($p);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$post);
        //////////////////////////////////////////////////

        if( ! $result = curl_exec($ch)) { 
            trigger_error(curl_error($ch)); 
        }

        curl_close($ch);

        return $result;
    }

}
?>