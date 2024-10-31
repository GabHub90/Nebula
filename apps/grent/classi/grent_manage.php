<?php
require('grent_veicoli.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/core/divo/divo.php');
require('reset_form.php');

class grentManage extends grentVeicoli {

    function __construct($tipo,$logged,$galileo) {

        $this->coefficienti['kasko']=0;

        parent::__construct($tipo,$logged,$galileo); 
    }

    function buildManage($grent) {

        //call_user_func_array(array($this, 'getPratiche_'.$this->tipoRent), array() );
        $this->getPratiche();

        //pratica si riferisce alla pratica attuale se c'è
        $this->galileo->executeSelect('grent','GRENT_veicoli',"grent_id='".$grent."'","");
        if ($this->galileo->getResult()) {
            $fid=$this->galileo->preFetch('grent');
            while($row=$this->galileo->getFetch('grent',$fid)) {
                $this->veicoli[$row['marca']][$row['rif_id']]=$row;
                $this->veicoli[$row['marca']][$row['rif_id']]['info']=false;
                $this->veicoli[$row['marca']][$row['rif_id']]['pratica_id']=0;
                $this->veicoli[$row['marca']][$row['rif_id']]['pratica_da']="";
                $this->veicoli[$row['marca']][$row['rif_id']]['pratica_a']="";
                $this->veicoli[$row['marca']][$row['rif_id']]['pratica_stato']="";
                $this->veicoli[$row['marca']][$row['rif_id']]['pratica_utente']="";
                //$this->veicoli[$row['marca']][$row['rif_id']]['note']="";
                $this->veicoli[$row['marca']][$row['rif_id']]['actual_km']="";
                $this->veicoli[$row['marca']][$row['rif_id']]['sva']='';
                $this->veicoli[$row['marca']][$row['rif_id']]['valore_i']='';
                $this->veicoli[$row['marca']][$row['rif_id']]['valore_f']='';
                $this->veicoli[$row['marca']][$row['rif_id']]['km_i']='';
                $this->veicoli[$row['marca']][$row['rif_id']]['km_f']='';
                $this->veicoli[$row['marca']][$row['rif_id']]['chiusura']=0;
                $this->veicoli[$row['marca']][$row['rif_id']]['reset_valore_i']='';
                $this->veicoli[$row['marca']][$row['rif_id']]['reset_valore_f']='';

                //call_user_func_array(array($this, 'elaboraPratiche_'.$this->tipoRent), array($row) );
                $this->elaboraPratiche($row);

                //collega i RESET
                $this->getResetManage($row);
            }
        }
    }

    function getResetManage($a) {

        $this->galileo->executeSelect('grent','GRENT_reset',"grent_id='".$a['grent_id']."'","d");
        if ($this->galileo->getResult()) {
            $fid=$this->galileo->preFetch('grent');
            
            while($row=$this->galileo->getFetch('grent',$fid)) {

                $this->elaboraReset($a,$row);            

                //##################################
                //scrittura dell'array reset
                $this->reset[$row['d'].'_'.$row['reset_id']]=$row;
                //##################################
            }
        }
    }

