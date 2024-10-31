<?php
require_once(DROOT.'/nebula/apps/gdm/classi/materiale.php');

class gdmGestione {

    protected $materiali=array();
    protected $richieste=array();

    protected $flagRichieste=array(
        "delActual"=>false,
        "scambio"=>false
    );

    protected $tempMateriali=array();
    
    protected $galileo;

    function __construct($telaio,$galileo) {

        $this->galileo=$galileo;

        $tempflag=array(
            "Deposito"=>"",
            "Vettura"=>""
        );

        $this->galileo->executeSelect("gdm","GDM_materiali","idTelaio='".$telaio."' AND isnull(isAnnullato,'False')!='True'","dataCreazione DESC");
        $result=$this->galileo->getResult();

        if ($result) {
            $fid=$this->galileo->preFetch('gdm');

            while($row=$this->galileo->getFetch('gdm',$fid)) {
                $this->materiali[$row['proprietario']][$row['id']]=new gdmMateriale($row,$this->galileo);
                $this->tempMateriali[$row['id']]=$row;

                //flag richieste
                if ($tempflag) {
                    if ($row['proprietario']=='Deposito') {
                        if ($tempflag['Deposito']=="" && $row['isBusy']=='False') $tempflag['Deposito']=$row['id'];
                        else $tempflag=false;
                    }
                    elseif ($row['proprietario']=='Vettura') {
                        if ($tempflag['Vettura']=="" && $row['isBusy']=='False') $tempflag['Vettura']=$row['id'];
                        else $tempflag=false;
                    }
                }
            }
        }

        if ($tempflag && ($tempflag['Deposito']=="" || $tempflag['Vettura']=="") ) $tempflag=false;

        if ($tempflag) $this->flagRichieste['scambio']=$tempflag;
      
        foreach ($this->materiali as $prop=>$p) {
            foreach ($p as $idmat=>$m) {
                foreach ($m->getOperazioni() as $idri=>$o) {

                    if (!isset($this->richieste[$o['dataRi'].'_'.$idri])) {
                        $this->richieste[$o['dataRi'].'_'.$idri]=$o;
                        $this->richieste[$o['dataRi'].'_'.$idri]['operazioni']=array();
                        $this->richieste[$o['dataRi'].'_'.$idri]['pss']=false;
                        $this->richieste[$o['dataRi'].'_'.$idri]['loca']=false;
                    }

                    //else {
                        foreach ($o['operazioni'] as $idop=>$op) {
                            $this->richieste[$o['dataRi'].'_'.$idri]['operazioni'][$idop]=$op;

                            //flag richieste
                            if ($o['statoRi']=='APERTA') {
                                if($op['statoOp']!='Completa' && $op['statoOp']!='Pronto per Stoccaggio') {
                                    if (!$this->flagRichieste['delActual'] || $this->flagRichieste['delActual']!='N') $this->flagRichieste['delActual']='S';
                                }
                                else {
                                    $this->flagRichieste['delActual']='N';
                                }

                                if($op['statoOp']=='Pronto per Stoccaggio') $this->richieste[$o['dataRi'].'_'.$idri]['pss']=true;
                            }

                            if($op['origine']=='LOCA') $this->richieste[$o['dataRi'].'_'.$idri]['loca']=true;
                        }
                    //}
                }
            }
        }
    }

