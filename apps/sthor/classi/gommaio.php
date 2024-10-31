<?php
include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/core/divo/divo.php');

class Gommaio {

    protected $id="";

    protected $dms=array();
    protected $veicolo=array();
    public $pneumatico=array();
    protected $tipo=array();
    protected $mano=array();
    protected $tempo=array();
    protected $margine=array();
    protected $raggio=array();
    protected $vel=array();
    protected $dim=array();
    public $sconti=array();

    function __construct($id) {

        $this->id=$id;

        $this->dms=array(
            "VGM"=>"infinity",
            "POM"=>"concerto"
        );

        $this->veicolo=array(
            "X"=>array(
                "txt"=>"Standard",
                "config"=>'{"dms":"VGM","mano":"40","tempo":"35","marg":"20"}'
            ),
            "A"=>array(
                "txt"=>"Audi",
                "config"=>'{"dms":"VGM","mano":"50","tempo":"35","marg":"20"}'
            ),
            "P"=>array(
                "txt"=>"Porsche",
                "config"=>'{"dms":"POM","mano":"60","tempo":"50","marg":"40"}'
            )
        );

        $this->pneumatico=array(
            "L"=>"Barum",
            "B"=>"Bridgestone",
            "C"=>"Continental",
            "D"=>"Dunlop",
            "F"=>"Firestone",
            "A"=>"Fulda",
            "G"=>"Goodyear",
            "H"=>"Hankook",
            "M"=>"Michelin",
            "P"=>"Pirelli"
        );

        $this->tipo=array(
            "ZTS"=>"Estivo",
            "ZTW"=>"Invernale",
            "ZTR"=>"4 stagioni"
        );

        $this->mano=array(
            "40"=>"40,00",
            "50"=>"50,00",
            "60"=>"60,00"
        );

        $this->tempo=array(
            "25"=>"25ut (100ut)",
            "35"=>"35ut (140ut)",
            "50"=>"50ut (200ut)"
        );

        $this->margine=array(
            "0"=>"0,00",
            "10"=>"10,00",
            "15"=>"15,00",
            "20"=>"20,00",
            "25"=>"25,00",
            "30"=>"30,00",
            "40"=>"40,00",
            "50"=>"50,00"
        );

        $this->raggio=array(
            "4"=>"14",
            "5"=>"15",
            "6"=>"16",
            "7"=>"17",
            "8"=>"18",
            "9"=>"19",
            "0"=>"20",
            "1"=>"21",
            "2"=>"22",
            "3"=>"23"
        );

        $this->vel=array(
            "Q"=>"Q",
            "S"=>"S",
            "T"=>"T",
            "H"=>"H",
            "V"=>"V",
            "W"=>"W",
            "Y"=>"Y"
        );

        $this->dim=array(
            "16"=>"165",
            "17"=>"175",
            "18"=>"185",
            "19"=>"195",
            "20"=>"205",
            "21"=>"215",
            "22"=>"225",
            "23"=>"235",
            "24"=>"245",
            "25"=>"255",
            "26"=>"265",
            "27"=>"275",
            "28"=>"285",
            "29"=>"295",
            "30"=>"305",
            "31"=>"315",
            "32"=>"325"
        );
        
        $this->sconti=array(
            "P"=>array('ZTS'=>54,'ZTR'=>54,'ZTW'=>49),
            "C"=>array('ZTS'=>43,'ZTR'=>40,'ZTW'=>47),
            "L"=>array('ZTS'=>37,'ZTR'=>37,'ZTW'=>44),
            "B"=>array('ZTS'=>41,'ZTR'=>41,'ZTW'=>52),
            "F"=>array('ZTS'=>35,'ZTR'=>35,'ZTW'=>48),
            "G"=>array('ZTS'=>42,'ZTR'=>43,'ZTW'=>44),
            "D"=>array('ZTS'=>42,'ZTR'=>43,'ZTW'=>44),
            "A"=>array('ZTS'=>38,'ZTR'=>38,'ZTW'=>42),
            "M"=>array('ZTS'=>32,'ZTR'=>32,'ZTW'=>30),
            "H"=>array('ZTS'=>41,'ZTR'=>41,'ZTW'=>40)
        );
    }

    static function gommaioInit() {

        echo '<link href="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/sthor/classi/gommaio.css" type="text/css" rel="stylesheet"/>';
        echo '<script type="text/javascript" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/sthor/classi/gommaio.js?v='.time().'" />';
    }