    function drawManage($marca,$vei) {

        $this->popolaInfo($marca,$vei);
        $v=$this->calcolaStato($this->veicoli[$marca][$vei]);

        echo '<div style="height:10%;">';

            echo '<div style="height:10px;font-size:0.9em;background-color:'.$v['color'].';text-align:right;">';
                echo '<img style="width:20px;height:20px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/grent/img/annulla.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].chiudiVei();" />';
            echo '</div>';

            //veicolo
            echo '<div style="height:15px;margin-top:5px;">';
                echo '<div style="position:relative;display:inline-block;vertical-align:top;width:18%;font-size:0.9em;" >'.$vei.'</div>';
                echo '<div style="position:relative;display:inline-block;vertical-align:top;width:17%;font-weight:bold;" >'.$v['info']['targa'].'</div>';
                echo '<div style="position:relative;display:inline-block;vertical-align:top;width:45%;font-weight:bold;" >'.$v['info']['telaio'].'</div>';
                echo '<div style="position:relative;display:inline-block;vertical-align:top;width:20%;font-size:0.9em;" >'.mainFunc::gab_todata($v['info']['d_cons']).'</div>';
            echo '</div>';

            echo '<div style="height:15px;font-size:0.9em;">';
                echo '<div style="position:relative;display:inline-block;vertical-align:top;width:18%;" >'.substr($v['info']['des_marca'],0,10).'</div>';
                echo '<div style="position:relative;display:inline-block;vertical-align:top;width:17%;" >'.$v['info']['modello'].'</div>';
                echo '<div style="position:relative;display:inline-block;vertical-align:top;width:65%;" >'.$v['info']['des_veicolo'].'</div>';
            echo '</div>';

            echo '</div>';

            echo '<div style="position:relative;font-weight:bold;text-align:center;">';
                echo 'Resets';
                if ($this->veicoli[$marca][$vei]['stato']!='chiuso' && $this->veicoli[$marca][$vei]['pratica_id']==0) {            
                    echo '<img id="grent_manage_add_reset" style="position:absolute;right:-5px;top:0px;width:20px;height:20px;z-index:10;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/grent/img/add.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].apriResetForm();" />';
                }
            echo '</div>';

        echo '</div>';
        
        echo '<div style="height:47%;">';

            echo '<div style="position:relative;height:100%;border-bottom:1px solid black;">';

                //divo dei reset
                echo '<div id="grent_manage_reset" style="width:100%;height:100%;">';
                    if (count($this->reset)==0) {
                        echo '<span style="font-weight:bold;">Nessun reset trovato !!!</span>';
                    }
                    else {

                        $divo=new Divo('grentReset','10%','89%',1);
                        $divo->setBk('#ccc3e8');

                        foreach ($this->reset as $k=>$r) {

                            ob_start();

                                echo '<div>';
                                    echo 'Reset: '.$r['reset_id'];
                                echo '</div>';

                                echo '<div style="">';
                                    echo '<table style="width:100%;border-collapse:collapse;font-size:1em;text-align:center;border:1px solid black;" >';
                                        echo '<tr>';
                                            echo '<th style="width:12%;">km</th>';
                                            echo '<th style="width:12%;">Inizio</th>';
                                            echo '<th style="width:12%;">Fine</th>';
                                            echo '<th style="width:12%;">Sva km</th>';
                                            echo '<th style="width:12%;">Sva mesi</th>';
                                            echo '<th style="width:10%;">K</th>';
                                            echo '<th style="width:10%;">K_km</th>';
                                            echo '<th style="width:10%;">Inc%</th>';
                                            echo '<th style="width:10%;">Fisso</th>';
                                        echo '</tr>';
                                        echo '<tr>';
                                            echo '<td style="width:12%;">'.$r['km_reset'].'</td>';
                                            echo '<td style="width:12%;">'.$r['valore_i'].'</td>';
                                            echo '<td style="width:12%;">'.$r['valore_f'].'</td>';
                                            echo '<td style="width:12%;">'.$r['sva_km'].'</td>';
                                            echo '<td style="width:12%;">'.$r['sva_tempo'].'</td>';
                                            echo '<td style="width:10%;">'.$r['coeff'].'</td>';
                                            echo '<td style="width:10%;">'.number_format($r['coeff_km'],1,'.','').'</td>';
                                            echo '<td style="width:10%;">'.($r['incent']*100).'</td>';
                                            echo '<td style="width:10%;">'.number_format($r['cop_fisso'],1,'.','').'</td>';
                                        echo '</tr>';
                                    echo '</table>';
                                echo '</div>';

                                echo '<div style="margin-top:10px;font-size:1em;">';

                                    echo '<div>';
                                        echo 'Fascia: '.$r['fascia'].' - '.$this->fasce[$r['fascia']]['testo'];
                                    echo '</div>';

                                    echo '<div>';
                                        echo '<table style="width:100%;border-collapse:collapse;font-size:1em;text-align:center;border:1px solid black;" >';
                                            echo '<tr>';
                                                echo '<th style="width:17%;">SVALUT.</th>';
                                                echo '<th style="width:16%;">IPT</th>';
                                                echo '<th style="width:16%;">BOLLO</th>';
                                                echo '<th style="width:17%;">KASKO</th>';
                                                echo '<th style="width:17%;">MANUT.</th>';
                                                echo '<th style="width:17%;">GOMME</th>';
                                            echo '</tr>';
                                            echo '<tr>';
                                                echo '<td style="width:17%;">'.number_format($v['sva'],0,',','').'</td>';
                                                echo '<td style="width:16%;">'.number_format($this->fasce[$r['fascia']]['ipt'],0,',','.').'</td>';
                                                echo '<td style="width:16%;">'.number_format($this->fasce[$r['fascia']]['bollo'],0,',','.').'</td>';
                                                echo '<td style="width:17%;">'.number_format($this->fasce[$r['fascia']]['kasko'],0,',','.').'</td>';
                                                echo '<td style="width:17%;">'.number_format($this->fasce[$r['fascia']]['manut'],0,',','.').'</td>';
                                                echo '<td style="width:17%;">'.number_format($this->fasce[$r['fascia']]['gomme'],0,',','.').'</td>';
                                            echo '</tr>';
                                        echo '</table>';
                                    echo '</div>';

                                echo '</div>';

                                echo '<div style="margin-top:10px;font-size:1em;">';

                                    echo '<div>';
                                        echo 'Franchigia';
                                    echo '</div>';

                                    echo '<div>';
                                        echo '<table style="width:100%;border-collapse:collapse;font-size:1em;text-align:center;border:1px solid black;" >';
                                            echo '<tr>';
                                                echo '<th style="width:12%;">Importo</th>';
                                                echo '<th style="width:12%;">%</th>';
                                                echo '<th style="width:12%;">Limite</th>';
                                                echo '<th style="width:12%;">Rid. Imp.</th>';
                                                echo '<th style="width:12%;">Rid. %</th>';
                                                echo '<th style="width:10%;">Rid. Lim.</th>';
                                                echo '<th style="width:10%;">Max</th>';
                                                echo '<th style="width:10%;">Min</th>';
                                                echo '<th style="width:10%;">K</th>';
                                            echo '</tr>';
                                            echo '<tr>';
                                                echo '<td style="width:12%;">'.number_format($this->franchigie[$r['franchigia']]['importo'],0,',','').'</td>';
                                                echo '<td style="width:12%;">'.number_format($this->franchigie[$r['franchigia']]['perc'],0,',','').'</td>';
                                                echo '<td style="width:12%;">'.number_format($this->franchigie[$r['franchigia']]['limite'],0,',','').'</td>';
                                                echo '<td style="width:12%;">'.number_format($this->franchigie[$r['franchigia']]['flag_importo'],0,',','').'</td>';
                                                echo '<td style="width:12%;">'.number_format($this->franchigie[$r['franchigia']]['flag_perc'],0,',','').'</td>';
                                                echo '<td style="width:10%;">'.number_format($this->franchigie[$r['franchigia']]['flag_limite'],0,',','').'</td>';
                                                echo '<td style="width:10%;">'.number_format($this->franchigie[$r['franchigia']]['calc_max'],0,',','').'</td>';
                                                echo '<td style="width:10%;">'.number_format($this->franchigie[$r['franchigia']]['calc_min'],0,',','').'</td>';
                                                echo '<td style="width:10%;">'.number_format($this->franchigie[$r['franchigia']]['calc_indice'],1,'.','').'</td>';     
                                            echo '</tr>';
                                        echo '</table>';
                                    echo '</div>';

                                echo '</div>';

                                echo '<div style="margin-top:20px;font-size:1em;">';

                                    if ($r['reset_id']==$v['reset_id']) {
                                        echo '<div style="position:relative;">';
                                            echo '<div style="position:relative;display:inline-block;width:15%;vertical-align:top;">Note Veicolo:</div>';
                                            echo '<div style="position:relative;display:inline-block;width:75%;vertical-align:top;">';
                                                echo '<input id="grent_reset_vei_nota" style="width:95%;" type="text" value="'.$v['note'].'" onkeydown="if(event.keyCode==13) window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].editVeiNote(\''.$v['grent_id'].'\');" />';
                                            echo '</div>';
                                            echo '<div style="position:relative;display:inline-block;width:10%;vertical-align:top;">';
                                                echo '<button onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].editVeiNote(\''.$v['grent_id'].'\');">--></button>';
                                            echo '</div>';
                                        echo '</div>';
                                    }

                                echo '</div>';

                            $divo->add_div(mainFunc::gab_todata($r['d']),'black',0,'',ob_get_clean(),($r['reset_id']==$v['reset_id'])?1:0,array('font-size'=>'0.9em;font-weight:bold;left:8px;'));
                        }

                        $divo->build();
                        $divo->draw();

                        unset($divo);

                    }
                echo '</div>';

                //divo reset form
                echo '<div id="grent_manage_reset_form" style="width:100%;height:100%;display:none;">';
                    $grForm=new grentResetForm('grform');

                    $a=array(
                        "grent_id"=>array(
                            "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
                            "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
                        ),
                        "rif_vei"=>array(
                            "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
                            "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
                        ),
                        "dms"=>array(
                            "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
                            "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
                        ),
                        "chiusura"=>array(
                            "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
                            "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
                        ),
                        "km_reset"=>array(
                            "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
                            "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
                        ),
                        "valore_i"=>array(
                            "js_chk_req"=>array("codice"=>3,"anor"=>"","anand"=>"","anxor"=>""),
                            "js_chk_ifreq"=>array("campo"=>"chiusura","op"=>"==","val"=>"0")
                        ),
                        "valore_f"=>array(
                            "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
                            "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
                        ),
                        "sva"=>array(
                            "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
                            "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
                        ),
                        "sva_km"=>array(
                            "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
                            "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
                        ),
                        "sva_tempo"=>array(
                            "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
                            "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
                        ),
                        "coeff"=>array(
                            "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
                            "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
                        ),
                        "coeff_km"=>array(
                            "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
                            "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
                        ),
                        "incent"=>array(
                            "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
                            "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
                        ),
                        "cop_fisso"=>array(
                            "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
                            "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
                        ),
                        "fascia"=>array(
                            "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
                            "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
                        ),
                        "franchigia"=>array(
                            "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
                            "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
                        )
                    );
                    $grForm->add_fields($a);

                    $a=array(
                        "grent_id"=>"none",
                        "rif_vei"=>"none",
                        "dms"=>"none",
                        "chiusura"=>"none",
                        "km_reset"=>"kmreset",
                        "valore_i"=>"digit",
                        "valore_f"=>"valf",
                        "sva"=>"none",
                        "sva_km"=>"digit",
                        "sva_tempo"=>"digit",
                        "coeff"=>"none",
                        "coeff_km"=>"none",
                        "incent"=>"none",
                        "cop_fisso"=>"none",
                        "fascia"=>"none",
                        "franchigia"=>"none"
                    );
                    $grForm->load_tipi($a);

                    $a=array(
                        "grent_id"=>"",
                        "rif_vei"=>"",
                        "dms"=>"",
                        "chiusura"=>"",
                        "km_reset"=>"",
                        "valore_i"=>"",
                        "valore_f"=>"",
                        "sva_km"=>"",
                        "sva_tempo"=>"",
                        "coeff"=>"",
                        "coeff_km"=>"",
                        "incent"=>"",
                        "cop_fisso"=>"",
                        "fascia"=>"",
                        "franchigia"=>""
                    );
                    $grForm->load_expo($a);

                    $a=array(
                        "grent_id"=>"grent_id",
                        "rif_vei"=>"rif_vei",
                        "dms"=>"dms",
                        "chiusura"=>"chiusura",
                        "km_reset"=>"km_reset",
                        "valore_i"=>"valore_i",
                        "valore_f"=>"valore_f",
                        "sva_km"=>"sva_km",
                        "sva_tempo"=>"sva_tempo",
                        "coeff"=>"coeff",
                        "coeff_km"=>"coeff_km",
                        "incent"=>"incent",
                        "cop_fisso"=>"cop_fisso",
                        "fascia"=>"fascia",
                        "franchigia"=>"franchigia"
                    );
                    $grForm->load_conv($a);

                    $a=array(
                        "grent_id"=>array(
                            "prop"=>array(
                                "input"=>"input",
                                "tipo"=>"hidden",
                                "maxlenght"=>"",
                                "options"=>array(),
                                "rows"=>"",
                                "default"=>$this->veicoli[$marca][$vei]['grent_id'],
                                "disabled"=>false
                            ),
                            "css"=>array()
                        ),
                        "rif_vei"=>array(
                            "prop"=>array(
                                "input"=>"input",
                                "tipo"=>"hidden",
                                "maxlenght"=>"",
                                "options"=>array(),
                                "rows"=>"",
                                "default"=>$this->veicoli[$marca][$vei]['info']['rif'],
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
                                "default"=>$this->veicoli[$marca][$vei]['info']['dms'],
                                "disabled"=>false
                            ),
                            "css"=>array()
                        ),
                        "chiusura"=>array(
                            "prop"=>array(
                                "input"=>"input",
                                "tipo"=>"hidden",
                                "maxlenght"=>"",
                                "options"=>array(),
                                "rows"=>"",
                                "default"=>$this->veicoli[$marca][$vei]['chiusura'],
                                "disabled"=>(count($this->reset)==0)?true:false
                            ),
                            "css"=>array()
                        ),
                        "km_reset"=>array(
                            "prop"=>array(
                                "input"=>"input",
                                "tipo"=>"text",
                                "maxlenght"=>"",
                                "options"=>array(),
                                "rows"=>"",
                                "default"=>$this->veicoli[$marca][$vei]['actual_km'],
                                "disabled"=>false
                            ),
                            "css"=>array()
                        ),
                        "valore_i"=>array(
                            "prop"=>array(
                                "input"=>"input",
                                "tipo"=>"text",
                                "maxlenght"=>"",
                                "options"=>array(),
                                "rows"=>"",
                                "default"=>$this->veicoli[$marca][$vei]['reset_valore_i'],
                                "disabled"=>false
                            ),
                            "css"=>array()
                        ),
                        "valore_f"=>array(
                            "prop"=>array(
                                "input"=>"input",
                                "tipo"=>"text",
                                "maxlenght"=>"",
                                "options"=>array(),
                                "rows"=>"",
                                "default"=>$this->veicoli[$marca][$vei]['reset_valore_f'],
                                "disabled"=>false
                            ),
                            "css"=>array()
                        ),
                        "sva"=>array(
                            "prop"=>array(
                                "input"=>"input",
                                "tipo"=>"text",
                                "maxlenght"=>"",
                                "options"=>array(),
                                "rows"=>"",
                                "default"=>"",
                                "disabled"=>true
                            ),
                            "css"=>array()
                        ),
                        "sva_km"=>array(
                            "prop"=>array(
                                "input"=>"input",
                                "tipo"=>"text",
                                "maxlenght"=>"",
                                "options"=>array(),
                                "rows"=>"",
                                "default"=>"30000",
                                "disabled"=>true
                            ),
                            "css"=>array()
                        ),
                        "sva_tempo"=>array(
                            "prop"=>array(
                                "input"=>"input",
                                "tipo"=>"text",
                                "maxlenght"=>"",
                                "options"=>array(),
                                "rows"=>"",
                                "default"=>"18",
                                "disabled"=>true
                            ),
                            "css"=>array()
                        ),
                        "coeff"=>array(
                            "prop"=>array(
                                "input"=>"input",
                                "tipo"=>"text",
                                "maxlenght"=>"",
                                "options"=>array(),
                                "rows"=>"",
                                "default"=>"4.0",
                                "disabled"=>true
                            ),
                            "css"=>array()
                        ),
                        "coeff_km"=>array(
                            "prop"=>array(
                                "input"=>"input",
                                "tipo"=>"text",
                                "maxlenght"=>"",
                                "options"=>array(),
                                "rows"=>"",
                                "default"=>"0.3",
                                "disabled"=>true
                            ),
                            "css"=>array()
                        ),
                        "incent"=>array(
                            "prop"=>array(
                                "input"=>"input",
                                "tipo"=>"text",
                                "maxlenght"=>"",
                                "options"=>array(),
                                "rows"=>"",
                                "default"=>"3",
                                "disabled"=>true
                            ),
                            "css"=>array()
                        ),
                        "cop_fisso"=>array(
                            "prop"=>array(
                                "input"=>"input",
                                "tipo"=>"text",
                                "maxlenght"=>"",
                                "options"=>array(),
                                "rows"=>"",
                                "default"=>"0.5",
                                "disabled"=>true
                            ),
                            "css"=>array()
                        ),
                        "fascia"=>array(
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
                        "franchigia"=>array(
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
                        )
                    );

                    foreach ($this->fasce as $k=>$f) {
                        $a['fascia']['prop']['options'][$k]=$f;
                    }

                    foreach ($this->franchigie as $k=>$f) {
                        $a['franchigia']['prop']['options'][$k]=$f;
                    }

                    $grForm->load_mappa($a);

                    echo '<div style="height:1%;text-align:right;">';
                        echo '<img style="position:relative;width:20px;height:20px;cursor:pointer;z-index:10;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/grent/img/annulla.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].chiudiResetForm();" />';
                    echo '</div>';

                    echo '<div style="height:99%;background-color:aliceblue;">';
                        $grForm->draw();
                    echo '</div>';

                echo '</div>';

            echo '</div>';

        echo '</div>';

        echo '<div style="height:43%;overflow:scroll;overflow-x:hidden;">';
            
            echo '<div style="position:relative;margin-top:5px;width:95%;">';

                echo '<div style="position:relative;display:inline-block;vertical-align:top;width:40%;font-size:1em;" >';
                    echo 'Ultimi Km:<span style="margin-left:10px;font-size:1.1em;font-weight:bold;">'.$this->veicoli[$marca][$vei]['actual_km'].'</span>';
                    echo '<button style="margin-left:10px;">Rettifica</button>';
                echo '</div>';

                echo '<div style="position:relative;display:inline-block;vertical-align:top;width:60%;font-weight:bold;color:blue;" >';
                    if (isset($this->veicoli[$marca][$vei]['reset'])) {
                        echo 'Limiti: '.($this->veicoli[$marca][$vei]['km_reset']+$this->veicoli[$marca][$vei]['sva_km']).' km - '.date('d/m/Y',strtotime('+'.$this->veicoli[$marca][$vei]['sva_tempo'].' month',mainFunc::gab_tots($this->veicoli[$marca][$vei]['reset'])));
                    }
                echo '</div>';

            echo '</div>';

            if (count($this->reset)>0) {

                echo '<div style="position:relative;margin-top:5px;width:95%;">';

                    echo '<div style="position:relative;">';
                        echo '<div style="position:relative;display:inline-block;vertical-align:top;width:35%;font-size:1em;" >Valore Iniziale:<span style="font-weight:bold;font-size:1.1em;margin-left:5px;">'.number_format($v['valore_i'],0,'','.').'</span> </div>';
                        echo '<div style="position:relative;display:inline-block;vertical-align:top;width:35%;font-size:1em;" >Valore Finale:<span style="font-weight:bold;font-size:1.1em;margin-left:5px;">'.number_format($v['valore_f'],0,'','.').'</span> </div>';
                        echo '<div style="position:relative;display:inline-block;vertical-align:top;width:30%;font-size:1em;text-align:right;" >Svalutazione:<span style="font-weight:bold;font-size:1.1em;margin-left:5px;">'.number_format($v['valore_i']-$v['valore_f'],2,',','.').'</span> </div>';
                    echo '</div>';

                    echo '<div style="margin-top:10px;">';

                        echo '<div>Spese per immatricolazione:</div>';
                        echo '<div>Spese assicurative:</div>';
                        echo '<div>Spese di ripristino:</div>';
                        echo '<div>Risarcimenti:</div>';

                    echo '</div>';

                echo '</div>';

                echo '<div style="position:relative;margin-top:5px;width:95%;">';
                    echo '<div>Fatture:</div>';
                echo '</div>';
            }

        echo '</div>';

    }

}
?>