    function draw() {

        echo '<div style="position:relative;display:inline-block;width:65%;height:99%;border-right:1px solid black;padding:3px;box-sizing:border-box;overflow:scroll;overflow-x:hidden;vertical-align:top;">';

            echo '<div id="gdm_gestione_main" style="width:95%" >';

                echo '<div style="width:100%;margin-top;15px;font-size:1.2em;border-bottom:2px solid chocolate;font-weight:bold;">';

                    echo '<div style="position:relative;display:inline-block;width:92%;vertical-align:top;">';
                        echo '<img style="width:30px;height:25px;margin-right:25px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/gdm/img/deposito.png" />';
                        echo 'Materiale in deposito:';
                    echo '</div>';

                    echo '<div style="position:relative;display:inline-block;width:7%;text-align:center;vertical-align:top;">';
                        echo '<img style="width:15px;height:15px;margin-top:7px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/gdm/img/add.png" onclick="window._nebulaGdm.creaMateriale(\'Deposito\');" />';
                    echo '</div>';

                echo '</div>';

                echo '<div style="position:relative;" >';

                if (isset($this->materiali['Deposito'])) {
                    foreach ($this->materiali['Deposito'] as $id=>$m) {
                        echo '<div class="gdmMaterialeDiv">';
                            $m->drawInfo(true,false,'');
                        echo '</div>';
                    }
                }

                echo '</div>';

                ///////////////////////////////////////

                echo '<div style="width:100%;margin-top:15px;font-size:1.2em;border-bottom:2px solid chocolate;font-weight:bold;">';

                    echo '<div style="position:relative;display:inline-block;width:92%;vertical-align:top;">';
                        echo '<img style="width:30px;height:25px;margin-right:25px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/gdm/img/veicolo.png" />';
                        echo 'Materiale installato:';
                    echo '</div>';

                    echo '<div style="position:relative;display:inline-block;width:7%;text-align:center;vertical-align:top;">';
                        echo '<img style="width:15px;height:15px;margin-top:7px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/gdm/img/add.png" onclick="window._nebulaGdm.creaMateriale(\'Vettura\');" />';
                    echo '</div>';

                echo '</div>';

                echo '<div style="position:relative;" >';

                if (isset($this->materiali['Vettura'])) {
                    foreach ($this->materiali['Vettura'] as $id=>$m) {
                        echo '<div class="gdmMaterialeDiv">';
                            $m->drawInfo(true,false,'');
                        echo '</div>';
                    }
                }

                echo '</div>';

                ///////////////////////////////////////

                echo '<div style="width:100%;margin-top:15px;font-size:1.2em;border-bottom:2px solid chocolate;font-weight:bold;">';

                    echo '<div style="position:relative;display:inline-block;width:92%;vertical-align:top;">';
                        echo '<img style="width:30px;height:25px;margin-right:25px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/gdm/img/cliente.png" />';
                        echo 'Materiale consegnato al cliente:';
                    echo '</div>';

                    echo '<div style="position:relative;display:inline-block;width:7%;text-align:center;vertical-align:top;">';
                        echo '<img style="width:15px;height:15px;margin-top:7px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/gdm/img/add.png" onclick="window._nebulaGdm.creaMateriale(\'Cliente\');" />';
                    echo '</div>';

                echo '</div>';

                echo '<div style="position:relative;" >';

                if (isset($this->materiali['Cliente'])) {
                    foreach ($this->materiali['Cliente'] as $id=>$m) {
                        echo '<div class="gdmMaterialeDiv">';
                            $m->drawInfo(true,false,'');
                        echo '</div>';
                    }
                }

                echo '</div>';

                ///////////////////////////////////////

                echo '<div style="width:100%;margin-top:15px;font-size:1.2em;border-bottom:2px solid chocolate;font-weight:bold;">';
                    echo '<img style="width:30px;height:25px;margin-right:25px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/gdm/img/trash.png" />';
                    echo 'Materiale smaltito:';
                echo '</div>';

                echo '<div style="position:relative;" >';

                if (isset($this->materiali['Smaltimento'])) {
                    foreach ($this->materiali['Smaltimento'] as $id=>$m) {
                        echo '<div class="gdmMaterialeDiv">';
                            $m->drawInfo(true,false,'');
                        echo '</div>';
                    }
                }

                echo '</div>';

            echo '</div>';

            echo '<div id="gdm_gestione_util" style="width:95%;height:100%;display:none;" >';
                echo '<div style="position:relative;width:100%;height:10%;text-align:right;" >';
                    echo '<img style="width:25px;height:25px;margin-right:15px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/gdm/img/annulla.png" onclick="window._nebulaGdm.closeUtil()"; />';
                echo '</div>';
                echo '<div id="gdm_gestione_util_body" style="position:relative;width:100%;height:90%;" >';
                echo '</div>';
            echo '</div>';

            echo '<script type="text/javascript" >';
                echo 'var temp='.json_encode(mb_convert_encoding($this->tempMateriali, 'UTF-8')).';';
                echo 'window._nebulaGdm.loadMateriali(temp);';
            echo '</script>';

            //echo json_encode(mb_convert_encoding($this->tempMateriali, 'UTF-8'));

        echo '</div>';

        echo '<div style="position:relative;display:inline-block;width:35%;height:99%;padding:3px;box-sizing:border-box;vertical-align:top;">';

            echo '<div style="position:relative;width:100%;height:10%;font-weight:bold;border-bottom:1px solid black;box-sizing:border-box;">';

                echo '<div style="text-align:center;">Richieste</div>';

                echo '<div style="margin-top:5px;">';

                    echo '<div style="position:relative;display:inline-block;width:90%;text-align:left;">';

                        if ($this->flagRichieste['scambio']) {

                            echo '<div id="gdm_scambio" style="width:250px;text-align:center;font-size:0.9em;border: 2px solid #37664a;border-radius: 10px;background-color: #a8ceb7;cursor:pointer;" onclick="window._nebulaGdm.scambio(\''.$this->flagRichieste['scambio']['Deposito'].'\',\''.$this->flagRichieste['scambio']['Vettura'].'\');">';

                                echo '<img style="position:relative;width:16px;height:16px;top:1px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/gdm/img/deposito.png" />';
                                echo '<b style="position:relative;top:-1px;margin-right:10px;margin-left:10px;">('.$this->flagRichieste['scambio']['Deposito'].')</b>';
                                echo '<img style="position:relative;width:15px;height:15px;top:2px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/main/img/blackarrowL.png" />';
                                echo '<img style="position:relative;width:15px;height:15px;margin-left:5px;top:2px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/main/img/blackarrowR.png" />';
                                echo '<b style="position:relative;top:-1px;margin-left:10px;margin-right:10px;">('.$this->flagRichieste['scambio']['Vettura'].')</b>';
                                echo '<img style="position:relative;width:16px;height:16px;top:1px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/gdm/img/veicolo.png" />';

                            echo '</div>';
                        }

                    echo '</div>';

                    echo '<div style="position:relative;display:inline-block;width:9%;text-align:center;">';
                        if ($this->flagRichieste['delActual'] && $this->flagRichieste['delActual']=='S') {
                            echo '<img style="width:20px;height:20px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/gdm/img/trash.png" onclick="window._nebulaGdm.delRichiesta();"/>';
                        }
                    echo '</div>';

                echo '</div>';

            echo '</div>';

            echo '<div style="position:relative;width:100%;height:89%;overflow:scroll;overflow-x:hidden;">';

                echo '<div id="gdm_new_richiesta" style="position:relative;padding-bottom:15px;margin-top:10px;border:2px solid #fb7348;width:95%;padding:3px;box-sizing:border-box;display:none;" ></div>';

                //echo json_encode($this->richieste);
                /*{
                    "20211026_10259": {
                        "dataRi": "20211026",
                        "operazioni": {
                        "19542": {
                            "id": 19542,
                            "idRi": 10259,
                            "idMat": 5836,
                            "destinazione": "Vettura",
                            "statoOp": "Completa",
                            "origine": "Deposito",
                            "storico": "{\"id\":5836,\"tipologia\":\"Pneumatici\",\"locazione\":\"\",\"annotazioni\":\"\",\"nome\":\"\",\"descrizione\":\"\",\"dotASx\":\"4919\",\"dotADx\":\"4919\",\"dotPSx\":\"4919\",\"dotPDx\":\"4919\",\"marcaASx\":\"BRIDGESTONE BLIZZAK\",\"marcaADx\":\"BRIDGESTONE BLIZZAK\",\"marcaPSx\":\"BRIDGESTONE BLIZZAK\",\"marcaPDx\":\"BRIDGESTONE BLIZZAK\",\"usuraASx\":\"8.0\",\"usuraADx\":\"8.0\",\"usuraPSx\":\"8.0\",\"usuraPDx\":\"8.0\",\"compoGomme\":\"KIT\",\"tipoGomme\":\"INVERNALE\",\"colore\":\"\",\"idTelaio\":\"WVWZZZCDZMW352615\",\"proprietario\":\"Vettura\",\"isBusy\":\"False\",\"dataCreazione\":\"20211026\",\"dimeASx\":\"205\\/55R16 94H\",\"dimeADx\":\"205\\/55R16 94H\",\"dimePSx\":\"205\\/55R16 94H\",\"dimePDx\":\"205\\/55R16 94H\",\"isAnnullato\":\"False\",\"isAnnullabile\":\"False\",\"isFull\":\"True\"}",
                            "dataOperazione": null,
                            "statoRi": "CHIUSA",
                            "dataRi": "20211026",
                            "data_rif": "20211026"
                        },
                        "19543": {
                            "id": 19543,
                            "idRi": 10259,
                            "idMat": 5837,
                            "destinazione": "Deposito",
                            "statoOp": "Completa",
                            "origine": "Vettura",
                            "storico": "{\"id\":5837,\"tipologia\":\"Pneumatici\",\"locazione\":\"\",\"annotazioni\":\"materiale generico\",\"nome\":\"\",\"descrizione\":\"\",\"dotASx\":\"4920\",\"dotADx\":\"4920\",\"dotPSx\":\"4920\",\"dotPDx\":\"4920\",\"marcaASx\":\"NEXEN\",\"marcaADx\":\"NEXEN\",\"marcaPSx\":\"NEXEN\",\"marcaPDx\":\"NEXEN\",\"usuraASx\":\"7.0\",\"usuraADx\":\"7.0\",\"usuraPSx\":\"7.0\",\"usuraPDx\":\"7.0\",\"compoGomme\":\"KIT\",\"tipoGomme\":\"ESTIVO\",\"colore\":\"\",\"idTelaio\":\"WVWZZZCDZMW352615\",\"proprietario\":\"Deposito\",\"isBusy\":\"True\",\"dataCreazione\":\"20211026\",\"dimeASx\":\"205\\/55 R16 91V\",\"dimeADx\":\"205\\/55 R16 91V\",\"dimePSx\":\"205\\/55 R16 91V\",\"dimePDx\":\"205\\/55 R16 91V\",\"isAnnullato\":\"False\",\"isAnnullabile\":\"False\",\"isFull\":\"True\"}",
                            "dataOperazione": null,
                            "statoRi": "CHIUSA",
                            "dataRi": "20211026",
                            "data_rif": "20211026"
                        }
                        }
                    }
                }*/

                $this->drawStorico();

            echo '</div>';

        echo '</div>';

    }

