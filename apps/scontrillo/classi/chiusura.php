<?php
include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/core/odl/wormhole.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/core/odl/odl_func.php');

class strilloChiusura {

    protected $param=array(
        "ID"=>"",
        "cassa"=>"",
        "dataora"=>"",
        "utente"=>"",
        "reparti"=>array()
    );

    protected $movimenti=array();
    protected $aperti=array();

    protected $galileo;
    protected $log=array();

    function __construct($param,$galileo) {
        
        foreach ($this->param as $k=>$v) {
            if (array_key_exists($k,$param)) {
                $this->param[$k]=$param[$k];
            }
        }

        $this->galileo=$galileo;

        /*TEST
        $m=array(
            array(
                "ID"=>1,
                "cassa"=>"C1",
                "chiusura"=>1,
                "rif_dms"=>"LO02_2024_12345",
                "dms"=>"infinity",
                "lista"=>"LO02",
                "desc_movimento"=>"Commessa Officina AUDI PESARO",
                "intest_ragsoc"=>"Cecconi Matteo",
                "importo"=>1000,
                "d_fatt"=>"20240905",
                "ambito"=>"OFF",
                "repNebula"=>"AUS"
            )
        );*/

        $this->galileo->clearQuery();
        $this->galileo->clearQueryOggetto('default','strillo');

        $a=array(
            "chiusura"=>$this->param['ID'],
            "cassa"=>$this->param['cassa']
        );

        $this->galileo->executeGeneric('strillo','getMovimenti',$a,'');

        if ($this->galileo->getResult()) {

            $fid=$this->galileo->preFetch('strillo');

            $mov=0;

            while ($row=$this->galileo->getFetch('strillo',$fid)) {

                if ($mov!=$row['ID']) {
                    $this->movimenti[$row['rif_dms']]=$row;
                    $this->movimenti[$row['rif_dms']]['incassi']=array();
                }

                $this->movimenti[$row['rif_dms']]['incassi'][$row['pos']]=array(
                    "importo"=>$row['importo'],
                    "incasso"=>$row['incasso']
                );

                $mov=$row['ID'];
            }
        }
    }

    function getLog() {
        return $this->log;
    }

    function getID() {
        return $this->param['ID'];
    }

    function caricaAperti($data_i) {

        $maps=array();

        $odlFunc=new nebulaOdlFunc($this->galileo);

        foreach ($this->param['reparti'] as $ambito=>$r) {

            foreach ($r as $k=>$rr) {

                $wh=new odielleWH($rr,$this->galileo);
                $wh->build( array("inizio"=>$data_i,"fine"=>date('Ymd')) );

                //$this->log[]=$wh->exportMap();

                foreach ($wh->exportMap() as $k=>$m) {
                    $m['piattaforma']=$wh->getPiattaforma($m['dms']);
                    $m['repNebula']=$rr;
                    $m['ambito']=$ambito;
                    $maps[]=$m;
                }
            }
        }

        foreach ($maps as $k=>$m) {
            $maps[$k]=$odlFunc->getScontrillo($m);
        }

        foreach ($maps as $k=>$m) {

            if ($m['result']) {
                $fid=$this->galileo->preFetchPiattaforma($m['piattaforma'],$m['result']);

                while ($row=$this->galileo->getFetchPiattaforma($m['piattaforma'],$fid)) {
                    $id=$row['tipo_doc'].'_'.substr($row['d_fatt'],0,4).'_'.$row['num_fatt'];
                    if (!array_key_exists($id,$this->movimenti)) {
                        $row['repNebula']=$m['repNebula'];
                        $row['ambito']=$m['ambito'];
                        $this->aperti[$id]=$row;
                    }
                }
            }
        }
    }

    function getSaldo() {
        return 0;
    }

