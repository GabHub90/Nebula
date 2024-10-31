<?php

$this->closure['drawLeft']=function() {

    //echo '<script type="text/javascript" src="http://'.SADDR.'/nebula/core/calendario/calnav.js?v='.time().'"></script>';

    echo '<div style="height:70%;">';

        $arr=$this->getCheckTitle();
        echo '<div style="margin-top:5px;font-weight:bold;text-align:center;">';
            //echo $this->param['qc_reparto'].' - '.$this->collGruppo.' - '.$arr['titolo'];
            echo $this->param['qc_reparto'].' - '.$arr['titolo'];
        echo '</div>';

        ////////////////
        //FILTRO RICERCA
        $this->qcControlli['c'.$this->chain['IDabbinamento']]->drawStoricoFilter($this->collGruppo);

    echo '</div>';

    /*echo '<div style="margin-top:1%;height:55%;overflow:scroll;" >';

        echo '<div id="qcStoricoLines" style="width:90%;"></div>';

    echo '</div>';*/


};

$this->closure['drawRight']=function() {

    echo '<div id="qcStoricoElenco" style="height:100%;overflow:scroll;" >';

        echo '<div id="qcStoricoLines" style="position:relative;width:90%;left:5%;font-size:1.1em;"></div>';

    echo '</div>';

    echo '<div id="qcStoricoViewer" style="height:100%;display:none;">';

        echo '<div style="position:relative;height:9%;">';
            //echo '<iframe id="qcheck_pdf_frame" style="display:none;"></iframe>';
            echo '<button style="position:relative;margin-left:10px;top:10px;" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].pdfView(\'prova\');" >Scarica PDF</button>';
            echo '<img style="position:absolute;top:50%;right:5px;cursor:pointer;width:20px;height:20px;transform:translate(0px,-50%);" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/qcheck/img/chiudi.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].closeView();" />';
        echo '</div>';

        echo '<div style="height:91%;overflow:scroll;">';

            echo '<div id="qcStoricoView" style="position:relative;width:92%;left:1%;"></div>';

        echo '</div>';

    echo '</div>';

    

};

?>