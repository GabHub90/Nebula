<?php
//carica i WIDGET da visualizzare in base alla configurazione dell'utente e ad una chiave identificativa del contesto
//istanzia il JS che li caricherà tramite ajax asincrono

class nebulaWizard {

    //protected $lista=array();

    protected $id;

    protected $contesto=array(
        "utente"=>"",
        "tag"=>"",
        "data_rif"=>"",
        "data_i"=>"",
        "data_f"=>""
    );

    protected $widget=array();

    protected $galileo;

    function __construct($id,$contesto,$galileo) {

        $this->id=$id;

        $this->loadContesto($contesto);

        /*$this->lista=array(
            "qcReport"=>array(
                "loc"=>$_SERVER['DOCUMENT_ROOT']."/nebula/apps/qcheck/class/qc_widget.php"
            )
        );*/
 
        $this->galileo=$galileo;

        //function executeSelect($tipo,$tabella,$wclause,$order)
        //$wclause="utente='".$this->contesto['utente']."' AND contesto='".$this->contesto['tag']."'";
        //$order="pos";
        //$this->galileo->executeSelect('wizard','WIZARD_config',$wclause,$order);

        //###################################################
        //definizione dei WIDGET X UTENTE
        //TEST
        //record (contesto,utente,widget,argomenti,pos)
        if ($contesto['utente']=='f.cangiotti') {
            $this->widget=array("qcWidgetTot"=>'{"controlli":[ {"controllo":"2","periodo":"mese"} ] }');
        }
        else {
            $this->widget=array("qcWidget"=>'{"periodo":"trimestre"}');
        }
        //END TEST
        //####################################################
    }

    function loadContesto($contesto) {

        foreach ($this->contesto as $k=>$v) {
            if ( array_key_exists($k,$contesto) ) {
                $this->contesto[$k]=$contesto[$k];
            } 
        }

    }

    function draw() {

        echo '<div id="nebulaWizardMain_'.$this->id.'" style="width:90%;"></div>';

        echo '<script type"text/javascript" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/wizard/wizard.js"></script>';

        echo '<script type"text/javascript">';
            echo 'var c='.json_encode($this->contesto).';';
            echo 'var w='.json_encode($this->widget).';';
            echo 'window._nebulawizard_'.$this->id.'=new nebulaWizard("'.$this->id.'",c,w);';
            echo 'window._nebulawizard_'.$this->id.'.draw();';
        echo '</script>';
    }

    


}

?>