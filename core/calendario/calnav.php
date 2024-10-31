<?php

require_once ('calendario.php');

class calnav {
    //fornisce una riga di intestaziona del calendario con la quale è possibile navigare attraverso di esso

    //situazione attuale
    protected $d=array(
        "today"=>"",
        "giorno"=>"",
        "settimana"=>"",
        "weekday"=>"",
        "mese"=>"",
        "anno"=>""
    );

    /*
        risoluzione : indica il grado di precisione del calendario (Y , M , W , D)
        range       : YYYYMMDD che indica l'intrvallo del navigatore
        tag         : modo di visualizzare la data ( come date() )
        mtype       : modo di visualizzare il mese (nome (Dicembre) , tag (Dic) , numero (12) )
        m123        : cursori in meno (anno , mese , settimana , giorno , none"none") , step
        p123        : cursori in più (anno , mese , settimana , giorno , none"none") , step
        export      : modo di esportare la data scelta
        div         : indica se deve apparire il div per la navigazione
        now         : icona per tornare subito ad oggi
    */
    protected $risoluzione="D";

    protected $config=array(
        "index"=>"",
        "range_i"=>"",
        "range_f"=>"",
        "tag"=>"w d m Y",
        "mtype"=>"nome",
        "m1"=>array("none",""),
        "m2"=>array("none",""),
        "m3"=>array("none",""),
        "p1"=>array("none",""),
        "p2"=>array("none",""),
        "p3"=>array("none",""),
        "export"=>"Ymd",
        "div"=>true,
        "now"=>false,
        "disabled"=>false
    );

    protected $css=array(
        "color"=>"black",
        "background-color"=>"transparent",
        "font-size"=>"1em",
        "z-index"=>"20"
    );

    protected $calendario;

    protected $galileo;

    function __construct($risoluzione,$today,$config,$css,$galileo) {

        $this->galileo=$galileo;

        //today YYYYMMDD
        if ($today=="") $today=date('Ymd');

        if ($risoluzione!="") $this->risoluzione=$risoluzione;

        foreach ($this->config as $k=>$c) {
            if ( array_key_exists($k,$config) ) {
                $this->config[$k]=$config[$k];
            }
        }

        foreach ($this->css as $k=>$c) {
            if ( array_key_exists($k,$css) ) {
                $this->css[$k]=$css[$k];
            }
        }

        //elaborazione TODAY
        //nel caso la risoluzione non sia profonda i dati che non servono e che quindi potrebbero essere imprecisi
        //non verranno presi in considerazione nelle elaborazioni successive

        if ($this->config['range_i']=="") $this->config['range_i']=$today;
        if ($this->config['range_f']<$this->config['range_i']) $this->config['range_f']=$this->config['range_i'];
        
        if ($today<$this->config['range_i']) $today=$this->config['range_i'];
        if ($today>$this->config['range_f']) $today=$this->config['range_f'];

        $this->d['today']=$today;
        $this->d['giorno']=substr($today,6,2);
        $this->d['mese']=substr($today,4,2);
        $this->d['anno']=substr($today,0,4);
        $this->d['weekday']=date('w',mainFunc::gab_tots($today));
        $this->d['settimana']=date('W',mainFunc::gab_tots($today));

        //function __construct($anno,$reparto,$galileo)
        $this->calendario=new nebulaCalendario($this->d['anno'],$this->galileo);
        $this->calendario->setReparto("");
    }

    function getFeste() {
        return $this->calendario->getFeste();
    }

    function getChiusure() {
        return $this->calendario->getChiusure();
    }

    function getConfig() {
        return $this->d;
    }

    function draw() {

        echo '<script type="text/javascript" src="http://'.SADDR.'/nebula/core/calendario/calnav.js?v='.time().'"></script>';
        echo '<style>@import url("http://'.SADDR.'/nebula/core/calendario/calnav.css?v='.time().'");</style>';

        echo '<div class="calnavTitle" style="';
            foreach($this->css as $k=>$v) {
                echo $k.':'.$v.';';
            }
        echo '">';
        
            echo '<div class="calnavTitleDiv" style="width:30%;">';
                $this->drawLeftRight('L');
            echo '</div>';

            echo '<div class="calnavTitleDiv" style="width:40%;text-align:center;font-weight:bold;">';
                echo '<u ';
                    if ($this->config['div']) {
                        echo 'style="cursor:pointer;" onclick="window._calnav_'.$this->config['index'].'.setDiv();"';
                    }
                echo '>'.$this->tagDecript().'</u>';
                echo '<div id="calnavOpt_'.$this->config['index'].'" class="calnavOptDiv">'; 
                    echo '<div id="calnavInnerOpt_'.$this->config['index'].'" class="calnavOptInnerDiv" style="background-color:'.$this->css['background-color'].';">';
                        $this->drawOpt();
                    echo '</div>';
                echo '</div>';

                if ($this->config['now']) {
                    /*if ($this->risoluzione=='Y') $tnow=date('Y');
                    elseif ($this->risoluzione=='M') $tnow=date('md');
                    else $tnow=date('Ymd');*/
                    echo '<img class="calnavNavNow" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/calendario/img/now.png" onclick="window._calnav_'.$this->config['index'].'.executeNow(\''.date('Ymd').'\');" />';
                }
            echo '</div>';

            echo '<div class="calnavTitleDiv" style="width:30%;">';
                echo $this->drawLeftRight('R');
            echo '</div>';
        
        echo '</div>';

        echo '<script type="text/javascript">';
            echo 'var config='.json_encode($this->config).';';
            //echo 'var css='.json_encode($this->css).';';
            echo 'window._calnav_'.$this->config['index'].'=new calnav("'.$this->config['index'].'","'.$this->risoluzione.'","'.$this->d['today'].'",config);';
        echo '</script>';
    }

