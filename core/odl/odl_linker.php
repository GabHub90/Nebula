<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/core/divo/divo.php');

require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/core/veicolo/classi/veicolo_main.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/core/anagrafica/classi/anagrafica_main.php');

class nebulaOdlLinker {

    protected $dmss=array(
        'concerto',
        'infinity'
    );

    protected $master=array(
        "abbinamenti"=>"concerto",
        "veicoli"=>"concerto",
        "anagrafiche"=>"concerto"
    );

    protected $param=array(
        "veicolo"=>false,
        "abbinamento"=>"",
        "anagrafiche"=>array(
            "util"=>false,
            "intest"=>false,
            "locat"=>false,
            "fatt"=>false
        ),
        "km"=>"",
        "dms"=>"",
        "ambito"=>""
    );

    //conserva i parametri prima di "build"
    //Build non è sempre necessario (per esempio quando si eseguono delle query di ricerca degli abbinamenti)
    protected $tempParam=array(
        "veicolo"=>"",
        "abbinamento"=>"",
        "util"=>"",
        "intest"=>"",
        "locat"=>"",
        "fatt"=>"",
        "km"=>"",
        "dms"=>"",
        "ambito"=>""
    );

    //contiene gli abbinamenti possibili per i parametri (param) selezionati
    protected $abbinamenti=array();

    protected $galileo;

    protected $log=array();

    function __construct($param,$map,$galileo) {

        $this->galileo=$galileo;

        //$param contiene l'informazione del DMS

        foreach ($this->tempParam as $k=>$v) {
            if (array_key_exists($k,$param)) {
                $this->tempParam[$k]=$param[$k];
            }
        }

        $this->master['abbinamenti']=$this->tempParam['dms'];
        $this->master['veicoli']=$this->tempParam['dms'];
        $this->master['anagrafiche']=$this->tempParam['dms'];

        //$this->log[]=$param;
    }

    function build() {

        //########################################
        //trasforma in oggetti i valori di "tempParam"
        //########################################

        /*
        $p=array(
            "veicolo"=>
            "abbinamento"=>"",
            "util"=>
            "intest"=>
            "locat"=>
        );
        */

        
        foreach ($this->tempParam as $k=>$v) {

            switch ($k) {

                case 'abbinamento':
                    $this->param[$k]=$v;
                break;

                case 'km':
                    $this->param[$k]=$v;
                break;

                case 'dms':
                    $this->param[$k]=$v;
                break;

                case 'ambito':
                    $this->param[$k]=$v;
                break;

                case "veicolo":
                    if ($v!="") {
                        $this->param[$k]=new nebulaVeicolo($this->master['veicoli'],$this->galileo);
                        //$this->param[$k]->loadVeicolo($v,isset($this->tempParam['km'])?$this->tempParam['km']:'');
                        $this->param[$k]->loadVeicolo($v);
                    }
                break;

                case "util":
                    if ($v!="") {
                        $this->param['anagrafiche'][$k]=new nebulaAnagrafica($this->master['anagrafiche'],$this->galileo);
                        $this->param['anagrafiche'][$k]->loadAnagra('util',$v);
                    }
                break;

                case "intest":
                    if ($v!="") {
                        $this->param['anagrafiche'][$k]=new nebulaAnagrafica($this->master['anagrafiche'],$this->galileo);
                        $this->param['anagrafiche'][$k]->loadAnagra('intest',$v);
                    }
                break;

                case "locat":
                    if ($v!="") {
                        $this->param['anagrafiche'][$k]=new nebulaAnagrafica($this->master['anagrafiche'],$this->galileo);
                        $this->param['anagrafiche'][$k]->loadAnagra('locat',$v);
                    }
                break;
            }
        }
        
    }

    function buildLinks() {

        /*
        -   utilizza CONCERTO come master leggendo gli abbinamenti in esso contenuti
        -   legge gli abbinamenti esistenti per il veicolo/nominativi negli altri DMS e li confronta
        -   considera gli abbinamenti dell'odl attivo se esiste e lo confronta con quelli di CONCERTO
        -   sincronizza le informazioni tra DMS
        */

        $this->abbinamenti=array();

        if ($this->param['veicolo']) {

            //prendi gli abbinamenti MASTER
            if ( $this->param['veicolo']->switchDms($this->master['abbinamenti']) ) {
                $this->abbinamenti=$this->param['veicolo']->getLinks();
            }

            $this->log[]=$this->param['veicolo']->getLog();
            $this->log[]=$this->tempParam;

        }

    }

    function getMaster($ambito) {
        return $this->master[$ambito];
    }

    function getInfoVeicolo() {
        return $this->param['veicolo']->getInfo();
    }

