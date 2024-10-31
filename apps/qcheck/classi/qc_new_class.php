<?php
require_once(DROOT.'/nebula/core/chekko/chekko.php');

class qcNew extends Chekko {

    //ID controllo
    protected $esecutore;
    protected $IDabbinamento;
    protected $controllo;
    protected $reparto;
    protected $versione;
    protected $moduli=array();

    function __construct($esecutore,$IDabbinamento,$controllo,$reparto,$versione,$moduli) {
        
        parent::__construct('qc_new');

        $this->esecutore=$esecutore;
        $this->IDabbinamento=$IDabbinamento;
        $this->controllo=$controllo;
        $this->reparto=$reparto;
        $this->versione=$versione;
        $this->moduli=$moduli;

        $this->keyConfig();

    }

    function keyConfig() {

        $f=array();

        $f['chiave']=array(
            "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
            "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
        );

        $f['intestazione']=array(
            "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
            "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
        );

        $f['esecutore']=array(
            "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
            "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
        );

        $f['controllo']=array(
            "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
            "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
        );

        $f['reparto']=array(
            "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
            "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
        );

        $f['versione']=array(
            "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
            "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
        );

        $f['ID_abbinamento']=array(
            "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
            "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
        );

        $this->add_fields($f);

        /////////////////////////////////////

        $f=array();

        $f['chiave']='none';
        $f['intestazione']='none';
        $f['esecutore']='none';
        $f['controllo']='none';
        $f['reparto']='none';
        $f['versione']='none';
        $f['ID_abbinamento']='none';

        $this->load_tipi($f);

        ////////////////////////////////////

        $f=array();

        $f['chiave']='';
        $f['intestazione']='';
        $f['esecutore']='';
        $f['controllo']='';
        $f['reparto']='';
        $f['versione']='';
        $f['ID_abbinamento']='';

        $this->load_expo($f);

        ////////////////////////////////////

        $f=array();

        $f['chiave']='chiave';
        $f['intestazione']='intestazione';
        $f['esecutore']='esecutore';
        $f['controllo']='controllo';
        $f['reparto']='reparto';
        $f['versione']='versione';
        $f['ID_abbinamento']='ID_abbinamento';

        $this->load_conv($f);

        ////////////////////////////////////

        $f=array();

        $f['chiave']=array(
            "prop"=>array(
                "input"=>"input",
                "tipo"=>"hidden",
                "maxlenght"=>"",
                "options"=>array(),
                "rows"=>"",
                "default"=>"",
                "placeholder"=>"",
                "disabled"=>false
            ),
            "css"=>array()
        );

        $f['intestazione']=array(
            "prop"=>array(
                "input"=>"input",
                "tipo"=>"hidden",
                "maxlenght"=>"",
                "options"=>array(),
                "rows"=>"",
                "default"=>"",
                "placeholder"=>"",
                "disabled"=>false
            ),
            "css"=>array()
        );

        $f['esecutore']=array(
            "prop"=>array(
                "input"=>"input",
                "tipo"=>"hidden",
                "maxlenght"=>"",
                "options"=>array(),
                "rows"=>"",
                "default"=>"",
                "placeholder"=>"",
                "disabled"=>false
            ),
            "css"=>array()
        );

        $f['controllo']=array(
            "prop"=>array(
                "input"=>"input",
                "tipo"=>"hidden",
                "maxlenght"=>"",
                "options"=>array(),
                "rows"=>"",
                "default"=>"",
                "placeholder"=>"",
                "disabled"=>false
            ),
            "css"=>array()
        );

        $f['reparto']=array(
            "prop"=>array(
                "input"=>"input",
                "tipo"=>"hidden",
                "maxlenght"=>"",
                "options"=>array(),
                "rows"=>"",
                "default"=>"",
                "placeholder"=>"",
                "disabled"=>false
            ),
            "css"=>array()
        );

        $f['versione']=array(
            "prop"=>array(
                "input"=>"input",
                "tipo"=>"hidden",
                "maxlenght"=>"",
                "options"=>array(),
                "rows"=>"",
                "default"=>"",
                "placeholder"=>"",
                "disabled"=>false
            ),
            "css"=>array()
        );

        $f['ID_abbinamento']=array(
            "prop"=>array(
                "input"=>"input",
                "tipo"=>"hidden",
                "maxlenght"=>"",
                "options"=>array(),
                "rows"=>"",
                "default"=>"",
                "placeholder"=>"",
                "disabled"=>false
            ),
            "css"=>array()
        );

        $this->load_mappa($f);

    }

    function autoConfig($modulo) {

        $f=array();

        $f['op_'.$modulo]=array(
            "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
            "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
        );

        $f['va_'.$modulo]=array(
            "js_chk_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
            "js_chk_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
        );

        $this->add_fields($f);

        /////////////////////////////////////

        $f=array();

        $f['op_'.$modulo]='none';
        $f['va_'.$modulo]='none';

        $this->load_tipi($f);

        ////////////////////////////////////

        $f=array();

        $f['op_'.$modulo]='';
        $f['va_'.$modulo]='';

        $this->load_expo($f);

        ////////////////////////////////////

        $f=array();

        $f['op_'.$modulo]='op_'.$modulo;
        $f['va_'.$modulo]='va_'.$modulo;

        $this->load_conv($f);

        ////////////////////////////////////

        $f=array();

        $f['op_'.$modulo]=array(
            "prop"=>array(
                "input"=>"select",
                "tipo"=>"",
                "maxlenght"=>"",
                "options"=>array(),
                "rows"=>"",
                "default"=>"",
                "placeholder"=>"",
                "disabled"=>false
            ),
            "css"=>array()
        );

        $f['va_'.$modulo]=array(
            "prop"=>array(
                "input"=>"select",
                "tipo"=>"",
                "maxlenght"=>"",
                "options"=>array(),
                "rows"=>"",
                "default"=>"",
                "placeholder"=>"",
                "disabled"=>false
            ),
            "css"=>array()
        );

        //##########################
        //caricamento varianti
        foreach ($this->moduli[$modulo]->getVarianti() as $k=>$v) {
            $f['va_'.$modulo]['options'][$k]=$v['tag'];
        }
        //##########################

        $this->load_mappa($f);
    }

