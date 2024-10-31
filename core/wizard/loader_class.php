<?php

class wizardLoader {

    protected $funzione="";
    protected $contesto="";

    //sono i parametri generali LOGGED passati dalla galassia che ha usato WIZARD
    /*... "configFunzioni": {
    "mytech": {
      "home": {
        "home": {
          "chk": "true"
        },
        "provo": {
          "chk": "true"
        }
      },
      "pianifico": {
        "home": {
          "chk": "true"
        },
        "presenza": {
          "chk": "true"
        }
      }
    }*/
    
    protected $main=array();

    protected $galileo;

    function __construct($funzione,$contesto,$args,$mainParam,$galileo) {

        $this->main=$mainParam;
        $this->funzione=$funzione;
        $this->contesto=$contesto;

        try {
            $t=json_decode($args,true);
        }catch(Exception $e) {
            $t=array();
        }

        foreach ($t as $k=>$v) {
            $this->contesto[$k]=$v;
        }

        $this->galileo=$galileo;
    }

    function draw() {

        //echo '<div class="nebulaWidget">';

            call_user_func_array(array($this,$this->funzione),array($this->contesto));

        //echo '</div>';
    }

    function qcWidget($args) {

        $args['data_rif']=date('Ymd');

        $widget=new qcWidget($this->contesto['utente'],$args,$this->main,$this->galileo);

        echo '<img style="position:absolute;width:40px;height:40px;left:1px;top:3px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/qcheck/img/qcheck.png" />';

        $widget->draw();

        /*echo '<div>';
            echo json_encode($this->main);
        echo '</div>';*/

    }

    function qcWidgetTot($args) {

        $args['data_rif']=date('Ymd');

        $widget=new qcWidget($this->contesto['utente'],$args,$this->main,$this->galileo);

        echo '<img style="position:absolute;width:40px;height:40px;left:1px;top:3px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/qcheck/img/qcheck.png" />';

        $widget->drawTot();
    }

}