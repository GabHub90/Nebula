<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/apps/lizard/classi/lizchekko.php');

class lizardAttach {

    protected $lizardFunzioni=array(
        "appOff"=>true,
        "carb"=>true,
        "utif"=>true,
        "olio"=>true,
        "magNOLOC"=>true,
        "magNEGATIVI"=>true,
        "magGAR"=>true,
        "lucia"=>true,
        "gdm"=>true,
        "comGar"=>true,
        "gdmP"=>true,
        "gdmS"=>true
    );

    protected $form=false;
    protected $methods=array();
    protected $closure=array();
    protected $galileo;

    function __construct($func,$galileo) {
        
        $this->galileo=$galileo;

        if (!array_key_exists($func,$this->lizardFunzioni)) die('Funzione inesistente');

        include($_SERVER['DOCUMENT_ROOT'].'/nebula/apps/lizard/core/funzioni/'.$func.'.php');
        
        $this->loadClosure();

        $this->form=new lizChekko('lizForm');

        //imposta CHEKKO
        $this->setStruttura();
    }

    public function __call($methodName, array $args) {

        if (isset($this->methods[$methodName])) {
            return call_user_func_array($this->methods[$methodName], $args);
        }
        else echo $methodName;
    }

    function loadClosure() {

        //################################################
        foreach ($this->closure as $key=>$c) {
            $this->methods[$key] = Closure::bind($c, $this, get_class() );
        }
        //################################################

    }

    function draw() {

        $this->form->draw();
    }

}
?>