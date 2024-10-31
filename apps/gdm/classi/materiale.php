<?php
require_once(DROOT.'/nebula/apps/gdm/classi/form_pneumatici.php');

class gdmMateriale {

    protected $info=array(
        "id"=>"0",
        "tipologia"=>"",
        "locazione"=>"",
        "annotazioni"=>"",
        "nome"=>"",
        "descrizione"=>"",
        "dotASx"=>"",
        "dotADx"=>"",
        "dotPSx"=>"",
        "dotPDx"=>"",
        "marcaASx"=>"",
        "marcaADx"=>"",
        "marcaPSx"=>"",
        "marcaPDx"=>"",
        "usuraASx"=>"",
        "usuraADx"=>"",
        "usuraPSx"=>"",
        "usuraPDx"=>"",
        "dimeASx"=>"",
        "dimeADx"=>"",
        "dimePSx"=>"",
        "dimePDx"=>"",
        "compoGomme"=>"",
        "tipoGomme"=>"",
        "colore"=>"",
        "idTelaio"=>"",
        "proprietario"=>"",
        "isBusy"=>"False",
        "isAnnullato"=>"False",
        "isAnnullabile"=>"True",
        "isFull"=>"",
        "dataCreazione"=>""
    );

    protected $operazioni=array();

    protected $ultimoMovimento=array(
        "Deposito"=>false,
        "Vettura"=>false,
        "Cliente"=>false,
        "Smaltimento"=>false
    );

    protected $galileo;

    function __construct($info,$galileo) {

        $this->galileo=$galileo;

        foreach ($this->info as $k=>$o) {
            if (array_key_exists($k,$info)) $this->info[$k]=$info[$k];
        }

        //////////////////////////////////

        if ($this->info['id']!="0") {

            $this->galileo->clearQuery();
            $this->galileo->clearQueryOggetto('default','gdm');
            $this->galileo->setResult(false);

            //echo '<div>'.$this->info['idTelaio'].'</div>';

            $this->galileo->executeGeneric('gdm','getOperazioni',array('telaio'=>$this->info['idTelaio']),'');

            if ($result=$this->galileo->getResult()) {
                $fid=$this->galileo->preFetch('gdm');

                while($row=$this->galileo->getFetch('gdm',$fid)) {

                    if (!$this->ultimoMovimento[$row['destinazione']] && $row['statoOp']=='Completa') $this->ultimoMovimento[$row['destinazione']]=$row['data_rif'];

                    if (!isset($this->operazioni[$row['idRi']])) {
                        $this->operazioni[$row['idRi']]=array(
                            "dataRi"=>$row['dataRi'],
                            "statoRi"=>$row['statoRi'],
                            "operazioni"=>array()
                        );
                    }

                    $this->operazioni[$row['idRi']]['operazioni'][$row['id']]=$row;
                }
            }
        }
    }

    function setInfo($obj) {
        foreach ($this->info as $k=>$o) {
            if (array_key_exists($k,$obj)) $this->info[$k]=$obj[$k];
        }
    }

    function getOperazioni() {
        return $this->operazioni;
    }

    function getFull() {
        if ($this->info['isFull']=='False') return false;
        else return true;
    }
    
    function drawInfo($flag,$form,$op) {
        
        if ($this->info['tipologia']=='Pneumatici') $this->drawPneumatici($flag,$form,$op);
        elseif ($this->info['tipologia']=='Generico') $this->drawGenerico($flag,$form,$op);
    }

    function drawLink() {
       
        echo '<div id="gdm_busy_'.$this->info['id'].'" style="font-size:0.9em;color:red;';
            if ($this->info['isBusy']!='True') echo 'display:none;';
        echo '" data-busy="'.$this->info['isBusy'].'" >Busy</div>';

        echo '<div id="gdm_buttons_'.$this->info['id'].'" style="';
            if ($this->info['isBusy']=='True') echo 'display:none;';
        echo '" data-busy="'.$this->info['isBusy'].'" >';

            echo '<div style="height:15px;" >';
            echo '</div>';

            if ($this->info['proprietario']!='Deposito') {
                echo '<div>';
                    echo '<img style="width:15px;height:15px;margin-top:5px;margin-bottom:5px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/gdm/img/deposito.png" onclick="window._nebulaGdm.addNew(\''.$this->info['id'].'\',\''.$this->info['proprietario'].'\',\'Deposito\');" />';
                echo '</div>';
            }
            if ($this->info['proprietario']!='Vettura' && $this->info['isFull']!='False') {
                echo '<div>';
                    echo '<img style="width:15px;height:15px;margin-top:5px;margin-bottom:5px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/gdm/img/veicolo.png" onclick="window._nebulaGdm.addNew(\''.$this->info['id'].'\',\''.$this->info['proprietario'].'\',\'Vettura\');" />';
                echo '</div>';
            }
            if ($this->info['proprietario']!='Cliente') {
                echo '<div>';
                    echo '<img style="width:15px;height:15px;margin-top:5px;margin-bottom:5px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/gdm/img/cliente.png" onclick="window._nebulaGdm.addNew(\''.$this->info['id'].'\',\''.$this->info['proprietario'].'\',\'Cliente\');" />';
                echo '</div>';
            }

        echo '</div>';
    }

