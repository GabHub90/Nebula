<?php

class c2rApp extends appBaseClass {

    protected $reparti=array();
    //reparti di appartenenza del collaboratore loggato
    protected $repColl=array();

    //array dei gruppi identificati come responsabili
    protected $responsabili=array();

    protected $moduli=array(
        "fatturato_S"=>false,
        "prod_S"=>false,
        "budget_S"=>false,
        "giac_M"=>false,
        "ven_M"=>false,
        "acq_M"=>false
    );

    protected $config=array(
        "mrep"=>"",
        "tipo"=>"standard",
        "totali"=>false,
        "collab"=>false,
        "repcol"=>false,
        "responsabile"=>false,
        "collaboratore"=>""
    );

    function __construct($param,$galileo) {
        
        parent::__construct($galileo);

        $this->loc='/nebula/apps/c2r/';

        $this->param['c2r_macroreparto']="";
        $this->param['c2rConfig']="";
        //$this->param['c2rCollaboratore']="";

        $this->loadParams($param);

        $this->config['mrep']=$this->param['c2r_macroreparto'];

        $this->galileo->executeSelect('croom','C2R_responsabili',"stato!='0'","");
        $result=$this->galileo->getResult();
        if ($result) {
            $fetID=$this->galileo->preFetch('croom');
            while ($row=$this->galileo->getFetch('croom',$fetID)) {
                $this->responsabili[$row['gruppo']]=$row;
            }
        }

        $this->param['c2rConfig']=($this->param['c2rConfig']=='')?'standard':$this->param['c2rConfig'];
        //$this->config['collaboratore']=$this->param['c2rCollaboratore'];

        if ($this->param['c2r_macroreparto']!="") {

            $this->repColl=$this->id->getReparto($this->param['c2r_macroreparto']);

            $this->galileo->getReparti($this->param['c2r_macroreparto'],'ar.area,trep.tag');
            $result=$this->galileo->getResult();
            if ($result) {
                $fetID=$this->galileo->preFetchBase('maestro');
                while ($row=$this->galileo->getFetchBase('maestro',$fetID)) {
                    $this->reparti[$row['area']]['reparti'][$row['reparto']]=$row;
                    $this->reparti[$row['area']]['des_area']=$row['des_area'];

                    //$this->config['gruppi'][]=$this->id->getGruppo($row['reparto'],array('A','M'));
                }
            }

            /*
            foreach($this->moduli as $km=>$m) {
                $t=explode("_",$km);
                if ($t[1]==$this->param['c2r_macroreparto']) $this->moduli[$km]=true;
                else $this->moduli[$km]=false;
            }
            */

            /////////////////////////////
            //CONFIG
            $this->config['collaboratore']=$this->id->getCollID();

            foreach ($this->responsabili as $gruppo=>$g) {

                if ($this->id->checkIDgruppi($gruppo)) {

                    if ($g['responsabile']=="1") $this->config['responsabile']=true;

                    if ($this->config['mrep']=='S') {

                        if ($g['modulo_prod']=="1") $this->moduli['prod_S']=true;
                        if ($g['modulo_fatt']=="1") $this->moduli['fatturato_S']=true;
                        if ($g['modulo_budget']=="1") $this->moduli['budget_S']=true;

                        if ($g['prod_totali']=="1") $this->config['totali']=true;
                        if ($g['prod_collab']=="1") $this->config['collab']=true;
                        if ($g['prod_repcol']=="1") $this->config['repcol']=true;

                        //########################################
                        //########################################
                        //aggiunto per Giacomo Blasi 01.10.2024
                        if ($this->config['collaboratore']==22) {
                            $this->config['collab']=true;
                            $this->config['repcol']=true;
                        }
                        //########################################
                        //########################################
                    }

                    //per il momento tolto e messo sempre TRUE - 03.01.2024
                    /*if ($this->config['mrep']=='M') {

                        if ($g['modulo_giac']=="1") $this->moduli['giac_M']=true;
                        if ($g['modulo_ven']=="1") $this->moduli['ven_M']=true;
                        if ($g['modulo_acq']=="1") $this->moduli['acq_M']=true;
                    }*/
                }
            }

            $this->config['tipo']=($this->config['responsabile'])?'standard':$this->param['c2rConfig'];

            //TEST
            if ($this->config['mrep']=='M') {

                $this->moduli['giac_M']=true;
                $this->moduli['ven_M']=true;
                $this->moduli['acq_M']=true;

                $this->config['responsabile']=true;
            }
            //ENDTEST
            

            /*se l'utente NON è RS in un reparto di cui fa parte
            $tempchk=false;
            $temptotaliprod=false;
            foreach ($this->config['gruppi'] as $kg) {
                //se lo stato == 1 è un responsabile che può vedere tutte le analisi ed i totali
                if ($this->checkStato($kg,"1")) {
                    $tempchk=true;
                    $temptotaliprod=true;
                }
                //stato == 2 significa che può vedere i totali della PRODUTTIVITÀ
                elseif ($this->checkStato($kg,"2")) {
                    $temptotaliprod=true;
                }
            }
            
            //if (!in_array('RS',$this->config['gruppi']) && !in_array('ITR',$this->config['gruppi']) && !in_array('RM',$this->config['gruppi']) ) {
            if (!$tempchk) {
                $this->moduli['fatturato_S']=false;
                $this->moduli['budget_S']=false;

                if ($this->param['c2rConfig']=='personale') {
                    //assegnare questa variabile significa definire un'analisi specifica per collaboratore (produttività)
                    if (!$temptotaliprod) {
                        $this->config['collaboratore']=$this->id->getCollID();
                    }
                }
            }
            else {
                $this->config['responsabile']=true;
            }*/
        
        }
    }

