<?php

require ('blocco.php');
require ('totale.php');

require_once ($_SERVER['DOCUMENT_ROOT']."/nebula/core/divo/divo.php");

class c2rFatturato_S {

    //SCHEMA esempio TOT
    /*analisi: {
        "tot":{
            "tag":
            "flag":
            "blocco": {
                "totale": obj BLOCCO
                "marche": {
                    "V": 
                        "totale": obj BLOCCO
                        "modelli": {
                            "5G": obj BLOCCO
                        }
                    }
                }
            }
        }
    }*/

    protected $param=array();

    protected $analisi=array(
        "tot"=>array(
            "tag"=>"Risultato",
            "flag"=>true,
            "blocco"=>null
        ),
        "nlt"=>array(
            "tag"=>"Nlt",
            "flag"=>false,
            "blocco"=>null
        ),
        "dettaglio"=>array(
            "tag"=>"Dettaglio",
            "flag"=>false,
            "blocco"=>null
        )
    );

    //questo è il blocco STANDARD utilizzato per l'analisi del fatturato
    //analisi proprietarie tipo PIT utilizzano BLOCCHI proprietari
    //anche dettaglio utilizza un blocco proprietario
    protected $blocco=array(
        "totale"=>null,
        "marche"=>array()
    );

    protected $modelli=array();

    protected $servizi=array();

    protected $addebiti=array();

    protected $gruppi=array();

    protected $galileo;

    protected $log=array();

    function __construct($param,$galileo) {

        $this->galileo=$galileo;

        //##################################
        //in questo momento viene considerato SOLO concerto,
        //successivamente occorrerà impostare le query per infinity che ritornino gli stessi campi
        //##################################

        $param['txtReparti']=substr(str_replace("'","",$param['reparti']),0,-1);
        $param['txtReparti']=str_replace(","," - ",$param['txtReparti']);

        //sostituzione dei reparti con le officine di concerto
        $this->galileo->getOfficine();
        $result=$this->galileo->getResult();
        if ($result) {
            $fetID=$this->galileo->preFetchBase('reparti');
            while ($row=$this->galileo->getFetchBase('reparti',$fetID)) {
                $param['reparti']=str_replace("'".$row['reparto']."'","'".$row['concerto']."'",$param['reparti']);
            }
        }

        $param['inizio']=str_replace("-","",$param['inizio']);
        $param['fine']=str_replace("-","",$param['fine']);

        $this->param=$param;

        //////////////////////////////////////////////
        $wclause="anno_i<=".substr($this->param['fine'],0,4)." AND anno_f>=".substr($this->param['inizio'],0,4);
        $this->galileo->executeSelect('croom',"CROOM_modelli",$wclause,"marca,modello,anno_i");
        $result=$this->galileo->getResult();
        if ($result) {
            $fetID=$this->galileo->preFetch('croom');
            while ($row=$this->galileo->getFetch('croom',$fetID)) {
                $this->modelli[$row['marca']][$row['modello']][]=$row;
            }
        }

        //TEST
        $this->servizi=array(
            "V"=>array(
                "OLAE"=>array(
                    "codice"=>"OLAE",
                    "data_i"=>"20210101",
                    "data_f"=>"21001231"
                )
            )
        );

        //gli addebiti dipendonbo dal DMS (per il momento consideriamo solo Concerto)
        $this->addebiti=array(
            "OOP"=>array(
                "OCO"=>array(
                    array("cg"=>"","tipo"=>"pag","gruppo"=>""),
                    array("cg"=>"E","tipo"=>"pag","gruppo"=>"pack")
                ),
                "OGO"=>array(
                    array("cg"=>"","tipo"=>"gar","gruppo"=>""),
                    array("cg"=>"O","tipo"=>"gar","gruppo"=>""),
                    array("cg"=>"G","tipo"=>"gar","gruppo"=>""),
                    array("cg"=>"R","tipo"=>"gar","gruppo"=>"")
                ),
                "OGOA"=>array(
                    array("cg"=>"","tipo"=>"gar","gruppo"=>""),
                    array("cg"=>"O","tipo"=>"gar","gruppo"=>""),
                    array("cg"=>"G","tipo"=>"gar","gruppo"=>""),
                    array("cg"=>"R","tipo"=>"gar","gruppo"=>"")
                ),
                "OPO"=>array(
                    array("cg"=>"P","tipo"=>"corr","gruppo"=>"corr")
                )
            ),
            "OOA"=>array(
                "OLP00"=>array(
                    array("cg"=>"","tipo"=>"app","gruppo"=>"")
                ),
                "OLP01"=>array(
                    array("cg"=>"","tipo"=>"app","gruppo"=>"")
                ),
                "OLRIP"=>array(
                    array("cg"=>"","tipo"=>"rip","gruppo"=>"")
                )
            ),
            "OOI"=>array(
                "OIG"=>array(
                    array("cg"=>"","tipo"=>"gzi","gruppo"=>"")
                )
            ),
            "OOPN"=>array(
                "ONCO"=>array(
                    array("cg"=>"","tipo"=>"nac","gruppo"=>"")
                )
            )
        );

        $this->gruppi=array(
            "V"=>array(
                "OLAE"=>array(
                    "gruppo"=>"lav",
                    "data_i"=>"20210101",
                    "data_f"=>"21001231"
                ),
                "OSCL"=>array(
                    "gruppo"=>"lav",
                    "data_i"=>"20210101",
                    "data_f"=>"21001231"
                ),
                "ONOLEG"=>array(
                    "gruppo"=>"nol",
                    "data_i"=>"20210101",
                    "data_f"=>"21001231"
                ),
                "ONOLEU"=>array(
                    "gruppo"=>"nol",
                    "data_i"=>"20210101",
                    "data_f"=>"21001231"
                ),
                "OSCS"=>array(
                    "gruppo"=>"nol",
                    "data_i"=>"20210101",
                    "data_f"=>"21001231"
                ),
                "OREVCOR"=>array(
                    "gruppo"=>"rec",
                    "data_i"=>"20210101",
                    "data_f"=>"21001231"
                ),
                "OREVDIR"=>array(
                    "gruppo"=>"rec",
                    "data_i"=>"20210101",
                    "data_f"=>"21001231"
                ),
                "OPFU"=>array(
                    "gruppo"=>"rec",
                    "data_i"=>"20210101",
                    "data_f"=>"21001231"
                ),
                "OREVIS"=>array(
                    "gruppo"=>"rev",
                    "data_i"=>"20210101",
                    "data_f"=>"21001231"
                ),
                "ORPD"=>array(
                    "gruppo"=>"gom",
                    "data_i"=>"20210101",
                    "data_f"=>"21001231"
                )
            )
        );

        //END TEST

    }

