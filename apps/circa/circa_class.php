<?php
include($_SERVER['DOCUMENT_ROOT'].'/nebula/core/veicolo/classi/veicolo_main.php');
include($_SERVER['DOCUMENT_ROOT'].'/nebula/apps/circa/classi/wormhole.php');

class circaApp extends appBaseClass {

    protected $veicolo=false;
    protected $actualVei=false;

    protected $dmss=array('generico','infinity','concerto');

    protected $log=array();

    function __construct($param,$galileo) {

        parent::__construct($galileo);

        $this->loc='/nebula/apps/circa/';

        $this->param['officina']="";
        $this->param['cir_tt']="";
        $this->param['cir_ragsoc']="";
        $this->param['appArgs']['cir_actual']="";

        $this->loadParams($param);

        //#############################
        //cir_actual NON viene passato dal RIBBON ma viene valorizzato prima di chiamare la classe CIRCA
        //questo perché il ribbon serve per cercare combinazioni di veicoli e anagrafiche per le quali sono stati fatti dei preventivi
        //una volta identificato il veicolo o il nominativo generico sul quale eseguire il preventivo non viene chiamato ribbonExecute ma vengono aggiornati i DIV
        //#############################

        if (isset($this->param['appArgs']['cir_actual'])) {
            $this->param['cir_actual']=json_decode(base64_decode($this->param['appArgs']['cir_actual']),true);

            /*cir_actual
            targa               campo di testo slegato da ID veicolo
            telaio              campo di testo slegato da ID veicolo
            dms
            ragsoc              campo di testo slegato da ID anagrafica
            odl                 numero odl - quando il preventivo è legato ad un odl di officina
            id_veicolo          quando si è selezionato un veicolo specifico dal dms
            id_anagra ??????    quando si è specificata una ANAGRAFICA dal dms (no cliente infinity)
            */

            if ($this->param['cir_actual'] && ($this->param['cir_actual']['telaio']!='' || $this->param['cir_actual']['id_veicolo']!='') ) {

                $this->veicolo=new nebulaVeicolo($this->param['cir_actual']['dms'],$this->galileo);

                if ($this->param['cir_actual']['id_veicolo']!='') $this->veicolo->loadVeicolo($this->param['cir_actual']['id_veicolo']);
                else $this->veicolo->loadTT($this->param['cir_actual']['telaio'],true);

                $this->actualVei=$this->veicolo->getInfo();
            }
        }
        else $this->param['cir_actual']=false;
    }

    function initClass() {
        return ' circaCode(\''.$this->param['nebulaFunzione']['nome'].'\');';
    }

    function getLog() {
        return $this->log;
    }

    function customDraw() {

        echo '<div style="position:relative;width:100%;height:10%;padding:3px;box-sizing:border-box;background-color:#eeeeee;border:1px solid black;border-radius:5px;" >';

            $this->drawHead();

        echo '</div>';

        echo '<div style="position:relative;width:100%;height:90%;padding:3px;box-sizing:border-box;" >';

            echo '<div style="position:relative;display:inline-block;width:45%;height:100%;border-right:1px solid black;box-sizing:border-box;vertical-align:top;" >';

                if (!$this->param['cir_actual']) {
                    $this->drawFind();
                }

            echo '</div>';

            echo '<div style="position:relative;display:inline-block;width:55%;height:100%;box-sizing:border-box;vertical-align:top;" >';

                echo '<div id="circa_right_main" style="width:100%;height:100%;';
                    if (!$this->param['cir_actual']) echo 'display:none;';
                echo '">';
                echo '</div>';

                echo '<div id="circa_right_new" style="width:100%;height:100%;';
                    if ($this->param['cir_actual']) echo 'display:none;';
                echo '">';
                    $this->drawNew();
                echo '</div>';
                
            echo '</div>';

        echo '</div>';

        echo '<script type="text/javascript" src="'.$_SERVER['SERVER_ADDRESS'].'/nebula/apps/circa/core/circa.js?v='.time().'"></script>';

        echo '<script type="text/javascript">';

            echo 'window._nebulaCirca=new circaJS();';

            ob_start();
                include (DROOT.'/nebula/apps/circa/core/default.js');
            ob_end_flush();
            
        echo '</script>';

    }

