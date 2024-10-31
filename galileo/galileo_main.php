<?php
include_once(DROOT.'/nebula/galileo/maestro.php');
include_once(DROOT.'/nebula/galileo/gab500.php');
include_once(DROOT.'/nebula/galileo/solari.php');
include_once(DROOT.'/nebula/galileo/rocket.php');
//classe per collegamento ad infinity
//include(rocket.php');

include_once(DROOT.'/nebula/galileo/galileo_ops.php');
include_once(DROOT.'/nebula/galileo/galileo_tab.php');

class galileoMain {

    protected $funzioniBase=array(
        "utenti"=>array("piattaforma"=>"","oggetto"=>null),
        "reparti"=>array("piattaforma"=>"","oggetto"=>null),
        "applicazioni"=>array("piattaforma"=>"","oggetto"=>null),
        "maestro"=>array("piattaforma"=>"","oggetto"=>null),
        "calendario"=>array("piattaforma"=>"","oggetto"=>null),
        "schemi"=>array("piattaforma"=>"","oggetto"=>null),
        "badge"=>array("piattaforma"=>"","oggetto"=>null),
        "avalon"=>array("piattaforma"=>"","oggetto"=>null)
    );

    protected $funzioniDefault=array(
        "tempo"=>array("piattaforma"=>"","oggetto"=>null),
        "qcheck"=>array("piattaforma"=>"","oggetto"=>null),
        "odl"=>array("piattaforma"=>"","oggetto"=>null),
        "alert"=>array("piattaforma"=>"","oggetto"=>null),
        "anagra"=>array("piattaforma"=>"","oggetto"=>null),
        "veicoli"=>array("piattaforma"=>"","oggetto"=>null),
        "ricambi"=>array("piattaforma"=>"","oggetto"=>null),
        "centavos"=>array("piattaforma"=>"","oggetto"=>null),
        "croom"=>array("piattaforma"=>"","oggetto"=>null),
        "alan"=>array("piattaforma"=>"","oggetto"=>null),
        "gdm"=>array("piattaforma"=>"","oggetto"=>null),
        "carb"=>array("piattaforma"=>"","oggetto"=>null),
        "grent"=>array("piattaforma"=>"","oggetto"=>null),
        "comest"=>array("piattaforma"=>"","oggetto"=>null),
        "ermes"=>array("piattaforma"=>"","oggetto"=>null),
        "chain"=>array("piattaforma"=>"","oggetto"=>null),
        "dudu"=>array("piattaforma"=>"","oggetto"=>null),
        "fidel"=>array("piattaforma"=>"","oggetto"=>null),
        "strillo"=>array("piattaforma"=>"","oggetto"=>null)
    );

    protected $query;
    //query per transaction
    protected $arrQuery=array();
    protected $result;
    //valori ritornati da executeGeneric (transaction)
    protected $resVar=array();

    protected $piattaforma=array(
        'maestro'=>null,
        'gab500'=>null,
        'rocket'=>null,
        'solari'=>null
    );

    //setta se eseguire la query in una transaction
    protected $transaction=false;
    //setta la forzatura del rollback
    protected $forceRollback=false; 

    protected $log=array(
        "query"=>array(),
        "errori"=>array(),
        "dberror"=>array()
    );

    function __construct($base) {

        /*$this->piattaforma['maestro']=new Maestro();
        $this->piattaforma['gab500']=new Gab500();
        $this->piattaforma['solari']=new Solari();
        $this->piattaforma['rocket']=new Rocket();
        */

        foreach ($this->funzioniBase as $k=>$v) {
            if ( array_key_exists($k,$base) ) {
                $this->funzioniBase[$k]['piattaforma']=$base[$k][0];
                $this->funzioniBase[$k]['oggetto']=$base[$k][1];
            }
        }
        
    }

    function setHandler($piattaforma) {

        if (!$this->piattaforma[$piattaforma]) {
            switch($piattaforma) {
                case 'maestro': $this->piattaforma['maestro']=new Maestro();break;
                case 'gab500': $this->piattaforma['gab500']=new Gab500();break;
                case 'solari': $this->piattaforma['solari']=new Solari();break;
                case 'rocket': $this->piattaforma['rocket']=new Rocket();break;
            }
        }
    }

    function executeQuery($ambito,$tipo) {

        $eschr=array(
            '\n',
            '\r',
            '\t'
        );

        if ($ambito=='default') {

            $this->setHandler($this->funzioniDefault[$tipo]['piattaforma']);
            /*if (!$this->piattaforma[$this->funzioniDefault[$tipo]['piattaforma']]) {
                switch($this->funzioniDefault[$tipo]['piattaforma']) {
                    case 'maestro': $this->piattaforma['maestro']=new Maestro();break;
                    case 'gab500': $this->piattaforma['gab500']=new Gab500();break;
                    case 'solari': $this->piattaforma['solari']=new Solari();break;
                    case 'rocket': $this->piattaforma['rocket']=new Rocket();break;
                }
            }*/

            return $this->piattaforma[$this->funzioniDefault[$tipo]['piattaforma']]->query(str_replace($eschr, '', $this->query));
            //return $this->piattaforma[$this->funzioniDefault[$tipo]['piattaforma']]->query($this->query);
        }

        if ($ambito=='base') {

            $this->setHandler($this->funzioniBase[$tipo]['piattaforma']);
            /*if (!$this->piattaforma[$this->funzioniBase[$tipo]['piattaforma']]) {
                switch($this->funzioniBase[$tipo]['piattaforma']) {
                    case 'maestro': $this->piattaforma['maestro']=new Maestro();break;
                    case 'gab500': 
                        $this->piattaforma['gab500']=new Gab500();
                    break;
                    case 'solari': $this->piattaforma['solari']=new Solari();break;
                    case 'rocket': $this->piattaforma['rocket']=new Rocket();break;
                }
            }*/

            return $this->piattaforma[$this->funzioniBase[$tipo]['piattaforma']]->query(str_replace($eschr, '', $this->query));
        }

    }

    function getObjectBase($nome) {
        return $this->funzioniBase[$nome]['oggetto'];
    }

    function checkHandler($piattaforma) {

        $this->setHandler($piattaforma);
        return $this->piattaforma[$piattaforma]->get_handler();
    }

    function resetHandler($piattaforma) {
        if($this->piattaforma[$piattaforma]) {
            $this->piattaforma[$piattaforma]->reset();
        }
    }

    function closeHandler($piattaforma) {
        if($this->piattaforma[$piattaforma]) {
            $this->piattaforma[$piattaforma]->close();
        }
        $this->piattaforma[$piattaforma]=null;
    }

    function freeHandler($piattaforma,$id) {
        $this->piattaforma[$piattaforma]->free($id);
    }

