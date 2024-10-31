<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/core/divo/divo.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/apps/ermes/classi/ermes.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/core/chain/chain.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/core/dudu/dudu.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/core/blocklist/blocklist.php');

class ermesApp extends appBaseClass {

    //usato per MONO categoria
    //protected $categoria="";

    protected $reparti=array();
    protected $macrorep=array();

    //collaboratori dei reparti di cui faccio parte
    //SE sono reparti abilitati a ricevere ticket
    protected $coll=array();

    protected $defColl=array(
        "sede"=>"",
        "macrorep"=>"",
        "reparto"=>""
    );

    protected $contesto="";

    protected $ermes;

    function __construct($param,$galileo) {
        
        parent::__construct($galileo);

        $this->loc='/nebula/apps/ermes/';
       
        $this->loadParams($param);

        $this->contesto='_nebulaApp_'.$this->param['nebulaFunzione']['nome'];

        $this->ermes=new ermes($this->galileo);

        $temp=$this->ermes->buildRep();

        $this->reparti=$temp['reparti'];
        $this->macrorep=$temp['macrorep'];

        $temp=$this->ermes->buildColl($this->id->getLoggedReparti());

        $this->coll=$temp['coll'];
        $this->defColl=$temp['defColl'];
    }

    function initClass() {
        return ' ermesCode(\''.$this->param['nebulaFunzione']['nome'].'\');';
    }

    function customDraw() {

        nebulaDudu::duduInit();
        BlockList::blockListInit();

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

                //se non ci sono elementi in "coll" significa che nemmeno io faccio parte di un reparto che può gestire i ticket
                if (count($this->coll)!=0) {

                    ob_start();
                        $this->drawPanorama();

                    $divo->add_div('Panorama','black',0,"",ob_get_clean(),0,$css);

                    ob_start();
                        $this->drawGestione();

                    $divo->add_div('In Gestione','black',0,"",ob_get_clean(),1,$css);
                
                }

                ob_start();
                        $this->drawMiei();

                $divo->add_div('Miei Ticket','black',0,"",ob_get_clean(),0,$css);

                $divo->build();

                $divo->draw();

            echo '</div>';

            echo '<div style="position:relative;display:inline-block;width:18%;height:100%;vertical-align:top;border-right:1px solid black;padding:3px;box-sizing:border-box;" >';
            echo '</div>';

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
            echo 'window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.loadGestione(\''.$this->contesto.'\');';
        echo '</script>';

    }

    function drawTodos($actual,$temp,$row) {

        $t=array(

        );

        $todo=new nebulaDudu("_ermesTicket",$this->galileo);
        $bl=new BlockList('ermes_'.$actual,0);
        $tk=new ermesTicket($this->galileo);
        $tk->build($row);
        $todo->loadLista($actual,$temp);
        ob_start();
            //è sempre enabled visto che per i ticket chiusi nemmeno viene considerato
            $todo->drawLista('erpan',1);
        $bl->setBody(ob_get_clean());
        ob_start();
            $tk->drawTodoIntest();
        $bl->setHead(ob_get_clean());
        $bl->setPcg(7);
        $bl->setHeadH('30px');
        return $bl->draw();
    }

    function drawPanorama() {

        echo '<div style="position:relative;width:100%;height:10%;" >';

            echo '<div class="ermes_panorama_form" style="width:100%;margin-top:5px;">';
            
                echo '<div style="position:relative;display:inline-block;width:14%;vertical-align:top;" >';
                    echo '<div>';
                        echo '<lable>Sede:</lable>';
                    echo '</div>';
                    echo '<div>';
                        echo '<select id="ermes_sede" style="width:90%;" onchange="window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.changeSede(this.value);">';
                        echo '</select>';
                    echo '</div>';
                echo '</div>';
                echo '<div style="position:relative;display:inline-block;width:28%;vertical-align:top;" >';
                    echo '<div>';
                        echo '<lable>Macroreparto:</lable>';
                    echo '</div>';
                    echo '<div>';
                        echo '<select id="ermes_macrorep" style="width:90%;" onchange="window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.changeMacrorep(this.value);">';
                        echo '</select>';
                    echo '</div>';
                echo '</div>';
                echo '<div style="position:relative;display:inline-block;width:35%;vertical-align:top;" >';
                    echo '<div>';
                        echo '<lable>Reparto:</lable>';
                    echo '</div>';
                    echo '<div>';
                        echo '<select id="ermes_reparto" style="width:95%;" >';
                        echo '</select>';
                    echo '</div>';
                echo '</div>';
                echo '<div style="position:relative;display:inline-block;width:8%;vertical-align:top;height:100%;text-align:center;" >';
                    echo '<div>';
                        echo '<lable>Gestito</lable>';
                    echo '</div>';
                    echo '<div>';
                        echo '<input id="ermes_flag_gestito" style="" type="checkbox" />';
                    echo '</div>';
                echo '</div>';
                echo '<div style="position:relative;display:inline-block;width:15%;vertical-align:top;height:100%;" >';
                    echo '<button style="top: 50%;position: relative;transform: translate(0,-65%);" onclick="window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.loadPanorama(\''.$this->contesto.'\');" >Aggiorna</button>';
                echo '</div>';

            echo '</div>';

        echo '</div>';

        echo '<div id="ermes_panorama_body" style="position:relative;width:100%;height:84%;overflow:scroll;overflow-x:hidden;" >';
            //echo json_encode($this->reparti);
        echo '</div>';

    }