    function setAnalisi($tipo,$flag) {
        $this->analisi[$tipo]['flag']=$flag;
    }

    public static function getTotRow() {
        //gli INDICI EXTRA sono parametri aggiuntivi in base al contesto
        //gli INDICI vengono calcolati alla fine e dipensdono dai valori che hanno assunto gli altri parametri
        //gli indici sono di default FALSE
        //gli indici dei totali e subtotali vengono attivati in base alle SOMME degli elementi che contengono
        $row=array(
            "std"=>array(
                "man"=>array(
                    "tag"=>"Manodopera",
                    "lordo"=>0,
                    "netto"=>0
                ),
                "ric"=>array(
                    "tag"=>"Ricambi",
                    "lordo"=>0,
                    "netto"=>0
                ),
                "var"=>array(
                    "tag"=>"Vario",
                    "lordo"=>0,
                    "netto"=>0
                ),
                "ore"=>array(
                    "tag"=>"Ore",
                    "valore"=>0
                ),
                "pass"=>array(
                    "tag"=>"Doc",
                    "valore"=>0
                )
            ),

            "ext"=>array(
                "r19"=>array(
                    "tag"=>"Ric 1-9",
                    "flag"=>false,
                    "valore"=>0
                )
            ),

            "indici"=>array(
                "epass"=>array(
                    "tag"=>"€/pass",
                    "flag"=>false,
                    "valore"=>0
                )
            )

        );

        return $row;
    }

