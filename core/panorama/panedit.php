<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/core/divo/divo.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/core/kassettone/kassettone.php');

require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/core/panorama/schemi.php');

class nebulaPanEdit {

    protected $reparto="";
    protected $tipo="";
    protected $today="";

    protected $schemi;
    protected $panorama=array();
    //tutti gli schemi definiti per il reparto
    protected $schemiReparto=array();
    protected $schemiPanorama=array();

    protected $kas=array();
    protected $galileo;

    function __construct($reparto,$tipo,$today,$galileo) {

        $this->reparto=$reparto;
        $this->tipo=$tipo;
        $this->today=$today;
        $this->galileo=$galileo;

        $this->schemi=new ensambleSchemi($today,$this->galileo);
        $this->panorama=$this->schemi->setPanorama($tipo,$reparto);

        $this->schemiPanorama=$this->schemi->exportSchemi($tipo);

        $this->galileo->getRepSk($reparto);
        $fetID=$this->galileo->preFetchBase('schemi');
        while($row=$this->galileo->getFetchBase('schemi',$fetID)) {

            if (array_key_exists($row['codice'],$this->schemiPanorama)) {
                $row['busy']=true;
                $row['data_i']=$this->schemiPanorama[$row['codice']]['data_i'];
                $row['blocco_inizio']=$this->schemiPanorama[$row['codice']]['blocco_inizio'];
            }
            else {
                $row['busy']=false;
                $row['data_i']="";
                $row['blocco_inizio']="";
            }

            $this->schemiReparto[$row['codice']]=$row;
        }
    }

    function drawEdit() {

        $this->kas['schemi']=new kassettone('schemi');
        $this->kas['turni']=new kassettone('turni');

        $divo=new Divo('panedit','6%','94%',true);
        $divo->setBk('#e4baec');

        $css=array(
            "font-weight"=>"bold",
            "font-size"=>"1em",
            "margin-left"=>"5px",
            "margin-top"=>"3px"
        );

        ob_start();
            $this->editSchemi();
        $txt=ob_get_clean();

        $divo->add_div('Schemi','black',0,"",$txt,1,$css);

        $txt="";

        ob_start();
            $this->editSubs();
        $txt=ob_get_clean();

        $divo->add_div('Subs','black',0,"",$txt,0,$css);

        $txt="";

        ob_start();
            $this->editTurni();
        $txt=ob_get_clean();

        $divo->add_div('Turni','black',0,"",$txt,0,$css);

        //////////////////////////////////////////////////////

        $divo->build();

        echo '<div style="width:100%;" >';

            $this->kas['schemi']->drawJS();

            $divo->draw();

        echo '</div>';

        echo '<script type="text/javascript" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/panorama/panedit.js" ></script>';
        echo '<script type="text/javascript">';
            echo 'window._panedit=new panEdit(\''.$this->reparto.'\',\''.$this->tipo.'\');';
            echo 'var temp='.json_encode($this->schemiReparto).';';
            echo 'window._panedit.loadSchemi(temp);';
            echo 'var temp='.json_encode($this->schemi->exportTurni()).';';
            echo 'window._panedit.loadTurni(temp);';
            echo 'var temp='.json_encode($this->schemi->exportSubrep($this->tipo)).';';
            echo 'window._panedit.loadSubrep(temp);';
            echo 'window._kassettone_schemi.postSelect=function() {';
            echo <<<JS
                window._panedit.selectSchema(this.val);
JS;
            echo '};';
            echo 'window._kassettone_turni.postSelect=function() {';
                echo <<<JS
                    window._panedit.selectTurno(this.val,this.mark);
JS;
            echo '};';
            echo 'window._kassettone_turni.setEnable(false);';
        echo '</script>';

    }

    function turnoLine($a) {

        $tt="";

        foreach($a as $t) {
            if ($t['i']=='00:00' && $t['f']=='00:00') break;

            $tt.=' '.$t['i'].' - '.$t['f'].' /';
        }

        return substr($tt,0,-1);

    }

