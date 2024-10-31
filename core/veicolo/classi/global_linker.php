<?php
require_once(DROOT.'/nebula/core/veicolo/classi/wormhole.php');

class nebulaGlobalLinker {

    protected $param=array(
        "contesto"=>""
    );

    protected $colori=['#dedcc4','#adc5ba'];

    protected $listaTelai=array();

    protected $wh=false;
    protected $galileo;

    function __construct($param,$galileo) {

        $this->galileo=$galileo;

        $this->wh=new veicoloWH('',$this->galileo);

        foreach ($this->param as $k=>$o) {
            if (array_key_exists($k,$param)) $this->param[$k]=$param[$k];
        }
        
    }

    static function drawJS() {
        echo '<script type="text/javascript" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/veicolo/global_linker.js?v='.time().'"></script>';
    }

    function getTelai($dms,$txt) {
        //DMS= 'infinity','concerto','tutti'
        //TXT= stringa da cercare

        foreach ($this->wh->getDmss() as $k=>$d) {

            if ($dms!='tutti' && $dms!=$d) continue;

            $map=$this->wh->linkerSearch($d,$txt);

            if ($map['result']) {
                $fid=$this->galileo->preFetchPiattaforma($map['piattaforma'],$map['result']);

                while ($row=$this->galileo->getFetchPiattaforma($map['piattaforma'],$fid)) {
                    
                    if ($row['telaio']=='') continue;
                    if ($row['ragsoc_util']=='' && $row['ragsoc_intest']=='' && $row['ragsoc_locat']=='') continue;

                    if (!array_key_exists($row['telaio'],$this->listaTelai)) {
                        $this->listaTelai[$row['telaio']]=array();
                        $this->listaTelai[$row['telaio']][]=$row;
                    }

                    else {
                        $i=true;
                        foreach ($this->listaTelai[$row['telaio']] as $k=>$t) {
                            if ($row['ragsoc_util']==$t['ragsoc_util'] || ( $row['ragsoc_intest']==$t['ragsoc_intest'] && $row['ragsoc_util']==$t['ragsoc_util']) ) {
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

    function drawListaTelai() {

        echo '<div style="position:relative;width:100%;height:100%;overflow:scroll;overflow-x:hidden;" >';
            
            $col=1;

            foreach ($this->listaTelai as $telaio=>$lista) {

                $col=($col==1)?0:1;

                foreach ($lista as $k=>$l) {

                    echo '<div style="position:relative;width:97%;margin-top:8px;margin-bottom:8px;padding:5px;box-sizing:border-box;border:1px solid black;border-radius:6px;box-shadow: 3px 3px #bbbbbb;background-color:'.$this->colori[$col].';cursor:pointer;" onclick="window._globalLinker.selectLink(\''.$l['dms'].'\',\''.$l['telaio'].'\');" >';

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

        echo '</div>';

    }

    function drawListaTelaiErmes() {

        echo '<div style="position:relative;width:100%;height:100%;overflow:scroll;overflow-x:hidden;" >';
            
            $col=1;

            foreach ($this->listaTelai as $telaio=>$lista) {

                $col=($col==1)?0:1;

                foreach ($lista as $k=>$l) {

                    echo '<div style="position:relative;width:90%;margin-top:8px;margin-bottom:8px;padding:5px;box-sizing:border-box;border:1px solid black;border-radius:6px;box-shadow: 3px 3px #bbbbbb;background-color:'.$this->colori[$col].';cursor:pointer;" onclick="window._globalLinker.selectLinkErmes(\''.base64_encode(json_encode($l)).'\');" >';

                        echo '<div style="position:relative;font-size:0.9em;" >';
                            echo '<div style="position:relative;display:inline-block;width:5%;">('.substr($l['dms'],0,1).')</div>';
                            echo '<div style="position:relative;display:inline-block;width:35%;font-weight:bold;">'.$l['targa'].'</div>';
                            echo '<div style="position:relative;display:inline-block;width:60%;font-size:0.9em;">'.substr($l['des_veicolo'],0,20).'</div>';
                        echo '</div>';
                        if ($l['ragsoc_util']!='') {
                            echo '<div style="position:relative;font-size:0.9em;" >';
                                echo '<div style="position:relative;display:inline-block;width:50%;font-weight:bold;">'.strtolower(substr($l['ragsoc_util'],0,27)).'</div>';
                                echo '<div style="position:relative;display:inline-block;width:25%;font-size:0.9em;">'.substr($l['tel1_util'],0,12).'</div>';
                                echo '<div style="position:relative;display:inline-block;width:25%;font-size:0.9em;">'.substr($l['tel2_util'],0,12).'</div>';
                            echo '</div>';
                        }
                        elseif ($l['ragsoc_intest']!='') {
                            echo '<div style="position:relative;font-size:0.9em;" >';
                                echo '<div style="position:relative;display:inline-block;width:50%;font-weight:bold;">'.strtolower(substr($l['ragsoc_intest'],0,27)).'</div>';
                                echo '<div style="position:relative;display:inline-block;width:25%;font-size:0.9em;">'.substr($l['tel1_intest'],0,12).'</div>';
                                echo '<div style="position:relative;display:inline-block;width:25%;font-size:0.9em;">'.substr($l['tel2_intest'],0,12).'</div>';
                            echo '</div>';
                        }
                        
                        //echo json_encode($l);

                    echo '</div>';
                }
            }

        echo '</div>';

    }

    function drawSearch() {

        echo '<div style="position:relative;width:100%;height:100%;" >';

            echo '<div style="position:relative;height:8%;margin-top:1%;border-bottom:1px solid black;">';

                echo '<div style="position:relative;display:inline-block;width:50%;vertical-align:top;" >';
                    echo '<div style="font-weight:bold;font-size:0.9em;">Ricerca per targa,telaio,intestatario (inserire più di 3 caratteri):</div>';
                    echo '<input id="global_linker_input" type="text" style="width:90%;" onkeydown="if(event.keyCode==13) window._globalLinker.readLista();" />';
                echo '</div>';

                echo '<div style="position:relative;display:inline-block;width:10%;vertical-align:bottom;text-align:left;" >';
                    echo '<button onclick="window._globalLinker.readLista();">Cerca</button>';
                echo '</div>';

                echo '<div style="position:relative;display:inline-block;width:10%;vertical-align:bottom;text-align:right;" >';
                    echo '<img style="width:20px;height:20px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/veicolo/img/annulla.png" onclick="window._globalLinker.closeGlobalLinker();" />';
                echo '</div>';

            echo '</div>';

            echo '<div id="global_linker_lista_div" style="position:relative;height:90%;width:100%;overflow:scroll;overflow-x:hidden;">';
            echo '</div>';

        echo '</div>';

        echo '<script type="text/javascript" >';
            echo 'window._globalLinker=new nebulaGlobalLinker();';
            echo 'window._globalLinker.setContesto(\''.$this->param['contesto'].'\');';
        echo '</script>';
    }

}
?>