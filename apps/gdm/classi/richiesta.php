<?php
include_once(DROOT."/nebula/core/imgselect/imageselect.php");
include_once(DROOT."/nebula/apps/gdm/classi/form_pneumatici_info.php");
include_once(DROOT."/nebula/apps/gdm/classi/materiale.php");

class gdmRichiesta {

    protected $ambito="";

    protected $defaultDMS="infinity";

    protected $info=array(
        "id"=>"",
        "statoRi"=>"",
        "dataRi"=>"",
        "idTelaio"=>"",
        "nomeCliente"=>"",
        "targa"=>"",
        "tipoVeicolo"=>"",
        "numPratica"=>"",
        "dms"=>"",
        "flag_stop"=>false,
        "attivo"=>false
    );

    protected $pps=false;

    protected $operazioni=array();

    protected $materiali=array();

    protected $immagini=array();

    protected $galileo;

    function __construct($ambito,$info,$galileo) {

        $this->ambito=$ambito;
        $this->galileo=$galileo;

        $this->immagini=array(
            "Deposito"=>"http://".$_SERVER['SERVER_ADDR']."/nebula/apps/gdm/img/deposito.png",
            "Vettura"=>"http://".$_SERVER['SERVER_ADDR']."/nebula/apps/gdm/img/veicolo.png",
            "Cliente"=>"http://".$_SERVER['SERVER_ADDR']."/nebula/apps/gdm/img/cliente.png",
            "Smaltimento"=>"http://".$_SERVER['SERVER_ADDR']."/nebula/apps/gdm/img/trash.png",
        );

        foreach ($this->info as $k=>$o) {
            if (array_key_exists($k,$info)) $this->info[$k]=$info[$k];
        }

        if ($this->info['id'] && $this->info['id']!='') {

            $this->galileo->clearQuery();
            $this->galileo->clearQueryOggetto('default','gdm');

            $this->galileo->executeGeneric('gdm','getOperazioniRichiesta',array('idRi'=>$this->info['id']),'');

            $result=$this->galileo->getResult();
            //$result=false;

            if ($result) {
                $fid=$this->galileo->preFetch('gdm');

                while($row=$this->galileo->getFetch('gdm',$fid)) {
                    $this->operazioni[$row['id']]=$row;

                    if ($row['statoOp']=='Richiesto' && $this->ambito=='gdmr') $this->info['flag_stop']=true;
                    if ($row['isAnnullato']=='Fake' && $row['statoOp']=='Richiesto') $this->info['flag_stop']=true;
                    if ($this->ambito=='gdmr' && ($row['statoOp']=='Pronto' || $row['statoOp']=='Installato')) $this->info['attivo']=true;

                    if ($row['statoOp']=='Pronto per Stoccaggio') $this->pps=true;
                }
            }

            //##########################
            //echo '<div>'.json_encode($this->info).'</div>';
            //return;
            //##########################

            foreach ($this->operazioni as $idop=>$o) {

                $this->galileo->clearQuery();
                $this->galileo->clearQueryOggetto('default','gdm');

                $this->galileo->executeSelect("gdm","GDM_materiali","id='".$o['idMat']."'","");
                $result=$this->galileo->getResult();
        
                if ($result) {
                    $fid=$this->galileo->preFetch('gdm');
        
                    while($row=$this->galileo->getFetch('gdm',$fid)) {
                        try {
                            $this->materiali[$row['id']]=new gdmMateriale($row,$this->galileo);
                        } catch (Exception $e) {
                            // Gestisci l'eccezione catturata
                            die('Si è verificato un errore: ' . $e->getMessage());
                        }
                    }
                }
            }
        }

        if ($ambito=='prelievo' || $ambito=='stoccaggio') $this->info['attivo']=true;
    }

    function getPps() {
        return $this->pps;
    }

    function setMateriali($id,$o) {

        if (array_key_exists($id,$this->materiali)) {
            $this->materiali[$id]->setInfo($o);
        }
    }

