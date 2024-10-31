<?php
include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/apps/scontrillo/classi/chiusura.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/apps/scontrillo/classi/provvisori.php');
require_once ($_SERVER['DOCUMENT_ROOT']."/nebula/core/divo/divo.php");

class strilloCassa {

    protected $param=array(
        "ID"=>"",
        "nome"=>"",
        "data_i"=>"",
        "data_f"=>"",
        "reparti"=>array(),
        "fondocassa"=>0,
        "saldo"=>0,
        "ultimaChiusura"=>array(),
        "chiusuraAttuale"=>false
    );

    protected $tipiIncasso=array();

    protected $aperti=array();
    protected $provvisori;

    protected $galileo;

    function __construct($param,$galileo) {

        foreach ($this->param as $k=>$v) {
            if (array_key_exists($k,$param)) {
                $this->param[$k]=($k=='reparti')?json_decode($param[$k],true):$param[$k];
            }
        }

        $this->galileo=$galileo;

        //TIPI INCASSO
        $r=array(
            array( 
                "codice"=>"pos",
                "tag"=>"POS",
                "pos"=>1,
                "saldo"=>0,
                "stato"=>1
            ),
            array( 
                "codice"=>"con",
                "tag"=>"CONTANTI",
                "pos"=>2,
                "saldo"=>1,
                "stato"=>1
            ),
            array( 
                "codice"=>"ass",
                "tag"=>"ASSEGNO",
                "pos"=>3,
                "saldo"=>0,
                "stato"=>1
            ),
            array( 
                "codice"=>"bon",
                "tag"=>"BONIFICO",
                "pos"=>4,
                "saldo"=>0,
                "stato"=>1
            ),
            array( 
                "codice"=>"rib",
                "tag"=>"RIBA",
                "pos"=>5,
                "saldo"=>0,
                "stato"=>1
            )
        );

        foreach($r as $k=>$row) {
            $this->tipiIncasso[$row['codice']]=$row;
        }
        $this->tipiIncasso['xxx']=array(
            "codice"=>"xxx",
            "tag"=>"ESCLUSIONE",
            "pos"=>0,
            "saldo"=>0
        );

        //ULTIMA CHIUSURA
        //TEST
        $q=array(
            array(
                "ID"=>1,
                "cassa"=>"C1",
                "dataora"=>"20240907:18:25",
                "utente"=>"m.cecconi",
                "documento"=>'{"concerto":170000,"inifinity":60000}'
            )
        );

        foreach ($q as $k=>$row) {
            $this->param['ultimaChiusura']=$row;
        } 
        //END TEST

        //ULTIMA ATTUALE
        //TEST
        $q=array(
            array(
                "ID"=>2,
                "cassa"=>"C1"
            )
        );

        foreach ($q as $k=>$row) {
            $row['reparti']=$this->param['reparti'];
            $this->param['chiusuraAttuale']=new strilloChiusura($row,$this->galileo);
        } 
        //END TEST

        if (!$this->param['chiusuraAttuale'] instanceof strilloChiusura) die ('Chiusura Attuale non caricata!!!');

        $this->provvisori=new strilloProvvisori();

        $this->param['saldo']=$this->param['fondocassa']+$this->param['chiusuraAttuale']->getSaldo()+$this->provvisori->getSaldo();

        $this->getAperti();
    }

    function getAperti() {

        $d=substr($this->param['ultimaChiusura']['dataora'],0,8);
        $this->param['chiusuraAttuale']->caricaAperti( ($d!="")?$d:date('Ymd') );

    }

