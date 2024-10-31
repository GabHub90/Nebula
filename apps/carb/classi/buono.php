<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/core/chekko/chekko.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/core/divutil/divutil.php');

class carbBuono extends chekko {

    //new - fill - cash - print
    protected $ambito="new";

    protected $utente="";

    protected $responsabili=array();

    protected $stati=array(
        "creato"=>"Creato",
        "dacompletare"=>" da Completare",
        "daris"=>"da Risarcire",
        "stampato"=>"Stampato"
    );

    //contiene il buono caricato (per i campi che non sono gestiti dal FORM)
    protected $buono=array();

    protected $galileo;

    function __construct($galileo) {

        $this->galileo=$galileo;
        
        parent::__construct('carbBuono');

        $this->chk_fields=array(
            "ID"=>array(
                "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
            "dms"=>array(
                "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
            "veicolo"=>array(
                "js_chk_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
            "importo"=>array(
                "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>"pieno"),
                "js_chk_ifreq"=>array("campo"=>"",""=>"==","val"=>"")
            ),
            "reparto"=>array(
                "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
            "id_rich"=>array(
                "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
            "id_esec"=>array(
                "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
            "nota"=>array(
                "js_chk_req"=>array("codice"=>3,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ifreq"=>array("campo"=>"flag_nota","op"=>"==","val"=>"1")
            ),
            "flag_nota"=>array(
                "js_chk_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
            "gestione"=>array(
                "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
            "causale"=>array(
                "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
            "pieno"=>array(
                "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>"importo"),
                "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
            "flag_ris"=>array(
                "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
            "tipo_carb"=>array(
                "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
            "telaio"=>array(
                "js_chk_req"=>array("codice"=>1,"anor"=>"targa","anand"=>"","anxor"=>""),
                "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
            "targa"=>array(
                "js_chk_req"=>array("codice"=>1,"anor"=>"telaio","anand"=>"","anxor"=>""),
                "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
            "des_veicolo"=>array(
                "js_chk_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
            "stato"=>array(
                "js_chk_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
            "d_creazione"=>array(
                "js_chk_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            )
        );

        $this->tipi=array(
            "ID"=>"none",
            "dms"=>"none",
            "veicolo"=>"none",
            "importo"=>"intpos",
            "reparto"=>"none",
            "id_rich"=>"none",
            "id_esec"=>"none",
            "nota"=>"text",
            "gestione"=>"none",
            "causale"=>"none",
            "pieno"=>"none",
            "flag_ris"=>"none",
            "tipo_carb"=>"none",
            "telaio"=>"none",
            "targa"=>"none",
            "des_veicolo"=>"none",
            "flag_nota"=>"none",
            "stato"=>"none",
            "d_creazione"=>"none"
        );

        $this->expo=array(
            "ID"=>"",
            "dms"=>"",
            "veicolo"=>"",
            "importo"=>"",
            "reparto"=>"",
            "id_rich"=>"",
            "id_esec"=>"",
            "nota"=>"",
            "gestione"=>"",
            "causale"=>"",
            "pieno"=>"pieno",
            "flag_ris"=>"",
            "tipo_carb"=>"",
            "telaio"=>"",
            "targa"=>"",
            "des_veicolo"=>"",
            "stato"=>"",
            "d_creazione"=>""
        );

        $this->conv=array(
            "ID"=>"ID",
            "dms"=>"dms",
            "veicolo"=>"veicolo",
            "importo"=>"importo",
            "reparto"=>"reparto",
            "id_rich"=>"id_rich",
            "id_esec"=>"id_esec",
            "nota"=>"nota",
            "gestione"=>"gestione",
            "causale"=>"causale",
            "pieno"=>"pieno",
            "flag_ris"=>"flag_ris",
            "tipo_carb"=>"tipo_carb",
            "telaio"=>"telaio",
            "targa"=>"targa",
            "des_veicolo"=>"des_veicolo",
            "stato"=>"stato",
            "d_creazione"=>"d_creazione"
        );

        $this->mappa=array(
            "ID"=>array(
                "prop"=>array(
                    "input"=>"input",
                    "tipo"=>"hidden",
                    "maxlenght"=>"",
                    "options"=>array(),
                    "rows"=>"",
                    "default"=>"",
                    "disabled"=>false
                ),
                "css"=>array()
            ),
            "dms"=>array(
                "prop"=>array(
                    "input"=>"input",
                    "tipo"=>"hidden",
                    "maxlenght"=>"",
                    "options"=>array(),
                    "rows"=>"",
                    "default"=>"",
                    "disabled"=>false
                ),
                "css"=>array()
            ),
            "veicolo"=>array(
                "prop"=>array(
                    "input"=>"input",
                    "tipo"=>"hidden",
                    "maxlenght"=>"",
                    "options"=>array(),
                    "rows"=>"",
                    "default"=>"0",
                    "disabled"=>false
                ),
                "css"=>array()
            ),
            "importo"=>array(
                "prop"=>array(
                    "input"=>"input",
                    "tipo"=>"text",
                    "maxlenght"=>"",
                    "options"=>array(),
                    "rows"=>"",
                    "default"=>"",
                    "disabled"=>false
                ),
                "css"=>array()
            ),
            "reparto"=>array(
                "prop"=>array(
                    "input"=>"input",
                    "tipo"=>"hidden",
                    "maxlenght"=>"",
                    "options"=>array(),
                    "rows"=>"",
                    "default"=>"",
                    "disabled"=>false
                ),
                "css"=>array()
            ),
            "id_rich"=>array(
                "prop"=>array(
                    "input"=>"select",
                    "tipo"=>"",
                    "maxlenght"=>"",
                    "options"=>array(),
                    "rows"=>"",
                    "default"=>"",
                    "disabled"=>false
                ),
                "css"=>array()
            ),
            "id_esec"=>array(
                "prop"=>array(
                    "input"=>"input",
                    "tipo"=>"hidden",
                    "maxlenght"=>"",
                    "options"=>array(),
                    "rows"=>"",
                    "default"=>"",
                    "disabled"=>false
                ),
                "css"=>array()
            ),
            "nota"=>array(
                "prop"=>array(
                    "input"=>"input",
                    "tipo"=>"text",
                    "maxlenght"=>"",
                    "options"=>array(),
                    "rows"=>"",
                    "default"=>"",
                    "disabled"=>false
                ),
                "css"=>array()
            ),
            "gestione"=>array(
                "prop"=>array(
                    "input"=>"input",
                    "tipo"=>"hidden",
                    "maxlenght"=>"",
                    "options"=>array(),
                    "rows"=>"",
                    "default"=>"NEBULA",
                    "disabled"=>false
                ),
                "css"=>array()
            ),
            "causale"=>array(
                "prop"=>array(
                    "input"=>"select",
                    "tipo"=>"",
                    "maxlenght"=>"",
                    "options"=>array(),
                    "rows"=>"",
                    "default"=>"",
                    "disabled"=>false
                ),
                "css"=>array()
            ),
            "pieno"=>array(
                "prop"=>array(
                    "input"=>"input",
                    "tipo"=>"checkbox",
                    "maxlenght"=>"",
                    "options"=>array(),
                    "rows"=>"",
                    "default"=>"",
                    "disabled"=>false
                ),
                "css"=>array()
            ),
            "flag_ris"=>array(
                "prop"=>array(
                    "input"=>"input",
                    "tipo"=>"checkbox",
                    "maxlenght"=>"",
                    "options"=>array(),
                    "rows"=>"",
                    "default"=>"0",
                    "disabled"=>false
                ),
                "css"=>array()
            ),
            "tipo_carb"=>array(
                "prop"=>array(
                    "input"=>"select",
                    "tipo"=>"",
                    "maxlenght"=>"",
                    "options"=>array(
                        "D"=>"Diesel",
                        "B"=>"Benzina",
                        "M"=>"Metano"
                    ),
                    "rows"=>"",
                    "default"=>"",
                    "disabled"=>false
                ),
                "css"=>array()
            ),
            "telaio"=>array(
                "prop"=>array(
                    "default"=>""
                )
            ),
            "targa"=>array(
                "prop"=>array(
                    "default"=>""
                )
            ),
            "des_veicolo"=>array(
                "prop"=>array(
                    "default"=>""
                )
            ),
            "flag_nota"=>array(
                "prop"=>array(
                    "default"=>"0"
                )
            ),
            "stato"=>array(
                "prop"=>array(
                    "default"=>""
                )
            ),
            "d_creazione"=>array(
                "prop"=>array(
                    "default"=>""
                )
            )
        );

        $this->galileo->executeGeneric('carb','getResponsabili',array(),'');

        if ($result=$this->galileo->getResult()) {

            $fid=$this->galileo->preFetch('carb');

            while($row=$this->galileo->getFetch('carb',$fid)) {
                $this->responsabili[$row['ID']]=$row;
            }
        }
    }

    function setAmbito($str) {
        $this->ambito=$str;
    }

    function setUtente($str) {
        $this->utente=$str;
    }

    function init($arr) {

        $this->buono=$arr;

        foreach ($this->conv as $k=>$m) {
            if (array_key_exists($k,$arr)) {
                $this->mappa[$this->conv[$k]]['prop']['default']=$arr[$k];
            }
        }
    }

    function loadCausali($arr) {

        foreach($arr as $k=>$a) {
            $this->mappa['causale']['prop']['options'][$a['codice']]=$a;
        }
    }

    function loadCollab($arr) {

        usort($arr, function ($item1, $item2) {
            return $item1['cognome'].$item1['nome'] <=> $item2['cognome'].$item2['nome'];
        });

        $this->mappa['id_rich']['prop']['options']=$arr;
    }

    function checkNota() {

        //la nota è obbligatoria se:
        //flag_ris==1 , gestione==TANICA , causale['nota']==1

        if ($this->mappa['flag_ris']['prop']['default']=="1") return "1";
        if ($this->mappa['gestione']['prop']['default']=="TANICA") return "1";
        if (isset($this->mappa['causale']['prop']['default']) && $this->mappa['causale']['prop']['default']!="") {
            if ($this->mappa['causale']['prop']['options'][$this->mappa['causale']['prop']['default']]['nota']=="1") return "1";
        }

        return "0";
    }

    function draw_js() {

        echo 'window._js_chk_'.$this->form_tag.'.kind_intpos=function(val,id) {';
            echo 'val=val.replace(",",".");';
            echo '$("#"+id).val(val);';
            echo 'if (parseInt(val)>0) return false;';
            echo 'return true;';
        echo '};';

        echo 'window._js_chk_'.$this->form_tag.'.pre_check=function() {';
            echo 'window._nebulaCarb.checkNota();';
        echo '};';

        echo 'window._nebulaCarb=new nebulaCarb(\''.$this->form_tag.'\');';

    }

    function draw_css() {}

    function draw() {

        echo '<script type="text/javascript" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/carb/core/carb.js" />';

        echo '<div id="carb_form" style="position:relative;width:100%;height:100%;box-sizing:border-box;padding-left:10px;" >';

            echo '<div style="font-weight:bold;font-size:1.2em;margin-top:10px;">';
                switch ($this->ambito) {
                    case 'new': echo 'Nuovo buono:';
                    break;
                    case 'fill': echo 'Buono da completare';
                    break;
                    case 'cash': echo 'Buono da risarcire:';
                    break;
                    case 'print': echo 'Buono da stampare:';
                    break;
                    case 'trash': echo 'Buono da annullare:';
                    break;
                }
            echo '</div>';

            echo '<input id="'.$this->form_tag.'_ID" class="js_chk_'.$this->form_tag.'" js_chk_'.$this->form_tag.'_tipo="ID" value="'.$this->mappa['ID']['prop']['default'].'" type="hidden" />';
            echo '<input id="'.$this->form_tag.'_dms" class="js_chk_'.$this->form_tag.'" js_chk_'.$this->form_tag.'_tipo="dms" value="'.$this->mappa['dms']['prop']['default'].'" type="hidden" />';
            echo '<input id="'.$this->form_tag.'_reparto" class="js_chk_'.$this->form_tag.'" js_chk_'.$this->form_tag.'_tipo="reparto" value="'.$this->mappa['reparto']['prop']['default'].'" type="hidden" />';
            echo '<input id="'.$this->form_tag.'_id_esec" class="js_chk_'.$this->form_tag.'" js_chk_'.$this->form_tag.'_tipo="id_esec" value="'.$this->mappa['id_esec']['prop']['default'].'" type="hidden" />';
            echo '<input id="'.$this->form_tag.'_gestione" class="js_chk_'.$this->form_tag.'" js_chk_'.$this->form_tag.'_tipo="gestione" value="'.$this->mappa['gestione']['prop']['default'].'" type="hidden" />';
            echo '<input id="'.$this->form_tag.'_stato" class="js_chk_'.$this->form_tag.'" js_chk_'.$this->form_tag.'_tipo="stato" value="'.$this->mappa['stato']['prop']['default'].'" type="hidden" />';
            echo '<input id="'.$this->form_tag.'_d_creazione" class="js_chk_'.$this->form_tag.'" js_chk_'.$this->form_tag.'_tipo="d_creazione" value="'.$this->mappa['d_creazione']['prop']['default'].'" type="hidden" />';

            echo '<input id="'.$this->form_tag.'_veicolo" class="js_chk_'.$this->form_tag.'" js_chk_'.$this->form_tag.'_tipo="veicolo" value="'.$this->mappa['veicolo']['prop']['default'].'" type="hidden" />';
            echo '<input id="'.$this->form_tag.'_telaio" class="js_chk_'.$this->form_tag.'" js_chk_'.$this->form_tag.'_tipo="telaio" value="'.$this->mappa['telaio']['prop']['default'].'" type="hidden" />';
            echo '<input id="'.$this->form_tag.'_targa" class="js_chk_'.$this->form_tag.'" js_chk_'.$this->form_tag.'_tipo="targa" value="'.$this->mappa['targa']['prop']['default'].'" type="hidden" />';
            echo '<input id="'.$this->form_tag.'_des_veicolo" class="js_chk_'.$this->form_tag.'" js_chk_'.$this->form_tag.'_tipo="des_veicolo" value="'.$this->mappa['des_veicolo']['prop']['default'].'" type="hidden" />';

            echo '<div style="position:relative;" >';
                echo '<div style="position:relative;display:inline-block;vertical-align:top;width:30%;" >';
                    echo '<b>Reparto:&nbsp;</b>'.$this->mappa['reparto']['prop']['default'];
                echo '</div>';

                $temp=$this->getCollab($this->mappa['id_esec']['prop']['default']);

                echo '<div style="position:relative;display:inline-block;vertical-align:top;width:40%;" >';
                    echo '<b>Operatore:&nbsp;</b>';
                    if ($temp) echo $temp['nome'].' '.$temp['cognome'];
                    else echo 'errore operatore';

                    if ($this->ambito=='print') echo '<img style="width:20px;height:20px;cursor:pointer;margin-left:25px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/carb/img/trash.png" onclick="window._nebulaCarb.cancella(\''.$this->mappa['ID']['prop']['default'].'\');" />';
                    else if ($this->ambito!='new' && $this->ambito!='trash') echo '<img style="width:20px;height:20px;cursor:pointer;margin-left:25px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/carb/img/trash.png" onclick="window._nebulaCarb.annulla(\''.$this->mappa['ID']['prop']['default'].'\');" />';
                echo '</div>';
            echo '</div>';

            echo '<div style="position:relative;margin-top:10px;" >';
                echo '<span>Vettura</span>';
                echo '<input name="carb_vettan_input" style="margin-left:5px;" type="radio" value="NEBULA" ';
                    if ($this->mappa['gestione']['prop']['default']=='NEBULA') echo 'checked';
                    if ($this->ambito=='fill' || $this->ambito=='cash' || $this->ambito=='trash') echo ' disabled';
                echo ' onchange="window._nebulaCarb.setTelaio(this.value);" />';
                echo '<span style="margin-left:20px;" >Tanica</span>';
                echo '<input name="carb_vettan_input" style="margin-left:5px;" type="radio" value="TANICA" ';
                    if ($this->mappa['gestione']['prop']['default']=='TANICA') echo 'checked';
                    if ($this->ambito=='fill' || $this->ambito=='cash' || $this->ambito=='trash') echo ' disabled';
                echo ' onchange="window._nebulaCarb.setTelaio(this.value);" />';
            echo '</div>';

            echo '<div style="position:relative;margin-top:10px;" >';

                echo '<div style="position:relative;" >';
                    echo '<div   style="position:relative;display:inline-block;vertical-align:top;width:50%;">';
                        echo '<div id="js_chk_'.$this->form_tag.'_tag_id_rich" class="chekko_tag" ><b>Richiedente:</b></div>';
                        echo '<select id="'.$this->form_tag.'_id_rich" style="width:95%;font-size:1.2em;" class="js_chk_'.$this->form_tag.'" js_chk_'.$this->form_tag.'_tipo="id_rich" onchange="window._js_chk_'.$this->form_tag.'.js_chk();" ';
                            if ($this->mappa['id_rich']['prop']['disabled']) echo 'disabled="disabled"';
                        echo '>';

                            if ($this->ambito=='new') {
                                echo '<option value="" >Scegli un collaboratore...</option>';
                            }

                            foreach ($this->mappa['id_rich']['prop']['options'] as $k=>$t) {
                                if ($this->ambito!='new' && $t['ID_coll']!=$this->mappa['id_rich']['prop']['default']) continue;
                                echo '<option value="'.$t['ID_coll'].'" ';
                                    if ($t['ID_coll']==$this->mappa['id_rich']['prop']['default']) echo 'selected="selected" ';
                                echo '>'.$t['cognome'].' '.$t['nome'].'</option>';
                            }
                        echo '</select>';
                    echo '</div>';
                echo '</div>';
            echo'</div>';

            echo '<div style="position:relative;margin-top:10px;" >';
                echo '<div style="position:relative;">';

                    echo '<div style="position:relative;display:inline-block;vertical-align:top;width:30%;" >';
                        echo '<div id="js_chk_'.$this->form_tag.'_tag_importo" class="chekko_tag" ><b>Importo:</b></div>';
                        echo '<input id="'.$this->form_tag.'_importo" type="text" style="width:90%;font-size:1.1em;text-align:center;" value="'.($this->mappa['importo']['prop']['default']>0?$this->mappa['importo']['prop']['default']:"").'" class="js_chk_'.$this->form_tag.'" js_chk_'.$this->form_tag.'_tipo="importo" onchange="window._js_chk_'.$this->form_tag.'.js_chk();" ';
                            if ($this->ambito=='cash' || $this->ambito=='trash') echo ' disabled';
                        echo '/>';
                    echo '</div>';

                    echo '<div style="position:relative;display:inline-block;vertical-align:top;width:20%;text-align:center;" >';
                        echo '<div id="js_chk_'.$this->form_tag.'_tag_pieno" class="chekko_tag"><b>Pieno</b></div>';
                        echo '<input id="'.$this->form_tag.'_pieno" type="checkbox" onchange="window._js_chk_'.$this->form_tag.'.chg_flagCkb_null(\'pieno\',this.checked);" ';
                            if ($this->mappa['pieno']['prop']['default']=="1") echo 'checked';
                            if ($this->ambito=='fill' || $this->ambito=='cash' || $this->ambito=='trash') echo ' disabled';
                        echo ' />';
                        echo '<input type="hidden" class="js_chk_'.$this->form_tag.'" js_chk_'.$this->form_tag.'_tipo="pieno" value="'.($this->mappa['pieno']['prop']['default']==0?"":1).'" />';
                    echo '</div>';

                    echo '<div style="position:relative;display:inline-block;vertical-align:top;width:40%;" >';
                        echo '<div id="js_chk_'.$this->form_tag.'_tag_tipo_carb" class="chekko_tag" ><b>Carburante:</b></div>';
                        echo '<select id="'.$this->form_tag.'_tipo_carb" style="width:95%;font-size:1.2em;" class="js_chk_'.$this->form_tag.'" js_chk_'.$this->form_tag.'_tipo="tipo_carb" onchange="window._js_chk_'.$this->form_tag.'.js_chk();" ';
                            if ($this->mappa['tipo_carb']['prop']['disabled']) echo 'disabled="disabled"';
                            else if ($this->ambito=='fill' || $this->ambito=='cash' || $this->ambito=='trash') echo ' disabled';
                        echo '>';

                            echo '<option value="" >Tipo...</option>';

                            foreach ($this->mappa['tipo_carb']['prop']['options'] as $k=>$t) {
                                echo '<option value="'.$k.'" ';
                                    if ($k==$this->mappa['tipo_carb']['prop']['default']) echo 'selected="selected" ';
                                echo '>'.$t.'</option>';
                            }
                        echo '</select>';
                    echo '</div>';

                echo '</div>';
            echo'</div>';

            echo '<div style="position:relative;margin-top:10px;" >';

                echo '<div style="position:relative;">';

                    echo '<div style="position:relative;display:inline-block;vertical-align:top;width:40%;" >';
                        echo '<div id="js_chk_'.$this->form_tag.'_tag_causale" class="chekko_tag"><b>Causale:</b></div>';
                        echo '<select id="'.$this->form_tag.'_causale" style="width:95%;font-size:1.2em;" class="js_chk_'.$this->form_tag.'" js_chk_'.$this->form_tag.'_tipo="causale" onchange="window._nebulaCarb.updateCausale();" ';
                            if ($this->mappa['causale']['prop']['disabled']) echo 'disabled="disabled"';
                            else if ($this->ambito=='fill' || $this->ambito=='cash' || $this->ambito=='trash') echo ' disabled';
                        echo '>';

                            echo '<option value="" >Scegli...</option>';

                            foreach ($this->mappa['causale']['prop']['options'] as $k=>$t) {
                                echo '<option value="'.$t['codice'].'" ';
                                    if ($t['codice']==$this->mappa['causale']['prop']['default']) echo 'selected="selected" ';
                                echo ' data-ris="'.$t['ris'].'" data-nota="'.$t['nota'].'">'.$t['causale'].'</option>';
                            }
                        echo '</select>';
                    echo '</div>';

                    echo '<div style="position:relative;display:inline-block;vertical-align:top;width:50%;text-align:center;" >';
                        echo '<div><b>Da Risarcire</b></div>';
                        echo '<input id="'.$this->form_tag.'_flag_ris" type="checkbox" ';
                            if ($this->mappa['flag_ris']['prop']['default']=="1") echo 'checked';
                            if ($this->ambito=='fill' || $this->ambito=='cash' || $this->ambito=='trash') echo ' disabled';
                        echo ' onchange="window._js_chk_'.$this->form_tag.'.chg_flagCkb_std(\'flag_ris\',this.checked);" />';
                        echo '<input type="hidden" class="js_chk_'.$this->form_tag.'" js_chk_'.$this->form_tag.'_tipo="flag_ris" value="'.$this->mappa['flag_ris']['prop']['default'].'"/>';
                    echo '</div>';

                echo '</div>';

            echo'</div>';

            echo '<div style="position:relative;margin-top:10px;" >';

                echo '<div id="js_chk_'.$this->form_tag.'_tag_nota" class="chekko_tag"><b>Nota:</b></div>';

                echo '<div style="position:relative;">';
                    echo '<input id="'.$this->form_tag.'_nota" type="text" style="width:75%;" maxlength="50" value="'.$this->mappa['nota']['prop']['default'].'" class="js_chk_'.$this->form_tag.'" js_chk_'.$this->form_tag.'_tipo="nota" onchange="window._js_chk_'.$this->form_tag.'.js_chk();" ';
                        if ($this->ambito=='fill' || $this->ambito=='cash' || $this->ambito=='trash') echo ' disabled';
                    echo '/>';
                    echo '<input id="'.$this->form_tag.'_flag_nota" type="hidden" class="js_chk_'.$this->form_tag.'" js_chk_'.$this->form_tag.'_tipo="flag_nota" value="'.$this->checkNota().'" />';
                echo '</div>';

            echo'</div>';

            echo '<div id="carb_veicoloDiv_main" style="position:relative;margin-top:10px;';
                if ($this->mappa['gestione']['prop']['default']=='TANICA') echo 'display:none;';
            echo '" >';
                
                if ($this->ambito!='fill' && $this->ambito!='cash' && $this->ambito!='trash') {
                    echo '<div style="position:relative;">';

                        echo '<div style="position:relative;display:inline-block;vertical-align:top;width:50%;" >';
                            echo '<div id="js_chk_'.$this->form_tag.'_tag_telaio" class="chekko_tag"><b>Targa/Telaio</b></div>';
                            echo '<input id="carb_tt" type="text" style="width:90%;font-size:1.1em;text-align:center;" onkeydown="if(event.keyCode==13) window._nebulaCarb.cercaTT(\''.$this->mappa['reparto']['prop']['default'].'\',\''.$this->mappa['dms']['prop']['default'].'\');" />';
                        echo '</div>';
                        echo '<div style="position:relative;display:inline-block;vertical-align:top;width:15%;vertical-align:bottom;" >';
                            echo '<button style="margin-left:10px;" onclick="window._nebulaCarb.cercaTT(\''.$this->mappa['reparto']['prop']['default'].'\',\''.$this->mappa['dms']['prop']['default'].'\');">Cerca</button>';
                        echo '</div>';

                    echo '</div>';
                }
                
                echo '<div style="position:relative;margin-top:10px;height:50px;';
                            //if ($this->mappa['veicolo']['prop']['default']==0) echo 'display:none;';
                echo '">';
                                    
                    echo '<div id="carb_veicoloDiv_targa"  style="position:relative;display:inline-block;width:15%;" >';
                        echo $this->mappa['targa']['prop']['default'];
                    echo '</div>';
        
                    echo '<div id="carb_veicoloDiv_telaio" style="position:relative;display:inline-block;width:35%;" >';
                        echo $this->mappa['telaio']['prop']['default'];
                    echo '</div>';
        
                    echo '<div id="carb_veicoloDiv_des_veicolo" style="position:relative;display:inline-block;width:45%;font-size:0.9em;" >';
                        echo substr($this->mappa['des_veicolo']['prop']['default'],0,30);
                    echo '</div>';
                    
                    echo '<div id="carb_veicoloDiv_img_veicolo" style="position:relative;display:inline-block;width:5%;font-size:0.9em;" >';
                            if ($this->ambito=='print') echo '<img style="width:20px;height:20px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/carb/img/trash.png" onclick="window._nebulaCarb.delTT();" />';
                    echo '</div>';

                echo '</div>';

            echo '</div>';

            echo '<div style="position:relative;margin-top:20px;" >';
                echo '<div style="position:relative;display:inline-block;vertical-align:top;width:50%;" >';
                    if ($this->ambito!='fill' && $this->ambito!='cash' && $this->ambito!='trash') { 
                        echo '<button onclick="window._nebulaCarb.salva();">Salva</button>';
                    }
                    else if ($this->ambito=='cash') {
                        echo '<button style="color:red;" onclick="window._nebulaCarb.noris(\''.$this->mappa['ID']['prop']['default'].'\');">Mancato Risarcimento</button>';
                    }
                echo '</div>';
                echo '<div style="position:relative;display:inline-block;vertical-align:top;width:40%;text-align:right;" >';

                    if ($this->ambito!='trash') {
                        if (isset($this->responsabili[$this->mappa['id_esec']['prop']['default']]) && $this->responsabili[$this->mappa['id_esec']['prop']['default']]['stampa']==1) {
                            if ($this->ambito!='fill' && $this->ambito!='cash') {
                                echo '<button onclick="window._nebulaCarb.stampa();">Stampa</button>';
                            }
                            else if ($this->ambito=='cash') {
                                echo '<button onclick="window._nebulaCarb.risarcisci(\''.$this->mappa['ID']['prop']['default'].'\');">Risarcisci</button>';
                            }
                            else if ($this->ambito=='fill') {
                                echo '<button onclick="window._nebulaCarb.completa();">Completa</button>';
                            }
                        }
                    }
                    else if ($this->ambito=='trash'){
                        if (isset($this->responsabili[$this->mappa['id_esec']['prop']['default']]) && $this->responsabili[$this->mappa['id_esec']['prop']['default']]['annulla']==1) {
                            echo '<button style="color:red;" onclick="window._nebulaCarb.annulla(\''.$this->mappa['ID']['prop']['default'].'\');">Annulla</button>';
                        }
                    }
                echo '</div>';
            echo '</div>';
        
        echo '</div>';

        /*echo '<div>';
            echo json_encode($this->responsabili);
        echo '</div>';
        echo '<div>';
            echo json_encode($this->galileo->getLog('query'));
        echo '</div>';*/

        $t=new nebulaUtilityDiv('carb',"window._nebulaCarb.closeUtil()");
        $t->draw();

        $this->draw_js_base();
    }

    function getCollab($id) {

        $temp=false;
        $this->galileo->getMaestroCollab("id='".$id."'");
        $fid=$this->galileo->preFetchBase('maestro');
        while ($row=$this->galileo->getFetchBase('maestro',$fid)) {
            $temp=$row;
        }

        return $temp;
    }

    function drawHead() {

        echo '<div style="position:relative;margin-top:8px;margin-bottom:8px;border:1px solid #999999;padding:3px;box-sizing:border-box;width:90%;">';

            echo '<div style="position:relative;" >';

                echo '<div style="position:relative;display:inline-block;width:18%;font-size:0.9em;" >';
                    echo '<b>Reparto:&nbsp;</b>'.$this->mappa['reparto']['prop']['default'];
                echo '</div>';

                $temp=$this->getCollab($this->mappa['id_rich']['prop']['default']);

                echo '<div style="position:relative;display:inline-block;width:53%;" >';
                    echo '<b>Richiedente:&nbsp;</b>';
                    if ($temp) echo $temp['nome'].' '.$temp['cognome'];
                    else echo 'errore richiedente';
                echo '</div>';

                echo '<div style="position:relative;display:inline-block;width:29%;font-size:0.9em;" >';
                    echo $this->stati[$this->buono['stato']].' ('.$this->mappa['ID']['prop']['default'].')';
                echo '</div>';

            echo '</div>';

            echo '<div style="position:relative;color:red;font-weight:bold;font-size:0.9em;" >';
                echo $this->mappa['nota']['prop']['default'];
            echo '</div>';

            echo '<div style="position:relative;" >';

                echo '<div style="position:relative;display:inline-block;width:18%;" >';
                    if ($this->buono['gestione']=='TANICA' || $this->buono['telaio']=='TANICA') echo 'TANICA';
                    else echo $this->mappa['targa']['prop']['default'];
                echo '</div>';

                echo '<div style="position:relative;display:inline-block;width:34%;" >';
                    echo $this->mappa['telaio']['prop']['default'];
                echo '</div>';

                echo '<div style="position:relative;display:inline-block;width:48%;font-size:0.9em;" >';
                    echo substr($this->mappa['des_veicolo']['prop']['default'],0,30);
                echo '</div>';

            echo '</div>';

            echo '<div style="position:relative;" >';

                echo '<div style="position:relative;display:inline-block;width:18%;font-weight:bold;" >';
                    echo ($this->mappa['pieno']['prop']['default']==1)?'PIENO':number_format($this->mappa['importo']['prop']['default'],2,'.','');
                echo '</div>';

                echo '<div style="position:relative;display:inline-block;width:34%;" >';
                    if (isset($this->mappa['tipo_carb']['prop']['options'][$this->mappa['tipo_carb']['prop']['default']])) {
                        echo $this->mappa['tipo_carb']['prop']['options'][$this->mappa['tipo_carb']['prop']['default']];
                    }
                echo '</div>';

                echo '<div style="position:relative;display:inline-block;width:48%;" >';
                    if (isset($this->mappa['causale']['prop']['options'][$this->mappa['causale']['prop']['default']])) {
                        echo '<b>Causale:&nbsp;</b>'.$this->mappa['causale']['prop']['options'][$this->mappa['causale']['prop']['default']]['causale'];
                    }
                    else {
                        echo 'errore causale';
                    }
                echo '</div>';

            echo '</div>';

            echo '<div style="position:relative;" >';

                //echo json_encode($this->causali);
                echo '<div style="position:relative;display:inline-block;width:18%;" >';
                    echo mainFunc::gab_todata($this->buono['d_creazione']);
                echo '</div>';
                
                $temp=$this->getCollab($this->mappa['id_esec']['prop']['default']);

                echo '<div style="position:relative;display:inline-block;width:60%;" >';
                    echo '<b>Operatore:&nbsp;</b>';
                    if ($temp) echo $temp['nome'].' '.$temp['cognome'];
                    else echo 'errore operatore';
                echo '</div>';

                echo '<div style="position:relative;display:inline-block;width:22%;text-align:center;" >';
                    switch($this->buono['stato']) {

                        case 'dacompletare':
                            echo '<img style="position:absolute;width:35px;height:35px;top:-30px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/carb/img/fill.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].buonoEdit(\'fill\',\''.$this->mappa['ID']['prop']['default'].'\',\''.$this->utente.'\');" />';
                        break;

                        case 'daris':
                            echo '<img style="position:absolute;width:35px;height:35px;top:-30px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/carb/img/cash.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].buonoEdit(\'cash\',\''.$this->mappa['ID']['prop']['default'].'\',\''.$this->utente.'\');" />';
                        break;

                        case 'creato':
                            echo '<img style="position:absolute;width:35px;height:35px;top:-30px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/carb/img/print.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].buonoEdit(\'print\',\''.$this->mappa['ID']['prop']['default'].'\',\''.$this->utente.'\');" />';
                        break;
                    }
                echo '</div>';

            echo '</div>';

        echo '</div>';
    }

}

?>