    function drawHead($flag) {

        echo '<div style="position:relative;width:100%;">';
            //echo '<div style="position:relative;;display:inline-block;width:40%;vertical-align:top;" >'.($flag?date('d/m/Y'):mainFunc::gab_todata($this->info['dataRi'])).' ('.$this->info['id'].')</div>';
            echo '<div style="position:relative;;display:inline-block;width:40%;vertical-align:top;" >'.(mainFunc::gab_todata($this->info['dataRi'])).' ('.$this->info['id'].')</div>';
            echo '<div style="position:relative;display:inline-block;width:20%;vertical-align:top;" >'.$this->info['targa'].'</div>';
            echo '<div style="position:relative;display:inline-block;width:40%;vertical-align:top;font-size:0.9em;" >'.$this->info['idTelaio'].'</div>';
        echo '</div>';

        //echo '<div style="position:relative;width:100%;font-size:0.9em;" >'.$this->info['tipoVeicolo'].'</div>';

        echo '<div style="position:relative;width:100%;font-weight:bold;" >'.$this->info['nomeCliente'].'</div>';

        if ($flag) {
            echo '<input id="gdm_actual_richiesta_id" type="hidden" value="'.$this->info['id'].'" />';
            echo '<input id="gdm_actual_richiesta_ambito" type="hidden" value="'.$this->ambito.'" />';
        }
    }

    function draw($flag) {

        echo '<div style="position:relative;width:100%;padding:3px;box-sizing:border-box;" >';

            $this->drawHead($flag);

            echo '<div style="position:relative;width:100%;">';

                foreach ($this->operazioni as $k=>$o) {

                    echo '<div style="position:relative;font-size:0.9em;height:20px;padding:2px;width:100%;box-sizing:border-box;margin-top:2px;">';

                        echo '<div style="position:relative;display:inline-block;width:12%;vertical-align:top;font-size:0.9em;" >('.$o['idMat'].')</div>';
                        echo '<div style="position:relative;display:inline-block;width:18%;vertical-align:top;" >'.($o['tipologia']=='Pneumatici'?$o['compoGomme']:$o['nome']).'</div>';
                        echo '<div style="position:relative;display:inline-block;width:27%;vertical-align:top;" >'.($o['tipologia']=='Pneumatici'?$o['tipoGomme']:$o['descrizione']).'</div>';
                        echo '<div style="position:relative;display:inline-block;width:16%;vertical-align:top;" >'.$o['origine'].'</div>';
                        echo '<div style="position:relative;display:inline-block;width:6%;vertical-align:top;text-align:center;height:100%;" >';
                            echo '<img style="position:relative;width:10px;height:6px;top:55%;transform:translate(0px,-50%);" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/main//img/blackarrowR.png" />';
                        echo '</div>';
                        echo '<div style="position:relative;display:inline-block;width:20%;vertical-align:top;" >'.substr($o['destinazione'],0,8).'</div>';

                    echo '</div>';
                }

            echo '</div>';

        echo '</div>';
    }

