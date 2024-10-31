<?php
require_once('centret.php');
require_once('centext.php');

abstract class centavosBase {

    /*
    $this->panorama=array(
            "piano"=>1,
            "struttura"=>"1",
            "macroreparto"=>"S",
            "reparto"=>"VWS",
            "descrizione"=>"Incentivazione 2021",
            "data_i"=>"20210701",
            "data_f"=>"20211231",
            "parametri"=>'{"riferimento":"reparto","marchi":{"A","V","N","C","S","X"},"tariffa":53}'
        );

        NOTABENE
        In fase di analisi le date I e F sono quelle di inizio e fine del PERIODO
        viene aggiunto l'indice "periodoAnalisi" = all'ID del periodo
    */

    protected $panorama=array();
    protected $coefficienti=array();

    //è una parte di dati necessari per l'oggetto c2r ma può essere adattato agli altri oggetti
    protected $config=array(
        "inizio"=>"",
        "fine"=>""
    );

    protected $oggetti=array(
        "interni"=>array(),
        "esterni"=>array()
    );

    //sono gli oggetti che forniscono i dati
    protected $sorgenti=array();

    protected $flagSimula=false;
    protected $sorgentiSimula=array();
    protected $sorgentiActual=array();

    protected $rettifiche=false;

    protected $galileo;

    protected $log=array();

    function __construct($panorama,$galileo) {
        
        $this->panorama=$panorama;
        $this->galileo=$galileo;

        $this->coefficienti=array(
            "_pres_"=>array(
                "titolo"=>"presenza",
                "icon"=>"presenza.png",
                "stato"=>true,
                "sorgente"=>"centcoef",
                "default"=>100,
                "classe"=>'individuale'
            ),
            "_redd_"=>array(
                "titolo"=>"redditività",
                "icon"=>"redditivita.png",
                "stato"=>true,
                "sorgente"=>"centcoef",
                "default"=>100,
                "classe"=>'team'
            ),
            "_rett_"=>array(
                "titolo"=>"rettifica",
                "icon"=>"general.png",
                "stato"=>true,
                "sorgente"=>"centcoef",
                "default"=>100,
                "classe"=>'individuale'
            )
        );

        $this->sorgenti=array(
            "centext"=>false
        );

        //do per scontato che non può essere creato un periodo di analisi con data nel futuro
        //l'array viene inizializzato per l'oggetto c2r
        $this->config['inizio']=$panorama['data_i'];
        $this->config['fine']=(date('Ymd')>$panorama['data_f'])?$panorama['data_f']:date('Ymd');

    }

    function getLog() {
        return $this->log;
    }

    function setSorgente($parametro,$contesto,$rettifica) {

        ////////////////////////////////////////////////////////////////////
        if ($contesto=='analisi' && !$this->rettifiche) {
            //instanzia la classe RETTIFICHE

            $a=array(
                "piano"=>$this->panorama['ID'],
                "periodo"=>$this->panorama['periodoAnalisi']
            );

            $this->rettifiche=new centret($a,$this->galileo);
        }
        ////////////////////////////////////////////////////////////////////
        
        if (isset($this->oggetti['interni'][$parametro])) {

            $this->oggetti['interni'][$parametro]['flag']=true;
            if ($rettifica==1) {
                $this->oggetti['interni'][$parametro]['rettifica']=true;
            }

            if ($contesto=='analisi') {

                if (isset($this->sorgenti[$this->oggetti['interni'][$parametro]['sorgente']])) {

                    if (!$this->sorgenti[$this->oggetti['interni'][$parametro]['sorgente']]) {

                        if ($this->oggetti['interni'][$parametro]['sorgente']=='centext') {
                            $this->sorgenti['centext']=new centext($this->config,$this->galileo);
                        }
                        else {
                            $this->initSorgente($this->oggetti['interni'][$parametro]['sorgente']);
                        }
                    }
                }
            }
        }

        elseif (isset($this->oggetti['esterni'][$parametro])) {

            $this->oggetti['esterni'][$parametro]['flag']=true;

            if ($rettifica==1) {
                $this->oggetti['esterni'][$parametro]['rettifica']=true;
            }

            if ($contesto=='analisi') {

                if (isset($this->sorgenti[$this->oggetti['esterni'][$parametro]['sorgente']])) {

                    if (!$this->sorgenti[$this->oggetti['esterni'][$parametro]['sorgente']]) {

                        if ($this->oggetti['esterni'][$parametro]['sorgente']=='centext') {
                            $this->sorgenti['centext']=new centext($this->config,$this->galileo);
                        }
                        else {
                            $this->initSorgente($this->oggetti['esterni'][$parametro]['sorgente']);
                        }
                    }
                }
            }
        }
    }

