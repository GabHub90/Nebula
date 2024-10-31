<?php
include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/core/divo/divo.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/core/divutil/divutil.php');

class pitstopApp extends appBaseClass {

    protected $logged="";

    protected $magazzini=array();
    protected $officine=array(); 

    protected $log=array();

    function __construct($param,$galileo) {

        parent::__construct($galileo);

        $this->loc='/nebula/apps/pitstop/';

        $this->param['magazzino']="";
        $this->param['operatore']="";
    
        $this->loadParams($param);

        $this->logged=$this->id->getLogged();

        $this->galileo->getMagazzini();
        if ($result=$this->galileo->getResult() ) {
            $fetID=$this->galileo->preFetchBase('reparti');
            while ($row=$this->galileo->getFetchBase('reparti',$fetID)) {
                $this->magazzini[$row['reparto']]=$row;
            }
        }

        ksort($this->magazzini);

        $this->galileo->clearQuery();
        $this->galileo->clearQueryOggetto('base','reparti');

        $this->galileo->getOfficine();
        if ($result=$this->galileo->getResult() ) {
            $fetID=$this->galileo->preFetchBase('reparti');
            while ($row=$this->galileo->getFetchBase('reparti',$fetID)) {
                $this->officine[$row['reparto']]=$row;
            }
        }

        ksort($this->officine);
    }

    function initClass() {
        return ' pitstopCode(\''.$this->param['nebulaFunzione']['nome'].'\');';
    }

    function getLog() {
        return $this->log;
    }

    function customDraw() {

        $divo=new Divo('pitstop','5%','94%',true);

        $divo->setBk('#aef1a5');

        $css=array(
            "font-weight"=>"bold",
            "font-size"=>"1.2em",
            "margin-left"=>"15px",
            "margin-top"=>"0px"
        );

        $css2=array(
            "width"=>"15px",
            "height"=>"15px",
            "top"=>"50%",
            "transform"=>"translate(0%,-50%)",
            "right"=>"5px"
        );

        $divo->setChkimgCss($css2);

        echo '<div id="pitstop_liste" style="position:relative;width:100%;height:100%;padding:3px;box-sizing:border-box;" >';

            ob_start();
            $this->banco();

            $divo->add_div('Banco','black',0,"",ob_get_clean(),0,$css);

            ob_start();
            $this->officina();

            $divo->add_div('Officina','black',0,"",ob_get_clean(),0,$css);

            $txt="Liste Nebula richieste dall'azienda (tutte)";

            $divo->add_div('Liste','black',1,"Y",$txt,1,$css);

            ob_start();
            $this->listeQualificate();

            $divo->add_div('Qualificate','black',1,"Y",ob_get_clean(),0,$css);

            $txt="Liste di prelievo derivanti dalle richieste evase (collaboratore)";

            $divo->add_div('Prelievo','black',1,"Y",$txt,0,$css);

            $divo->build();

            $divo->draw();

            unset($txt);
            unset($divo);

        echo '</div>';
        
        $divutil=new nebulaUtilityDiv('pitstop','window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].closeOperazioni()');
        $divutil->draw();

        echo '<script type="text/javascript">';

            ob_start();
                include (DROOT.'/nebula/apps/pitstop/core/default.js');
            ob_end_flush();
            
        echo '</script>';

    }

    function banco() {

        $divo2=new Divo('pitBanco','5%','94%',true);

        $divo2->setBk('#ecf1a5');

        $css=array(
            "font-weight"=>"bold",
            "font-size"=>"1.2em",
            "margin-left"=>"15px",
            "margin-top"=>"0px"
        );

        echo '<div style="position:relative;display:inline-block;width:65%;border-right:1px solid black;padding:3px;box-sizing:border-box;vertical-align:top;" >';

            $txt="";

            $txt='<div id="pitstop_banco_saldi" style="width:100%;height:100%;" ></div>';
            $divo2->add_div('In ordine','black',0,"",$txt,0,$css);

            $txt='<div id="pitstop_banco_aperti" style="width:100%;height:100%;" ></div>';
            $divo2->add_div('Aperti','black',0,"",$txt,0,$css);

            $txt='<div id="pitstop_banco_liste" style="width:100%;height:100%;" ></div>';
            $divo2->add_div('Liste Vendita','black',0,"",$txt,0,$css);

            $divo2->build();

            $divo2->draw();

            unset($txt);
            unset($divo2);

        echo '</div>';

        $this->galileo->clearQuery();
        $this->galileo->getCollaboratori("macroreparto","M",date('Ymd'));

        echo '<div style="position:relative;display:inline-block;width:35%;padding:3px;box-sizing:border-box;vertical-align:top;" >';

            echo '<div style="margin-top:10px;width:100%;">';

                echo '<div style="margin-left:10px;">';

                    echo '<div style="position:relative;font-weight:bold;font-size:0.9em;" >Operatore:</div>';

                    echo '<div style="position:relative;" >';
                        echo '<select id="pitstop_banco_operatore" style="font-size:1.2em;min-width:200px;" >';
                            echo '<option value="">Tutti</option>';

                            if ( $result=$this->galileo->getResult() ) {
                                $fetID=$this->galileo->preFetchBase('maestro');
                                while ($row=$this->galileo->getFetchBase('maestro',$fetID)) {
                                    echo '<option value="'.$row['concerto'].'" ';
                                        if ($row['concerto']==$this->logged) echo 'selected';
                                    echo '>'.$row['cognome'].' '.$row['nome'].'</option>';
                                }
                            }

                        echo '</select>';
                    echo '</div>';

                echo '</div>';

            echo '</div>';

            echo '<div style="margin-top:20px;width:100%;">';

                echo '<div style="position:relative;display:inline-block;width:20%;box-sizing:border-box;vertical-align:top;text-align:center;" >';
                    echo '<img style="width:30px;height:30px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/pitstop/img/reload.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].loadBanco(\''.$this->param['magazzino'].'\');" />';
                echo '</div>';

                echo '<div id="pitstop_banco_reload" style="position:relative;display:inline-block;width:80%;box-sizing:border-box;vertical-align:top;text-align:left;" >';
                    echo 'Ordini non caricati';
                echo '</div>';

            echo '</div>';

            echo '<div style="margin-top:20px;width:100%;">';

                echo '<div style="position:relative;font-weight:bold;font-size:1em;border-top:1px solid #777777;" >Filtro ordini e Liste Vendita:</div>';

                echo '<div style="position:relative;margin-top:10px;" >Ragione sociale (CASE SENSITIVE)</div>';

                echo '<div style="position:relative;">';

                    echo '<div style="position:relative;display:inline-block;vertical-align:top;width:70%;" >';
                        echo '<input id="pitstop_banco_filtro_ragsoc" style="width:98%;" type="text" onkeydown="if(event.keyCode==13) window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].setFiltroBanco();" />';
                    echo '</div>';

                    echo '<div style="position:relative;display:inline-block;vertical-align:top;width:15%;text-align:center;" >';
                        echo '<button onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].resetFiltroBanco();">reset</button>';
                    echo '</div>';

                    echo '<div style="position:relative;display:inline-block;vertical-align:top;width:15%;text-align:center;" >';
                        echo '<button onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].setFiltroBanco();">filtra</button>';
                    echo '</div>';

                echo '</div>';

            echo '</div>';

        echo '</div>';            

    }

