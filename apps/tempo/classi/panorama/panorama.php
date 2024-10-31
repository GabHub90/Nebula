<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/core/calendario/calnav.php');
require_once('daylight.php');
require_once(DROOT.'/nebula/core/alan/alan.php');
require_once(DROOT.'/nebula/core/panorama/intervallo.php');

class tempoPanorama extends appBaseClass {

    protected $info=array(
        "color"=>"#d3d6a4"
    );

    protected $default=array(
        "agenda"=>true,
        "brogliaccio"=>false,
        "intervallo"=>'trimestre',
        "data_i"=>"",
        "data_f"=>"",
        "tag"=>'m Y',
        "steptag"=>"mese",
        "step"=>"3",
        "fattore"=>"250",
        "telefono"=>false
    );

    protected $tpanReparti=array();

    protected $giorni=array();
    protected $eventi=array();
    protected $collaboratori=array();
    protected $actualRep=array();
    protected $filtri=array(
        "reparti"=>array(),
        "gruppi"=>array()
    );

    protected $tpanCalnav;
    protected $tpanAlan;
    protected $tpanIntervallo;
    protected $daylight;

    function __construct($param,$galileo) {

        parent::__construct($galileo);

        $this->loc='/nebula/apps/tempo/classi/panorama/';

        $this->param['tpo_macroreparto']="";
        $this->param['tpo_today']="";

        $this->loadParams($param);

        if ($this->param['tpo_today']=="") $this->param['tpo_today']=date('Ym01');

        if ($this->param['tpo_macroreparto']=="") {
            die("macroreparto non definito !!!");
        }

        $this->default['data_i']=$this->param['tpo_today'];

        $this->galileo->getReparti($this->param['tpo_macroreparto'],'');
        $fetID=$this->galileo->preFetchBase('reparti');

        $first=true;

        while($row=$this->galileo->getFetchBase('reparti',$fetID)) {
            $this->tpanReparti[$row['reparto']]=$row;
            
            if ($first) {
                $this->tpanAlan=new nebulaAlan($this->param['tpo_macroreparto'],'tpan_'.$row['reparto'],null,$this->galileo);
                $this->tpanAlan->importa();
                $first=false;
            }
        }
        
    }

    function initClass() {
        return ' tempoPanoramaCode(\''.$this->param['nebulaFunzione']['nome'].'\');';
    }

    function build($arr) {

        foreach ($this->default as $k=>$v) {
            if (array_key_exists($k,$arr)) $this->default[$k]=$arr[$k];
        }

        $config=array(
            "index"=>"panorama",
            "range_i"=>"20120501",
            "range_f"=>"21001231",
            "tag"=>$this->default['tag'],
            "m1"=>array($this->default['steptag'],$this->default['step']),
            "p1"=>array($this->default['steptag'],$this->default['step']),
            "now"=>true,
            "disabled"=>false
        );
        $css=array(
            "background-color"=>$this->info['color']
        );
    
        $this->tpanCalnav=new calnav('M',$this->param['tpo_today'],$config,$css,$this->galileo);
    
        $this->tpanIntervallo=new quartetIntervallo($this->default,$this->tpanReparti,$this->galileo);
        $this->tpanIntervallo->calcola();
    
        $this->collaboratori=$this->tpanIntervallo->getCollaboratori();
        $this->giorni=$this->tpanIntervallo->getGrigliaCal();

        foreach ($this->tpanIntervallo->getCollEventi() as $rep=>$r) {

            foreach ($r as $coll=>$e) {

                if (isset($e['periodi'])) {

                    if (!array_key_exists($rep,$this->eventi)) $this->eventi[$rep]=array();

                    if (!array_key_exists($coll,$this->eventi[$rep])) $this->eventi[$rep][$coll]=array();

                    foreach ($e['periodi'] as $p) {
                        /*"periodi": [
                            {
                            "ID": 2710,
                            "coll": "62",
                            "tipo": "F",
                            "data_i": "20220404",
                            "data_f": "20220409",
                            "utente_inserimento": "s.delbianco",
                            "utente_modifica": null,
                            "utente_conferma": "s.delbianco",
                            "dat_inserimento": "20220411",
                            "dat_modifica": null,
                            "dat_conferma": "20220411"
                            }
                        ]*/
                        $i=mainFunc::gab_tots($p['data_i']);
                        $f=mainFunc::gab_tots($p['data_f']);

                        while ($i<=$f) {

                            $d=date('Ymd',$i);
                            if (!array_key_exists($d,$this->eventi[$rep][$coll])) $this->eventi[$rep][$coll][$d]=array();

                            $this->eventi[$rep][$coll][$d]=array(
                                "tipo"=>$p['tipo'],
                                "conferma"=>$p['dat_conferma']
                            );

                            $i=strtotime("+1 day",$i);
                        }
                    }
                }
            }
        }
        
        $first=true;

        foreach ($this->tpanReparti as $reparto=>$r) {
            $this->actualRep[]=$reparto;

            if ($first) {
                $this->daylight=new tpanDaylight('tempo_tpan',$this->galileo);
                $this->daylight->loadDays($this->giorni[$reparto]);
                $this->daylight->setConfig('subs',false);
                $this->daylight->setConfig('labels',false);
                $this->daylight->setConfig('fill',false);
                //$this->daylight->setConfig('divWidth','200%');
                $first=false;
            }
        }
    }