    function getPiattaforma($ambito,$tipo) {

        if ($ambito=='default') {
            return $this->funzioniDefault[$tipo]['piattaforma'];
        }
        if ($ambito=='base') {
            return $this->funzioniBase[$tipo]['piattaforma'];
        }
    }

    function clearQuery() {
        $this->query="";
    }

    function clearQueryOggetto($ambito,$tipo) {

        if ($ambito=='default') {
            $this->funzioniDefault[$tipo]['oggetto']->clearQuery();
        }
        if ($ambito=='base') {
            $this->funzioniBase[$tipo]['oggetto']->clearQuery();
        }
        
    }

    function disableIncrement($tipo,$tabella) {
        //disabilita la funzione AUTO INCREMENT dalla tabella
        //16.05.2022 l'ho utilizzato con una tabella dove ho utilizzato UPSERT ma in alcuni casi
        //mi serviva sapere l'ID del nuovo record
        $res=$this->funzioniDefault[$tipo]['oggetto']->disableIncrement($tabella);

        return $res;
    }

    function getLog($id) {
        return $this->log[$id];
    }

    function getOpLog($tipo,$id) {
        $this->funzioniDefault[$tipo]['oggetto']->getTabErrors();
        return $this->funzioniDefault[$tipo]['oggetto']->getLog($id);
    }

    function addLog($id,$str) {
        $this->log[$id][]=trim(preg_replace('/\s\s+/', ' ', $str));
    }

    function clearLog() {
        $this->log=array(
            "query"=>array(),
            "errori"=>array(),
            "dberror"=>array()
        );
    }

    function getResult() {
        //return ($this->result)?true:false;
        return $this->result;
    }

    function setResult($v) {
        return $this->result=$v;
    }

    function getResvar() {
        return $this->resVar;
    }

    function getFunzioniDefault() {
        //esporta la lista delle funzioni definite
        return $this->funzioniDefault;
    }

    function setFunzioniDefault($arr) {

        foreach ($this->funzioniDefault as $k=>$v) {
            if ( array_key_exists($k,$arr) ) {
                $this->funzioniDefault[$k]['piattaforma']=$arr[$k][0];
                $this->funzioniDefault[$k]['oggetto']=$arr[$k][1];
            }
        }

    }

    function setTransaction($opt) {
        $this->transaction=$opt;
    }

    function setRollback($opt) {
        $this->forceRollback=$opt;
    }

    function executeClear($tipo) {
        //azzera la query dell'oggetto
        $this->funzioniDefault[$tipo]['oggetto']->clearQuery();
    }

    function executeGeneric($tipo,$funzione,$args,$order) {

        //nel caso di una select con join che viene specificata a livello di oggetto OPS
        //wClause puo' essere specificata all'interno di $args se necessario

        $this->funzioniDefault[$tipo]['oggetto']->setOrderby($order);
        $res=$this->funzioniDefault[$tipo]['oggetto']->callGeneric($funzione,$args);

        //se ci sono stati degli errori nella costruzione della query
        if (!$res) {
            $this->log['errori'][]=$this->funzioniDefault[$tipo]['oggetto']->getLog('errori');
            return $res;
        }
        /////////////////////////////////////////////////////////////

        if ($this->transaction) {  
            $this->resVar=array();
            $this->arrQuery=$this->funzioniDefault[$tipo]['oggetto']->getArrquery();
            $this->log['query'][]=$this->arrQuery;
            $res=$this->executeTransaction($tipo);

            //$this->log['query'][]=$this->funzioniDefault[$tipo]['oggetto']->getIncsetTab();

        }
        else {
            $this->query=$this->funzioniDefault[$tipo]['oggetto']->getQuery();
            $this->addLog('query',$this->query);
            //$this->result=$this->piattaforma[$this->funzioniDefault[$tipo]['piattaforma']]->query($this->query);
            try {
                $this->result=$this->executeQuery('default',$tipo);
            } catch (Exception $e) {
                // Gestisci l'eccezione catturata
                die('Si è verificato un errore: ' . $e->getMessage());
            }

            if (!$this->result) $this->addLog('dberror',$this->query);
        }

        return $res;
    }

    function executeCount($tipo,$tabella,$wclause) {
        $this->query=$this->funzioniDefault[$tipo]['oggetto']->getCount($tabella,$wclause);
        $this->addLog('query',$this->query);

        //$this->result=$this->piattaforma[$this->funzioniDefault[$tipo]['piattaforma']]->query($this->query);
        $this->result=$this->executeQuery('default',$tipo);
        if (!$this->result) $this->addLog('dberror',$this->query);
    }

    function executeSelect($tipo,$tabella,$wclause,$order) {

        $this->funzioniDefault[$tipo]['oggetto']->setOrderby($order);
        $this->query=$this->funzioniDefault[$tipo]['oggetto']->getSelect($tabella,$wclause);
        $this->addLog('query',$this->query);

        //if ($tabella=='OT2_gruppi') die ($this->query);

        //$this->result=$this->piattaforma[$this->funzioniDefault[$tipo]['piattaforma']]->query($this->query);
        $this->result=$this->executeQuery('default',$tipo);
        if (!$this->result) $this->addLog('dberror',$this->query);
    }

    function executeUpdate($tipo,$tabella,$arr,$wclause) {

        if ($this->transaction) $accodamento="";
        else $accodamento='query';

        $res=$this->funzioniDefault[$tipo]['oggetto']->doUpdate($tabella,$arr,$wclause,$accodamento);

        //se ci sono stati degli errori nella costruzione della query
        if (!$res) {
            $this->log['errori']=$this->funzioniDefault[$tipo]['oggetto']->getLog('errori');
            return $res;
        }
        /////////////////////////////////////////////////////////////

        if ($this->transaction) {
            $this->resVar=array();
            $this->arrQuery=$this->funzioniDefault[$tipo]['oggetto']->getArrquery();
            $this->log['query'][]=$this->arrQuery;
            $this->executeTransaction($tipo);
        }
        else {
            $this->query=$this->funzioniDefault[$tipo]['oggetto']->getQuery();
            $this->addLog('query',$this->query);
            //$this->result=$this->piattaforma[$this->funzioniDefault[$tipo]['piattaforma']]->query($this->query);
            $this->result=$this->executeQuery('default',$tipo);
            if (!$this->result) $this->addLog('dberror',$this->query);
        }

        return $res;
    }