    function setCoeff($arr) {

        //viene chiamato da "drawStructSection" in fase STRUTTURA

        foreach ($this->coefficienti as $kc=>$c) {
            if (array_key_exists($kc,$arr)) {
                $this->coefficienti[$kc]['stato']=$arr[$kc];
            }
        }

    }

    function getCoeff() {
        return $this->coefficienti;
    }

    function getSourceInt() {
        $ret=array();

        foreach ($this->oggetti['interni'] as $k=>$o) {
            if ($o['data_i']<=$this->panorama['data_f'] && $o['data_f']>=$this->panorama['data_i']) {
                $ret[$k]=$o;
            }
        }

        return $ret;
    }

    function getSourceExt() {
        $ret=array();

        foreach ($this->oggetti['esterni'] as $k=>$o) {
            if ($o['data_i']<=$this->panorama['data_f'] && $o['data_f']>=$this->panorama['data_i']) {
                $ret[$k]=$o;
            }
        }

        return $ret;
    }

    function setSimula($sorgenti) {

        foreach ($sorgenti as $ks=>$s) {
            //se la sorgente esiste
            if (array_key_exists($ks,$this->oggetti['interni']) || array_key_exists($ks,$this->oggetti['esterni']) ) {
                $this->sorgentiSimula[$ks]['team']=$s;
                $this->sorgentiSimula[$ks]['individuale']=$s;
            }
        }

        $this->flagSimula=true;
    }

    function setActual($arg) {

        //in base agli oggetti il cui flag è impostato a TRUE
        //alimentare l'array $sorgentiActual in base in base ai paramemtri $arg passati (per esempio il collaboratore)

        $this->sorgentiActual=array();

        foreach ($this->oggetti['interni'] as $k=>$v) {

            if ($v['flag']) {
                $temp=$this->getSorgente($k,$arg);

                if ($temp) $this->sorgentiActual[$k]=$temp;


                else $this->sorgentiActual[$k]=array(
                    "team"=>$v['default'],
                    "individuale"=>$v['default']
                );

                $this->sorgentiActual[$k]['rettifica']=array(
                    "team"=>false,
                    "individuale"=>false
                );
        
                //############################
                //se il parametro è rettificabile verifica se esiste la rettifica
                if ($this->oggetti['interni'][$k]['rettifica']) {
                    $this->sorgentiActual[$k]['rettifica']['team']=$this->rettifiche->getValore($k,'team');
                    $this->sorgentiActual[$k]['rettifica']['individuale']=$this->rettifiche->getValore($k,$arg['ID_coll']);
                }
                //############################
            }
        }

        foreach ($this->oggetti['esterni'] as $k=>$v) {

            if ($v['flag']) {
                $temp=$this->getSorgente($k,$arg);

                if ($temp) $this->sorgentiActual[$k]=$temp;
                else $this->sorgentiActual[$k]=array(
                    "team"=>$v['default'],
                    "individuale"=>$v['default']
                );

                $this->sorgentiActual[$k]['rettifica']=array(
                    "team"=>false,
                    "individuale"=>false
                );
        
                //############################
                //se il parametro è rettificabile verifica se esiste la rettifica
                if ($this->oggetti['esterni'][$k]['rettifica']) {
                    $this->sorgentiActual[$k]['rettifica']['team']=$this->rettifiche->getValore($k,'team');
                    $this->sorgentiActual[$k]['rettifica']['individuale']=$this->rettifiche->getValore($k,$arg['ID_coll']);
                }
                //############################
            }
        }

        //####################################
        //valorizzazione COEFFICIENTI
        foreach ($this->coefficienti as $k=>$v) {

            if ($v['stato']) {
                $temp=$this->getSorgente($k,$arg);

                if ($temp) $this->sorgentiActual['coefficienti'][$k]=$temp;
                else $this->sorgentiActual['coefficienti'][$k]=array(
                    "team"=>$v['default'],
                    "individuale"=>$v['default']
                );
            }
        }
        //####################################

        $this->flagSimula=false;

    }

    function getActual() {
        return $this->sorgentiActual;
    }

