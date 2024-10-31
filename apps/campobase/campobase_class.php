<?php

include_once(DROOT.'/nebula/core/wizard/wizard.php');

class campobaseApp extends appBaseClass {

    protected $wizard;

    function __construct($param,$galileo) {
        
        parent::__construct($galileo);

        $this->loc='/nebula/apps/campobase/';

        $this->loadParams($param);

        $contesto=array(
            "utente"=>$this->id->getLogged(),
            "tag"=>"campobase",
            "data_rif"=>date('Ymd:H:i')
        );

        $this->wizard=new nebulaWizard('cbase',$contesto,$this->galileo);

    }

    function initClass() {
        return ' campobaseCode(\''.$this->param['nebulaFunzione']['nome'].'\');';
    }

    function customDraw() {

        echo '<div class="ov_main_div">';

            echo '<div class="ov_left_div" style="border-image:url(http://'.$_SERVER['SERVER_ADDR'].'/nebula/main/img/bordo_div2.png) 3;">';
                //include(DROOT.'/nebula/apps/comune/under_construction.php');
                echo '<img style="width:100%;height:100%;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/campobase/img/90.png" />';
            echo '</div>';

            echo '<div class="ov_right_div" style="border-image:url(http://'.$_SERVER['SERVER_ADDR'].'/nebula/main/img/bordo_div2.png) 3;background-image:url(http://'.$_SERVER['SERVER_ADDR'].'/nebula/main/img/starred3.png);overflow-y:scroll;" >';
            
                $this->wizard->draw();
                
            echo '</div>';

        echo '</div>';

    }

}
?>