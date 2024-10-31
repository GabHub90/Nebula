<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/core/divo/divo.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_fidel.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/core/veicolo/classi/wormhole.php');
require_once('core/voucher_pdf.php');

class nebulaFidel {

    protected $index;

    protected $colori=['#dedcc4','#adc5ba'];

    protected $param=array(
        "tag"=>"",
        "ben1"=>"",
        "ben2"=>"",
        "dms"=>"",
        "utente"=>"",
        "marca"=>"",
        "punti"=>0,
        "odl"=>""
    );

    protected $listaTelai=array();

    protected $tipi=array();
    protected $offerte=array();
    protected $voucher=array();
    protected $attive=array();
    protected $storico=array();

    protected $galileo;

    function __construct($index,$galileo) {
        $this->index=$index;
        $this->galileo=$galileo;

        $obj=new galileoFidel();
        $gdef['fidel']=array("gab500",$obj);
        $this->galileo->setFunzioniDefault($gdef);
    }

    function build($param) {

        foreach ($this->param as $k=>$o) {
            if (array_key_exists($k,$param)) {
                $this->param[$k]=$param[$k];
            }
        }

        $today=date('Ymd');

        if ($this->index=='odl') {

            $this->galileo->executeSelect('fidel','FIDEL_tipi',"stato='1'",'');
            if ($this->galileo->getResult()) {
                $fid=$this->galileo->preFetch('fidel');
                while ($row=$this->galileo->getFetch('fidel',$fid)) {
                    $this->tipi[$row['tag']]=$row;
                }
            }

            $this->galileo->clearQuery();
            $this->galileo->clearQueryOggetto('default','fidel'); 

            $this->galileo->executeSelect('fidel','FIDEL_offerte',"",'');
            if ($this->galileo->getResult()) {
                $fid=$this->galileo->preFetch('fidel');
                while ($row=$this->galileo->getFetch('fidel',$fid)) {
                    
                    if (array_key_exists($row['tipo'],$this->tipi) && strpos($row['marca'],$this->param['marca']) && $today>=$row['data_i'] && $today<=$row['data_f']) {
                    
                        if ($arr=json_decode($row['offerta'],true)) {
        
                            foreach ($arr as $ko=>$o) {
                                if ($this->param['punti']>=$o[0]) {
                                    $this->offerte[$row['tipo']][$row['ID']]=$row;
                                    $this->offerte[$row['tipo']][$row['ID']]['offerta']=$o;
                                }
                            }
                        }
                    }
                }
            }
        }

        $this->galileo->clearQuery();
        $this->galileo->clearQueryOggetto('default','fidel');

        $this->galileo->executeSelect('fidel','FIDEL_voucher',"tag='".$this->param['tag']."'",'scadenza');
        if ($this->galileo->getResult()) {
            $fid=$this->galileo->preFetch('fidel');
            while ($row=$this->galileo->getFetch('fidel',$fid)) {

                if ($row['stato']=='aperto') {

                    if ($today>$row['scadenza']) {
                        //se è scaduto aggiorna il record
                        $a=array(
                            "stato"=>"scaduto"
                        );
                        $this->galileo->executeUpdate('fidel','FIDEL_voucher',$a);
                        $row['stato']='scaduto';
                    }

                    $this->voucher[$row['ID']]=$row;
                    if ($row['template'] && $row['template']!="") $this->attive[$row['template']]=true;
                }
                else {
                    $this->storico[$row['ID']]=$row;
                }
            }
        }


        //########################################
        /*TEST
        $temp=array(
            array(
                "ID"=>1,
                "data_i"=>'20240101',
                "data_f"=>'20240630',
                "tipo"=>'attiva',
                "marca"=>'_V_C_S_N_',
                "titolo"=>"Dischi e Pastiglie",
                "durata"=>180,
                "offerta"=>'[[0,"Sconto 25%"]]',
                "nota"=>"Stima durata freni km:"
            ),
            array(
                "ID"=>2,
                "data_i"=>'20240101',
                "data_f"=>'20240630',
                "tipo"=>'attiva',
                "marca"=>'_V_C_S_N_',
                "titolo"=>"Batteria",
                "durata"=>180,
                "offerta"=>'[[0,"Sconto 35%"]]',
                "nota"=>""
            ),
            array(
                "ID"=>3,
                "data_i"=>'20240101',
                "data_f"=>'20240630',
                "tipo"=>'attiva',
                "marca"=>'_V_C_S_N_',
                "titolo"=>"Spazzole",
                "durata"=>180,
                "offerta"=>'[[0,"Sconto 45%"]]',
                "nota"=>""
            )
        );

        $today=date('Ymd');

        foreach ($temp as $k=>$row) {

            if (array_key_exists($row['tipo'],$this->tipi) && $this->tipi[$row['tipo']]['stato']==1 && strpos($row['marca'],$this->param['marca']) && $today>=$row['data_i'] && $today<=$row['data_f']) {
                
                if ($arr=json_decode($row['offerta'],true)) {

                    foreach ($arr as $ko=>$o) {
                        if ($this->param['punti']>=$o[0]) {
                            $this->offerte[$row['tipo']][$row['ID']]=$row;
                            $this->offerte[$row['tipo']][$row['ID']]['offerta']=$o;
                        }
                    }
                }
            }
        }*/

        //########################################
    }


