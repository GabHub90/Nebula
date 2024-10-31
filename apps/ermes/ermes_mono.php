<?php

class ermesMono extends ermesApp {

    //tipo:
    //creazione = interfaccia per creatori di ticket Es: Distribuzione
    //ricezione = interfaccia per gestori di ticket Es: Magazzino
    protected $monoInfo=array(
        "categoria"=>"",
        "reparto"=>"",
        "tipo"=>""
    );

    function __construct($param,$galileo) {

        $this->param['categoria']="";
        
        parent::__construct($param,$galileo);

        $temp=explode(':',$this->param['categoria']);

        $this->monoInfo['reparto']=$temp[0];
        $this->monoInfo['categoria']=$temp[1];
        $this->monoInfo['tipo']=$temp[2];  
    }

    function customDraw() {

        nebulaDudu::duduInit();
        BlockList::blockListInit();

        //echo json_encode($this->param);

        echo '<div id="ermes_main" style="position:relative;width:100%;">';

            $divo=new Divo('ermes','5%','95%',false);

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

            echo '<div style="position:relative;display:inline-block;width:55%;height:100%;vertical-align:top;border-right:1px solid black;padding:3px;box-sizing:border-box;" >';

                echo '<input id="ermes_mono_reparto" type="hidden" value="'.$this->monoInfo['reparto'].'" />';
                echo '<input id="ermes_mono_categoria" type="hidden" value="'.$this->monoInfo['categoria'].'" />';

                if ($this->monoInfo['tipo']=='creazione') {
                    ob_start();
                        $this->drawPanorama();

                    $divo->add_div('Panorama','black',0,"",ob_get_clean(),0,$css);
                }
                else {
                    ob_start();
                        $this->drawPanoramaRic();

                    $divo->add_div('Panorama','black',0,"",ob_get_clean(),0,$css);
                }


                if ($this->monoInfo['tipo']=='ricezione') {

                    ob_start();
                        $this->drawGestione();

                    $divo->add_div('In Gestione','black',0,"",ob_get_clean(),1,$css);
                }

                //if ($this->monoInfo['tipo']=='creazione') {
                
                    ob_start();
                            $this->drawMiei();

                    $divo->add_div('Miei Ticket','black',0,"",ob_get_clean(),0,$css);
                //}

                $divo->build();

                $divo->draw();

            echo '</div>';

            echo '<div style="position:relative;display:inline-block;width:18%;height:100%;vertical-align:top;border-right:1px solid black;padding:3px;box-sizing:border-box;" >';
            echo '</div>';
            
            if ($this->monoInfo['tipo']=='ricezione') {

                echo '<div style="position:relative;display:inline-block;width:27%;height:100%;vertical-align:top;padding:3px;box-sizing:border-box;" >';

                    echo '<div style="position:relative;width:100%:height:10%;text-align:center;font-weight:bold;font-size:1.2em;" >';
                        echo 'TO-DOs';
                    echo '</div>';

                    echo '<div style="position:relative;width:100%:height:90%;overflow:scroll;overflow-x:hidden;">';
                        //scrittura dei TODO del collaboratore
                        $this->galileo->executeGeneric('ermes','getTodoByGestore',array('gestore'=>$this->id->getLogged()),'');
                        if ($this->galileo->getResult()) {
                            $fid=$this->galileo->preFetch('ermes');

                            $temp=array();
                            $actual=0;
                            $actualRow=array();

                            while($row=$this->galileo->getFetch('ermes',$fid)) {

                                if ($actual!=$row['todo_ID'] && count($temp)>0) {
                                    echo $this->drawTodos($actual,$temp,$actualRow);
                                    $temp=array();
                                }

                                $actual=$row['todo_ID'];
                                $actualRow=$row;
                                $temp[$row['todo_riga']]=array(
                                    "ID"=>$row['todo_ID'],
                                    "riga"=>$row['todo_riga'],
                                    "testo"=>$row['todo_testo'],
                                    "d_creazione"=>$row['todo_d_creazione'],
                                    "d_scadenza"=>$row['todo_d_scadenza'],
                                    "d_chiusura"=>$row['todo_d_chiusura']
                                );
                            }

                            if (count($temp)>0) {
                                echo $this->drawTodos($actual,$temp,$actualRow);
                            }

                        }
                    echo '</div>';

                echo '</div>';        
            }
        
        echo '</div>';

        echo '<div id="ermes_util" style="position:relative;width:100%;display:none;">';

            echo '<div style="position:relative;widht:100%;height:10%;">';

                echo '<div style="position:relative;display:inline-block;width:98%;vertical-align:top;height:100%;text-align:right;" >';
                    echo '<img style="position:relative;width:35px;height:35px;top:50%;transform:translate(0,-65%);" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/ermes/img/chiudi.png" onclick="window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.chiudiTicket(\''.$this->id->getLogged().'\');" />';
                echo '</div>';

            echo '</div>';

            echo '<div id="ermes_util_body" style="position:relative;width:100%;height:90%;">';
            echo '</div>';

        echo '</div>';

        echo '<script type="text/javascript" >';
            $temp=base64_encode(json_encode($this->reparti));
            echo 'window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.loadReparti("'.$temp.'");';
            $temp=base64_encode(json_encode($this->macrorep));
            echo 'window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.loadMacrorep("'.$temp.'");';
            $temp=base64_encode(json_encode($this->defColl));
            echo 'window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.loadDefColl("'.$temp.'");';

            echo 'window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.initPanorama();';

            if ($this->monoInfo['tipo']=='ricezione') {
                echo 'window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.loadGestione(\''.$this->contesto.'\');';
            }

        echo '</script>';

    }

