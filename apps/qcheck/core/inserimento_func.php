<?php

$this->closure['drawLeft']=function() {

    //echo '<script type="text/javascript" src="http://'.SADDR.'/nebula/core/calendario/calnav.js?v='.time().'"></script>';

    $arr=$this->getCheckTitle();
    echo '<div style="margin-top:5px;font-weight:bold;text-align:center;">';
        //echo $this->param['qc_reparto'].' - '.$this->collGruppo.' - '.$arr['titolo'];
        echo $this->param['qc_reparto'].' - '.$arr['titolo'];
    echo '</div>';
    echo '<div style="margin-top:5px;text-align:center;">';
        echo $arr['descrizione'];
    echo '</div>';

    ///////////////////////////////////////////
    $c=array(
        "index"=>"qc",
        "range_i"=>"20210101",
        "range_f"=>"20301231",
        "tag"=>"m Y",
        "mtype"=>"nome",
        "m1"=>array("mese","1"),
        "p1"=>array("mese","1"),
        "export"=>"Ymd",
        "div"=>true
    );

    $css=array(
        "color"=>"black",
        "background-color"=>"#ffd701",
        "font-size"=>"1.1em"
    ); 

    $this->qcCalnav=new calnav("M",$this->param["qc_today"],$c,$css,$this->galileo);

    /////////////////////////////////////////////

    echo '<div>';

        echo '<div id="qc_stat_div">';

            //Z-INDEX=3
            echo '<div style="position:relative;width:100%;height:10%;text-align:center;margin-top:1%;z-index:3;">';
                echo $this->qcCalnav->draw();
            echo '</div>';

            echo '<div style="position:relative;width:100%;height:6%;text-align:right;">';
                if (isset($this->mainAuth['new'])) {
                    if ($this->mainAuth['new']) {
                        echo '<div class="divButton" style="float:right;font-size:larger;" onclick="window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.openNew();">Nuovo</div>';
                    }
                }
            echo '</div>';

            echo '<div style="position:relative;width:100%;height:70%;margin-top:5%;overflow-y:scroll;">';

                //function __construct($reparto,$controllo,$galileo)
                //$wgt=new qcReport($this->param['qc_reparto'],$this->param['qc_check'],$this->galileo);
                $wgt=new qcReport($this->param['qc_reparto'],$this->chain['actualControllo'],$this->galileo);

                echo '<div style="font-size:1.3em;width:90%;margin-left:5%;">';
                    $calConfig=$this->qcCalnav->getConfig();
                    $wgt->draw_generale($calConfig['today']);
                    //echo json_encode($this->galileo->getLog('query'));
                echo '</div>';

            echo '</div>';
        
        echo '</div>';

        echo '<div id="qc_new_div" style="display:none;" >';

            echo '<div style="height:7%;">';

                echo '<div style="text-align:center;">';
                    echo '<div class="divButton" style="position:relative;left:50%;margin-left:-50px;margin-top:15px;" onclick="window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.closeNew();">Annulla</div>';
                    //echo '<button style="margin-top:10px;" onclick="window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.closeNew();">annulla</button>';
                echo '</div>';

            echo '</div>';

            echo '<div style="height:82%;overflow-y:scroll;">';

                echo '<div>';
                    $this->drawNew();
                echo '</div>';

                echo '<hr/>';

                echo '<div style="text-align:center;margin-top:10px;">';
                    echo '<div class="divButton" style="position:relative;left:50%;margin-left:-50px;" onclick="window._js_chk_qc_new.scrivi();">Conferma</div>';
                echo '</div>';

            echo '</div>';

        echo '</div>';

    echo '</div>';

    echo '<script type="text/javascript">';
        ob_start();
            include (DROOT.'/nebula/apps/qcheck/core/inserimento.js');
        ob_end_flush();
    echo '</script>';

};

$this->closure['drawRight']=function() {

    $ret=$this->getListOpen();
    $cret=count($ret);

    echo '<div class="qcListTitle">';
        echo '<div style="">Controlli Aperti ('.(!$cret?0:$cret).')</div>';
        echo '<div style="text-align: left;font-size: 0.8em;margin-top: 5px;" >';

            echo '<div style="display:inline-block;width:60%;height:30px;vertical-align:top;">';
                echo '<label style="margin-left:10px;">Filtro (chiave): </label>';
                echo '<input id="qc_filter_aperto" type="text" style="margin-left:10px;width:40%;font-size:1em;text-align:center;font-weight:bold;" onkeydown="if(event.keyCode==13) window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.filtra(\'aperto\');"/>';
            echo '</div>';

            echo '<div style="display:inline-block;width:30%;text-align:right;">';
                echo '<img style="width:30px;height:30px;cursor:pointer;" src="http://'.SADDR.'/nebula/apps/qcheck/img/filtra.png" onclick="window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.filtra(\'aperto\');"/>';
                echo '<img style="width:30px;height:30px;margin-left:20px;cursor:pointer;" src="http://'.SADDR.'/nebula/apps/qcheck/img/annulla_filtra.png" onclick="window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.annullaFiltra(\'aperto\');"/>';
            echo '</div>';

        echo '</div>';
    echo '</div>';

    /*echo '<div>';
        $this->loadForm("1","3");
        echo json_encode($this->getFormLog("1"));
    echo '</div>';*/

    echo '<div class="qcList">';

        foreach ($ret as $IDcontrollo=>$c) {

            //$div=$this->qcControlli['c'.$this->param['qc_check']]->drawControllo($c,$this->collGruppo);
            $div=$this->qcControlli['c'.$c['ID_abbinamento']]->drawControllo($c['moduli'],$this->collGruppo);

            /*echo '<div>';
                echo json_encode($c);
            echo '</div>';*/

            foreach ($c['moduli'] as $m) {
                $chiave=$m['chiave'];
                break;
            }

            echo '<div id="qc_elemento_lista_'.$chiave.'" qc_lista_tipo="aperto" class="qcListElement">';

                echo '<div class="qcElementIntest">';

                    echo '<div style="display:inline-block;width:80%;vertical-align:top;">';
                        echo $div['intestazione'];
                    echo '</div>';

                    if ($div['score_controllo']['completo']) $color="black;";
                    else $color="#cccccc;";

                    echo '<div style="display:inline-block;width:20%;vertical-align:top;text-align:right;font-weight:bold;color:'.$color.'">';
            
                        echo '<span>'.$div['score_controllo']['punteggio'].'</span>';
                        if ($div['score_controllo']['domande']==0) $comp=0;
                        else $comp=round( ($div['score_controllo']['risposte']/$div['score_controllo']['domande'])*100 );
                        echo '<span style="font-size:smaller;margin-left:5px;">( '.$comp.'% )</span>';

                    echo '</div>';
            
                echo '</div>';

                
                foreach ($div['txt'] as $k=>$m) {

                    echo '<div class="qcElementModulo" >';
                        echo $m;
                    echo '</div>';
                }

            echo '</div>';
        }
    
    echo '</div>';

}

?>