    function editSchemi() {

        $css1=array(
            "display"=>"inline-block",
            "position"=>"relative",
            "vertical-align"=>"top",
            "width"=>"70%",
            "height"=>"25px",
            "line-height"=>"21px",
            "box-sizing"=>"border-box"
        );

        $this->kas['schemi']->loadCss('Button',$css1);
        $this->kas['turni']->loadCss('Button',$css1);

        $css2=array(
            "position"=>"absolute",
            "width"=>"35%",
            "height"=>"300px",
            "border"=>"1px solid black",
            "padding"=>"4px;",
            "box-sizing"=>"border-box",
            "background-color"=>"#e9cfef",
            "z-index"=>"5",
            "overflow"=>"scroll",
            "top"=>"2px",
            "left"=>"0px",
        );

        $css3=array(
            "position"=>"absolute",
            "width"=>"65%",
            "height"=>"300px",
            "border"=>"1px solid black",
            "padding"=>"4px;",
            "box-sizing"=>"border-box",
            "background-color"=>"#e9cfef",
            "z-index"=>"5",
            "overflow"=>"scroll",
            "top"=>"2px",
            "left"=>"35%"
        );

        $this->kas['schemi']->loadCss('Lista',$css2);
        $this->kas['turni']->loadCss('Lista',$css3);

        foreach ($this->schemiReparto as $k=>$v) {
            $temphtml='<div style="text-align:center;';
                if (!$v['busy']) $temphtml.='background-color:#dddddd;opacity:0.5;';
                else $temphtml.='background-color:#eeeeee;';
            $temphtml.='">';
                $temphtml.='<div style="font-weight:bold;">'.$v['codice'];
                    if ($v['elem']==0) $temphtml.='<span style="margin-left:5px;font-weight:normal;">(edit)</span>'; 
                $temphtml.='</div>';
                $temphtml.='<div>'.$v['titolo'].'</div>';
            $temphtml.='</div>';

            $this->kas['schemi']->addElem($temphtml,$v['codice']);
        }

        foreach ($this->schemi->exportTurni() as $k=>$v) {
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

            $this->kas['turni']->addElem($temphtml,$k);
        }

        ////////////////////////////////////////////////////////////////////////

        echo '<div style="height:14%;">';

            echo '<div style="position:relative;width:100%;height:60px;">';

                echo '<div style="position:relative;display:inline-block;vertical-align:top;padding:5px;box-sizing:border-box;width:20%;" >';

                    $this->kas['schemi']->drawButton('Schema','schema');

                    echo '<div style="display:inline-block;vertical-align:top;overflow:visible;">';
                        echo '<img style="width:22px;height:22px;margin-left:12px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/kassettone/img/add.png" onclick="window._panedit.setNew();" />';
                    echo '</div>';

                    echo '<input id="panedit_form_panorama" type="hidden" value="'.$this->panorama['ID'].'" />';
                    echo '<input id="panedit_form_pandatai" type="hidden" value="'.$this->panorama['inizio'].'" />';
                    echo '<input id="panedit_form_reparto" type="hidden" value="'.$this->reparto.'" />';

                echo '</div>';

                echo '<div style="position:relative;display:inline-block;vertical-align:top;padding:5px;box-sizing:border-box;width:40%;border-left:1px solid black;height:100%;" >';
                    echo '<div>';
                        echo '<div id="panedit_tag_codice" class="panedit_tag" style="display:inline-block;width:20%;">Codice</div>';
                        echo '<div style="display:inline-block;width:80%;">';
                            echo '<input id="panedit_form_codice" class="panedit_form" type="text" maxlegth="10" style="text-align:center;width:90%;" onchange="window._panedit.change(\'codice\',this.value);" disabled />';
                        echo '</div>';
                    echo '</div>';
                    echo '<div style="margin-top:5px;" >';
                        echo '<div id="panedit_tag_titolo" class="panedit_tag" style="display:inline-block;width:20%;">Titolo</div>';
                        echo '<div style="display:inline-block;width:80%;">';
                            echo '<input id="panedit_form_titolo" class="panedit_form" type="text" maxlegth="50" style="width:90%;" onchange="window._panedit.change(\'titolo\',this.value);" disabled />';
                        echo '</div>';
                    echo '</div>';
                echo '</div>';

                echo '<div style="position:relative;display:inline-block;vertical-align:top;padding:5px;box-sizing:border-box;width:40%;border-left:1px solid black;height:100%;" >';
                    echo '<div id="panedit_form_collectdiv" style="position:relative;width:100%;vertical-align:top;height:25px;">';
                    echo '</div>';
                    echo '<div id="panedit_form_buttondiv" style="position:relative;text-align:center;">';
                    echo '</div>';
                echo '</div>';

            echo '</div>';

            echo '<div style="position:relative;border-bottom:2px solid black;height:2px;overflow:visible;width:100%;">';
                $this->kas['schemi']->drawLista();
                $this->kas['turni']->drawLista();
            echo '</div>';
        
        echo '</div>';

        ///////////////////////////////////////////////////////

        echo '<div style="height:85%;width:100%;">';

            echo '<div id="panedit_options_div" style="height:30%;border-bottom:1px solid black;">';

                echo '<div style="margin-top:5px;height:50px;">';
                    echo '<div id="panedit_tag_overall" class="panedit_tag" style="display:inline-block;width:12%;vertical-align:top;line-height:25px;">Turno Overall</div>';
                    echo '<div style="position:relative;display:inline-block;vertical-align:top;padding:5px;box-sizing:border-box;width:18%;" >';
                        $this->kas['turni']->drawButton('Turno','turnoOverall');
                    echo '</div>';
                    //echo '<div id="panedit_form_turnoOverall" style="display:inline-block;width:12%;vertical-align:top;text-align:center;font-weight:bold;height:40px;line-height:40px;"></div>';
                    echo '<div id="panedit_form_turnoOverall_table" style="display:inline-block;width:70%;vertical-align:top;"></div>';

                echo '</div>';

                echo '<div id="panedit_options_tab" style="margin-top:5px;height:50px;">';

                echo '</div>';

            echo '</div>';

            echo '<div id="panedit_blocks_div" style="height:70%;overflow:scroll;">';
                //echo json_encode($this->schemiPanorama);
                //echo json_encode($this->galileo->getLog('query'));
            echo '</div>';

        echo '</div>';

    }