    function initClass() {
        return ' c2rCode(\''.$this->param['nebulaFunzione']['nome'].'\');';
    }

    function checkStato($gruppo,$stato) {
        if (array_key_exists($gruppo,$this->responsabili)) {
            if ($this->responsabili[$gruppo]==$stato) return true;
            else return false;
        }
        else return false;
    }

    function navigator() {

        echo '<div id="c2r_navigator_head" style="position:relative;border:2px solid transparent;box-sizing:border-box;">';

            echo '<div style="margin-left:5px;display:inline-block;font-size:0.9em;vertical-align:top;">';

                echo '<div style="border:1px solid black;background-color:#4c4c4c;color:white;box-sizing:border-box;">';

                    echo '<div style="width:100%;text-align:center;font-weight:bold;">'.$this->param['c2r_macroreparto'].'</div>';

                    echo '<div style="padding-top:3px;padding-bottom:3px;padding-right:3px;color:black;">';

                        echo '<div style="position:relative;display:inline-block;width:70px;text-align:center;font-weight:bold;margin-left:3px;background-color:transparent;box-sizing:border-box;">';
                            echo '<button id="c2r_divbutton_tutti" style="cursor:pointer;width:90%;font-size:0.9em;" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].setRepartoAll();">tutti</button>';
                        echo '</div>';
                    
                    echo '</div>';
                
                echo '</div>';

            echo '</div>';

            foreach ($this->reparti as $area=>$a) {

                //#############################################
                //Visualizzazione reparti in base all'abilitazione
                //#############################################
                
                echo '<div style="margin-left:5px;display:inline-block;font-size:0.9em;min-width:50px;min-height:42px;vertical-align:top;">';

                    echo '<div style="border:1px solid black;background-color:#6b6565;color:white;box-sizing:border-box;border-radius: 10px 10px 0px 0px;">';

                        echo '<div style="width:100%;text-align:center;font-weight:bold;">'.$area.'</div>';

                        echo '<div style="padding-top:3px;padding-bottom:3px;padding-right:3px;color:black;">';

                            foreach ($a['reparti'] as $tag=>$r) {

                                //se è un responsabile o fa parte del reparto
                                if ($this->config['responsabile'] || array_key_exists($tag,$this->repColl)) {

                                    if ($area=='X') $onclick=false;
                                    else $onclick=true;

                                    echo '<div style="position:relative;display:inline-block;width:50px;text-align:center;font-weight:bold;margin-left:3px;background-color:#'.$r['fore'].';border:1px solid black;box-sizing:border-box;">';
                                        echo '<div style="cursor:pointer;"';
                                            if ($onclick) echo ' onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].setReparto(\''.$tag.'\');"';
                                        echo '>'.$tag.'</div>';

                                        echo '<div';
                                            if ($onclick) echo' id="c2r_reparto_cover_'.$tag.'"';
                                        echo ' style="position:absolute;top:0px;left:0px;width:100%;height:100%;background-color:white;opacity:0.8;z-index:5;cursor:pointer;';
                                            if (array_key_exists($tag,$this->repColl)) echo 'display:none;';
                                        echo '"';
                                            if ($onclick) echo ' onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].setReparto(\''.$tag.'\');"';
                                        echo '></div>';

                                        echo '<input id="c2r_reparto_set_'.$tag.'" type="hidden" value="';
                                            echo (array_key_exists($tag,$this->repColl))?'1':'0';
                                        echo '" data-id="'.$tag.'" data-area="'.$area.'" />';

                                    echo '</div>';
                                }
                            }
                
                        echo '</div>';
                    
                    echo '</div>';

                echo '</div>';
            }

            /*if ($this->param['c2r_macroreparto']=='S') {

                echo '<div style="margin-left:30px;display:inline-block;font-size:0.9em;margin-top:3px;">';

                    $tt=array('A','C','N','P','S','V','X');

                    foreach ($tt as $marca) {

                        echo '<div style="margin-left:5px;display:inline-block;font-size:0.9em;">';

                            echo '<div style="border:1px solid black;background-color:#b7aeae;color:black;padding:2px;box-sizing:border-box;">';

                                echo '<div style="text-align:center;">';
                                    echo '<input id="c2r_marca_set_'.$marca.'" type="checkbox" value="'.$marca.'" checked="checked" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].clear();"/>';
                                echo '</div>';

                                echo '<div style="width:100%;text-align:center;font-weight:bold;">'.$marca.'</div>';
                            
                            echo '</div>';

                        echo '</div>';
                    }

                    echo '<div style="margin-left:5px;display:inline-block;font-size:0.9em;vertical-align:top;">';

                        echo '<div style="border:1px solid black;background-color:#4c4c4c;color:white;padding:2px;box-sizing:border-box;">';

                            echo '<div style="text-align:center;">';
                                echo '<input type="checkbox" checked="checked" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].setMarcaAll(this.checked);"/>';
                            echo '</div>';
                        
                        echo '</div>';

                    echo '</div>';

                echo '</div>';

            }*/

		echo '</div>';

        echo '<div id="" style="margin-top:10px;width:100%;font-size:0.8em;">';

		    echo '<div style="display:inline-block;width:5%;">';
		
                $ty=(int)date('Y');
                echo '<select id="c2r_periodo_opzioni_anno" style="width:95%;" onchange="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].chgAnno();">';
                    while ($ty>=2012) {
                        echo '<option value="'.$ty.'">'.$ty.'</option>';
                        $ty--;
                    }
                echo '</select>';

            echo '</div>';

            echo '<div style="display:inline-block;width:0.5%;"></div>';

			echo '<div style="display:inline-block;width:4%;cursor:pointer;" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].setPeriodo(\'YTD\',\'YTD\',\'f\')">';
				echo '<div style="width:90%;border:1px solid black;text-align:center;box-sizing:border-box;">YTD</div>';
			echo '</div>';

			echo '<div style="display:inline-block;width:4%;cursor:pointer;" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].setPeriodo(\'\',\'\',\'f\')">';
				echo '<div style="width:90%;border:1px solid black;text-align:center;box-sizing:border-box;">xxx</div>';
			echo '</div>';

            echo '<div style="display:inline-block;width:0.5%;"></div>';
			
            $tt=array(
                'Gen'=>array('01-01','01-31'),
                'Feb'=>array('02-01','02-28'),
                'Mar'=>array('03-01','03-31'),
                'Apr'=>array('04-01','04-30'),
                'Mag'=>array('05-01','05-31'),
                'Giu'=>array('06-01','06-30'),
                'Lug'=>array('07-01','07-31'),
                'Ago'=>array('08-01','08-31'),
                'Set'=>array('09-01','09-30'),
                'Ott'=>array('10-01','10-31'),
                'Nov'=>array('11-01','11-30'),
                'Dic'=>array('12-01','12-31')
            );

            foreach ($tt as $mese=>$m) {
                echo '<div style="display:inline-block;width:4%;cursor:pointer;" >';
                    echo '<div style="width:90%;border:1px solid black;text-align:center;box-sizing:border-box;" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].setPeriodo(\''.$m[0].'\',\''.$m[1].'\',\'m\')">'.$mese.'</div>';
                echo '</div>';
            }

            echo '<div style="display:inline-block;width:0.5%;"></div>';

            echo '<div style="display:inline-block;width:2.5%;cursor:pointer;" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].setPeriodo(\'01-01\',\'03-31\',\'t\');">';
                echo '<div style="width:70%;border:1px solid black;text-align:center;box-sizing:border-box;">1</div>';
            echo '</div>';
            echo '<div style="display:inline-block;width:2.5%;cursor:pointer;" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].setPeriodo(\'04-01\',\'06-30\',\'t\');">';
                echo '<div style="width:70%;border:1px solid black;text-align:center;box-sizing:border-box;">2</div>';
            echo '</div>';
            echo '<div style="display:inline-block;width:2.5%;cursor:pointer;" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].setPeriodo(\'07-01\',\'09-30\',\'t\');">';
                echo '<div style="width:70%;border:1px solid black;text-align:center;box-sizing:border-box;">3</div>';
            echo '</div>';
            echo '<div style="display:inline-block;width:2.5%;cursor:pointer;" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].setPeriodo(\'10-01\',\'12-31\',\'t\');">';
                echo '<div style="width:70%;border:1px solid black;text-align:center;box-sizing:border-box;">4</div>';
            echo '</div>';

            echo '<div style="display:inline-block;width:0.5%;"></div>';

            echo '<div style="display:inline-block;width:4%;cursor:pointer;" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].setPeriodo(\'01-01\',\'06-30\',\'s\');">';
                echo '<div style="width:90%;border:1px solid black;text-align:center;box-sizing:border-box;">1-6</div>';
            echo '</div>';
            echo '<div style="display:inline-block;width:4%;cursor:pointer;" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].setPeriodo(\'07-01\',\'12-31\',\'s\');">';
                echo '<div style="width:90%;border:1px solid black;text-align:center;box-sizing:border-box;">7-12</div>';
            echo '</div>';

            echo '<div style="display:inline-block;width:0.5%;"></div>';

            echo '<div style="display:inline-block;width:4%;cursor:pointer;" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].setPeriodo(\'01-01\',\'12-31\',\'a\');">';
                echo '<div style="width:90%;border:1px solid black;text-align:center;box-sizing:border-box;">1-12</div>';
            echo '</div>';

        echo '</div>';

    }