    function drawHead() {

        echo '<input id="cir_actual" type="hidden" value="'.($this->param['cir_actual']?base64_encode(json_encode($this->param['cir_actual'])):"").'" />';

        echo '<div style="position:relative;display:inline-block;width:9%;vertical-align:top;" >';
            echo '<div style="font-weight:bold;font-size:0.9em;">Targa</div>';
            echo '<div style="font-size:1em;">'.($this->actualVei?$this->actualVei['targa']:($this->param['cir_actual']?$this->param['cir_actual']['targa']:'')).'</div>';
        echo '</div>';

        echo '<div style="position:relative;display:inline-block;width:18%;vertical-align:top;" >';
            echo '<div style="font-weight:bold;font-size:0.9em;">Telaio<span style="margin-left:5px;font-weight:normal;font-size:0.9em;">('.($this->actualVei?$this->actualVei['dms']:'').' - '.($this->actualVei?$this->actualVei['rif']:'').')</span></div>';
            echo '<div style="font-size:1em;">'.($this->actualVei?$this->actualVei['telaio']:'').'</div>';
        echo '</div>';

        echo '<div style="position:relative;display:inline-block;width:23%;vertical-align:top;" >';

            echo '<div style="font-size:1em;">';
                if ($this->actualVei) {
                    echo substr($this->actualVei['cod_marca'].' - '.$this->actualVei['des_marca'],0,18).' - '.$this->actualVei['modello'];
                }
                else echo 'Nessun veicolo selezionato';
            echo '</div>';

            echo '<div style="font-size:0.9em;">';
                if ($this->actualVei) {
                    echo substr($this->actualVei['des_veicolo'],0,30);
                }
            echo '</div>';

        echo '</div>';

        echo '<div style="position:relative;display:inline-block;width:17%;vertical-align:top;" >';

            echo '<div style="font-size:1em;">';
                echo 'Consegna: ';
                if ($this->actualVei) {
                    echo mainFunc::gab_todata($this->actualVei['d_cons']);
                }
            echo '</div>';

            echo '<div style="font-size:1em;">';
                echo 'Tempario: ';
                if ($this->actualVei) {
                    echo $this->actualVei['cod_vw_tipo_veicolo'].' - ';
                    echo '<span style="font-size:0.9em;">'.substr($this->actualVei['des_vw_tipo_veicolo'],0,8).'</span>';
                }
            echo '</div>';

        echo '</div>';

        echo '<div style="position:relative;display:inline-block;width:4%;vertical-align:top;" >';
            if ($this->actualVei && $this->actualVei['cod_vw_tipo_veicolo']!='') {
                echo '<img style="width:25px;height:25px;margin-top:5px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/circa/img/edit.png" onclick="" />';
            }
        echo '</div>';

        echo '<div style="position:relative;display:inline-block;width:23%;vertical-align:top;" >';
            echo '<div style="font-weight:bold;font-size:0.9em;">Ragione Sociale</div>';
            echo '<div style="font-size:1em;">'.($this->param['cir_actual']?substr($this->param['cir_actual']['ragsoc'],0,35):'').'</div>';
        echo '</div>';

        echo '<div style="position:relative;display:inline-block;width:5%;text-align:right;vertical-align:top;" >';
                echo '<img style="width:25px;height:25px;margin-top:5px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/circa/img/add.png" onclick="window._nebulaCirca.setRight(\'new\');" />';
        echo '</div>';

    }

    function drawFind() {

        //###################################
        // SELECT delle occorrenze in base ai valori impostati nel RIBBON - ORDER BY RAGSOC
        //TEST
            $temp=array(
                "telai"=>array(
                    "WVWZZZ9NZ2D030404_infinity"=>array(
                        "telaio"=>"WVWZZZ9NZ2D030404",
                        "targa"=>"BX725MF",
                        "ragsoc"=>"Pèrinco Pallò",
                        "dms"=>"infinity",
                        "id_veicolo"=>"9287"
                    )
                ),
                "generici"=>array(
                    array(
                        "telaio"=>"",
                        "targa"=>"",
                        "ragsoc"=>"Pèrinco Pallò",
                        "dms"=>"generico",
                        "id_veicolo"=>""
                    )
                )
            );
        //END TEST
        //###################################

        echo '<div style="position:relative;width:100%;height:6%;font-size:1.2em;font-weight:bold;">';
            echo 'Risultati ricerca:';
        echo '</div>';

        echo '<div style="position:relative;width:100%;height:94%;overflow:scroll;overflow-x:hidden;" >';
            
            foreach ($temp['telai'] as $k=>$t) {
                $this->drawFindElem($t);
            }

            foreach ($temp['generici'] as $k=>$t) {
                $this->drawFindElem($t);
            }

        echo '</div>';
    }

