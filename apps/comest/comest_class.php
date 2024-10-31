<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/apps/comest/classi/tt.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/apps/comest/classi/comest.php');

class comestApp extends appBaseClass {

    protected $utente;

    protected $commessa;

    function __construct($param,$galileo) {
        
        parent::__construct($galileo);

        $this->loc='/nebula/apps/comest/';

        $this->param['comest_commessa']="";
        $this->param['comest_tt']="";
        $this->param['comest_odltt']="";
        $this->param['comest_dmstt']="";
        $this->param['comest_telaio']="";
        $this->param['comest_targa']="";
        $this->param['comest_desc']="";
        $this->param['comest_dms']="";
        $this->param['comest_odl']="";
       
        $this->loadParams($param);

        $this->utente=$this->id->getCollID();

        $this->commessa=new nebulaComest($galileo);
    }

    function initClass() {
        return ' comestCode(\''.$this->param['nebulaFunzione']['nome'].'\');';
    }

    function customDraw() {

        if(!is_a($this->commessa,'nebulaComest')) echo 'Commessa non instanziata';

        //################################################
        /*TEST forza una commessa già inizializzata
            $test=array(
                "rif"=>1,
                "versione"=>1,
                "targa"=>"GE567PD",
                "telaio"=>"WVWZZZCDZMW352615",
                "descrizione"=>"GOLF 8 1.4 E-HYBRID DSG 204CV MY 21",
                "dms"=>"infinity",
                "odl"=>"0",
                "fornitore"=>array(
                    "ID"=>"1",
                    "ragsoc"=>"Augusto Gabellini Srl",
                    "indirizzo"=>"Str. Romagna, 121 Pesaro 61121 (PU)",
                    "mail"=>"michele.binda@gabellini.it",
                    "tel1"=>"0721270364",
                    "nota1"=>"Responsabile",
                    "tel2"=>"0721279325",
                    "nota2"=>"Centralino"
                ),
                "d_apertura"=>"",
                "utente_apertura"=>""
            );
        
            $this->commessa->init($test);

            $this->drawCommessa();

            return;*/
        //#################################################

        //se è specificata una commessa carica la commessa
        if ($this->param['comest_commessa']!="") {

            $this->commessa->init(array("rif"=>$this->param['comest_commessa']));

            $this->drawCommessa();
        }

        //altrimenti cerca la vettura o l'odl
        else if ($this->param['comest_telaio']=="" && $this->param['comest_targa']=="") $this->drawLista();

        else {

            $temp=array(
                'targa'=>$this->param['comest_targa'],
                'telaio'=>$this->param['comest_telaio'],
                'descrizione'=>$this->param['comest_desc'],
                'dms'=>$this->param['comest_dms'],
                'odl'=>$this->param['comest_odl']
            );

            $this->commessa->init($temp);

            $this->drawCommessa();
        }

    }

    function drawCommessa() {

        echo '<div style="position:relative;width:100%;height:5%;text-align:left;" >';
            echo '<img style="position:relative;width:25px;cursor:pointer" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/comest/img/chiudi.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].closeCommessa();" />';
        echo '</div>';

        $this->drawCommessaMain();
    }

    function drawCommessaMain() {

        $this->commessa->drawJS();

        echo '<div id="nebula_comest_main" style="position:relative;width:100%;height:95%;text-align:left;" >';
            $this->commessa->draw();
        echo '</div>';
    }

    function drawLista() {

        if ( ($this->param['comest_tt']=="" && $this->param['comest_odltt']=="") || $this->param['comest_dmstt']=="") {
            echo 'Parametri di ricerca sbagliati!!!';
            return;
        }

        //###################################################
        //ricerca e stampa commesse aperte che matchano la ricerca
        //l'iniziazione di GALILEO avviene nelle classe COMEST
        //###################################################

        ///////////////////////////////////////////

        $tt=new comestTT($this->param['comest_dmstt'],$this->galileo);
        
        echo '<div style="position:relative;width:100%;height:100%;overflow:scroll;overflow-x:hidden;">';

            $tt->draw($this->param['comest_tt'],$this->param['comest_odltt']);

        echo '</div>';

    }

}
?>