    function executeInsert($tipo,$tabella,$arr) {

        if ($this->transaction) $accodamento="";
        else $accodamento='query';

        $res=$this->funzioniDefault[$tipo]['oggetto']->doInsert($tabella,$arr,"",$accodamento);

        //se ci sono stati degli errori nella costruzione della query
        if (!$res) {
            $this->funzioniDefault[$tipo]['oggetto']->getTabErrors();
            $this->log['errori']=$this->funzioniDefault[$tipo]['oggetto']->getLog('errori');
            return $res;
        }
        /////////////////////////////////////////////////////////////

        if ($this->transaction) {
            $this->resVar=array();
            $this->arrQuery=$this->funzioniDefault[$tipo]['oggetto']->getArrquery();
            $this->log['query'][]=$this->arrQuery;
            $this->executeTransaction($tipo);
        }
        else {
            $this->query=$this->funzioniDefault[$tipo]['oggetto']->getQuery();
            $this->addLog('query',$this->query);
            //$this->result=$this->piattaforma[$this->funzioniDefault[$tipo]['piattaforma']]->query($this->query);
            $this->result=$this->executeQuery('default',$tipo);
            if (!$this->result) $this->addLog('dberror',$this->query);
        }

        return $res;
    }

    function executeNext($tipo,$tabella) {
        //restituisce il prossimo ID in una tabella dove è definito "Increment"
        $this->clearQueryOggetto('default',$tipo);
        $res=$this->funzioniDefault[$tipo]['oggetto']->doNext($tabella);

        if (!$res) return $res;

        $this->query=$res;
        //$this->result=$this->piattaforma[$this->funzioniDefault[$tipo]['piattaforma']]->query($res);
        $this->result=$this->executeQuery('default',$tipo);
        $fid=$this->preFetch($tipo);
        $ret=false;

        while ($row=$this->getFetch($tipo,$fid)) {
            $ret=$row['next_increment'];
        }

        return $ret;
    }

    function executeUpsert($tipo,$tabella,$arr,$wclause) {

        $check=substr($this->funzioniDefault[$tipo]['oggetto']->getSelect($tabella,$wclause),0,-1);

        $this->clearQueryOggetto('default',$tipo);
        $res=$this->funzioniDefault[$tipo]['oggetto']->doInsert($tabella,$arr,"","query");

        if (!$res) {
            $this->log['errori']=$this->funzioniDefault[$tipo]['oggetto']->getLog('errori');
            return $res;
        }
        $insert=substr($this->funzioniDefault[$tipo]['oggetto']->getQuery(),0,-1);

        $this->clearQueryOggetto('default',$tipo);
        $res=$this->funzioniDefault[$tipo]['oggetto']->doUpdate($tabella,$arr,$wclause,"query");
        if (!$res) {
            $this->log['errori']=$this->funzioniDefault[$tipo]['oggetto']->getLog('errori');
            return $res;
        }
        $update=substr($this->funzioniDefault[$tipo]['oggetto']->getQuery(),0,-1);

        //////////////////////////////////////////////////////////////////////////
        $this->query="IF NOT EXISTS (".$check.") ".$insert.' ELSE '.$update.';';

        $this->addLog('query',$this->query);
        //$this->result=$this->piattaforma[$this->funzioniDefault[$tipo]['piattaforma']]->query($this->query);
        $this->result=$this->executeQuery('default',$tipo);
        if (!$this->result) $this->addLog('dberror',$this->query);

        return $res;
    }

    function executeDelete($tipo,$tabella,$wclause) {

        if (!isset($wclause) || $wclause=='') return false;

        if ($this->transaction) $accodamento="";
        else $accodamento='query';

        $res=$this->funzioniDefault[$tipo]['oggetto']->doDelete($tabella,$wclause,$accodamento);

        if ($this->transaction) {
            $this->resVar=array();
            $this->arrQuery=$this->funzioniDefault[$tipo]['oggetto']->getArrquery();
            $this->log['query'][]=$this->arrQuery;
            $this->executeTransaction($tipo);
        }
        else {
            $this->query=$this->funzioniDefault[$tipo]['oggetto']->getQuery();
            $this->addLog('query',$this->query);
            //$this->result=$this->piattaforma[$this->funzioniDefault[$tipo]['piattaforma']]->query($this->query);
            $this->result=$this->executeQuery('default',$tipo);
            if (!$this->result) $this->addLog('dberror',$this->query);
        }

        return $res;
    }

    function executeTransaction($tipo) {

        $this->setHandler($this->funzioniDefault[$tipo]['piattaforma']);

        $this->piattaforma[$this->funzioniDefault[$tipo]['piattaforma']]->transaction_begin();

        $stmt=true;

        foreach ($this->arrQuery as $aq) {
            //$this->result=$this->piattaforma[$this->funzioniDefault[$tipo]['piattaforma']]->query($aq);
            $this->query=$aq;
            $this->result=$this->executeQuery('default',$tipo);
            $this->addLog('query',$aq);

            if (!$this->result) {
                $stmt=false;
                break;
            }

            $fetID=$this->preFetch($tipo);

            while ( $row=$this->piattaforma[$this->funzioniDefault[$tipo]['piattaforma']]->getFetch($fetID) ) {

                //k e v derivano da SELECT ad hoc  
                if (isset($row['k']) && isset($row['v'])) {
                    $this->resVar[$row['k']]=$row['v'];
                }
                //al posto di k e v la SELECT può restituire una serie di RECORD
                //ATTENZIONE!!! in questo caso RESVAR NON è associativo
                else {
                    $this->resVar[]=$row;
                }
            }    
        }

        if ($stmt) {

			if ($this->forceRollback) {
				$this->piattaforma[$this->funzioniDefault[$tipo]['piattaforma']]->transaction_rollback();
                return false;
			}
			else {

                $this->piattaforma[$this->funzioniDefault[$tipo]['piattaforma']]->transaction_commit();
                return true;

                /*while ($row=sqlsrv_fetch_array($this->result,SQLSRV_FETCH_ASSOC)) {
                    $this->resVar[$row['k']]=$row['v'];
                }*/

                /*while ( $row=$this->piattaforma[$this->funzioniDefault[$tipo]['piattaforma']]->getFetch() ) {
                    echo json_encode($row);
                    $this->resVar[$row['k']]=$row['v'];
                }*/	
			}
		}
		else {
			$this->piattaforma[$this->funzioniDefault[$tipo]['piattaforma']]->transaction_rollback();
            return false;
		}
    }

    function baseTransaction($tipo,$arr) {

        $this->setHandler($this->funzioniBase[$tipo]['piattaforma']);
        
        $this->piattaforma[$this->funzioniBase[$tipo]['piattaforma']]->transaction_begin();

        $stmt=true;

        foreach ($arr as $aq) {
            //$this->result=$this->piattaforma[$this->funzioniBase[$tipo]['piattaforma']]->query($aq);
            $this->query=$aq;
            $this->result=$this->executeQuery('base',$tipo);
            $this->addLog('query',$aq);

            if (!$this->result) {
                $stmt=false;
                break;
            }

            $fetID=$this->preFetchBase($tipo);

            //nel caso ci siano valori che la transaction restituisce
            while ( $row=$this->piattaforma[$this->funzioniBase[$tipo]['piattaforma']]->getFetch($fetID) ) {

                //k e v derivano da SELECT ad oc
                if (isset($row['k']) && isset($row['v'])) {
                    $this->resVar[$row['k']]=$row['v'];
                }
                 //al posto di k e v la SELECT può restituire una serie di RECORD
                //ATTENZIONE!!! in questo caso RESVAR NON è associativo
                else {
                    $this->resVar[]=$row;
                }

            }
        }

        if ($stmt) {

			if ($this->forceRollback) {
				$this->piattaforma[$this->funzioniBase[$tipo]['piattaforma']]->transaction_rollback();
			}
			else {
                $this->piattaforma[$this->funzioniBase[$tipo]['piattaforma']]->transaction_commit();
			}
		}
		else {
			$this->piattaforma[$this->funzioniBase[$tipo]['piattaforma']]->transaction_rollback();
		}

        return $stmt;
    }

