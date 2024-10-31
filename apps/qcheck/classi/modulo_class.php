<?php
/*
il modulo linka il controllo al FORM

MODULO
"ID"                    id del modulo
"titolo"                titolo che viene visualizzato
"varianti"              JSON delle varianti del modulo 
                        Esempio:    Controllo       Test di officina
                                    Versione        Controllo del processo di riparazione
                                    Variazione      Tagliando

"auth"                  determina le autorizzazioni sul modulo
                        Esempio: "auth"=>'{"RS":"*","RT":"1","RC":"1","ASS":"1"}',
                        "0": niente , "1":visione , "2":visione ed esecuzione

"tag"                   tag del FORM e nome del file di configurazione di CHEKKO
*/

require_once("form_class.php");

class qcModulo {

    protected $nebulaFunzione=array();

    protected $info=array();

    //array delle varianti dedotto da $info
    protected $varianti=array(); 
    //array delle autorizzazioni dedotto da $info
    protected $auth=array();

    //classe che "qcForm" che estende CHEKKO
    protected $form;
    protected $variante="";

    protected $galileo;

    protected $log=array();

    function __construct($id,$nebulaFunzione,$galileo) {

        $this->nebulaFunzione=$nebulaFunzione;
        $this->galileo=$galileo;

        //caricare il modulo $id da QCHECK_moduli
        //il TAG viene aggiunto in FETCH (non c'è nel DB)
        //RISPOSTE è un array VUOTO che viene aggiunto (non è nel DB) e viene valorizzato da LOADFORM

        $wClause="ID='".$id."'";

        //executeSelect($tipo,$tabella,$wclause,$order) {
        $this->galileo->executeSelect("qcheck","QCHECK_moduli",$wClause,"");
        $result=$this->galileo->getResult();
        //GAB500
        if ($result) {
            $fetID=$this->galileo->preFetch('qcheck');
            while ($row=$this->galileo->getFetch('qcheck',$fetID)) {
                $this->info=$row;
                $this->info['tag']="modulo".$id;
                $this->info['risposte']=array();
                $this->info['stato']="";
            }
        }

        /*TEST
        if ($id=="1") {
            $this->info=array(
                "ID"=>$id,
                "titolo"=>"tecnico",
                "varianti"=>'{"1":{"tag":"manutenzione"},"2":{"tag":"riparazione"},"3":{"tag":"interno"}}',
                "auth"=>'{"RS":"2","RT":"2","RC":"2","ASS":"2"}',
                "tag"=>"modulo".$id,
                "risposte"=>array(),
                "stato"=>"aperto"
            );
        }
        if ($id=="2") {
            $this->info=array(
                "ID"=>$id,
                "titolo"=>"R-tecnico",
                "varianti"=>'{"1":{"tag":"standard"}}',
                "auth"=>'{"RS":"2","RT":"1","RC":"1","ASS":"1"}',
                "tag"=>"modulo".$id,
                "risposte"=>array(),
                "stato"=>"aperto"
            );
        }
        if ($id=="3") {
            $this->info=array(
                "ID"=>$id,
                "titolo"=>"R-clienti",
                "varianti"=>'{"1":{"tag":"standard"}}',
                "auth"=>'{"RS":"2","RT":"1","RC":"1","ASS":"1"}',
                "tag"=>"modulo".$id,
                "risposte"=>array(),
                "stato"=>"aperto"
            );
        }
        //FINE TEST
        */

        if (isset($this->info['auth']) && $this->info['auth']!="") {
            $this->auth=json_decode($this->info['auth'],true);
        }
        else $this->auth=array();

        if (isset($this->info['varianti']) && $this->info['varianti']!="") {
            $this->varianti=json_decode($this->info['varianti'],true);
        }
        else $this->varianti=array();

    }

    function getVarianti() {
        return $this->varianti;
    }

    function getTitle() {
        return $this->info['titolo'];
    }

    function getFormTag() {
        return $this->info['tag'];
    }

    function getFormLog() {
        return $this->form->getLog();
    }