    function drawPneumatici($flag,$form,$op) {

        if ($flag) {

            echo '<div style="position:relative;width:100%;height:20px;">';

                echo '<div style="position:relative;display:inline-block;width:5%;text-align:center;vertical-align:top;height:100%;" >';
                    echo '<input id="gdmMatRadio_'.$this->info['id'].'" name="gdmMatRadio" type="radio" style="position:relative;top:35%;transform:translate(0px,-50%);" value="'.$this->info['id'].'" onclick="window._nebulaGdm.selectMateriale(this.value);" />';
                echo '</div>';

                echo '<div style="position:relative;display:inline-block;width:8%;vertical-align:top;height:100%;" >';
                    echo '<div style="position:relative;top:50%;transform:translate(0px,-50%);">'.$this->info['id'].'</div>';
                echo '</div>';

                echo '<div style="position:relative;display:inline-block;width:12%;font-size:0.8em;vertical-align:top;height:100%;" >';
                    if ($this->info['isBusy']!='True' || $this->info['isAnnullato']=='Fake') {
                        echo '<img style="width:15px;height:15px;margin-right:5px;margin-top:3px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/gdm/img/edit.png" onclick="window._nebulaGdm.edit(\''.$this->info['id'].'\');" />';
                    }
                    echo '<span style="position:relative;transform:translate(0px,-50%);">'.substr($this->info['tipologia'],0,5).'</span>';
                echo '</div>';

                echo '<div style="position:relative;display:inline-block;width:13%;text-align:center;vertical-align:top;height:100%;" >';
                    echo '<div style="position:relative;top:50%;transform:translate(0px,-50%);">'.$this->info['compoGomme'].'</div>';
                echo '</div>';

                echo '<div style="position:relative;display:inline-block;width:19%;text-align:center;vertical-align:top;height:100%;" >';
                    echo '<div style="position:relative;top:50%;transform:translate(0px,-50%);">'.$this->info['tipoGomme'].'</div>';
                echo '</div>';

                if ($this->info['proprietario']=='Deposito') {
                    echo '<div style="position:relative;display:inline-block;width:20%;border:1px solid black;text-align:center;vertical-align:top;height:90%;" >';
                        echo '<div style="position:relative;top:50%;transform:translate(0px,-50%);" >'.$this->info['locazione'].'</div>';
                    echo '</div>';
                }
                else echo '<div style="position:relative;display:inline-block;width:20%"></div>';

                echo '<div style="position:relative;display:inline-block;width:15%;text-align:center;vertical-align:top;height:100%;" >';
                    if ($this->ultimoMovimento[$this->info['proprietario']]) echo '<div style="position:relative;top:50%;transform:translate(0px,-50%);">'.mainFunc::gab_todata($this->ultimoMovimento[$this->info['proprietario']]).'</div>';
                echo '</div>';

                echo '<div style="position:relative;display:inline-block;width:7%;text-align:center;vertical-align:top;" >';
                    if ($this->info['proprietario']=='Deposito') {
                        echo '<img style="width:20px;height:20px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/gdm/img/print.png" onclick="window._nebulaGdm.stampaEtichetta(\''.$this->info['id'].'\');" />';
                    }
                echo '</div>';

            echo '</div>';

        }

        $this->drawAnnotazioni($form,$op);

        echo '<div id="GDM_materiali_main" style="position:relative;width:100%;cursor:pointer;" >';

            echo '<div style="position:relative;display:inline-block;width:93%;text-align:center;vertical-align:top;" onclick="window._nebulaGdm.editNote(\''.$this->info['id'].'\');" >';

                if ($this->info['isAnnullato']=='Fake') {

                    echo '<div style="position:relative;display:inline-block;width:24%;text-align:center;vertical-align:top;" >';
                        echo '<div style="position:relative;width:100%;font-weight:bold;margin-bottom:5px;">Anteriore Sx</div>';
                        echo '<img style="width:80px;height:40px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/gdm/img/fake2.png" />';
                    echo '</div>';
                    echo '<div style="position:relative;display:inline-block;width:24%;text-align:center;vertical-align:top;border-right:1px solid black;" >';
                        echo '<div style="position:relative;width:100%;font-weight:bold;margin-bottom:5px;">Anteriore Dx</div>';
                        echo '<img style="width:80px;height:40px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/gdm/img/fake2.png" />';
                    echo '</div>';
                    echo '<div style="position:relative;display:inline-block;width:24%;text-align:center;vertical-align:top;border-left:1px solid black;" >';
                        echo '<div style="position:relative;width:100%;font-weight:bold;margin-bottom:5px;">Posteriore Sx</div>';
                        echo '<img style="width:80px;height:40px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/gdm/img/fake2.png" />';
                    echo '</div>'; 
                    echo '<div style="position:relative;display:inline-block;width:24%;text-align:center;vertical-align:top;" >';
                        echo '<div style="position:relative;width:100%;font-weight:bold;margin-bottom:5px;">Posteriore Dx</div>';
                        echo '<img style="width:80px;height:40px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/gdm/img/fake2.png" />';
                    echo '</div>'; 
                }

                else {

                    echo '<div style="position:relative;display:inline-block;width:24%;text-align:center;vertical-align:top;" >';
                        echo '<div style="position:relative;width:100%;font-weight:bold;margin-bottom:5px;">Anteriore Sx</div>';
                        //if ($this->info['usuraASx']>0 || $this->info['proprietario']=='Smaltimento') {
                        if ($this->info['usuraASx']>0) {
                            echo '<div style="position:relative;width:100%;height:18px;">'.$this->info['dimeASx'].'</div>';
                            echo '<div style="position:relative;width:100%;height:18px;">'.substr($this->info['marcaASx'],0,13).'</div>';
                            echo '<div style="position:relative;width:100%;height:18px;">';
                                echo '<div style="position:relative;display:inline-block;width:50%;text-align:center;vertical-align:top;"><span style="font-size:0.7em;font-weight:bold;margin-right:3px;">DOT</span>'.$this->info['dotASx'].'</div>';
                                if ($this->info['proprietario']!='Smaltimento') {
                                    echo '<div style="position:relative;display:inline-block;width:50%;text-align:center;vertical-align:top;">'.number_format($this->info['usuraASx'],1,".",",").' mm'.'</div>';
                                }
                            echo '</div>';
                        }
                    echo '</div>';

                    echo '<div style="position:relative;display:inline-block;width:24%;text-align:center;vertical-align:top;border-right:1px solid black;" >';
                        echo '<div style="position:relative;width:100%;font-weight:bold;margin-bottom:5px;">Anteriore Dx</div>';
                        //if ($this->info['usuraADx']>0 || $this->info['proprietario']=='Smaltimento') {
                        if ($this->info['usuraADx']>0) {
                            echo '<div style="position:relative;width:100%;height:18px;">'.$this->info['dimeADx'].'</div>';
                            echo '<div style="position:relative;width:100%;height:18px;">'.substr($this->info['marcaADx'],0,13).'</div>';
                            echo '<div style="position:relative;width:100%;height:18px;">';
                                echo '<div style="position:relative;display:inline-block;width:50%;text-align:center;vertical-align:top;"><span style="font-size:0.7em;font-weight:bold;margin-right:3px;">DOT</span>'.$this->info['dotADx'].'</div>';
                                if ($this->info['proprietario']!='Smaltimento') {
                                    echo '<div style="position:relative;display:inline-block;width:50%;text-align:center;vertical-align:top;">'.number_format($this->info['usuraADx'],1,".",",").' mm'.'</div>';
                                }
                            echo '</div>';
                        }
                    echo '</div>';

                    echo '<div style="position:relative;display:inline-block;width:24%;text-align:center;vertical-align:top;border-left:1px solid black;" >';
                        echo '<div style="position:relative;width:100%;font-weight:bold;margin-bottom:5px;">Posteriore Sx</div>';
                        //(if ($this->info['usuraPSx']>0|| $this->info['proprietario']=='Smaltimento') {
                        if ($this->info['usuraPSx']>0) {
                            echo '<div style="position:relative;width:100%;height:18px;">'.$this->info['dimePSx'].'</div>';
                            echo '<div style="position:relative;width:100%;height:18px;">'.substr($this->info['marcaPSx'],0,13).'</div>';
                            echo '<div style="position:relative;width:100%;height:18px;">';
                                echo '<div style="position:relative;display:inline-block;width:50%;text-align:center;vertical-align:top;"><span style="font-size:0.7em;font-weight:bold;margin-right:3px;">DOT</span>'.$this->info['dotPSx'].'</div>';
                                if ($this->info['proprietario']!='Smaltimento') {
                                    echo '<div style="position:relative;display:inline-block;width:50%;text-align:center;vertical-align:top;">'.number_format($this->info['usuraPSx'],1,".",",").' mm'.'</div>';
                                }
                            echo '</div>';
                        }
                    echo '</div>';

                    echo '<div style="position:relative;display:inline-block;width:24%;text-align:center;vertical-align:top;" >';
                        echo '<div style="position:relative;width:100%;font-weight:bold;margin-bottom:5px;">Posteriore Dx</div>';
                        //if ($this->info['usuraPDx']>0 || $this->info['proprietario']=='Smaltimento') {
                        if ($this->info['usuraPDx']>0) {
                            echo '<div style="position:relative;width:100%;height:18px;">'.$this->info['dimePDx'].'</div>';
                            echo '<div style="position:relative;width:100%;height:18px;">'.substr($this->info['marcaPDx'],0,13).'</div>';
                            echo '<div style="position:relative;width:100%;height:18px;">';
                                echo '<div style="position:relative;display:inline-block;width:50%;text-align:center;vertical-align:top;"><span style="font-size:0.7em;font-weight:bold;margin-right:3px;">DOT</span>'.$this->info['dotPDx'].'</div>';
                                if ($this->info['proprietario']!='Smaltimento') {
                                    echo '<div style="position:relative;display:inline-block;width:50%;text-align:center;vertical-align:top;">'.number_format($this->info['usuraPDx'],1,".",",").' mm'.'</div>';
                                }
                            echo '</div>';
                        }
                    echo '</div>';

                }
            
            echo '</div>';

            echo '<div style="position:relative;display:inline-block;width:7%;text-align:center;vertical-align:top;" >';
                
                if ($this->info['proprietario']!='Smaltimento' && $flag) $this->drawLink();

            echo '</div>';

        echo '</div>';

    }

