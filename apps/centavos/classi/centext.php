<?php

class centext {

    protected $param=false;

    protected $piano=false;

    protected $valori=array();

    protected $galileo;

    function __construct($param,$galileo) {

        $this->galileo=$galileo;

        if (isset($param['inizio']) && $param['inizio']!="") {

            $this->param=$param;
            
            //recupera i valori in base a $param (inizio e fine)
            $wclause="d_validita>='".$param['inizio']."' AND d_validita<='".$param['fine']."'";
            $order="d_validita DESC";
            $galileo->executeSelect('centavos','CENTAVOS_esterni',$wclause,$order);

            $result=$galileo->getResult();
            if ($result) {
                $fetID=$this->galileo->preFetch('centavos');
                while ($row=$this->galileo->getFetch('centavos',$fetID)) {
                    if ($row['collaboratore']=="") $row['collaboratore']='team';
                    if ($row['collaboratore']=="*") $row['collaboratore']='jolly';
                    $this->valori[$row['tag']][$row['collaboratore']][$row['ID']]=$row;
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

    function getValori($tag,$collaboratore) {

        if (isset($this->valori[$tag])) {

            if ($collaboratore=="") $collaboratore='team';

            $collaboratore=(isset($this->valori[$tag][$collaboratore]))?$collaboratore:( (isset($this->valori[$tag]['jolly']) && $collaboratore!='team')?'jolly':false );
            
            if ($collaboratore) {
                if (isset($this->valori[$tag][$collaboratore])) {
                    return $this->valori[$tag][$collaboratore];
                }
                else return false;
            }
            else return false;
        }
        else return false;
    }

    function getResult($tag,$contesto,$operazione) {
        /*ritorna:
            ultimo                  ultimo valore
            somma                   somma dei valori
            media                   media dei valori
        */

        if (isset($this->valori[$tag])) {

            $contesto=(isset($this->valori[$tag][$contesto]))?$contesto:( (isset($this->valori[$tag]['jolly']) && $contesto!='team')?'jolly':false );

            if ($contesto) {

                if ($operazione=='ultimo') {
                    foreach ($this->valori[$tag][$contesto] as $k=>$v) {
                        return $v['valore'];
                    }
                }
                elseif ($operazione=='somma') {
                    $somma=0;
                    foreach ($this->valori[$tag][$contesto] as $k=>$v) {
                        $somma+=$v['valore'];
                    }
                    return $somma;
                }
                elseif ($operazione=='media') {
                    $somma=0;
                    $num=0;
                    foreach ($this->valori[$tag][$contesto] as $k=>$v) {
                        $somma+=$v['valore'];
                        $num++;
                    }
                    return $somma/$num;
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
                    echo '<div style="font-size:1.2em;">'.$tag.'</div>';
                    echo '<div style="font-size:0.9em;">'.$titolo.'</div>';
                echo '</div>';

                echo '<div style="position:relative;display:inline-block;width:18%;text-align:center;" >';

                    echo '<div style="font-size:0.9em;font-weight:bold;text-align:center;" >collaboratore</div>';

                    echo '<select id="ctv_extForm_coll" style="width:90%;text-align:center;">';
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
                    echo '<input id="ctv_extForm_team" style="width:25px;" type="checkbox" />';
                echo '</div>';

                echo '<div style="position:relative;display:inline-block;width:15%;text-align:center;" >';
                    echo '<div style="font-size:0.9em;font-weight:bold;text-align:center;" >valore</div>';
                    echo '<input id="ctv_extForm_valore" style="width:80%;text-align:center;" type="text" />';
                echo '</div>';

                echo '<div style="position:relative;display:inline-block;width:20%;text-align:center;" >';
                    echo '<div style="font-size:0.9em;font-weight:bold;text-align:center;" >data</div>';
                    echo '<input id="ctv_extForm_d" style="width:90%;text-align:center;" type="date" />';
                echo '</div>';

                echo '<div style="position:relative;display:inline-block;width:12%;text-align:center;height:100%;" >';
                    echo '<img style="position:relative;width:30px;height:30px;cursor:pointer;top:50%;transform:translate(-0%,-52%);" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/centavos/img/add.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].checkExt(\''.$tag.'\');" />';
                echo '</div>';

            echo '</div>';

        echo '</div>';

        echo '<div style="position:relative;height:88%;padding:5px;box-sizing:border-box;overflow:scroll;overflow-x:hidden;" >';

            echo '<table style="width:95%;border-spacing:5px;" >';

                echo '<colgroup>';
                    echo '<col span="1" style="width:5%;" />';
                    echo '<col span="5" style="width:19%;" />';
                echo '</colgroup>';

                echo '<thead>';
                    echo '<tr>';
                        echo '<th></th>';
                        echo '<th>Collaboratore</th>';
                        echo '<th>Valore</th>';
                        echo '<th>Data</th>';
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
                                    echo '<td style="font-weight:bold;" >'.mainFunc::gab_todata($t['d_validita']).'</td>';
                                    echo '<td style="font-weight:bold;color:#a7a7a7;font-size:0.8em;" >';
                                        echo '<div>'.$t['utente'].'</div>';
                                        echo '<div>'.mainFunc::gab_todata($t['d_inserimento']).'</div>';
                                    echo '</td>';
                                    echo '<td style="text-align:left;" >';
                                        echo '<img style="width:20px;height:20px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/centavos/img/sub.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].delExt(\''.$tag.'\',\''.$k.'\');" />';
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