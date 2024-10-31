<?php
require_once ($_SERVER['DOCUMENT_ROOT'].'/nebula/core/odl/odl_func.php');
require_once ($_SERVER['DOCUMENT_ROOT']."/nebula/apps/c2r/classi/wormhole.php");

require ('blocco.php');
require ('totale.php');

require_once ($_SERVER['DOCUMENT_ROOT']."/nebula/core/divo/divo.php");
include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/core/blocklist/blocklist.php');

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
            "tag"=>"Totale",
            "flag"=>true,
            "blocco"=>null
        ),
        "nlt"=>array(
            "tag"=>"NLT VWL",
            "flag"=>true,
            "blocco"=>null
        ),
        "dettaglio"=>array(
            "tag"=>"Dettaglio",
            "flag"=>false,
            "blocco"=>null
        ),
        "rc"=>array(
            "tag"=>"RC",
            "flag"=>true,
            "blocco"=>null,
            "dettaglio"=>array()
        )
    );

    protected $anarep=array();

    //questo è il blocco STANDARD utilizzato per l'analisi del fatturato
    //analisi proprietarie tipo PIT utilizzano BLOCCHI proprietari
    //anche dettaglio utilizza un blocco proprietario
    protected $blocco=array(
        "totale"=>null,
        "marche"=>array()
    );

    protected $modelli=array();

    //protected $servizi=array();

    protected $addebiti=array();

    protected $gruppi=array();

    protected $reparti=array();

    protected $collaboratori=array();

    protected $indefiniti=array();

    //##############################
    //famiglie incentivate
    protected $famiglie=array(
    
        "V"=>array(
            "pastiglie"=>array(
                "flag"=>true,
                "periodi"=>array(
                    array(
                        "inizio"=>'20220101',
                        "fine"=>'20221231',
                        "gr"=>array(),
                        "codici"=>array('BBB','BZB')
                    )
                )
            ),
            "dischi"=>array(
                "flag"=>true,
                "periodi"=>array(
                    array(
                        "inizio"=>'20220101',
                        "fine"=>'20221231',
                        "gr"=>array(),
                        "codici"=>array('BBA','BBC','BBS','BZA','BZS')
                    )
                )
            ),
            "spazzole"=>array(
                "flag"=>true,
                "periodi"=>array(
                    array(
                        "inizio"=>'20220101',
                        "fine"=>'20221231',
                        "gr"=>array(),
                        "codici"=>array('EWC','EWB','EWA','EWR','EWG','EZF','EZK')
                    )
                )
            ),
            "batterie"=>array(
                "flag"=>true,
                "periodi"=>array(
                    array(
                        "inizio"=>'20220101',
                        "fine"=>'20221231',
                        "gr"=>array(),
                        "codici"=>array('EGA','EGD','EZB')
                    )
                )
            ),
            "ammortizz"=>array(
                "flag"=>true,
                "periodi"=>array(
                    array(
                        "inizio"=>'20220101',
                        "fine"=>'20221231',
                        "gr"=>array(),
                        "codici"=>array('FEA','FEC','FEG','FZA')
                    )
                )
            ),
            "gomme"=>array(
                "flag"=>true,
                "periodi"=>array(
                    array(
                        "inizio"=>'20220101',
                        "fine"=>'20221231',
                        "gr"=>array('T'),
                        "codici"=>array()
                    )
                )
            ),
            "accessori"=>array(
                "flag"=>true,
                "periodi"=>array(
                    array(
                        "inizio"=>'20220101',
                        "fine"=>'20221231',
                        "gr"=>array('A','B','C','D','E','F','G'),
                        "codici"=>array(),
                        "escluso"=>array('81A051629')
                    )
                )
            )
        )
    );
    //##############################

    protected $telai=array();

    protected $wh;
    protected $odlFunc;
    protected $galileo;

    protected $log=array();

    function __construct($param,$galileo) {

        $this->galileo=$galileo;

        $this->odlFunc=new nebulaOdlFunc($this->galileo);

        $param['inizio']=str_replace("-","",$param['inizio']);
        $param['fine']=str_replace("-","",$param['fine']);

        foreach (explode(',',substr($param['reparti'],0,-1)) as $x) {
            $x=str_replace("'","",$x);
            $this->reparti[$x]=$x;

            $this->anarep[$x]=$this->analisi;
        }

        $param['txtReparti']=substr(str_replace("'","",$param['reparti']),0,-1);
        $param['txtReparti']=str_replace(","," - ",$param['txtReparti']);

        //sostituzione dei reparti con le officine di concerto
        /*$this->galileo->getOfficine();
        $result=$this->galileo->getResult();
        if ($result) {
            $fetID=$this->galileo->preFetchBase('reparti');
            while ($row=$this->galileo->getFetchBase('reparti',$fetID)) {
                $param['reparti']=str_replace("'".$row['reparto']."'","'".$row['concerto']."'",$param['reparti']);
            }
        }*/

        $this->param=$param;

        $this->param['flag_rv']=true;
        $this->param['flag_rp']=true;

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

        /*TEST
        $this->servizi=array(
            "V"=>array(
                "OLAE"=>array(
                    "codice"=>"OLAE",
                    "data_i"=>"20210101",
                    "data_f"=>"21001231"
                )
            )
        );*/

        /*gli addebiti dipendonbo dal DMS (per il momento consideriamo solo Concerto)
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
        */
        $this->gruppi=array(

            "concerto"=>array(
                "pag"=>array(
                    "V"=>array(
                        "OLAE"=>array(
                            "gruppo"=>"lav",
                            "classe"=>"ser",
                            "data_i"=>"20210101",
                            "data_f"=>"21001231"
                        ),
                        "OSCL"=>array(
                            "gruppo"=>"lav",
                            "classe"=>"ser",
                            "data_i"=>"20210101",
                            "data_f"=>"21001231"
                        ),
                        "ONOLEG"=>array(
                            "gruppo"=>"nol",
                            "classe"=>"ser",
                            "data_i"=>"20210101",
                            "data_f"=>"21001231"
                        ),
                        "ONOLEU"=>array(
                            "gruppo"=>"nol",
                            "classe"=>"ser",
                            "data_i"=>"20210101",
                            "data_f"=>"21001231"
                        ),
                        "OSCS"=>array(
                            "gruppo"=>"nol",
                            "classe"=>"ser",
                            "data_i"=>"20210101",
                            "data_f"=>"21001231"
                        ),
                        "OREVCOR"=>array(
                            "gruppo"=>"rec",
                            "classe"=>"rev",
                            "data_i"=>"20210101",
                            "data_f"=>"21001231"
                        ),
                        "OREVDIR"=>array(
                            "gruppo"=>"rec",
                            "classe"=>"rev",
                            "data_i"=>"20210101",
                            "data_f"=>"21001231"
                        ),
                        "OPFU"=>array(
                            "gruppo"=>"rec",
                            "classe"=>"gom",
                            "data_i"=>"20210101",
                            "data_f"=>"21001231"
                        ),
                        "OREVIS"=>array(
                            "gruppo"=>"rev",
                            "classe"=>"rev",
                            "data_i"=>"20210101",
                            "data_f"=>"21001231"
                        ),
                        "ORPD"=>array(
                            "gruppo"=>"",
                            "classe"=>"gom",
                            "data_i"=>"20210101",
                            "data_f"=>"21001231"
                        ),
                        "OPCAR"=>array(
                            "gruppo"=>"terzi",
                            "classe"=>"car",
                            "data_i"=>"20210101",
                            "data_f"=>"21001231"
                        )
                    )
                )
            ),
            "infinity"=>array(
                "pag"=>array(
                    "V"=>array(
                        "SCM"=>array(
                            "gruppo"=>"sco",
                            "classe"=>"",
                            "riga"=>"M",
                            "assoluto"=>"netto",
                            "data_i"=>"20210101",
                            "data_f"=>"21001231"
                        ),
                        "SCR"=>array(
                            "gruppo"=>"sco",
                            "classe"=>"",
                            "riga"=>"R",
                            "assoluto"=>"netto",
                            "data_i"=>"20210101",
                            "data_f"=>"21001231"
                        ),
                        "REV"=>array(
                            "gruppo"=>"rev",
                            "classe"=>"rev",
                            "data_i"=>"20210101",
                            "data_f"=>"21001231"
                        ),
                        "REVSP"=>array(
                            "gruppo"=>"rec",
                            "classe"=>"rev",
                            "data_i"=>"20210101",
                            "data_f"=>"21001231"
                        ),
                        "REVIMP"=>array(
                            "gruppo"=>"rec",
                            "classe"=>"rev",
                            "data_i"=>"20210101",
                            "data_f"=>"21001231"
                        ),
                        "PFU"=>array(
                            "gruppo"=>"rec",
                            "classe"=>"gom",
                            "data_i"=>"20210101",
                            "data_f"=>"21001231"
                        ),
                        "AZG"=>array(
                            "gruppo"=>"met",
                            "classe"=>"ser",
                            "data_i"=>"20210101",
                            "data_f"=>"21001231"
                        ),
                        "OLAE"=>array(
                            "gruppo"=>"lav",
                            "classe"=>"ser",
                            "data_i"=>"20210101",
                            "data_f"=>"21001231"
                        ),
                        "LAVSTD"=>array(
                            "gruppo"=>"lav",
                            "classe"=>"ser",
                            "data_i"=>"20210101",
                            "data_f"=>"21001231"
                        ),
                        "OPVER"=>array(
                            "gruppo"=>"",
                            "classe"=>"car",
                            "data_i"=>"20210101",
                            "data_f"=>"21001231"
                        ),
                        "OPCAR"=>array(
                            "gruppo"=>"terzi",
                            "classe"=>"car",
                            "data_i"=>"20210101",
                            "data_f"=>"21001231"
                        ),
                        "ORPD"=>array(
                            "gruppo"=>"",
                            "classe"=>"gom",
                            "data_i"=>"20210101",
                            "data_f"=>"21001231"
                        ),
                        "ONOLE"=>array(
                            "gruppo"=>"nol",
                            "classe"=>"ser",
                            "data_i"=>"20210101",
                            "data_f"=>"21001231"
                        ),
                        "NOL"=>array(
                            "gruppo"=>"nol",
                            "classe"=>"ser",
                            "data_i"=>"20210101",
                            "data_f"=>"21001231"
                        ),
                        "IGIEN"=>array(
                            "gruppo"=>"lav",
                            "classe"=>"ser",
                            "data_i"=>"20210101",
                            "data_f"=>"21001231"
                        ),
                        "RINN.MOBIL"=>array(
                            "gruppo"=>"rec",
                            "classe"=>"ser",
                            "data_i"=>"20210101",
                            "data_f"=>"21001231"
                        ),
                    )
                )
            )
        );

        //END TEST

        $this->galileo->getCollaboratoriIntervallo('',$this->param['inizio'],$this->param['fine']);
        if ($this->galileo->getResult()) {
            $fid=$this->galileo->preFetchBase('maestro');
            while ($row=$this->galileo->getFetchBase('maestro',$fid)) {
                $this->collaboratori[$row['ID_coll']]=$row;
            }
        }
    }

    function setAnalisi($tipo,$flag) {
        $this->analisi[$tipo]['flag']=$flag;

        foreach ($this->anarep as $rep=>$a) {
            $this->anarep[$rep][$tipo]['flag']=$flag;
        }
    }

    function forzaResponsabile() {
        //??????????????????????????????????????????????
        //per analisi NLT fatturate da RC non facenti parte del reparto specifico
        $this->param['default']['responsabile']='true';
    }

    function getBudget() {
        $temp=array();
        foreach ($this->anarep as $reparto=>$r) {
            /*echo '<div>';
                //eval('$temp='.var_export ($r['tot']['blocco']['totale']->getGrid()));
                echo json_encode($r['tot']['blocco']['totale']->getTotArr());
            echo '</div>';*/
            //echo '<div>'.$reparto.'</div>';
            if (isset($r['tot']['blocco']['totale'])) {
                $temp[$reparto]=$r['tot']['blocco']['totale']->getTotArr();

                unset($temp[$reparto]['tot']);

                //HANDLING
                $temp[$reparto]['budget']['totale']['std']['handling']=array(
                    "netto"=>$r['tot']['blocco']['totale']->getTotGruppo('mec','gar','hand','ric','netto')+$r['tot']['blocco']['totale']->getTotGruppo('mec','gar','corr','ric','netto'),
                    "tag"=>'Handling',
                    "classe"=>"mec"
                );
                /*TERZI -- creato nuova classe raggruppamento fatturato --
                $temp[$reparto]['budget']['totale']['std']['terzi']=array(
                    "netto"=>$r['tot']['blocco']['totale']->getTotGruppo('ser','pag','terzi','var','netto')+$r['tot']['blocco']['totale']->getTotGruppo('ser','int','terzi','var','netto')+$r['tot']['blocco']['totale']->getTotGruppo('ser','gar','terzi','var','netto')-$r['tot']['blocco']['totale']->getTotGruppo('ser','nac','terzi','var','netto'),
                    "tag"=>'Terzi',
                    "classe"=>"ser"
                );*/
            }
        }

        return $temp;
    }

    function getTotBudget() {
        $temp=array();
        foreach ($this->anarep as $reparto=>$r) {
            if (isset($r['tot']['blocco']['totale'])) {
                $temp[$reparto]=$r['tot']['blocco']['totale']->getTotTipi();
            }
        }

        return $temp;
    }

    function getTotaloneBudget() {
        $temp=array();
        foreach ($this->anarep as $reparto=>$r) {
            if (isset($r['tot']['blocco']['totale'])) {
                $temp[$reparto]=$r['tot']['blocco']['totale']->getTotaloneArr();
            }
        }

        return $temp;
    }

    public static function getTotRow() {
        //gli INDICI EXTRA sono parametri aggiuntivi in base al contesto
        //gli INDICI vengono calcolati alla fine e dipensdono dai valori che hanno assunto gli altri parametri
        //gli indici sono di default FALSE
        //gli indici dei totali e subtotali vengono attivati in base alle SOMME degli elementi che contengono
        
        /*$row=array(
            "std"=>array(
                "man"=>array(
                    "tag"=>"Manodopera",
                    "lordo"=>0,
                    "netto"=>0,
                    "costo"=>0
                ),
                "ric"=>array(
                    "tag"=>"Ricambi",
                    "lordo"=>0,
                    "netto"=>0,
                    "costo"=>0
                ),
                "var"=>array(
                    "tag"=>"Vario",
                    "lordo"=>0,
                    "netto"=>0,
                    "costo"=>0
                ),
                "ore"=>array(
                    "tag"=>"Ore",
                    "valore"=>0
                ),
                "pass"=>array(
                    "tag"=>"Doc",
                    "valore"=>0
                ),
                "cont"=>array(
                    "tag"=>"Contatti",
                    "valore"=>0
                )
            ),

            "ext"=>array(
                "r19"=>array(
                    "tag"=>"Lis 1-9",
                    "flag"=>false,
                    "valore"=>0
                ),
                "rv"=>array(
                    "tag"=>"Netto V",
                    "flag"=>false,
                    "valore"=>0
                ),
                "rp"=>array(
                    "tag"=>"Netto P",
                    "flag"=>false,
                    "valore"=>0
                ),
                "inc"=>array(
                    "tag"=>"Incentivo",
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

        );*/

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
                ),
                "cont"=>array(
                    "tag"=>"Contatti",
                    "valore"=>0
                )
            ),

            "ext"=>array(
                "r19"=>array(
                    "tag"=>"Lis 1-9",
                    "flag"=>false,
                    "valore"=>0
                ),
                "rv"=>array(
                    "tag"=>"Netto V",
                    "flag"=>false,
                    "valore"=>0
                ),
                "rp"=>array(
                    "tag"=>"Netto P",
                    "flag"=>false,
                    "valore"=>0
                ),
                "inc"=>array(
                    "tag"=>"Incentivo",
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

    function getGarOpen() {

        $res=0;

        foreach ($this->reparti as $reparto) {

            $this->wh=new c2rWHole($reparto,$this->galileo);
            //OK fine-fine
            $this->wh->build(array('inizio'=>$this->param['fine'],'fine'=>$this->param['fine']));

            //è solo uno
            foreach ($this->wh->exportMap() as $k=>$m) {

                $temp=array(
                    "officina"=>$this->odlFunc->getDmsRep($m['dms'],$reparto),
                    "tipo"=>"aperti",
                    "tipo_carico"=>"'G'"
                );
            
                $m['result']=$this->wh->getGarOpen($m['dms'],$temp);

                if ($m['result']) {
                    $fetID=$this->galileo->preFetchPiattaforma($this->wh->getPiattaforma($m['dms']),$m['result']);

                    $rif="";

                    while ($row=$this->galileo->getFetch('odl',$fetID)) {

                        if ($rif!=$row['rif']) {

                            if ($row['d_documento']<$this->param['fine']) {

                                $giorni=mainFunc::gab_delta_tempo($row['d_documento'],$this->param['fine'],'g');

                                if ($giorni>28) $res++;
                            }
                        }
                    }
                }
            }
        }

        return $res;
    }

    function getLines() {

        foreach ($this->reparti as $reparto) {

            $this->wh=new c2rWHole($reparto,$this->galileo);
            $this->wh->build(array('inizio'=>$this->param['inizio'],'fine'=>$this->param['fine']));

            $p=$this->param;

            foreach ($this->wh->exportMap() as $k=>$m) {

                //$p['reparti']=$this->odlFunc->repStringify($m['dms'],"'".$reparto."',");

                $p['officina']=$this->odlFunc->getDmsRep($m['dms'],$reparto);

                //echo json_encode($p);            

                $m['result']=$this->wh->getFatturatoS($k,$p);

                if ($m['result']) {
                    $fetID=$this->galileo->preFetchPiattaforma($this->wh->getPiattaforma($m['dms']),$m['result']);
                    
                    //###########################
                    //elabora linee estratte

                    $tempodl=0;
                    $tempmodello='99';

                    while ($row=$this->galileo->getFetch('odl',$fetID)) {

                        $row['repartoNebula']=$reparto;

                        //se siamo su concerto recupera il COSTO del ricambio
                        //è stato un tentativo
                        if ($m['dms']=='concerto' && $row['ind_tipo_riga']=="R") {
                            $row['costo']=$this->wh->getCostoConcerto($row)*$row['qta'];
                        }

                        //non dovrebbe essere possibile
                        if ($row['marca_veicolo']=='') $row['marca_veicolo']='ind';

                        if ($t=$this->odlFunc->getAddebito($row,$row['dms'])) {
                            $row['c2rTipo']=$t['tipo'];
                            $row['c2rClasse']=$t['c2rClasse'];
                            $row['c2rGruppo']=$t['gruppo']; 
                        }
                        else {
                            $row['c2rTipo']='ind';
                            $row['c2rGruppo']='';
                            $row['c2rClasse']='ind';

                            $this->indefiniti[]=$row;
                        }

                        ////////////////////////////////
                        $rif_codice="";

                        if ($row['ind_tipo_riga']=="R") $rif_codice=$row['cod_articolo'];
                        elseif ($row['ind_tipo_riga']=="M") $rif_codice=$row['cod_operazione'];
                        elseif ($row['ind_tipo_riga']=="V") $rif_codice=$row['cod_varie'];

                        //if (isset($this->servizi[$row['ind_tipo_riga']][$rif_codice])) $row['c2rClasse']='ser';
                        
                        //modifica in base allo specifico CODICE se esite tra quelli segnalati
                        if (isset($this->gruppi[$row['dms']][$row['c2rTipo']][$row['ind_tipo_riga']][$rif_codice])) {

                            if ($row['d_fatt']>=$this->gruppi[$row['dms']][$row['c2rTipo']][$row['ind_tipo_riga']][$rif_codice]['data_i'] && $row['d_fatt']<=$this->gruppi[$row['dms']][$row['c2rTipo']][$row['ind_tipo_riga']][$rif_codice]['data_f'] ) {
                                if ($this->gruppi[$row['dms']][$row['c2rTipo']][$row['ind_tipo_riga']][$rif_codice]['gruppo']!="") {
                                    $row['c2rGruppo']=$this->gruppi[$row['dms']][$row['c2rTipo']][$row['ind_tipo_riga']][$rif_codice]['gruppo'];
                                }
                                if ($this->gruppi[$row['dms']][$row['c2rTipo']][$row['ind_tipo_riga']][$rif_codice]['classe']!="") {
                                    $row['c2rClasse']=$this->gruppi[$row['dms']][$row['c2rTipo']][$row['ind_tipo_riga']][$rif_codice]['classe'];
                                }
                                if (isset($this->gruppi[$row['dms']][$row['c2rTipo']][$row['ind_tipo_riga']][$rif_codice]['assoluto'])) {
                                    $row['assoluto']=$this->gruppi[$row['dms']][$row['c2rTipo']][$row['ind_tipo_riga']][$rif_codice]['assoluto'];
                                }
                                if (isset($this->gruppi[$row['dms']][$row['c2rTipo']][$row['ind_tipo_riga']][$rif_codice]['riga'])) {
                                    $row['ind_tipo_riga']=$this->gruppi[$row['dms']][$row['c2rTipo']][$row['ind_tipo_riga']][$rif_codice]['riga'];
                                }
                            }
        
                        }
                        ////////////////////////////////

                        //RAGGRUPPO MODELLO
                        if ($row['rif']==$tempodl) {
                            $row['gruppo_modello']=$tempmodello;
                            $row['c2rPass']=0;
                            $row['c2rCont']=0;
                        }
                        else {   
                            //99 è il codice INDEFINITO per ogni marchio
                            //ci sono modelli con 2 cifre significative e modelli con 3 cifre significative
                            //viene prima verificato se esiste l'abbinamento con 3 cifre e poi se esiste l'abbinamento con 3
                            $due=substr($row['modello'],0,2);
                            $tre=substr($row['modello'],0,3);

                            $row['gruppo_modello']='99';
                            $tempmodello='99';
                            $tempodl=$row['rif'];
                            $row['c2rPass']=1;

                            if ($row['modello']!='') {

                                if (isset($this->modelli[$row['marca_veicolo']][$tre])) {
                                    //nel periodo potrebbero esserci più di un raggruppamento per il modello
                                    //i raggruppamenti sono comunque in ordine di anno_i
                                    foreach ($this->modelli[$row['marca_veicolo']][$tre] as $k=>$v) {
                                        if (substr($row['d_fatt'],0,4)<=$v['anno_f']) {
                                            $row['gruppo_modello']=$v['res_modello'];
                                            $tempmodello=$v['res_modello'];
                                        }
                                    }
                                }

                                else if (isset($this->modelli[$row['marca_veicolo']][$due])) {
                                    //nel periodo potrebbero esserci più di un raggruppamento per il modello
                                    //i raggruppamenti sono comunque in ordine di anno_i
                                    foreach ($this->modelli[$row['marca_veicolo']][$due] as $k=>$v) {
                                        if (substr($row['d_fatt'],0,4)<=$v['anno_f']) {
                                            $row['gruppo_modello']=$v['res_modello'];
                                            $tempmodello=$v['res_modello'];
                                        }
                                    }
                                }
                            }

                            //###########################
                            if (in_array($row['mat_telaio'],$this->telai)) {
                                $row['c2rCont']=0;
                            }
                            else {
                                $row['c2rCont']=1;
                                $this->telai[]=$row['mat_telaio'];
                            }
                            //###########################
                        }

                        //###########################

                        foreach ($this->analisi as $ka=>$a) {
                            if ($a['flag']) {
                                call_user_func_array(array($this, 'feed_'.$ka), array($row) );
                            }
                        }
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

    function feed_tot($arr) {

        //se necessario instanziare il BLOCCO dell'analisi
        if (is_null($this->analisi['tot']['blocco'])) {
            $this->analisi['tot']['blocco']=$this->blocco;
            $this->analisi['tot']['blocco']['totale']=new c2rBlocco("",$this->param);
        }
        if (is_null($this->anarep[$arr['repartoNebula']]['tot']['blocco'])) {
            $this->anarep[$arr['repartoNebula']]['tot']['blocco']=$this->blocco;
            $this->anarep[$arr['repartoNebula']]['tot']['blocco']['totale']=new c2rBlocco("",$this->param);
        }

        //se necessario instanziare il blocco della marca specifica
        if (!isset($this->analisi['tot']['blocco']['marche'][$arr['marca_veicolo']]) && $arr['marca_veicolo']!="") {
            $this->analisi['tot']['blocco']['marche'][$arr['marca_veicolo']]=array(
                "totale"=>new c2rBlocco($arr['marca_veicolo'],$this->param),
                "modelli"=>array()
            );
        }
        if (!isset($this->anarep[$arr['repartoNebula']]['tot']['blocco']['marche'][$arr['marca_veicolo']]) && $arr['marca_veicolo']!="") {
            $this->anarep[$arr['repartoNebula']]['tot']['blocco']['marche'][$arr['marca_veicolo']]=array(
                "totale"=>new c2rBlocco($arr['marca_veicolo'],$this->param),
                "modelli"=>array()
            );
        }

        //se necessario instanziare il blocco del modello specifico
        if (!isset($this->analisi['tot']['blocco']['marche'][$arr['marca_veicolo']]['modelli'][$arr['gruppo_modello']]) && $arr['gruppo_modello']!="") {
            $this->analisi['tot']['blocco']['marche'][$arr['marca_veicolo']]['modelli'][$arr['gruppo_modello']]=new c2rBlocco($arr['marca_veicolo'],$this->param);
        }
        if (!isset($this->anarep[$arr['repartoNebula']]['tot']['blocco']['marche'][$arr['marca_veicolo']]['modelli'][$arr['gruppo_modello']]) && $arr['gruppo_modello']!="") {
            $this->anarep[$arr['repartoNebula']]['tot']['blocco']['marche'][$arr['marca_veicolo']]['modelli'][$arr['gruppo_modello']]=new c2rBlocco($arr['marca_veicolo'],$this->param);
        }

        if ($arr['marca_veicolo']!="" && $arr['gruppo_modello']!="") {
            $this->analisi['tot']['blocco']['marche'][$arr['marca_veicolo']]['modelli'][$arr['gruppo_modello']]->feed($arr);
            $this->anarep[$arr['repartoNebula']]['tot']['blocco']['marche'][$arr['marca_veicolo']]['modelli'][$arr['gruppo_modello']]->feed($arr);
        }

        //$this->log[]=$arr;

    }

    function concludi_tot() {

        if (is_null($this->analisi['tot']['blocco'])) return;

        foreach ($this->analisi['tot']['blocco']['marche'] as $marca=>$mk) {

            foreach ($mk['modelli'] as $modello=>$m) {

                $this->analisi['tot']['blocco']['marche'][$marca]['totale']->sum($m->getGrid());

            }

            $this->analisi['tot']['blocco']['totale']->sum($this->analisi['tot']['blocco']['marche'][$marca]['totale']->getGrid());

        }

        foreach ($this->anarep as $reparto=>$a) {

            if (is_null($a['tot']['blocco'])) continue;

            foreach ($this->anarep[$reparto]['tot']['blocco']['marche'] as $marca=>$mk) {

                foreach ($mk['modelli'] as $modello=>$m) {

                    $this->anarep[$reparto]['tot']['blocco']['marche'][$marca]['totale']->sum($m->getGrid());

                }

                $this->anarep[$reparto]['tot']['blocco']['totale']->sum($this->anarep[$reparto]['tot']['blocco']['marche'][$marca]['totale']->getGrid());
            }
        }
    }

    function feed_nlt($arr) {

        //valori validi SOLO per INFINITY
        if ($arr['id_cliente']!=29269 && $arr['id_cliente']!=172257) return;

        //se necessario instanziare il BLOCCO dell'analisi
        if (is_null($this->analisi['nlt']['blocco'])) {
            $this->analisi['nlt']['blocco']=$this->blocco;
            $this->analisi['nlt']['blocco']['totale']=new c2rBlocco("",$this->param);
        }

        //se necessario instanziare il blocco della marca specifica
        if (!isset($this->analisi['nlt']['blocco']['marche'][$arr['marca_veicolo']]) && $arr['marca_veicolo']!="") {
            $this->analisi['nlt']['blocco']['marche'][$arr['marca_veicolo']]=array(
                "totale"=>new c2rBlocco($arr['marca_veicolo'],$this->param)
            );
        }

        if ($arr['marca_veicolo']!="") {
            $this->analisi['nlt']['blocco']['marche'][$arr['marca_veicolo']]['totale']->feed($arr);
        }
    }

    function concludi_nlt() {

        if (is_null($this->analisi['nlt']['blocco'])) return;

        foreach ($this->analisi['nlt']['blocco']['marche'] as $marca=>$mk) {

            //$this->analisi['nlt']['blocco']['marche'][$marca]['totale']->sum($mk['totale']->getGrid());

            $this->analisi['nlt']['blocco']['totale']->sum($this->analisi['nlt']['blocco']['marche'][$marca]['totale']->getGrid());

        }
    }

    function feed_rc($arr) {

        // param {"reparti":"'PV',","marche":"'A','C','N','P','S','V','X',","inizio":"20210701","fine":"20210930","prodTipo":"standard","default":{"tipo":"standard","totali":"true","collab":"false","repcol":"false","responsabile":"false","collaboratore":"134"},"operaio":""}
        if (isset($this->param['default'])) {
            if ($this->param['default']['responsabile']=='false') {
                if (isset($this->collaboratori[$this->param['default']['collaboratore']])) {
                    if (($this->collaboratori[$this->param['default']['collaboratore']]['concerto']!=$arr['cod_utente'])) return;
                }
                else return;
            }
        }

        $param=$this->param;

        $param['flag_inc']=true;

        //se non è un ricambio ritorna (questo controllo vale SOLO per l'incentivazione)
        //if ($arr['ind_tipo_riga']!="R") return;

        //se non è specificato il codice utente ritorna
        if ($arr['cod_utente']=='') return;

        //se necessario instanziare il BLOCCO dell'analisi
        if (is_null($this->analisi['rc']['blocco'])) $this->analisi['rc']['blocco']=array();

        if (!array_key_exists($arr['cod_utente'],$this->analisi['rc']['blocco'])) {
            $this->analisi['rc']['blocco'][$arr['cod_utente']]=$this->blocco;
            $this->analisi['rc']['blocco'][$arr['cod_utente']]['totale']=new c2rBlocco("",$param);
            $this->analisi['rc']['dettaglio'][$arr['cod_utente']]=array();
        }

        //se necessario instanziare il blocco della marca specifica
        if (!isset($this->analisi['rc']['blocco'][$arr['cod_utente']]['marche'][$arr['marca_veicolo']]) && $arr['marca_veicolo']!="") {
            $this->analisi['rc']['blocco'][$arr['cod_utente']]['marche'][$arr['marca_veicolo']]= new c2rBlocco($arr['marca_veicolo'],$param);
        }

        //######################################
        //calcolo valore incentivo ricambi
        if ($arr['c2rTipo']=='pag' && $arr['ind_tipo_riga']=='R') {

            foreach ($this->famiglie as $pre=>$f) {
                if ($pre!=$arr['cod_tipo_articolo']) continue;

                foreach ($f as $tipologia=>$t) {
                    if (!$t['flag']) continue;

                    foreach ($t['periodi'] as $p) {
                        if ($arr['d_fatt']<$p['inizio'] && $arr['d_fatt']>$p['fine']) continue;
                        if (isset($p['escluso'])) {
                            if (in_array($arr["cod_articolo"],$p['escluso'])) continue;
                        }

                        //se non sono specificati gruppi merceologici oppure il gruppo del ricambio è compreso nella lista
                        if (count($p['gr'])==0 || in_array($arr['cod_categoria_vendita'],$p['gr'])) {

                            //se non è specificata alcuna famiglia oppure è compresa nella lista 
                            if (count($p['codici'])==0 || in_array($arr['famiglia'],$p['codici'])) {

                                $temp=array(
                                    "rif"=>$arr['rif'],
                                    "d_fatt"=>$arr['d_fatt'],
                                    "pre"=>$arr['cod_tipo_articolo'],
                                    "articolo"=>$arr['cod_articolo'],
                                    "descrizione"=>$arr['descrizione'],
                                    "gr"=>$arr['cod_categoria_vendita'],
                                    "famiglia"=>$arr['famiglia'],
                                    "tipologia"=>$tipologia,
                                    "qta"=>$arr['qta'],
                                    "listino"=>$arr['listino'],
                                    "sconto"=>$arr['prc_sconto'],
                                    "importo"=>$arr['importo'],
                                    "costo"=>$arr['costo'],
                                    "margine"=>$arr['importo']-$arr['costo']
                                );

                                if ($temp['costo']<=0 && $temp['gr']=='T') {
                                    $temp['costo']=$temp['listino']*$temp['qta']*0.45;
                                    $temp['margine']=$arr['importo']-$temp['costo'];
                                    $temp['flag_costo']=true;
                                }
                                elseif ($temp['costo']<=0) {
                                    $temp['costo']=$temp['listino']*$temp['qta']*0.35;
                                    $temp['margine']=$arr['importo']-$temp['costo'];
                                    $temp['flag_costo']=true;
                                }

                                $this->analisi['rc']['dettaglio'][$arr['cod_utente']][]=$temp;

                                if ($temp['margine']>0) {
                                    $arr['valore_incentivo']=$temp['margine'];
                                }
                            }

                        }
                    }
                }
            }

        }
        //######################################

        $this->analisi['rc']['blocco'][$arr['cod_utente']]['marche'][$arr['marca_veicolo']]->feed($arr);
        
    }

    function concludi_rc() {

        if (is_null($this->analisi['rc']['blocco'])) return;

        foreach ($this->analisi['rc']['blocco'] as $utente=>$u) {

            foreach ($u['marche'] as $marca=>$m) {

                $this->analisi['rc']['blocco'][$utente]['totale']->sum($m->getGrid());

            }

            //$this->analisi['rc']['blocco'][$utente]['totale']->sum($this->analisi['rc']['blocco'][$utente]['totale']->getGrid());

        }

    }

    function draw() {

        /*foreach ($this->log as $l) {
            echo '<div>';
                echo $l;
            echo '</div>';

            break;
        }*/

        BlockList::blockListInit();

        //new DIVO 1
        //($index,$htab,$minh,$fixed)
        $divo1=new Divo('c2rAnalisi','5%','97%',1);
        $divo1->setBk('#dac59d');

        //scrivi i div delle analisi
        foreach ($this->analisi as $analisi=>$a) {

            if (!$a['flag']) continue;

            $txt='<div>';

                //scrivi DIVO delle analisi
                if ($analisi=='tot') {
                    $txt.=$this->drawTot();
                }
                else if ($analisi=='nlt') {
                    $txt.=$this->drawNlt();
                }
                else if ($analisi=='rc') {
                    $txt.=$this->drawRc();
                }
                
            $txt.='</div>';

            //add DIV
            //add_div($titolo,$color,$chk,$stato,$codice,$selected,$css)
            $divo1->add_div($a['tag'],'black',0,'',$txt,($analisi=='tot'?1:0),array());
        }

        $divo1->add_div('Reparti','black',0,'',$this->drawReparti(),0,array());

        $divo1->build();
        $divo1->draw();
        unset($divo1);

        //echo '<div>www'.json_encode($this->param).'</div>';

        //echo json_encode($this->log);
    }

    function drawReparti() {

        $divoR=new Divo('c2rReparti','4%','96%',1);
        $divoR->setBk('#a8b4ce');

        foreach ($this->anarep as $reparto=>$r) {

            $txt='<div>';

                ob_start();

                if (!is_null($r['tot']['blocco']) && !is_null($r['tot']['blocco']['totale'])) {
                    $r['tot']['blocco']['totale']->draw();
                }

                $txt.=ob_get_clean();
            $txt.='</div>';

            $divoR->add_div($reparto,'black',0,'',$txt,0,array());
        }

        $divoR->build();

        ob_start();
            $divoR->draw();
            unset($divoR);
        return ob_get_clean();
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
        if (!is_null($this->analisi['tot']['blocco']) && !is_null($this->analisi['tot']['blocco']['totale'])) {
            $this->analisi['tot']['blocco']['totale']->loadIndefiniti($this->indefiniti);
        }

        $txt='<div>';

            ob_start();

            if (!is_null($this->analisi['tot']['blocco']) && !is_null($this->analisi['tot']['blocco']['totale'])) {
                $this->analisi['tot']['blocco']['totale']->draw();
            }

            $txt.=ob_get_clean();
        $txt.='</div>';

        $divo2->add_div('TOT','black',0,'',$txt,1,$css);
        //###############################################
        
        if (!is_null($this->analisi['tot']['blocco']) && !is_null($this->analisi['tot']['blocco']['marche'])) {
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
        }

        $divo2->build();

        ob_start();
            $divo2->draw();
            unset($divo2);
        return ob_get_clean();

    }

    function drawRc() {

        if (is_null($this->analisi['rc']['blocco'])) return "";

        $divo2=new Divo('c2rRCfatt','4%','96%',1);
        $divo2->setBk('#a8b4ce');

        $css=array(
            "font-weight"=>"bold",
            "margin-top"=>"0px",
            "font-size"=>"0.8em",
            "text-align"=>"center"
        );

        foreach ($this->analisi['rc']['blocco'] as $utente=>$u) {

            $divo3=new Divo('c2rRC_fatt_'.str_replace('.','',$utente),'4%','96%',1);
            $divo3->setBk('#a8cead');

            //##############################################
            //scrittura del DIV per il totale generale in DIVO2
            $txt='<div>';
                ob_start();
                    $u['totale']->draw();
                $txt.=ob_get_clean();
            $txt.='</div>';

            $divo3->add_div('TOT','black',0,'',$txt,1,$css);
            //###############################################

            foreach ($u['marche'] as $marca=>$m) {

                ob_start();
                    $m->draw();
                $divo3->add_div($marca,'black',0,'',ob_get_clean(),0,$css);
            }

            $txt='<div>';
                //$txt.=json_encode($this->analisi['rc']['dettaglio'][$utente]);

                $tprimo=true;

                $txt.='<table style="width:95%;border-collapse:collapse;font-size:0.8em;text-align:center;">';
                
                    foreach ($this->analisi['rc']['dettaglio'][$utente] as $d) {

                        /*
                        "rif"=>$arr['rif'],
                        "d_fatt"=>$arr['d_fatt'],
                        "pre"=>$arr['cod_tipo_articolo'],
                        "articolo"=>$arr['cod_articolo'],
                        "descrizione"=>$arr['descrizione'],
                        "gr"=>$arr['cod_categoria_vendita'],
                        "famiglia"=>$arr['famiglia'],
                        "tipologia"=>$tipologia,
                        "qta"=>$arr['qta'],
                        "listino"=>$arr['listino'],
                        "sconto"=>$arr['prc_sconto'],
                        "importo"=>$arr['importo'],
                        "costo"=>$arr['costo'],
                        "margine"=>$arr['importo']-$arr['costo']
                        */

                        if ($tprimo) {
                            $txt.='<tr>';
                                foreach ($d as $k=>$v) {
                                    if ($k=='flag_costo') continue;
                                    $txt.='<th style="">'.$k.'</th>';
                                }
                            $txt.='</tr>';

                            $tprimo=false;
                        }

                        $txt.='<tr>';
                            /*foreach ($d as $k=>$v) {
                                $txt.='<td>'.$v.'</td>';
                            }*/
                            $txt.='<td style="width:60px;">'.$d['rif'].'</td>';
                            $txt.='<td style="width:80px;">'.mainFunc::gab_todata($d['d_fatt']).'</td>';
                            $txt.='<td style="width:10px;">'.$d['pre'].'</td>';
                            $txt.='<td style="width:120px;">'.$d['articolo'].'</td>';
                            $txt.='<td style="width:120px;">'.substr($d['descrizione'],0,15).'</td>';
                            $txt.='<td style="width:10px;">'.$d['gr'].'</td>';
                            $txt.='<td style="width:40px;">'.$d['famiglia'].'</td>';
                            $txt.='<td style="width:60px;">'.$d['tipologia'].'</td>';
                            $txt.='<td style="width:40px;">'.number_format($d['qta'],2,',','').'</td>';
                            $txt.='<td style="width:60px;">'.number_format($d['listino'],2,',','').'</td>';
                            $txt.='<td style="width:50px;">'.number_format($d['sconto'],2,',','').'%</td>';
                            $txt.='<td style="width:60px;">'.number_format($d['importo'],2,',','').'</td>';
                            $txt.='<td style="width:60px;'.(isset($d['flag_costo'])?"color:violet;":"").'">'.number_format($d['costo'],2,',','').'</td>';
                            $txt.='<td style="width:60px;">'.number_format($d['margine'],2,',','').'</td>';
                        $txt.='</tr>';
                    }

                $txt.='</table>';
            $txt.='</div>';

            $divo3->add_div('Dett.Inc.','black',0,'',$txt,0,$css);

            $txt='<div>';
                $divo3->build();
                ob_start();
                    $divo3->draw();
                $txt.=ob_get_clean();
            $txt.='</div>';

            $divo2->add_div(substr($utente,0,9),'black',0,'',$txt,0,$css); 

        }

        $divo2->build();

        ob_start();
            $divo2->draw();
            unset($divo2);
        return ob_get_clean();
        
    }

    function drawNlt() {

        if (is_null($this->analisi['nlt']['blocco'])) return "";

        $divo2=new Divo('c2NltMarche','4%','96%',1);
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

            if (!is_null($this->analisi['nlt']['blocco']) && !is_null($this->analisi['nlt']['blocco']['totale'])) {
                $this->analisi['nlt']['blocco']['totale']->draw();
            }

            $txt.=ob_get_clean();
        $txt.='</div>';

        $divo2->add_div('TOT','black',0,'',$txt,1,$css);
        //###############################################
        
        if (!is_null($this->analisi['nlt']['blocco']) && !is_null($this->analisi['nlt']['blocco']['marche'])) {
            ksort($this->analisi['nlt']['blocco']['marche']);

            foreach ($this->analisi['nlt']['blocco']['marche'] as $marca=>$m) {

                //##############################################
                //scrittura del DIV per il totale di marca in DIVO3
                $t3='<div>';
                    ob_start();
                        $m['totale']->draw();
                    $t3.=ob_get_clean();
                $t3.='</div>';

                $divo2->add_div($marca,'black',0,'',$t3,0,$css);
            }
        }

        $divo2->build();

        ob_start();
            $divo2->draw();
            unset($divo2);
        return ob_get_clean();
        
    }

    //======================================================

    function getValRc($coll,$tipo,$indice) {
        //coll , ext , inc

        if ($this->analisi['rc']['flag']) {

            if (array_key_exists($coll,$this->collaboratori)) {
                $utente=$this->collaboratori[$coll]['concerto'];
            }
            else return false;

            if (isset($this->analisi['rc']['blocco'][$utente])) {

                if ($tipo=='ext') {
                    return $this->analisi['rc']['blocco'][$utente]['totale']->getTotaloneVal($tipo,$indice,'');
                }
                else return false;
            }
            else return false;
        }

        else return false;
    }

    function getValRcs($colls,$tipo,$gruppo,$indice,$valore) {
        //coll(array) , int , pag

        //getTotVal($indice,$valore) {
        //indice= man - ric - var
        //valore= lordo - netto - costo

        if ($this->analisi['rc']['flag']) {

            $res=false;

            foreach ($colls as $k=>$c) {

                /*if (array_key_exists($c,$this->collaboratori)) {
                    $utente=$this->collaboratori[$c]['concerto'];
                }
                else continue;*/
                
                if (isset($this->analisi['rc']['blocco'][$c])) {

                    if (!$res) $res=0;

                    if ($tipo=='int') {
                        $res+=$this->analisi['rc']['blocco'][$c]['totale']->getTotTipo('tot',$gruppo,$indice,$valore);
                    }
                }
                else continue;
            }
        }

        return $res;
    }

    function getTotStd($classe,$tipo,$indice,$valore) {

        //classe= mec - car - gom ....
        //tipo=  pag - gar - int
        //indice= man - ric - var
        //valore= lordo - netto - costo

        return $this->analisi['tot']['blocco']['totale']->getTotTipo($classe,$tipo,$indice,$valore);
    }

}

?>