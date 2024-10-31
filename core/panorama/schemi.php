<?php

require_once('collaboratori.php');

class ensambleSchemi extends ensambleCollaboratori {

    protected $panorama=array(
        "A"=>array(),
        "P"=>array()
    );

    protected $schemi=array(
        "A"=>array(),
        "P"=>array()
    );

    protected $subrep=array(
        "A"=>array(),
        "P"=>array()
    );

    protected $collSkemi=array();

    protected $turni=array();
    
    //11 colori per gli schemi
    protected $colors=array(
        "#3DB300",
        "#FB0300",
        "#295CF7",
        "#fd2CC9",
        "#07A1D4",
        "#E4AB00",
        "#855DFB",
        "#FF4F15",
        "#9CC324",
        "#FB1A52",
        "#F9C91B"
    );

    protected $onclick="";

    function __construct($d,$galileo) {
        
        parent::__construct($d,$galileo);

        //executeSelect($tipo,$tabella,$wclause,$order)
        $this->galileo->getTurni();
        $fetID=$this->galileo->preFetchBase('schemi');

        while($row=$this->galileo->getFetchBase('schemi',$fetID)) {
            $this->turni[$row['codice']][$row['wd']]=$row;
        }

    }

    function setOnclick($txt) {
        $this->onclick=$txt;
    }

    function setPanorama($tipo,$reparto) {

        $this->galileo->getPanorama($tipo,$reparto,$this->rif);

        $fetID=$this->galileo->preFetchBase('schemi');

        while($row=$this->galileo->getFetchBase('schemi',$fetID)) {
            $this->panorama[$tipo]=$row;
            break;
        }

        $this->getSchemi($tipo);

        $this->getSubrep($tipo);

        return $this->getPanorama($tipo);
    }

    function getPanorama($tipo) {
        return $this->panorama[$tipo];
    }

    function getSchemi($tipo) {

        if (!array_key_exists('ID',$this->panorama[$tipo])) return;

        $this->galileo->getSchemi($this->panorama[$tipo]['ID']);
        $fetID=$this->galileo->preFetchBase('schemi');

        $c=0;
        $cc=count($this->colors)-1;

        while($row=$this->galileo->getFetchBase('schemi',$fetID)) {
            $this->schemi[$tipo][$row['codice']]=$row;
            $this->schemi[$tipo][$row['codice']]['colore']=$this->colors[$c];

            $c++;
            if ($c>$cc) $c=0;
        }
    }

    function getSubrep($tipo) {

        if (!array_key_exists('ID',$this->panorama[$tipo])) return;

        $this->galileo->getSubrep($this->panorama[$tipo]['ID']);
        $fetID=$this->galileo->preFetchBase('schemi');

        while($row=$this->galileo->getFetchBase('schemi',$fetID)) {
            $this->subrep[$tipo][$row['subrep']]=$row;
        }

    }

    function setCollSk($tipo) {

        if (!array_key_exists('ID',$this->panorama[$tipo])) return;

        //$rif è la data di riferimento memorizzata in ensableCollaboratori
        $this->galileo->getCollSk($this->panorama[$tipo]['ID'],$this->rif);
        $fetID=$this->galileo->preFetchBase('schemi');

        while($row=$this->galileo->getFetchBase('schemi',$fetID)) {
            $this->collSkemi[$row['collaboratore']][$row['skema']]=$row;
            $this->collSkemi[$row['collaboratore']][$row['skema']]['colore']=$this->schemi[$tipo][$row['skema']]['colore'];
        }

        //echo json_encode($this->galileo->getLog('query'));
    }

    function setCollSkIntervallo($tipo,$reparto,$i,$f) {

        if (!array_key_exists('ID',$this->panorama[$tipo])) return;

        //$rif è la data di riferimento memorizzata in ensableCollaboratori
        $this->galileo->getCollSkIntervallo("'".$reparto."'",$i,$f);
        $fetID=$this->galileo->preFetchBase('schemi');

        while($row=$this->galileo->getFetchBase('schemi',$fetID)) {
            $this->collSkemi[$row['collaboratore']][$row['skema']]=$row;
            $this->collSkemi[$row['collaboratore']][$row['skema']]['colore']=$this->schemi[$tipo][$row['skema']]['colore'];
        }

        //echo json_encode($this->galileo->getLog('query'));
    }

    function getStyle() {
        echo '<style>@import url("http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/panorama/style.css");</style>';
    }

    function exportSchemi($tipo) {
        return $this->schemi[$tipo];
    }

    function exportSubrep($tipo) {
        return $this->subrep[$tipo];
    }

    function exportTurni() {
        return $this->turni;
    }

    function exportCollSkemi() {
        return $this->collSkemi;
    }