    function officina() {

        $divo2=new Divo('pitOfficina','5%','94%',true);

        $divo2->setBk('#ecf1a5');

        $css=array(
            "font-weight"=>"bold",
            "font-size"=>"1.2em",
            "margin-left"=>"15px",
            "margin-top"=>"0px"
        );

        echo '<div style="position:relative;display:inline-block;width:65%;border-right:1px solid black;padding:3px;box-sizing:border-box;vertical-align:top;" >';

            $txt='<div id="pitstop_officina_saldi" style="width:100%;height:100%;" ></div>';
            $divo2->add_div('In ordine','black',0,"",$txt,0,$css);

            $txt='<div id="pitstop_officina_pren" style="width:100%;height:100%;" ></div>';
            $divo2->add_div('Prenotazioni','black',0,"",$txt,0,$css);

            $txt='<div id="pitstop_officina_comm" style="width:100%;height:100%;" ></div>';
            $divo2->add_div('Commesse','black',0,"",$txt,0,$css);

            $divo2->build();

            $divo2->draw();

            unset($txt);
            unset($divo2);

        echo '</div>';

        echo '<div style="position:relative;display:inline-block;width:35%;padding:3px;box-sizing:border-box;vertical-align:top;" >';

            echo '<div style="margin-top:10px;width:100%;">';

                echo '<div style="position:relative;display:inline-block;width:20%;box-sizing:border-box;vertical-align:top;text-align:center;" >';
                    echo '<img style="width:30px;height:30px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/pitstop/img/reload.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].loadOfficina(\''.$this->param['magazzino'].'\');" />';
                echo '</div>';

                echo '<div id="pitstop_officina_reload" style="position:relative;display:inline-block;width:80%;box-sizing:border-box;vertical-align:top;text-align:left;" >';
                    echo 'Ordini non caricati';
                echo '</div>';

            echo '</div>';

        echo '</div>';        

    }

    function listeQualificate() {
        //Liste corrette e qualificate dal collaboratore (collaboratore)

        echo '<div style="position:relative;width:100%;height:10%;" >';

            echo '<div style="position:relative;display:inline-block;top:5px:left:1%;vertical-align:top;width:30%;" >';

                echo '<div>';
                    echo '<label style="font-weight:bold;font-size:0.9em;" >Reparto:</label>';
                echo '</div>';
                
                echo '<div style="width:100%;">';
                    echo '<select id="pitstop_newlista_reparto" style="width:95%;" >';
                        foreach ($this->magazzini as $k=>$m) {
                            echo '<option value="'.$k.'">'.$k.' - '.$m['descrizione'].'</option>';
                        }

                        echo '<option value="">------------------------------------</option>';

                        foreach ($this->officine as $k=>$m) {
                            echo '<option value="'.$k.'">'.$k.' - '.$m['descrizione'].'</option>';
                        }
                    echo '</select>';
                echo '</div>';

            echo '</div>';

            echo '<div style="position:relative;display:inline-block;top:10px;vertical-align:top;width:5%;text-align:right;" >';
                echo '<img style="width:25px;height:25px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/pitstop/img/add.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].nuovaLista(\''.$this->id->getLogged().'\');"/>';
            echo '</div>';

        echo '</div>';

        echo '<div style="position:relative;width:100%;height:90%;overflow:scroll;overflow-x:hidden;" >';
            echo 'Lista richieste qualificate aperte per il collaboratore';
        echo '</div>';
    }

}

?>