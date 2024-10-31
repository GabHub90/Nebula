<?php

$this->closure['drawLeft']=function() {

    echo '<div style="position:relative;width:100%;height:6%;text-align:right;">';
        echo '<div class="divButton" style="float:right;font-size:larger;" onclick="window._nebulaApp.ribbonExecute();">Annulla</div>';
    echo '</div>';

    $arr=$this->getCheckTitle();
    echo '<div style="margin-top:5px;font-weight:bold;text-align:center;">';
        echo $this->param['qc_reparto'].' - '.$arr['titolo'];
    echo '</div>';
    echo '<div style="margin-top:5px;text-align:center;">';
        echo $arr['descrizione'];
    echo '</div>';

    /*
    $ret=array(
        "ID_controllo"=>"1",
        "controllo"=>"1",
        "reparto"=>"VWS",
        "d_controllo"=>"20210221",
        "versione"=>"1",
        "chiave"=>"123456",
        "intestazione"=>"AB123CD - Mario Rossi",
        "stato_controllo"=>"aperto",
        "punteggio_controllo"=>"",
        "modulo"=>"1",
        "des_modulo"=>"tecnico",
        "d_modulo"=>"20210221",
        "esecutore"=>"s.salucci",
        "operatore"=>"n.gjura",
        "variante"=>"1",
        "des_variante"=>"manutenzione",
        "risposte"=>"",
        "completezza"=>"",
        "punteggio_modulo"=>"",
        "stato_modulo"=>"salvato"
    );
    */

    echo '<div style="font-size:1.5em;">';

        echo '<div style="margin-top:20px;">';
            echo '<div class="qcInfoLabel" style="" >Controllo:</div>';
            echo '<div style="display:inline-block;" >'.mainFunc::gab_todata($this->qcInfo['d_controllo']).' - ('.$this->qcInfo['ID_controllo'].')</div>';

            //if ($this->id->getFuncAuth('qcelimina')) {
            if (isset($this->mainAuth['elimina'])) {
                if ($this->mainAuth['elimina']) {
                    echo '<div style="display:inline-block;margin-left:10px;vertical-align:middle;height:25px;">';
                        echo '<button style="font-weight:bold;font-size:0.7em;color:red;" onclick="window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.elimina('.$this->qcInfo['ID_controllo'].');" >ELIMINA</button>';
                    echo '</div>';
                }
            }
            //}
            //if ($this->id->getFuncAuth('qcforzaChiusura')) {
            if (isset($this->mainAuth['chiusura'])) {
                if ($this->mainAuth['chiusura']) {
                    echo '<div style="display:inline-block;margin-left:10px;vertical-align:middle;height:25px;">';
                        echo '<button style="font-weight:bold;font-size:0.7em;" onclick="window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.forzaChiusura('.$this->qcInfo['ID_controllo'].');">Forza Chiusura</button>';
                    echo '</div>';
                }
            }
            //}
        echo '</div>';

        echo '<div style="margin-top:5px;">';
            echo '<div class="qcInfoLabel" style="display:inline-block;" >Chiave:</div>';
            echo '<div style="display:inline-block;margin-left:5px;">'.$this->qcInfo['chiave'].'</div>';
        echo '</div>';

        echo '<div style="">';
            echo '<div style="">'.substr($this->qcInfo['intestazione'],0,30).'</div>';
        echo '</div>';

        echo '<div style="margin-top:5px;color:sienna;font-weight:bold;">';
            echo $this->qcInfo['des_modulo'].' - '.$this->qcInfo['des_variante'];
        echo '</div>';

        echo '<div style="margin-top:5px;">';
            echo '<div class="qcInfoLabel" style="display:inline-block;" >Operatore:</div>';
            //se operatore è un riferimento all'esecutore di un altro modulo
            if (substr($this->qcInfo['operatore'],0,1)=='#') {
                $t=($this->qcInfo['des_rif_operatore']!="")?$this->qcInfo['des_rif_operatore']:$this->qcInfo['operatore'];
            }
            else {
                $t=$this->qcInfo['operatore'];
            }
            echo '<div style="display:inline-block;margin-left:5px;">'.$t.'</div>';
        echo '</div>';
        
        echo '<div style="margin-top:5px;">';
            echo '<div class="qcInfoLabel" style="display:inline-block;" >Esecutore:</div>';
            echo '<div style="display:inline-block;margin-left:5px;">'.$this->qcInfo['esecutore'].'</div>';
        echo '</div>';

        echo '<div style="margin-top:5px;">';
            echo '<div class="qcInfoLabel" style="display:inline-block;" >Data:</div>';
            echo '<div style="display:inline-block;margin-left:5px;">'.( mainFunc::gab_todata(substr($this->qcInfo['d_modulo'],0,8) ).' '.substr($this->qcInfo['d_modulo'],9,5)).'</div>';
            //echo '<div>'.$this->qcInfo['d_modulo'].'</div>';
        echo '</div>';

        echo '<div style="position:relative;width:100%;text-align:left;margin-top:25px;font-size:smaller;">';
            echo '<div class="divButton" style="width:120px;opacity:0.3;" >Modifica</div>';

            //se il form non è nè salvato nè aperto non scrivere il bottone
            if ($this->qcInfo['stato_modulo']=='salvato' || $this->qcInfo['stato_modulo']=='aperto') {
                echo '<div class="divButton" style="position:absolute;top:0px;right:0px;" onclick="window._js_chk_'.$this->getFormTag().'.qcSalva();">Salva</div>';
            }
        echo '</div>';
    
    echo '</div>';

};

$this->closure['drawRight']=function() {

    echo '<div style="padding:5px;box-sizing: border-box;height:100%;" >';

        echo '<div class="qcListTitle">';
            echo '<div style="">Controllo</div>';
            //echo json_encode($this->chain);
        echo '</div>';

        $this->loadForm();

        echo  '<div class="qcList">';
            $this->drawForm();
        echo '</div>';

        //echo json_encode($this->getFormLog());

    echo '</div>';

}

?>