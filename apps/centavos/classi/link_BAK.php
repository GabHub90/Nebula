<?php

class centavosLink {

    //array dei campi del record del PIANO
    protected $piano=array();
    //array delle varianti possibili
    protected $varianti=array();
    //array dei periodi possibili
    protected $periodi=array();
    protected $firstPeriodo=0;
    protected $lastPeriodo=0;
    //array dei campi delle 
    protected $varobj=array();
    protected $coll=array(
        "info"=>array(),
        "data_i"=>"",
        "data_f"=>"",
        "eval_i"=>"",
        "eval_f"=>""
    );
    protected $link=array();

    protected $galileo;

    function __construct($piano,$linkoll,$galileo) {

        $this->piano=$piano;
        $this->galileo=$galileo;

        if (!$this->varianti=json_decode($this->piano['varianti'],true)) $this->varianti=array();

        $this->galileo->executeSelect('centavos','CENTAVOS_periodi',"piano='".$this->piano['ID']."'","ID");
        $result=$this->galileo->getResult();
        if ($result) {
            $fetID=$this->galileo->preFetch('centavos');
            while ($row=$this->galileo->getFetch('centavos',$fetID)) {
                $this->periodi[$row['ID']]=$row;
                $this->lastPeriodo=$row['ID'];
                if ($this->firstPeriodo==0) $this->firstPeriodo=$row['ID'];
            }
        }

        $a=array(
            "reparti"=>"'".$this->piano['reparto']."'",
            "data_i"=>$this->piano['data_i'],
            "data_f"=>$this->piano['data_f'],
            "piano"=>$this->piano['ID'],
            "coll"=>$linkoll
        );

        $this->galileo->executeGeneric('centavos','getCollaboratori',$a,"");
        $result=$this->galileo->getResult();
        if ($result) {
            $fetID=$this->galileo->preFetch('centavos');
            while ($row=$this->galileo->getFetch('centavos',$fetID)) {
                $this->coll['info']=$row;
                
                if ($this->coll['data_i']=='' || $row['data_i'] < $this->coll['data_i']) $this->coll['data_i']=$row['data_i'];
                if ($this->coll['data_f']=='' || $row['data_f'] > $this->coll['data_f']) $this->coll['data_f']=$row['data_f'];

                if ($row['ID_link']!=0) {
                    $this->link[$row['ID_link']]=$row;
                    if (!$this->link[$row['ID_link']]['grado']=json_decode($this->link[$row['ID_link']]['grado'],true)) $this->link[$row['ID_link']]['grado']=array();
                }
            }
        }

        $this->coll['eval_i']=($this->coll['data_i']<$this->piano['data_i'])?$this->piano['data_i']:$this->coll['data_i'];
        $this->coll['eval_f']=($this->coll['data_f']>$this->piano['data_f'])?$this->piano['data_f']:$this->coll['data_f'];

        ///////////////////////////////////////////////////////////////////
        //lettura delle sezioni suddivise per varianti
        $wclause="piano='".$piano['ID']."'";
        $orderby="ID";
        $this->galileo->executeSelect('centavos','CENTAVOS_varianti',$wclause,$orderby);
        $result=$this->galileo->getResult();
        if ($result) {
            $fetID=$this->galileo->preFetch('centavos');
            while ($row=$this->galileo->getFetch('centavos',$fetID)) {
                $this->varobj[$row['variante']][$row['ID']]=$row;
            }
        }
                
    }

