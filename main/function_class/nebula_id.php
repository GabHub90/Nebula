<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/core/chain/chain.php');

class nebulaID {

    protected $mainLogged="";
    protected $mainApp="";
    protected $nebulaFunzione=array();

    protected $collID;
    protected $nome;
    protected $cognome;
    protected $mail="";

    protected $generale=array();

    // reparto - gruppo
    //tutte le combinazioni configurate per l'utente
    protected $gruppi=array();
    //tutti gli ID gruppo NON suddivisi per reparto
    protected $IDgruppi=array();

    // reparto - macrogruppo
    //tutte le combinazioni configurate per l'utente
    protected $macrogruppi=array();

    //macroreparto - array del reparto
    protected $reparti=array();

    //copia di "apps" GALASSIA:SISTEMA
    protected $apps=array();

    //copia di "configFunzioni"
    protected $funzioni=array();

    function __construct($contesto,$funzione) {

        /*
        $contesto {
            nebulaContesto: {
                "mainLogged":"m.cecconi",
                "mainApp":"isla:home",
                "configUtente":{
                    "generale":{"TDD":{"TDD":{"ID_coll":"1","nome":"Matteo","cognome":"Cecconi","concerto":"m.cecconi","reparto":"TDD","des_reparto":"Team di Direzione","macroreparto":"D","des_macroreparto":"Direzione","ID_gruppo":"32","gruppo":"TDD","des_gruppo":"Direttivo","pos_gruppo":"1","macrogruppo":"","des_macrogruppo":"","pos_macrogruppo":"0"}}},
                    "apps":{"home":{"home":"Overview","login":"Login"},"isla":{"home":"iDesk"},"mytech":{"home":"Officina"},"ammo":{"home":"Office"},"sthor":{"home":"Magazzino"},"vendor":{"home":"Workplace"},"maestro":{"home":"Tempo","gruppi":"Gruppi","diagnosi":"Diagnosi"}
                },
                //ESEPIO MAESTRO:ANALISI
                "configFunzioni": {
                    "home":{"chk":true},
                    "phone":{"chk":true}
                }
            },
            "nebulaFunzione":{"nome":"home","loc": "/nebula/funzioni/idesk/"},
            "args":[]
        }
        */

        //#########################################################
        //utilizzando configUtente la classe serve per avere uno strumento univoco che la classe della funzione 
        //può chiamare per determiare come debba essere visualizzata la funzione (quale classe includere)
        
        //elaborare $contesto per valorizzare le proprietà della classe

        $this->mainLogged=$contesto['mainLogged'];
        $this->mainApp=$contesto['mainApp'];
        $this->apps=$contesto['configUtente']['apps'];
        $this->funzioni=$contesto['configFunzioni'];
        $this->nebulaFunzione=$funzione;

        $this->generale=$contesto['configUtente']['generale'];

        $this->mail=$contesto['mail'];

        foreach ($contesto['configUtente']['generale'] as $reparto=>$r) {

            foreach ($r as $gruppo=>$g) {

                //è lo stesso in tutti i record
                $this->collID=$g['ID_coll'];
                $this->nome=$g['nome'];
                $this->cognome=$g['cognome'];

                //$this->gruppi[$g['macroreparto']][$reparto][]=$gruppo;
                $this->gruppi[$reparto]=array(
                    "tag"=>$gruppo,
                    "ID"=>$g['ID_gruppo']
                );

                $this->IDgruppi[]=$g['ID_gruppo'];

                $this->reparti[$g['macroreparto']][$reparto]=$g;

                if ( !array_key_exists($reparto,$this->macrogruppi) ) {
                    $this->macrogruppi[$reparto]=array();
                }

                if ($g['macrogruppo']!="") {

                    if ( !in_array($g['macrogruppo'],$this->macrogruppi[$reparto]) ) {
                        $this->macrogruppi[$reparto][]=$g['macrogruppo'];
                    }
                }

            }
        }
    }

    function deChain($galileo) {
        //cancella tutte le risorse bloccate dall'utente
        $chain=new nebulaChain('',$galileo);
        $chain->dechain($this->mainLogged);
        unset($chain);
    }

    function addFunzione($func,$chk) {
        //aggiunge all'elenco delle funzioni le opzioni richieste dalle applicazioni
        $this->funzioni[$func]=array("chk"=>$chk);
    }

    function getMainApp() {
        return $this->mainApp;
    }

    function getGalassia() {
        $gs=explode(":",$this->mainApp);
        return $gs[0];
    }

    function getCollID() {
        return $this->collID;
    }

    function getLogged() {
        return $this->mainLogged;
    }

    function getLoggedMail() {
        return $this->mail;
    }

    function getLoggedReparti() {
        $ret=array();

        foreach ($this->generale as $k=>$g) {
            $ret[$k]=$g;
        }

        return $ret;
    }