    function drawHead($edit) {

        echo '<div style="position:relative;display:inline-block;width:44%;height:100%;vertical-align:top;/*border:1px solid #b3b3b3;*/padding:2px;box-sizing:border-box;">';

            if (!$this->param['veicolo']) {

                echo '<div style="position:relative;text-align:center;width:100%;height:65px;">';

                    echo '<div style="position:relative;text-align:center;height:35px;" >';

                        echo '<div style="position:relative;top:0px;">';
                            echo '<img style="position:relative;width:40px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/odl/img/veicolomancante2.png" />';
                            if ($edit) {
                                echo '<img style="position:relative;width:20px;top:50%;transform:translate(10px,-50%);" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/odl/img/add.png" />';
                            }
                        echo '</div>';

                    echo '</div>';

                    echo '<div>';
                        echo '<input id="odielleTempVeicolo" style="margin-top:5px;width:80%;text-align:center;" type="text" />';
                    echo '</div>';

                    //echo json_encode($this->tempParam);

                echo '</div>';

                //echo '<img style="position:relative;width:80px;top:50%;left:50%;transform:translate(-50%,-50%);"  />';
            }

            else {
                echo $this->param['veicolo']->drawMain($edit);
            }

            $txt='<div style="position:relative;text-align:left;margin-top:5px;" >';

                $txt.='<div style="position:relative;" >';

                    $txt.='<div style="position:relative;display:inline-block;width:25%;height:15px;vertical-align:top;font-size:0.9em;">Km:</div>';

                    //if ($edit) {
                        $txt.='<div style="position:relative;display:inline-block;width:50%;height:15px;vertical-align:top;font-weight:bold;font-size:1em;">';
                            $txt.='<input id="odielleLinkerKm" type="text" style="width:90%;font-size:1.1em;font-weight:bold;" value="'.$this->param['km'].'" />';
                        $txt.='</div>';
                    //}
                    /*else {
                        $txt.='<div style="position:relative;display:inline-block;width:50%;height:15px;vertical-align:top;font-weight:bold;font-size:1em;">'.$this->param['km'].'</div>';
                    }*/

                $txt.='</div>';

            $txt.='</div>';

            echo $txt;

            if ($this->param['veicolo'] && $this->param['ambito']=='avalon') {

                $temp=$this->param['veicolo']->getInfo();

                echo '<input id="avalon_storico_refresh_tt" type="hidden" value="'.$temp['telaio'].'" />';
                echo '<input id="avalon_storico_refresh_km" type="hidden" value="'.$this->param['km'].'" />';

                echo '<script style="text/javascript" >';
                    echo 'window["_nebulaApp_"+window._nebulaApp.getTagFunzione()].loadStorico(\''.$temp['telaio'].'\',\''.$this->param['km'].'\');';
                echo '</script>';
            }
       
        echo '</div>';

        echo '<div style="position:relative;display:inline-block;width:10%;height:100%;vertical-align:top;text-align:left;">';
            
            if (!$this->param['veicolo']) {
                echo '<img style="position:relative;margin-left:18px;top:50%;width:30px;height:30px;opacity:0.8;transform:translate(0px,0%);cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/odl/img/cerca.png" onclick="window._nebulaOdl.setLinker();" />';
            }

            elseif ( (!$this->param['anagrafiche']['util'] && $this->param['dms']=='concerto') || (!$this->param['anagrafiche']['intest'] && $this->param['dms']=='infinity') ) {
                echo '<img style="position:relative;margin-left:18px;top:50%;width:30px;height:30px;opacity:0.8;transform:translate(0px,0%);cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/odl/img/brklink.png" onclick="window._nebulaOdl.setLinker();" />';
            }

            else {
                echo '<img style="position:relative;margin-left:18px;top:50%;width:30px;height:30px;opacity:0.8;transform:translate(0px,0%);cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/odl/img/link.png" onclick="window._nebulaOdl.setLinker();" />';
            }
        
        echo '</div>';

        echo '<div style="position:relative;display:inline-block;width:44%;height:100%;vertical-align:top;/*border:1px solid #b3b3b3;*/padding:2px;box-sizing:border-box;">';

            $divo=new Divo('odlAna','20%','80%',true);

            $divo->setBk('#f5b7ed');

            $css=array(
                "font-weight"=>"bold",
                "font-size"=>"0.9em",
                "margin-left"=>"8px",
                "margin-top"=>"0px"
            );

            $css2=array(
                "width"=>"10px",
                "height"=>"10px",
                "top"=>"50%",
                "transform"=>"translate(0%,-50%)",
                "right"=>"3px"
            );

            $divo->setChkimgCss($css2);

            $chk=false;

            foreach($this->param['anagrafiche'] as $key=>$a) {

                if ($this->master['anagrafiche']=='infinity') {
                    if ($key=='locat' || $key=='fatt') continue;
                }

                $c=0;
                if ($a && !$chk) {
                    $c=1;
                    $chk=true;
                }

                $divo->add_div($key,'black',1,($a?$a->getStato():'R'),($a?$a->drawMain($edit):$this->anaMiss($key,$edit)),$c,$css);
            }

            $divo->build();

            $divo->draw();
           
        echo '</div>';

         ////////////////////////////////////////////////////////////////
        if ($edit) {

            $this->buildLinks();

            echo '<div id="odielleLinkerBase" style="display:none;" data-info="'.base64_encode(json_encode($this->abbinamenti)).'">'.json_encode($this->log).'</div>';
        }

    }