    function editTurni() {

        echo '<div style="position:relative;display:inline-block;width:30%;height:100%;overflow:scroll;vertical-align:top;" >';

            echo '<div style="width:90%;padding 3px;box-sizing:border-box;" >';

                foreach ($this->schemi->exportTurni() as $turno=>$t) {
                    echo '<div id="panedit_turni_turno_lista_div_'.$turno.'" style="position:relative;margin-top:2px;margin-bottom:2px;border:1px solid black;text-align:center;font-weight:bold;height:25px;line-height:25px;cursor:pointer;" onclick="window._panedit.selectTurniTurno(\''.$turno.'\');" >';
                        echo $turno;
                    echo '</div>';
                }

            echo '</div>';

        echo '</div>';

        echo '<div style="position:relative;display:inline-block;width:70%;height:100%;vertical-align:top;" >';
        
            echo '<div style="position:relative;width:100%;border-bottom:1px solid #888888;height:12%;" >';

                echo '<div style="position:relative;display:inline-block;border-right:1px solid #888888;width:75%;height:100%;line-height:100%;padding:3px;box-sizing:border-box;vertical-align:top;" >';
                
                    echo '<span style="margin-left:10px;" >Codice:</span>';
                    echo '<input id="panedit_turni_form_codice" style="position:relative;margin-left:10px;width:50%;text-align:center;" maxlenght="10" disabled />';
                    echo '<img style="width:22px;height:22px;margin-left:25px;top: 5px;position: relative;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/panorama/img/add.png" onclick="window._panedit.setNewTurno();" />';

                echo '</div>';

                echo '<div style="position:relative;display:inline-block;width:25%;height:100%;line-height:100%;" >';
                    echo '<button id="panedit_turni_form_confirm" style="position:relative;width:80%;left:50%;top:50%;transform:translate(-50%,-50%);" onclick="window._panedit.confermaTurno();" disabled >Conferma</button>';
                echo '</div>';

            echo '</div>';

            echo '<div id="panedit_turni_interval_div" style="position:relative;width:100%;border-bottom:1px solid #888888;height:22%;" >';
                
                echo '<div style="position:relative;display:inline-block;width:22%;height:100%;line-height:100%;vertical-align:top;text-align:center;" >';
                    echo '<div style="position:relative;width:100%;top:10px;font-weight:bold;" >Da</div>';
                    echo '<div style="position:relative;width:100%;top:20px;" >';
                        echo '<select id="panedit_turni_form_da" style="width:80%;text-align:center;font-size:1.2em;">';
                            $rif=0;
                            $step=15;
                            while ($rif<=(1440-$step)) {
                                $t=mainFunc::gab_mintostring($rif);
                                echo '<option value="'.$t.'" >'.$t.'</option>';
                                $rif+=$step;
                            }
                        echo '</select>';
                    echo '</div>';
                echo '</div>';

                echo '<div style="position:relative;display:inline-block;width:22%;height:100%;line-height:100%;vertical-align:top;text-align:center;" >';
                    echo '<div style="position:relative;width:100%;top:10px;font-weight:bold;" >A</div>';
                    echo '<div style="position:relative;width:100%;top:20px;" >';
                        echo '<select id="panedit_turni_form_a" style="width:80%;text-align:center;font-size:1.2em;">';
                            $step=15;
                            $rif=$step;
                            while ($rif<=1440) {
                                $t=mainFunc::gab_mintostring($rif);
                                echo '<option value="'.$t.'" >'.$t.'</option>';
                                $rif+=$step;
                            }
                        echo '</select>';
                    echo '</div>';
                echo '</div>';

                echo '<div style="position:relative;display:inline-block;width:6%;height:100%;line-height:100%;vertical-align:top;text-align:center;top:35px;" >';
                    echo '<img style="width:20px;height:10px;top: 5px;position: relative;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/panorama/img/blackarrowR.png" onclick="window._panedit.addTurnoInterval();" />';
                echo '</div>';

                echo '<div id="panedit_turni_show_interval" style="position:relative;display:inline-block;width:25%;height:100%;line-height:100%;vertical-align:top;text-align:center;" >';
                echo '</div>';

                echo '<div style="position:relative;display:inline-block;width:25%;height:100%;line-height:100%;vertical-align:top;" >';
                    echo '<button id="panedit_turni_interval_confirm" style="position:relative;width:60%;left:50%;top:50%;transform:translate(-50%,-50%);" onclick="window._panedit.assegnaInterval();" disabled >Assegna</button>';    
                echo '</div>';

            echo '</div>';

            echo '<div id="panedit_turni_main_div" style="position:relative;width:100%;" >';
            echo '</div>';
        
        echo '</div>';
    }

