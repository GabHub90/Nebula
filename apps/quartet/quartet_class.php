<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/core/calendario/calnav.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/core/divo/divo.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/core/kassettone/kassettone.php');

require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/core/panorama/schemi.php');

class quartetApp extends appBaseClass {

    protected $qtLista=array();
    protected $qtReparti=array();
    protected $qtPanorami=array(
        "A"=>array(),
        "P"=>array()
    );

    //oggetti
    protected $qtCalnav;
    protected $schemi;

    function __construct($param,$galileo) {
        
        parent::__construct($galileo);

        $this->loc='/nebula/apps/quartet/';

        $this->param['qt_macroreparto']="";
        $this->param['qt_reparto']="";
        $this->param['qt_today']="";
        $this->param['qt_openType']="A";
        $this->param['qt_date']="";

        $this->loadParams($param);

        if ($this->param['qt_today']=="") $this->param['qt_today']=date('Ymd');
        if ($this->param['qt_date']!="") $this->param['qt_today']=$this->param['qt_date'];

        if ($this->param['qt_macroreparto']=="") {
            die("macroreparto non definito !!!");
        }
        
        //$this->loadClosure();

        $config=array(
            "index"=>"quartet",
            "range_i"=>"20120501",
            "range_f"=>"21001231",
            "tag"=>"d m Y",
            "m1"=>array("giorno","1"),
            "m2"=>array("mese","1"),
            "p1"=>array("giorno","1"),
            "p2"=>array("mese","1"),
            "now"=>true,
            "disabled"=>false
        );
        $css=array(
            "background-color"=>"#baecec"
        );

        if ($this->param['qt_date']!="") $config['disabled']=true;
    
        $this->qtCalnav=new calnav('D',$this->param['qt_today'],$config,$css,$this->galileo);

        $this->galileo->getReparti($this->param['qt_macroreparto'],'');
        $fetID=$this->galileo->preFetchBase('reparti');

        while($row=$this->galileo->getFetchBase('reparti',$fetID)) {
            $this->qtReparti[$row['reparto']]=$row;
        }
    }

    function initClass() {
        return ' quartetCode(\''.$this->param['nebulaFunzione']['nome'].'\');';
    }

    function customDraw() {

        echo '<div style="height:13%;">';

            echo '<div style="display:inline-block;width:50%;vertical-align:top;">';
                echo '<div style="margin-top:10px;">';
                    $this->qtCalnav->draw();
                echo '</div>';
            echo '</div>';

            echo '<div style="display:inline-block;width:44%;margin-left:6%;vertical-align:top;/* border: 2px solid black; */box-sizing: border-box;margin-top: 3px;padding: 10px;">';
                echo '<div style="">';
                    echo '<select id="qt_reparto_select" style="font-size:1.5em;box-shadow: 5px 5px 5px #777;" onchange="window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.quartetChangeRep(this.value);">';
                        echo '<option value="">Seleziona un reparto...</option>';
                        foreach ($this->qtReparti as $reparto=>$r) {
                            echo '<option value="'.$reparto.'" ';
                                if ($reparto==$this->param['qt_reparto']) echo 'selected="selected"';
                            echo '>'.$r['reparto'].' - '.$r['descrizione'].'</option>';
                        }
                    echo '</select>';
                echo '</div>';
            echo '</div>';

        echo '</div>';

        echo '<div style="height:87%;">';

            if ($this->param['qt_reparto']!="") {
                $this->drawReparto();
            }

        echo '</div>';

        echo '<script type="text/javascript">';
            ob_start();
                include (DROOT.'/nebula/apps/quartet/core/default.js');
            ob_end_flush();
        echo '</script>';

    }