    function drawGestione() {

        echo '<div style="position:relative;width:100%;height:10%;" >';

            echo '<div class="ermes_gestione_form" style="width:100%;margin-top:3px;">';

                echo '<div style="position:relative;display:inline-block;width:38%;vertical-align:top;" >';
                    echo '<div>';
                        echo '<lable>Collega di reparto:</lable>';
                    echo '</div>';
                    echo '<div>';
                        echo '<select id="ermes_coll" style="width:90%;font-weight:1.2em;" >';

                            $txt="";

                            foreach ($this->coll as $k=>$c) {

                                $txt.="'".$k."',";
                            
                                echo '<option value="\''.$k.'\'"';
                                    if ($k==$this->id->getLogged()) echo ' selected';
                                echo '>'.$k.'</option>';
                            }

                            echo '<option value="'.substr($txt,0,-1).'" >Tutti</option>';

                        echo '</select>';
                    echo '</div>';

                echo '</div>';

                echo '<div style="position:relative;display:inline-block;width:20%;vertical-align:top;height:100%;" >';
                    echo '<button style="top: 50%;position: relative;transform: translate(0,-65%);" onclick="window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.loadGestione(\''.$this->contesto.'\');" >Aggiorna</button>';
                echo '</div>';

            echo '</div>';

        echo '</div>';

        echo '<div id="ermes_gestione_body" style="position:relative;width:100%;height:90%;overflow:scroll;overflow-x:hidden;" >';
            //echo json_encode($this->id->getLoggedReparti());
        echo '</div>';
    }

    function drawMiei() {

        echo '<div style="position:relative;width:100%;height:15%;" >';

            echo '<div>';

                echo '<div class="ermes_miei_form" style="width:100%;margin-top:3px;">';

                    /*echo '<div style="position:relative;display:inline-block;width:38%;vertical-align:top;" >';
                        echo '<div>';
                            echo '<lable>Macroreparto:</lable>';
                        echo '</div>';
                        echo '<div>';
                            echo '<select id="ermes_miei_macrorep" style="width:90%;" >';
                            echo '</select>';
                        echo '</div>';
                        echo '<div>';
                            echo '<lable>Reparto:</lable>';
                        echo '</div>';
                        echo '<div>';
                            echo '<select id="ermes_miei_reparto" style="width:90%;" >';
                            echo '</select>';
                        echo '</div>';
                    echo '</div>';*/
                    
                    echo '<div style="position:relative;display:inline-block;width:22%;vertical-align:top;" >';

                        echo '<div>';
                            echo '<lable>Gestione:</lable>';
                        echo '</div>';

                        echo '<div>';
                            echo '<select id="ermes_miei_gestione" style="width:70%;" >';
                                echo '<option value="creato" >Creato</option>';
                                echo '<option value="gestito" >Gestito</option>';
                                echo '<option value="tutti" >Tutti</option>';
                            echo '</select>';
                        echo '</div>';

                        echo '<div>';
                            echo '<lable>Stato:</lable>';
                        echo '</div>';

                        echo '<div>';
                            echo '<select id="ermes_miei_stato" style="width:70%;" >';
                                echo '<option value="aperto" >Aperto</option>';
                                echo '<option value="chiuso" >Chiuso</option>';
                                echo '<option value="tutti" >Tutti</option>';
                            echo '</select>';
                        echo '</div>';
                        
                    echo '</div>';

                    $temp=strtotime("-1 month",time());


                    echo '<div style="position:relative;display:inline-block;width:30%;vertical-align:top;" >';
                        echo '<div>';
                            echo '<lable>Da:</lable>';
                        echo '</div>';
                        echo '<div>';
                            echo '<input id="ermes_miei_da" style="width:90%;" type="date" value="'.date("Y-m-d",$temp).'" />';
                        echo '</div>';
                        echo '<div>';
                            echo '<lable>A:</lable>';
                        echo '</div>';
                        echo '<div>';
                            echo '<input id="ermes_miei_a" style="width:90%;" type="date" value="'.date('Y-m-d').'" />';
                        echo '</div>';
                    echo '</div>';

                    echo '<div style="position:relative;display:inline-block;width:38%;vertical-align:bottom;" >';

                        echo '<div style="text-align:left;">';
                            echo '<button style="margin-top:20px;" onclick="window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.loadMiei(\''.$this->contesto.'\');" >cerca</button>';
                        echo '</div>';

                    echo '</div>';

                    echo '<div style="position:relative;display:inline-block;width:10%;vertical-align:top;text-align:right;" >';
                        echo '<div>';
                            echo '<img style="position:relative;width:35px;height:35px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/ermes/img/add.png" onclick="window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.newTicket(0,\''.$this->contesto.'\');"/>';
                        echo '</div>';

                        /*echo '<div style="text-align:center;">';
                            echo '<button style="margin-top:20px;">cerca</button>';
                        echo '</div>';*/

                    echo '</div>';

                echo '</div>';

            echo '</div>';

        echo '</div>';

        echo '<div id="ermes_miei_body" style="position:relative;width:100%;height:85%;overflow:scroll;overflow-x:hidden;" >';
        echo '</div>';

    }

}
?>