    function getLines() {

        //$this->setup();

        //lettura della lista dei movimenti
        $this->galileo->executeGeneric('odl','fatturato_S',$this->param,"");
        $result=$this->galileo->getResult();
        if ($result) {
            $fetID=$this->galileo->preFetch('odl');

            $tempodl=0;
            $tempmodello='99';

            while ($row=$this->galileo->getFetch('odl',$fetID)) {

                if ($row['marca_veicolo']=='') $row['marca_veicolo']='ind';

                //###################################
                //CLASSE
                //è bene deciderla qui per non appesantire gli oggetti BLOCCO
                $row['c2rClasse']='mec';
                if ($row['ind_carrozzeria']=='S') $row['c2rClasse']='car';
                if ($this->checkClasseServizi($row)) $row['c2rClasse']='ser';

                //TIPO
                $row['c2rTipo']='ind';
                $row['c2rGruppo']=''; 

                if (isset($this->addebiti[$row['cod_movimento']][$row['acarico']])) {
                                
                    foreach ($this->addebiti[$row['cod_movimento']][$row['acarico']] as $a) {
                        if ($a['cg']=='' || $a['cg']==$row['cod_tipo_garanzia']) {
                            $row['c2rTipo']=$a['tipo'];
                            $row['c2rGruppo']=$a['gruppo'];

                            if($a['cg']==$row['cod_tipo_garanzia']) break;
                        }
                    }
                }

                //RAGGRUPPO MODELLO
                if ($row['num_rif_movimento']==$tempodl) {
                    $row['gruppo_modello']=$tempmodello;
                    $row['c2rPass']=0;
                }
                else {   
                    //99 è il codice INDEFINITO per ogni marchio
                    //ci sono modelli con 2 cifre significative e modelli con 3 cifre significative
                    //viene prima verificato se esiste l'abbinamento con 3 cifre e poi se esiste l'abbinamento con 3
                    $due=substr($row['modello'],0,2);
                    $tre=substr($row['modello'],0,3);

                    $row['gruppo_modello']='99';
                    $tempmodello='99';
                    $tempodl=$row['num_rif_movimento'];
                    $row['c2rPass']=1;

                    if ($row['modello']!='') {

                        if (isset($this->modelli[$row['marca_veicolo']][$tre])) {
                            //nel periodo potrebbero esserci più di un raggruppamento per il modello
                            //i raggruppamenti sono comunque in ordine di anno_i
                            foreach ($this->modelli[$row['marca_veicolo']][$tre] as $k=>$v) {
                                if (substr($row['d_rif'],0,4)<=$v['anno_f']) {
                                    $row['gruppo_modello']=$v['res_modello'];
                                    $tempmodello=$v['res_modello'];
                                }
                            }
                        }

                        else if (isset($this->modelli[$row['marca_veicolo']][$due])) {
                            //nel periodo potrebbero esserci più di un raggruppamento per il modello
                            //i raggruppamenti sono comunque in ordine di anno_i
                            foreach ($this->modelli[$row['marca_veicolo']][$due] as $k=>$v) {
                                if (substr($row['d_rif'],0,4)<=$v['anno_f']) {
                                    $row['gruppo_modello']=$v['res_modello'];
                                    $tempmodello=$v['res_modello'];
                                }
                            }
                        }
                    }
                }

                //###################################
                //definizione gruppo specifico
                //sovrascrive eventuali definizioni precedenti (inizialmente solo correntezza)
                if (isset($this->gruppi[$row['ind_tipo_riga']][$row['rif_codice']])) {

                    if ($row['d_rif']>=$this->gruppi[$row['ind_tipo_riga']][$row['rif_codice']]['data_i'] && $row['d_rif']<=$this->gruppi[$row['ind_tipo_riga']][$row['rif_codice']]['data_f'] ) {
                        $row['c2rGruppo']=$this->gruppi[$row['ind_tipo_riga']][$row['rif_codice']]['gruppo'];
                    }

                }


                //###################################

                foreach ($this->analisi as $ka=>$a) {
                    if ($a['flag']) {
                        call_user_func_array(array($this, 'feed_'.$ka), array($row) );
                    }
                }
            }
        }

        //concludi (definisci i totali)
        foreach ($this->analisi as $ka=>$a) {
            if ($a['flag']) {
                call_user_func_array(array($this, 'concludi_'.$ka), array() );
            }
        }
    }

    function checkClasseServizi($row) {

        //verifica se esiste il record nell'array SERVIZIO per le condizioni della specifica riga
        return isset($this->servizi[$row['ind_tipo_riga']][$row['rif_codice']]);
    }

