<?php
abstract class appSlaveClass {
    //è una forma ridotta di BASECLASS che carica solo le impostazioni di base

    //gli altri parametri vengono inizializzati dalla classe child
    protected $param=array(
        "nebulaFunzione"=>"",
        "appArgs"=>array()
    );
    
    //classe nebula_ID
    protected $id;
    protected $galileo;

    protected $log=array();

    function __construct($galileo) {
        
        $this->galileo=$galileo;
    }

    //viene chiamata da child dopo aver inizializzato i parametri
    function loadParams($param) {

        foreach ($this->param as $k=>$v) {
            if ( array_key_exists($k,$param['ribbon']) ) {
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

        $this->param['nebulaFunzione']=$param['nebulaFunzione'];

        if (isset($param['contesto'])) {
            $this->id=new nebulaID($param['contesto'],$param['nebulaFunzione']);
        }

    }
}

?>