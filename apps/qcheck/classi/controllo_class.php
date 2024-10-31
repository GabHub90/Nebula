<?php
/*
- Abbinamento definisce l'arco temporale in cui una data versione di un controllo è valida per un reparto
- di fatto il parametro "versione" serve per definire un raggruppamento.
ID e quindi il TITOLO e la DESCRIZIONE rappresentano il posizionamento logico del controllo,
la VERSIONE indica il cambiamento nel tempo del controllo.
- Quando i cambiamenti vengono ritenuti sostanziali cambierà l'ID del controllo ed i risultati non saranno più
in relazione tra di loro.
- I risultati infatti sono in relazione tra loro attraverso il PUNTEGGIO che viene attribuito ad ogni controllo.
Finché i punteggi, benché provenienti da FORM differenti sono equiparabili allora si parla di versioni differenti
dello stesso controllo. Quando questo non è più vero allora viene cambiato il controllo.

ABBINAMENTO (QCHECK_abbinamenti)
"ID"                Indice del controllo
"reparto"           Reparto a cui è abbinato
"titolo"            Titolo (da QCHECK_controlli)
"auth"              Descrive chi può vedere il controllo Esempio: {"RS":"1","RT":"1","RC":"1","ASS":"1"}
                    ( "1"=si, "0"=no )
                    21.04.2021 aggiunta pozione "2" = vista senza limitazione di reparto (storico)

"data_i"            Inizio validità dell'accoppiamento
"data_f"            Fine di validità dell'accoppiamento
"versione"          Versione del controllo

VERSIONI (QCHECK_versioni)
"ID"                Indice del controllo (riferimento abbinamento)
"versione"          Versione (riferimento abbinamento)
"descrizione"       Descrizione della versione del controllo
"moduli"            Moduli che compongono il controllo Esempio: ["1","2"]
"auth"              Descrive chi può vedere il controllo Esempio: {"RS":"*","RT":"1","RC":"1","ASS":"1"}
                    ( "*"=tutti , "1"=solo i propri, "0"=nessuno )
"peso"              JSON che determina il peso dei punteggi dei moduli sul totale

//01.03.2021 TOLTO
"limiti"            riferimenti per la colorazione del punteggio {"R":60,"G":80,"V":90}

*/


require_once("modulo_class.php");
require_once("qc_new_class.php");
require_once("qc_report.php");
require_once("qc_filtro_storico.php");

class qcControllo {

    protected $nebulaFunzione=array();

    protected $abbinamento=array(
        "ID"=>"",
        "controllo"=>"",
        "reparto"=>"",
        "titolo"=>"",
        "auth"=>'',
        "data_i"=>"",
        "data_f"=>"",
        "versione"=>""
    );

    //trasformazioni dei JSON dei DB VERSIONE
    protected $authControllo=array();
    protected $authVersione=array();
    protected $arrayPeso=array();

    //autorizzazioni alle funzioni di amministrazione
    //esiste solo se è il controllo attuale altrimenti è un array vuoto
    protected $mainAuth=array();

    //actual
    protected $modulo="";
    protected $versione=array();

    protected $moduli=array();

    //oggetto qcNew
    protected $qcNew;

    //oggetto nebulaID
    protected $id;
    protected $galileo;

    protected $log=array();

    function __construct($abbinamento,$nebulaFunzione,$id,$mainAuth,$galileo) {

        $this->id=$id;
        $this->nebulaFunzione=$nebulaFunzione;
        $this->mainAuth=$mainAuth;
        
        foreach ($this->abbinamento as $k=>$o) {
            if ( array_key_exists($k,$abbinamento) ) {
                $this->abbinamento[$k]=$abbinamento[$k];
            }
        }

        if (isset($this->abbinamento['auth']) && $this->abbinamento['auth']!="") {
            $this->authControllo=json_decode($this->abbinamento['auth'],true);
        }
        else $this->authControllo=array();

        //$this->log[]=$this->authControllo;

        $this->galileo=$galileo;

        $this->loadVersion();
    }

    function getLog() {
        return $this->log;
    }

    function getFormLog($modulo) {
        return $this->moduli["m".$modulo]->getFormLog();
    }

    function getTitle() {

        $arr=array(
            "titolo"=>$this->abbinamento['titolo'],
            "descrizione"=>$this->versione['descrizione'].' - v'.$this->versione['versione']
        );

        return $arr;
    }