    function veiSearch() {

        ob_start();

            echo '<div style="text-align:center;margin-top:15px;">';
                echo '<div style="font-size:0.9em;font-weight:bold;" >Targa</div>';
                echo '<input id="odielleSearchVeicoloTarga" style="width:85%;text-align:center;" type="text" data-tipo="targa" onkeydown="if(event.keyCode==13) window._nebulaOdl.cercaVeicolo();" />';
            echo '</div>';

            echo '<div style="text-align:center;margin-top:15px;">';
                echo '<div style="font-size:0.9em;font-weight:bold;" >Telaio</div>';
                echo '<input id="odielleSearchVeicoloTelaio" style="width:85%;text-align:center;" type="text" data-tipo="telaio" onkeydown="if(event.keyCode==13) window._nebulaOdl.cercaVeicolo();" />';
            echo '</div>';

            echo '<div style="text-align:center;margin-top:15px;">';
                echo '<div style="font-size:0.9em;font-weight:bold;" >Codice Veicolo</div>';
                echo '<input id="odielleSearchVeicoloCodice" style="width:85%;text-align:center;" type="text" data-tipo="codice" onkeydown="if(event.keyCode==13) window._nebulaOdl.cercaVeicolo();" />';
            echo '</div>';

            echo '<div style="text-align:center;margin-top:15px;">';
                echo '<div style="font-size:0.9em;font-weight:bold;" >Contratto</div>';
                echo '<input id="odielleSearchVeicoloContratto" style="width:85%;text-align:center;" type="text" data-tipo="contratto" onkeydown="if(event.keyCode==13) window._nebulaOdl.cercaVeicolo();" />';
            echo '</div>';

            echo '<div style="text-align:center;margin-top:15px;">';
                echo '<button style="font-size:0.9em;font-weight:bold;" onclick="window._nebulaOdl.cercaVeicolo();">Cerca</button>';
            echo '</div>';
        
        return ob_get_clean(); 
    }

    function anaMiss($key,$edit) {

        ob_start();

            if ($key=='util') {

                echo '<div style="position:relative;text-align:center;width:100%;height:100%;">';

                    echo '<div style="text-align:center;height:40px;">';
                        echo '<img style="position:relative;width:30px;top:60%;transform:translate(10px,-50%);" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/odl/img/anagraficamancante2.png" />';

                        if ($edit) {
                            echo '<img style="position:relative;width:20px;top:60%;transform:translate(15px,-100%);" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/odl/img/add.png" />';
                        }

                    echo '</div>';

                    echo '<div>';
                        echo '<input id="odielleTempUtil" style="margin-top:5px;width:85%;text-align:center;" type="text" />';
                    echo '</div>';

                echo '</div>';

            }

            else {

                echo '<div style="position:relative;text-align:center;width:100%;height:100%;text-align:center;height:60px;">';
                    echo '<img style="position:relative;width:50px;top:50%;transform:translate(0px,-50%);" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/odl/img/anagraficamancante2.png" />';

                    if ($edit) {
                        echo '<img style="position:relative;width:20px;top:50%;transform:translate(10px,-200%);" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/odl/img/add.png" />';
                    }

                echo '</div>';
            }

        return ob_get_clean();
    }