    public static function initJS() {
        echo '<script type="text/javascript" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/fidel/fidel.js?v='.time().'" />';
    }

    function getListaTelai($tt) {

        $wh=new veicoloWH('',$this->galileo);

        foreach ($wh->getDmss() as $k=>$d) {

            $map=$wh->linkerSearch($d,$tt);

            if ($map['result']) {
                $fid=$this->galileo->preFetchPiattaforma($map['piattaforma'],$map['result']);

                while ($row=$this->galileo->getFetchPiattaforma($map['piattaforma'],$fid)) {
                    
                    if ($row['telaio']=='') continue;
                    if ($row['cod_anagra_util']=='' && $row['cod_anagra_intest']=='' && $row['cod_anagra_locat']=='') continue;

                    if (!array_key_exists($row['telaio'],$this->listaTelai)) {
                        $this->listaTelai[$row['telaio']]=array();
                        $this->listaTelai[$row['telaio']][]=$row;
                    }

                    else {
                        $i=true;
                        foreach ($this->listaTelai[$row['telaio']] as $k=>$t) {
                            //if ($row['cod_anagra_util']==$t['cod_anagra_util'] || $row['cod_anagra_intest']==$t['cod_anagra_intest'] || $row['cod_anagra_util']==$t['cod_anagra_intest']) {
                            //if ($row['cod_anagra_util']==$t['cod_anagra_util'] || ( $row['cod_anagra_intest']==$t['cod_anagra_intest'] && $row['cod_anagra_util']==$t['cod_anagra_util']) ) {
                            if ($row['cod_anagra_intest']==$t['cod_anagra_intest'] && $row['cod_anagra_util']==$t['cod_anagra_util']) {
                                $i=false;
                                break;
                            }
                        }
                        if ($i) $this->listaTelai[$row['telaio']][]=$row;
                    }
                }
            }
        }
    }

    function draw() {

        $this->refresh();

        echo '<script type="text/javascript">';
            echo "temp='".base64_encode(json_encode($this->param))."';";
            echo "window._fidel_".$this->index."= new nebulaFidel('".$this->index."',temp);";
        echo '</script>';

    }

    function drawInterface($width,$height) {

        echo '<script type="text/javascript">';
            echo "window._fidel_".$this->index."= new nebulaFidel('".$this->index."','');";
        echo '</script>';

        echo '<div style="width:'.$width.'px;height:'.$height.'px;" >';

            echo '<div style="height:12%;border-bottom:1px dotted #777777;" >';
                echo '<div>Ricerca Vettura (più di 2 caratteri richiesti):</div>';
                echo '<input id="fidel_'.$this->index.'_interface_search" style="width:300px;" type="text" />';
                echo '<button style="margin-left:15px;" onclick="window._fidel_'.$this->index.'.getTT();" >cerca</button>';
            echo '</div>';

            echo '<div id="fidel_'.$this->index.'_interface_viewer" style="height:88%;padding:5px;box-sizing:border-box;" >';
            echo '</div>';

        echo '</div>';
    }