    function loadVersion() {

        //$ver=$versione!=""?$versione:$this->abbinamento['versione'];
        $ver=$this->abbinamento['versione'];
        
        /*
        if ($versione=="") {
            //carica versione in abbinamento da QCHECK_versioni ($this->abbinamento['versione'])

            //TEST
                $v=array(
                    "controllo"=>1,
                    "versione"=>"1",
                    "descrizione"=>"Controllo del processo di riparazione",
                    "moduli"=>'["1","2","3"]',
                    "auth"=>'{"RS":"*","RT":"1","RC":"1","ASS":"1"}',
                    "peso"=>'{"1":33.33,"2":33.33,"3":33.33}'
                );
            //END TEST
        }
        else {
            //carica versione richiesta
            //questo diventa necessario quando si vuole completare un FORM già aperto ma non più attuale

            //TEST
            $v=array(
                "controllo"=>1,
                "versione"=>"1",
                "descrizione"=>"Controllo del processo di riparazione",
                "moduli"=>'["1","2","3"]',
                "auth"=>'{"RS":"*","RT":"1","RC":"1","ASS":"1"}',
                "peso"=>'{"1":33.33,"2":33.33,"3":33.33}'
            );
            //END TEST
        }*/

        $wClause="controllo='".$this->abbinamento['controllo']."' AND versione='".$ver."'";

        //executeSelect($tipo,$tabella,$wclause,$order) {
        $this->galileo->executeSelect("qcheck","QCHECK_versioni",$wClause,"");
        $result=$this->galileo->getResult();
        
        if ($result) {

            $fetID=$this->galileo->preFetch('qcheck');

             while ($row=$this->galileo->getFetch('qcheck',$fetID)) {
                $this->versione=$row;
            }
        }

        if (isset($this->versione['auth']) && $this->versione['auth']!="") {
            $this->authVersione=json_decode($this->versione['auth'],true);
        }
        else $this->authVersione=array();

        if (isset($this->versione['peso']) && $this->versione['peso']!="") {
            $this->arrayPeso=json_decode($this->versione['peso'],true);
        }
        else $this->arrayPeso=array();

        $this->loadModuli();
    }

    function loadModuli() {

        //echo json_encode($this->galileo->getLog('query'));

        $arr=json_decode($this->versione['moduli'],true);

       foreach ($arr as $k) {
           //la "m" serve per non modificare l'ordine
           $this->moduli["m".$k]=new qcModulo($k,$this->nebulaFunzione,$this->galileo);
       }

       $this->qcNew=new qcNew($this->id->getLogged(),$this->abbinamento['ID'],$this->abbinamento['controllo'],$this->abbinamento['reparto'],$this->abbinamento['versione'],$this->moduli);

    }

    function loadForm($chain,$risposte,$stato) {
        $this->modulo=$chain['modulo'];
        $this->moduli["m".$chain['modulo']]->loadForm($chain,$risposte,$stato);
    }

    function getSelectParam($gruppo) {

        $d=date('Ymd');
        if ($this->abbinamento['data_i']<=$d && $this->abbinamento['data_f']>=$d) {
            $v=$this->abbinamento['ID'].':'.$this->abbinamento['controllo'];
        }
        else $v='0';

        return array(
            "val"=>$v,
            "testo"=>$this->abbinamento['titolo'],
            "disabled"=>($this->getAuthControllo($gruppo)?false:true)
        );

    }

    function getAuthControllo($gruppo) {

        //$this->log=$this->authControllo[$gruppo];

        $ret=false;
        if ( isset($this->authControllo[$gruppo]) ) {
            if ($this->authControllo[$gruppo]=='1' || $this->authControllo[$gruppo]=='2') $ret=true;
        }

        return $ret;
    }

    function getAuthVersione($gruppo) {

        //$this->log=$this->authControllo[$gruppo];

        if ( isset($this->authVersione[$gruppo]) ) {
            return $this->authVersione[$gruppo];
        }

        else return "";
    }

    function getVarianti($modulo) {
        return $this->moduli['m'.$modulo]->getVarianti();
    }

    function getFormTag($modulo) {
        return $this->moduli["m".$modulo]->getFormTag();
    }

    function evaluateScore($s) {

        if ( !$score=json_decode($s,true) ) {
            $score=array();
        }

        return $score;
    }

