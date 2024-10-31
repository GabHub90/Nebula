<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/core/calendario/calnav.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/apps/idesk/classi/inofficina.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/core/divutil/divutil.php');
include_once($_SERVER['DOCUMENT_ROOT']."/nebula/core/blocklist/blocklist.php");

//require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/apps/idesk/classi/wormhole.php');
//require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/core/odl/odl_func.php');
//require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/core/odl/pratica_func.php');

class timelessApp extends appBaseClass {

    protected $giorni=array();

    //protected $nodata=array();

    protected $tpoCalnav;
    protected $calendario;
    protected $odlFunc;
    protected $wh;

    protected $inofficina;

    protected $log=array();

    function __construct($param,$galileo) {
        
        parent::__construct($galileo);

        $this->loc='/nebula/apps/timeless/';

        $this->param['officina']="";
        $this->param['time_today']="";
        $this->param['time_fine']="";

        $this->loadParams($param);

        if ($this->param['officina']=="") {
            die("officina non definita !!!");
        }

        if ($this->param['time_today']=="") $this->param['time_today']=date('Ymd');

        $i=mainFunc::gab_tots($this->param['time_today']);
        while (date('w',$i)>0) {
            $i=strtotime("-1 day",$i);
        }

        $f=strtotime("+20 days",$i);

        $this->param['time_today']=date('Ymd',$i);
        $this->param['time_fine']=date('Ymd',$f);

        $config=array(
            "index"=>"timeless",
            "range_i"=>"20120501",
            "range_f"=>"21001231",
            "tag"=>"d m Y",
            "m1"=>array("settimana","1"),
            "p1"=>array("settimana","1"),
            "now"=>true,
            "disabled"=>false
        );
        $css=array(
            "background-color"=>"White"
        );
    
        $this->tpoCalnav=new calnav('W',$this->param['time_today'],$config,$css,$this->galileo);

        $this->odlFunc=new nebulaOdlFunc($this->galileo);

        $this->wh=new ideskWHole($this->param['officina'],$this->galileo);

        $a=array(
            "inizio"=>$this->param['time_today'],
            "fine"=>$this->param['time_fine']
        );

        $this->wh->build($a);

        $this->inofficina=new ideskInofficina(array("timeless"=>true,"reparto"=>$this->param['officina']),$this->galileo);
    }

    function initClass() {
        return ' timelessCode(\''.$this->param['nebulaFunzione']['nome'].'\');';
    }