    function drawReparto() {

        $this->schemi=new ensambleSchemi($this->param['qt_today'],$this->galileo);
        $this->schemi->getCollaboratoriReparto($this->param['qt_reparto']);
        $this->qtLista=$this->schemi->getCollaboratori();

        $rep=$this->qtReparti[$this->param['qt_reparto']];

        $this->qtPanorami['A']=$this->schemi->setPanorama('A',$this->param['qt_reparto']);
        $this->qtPanorami['P']=$this->schemi->setPanorama('P',$this->param['qt_reparto']);

        $this->schemi->setCollSk($this->param['qt_openType']);

        $this->schemi->setOnclick('window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.quartetCheckSkema(this);');

        echo '<div class="qtLeft">';

            echo '<div class="qtRepDiv">';

                echo '<div class="qtRepDivTitle" style="position:relative;">';
                    echo '<div style="text-align:center;">'.$rep['reparto'].'</div>';
                    echo '<div style="text-align:center;">'.$rep['descrizione'].'</div>';

                    if (isset($this->qtPanorami[$this->param['qt_openType']]['actual'])) {
                        if ($this->qtPanorami[$this->param['qt_openType']]['actual']=='1') {
                            echo '<img id="qt_openedit_img" style="position:absolute;width:20px;height:20px;top:50%;right:2px;transform:translate(0px,-50%);cursor:pointer;" src="http://'.SADDR.'/nebula/apps/ensamble/img/edit.png" onclick="window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.quartetOpenEdit(\''.$this->param['qt_openType'].'\',\''.$this->param['qt_today'].'\');"\>';
                        }
                    }
                echo '</div>'; 

                echo '<div class="qtRepDivBodyContainer" style="">';

                    echo '<div class="qtRepDivBody">';

                        if (isset($this->qtLista[$this->param['qt_reparto']])) {
                            foreach ($this->qtLista[$this->param['qt_reparto']]['gruppi'] as $gruppo=>$g) {

                                echo '<div style="margin-top:5px;border-top: 3px dotted black;">';

                                    echo '<div style="background-color: #dddddd;">';
                                        echo $g['info']['tag'].' - '.$g['info']['descrizione'].' ('.$g['info']['macrogruppo'].')';
                                    echo '</div>';

                                echo '</div>';

                                foreach ($g['coll'] as $ID_coll=>$c) {

                                    echo '<div class="qtRepDivElem">';
                                        echo '<div id="quartetCollDiv_'.$ID_coll.'" style="position:relative;font-weight:bold;">';
                                            //if ($c['cod_operaio']!='') echo '('.$c['cod_operaio'].') ';
                                            echo '<span style="font-size:smaller;">('.$ID_coll.') </span>';
                                            echo $c['cognome'].' '.$c['nome'];

                                            if (isset($this->qtPanorami[$this->param['qt_openType']]['actual'])) {
                                                if ($this->qtPanorami[$this->param['qt_openType']]['actual']=='1') {
                                                    echo '<img id="quartetCollImg_'.$ID_coll.'" style="position:absolute;right:2px;top:2px;width:14px;height:14px;cursor:pointer;" src="http://'.SADDR.'/nebula/apps/ensamble/img/edit.png" onclick="window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.quartetSelColl(\''.$ID_coll.'\');" />';
                                                }
                                            }
                                        echo '</div>';

                                        echo '<div>';
                                            $this->schemi->drawCollSk($ID_coll);
                                        echo '</div>';

                                    echo '</div>';

                                }
                            }
                        }

                    echo '</div>';

                echo '</div>';
            
            echo '</div>';

            echo '<script type="text/javascript" >';
                echo 'var temp='.json_encode($this->schemi->exportCollSkemi()).';';
                echo 'window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.loadCollSkemi(temp);';
                echo 'window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.quartetSetRif("'.$this->param['qt_today'].'");';
            echo '</script>';

        echo '</div>';

        ////////////////////////////////////////////

        if (isset($this->qtPanorami['A']['ID'])) {
        
            //__construct($index,$htab,$minh,$fixed)
            $divo=new Divo('panorami','8%','90%',true);

            $divo->setBk('#baecec');

            $ts=mainFunc::gab_tots(date('Ymd'));
            $temp_d=($this->qtPanorami['P']['inizio']>date('Ym')?$this->qtPanorami['P']['inizio'].'01':date('Ym01',strtotime("+1 month",$ts)));
            
            //se inizio P > di oggi allora prendi quello altrimenti oggi + 1 mese
            $txt='window._temp="'.$temp_d.'";';
            $txt.='window._panorami_divo.preSel=function(tab) {';
                $txt.=<<<JS
                    if (tab==0) {
                        $('#ribbon_qt_openType').val("A");
                        $('#ribbon_qt_date').val("");
                        window._nebulaApp.ribbonExecute();
                    }
                    if (tab==1) {
                        $('#ribbon_qt_openType').val("P");
                        $('#ribbon_qt_date').val(window._temp);
                        window._nebulaApp.ribbonExecute();
                    }
JS;
            $txt.='};';

            $divo->setJS($txt);      

            $css=array(
                "font-weight"=>"bold",
                "font-size"=>"1.5em",
                "margin-left"=>"15px",
                "margin-top"=>"8px"
            );

            ///////////////
            //Attuale

            $tab="<span>Attuale: (".$this->qtPanorami['A']['ID'].") ".mainFunc::gab_todata($this->qtPanorami['A']['inizio'].'01').'</span><img style="width:20px;height:15px;margin-left:10px;" src="http://'.SADDR.'/nebula/main/img/blackarrowR.png" />';

            ob_start();
                $this->schemi->drawSchemi('A');
            $txt=ob_get_clean();

            //add_div($titolo,$color,$chk,$stato,$codice,$selected)
            $divo->add_div($tab,'black',0,"",$txt,($this->param['qt_openType']=='A'?1:0),$css);

            ///////////////
            //Provvisorio

            $tab="<span>Provvisorio: (".$this->qtPanorami['P']['ID'].") ".mainFunc::gab_todata($temp_d).'</span><img style="width:20px;height:15px;margin-left:10px;" src="http://'.SADDR.'/nebula/main/img/blackarrowR.png" />';

            ob_start();
                $this->schemi->drawSchemi('P');
            $txt=ob_get_clean();
            
            $divo->add_div($tab,'black',0,"",$txt,($this->param['qt_openType']=='P'?1:0),$css);

            ///////////////
            //draw

            $divo->build();

            echo '<div class="qtRight">';


                $this->schemi->getStyle();

                $divo->draw();

            echo '</div>';
        }

        else {

            echo '<div class="qtRight">';

                $this->drawNewPanorama();

            echo '</div>';
        }

    }

