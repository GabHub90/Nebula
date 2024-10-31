<?php

abstract class galileoTab {

    //nome della tabella
    protected $tabName="";
    //array di tutti i campi della tabella e valori di default che ha senso considerare in una query (SELECT a parte)
    //escluso eventuale INCREMENT
    protected $default=array();
    //eventuale campo indice da incrementare in automatico in un INSERT
    protected $increment="";
    //incSet "" = usa buildInsert liscio
    //incSet "nome" = utilizza il nome per SET @nome come valore per il campo increment d inserire nella tabella INCSETTAB
    protected $incSet="";
    protected $incsetTab="";
    //array dei campi e dei relativi valori da utilizzare nella query
    //escluso eventuale INCREMENT
    protected $actual=array();
    //mappa dei campi da estrarre in una SELECT
    protected $selectMap=array();
    //clausola WHERE
    protected $whereClause="";
    //definisce i controlli da fare prima di un INSERT
    //  $check={ "campo":[verifica,verifica...] }
    //  NOTNULL = non può essere un campo vuoto
    protected $checkMap=array();

    //stringa query elaborata
    protected $query="";

    //LOG
    protected $log=array(
        "errori"=>array()
    );

    function disableIncrement() {

        if ($this->increment=="") return false;

        $this->default[$this->increment]="";
        $this->checkMap[$this->increment]=array("NOTNULL");
        $this->increment="";

        return true;
    }

    function setDefault() {
        //riporta ACTUAL e WHERECLAUSE ai valori di DEFAULT
        $this->actual=$this->default;
        $this->whereClause="";
    }

    function setValues($arr) {
        //setta i valori di ACTUAL con un array fornito dall'esterno
        foreach ($arr as $key=>$a ) {

            if ( array_key_exists($key,$this->actual) ) $this->actual[$key]=$a;
        }

        $this->evaluate('insert');
    }

    function setUpdate($arr) {
        $temp=array();
        foreach ($this->actual as $k=>$v) {
            if (array_key_exists($k,$arr)) $temp[$k]=$arr[$k];
        }

        $this->actual=$temp;

        $this->evaluate('update');
    }

    function setWhereClause($str) {
        //setta la clausola WHERE
        if ($str=='') return;

        $this->whereClause=" WHERE ".$str;

    }

    function setIncset($nome) {
        $this->incSet=$nome;
    } 

    function buildSelect() {
        //costruisce una query SELECT con i campi mappati in SELECTMAP
        $txt='SELECT ';
        $temp="";

        foreach ($this->selectMap as $m) {
            $temp.=$m.',';
        }

        $this->query=$txt.substr($temp,0,-1).' FROM '.$this->tabName;

        $this->query.=$this->whereClause;

    }

    function buildCount() {
        $this->query="SELECT isnull(count(*),0) AS numero_elementi ";
        $this->query.=' FROM '.$this->tabName;
        $this->query.=$this->whereClause;
    }

    function buildTransactionHead($tab) {

        $this->incsetTab=$tab;

        $this->query="";

        $this->query="CREATE TABLE ".$tab." ( k varchar(25), v varchar(25) );";

        return true;
    }