    function loadForm($chain,$risposte,$stato) {
        //echo $variante;
        $this->variante=$chain['variante'];

        /////////////
        if($r=json_decode($risposte,true)) {

            $r['IDcontrollo']=$chain['IDcontrollo'];
            $r['modulo']=$chain['modulo'];
        }
        else $r=array(
            "IDcontrollo"=>$chain['IDcontrollo'],
            "modulo"=>$chain['modulo']
        );

        $this->info['stato']=$stato;

        $this->form=new qcForm($this->info,$chain['variante'],$r);
    }

    function checkAuth($collGruppo) {

        $ret="0";
        if ( array_key_exists($collGruppo, $this->auth) ) {
            $ret=$this->auth[$collGruppo];
        }

        return $ret;
    }

    function drawModulo($IDabbinamento,$ID,$a,$collGruppo) {

        $ret=array(
            "intestazione"=>mainFunc::gab_todata($a['d_controllo']).' - ('.$a['chiave'].') - '.substr($a['intestazione'],0,20),
            "txt"=>""
        );

        $txt='<div style="" >';

            $txt.='<div style="display:inline-block;width:70%;vertical-align:top;color:sienna;font-weight:bold;" >';
                //$txt.=$a['des_modulo'].' - '.$a['des_variante'];
                //$txt.=json_encode($this->varianti);
                $txt.=$this->info['titolo'].' - '.$this->varianti[$a['variante']]['tag'];
            $txt.='</div>';

            $txt.='<div style="display:inline-block;width:20%;vertical-align:top;text-align:right;font-weight:bold;" >';
                if (count($a['score'])>0) {
                    if ($a['score']['domande']!=0) {
                        $completezza=round( ($a['score']['risposte']/$a['score']['domande'])*100 ).'%';
                    }
                    else {
                        $completezza="0%";
                    }

                    $txt.=$a['score']['punteggio'].'<span style="font-size:smaller;"> ( '.$completezza.' )</span>';
                }
            $txt.='</div>';

        $txt.='</div>';

        $txt.='<div style="" >';

            $txt.='<div style="display:inline-block;width:70%;vertical-align:top;" >';
                if ($a['esecutore']=='') $txt.='Da Eseguire';
                else $txt.=$a['esecutore'].' - ('.( mainFunc::gab_todata(substr($a['d_modulo'],0,8) ).' '.substr($a['d_modulo'],9,5)).') - '.$a['stato_modulo'];
            $txt.='</div>';

            $txt.='<div style="display:inline-block;width:20%;vertical-align:top;text-align:right;" >';
                //se operatore è un riferimento all'esecutore di un altro modulo
                if (substr($a['operatore'],0,1)=='#') {
                    $txt.=($a['des_rif_operatore']!="")?$a['des_rif_operatore']:$a['operatore'];
                }
                else {
                    $txt.=$a['operatore'];
                }
            $txt.='</div>';

        $txt.='</div>';

        $auth=$this->checkAuth($collGruppo);

        $txt.='<div style="position:absolute;top:0px;right:0px;width:10%;height:100%;text-align:center;">';

            if ($auth!="0") {

                $form=$ID.':'.$a['versione'].':'.$a['modulo'].':'.$a['variante'].':'.$IDabbinamento;

                if ( ($a['stato_modulo']=='salvato' || $a['stato_modulo']=='aperto') && $auth=='2' ) {

                        //se operatore è univocamente definito (NON #)
                        if ( ($a['operatore']!="" && substr($a['operatore'],0,1)!='#') || ($a['des_rif_operatore']!="" && substr($a['operatore'],0,1)=='#') ) {
                            $txt.='<img style="width:25px;height:25px;top:50%;margin-top:-12.5px;position:relative;cursor:pointer;" src="http://'.SADDR.'/nebula/apps/qcheck/img/edit.png" onclick="window._nebulaApp_'.$this->nebulaFunzione['nome'].'.openForm(\''.$form.'\')" />';
                        }
                }
                elseif ($a['stato_modulo']=='chiuso') {
                    $txt.='<img style="width:25px;height:25px;top:50%;margin-top:-12.5px;position:relative;cursor:pointer;" src="http://'.SADDR.'/nebula/apps/qcheck/img/view.png" onclick="window._nebulaApp_'.$this->nebulaFunzione['nome'].'.openForm(\''.$form.'\')" />';
                }
            }
        $txt.='</div>';

        $ret['txt']=$txt;

        return $ret;
    }

    function drawForm() {
        //echo json_encode($this->info);
        $this->form->draw();
    }

}

?>