    function preFetchPiattaforma($piattaforma,$result) {
        $this->setHandler($piattaforma);
        return $this->piattaforma[$piattaforma]->loadResult($result);
    }

    function getFetchPiattaforma($piattaforma,$index) {
        return $this->piattaforma[$piattaforma]->getFetch($index);
    }

    function preFetchBase($tipo) {
        $this->setHandler($this->funzioniBase[$tipo]['piattaforma']);
        return $this->piattaforma[$this->funzioniBase[$tipo]['piattaforma']]->loadResult($this->result);
    }

    function getFetchBase($tipo,$index) {
        //esegue il fetch del result in base al DB della piattaforma
        return $this->piattaforma[$this->funzioniBase[$tipo]['piattaforma']]->getFetch($index);
    }

    function preFetch($tipo) {
        $this->setHandler($this->funzioniDefault[$tipo]['piattaforma']);
        return $this->piattaforma[$this->funzioniDefault[$tipo]['piattaforma']]->loadResult($this->result);
    }

    function getFetch($tipo,$index) {
        //esegue il fetch del result in base al DB della piattaforma
        return $this->piattaforma[$this->funzioniDefault[$tipo]['piattaforma']]->getFetch($index);
    }

    //################################################

    //usata per il LOGIN dal database utenti di CONCERTO (DEPRECATO)
    function getUtenti() {

        //utilizza la funzione di base UTENTI
        //$this->query=$this->funzioniBase['utenti']['oggetto']->getSelect('TB_GEN_ANAUTE',"ind_annullo='N'");
        $this->query=$this->funzioniBase['utenti']['oggetto']->getSelect('TB_GEN_ANAUTE',"");
        //$this->query="SELECT * FROM TB_GEN_ANAUTE WHERE ind_annullo='N'";
        $this->addLog('query',$this->query);

        //$this->result=$this->piattaforma[$this->funzioniBase['utenti']['piattaforma']]->query($this->query);
        $this->result=$this->executeQuery('base','utenti');
        if (!$this->result) $this->addLog('dberror',$this->query);
    }

    function getLogin() {

        $this->query=$this->funzioniBase['maestro']['oggetto']->getLogin();
        $this->addLog('query',$this->query);

        //$this->result=$this->piattaforma[$this->funzioniBase['maestro']['piattaforma']]->query($this->query);
        $this->result=$this->executeQuery('base','maestro');
        if (!$this->result) $this->addLog('dberror',$this->query);
    }

    function getConfigUtente($id,$d) {
        //individua la configurazione di un utente in una certa data
        //utilizza la funzione di base UTENTI
        $this->funzioniBase['maestro']['oggetto']->getConfigUtente($id,$d);
        $this->query=$this->funzioniBase['maestro']['oggetto']->getQuery();
        $this->addLog('query',$this->query);

        //$this->result=$this->piattaforma[$this->funzioniBase['maestro']['piattaforma']]->query($this->query);
        $this->result=$this->executeQuery('base','maestro');
        if (!$this->result) $this->addLog('dberror',$this->query);

    }

    function getMaestroCollab($wc) {

        $wclause=($wc!="")?$wc:"";

        $this->funzioniBase['maestro']['oggetto']->getSelect('MAESTRO_collaboratori',$wclause);
        $this->query=$this->funzioniBase['maestro']['oggetto']->getQuery();
        $this->addLog('query',$this->query);

        //$this->result=$this->piattaforma[$this->funzioniBase['maestro']['piattaforma']]->query($this->query);
        $this->result=$this->executeQuery('base','maestro');
        if (!$this->result) $this->addLog('dberror',$this->query);

    }

    function getCollaboratori($wflag,$flag,$d) {
        //ritorna tutti i collaboratori di un reparto o di un macroreparto (in base alla $wcflag)
        //in una certa data

        $wclause="";

        if ($wflag=='reparto') $wclause="rep.reparto='".$flag."'";
        if ($wflag=='macroreparto') $wclause="rep.macroreparto='".$flag."'";

        $this->funzioniBase['maestro']['oggetto']->setOrderBy('rep.macroreparto,rep.reparto,gru.posizione,coll.cognome');
        $this->funzioniBase['maestro']['oggetto']->getCollaboratori($wclause,$d,$d);
        $this->query=$this->funzioniBase['maestro']['oggetto']->getQuery();
        $this->addLog('query',$this->query);

        //$this->result=$this->piattaforma[$this->funzioniBase['maestro']['piattaforma']]->query($this->query);
        $this->result=$this->executeQuery('base','maestro');
        if (!$this->result) $this->addLog('dberror',$this->query);
    }

    function getCollaboratoriGruppi($gruppi,$d) {
        //$gruppi in stringa IN
        $wclause="gru.ID IN (".$gruppi.")";

        $this->funzioniBase['maestro']['oggetto']->setOrderBy('coll.cognome,coll.nome');
        $this->funzioniBase['maestro']['oggetto']->getCollaboratori($wclause,$d,$d);
        $this->query=$this->funzioniBase['maestro']['oggetto']->getQuery();
        $this->addLog('query',$this->query);

        //$this->result=$this->piattaforma[$this->funzioniBase['maestro']['piattaforma']]->query($this->query);
        $this->result=$this->executeQuery('base','maestro');
        if (!$this->result) $this->addLog('dberror',$this->query);
    }

    function getCollaboratoriIntervallo($reparti,$i,$f) {
        //ritorna tutti i gruppi di appartenenza di tutti collaboratori (in ordine alfabetico e di data)
        //appartenenti ad reparto/macroreparto/o tutta l'azienda
        //in un intervallo di tempo

        $wclause="";
        if ($reparti!="") $wclause="rep.reparto IN (".$reparti.")";

        $this->funzioniBase['maestro']['oggetto']->setOrderBy('coll.cognome,cog.collaboratore,cog.data_i');
        $this->funzioniBase['maestro']['oggetto']->getCollaboratori($wclause,$i,$f);
        $this->query=$this->funzioniBase['maestro']['oggetto']->getQuery();
        $this->addLog('query',$this->query);

        //$this->result=$this->piattaforma[$this->funzioniBase['maestro']['piattaforma']]->query($this->query);
        $this->result=$this->executeQuery('base','maestro');
        if (!$this->result) $this->addLog('dberror',$this->query);
    }