    function editSubs() {

        $rif=$this->schemi->exportSubrep($this->tipo);

        $this->galileo->getSubsPerRep($this->reparto);
        $result=$this->galileo->getResult();

        echo '<div style="position:relative;display:inline-block;width:60%;height:100%;overflow:scroll;vertical-align:top;" >';

            echo '<div style="width:90%;padding 3px;box-sizing:border-box;" >';

                if ($result) {
                    $fetID=$this->galileo->preFetchBase('schemi');

                    while($row=$this->galileo->getFetchBase('schemi',$fetID)) {

                        //if (array_key_exists($row['subrep'],$rif)) $bck='white';
                        //else $bck='#dddddd';

                        echo '<div id="panedit_sub_subrep_lista_div_'.$row['subrep'].'" style="position:relative;margin-top:4px;margin-bottom:4px;border:1px solid black;text-align:left;padding:3px;box-sizing:border-box;cursor:pointer;" onclick="" >';

                                echo '<div style="position:relative;font-weight:bold;" >';
                                    echo '<input id="panedit_sub_subrep_chk_'.$row['subrep'].'" style="margin-left:10px;" type="checkbox" data-sub="'.$row['subrep'].'" data-pan="'.$this->panorama['ID'].'"';
                                        if (array_key_exists($row['subrep'],$rif)) echo 'checked';
                                    echo '/>';
                                    echo '<span style="margin-left:5px;" >'.$row['subrep'].'</span>';
                                    if ($row['reparto']=='') echo ' - condiviso';

                                    echo '<input id="panedit_sub_subrep_radio_'.$row['subrep'].'" name="panedit_sub_subrep_radio" style="position:absolute;right:10px;top:0px;" type="radio" value="'.$row['subrep'].'" ';
                                        if (array_key_exists($row['subrep'],$rif)) {
                                            if ($rif[$row['subrep']]['cod_def']=='S') echo 'checked';
                                        }
                                    echo '/>';

                                echo '</div>';

                                echo '<div style="margin-left:40px;">';
                                    echo $row['descrizione'];
                                echo '</div>';

                        echo '</div>';
                    }
                }

            echo '</div>';

        echo '</div>';

        echo '<div style="position:relative;display:inline-block;width:30%;vertical-align:top;" >';
            echo '<button style="font-size:1.2em;font-weight:bold;margin-top:15px;margin-left:15px;" onclick="window._panedit.confirmSubs();" >Conferma</button>';
        echo '</div>';

    }

}

?>