    function feed_tot($arr) {

        //se necessario instanziare il BLOCCO dell'analisi
        if (is_null($this->analisi['tot']['blocco'])) {
            $this->analisi['tot']['blocco']=$this->blocco;
            $this->analisi['tot']['blocco']['totale']=new c2rBlocco("",$this->param);
        }

        //se necessario instanziare il blocco della marca specifica
        if (!isset($this->analisi['tot']['blocco']['marche'][$arr['marca_veicolo']])) {
            $this->analisi['tot']['blocco']['marche'][$arr['marca_veicolo']]=array(
                "totale"=>new c2rBlocco($arr['marca_veicolo'],$this->param),
                "modelli"=>array()
            );
        }

        //se necessario instanziare il blocco del modello specifico
        if (!isset($this->analisi['tot']['blocco']['marche'][$arr['marca_veicolo']]['modelli'][$arr['gruppo_modello']])) {
            $this->analisi['tot']['blocco']['marche'][$arr['marca_veicolo']]['modelli'][$arr['gruppo_modello']]=new c2rBlocco($arr['marca_veicolo'],$this->param);
        }

        $this->analisi['tot']['blocco']['marche'][$arr['marca_veicolo']]['modelli'][$arr['gruppo_modello']]->feed($arr);

        //$this->log[]=$arr;

    }

    function concludi_tot() {   

        foreach ($this->analisi['tot']['blocco']['marche'] as $marca=>$mk) {

            foreach ($mk['modelli'] as $modello=>$m) {

                $this->analisi['tot']['blocco']['marche'][$marca]['totale']->sum($m->getGrid());

            }

            $this->analisi['tot']['blocco']['totale']->sum($this->analisi['tot']['blocco']['marche'][$marca]['totale']->getGrid());

        }
    }

    function feed_nlt($arr) {

        //verifica se il record rientra nelle caratteristiche di NLT
        //questa analisi per il resto è come TOT ??? (non è divisa per MARCA e MODELLO allo stesso modo) ???
        
    }

    function concludi_nlt() {

    }

    function draw() {

        //new DIVO 1
        //($index,$htab,$minh,$fixed)
        $divo1=new Divo('c2rAnalisi','5%','97%',1);
        $divo1->setBk('#dac59d');

        //scrivi i div delle analisi
        foreach ($this->analisi as $analisi=>$a) {

            if (!$a['flag']) continue;

            $txt='<div>';

                //scrivi DIVO delle analisi
                if ($analisi=='tot') $txt.=$this->drawTot();
                
            $txt.='</div>';

            //add DIV
            //add_div($titolo,$color,$chk,$stato,$codice,$selected,$css)
            $divo1->add_div($a['tag'],'black',0,'',$txt,($analisi=='tot'?1:0),array());
        }

        $divo1->build();
        $divo1->draw();

        //echo json_encode($this->analisi);
        //echo json_encode($this->log);
    }

    function drawTot() {

        $divo2=new Divo('c2rMarche','4%','96%',1);
        $divo2->setBk('#a8b4ce');

        $css=array(
            "font-weight"=>"bold",
            "margin-top"=>"0px",
            "font-size"=>"0.8em",
            "text-align"=>"center"
        );

        //##############################################
        //scrittura del DIV per il totale generale in DIVO2
        $txt='<div>';
            ob_start();
                $this->analisi['tot']['blocco']['totale']->draw();
            $txt.=ob_get_clean();
        $txt.='</div>';

        $divo2->add_div('TOT','black',0,'',$txt,1,$css);
        //###############################################

        ksort($this->analisi['tot']['blocco']['marche']);

        foreach ($this->analisi['tot']['blocco']['marche'] as $marca=>$m) {

            $divo3=new Divo('c2rModelli_'.$marca,'4%','95%',1);
            $divo3->setBk('#a8cead');

            //##############################################
            //scrittura del DIV per il totale di marca in DIVO3
            $t3='<div>';
                ob_start();
                    $m['totale']->draw();
                $t3.=ob_get_clean();
            $t3.='</div>';

            $divo3->add_div('TOT','black',0,'',$t3,1,$css);
            //###############################################

            ksort($m['modelli']);

            //creazione del div dei modelli
            foreach ($m['modelli'] as $modello=>$o) {

                $t2='<div>';
                    ob_start();
                        $o->draw();
                    $t2.=ob_get_clean();            
                $t2.='</div>';

                $divo3->add_div($modello,'black',0,'',$t2,0,$css);
            }

            //scrivi DIVO delle marche
            $txt='<div>';
                $divo3->build();
                ob_start();
                    $divo3->draw();
                $txt.=ob_get_clean();
            $txt.='</div>';

            $divo2->add_div($marca,'black',0,'',$txt,0,$css);
        }

        $divo2->build();

        ob_start();
            $divo2->draw();
        return ob_get_clean();

    }

}

?>