    function calcoloOre($orario) {

        $minuti=0;

        foreach ($orario as $o) {

            $minuti+=mainFunc::gab_delta_min($o['i'],$o['f']);
        }

        return $minuti;
    }

    function drawSchemi($tipo) {
        
        echo '<div id="skemicontainer_'.$tipo.'" class="schemiContainer" style="">';

            echo '<div class="schemiMain" style="">';

                //SUBREPS
                echo '<table class="schemiGrigliaTab schemiGrigliaSubrepTab" style="" >';

                    echo '<tr style="height:15px;"></tr>';

                    $col=0;
                    $row=0;
                    foreach ($this->subrep[$tipo] as $subrep=>$r) {
        
                        if ($col==0) {
                            echo '<tr>';
                                echo '<td class="schemiGrigliaHeadTD">';
                                    if ($row==0) echo '<div style="font-size:1.4em;font-weight:bold;">Subreps</div>';
                                echo '</td>';
                                $col++;
                                $row++;
                        }
        
                        echo '<td class="schemiGrigliaHeadTD" style="border:1px solid black;" >';

                            echo '<div style="width:100%;text-align: center;font-size: 0.8em;';
                                if ($r['reparto']=='') echo 'background-color:#dddddd;';
                            echo '">';
                                echo '<div style="font-weight:bold;">'.$subrep.'</div>';
                                echo '<div>'.substr($r['descrizione'],0,15).'</div>';
                            echo '</div>';

                        echo '</td>';
        
                        $col++;
        
                        if ($col==7) {
                            echo '</tr>';
                            $col=0;
                        }
                    }
        
                    if ($col!=0) {

                        while ($col<7) {
                            echo '<td class="schemiGrigliaHeadTD"></td>';
                            $col++;
                        }

                        echo '</tr>';
                    }
        
                    echo '<tr style="height:15px;"></tr>';

                echo '</table>';

                //////////////////////////////////////////////////
                //SCHEMI
                foreach ($this->schemi[$tipo] as $codice=>$s) {

                    //echo '<div style="position:relative;margin-top:10px;border-bottom:1px solid black;">';
                    echo '<div class="schemiTitleContainer" style="background-color:'.$s['colore'].'99;">';
                        echo '<div class="schemiTitle" style="" >'.$s['titolo'].' ('.$s['codice'].') - '.mainFunc::gab_todata($s['data_i']).' ('.$s['blocco_inizio'].') ';
                            if ($s['mark']==1) echo ' - con segnaposto';
                        echo '</div>';
                        echo '<div class="schemiSubtitle" style="">';
                            //se fisso
                            if ($s['turnazione']!=0) {
                                echo 'turnazione ogni '.$s['turnazione'].' giorni ';
                                if ($s['flag_festivi']==1) {
                                    echo ' (salta '.$s['on_flag'].' giorni se festivo) ';
                                }
                                if ($s['flag_turno']==1) {
                                    echo ' (salta '.$s['on_flag'].' giorni se chiusura) ';
                                }
                            }

                            if ($s['exclusive']==1) echo ' - esclusivo';
                        echo '</div>';
                    echo '</div>';

                    $this->drawGriglia($tipo,$codice,$s['exclusive']);
                }

            echo '</div>';
        
        echo '</div>';

    }

    function drawGriglia($tipo,$codice,$exc) {

        try{
            $griglia=json_decode($this->schemi[$tipo][$codice]['griglia'],true);
        }catch (exception $e) {
            $griglia=array();
        }

        echo '<table class="schemiGrigliaTab" style="" >';

            echo '<tr>';
                for ($i=0;$i<=6;$i++) {

                    echo '<td class="schemiGrigliaHeadTD" style="">';
                        echo mainFunc::gab_weektotag($i);
                    echo '</td>';
                
                }
            echo '</tr>';

            echo '<tr>';
                for ($i=0;$i<=6;$i++) {

                    echo '<td class="schemiGrigliaElemTD" style="border-color:'.$this->schemi[$tipo][$codice]['colore'].';">';

                        foreach ($griglia as $blocco=>$g) {

                            //compatibilità passato
                            if ($blocco=="0") continue;

                            $this->drawDiv($tipo,$codice,$blocco,$g,'',$i,$exc,'');
                        }

                    echo '</td>';
                
                }
            echo '</tr>';

        echo '</table>';
    }