    function getConfigAppUtente($conf) {
        //individua la configurazione delle applicazioni per un utente
        //in base alla sua configurazione generale
        $this->funzioniBase['applicazioni']['oggetto']->getConfigAppUtente($conf);
        $this->query=$this->funzioniBase['applicazioni']['oggetto']->getQuery();
        $this->addLog('query',$this->query);

        //$this->result=$this->piattaforma[$this->funzioniBase['applicazioni']['piattaforma']]->query($this->query);
        $this->result=$this->executeQuery('base','applicazioni');
        if (!$this->result) $this->addLog('dberror',$this->query);

    }

    function getAvalaibleColl($reparto,$today) {
        //fornisce la lista dei collaboratori (ID - cognome - nome - concerto)
        //che sono disponibili per l'inserimento nel reparto
        //escludendo gli altri in base al reparto ed alla data in cui li si vuole inserire

        $this->funzioniBase['maestro']['oggetto']->getAvalaibleColl($reparto,$today);
        $this->query=$this->funzioniBase['maestro']['oggetto']->getQuery();
        $this->addLog('query',$this->query);

        //$this->result=$this->piattaforma[$this->funzioniBase['maestro']['piattaforma']]->query($this->query);
        $this->result=$this->executeQuery('base','maestro');
        if (!$this->result) $this->addLog('dberror',$this->query);
    }

    function getWormhole($reparto) {

        $wclause=($reparto!="")?"reparto='".$reparto."'":"";

        $this->funzioniBase['reparti']['oggetto']->setOrderBy('reparto,inizio');
        $this->query=$this->funzioniBase['reparti']['oggetto']->getSelect("MAESTRO_wormhole",$wclause);
        $this->addLog('query',$this->query);

        //$this->result=$this->piattaforma[$this->funzioniBase['reparti']['piattaforma']]->query($this->query);
        $this->result=$this->executeQuery('base','reparti');
        if (!$this->result) $this->addLog('dberror',$this->query);
    }

    function getSede($codice) {

        $wc="";
        if ($codice!="") $wc="codice='".$codice."'";

        $this->query=$this->funzioniBase['reparti']['oggetto']->getSelect("MAESTRO_sedi",$wc);
        $this->addLog('query',$this->query);

        //$this->result=$this->piattaforma[$this->funzioniBase['reparti']['piattaforma']]->query($this->query);
        $this->result=$this->executeQuery('base','reparti');
        if (!$this->result) $this->addLog('dberror',$this->query);
    }

    function getMacroreparti() {
        //restituisce i macroreparti dell'azienda
        $this->funzioniBase['reparti']['oggetto']->setOrderBy('tipo');
        $this->query=$this->funzioniBase['reparti']['oggetto']->getSelect("MAESTRO_macroreparti","");
        $this->addLog('query',$this->query);

        //$this->result=$this->piattaforma[$this->funzioniBase['reparti']['piattaforma']]->query($this->query);
        $this->result=$this->executeQuery('base','reparti');
        if (!$this->result) $this->addLog('dberror',$this->query);
    }

    function getReparti($macrorep,$orderby) {
        //restituisce l'elenco dei reparti in un dato macroreparto
        //se $macrorep=="" significa TUTTI

        $ob=($orderby!="")?$orderby:'trep.tag';

        $this->funzioniBase['reparti']['oggetto']->setOrderby($ob);
        $this->funzioniBase['reparti']['oggetto']->getReparti($macrorep);
        $this->query=$this->funzioniBase['reparti']['oggetto']->getQuery();
        $this->addLog('query',$this->query);

        //$this->result=$this->piattaforma[$this->funzioniBase['reparti']['piattaforma']]->query($this->query);
        $this->result=$this->executeQuery('base','reparti');
        if (!$this->result) $this->addLog('dberror',$this->query);
    }

    function getReparto($reparto) {
        //restituisce le caratteristiche dello specifico reparto

        $this->funzioniBase['reparti']['oggetto']->getReparto($reparto);
        $this->query=$this->funzioniBase['reparti']['oggetto']->getQuery();
        $this->addLog('query',$this->query);

        //$this->result=$this->piattaforma[$this->funzioniBase['reparti']['piattaforma']]->query($this->query);
        $this->result=$this->executeQuery('base','reparti');
        if (!$this->result) $this->addLog('dberror',$this->query);
    }

    function getOfficine() {
        //restituisce l'elenco delle officine con i rispettivi dati di reparto
        //ordinate per codice officina (concerto)

        $this->funzioniBase['reparti']['oggetto']->getOfficine();
        $this->query=$this->funzioniBase['reparti']['oggetto']->getQuery();
        $this->addLog('query',$this->query);

        //$this->result=$this->piattaforma[$this->funzioniBase['reparti']['piattaforma']]->query($this->query);
        $this->result=$this->executeQuery('base','reparti');
        if (!$this->result) $this->addLog('dberror',$this->query);
    }

    function getMagazzini() {
        //restituisce l'elenco deimagazzini con i rispettivi dati di reparto
        //ordinate per codice officina (concerto)

        $this->funzioniBase['reparti']['oggetto']->getMagazzini();
        $this->query=$this->funzioniBase['reparti']['oggetto']->getQuery();
        $this->addLog('query',$this->query);

        //$this->result=$this->piattaforma[$this->funzioniBase['reparti']['piattaforma']]->query($this->query);
        $this->result=$this->executeQuery('base','reparti');
        if (!$this->result) $this->addLog('dberror',$this->query);
    }

    function getGruppi($wclause) {
        //restituisce i gruppi in base alla wclause (reparto= , stato=) in ordine di posizione
        $this->funzioniBase['maestro']['oggetto']->getGruppi($wclause);
        $this->query=$this->funzioniBase['maestro']['oggetto']->getQuery();
        $this->addLog('query',$this->query);

        //$this->result=$this->piattaforma[$this->funzioniBase['maestro']['piattaforma']]->query($this->query);
        $this->result=$this->executeQuery('base','maestro');
        if (!$this->result) $this->addLog('dberror',$this->query);
    }

    function getFeste($anno) {
        //retituisce le feste per un dato anno

        $this->funzioniBase['calendario']['oggetto']->setOrderBy('mese,giorno');
        $this->query=$this->funzioniBase['calendario']['oggetto']->getSelect("CALENDARIO_feste","anno_i<='".$anno."' AND anno_f>='".$anno."'");
        $this->addLog('query',$this->query);

        //$this->result=$this->piattaforma[$this->funzioniBase['calendario']['piattaforma']]->query($this->query);
        $this->result=$this->executeQuery('base','calendario');
        if (!$this->result) $this->addLog('dberror',$this->query);
    }