    function drawPanorama() {

        //echo json_encode($this->monoInfo);

        echo '<div style="position:relative;width:100%;height:15%;" >';

            //if ($this->monoInfo['tipo']=='creazione') {

                echo '<div class="ermes_miei_form" style="width:100%;margin-top:3px;">';

                    echo '<div style="position:relative;display:inline-block;width:22%;vertical-align:top;" >';

                        echo '<div>';
                            echo '<lable>Stato:</lable>';
                        echo '</div>';

                        echo '<div>';
                            echo '<select id="ermes_mono_panorama_stato" style="width:70%;" >';
                                echo '<option value="aperto" >Aperto</option>';
                                echo '<option value="chiuso" >Chiuso</option>';
                                echo '<option value="tutti" >Tutti</option>';
                            echo '</select>';
                        echo '</div>';
                    
                    echo '</div>';

                    echo '<div style="position:relative;display:inline-block;width:40%;vertical-align:top;" >';

                        echo '<div>';
                            echo '<lable>Mittente:</lable>';
                        echo '</div>';

                        echo '<div>';
                            echo '<input id="ermes_mono_panorama_testo" style="width:90%;" type="text" />';
                        echo '</div>';
                    
                    echo '</div>';

                    echo '<div style="position:relative;display:inline-block;width:28%;vertical-align:bottom;" >';

                        echo '<div style="text-align:left;">';
                            echo '<button style="" onclick="window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.monoPanorama(\''.$this->contesto.'\');" >cerca</button>';
                        echo '</div>';

                    echo '</div>';
                
                echo '</div>';
            //}

        echo '</div>';

        echo '<div id="ermes_mono_panorama_body" style="position:relative;width:100%;height:85%;overflow:scroll;overflow-x:hidden;" >';
        echo '</div>';

        echo '<script type="text/javascript" >';
            echo 'window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.monoPanorama(\''.$this->contesto.'\');';
        echo '</script>';

    }

