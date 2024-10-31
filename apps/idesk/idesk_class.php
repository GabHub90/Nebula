<?php

require_once(DROOT.'/nebula/core/divo/divo.php');
include_once($_SERVER['DOCUMENT_ROOT']."/nebula/core/blocklist/blocklist.php");
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/core/divutil/divutil.php');
include_once($_SERVER['DOCUMENT_ROOT']."/nebula/core/odl/odl_func.php");
include_once($_SERVER['DOCUMENT_ROOT']."/nebula/core/odl/pratica_func.php");

class ideskApp extends appBaseClass {

    protected $collaboratori=array();
    protected $responsabili=array();

    protected $config=array(
        "coll"=>"",
        "gruppo"=>"",
        "default"=>"",
        "flagColl"=>1,
        "flagRep"=>0,
        "tutti"=>0
    );

    protected $odlFunc;

    protected $log=array();

    function __construct($param,$galileo) {
        
        parent::__construct($galileo);

        $this->loc='/nebula/apps/idesk/';

        //officina==reparto
        $this->param['officina']="";
        $this->param['idk_visuale']="";
        $this->param['idk_rc']="";
        $this->param['idk_cliente']="";
        $this->param['idk_marca']="";
        $this->param['idk_divo']="";
        $this->param['idk_desk']="";

        $this->loadParams($param);

        if ($this->param['officina']=='') die ('Officina non definita !!!');

        $this->galileo->getCollaboratori('reparto',$this->param['officina'],date('Ymd'));
        if ($this->galileo->getResult()) {
            $fid=$this->galileo->preFetchBase('maestro');
            while($row=$this->galileo->getFetchBase('maestro',$fid)) {
                if ($row['gruppo']=='RC' || $row['gruppo']=='RS' || $row['gruppo']=='AS') {
                    $this->collaboratori[$row['ID_coll']]=$row;
                }
            }
        }

        if (isset($this->id)) {

            $this->config['coll']=$this->id->getCollID();
            $this->config['gruppo']=$this->id->getGruppo($this->param['officina'],array('S','A','D'));

            //TEST
            $this->responsabili=array(
                "RS"=>array(
                    "default"=>'coll',
                    "flagColl"=>1,
                    "flagRep"=>1,
                    "tutti"=>1
                ),
                "RC"=>array(
                    "default"=>'coll',
                    "flagColl"=>1,
                    "flagRep"=>0,
                    "tutti"=>0
                ),
                "AS"=>array(
                    "default"=>'coll',
                    "flagColl"=>1,
                    "flagRep"=>0,
                    "tutti"=>0
                ),
                "ITR"=>array(
                    "default"=>'rep',
                    "flagColl"=>1,
                    "flagRep"=>1,
                    "tutti"=>1
                )
            );
            //END TEST

            if (array_key_exists($this->config['gruppo'],$this->responsabili)) {
                foreach ($this->config as $k=>$c) {
                    if (array_key_exists($k,$this->responsabili[$this->config['gruppo']])) $this->config[$k]=$this->responsabili[$this->config['gruppo']][$k];
                }
            }

            if ($this->param['idk_visuale']!="") $this->config['default']=$this->param['idk_visuale'];
        }

        $this->odlFunc=new nebulaOdlFunc($this->galileo);

        //se il reparto è interno "idk_cliente" di default è "apprip"
        if ($this->param['idk_cliente']=='' && in_array($this->param['officina'],$this->odlFunc->getRepint())) {
            $this->param['idk_cliente']='apprip';
        }

    }

    function initClass() {
        return ' ideskCode(\''.$this->param['nebulaFunzione']['nome'].'\');';
    }

    function getLog() {
        return $this->log;
    }