    function draw() {

        $divo=new Divo('gom_'.$this->id,'30px','250px',1);

        $divo->divoInit();

        echo '<div class="gommaio" style="width:100%;height:100%;" >';

            echo '<div style="font-weight:bold;font-size:20px;text-align:center;">';
                echo '<span>G</span>';
                echo '<img style="position:relative;width:20px;height:20px;top:3px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/sthor/img/gomma.png" />';
                echo '<span>MMAI</span>';
                echo '<img style="position:relative;width:20px;height:20px;top:3px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/sthor/img/gomma.png" />';
            echo '</div>';

            echo '<div style="position:relative;margin-top:10px;" >';

                echo '<div style="position:relative;display:inline-block;vertical-align:top;width:50%;padding:5px;border-right:2px solid #777777;box-sizing:border-box;" >';

                    echo '<div style="margin-top:10px;" >';
                        echo '<label>Veicolo</label>';
                        echo '<div>';
                            echo '<select id="gommaio_vei_'.$this->id.'" onchange="window._gom_'.$this->id.'.set();" >';
                                foreach($this->veicolo as $k=>$o) {
                                    echo '<option value="'.$k.'" data-config="'.base64_encode($o['config']).'" >'.$o['txt'].'</option>';
                                }
                            echo '</select>';
                        echo '</div>';
                    echo '</div>';

                    echo '<div style="margin-top:10px;" >';
                        echo '<label>DMS</label>';
                        echo '<div>';
                            echo '<select id="gommaio_dms_'.$this->id.'">';
                                foreach($this->dms as $k=>$o) {
                                    echo '<option value="'.$k.'">'.$o.'</option>';
                                }
                            echo '</select>';
                        echo '</div>';
                    echo '</div>';

                    echo '<div style="margin-top:10px;" >';
                        echo '<label>Pneumatico</label>';
                        echo '<div>';
                            echo '<select id="gommaio_pneu_'.$this->id.'">';
                                echo '<option value="">Tutti</option>';
                                foreach($this->pneumatico as $k=>$o) {
                                    echo '<option value="'.$k.'">'.$o.'</option>';
                                }
                            echo '</select>';
                        echo '</div>';
                    echo '</div>';

                    echo '<div style="margin-top:10px;margin-bottom:10px;" >';
                        echo '<label>Tipo</label>';
                        echo '<div>';
                            echo '<select id="gommaio_tipo_'.$this->id.'">';
                                echo '<option value="">Tutti</option>';
                                foreach($this->tipo as $k=>$o) {
                                    echo '<option value="'.$k.'">'.$o.'</option>';
                                }
                            echo '</select>';
                        echo '</div>';
                    echo '</div>';

                echo '</div>';

                echo '<div style="position:relative;display:inline-block;vertical-align:top;width:50%;padding:5px;box-sizing:border-box;text-align: center;" >';
                                
                    echo '<div style="margin-top:10px;" >';
                        echo '<label>Manodopera / ora</label>';
                        echo '<div>';
                            echo '<select id="gommaio_mano_'.$this->id.'" style="text-align:center;" onchange="window._gom_'.$this->id.'.calcola();" ;>';
                                foreach($this->mano as $k=>$o) {
                                    echo '<option value="'.$k.'">'.$o.'</option>';
                                }
                            echo '</select>';
                        echo '</div>';
                    echo '</div>';

                    echo '<div style="margin-top:10px;" >';
                        echo '<label>Tempario cad.</label>';
                        echo '<div>';
                            echo '<select id="gommaio_tempo_'.$this->id.'" style="text-align:center;" onchange="window._gom_'.$this->id.'.calcola();" >';
                                foreach($this->tempo as $k=>$o) {
                                    echo '<option value="'.$k.'">'.$o.'</option>';
                                }
                            echo '</select>';
                        echo '</div>';
                    echo '</div>';

                    echo '<div style="margin-top:10px;margin-bottom:10px;" >';
                        echo '<label>Margine cad.</label>';
                        echo '<div>';
                            echo '<select id="gommaio_marg_'.$this->id.'" style="text-align:center;" onchange="window._gom_'.$this->id.'.calcola();" >';
                                foreach($this->margine as $k=>$o) {
                                    echo '<option value="'.$k.'">'.$o.'</option>';
                                }
                            echo '</select>';
                        echo '</div>';
                    echo '</div>';

                    echo '<div style="margin-bottom:10px;" >';

                        echo '<div style="position:relative;display:inline-block;vertical-align:top;width:29%;text-align:center;">';
                            echo '<label>D</label>';
                            echo '<div>';
                                echo '<select id="gommaio_D_'.$this->id.'" style="width: 90%;font-size: 1em;text-align:center;">';
                                    foreach($this->dim as $k=>$o) {
                                        echo '<option value="'.$k.'">'.$o.'</option>';
                                    }
                                echo '</select>';
                            echo '</div>';
                        echo '</div>';

                        echo '<div style="position:relative;display:inline-block;vertical-align:top;width:23%;text-align:center;">';
                            echo '<label>R</label>';
                            echo '<div>';
                                echo '<select id="gommaio_R_'.$this->id.'" style="width: 90%;font-size: 1em;text-align:center;">';
                                    echo '<option value=""></option>';
                                    foreach($this->raggio as $k=>$o) {
                                        echo '<option value="'.$k.'">'.$o.'</option>';
                                    }
                                echo '</select>';
                            echo '</div>';
                        echo '</div>';

                        echo '<div style="position:relative;display:inline-block;vertical-align:top;width:23%;text-align:center;">';
                            echo '<label>Qtà</label>';
                            echo '<div>';
                                echo '<select id="gommaio_Q_'.$this->id.'" style="width: 90%;font-size: 1em;text-align:center;">';
                                    echo '<option value="4">4</option>';
                                    echo '<option value="2">2</option>';
                                    echo '<option value="1">1</option>';
                                    echo '<option value="3">3</option>';
                                echo '</select>';
                            echo '</div>';
                        echo '</div>';

                        echo '<div style="position:relative;display:inline-block;vertical-align:top;width:25%;text-align:center;">';
                            echo '<label>Cerca</label>';
                            echo '<div>';
                               echo '<img style="width:20px;height:20px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/sthor/img/cerca.png" onclick="window._gom_'.$this->id.'.getList();"/>';
                            echo '</div>';
                        echo '</div>';

                    echo '</div>';

                echo '</div>';

            echo '</div>';

            echo '<div style="position:relative;border:1px solid#777777;margin-top:10px;width:100%;padding:10px;box-sizing:border-box;" >';

                $txt='<div id="gommaio_'.$this->id.'_totale"></div>';

                $divo->add_div('Totale','black',0,'',$txt,0,array());

                $txt='<div id="gommaio_'.$this->id.'_lista"></div>';

                $divo->add_div('Lista','black',0,'',$txt,0,array());

                ob_start();
                $this->drawLibero();

                $divo->add_div('Libero','black',0,'',ob_get_clean(),0,array());

                $divo->build();
                $divo->draw();

            echo '</div>';

            echo '<script type="text/javascript" >';
                echo 'window._gom_'.$this->id.'=new  gommaio(\''.$this->id.'\');';
                echo 'window._gom_'.$this->id.'.set();';
            echo '</script>';

        echo '</div>';
    }

