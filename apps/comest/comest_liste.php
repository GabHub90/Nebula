<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/apps/comest/classi/comest.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_comest.php');

class comestListeApp extends appBaseClass {

    protected $utente;

    protected $class;

    function __construct($param,$galileo) {
        
        parent::__construct($galileo);

        $this->loc='/nebula/apps/comest/';

        $this->param['tipo']="";
        $this->param['targa']="";
        $this->param['telaio']="";
        $this->param['dms']="";
        $this->param['odl']="";
        $this->param['fornitore']="";
        $this->param['da']="";
        $this->param['a']="";
       
        $this->loadParams($param);

        $this->utente=$this->id->getCollID();

        $obj=new galileoComest();
        $nebulaDefault['comest']=array("gab500",$obj);
        $this->galileo->setFunzioniDefault($nebulaDefault);

        $this->class=new nebulaComest($galileo);
    }

    function initClass() {
        return ' comestCode(\''.$this->param['nebulaFunzione']['nome'].'\');';
    }

    function build($arr) {

        if ($this->param['tipo']=='salvate') {
            $this->galileo->executeGeneric('comest','getCommesse',array("tipo"=>"salvate"),'');
        }
        elseif ($this->param['tipo']=='aperte') {
            $this->galileo->executeGeneric('comest','getCommesse',array("tipo"=>"aperte"),'');
        }
        elseif ($this->param['tipo']=='archivio') {
            $this->galileo->executeGeneric('comest','getCommesse',$this->param,'');
        }

    }

    function customDraw() {

        echo '<div id="comest_liste" >';

            if ($this->param['tipo']=='salvate') $this->drawSalvate();

            elseif ($this->param['tipo']=='aperte') {
                $this->drawSalvate();

                echo '<div style="position:relative;display:inline-block;vertical-align:top;width:45%;height:100%;text-align:right;" >';
                    echo '<button onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].allineaChiuse();">allinea</button>';
                echo '</div>';

            }

            elseif ($this->param['tipo']=='archivio') $this->drawSalvate();

        echo '</div>';

        echo '<div id="comest_liste_commessa" style="position:relative;width:100%;height:100%;display:none;" >';

            $this->class->drawJS();

            echo '<div style="position:relative;width:100%;height:5%;text-align:left;" >';
                echo '<img style="position:relative;width:25px;cursor:pointer" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/comest/img/chiudi.png" onclick="window._nebulaApp.ribbonExecute();" />';
            echo '</div>';

            echo '<div id="nebula_comest_main" style="position:relative;width:100%;height:95%;text-align:left;" ></div>';

        echo '</div>';

    }

    function drawSalvate() {

        $this->build(array());

        $rows=0;

        echo '<div style="position:relative;display:inline-block;vertical-align:top;width:50%;height:100%;overflow:scroll;overflow-x:hidden;" >';

            if ($this->galileo->getResult()) {
                
                $fid=$this->galileo->preFetch('comest');

                while ($row=$this->galileo->getFetch('comest',$fid)) {

                    $this->drawLine($row);

                    $rows++;
                }
            }

            if ($rows==0) echo '<div style="color:red;font-weight:bold;" >Nessuna Commessa trovata</div>';

            /*echo '<div>';
                echo json_encode($this->galileo->getLog('query'));
            echo '</div>';*/
            
        echo '</div>';

    }

    function drawListaOdl($new) {

        $this->build(array());

        $rows=0;

        echo '<div id="comest_liste_commessa" style="position:relative;width:100%;height:100%;overflow:scroll;overflow-x:hidden;" >';

            //$this->class->drawJS();

            if ($this->galileo->getResult()) {
                    
                $fid=$this->galileo->preFetch('comest');

                while ($row=$this->galileo->getFetch('comest',$fid)) {

                    $this->drawLine($row);

                    $rows++;
                }
            }

            if ($rows==0) echo '<div style="color:red;font-weight:bold;" >Nessuna Commessa trovata</div>';

            echo '<div style="width:100%;text-align:center;margin-top:10px;" >';
                echo '<button data-info="'.base64_encode(json_encode($new)).'" onclick="window._nebulaOdl.nuovaPraticaComest(this);" >Apri nuova commessa</button>';
            echo '</div>';

            $this->class->drawJS();

        echo '</div>';
    }

    function drawLine($row) {
        
        echo '<div style="position:relative;margin-top:10px;margin-bottom:10px;width:90%;border:1px solid black;border-radius:10px 10px;box-sizing:border-box;padding:5px;cursor:pointer;" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].selectCommessaFromLista(\''.$row['rif'].'\');" >';
            
            echo '<div style="position:relative;" >';

                echo '<div style="position:relative;display:inline-block;width:15%;text-align:center;" >'.$row['rif'].'</div>';
                echo '<div style="position:relative;display:inline-block;width:25%;text-align:left;" >'.$row['targa'].'</div>';
                echo '<div style="position:relative;display:inline-block;width:60%;text-align:left;" >'.$row['telaio'].'</div>';

            echo '</div>';

            echo '<div style="position:relative;" >';

                $temp=json_decode($row['fornitore'],true);

                echo '<div style="position:relative;display:inline-block;width:60%;text-align:center;" >'.($temp && isset($temp['ragsoc'])?$temp['ragsoc']:'Nessun fornitore selezionato').'</div>';

                if ($row['d_apertura']=="") {
                    echo '<div style="position:relative;display:inline-block;width:40%;text-align:center;" >Non Aperta</div>';
                }
                else {
                    echo '<div style="position:relative;display:inline-block;width:18%;text-align:center;" >'.mainFunc::gab_todata($row['d_apertura']).'</div>';
                    echo '<div style="position:relative;display:inline-block;width:4%;text-align:center;" >';
                        echo '<img style="width:20px;height:10px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/main/img/blackarrowR.png" />';
                    echo '</div>';

                    if ($row['d_annullo']!="") {
                        echo '<div style="position:relative;display:inline-block;width:18%;text-align:center;font-weight:bold;color:red;" >ANNULLATA</div>';
                    }
                    elseif ($row['d_controllo']!="") {
                        echo '<div style="position:relative;display:inline-block;width:18%;text-align:center;font-weight:bold;" >Riconsegnata</div>';
                    }
                    else {
                        echo '<div style="position:relative;display:inline-block;width:18%;text-align:center;';
                            if ($row['riconsegna']<=date('Ymd')) echo 'color:red;';
                        echo '" >'.mainFunc::gab_todata($row['riconsegna']).'</div>';
                    }
                }

            echo '</div>';

            echo '<div style="position:relative;font-weight:bold;text-align:center;" >';
                echo $row['nota'];
            echo '</div>';

        echo '</div>';
    }

}
?>