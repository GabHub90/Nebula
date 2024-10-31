<?php
include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/core/divo/divo.php');

class pitstopLista {

    protected $info=array(
        "ID"=>"",
        "reparto"=>"",
        "dms"=>"",
        "do_creazione"=>"",
        "ut_creazione"=>"",
        "do_elaborazione"=>"",
        "ut_elaborazione"=>"",
        "destinazione"=>"",
        "id_destinazione"=>"",
        "nota"=>"",
        "allegati"=>"",
        "stato"=>"nuova",
        "originale"=>""
    );

    protected $elementi=array(
        "0"=>array(
            "mark_des"=>"senza etichetta",
            "pack"=>false,
            "elem"=>array()
        )
    );

    protected $originale=array(
        "0"=>array(
            "mark_des"=>"senza etichetta",
            "pack"=>false,
            "elem"=>array()
        )
    );

    protected $wh;
    protected $galileo;

    function __construct($a,$galileo) {
        
        $this->galileo=$galileo;

        if (isset($a['ID']) && $a['ID']=!"") {
            //carica l'header della lista da DB

            $row=array();

            $this->fillHead($row);
            $this->getLines();
        }
        else {
            $this->fillHead($a);
            $this->info['do_creazione']=date('Ymd:H:i');
            $this->info['do_elaborazione']=date('Ymd:H:i');

            //solo TEST in realtà essendo una lista nuova non ha linee
            //$this->getLines();
            //////////////////////
        }

    }

    function fillHead($row) {

        foreach ($this->info as $k=>$v) {
            if (array_key_exists($k,$row)) $this->info[$k]=$row[$k];
        }
    }

    function getLines() {

        $linee=array();

        /*TEST
        $linee=array(
            array(
                "lista"=>"12",
                "ID"=>1,
                "precodice"=>"V",
                "codice"=>"1J0819655AHRSM",
                "descrizione"=>"Filtro antipolline",
                "qta"=>"1",
                "stato"=>"dms",
                "mark"=>"1",
                "mark_des"=>"antipolline",
                "dest_lam"=>"",
                "pack"=>'{"marca":"V","modello":"1J1764","evento":"FANT"}',
                "edit"=>0,
                "nota"=>"In casa",
                "do_nota"=>"20220929:15:21",
                "dispo"=>0
            ),
            array(
                "lista"=>"12",
                "ID"=>2,
                "precodice"=>"",
                "codice"=>"",
                "descrizione"=>"igienizzante",
                "qta"=>"1",
                "stato"=>"",
                "mark"=>"1",
                "mark_des"=>"antipolline",
                "dest_lam"=>"",
                "pack"=>'{"marca":"V","modello":"1J1764","evento":"FANT"}',
                "edit"=>0,
                "nota"=>"",
                "do_nota"=>"",
                "dispo"=>0
            ),
            array(
                "lista"=>"12",
                "ID"=>3,
                "precodice"=>"",
                "codice"=>"",
                "descrizione"=>"motore",
                "qta"=>"1",
                "stato"=>"generico",
                "mark"=>"",
                "mark_des"=>"",
                "dest_lam"=>"",
                "pack"=>'',
                "edit"=>0,
                "nota"=>"Disponibile Verona",
                "do_nota"=>"20220929:15:21",
                "dispo"=>0
            )
        );*/
        //ENDTEST

        foreach ($linee as $k=>$l) {

            if ($l['mark']=='') $l['mark']='0';

            if (!array_key_exists($l['mark'],$this->elementi)) {
                $this->elementi[$l['mark']]=array(
                    "mark_des"=>$l['mark_des'],
                    "pack"=>false,
                    "elem"=>array()
                );
            }

            $this->elementi[$l['mark']]["elem"][$l['ID']]=$l;

            if (!$this->elementi[$l['mark']]["pack"] || $this->elementi[$l['mark']]["pack"]['evento']!='pserror') {

                if ($l['pack']!="") {

                    $obj=json_decode($l['pack'],true);

                    if ($obj) {
                        if ($this->elementi[$l['mark']]["pack"] && $this->elementi[$l['mark']]["pack"]['evento']!=$obj['evento']) {
                            $this->elementi[$l['mark']]["pack"]['evento']='pserror';
                        }
                        else $this->elementi[$l['mark']]["pack"]=$obj;
                    }
                }
            }
        }

    }