    function drawControllo($arr,$collGruppo) {

        $div=array(
            "intestazione"=>"",
            "score_controllo"=>array(
                "punteggio"=>0,
                "risposte"=>0,
                "domande"=>0,
                "completo"=>true
            ),
            "txt"=>array()
        );

        $countModuli=0;

        foreach ($this->moduli as $k=>$m) {

            $index=substr($k,1);
            $rif=$arr[$index];

            //trasforma il JSON punteggio in array()
            //26.02.2021 ho voluto mettere evaluateScore a livello controllo
            $rif['score']=$this->evaluateScore($rif['punteggio']);

            $a=$this->moduli[$k]->drawModulo($this->abbinamento['ID'],$rif['ID_controllo'],$rif,$collGruppo);
            
            $div['intestazione']=$a['intestazione'];
            $div['txt'][]=$a['txt'];

            //###################
            //calcolo punteggio e completezza controllo in base al peso specifico del modulo
            if ( isset($rif['score']['risposte']) ) $div['score_controllo']['risposte']+=$rif['score']['risposte'];
            if ( isset($rif['score']['domande']) ) $div['score_controllo']['domande']+=$rif['score']['domande'];

            if ( isset($rif['score']['punteggio']) ) {

                if ( isset($index,$this->arrayPeso) ) {
                    $peso=$this->arrayPeso[$index]/100;
                }
                else {
                    $peso=0;
                }

                $div['score_controllo']['punteggio']+=$rif['score']['punteggio']*$peso;
            }
            else {
                $div['score_controllo']['completo']=false;
            }

            //###################################
            
        }

        $div['score_controllo']['punteggio']=round($div['score_controllo']['punteggio']);

        /*
        echo '<div>';
            //echo $this->modulo;
            //echo json_encode($this->arrayPeso);
        echo '</div>';
        */

        return $div;
    }

    function drawNew() {
        $this->qcNew->draw();
    }

    function drawForm() {
        $this->moduli["m".$this->modulo]->drawForm();
    }

