<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/core/calendario/calnav.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/core/panorama/schemi.php');

class ensambleApp extends appBaseClass {

    protected $ensLista=array();
    protected $ensGruppi=array();
    protected $ensPassi=array();
    protected $ensPanorama=array();

    //reparti del macroreparo
    protected $reparti=array();

    //oggetto collaboratori
    protected $collaboratori;

    function __construct($param,$galileo) {
        
        parent::__construct($galileo);

        $this->loc='/nebula/apps/ensamble/';

        $this->param['ens_macroreparto']="";
        $this->param['ens_reparto']="";
        $this->param['ens_today']="";

        $this->loadParams($param);

        if ($this->param['ens_today']=="") $this->param['ens_today']=date('Ymd');

        if ($this->param['ens_reparto']=="") include('core/func_overview.php');
        else include('core/func_reparto.php');
        
        $this->loadClosure();

        //////////////////////////////////////////////
        $this->collaboratori=new ensambleSchemi($this->param['ens_today'],$this->galileo);
   
    }

    function initClass() {
        return ' ensambleCode(\''.$this->param['nebulaFunzione']['nome'].'\');';
    }

    function customDraw() {

        $this->drawMain();
    }

    function drawReparti($o) {

        //opt={ "repEdit":true/false , "collEdit":true/false }
        $opt=array(
            "repEdit"=>false,
            "collEdit"=>false,
            "back"=>false,
            "add"=>false
        );

        foreach ($opt as $k=>$v) {
            if (array_key_exists($k,$o)) {
                $opt[$k]=$o[$k];
            }
        }

        //////////////////////////////////////////////////////////////

        foreach ($this->reparti as $reparto=>$p) {

            if (array_key_exists($reparto,$this->ensLista)) {
                $r=$this->ensLista[$reparto];
            }
            else $r=array();

            echo '<div class="ensRepDiv">';

                if ($opt['repEdit']) {
                    echo '<img style="position:absolute;width:20px;height:20px;top:5px;right:5px;cursor:pointer;" src="http://'.SADDR.'/nebula/apps/ensamble/img/edit.png" onclick="window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.selectReparto(\''.$reparto.'\');"/>';
                }

                if ($opt['back']) {
                    echo '<img style="position:absolute;width:25px;height:25px;top:5px;right:5px;cursor:pointer;" src="http://'.SADDR.'/nebula/apps/ensamble/img/back.png" onclick="window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.backToOverview();"/>';
                }

                echo '<div class="ensRepDivTitle" style="';
                    if ($opt['add']) echo 'height:13%;';
                echo '">';
                    echo '<div style="text-align:center;">'.$p['reparto'].'</div>';
                    echo '<div style="text-align:center;">'.$p['descrizione'].'</div>';

                    if ($opt['add']) {
                        echo '<div style="margin-top:5px;margin-bottom:5px;text-align:center;">';
                            echo '<img style="width:25px;height:25px;" src="http://'.SADDR.'/nebula/apps/ensamble/img/add.png" onclick="window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.add();" />';
                        echo '</div>';
                    }

                echo '</div>'; 

                //echo '<div>'.json_encode($r['coll']).'</div>';

                echo '<div class="ensRepDivBodyContainer" style="';
                    if ($opt['add']) echo 'height:85%;';
                echo '">';

                    echo '<div class="ensRepDivBody">';

                        if (isset($r['gruppi'])) {
                            
                            foreach ($r['gruppi'] as $gruppo=>$g) {

                                echo '<div style="margin-top:5px;border-top: 3px dotted black;">';

                                    echo '<div style="background-color: #dddddd;">';
                                        echo $g['info']['tag'].' - '.$g['info']['descrizione'].' ('.$g['info']['macrogruppo'].')';
                                    echo '</div>';
                                
                                echo '</div>';

                                foreach ($g['coll'] as $ID_coll=>$c) {

                                    echo '<div class="ensRepDivElem">';
                                        echo '<div style="position:relative;">';
                                            //if ($c['cod_operaio']!='') echo '('.$c['cod_operaio'].') ';
                                            echo '<span style="font-size:smaller;">('.$ID_coll.') </span>';
                                            echo $c['cognome'].' '.$c['nome'];

                                            if($this->param['ens_reparto']!="") {
                                                echo '<img style="position:absolute;right:2px;top:2px;width:14px;height:14px;cursor:pointer;" src="http://'.SADDR.'/nebula/apps/ensamble/img/edit.png" onclick="window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.edit(\''.$ID_coll.'\',\''.(isset($this->ensPanorama['ID'])?$this->ensPanorama['ID']:'').'\');" />';
                                            }

                                        echo '</div>';
                                        echo '<div>';
                                            echo mainFunc::gab_todata($c['data_i']).' - '.$c['concerto'];
                                        echo '</div>';
                                    echo '</div>';

                                }

                            }
                        }

                    echo '</div>';

                echo '</div>';
            
            echo '</div>';

        }

    }