    function drawGenerico($flag,$form,$op) {
        echo 'materiale generico';
    }

    function drawAnnotazioni($form,$op) {

        if ($form) {
            echo '<div style="position:relative;width:100%;">';
                echo '<div  id="js_chk_gdmForm_'.$this->info['id'].'_elem_annotazioni" class="chekko_elem" style="margin-top:5px;margin-bottom:5px;padding:2px;box-sizing-border-box;">';
                    echo '<div style="font-weight:bold;font-size:0.9em;text-align:left;">Annotazioni:</div>';
                    echo '<input id="gdmForm_'.$this->info['id'].'_annotazioni" type="text" style="width:90%;" value="'.$this->info['annotazioni'].'" class="js_chk_gdmForm_'.$this->info['id'].'" js_chk_gdmForm_'.$this->info['id'].'_tipo="annotazioni" />';
                    echo '<img style="position:relative;width:20px;height:20px;top:5px;left:5px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/gdm/img/chiudi.png" onclick="window._js_chk_gdmForm_'.$this->info['id'].'.delAnnotazione(\''.$this->info['id'].'\');" />';
                echo '</div>';

                echo '<input id="gdmForm_'.$this->info['id'].'_operazione" type="hidden" value="'.$op.'" class="js_chk_gdmForm_'.$this->info['id'].'" js_chk_gdmForm_'.$this->info['id'].'_tipo="operazione"/>';
                echo '<input id="gdmForm_'.$this->info['id'].'_destinazione" type="hidden" value="'.($this->info['proprietario']).'" class="js_chk_gdmForm_'.$this->info['id'].'" js_chk_gdmForm_'.$this->info['id'].'_tipo="destinazione"/>';
                echo '<input id="gdmForm_'.$this->info['id'].'_origine" type="hidden" value="'.$this->info['proprietario'].'" class="js_chk_gdmForm_'.$this->info['id'].'" js_chk_gdmForm_'.$this->info['id'].'_tipo="origine"/>';
                echo '<input id="gdmForm_'.$this->info['id'].'_id" type="hidden" value="'.$this->info['id'].'" class="js_chk_gdmForm_'.$this->info['id'].'" js_chk_gdmForm_'.$this->info['id'].'_tipo="id"/>';

                echo '<script type="text/javascript" >';
                    echo 'window._ckmf.addForm("gdmForm_'.$this->info['id'].'");';
                echo '</script>';

            echo '</div>';
        }
        //se occorre scrivere solo le indicazioni
        else {
            echo '<div style="position:relative;width:100%;height:15px;">';

                echo '<div style="position:relative;display:inline-block;width:5%;vertical-align:top;height:100%;" ></div>';

                echo '<div style="position:relative;display:inline-block;width:90%;font-size:0.9em;color:red;">';
                    echo $this->info['annotazioni'];
                echo '</div>';

            echo '</div>';
        }
        
    }

