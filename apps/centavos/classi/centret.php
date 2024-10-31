<?php

class centret{

    protected $param=false;

    protected $piano=false;

    protected $valori=array();

    protected $galileo;

    function __construct($param,$galileo) {

        $this->galileo=$galileo;

        if (isset($param['piano'])) {

            $param['periodo']=isset($param['periodo'])?$param['periodo']:false;

            $this->param=$param;
            
            //recupera i valori in base a $param (inizio e fine)
            $order="per.d_inizio";
            //$tipo,$funzione,$args,$order
            $galileo->executeGeneric('centavos','getRettifiche',$param,$order);

            $result=$galileo->getResult();
            if ($result) {
                $fetID=$this->galileo->preFetch('centavos');
                while ($row=$this->galileo->getFetch('centavos',$fetID)) {

                    //se il periodo è valorizzato ma il record non vi appartiene saltalo
                    if ($this->param['periodo'] && $row['periodo']!=$this->param['periodo']) continue;

                    if ($row['collaboratore']=="") $row['collaboratore']='team';
                    if ($row['collaboratore']=="*") $row['collaboratore']='jolly';
                    $this->valori[$row['parametro']][$row['collaboratore']][$row['ID']]=$row;
                }
            }

            $wclause="ID='".$this->param['piano']."'";
            $galileo->executeSelect('centavos','CENTAVOS_piani',$wclause,'');

            $result=$galileo->getResult();
            if ($result) {
                $fetID=$this->galileo->preFetch('centavos');
                while ($row=$this->galileo->getFetch('centavos',$fetID)) {
                    $this->piano=$row;
                }
            }

        }
        
    }

    function getValore($tag,$collaboratore) {
        //ritorna l'ultimo valore della lista (il più recente)
        //in teoria dovrebbe comunque essercene solo uno

        if (isset($this->valori[$tag])) {

            if ($collaboratore=="") $collaboratore='team';

            $collaboratore=(isset($this->valori[$tag][$collaboratore]))?$collaboratore:( (isset($this->valori[$tag]['jolly']) && $collaboratore!='team')?'jolly':false );
            
            if ($collaboratore) {
                if (isset($this->valori[$tag][$collaboratore])) {

                    $ret=false;

                    foreach ($this->valori[$tag][$collaboratore] as $k=>$v) {
                        //$ret=$v['valore'];
                        $ret=$v;
                    }

                    return $ret;
                }
                else return false;
            }
            else return false;
        }
        else return false;
        
    }

