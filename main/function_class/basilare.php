<?php
require_once('nebula_id.php');
require_once('ribbon.php');

/*le funzioni comuni definite in CLUSURE sono:

initApp             instanzia ribbon ...

*/

abstract class basilare {

    protected $contesto=array(
        "nebulaContesto"=>array(),
        "nebulaFunzione"=>array(),
        "ribbon"=>array(),
        "args"=>array()
    );

    //è l'ID del form del ribbon
    protected $ribbonID="";

    //viene valorizzata dal costruttore child e viene usata da ribbon (es:iDesk)
    protected $appTag="";

    //viene valorizzata dalla classe child e contiene tutti i suffissi validi per includere INIT
    protected $suffix=array();
    //è il suffiso definito (viene valorizzato da setClass() )
    protected $classe="";

    //array dei campi comuni del ribbon che viene valorizzato da child
    //info - fields - tipi sono array necessari a chekko
    //mappa serve per definire lo stato dei campi (sola lettura - modificabile)
    protected $common=array(
        "fields"=>array(),
        "tipi"=>array(),
        "mappa"=>array(),
        "expo"=>array(),
        "conv"=>array()
    );
    //array dei campi del ribbon (non comuni) che viene valorizzato da child
    protected $uncommon=array(
        "fields"=>array(),
        "tipi"=>array(),
        "mappa"=>array(),
        "expo"=>array(),
        "conv"=>array()
    );

    //codici delle funzioni da importare nella classe in base a SETCLASS
    //definizione di nuovi metodi in maniera dinamica
    protected $methods=array();
    protected $closure=array();

    //closure da caricare in chekko
    protected $cls=array();

    //classi
    protected $id;
    protected $ribbon;

    protected $galileo;

    function __construct($contesto,$galileo) {

        $this->galileo=$galileo;
        
        foreach ($this->contesto as $k=>$o) {
            if ( array_key_exists($k,$contesto) ) {
                $this->contesto[$k]=$contesto[$k];
            }
        }

        $this->id=new nebulaID($contesto['nebulaContesto'],$contesto['nebulaFunzione']);

        //###############################################################
        //È fondamentale che sia così perché questo diventa il FORMTAG ed anche il link alle funzioni JS
        $this->ribbonID=$this->contesto['nebulaFunzione']['nome'];
        //###############################################################
        //$this->ribbonID=$this->contesto['nebulaContesto']['mainApp'].':'.$this->contesto['nebulaFunzione']['nome'];
        //$this->ribbonID=str_replace(':','_',$this->ribbonID);
        
        //################################################################
        //INCLUDE /galassia/common/ <common_SISTEMA.php
        //per valorizzare le variabili COMMON del ribbon
        $gs=explode(':',$this->contesto['nebulaContesto']['mainApp']);
        if ($gs[0]!='home') {
            include(DROOT.'/nebula/galassie/'.$gs[0].'/common/common_'.$gs[1].'.php');
        }
        else include(DROOT.'/nebula/home/common/common_'.$gs[1].'.php');

    }

    public function __call($methodName, array $args) {

        if (isset($this->methods[$methodName])) {
            return call_user_func_array($this->methods[$methodName], $args);
        }
    }

    function loadClass($rif) {

        //se $rif non è contenuto in $suffix ERRORE
        if (!in_array($rif,$this->suffix)) {
            die('ERRORE inizializzazione classe '.$rif);
        }

        //################################################
        include(DROOT.$this->contesto['nebulaFunzione']['loc'].'classi/class_'.$rif.'.php');
        foreach ($this->closure as $key=>$c) {
            $this->methods[$key] = Closure::bind($c, $this, get_class() );
        }
        //################################################

        //initapp è definita come CLOSURE nel file include
        $this->initApp();

        //inizializza ribbonForm
        $this->loadForm();

    }

    function Build() {
        //costruisce il FORM del RIBBON

        //abstract - definisce le funzioni CLOSURE del form (CHEKKO)
        $this->setRibbonFunctions();

        $this->ribbon->preBuild($this->cls);

        //$this->addRibbon('<div>'.json_encode($this->contesto['ribbon']).'</div>');

        //closure
        $this->postApp();
    }

    function addRibbon($txt) {
        $this->ribbon->addRibbon($txt);
    }

    function addBody($txt) {
        $this->ribbon->addBody($txt);
    }

    function addJS($txt) {
        $this->ribbon->addJS($txt);
    }

    function export() {
        return $this->ribbon->export();
    }

    //===============================================================
    
    function loadForm() {
        //carica FIELDS - TIPI - MAPPA - EXPO - CONV in chekko (ribbonForm) attraverso $this->Ribbon
        //però prima deve mettere assieme le varibili common ed uncommon

        /*
        $info=$this->common['info'];
        foreach ($this->uncommon['info'] as $campo=>$c) {
            $info[$campo]=$c;
        }
        */

        $fields=$this->common['fields'];
        foreach ($this->uncommon['fields'] as $campo=>$c) {
            $fields[$campo]=$c;
        }

        $tipi=$this->common['tipi'];
        foreach ($this->uncommon['tipi'] as $campo=>$c) {
            $tipi[$campo]=$c;
        }

        $mappa=$this->common['mappa'];
        foreach ($this->uncommon['mappa'] as $campo=>$c) {
            $mappa[$campo]=$c;
        }

        $expo=$this->common['expo'];
        foreach ($this->uncommon['expo'] as $campo=>$c) {
            $expo[$campo]=$c;
        }

        $conv=$this->common['conv'];
        foreach ($this->uncommon['conv'] as $campo=>$c) {
            $conv[$campo]=$c;
        }

        //eventualmente caricare il valore degli "args RIBBON" passati
        if ( array_key_exists('ribbon',$this->contesto) ) {
            foreach ($mappa as $campo=>$c) {
                if ( array_key_exists($campo,$this->contesto['ribbon']) ) {
                    //valore che viene passato anche a CHEKKO
                    $mappa[$campo]['prop']['default']=$this->contesto['ribbon'][$campo];
                }
            }
        }

        $this->ribbon->loadForm($fields,$tipi,$mappa,$expo,$conv);
    }

    //===============================================================
    //in base alle informazioni ricavate da "ID" decide che file caricare per completare la classe
    abstract function setClass();

    //definisce le funzioni closure per chekko (nebulaCss - nebulaJs - nebulaDraw)
    abstract function setRibbonFunctions();

}

?>