    function customDraw() {

        nebulaPraticaFunc::initJS();
        BlockList::blockListInit();

        $divo=new Divo('idesk','5%','95%',true);

        $divo->setBk('#cccccc');

        $css=array(
            "font-weight"=>"bold",
            "font-size"=>"1.3em",
            "margin-left"=>"15px",
            "margin-top"=>"2px"
        );

        $css2=array(
            "width"=>"15px",
            "height"=>"15px",
            "top"=>"50%",
            "transform"=>"translate(0%,-50%)",
            "right"=>"5px"
        );

        $divo->setChkimgCss($css2);

        ob_start();
            $this->mainDraw();

        $divo->add_div('Desktop','black',0,"",ob_get_clean(),1,$css);

        $txt="";

        $txt='<div id="avalon_odielle" style="width:100%;height:92%;" >';
            $txt.='<div style="font-weight:bold;font-size:1.3em;text-align:center;">Nessun ordine selezionato</div>'; 
        $txt.='</div>';

        $divo->add_div('Odl','black',1,"Y",$txt,0,$css);

        $txt='<div id="avalon_storico" style="width:100%;height:92%;" >';
            $txt.='<div style="font-weight:bold;font-size:1.3em;text-align:center;">Nessun ordine selezionato</div>'; 
        $txt.='</div>';

        $divo->add_div('Storico','black',1,"Y",$txt,0,$css);

        unset($txt);

        $divo->build();

        $divo->draw();

        echo '<script type="text/javascript">';

            ob_start();
                include (DROOT.'/nebula/apps/idesk/core/default.js');
            ob_end_flush();

            echo 'window._idesk_divo.postSel=function() {';
                echo <<<JS
                $('#ribbon_idk_divo').val(this.getSel());
JS;
            echo '};';
            
        echo '</script>';
    
    }

