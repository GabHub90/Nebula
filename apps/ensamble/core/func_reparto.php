<?php

$this->closure['drawMain']=function() {
    //elenca tutti i collaboratori di un reparto, i gruppi, i macrogruppi, i passi

    /////////////////////////////////
    /*config=array(
        "index"=>"",
        "range_i"=>"",
        "range_f"=>"",
        "tag"=>"w d m Y",
        "mtype"=>"nome",
        "m1"=>"none",
        "m2"=>"none",
        "m3"=>"none",
        "p1"=>"none",
        "p2"=>"none",
        "p3"=>"none",
        "export"=>"Ymd",
        "div"=>true
    );

    $css=array(
        "color"=>"black",
        "background-color"=>"transparent",
        "font-size"=>"1em"
    );*/
    $config=array(
        "index"=>"ensamble",
        "range_i"=>"20120501",
        "range_f"=>"21001231",
        "tag"=>"d m Y",
        "m1"=>array("giorno","1"),
        "m2"=>array("mese","1"),
        "p1"=>array("giorno","1"),
        "p2"=>array("mese","1"),
        "now"=>true
    );
    $css=array(
        "background-color"=>"#baecec"
    );

    $calnav=new calnav('D',$this->param['ens_today'],$config,$css,$this->galileo);

    ////////////////////////////////

    $this->ensPanorama=$this->collaboratori->setPanorama('A',$this->param['ens_reparto']);

    $this->galileo->getReparti($this->param['ens_macroreparto'],'');
    $result=$this->galileo->getResult();
    $fetID=$this->galileo->preFetchBase('reparti');

    while ($row=$this->galileo->getFetchBase('reparti',$fetID)) {
        if ($row['reparto']==$this->param['ens_reparto']) {
            $this->reparti[$row['reparto']]=$row;
        }
    }

    ///////////////////////////////   
    //$this->galileo->getCollaboratori('reparto',$this->param['ens_reparto'],$this->param['ens_today']);
    //$this->setCollaboratori();
    $this->collaboratori->getCollaboratoriReparto($this->param['ens_reparto']);
    $this->ensLista=$this->collaboratori->getCollaboratori();
    //////////////////////////////

    echo '<div class="ensRepartoLeft" >';

        $opt=array(
            "repEdit"=>false,
            "collEdit"=>true,
            "back"=>true,
            "add"=>true
        );

        $this->drawReparti($opt);

    echo '</div>';

    echo '<div class="ensRepartoRight">';

        echo '<div style="height:13%;">';

            echo '<div style="display:inline-block;width:62%;vertical-align:top;">';
                echo '<div style="margin-top:10px;">';
                    $calnav->draw();
                echo '</div>';
            echo '</div>';

            $temp=(!array_key_exists('ID',$this->ensPanorama))?false:$this->ensPanorama;

            echo '<div style="display:inline-block;width:32%;margin-left:6%;vertical-align:top;">';
                echo '<div style="margin-top:10px;">';
                    echo '<div>Panorama attivo: '.($temp?$this->ensPanorama['ID']:'').'</div>';
                    echo '<div>'.($temp?mainFunc::gab_todata($this->ensPanorama['inizio'].'01'):'').'</div>';
                echo '</div>';
            echo '</div>';

        echo '</div>';

        echo '<div id="ensRepartoInfoMain" style="height:87%;">';
            $this->drawGruppi();
        echo '</div>';

        //###################################
        //scrivere div "endRepartoEdit"
        //###################################


    echo '</div>';

    echo '<script type="text/javascript">';
        ob_start();
            include ('reparto.js');
        ob_end_flush();
    echo '</script>';

}

?>