    function drawPanoramaRic() {

        //echo json_encode($this->monoInfo);

        echo '<div style="position:relative;width:100%;height:15%;" >';

            echo '<div class="ermes_miei_form" style="width:100%;margin-top:3px;">';

                echo '<div style="position:relative;display:inline-block;width:22%;vertical-align:top;" >';

                    echo '<div>';
                        echo '<lable>Stato:</lable>';
                    echo '</div>';

                    echo '<div>';
                        echo '<select id="ermes_mono_panorama_stato" style="width:70%;" >';
                            echo '<option value="nogest" >NON Gestito</option>';
                            echo '<option value="chiuso" >Chiuso</option>';
                        echo '</select>';
                    echo '</div>';
                
                echo '</div>';

                echo '<div style="position:relative;display:inline-block;width:40%;vertical-align:top;" >';

                    echo '<div>';
                        echo '<lable>Mittente:</lable>';
                    echo '</div>';

                    echo '<div>';
                        echo '<input id="ermes_mono_panorama_testo" style="width:90%;" type="text" />';
                    echo '</div>';
                
                echo '</div>';

                echo '<div style="position:relative;display:inline-block;width:28%;vertical-align:bottom;" >';

                    echo '<div style="text-align:left;">';
                        echo '<button style="" onclick="window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.monoPanorama(\''.$this->contesto.'\');" >cerca</button>';
                    echo '</div>';

                echo '</div>';

            echo '</div>';

        echo '</div>';

        echo '<div id="ermes_mono_panorama_body" style="position:relative;width:100%;height:85%;overflow:scroll;overflow-x:hidden;" >';
        echo '</div>';

        echo '<script type="text/javascript" >';
            echo 'window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.monoPanorama(\''.$this->contesto.'\');';
        echo '</script>';

    }

    function drawGestione() {

        echo '<div id="ermes_mono_gestione_body" style="position:relative;width:100%;height:99%;margin-top:1%;overflow:scroll;overflow-x:hidden;" >';
            //visualizzazione tickets aperti in gestione
        echo '</div>';

        echo '<script type="text/javascript" >';
            echo 'window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.monoGestione(\''.$this->contesto.'\');';
        echo '</script>';

    }

    function drawMiei() {

        echo '<div class="ermes_miei_form" style="width:100%;margin-top:3px;height:15%">';

            echo '<input id="ermes_mono_miei_gestione" type="hidden" value="'.($this->monoInfo['tipo']=='creazione'?'creato':'gestito').'" />';

            echo '<div style="position:relative;display:inline-block;width:22%;vertical-align:top;" >';

                echo '<div>';
                    echo '<lable>'.($this->monoInfo['tipo']=='creazione'?'CREATI':'GESTITI').'</lable>';
                echo '</div>';

                echo '<div>';
                    echo '<select id="ermes_mono_miei_stato" style="width:70%;" >';
                        echo '<option value="aperto" >Aperto</option>';
                        echo '<option value="chiuso" >Chiuso</option>';
                        echo '<option value="tutti" >Tutti</option>';
                    echo '</select>';
                echo '</div>';
            
            echo '</div>';

            /*echo '<div style="position:relative;display:inline-block;width:22%;vertical-align:top;" >';
                echo '<div>';
                    echo '<lable>Gestione:</lable>';
                echo '</div>';

                echo '<div>';
                    echo '<select id="ermes_mono_miei_gestione" style="width:70%;" >';
                        echo '<option value="creato" >Creato</option>';
                        echo '<option value="gestito" >Gestito</option>';
                        echo '<option value="tutti" >Tutti</option>';
                    echo '</select>';
                echo '</div>';
            echo '</div>';*/

            echo '<div style="position:relative;display:inline-block;width:40%;vertical-align:top;" >';

                echo '<div>';
                    echo '<lable>Mittente:</lable>';
                echo '</div>';

                echo '<div>';
                    echo '<input id="ermes_mono_miei_testo" style="width:90%;" type="text" />';
                echo '</div>';
            
            echo '</div>';

            echo '<div style="position:relative;display:inline-block;width:28%;vertical-align:bottom;" >';

                echo '<div style="text-align:left;">';
                    echo '<button style="" onclick="window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.monoMiei(\''.$this->contesto.'\');" >cerca</button>';
                echo '</div>';

            echo '</div>';

            echo '<div style="position:relative;display:inline-block;width:10%;vertical-align:top;text-align:right;" >';

                if ($this->monoInfo['tipo']=='creazione') {

                    echo '<div>';
                        echo '<img style="position:relative;width:35px;height:35px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/ermes/img/add.png" onclick="window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.newMonoTicket(\''.$this->contesto.'\',\''.$this->monoInfo['reparto'].'\',\''.$this->monoInfo['categoria'].'\');"/>';
                    echo '</div>';

                }

            echo '</div>';
        
        echo '</div>';

        echo '<div id="ermes_mono_miei_body" style="position:relative;width:100%;height:85%;overflow:scroll;overflow-x:hidden;" >';
        echo '</div>';

    }


}
?>