    function draw() {

        echo '<div style="margin-top:10px;font-weight:bold;font-size:1.3em;" >';
            echo $this->piano['reparto'].' - '.$this->coll['info']['cognome'].' '.$this->coll['info']['nome'].' ( '.mainFunc::gab_todata($this->coll['data_i']).' - '.mainFunc::gab_todata($this->coll['data_f']).' )';
        echo '</div>';

        foreach ($this->link as $idlink=>$l) {

            echo '<div style="border:2px solid brown;padding:10px;box-sizing:border-box;margin-top:10px;min-height:80px;" >';

                echo '<div style="display:inline-block;width:15%;text-align:center;vertical-align:top;" >';

                    echo '<div style="font-weight:bold;">variante</div>';

                    echo '<div>';
                        echo '<select id="ctv_link_select_'.$idlink.'" style="width:95%;" ';
                            //####################
                            //verifica se ci sono ANALISI concluse con questa configurazione
                            //nel caso il parametro non è modificabile
                            //####################
                        echo '>';
                            foreach ($this->varianti as $v) {
                                echo '<option value="'.$v.'" ';
                                    if ($v==$l['variante']) echo 'selected="selected"';
                                echo '>';
                                    echo $v;
                                echo '</option>';
                            }
                        echo '</select>';
                    echo '</div>';

                echo '</div>';

                echo '<div style="display:inline-block;width:22%;text-align:center;vertical-align:top;" >';
                
                    echo '<div style="font-weight:bold;">da</div>';

                    echo '<div>';

                        //echo '<input id="ctv_link_da_'.$idlink.'" type="date" style="width:95%;" value="'.mainFunc::gab_toinput($l['dlink_i']).'" />';
                        echo '<select style="font-size:1.1em;" onchange="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].ctvCheckLink();" >';
                            foreach ($this->periodi as $k=>$p) {
                                echo '<option value="'.($k==$this->firstPeriodo?'0':$k).'" data-d="'.$p['d_inizio'].'" ';
                                    if ($l['periodo_i']==$k || ($l['periodo_i']==0 && $k==$this->firstPeriodo) ) echo ' selected ';
                                echo '>'.$k.' - '.mainFunc::gab_todata($p['d_inizio']).'</option>';
                            }
                        echo '</select>';

                    echo '</div>';
                            
                    echo '<div>';

                        //echo '<input id="ctv_link_da_'.$idlink.'" type="date" style="width:95%;" value="'.mainFunc::gab_toinput($l['dlink_i']).'" />';
                        //echo '<div style="width:100%;text-align:center;" >'.mainFunc::gab_todata($l['dlink_i']).'</div>';

                    echo '</div>';

                echo '</div>';

                echo '<div style="display:inline-block;width:22%;text-align:center;vertical-align:top;" >';
                    
                    echo '<div style="font-weight:bold;">a</div>';

                    echo '<div>';

                        //echo '<input id="ctv_link_da_'.$idlink.'" type="date" style="width:95%;" value="'.mainFunc::gab_toinput($l['dlink_f']).'" />';
                        echo '<select style="font-size:1.1em;" onchange="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].ctvCheckLink();" >';
                            foreach ($this->periodi as $k=>$p) {
                                echo '<option value="'.($k==$this->lastPeriodo?'9999':$k).'"  data-d="'.$p['d_fine'].'" ';
                                    if ($l['periodo_f']==$k || ($l['periodo_f']==9999 && $k==$this->lastPeriodo) ) echo ' selected ';
                                echo '>'.$k.' - '.mainFunc::gab_todata($p['d_fine']).'</option>';
                            }
                        echo '</select>';

                    echo '</div>';
                            
                    echo '<div>';

                        //echo '<input id="ctv_link_da_'.$idlink.'" type="date" style="width:95%;" value="'.mainFunc::gab_toinput($l['dlink_f']).'" />';
                        //echo '<div style="width:100%;text-align:center;" >'.mainFunc::gab_todata($l['dlink_f']).'</div>';

                    echo '</div>';

                echo '</div>';

                echo '<div style="display:inline-block;width:33%;text-align:center;vertical-align:top;" >';
                    
                    echo '<div style="font-weight:bold;">livello</div>';
                            
                    echo '<div>';

                        foreach ($this->varianti as $kv=>$v) { 
                            
                            echo '<div id="ctv_livello_div_'.$v.'" style="padding-left:5px;box-sizing:border-box;';
                                echo ($v==$l['variante'])?'display:block;':'display:none;';
                            echo '">';

                                if ( isset($this->varobj[$v]) ) {

                                    foreach ($this->varobj[$v] as $idvar=>$vz) {
                                        echo '<div style="display:inline-block;width:75%;text-align:left;" >'.substr($vz['titolo'],0,25).'</div>';

                                        echo '<div style="display:inline-block;width:25%;text-align:center;" >';

                                            echo '<select id="ctv_link_livello" data-id="'.$idvar.'" >';

                                                for ($i=0;$i<=4;$i++) {
                                                    echo '<option value="'.$i.'" ';
                                                        if (isset($l['grado'][$idvar])) {
                                                            if ($i==$l['grado'][$idvar]) echo 'selected="selected"';
                                                        }
                                                    echo '>'.($i+1).'</option>';
                                                }

                                            echo '</select>';

                                        echo '</div>';
                                    }
                                }

                            echo '</div>';
                        }

                    echo '</div>';

                echo '</div>';

            echo '</div>';

        }

        echo '<script type="text/javascript" >';
            echo 'var obj='.json_encode($this->link).';';
            echo 'window["_nebulaApp_"+window._nebulaApp.getTagFunzione()].loadLink(obj);';
        echo '</script>';

        /*echo '<div>';
            echo json_encode($this->coll);
        echo '</div>';*/

        echo '<div>';
            echo json_encode($this->link);
        echo '</div>';

        /*echo '<div>';
            echo $this->firstPeriodo.' '.$this->lastPeriodo;
        echo '</div>';*/


    }

}
?>