    function getCoefVal($k) {

        $a=array(
            "valore"=>0,
            "classe"=>""
        );

        if (isset($this->sorgentiActual['coefficienti'][$k])) {

            $a['valore']=(is_array($this->sorgentiActual['coefficienti'][$k][$this->coefficienti[$k]['classe']]))?$this->sorgentiActual['coefficienti'][$k][$this->coefficienti[$k]['classe']]['valore']:$this->sorgentiActual['coefficienti'][$k][$this->coefficienti[$k]['classe']];
            $a['classe']=$this->coefficienti[$k]['classe'];
        }
        
        return $a;
    }

    function getValore($sorgente,$classe) {

        if ($this->flagSimula) {
            if (array_key_exists($sorgente,$this->sorgentiSimula)) {
                if (array_key_exists($classe,$this->sorgentiSimula[$sorgente])) {
                    if($this->sorgentiSimula[$sorgente][$classe]!="") {
                        return $this->sorgentiSimula[$sorgente][$classe];
                    }
                }
            }
            
            return 0;
        }

        else {

            $ret=0;

            if (array_key_exists($sorgente,$this->sorgentiActual)) {
                if (array_key_exists($classe,$this->sorgentiActual[$sorgente])) {
                    //if ($this->sorgentiActual[$sorgente][$classe]!="") {

                        $ret=$this->sorgentiActual[$sorgente][$classe];

                        //verifica RETTIFICA
                        if ($this->sorgentiActual[$sorgente]['rettifica'][$classe]) {
                            $ret=$this->sorgentiActual[$sorgente]['rettifica'][$classe]['valore'];
                        }

                        //if ($sorgente=='QC_1') $ret='100';
                    //}
                }
            }

            //if ($sorgente=='QC_1') $ret='100';
            return $ret;
        }

        /*    if (isset($this->sorgentiActual[$sorgente][$classe]) && $this->sorgentiActual[$sorgente][$classe]!="") {

                $ret=$this->sorgentiActual[$sorgente][$classe];

                //verifica RETTIFICA
                if ($this->sorgentiActual[$sorgente]['rettifica'][$classe]) {
                    $ret=$this->sorgentiActual[$sorgente]['rettifica'][$classe]['valore'];
                }

                return $ret;
            }
            else return 0;
        }*/

    }

    function getSorgente($oggetto,$arg) {

        if (isset($this->oggetti['interni'][$oggetto])) {

            $f='getSorgente_'.$this->oggetti['interni'][$oggetto]['sorgente'];

            return $this->$f($oggetto,$arg);
        }

        elseif (isset($this->oggetti['esterni'][$oggetto])) {

            $f='getSorgente_'.$this->oggetti['esterni'][$oggetto]['sorgente'];

            return $this->$f($oggetto,$arg);
        }

        elseif (isset($this->coefficienti[$oggetto])) {

            $f='getSorgente_'.$this->coefficienti[$oggetto]['sorgente'];

            return $this->$f($oggetto,$arg);
        }

        else return false;
    }

    function getSorgente_centext($oggetto,$arg) {

        $res=array(
            "team"=>$this->sorgenti['centext']->getResult($oggetto,'team',$this->oggetti['esterni'][$oggetto]['operazione']),
            "individuale"=>$this->sorgenti['centext']->getResult($oggetto,$arg['ID_coll'],$this->oggetti['esterni'][$oggetto]['operazione']),
        );

        if (!$res['team']) $res['team']=$this->oggetti['esterni'][$oggetto]['default'];
        if (!$res['individuale']) $res['individuale']=$this->oggetti['esterni'][$oggetto]['default'];

        return $res;
    }

    function getSorgente_centcoef($coeff,$arg) {
        //usato SOLO per i COEFFICIENTI che di fatto hanno gli stessi campi delle rettifiche

        $res=array(
            "team"=>$this->rettifiche->getValore($coeff,'team'),
            "individuale"=>$this->rettifiche->getValore($coeff,$arg['ID_coll'])
        );

        if (!$res['team']) $res['team']=array("valore"=>$this->coefficienti[$coeff]['default']);
        if (!$res['individuale']) $res['individuale']=array("valore"=>$this->coefficienti[$coeff]['default']);

        /*$res=array(
            "team"=>50,
            "individuale"=>50
        );*/

        return $res;
    }