    function drawGruppi() {

        $wclause="gru.reparto='".$this->param['ens_reparto']."' AND gru.stato='1'";

        $this->galileo->getGruppi($wclause);

        //echo json_encode($this->galileo->getLog('query'));

        $fetID=$this->galileo->preFetchBase('maestro');

        while($row=$this->galileo->getFetchBase('maestro',$fetID)) {
            $this->ensGruppi[$row['ID_gruppo']]=$row;
        }

        ////////////////

        $this->galileo->getPassiReparto($this->param['ens_reparto']);

        $fetID=$this->galileo->preFetchBase('applicazioni');

        while($row=$this->galileo->getFetchBase('applicazioni',$fetID)) {
            $this->ensPassi[]=$row;
        }

        ////////////////////////////////////////////////////////

        $macro=array();

        echo '<div class="ensRepDiv">';

            echo '<div class="ensRepDivBodyContainer" style="height:100%">';

                echo '<div class="ensRepDivTitle" style="height:5%;background-color:transparent;">';
                    echo '<div style="text-align:center;">Gruppi</div>';
                echo '</div>';

                echo '<div class="ensRepDivBody" style="height:95%;">';

                    foreach ($this->ensGruppi as $ID_gruppo=>$g) {

                        if ($g['macrogruppo']!="") {
                            $macro[$g['macrogruppo']]=array(
                                "descrizione"=>$g['des_macrogruppo'],
                                "posizione"=>$g['posizione_macrogruppo']
                            );
                        }

                        echo '<div class="ensRepDivElem" style="text-align:center;" >';
                            echo '<div>('.$ID_gruppo.') '.$g['gruppo'].'</div>';
                            echo '<div>'.$g['des_gruppo'].'</div>';
                        echo '</div>';
                    }

                echo '</div>';

            echo '</div>';
        
        echo '</div>';

        /////////////////////////////////
        //macrogruppi
        
        echo '<div class="ensRepDiv">';

            echo '<div class="ensRepDivBodyContainer" style="height:100%">';
                    
                echo '<div class="ensRepDivTitle" style="height:5%;background-color:transparent;">';
                    echo '<div style="text-align:center;">Macro Gruppi</div>';
                echo '</div>';

                echo '<div class="ensRepDivBody" style="height:95%;">';

                    foreach ($macro as $macrogruppo=>$m) {

                        echo '<div class="ensRepDivElem" style="text-align:center;" >';
                            echo '<div>'.$macrogruppo.'</div>';
                            echo '<div>'.$m['descrizione'].'</div>';
                        echo '</div>';
                    }

                echo '</div>';

            echo '</div>';
        
        echo '</div>';

        /////////////////////////////////
        //passi

        echo '<div class="ensRepDiv">';

            echo '<div class="ensRepDivBodyContainer" style="height:100%">';
                
                echo '<div class="ensRepDivTitle" style="height:5%;background-color:transparent;">';
                    echo '<div style="text-align:center;">Passi</div>';
                echo '</div>';

                echo '<div class="ensRepDivBody" style="height:95%;">';

                    foreach ($this->ensPassi as $p) {

                        echo '<div class="ensRepDivElem" style="';
                            if ($p['modificatore']==1) echo 'background-color:#bff3bf;';
                            else echo 'background-color:#f3bfbf;';
                        echo '">';

                            switch ($p['livello']) {

                                case "reparto":
                                    echo '<div>Reparto:'.$p['reparto'].'</div>';
                                break;
                                case "macrorep":
                                    echo '<div>Macroreparto:'.$p['tipo'].'</div>';
                                break;
                                case "gruppo":
                                    echo '<div>Gruppo:'.$p['des_gruppo'].'</div>';
                                break;
                                case "macrogru":
                                    echo '<div>Macrogruppo:'.$p['macrogruppo'].'</div>';
                                break;
                            }
                            
                            echo '<div>'.$p['galassia'].':'.$p['sistema'].'</div>';

                            if ($p['funzione']!="") {
                                echo '<div>Funzione:'.$p['funzione'].'</div>';
                            }
                            
                        echo '</div>';
                    }

                echo '</div>';
            
            echo '</div>';
        
        echo '</div>';

    }

}
?>