    function drawAperti() {

        echo '<div style="width:94%;font-weight:normal;text-align:left;" >';

            foreach ($this->aperti as $id=>$a) {

                /*{
                    "d_fatt": "20240904",
                    "desc_movimento": "Lista di Vendita PESARO",
                    "dms": "infinity",
                    "gestione": "M",
                    "id_cliente": 122764,
                    "id_documento": 73125,
                    "importo": "53.000",
                    "intest_ragsoc": "RAZZI GIORGIO",
                    "magazzino": "01",
                    "num_fatt": 4815,
                    "officina": null,
                    "tipo_doc": "L101",
                    "tipo_intervento": "",
                    "ambito"="MAG",
                    "repNebula"="VGM"
                },*/

                $arr=array(
                    "rif_dms"=>$id,
                    "dms"=>$a['dms'],
                    "d_fatt"=>$a['d_fatt'],
                    "num_fatt"=>$a['num_fatt'],
                    "importo"=>number_format($a['importo'],2,'.',''),
                    "reparto"=>$a['repNebula'],
                    "desc_movimento"=>$a['desc_movimento'],
                    "intest_ragsoc"=>$a['intest_ragsoc']
                );

                echo '<div id="strillo_aperto_div_'.$id.'" style="position:relative;width:100%;margin-top:5px;margin-bottom:5px;border:1px solid #45803d;padding:3px;box-sizing:border-box;background-color:#c2f0c8;border-radius: 10px;" data-movimento="'.base64_encode(json_encode($arr)).'" >';

                    echo '<div style="position:relative;" >';

                        echo '<div style="position:relative;width:70%;display:inline-block;vertical-align:top;box-sizing:border-box;">';
                            echo '<span style="font-size:0.8em;">'.$a['repNebula'].' ('.$a['tipo_doc'].')</span>';
                            echo '<span style="font-size:1em;margin-left:5px;">Doc. '.$a['num_fatt'].'</span>';
                            echo '<span style="font-size:1em;margin-left:5px;">del '.mainFunc::gab_todata($a['d_fatt']).'</span>';
                        echo '</div>';

                        echo '<div id="strillo_aperto_importo_'.$id.'" style="position:relative;width:29%;display:inline-block;vertical-align:top;box-sizing:border-box;text-align:right;cursor:pointer;" data-importo="'.number_format($a['importo'],2,',','').'" onclick="window._strillo.setAperto(\''.$id.'\')" >';
                            echo '<button style="font-size:1em;font-weight:bold;border: 1px solid black;padding: 2px;border-radius: 5px 5px;text-align: center;top: 5px;position: relative;cursor:pointer;width:90%;background-color:#95caad;">'.number_format($a['importo'],2,',','.').'</button>';
                        echo '</div>';

                    echo '</div>';

                    echo '<div style="position:relative;" >';   
                        echo '<div style="font-size:0.9em;font-weight:bold;">'.substr($a['intest_ragsoc'],0,32).'</div>';
                    echo '</div>';

                    echo '<div style="position:relative;" >';   
                        echo '<div style="font-size:0.9em;font-weight:normal;">';
                            echo '<span style="font-size:0.9em;">'.substr($a['desc_movimento'],0,25).'</span>';
                        echo '</div>';
                    echo '</div>';

                    echo '<div style="position:relative;" >';

                        echo '<div style="position:relative;width:100%;" >';

                            echo '<div id="strillo_incasso_select_'.$id.'" style="position:relative;width:80%;display:inline-block;vertical-align:top;box-sizing:border-box;margin-top:8px;text-align:left;padding-left:3px;">';
                                /*echo '<select style="position:relative;width:90%;height:25px;text-align:center;background-color:inherit;font-weight:bold;" >';
                                    echo '<option>Seleziona tipo incasso...</option>';
                                echo '</select>';*/
                            echo '</div>';

                            echo '<div id="strillo_incasso_img_'.$id.'" style="position:relative;width:19%;display:inline-block;vertical-align:top;box-sizing:border-box;text-align:right;margin-top:8px;" >';
                                //echo '<button>Conferma</button>';
                                //echo '<img style="width:40px;cursor:pointer;top: -15px;position: relative;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/scontrillo/img/dollari.png" />';
                            echo '</div>';

                        echo '</div>';

                    echo '</div>';

                echo '</div>';
            }

        echo '</div>';
    }

}
?>