    function drawTag($tag,$titolo) {

        if ($tag=="" || !$this->param) die('Parametri non corretti !!!');

        echo '<div style="position:relative;height:12%;padding:5px;box-sizing:border-box;" >';

            echo '<div style="position:relative;height:100%;">';

                echo '<div style="position:relative;display:inline-block;width:25%;font-weight:bold;text-align:center;" >';
                    if (substr($tag,0,1)=='_') {
                        echo '<div style="font-size:1.2em;"><span style="color:red;font-size:0.7em;">COEFF&nbsp;</span>'.$tag.'</div>';
                    }
                    else {
                        echo '<div style="font-size:1.2em;"><span style="color:red;font-size:0.7em;">RETT&nbsp;</span>'.$tag.'</div>';
                    }
                    echo '<div style="font-size:0.9em;">'.$titolo.'</div>';
                echo '</div>';

                echo '<div style="position:relative;display:inline-block;width:18%;text-align:center;" >';
                    echo '<div style="font-size:0.9em;font-weight:bold;text-align:center;" >Collaboratore</div>';
                    echo '<select id="ctv_retForm_coll" style="width:90%;text-align:center;">';
                        echo '<option value="">Team</option>';
                        echo '<option value="*">TUTTI</option>';

                        $this->galileo->getCollaboratoriIntervallo("'".$this->piano['reparto']."'",$this->piano['data_i'],$this->piano['data_f']);

                        $result=$this->galileo->getResult();
                        if ($result) {
                            $fetID=$this->galileo->preFetchBase('maestro');

                            $ID_coll="";

                            while ($row=$this->galileo->getFetchBase('maestro',$fetID)) {

                                if ($row['ID_coll']==$ID_coll) continue;

                                echo '<option value="'.$row['ID_coll'].'">'.$row['cognome'].' '.$row['nome'].'('.$row['ID_coll'].')</option>';

                                $ID_coll=$row['ID_coll'];
                            }
                        }

                    echo '</select>';
                echo '</div>';

                echo '<div style="position:relative;display:inline-block;width:10%;text-align:center;" >';
                    echo '<div style="font-size:0.9em;font-weight:bold;text-align:center;" >team</div>';
                    echo '<input id="ctv_retForm_team" style="width:25px;" type="checkbox" />';
                echo '</div>';

                echo '<div style="position:relative;display:inline-block;width:15%;text-align:center;" >';
                    echo '<div style="font-size:0.9em;font-weight:bold;text-align:center;" >valore</div>';
                    echo '<input id="ctv_retForm_valore" style="width:80%;text-align:center;" type="text" />';
                echo '</div>';

                echo '<div style="position:relative;display:inline-block;width:25%;text-align:center;" >';
                    echo '<select id="ctv_retForm_periodo" style="width:80%;text-align:center;">';
                        echo '<option value="">Periodo...</option>';
                        
                        $wclause="piano='".$this->param['piano']."'";
                        $order="d_inizio";
                        $this->galileo->executeSelect('centavos','CENTAVOS_periodi',$wclause,$order);

                        $result=$this->galileo->getResult();
                        if ($result) {
                            $fetID=$this->galileo->preFetch('centavos');
                            while ($row=$this->galileo->getFetch('centavos',$fetID)) {
                                echo '<option value="'.$row['ID'].'">'.mainFunc::gab_todata($row['d_inizio']).' - '.mainFunc::gab_todata($row['d_fine']).'</option>';
                            }
                        }

                    echo '</select>';
                echo '</div>';

                echo '<div style="position:relative;display:inline-block;width:7%;text-align:center;height:100%;" >';
                    echo '<img style="position:relative;width:30px;height:30px;cursor:pointer;top:50%;transform:translate(-0%,-52%);" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/centavos/img/add.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].checkRet(\''.$tag.'\');" />';
                echo '</div>';

            echo '</div>';

        echo '</div>';

        echo '<div style="position:relative;height:88%;padding:5px;box-sizing:border-box;overflow:scroll;overflow-x:hidden;" >';

            echo '<table style="width:95%;border-spacing:5px;" >';

                echo '<colgroup>';
                    echo '<col span="1" style="width:5%;" />';
                    echo '<col span="3" style="width:19%;" />';
                    echo '<col span="1" style="width:28%;" />';
                    echo '<col span="1" style="width:9%;" />';
                echo '</colgroup>';

                echo '<thead>';
                    echo '<tr>';
                        echo '<th></th>';
                        echo '<th>Collaboratore</th>';
                        echo '<th>Valore</th>';
                        echo '<th>Periodo</th>';
                        echo '<th>Inserimento</th>';
                        echo '<th></th>';
                    echo '</tr>';
                echo '</thead>';

                echo '<tbody>';
                    
                    if (isset($this->valori[$tag])) {

                        foreach ($this->valori[$tag] as $coll=>$v) {

                            foreach ($v as $k=>$t) {

                                echo '<tr style="text-align:center;">';

                                    echo '<td style="font-size:0.8em;" >'.$k.'</td>';
                                    echo '<td style="font-weight:bold;" >'.($t['collaboratore']==""?"team":($t['collaboratore']=='jolly'?'tutti':$t['collaboratore'])).'</td>';
                                    echo '<td style="font-weight:bold;" >'.$t['valore'].'</td>';
                                    echo '<td style="font-weight:bold;font-size:0.8em;" >';
                                        echo '<div>'.mainFunc::gab_todata($t['d_inizio']).'</div>';
                                        echo '<div>'.mainFunc::gab_todata($t['d_fine']).'</div>';
                                    echo '</td>';
                                    echo '<td style="font-weight:bold;color:#a7a7a7;font-size:0.8em;" >';
                                        echo '<div>'.$t['utente'].'</div>';
                                        echo '<div>'.mainFunc::gab_todata($t['d_inserimento']).'</div>';
                                    echo '</td>';
                                    echo '<td style="text-align:left;" >';
                                        echo '<img style="width:20px;height:20px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/centavos/img/sub.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].delRet(\''.$tag.'\',\''.$k.'\');" />';
                                    echo '</td>';

                                echo '</tr>';
                            }
                        }
                    }

                echo '</tbody>';

            echo '</table>';

        echo '</div>';

    }

}

?>