<?php

abstract class appBaseClass {

    //gli altri parametri vengono inizializzati dalla classe child
    protected $param=array(
        "nebulaFunzione"=>"",
        "appArgs"=>array()
    );
    
    //classe nebula_ID
    protected $id;
    protected $galileo;

    //è il percorso dell'applicazione che viene inizializzato dalla classe child
    // esempio: '/nebula/apps/qcheck/'
    protected $loc="";

    //codici delle funzioni da importare nella classe in base a SETCLASS
    //definizione di nuovi metodi in maniera dinamica
    protected $methods=array();
    protected $closure=array();

    protected $log=array();

    function __construct($galileo) {
        
        $this->galileo=$galileo;

    }

    //viene chiamata da child dopo aver inizializzato i parametri
    function loadParams($param) {

        foreach ($this->param as $k=>$v) {
            if (isset($param['ribbon']) && array_key_exists($k,$param['ribbon']) ) {
                $this->param[$k]=$param['ribbon'][$k];
            }
        }

        if ( isset($param['args']) )  {
            foreach ($this->param['appArgs'] as $k=>$v) {
                if ( array_key_exists($k,$param['args']) ) {
                    $this->param['appArgs'][$k]=$param['args'][$k];
                }
            }
        }

        if (isset($param['nebulaFunzione'])) {
            $this->param['nebulaFunzione']=$param['nebulaFunzione'];
        }

        if (isset($param['contesto'])) {
            $this->id=new nebulaID($param['contesto'],$param['nebulaFunzione']);
            $this->id->dechain($this->galileo);
        }

        //$this->log=$param;
    }

    public function __call($methodName, array $args) {

        if (isset($this->methods[$methodName])) {
            return call_user_func_array($this->methods[$methodName], $args);
        }
    }

    function loadClosure() {

        //################################################
        foreach ($this->closure as $key=>$c) {
            $this->methods[$key] = Closure::bind($c, $this, get_class() );
        }
        //################################################

    }

    function draw() {

        echo '<style>@import url("http://'.SADDR.$this->loc.'style.css?v='.time().'");</style>';
        echo '<script type="text/javascript" src="http://'.SADDR.$this->loc.'code.js?v='.time().'"></script>';

        echo '<script type="text/javascript">';
            echo 'window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'=new '.$this->initClass().';';
        echo '</script>';

        $this->customDraw();

        echo '<script type="text/javascript">';
            echo 'if (window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.hasOwnProperty(\'nebulaAppSetup\')) window._nebulaApp_'.$this->param['nebulaFunzione']['nome'].'.nebulaAppSetup();';
        echo '</script>';

    }

    //////////////////////////////////////////////////////////////////////
    //serve per scrivere la stringa di inizializzazione della classe JS dell'applicazione
    abstract function initClass();

    //scrive le parti specifiche dell'applicazione
    abstract function customDraw();
}

?>