    function getReparto($macrorep) {
        //restituisce i reparti di appartenenza in un dato macroreparto
        $ret=array();

        //l'utente potrebbe non avere un ruolo nel macroreparto tipo il responsabile IT
        if (array_key_exists($macrorep,$this->reparti) ) {

            /*foreach ($this->reparti[$macrorep] as $reparto=>$r) {
                $ret[$reparto]=$r;
            }*/
            $ret=$this->reparti[$macrorep];
        }

        //{"ID_coll":"1","nome":"Matteo","cognome":"Cecconi","concerto":"m.cecconi","reparto":"TDD","des_reparto":"Team di Direzione","macroreparto":"D","des_macroreparto":"Direzione","ID_gruppo":"32","gruppo":"TDD","des_gruppo":"Direttivo","pos_gruppo":"1","macrogruppo":"","des_macrogruppo":"","pos_macrogruppo":"0"}
        return $ret;
    }

    function getGalassiaMacro() {
        //in base alla galassia ritorna i macroreparti interessati all'utente
        
        $galassia=$this->getGalassia();
        $macro=array();

        switch ($galassia) {
            case "mytech":
                $macro=array('S');
                break;
            case "home":
                $macro=array('A','D','M','S','V','X');
                break;
        }

        return $macro;
    }

    function getGalassiaRepTags() {
        //ritorna l'array dei TAG dei reparti a cui è collegato l'utente in base alla galassia in cui è
        $reptag=array();

        //getGalassiaMacro fornisce un array di macroreparti collegati alla galassia in cui ci si trova
        foreach ($this->getGalassiaMacro() as $k=>$m) {
            //$m è un array di macroreparti
            $reps=$this->getReparto($m);
            foreach ($reps as $reparto=>$r) {
                $reptag[]=$reparto;
            }
        }

        return $reptag;
    }

    function getGalassiaRepLines() {
        //ritorna l'array dei TAG dei reparti a cui è collegato l'utente in base alla galassia in cui è
        $repLines=array();

        //getGalassiaMacro fornisce un array di macroreparti collegati alla galassia in cui ci si trova
        foreach ($this->getGalassiaMacro() as $k=>$m) {
            //$m è un array di macroreparti
            $reps=$this->getReparto($m);
            foreach ($reps as $reparto=>$r) {
                $repLines[$reparto]=$r;
            }
        }

        return $repLines;
    }

    function getGruppo($reparto,$override) {
            //restituisce il gruppo all'interno di uno specifico reparto
            //se il valore è "" ed $override è diverso da array()
            //allora restituisce il PRIMO gruppo che trova all'interno dei MACROREPARTI inseriti in $override
        if (array_key_exists($reparto,$this->gruppi)) {
            return $this->gruppi[$reparto]['tag'];
        }
        elseif (count($override)>0) {

            foreach ($override as $macro) {
                if ( array_key_exists($macro,$this->reparti) ) {
                    foreach ($this->reparti[$macro] as $reparto=>$r) {
                        return $this->gruppi[$reparto]['tag'];
                    }
                }
            }

            return "";

        }
        else return "";
    }

    function getGruppoID($reparto,$override) {
        //restituisce il gruppo all'interno di uno specifico reparto
        //se il valore è "" ed $override è diverso da array()
        //allora restituisce il PRIMO gruppo che trova all'interno dei MACROREPARTI inseriti in $override
    if (array_key_exists($reparto,$this->gruppi)) {
        return $this->gruppi[$reparto]['ID'];
    }
    elseif (count($override)>0) {

        foreach ($override as $macro) {
            if ( array_key_exists($macro,$this->reparti) ) {
                foreach ($this->reparti[$macro] as $reparto=>$r) {
                    return $this->gruppi[$reparto]['ID'];
                }
            }
        }

        return "";

    }
    else return "";
}

    function getGruppoRep($reparto,$override) {

        //funziona come GETGRUPPO ma restituisce un array con le informazioni di reparto e gruppo
        //inoltre non restituisce solo la prima occorrenza ma tutte
        //nel caso il collaboratore sia attivo in più reparti dello stesso macroreparto

        $res=array();

        if (array_key_exists($reparto,$this->gruppi)) {
            $res[]=array(
                "reparto"=>$reparto,
                "gruppo"=>$this->gruppi[$reparto]['tag'],
            );
        }
        elseif (count($override)>0) {

            foreach ($override as $macro) {
                if ( array_key_exists($macro,$this->reparti) ) {
                    foreach ($this->reparti[$macro] as $reparto=>$r) {
                        $res[]=array(
                            "reparto"=>$reparto,
                            "gruppo"=>$this->gruppi[$reparto]['tag']
                        );
                    }
                }
            }
        }

        if (count($res)==0) return false;
        else return $res;
    }

    function checkIDgruppi($id)  {
        if (in_array($id,$this->IDgruppi)) return true;
        else return false;
    }

    function getFuncAuth($func) {
        return $this->funzioni[$func]['chk'];
    }

    function getAppVersion() {
        //esempio id - ammo:carb
        if (isset(nebulaUniverse::$nebulaVersioni[$this->mainApp.':'.$this->nebulaFunzione['nome']])) { 
            return nebulaUniverse::$nebulaVersioni[$this->mainApp.':'.$this->nebulaFunzione['nome']];
        }
        else return "???";
        //else return $this->mainApp.':'.$this->nebulaFunzione['nome'];
    }
  

}
?>