    function tagDecript() {

        $txt="";

        $length = strlen($this->config['tag']);

        for ($i=0; $i<$length; $i++) {

            $c=substr($this->config['tag'],$i,1);

            switch ($c) {

                case 'd':
                    $txt.=$this->d['giorno'];
                break;

                case 'm':
                    if ($this->config['mtype']=='nome') $txt.=mainFunc::gab_monthtotag( ((int)$this->d['mese'])-1 );
                    elseif ($this->config['mtype']=='tag') $txt.=substr(mainFunc::gab_monthtotag(( (int)$this->d['mese']))-1,0,3);
                    else $txt.=$this->d['mese'];
                break;

                case 'Y':
                    $txt.=$this->d['anno'];
                break;

                case 'y':
                    $txt.=substr($this->d['anno'],2,2);
                break;

                case 'w':
                    $txt.=mainFunc::gab_weektotag((int)$this->d['weekday']);
                break;

                case 'W':
                    $txt.=$this->d['settimana'];
                break;

                default: $txt.=$c;
            }
        }

        return $txt;
    }

    function drawLeftRight($lato) {

        $ts=mainFunc::gab_tots($this->d['today']);

        $pm=($lato=='L')?'m':'p';
        $op=($lato=='L')?'-':'+';
        $float=($lato=='L')?'right':'left';

        for ($i=1;$i<=3;$i++) {

            if($this->config[$pm.$i][0]=='none') continue;

            $txt="";

            if($this->config[$pm.$i][0]=='giorno') {
                $ns=strtotime($op.$this->config[$pm.$i][1]." days",$ts);
                $txt=date('d',$ns);
            }
            elseif($this->config[$pm.$i][0]=='mese') {
                $ns=strtotime($op.$this->config[$pm.$i][1]." month",$ts);
                $txt=substr(mainFunc::gab_monthtotag( ((int)date('m',$ns))-1 ),0,3);
            }
            elseif($this->config[$pm.$i][0]=='anno') {
                $ns=strtotime($op.$this->config[$pm.$i][1]." year",$ts);
                $txt=date('Y',$ns);
            }
            elseif($this->config[$pm.$i][0]=='settimana') {
                $ns=strtotime($op.($this->config[$pm.$i][1]*7)." days",$ts);
                $txt=date('d',$ns);
            }

            $dns=date('Ymd',$ns);

            if($dns<$this->config['range_i'] || $dns>$this->config['range_f'] ) break;

            echo '<div class="calnavNavIconDiv" style="float:'.$float.';" onclick="window._calnav_'.$this->config['index'].'.setToday(\''.$dns.'\');" >';


                echo '<div class="calnavNavDayDiv" style="float:'.$float.';background-image: url(\'http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/calendario/img/calicon.png\');" >';
                    echo '<div class="calnavNavDayTxt" >'; 
                        echo $txt;
                    echo '</div>';
                echo '</div>';
                echo '<div class="calnavNavDayDiv" style="float:'.$float.';text-align:'.$float.';" >';
                    echo '<img class="calnavNavArrow" style="" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/calendario/img/'.$i.$lato.'.png" />';
                echo '</div>';

            echo '</div>';
        }
    }