    function build() {

        $anno="";
        $mese="";
        $giorni=array();

        //$i=mainFunc::gab_tots($this->param['time_today']);
        $i=$this->param['time_today'];

        //$this->giorni['fine']=$this->param['time_fine'];

        while ($i<$this->param['time_fine']) {
        //while ($count<=1) {

            if (substr($i,0,4)!=$anno) {
                //$this->calendario=new nebulaCalendario(substr($this->param['time_today'],0,4),$this->galileo);
                $this->calendario=new nebulaCalendario(substr($i,0,4),$this->galileo);
                $this->calendario->setReparto($this->param['officina']);

                $anno=substr($i,0,4);
            }

            if (substr($i,4,2)!=$mese) {
                $mese=substr($i,4,2);
                $giorni=$this->calendario->mese($mese);
            }

            if (!$giorni) $i='99999999';

            //$this->giorni['d']=$giorni;

            foreach ($giorni as $w=>$s) {
                foreach ($s as $d=>$g) {
                    if ($g['tag']>=$this->param['time_today'] && $g['tag']<=$this->param['time_fine']) {
                        $this->giorni[$g['tag']]=$g;
                        $this->giorni[$g['tag']]['info']=array();
                        $this->giorni[$g['tag']]['pratiche']=array();
                    }

                    $i=$g['tag'];
                }

                if ($i>=$this->param['time_fine']) break;
            }

            //cambia mese quando il mese finisce di sabato
            $i=date('Ymd',strtotime("+1 day",mainFunc::gab_tots($i)));

            //$count++;
        }

        ////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////

        $this->wh->getTimeless($this->param['time_today'],$this->param['time_fine'],$this->odlFunc->getDmsRep($this->wh->getTodayDms($this->param['time_today']),$this->param['officina']));

        foreach ($this->wh->exportMap() as $k=>$m) {

            if ($m['result']) {
                $fid=$this->galileo->preFetchPiattaforma($m['piattaforma'],$m['result']);

                while ($row=$this->galileo->getFetchPiattaforma($m['piattaforma'],$fid) ) {

                    //$this->log[]=$row;
                    //continue;

                    //se è CHIUSO e non appartiene al reparto salta
                    if ($row['ind_chiuso']=='S' && $row['cod_officina']!=$this->param['officina']) continue;

                    $id=base64_encode($row['pratica']);
                    $row['flag_prevista_consegna']=false;

                    //costruire le pratiche in lavorazione e senza data
                    if ($row['d_ricon_pratica']=='xxxxxxxx:xx:xx') {

                        $this->inofficina->evalCore($row,$m,true);

                        $rif=substr($row['d_entrata_pratica'],0,8);
                        $row['flag_prevista_consegna']=true;
                    }
                    else {
                        $rif=substr($row['d_ricon_pratica'],0,8);
                    }

                    //se non è stata definita una data di prevista riconsegna
                    //a questo punto è già stato segnalato l'ordine di lavoro tra quelli senza data
                    //gli viene attribuita come data quella di apertura dell'ordine e se è compresa nell'intervallo viene aggiunto il record
                    
                    //$rif=substr($row['d_ricon_pratica'],0,8);

                    if ( ($rif>=$this->param['time_today'] && $rif<=$this->param['time_fine']) ) {

                        //se siamo su PORSCHE INTERNI su CONCERTO occorre verificare se esiste un contratto in INFINITY
                        if ($m['dms']=='concerto' && $row['cod_officina']=='PI') {

                            $h=$this->wh->getContrattoInfinity($row['mat_telaio']);

                            if ($h['result']) {
                                $fid2=$this->galileo->preFetchPiattaforma($h['piattaforma'],$h['result']);
                                while ($row2=$this->galileo->getFetchPiattaforma($h['piattaforma'],$fid2)) {

                                    foreach ($row2 as $k2=>$v2) {
                                        $row[$k2]=$v2;
                                    }
                                }
                            }

                            //se non c'è nessun contratto o il contratto è annullato SALTA
                            if (!isset($row['numero_contratto']) || $row['numero_contratto']==0) continue;
                        }

                        if (!array_key_exists($id,$this->giorni[$rif]['pratiche'])) {

                            $this->giorni[$rif]['pratiche'][$id]['obj']=new nebulaPraticaFunc($row['pratica'],(isset($row['pratica_pren'])?$row['pratica_pren']:'0'),$m['dms'],'N',$this->odlFunc);
                            $this->giorni[$rif]['pratiche'][$id]['obj']->setDefaultAlert();
                            $this->giorni[$rif]['pratiche'][$id]['info']=$row;
                        }

                        $this->giorni[$rif]['pratiche'][$id]['obj']->addLam($row);
                    }
                }
            }
        }
    }

    function drawColor($t) {

        $a=array(
            "ck"=>"",
            "bk"=>""
        );
        
        $a['ck']=($t['wd']==0 || $t['festa']==1)?'red':'black';
        if ($t['chiusura']==1) $a['ck']='darkviolet';
        $a['bk']='#ffffff';
        
        return $a;
    }

    function customDraw() {

        nebulaPraticaFunc::initJS();
        BlockList::blockListInit();

        echo '<div style="height:15%;">';

            echo '<div style="display:inline-block;width:40%;vertical-align:top;">';
                echo '<div style="margin-top:10px;">';
                    $this->tpoCalnav->draw();
                echo '</div>';
            echo '</div>';

            echo '<div style="display:inline-block;width:50%;vertical-align:top;margin-left:5%;">';
                echo '<div style="margin-top:10px;font-size:0.9em;font-weight:bold;">';
                    echo 'Ricerca ultimi 6 mesi per targa,telaio,intestatario (inserire più di 3 caratteri):';
                echo '</div>';
                echo '<div style="margin-top:10px;">';
                    echo '<div style="display:inline-block;width:60%;vertical-align:top;" >';
                        echo '<input id="tml_search" style="width:80%;text-align:center;" type="text" onkeydown="if(event.keyCode==13) window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].search(\''.$this->param['officina'].'\');" />';
                    echo '</div>';
                    echo '<div style="display:inline-block;width:20%;vertical-align:top;" >';
                        echo '<input id="tml_prenFlag" style="" type="checkbox" />';
                        echo '<span style="margin-left:5px;font-size:0.7em;position:relative;top:-3px;">Prenotazioni</span>';
                    echo '</div>';
                    echo '<div style="display:inline-block;width:20%;vertical-align:top;" >';
                        echo '<button onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].search(\''.$this->param['officina'].'\');" >Cerca</button>';
                    echo '</div>';
                echo '</div>';
            echo '</div>';

        echo '</div>';

        echo '<div style="height:85%;">';

            if ($this->param['officina']!="") {
                $this->drawReparto();
            }

        echo '</div>';

        echo '<script type="text/javascript">';

            ob_start();
                include (DROOT.'/nebula/apps/timeless/core/default.js');
            ob_end_flush();
            
        echo '</script>';

    }