    function getChiusure($anno) {
        //retituisce le chiusure in un anno specifico

        $this->funzioniBase['calendario']['oggetto']->setOrderBy('anno,mese,giorno');
        $this->query=$this->funzioniBase['calendario']['oggetto']->getSelect("CALENDARIO_chiusure","anno='".$anno."'");
        $this->addLog('query',$this->query);

        //$this->result=$this->piattaforma[$this->funzioniBase['calendario']['piattaforma']]->query($this->query);
        $this->result=$this->executeQuery('base','calendario');
        if (!$this->result) $this->addLog('dberror',$this->query);
    }

    function getPassiReparto($reparto) {
        //restituisce tutte le autorizzazioni che coinvolgono un determinato reparto (eccetto quelle legate ad un singolo collaboratore)

        $this->funzioniBase['applicazioni']['oggetto']->getPassiReparto($reparto);
        $this->query=$this->funzioniBase['applicazioni']['oggetto']->getQuery();
        $this->addLog('query',$this->query);

        //$this->result=$this->piattaforma[$this->funzioniBase['applicazioni']['piattaforma']]->query($this->query);
        $this->result=$this->executeQuery('base','applicazioni');
        if (!$this->result) $this->addLog('dberror',$this->query);

    }

    function insertCollgru($arr) {

        $this->funzioniBase['maestro']['oggetto']->doInsert('MAESTRO_collgru',$arr,"","query");
        $this->query=$this->funzioniBase['maestro']['oggetto']->getQuery();
        $this->addLog('query',$this->query);

        //$this->result=$this->piattaforma[$this->funzioniBase['maestro']['piattaforma']]->query($this->query);
        $this->result=$this->executeQuery('base','maestro');
        if (!$this->result) $this->addLog('dberror',$this->query);
    }

    function chiudiCollgru($arr) {

        $wc="gruppo='".$arr['gruppo']."' AND collaboratore='".$arr['collaboratore']."' AND data_f='21001231' AND data_i<'".$arr['data_f']."'";
        $this->funzioniBase['maestro']['oggetto']->doUpdate('MAESTRO_collgru',$arr,$wc,'query');
        $this->query=$this->funzioniBase['maestro']['oggetto']->getQuery();
        $this->addLog('query',$this->query);

        //$this->result=$this->piattaforma[$this->funzioniBase['maestro']['piattaforma']]->query($this->query);
        $this->result=$this->executeQuery('base','maestro');
        if (!$this->result) $this->addLog('dberror',$this->query);
    }

    ////////////////////////////////////////////////////////////

    function getPanorama($tipo,$reparto,$d) {
        //restituisce tutti i panorami "A" per un determinato "reparto" dal pià recente (in base alla data fornita) al più vecchio 

        if ($tipo=='A') {
            $wclause="stato='A' AND reparto='".$reparto."' AND inizio<='".substr($d,0,6)."'";
        }
        elseif ($tipo=='P') {
            $wclause="stato='P' AND reparto='".$reparto."'";
        }
        else return;

        $this->funzioniBase['schemi']['oggetto']->setOrderBy('inizio DESC');
        $this->query=$this->funzioniBase['schemi']['oggetto']->getSelect("QUARTET_panorami",$wclause);
        $this->addLog('query',$this->query);

        //$this->result=$this->piattaforma[$this->funzioniBase['schemi']['piattaforma']]->query($this->query);
        $this->result=$this->executeQuery('base','schemi');
        if (!$this->result) $this->addLog('dberror',$this->query);
    }

    function getSchemi($panorama) {
        $this->funzioniBase['schemi']['oggetto']->getSchemi($panorama);

        $this->query=$this->funzioniBase['schemi']['oggetto']->getQuery();
        $this->addLog('query',$this->query);

        //$this->result=$this->piattaforma[$this->funzioniBase['schemi']['piattaforma']]->query($this->query);
        $this->result=$this->executeQuery('base','schemi');
        if (!$this->result) $this->addLog('dberror',$this->query);
    }

    function getTurni() {
        //restituisce tutti i turni definiti
        
        $this->funzioniBase['schemi']['oggetto']->setOrderby("codice,wd");
        $this->query=$this->funzioniBase['schemi']['oggetto']->getSelect("QUARTET_turni","");
        $this->addLog('query',$this->query);

        //$this->result=$this->piattaforma[$this->funzioniBase['schemi']['piattaforma']]->query($this->query);
        $this->result=$this->executeQuery('base','schemi');
        if (!$this->result) $this->addLog('dberror',$this->query);
    }

    function getRepSk($reparto) {
        //ritorna tutti gli schemi definiti per un reparto
        //ed il numero di volte che sono stati legati ad un collaboratore in un panorama A

        /*$this->funzioniBase['schemi']['oggetto']->setOrderby("codice");
        $this->query=$this->funzioniBase['schemi']['oggetto']->getSelect("QUARTET_schemi","reparto='".$reparto."'");
        $this->addLog('query',$this->query);
        $this->result=$this->piattaforma[$this->funzioniBase['schemi']['piattaforma']]->query($this->query);*/

        $this->funzioniBase['schemi']['oggetto']->getRepSk($reparto);
        $this->query=$this->funzioniBase['schemi']['oggetto']->getQuery();
        $this->addLog('query',$this->query);
        //$this->result=$this->piattaforma[$this->funzioniBase['schemi']['piattaforma']]->query($this->query);
        $this->result=$this->executeQuery('base','schemi');
        if (!$this->result) $this->addLog('dberror',$this->query);

    }

    function getCollSk($panorama,$rif) {

        $this->funzioniBase['schemi']['oggetto']->getCollSk($panorama,$rif);
        $this->query=$this->funzioniBase['schemi']['oggetto']->getQuery();
        $this->addLog('query',$this->query);

        //$this->result=$this->piattaforma[$this->funzioniBase['schemi']['piattaforma']]->query($this->query);
        $this->result=$this->executeQuery('base','schemi');
        if (!$this->result) $this->addLog('dberror',$this->query);
    }

    function getSubrepTable($wclause) {
        $this->query=$this->funzioniBase['schemi']['oggetto']->getSelect('QUARTET_subrep',$wclause);
        $this->addLog('query',$this->query);

        //$this->result=$this->piattaforma[$this->funzioniBase['schemi']['piattaforma']]->query($this->query);
        $this->result=$this->executeQuery('base','schemi');
        if (!$this->result) $this->addLog('dberror',$this->query);
   
    }

    function getSubrep($panorama) {

        $this->funzioniBase['schemi']['oggetto']->getSubrep($panorama);
        $this->query=$this->funzioniBase['schemi']['oggetto']->getQuery();
        $this->addLog('query',$this->query);

        //$this->result=$this->piattaforma[$this->funzioniBase['schemi']['piattaforma']]->query($this->query);
        $this->result=$this->executeQuery('base','schemi');
        if (!$this->result) $this->addLog('dberror',$this->query);
    }