    ////////////////////////////////////////////////

    function drawForm($usura,$op) {
        
        if ($this->info['tipologia']=='Pneumatici') $this->drawFormPneumatici($usura,$op);
        elseif ($this->info['tipologia']=='Generico') $this->drawFormGenerico($op);
    }

    function drawFormPneumatici($usura,$op) {

        $form=new gdmPneuForm('gdmForm_'.$this->info['id'],$op);

        $opt=array(
            "8.0"=>"8.0",
            "7.0"=>"7.0",
            "6.5"=>"6.5",
            "6.0"=>"6.0",
            "5.5"=>"5.5",
            "5.0"=>"5.0",
            "4.0"=>"4.0",
            "3.0"=>"3.0",
            "2.0"=>"2.0",
            "-1"=>"smaltita"
        );

        $compoOpt=array(
            "TRENO"=>"TRENO",
            "KIT"=>"KIT"
        );

        $tipoOpt=array(
            "ESTIVO"=>"ESTIVO",
            "INVERNALE"=>"INVERNALE",
            "4 STAGIONI"=>"4 STAGIONI"
        );

        //###################
        if ($op==0) {
            $opt["-1"]='MANCANTE';

            //if ($this->info['compoGomme']=='KIT') {
                $opt['-2']="solo cerchio";
            //}
        }
        elseif ($this->info['compoGomme']=='KIT') {
            unset($opt['-1']);
            $opt['-2']="solo cerchio";
        }
        //###################

        if ($op!=0) {

            $fields=array(
                "id"=>array(
                    "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
                    "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
                ),
                "misuraASX"=>array(
                    "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
                    "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
                ),
                "misuraADX"=>array(
                    "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
                    "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
                ),
                "misuraPSX"=>array(
                    "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
                    "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
                ),
                "misuraPDX"=>array(
                    "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
                    "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
                ),
                "marcaASX"=>array(
                    "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
                    "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
                ),
                "marcaADX"=>array(
                    "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
                    "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
                ),
                "marcaPSX"=>array(
                    "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
                    "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
                ),
                "marcaPDX"=>array(
                    "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
                    "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
                ),
                "dotASX"=>array(
                    "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
                    "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
                ),
                "dotADX"=>array(
                    "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
                    "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
                ),
                "dotPSX"=>array(
                    "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
                    "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
                ),
                "dotPDX"=>array(
                    "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
                    "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
                ),
                "usuraASX"=>array(
                    "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
                    "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
                ),
                "usuraADX"=>array(
                    "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
                    "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
                ),
                "usuraPSX"=>array(
                    "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
                    "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
                ),
                "usuraPDX"=>array(
                    "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
                    "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
                ),
                "annotazioni"=>array(
                    "js_chk_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
                    "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
                ),
                "operazione"=>array(
                    "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
                    "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
                ),
                "destinazione"=>array(
                    "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
                    "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
                ),
                "origine"=>array(
                    "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
                    "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
                )
            );
        }
        elseif ($op==0) {

            $fields=array(
                "id"=>array(
                    "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
                    "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
                ),
                "misuraASX"=>array(
                    "js_chk_req"=>array("codice"=>3,"anor"=>"","anand"=>"","anxor"=>""),
                    "js_chk_ifreq"=>array("campo"=>"usuraASX","op"=>">","val"=>"0")
                ),
                "misuraADX"=>array(
                    "js_chk_req"=>array("codice"=>3,"anor"=>"","anand"=>"","anxor"=>""),
                    "js_chk_ifreq"=>array("campo"=>"usuraADX","op"=>">","val"=>"0")
                ),
                "misuraPSX"=>array(
                    "js_chk_req"=>array("codice"=>3,"anor"=>"","anand"=>"","anxor"=>""),
                    "js_chk_ifreq"=>array("campo"=>"usuraPSX","op"=>">","val"=>"0")
                ),
                "misuraPDX"=>array(
                    "js_chk_req"=>array("codice"=>3,"anor"=>"","anand"=>"","anxor"=>""),
                    "js_chk_ifreq"=>array("campo"=>"usuraPDX","op"=>">","val"=>"0")
                ),
                "marcaASX"=>array(
                    "js_chk_req"=>array("codice"=>3,"anor"=>"","anand"=>"","anxor"=>""),
                    "js_chk_ifreq"=>array("campo"=>"usuraASX","op"=>">","val"=>"0")
                ),
                "marcaADX"=>array(
                    "js_chk_req"=>array("codice"=>3,"anor"=>"","anand"=>"","anxor"=>""),
                    "js_chk_ifreq"=>array("campo"=>"usuraADX","op"=>">","val"=>"0")
                ),
                "marcaPSX"=>array(
                    "js_chk_req"=>array("codice"=>3,"anor"=>"","anand"=>"","anxor"=>""),
                    "js_chk_ifreq"=>array("campo"=>"usuraPSX","op"=>">","val"=>"0")
                ),
                "marcaPDX"=>array(
                    "js_chk_req"=>array("codice"=>3,"anor"=>"","anand"=>"","anxor"=>""),
                    "js_chk_ifreq"=>array("campo"=>"usuraPDX","op"=>">","val"=>"0")
                ),
                "dotASX"=>array(
                    "js_chk_req"=>array("codice"=>3,"anor"=>"","anand"=>"","anxor"=>""),
                    "js_chk_ifreq"=>array("campo"=>"usuraASX","op"=>">","val"=>"0")
                ),
                "dotADX"=>array(
                    "js_chk_req"=>array("codice"=>3,"anor"=>"","anand"=>"","anxor"=>""),
                    "js_chk_ifreq"=>array("campo"=>"usuraADX","op"=>">","val"=>"0")
                ),
                "dotPSX"=>array(
                    "js_chk_req"=>array("codice"=>3,"anor"=>"","anand"=>"","anxor"=>""),
                    "js_chk_ifreq"=>array("campo"=>"usuraPSX","op"=>">","val"=>"0")
                ),
                "dotPDX"=>array(
                    "js_chk_req"=>array("codice"=>3,"anor"=>"","anand"=>"","anxor"=>""),
                    "js_chk_ifreq"=>array("campo"=>"usuraPDX","op"=>">","val"=>"0")
                ),
                "usuraASX"=>array(
                    "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
                    "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
                ),
                "usuraADX"=>array(
                    "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
                    "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
                ),
                "usuraPSX"=>array(
                    "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
                    "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
                ),
                "usuraPDX"=>array(
                    "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
                    "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
                ),
                "annotazioni"=>array(
                    "js_chk_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
                    "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
                ),
                "operazione"=>array(
                    "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
                    "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
                ),
                "destinazione"=>array(
                    "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
                    "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
                ),
                "origine"=>array(
                    "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
                    "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
                ),
                "compoGomme"=>array(
                    "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
                    "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
                ),
                "tipoGomme"=>array(
                    "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
                    "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
                )
            );
        }



        $tipi=array(
            "id"=>"none",
            "misuraASX"=>"misura",
            "misuraADX"=>"misura",
            "misuraPSX"=>"misura",
            "misuraPDX"=>"misura",
            "marcaASX"=>"text",
            "marcaADX"=>"text",
            "marcaPSX"=>"text",
            "marcaPDX"=>"text",
            "dotASX"=>"dot",
            "dotADX"=>"dot",
            "dotPSX"=>"dot",
            "dotPDX"=>"dot",
            "usuraASX"=>"none",
            "usuraADX"=>"none",
            "usuraPSX"=>"none",
            "usuraPDX"=>"none",
            "annotazioni"=>"text",
            "operazione"=>"none",
            "destinazione"=>"none",
            "origine"=>"none"
        );

        if ($op==0) {
            $tipi['compoGomme']='none';
            $tipi['tipoGomme']='none';
        }

        $export=array(
            "id"=>"",
            "dimeASx"=>"",
            "dimeADx"=>"",
            "dimePSx"=>"",
            "dimePDx"=>"",
            "marcaASx"=>"",
            "marcaADx"=>"",
            "marcaPSx"=>"",
            "marcaPDx"=>"",
            "dotASx"=>"",
            "dotADx"=>"",
            "dotPSx"=>"",
            "dotPDx"=>"",
            "usuraASx"=>"",
            "usuraADx"=>"",
            "usuraPSx"=>"",
            "usuraPDx"=>"",
            "annotazioni"=>"",
            "operazione"=>"",
            "destinazione"=>"",
            "origine"=>""
        );

        if ($op==0) {
            $export['compoGomme']='';
            $export['tipoGomme']='';
        }

        $conv=array(
            "id"=>"id",
            "dimeASx"=>"misuraASX",
            "dimeADx"=>"misuraADX",
            "dimePSx"=>"misuraPSX",
            "dimePDx"=>"misuraPDX",
            "marcaASx"=>"marcaASX",
            "marcaADx"=>"marcaADX",
            "marcaPSx"=>"marcaPSX",
            "marcaPDx"=>"marcaPDX",
            "dotASx"=>"dotASX",
            "dotADx"=>"dotADX",
            "dotPSx"=>"dotPSX",
            "dotPDx"=>"dotPDX",
            "usuraASx"=>"usuraASX",
            "usuraADx"=>"usuraADX",
            "usuraPSx"=>"usuraPSX",
            "usuraPDx"=>"usuraPDX",
            "annotazioni"=>"annotazioni",
            "operazione"=>"operazione",
            "destinazione"=>"destinazione",
            "origine"=>"origine"
        );

        if ($op==0) {
            $conv['compoGomme']='compoGomme';
            $conv['tipoGomme']='tipoGomme';
        }

        $mappa=array(
            $mappa['id']=array(
                "prop"=>array(
                    "input"=>"input",
                    "tipo"=>"hidden",
                    "maxlenght"=>"",
                    "options"=>"",
                    "rows"=>"",
                    "default"=>$this->info['id'],
                    "placeholder"=>"",
                    "disabled"=>false
                ),
                "css"=>array(
                    "width"=>"90%;",
                    "text-align"=>"left;",
                    "font-size"=>"1.2em;"
                )
            ),
            "misuraASX"=>array(
                "prop"=>array(
                    "input"=>"input",
                    "tipo"=>"text",
                    "maxlenght"=>"",
                    "options"=>array(),
                    "rows"=>"",
                    "default"=>$this->info['usuraASx']>0?$this->info['dimeASx']:"",
                    "placeholder"=>"misura",
                    "disabled"=>false
                ),
                "css"=>array(
                    "width"=>"100%;",
                    "text-align"=>"center;",
                    "font-size"=>"1.1em;"
                )
            ),
            "misuraADX"=>array(
                "prop"=>array(
                    "input"=>"input",
                    "tipo"=>"text",
                    "maxlenght"=>"",
                    "options"=>array(),
                    "rows"=>"",
                    "default"=>$this->info['usuraADx']>0?$this->info['dimeADx']:"",
                    "placeholder"=>"misura",
                    "disabled"=>false
                ),
                "css"=>array(
                    "width"=>"100%;",
                    "text-align"=>"center;",
                    "font-size"=>"1.1em;"
                )
            ),
            "misuraPSX"=>array(
                "prop"=>array(
                    "input"=>"input",
                    "tipo"=>"text",
                    "maxlenght"=>"",
                    "options"=>array(),
                    "rows"=>"",
                    "default"=>$this->info['usuraPSx']>0?$this->info['dimePSx']:"",
                    "placeholder"=>"misura",
                    "disabled"=>false
                ),
                "css"=>array(
                    "width"=>"100%;",
                    "text-align"=>"center;",
                    "font-size"=>"1.1em;"
                )
            ),
            "misuraPDX"=>array(
                "prop"=>array(
                    "input"=>"input",
                    "tipo"=>"text",
                    "maxlenght"=>"",
                    "options"=>array(),
                    "rows"=>"",
                    "default"=>$this->info['usuraPDx']>0?$this->info['dimePDx']:"",
                    "placeholder"=>"misura",
                    "disabled"=>false
                ),
                "css"=>array(
                    "width"=>"100%;",
                    "text-align"=>"center;",
                    "font-size"=>"1.1em;"
                )
            ),
            "marcaASX"=>array(
                "prop"=>array(
                    "input"=>"input",
                    "tipo"=>"text",
                    "maxlenght"=>"",
                    "options"=>array(),
                    "rows"=>"",
                    "default"=>$this->info['usuraASx']>0?$this->info['marcaASx']:"",
                    "placeholder"=>"marca",
                    "disabled"=>false
                ),
                "css"=>array(
                    "width"=>"100%;",
                    "text-align"=>"center;",
                    "font-size"=>"1em;"
                )
            ),
            "marcaADX"=>array(
                "prop"=>array(
                    "input"=>"input",
                    "tipo"=>"text",
                    "maxlenght"=>"",
                    "options"=>array(),
                    "rows"=>"",
                    "default"=>$this->info['usuraADx']>0?$this->info['marcaADx']:"",
                    "placeholder"=>"marca",
                    "disabled"=>false
                ),
                "css"=>array(
                    "width"=>"100%;",
                    "text-align"=>"center;",
                    "font-size"=>"1em;"
                )
            ),
            "marcaPSX"=>array(
                "prop"=>array(
                    "input"=>"input",
                    "tipo"=>"text",
                    "maxlenght"=>"",
                    "options"=>array(),
                    "rows"=>"",
                    "default"=>$this->info['usuraPSx']>0?$this->info['marcaPSx']:"",
                    "placeholder"=>"marca",
                    "disabled"=>false
                ),
                "css"=>array(
                    "width"=>"100%;",
                    "text-align"=>"center;",
                    "font-size"=>"1em;"
                )
            ),
            "marcaPDX"=>array(
                "prop"=>array(
                    "input"=>"input",
                    "tipo"=>"text",
                    "maxlenght"=>"",
                    "options"=>array(),
                    "rows"=>"",
                    "default"=>$this->info['usuraPDx']>0?$this->info['marcaPDx']:"",
                    "placeholder"=>"marca",
                    "disabled"=>false
                ),
                "css"=>array(
                    "width"=>"100%;",
                    "text-align"=>"center;",
                    "font-size"=>"1em;"
                )
            ),
            "dotASX"=>array(
                "prop"=>array(
                    "input"=>"input",
                    "tipo"=>"text",
                    "maxlenght"=>"",
                    "options"=>array(),
                    "rows"=>"",
                    "default"=>$this->info['usuraASx']>0?$this->info['dotASx']:"",
                    "placeholder"=>"DOT",
                    "disabled"=>false
                ),
                "css"=>array(
                    "width"=>"100%;",
                    "text-align"=>"center;",
                    "font-size"=>"1.1em;"
                )
            ),
            "dotADX"=>array(
                "prop"=>array(
                    "input"=>"input",
                    "tipo"=>"text",
                    "maxlenght"=>"",
                    "options"=>array(),
                    "rows"=>"",
                    "default"=>$this->info['usuraADx']>0?$this->info['dotADx']:"",
                    "placeholder"=>"DOT",
                    "disabled"=>false
                ),
                "css"=>array(
                    "width"=>"100%;",
                    "text-align"=>"center;",
                    "font-size"=>"1.1em;"
                )
            ),
            "dotPSX"=>array(
                "prop"=>array(
                    "input"=>"input",
                    "tipo"=>"text",
                    "maxlenght"=>"",
                    "options"=>array(),
                    "rows"=>"",
                    "default"=>$this->info['usuraPSx']>0?$this->info['dotPSx']:"",
                    "placeholder"=>"DOT",
                    "disabled"=>false
                ),
                "css"=>array(
                    "width"=>"100%;",
                    "text-align"=>"center;",
                    "font-size"=>"1.1em;"
                )
            ),
            "dotPDX"=>array(
                "prop"=>array(
                    "input"=>"input",
                    "tipo"=>"text",
                    "maxlenght"=>"",
                    "options"=>array(),
                    "rows"=>"",
                    "default"=>$this->info['usuraPDx']>0?$this->info['dotPDx']:"",
                    "placeholder"=>"DOT",
                    "disabled"=>false
                ),
                "css"=>array(
                    "width"=>"100%;",
                    "text-align"=>"center;",
                    "font-size"=>"1.1em;"
                )
            ),
            "usuraASX"=>array(
                "prop"=>array(
                    "input"=>"select",
                    "tipo"=>"",
                    "maxlenght"=>"",
                    "options"=>$opt,
                    "rows"=>"",
                    "default"=>($usura)?$usura:($this->info['usuraASx']>0?$this->info['usuraASx']:''),
                    "placeholder"=>"",
                    "disabled"=>false
                ),
                "css"=>array(
                    "width"=>"100%;",
                    "text-align"=>"center;",
                    "font-size"=>"1.2em;"
                )
            ),
            "usuraADX"=>array(
                "prop"=>array(
                    "input"=>"select",
                    "tipo"=>"",
                    "maxlenght"=>"",
                    "options"=>$opt,
                    "rows"=>"",
                    "default"=>($usura)?$usura:($this->info['usuraADx']>0?$this->info['usuraADx']:''),
                    "placeholder"=>"",
                    "disabled"=>false
                ),
                "css"=>array(
                    "width"=>"100%;",
                    "text-align"=>"center;",
                    "font-size"=>"1.2em;"
                )
            ),
            "usuraPSX"=>array(
                "prop"=>array(
                    "input"=>"select",
                    "tipo"=>"",
                    "maxlenght"=>"",
                    "options"=>$opt,
                    "rows"=>"",
                    "default"=>($usura)?$usura:($this->info['usuraPSx']>0?$this->info['usuraPSx']:''),
                    "placeholder"=>"",
                    "disabled"=>false
                ),
                "css"=>array(
                    "width"=>"100%;",
                    "text-align"=>"center;",
                    "font-size"=>"1.2em;"
                )
            ),
            "usuraPDX"=>array(
                "prop"=>array(
                    "input"=>"select",
                    "tipo"=>"",
                    "maxlenght"=>"",
                    "options"=>$opt,
                    "rows"=>"",
                    "default"=>($usura)?$usura:($this->info['usuraPDx']>0?$this->info['usuraPDx']:''),
                    "placeholder"=>"",
                    "disabled"=>false
                ),
                "css"=>array(
                    "width"=>"100%;",
                    "text-align"=>"center;",
                    "font-size"=>"1.2em;"
                )
            ),
            "annotazioni"=>array(),
            "operazione"=>array(),
            "destinazione"=>array(),
            "origine"=>array()
        );

        if ($op==0) {
            $mappa['compoGomme']=array(
                "prop"=>array(
                    "input"=>"select",
                    "tipo"=>"",
                    "maxlenght"=>"",
                    "options"=>$compoOpt,
                    "rows"=>"",
                    "default"=>$this->info['compoGomme'],
                    "placeholder"=>"",
                    "disabled"=>false
                ),
                "css"=>array(
                    "width"=>"90%;",
                    "text-align"=>"left;",
                    "font-size"=>"1.2em;"
                )
            );
            $mappa['tipoGomme']=array(
                "prop"=>array(
                    "input"=>"select",
                    "tipo"=>"",
                    "maxlenght"=>"",
                    "options"=>$tipoOpt,
                    "rows"=>"",
                    "default"=>$this->info['tipoGomme'],
                    "placeholder"=>"",
                    "disabled"=>false
                ),
                "css"=>array(
                    "width"=>"90%;",
                    "text-align"=>"left;",
                    "font-size"=>"1.2em;"
                )
            );
        }

        $form->add_fields($fields);
        $form->load_tipi($tipi);
        $form->load_expo($export);
        $form->load_conv($conv);
        $form->load_mappa($mappa);

        $this->drawAnnotazioni(true,$op);

        if ($op==0) {

            echo '<div style="width:100%;height:30px;">';

                echo '<div style="position:relative;display:inline-block;width:45%;vertical-align:top;" >';
                    echo '<div style="position:relative;display:inline-block;width:40%;vertical-align:bottom;font-weight:bold;" >';
                        echo '<span style="margin-left:20px;" >Tipo gomme:</span>';
                    echo '</div>';
                    echo '<div id="js_chk_'.'gdmForm_'.$this->info['id'].'_elem_tipoGomme" class="chekko_elem" style="text-align:center;padding:2px;box-sizing:border-box;margin-top:5px;position:relative;display:inline-block;width:60%;vertical-align:top;" >';
                        echo '<select id="'.'gdmForm_'.$this->info['id'].'_tipoGomme" style="';
                            foreach ($mappa['tipoGomme']['css'] as $k=>$c) {
                                echo $k.':'.$c;
                            }
                        echo '" class="js_chk_'.'gdmForm_'.$this->info['id'].'" js_chk_'.'gdmForm_'.$this->info['id'].'_tipo="tipoGomme" onchange="window._js_chk_'.'gdmForm_'.$this->info['id'].'.js_chk();">';

                            echo '<option value=""></option>';

                            foreach ($mappa['tipoGomme']['prop']['options'] as $v=>$t) {
                                echo '<option value="'.$v.'"' ;
                                    if ($v==$mappa['tipoGomme']['prop']['default']) echo ' selected';
                                echo ' >'.$t.'</option>';
                            }
                        echo '</select>';
                    echo '</div>';
                echo '</div>';

                echo '<div style="position:relative;display:inline-block;width:45%;vertical-align:top;" >';
                    echo '<div style="position:relative;display:inline-block;width:40%;vertical-align:bottom;font-weight:bold;" >';
                        echo '<span style="margin-left:20px;" >Composizione:</span>';
                    echo '</div>';
                    echo '<div id="js_chk_'.'gdmForm_'.$this->info['id'].'_elem_compoGomme" class="chekko_elem" style="text-align:center;padding:2px;box-sizing:border-box;margin-top:5px;position:relative;display:inline-block;width:60%;vertical-align:top;" >';
                        echo '<select id="'.'gdmForm_'.$this->info['id'].'_compoGomme" style="';
                            foreach ($mappa['compoGomme']['css'] as $k=>$c) {
                                echo $k.':'.$c;
                            }
                        echo '" class="js_chk_'.'gdmForm_'.$this->info['id'].'" js_chk_'.'gdmForm_'.$this->info['id'].'_tipo="compoGomme" onchange="window._js_chk_'.'gdmForm_'.$this->info['id'].'.js_chk();">';

                            echo '<option value=""></option>';

                            foreach ($mappa['compoGomme']['prop']['options'] as $v=>$t) {
                                echo '<option value="'.$v.'"' ;
                                    if ($v==$mappa['compoGomme']['prop']['default']) echo ' selected';
                                echo ' >'.$t.'</option>';
                            }
                        echo '</select>';
                    echo '</div>';
                echo '</div>';

            echo '</div>';
        }

        echo '<div style="position:relative;width:100%;height:400px;background-image:url(\'http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/gdm/img/chassis.png\');background-repeat: no-repeat;background-size: 100% 100%;">';

            $form->draw();

        echo '</div>';

    }

