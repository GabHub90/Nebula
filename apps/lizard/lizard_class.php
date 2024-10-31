<?php

require('classi/lizchekko.php');

class lizardApp extends appBaseClass {

    protected $lizardFunzioni=array(
        "appOff"=>true,
        "carb"=>true,
        "utif"=>true,
        "giatif"=>true,
        "olio"=>true,
        "magNOLOC"=>true,
        "magNEGATIVI"=>true,
        "magGAR"=>true,
        "lucia"=>true,
        "gdm"=>true,
        "comGar"=>true,
        "gdmP"=>true,
        "gdmS"=>true,
        "fattint"=>true,
        "stkvei"=>true,
        "revi"=>true,
        "wake"=>true,
        "statRic"=>true,
        "passCons"=>true,
        "fidel"=>true
    );

    protected $form=false;

    function __construct($param,$galileo) {
        
        parent::__construct($galileo);

        $this->loc='/nebula/apps/lizard/';

        $this->param['liz_function']="";

        $this->loadParams($param);

        if (!array_key_exists($this->param['liz_function'],$this->lizardFunzioni)) die('Funzione inesistente');

        include('core/funzioni/'.$this->param['liz_function'].'.php');
        
        $this->loadClosure();

        $this->form=new lizChekko('lizForm');

        //imposta CHEKKO
        $this->setStruttura();
   
    }

    function initClass() {
        return ' lizardCode(\''.$this->param['nebulaFunzione']['nome'].'\');';
    }

    function customDraw() {

        $this->form->draw();

    }

}
?>