    function draw() {

        echo '<div style="position:relative;width:100%;height:7%;border:1px solid black;border-radius:5px;box-sizing:border-box;background-color:#c1e9c1;margin-top:1%;padding:2px;" >';
            $this->drawHead();
        echo '</div>';

        echo '<div style="position:relative;width:100%;height:92%;" >';

            echo '<div style="position:relative;display:inline-block;width:50%;height:100%;border-right:1px solid black;box-sizing:border-box;vertical-align:top;padding:3px;" >';
                $this->drawbody();
            echo '</div>';

            echo '<div style="position:relative;display:inline-block;width:50%;height:100%;box-sizing:border-box;vertical-align:top;padding:3px;" >';

                $divo=new Divo('pslista','5%','94%',true);

                $divo->setBk('#9aebeb');
        
                $css=array(
                    "font-weight"=>"bold",
                    "font-size"=>"1.2em",
                    "margin-left"=>"15px",
                    "margin-top"=>"0px"
                );

                $txt="";

                $txt.='<div style="position:relative;height:25%;border-bottom:2px solid black;">';

                    $txt.='<div style="margin-top:15px;margin-bottom:5px;width:40%;">';
                        $txt.='<button style="width:100%;text-align:center;">Nota testata</button>';
                    $txt.='</div>';

                    $txt.='<div style="position:relative;margin-top:15px;margin-bottom:5px;" >';
                        $txt.='<div style="position:relative;display:inline-block;vertical-align:top;width:47%;" >';
                            $txt.='<div style="position:relative;display:inline-block;vertical-align:top;width:30%;">';
                                $txt.='Etichetta:';
                            $txt.='</div>';
                            $txt.='<input id="pitstop_newlista_form_etichetta" style="width:70%;" type="text" />';
                        $txt.='</div>';
                        $txt.='<div style="position:relative;display:inline-block;vertical-align:top;">';  
                            $txt.='<button style="margin-left:10px;width:25px;text-align:center;">-</button>';
                            $txt.='<button style="margin-left:10px;width:120px;text-align:center;">Sposta materiale</button>';
                        $txt.='</div>';
                    $txt.='</div>';

                    $txt.='<div style="position:relative;margin-top:15px;margin-bottom:5px;" >';
                        $txt.='<div style="position:relative;display:inline-block;vertical-align:top;width:47%;" >';
                            $txt.='<input id="pitstop_newlista_form_codice" style="width:100%;" type="text" />';
                        $txt.='</div>';
                        $txt.='<div style="position:relative;display:inline-block;vertical-align:top;width:25%;" >';
                            $txt.='<img style="width:23px;height:23px;cursor:pointer;margin-left:10px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/pitstop/img/cerca.png" />';
                            $txt.='<img style="width:20px;height:20px;cursor:pointer;margin-left:15px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/pitstop/img/add.png" onclick="window._pitstopLista.addGenerico();"/>';
                            $txt.='<img style="width:20px;height:20px;cursor:pointer;margin-left:15px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/pitstop/img/cambia.png" />';
                        $txt.='</div>';
                        $txt.='<div style="position:relative;display:inline-block;vertical-align:top;width:25%;" >';
                            $txt.='<button style="">Da catalogo</button>';
                        $txt.='</div>';
                    $txt.='</div>';

                $txt.='</div>';

                $txt.='<div style="position:relative;height:74%;">';

                    /*$filename="\\\\10.55.99.4\\etka\\Userdata\\ETKAORDERS\\VA\\bidioli1.xml";
                    $handle = fopen($filename, "r");
                    $contents = fread($handle, filesize($filename));

                    $xml = simplexml_load_string($contents, "SimpleXMLElement", LIBXML_NOCDATA);
                    $json = json_encode($xml);
                    $array = json_decode($json,TRUE);

                    $txt.=json_encode($array);*/

                $txt.='</div>';

                $divo->add_div('Operazioni','black',0,"",$txt,0,$css);

                $txt="";
                
                $divo->add_div('Destinazione','black',0,"",$txt,0,$css);

                if ($this->info['originale']!="") {
                    //############################
                    //alimentare ORIGINALE
                    //############################

                    $txt="";
                    $divo->add_div('Originale','black',0,"",$txt,0,$css);

                }

                $divo->build();

                $divo->draw();

                unset($txt);
                unset($divo);

            echo '</div>';

        echo '</div>';

        echo '<script type="text/javascript" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/pitstop/classi/lista.js" ></script>';
        echo '<script type="text/javascript">';
            echo 'var temp='.json_encode($this->info).';';
            echo 'window._pitstopLista= new window._pitstopListaClass(temp,\'pitstop_utilityDivBody\');';
        echo '</script>';
    }

