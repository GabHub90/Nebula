<?php

class nebulaWHole {
    //dato un intervallo di tempo ed un reparto di riferimento
    //definisce gli intervalli di appartenenza ad un DMS
    //la classe viene estesa per i diversi contesti in modo da fornire i metodi articolati per la raccolta e la scrittura dei dati
    //in base all'apertenenza la classe si preoccuperà di configurare "galileo" .

    //restituisce gli oggetti RESULT delle query

    protected $reparto="";
    protected $map=array();

    protected $dmss=array('infinity','concerto');

    protected $piattaforma=array(
        'concerto'=>'maestro',
        'infinity'=>'rocket',
        'nebula'=>'gab500'
    );

    protected $galileo;

    function __construct($reparto,$galileo) {

        $this->reparto=$reparto;
        $this->galileo=$galileo;
        
    }

    function getDmss() {
        return $this->dmss;
    }

    function getPiattaforma($dms) {
        return $this->piattaforma[$dms];
    }

    function build($intervallo) {
        //inizio:YYYYMMDD fine:YYYYMMDD
        //seleziona le voci del DB che intersecano il periodo

        $this->map=array();

        if ($this->reparto=="") return;

        //normalizza le date in entrata
        $inizio=substr($intervallo['inizio'],0,6);
        $fine=substr($intervallo['fine'],0,6);

        /*TEST VWS
        //array letto dal DB in ordine di inizio
        //ATTENZIONE!!!! IL TEST NON DISTINGUE PER REPARTO
        $arr=array(
            array(
                "reparto"=>"VWS",
                "dms"=>"concerto",
                "inizio"=>"201205",
                "fine"=>"202201"
            ),
            array(
                "reparto"=>"VWS",
                "dms"=>"infinity",
                "inizio"=>"202202",
                "fine"=>"210012"
            )
        );*/

        $this->galileo->clearQuery();
        $this->galileo->clearQueryOggetto('base','reparti');

        $this->galileo->getWormhole($this->reparto);

        if ($this->galileo->getResult()) {

            $fetID=$this->galileo->preFetchBase('reparti');

            while ($row=$this->galileo->getFetchBase('reparti',$fetID)) {

            //foreach ($arr as $row) {

                //se l'intervallo che mi interessa interseca l'intervallo di riferimento
                if ($inizio<=$row['fine'] && $fine>=$row['inizio']) {
                    $this->map[]=array(
                        "inizioDms"=>$row['inizio'].'01',
                        "fineDms"=>date("Ymt",mainFunc::gab_tots($row['fine'].'01')),
                        "inizio"=>($inizio>=$row['inizio'])?$intervallo['inizio']:$row['inizio'].'01',
                        "fine"=>($fine>$row['fine'])?date("Ymt",mainFunc::gab_tots($row['fine'].'01')):$intervallo['fine'],
                        "dms"=>$row['dms'],
                        "result"=>false
                    );
                }
            }
        }

    }

    function forceMap($a) {

        $temp=array(
            "inizio"=>"",
            "fine"=>"",
            "dms"=>"",
            "result"=>false
        );

        foreach ($temp as $k=>$v) {
            if (array_key_exists($k,$a)) {
                $temp[$k]=$a[$k];
            }
        }

        $this->map=array();
        $this->map[]=$temp;
    }

    function clearMap() {
        $this->map=array();
    }

    function exportMap() {
        return $this->map;
    }

    function getTodayDms($today) {
        //deve essere stato chiamato BUILD

        $dms=false;

        foreach ($this->map as $k=>$m) {
            if ($today>=$m['inizio'] && $today<=$m['fine']) {
                $dms=$m['dms'];
                break;
            }
        }

        return $dms;
    }

    function getInizioDms($today) {
        //deve essere stato chiamato BUILD

        $inizio=false;

        foreach ($this->map as $k=>$m) {
            if ($today>=$m['inizio'] && $today<=$m['fine']) {
                $inizio=$m['inizioDms'];
                break;
            }
        }

        return $inizio;
    }

}

?>