    function drawListaTelai($tt) {

        $this->getListaTelai($tt);

        echo '<div style="position:relative;width:100%;height:100%;overflow:scroll;overflow-x:hidden;" >';
            
            $col=1;

            foreach ($this->listaTelai as $telaio=>$lista) {

                $col=($col==1)?0:1;

                foreach ($lista as $k=>$l) {

                    $a=array(
                        "tag"=>$l['telaio'],
                        "ben1"=>$l['des_veicolo'],
                        "ben2"=>($l['ragsoc_util']!="")?$l['ragsoc_util']:$l['ragsoc_intest'],
                        "dms"=>$l['dms'],
                        "marca"=>$l['cod_marca'],
                        "punti"=>0,
                        "odl"=>""
                    );

                    echo '<div style="position:relative;width:97%;margin-top:8px;margin-bottom:8px;padding:5px;box-sizing:border-box;border:1px solid black;border-radius:6px;box-shadow: 3px 3px #bbbbbb;background-color:'.$this->colori[$col].';cursor:pointer;" data-info="'.base64_encode(json_encode($a)).'" onclick="window._fidel_'.$this->index.'.setTT(this);" >';

                        echo '<div style="position:relative;display:inline-block;width:2%;">('.substr($l['dms'],0,1).')</div>';
                        echo '<div style="position:relative;display:inline-block;width:18%;font-weight:bold;">'.$l['telaio'].'</div>';
                        echo '<div style="position:relative;display:inline-block;width:9%;font-weight:bold;">'.$l['targa'].'</div>';
                        echo '<div style="position:relative;display:inline-block;width:8%;">'.$l['modello'].'</div>';
                        echo '<div style="position:relative;display:inline-block;width:15%;font-size:0.9em;">'.substr($l['des_veicolo'],0,20).'</div>';
                        echo '<div style="position:relative;display:inline-block;width:24%;font-weight:bold;"><span style="font-size:0.8em;font-weight:normal;">Util:&nbsp;</span>'.strtolower(substr($l['ragsoc_util'],0,27)).'</div>';
                        echo '<div style="position:relative;display:inline-block;width:24%;font-weight:bold;"><span style="font-size:0.8em;font-weight:normal;">Inte:&nbsp;</span>'.strtolower(substr($l['ragsoc_intest'],0,27)).'</div>';

                    echo '</div>';
                }
            }

            //echo '<div>'.json_encode($this->wh->getLog()).'</div>';

        echo '</div>';
    }

    function refresh() {

        $fideldivo=new Divo('fidel_'.$this->index,'8%','92%',true);

        $fideldivo->setBk('#f5f5db');

        $css=array(
            "font-weight"=>"bold",
            "font-size"=>"0.8em",
            "margin-left"=>"8px",
            "margin-top"=>"0px"
        );

        ob_start();
            $this->drawOfferte();
        $fideldivo->add_div('Offerte','black',0,'',ob_get_clean(),1,$css);

        ob_start();
            $this->drawNuova();
        $fideldivo->add_div('Nuovo','black',0,'',ob_get_clean(),0,$css);

        ob_start();
            $this->drawStorico();
        $fideldivo->add_div('Storico','black',0,'',ob_get_clean(),0,$css);

        $fideldivo->build();

        echo '<div id="nebulaFidel_'.$this->index.'">';

            $fideldivo->draw();

        echo '</div>';
    }

    function drawOffer($o) {
        echo '<div style="font-size:0.9em;">'.$this->tipi[$o['tipo']]['titolo'].'</div>';
        echo '<div style="font-weight:bold;">'.$o['titolo'].' - '.$o['offerta'][1].'</div>';
    }