    function customDraw() {

        echo '<div style="height:13%;">';
                $this->navigator();
        echo '</div>';

        echo '<div style="width:100%;height:87%;" >';

            echo '<div style="display:inline-block;width:18%;height:100%;border-right:1px solid black;padding:3px;box-sizing:border-box;vertical-align:top;">';

                echo '<div id="c2r_navigator_range" style="position:relative;border:2px solid transparent;box-sizing:border-box;padding:5px;margin-top:10px;">';

                    echo '<div style="">';
                        echo '<div style="display:inline-block;width:15%;">Da</div>';
                        echo '<div style="display:inline-block;width:85%;">';
                            echo '<input id="c2r_periodo_opzioni_inizio" type="date" style="width:90%;" />';
                        echo '</div>';
                    echo '</div>';

                    echo '<div style="margin-top:10px;" >';
                        echo '<div style="display:inline-block;width:15%;">A</div>';
                        echo '<div style="display:inline-block;width:85%;">';
                            echo '<input id="c2r_periodo_opzioni_fine" type="date" style="width:90%;" />';
                        echo '</div>';
                    echo '</div>';

                echo '</div>';

                echo '<div id="c2r_navigator_error" style="height:20px;color:red;text-align:center;font-weight:bold;" >';
                    //errore nelle date - reparti - marche
                echo '</div>';

                echo '<div style="margin-top:5px;" >';

                    $this->drawModuli();

                echo '</div>';

            echo '</div>';

            echo '<div style="display:inline-block;width:82%;height:100%;padding:5px;box-sizing:border-box;vertical-align:top;">';

                foreach ($this->moduli as $km=>$m) {
                    if ($m) {
                        echo '<div id="c2r_main_'.$km.'" style="width:100%;height:100%;display:none;" ></div>';
                    }
                }

            echo '</div>';


        echo '</div>';

    }