    function drawLibero() {

        echo '<div>';

            echo '<div style="margin-top:5px;" >';
                echo '<label>Pneumatico</label>';
                echo '<div>';
                    echo '<select id="gomlibero_pneu_'.$this->id.'" style="widht:60%;">';
                        echo '<option value="">Tutti</option>';
                        foreach($this->pneumatico as $k=>$o) {
                            echo '<option value="'.$k.'" data-sconto="'.base64_encode(json_encode($this->sconti[$k])).'">'.$o.'</option>';
                        }
                    echo '</select>';
                echo '</div>';
            echo '</div>';

            echo '<div style="margin-top:10px;margin-bottom:5px;" >';
                echo '<label>Tipo</label>';
                echo '<div>';
                    echo '<select id="gomlibero_tipo_'.$this->id.'" style="widht:60%;">';
                        echo '<option value="">Tutti</option>';
                        foreach($this->tipo as $k=>$o) {
                            echo '<option value="'.$k.'">'.$o.'</option>';
                        }
                    echo '</select>';
                echo '</div>';
            echo '</div>';

            echo '<div style="margin-top:10px;margin-bottom:10px;" >';
                echo '<label>Descrizione</label>';
                echo '<div>';
                    echo '<input id="gomlibero_desc_'.$this->id.'" type="text" style="width:80%;" />';
                echo '</div>';
            echo '</div>';

            echo '<div style="margin-top:10px;margin-bottom:10px;" >';
                echo '<div style="position:relative;display:inline-block;vertical-align:top;width:30%;text-align:center;">';
                    echo '<label>Listino</label>';
                    echo '<input id="gomlibero_listino_'.$this->id.'" type="text" style="width:90%;text-align:center;" />';
                echo '</div>';
                echo '<div style="position:relative;display:inline-block;vertical-align:top;width:23%;text-align:center;">';
                    echo '<label>Qtà</label>';
                    echo '<div>';
                        echo '<select id="gomlibero_Q_'.$this->id.'" style="width: 90%;font-size: 1em;text-align:center;">';
                            echo '<option value="4">4</option>';
                            echo '<option value="2">2</option>';
                            echo '<option value="1">1</option>';
                            echo '<option value="3">3</option>';
                        echo '</select>';
                    echo '</div>';
                echo '</div>';
                echo '<div style="position:relative;display:inline-block;vertical-align:bottom;width:23%;text-align:center;">';
                        echo '<button onclick="window._gom_'.$this->id.'.conferma();">conferma</button>';
                echo '</div>';
            echo '</div>';

        echo '</div>';

    }

}
?>