    function drawSkDay($tipo,$codice,$tag,$i,$bloccoExc,$onclick) {

        try{
            $griglia=json_decode($this->schemi[$tipo][$codice]['griglia'],true);
        }catch (exception $e) {
            $griglia=array();
        }

        //richiama dall'esterno la scrittura dei blocchi di uno schema in un dato giorno della settimana
        echo '<div style="border: 2px solid black;width:100%;height:100%;border-color:'.$this->schemi[$tipo][$codice]['colore'].';box-sizing:border-box;">';

            echo '<div style="min-height:10px;font-size:0.8em;font-weight:bold;color:'.$this->schemi[$tipo][$codice]['colore'].';text-align:center;">'.$codice.'</div>';

            foreach ($griglia as $blocco=>$g) {

                //compatibilità passato
                if ($blocco=="0") continue;

                //se è uno schema exclusive ed il blocco NON è quello attivo quel giorno allora salta
                if ($bloccoExc!="" && $blocco!=$bloccoExc) continue;

                $this->drawDiv($tipo,$codice,$blocco,$g,$tag,$i,($bloccoExc!="")?1:0,$onclick);

            }

        echo '</div>';
    }

    function drawDiv($tipo,$codice,$blocco,$g,$tag,$i,$exc,$onclick) {
        ///////////////////////////////////
        //BUILD DIV
        $txt1="";
        $txt2="";

        if ($onclick=="") $onclick=$this->onclick;

        try{
            $orario=json_decode($this->turni[$g['turno']][$i]['orari'],true);
        }
        catch(Exception $e) {
            $orario=array();
        }

        $minuti=$this->calcoloOre($orario);

        if ($minuti>0) {

            if (array_key_exists('agenda',$g) && count($g['agenda'])>0) {

                //ci sarà sempre UN SOLO SUBREP (al 100%)
                foreach ($g['agenda'] as $subrep=>$a) {

                    //$txt='<b>'.$subrep.'</b> '.mainFunc::gab_mintostring($minuti);
                    $txt1='<b style="';
                        if (!array_key_exists($subrep,$this->subrep[$tipo])) {
                            $txt1.='background-color:#fbb0b0;';
                        }
                    $txt1.='">'.$subrep.'</b>';
                }
            }

            else if ((int)$g['ricric']!=0) {
                $txt1.='<b>Ric: '.$g['ricric'].'/h</b>';
            }
        
        }

        foreach ($orario as $o) {

            if ($o['i']!='00:00' || $o['f']!="00:00") {
                $txt2.='<div class="schemiGrigliaOrariElem schemiGrigliaOrari" style="">'.$o['i'].' - '.$o['f'].'</div>';
            }
        }

        /////////////////////////////////////////////////////

        $temp=($txt1!="" || $txt2!="")?true:false;

        if ($temp) {

            echo '<div class="schemiGrigliaElemDIV" style="';
                //if (!$temp) echo 'display:block;height:33.3%;';
            echo '" >';
            //echo '<div style="padding:2px;box-sizing:border-box;">';

                echo '<div class="schemiGrigliaBloccoContainer" style="';
                    if ($exc==1) echo 'background-color:yellow;';
                echo '">';

                    echo '<div class="schemiGrigliaBlocco" style="">';
                        echo '<div>'.$blocco.'</div>';
                        //il controllo dello zero serve per il passato
                        if ($blocco!=$g['next'] && $g['next']!="0") {
                            echo '<img style="width:5px;height:8px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/panorama/img/blackarrowD.png"/>';
                            echo '<div>'.$g['next'].'</div>';
                        }
                    echo '</div>';

                echo '</div>';

                echo '<div class="schemiGrigliaOrariContainer" style="">';    

                    echo '<div class="schemiGrigliaOrariElem" style="" >';
                        echo $txt1;
                    echo '</div>';

                    echo $txt2;

                echo '</div>';

                echo '<div class="schemiGrigliaCover" style="position:absolute;top:0px;left:0px;width:100%;height:100%;z-index:3;background-color:transparent;';
                    if ($onclick!='') echo 'cursor:pointer;';
                echo '" data-panorama="'.$this->panorama[$tipo]['ID'].'" data-codice="'.$codice.'" data-tag="'.$tag.'" data-blocco="'.$blocco.'" onclick="'.$onclick.'"></div>';

            echo '</div>';
        }
    }

    function drawCollSk($IDcoll) {

        if (!array_key_exists($IDcoll,$this->collSkemi)) return;

        foreach ($this->collSkemi[$IDcoll] as $skema=>$s) {

            echo '<div class="schemiCollElem" style="border:1px solid '.$s['colore'].';color:'.$s['colore'].'" data-idcoll="'.$IDcoll.'" data-skema="'.$s['skema'].'" data-turno="'.$s['turno'].'" >';

                echo '<div style="display:inline-block;width:60%;">';
                    echo substr($s['skema'],0,10).' ('.$s['turno'].')';
                echo '</div>';

                echo '<div style="display:inline-block;width:40%;">';
                    echo mainFunc::gab_todata($s['data_i']);
                echo '</div>';

            echo '</div>';
        }

    }

}
?>