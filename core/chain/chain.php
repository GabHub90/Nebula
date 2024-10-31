<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_chain.php');

class nebulaChain {

    protected $lista=array();

    protected $app="";

    protected $res=array(
        "ok"=>false,
        "error"=>"",
        "row"=>array()
    );

    protected $galileo;

    function __construct($app,$galileo) {

        $this->app=$app;
        $this->galileo=$galileo;

        $obj=new galileoChain();
        $nebulaDefault['chain']=array("gab500",$obj);

        $galileo->setFunzioniDefault($nebulaDefault);

        if ($app && $app!="") {

            $galileo->executeSelect('chain','CHAIN',"app='".$app."'","");

            if ($galileo->getResult()) {
                $fid=$galileo->preFetch('chain');
                while ($row=$galileo->getFetch('chain',$fid)) {
                    $this->lista[$row['chiave']]=$row;
                }
            }
        }
    }

    static function drawJS() {
        echo '<script type="text/javascript" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/chain/code.js" /></script>';
        echo '<script type="text/javascript">';
            echo 'window._nebulaChain=new nebulaChain();';
        echo '</script>';
    }

    function check($chiave,$utente) {
        //verifica se l'utente è ancora proprietario della risorsa
        if (array_key_exists($chiave,$this->lista)) {
            if ($this->lista[$chiave]['utente']==$utente) return true;
        }

        return false;
    }

    function execute($chiave,$utente) {

        $chiave=''.$chiave;

        if (array_key_exists($chiave,$this->lista)) {

            if ($this->lista[$chiave]['utente']==$utente) {
                $this->res['ok']=true;
                $this->renew($chiave);
            }
            //else {
                $this->res['row']=$this->lista[$chiave];
            //}
        }
        else {
            if(!$this->block($chiave,$utente)) $this->res['errore']='Blocco non riuscito';
            $this->res['ok']=true;
        }

        return $this->res;
    }

    function renew($chiave) {
        $arr=array(
            "dataora"=>date('Ymd:H:i')
        );
        $this->galileo->executeUpdate('chain','CHAIN',$arr,"app='".$this->app."' AND chiave='".$chiave."'");
    }

    function block($chiave,$utente) {
        $arr=array(
            "app"=>$this->app,
            "chiave"=>$chiave,
            "utente"=>$utente,
            "dataora"=>date('Ymd:H:i')
        );

        $this->res['row']=$arr;

        $this->galileo->executeInsert('chain','CHAIN',$arr);
        return ($this->galileo->getResult()); 
    }

    function sblock($chiave) {
        $this->galileo->executeDelete('chain','CHAIN',"app='".$this->app."' AND chiave='".$chiave."'");
    }

    function unchain($chiave,$utente) {
        $this->galileo->executeDelete('chain','CHAIN',"app='".$this->app."' AND chiave='".$chiave."' AND utente='".$utente."'");
    }

    function dechain($utente) {
        $this->galileo->executeDelete('chain','CHAIN',"utente='".$utente."'");
    }

    function force($chiave,$utente) {
        $arr=array(
            "utente"=>$utente,
            "dataora"=>date('Ymd:H:i')
        );
        $this->galileo->executeUpdate('chain','CHAIN',$arr,"app='".$this->app."' AND chiave='".$chiave."'");
    }

    function drawForceButton($txt,$chiave,$utente) {
        echo '<button onclick="window._nebulaChain.force\''.$this->app.'\',\''.$chiave.'\',\''.$utente.'\');">'.$txt.'</button>';
    }

    function drawSblockButton($txt,$chiave) {
        echo '<button onclick="window._nebulaChain.sblock(\''.$this->app.'\',\''.$chiave.'\');">'.$txt.'</button>';
    }

}