    function drawStoricoFilter($gruppo) {

        $formTag='qcfs';
        $qcfs=new qcFiltroStorico($formTag);

        $authVersion=array();
        $authControllo=(isset($this->authControllo[$gruppo]))?$this->authControllo[$gruppo]:'0';

        $wClause="controllo='".$this->abbinamento['controllo']."'";

        //executeSelect($tipo,$tabella,$wclause,$order) {
        $this->galileo->executeSelect("qcheck","QCHECK_versioni",$wClause,"");
        $result=$this->galileo->getResult();
        if ($result) {
            $fetID=$this->galileo->preFetch('qcheck');

            while ($row=$this->galileo->getFetch('qcheck',$fetID)) {

                if (isset($row['auth']) && $row['auth']!="") {

                    if ($tempversion=json_decode($row['auth'],true)) {

                        foreach ($tempversion as $kv=>$v) {
                            if (!array_key_exists($kv,$authVersion)) $authVersion[$kv]=$v;
                            else {
                                if ($v=='*') $authVersion[$kv]='*';
                                elseif ($v=='1' && $authVersion[$kv]=='0') $authVersion[$kv]='1';
                            }
                        }
                    }
                    
                }
            }
        }

        if ( !isset($authVersion[$gruppo]) || $authVersion[$gruppo]=='0' || $authControllo=='0') {
            echo 'Collaboratore non autorizzato';
            return;
        }

        /////////////////////////////////////////////////////

        $qcfs->draw();

        echo '<div style="margin-top:15px;">';

            ////////////////////
            //filtro reparto
            $tempreparti=array();

            $tarr=array('controllo'=>$this->abbinamento['controllo']);
            $this->galileo->executeGeneric("qcheck","getRepartiControllo",$tarr,"");
            $result=$this->galileo->getResult();
            if ($result) {
                $fetID=$this->galileo->preFetch('qcheck');
                while ($row=$this->galileo->getFetch('qcheck',$fetID)) {
                    $tempreparti[]=$row['reparto'];
                }
            }

            echo '<div style="display:inline-block;width:25%;text-align:center;">';

                echo '<div>Reparto</div>';

                echo '<div>';

                    if (count($tempreparti)>0 && $authControllo=="2") {

                        echo '<select id="'.$formTag.'_reparto" style="width:90%;font-size:1.2em;" class="js_chk_'.$formTag.'" js_chk_'.$formTag.'_tipo="reparto">';
                            echo '<option value="">TUTTI</option>';
                            foreach ($tempreparti as $kt=>$t) {
                                echo '<option style="" value="'.$t.'">'.$t.'</option>';
                            }
                        echo '</select>';
                    }
                    else {
                        echo '<select id="'.$formTag.'_reparto" style="width:90%;font-size:1.2em;" class="js_chk_'.$formTag.'" js_chk_'.$formTag.'_tipo="reparto" disabled="disabled" >';
                                echo '<option style="" value="'.$this->abbinamento['reparto'].'">'.$this->abbinamento['reparto'].'</option>';
                        echo '</select>';
                    }

                echo '</div>';

            echo '</div>';

            ////////////////////
            //filtro esecutore
            echo '<div style="display:inline-block;width:37%;text-align:center;">';

                echo '<div>Esecutore</div>';

                echo '<div>';

                    echo '<input id="'.$formTag.'_esecutore" style="width:90%;font-size:1em;text-align:center;" type="text"';
                        //se tutti
                        if ($authVersion[$gruppo]=="*") {
                            echo ' value=""';
                        }
                        else {
                            echo ' value="'.$this->id->getLogged().'" disabled="disabled"';
                        }
                    echo ' class="js_chk_'.$formTag.'" js_chk_'.$formTag.'_tipo="esecutore"/>';

                echo '</div>';

            echo '</div>';

            ////////////////////
            //filtro operatore
            echo '<div style="display:inline-block;width:37%;text-align:center;">';

                echo '<div>Operatore</div>';

                echo '<div>';

                    echo '<input id="'.$formTag.'_operatore" style="width:90%;font-size:1em;text-align:center;" type="text"';
                        //se tutti
                        if ($authVersion[$gruppo]=="*") {
                            echo ' value=""';
                        }
                        else {
                            echo ' value="'.$this->id->getLogged().'" disabled="disabled"';
                        }
                    echo ' class="js_chk_'.$formTag.'" js_chk_'.$formTag.'_tipo="operatore"/>';

                echo '</div>';

            echo '</div>';

        echo '</div>';

        /////////////////////////////////

        echo '<div style="margin-top:15px;">';

            echo '<div style="display:inline-block;width:25%;text-align:center;"></div>';

            ////////////////////
            //filtro modulo
            echo '<div style="display:inline-block;width:37%;text-align:center;">';

                echo '<div>Modulo</div>';

                echo '<div>';

                    echo '<input id="'.$formTag.'_modulo" style="width:50%;font-size:1em;text-align:center;" maxlenght="2" type="text" class="js_chk_'.$formTag.'" js_chk_'.$formTag.'_tipo="modulo"/>';

                echo '</div>';

            echo '</div>';

            ////////////////////
            //filtro variante
            echo '<div style="display:inline-block;width:37%;text-align:center;">';

                echo '<div>Variante</div>';

                echo '<div>';

                    echo '<input id="'.$formTag.'_variante" style="width:50%;font-size:1em;text-align:center;" type="text" class="js_chk_'.$formTag.'" js_chk_'.$formTag.'_tipo="variante"/>';

                echo '</div>';

            echo '</div>';

        echo '</div>';

        ///////////////////////////////////////////////////

        echo '<div style="margin-top:20px;">';
            
            ////////////////////
            //filtro chiave
            echo '<div style="display:inline-block;width:25%;text-align:center;">';

                echo '<div>chiave</div>';
            
            echo '</div>';

            echo '<div style="display:inline-block;width:74%;text-align:center;">';

                echo '<input id="'.$formTag.'_chiave" style="width:95%;font-size:1em;text-align:center;" type="text" class="js_chk_'.$formTag.'" js_chk_'.$formTag.'_tipo="chiave" />';

            echo '</div>';

        echo '</div>';

        /////////////////////////////////

        echo '<div style="margin-top:15px;">';

            ////////////////////
            //filtro DA
            echo '<div style="display:inline-block;width:49%;text-align:center;vertical-align:top;">';

                echo '<div>da</div>';

                echo '<div>';

                    echo '<input id="'.$formTag.'_da" style="width:90%;font-size:1em;" type="date" value="'.date('Y-m-d').'" class="js_chk_'.$formTag.'" js_chk_'.$formTag.'_tipo="da" onchange="window._js_chk_'.$formTag.'.js_chk();" />';

                echo '</div>';

                echo '<div id="js_chk_'.$formTag.'_error_da" class="chekko_error js_chk_'.$formTag.'_error"></div>';
            
            echo '</div>';

            ////////////////////
            //filtro A
            echo '<div style="display:inline-block;width:49%;text-align:center;vertical-align:top;">';

                echo '<div>a</div>';

                echo '<div>';

                    echo '<input id="'.$formTag.'_a" style="width:90%;font-size:1em;" type="date" value="'.date('Y-m-d').'" class="js_chk_'.$formTag.'" js_chk_'.$formTag.'_tipo="a" onchange="window._js_chk_'.$formTag.'.js_chk();" />';

                echo '</div>';

                echo '<div id="js_chk_'.$formTag.'_error_a" class="chekko_error js_chk_'.$formTag.'_error"></div>';
            
            echo '</div>';
        
        echo '</div>';

        echo '<div style="margin-top:15px;width:100%;text-align:center;">';
            echo '<div class="divButton" style="width:80px;left:50%;transform:translate(-50%);" onclick="window._js_chk_'.$formTag.'.scrivi();">cerca</div>';
        echo '</div>';

        echo '<input id="'.$formTag.'_controllo" type="hidden" value="'.$this->abbinamento['controllo'].'" class="js_chk_'.$formTag.'" js_chk_'.$formTag.'_tipo="controllo" />';

        ////////////////////////////////////////////////////////////

        

        /*echo '<div>';
            echo json_encode($this->abbinamento);
            echo (isset($this->authControllo[$gruppo]))?$this->authControllo[$gruppo]:'error';
        echo '</div>';

        echo '<div>';
            echo json_encode($authVersion);
        echo '</div>';
        */



    }

}
?>