    function getSubsPerRep($reparto) {

        $this->funzioniBase['schemi']['oggetto']->getSubsPerRep($reparto);
        $this->query=$this->funzioniBase['schemi']['oggetto']->getQuery();
        $this->addLog('query',$this->query);

        //$this->result=$this->piattaforma[$this->funzioniBase['schemi']['piattaforma']]->query($this->query);
        $this->result=$this->executeQuery('base','schemi');
        if (!$this->result) $this->addLog('dberror',$this->query);
    }

    function getCollskIntervallo($reparti,$i,$f) {
        //ritorna tutti gli abbinamenti collaboratore-schema in un dato intervallo (linkati allo schema)
        
        $wclause="";
        if ($reparti!="") $wclause="sk.reparto IN (".$reparti.")";

        $this->funzioniBase['schemi']['oggetto']->getCollSkIntervallo($wclause,$i,$f);
        $this->query=$this->funzioniBase['schemi']['oggetto']->getQuery();
        $this->addLog('query',$this->query);

        //$this->result=$this->piattaforma[$this->funzioniBase['schemi']['piattaforma']]->query($this->query);
        $this->result=$this->executeQuery('base','schemi');
        if (!$this->result) $this->addLog('dberror',$this->query);

    }

    function getPanskIntervallo($reparti,$i,$f) {
        //ritorna tutti gli schemi di tutti i panorami attivi per i reparti nell'intervallo 
        
        $wclause="";
        if ($reparti!="") $wclause="pan.reparto IN (".$reparti.")";

        $this->funzioniBase['schemi']['oggetto']->getPanSkIntervallo($wclause,$i,$f);
        $this->query=$this->funzioniBase['schemi']['oggetto']->getQuery();
        $this->addLog('query',$this->query);

        //$this->result=$this->piattaforma[$this->funzioniBase['schemi']['piattaforma']]->query($this->query);
        $this->result=$this->executeQuery('base','schemi');
        if (!$this->result) $this->addLog('dberror',$this->query);

    }

    function getPanSubsIntervallo($reparti,$i,$f) {
        //ritorna tutti gli schemi di tutti i panorami attivi per i reparti nell'intervallo 
        
        $wclause="";
        if ($reparti!="") $wclause="pan.reparto IN (".$reparti.")";

        $this->funzioniBase['schemi']['oggetto']->getPanSubsIntervallo($wclause,$i,$f);
        $this->query=$this->funzioniBase['schemi']['oggetto']->getQuery();
        $this->addLog('query',$this->query);

        //$this->result=$this->piattaforma[$this->funzioniBase['schemi']['piattaforma']]->query($this->query);
        $this->result=$this->executeQuery('base','schemi');
        if (!$this->result) $this->addLog('dberror',$this->query);

    }

    function getOrario($orario,$w) {
        //ritorna l'array di uno o più orari, sempre o solo in un giorno della settimana
        //$orario è una stringa pronta per la clausola IN

        $wclause="codice IN (".$orario.")";
        if ($w!="") $wclause.=" AND wd='".$w."'";

        $this->funzioniBase['schemi']['oggetto']->setOrderby("codice,wd");
        $this->query=$this->funzioniBase['schemi']['oggetto']->getSelect('QUARTET_turni',$wclause);
        $this->addLog('query',$this->query);

        //$this->result=$this->piattaforma[$this->funzioniBase['schemi']['piattaforma']]->query($this->query);
        $this->result=$this->executeQuery('base','schemi');
        if (!$this->result) $this->addLog('dberror',$this->query);
    }

    function getOrariOA($reparto,$d) {
        //ritorna le righe dell'orario OA del panorama attuale in ordine di wd
        $this->funzioniBase['schemi']['oggetto']->getOrariOA($reparto,$d);
        $this->query=$this->funzioniBase['schemi']['oggetto']->getQuery();
        $this->addLog('query',$this->query);

        //$this->result=$this->piattaforma[$this->funzioniBase['schemi']['piattaforma']]->query($this->query);
        $this->result=$this->executeQuery('base','schemi');
        if (!$this->result) $this->addLog('dberror',$this->query);
    }

    function creaSchema($arr) {
        //prevede accodamento per transaction
        $this->funzioniBase['schemi']['oggetto']->doInsert('QUARTET_schemi',$arr,'','');
        //$this->query=$this->funzioniBase['schemi']['oggetto']->getQuery();
        //$this->addLog('query',$this->query);
    }

    function updateSchema($param) {
        //prevede accodamento per transaction
        $wc="codice='".$param['codice']."' AND reparto='".$param['reparto']."'";
        $this->funzioniBase['schemi']['oggetto']->doUpdate('QUARTET_schemi',$param,$wc,'');
        //$this->query=$this->funzioniBase['schemi']['oggetto']->getQuery();
        //$this->addLog('query',$this->query);

        //$this->result=$this->piattaforma[$this->funzioniBase['schemi']['piattaforma']]->query($this->query);
    }

    function executeSchema() {
        //da usare nel caso non si esegua insertPansk
        //ma si sia solo scritto o aggiornato lo schema
        $q=$this->funzioniBase['schemi']['oggetto']->getArrquery();
        foreach ($q as $qe) {
            $this->addLog('query',$qe);
        }
        return $this->baseTransaction('schemi', $q);
    }

    function insertPansk($arr) {
        //prevede accodamento per transaction
        $this->funzioniBase['schemi']['oggetto']->doInsert('QUARTET_pan_sk',$arr,"",'');
        //$this->query=$this->funzioniBase['schemi']['oggetto']->getQuery();

        //chiamado il metodo alla fine esegue tutte le query archiviate in SCHEMI
        $q=$this->funzioniBase['schemi']['oggetto']->getArrquery();
        foreach ($q as $qe) {
            $this->addLog('query',$qe);
        }
        return $this->baseTransaction('schemi', $q);
    }

    function getCollskMaxDate($panorama,$collaboratore,$skema) {
        $this->funzioniBase['schemi']['oggetto']->getCollskMaxDate($panorama,$collaboratore,$skema);
        $this->query=$this->funzioniBase['schemi']['oggetto']->getQuery();
        $this->addLog('query',$this->query);

        //$this->result=$this->piattaforma[$this->funzioniBase['schemi']['piattaforma']]->query($this->query);
        $this->result=$this->executeQuery('base','schemi');
        if (!$this->result) $this->addLog('dberror',$this->query);
    }

    function insertCollsk($c) {
        $this->funzioniBase['schemi']['oggetto']->doInsert('QUARTET_coll_sk',$c,'','query');
        $this->query=$this->funzioniBase['schemi']['oggetto']->getQuery();
        $this->addLog('query',$this->query);

        //$this->result=$this->piattaforma[$this->funzioniBase['schemi']['piattaforma']]->query($this->query);
        $this->result=$this->executeQuery('base','schemi');
        if (!$this->result) $this->addLog('dberror',$this->query);
    }

