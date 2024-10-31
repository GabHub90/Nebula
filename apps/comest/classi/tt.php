<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/concerto/concerto_veicoli.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/infinity/infinity_veicoli.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_comest.php');

class comestTT {

    protected $actualDms="";

    protected $colori=['#dedcc4','#adc5ba'];

    protected $galileo;
    protected $log=array();

    function __construct($dms,$galileo) {
        
        $this->galileo=$galileo;

        $obj=new galileoComest();
        $nebulaDefault['comest']=array("gab500",$obj);
        $this->galileo->setFunzioniDefault($nebulaDefault);

        $this->initGalileo($dms);
    }

    function initGalileo($dms) {

        if ($this->actualDms==$dms) return;

        if ($dms=='concerto') {
            //inizializzazione galileo con gli oggetti ODL
            $obj=new galileoConcertoVeicoli();
            $nebulaDefault['veicoli']=array("maestro",$obj);

            $this->galileo->setFunzioniDefault($nebulaDefault);
        }

        elseif ($dms=='infinity') {
            //inizializzazione galileo con gli oggetti ODL
            $obj=new galileoInfinityVeicoli();
            $nebulaDefault['veicoli']=array("rocket",$obj);

            $this->galileo->setFunzioniDefault($nebulaDefault);
        }

        $this->actualDms=$dms;
    }

    function getLog() {
        return $this->log;
    }

    function draw($txt,$odl) {

        echo '<div style="font-weight:bold;font-size:0.9em;margin-top:5px;margin-bottom:5px;" >Commesse aperte:</div>';

        $this->galileo->executeGeneric('comest','getTT',array('txt'=>$txt,"odl"=>$odl,"dms"=>$this->actualDms),'');
    
        if ($this->galileo->getResult()) {

            $fid=$this->galileo->preFetch('comest');
    
            while ($row=$this->galileo->getFetch('comest',$fid)) {
    
                echo '<div style="position:relative;width:90%;margin-top:8px;margin-bottom:8px;padding:5px;box-sizing:border-box;border:1px solid black;border-radius:6px;box-shadow: 3px 3px #bbbbbb;background-color:#efcd9a;cursor:pointer;" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].setCommessa(\''.$row['rif'].'\');" >';

                    echo '<div style="position:relative;display:inline-block;width:10%;">('.$row['rif'].')</div>';
                    echo '<div style="position:relative;display:inline-block;width:10%;">'.($row['odl']!=0?'('.substr($row['dms'],0,1).')'.$row['odl']:'').'</div>';
                    echo '<div style="position:relative;display:inline-block;width:20%;font-weight:bold;">'.$row['telaio'].'</div>';
                    echo '<div style="position:relative;display:inline-block;width:15%;font-weight:bold;">'.$row['targa'].'</div>';
                    echo '<div style="position:relative;display:inline-block;width:30%;font-size:0.9em;">'.substr($row['descrizione'],0,35).'</div>';

                echo '</div>';
            }
        }
    
        echo '<hr style="margin-top:10px;margin:bottom:10px;" />';

        $this->drawTT($txt,$odl);
    }

    function drawTT($txt,$odl) {

        if ($this->actualDms=="") return;

        $this->galileo->clearQuery();

        $this->galileo->executeGeneric('veicoli','getComest',array('txt'=>$txt,'odl'=>$odl),'');

        if ($this->galileo->getResult()) {

            $fid=$this->galileo->preFetch('veicoli');

            $col=1;
            $tt="";

            while($row=$this->galileo->getFetch('veicoli',$fid)) {

                if ($tt!=$row['targa'].$row['telaio']) {
                    $col=($col==1)?0:1;
                    $tt=$row['targa'].$row['telaio'];
                }

                $row['descrizione']=iconv('ISO-8859-1','UTF-8',$row['descrizione']);

                echo '<div style="position:relative;width:90%;margin-top:8px;margin-bottom:8px;padding:5px;box-sizing:border-box;border:1px solid black;border-radius:6px;box-shadow: 3px 3px #bbbbbb;background-color:'.$this->colori[$col].';cursor:pointer;" data-info="'.base64_encode(json_encode($row)).'" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].setVeicolo(this);" >';

                    echo '<div style="position:relative;display:inline-block;width:10%;">('.$this->actualDms.')</div>';
                    echo '<div style="position:relative;display:inline-block;width:10%;">'.($row['odl']!=0?$row['odl']:'').'</div>';
                    echo '<div style="position:relative;display:inline-block;width:20%;font-weight:bold;">'.$row['telaio'].'</div>';
                    echo '<div style="position:relative;display:inline-block;width:15%;font-weight:bold;">'.$row['targa'].'</div>';
                    echo '<div style="position:relative;display:inline-block;width:30%;font-size:0.9em;">'.substr($row['descrizione'],0,35).'</div>';

                echo '</div>';
                
            }
        }
    }
}

?>