    function drawFormGenerico($op) {
        
    }


    //###########################################################################################

    function insert($veicolo) {

        $mat=$this->info;
        unset($mat['id']);

        $arg=array(
            "materiale"=>$mat,
            "veicolo"=>$veicolo
        );

        $this->galileo->setTransaction(true);

        $this->galileo->executeGeneric('gdm','nuovoMateriale',$arg,'');

    }

    function printMateriale($pdf,$fontName) {
        //PNEUMATICI

        $pdf->Write(10,'('.$this->info['id'].') '.$this->info['compoGomme'].' '.$this->info['tipoGomme']);
        $pdf->Ln(5);
        $pdf->Write(10,$this->info['annotazioni']);
        $pdf->Ln(5);
        $pdf->SetFont($fontName,'',9);

        if ($this->info['proprietario']=='Smaltimento') {

            $txt='ASx: ';
            if ($this->info['usuraASx']==1) $txt.=substr($this->info['marcaASx'],0,8).' '.$this->info['dimeASx'].' Dot:'.$this->info['dotASx'];
            $pdf->Cell(90,10,$txt,0,0,'L');

            $txt='ADx: ';
            if ($this->info['usuraADx']==1) $txt.=substr($this->info['marcaADx'],0,8).' '.$this->info['dimeADx'].' Dot:'.$this->info['dotADx'];
            $pdf->Cell(90,10,$txt,0,0,'L');

            $pdf->Ln(5);

            $txt='PSx: ';
            if ($this->info['usuraPSx']==1) $txt.=substr($this->info['marcaPSx'],0,8).' '.$this->info['dimePSx'].' Dot:'.$this->info['dotPSx'];
            $pdf->Cell(90,10,$txt,0,0,'L');

            $txt='PDx: ';
            if ($this->info['usuraPDx']==1) $txt.=substr($this->info['marcaPDx'],0,8).' '.$this->info['dimePDx'].' Dot:'.$this->info['dotPDx'];
            $pdf->Cell(90,10,$txt,0,0,'L');
        }

        else {
            $txt='ASx: '.($this->info['usuraASx']==-1?'Smaltito':($this->info['usuraASx']==-2?'Solo cerchio':substr($this->info['marcaASx'],0,8).' '.$this->info['dimeASx'].' Dot:'.$this->info['dotASx'].' '.$this->info['usuraASx'].' mm'));
            $pdf->Cell(90,10,$txt,0,0,'L');

            $txt='ADx: '.($this->info['usuraADx']==-1?'Smaltito':($this->info['usuraADx']==-2?'Solo cerchio':substr($this->info['marcaADx'],0,8).' '.$this->info['dimeADx'].' Dot:'.$this->info['dotADx'].' '.$this->info['usuraADx'].' mm'));
            $pdf->Cell(90,10,$txt,0,0,'L');

            $pdf->Ln(5);

            $txt='PSx: '.($this->info['usuraPSx']==-1?'Smaltito':($this->info['usuraPSx']==-2?'Solo cerchio':substr($this->info['marcaPSx'],0,8).' '.$this->info['dimePSx'].' Dot:'.$this->info['dotPSx'].' '.$this->info['usuraPSx'].' mm'));
            $pdf->Cell(90,10,$txt,0,0,'L');

            $txt='PDx: '.($this->info['usuraPDx']==-1?'Smaltito':($this->info['usuraPDx']==-2?'Solo cerchio':substr($this->info['marcaPDx'],0,8).' '.$this->info['dimePDx'].' Dot:'.$this->info['dotPDx'].' '.$this->info['usuraPDx'].' mm'));
            $pdf->Cell(90,10,$txt,0,0,'L');
        }

    }
}
?>