    function drawHead() {

        echo '<div style="position:relative;">';

            echo '<div style="position:relative;display:inline-block;vertical-align:top;width:15%;" >';
                echo '<span style="font-weight:bold;font-size:0.9em;" >ID:</span>';
                echo '<span style="margin-left:5px;" >'.$this->info['ID'].'('.$this->info['stato'].')</span>';
            echo '</div>';

            echo '<div style="position:relative;display:inline-block;vertical-align:top;width:10%;" >';
                echo '<span style="font-weight:bold;font-size:0.9em;" >Rep:</span>';
                echo '<span style="margin-left:5px;" >'.$this->info['reparto'].'</span>';
            echo '</div>';

            echo '<div style="position:relative;display:inline-block;vertical-align:top;width:22%;" >';
                echo '<span style="font-weight:bold;font-size:0.9em;" >'.$this->info['ut_creazione'].'</span>';
                echo '<span style="margin-left:5px;" >'.mainFunc::gab_todata(substr($this->info['do_creazione'],0,8)).' - '.substr($this->info['do_creazione'],9,5).'</span>';
            echo '</div>';

            echo '<div style="position:relative;display:inline-block;vertical-align:top;width:27%;" >';
                echo '<img style="width:15px;height:12px;top:3px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/main/img/blackarrowR.png" />';
                echo '<span style="font-weight:bold;font-size:0.9em;margin-left:5px;" >'.$this->info['ut_elaborazione'].'</span>';
                echo '<span style="margin-left:5px;" >'.mainFunc::gab_todata(substr($this->info['do_elaborazione'],0,8)).' - '.substr($this->info['do_elaborazione'],9,5).'</span>';
            echo '</div>';

            echo '<div style="position:relative;display:inline-block;vertical-align:top;width:25%;" >';
                echo '<img style="width:15px;height:12px;top:3px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/main/img/blackarrowR.png" />';
                echo '<span style="margin-left:5px;" >'.$this->info['destinazione'].' - '.$this->info['id_destinazione'].'</span>';
            echo '</div>';

        echo '</div>';

        echo '<div style="position:relative;">';

            echo '<div style="position:relative;display:inline-block;vertical-align:top;width:95%;" >';
                echo '<span style="font-weight:bold;font-size:0.9em;" >Nota:</span>';
                echo '<span style="margin-left:5px;" >'.$this->info['nota'].'</span>';
            echo '</div>';

        echo '</div>';

    }