    function drawDatiExt($inizio,$fine) {

        $divo=new Divo('centavos_ext','7%','93%',0);
        $divo->setBk('#fdbea7');

        ob_start();

            echo '<div style="width:100%;height:92%;overflow:scroll;overflow-x:hidden;">';

                foreach ($this->oggetti['esterni'] as $k=>$o) {

                    //è possibile gestire solo i dati che si inseriscono attraverso la classe "centext" e che appatengono al panorama selezionato
                    if (!$o['flag'] || $o['sorgente']!="centext") continue;

                    //if ($o['data_i']<=$this->config['fine'] && $o['data_f']>=$this->config['inizio']) {
                    if ($o['data_i']<=$fine && $o['data_f']>=$inizio) {

                        //echo '<div id="ctv_extButton_'.$k.'" style="width:90%;border:1px solid black;padding:2px;box-sizing:border-box;margin-top:8px;margin-bottom:8px;text-align:center;font-weight:bold;font-size:1.2em;cursor:pointer;" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].editExt(\''.$k.'\',\''.$o['titolo'].'\',\''.$o['data_i'].'\',\''.$o['data_f'].'\');" >';
                        echo '<div id="ctv_extButton_'.$k.'" style="width:90%;border:1px solid black;padding:2px;box-sizing:border-box;margin-top:8px;margin-bottom:8px;text-align:center;font-weight:bold;font-size:1.2em;cursor:pointer;" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].editExt(\''.$k.'\',\''.$o['titolo'].'\',\''.$inizio.'\',\''.$fine.'\');" >';
                            echo '<div>'.$o['titolo'].'</div>';
                            echo '<div style="font-weight:normal;" >('.$o['operazione'].')</div>';
                        echo '</div>';

                    }
                }
            
            echo '</div>';

        $divo->add_div('Esterni','black',0,0,ob_get_clean(),0,array());

        /////////////////////////////////////////////////////////////////////

        ob_start();

            echo '<div style="width:100%;height:92%;overflow:scroll;overflow-x:hidden;">';

                foreach ($this->oggetti['interni'] as $k=>$o) {

                    if (!$o['flag'] || !$o['rettifica']) continue;

                    if ($o['data_i']<=$this->config['fine'] && $o['data_f']>=$this->config['inizio']) {

                        echo '<div id="ctv_retButton_'.$k.'" style="width:90%;border:1px solid black;padding:2px;box-sizing:border-box;margin-top:8px;margin-bottom:8px;text-align:center;font-weight:bold;font-size:1.2em;cursor:pointer;" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].editRet(\''.$k.'\',\''.$o['titolo'].'\');" >';
                            echo '<div>'.$o['titolo'].'</div>';
                        echo '</div>';

                    }
                }

                foreach ($this->oggetti['esterni'] as $k=>$o) {

                    if (!$o['flag'] || !$o['rettifica']) continue;

                    if ($o['data_i']<=$this->config['fine'] && $o['data_f']>=$this->config['inizio']) {

                        echo '<div id="ctv_retButton_'.$k.'" style="width:90%;border:1px solid black;padding:2px;box-sizing:border-box;margin-top:8px;margin-bottom:8px;text-align:center;font-weight:bold;font-size:1.2em;cursor:pointer;" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].editRet(\''.$k.'\',\''.$o['titolo'].'\');" >';
                            echo '<div>'.$o['titolo'].'</div>';
                        echo '</div>';

                    }
                }

            echo '</div>';

        $divo->add_div('Rettifiche','black',0,0,ob_get_clean(),0,array());

        /////////////////////////////////////////////////////////////////////

        ob_start();
            
            //RETTIFICHE e COEFFICIENTI fanno riferimento allo stesso DATABASE

            echo '<div style="width:100%;height:92%;overflow:scroll;overflow-x:hidden;">';

                foreach ($this->coefficienti as $k=>$o) {

                    if (!$o['stato'] || $o['sorgente']!='centcoef') continue;

                    echo '<div id="ctv_retButton_'.$k.'" style="width:90%;border:1px solid black;padding:2px;box-sizing:border-box;margin-top:8px;margin-bottom:8px;text-align:center;font-weight:bold;font-size:1.2em;cursor:pointer;" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].editRet(\''.$k.'\',\''.$o['titolo'].'\');" >';
                        echo '<div>'.$o['titolo'].'</div>';
                    echo '</div>';
                }

            echo '</div>';

        $divo->add_div('Coeff','black',0,0,ob_get_clean(),0,array());


        $divo->build();

        $divo->draw();
    }

    abstract function initSorgente($tipo);

}

?>