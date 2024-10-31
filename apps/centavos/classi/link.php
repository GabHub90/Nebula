<?php

class centavosLink {

    //array dei campi del record del PIANO
    protected $piano=array();
    protected $ID_coll=0;
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
        $this->ID_coll=$linkoll;
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

                //$row['nome']=mb_convert_encoding($row['nome'], "UTF-8", "auto");
                //$row['cognome']=mb_convert_encoding($row['cognome'], "UTF-8", "auto");

                $row['nome']=iconv("ISO-8859-1", "UTF-8", $row['nome']);
                $row['cognome']=iconv("ISO-8859-1", "UTF-8", $row['cognome']);

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

        echo '<div style="margin-top:10px;font-weight:bold;font-size:1.3em;height:5%;" >';
            echo $this->piano['reparto'].' - '.$this->coll['info']['cognome'].' '.$this->coll['info']['nome'].' ( '.mainFunc::gab_todata($this->coll['data_i']).' - '.mainFunc::gab_todata($this->coll['data_f']).' )';
        echo '</div>';

        $temp=array(
            "varianti"=>$this->varianti,
            "varobj"=>$this->varobj,
            "periodi"=>$this->periodi,
            "firstPeriodo"=>$this->firstPeriodo,
            "lastPeriodo"=>$this->lastPeriodo,
            "piano"=>$this->piano['ID'],
            "ID_coll"=>$this->ID_coll
        );  

        echo '<script type="text/javascript" >';
            echo 'var obj='.json_encode($this->link).';';
            echo 'window["_nebulaApp_"+window._nebulaApp.getTagFunzione()].loadLink(obj);';
            echo 'var obj='.json_encode($temp).';';
            echo 'window["_nebulaApp_"+window._nebulaApp.getTagFunzione()].loadLinkInfo(obj);';
        echo '</script>';

        echo '<div id="ctv_link_edit_main" style="height:95%;overflow:scroll;overflow-x:hidden;" >';

        echo '</div>';

        echo '<script type="text/javascript" >';
            echo 'window["_nebulaApp_"+window._nebulaApp.getTagFunzione()].ctvDrawLink();';
        echo '</script>';

        /*echo '<div>';
            echo json_encode($this->coll);
        echo '</div>';*/

        /*echo '<div>';
            echo json_encode($this->link);
        echo '</div>';*/

        /*echo '<div>';
            echo $this->firstPeriodo.' '.$this->lastPeriodo;
        echo '</div>';*/


    }

}
?>