    function drawBody() {

        echo '<div style="position:relative;width:95%;">';

            foreach ($this->elementi as $mark=>$m) {
                
                echo '<div style="background-color:#cccccc;border:1px solid cadetblue;border-radius:5px;padding:2px;font-weight:bold;height:25px;" >';

                    echo '<div style="position:relative;display:inline-block;width:40%;top:50%;transform: translate(0px, -50%);" >';
                        echo '<input type="checkbox" />';
                        echo '<span style="margin-left:5px;" >'.$m['mark_des'].'</span>';
                    echo '</div>';

                    echo '<div style="position:relative;display:inline-block;width:20%;vertical-align:top;" >';
                        if ($m['pack']) {
                            echo '<div style="position:relative;width:85%;padding:1px;border:1px solid black;border-radius:8px;text-align:center;" >';
                                echo '<img style="width:18px;height:18px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/pitstop/img/pacchetto.png" />';
                                echo '<span style="margin-left:10px;font-weight:normal;">'.$m['pack']['evento'].'</span>';
                            echo '</div>';
                        }
                    echo '</div>';

                    echo '<div style="position:relative;display:inline-block;width:20%;top:50%;transform: translate(0px, -80%);" >';
                        echo '<img style="width:15px;height:12px;top:3px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/main/img/blackarrowR.png" />';
                    echo '</div>';

                    echo '<div style="position:relative;display:inline-block;width:18%;text-align:right;top:50%;transform: translate(0px, -50%);" >';
                        echo '<input id="pitstop_lista_etichetta_'.$mark.'" name="pitstop_lista_etichetta" type="radio" value="'.$mark.'" />';
                    echo '</div>';
                echo '</div>';

                foreach ($m['elem'] as $k=>$e) {

                    //##################################
                    //verifica codice con DMS (se non è generico ovviamente)
                    //verifica disponibilità
                    //##################################

                    echo '<div style="position:relative;margin-top:5px;margin-bottom:5px;border-bottom:1px solid #777777;padding-bottom:3px;" >';

                        echo '<div style="position:relative;display:inline-block;vertical-align:top;width:5%;" >';
                            echo '<input id="pitstop_lista_elemento_'.$e['ID'].'" type="checkbox" style="position:relative;left:2px;" value="'.$e['ID'].'" />';
                        echo '</div>';
                        echo '<div style="position:relative;display:inline-block;vertical-align:top;width:35%;" >';
                            echo '<div>'.$e['precodice'].' - '.$e['codice'].'</div>';
                            echo '<div style="';
                                if ($e['stato']=='generico') {
                                    echo 'font-weight:bold;color:red;';
                                }
                            echo '">'.$e['descrizione'].'</div>';
                        echo '</div>';

                        echo '<div style="position:relative;display:inline-block;vertical-align:top;width:60%;" >';

                            echo '<div style="width:100%;" >';
                                echo '<div style="position:relative;display:inline-block;vertical-align:top;width:12%;" >';
                                    echo $e['qta'];
                                echo '</div>';

                                echo '<div style="position:relative;display:inline-block;vertical-align:top;width:10%;" >';
                                    echo '<div style="position:relative;border:1px solid black;border-radius:50%;background-color:'.($e['dispo']>0?'green':'red').';width:15px;height:15px;"></div>';
                                echo '</div>';

                                echo '<div style="position:relative;display:inline-block;vertical-align:top;width:30%;" >';
                                    echo $e['stato'];
                                echo '</div>';
                                echo '<div style="position:relative;display:inline-block;vertical-align:top;width:35%;font-size:0.9em;" >';
                                    //echo '<img style="width:20px;height:20px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/pitstop/img/cambia.png" />';
                                    if ($e['pack']!="") {
                                        $obj=json_decode($e['pack'],true);
                                        if ($obj) {
                                            echo $obj['marca'].' - '.$obj['modello'].' - '.$obj['evento'];
                                        }
                                    }
                                echo '</div>';
                            echo '</div>';

                            echo '<div style="width:100%;" >';
                                echo '<div style="position:relative;display:inline-block;vertical-align:top;width:95%;color:#c600ff;" >';
                                    if ($e['nota']!="") {
                                        echo '<span style="font-size:0.9em">'.mainFunc::gab_todata(substr($e['do_nota'],0,8)).' '.substr($e['do_nota'],9,5).'</span>';
                                        echo ' - '.$e['nota'];
                                    }
                                echo '</div>';
                            echo '</div>';

                        echo '</div>';

                    echo '</div>';
                }
            }
            
        echo '</div>';

    }

}

?>