    function drawVoucher($v) {
        echo '<div style="font-size:0.9em;">';
            echo '<div style="position:relative;display:inline-block;vertical-align:top;width:50%;" >'.mainFunc::gab_todata($v['creazione']).' - '.$v['utente'].'</div>';
            echo '<div style="position:relative;display:inline-block;vertical-align:top;width:50%;text-align:right;font-weight:bold;" >';
                if ($v['stato']=='aperto' && $this->index=='odl') {
                    echo '<img style="position:relative;width:15px;height:15px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/fidel/img/trash.png" onclick="window._fidel_'.$this->index.'.annulla(\''.$v['ID'].'\');"/>';
                }
                echo '<span style="margin-left:3px;">'.mainFunc::gab_todata($v['scadenza']).'</span>';
            echo '</div>';
        echo'</div>';
        echo '<div style="font-weight:bold;">'.$v['titolo'].' - '.$v['offerta'].'</div>';
        echo '<div style="font-size:0.9em;" >'.$v['nota'].'</div>';
    }

    function drawAttive() {
        echo '<div style="font-weight:bold;font-size:0.9em;" >';
            echo '<span>Voucher Attivi:</span>';
            echo '<img style="position:relative;margin-left:10px;width:20px;height:20px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/fidel/img/print.png" onclick="window._fidel_'.$this->index.'.print();"/>';
        echo '</div>';

        foreach ($this->voucher as $kv=>$v) {
            //scrittura Voucher Attivi
            //{"1":{"ID":1,"tag":"WVWZZZ3CZPE016030","dms":"infinity","utente":"m.cecconi","creazione":"20240404","template":1,"stato":"aperto","chiusura":"","utente_chiusura":"","dms_chiusura":"","odl_chiusura":0,"titolo":"Dischi e Pastiglie","offerta":"Sconto 25%","nota":"Stima durata freni:","scadenza":"20241001","ben1":"PASSAT VARIANT 2.0 TDI BUSINESS DSG 122C MY 21","ben2":"VOLKSWAGEN LEASING GMBH SEDE SECONDARIA MILANO"}}
            echo '<div style="position:relative;margin-top:5px;width:90%;border:1px solid black;padding:3px;box-sizing:border-box;min-height:42px;background-color:#d1dae8;">';
                echo '<div style="position:relative;display:inline-block;vertical-align:top;width:80%;">';
                    //ob_start();
                        $this->drawVoucher($v);
                    //echo ob_get_clean();
                echo '</div>';
                echo '<div style="position:relative;display:inline-block;vertical-align:top;width:20%;text-align:center;min-height:inherit;">';
                    echo '<button style="position:absolute;top: 50%;left:50%;transform: translate(-50%, -50%);" onclick="window._fidel_'.$this->index.'.usa(\''.$v['ID'].'\');" >USA</button>';
                echo '</div>';
            echo '</div>';
        }
    }

    function drawStorico() {

        foreach ($this->storico as $kv=>$v) {
            //scrittura Voucher Attivi
            //{"1":{"ID":1,"tag":"WVWZZZ3CZPE016030","dms":"infinity","utente":"m.cecconi","creazione":"20240404","template":1,"stato":"aperto","chiusura":"","utente_chiusura":"","dms_chiusura":"","odl_chiusura":0,"titolo":"Dischi e Pastiglie","offerta":"Sconto 25%","nota":"Stima durata freni:","scadenza":"20241001","ben1":"PASSAT VARIANT 2.0 TDI BUSINESS DSG 122C MY 21","ben2":"VOLKSWAGEN LEASING GMBH SEDE SECONDARIA MILANO"}}
            echo '<div style="position:relative;margin-top:5px;width:90%;border:1px solid black;padding:3px;box-sizing:border-box;min-height:42px;background-color:#f3e9d5;">';
                echo '<div style="position:relative;display:inline-block;vertical-align:top;width:80%;">';
                    //ob_start();
                        $this->drawVoucher($v);
                    //echo ob_get_clean();
                echo '</div>';
                echo '<div style="position:relative;display:inline-block;vertical-align:top;width:20%;text-align:center;min-height:inherit;">';
                    echo '<div style="font-weight:bold;font-size:0.9em;" >'.$v['stato'].'</div>';
                    echo '<div style="font-size:0.9em;" >'.mainFunc::gab_todata($v['chiusura']).'</div>';
                    echo '<div style="font-size:0.9em;" >'.$v['utente_chiusura'].'</div>';
                echo '</div>';
            echo '</div>';
        }
    }