    function buildInsert() {

        if (!$this->check()) {
            //echo 'gegzgavaefvzas';
            return false;
        }

        //equalizza INCREMENT ed incSet da eventuali errori di impostazione a monte
        if ($this->increment=="") $this->incSet="";

        /////////////////////
        $this->query="";
        $c="";
        $v="";
        ////////////////////

        //se è stato specificato il campo INCREMENT allora deve essere gestito
        if ($this->increment!="") {

            //se NON è stato specificato INCSET allora increment + 1
            if ($this->incSet=="") {
                $c.=$this->increment.',';
                $v.="(SELECT isnull(max(".$this->increment."),0)+1 FROM ".$this->tabName."),";
            }
            //altrimenti alimenta la tabella temporanea
            else {
                /*$this->query.="DECLARE @".$this->incSet.' INT;';
                //$this->query.="DECLARE @incTab TABLE(lastID INT);";
                $this->query.="SELECT @".$this->incSet."= (SELECT isnull(max(".$this->increment."),0)+1 FROM ".$this->tabName.");";
                $c.=$this->increment.',';
                $v.="@".$this->incSet.',';*/

                if ($this->incsetTab=="") return false;

                $this->query.="DECLARE @".$this->incSet.' VARCHAR(25);';
                $this->query.="SET @".$this->incSet."= (SELECT isnull(max(".$this->increment."),0)+1 FROM ".$this->tabName.");";
                $this->query.="INSERT INTO ".$this->incsetTab." VALUES ('".$this->incSet."', @".$this->incSet." );";
                $c.=$this->increment.',';
                $v.="(SELECT v FROM ".$this->incsetTab." WHERE k='".$this->incSet."'),";
            }
        }

        ////////////////////////////

        $this->query.="INSERT INTO ".$this->tabName." (";

        foreach ($this->actual as $k=>$a) {
            $c.=$k.",";
            if (substr($a,0,3)=='###') $v.=substr($a,3).',';
            elseif (substr($a,0,1)=='@' || $a=='NULL') $v.=$a.',';
            else $v.="'".str_replace("'","''",$a)."',";
        }

        $this->query.=substr($c,0,-1).") VALUES (".substr($v,0,-1).") ";

        $this->query.=$this->whereClause;

        $this->query.=";";

        return true;
    }

    function buildUpdate() {

         /////////////////////
         $this->query="";
         $txt="";
         ////////////////////

         $this->query.="UPDATE ".$this->tabName." SET ";

         foreach ($this->actual as $k=>$a) {
            $txt.=$k."=";
            if (substr($a,0,3)=='###') $txt.=substr($a,3).',';
            elseif (substr($a,0,1)=='@' || $a=='NULL') $txt.=$a.',';
            else $txt.="'".str_replace("'","''",$a)."',";
        }

        $this->query.=substr($txt,0,-1);

        $this->query.=$this->whereClause;

        $this->query.=';';
    }

    /*
    function addValue($k,$a) {
        $c="";
        $v="";

        $c=$k.",";
        if (substr($a,0,1)=='@' || $a=='NULL') $v=$a.',';
        else $v="'".str_replace("'","''",$a)."',";

        return (array($c,$v));
    }
    */

    function buildDelete() {

        $this->query='DELETE '.$this->tabName;
        $this->query.=$this->whereClause.';';
    }

    function check() {

        //echo json_encode($this->actual);

        $chk=true;

        foreach ($this->checkMap as $campo=>$c) {

            foreach ($c as $verifica) {

                if ($verifica=='NOTNULL') {
                    if ( ( !isset($this->actual[$campo]) && $this->actual[$campo]!=0 ) || $this->actual[$campo]=="") {
                        $this->log['errori'][]='GALILEO-'.$this->tabName.':'.$campo.' IS NULL';
                        //echo 'GALILEO-'.$this->tabName.':'.$campo.' IS NULL';
                        $chk=false;
                    }
                }

                else if (substr($verifica,0,2)=='IN') {
                    if (!strpos($verifica,$this->actual[$campo].',') !== false) {
                        $this->log['errori'][]='GALILEO-'.$this->tabName.':'.$campo.' VALORE NON ACCETTATO';
                        $chk=false;
                    }
                }
            }
        }

        return $chk;
    }

    function buildNext() {

        if ($this->increment=="") return false;

        $q="SELECT isnull(max(".$this->increment."),0)+1 AS next_increment FROM ".$this->tabName.";";

        return $q;
    }

    function getResvar() {
        //viene valorizzato SOLO specificando INCSET (che è valido SOLO se è specificato increment) e INCSETTAB
        if ($this->incsetTab=="") return "";

            $this->query="SELECT * FROM ".$this->incsetTab.";";
            //$this->query.="DROP TABLE ".$this->incsetTab.";";
   
            return $this->query;
    }

    function getErrors() {
        return $this->log['errori'];
    }

    function getQuery() {
        return $this->query;
    }

    function getTabName() {
        return $this->tabName;
    }


    //definisce il valore che alcuni campi devono assumere in relazione agli altri
    //viene chiamato da SETVALUES
    //verifica la presenza di alcuni campi in ACTUAL
    //calcola i campi correlati e se non ci sono li aggiunge i ACTUAL
    //se necessario ne corregge il valore in base al giusto calcolo
    abstract function evaluate($tipo);

}

?>