    function anaSearch() {

        ob_start();

            echo '<div style="text-align:center;margin-top:15px;">';
                echo '<div style="font-size:0.9em;font-weight:bold;" >Nominativo</div>';
                echo '<input id="odielleSearchAnagraNominativo" style="width:85%;text-align:center;" type="text" data-tipo="nominativo" onkeydown="if(event.keyCode==13) window._nebulaOdl.cercaAnagrafica();" />';
            echo '</div>';

            echo '<div style="text-align:center;margin-top:15px;">';
                echo '<div style="font-size:0.9em;font-weight:bold;" >E-mail</div>';
                echo '<input id="odielleSearchAnagraMail" style="width:85%;text-align:center;" type="text" data-tipo="mail" onkeydown="if(event.keyCode==13) window._nebulaOdl.cercaAnagrafica();" />';
            echo '</div>';

            echo '<div style="text-align:center;margin-top:15px;">';
                echo '<div style="font-size:0.9em;font-weight:bold;" >Telefono</div>';
                echo '<input id="odielleSearchAnagraTelefono" style="width:85%;text-align:center;" type="text" data-tipo="telefono" onkeydown="if(event.keyCode==13) window._nebulaOdl.cercaAnagrafica();" />';
            echo '</div>';

            echo '<div style="text-align:center;margin-top:15px;">';
                echo '<div style="font-size:0.9em;font-weight:bold;" >Codice Anagrafica</div>';
                echo '<input id="odielleSearchAnagraCodice" style="width:85%;text-align:center;" type="text" data-tipo="codice" onkeydown="if(event.keyCode==13) window._nebulaOdl.cercaAnagrafica();" />';
            echo '</div>';

            echo '<div style="text-align:center;margin-top:15px;">';
                echo '<button style="font-size:0.9em;font-weight:bold;" onclick="window._nebulaOdl.cercaAnagrafica();">Cerca</button>';
            echo '</div>';
        
        return ob_get_clean(); 
    
    }

    function drawBody() {

        $css=array(
            "font-weight"=>"bold",
            "font-size"=>"1em",
            "margin-left"=>"8px",
            "margin-top"=>"0px"
        );

        //echo '<img style="position:absolute;width:20px;cursor:pointer;top:10px;left:10px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/odl/img/chiudi.png" onclick="window._nebulaOdl.setOdl(false);" />';

        echo '<div style="position:relative;display:inline-block;width:29%;height:100%;vertical-align:top;text-align:center;">';

            echo '<div style="width:100%;height:5%;text-align:center;font-weight:bold;" >Abbinamenti (globale)</div>';

            echo '<div id="linkerListaAbbinamenti" style="width:100%;height:94%;border-right:2px solid #817f7f;"></div>';

        echo '</div>';

        echo '<div style="position:relative;display:inline-block;width:35%;height:100%;vertical-align:top;text-align:center;margin-left:0.4%;">';

            echo '<div style="position:relative;width:100%;height:5%;text-align:center;font-weight:bold;" >';
                echo '<div>Veicoli (base:'.$this->master['veicoli'].')</div>';
                echo '<img style="position:absolute;width:25px;left:0px;top:-5px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/odl/img/eliminaveicolo.png" onclick="window._nebulaOdl.linker.delVeicolo();" />';
            echo '</div>';

            echo '<div style="width:100%;height:94%;border-right:2px solid #817f7f;">';

                $divo=new Divo('odlSearchVei','5%','95%',true);

                $divo->setBk('#f5b7ed');

                $txt='<div id="linkerListaVeicoli" style="width:100%;height:100%;overflow:scroll;overflow-x:hidden;" ></div>';

                $divo->add_div('Lista','black',0,'',$txt,0,$css);

                $txt="";

                $divo->add_div('Cerca','black',0,'',$this->veiSearch(),1,$css);
                //$divo->add_div('Modifica','black',0,'',$txt,0,$css);

                $divo->build();

                $divo->draw();

            echo '</div>';

        echo '</div>';

        echo '<div style="position:relative;display:inline-block;width:35%;height:100%;vertical-align:top;text-align:center;margin-left:0.4%;">';

            echo '<div style="position:relative;width:100%;height:5%;text-align:center;font-weight:bold;" >';
                echo '<div>Anagrafiche (base:'.$this->master['anagrafiche'].')</div>';
                echo '<img style="position:absolute;width:25px;left:0px;top:-5px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/odl/img/eliminaanagrafica.png" onclick="window._nebulaOdl.linker.delAnagra();"/>';
            echo '</div>';

            echo '<div style="width:100%;height:94%;/*border:1px dotted black;*/">';

                $divo=new Divo('odlSearchAna','5%','95%',true);

                $divo->setBk('#f5b7ed');

                $txt='<div id="linkerListaAnagrafiche" style="width:100%;height:100%;overflow:scroll;overflow-x:hidden;" ></div>';

                $divo->add_div('Lista','black',0,'',$txt,0,$css);

                $txt="";

                $divo->add_div('Cerca','black',0,'',$this->anaSearch(),1,$css);
                //$divo->add_div('Modifica','black',0,'',$txt,0,$css);

                $divo->build();

                $divo->draw();

            echo '</div>';

        echo '</div>';

    }

}

?>