    function drawOfferte() {

        if (count($this->voucher)>0) {
            
            $this->drawAttive();

            echo '<hr/>';
        }

        echo '<div style="font-weight:bold;font-size:0.9em;" >Offerte attivabili:</div>';

        foreach ($this->offerte as $tipo=>$t) {
            foreach ($t as $kt=>$o) {

                //se c'è un voucher "aperto" per la stessa offerta saltala
                if (array_key_exists($o['ID'],$this->attive)) continue;

                echo '<div style="position:relative;margin-top:5px;width:90%;border:1px solid black;padding:3px;box-sizing:border-box;height:42px;background-color:#e8d1e8;">';
                    echo '<div style="position:relative;display:inline-block;vertical-align:top;width:80%;">';
                        ob_start();
                            $this->drawOffer($o);
                        echo ob_get_clean();
                    echo '</div>';
                    echo '<div style="position:relative;display:inline-block;vertical-align:top;width:20%;text-align:center;height:100%;">';
                        echo '<button style="position:relative;top:50%;transform:translate(0,-50%);" data-info="'.base64_encode(json_encode($o)).'" onclick="window._fidel_'.$this->index.'.scegli(this);" >Scegli</button>';
                    echo '</div>';
                echo '</div>';
            }

            $this->drawStorico();
        }

        //echo '<div>'.json_encode($this->param).'</div>';
    }

    function drawLista() {
        echo '<div style="font-weight:bold;font-size:0.9em;">Voucher per il telaio:</div>';
        echo '<div style="position:relative;width:80%;border-top:1px solid #777777;border-bottom:1px solid #777777;padding-top:5px;padding-bottom:5px;margin-bottom:10px;">';
            foreach ($this->voucher as $kv=>$v) {
                //scrittura Voucher Attivi
                //{"1":{"ID":1,"tag":"WVWZZZ3CZPE016030","dms":"infinity","utente":"m.cecconi","creazione":"20240404","template":1,"stato":"aperto","chiusura":"","utente_chiusura":"","dms_chiusura":"","odl_chiusura":0,"titolo":"Dischi e Pastiglie","offerta":"Sconto 25%","nota":"Stima durata freni:","scadenza":"20241001","ben1":"PASSAT VARIANT 2.0 TDI BUSINESS DSG 122C MY 21","ben2":"VOLKSWAGEN LEASING GMBH SEDE SECONDARIA MILANO"}}
                echo '<div style="position:relative;margin-top:5px;width:90%;border:1px solid black;padding:3px;box-sizing:border-box;min-height:42px;background-color:#d1dae8;">';
                    echo '<div style="position:relative;display:inline-block;vertical-align:top;width:80%;">';
                        //ob_start();
                            $this->drawVoucher($v);
                        //echo ob_get_clean();
                    echo '</div>';
                    echo '<div style="position:relative;display:inline-block;vertical-align:top;width:20%;text-align:center;min-height:inherit;">';
                        echo '<div style="font-weight:bold;font-size:0.9em;" >'.$v['stato'].'</div>';
                        //echo '<button style="position:absolute;top: 50%;left:50%;transform: translate(-50%, -50%);" onclick="window._fidel_'.$this->index.'.usa(\''.$v['ID'].'\');" >USA</button>';
                    echo '</div>';
                echo '</div>';
            }
        echo '</div>';
    }