    function drawFindElem($row) {

        echo '<div style="position:relative;margin-top:5px;margin-bottom:5px;width:95%;border:1px solid black;border-radius:5px;font-size:0.9em;padding:2px;box-sizing:border-box;cursor:pointer;" data-info="'.base64_encode(json_encode($row)).'" onclick="window._nebulaCirca.setHeader(this);" >';
            echo '<div style="position:relative;display:inline-block;width:36%;">'.$row['telaio'].'</div>';
            echo '<div style="position:relative;display:inline-block;width:15%;">'.$row['targa'].'</div>';
            echo '<div style="position:relative;display:inline-block;width:36%;">'.$row['ragsoc'].'</div>';
            echo '<div style="position:relative;display:inline-block;width:13%;">'.$row['dms'].'</div>';
        echo '</div>';
    }

    function drawNew() {

        $dms="generico";

        if ($this->param['cir_actual']) $dms=$this->param['cir_actual']['dms'];

        elseif ($this->param['officina']!="") {
            $wh=new circaWHole($this->param['officina'],$this->galileo);
            $wh->build(array('inizio'=>date('Ymd'),'fine'=>date('Ymd')));
            $dms=$wh->getTodayDms(date('Ymd'));
        }

        echo '<div style="position:relative;width:100%;height:40%;border-bottom:1px solid black;box-sizing:border-box;padding:5px;" >';

            echo '<div style="position:relative;width:100%;font-size:1.2em;font-weight:bold;">';
                echo 'Nuovo riferimento:';
                echo '<img style="position:absolute;right:7px;top:0px;width:25px;height:25px;margin-top:5px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/circa/img/chiudi.png" onclick="window._nebulaCirca.setRight(\'main\');" />';
            echo '</div>';

            /*echo '<div style="position:relative;margin-top:5px;">';
                echo '<div>';
                    echo '<label style="font-weight:bold;font-size:0.9em;">Officina(dms):</label>';
                echo '</div>';

                echo '<div style="position:relative;vertical-align:top;" >';

                    echo '<select id="" style="width:70%;font-size:1em;">';

                        echo '<option value="banco" data-officinaconcerto="" >Generico</option>';

                        $this->galileo->getOfficine();

                        if ( $result=$this->galileo->getResult() ) {
                            $fetID=$this->galileo->preFetchBase('reparti');
                            while ($row=$this->galileo->getFetchBase('reparti',$fetID)) {
                                echo '<option value="'.$row['reparto'].'" ';
                                    if ($row['reparto']==$this->param['officina']) echo 'selected="selected" ';
                                echo ' data-officinaconcerto="'.$row['concerto'].'" >'.$row['reparto'].' - '.$row['descrizione'].'</option>';
                            }                        
                        }
                    echo '</select>';

                echo '</div>';

            echo '</div>';*/

            echo '<div style="position:relative;margin-top:5px;">';
                echo '<div>';
                    echo '<label style="font-weight:bold;font-size:0.9em;">DMS:</label>';
                echo '</div>';

                echo '<div style="position:relative;" >';

                    echo '<select id="newcirca_dms" style="width:35%;font-size:1em;">';

                        foreach ($this->dmss as $k=>$d) {
                            echo '<option value="'.$d.'" ';
                                if ($d==$dms) echo 'selected';
                            echo ' >'.$d.'</option>';
                        }

                    echo '</select>';

                echo '</div>';

            echo '</div>';

            echo '<div style="position:relative;margin-top:5px;">';

                $len=strlen($this->param['cir_tt']);

                echo '<div style="position:relative;display:inline-block;width:40%;vertical-align:top;">';

                    echo '<div>';
                        echo '<label style="font-weight:bold;font-size:0.9em;">Telaio:</label>';
                        /*if ($this->param['cir_actual'] && $this->param['cir_actual']['id_veicolo']!="") {
                            echo '<span style="color:green;margin-left:10px;" >'.$this->param['cir_actual']['id_veicolo'].'</span>';
                        }
                        else echo '<span style="color:red;margin-left:10px;" >no id</span>';*/
                    echo '</div>';

                    echo '<div style="position:relative;vertical-align:top;" >';
                        /*echo '<input id="newcirca_telaio" type="text" style="position:relative;width:95%" value="'.($this->param['cir_actual']?$this->param['cir_actual']['telaio']:($len>=8?$this->param['cir_tt']:'')).'" ';
                            if ($this->veicolo) echo 'disabled';
                        echo '/>';
                        echo '<input id="newcirca_idveicolo" type="hidden" value="'.($this->param['cir_actual']?$this->param['cir_actual']['id_veicolo']:'').'"/>';*/
                        echo '<input id="newcirca_telaio" type="text" style="position:relative;width:95%" value="'.(!$this->param['cir_actual'] && $len>=8?$this->param['cir_tt']:'').'" />';
                        echo '<input id="newcirca_idveicolo" type="hidden" value=""/>';
                        //echo '<input id="newcirca_find_tt" type="text" style="margin-left:15px;width:35%;background-color:antiquewhite;" placeholder="Ricerca targa/telaio" value="'.(!$this->param['cir_actual']?$this->param['cir_tt']:'').'" />';
                    echo '</div>';

                echo '</div>';

                echo '<div style="position:relative;display:inline-block;width:35%;vertical-align:top;">';

                    echo '<div>';
                        echo '<label style="font-weight:bold;font-size:0.9em;">Targa:</label>';
                    echo '</div>';

                    echo '<div style="position:relative;vertical-align:top;" >';
                        /*echo '<input id="newcirca_targa" type="text" style="position:relative;width:80%" value="'.($this->param['cir_actual']?$this->param['cir_actual']['targa']:($len<8?$this->param['cir_tt']:'')).'"';
                            if ($this->veicolo) echo 'disabled';
                        echo '/>';*/
                        echo '<input id="newcirca_targa" type="text" style="position:relative;width:80%" value="'.(!$this->param['cir_actual'] && $len<8?$this->param['cir_tt']:'').'" />';
                    echo '</div>';

                echo '</div>';

                echo '<div style="position:relative;display:inline-block;width:25%;vertical-align:bottom;">';
                    echo '<img style="position:relative;margin-left:5px;top:3px;width:15px;height:15px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/circa/img/chiudi.png" onclick="window._nebulaCirca.clearNewForm(\'telaio\');" />';
                    echo '<button style="position:relative;margin-left:25px;">cerca</button>';
                echo '</div>';

            echo '</div>';

            echo '<div style="position:relative;margin-top:5px;">';

                echo '<div>';
                    echo '<label style="font-weight:bold;font-size:0.9em;">Ragione Sociale:</label>';
                    /*if ($this->param['cir_actual'] && $this->param['cir_actual']['id_anagra']!="") {
                        echo '<span style="color:green;margin-left:10px;" >'.$this->param['cir_actual']['id_anagra'].'</span>';
                    }
                    else echo '<span style="color:red;margin-left:10px;" >no id</span>';*/
                echo '</div>';

                echo '<div style="position:relative;vertical-align:top;" >';
                    //echo '<input id="newcirca_ragsoc" type="text" style="width:75%" value="'.(!$this->param['cir_actual']?$this->param['cir_ragsoc']:$this->param['cir_actual']['ragsoc']).'" />';
                    echo '<input id="newcirca_ragsoc" type="text" style="width:75%" value="'.(!$this->param['cir_actual']?$this->param['cir_ragsoc']:'').'" />';
                    //echo '<input id="newcirca_idanagra" type="hidden" value="'.($this->param['cir_actual']?$this->param['cir_actual']['id_anagra']:'').'"/>';
                    echo '<img style="position:relative;margin-left:5px;top:3px;width:15px;height:15px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/circa/img/chiudi.png" onclick="window._nebulaCirca.clearNewForm(\'ragsoc\');" />';
                    echo '<button style="position:relative;margin-left:25px;">cerca</button>';
                echo '</div>';

            echo '</div>';

            echo '<div style="position:relative;margin-top:20px;">';
                echo '<button style="position:relative;font-size:1.2em;font-weight:bold;" onclick="window._nebulaCirca.validateNew();">Conferma</button>';
            echo '</div>';

        echo '</div>';

    }

}

?>