    function customDraw() {

        echo '<div style="height:10%;">';

            echo '<div style="display:inline-block;width:40%;vertical-align:top;">';
                echo '<div style="margin-top:10px;">';
                    $this->tpanCalnav->draw();
                echo '</div>';
            echo '</div>';

            echo '<div style="display:inline-block;width:30%;vertical-align:top;"></div>';

            echo '<div id="tempo_panorama_view" style="display:inline-block;width:30%;vertical-align:top;border:1px solid black;box-sizing:border-box;height:55px;">';
            echo '</div>';
        
        echo '</div>';

        echo '<div style="height:90%;width:100%;overflow:scroll;">';

            echo '<table style="white-space: nowrap;margin: 0;border: none;border-collapse: separate;border-spacing: 0;table-layout: fixed;margin-bottom:30px;margin-right:30px;width:'.$this->default['fattore'].'%;" >';
                
                echo '<thead>';
                    echo '<tr>';
                        echo '<th style="padding: 3px;position: sticky;left: 0;top:0px;z-index: 4;width: 200px;background: white;" >';
                            echo '<button onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].openFiltri();">Filtro</button>';
                        echo '</th>';
                        echo '<th style="padding: 3px;position: sticky;top: 0;z-index: 3;background: white;" >';
                            $this->daylight->draw();
                        echo '</th>';
                    echo '</tr>';
                echo '</thead>';

                echo '<tbody>';

                    $dl=$this->daylight->getDays();
                    $today=date('Ymd');

                    foreach ($this->tpanReparti as $reparto=>$r) {

                        $this->filtri['reparti'][$reparto]=true;

                        if (array_key_exists($reparto,$this->collaboratori)) {

                            foreach ($this->collaboratori[$reparto] as $IDcoll=>$c) {

                                foreach ($c as $cc) {

                                    if ($cc['macrogruppo']!="" && !isset($this->filtri['gruppi'][$cc['macrogruppo']])) $this->filtri['gruppi'][$cc['macrogruppo']]=true;

                                    echo '<tr class="panorama_line" data-reparto="'.$reparto.'" data-gruppo="'.$cc['macrogruppo'].'">';

                                        echo '<th style="width:200px;position:sticky;z-index:1;left:0px;background-color:white;padding:3px;border-bottom:1px solid #777777;text-align:left;">';
                                            echo '<div style="font-size:0.8em;"><span style="font-weight:normal;">'.$cc['ID_coll'].' -&nbsp;</span>'.$reparto.' '.($cc['macrogruppo']!=''?'('.$cc['macrogruppo'].')':'').'</div>';
                                            echo '<div style="font-weight:normal;">'.$cc['cognome'].' '.$cc['nome'].'</div>';
                                            if ($this->default['telefono']) {
                                                echo '<div style="font-size:0.8em;font-weight:normal;">';
                                                    echo 'Interno:'.$cc['tel_interno'];
                                                    if ($cc['cellulare']!="") echo '<span style="margin-left:10px;">('.$cc['cellulare'].')</span>';
                                                echo '</div>';
                                            }
                                        echo '</th>';
                                        
                                        echo '<th style="padding:3px;border-bottom:1px solid #777777;">';

                                            echo '<div style="white-space: nowrap;">';

                                                echo '<div style="display:inline-block;width:100%;vertical-align:top;">';

                                                    echo '<table style="width:100%;border-space:2px;">';

                                                        echo '<colgroup>';
                                                            echo '<col span="'.$dl['giorni'].'" style="width:'.number_format(100/$dl['giorni'],2,'.','').'%;" />';
                                                        echo '</colgroup>';

                                                        echo '<tr class="panorama_line" data-reparto="'.$reparto.'" data-gruppo="'.$cc['macrogruppo'].'">';

                                                            //for ($i=1;$i<=$dl['giorni'];$i++) {
                                                            foreach ($this->giorni[$reparto] as $tag=>$g) {
                                                                //"20220401":{"tag":"20220401","wd":"5","festa":0,"chiusura":0,"chi":[],"testo":"","chk":"OK"}
                                                                
                                                                /*{
                                                                    "nominale": 480,
                                                                    "actual": 480,
                                                                    "actualBro": 480,
                                                                    "turno": [
                                                                        {
                                                                        "i": "08:00",
                                                                        "f": "12:00"
                                                                        },
                                                                        {
                                                                        "i": "14:00",
                                                                        "f": "18:00"
                                                                        }
                                                                    ],
                                                                    "turnoNominale": [
                                                                        {
                                                                        "i": "08:00",
                                                                        "f": "12:00"
                                                                        },
                                                                        {
                                                                        "i": "14:00",
                                                                        "f": "18:00"
                                                                        }
                                                                    ]
                                                                }*/

                                                                $t=$this->tpanIntervallo->getTurnoCollDay($reparto,$cc['ID_coll'],$tag);

                                                                $bk='transparent';
                                                                $bo='#777777';
                                                                $ck=true;

                                                                if (!$t) {
                                                                    if ($g['festa']==1) $bk='#cccccc';
                                                                    else $bo='transparent';
                                                                }
                                                                else if ($g['festa']==1) $bk='#cccccc';
                                                                else if ($t['nominale']==0 && $t['actual']==0) $bk='#cccccc';
                                                                else if ($t['nominale']==0 && $t['actual']>0) $bk='#9dff8c';
                                                                else if ($t['actual']==0) {
                                                                    if (isset($this->eventi[$reparto][$cc['ID_coll']][$tag])) {
                                                                        
                                                                        if ($this->eventi[$reparto][$cc['ID_coll']][$tag]['tipo']=='F') $bk='#f5b05a';
                                                                        else if ($this->eventi[$reparto][$cc['ID_coll']][$tag]['tipo']=='M') $bk='#fa9090';

                                                                        if ($this->eventi[$reparto][$cc['ID_coll']][$tag]['conferma']=='') $ck=false;
                                                                    }
                                                                    else $bk='#7ee8f3';
                                                                    
                                                                }
                                                                else if ($t['actual']<$t['nominale']) $bk='#7ee8f3';

                                                                $txt='';

                                                                if ($t) {
                                                                    foreach ($t['turno'] as $tu) {
                                                                        $txt.=$tu['i'].' - '.$tu['f'].' / ';
                                                                    }
                                                                }
                                                                

                                                                echo '<td id="panorama_blocco_'.$reparto.'_'.$cc['ID_coll'].'_'.$tag.'" style="position:relative;border:1px solid '.$bo.';font-size:0.6em;height:24px;border-radius:10px;background-color:'.$bk.';" data-turno="'.($t?substr($txt,0,-2):'').'" data-nome="'.$cc['cognome'].' '.$cc['nome'].'" data-tag="'.mainFunc::gab_todata($tag).'" data-reparto="'.$reparto.'" ';
                                                                    if ($t) echo 'onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].view(this.id);" ';
                                                                echo '>';
                                                                    if (!$ck) echo '<img style="position:relative;width:100%;height:100%;opacity:0.5;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/tempo/img/zebra.png" />';
                                                                    
                                                                        if ($tag==$today) {
                                                                            if ($cc['IDDIP'] && $cc['IDDIP']!="") {
            
                                                                                $temptimb=$this->tpanAlan->getActualTimb($cc['IDDIP'],'DESC');
                                                                            }

                                                                            if ($temptimb['timbrature']) {
                                                                                
                                                                                foreach ($temptimb['timbrature'] as $kt=>$timb) {

                                                                                    if ($timb['VERSOO']=='E') {
                                                                                        echo '<img style="position:absolute;width:10px;;height:10px;left:50%;top:50%;margin-left:-5px;margin-top:-5px;z-index:2;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/tempo/img/dot.png" />';
                                                                                    }
                                                                                    break;
                                                                                }
                                                                            }
                                                                        }
                                                                echo '</td>';
                                                            }
                                                        
                                                        echo '</tr>';

                                                    echo '</table>';

                                                echo '</div>';

                                            echo '</div>';

                                        echo '</th>';
                
                                    echo '</tr>';

                                    //break;
                                }

                            }

                        }

                    }

                echo '</tbody>';
            
            echo '</table>';
        
        echo '</div>';

        //echo '<div id="panorama_filtri" style="position:absolute;left:0px;top:20%;height:78%;width:200px;padding:3px;box-sizing:border-box;z-index:5;display:none;background-color:'.$this->info['color'].';">';
        echo '<div id="panorama_filtri" style="position:absolute;left:0px;top:20%;height:78%;width:200px;padding:3px;box-sizing:border-box;z-index:5;display:none;background-image:url(\'http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/tempo/img/marmo.png\');">';
            echo '<div style="text-align:right;">';
                echo '<img style="position:relative;width:25px;height:25px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/tempo/img/annulla.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].closeFiltri();" />';
            echo '</div>';
            echo '<div>';

                echo '<div style="font-weight:bold;">';
                    echo '<span>Reparti:</span>';
                    echo '<img id="panorama_toggle_reparto" style="position:relative;width:20px;height:20px;cursor:pointer;margin-left:10px;" data-val="1" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/tempo/img/toggle.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].toggleFiltri(\'reparto\');" />';
                echo '</div>';
                foreach ($this->filtri['reparti'] as $rep=>$r) {
                    echo '<div>';
                        echo '<input id="panorama_filtro_reparto_'.$rep.'" type="checkbox" value="'.$rep.'" checked />';
                        echo '<span style="margin-left:5px;">'.$rep.'</span>';
                    echo '</div>';
                }

                echo '<div style="margin-top:10px;font-weight:bold;">';
                    echo '<span>Gruppi:</span>';
                    echo '<img id="panorama_toggle_gruppo" style="position:relative;width:20px;height:20px;cursor:pointer;margin-left:10px;" data-val="1" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/tempo/img/toggle.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].toggleFiltri(\'gruppo\');" />';
                echo '</div>';

                echo '<div>';
                    echo '<input id="panorama_filtro_gruppo_nessuno" type="checkbox" value="" checked />';
                    echo '<span style="margin-left:5px;">Nessuno</span>';
                echo '</div>';
                
                foreach ($this->filtri['gruppi'] as $gruppo=>$r) {
                    echo '<div>';
                        echo '<input id="panorama_filtro_gruppo_'.$gruppo.'" type="checkbox" value="'.$gruppo.'" checked />';
                        echo '<span style="margin-left:5px;">'.$gruppo.'</span>';
                    echo '</div>';
                }

            echo '</div>';
        echo '</div>';

        echo '<div>';
            /*foreach ($this->eventi as $k=>$g) {
                echo '<div>'.$k.' '.json_encode($g).'</div>';
            }*/
            //echo json_encode($this->tpanIntervallo->getTurnoCollDay('AMM','48','20220401'));
        echo '</div>';

        echo '<script type="text/javascript">';

            ob_start();
                include (DROOT.'/nebula/apps/tempo/classi/panorama/core/default.js');
            ob_end_flush();
            
        echo '</script>';
    }
}
?>