    function drawModuli() {

        //#############################################
        //Visualizzazione moduli in base all'abilitazione
        //VISUALIZZAZIONE IN BASE AL MACROREPARTO
        //#############################################

        if ($this->moduli['fatturato_S']) {

            echo '<div>';

                echo '<hr style="width:80%;" />';

                echo '<div style="height:30px;" >';

                    echo '<div style="display:inline-block;width:80%;text-align:center;vertical-align:top;">';
                        echo '<div class="divButton" style="position:relative;width:90%;top:2px;" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].c2rMainToggle(\'fatturato_S\')">Fatturato</div>';
                    echo '</div>';

                    echo '<div style="display:inline-block;width:20%;height:100%;text-align:center;vertical-align:top;">';
                        echo '<img id="c2r_reload_fatturato_S" style="position:relative;width:20px;height:20px;top:50%;transform:translate(0px,-50%);cursor:pointer;visibility:hidden;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/c2r/img/reload.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].c2rReload(\'fatturato_S\')"/>';
                    echo '</div>';

                echo '</div>';

            echo '</div>';

            //echo '<div style="border:1px solid black;box-sizing:border-box;padding:2px;background-color:#f5e9d4;" >';
            echo '<div style="box-sizing:border-box;padding:2px;height:30px;" >';
                
                /*
                echo '<div>';
                    echo '<div id="c2r_modulo_data_fatturato_S" style="text-align:left;font-weight:bold;font-size:0.9em;min-height:20px;"></div>';
                echo '</div>';

                echo '<div>';
                    echo '<div id="c2r_modulo_reparti_fatturato_S" style="text-align:left;font-weight:bold;font-size:0.9em;min-height:20px;"></div>';
                echo '</div>';
                */

                echo '<div style="display:inline-block;width:80%;vertical-align:top;">';
                    echo '<input id="c2rTableLabel" type="checkbox" checked="checked" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].c2r_totToggle();" />';
                    echo '<span style="margin-left:10px;font-weight:bold;font-size:1.1em;" >Totali</span>';
                echo '</div>';

                echo '<div style="display:inline-block;width:20%;height:100%;text-align:center;vertical-align:top;">';
                    echo '<img id="c2r_del_fatturato_S"  style="position:relative;width:20px;height:20px;top:50%;transform:translate(0px,-50%);cursor:pointer;visibility:hidden;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/c2r/img/annulla.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].c2rDelete(\'fatturato_S\')" />';
                echo '</div>';

            echo '</div>';

        }

        if ($this->moduli['prod_S']) {

            echo '<div>';
                echo '<hr style="width:80%;" />';

                echo '<div style="height:30px;" >';

                    echo '<div style="display:inline-block;width:80%;text-align:center;vertical-align:top;">';
                        echo '<div class="divButton" style="position:relative;width:90%;top:2px;" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].c2rMainToggle(\'prod_S\')">Produttività</div>';
                    echo '</div>';

                    echo '<div style="display:inline-block;width:20%;height:100%;text-align:center;vertical-align:top;">';
                        echo '<img id="c2r_reload_prod_S"  style="position:relative;width:20px;height:20px;top:50%;transform:translate(0px,-50%);cursor:pointer;visibility:hidden;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/c2r/img/reload.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].c2rReload(\'prod_S\')" />';
                    echo '</div>';

                echo '</div>';

                //echo '<div style="border:1px solid black;box-sizing:border-box;padding:2px;background-color:#f5e9d4;" >';
                echo '<div style="box-sizing:border-box;padding:2px;height:30px;" >';

                    echo '<div style="display:inline-block;width:80%;vertical-align:top;">';
                        echo '<select style="font-weight: bold;font-size: 1.1em;margin-left:5px;" id="c2rProdTipo" onchange="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].c2r_prodTipoToggle();" ';
                            if ($this->config['tipo']=='personale') echo 'disabled="disabled"';
                        echo '>';
                            echo '<option value="standard">Standard</option>';
                            echo '<option value="nospec">NO Speciale</option>';
                            echo '<option value="noescl">NO Esclusioni</option>';
                            echo '<option value="flat">Flat</option>';
                            echo '<option value="nominale">Nominale</option>';
                        echo '</select>';
                    echo '</div>';

                    echo '<div style="display:inline-block;width:20%;height:100%;text-align:center;vertical-align:top;">';
                        echo '<img id="c2r_del_prod_S"  style="position:relative;width:20px;height:20px;top:50%;transform:translate(0px,-50%);cursor:pointer;visibility:hidden;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/c2r/img/annulla.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].c2rDelete(\'prod_S\')" />';
                    echo '</div>';

                echo '</div>';

            echo '</div>';
        }

        if ($this->moduli['budget_S']) {

            echo '<div>';
                echo '<hr style="width:80%;" />';

                echo '<div style="height:30px;" >';

                    echo '<div style="display:inline-block;width:80%;text-align:center;vertical-align:top;">';
                        echo '<div class="divButton" style="position:relative;width:90%;top:2px;" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].c2rMainToggle(\'budget_S\');">Budget</div>';
                    echo '</div>';

                    echo '<div style="display:inline-block;width:20%;height:100%;text-align:center;vertical-align:top;">';
                        echo '<img id="c2r_reload_budget_S"  style="position:relative;width:20px;height:20px;top:50%;transform:translate(0px,-50%);cursor:pointer;visibility:hidden;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/c2r/img/reload.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].c2rReload(\'budget_S\')" />';
                    echo '</div>';

                echo '</div>';

                echo '<div style="box-sizing:border-box;padding:2px;height:30px;" >';

                    echo '<div style="display:inline-block;width:80%;vertical-align:top;">';
                    echo '</div>';

                    echo '<div style="display:inline-block;width:20%;height:100%;text-align:center;vertical-align:top;">';
                        echo '<img id="c2r_del_budget_S"  style="position:relative;width:20px;height:20px;top:50%;transform:translate(0px,-50%);cursor:pointer;visibility:hidden;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/c2r/img/annulla.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].c2rDelete(\'budget_S\')" />';
                    echo '</div>';

                echo '</div>';

            echo '</div>';
        }

        if ($this->moduli['giac_M']) {

            echo '<div style="height:80px;">';
                echo '<hr style="width:80%;" />';

                echo '<div style="height:30px;" >';

                    echo '<div style="display:inline-block;width:80%;text-align:center;vertical-align:top;">';
                        echo '<div class="divButton" style="position:relative;width:90%;top:2px;" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].c2rMainToggle(\'giac_M\');">Giacenza</div>';
                    echo '</div>';

                    echo '<div style="display:inline-block;width:20%;height:100%;text-align:center;vertical-align:top;">';
                        echo '<img id="c2r_reload_giac_M"  style="position:relative;width:20px;height:20px;top:50%;transform:translate(0px,-50%);cursor:pointer;visibility:hidden;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/c2r/img/reload.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].c2rReload(\'giac_M\')" />';
                    echo '</div>';

                echo '</div>';

                echo '<div style="box-sizing:border-box;padding:2px;height:30px;" >';

                    echo '<div style="display:inline-block;width:80%;vertical-align:top;">';
                        echo '<div style="font-weight:bold;">';
                            echo '<span>Obsolescenza:</span>';
                            echo '<button style="margin-left:10px;" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].c2rm_deltaTrToggle();">delta</button>';
                        echo '</div>';
                        echo '<select id="c2rMagObso" style="font-weight: bold;font-size: 1.1em;margin-left:5px;" >';
                            echo '<option value="36">3 anni</option>';
                            echo '<option value="24">2 anni</option>';
                            echo '<option value="12">1 anno</option>';
                            echo '<option value="6">6 mesi</option>';
                            echo '<option value="3">3 mesi</option>';
                        echo '</select>';
                    echo '</div>';

                    echo '<div style="display:inline-block;width:20%;height:100%;text-align:center;vertical-align:top;">';
                        echo '<img id="c2r_del_giac_M"  style="position:relative;width:20px;height:20px;top:50%;transform:translate(0px,-50%);cursor:pointer;visibility:hidden;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/c2r/img/annulla.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].c2rDelete(\'giac_M\')" />';
                    echo '</div>';

                echo '</div>';

            echo '</div>';
        }

        if ($this->moduli['ven_M']) {

            echo '<div>';
                echo '<hr style="width:80%;" />';

                echo '<div style="height:30px;" >';

                    echo '<div style="display:inline-block;width:80%;text-align:center;vertical-align:top;">';
                        echo '<div class="divButton" style="position:relative;width:90%;top:2px;" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].c2rMainToggle(\'ven_M\');">Vendite</div>';
                    echo '</div>';

                    echo '<div style="display:inline-block;width:20%;height:100%;text-align:center;vertical-align:top;">';
                        echo '<img id="c2r_reload_ven_M"  style="position:relative;width:20px;height:20px;top:50%;transform:translate(0px,-50%);cursor:pointer;visibility:hidden;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/c2r/img/reload.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].c2rReload(\'ven_M\')" />';
                    echo '</div>';

                echo '</div>';

                echo '<div style="box-sizing:border-box;padding:2px;height:30px;" >';

                    echo '<div style="display:inline-block;width:80%;vertical-align:top;">';
                    echo '</div>';

                    echo '<div style="display:inline-block;width:20%;height:100%;text-align:center;vertical-align:top;">';
                        echo '<img id="c2r_del_ven_M"  style="position:relative;width:20px;height:20px;top:50%;transform:translate(0px,-50%);cursor:pointer;visibility:hidden;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/c2r/img/annulla.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].c2rDelete(\'ven_M\')" />';
                    echo '</div>';

                echo '</div>';

            echo '</div>';
        }

        if ($this->moduli['acq_M']) {

            echo '<div>';
                echo '<hr style="width:80%;" />';

                echo '<div style="height:30px;" >';

                    echo '<div style="display:inline-block;width:80%;text-align:center;vertical-align:top;">';
                        echo '<div class="divButton" style="position:relative;width:90%;top:2px;" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].c2rMainToggle(\'acq_M\');">Acquisti</div>';
                    echo '</div>';

                    echo '<div style="display:inline-block;width:20%;height:100%;text-align:center;vertical-align:top;">';
                        echo '<img id="c2r_reload_acq_M"  style="position:relative;width:20px;height:20px;top:50%;transform:translate(0px,-50%);cursor:pointer;visibility:hidden;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/c2r/img/reload.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].c2rReload(\'acq_M\')" />';
                    echo '</div>';

                echo '</div>';

                echo '<div style="box-sizing:border-box;padding:2px;height:30px;" >';

                    echo '<div style="display:inline-block;width:80%;vertical-align:top;">';
                    echo '</div>';

                    echo '<div style="display:inline-block;width:20%;height:100%;text-align:center;vertical-align:top;">';
                        echo '<img id="c2r_del_acq_M"  style="position:relative;width:20px;height:20px;top:50%;transform:translate(0px,-50%);cursor:pointer;visibility:hidden;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/c2r/img/annulla.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].c2rDelete(\'acq_M\')" />';
                    echo '</div>';

                echo '</div>';

            echo '</div>';
        }

        echo '<input id="c2rDefault" type="hidden" data-default="" value="" />';
        echo '<script type="text/javascript">';
            echo 'var temp='.json_encode($this->config).';';
            echo '$("#c2rDefault").data("default",temp);'; 
        echo '</script>';

    }

}
?>