    function drawOpt() {

        echo '<div class="calnavOptFirst" style="" >';

            echo '<div style=" width:50%;" >';
                echo '<select id="calnav_anno_'.$this->config['index'].'" style="width:90%;font-size:0.9em;" onchange="';
                    if ($this->risoluzione=='Y') echo 'window._calnav_'.$this->config['index'].'.execute(this.value);';
                    else echo 'window._calnav_'.$this->config['index'].'.refreshOpt();';
                echo '">';
                    for ($v=(int)substr($this->config['range_i'],0,4);$v<=(int)substr($this->config['range_f'],0,4);$v++) {
                        echo '<option value="'.$v.'" ';
                            if ($v==(int)$this->d['anno']) echo 'selected="selected"';
                        echo '>'.$v.'</option>';
                    }
                echo '</select>';
            echo '</div>';

            echo '<div style=" width:50%;" >';

                if ($this->risoluzione=='D' || $this->risoluzione=='W') {

                    echo '<select id="calnav_mese_'.$this->config['index'].'" style="width:90%;font-size:0.9em;text-align:center;" onchange="';
                        echo 'window._calnav_'.$this->config['index'].'.refreshOpt();';
                    echo '">';
                        for ($v=1;$v<=12;$v++) {

                            $m=$v>9?"".$v:'0'.$v;
                            $am=$this->d['anno'].$m;
                            //se il mese è fuori dal range salta l'opzione
                            if ( $am<substr($this->config['range_i'],0,6) || $am>substr($this->config['range_f'],0,6) ) continue;


                            echo '<option value="'.$m.'" ';
                            if ((int)$v==(int)$this->d['mese']) echo 'selected="selected"';
                        echo '>'.substr(mainFunc::gab_monthtotag($v-1),0,3).'</option>';
                        }
                    echo '</select>';
                }

            echo '</div>';

        echo '</div>';
        
        echo '<div class="calnavOptSecond" style="" >';

            if ($this->risoluzione=='M') {
                $this->monthGrid();
            }

            if ($this->risoluzione=='D' || $this->risoluzione=='W') {
                $this->dayGrid();
            }
        
        echo '</div>'; 

    }

    function monthGrid() {

        echo  '<table style="border:5px solid transparent;margin-top:15px;width:100%;font-size:0.8em;font-weight:bold;" >';
            $m=1;
            for ($tr=1;$tr<=3;$tr++) {
                echo '<tr>';
                    $m=$this->monthLine($m);
                echo '</tr>';

                echo '<tr>';
                    for ($i=0;$i<=2;$i++) {
                        echo '<td style="height:5px;"></td>';
                    }
                echo '</tr>';
            }

        echo '</table>';
    }

    function monthLine($m) {

        for ($td=1;$td<=4;$td++) {
            $txt=$m<10?'0'.$m:$m;
            $gray=false;
            $am=$this->d['anno'].$txt;
            if ( $am<substr($this->config['range_i'],0,6) || $am>substr($this->config['range_f'],0,6) ) $gray=true;

            echo '<td style="width:25%;height:28px;text-align:center;"><div style="width:100%;';
                if(!$gray) echo 'cursor:pointer;';
                else echo 'color:#bbbbbb;';
            echo '" onclick="';
                if (!$gray) echo 'window._calnav_'.$this->config['index'].'.execute(\''.$txt.'\');';
            echo '">'.substr(mainFunc::gab_monthtotag($m-1),0,3).'</div></td>';
            $m++;
        }

        return $m;
    }

    function dayGrid() {

        $giorni=$this->calendario->mese($this->d['mese']);

        echo '<table style="border:5px solid transparent;margin-top:15px;width:100%;font-size:0.8em;font-weight:bold;text-align:center;" >';
            
            echo '<tr>';
                echo '<td></td>';
                echo '<td>Do</td>';
                echo '<td>Lu</td>';
                echo '<td>Ma</td>';
                echo '<td>Me</td>';
                echo '<td>Gi</td>';
                echo '<td>Ve</td>';
                echo '<td>Sa</td>';
            echo '</tr>';

            foreach ($giorni as $week=>$w) {
                
                echo '<tr>';

                    echo '<td style="width:5.5%;font-size:0.7em;';
                        //if ($this->risoluzione=='D') echo 'cursor:pointer;';
                    echo '">'.$week.'</td>';

                    foreach ($w as $weekday=>$d) {

                        $gray=true;
                        $m=substr($d['tag'],4,2);

                        if ($m==$this->d['mese']) {
                            //se è nel range definito
                            if($d['tag']>=$this->config['range_i'] && $d['tag']<=$this->config['range_f']) {
                                $gray=false;
                            }
                        }

                        echo '<td style="width:13.5%;';
                            
                            if ($gray) echo 'color:#bbbbbb;';
                            else echo 'cursor:pointer;';

                            if ($d['chiusura']==1) echo 'color:blue;';
                            if ($d['festa']==1 || $d['wd']==0) echo 'color:red;';

                        echo '" onclick="';
                            //se il giorno appartiene al mese in corso
                            if (!$gray) {
                                echo 'window._calnav_'.$this->config['index'].'.execute(\''.substr($d['tag'],6,2).'\');';
                            }
                        echo '">'.substr($d['tag'],6,2).'</td>';
                    }

                echo '</tr>';

                echo '<tr>';
                    for ($i=0;$i<=7;$i++) {
                        echo '<td style="height:5px;"></td>';
                    }
                echo '</tr>';
            }
        
        echo '</table>';

    }


}

?>