    function drawReparto() {

        //$this->build();

        echo '<div class="timeLeft">';

            echo '<div id="tml_main_div" style="width;100%;height:100%;" >';

                echo '<div style="position:relative;width:97%;">';

                    $this->drawCalHead();

                echo '</div>';

                /*if ($this->param['officina']=='PPM') {

                    echo '<div style="width:100%;height:93%;overflow:scroll;overflow-x:hidden;">';
                        echo json_encode($this->log);
                    echo '</div>';

                }*/

                //else {

                    echo '<div style="width:100%;height:93%;overflow:scroll;overflow-x:hidden;">';
                        
                        echo '<div style="width:97%;">';

                            echo '<table class="time_cal_table" style="margin-bottom:10px;" >';

                                foreach ($this->giorni as $tag=>$t) {

                                    if ($t['wd']==0) {
                                        echo '<tr>';
                                    }

                                        //{"tag":"20210430","wd":5,"festa":0,"chiusura":0,"chi":[],"testo":""},{"tag":"20210501","wd":6,"festa":1,"chiusura":0,"chi":[],"testo":"Festa Lavoratori"}
                                        
                                        $cc=$this->drawColor($t);
                                        
                                        echo '<td style="width:'.round(100/7,2).'%;border:1px solid '.$cc['ck'].';background-color:'.$cc['bk'].';height:120px;overflow:hidden;vertical-align:top;padding:3px;" >';

                                            echo '<div class="time_cal_day_title" style="color:'.$cc['ck'].';border-color:'.$cc['ck'].';text-align:right;">';
                                                echo '<span>'.substr($tag,6,2).'</span>';

                                                if ($t['festa']==1 || $t['chiusura']==1) {
                                                    echo '<div style="position:absolute;left:2px;top:2px;font-size: 0.8em;line-height: 16px;vertical-align: middle;">';
                                                        echo substr(html_entity_decode($t['testo']),0,20);
                                                    echo '</div>';
                                                }

                                            echo '</div>';
                                            
                                            foreach ($t['pratiche'] as $k=>$p) {

                                                echo '<div style="position:relative;margin-top:3px;margin-bottom:3px;border:1px solid black;padding:2px;font-size:0.9em;box-sizing:border-box;" ';
                                                    //if ($p['info']['flag_prevista_consegna']) echo 'onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].toPraticaDiv(\''.$k.'\');" ';
                                                    //else 
                                                    echo 'onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].openUtility(\''.$this->odlFunc->getDmsRep($p['info']['dms'],$this->param['officina']).'\',\''.$this->param['officina'].'\',\''.$k.'\',\''.$p['info']['dms'].'\');" ';
                                                echo '>';

                                                    /*if (isset($p['info']['id_nuovo']) && isset($p['info']['d_arrivo']) && $p['info']['id_nuovo']!=0 && $p['info']['d_arrivo']=="") {
                                                        $stato=array(
                                                            "colore"=>"yellow",
                                                            "testo"=>"non arrivata"
                                                        );
                                                    }
                                                    else {*/
                                                        $tempstato=$p['obj']->getStato('','');
                                                        $stato=$tempstato?$this->odlFunc->getStatoOdl($tempstato,$p['info']['dms']):false;
                                                    //}
                                        
                                                    echo '<div style="position:relative;font-size:0.8em;height:15px;text-align:center;background-color:';
                                                        echo ($stato)?$stato['colore']:'white';
                                                        //echo 'white';
                                                    echo ';" >';
                                                        echo ($stato)?$stato['testo']:'';
                                                    echo '</div>';

                                                    echo '<div style="position:relative;font-size:0.8em;height:15px;text-align:center;">';
                                                        echo $p['info']['numero_contratto'].' - ';
                                                        echo '<span style="font-size:0.9em">'.$p['info']['status_contratto'];
                                                        if ($p['info']['flag_prevista_consegna']) echo '<span style="font-weight:bold;font-size:0.9em;margin-left:3px;color:red;">data</span>';
                                                    echo '</div>';

                                                    echo '<div style="position:relative;font-size:0.7em;height:15px;text-align:center;font-weight:bold;">';

                                                        //if (isset($p['info']['mat_targa']) && $p['info']['mat_targa']!="") echo $p['info']['mat_targa'];
                                                        if (isset($p['info']['des_veicolo']) && $p['info']['des_veicolo']!="") echo substr($p['info']['des_veicolo'],0,12);
                                                        else echo substr($p['info']['mat_telaio'],6);

                                                        /*if ($p['info']['cod_officina']=='PN') {
                                                            echo substr($p['info']['mat_telaio'],6);
                                                        }
                                                        else if ($p['info']['cod_officina']=='PU') {
                                                            echo $p['info']['mat_targa'];
                                                        }*/

                                                    echo '</div>';

                                                    echo '<div style="position:relative;font-size:0.7em;height:15px;text-align:center;">';
                                                        echo strtoupper(substr($p['info']['intest_contratto'],0,10));
                                                    echo '</div>';

                                                    $d_fine=$p['obj']->getFine('','');

                                                    if (isset($p['info']['id_nuovo']) && isset($p['info']['d_arrivo']) && $p['info']['id_nuovo']!=0 && $p['info']['d_arrivo']=="") {
                                                        echo '<div style="position:relative;font-size:0.7em;height:15px;text-align:center;color:red;font-weight:bold;" >';
                                                            echo 'NON ARRIVATA';
                                                        echo '</div>';
                                                    }

                                                    else {

                                                        echo '<div style="position:relative;font-size:0.9em;height:15px;text-align:center;">';

                                                            if (substr($d_fine,0,8)>''.$tag) {
                                                                echo '<img style="position:relative;width:15px;top;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/timeless/img/alert.png" />';
                                                            }
                                                            else {
                                                                echo '<img style="position:relative;width:12px;top:1px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/timeless/img/work2.png" />';
                                                            }
                                                            echo '<span style="margin-left:5px;vertical-align:top;">';
                                                                echo substr(mainFunc::gab_todata(substr($d_fine,0,8)),0,5);
                                                            echo '</span>';
                                                        echo '</div>';
                                                    }

                                                echo '</div>';
                                            }

                                        echo '</td>';

                                    if ($t['wd']==6) {
                                        echo '</tr>';
                                    }
                                }

                            echo '</table>';

                        echo '</div>';

                    echo '</div>';
                //}

            echo '</div>';

            echo '<div id="tml_search_div" style="width;100%;height:100%;" >';

                echo '<div style="height:10%;border-bottom:1px solid black;box-sizing:border-box;text-align:right;">';
                    echo '<img style="width:25px;height:25px;margin-right:20px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/timeless/img/chiudi.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].chiudiSearch();" />';
                echo '</div>';

                echo '<div id="tml_search_main" style="height:90%;">';
                echo '</div>';

            echo '</div>';


        echo '</div>';


        echo '<div class="timeRight">';

            echo '<div id="timeless_main" style="position:relative;width:100%;height:100%;" >';

                $divo=new Divo('tempo','5%','95%',true);

                $divo->setBk('#65c1ba');

                $css=array(
                    "font-weight"=>"bold",
                    "font-size"=>"1.2em",
                    "margin-left"=>"10px"
                );

                $css2=array(
                    "width"=>"15px",
                    "height"=>"15px",
                    "top"=>"50%",
                    "transform"=>"translate(0%,-50%)",
                    "right"=>"5px"
                );

                $divo->setChkimgCss($css2);

                $txt='<div id="timeless_daaprire" style="width:96%;" >';

                $txt.='</div>';

                $txt.='<script type="text/javascript">window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].daAprire(\''.$this->param['officina'].'\',\''.mainFunc::gab_toinput($this->param['time_today']).'\',\''.mainFunc::gab_toinput('21001231').'\');</script>';
                //$txt='<div>'.json_encode($this->log).'</div>';

                $divo->add_div('Da Aprire','black',0,"",$txt,1,$css);

                //$txt='<div>'.json_encode($this->galileo->getLog('query')).'</div>';
                //$txt='<div>'.json_encode($this->nodata).'</div>';
                ob_start();
                    echo '<div style="position:relative;width:96%;">';
                        $this->inofficina->drawInofficina(true);
                    echo '</div>';
                $divo->add_div('Senza Data','black',0,"",ob_get_clean(),0,$css);

                unset($txt);

                $divo->build();

                $divo->draw();

            echo '</div>';

            $util=new nebulaUtilityDiv('timeless','window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].chiudiUtility();');

            $util->draw();

        echo '</div>';

    }

    function drawCalHead() {

        echo '<table class="time_cal_table">';

            echo '<tr>';

                for ($i=0;$i<=6;$i++) {
                    $c=($i==0)?'red':'black';
                    echo '<th style="width:'.round(100/7,2).'%;border:1px solid '.$c.';color:'.$c.';">';                    
                        echo mainFunc::gab_weektotag($i);
                    echo '</th>';
                }

            echo '</tr>';

        echo '</table>';

    }

}
?>