    function draw_css() {

    }

    function draw_JS() {

        ob_start();
            include ($_SERVER['DOCUMENT_ROOT'].'/nebula/apps/qcheck/classi/qc_new.js');
        ob_end_flush();

        ob_start();
            include ($_SERVER['DOCUMENT_ROOT'].'/nebula/apps/qcheck/controlli/controllo_'.$this->controllo.'.js');
        ob_end_flush();
    }

    function draw() {

        echo '<div style="width:95%;">';

            echo '<div style="margin-top:5px;margin-bottom:10px;">';
                echo '<div style="display:inline-block;vertical-align:top;width:70%;">';
                    echo '<input id="qc_new_form_riferimento" type="text" style="width:95%;" placeholder="chiave" onkeydown="if(event.keyCode==13) window._js_chk_qc_new.confirmRiferimento();" />';
                echo '</div>';
                echo '<div style="display:inline-block;vertical-align:top;width:30%;">';
                    echo '<button onclick="window._js_chk_qc_new.confirmRiferimento();">cerca</button>';
                echo '</div>';
            echo '</div>';

            echo '<div style="height:30px;">';
                echo '<div style="display:inline-block;vertical-align:top;width:30%;font-weight:bold;">Chiave:</div>';
                echo '<div id="qc_new_form_chiave" style="display:inline-block;vertical-align:top;width:70%;"></div>';
            echo '</div>';
            echo '<input type="hidden" class="js_chk_qc_new" js_chk_qc_new_tipo="chiave" />';

            echo '<div style="height:30px;">';
                echo '<div style="display:inline-block;vertical-align:top;width:30%;font-weight:bold;">Intestazione:</div>';
                echo '<div id="qc_new_form_intestazione" style="display:inline-block;vertical-align:top;width:70%;"></div>';
            echo '</div>';
            echo '<input type="hidden" class="js_chk_qc_new" js_chk_qc_new_tipo="intestazione" />';

            echo '<div id="js_chk_qc_new_error_chiave" class="chekko_error js_chk_qc_new_error"></div>';

            echo '<input type="hidden" class="js_chk_qc_new" js_chk_qc_new_tipo="esecutore" value="'.$this->esecutore.'"/>';
            echo '<input type="hidden" class="js_chk_qc_new" js_chk_qc_new_tipo="controllo" value="'.$this->controllo.'"/>';
            echo '<input type="hidden" class="js_chk_qc_new" js_chk_qc_new_tipo="reparto" value="'.$this->reparto.'"/>';
            echo '<input type="hidden" class="js_chk_qc_new" js_chk_qc_new_tipo="versione" value="'.$this->versione.'"/>';
            echo '<input type="hidden" class="js_chk_qc_new" js_chk_qc_new_tipo="ID_abbinamento" value="'.$this->IDabbinamento.'"/>';

            $count=0;
            foreach ($this->moduli as $modulo=>$m) {

                $count++;

                $this->autoConfig($modulo);

                echo '<hr/>';

                echo '<div>';

                    echo '<div>';
                        //echo '<span>Modulo:</span>';
                        echo '<span style="margin-left:5px;font-weight:bold;font-size:1.2em;">'.$m->getTitle().'</span>';
                    echo '</div>';

                    echo '<div style="margin-top:5px;">';

                        echo '<div style="display:inline-block;vertical-align:top;width:20%">';
                            echo '<label>Variante</label>';
                        echo '</div>';

                        echo '<div style="display:inline-block;vertical-align:top;width:50%">';
                            echo '<select class="js_chk_qc_new" style="width:95%;font-size:1.2em;" js_chk_qc_new_tipo="va_'.$modulo.'" >';
                                foreach ($this->mappa['va_'.$modulo]['options'] as $v=>$t) {
                                    echo '<option value="'.$v.'">'.$t.'</option>';
                                }
                            echo '</select>';
                        echo '</div>';

                        echo '<div id="js_chk_qc_new_error_va_'.$modulo.'" class="chekko_error js_chk_qc_new_error" style="display:inline-block;vertical-align:top;width:30%" ></div>';

                    echo '</div>';

                    echo '<div style="margin-top:5px;" >';

                        echo '<div style="display:inline-block;vertical-align:top;width:20%">';
                            echo '<label>Operatore</label>';
                        echo '</div>';
                        
                        echo '<div style="display:inline-block;vertical-align:top;width:50%">';
                            echo '<select class="js_chk_qc_new" style="width:95%;font-size:1.2em;" data-idmodulo="m'.$count.'" js_chk_qc_new_tipo="op_'.$modulo.'" >';
                            echo '</select>';
                        echo '</div>';

                        echo '<div id="js_chk_qc_new_error_op_'.$modulo.'" class="chekko_error js_chk_qc_new_error" style="display:inline-block;vertical-align:top;width:30%"></div>';

                    echo '</div>';

                echo '</div>';
            }

        echo '</div>';

        $this->draw_JS_base();

    }

}

?>