    function draw() {
        //echo json_encode($this->param);

        $divo1=new Divo('scontrillo','5%','97%',1);
        $divo1->setBk('#aae476');

        $css=array(
            "margin-left"=>"10px",
            "margin-right"=>"5px",
            "font-weight"=>"bold",
            "text-align"=>"center"
        );

        $txt="";

        ob_start();
            $this->drawSaldo();

        $divo1->add_div('Saldo','black',0,'',ob_get_clean(),0,$css);

        $divo1->add_div('Nuovo Movimento','black',0,'',$txt,0,$css);
    
        $divo1->add_div('Chiusura Attuale','black',0,'',$txt,0,$css);

        
        echo '<div class="sct_title" style="font-weight:bold;padding:5px;width:100%;height:5%;box-sizing:border-box;line-height: 15px;" >';
            echo '<span style="font-size:0.9em;margin-right:20px" >'.$this->param['ID'].'</span>';
            echo '<span style="font-size:1.4em;">'.$this->param['nome'].'</span>';
            echo '<span style="font-size:0.9em;margin-left:20px" >Reparti: ';
            $temp=" ";
            foreach ($this->param['reparti'] as $k=>$r) {
                foreach ($r as $kk=>$rr) {
                    $temp.=$rr.' - ';
                }
            }
            echo substr($temp,0,-3).'</span>';
        echo '</div>';
        
        echo '<div style="width:100%;height:95%;">';

            $divo1->build();
            $divo1->draw();

        echo '</div>';

        unset($divo1);
    }

    function drawSaldo() {

        echo '<div class="sct_saldo_div" style="width:32%;" >';

            echo '<table class="sct_saldo_tab" style="" >';

                echo '<colgroup>';
                    echo '<col span="1" style="width:220px;" />';
                    echo '<col span="1" style="width:250px;" />';
                echo '</colgroup>';

                echo '<tbody>';

                    echo '<tr>';
                        echo '<td style="" >Fondocassa</td>';
                        echo '<td style="text-align:left;" >'.number_format($this->param['fondocassa'],2,',','.').'</td>';
                    echo '</tr>';

                    echo '<tr>';
                        echo '<td style="" >Ultima Chiusura</td>';
                        echo '<td style="text-align:left;" >';
                            echo '<div style="font-size:0.9em;" >'.mainFunc::gab_todata(substr($this->param['ultimaChiusura']['dataora'],0,8)).' '.substr($this->param['ultimaChiusura']['dataora'],9,5).'</div>';
                            echo '<div style="font-size:0.9em;" >'.$this->param['ultimaChiusura']['utente'].' ('.$this->param['ultimaChiusura']['ID'].')</div>';
                        echo '</td>';
                    echo '</tr>';

                    echo '<tr style="text-align:left;font-size:1.2em;font-weight:bold;">';
                        echo '<td style="" >SALDO</td>';
                        echo '<td style="" >'.number_format($this->param['saldo'],2,',','.').'</td>';
                    echo '</tr>';

                echo '</tbody>';

            echo '</table>';

        echo '</div>';

        echo '<div class="sct_saldo_div" style="width:32%;margin-left:1%;" >';

            echo '<div style="text-align:center;font-weight:bold;height:5%;font-size:1.2em;">Provvisorio</div>';

            echo '<div style="text-align:center;font-weight:bold;height:95%;width:100%;overflow:scroll;overvlow-x:hidden;">';

                //##########################
                //lista movimenti provvisori
                //##########################

            echo '</div>';

        echo '</div>';

        echo '<div class="sct_saldo_div" style="width:34%;margin-left:1%;" >';

            echo '<div style="text-align:center;font-weight:bold;height:5%;font-size:1.2em;">Movimenti Aperti</div>';

            echo '<div style="text-align:center;font-weight:bold;height:95%;width:100%;overflow:scroll;overvlow-x:hidden;">';

                //##########################
                //lista movimenti aperti
                echo '<div>'.($this->param['chiusuraAttuale']->drawAperti()).'</div>';
                //##########################

            echo '</div>';

        echo '</div>';

        echo '<script type="text/javascript" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/scontrillo/core/strillo.js" ></script>';
        echo '<script type="text/javascript" >';
            echo 'window._strillo=new scontrillo("'.$this->param['ID'].'",'.$this->param['chiusuraAttuale']->getID().');';
            echo 'window._strillo.setIncassi("'.base64_encode(json_encode($this->tipiIncasso)).'");';
        echo '</script>';

    }

}
?>