    function drawNuova() {

        echo '<div style="position:relative;width:95%;border:1px solid black;padding:3px;box-sizing:border-box;background-color:#eeeeee;">';

            echo '<div>';
                echo '<div style="position:relative;display:inline-block;vertical-align:top;width:50%;font-weight:bold;">'.$this->param['tag'].'</div>';
                echo '<div style="position:relative;display:inline-block;vertical-align:top;width:50%;font-size:0.9em;text-align:right;">'.$this->param['dms'].' - '.$this->param['utente'].'</div>';
            echo '</div>';

            echo '<div>';
                echo '<div style="position:relative;display:inline-block;vertical-align:top;width:50%;font-size:0.9em;">'.substr($this->param['ben1'],0,27).'</div>';
                echo '<div style="position:relative;display:inline-block;vertical-align:top;width:50%;font-size:0.9em;">'.substr($this->param['ben2'],0,27).'</div>';
            echo '</div>';

        echo '</div>';

        echo '<div style="margin-top:10px;width:90%;">';
            echo '<div style="position:relative;display:inline-block;vertical-align:top;width:20%;text-align:center;">';
                echo '<button onclick="window._fidel_'.$this->index.'.generico();">Generico</button>';
            echo '</div>';
            echo '<div id="fidel_'.$this->index.'_tipo" style="position:relative;display:inline-block;vertical-align:top;width:80%;"></div>';
            echo '<input id="fidel_'.$this->index.'_tipoid" type="hidden" value="" />';
        echo '</div>';

        echo '<div style="margin-top:10px;width:90%;">';
            echo '<div id="fidel_'.$this->index.'_titolo_label" class="fidel_label" style="font-weight:bold;font-size:0.9em;">Titolo:</div>';
            echo '<div style="width:100%;">';
                echo '<input id="fidel_'.$this->index.'_titolo" type="text" style="width:90%;" value="" />';
            echo '</div>';
        echo '</div>';

        echo '<div style="margin-top:10px;width:90%;">';
            echo '<div id="fidel_'.$this->index.'_offerta_label" class="fidel_label" style="font-weight:bold;font-size:0.9em;">Offerta:</div>';
            echo '<div style="width:100%;">';
                echo '<input id="fidel_'.$this->index.'_offerta" type="text" style="width:90%;" value="" />';
            echo '</div>';
        echo '</div>';

        echo '<div style="margin-top:10px;width:90%;">';
            echo '<div style="font-weight:bold;font-size:0.9em;">Nota:</div>';
            echo '<div style="width:100%;">';
                echo '<input id="fidel_'.$this->index.'_nota" type="text" style="width:90%;" value="" />';
            echo '</div>';
        echo '</div>';

        echo '<div style="margin-top:10px;widht:90%;">';
            echo '<div id="fidel_'.$this->index.'_scadenza_label" class="fidel_label" style="font-weight:bold;font-size:0.9em;">Scadenza:</div>';
            echo '<div style="width:100%;">';
                echo '<div style="position:relative;display:inline-block;vertical-align:top;width:50%;">';
                    echo '<input id="fidel_'.$this->index.'_scadenza" type="date" style="width:90%;" value="" />';
                echo '</div>';
                echo '<div style="position:relative;display:inline-block;vertical-align:top;width:50%;">';
                    echo '<button onclick="window._fidel_'.$this->index.'.conferma();">Conferma</button>';
                echo '</div>';
            echo '</div>';
        echo '</div>';
    }

    function print() {

        $pdf=new fidelPDF('P','mm','A4');

        $pdf->loadParam($this->param);

        $pdf->AddPage();

        foreach ($this->voucher as $kv=>$v) {
            $pdf->drawVoucher($v);
        }

        return $pdf->exportCommessa_b64();
    }

    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    function newVoucher($v) {

        $a=array(
            "tag"=>"",
            "dms"=>"",
            "utente"=>"",
            "creazione"=>"",
            "template"=>"",
            "stato"=>"",
            "chiusura"=>"",
            "utente_chiusura"=>"",
            "dms_chiusura"=>"",
            "odl_chiusura"=>"",
            "titolo"=>"",
            "offerta"=>"",
            "nota"=>"",
            "scadenza"=>"",
            "ben1"=>"",
            "ben2"=>""
        );

        foreach ($a as $k=>$o) {
            if (array_key_exists($k,$v)) {
                $a[$k]=$v[$k];
            }
        }

        $this->galileo->executeInsert('fidel','FIDEL_voucher',$a);
    }

    function usa($id,$arr) {

        $this->galileo->executeUpdate('fidel','FIDEL_voucher',$arr,"ID='".$id."'");
    }

    function annulla($id,$arr) {

        $this->galileo->executeUpdate('fidel','FIDEL_voucher',$arr,"ID='".$id."'");
    }

}