    function updateCollsk($c) {
        $wc="panorama='".$c['panorama']."' AND collaboratore='".$c['collaboratore']."' AND skema='".$c['skema']."'";
        $this->funzioniBase['schemi']['oggetto']->doUpdate('QUARTET_coll_sk',$c,$wc,'query');
        $this->query=$this->funzioniBase['schemi']['oggetto']->getQuery();
        $this->addLog('query',$this->query);

        //$this->result=$this->piattaforma[$this->funzioniBase['schemi']['piattaforma']]->query($this->query);
        $this->result=$this->executeQuery('base','schemi');
        if (!$this->result) $this->addLog('dberror',$this->query);
    }

    function chiudiCollskOpen($c) {
        //setta una data di chiusura quando viene escluso un collaboratore da un reparto
        if ($c['panorama']=='') return;
        $wc="panorama='".$c['panorama']."' AND collaboratore='".$c['collaboratore']."' AND data_f='21001231' AND data_i<'".$c['data_f']."'";
        $this->funzioniBase['schemi']['oggetto']->doUpdate('QUARTET_coll_sk',$c,$wc,'query');
        $this->query=$this->funzioniBase['schemi']['oggetto']->getQuery();
        $this->addLog('query',$this->query);

        //$this->result=$this->piattaforma[$this->funzioniBase['schemi']['piattaforma']]->query($this->query);
        $this->result=$this->executeQuery('base','schemi');
        if (!$this->result) $this->addLog('dberror',$this->query);
    }

    function switchCollsk($arr) {

        foreach ($arr as $a) {
            if ($a['edit']['op']=='update') {
                $wc="panorama='".$a['panorama']."' AND collaboratore='".$a['collaboratore']."' AND skema='".$a['skema']."'";
                $this->funzioniBase['schemi']['oggetto']->doUpdate('QUARTET_coll_sk',$a,$wc,'');
            }
            else if ($a['edit']['op']=='insert') {
                $this->funzioniBase['schemi']['oggetto']->doInsert('QUARTET_coll_sk',$a,'','');
            }
        }

        $q=$this->funzioniBase['schemi']['oggetto']->getArrquery();
        $result=$this->baseTransaction('schemi', $q);

        return $result;
    }

    function setNewPano($a) {
        //crea il panorama A e P
        //$a contiene "reparto","am","oa" per il panorama A
        $arr=array(
            "inizio"=>$a['am'],
            "stato"=>"A",
            "reparto"=>$a['reparto'],
            "orariOA"=>$a['oa'],
            "actual"=>"1"
        );

        $this->funzioniBase['schemi']['oggetto']->doInsert('QUARTET_panorami',$arr,'','');

        $rif=strtotime("+1 month",mainFunc::gab_tots($a['am'].'01'));

        $arr=array(
            "inizio"=>date('Ym',$rif),
            "stato"=>"P",
            "reparto"=>$a['reparto'],
            "orariOA"=>$a['oa'],
            "actual"=>"1"
        );

        $this->funzioniBase['schemi']['oggetto']->doInsert('QUARTET_panorami',$arr,'','');

        $q=$this->funzioniBase['schemi']['oggetto']->getArrquery();
        $result=$this->baseTransaction('schemi', $q);

        return $result;
    }

    function creaTurno($arr) {
        //inserisce un turno completo
        foreach ($arr as $k=>$t) {
            $this->funzioniBase['schemi']['oggetto']->doInsert('QUARTET_turni',$t,'','');
        }

        $q=$this->funzioniBase['schemi']['oggetto']->getArrquery();
        $result=$this->baseTransaction('schemi', $q);

        return $result;
    }

    function delPanSubs($panorama) {
        //accoda la query di delete di tutti i subrep attribuiti ad un panorama

        $wc="panorama='".$panorama."'";
        $this->funzioniBase['schemi']['oggetto']->doDelete('QUARTET_pan_subrep',$wc,'');
    
    }

    function insertPanSub($arr) {
        //accoda la query di inserimento di un collegamento panorama-subrep
        $this->funzioniBase['schemi']['oggetto']->doInsert('QUARTET_pan_subrep',$arr,'','');
    }

    function executeSubs() {
        $q=$this->funzioniBase['schemi']['oggetto']->getArrquery();
        $result=$this->baseTransaction('schemi', $q);

        return $result;
    }

    function getQuartetSubrep() {
        $this->query=$this->funzioniBase['schemi']['oggetto']->getSelect('QUARTET_subrep','');
        $this->addLog('query',$this->query);

        //$this->result=$this->piattaforma[$this->funzioniBase['schemi']['piattaforma']]->query($this->query);
        $this->result=$this->executeQuery('base','schemi');
        if (!$this->result) $this->addLog('dberror',$this->query);
    }

    ////////////////////////////////////////////////////////////////////////////////

    function getTimbratureSolari($rif) {

        /*if (!$this->piattaforma['solari']) {
            $this->piattaforma['solari']=new Solari();
        }

        if (!$this->piattaforma['solari']->get_handler()) {
            $this->result=false;
            return;
        }*/

        //$this->funzioniBase['badge']['oggetto']->setOrderby("IDTIMBRATURA");
        //$this->query=$this->funzioniBase['badge']['oggetto']->getSelect("TIMBRATURE","IDTIMBRATURA>'".$rif."'");
        $this->funzioniBase['badge']['oggetto']->importaTimbrature($rif);
        $this->query=$this->funzioniBase['badge']['oggetto']->getQuery();
        $this->addLog('query',$this->query);

        //$this->result=$this->piattaforma[$this->funzioniBase['badge']['piattaforma']]->query($this->query);
        $this->result=$this->executeQuery('base','badge');
        if (!$this->result) $this->addLog('dberror',$this->query);
		
    }

    function getTimbratureSolariHR($rif) {

        $this->funzioniBase['badge']['oggetto']->importaTimbratureHR($rif);
        $this->query=$this->funzioniBase['badge']['oggetto']->getQuery();
        $this->addLog('query',$this->query);

        $this->result=$this->executeQuery('base','badge');
        if (!$this->result) $this->addLog('dberror',$this->query);
		
    }

    //////////////////////////////////////////////////////////////////////////////

    function getLamExtra($dms,$rif,$lam) {

        $this->funzioniBase['avalon']['oggetto']->getLamExtra($dms,$rif,$lam);
        $this->query=$this->funzioniBase['avalon']['oggetto']->getQuery();
        $this->addLog('query',$this->query);

        //$this->result=$this->piattaforma[$this->funzioniBase['avalon']['piattaforma']]->query($this->query);
        $this->result=$this->executeQuery('base','avalon');
        if (!$this->result) $this->addLog('dberror',$this->query);
    }

}

?>