    function turnoLine($a) {

        $tt="";

        foreach($a as $t) {
            if ($t['i']=='00:00' && $t['f']=='00:00') break;

            $tt.=' '.$t['i'].' - '.$t['f'].' /';
        }

        return substr($tt,0,-1);

    }


    function drawNewPanorama() {

        $this->galileo->getTurni();
        $fetID=$this->galileo->preFetchBase('schemi');

        while($row=$this->galileo->getFetchBase('schemi',$fetID)) {
            $turni[$row['codice']][$row['wd']]=$row;
        }

        $kas=new kassettone('turni');

        ///////////////////
        $css1=array(
            "display"=>"inline-block",
            "position"=>"relative",
            "vertical-align"=>"top",
            "width"=>"70%",
            "height"=>"25px",
            "line-height"=>"21px",
            "box-sizing"=>"border-box"
        );

        $kas->loadCss('Button',$css1);

        $css3=array(
            "position"=>"absolute",
            "width"=>"100%",
            "height"=>"500px",
            "border"=>"1px solid black",
            "padding"=>"4px;",
            "box-sizing"=>"border-box",
            "background-color"=>"#e9cfef",
            "z-index"=>"5",
            "overflow"=>"scroll",
            "top"=>"2px",
            "left"=>"0%"
        );

        $kas->loadCss('Lista',$css3);

        foreach ($turni as $k=>$v) {
            $temphtml='<div style="text-align:center;background-color:#eeeeee;">';
                $temphtml.='<div style="font-weight:bold;">'.$k.'</div>';
                $a=json_decode($v[0]['orari'],true);
                $tt='(Dom) '.$this->turnoLine($a);
                $a=json_decode($v[1]['orari'],true);
                $tt.='(Lun) '.$this->turnoLine($a);
                $tt.=' ... ';
                $a=json_decode($v[6]['orari'],true);
                $tt.='(Sab) '.$this->turnoLine($a);
                $temphtml.='<div>'.$tt.'</div>';
            $temphtml.='</div>';

            $kas->addElem($temphtml,$k);
        }

        echo '<div style="position:relative;width:80%;left:10%;">';

            echo '<div style="position:relative;display:inline-block;width:35%;vertical-align:top;">';

                $kas->drawJS();

                echo '<div style="position:relative;width:100%;height:50px;" >';

                    $kas->drawButton('Turno','turni');

                echo '</div>';

            echo '</div>';

            echo '<div style="position:relative;display:inline-block;width:35%;vertical-align:top;">';

                echo '<button style="font-size:1.3em;font-weight:bold;" onclick="window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.quartetNewPanorami(\''.$this->param['qt_today'].'\');" >Crea Panorami</button>';

            echo '</div>';

            echo '<div style="position:relative;width:80%;height:2px;overflow:visible;">';

                $kas->drawLista();

            echo '</div>';

            echo '<script type="text/javascript">';

            echo 'window._kassettone_turni.postSelect=function() {';
                echo <<<JS
                   this.setTitle(this.val,this.mark);
JS;
            echo '};';

            echo '</script>';

        echo '</div>';


    }

}

?>