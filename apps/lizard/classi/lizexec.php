<?php
include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/apps/avalon/classi/wormhole.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/core/odl/odl_func.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/core/magazzino/mag_func.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/apps/c2r/classi/wormhole.php');

include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_carb.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_gdm.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_fidel.php');

include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/infinity/infinity_ricambi.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/concerto/concerto_ricambi.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/infinity/infinity_anagrafiche.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/infinity/infinity_veicoli.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/infinity/infinity_odl.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/concerto/concerto_odl.php');

require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/core/excalibur/excalibur.php');

class lizExec {

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

    protected $param=array();

    //oggetto EXCALIBUR instanziato da BUILD
    protected $lista;

    protected $methods=array();
    protected $closure=array();

    protected $galileo;

    function __construct($param,$galileo) {

        $this->galileo=$galileo;

        if (!isset($param['liz_exec'])) die ('parametro funzione non impostato!!!');
        if (!array_key_exists($param['liz_exec'],$this->lizardFunzioni)) die ('Funzione inesistente '.$param['liz_exec']);

        $this->param=$param;

        include(DROOT.'/nebula/apps/lizard/core/funzioni/'.$param['liz_exec'].'_exec.php');

        foreach ($this->closure as $key=>$c) {
            $this->methods[$key] = Closure::bind($c, $this, get_class() );
        }
    }

    public function __call($methodName, array $args) {

        if (isset($this->methods[$methodName])) {
            return call_user_func_array($this->methods[$methodName], $args);
        }
    }

    function draw() {

        excalibur::init();

        //if (is_a($this->lista,'excalibur')) {
            $this->lista->draw();
        //}

        //echo '<div>'.json_encode($this->galileo->getLog('query')).'</div>';
        
        //se non esiste non succede nulla
        $this->excaliburFunc();
    }

    function check() {

        $txt="";

        //metodo definito in CLOSURE
        $txt=$this->checkParam();

        if ($txt!="") die ($txt);
    }

    function build() {

        //metodo definito in CLOSURE
        $this->buildDatas();
    }

    function getLista() {
        return $this->lista->getElementi();
    }


}

?>