    function mainDraw() {

        $divo=new Divo('idktop','5%','96%',true);

        $divo->setBk('#e1e3ab');

        $css=array(
            "font-weight"=>"bold",
            "font-size"=>"1em",
            "margin-left"=>"10px",
            "margin-top"=>"2px"
        );

        $css2=array(
            "width"=>"10px",
            "height"=>"10px",
            "top"=>"50%",
            "transform"=>"translate(0%,-50%)",
            "right"=>"5px"
        );

        $divo->setChkimgCss($css2);

        echo '<div style="position:relative;display:inline-block;vertical-align:top;width:45%;height:100%;border-right:1px solid black;padding:3px;box-sizing:border-box;" >';

            $count=0;

            $txt='<div style="position:relative;height:7%;">';
                $txt.='<div style="position:relative;margin-top:10px;">';
                    $txt.='<span style="margin-left:10px;font-size:0.9em;">Da:</span>';
                    $txt.='<input id="idk_inarrivo_data_da" style="width:120px;margin-left:5px;" type="date" value="'.date('Y-m-d').'" />';
                    $txt.='<span style="margin-left:10px;font-size:0.9em;">A:</span>';
                    $txt.='<input id="idk_inarrivo_data_a" style="width:120px;margin-left:5px;" type="date" value="'.date('Y-m-d').'" />';
                    $txt.='<button style="margin-left:10px;" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].inarrivo(\''.$this->param['officina'].'\');">Cerca</button>';

                    /*$txt.='<div style="position:relative;display:inline-block;width:150px;text-align:right;vertical-align:top;">';
                        $txt.='<img style="width:25px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/idesk/img/last7.png" />';
                    $txt.='</div>';*/

                $txt.='</div>';
            $txt.='</div>';
            $txt.='<div id="idk_inarrivo" style="height:91%;overflow:scroll;overflow-x:hidden;"></div>';

            $txt.='<script type="text/javascript">window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].inarrivo(\''.$this->param['officina'].'\');</script>';

            $divo->add_div('Arrivo','black',0,"",$txt,($count==$this->param['idk_desk']?1:0),$css);

            $txt="";

            $count++;

            $txt.='<div style="height:99%;overflow:scroll;overflow-x:hidden;">';
                $txt.='<div id="idk_sospesi" style="width:95%;">';
                $txt.='</div>'; 
            $txt.='</div>';

            $divo->add_div('Sospesi','black',1,"Y",$txt,($count==$this->param['idk_desk']?1:0),$css);

            $txt="";

            $count++;

            $txt.='<div style="height:99%;overflow:scroll;overflow-x:hidden;">';
                $txt.='<div id="idk_esterni" style="width:95%;">';
                $txt.='</div>'; 
            $txt.='</div>';

            $divo->add_div('Esterni','black',1,"Y",$txt,($count==$this->param['idk_desk']?1:0),$css);

            $txt="";

            $count++;

            $txt.='<div style="height:99%;overflow:scroll;overflow-x:hidden;">';
                $txt.='<div id="idk_ricambi" style="width:95%;">';
                $txt.='</div>'; 
            $txt.='</div>';

            $divo->add_div('Ricambi','black',1,"Y",$txt,($count==$this->param['idk_desk']?1:0),$css);

            $txt="";

            $count++;

            $txt.='<div style="height:99%;overflow:scroll;overflow-x:hidden;">';
                $txt.='<div id="idk_pronto" style="width:95%;">';
                $txt.='</div>'; 
            $txt.='</div>';

            $divo->add_div('Pronto','black',1,"Y",$txt,($count==$this->param['idk_desk']?1:0),$css);

            $txt="";

            $count++;

            $txt='<div style="height:7%;">';
                $txt.='<div style="margin-top:10px;">';

                    $txt.='<select id="idk_filter_marca" style="position:relative;margin-left:10px;font-size:1.2em;" onchange="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].filterMarca();">';
                        $txt.='<option value="">Tutte le marche</option>';
                        foreach ($this->odlFunc->getMarcheStandard() as $marca=>$m) {
                            $txt.='<option value="'.$marca.'" ';
                                if (isset($this->param['idk_marca']) && $this->param['idk_marca']==$marca) $txt.='selected';
                            $txt.=' >'.$marca.' - '.$m.'</option>';
                        }
                    $txt.='</select>';

                    if ( in_array($this->param['officina'],$this->odlFunc->getRepint()) ) {

                        $tarr=array(
                            "cliente"=>"Cliente officina",
                            "apprip"=>"Contratto in essere"
                        );

                        $txt.='<div style="position:relative;display:inline-block;width:250px;vertical-align:top;border:2px solid brown;padding:2px;margin-left:20px;background-color:antiquewhite;">';
                            $txt.='<select id="idk_filter_cliente" style="position:relative;margin-left:10px;font-size:1.2em;" >';
                                foreach ($tarr as $k=>$c) {
                                    $txt.='<option value="'.$k.'" ';
                                        if ($k==$this->param['idk_cliente']) $txt.='selected';
                                    $txt.=' >'.$c.'</option>';
                                }
                            $txt.='</select>';
                            $txt.='<button style="margin-left:10px;" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].filterCliente();" >Filtra</button>';
                        $txt.='</div>'; 
                    }

                $txt.='</div>';
            $txt.='</div>';

            $txt.='<div style="height:91%;overflow:scroll;overflow-x:hidden;">';
                $txt.='<div id="idk_inofficina" style="width:95%;">';
                $txt.='</div>'; 
            $txt.='</div>';
            $txt.='<script type="text/javascript">window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].inofficina(\''.$this->param['officina'].'\',\''.$this->param['idk_cliente'].'\');</script>';

            $divo->add_div('Officina','black',0,"",$txt,($count==$this->param['idk_desk']?1:0),$css);

            unset($txt);

            $divo->build();

            echo '<div id="idesk_main_monitor" style="width:100%;height:100%;" >';

                $divo->draw();
            
            echo '</div>';

            $util=new nebulaUtilityDiv('idesk','window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].chiudiUtility();');

            $util->draw();

            echo '<script type="text/javascript">';

                echo 'window._idktop_divo.postSel=function() {';
                    echo <<<JS
                    $('#ribbon_idk_desk').val(this.getSel());
JS;
                echo '};';
                
            echo '</script>';

        echo '</div>';

        echo '<div style="position:relative;display:inline-block;vertical-align:top;width:25%;height:100%;padding:3px;box-sizing:border-box;" >';

            echo '<div style="">';

                echo '<div style="margin-top:10px;">';

                    if ($this->config['flagColl']==1) {
                        echo '<input name="idk_inofficina_tipolista" type="radio" style="margin-left:10px;" value="rc" '.($this->config['default']=='rc'?"checked":"").' />';
                        /*"4": {
                            "ID_coll": 4,
                            "data_i": "20201001",
                            "data_f": "21001231",
                            "ID_gruppo": 1,
                            "gruppo": "RS",
                            "des_gruppo": "Resp. Service",
                            "posizione": 5,
                            "macrogruppo": "RSC",
                            "des_macrogruppo": "Responsabili Officina",
                            "posizione_macrogruppo": 1,
                            "reparto": "VWS",
                            "macroreparto": "S",
                            "des_reparto": "Service Volkswagen",
                            "rep_concerto": "PV",
                            "des_macroreparto": "Service",
                            "nome": "Matteo",
                            "cognome": "Magi",
                            "concerto": "m.magi",
                            "cod_operaio": "",
                            "tel_interno": "352",
                            "cellulare": "",
                            "IDDIP": "13",
                            "IDMAT": "148"
                        }*/
                        echo '<select id="idk_inofficina_rc" style="position:relative;margin-left:5px;width:150px;">';
                            foreach ($this->collaboratori as $id=>$c) {
                                if ($this->config['tutti']==0 && $id!=$this->config['coll']) continue;
                                echo '<option value="'.$c['concerto'].'" ';
                                    if ($c['concerto']==$this->param['idk_rc']) echo 'selected';
                                echo ' >'.$c['nome'].' '.$c['cognome'].'</option>';
                            }
                        echo '</select>';
                    }
                    if ($this->config['flagRep']==1) {
                        echo '<input name="idk_inofficina_tipolista" type="radio" style="margin-left:10px;" value="rep" '.($this->config['default']=='rep'?"checked":"").' />';
                        echo '<span style="margin-left:5px">Reparto</span>';
                    }

                echo '</div>';

                echo '<div style="position:relative;text-align:center;height:40px;border-bottom:1px solid #777777;margin-top:10px;" >';
                    echo '<button class="divButton" style="left:50%;margin-left:-100px;width:200px;height:30px;" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].update();" >aggiorna</button>';
                echo '</div>';

            echo '</div>';

            echo '<div style="position:relative;">';
                echo '<div style="margin-top:10px;font-size:0.9em;font-weight:bold;text-align:center;">';
                    echo 'targa,telaio,intest. (più di 3 caratt.):';
                echo '</div>';

                echo '<div style="position:relative;margin-top:10px;text-align:center;">';
                    echo '<input id="idk_search" style="width:80%;text-align:center;" type="text" data-reparto="'.$this->param['officina'].'" onkeydown="if(event.keyCode==13) window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].search(\''.$this->param['officina'].'\');" />';
                echo '</div>';

                echo '<div style="position:relative;margin-top:10px;text-align:center;border-bottom:1px solid #777777;">';
                    echo '<button style="margin-bottom:15px;" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].search(\''.$this->param['officina'].'\');" >Cerca</button>';
                echo '</div>';
 
            echo '</div>';

            

            /*echo '<div>';
                echo json_encode($this->collaboratori);
            echo '</div>';*/

        echo '</div>';

        echo '<div style="position:relative;display:inline-block;vertical-align:top;width:30%;height:100%;border-left:1px solid black;padding:3px;box-sizing:border-box;" >';

            echo '<div style="position:relative;text-align:center;height:5%;font-size:1.1em;font-weight:bold;" >Timeline</div>';

            echo '<div id="idk_timeline" style="position:relative;text-align:center;height:95%;overflow:scroll;overflow-x:hidden;">';
            echo '</div>';

        echo '</div>';

    }

}
?>