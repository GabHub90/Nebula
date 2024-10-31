<?php
include('base.php');
include('base_officina.php');
include('sezione.php');

class centaStruttura {

    //panorama COINCIDE con il PIANO
    protected $panorama=array();
    protected $struttura=array();

    protected $varianti=array();
    protected $actualVar="";
    //oggetti sezioni
    protected $sezioni=array();

    //contiene il totale dell'incentivazione dopo il calcolo per il singolo collaboratore 
    protected $totaleIncentivo=0;

    protected $printAnalisiBody="";

    protected $base=null;
    protected $galileo;

    function __construct($panorama,$galileo) {

        $this->galileo=$galileo;

        $this->panorama=$panorama;

        /*TEST
        //i parametri sono quelli da passare all'oggetto BASE DATI
        $this->panorama=array(
            "ID"=>1,
            "struttura"=>"1",
            "macroreparto"=>"S",
            "reparto"=>"VWS",
            "descrizione"=>"Incentivazione 2021",
            "data_i"=>"20210701",
            "data_f"=>"20211231",
            "parametri"=>'{"riferimento":"reparto","marchi":{"A","V","N","C","S","X"},"tariffa":53}'
        );

        $this->struttura=array(
            "ID"=>1,
            "base_dati"=>"officina",
            "cadenza"=>"trimestre",
            "sezioni"=>'{"1","2","3","4"}',
            "varianti"=>'["TEC","RT","RC","ASS"]'
        );

        //indice=sezione
        $temposezioni=array(
            "1"=>array(
                "sezione"=>"1",
                "titolo"=>"Incentivazione personale",
                "varianti"=>'["TEC","RT","RC","ASS"]',
                "budget"=>350,
                "coefficienti"=>'{"pres":true,"redd":true,"gen":true}'
            ),
            "2"=>array(
                "sezione"=>"2",
                "titolo"=>"Vendita personale",
                "varianti"=>'["RC","ASS"]',
                "budget"=>-1,
                "coefficienti"=>'{"pres":false,"redd":false,"gen":true}'
            ),
            "3"=>array(
                "sezione"=>"3",
                "titolo"=>"Obiettivo Ricambi (Team)",
                "varianti"=>'["TEC","RT","RC","ASS"]',
                "budget"=>150,
                "coefficienti"=>'{"pres":false,"redd":false,"gen":true}'
            ),
            "4"=>array(
                "sezione"=>"4",
                "titolo"=>"Obiettivo Cem (Team)",
                "varianti"=>'["TEC","RT","RC","ASS"]',
                "budget"=>150,
                "coefficienti"=>'{"pres":false,"redd":false,"gen":true}'
            )
        );
        */

        //END TEST

        switch($this->panorama['base_dati']) {

            case 'officina':
                $this->base=new centavosBaseOfficina($this->panorama,$this->galileo);
            break;
        }

        /*foreach ($temposezioni as $sez=>$s) {

            $this->sezioni[$sez]=new centaSezione($s,$this->base,$this->galileo);

        }*/     

    }

    //viene chiamato prima di "calcola"
    function initSezioni($variante) {
        foreach ($this->sezioni as $sez=>$s) {
            $s->buildVariante($variante);
        }
    }

    function getPrintAnalisiBody() {
        return $this->printAnalisiBody;
    }

    function getTotaleIncentivo() {
        return $this->totaleIncentivo;
    }

    function drawSelectVar($variante) {

        $this->varianti=json_decode($this->panorama['varianti'],true);

        //echo '<option>'.json_encode($arr).'</option>';

        if ($this->varianti) {

            foreach ($this->varianti as $v) {
                echo '<option value="'.$v.'" ';
                    if ($v==$variante) echo 'selected="selected"';
                echo '>'.$v.'</option>';
            }
        }
    }

    function drawHead($ambito) {

        echo '<div style="font-size:1.1em;">';
            echo '<div style="position:relative;" >';
                echo mainFunc::gab_todata($this->panorama['data_i']). ' - '.mainFunc::gab_todata($this->panorama['data_f']);
                if ($ambito=='link') {
                    echo '<img style="position:absolute;top:0px;right:10px;width:20px;height:20px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/centavos/img/add.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].addPeriodo(\''.$this->panorama['ID'].'\');" />';
                }
            echo '</div>';
            echo '<div>Cadenza: '.$this->panorama['cadenza'].'</div>';
            echo '<div>Base dati: '.$this->panorama['base_dati'].'</div>';
        echo '</div>';
    }

    function drawStructBody($variante,$contesto) {

        $this->actualVar=$variante;

        if (!in_array($this->actualVar,$this->varianti)) die ('Variante inesistente !!!');

        $this->sezioni=array();

        //////////////////////////////////////////////
        //valorizzare le sezioni
        $wclause="variante='".$variante."' AND piano='".$this->panorama['ID']."'";
        $orderby="ID";
        $this->galileo->executeSelect('centavos','CENTAVOS_varianti',$wclause,$orderby);
        $result=$this->galileo->getResult();
        if ($result) {
            $fetID=$this->galileo->preFetch('centavos');
            while ($row=$this->galileo->getFetch('centavos',$fetID)) {
                $this->sezioni[$row['ID']]=new centaSezione($row,$this->base,$this->galileo);
            }
        }
        //////////////////////////////////////////////
        //echo json_encode($this->galileo->getLog('query'));

        foreach ($this->sezioni as $sez=>$s) {
            //$s->buildVariante($variante);
            $s->drawSezione($contesto);
        }

    }

    function drawStructSection($sezione) {
        $this->sezioni[$sezione]->drawStructSection();
    }

    /////////////////////////////////////////////////////////////////////////////

    function setSimula($sorgenti) {
        //setta le sorgenti di BASE ed indica che siamo in stato di SIMULAZIONE
        $this->base->setSimula($sorgenti);
    }

    function simula($sezione) {
        return $this->sezioni[$sezione]->calcola();
    }

    function drawAnalisiColl($coll) {

        //######################
        //allega agli argomenti che vengono passati anche la variante per permettere una diversa raccolta dei dati
        $coll['ctvVariante']=$this->actualVar;
        $this->base->setActual($coll);
        //######################

        $res="";
        $this->totaleIncentivo=0;

        $this->printAnalisiBody="";

        foreach ($this->sezioni as $sez=>$s) {

            $res.='<hr style="width:80%;" />';
            $res.=$s->drawAnalisiColl($coll);

            $this->printAnalisiBody.='<div>'.$s->getPrintAnalisiBody().'</div>';

            $this->totaleIncentivo+=$s->getIncentivo();
        }

        /*$res.='<div>';
            $res.=json_encode($this->base->getActual());
        $res.='</div>';*/

        return $res;

    }

    function drawDatiExt() {

        //serve per abilitare solo i dati che appartengono al panorama in ogni sua variante
        ob_start();
            $this->drawSelectVar('');
            foreach ($this->varianti as $kv) {
                $this->drawStructBody($kv,'ext');
            }
        ob_end_clean();

        $this->base->drawDatiExt($this->panorama['data_i'],$this->panorama['data_f']);
    }

}

?>