    function drawForm() {

        imageSelect::imageSelectInit();

        echo '<div style="position:relative;width:100%;height:15%;border-bottom: 1px solid black;" >';

            $this->drawHead(true);

            echo '<div style="position:relative;width:100%;margin-top:10px;text-align:center;" >';
                if ($this->info['flag_stop']) {
                    echo '<div style="width:100%;color:red;text-align:center;font-weight:bold;" >Ci sono Operazioni non confermabili.</div>';
                }
                elseif(!$this->info['attivo']){
                    echo '<button style="width:50%;" onclick="window._nebulaGdm.stampaRichiesta(\''.$this->info['id'].'\')" >Stampa Operazioni</button>';
                }
                else {
                    echo '<button style="width:50%;background-color:burlywood;" onclick="window._nebulaGdm.chiudiRichiestaActual(\''.$this->ambito.'\');" >Conferma Operazioni</button>';
                }

                echo '<img style="position:absolute;right:0px;top:0px;width:20px;height:20px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/gdm/img/refresh.png" onclick="window._nebulaGdm.refreshRichiestaButton();" />';

                //if (isset($this->info['dms']) && $this->info['dms']!="") {
                    echo '<img style="position:absolute;left:0px;top:0px;width:20px;height:20px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/gdm/img/cerca.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].setVeicoloRichiesta(\''.$this->info['idTelaio'].'\',\''.($this->info['dms']!=""?$this->info['dms']:$this->defaultDMS).'\');">';
                //}
            echo '</div>';
            
            if ($this->info['attivo']){
                echo '<script type="text/javascript" >';
                    echo 'window._ckmf=new chekkoMultiForm(\''.$this->info['id'].'\');';
                echo '</script>';
            }

        echo '</div>';

        echo '<div style="position:relative;width:100%;height:85%;overflow:scroll;overflow-x:hidden;" >';

            foreach ($this->operazioni as $k=>$o) {

                if ($o['statoOp']=='Completa' && $this->ambito!='gdmr') continue;
                if ($this->ambito=='prelievo' && $o['statoOp']!='Richiesto') continue;
                if ($this->ambito=='stoccaggio' && $o['statoOp']!='Pronto per Stoccaggio') continue;
                //if ($this->ambito=='gdmr' && $o['statoOp']=='Pronto per Stoccaggio') continue;

                echo '<div style="position:relative;width:95%;margin-bottom:20px;border-bottom:1px solid black;" >';

                    $this->drawHeadOperazione($o);

                    if (array_key_exists($o['idMat'],$this->materiali)) {

                        //se siamo in gestione e l'origine non è il deposito scrivi il form oppure siamo in "prelievo" o "stoccaggio"
                        if ( ($this->ambito=='gdmr' && $o['origine']!='Deposito') && $this->info['attivo']) {

                            $this->materiali[$o['idMat']]->drawForm('0.0',$o['id']);
                        }
                        //se siamo in gestione e l'origine è il deposito scrivi la descrizione dell'articolo
                        //else if ( ($this->ambito=='gdmr' && $o['origine']=='Deposito') || $this->ambito=='prelievo' || $this->ambito=='stoccaggio') {
                        else if ($this->ambito=='gdmr' || $this->ambito=='prelievo' || $this->ambito=='stoccaggio') {

                            echo '<div style="position:relative;margin-top:10px;margin-bottom:10px;">';

                                if(!$this->info['attivo']){
                                    $this->materiali[$o['idMat']]->drawInfo(false,false,$o['id']);
                                }

                                else {

                                    if ($this->ambito=='stoccaggio') {

                                        echo '<div style="position:relative;margin-top:10px;margin-bottom:10px;height:30px;" >';
                                            echo '<div style="position:relative;display:inline-block;vertical-align:bottom;width:100px;text-align:left;font-weight:bold;" >Locazione:</div>';
                                            //echo '<div id="js_chk_gdmForm_'.$o['idMat'].'_elem_locazione" class="chekko_elem" style="position:relative;display:inline-block;vertical-align:top;width:150px;text-align:left;font-weight:bold;border-color: transparent;padding: 2px; box-sizing: border-box;text-align:center;">';
                                            echo '<div style="position:relative;display:inline-block;vertical-align:top;width:150px;text-align:left;font-weight:bold;border-color: transparent;padding: 2px; box-sizing: border-box;text-align:center;">';
                                                echo '<input id="gdmForm_'.$o['idMat'].'_locazione" type="text" style="width:90%;text-align:center;"  value="" class="js_chk_gdmForm_'.$o['idMat'].'" js_chk_gdmForm_'.$o['idMat'].'_tipo="locazione" />';
                                            echo '</div>';
                                        echo '</div>';
                                    }

                                    //if ($this->ambito=='prelievo') {
                                        //$this->materiali[$o['idMat']]->drawAnnotazioni('gdmForm_'.$o['idMat'],$o['id']);
                                    //}
                                    /*else {
                                        echo '<input id="gdmForm_'.$o['idMat'].'_operazione" type="hidden" value="'.$o['id'].'" class="js_chk_gdmForm_'.$o['idMat'].'" js_chk_gdmForm_'.$o['idMat'].'_tipo="operazione"/>';
                                        echo '<input id="gdmForm_'.$o['idMat'].'_destinazione" type="hidden" value="'.$o['destinazione'].'" class="js_chk_gdmForm_'.$o['idMat'].'" js_chk_gdmForm_'.$o['idMat'].'_tipo="destinazione"/>';
                                        echo '<input id="gdmForm_'.$o['idMat'].'_id" type="hidden" value="'.$o['idMat'].'" class="js_chk_gdmForm_'.$o['idMat'].'" js_chk_gdmForm_'.$o['idMat'].'_tipo="id"/>';
                                    }*/

                                    //$this->materiali[$o['idMat']]->drawInfo($this->ambito=='prelievo'?true:false,false,$o['id']);
                                    $this->materiali[$o['idMat']]->drawInfo(false,true,$o['id']);

                                    $form=new gdmPneuFormInfo('gdmForm_'.$o['idMat'],$o['id']);

                                    $fields=array(
                                        "id"=>array(
                                            "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
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

                                    //if ($this->ambito=='prelievo') {
                                        $fields['annotazioni']=array(
                                            "js_chk_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
                                            "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
                                        );
                                    //}

                                    if ($this->ambito=='stoccaggio') {
                                        $fields['locazione']=array(
                                            "js_chk_req"=>array("codice"=>3,"anor"=>"","anand"=>"","anxor"=>""),
                                            "js_chk_ifreq"=>array("campo"=>"destinazione","op"=>"==","val"=>"Deposito")
                                        );
                                    }

                                    $tipi=array(
                                        "id"=>"none",
                                        "operazione"=>"none",
                                        "destinazione"=>"none",
                                        "origine"=>"none"
                                    );

                                    //if ($this->ambito=='prelievo') {
                                        $tipi['annotazioni']="text";
                                    //}

                                    if ($this->ambito=='stoccaggio') {
                                        $tipi['locazione']="text";
                                    }

                                    $export=array(
                                        "id"=>"",
                                        "operazione"=>"",
                                        "destinazione"=>"",
                                        "origine"=>""
                                    );

                                    //if ($this->ambito=='prelievo') {
                                        $export['annotazioni']="";
                                    //}

                                    if ($this->ambito=='stoccaggio') {
                                        $export['locazione']="";
                                    }

                                    $conv=array(
                                        "id"=>"id",
                                        "operazione"=>"operazione",
                                        "destinazione"=>"destinazione",
                                        "origine"=>"origine"
                                    );

                                    //if ($this->ambito=='prelievo') {
                                        $conv['annotazioni']="annotazioni";
                                    //}

                                    if ($this->ambito=='stoccaggio') {
                                        $conv['locazione']="locazione";
                                    }

                                    $mappa=array(
                                        "id"=>array(),
                                        "operazione"=>array(),
                                        "destinazione"=>array(),
                                        "origine"=>array()
                                    );

                                    //if ($this->ambito=='prelievo') {
                                        $mappa['annotazioni']=array();
                                    //}

                                    if ($this->ambito=='stoccaggio') {
                                        $mappa["locazione"]=array();
                                    }

                                    $form->add_fields($fields);
                                    $form->load_tipi($tipi);
                                    $form->load_expo($export);
                                    $form->load_conv($conv);
                                    $form->load_mappa($mappa);

                                    $form->draw();
                                }

                            echo '</div>';
                        }
                    }
                    else {
                        echo '<div style="font-size:1.1em;font-weight:bold;" >Materiale inesistente</div>';
                    }
                
                echo '</div>';
            }

         echo '</div>';

    }

    function drawHeadOperazione($o) {

        echo '<div style="position:relative;width:100%;margin-top:5px;height:50px;">';

            echo '<div style="position:relative;display:inline-block;width:50%;vertical-align:top;border: 2px solid #f7b872;padding: 3px;box-sizing: border-box;border-radius: 10px;';
                if ($o['statoOp']=='Richiesto' || $o['isAnnullato']=='Fake') echo 'background-color:#ffadad;';
            echo '" >';

                echo '<div style="position:relative;width:100%;">';
                    echo '<div style="position:relative;display:inline-block;width:20%;vertical-align:top;" >('.$o['idMat'].')</div>';
                    echo '<div style="position:relative;display:inline-block;width:38%;vertical-align:top;" >'.($o['tipologia']=='Pneumatici'?$o['compoGomme']:$o['nome']).'</div>';
                    echo '<div style="position:relative;display:inline-block;width:42%;vertical-align:top;" >'.($o['tipologia']=='Pneumatici'?$o['tipoGomme']:$o['descrizione']).'</div>';
                echo '</div>';

                echo '<div style="position:relative;width:100%;">';
                    echo '<div style="position:relative;display:inline-block;width:20%;vertical-align:top;font-size:0.9em;" >'.$o['id'].'</div>';
                    echo '<div style="position:relative;display:inline-block;width:80%;vertical-align:top;font-weight:bold;" >';
                        echo $o['statoOp'];
                        if ($o['statoOp']=='Richiesto') echo ' - '.$o['locazione'];
                        if ($o['statoOp']=='Pronto per Stoccaggio') {
                            $temp=array(
                                "targa"=>$this->info['targa'],
                                "nomeCliente"=>$this->info['nomeCliente']
                            );
                            echo '<img style="width:15px;height:15px;margin-left:10px;" data-info="'.base64_encode(json_encode($temp)).'" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/gdm/img/print.png" onclick="window._nebulaGdm.stampaEtichettaRichiesta(\''.$o['idMat'].'\',this);"/>';
                        }
                    echo '</div>';
                echo '</div>';

            echo '</div>';

            echo '<div style="position:relative;display:inline-block;width:50%;vertical-align:top;" >';

                echo '<div style="position:relative;display:inline-block;width:35%;vertical-align:top;text-align:center;height:95%;" >';

                    $img=isset($this->immagini[$o['origine']])?$this->immagini[$o['origine']]:"";
                    echo '<img style="position:relative;width:35px;height:35px;top:50%;transform:translate(0px,-50%);" src="'.$img.'" />';

                echo '</div>';

                echo '<div style="position:relative;display:inline-block;width:15%;vertical-align:top;text-align:center;height:95%;" >';
                    echo '<img style="position:relative;width:30px;height:20px;top:50%;transform:translate(0px,-50%);" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/main//img/blackarrowR.png" />';
                echo '</div>';

                echo '<div style="position:relative;display:inline-block;width:50%;vertical-align:top;text-align:center;height:95%;" >';
                    
                    if($this->info['attivo']) {

                        $css=array(
                            "select"=>array(
                                "width"=>"50%;",
                                "position"=>"relative;",
                                "left"=>"50%;",
                                "transform"=>"translate(-50%,0px);",
                                "text-align"=>"center;"
                            ),
                            "optionImg"=>array(
                                "width"=>"30px;",
                                "height"=>"30px;",
                                "position"=>"relative;",
                                "top"=>"50%;",
                                "left"=>"50%;",
                                "transform"=>"translate(-60%,-50%);"
                            ),
                            "list"=>array(
                                "background-color"=>"#575757;",
                            ),
                            "buttonList"=>array(
                                "height"=>"40px;",
                                "background-color"=>"gold;"
                            )
                        );

                        $map=array();

                        if ($o['origine']=='Vettura') {
                            $map[]=array(
                                "value"=>"Deposito",
                                "img"=>isset($this->immagini['Deposito'])?$this->immagini['Deposito']:"",
                                "txt"=>"",
                                "selected"=>($o['destinazione']=='Deposito'?true:false)
                            );
                            $map[]=array(
                                "value"=>"Cliente",
                                "img"=>isset($this->immagini['Cliente'])?$this->immagini['Cliente']:"",
                                "txt"=>"",
                                "selected"=>($o['destinazione']=='Cliente'?true:false)
                            );
                            $map[]=array(
                                "value"=>"Smaltimento",
                                "img"=>isset($this->immagini['Smaltimento'])?$this->immagini['Smaltimento']:"",
                                "txt"=>"",
                                "selected"=>($o['destinazione']=='Smaltimento'?true:false)
                            );
                        }

                        elseif ($o['origine']=='Deposito') {

                            if ($this->materiali[$o['idMat']]->getFull()) {
                                $map[]=array(
                                    "value"=>"Vettura",
                                    "img"=>isset($this->immagini['Vettura'])?$this->immagini['Vettura']:"",
                                    "txt"=>"",
                                    "selected"=>($o['destinazione']=='Vettura'?true:false)
                                );
                            }
                            $map[]=array(
                                "value"=>"Cliente",
                                "img"=>isset($this->immagini['Cliente'])?$this->immagini['Cliente']:"",
                                "txt"=>"",
                                "selected"=>($o['destinazione']=='Cliente'?true:false)
                            );
                            $map[]=array(
                                "value"=>"Smaltimento",
                                "img"=>isset($this->immagini['Smaltimento'])?$this->immagini['Smaltimento']:"",
                                "txt"=>"",
                                "selected"=>($o['destinazione']=='Smaltimento'?true:false)
                            );
                        }

                        elseif ($o['origine']=='Cliente') {
                            $map[]=array(
                                "value"=>"Deposito",
                                "img"=>isset($this->immagini['Deposito'])?$this->immagini['Deposito']:"",
                                "txt"=>"",
                                "selected"=>($o['destinazione']=='Deposito'?true:false)
                            );
                            if ($this->materiali[$o['idMat']]->getFull()) {
                                $map[]=array(
                                    "value"=>"Vettura",
                                    "img"=>isset($this->immagini['Vettura'])?$this->immagini['Vettura']:"",
                                    "txt"=>"",
                                    "selected"=>($o['destinazione']=='Vettura'?true:false)
                                );
                            }
                            $map[]=array(
                                "value"=>"Smaltimento",
                                "img"=>isset($this->immagini['Smaltimento'])?$this->immagini['Smaltimento']:"",
                                "txt"=>"",
                                "selected"=>($o['destinazione']=='Smaltimento'?true:false)
                            );
                        }

                        $sel=new imageSelect('op_'.$o['id'],$css,$map);
                        $sel->draw();
                    }

                    else {
                        $img=isset($this->immagini[$o['destinazione']])?$this->immagini[$o['destinazione']]:"";
                        echo '<img style="position:relative;width:35px;height:35px;top:50%;transform:translate(0px,-50%);" src="'.$img.'" />';
                    }
                echo '</div>';

            echo '</div>';

        echo '</div>';
    }

    function printStorico($pdf,$fontName) {

        $pdf->SetFont($fontName,'B',15);

        $pdf->Write(10,'RICHIESTA MOVIMENTAZIONE MATERIALE DEL CLIENTE');

        $pdf->Ln(10);

        $pdf->SetFont($fontName,'',12);

        $pdf->Write(10,'Data: '.mainFunc::gab_todata($this->info['dataRi']));

        $pdf->Ln(10);

        $pdf->Write(10,'Cliente: '.$this->info['nomeCliente']);

        $pdf->Ln(10);

        $pdf->Write(10,'Veicolo: '.$this->info['targa'].' - '.$this->info['idTelaio']);

        //$pdf->Cell(128,10,(substr((isset($param['cliente'])?$param['cliente']:''),0,14)),0,1,'C');

        $edit=false;

        foreach ($this->operazioni as $k=>$o) {

            $tempop=json_decode($o['storico'],true);
            if ($tempop) {
                $this->setMateriali($o['idMat'],$tempop);
            }

            $pdf->Ln(10);

            if ($o['origine']=='EDIT' || $o['origine']=='NUOVA') {
                $pdf->SetFont($fontName,'B',10);
                $pdf->Write(10,"Modifica del materiale:");
                $pdf->Ln(8);
                $this->materiali[$o['idMat']]->printMateriale($pdf,$fontName);
                $edit=true;
            }
            else {
    
                switch ($o['destinazione']) {

                    case 'Deposito':
                        $pdf->SetFont($fontName,'B',10);
                        $pdf->Write(10,"Rimessaggio: (Provenienza: ".$o['origine'].")");
                        $pdf->Ln(5);
                        $pdf->SetFont($fontName,'',10);
                        $pdf->Write(10,"Il cliente lascia in deposito presso di noi il seguente materiale di sua proprietà.");
                        $pdf->Ln(8);
                        $this->materiali[$o['idMat']]->printMateriale($pdf,$fontName);
                        $pdf->Ln(8);
                        $pdf->Write(5,"Il materiale lasciato presso la concessionaria in rimessaggio sarà tenuto dalla stessa per un periodo massimo di 12 mesi. Trascorso tale periodo ed in mancanza di contatti con il proprietario, la concessionaria si riserverà di provvedere al suo opportuno smaltimento.");
                    break;

                    case 'Cliente':
                        $pdf->SetFont($fontName,'B',10);
                        $pdf->Write(10,"Riconsegnato: (Provenienza: ".$o['origine'].")");
                        $pdf->Ln(5);
                        $pdf->SetFont($fontName,'',10);
                        $pdf->Write(10,"Al cliente viene riconsegnato il seguente materiale di sua proprietà.");
                        $pdf->Ln(8);
                        $this->materiali[$o['idMat']]->printMateriale($pdf,$fontName);
                    break;

                    case 'Vettura':
                        $pdf->SetFont($fontName,'B',10);
                        $pdf->Write(10,"Installato sul veicolo: (Provenienza: ".$o['origine'].")");
                        $pdf->SetFont($fontName,'',10);
                        $pdf->Ln(8);
                        $this->materiali[$o['idMat']]->printMateriale($pdf,$fontName);
                        $pdf->Ln(8);
                        $pdf->SetFont($fontName,'',10);
                        $pdf->Write(10,"CONTROLLO TECNICO.");
                        $pdf->Ln(5);
                        $pdf->Write(10,"Il materiale è adatto al veicolo ed in buono stato? -SI- -NO-");
                        $pdf->Ln(8);
                        $pdf->Write(10,"Controllato da: __________________________________Firma:____________________________________");
                    break;

                    case 'Smaltimento':
                        $pdf->SetFont($fontName,'B',10);
                        $pdf->Write(10,"Smaltito: (Provenienza: ".$o['origine'].")");
                        $pdf->Ln(5);
                        $pdf->SetFont($fontName,'',10);
                        $pdf->Write(10,"Il seguente materiale viene smaltito perché non più idoneo.");
                        $pdf->Ln(8);
                        $this->materiali[$o['idMat']]->printMateriale($pdf,$fontName);
                    break;
                }
            }
        }

        if (!$edit) {

            $pdf->Ln(10);

            $pdf->Write(10,'FIRMA DEL CLIENTE PER ACCETTAZIONE:');

            $pdf->Ln(8);

            $pdf->Write(10,'__________________________________________________________');
        }
        
    }

}
?>