    function drawStorico() {

        foreach ($this->richieste as $k=>$r) {

            echo '<div style="position:relative;padding-bottom:15px;margin-top:10px;border-bottom:2px solid black;width:95%;" >';

                echo '<div style="position:relative;padding:3px;box-sizing:border-box;width:100%;min-height:40px;'.($r['statoRi']=='APERTA'?'border:2px solid #81b80f;':'').'" >';

                    $temp=explode('_',$k);

                    if($r['statoRi']=='APERTA') {
                        echo '<input id="gdm_richiesta_aperta" type="hidden" value="'.$temp[1].'" />';
                    }

                    
                    //echo '<div style="position:relative;width:100%;font-weight:bold;background-color:#c0c0be;" >';
                    echo '<div style="position:relative;width:100%;font-weight:bold;'.($r['statoRi']=='APERTA'?'background-color:#daf5a2;':'').'" >';
                        echo '<div style="position:relative;display:inline-block;width:85%;vertical-align:top;" >'.$temp[1].' - '.mainFunc::gab_todata($r['dataRi']).' - '.$r['statoRi'].'</div>';
                        echo '<div style="position:relative;display:inline-block;width:14%;text-align:center;vertical-align:top;" >';
                            if(!$r['loca'] && ($r['statoRi']=='CHIUSA' || $r['pss'])) {
                                echo '<img style="width:15px;height:15px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/gdm/img/print.png" onclick="window._nebulaGdm.stampaRichiesta(\''.$temp[1].'\');"/>';
                            }
                        echo '</div>';
                    echo '</div>';

                    foreach ($r['operazioni'] as $ko=>$o) {

                        //la classe viene utilizzata per identificare il div
                        echo '<div class="gdmOperazioneDiv gdmOperazione_'.$o['idMat'].'" style="position:relative;margin-top:5px;padding:3px;box-sizing:border-box;width:100%;border:1px solid black;" >';

                            $storico=json_decode($o['storico'],true);

                            echo '<div style="position:relative;font-size:0.9em;font-weight:bold;">'.$ko.' - '.$o['statoOp'].' - '.(!is_null($o['dataOperazione'])?mainFunc::gab_todata($o['dataOperazione']):'');
                                if ($storico && isset($storico['locazione']) && $o['destinazione']=='Deposito') echo ' - '.$storico['locazione'];
                            echo '</div>';

                            echo '<div style="position:relative;font-size:0.9em;height:15px;';
                                if ($o['origine']=='NUOVA' || $o['origine']=='EDIT' || $o['origine']=='LOCA') echo 'color:blueviolet;';
                            echo '">';
                                echo '<div style="position:relative;display:inline-block;width:12%;vertical-align:top;font-size:0.9em;" >('.$o['idMat'].')</div>';
                                echo '<div style="position:relative;display:inline-block;width:18%;vertical-align:top;" >'.($o['tipologia']=='Pneumatici'?$o['compoGomme']:$o['nome']).'</div>';
                                echo '<div style="position:relative;display:inline-block;width:27%;vertical-align:top;" >'.($o['tipologia']=='Pneumatici'?$o['tipoGomme']:$o['descrizione']).'</div>';
                                echo '<div style="position:relative;display:inline-block;width:16%;vertical-align:top;" >'.$o['origine'].'</div>';
                                echo '<div style="position:relative;display:inline-block;width:6%;vertical-align:top;text-align:center;height:100%;" >';
                                    echo '<img style="position:relative;width:10px;height:6px;top:55%;transform:translate(0px,-50%);" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/main//img/blackarrowR.png" />';
                                echo '</div>';
                                echo '<div style="position:relative;display:inline-block;width:20%;vertical-align:top;'.($o['destinazione']=='Smaltimento'?'color:red;font-weight:bold;':'').'" >'.substr($o['destinazione'],0,8).'</div>';
                            echo '</div>';

                        echo '</div>';
                    }
                echo '</div>';

            echo '</div>';
        }

    }

}
?>