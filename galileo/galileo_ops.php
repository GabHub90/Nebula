<?php

class galileoOps {

    //query definitiva
    protected $query="";

    //query per transaction
    protected $arrQuery=array();

    //array di oggetti tabella
    protected $tabelle=array();

    //array delle viste
    //tag:nome
    protected $viste=array();

    //array di oggetti operazioni necessari a questo oggetto
    protected $link=array();

    //stringa ORDER BY
    protected $orderBy="";

    //errorFLag (TRUE = NO ERRORS)
    protected $errorFlag=true;
    //indica la tabella di cui prendere i valori restituiti da una transaction
    protected $incsetTab="";

    //log degli errori
    protected $log=array(
        "errori"=>array()
    );

    /*protected $feedback=array(
        "overall"=>true,
        "passaggi"=>array()
    );*/

    function addLink($nome,$obj) {
        $this->link[$nome]=$obj;
    }

    function clearQuery() {
        $this->query="";
        $this->arrQuery=array();
        $this->orderBy="";
    }

    function disableIncrement($tabella) {

        $res=$this->tabelle[$tabella]->disableIncrement();

        return $res;
    }

    function getLog($ambito) {
        return $this->log[$ambito];
    }

    function getIncsetTab() {
        return $this->incsetTab;
    }

    function getSelect($tabella,$wclause) {

        $this->tabelle[$tabella]->setDefault();

        //il valore nullo è ''
        $this->tabelle[$tabella]->setWhereClause($wclause);

        $this->tabelle[$tabella]->buildSelect();

        $this->query=$this->tabelle[$tabella]->getQuery();

        if ($this->orderBy!="") {
            $this->query.=' ORDER BY '.$this->orderBy;
        }

        $this->query.=";";

        return $this->query;
    }

    function getCount($tabella,$wclause) {

        $this->tabelle[$tabella]->setWhereClause($wclause);

        $this->tabelle[$tabella]->buildCount();

        $this->query=$this->tabelle[$tabella]->getQuery();

        return $this->query;
    }

    function doTransactionHead($tabella,$tab) {

        $res=$this->tabelle[$tabella]->buildTransactionHead($tab);

        //se build ha dato errore
        if (!$res) {
            $this->errorFlag=false;
            return false;
        }

        $this->arrQuery[]=$this->tabelle[$tabella]->getQuery();

        return true;
    }

    function doInsert($tabella,$arr,$incset,$accodamento) {
        //incSet "" = usa buildInsert liscio
        //incSet "nome" = utilizza il nome per SET @nome come valore per il campo increment

        $this->tabelle[$tabella]->setDefault();

        $this->tabelle[$tabella]->setValues($arr);

        //il valore nullo è ''
        $this->tabelle[$tabella]->setWhereClause("");

        $this->tabelle[$tabella]->setIncset($incset);

        $res=$this->tabelle[$tabella]->buildInsert();

        //se build ha dato errore
        if (!$res) {
            $this->errorFlag=false;
            return false;
        }

        if ($accodamento=='query') {
            $this->query.=$this->tabelle[$tabella]->getQuery();
        }
        else $this->arrQuery[]=$this->tabelle[$tabella]->getQuery();
 
        return true;
    }

    function doUpdate($tabella,$arr,$wclause,$accodamento) {

        $this->tabelle[$tabella]->setDefault();
        $this->tabelle[$tabella]->setUpdate($arr);
        //il valore nullo è ''
        $this->tabelle[$tabella]->setWhereClause($wclause);

        $this->tabelle[$tabella]->buildUpdate();

        if ($accodamento=='query') {
            $this->query.=$this->tabelle[$tabella]->getQuery();
        }
        else $this->arrQuery[]=$this->tabelle[$tabella]->getQuery();
        
        return true;
    }

    function doDelete($tabella,$wclause,$accodamento) {

        if ($wclause=="") return false;
        
        $this->tabelle[$tabella]->setWhereClause($wclause);
        $this->tabelle[$tabella]->buildDelete();
        
        if ($accodamento=='query') {
            $this->query.=$this->tabelle[$tabella]->getQuery();
        }
        else $this->arrQuery[]=$this->tabelle[$tabella]->getQuery();
        
        return true;
    }

    function doNext($tabella) {
        
        $res=$this->tabelle[$tabella]->buildNext();

        return $res;
    }

    function getQuery() {
        return $this->query;
    }

    function getArrquery() {
        return $this->arrQuery;
    }

    function setOrderby($txt) {
        $this->orderBy=$txt;
    }

    function callGeneric($funzione,$args) {
        //la funzione passa SEMPRE un array []
        $res= call_user_func_array(array($this, $funzione), array($args) );

        //$this->log['errori'][]=$funzione;
        //return false;

        if (!$res) $this->getTabErrors();

        return $res;
    }

    function getTabErrors() {
        //raccoglie gli errori da tutte le tabelle
        foreach ($this->tabelle as $t) {

            $e=$t->getErrors();
            $this->log['errori']=array_merge($this->log['errori'],$e);
        }
    }


    /*function setPass($nome,$pass) {

        //nome dell'operazione
        //array degli argomenti per ogni passaggio

        call_user_func_array( array($this,$nome),$pass );
        //la funzione definisce i passaggi dell'operazione

    }*/

}

?>