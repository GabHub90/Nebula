<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/core/alan/alan.php');

require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/concerto/concerto_odl.php');
require_once($_SERVER['DOCUMENT_ROOT']."/nebula/galileo/infinity/infinity_odl.php");

include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_alan.php');

class nebulaTimbFunc {
    //utilities per la gestione delle timbrature multi-DMS
    //funziona in maniera simile ad un wormhole ma non lo eredita in quanto per le operazioni necessarie
    //sappiamo già quale gestionale è da usare

    //CONCERTO viene usato come DMS di riferimento
    //il cod_operaio del collaboratore corrisponde al codice di CONCERTO
    //per gli altri DMS , il codice operaio dovrà essere trasformato in quello di CONCERTO

    protected $refCollaboratori=array();

    protected $linkDmsRef=array(
        "concerto"=>array(),
        "infinity"=>array()
    );

    protected $linkRefDms=array(
        "concerto"=>array(),
        "infinity"=>array()
    );

    protected $defaultMap=array(
        "concerto"=>array(
            "dms"=>"concerto",
            "piattaforma"=>'maestro',
            "result"=>false
        ),
        "infinity"=>array(
            "dms"=>"infinity",
            "piattaforma"=>'rocket',
            "result"=>false
        ),
    );

    //simulazione di un wormhole
    protected $map=array();
    //protected $piattaforma="";

    //contiene le marcature aperte
    protected $marcature=array();

    protected $actualDms="";

    protected $config=array(
        "OOS"=>array(
            "concerto"=>array(
                "PV"=>"945604",
                "PA"=>"945607",
                "PU"=>"945605",
                "PP"=>"945606",
                "AP"=>"1000070",
                "PC"=>"993753",
                "PN"=>"1137999",
                "PI"=>"945606",
                "CP"=>"1405987"
            ),
            "infinity"=>array(
                "ZZ"=>"0k",
                "PV"=>"0k",
                "PA"=>"0k",
                "PU"=>"0k",
                "PP"=>"0k",
                "AP"=>"0k",
                "PC"=>"0k",
                "PN"=>"0k",
                "CA"=>"0k",
                "CN"=>"0k",
                "CU"=>"0k"
            )
        ),
        "speciale"=>array(
            "ANT"=>0,
            "PUL"=>0,
            "SER"=>0,
            "PRV"=>0,
            "ATT"=>0,
            "EXT"=>0,
            "CHI"=>0
        )
    );

    //in questo caso GALILEO è una COPIA dell'originale
    protected $galileo;
    protected $alan;

    protected $log=array();

    function __construct($galileo) {

        $this->galileo=clone $galileo;

        $obj=new galileoAlan();
        $nebulaDefault['alan']=array("gab500",$obj);
        $this->galileo->setFunzioniDefault($nebulaDefault);

        $this->alan=new nebulaAlan('S','',null,$this->galileo);
        //$this->alan->importa();

        //nebula
        //$this->setGalileo('nebula');

        //recupera i collaboratori che hanno un codice operaio
        $this->galileo->getMaestroCollab("isnull(cod_operaio,'')!='' OR isnull(cod_operaio_infinity,'')!=''");

        $fid=$this->galileo->preFetchBase('maestro');

        while ($row=$this->galileo->getFetchBase('maestro',$fid)) {

            $this->refCollaboratori[$row['ID']]=$row;

            $this->linkDmsRef['concerto'][$row['cod_operaio']]=$row['ID'];
            $this->linkRefDms['concerto'][$row['ID']]=$row['cod_operaio'];

            if ($row['cod_operaio_infinity']!="") {
                $this->linkDmsRef['infinity'][$row['cod_operaio_infinity']]=$row['ID'];
                $this->linkRefDms['infinity'][$row['ID']]=$row['cod_operaio_infinity'];
            }
        }
    }

    function getLog() {
        return $this->log;
    }

    function getDefaultMap($dms) {
        if (isset($this->defaultMap[$dms])) return $this->defaultMap[$dms];
        else return false;
    }

    function getMarcature() {
        return $this->marcature;
    }

    function getRef($operaio,$dms) {
        //fornendo il dms ed il codice operaio restituisce il riferimento al cod_operaio di NEBULA = IDcoll
        $res=false;

        if (array_key_exists($operaio,$this->linkDmsRef[$dms])) {
            //trasforma i cod_operaio in quello di NEBULA = IDcoll
            $res=$this->linkDmsRef[$dms][$operaio];
        }

        return $res;
    }

    function getDmsRef($IDcoll,$dms) {
        //fornendo il dms ed il codice collaboratore restituisce il riferimento al cod_operaio del DMS
        $res=false;

        if (array_key_exists($IDcoll,$this->linkRefDms[$dms])) {
            //trasforma i cod_operaio in quello di NEBULA = IDcoll
            $res=$this->linkRefDms[$dms][$IDcoll];
        }

        return $res;
    }

    function getSpeciali() {
        return $this->config['speciale'];
    }

    function rifStringify($coll) {

        $txt="";

        foreach ($this->linkRefDms as $dms=>$d) {
            if (array_key_exists($coll,$d)) {
                $txt.=$d[$coll].' ('.$dms.') ';
            }
        }

        return $txt;

    }

    function setGalileo($dms) {

        if ($dms=='concerto') {

            $obj=new galileoConcertoODL();
            $nebulaDefault['odl']=array("maestro",$obj);

            $this->galileo->setFunzioniDefault($nebulaDefault);

            $this->actualDms='concerto';

        }

        elseif ($dms=='infinity') {

            $obj=new galileoInfinityODL();
            $nebulaDefault['odl']=array("rocket",$obj);

            $this->galileo->setFunzioniDefault($nebulaDefault);

            $this->actualDms='infinity';

        }

    }

    /*function getPiattaforma() {
        return $this->piattaforma;
    }*/

    /////////////////////////////////////////////////////////////////////////////////////
    function getMarcatureOdl($rif,$lam,$dms) {

        if ($this->actualDms!=$dms) $this->setGalileo($dms);

        $map=$this->defaultMap[$dms];

        $arg=array(
            "rif"=>$rif,
            "lam"=>$lam
        );

        $this->galileo->clearQuery();
        $this->galileo->clearQueryOggetto('default','odl'); 

        $this->galileo->executeGeneric('odl','getMarcatureOdl',$arg,'');

        $map['result']=$this->galileo->getResult();

        return $map;
    }

    function getMarcatureAperte() {
        //funziona come un wormhole ma non abbiamo bisogno di definire il DMS in base al periodo

        $this->map=array();

        ////////////////////////////////////////////
        //CONCERTO

        $this->map[0]=array(
            "inizio"=>"201205",
            "fine"=>"210012",
            "dms"=>"concerto",
            "piattaforma"=>'maestro',
            "result"=>false
        );

        $this->setGalileo('concerto');

        //$tipo,$funzione,$args,$order
        $arg=array();

        $this->galileo->clearQuery();
        $this->galileo->clearQueryOggetto('default','odl'); 

        $this->galileo->executeGeneric('odl','getMarcatureAperte',$arg,'');
        $this->map[0]['result']=$this->galileo->getResult();

        ////////////////////////////////////////////

        ////////////////////////////////////////////
        //INFINITY

        $this->map[1]=array(
            "inizio"=>"201205",
            "fine"=>"210012",
            "dms"=>"infinity",
            "piattaforma"=>'rocket',
            "result"=>false
        );

        $this->setGalileo('infinity');

        //$tipo,$funzione,$args,$order
        $arg=array();

        $this->galileo->clearQuery();
        $this->galileo->clearQueryOggetto('default','odl'); 

        $this->galileo->executeGeneric('odl','getMarcatureAperte',$arg,'');
        $this->map[1]['result']=$this->galileo->getResult();

        ////////////////////////////////////////////

        ////////////////////////////////////////////
        //INFINITY SPECIALI

        /*"9": {
            "cod_operaio": 9,
            "d_inizio": "20220129",
            "o_inizio": "14:18",
            "d_fine": "",
            "o_fine": "",
            "qta_ore_lavorate": "0.00",
            "des_note": "SER",
            "num_rif_riltem": 5,
            "num_rif_movimento": "0",
            "cod_officina": "PV",
            "cod_inconveniente":"MM",
            "cod_movimento":"OOS",
            "fittizio": 1,
            "dms": "infinity"
        }*/

        $this->map[2]=array(
            "inizio"=>"201205",
            "fine"=>"210012",
            "dms"=>"infinity",
            "piattaforma"=>'rocket',
            "result"=>false
        );

        //$tipo,$funzione,$args,$order
        $arg=array();

        $this->galileo->clearQuery();
        $this->galileo->clearQueryOggetto('default','odl'); 

        $this->galileo->executeGeneric('odl','getMarcatureSpecialiAperte',$arg,'');
        $this->map[2]['result']=$this->galileo->getResult();

        ////////////////////////////////////////////

        //$this->galileo->closeHandler('rocket');
  
    }

    //15.09.21 il metodo è stato spostato qui per non dover instanziare la classe workshop per modificare le marcature
    function calcolaMarcatureAperte() {

        $marcature=array();

        $this->getMarcatureAperte();

        //echo json_encode($this->galileo->getLog('query'));

        foreach ($this->map as $m) {

            if ($m['result']) {
                $fid=$this->galileo->preFetchPiattaforma($m['piattaforma'],$m['result']);

                while ($row=$this->galileo->getFetchPiattaforma($m['piattaforma'],$fid)) {

                    //////////////////////////
                    //se non esiste il riferimento dell'operaio tra DMS e NEBULA non considerare la marcatura
                    if (!array_key_exists($row['cod_operaio'],$this->linkDmsRef[$m['dms']])) continue;
                    //trasforma i cod_operaio in quello di NEBULA
                    $row['cod_operaio']=$this->linkDmsRef[$m['dms']][$row['cod_operaio']];
                    /////////////////////////

                    //se la marcatura è chiusa ed è una marcatura speciale e la data non è quella odierna non prendere in considerazione
                    if ($row['d_fine']!="" && $row['des_note']!="" && $row['d_inizio']!=date('Ymd')) continue;

                    //se non c'è ora inizio non considerare(INFINITY)
                    //19.02.22 - non dovrebbe più estrarre queste marcature
                    if ($row['o_inizio']=='') continue;

                    if ($row['num_rif_movimento']==$this->config['OOS'][$m['dms']][$row['cod_officina']]) {
                        //$marcature[$row['cod_operaio']]['fittizio']=1;
                        $row['fittizio']=1;
                    }
                    else {
                        //$marcature[$row['cod_operaio']]['fittizio']=0;
                        $row['fittizio']=0;
                    }

                    $row['dms']=$m['dms'];

                    //###########################################################

                    //potrebbe già esistere un'ultima marcatura per l'operaio
                    //attingendo le informazioni da DMS differenti

                    if (array_key_exists($row['cod_operaio'],$marcature)) {

                        $new=$row['d_inizio'].':'.$row['o_inizio'];
                        $actual=$marcature[$row['cod_operaio']]['d_inizio'].':'.$marcature[$row['cod_operaio']]['o_inizio'];

                        //###################
                        //se sono tutte e due aperte ==>> errore da gestire
                        //###################

                        //se è aperta la nuova ma quella già registrata è chiusa la nuova prende il sopravvento
                        if ($row['d_fine']=='' && $marcature[$row['cod_operaio']]['d_fine']!='') $marcature[$row['cod_operaio']]=$row;
                        
                        //non fare niente
                        //elseif ($row['d_fine']!='' && $marcature[$row['cod_operaio']]['d_fine']=='') $marcature[$row['cod_operaio']]=$row;

                        //a questo punto sono tutte e due chiuse e se la nuova è più recente prende il sopravvento
                        if ($new>$actual) $marcature[$row['cod_operaio']]=$row;
                        //$marcature[$row['cod_operaio']]['dms']=$m['dms'];
                    }
                    else {
                        $marcature[$row['cod_operaio']]=$row;
                        //$marcature[$row['cod_operaio']]['dms']=$m['dms'];
                    }
                }
            }
        }

        $this->marcature=$marcature;
    }

    function correggiAlan($marcatura,$tl) {

        $timb=$marcatura;

        $coll=$this->refCollaboratori[$timb['cod_operaio']];

        $this->alan->importa();

        $uscita=$this->alan->controllaUscita($timb['d_inizio'],$timb['o_inizio'],$coll['IDDIP']);

        //se esiste una uscita oppure la data non è odierna oppure è una marcatura speciale in un periodo $tl non attivo ALLINEA la marcatura

        //se non c'è una uscita ma l'ordine di lavoro è chiuso e non è una marcatura speciale ANT => SIMULA UNA USCITA ADESSO
        //se la data non è odierna o è una marcatura speciale l'uscita non verrà considerata a causa degli IF precedenti
        if (!$uscita && $timb['ind_chiuso']=='S' && substr($timb['des_note'],0,3)!="ANT") {
            
            //i dms non hanno l'orario di stampa della fattura
            $uscita=array(
                "h"=> date('H:i'),
                "VERSOO"=>"U",
                "flag"=>1
            );   
        }

        //se la data non è odierna
        if ($timb['d_inizio']<date('Ymd')) {

            if ($tl) {

                $fineTurno=mainfunc::gab_mintostring($tl->getFineTurno(mainFunc::gab_stringtomin($timb['o_inizio'])));

                if ($fineTurno) {

                    $timb['d_fine']=$timb['d_inizio'];
                    $timb['o_fine']=(!$fineTurno || $fineTurno=='00:00')?$timb['o_inizio']:$fineTurno;

                    //UPDATE DB
                    //echo json_encode($timb);
                    $timb=$this->chiudiMarcatura($timb);
                    $this->timbLog('ieriFine',$timb,$uscita);
                }
            }
            else {
                $timb['d_fine']=$timb['d_inizio'];
                $timb['o_fine']=($timb['o_inizio']<='20:00')?'20:00':$timb['o_inizio'];
                $timb=$this->chiudiMarcatura($timb);
                $this->timbLog('ieriNotl',$timb,$uscita);
            }
        }

        //se è una marcatura speciale
        else if ($timb['des_note']!="" && substr($timb['des_note'],0,3)!="ANT") {

            if ($tl) {
                //verifica se adesso è un periodo attivo

                //#################################################################
                $temp=$tl->checkFlag(mainFunc::gab_stringtomin(date('H:i')));
                //#################################################################

                //se il periodo NON è attivo
                if (!$temp) {

                    $fineTurno=mainfunc::gab_mintostring($tl->getFineTurno(mainFunc::gab_stringtomin($timb['o_inizio'])));

                    if ($fineTurno) {

                        $timb['d_fine']=$timb['d_inizio'];
                        $timb['o_fine']=$fineTurno;

                        if ($timb['o_fine']=='00:00') $timb['o_fine']=date('H:i');
                        else if ($timb['o_fine']>date('H:i')) $timb['o_fine']=date('H:i');
    
                        //UPDATE DB
                        $timb=$this->chiudiMarcatura($timb);
                        $this->timbLog('speciale',$timb,$tl->getTl());
                    }

                }
            }
            else {
                $timb['d_fine']=$timb['d_inizio'];
                $timb['o_fine']=$timb['o_inizio'];
                $timb=$this->chiudiMarcatura($timb);
                $this->timbLog('specNotl',$timb,$uscita);
            }
        }

        /*se l'ordine di lavoro è chiuso ma non è una marcatura ANT
        elseif ($timb['ind_chiuso']=='S' && substr($timb['des_note'],0,3)!="ANT") {
            //concerto non ha l'orario di stampa della fattura
            $timb['d_fine']=$timb['d_inizio'];
            $timb['o_fine']=date('H:i');
            $timb=$this->chiudiMarcatura($timb);
        }*/

        elseif ($uscita) {

            $ttt=isset($uscita['flag'])?'chiuso':'badge';

            /*{ "IDDIP": 51,
                "d": "20210907",
                "h": "16:32",
                "VERSOO": "U",
                "IDTIMBRATURA": 604737,
                "forza_minuti": -1 }
            */

            //$this->log[]=$tl?'ok':'ko';

            //cerca il primo intervallo FALSE compreso dall'inizio della timbratura e la marcatura di uscita

            if ($tl) {

                $fineturno=false;
                
                $temp=$tl->checkFlag(mainFunc::gab_stringtomin($uscita['h']));

                //se la timbratura è in uscita ed è dentro ad un periodo valido corrisponde alla chiusura della marcatura
                if ($temp && $uscita['VERSOO']=='U') {
                    $fineTurno=mainfunc::gab_mintostring($temp['start']);
                }
                else {
                    $fineTurno=mainfunc::gab_mintostring($tl->getFineTurno(mainFunc::gab_stringtomin($timb['o_inizio'])));
                }

                //$this->log[]=$fineTurno?'ok':'ko';

                if ($fineTurno && $fineTurno!='00:00') {

                    $timb['d_fine']=$timb['d_inizio'];

                    if ($fineTurno<$uscita['h']) $timb['o_fine']=$fineTurno;
                    else $timb['o_fine']=$uscita['h'];

                    //UPDATE DB
                    //$this->log[]=$uscita;
                    //$this->log[]=$fineTurno;
                    $timb=$this->chiudiMarcatura($timb);
                    $this->timbLog($ttt.'Fine',$timb,$uscita);
                }

                else {
                    $timb['d_fine']=$timb['d_inizio'];
                    $timb['o_fine']=$uscita['h'];
                    $timb=$this->chiudiMarcatura($timb);
                    $this->timbLog($ttt,$timb,$uscita);
                }
            }

            //se non è definito nessun turno chiudi alla badgiata
            else {
                $timb['d_fine']=$timb['d_inizio'];
                $timb['o_fine']=$uscita['h'];
                $timb=$this->chiudiMarcatura($timb);
                $this->timbLog($ttt.'Notl',$timb,$uscita);
            }

        }

        //ritorna la timbratura come è stata effettivamente riscritta sul DB o la stessa se NON è stata modificata
        return $timb;
    }

    function timbLog($tipo,$timb,$uscita) {
        //scrive un log se correggiAlan ha chiuso una marctura

        //[{"num_rif_movimento":"1000070","cod_inconveniente":"G","cod_operaio":"50","num_riga":3302,"d_inizio":"20240227","o_inizio":"14:56","d_fine":"20240227","o_fine":"15:00","qta_ore_lavorate":".07","des_note":"SER{\"coll\":\"138\",\"rif\":\"\",\"lam\":\"\",\"dms\":null}","num_rif_riltem":378705,"dms":"concerto"},false]

        $file='C:/timbLog/'.time().'_'.$tipo.'.txt';
        $txt=json_encode(array($timb,$uscita));
        file_put_contents($file, $txt);
    }

    function leggiMarcatura($dms,$id,$ambito) {
        //per infinity, ambito è 'regolare' || 'speciale'
        //anche se sembra che il metodo non venga mai invocato per leggere marcature speciali ?????

        $timb=false;
        $m=array();

        if ($dms!=$this->actualDms) {
            $this->setGalileo($dms);
        }

        $m=$this->defaultMap[$dms];

        //////////////////////////////////////////////////////
        $this->galileo->clearQuery();
        $this->galileo->clearQueryOggetto('default','odl'); 

        $arg=array(
            "ID"=>$id
        );
        if ($ambito=='regolare') {
            $this->galileo->executeGeneric('odl','getMarcaturaByID',$arg,'');
        }
        elseif ($ambito=='speciale') {
            $this->galileo->executeGeneric('odl','getMarcaturaSpecialeByID',$arg,'');
        }
        $m['result']=$this->galileo->getResult();

        if ($m['result']) {

            $fid=$this->galileo->preFetchPiattaforma($m['piattaforma'],$m['result']);

            while ($row=$this->galileo->getFetchPiattaforma($m['piattaforma'],$fid)) {
                $timb=$row;
                //il metodo non fa gli stessi controlli di "calcolaMarcatureAperte" perché vuole essere solo
                //una lettura in preparazione ad una riscrittura.
                //cambiare il codice operaio è però necessario per agire in multidms
                if (!array_key_exists($row['cod_operaio'],$this->linkDmsRef[$m['dms']])) return false;
                $timb['cod_operaio']=$this->linkDmsRef[$m['dms']][$row['cod_operaio']];
                $timb['dms']=$this->actualDms;
                if ($ambito=='speciale') $timb['fittizio']=1;
            }

        }

        return $timb;
    }

    function verificaSpeciale($timb) {
        //modifica la marcatura per la riscrittura (concerto)

        $speciale=array(
            "flag"=>false,
            "codice"=>"",
            "obj"=>false
        );

        $speciale['codice']=substr($timb['des_note'],0,3);

        if (isset($this->config['speciale'][$speciale['codice']])) {
        
            $speciale['obj']=json_decode(substr($timb['des_note'],3),true);
        }

        if ($speciale['obj']) {
            //questo va bene per CONCERTO e dovrebbe essere utile anche per infinity ma vedremo

            //PUL in realtà non viene più aperto nell'ordine fittizio ma serve per il periodo di transizione (viene riscritto solo se era stato spostato in fittizio da Start&Stop)
            //inoltre è possibile che PUL sia nell'ordine fittizio nel caso venga chiusa la marcatura in anticipo
            if ($timb['dms']==$speciale['obj']['dms']) {
                if ($speciale['codice']=='ANT' || ($speciale['codice']=='PUL' && $timb['num_rif_movimento']!=$speciale['obj']['rif']) ) {

                    if ($speciale['codice']=='ANT') {
                        $timb['des_note']="";
                    }
                    $timb['num_rif_movimento']=$speciale['obj']['rif'];
                    $timb['cod_inconveniente']=$speciale['obj']['lam'];
                    $timb['num_riga']="###(SELECT isnull(max(num_riga),0)+1 FROM OF_RILTEM WHERE num_rif_movimento='".$timb['num_rif_movimento']."' AND cod_inconveniente='".$timb['cod_inconveniente']."')";
                }
            }
        }

        return $timb;

    }

    function chiudiMarcatura($timb) {
        // {"num_rif_movimento":"1360428","cod_inconveniente":"A","cod_operaio":"18","num_riga":1,"d_inizio":"20210903","o_inizio":"15:23","d_fine":"","o_fine":"","qta_ore_lavorate":".00","des_note":"","num_rif_riltem":331694,"dms":"concerto"}

        //#########################################
        //if ($timb['dms']=='infinity')return $timb;
        //#########################################

        if ($timb['dms']!=$this->actualDms) {
            $this->setGalileo($timb['dms']);
        }

        $this->galileo->clearQuery();
        $this->galileo->clearQueryOggetto('default','odl'); 

        $this->galileo->setTransaction(true);

        if ($timb['dms']=='concerto') {

            if ($timb['des_note']!="") {
                $timb=$this->verificaSpeciale($timb);
            }

            $timb['cod_operaio']=$this->linkRefDms['concerto'][$timb['cod_operaio']];

            $timb['dat_ora_inizio']=$timb['d_inizio'].' '.$timb['o_inizio'];
            $timb['dat_ora_fine']=$timb['d_fine'].' '.$timb['o_fine'];

            if ($timb['o_fine']!="" && $timb['o_fine']>$timb['o_inizio'] && $timb['d_inizio']==$timb['d_fine']) {
                $timb['qta_ore_lavorate']=number_format( (mainFunc::gab_delta_min($timb['o_inizio'],$timb['o_fine']))/60,2,'.','');
            }
            else $timb['qta_ore_lavorate']=0;

        }

        elseif ($timb['dms']=='infinity') {

            $timb['matricola']=$this->linkRefDms['infinity'][$timb['cod_operaio']];
            $timb['id_ordine']=$timb['num_rif_riltem'];

            if ($timb['o_fine']!="" && $timb['o_fine']>$timb['o_inizio'] && $timb['d_inizio']==$timb['d_fine']) {
                $timb['tempo_calcolato']=number_format( (mainFunc::gab_delta_min($timb['o_inizio'],$timb['o_fine']))/60,2,'.','');
            }
            else $timb['tempo_calcolato']=0;

            //se la causale non è selezionata=>imposta "inlavorazione"
            if (isset($timb['statoLamentato']) && $timb['statoLamentato']!="" && $timb['statoLamentato']!="jump") {
                $timb['causale']=$timb['statoLamentato'];
            }
            else $timb['causale']='6';

            $timb['data_fine']=$timb['d_fine'];
            $timb['ora_fine']=$timb['o_fine'];
    
        }

        ///////////////////////////////////////////////////////
        if (!$this->galileo->executeGeneric('odl','updateTimbratura',$timb,'') ) return false;
        //$this->galileo->executeGeneric('odl','updateTimbratura',$timb,'');
        $resvar=$this->galileo->getResvar();

        $res=false;

        foreach ($resvar as $r) {

            if (isset($r['num_rif_movimento'])) {
                $res=$r;
                $res['dms']=$this->actualDms;
            }
        }

        //echo '<div>'.json_encode($this->galileo->getLog('query')).'</div>';

        //$this->log[]=$this->galileo->getLog('query');
        //($this->log[]=$res;

        $this->galileo->setTransaction(false);

        //echo json_encode($this->galileo->getLog('query'));
        //echo json_encode($timb);
        
        return $res;

    }

    function apriMarcatura($timb,$dms) {
       
        $this->galileo->clearQuery();
        $this->galileo->clearQueryOggetto('default','odl');

        //se concerto verifica prima se ci sono marcature aperte
        /*if ($dms=='concerto') {

            $this->calcolaMarcatureAperte();
    
           if (array_key_exists($timb['cod_operaio'],$this->marcature) && $this->marcature[$timb['cod_operaio']]['d_fine']=='') {
                return;
            }

            $this->galileo->clearQuery();
            $this->galileo->clearQueryOggetto('default','odl');
        }*/

        $this->galileo->setTransaction(true);

        ///////////////////////////////////////////////////////
        $this->galileo->executeGeneric('odl','apriTimbratura',$timb,'');

        $this->galileo->setTransaction(false);
        
        //non restituisce nulla
        return;

    }

    function apriSpeciale($timb,$dms) {

        //{"num_rif_movimento":"945604","cod_inconveniente":"","cod_operaio":"18","dat_ora_inizio":"20211019 15:34","specialTag":{"coll":"","rif":"1370162","lam":"A","limite":0.1},"speciale":"ATT"}

        $this->galileo->clearQuery();
        $this->galileo->clearQueryOggetto('default','odl');

        /*if ($dms=='concerto') {

            $this->calcolaMarcatureAperte();
    
           if (array_key_exists($timb['cod_operaio'],$this->marcature) && $this->marcature[$timb['cod_operaio']]['d_fine']=='') {
                return;
            }

            $this->galileo->clearQuery();
            $this->galileo->clearQueryOggetto('default','odl');
        }*/

        $this->galileo->setTransaction(true);

        ///////////////////////////////////////////////////////
        $this->galileo->executeGeneric('odl','apriTimbraturaSpeciale',$timb,'');

        $this->galileo->setTransaction(false);

        //echo json_encode($this->galileo->getLog('query'));
        
        //non restituisce nulla
        return;

    }

    function spostaMarcatura($timb) {

       //in pratica è un update

        $this->galileo->clearQuery();
        $this->galileo->clearQueryOggetto('default','odl');

        if ($timb['dms']=='concerto') {
            return $this->galileo->executeUpdate('odl','OF_RILTEM',$timb,"num_rif_riltem='".$timb['num_rif_riltem']."'");
        }

        return false;
    }

    function restartMarcatura($dms,$id) {

        $this->galileo->clearQuery();
        $this->galileo->clearQueryOggetto('default','odl');

        //l'inizializzazione di Galileo avviene in "leggiMarcatura"
        //la stringa 'regolare' serve per specificare la tabella di infinity
        $old=$this->leggiMarcatura($dms,$id,'regolare');

        if (!$old) return;

        //per sicurezza
        if ($old['des_note']!="") return;
        if ($old['ind_chiuso']=='S') return;

        //dopo la lettura della marcatura "cod_operaio" è quello definito da NEBULA quindi deve essere riconvertito nel DMS
        if (!array_key_exists($old['cod_operaio'],$this->linkRefDms[$this->actualDms])) return;

        $this->galileo->clearQuery();
        $this->galileo->clearQueryOggetto('default','odl');

        if ($dms=='concerto') {

            $arr=array(
                "num_rif_movimento"=>$old['num_rif_movimento'],
                "cod_inconveniente"=>$old['cod_inconveniente'],
                "cod_operaio"=>$this->linkRefDms['concerto'][$old['cod_operaio']],
                "dat_ora_inizio"=>date('Ymd H:i'),
                "statoLamentato"=>"L"
            );

        }
        elseif ($dms=='infinity') {

            $arr=array(
                "anno"=>$old['anno'],
                "id_cliente"=>$old['id_cliente'],
                "data_doc"=>$old['data_doc'],
                "tipo_doc"=>$old['tipo_doc'],
                "numero_doc"=>$old['numero_doc'],
                "id_riga"=>$old['cod_inconveniente'],
                "id_inconveniente"=>"NULL",
                "matricola"=>$this->linkRefDms['infinity'][$old['cod_operaio']],
                "data_inizio"=>date('Ymd'),
                "ora_inizio"=>date("H:i:s"),
                "data_fine"=>"NULL",
                "ora_fine"=>"NULL",
                "tempo_calcolato"=>'0.00',
                "causale"=>"NULL",
                "tempo_fatturato_man"=>'0.00',
                "tempo_fatturato"=>'0.00',
                "id_utente"=>'57',
                "data_modifica"=>date("Y-m-d H:i:s"),
                "note"=>"",
            );

        }

        //$this->galileo->executeInsert('odl','OF_RILTEM',$arr);
        $this->apriMarcatura($arr,$dms);

        //echo json_encode($this->galileo->getLog('query'));

    }

    function fineMarcatura($param) {

        //l'inizializzazione di Galileo avviene in "leggiMarcatura"
        //la stringa 'regolare' serve per indicare la tabella di infinity
        $ambito='regolare';
        if (isset($param['ambito'])) {
            if ($param['dms']=='infinity' && $param['ambito']=='speciale') $ambito='speciale';
        }
        $old=$this->leggiMarcatura($param['dms'],$param['ID'],$ambito);

        //echo $param['dms'].' '.$param['ID'];

        if (!$old) return;

        if (isset($param['d_fine']) && isset($param['o_fine']) ) {
            $old['d_fine']=$param['d_fine'];
            $old['o_fine']=$param['o_fine'];
        }
        else {
            $old['d_fine']=date('Ymd');
            $old['o_fine']=date('H:i');
        }

        if (isset($param['statoLamentato']) && $param['statoLamentato']!="") {

            if ($param['statoLamentato']!='jump') {
                $old['statoLamentato']=$param['statoLamentato'];
            }
        }

        $this->chiudiMarcatura($old);
    }

    function inizioMarcatura($param) {
        //dms,IDcoll,odl,lam

            //echo json_encode($this->linkRefDms);

            //echo json_encode($this->linkDmsRef);

        $this->calcolaMarcatureAperte();
        //$marcature[$row['cod_operaio']] - "cod_operaio" è l'ID collaboratore di NEBULA
        //se esiste una marcatura aperta per l'IDcoll CHIUDILA

        $chiusa=false;

        if (array_key_exists($param['IDcoll'],$this->marcature) && $this->marcature[$param['IDcoll']]['d_fine']=='') {

            $old=$this->marcature[$param['IDcoll']];
            //d_fine deve avere lo stesso formato di d_inizio
            $old['d_fine']=date('Ymd');
            $old['o_fine']=date('H:i');

            if (isset($param['statoLamentato']) && $param['statoLamentato']!="") {
                if ($param['statoLamentato']!='jump') {
                    $old['statoLamentato']=$param['statoLamentato'];
                }
            }

            //echo json_encode($old);

            $chiusa=$this->chiudiMarcatura($old);

        }
        else $chiusa=array('d_fine'=>'chiusa');

        //se $res di ritorno da "chiudiMarcatura" certifica l'avvenuta chiusura
        //APRI la nuova marcatura

        if ($chiusa && $chiusa['d_fine']!="") {

            if ($param['dms']!=$this->actualDms) {
                $this->setGalileo($param['dms']);
            }

            if ($param['dms']=='concerto') {

                $arr=array(
                    "num_rif_movimento"=>$param['odl'],
                    "cod_inconveniente"=>$param['lam'],
                    "cod_operaio"=>$this->linkRefDms['concerto'][$param['IDcoll']],
                    "dat_ora_inizio"=>date('Ymd H:i'),
                    "statoLamentato"=>"L"
                );
            }
            
            if ($param['dms']=='infinity') {

                $this->galileo->clearQuery();
                $this->galileo->clearQueryOggetto('default','odl');

                //legge i dati dell'ordine di lavoro
                $temp=false;
                $arg=array(
                    "num_rif_movimento"=>$param['odl'],
                    "lista"=>"cli",
                    "tipo"=>"aperti"
                );
                $this->galileo->executeGeneric('odl','getOdlLamentati',$arg,'');
                $result=$this->galileo->getResult();
                if ($result) {

                    $fid=$this->galileo->preFetch('odl');

                    while ($row=$this->galileo->getFetch('odl',$fid)) {
                        if ($row['lam']!=$param['lam']) continue;
                        $temp=$row;
                        break;
                    }
                }

                if (!$temp) return;

                $arr=array(
                    "anno"=>$temp['anno'],
                    "id_cliente"=>$temp['id_cliente'],
                    "tipo_doc"=>$temp['tipo_doc'],
                    "data_doc"=>$temp['data_doc'],
                    "numero_doc"=>$temp['numero_doc'],
                    "id_riga"=>$param['lam'],
                    "id_inconveniente"=>"NULL",
                    "matricola"=>$this->linkRefDms['infinity'][$param['IDcoll']],
                    "data_inizio"=>date('Ymd'),
                    "ora_inizio"=>date("H:i:s"),
                    "data_fine"=>"NULL",
                    "ora_fine"=>"NULL",
                    "tempo_calcolato"=>'0.00',
                    "causale"=>"NULL",
                    "tempo_fatturato_man"=>'0.00',
                    "tempo_fatturato"=>'0.00',
                    "id_utente"=>'57',
                    "data_modifica"=>date("Y-m-d H:i:s"),
                    "note"=>"",
                );
            } 

            $this->apriMarcatura($arr,$param['dms']);
        }

        //echo json_encode($this->galileo->getLog('query'));

    }

    function inizioSpeciale($IDcoll,$checkID,$param) {

        /*
        $param=array(
            "statoLamentato"=>$statoLamentato,
            "speciale"=>"ATT",
            "dms"=>$temp[0],
            "officinaDms"=>$temp[1]
        );
        */

        //"calcola marcature aperte" è già stato chiamato dalla classe chiamante
        //checkID era l'ID della marcatura al momento del rendering della pagina web
        //officina DMS è il codice del DMS dell'officina dove deve essere aperta la marcatura fittizia

        //se esiste la marcatura aperta chiudila
        $actual=false;
        if (array_key_exists($IDcoll,$this->marcature)) $actual=$this->marcature[$IDcoll];
        //checkID era l'ID della marcatura al momento del rendering della pagina web
        if ($actual && $actual['num_rif_riltem']!=$checkID) return false;

        if ($actual && $actual['d_fine']=='') {
            $actual['d_fine']=date('Ymd');
            $actual['o_fine']=date('H:i');
            $actual['statoLamentato']=$param['statoLamentato'];

            $res=$this->chiudiMarcatura($actual);
        }
        else {
            $res=$actual;
        }

        //se la marcatura è stata chiusa allora apri la marcatura speciale
        if (!$actual || $res['d_fine']!="") {

            $rif=false;

            if (isset($this->config['OOS'][$param['dms']][$param['officinaDms']])) {
                $rif=$this->config['OOS'][$param['dms']][$param['officinaDms']];
            }

            if (!$rif) return false;

            //////////////////////////////////////////////

            $timb=array();

            ///////////////////////////////////////////////
            switch($param['speciale']) {

                case "ATT":
                    $timb['specialTag']=array(
                        "coll"=>$IDcoll,
                        "rif"=>($actual)?$actual['num_rif_movimento']:"",
                        "lam"=>($actual)?$actual['cod_inconveniente']:"",
                        "dms"=>$actual['dms'],
                        "limite"=>0.1
                    );
                    $timb['speciale']='ATT';
                break;

                case "SER":
                    $timb['specialTag']=array(
                        "coll"=>$IDcoll,
                        "rif"=>($actual)?$actual['num_rif_movimento']:"",
                        "lam"=>($actual)?$actual['cod_inconveniente']:"",
                        "dms"=>$actual['dms'],
                    );
                    $timb['speciale']='SER';
                break;

                case "PRV":
                    $timb['specialTag']=array(
                        "coll"=>$IDcoll,
                        "rif"=>$param['rifOdlPRV'],
                        "lam"=>'',
                        "limite"=>0.3,
                        "odl"=>$param['rifOdlPRV'],
                        "dms"=>$actual['dms']
                    );
                    $timb['speciale']='PRV';
                break;
            }

            //////////////////////////////////////////

            if ($param['dms']=='concerto') {

                if ($this->actualDms!='concerto') {
                    $this->setGalileo('concerto');
                }

                $timb["num_rif_movimento"]=$rif;
                $timb["cod_inconveniente"]="";
                $timb["cod_operaio"]=$this->linkRefDms['concerto'][$IDcoll];
                $timb["dat_ora_inizio"]=date('Ymd H:i');

                ////////////////////////////////////////////
                //codice inconveniente
                $this->galileo->clearQuery();
                $this->galileo->clearQueryOggetto('default','odl');
                $this->galileo->executeGeneric('odl','getCodIncRiltem',$timb,'');
                $result=$this->galileo->getResult();
                if ($result) {

                    $fid=$this->galileo->preFetchBase('maestro');

                    while ($row=$this->galileo->getFetchBase('maestro',$fid)) {
                        $timb['cod_inconveniente']=$row['cod_inconveniente'];
                    }
                }
                else return false;

                if (!$timb['cod_inconveniente'] || $timb['cod_inconveniente']=="") return false;
            }

            else if ($param['dms']=='infinity') {

                if ($this->actualDms!='infinity') {
                    $this->setGalileo('infinity');
                }

                $timb["matricola"]=$this->linkRefDms['infinity'][$IDcoll];
                $timb["tempo_calcolato"]='0.00';
                $timb["data_inizio"]=date('Ymd');
                $timb["ora_inizio"]=date('H:i');
                $timb["data_modifica"]=date('Y-m-d H:i:s');
                $timb["id_utente"]="57";

                switch($timb['speciale']) {

                    case "SER":
                        $timb['cod_spesa']='MM';
                    break;

                    case "ATT":
                        $timb['cod_spesa']='MA';
                    break;
                }
            }

            //echo json_encode($timb);

            $this->apriSpeciale($timb,$param['dms']);

            return true;

        }

    }

    function inizioPUL($IDcoll,$checkID,$param,$odlFunc) {

        $actual=false;
        if (array_key_exists($IDcoll,$this->marcature)) $actual=$this->marcature[$IDcoll];

        //se non c'è una marcatura aperta ritorna
        if (!$actual) return false;
        //checkID era l'ID della marcatura al momento del rendering della pagina web
        if ($actual && $actual['num_rif_riltem']!=$checkID) return false;

        ///////////////////////////////////////////////
        if ($this->actualDms!=$actual['dms']) $this->setGalileo($actual['dms']);
        ///////////////////////////////////////////////

        //se il tecnico ha già marcato PUL nel lamentato non accettarlo
        $operaio=$this->getDmsRef($IDcoll,$actual['dms']);
        if (!$operaio) return false;

        $ore=array(
            "oreTotali"=>0,
            "orePul"=>0
        );

        $result=$odlFunc->getLamMarcato($actual['num_rif_movimento'],$actual['cod_inconveniente'],$actual['dms'],$operaio);

        if ($result) {

            $pf=$odlFunc->getPiattaforma();
            $fid=$this->galileo->preFetchPiattaforma($pf,$result);

            while($row=$this->galileo->getFetchPiattaforma($pf,$fid)) {

                $ore['oreTotali']+=$row['qta_ore_lavorate'];

                if (substr($row['des_note'],0,3)=='PUL') {
                    $ore['orePul']+=$row['qta_ore_lavorate'];
                }
            }

        }
        else return false;

        if ($ore['orePul']>0) return false;

        ///////////////////////////////////////////////////////////////////////////////

        if ($actual) {
            $actual['d_fine']=date('Ymd');
            $actual['o_fine']=date('H:i');
            $actual['statoLamentato']=$param['statoLamentato'];

            $res=$this->chiudiMarcatura($actual);
        }

        //echo json_encode($res);
        //{"anno":"2022","id_cliente":37079,"data_doc":"2022-02-02 00:00:00.000","tipo_doc":"LO01","numero_doc":284,"id_riga":2,"cod_inconveniente":2,"num_rif_movimento":279,"cod_officina":"PV","cod_movimento":"OOP","cod_operaio":"056","d_inizio":"20220208","o_inizio":"18:53","d_fine":"20220208","o_fine":"18:53","qta_ore_lavorate":"0.00","des_note":"","num_rif_riltem":100,"ind_chiuso":"N","dms":"infinity"}

        if ($res['d_fine']!="") {

            //calcola il LIMITE in base al totale delle ore marcate dal tecnico nel lamentato appena chiuso
            $limite=0;
            if ($ore['oreTotali']>=2) {
                $limite=(round($ore['oreTotali']))/100;
                if ($limite>0.1) $limite=0.1;
            }

            $timb=array();

            $timb['specialTag']=array(
                "coll"=>$IDcoll,
                "rif"=>$actual['num_rif_movimento'],
                "lam"=>$actual['cod_inconveniente'],
                "limite"=>$limite
            );
            $timb['speciale']='PUL';

            if ($actual['dms']=='concerto') {

                $timb["num_rif_movimento"]=$actual['num_rif_movimento'];
                $timb["cod_inconveniente"]=$actual['cod_inconveniente'];
                $timb["cod_operaio"]=$this->linkRefDms['concerto'][$IDcoll];
                $timb["dat_ora_inizio"]=date('Ymd H:i');
                $timb['des_note']='PUL'.json_encode($timb['specialTag']);
            }

            if ($actual['dms']=='infinity') {

                $timb["anno"]=$res['anno'];
                $timb["id_cliente"]=$res['id_cliente'];
                $timb["tipo_doc"]=$res['tipo_doc'];
                $timb["data_doc"]=$res['data_doc'];
                $timb["numero_doc"]=$res['numero_doc'];
                $timb["id_riga"]=$res['cod_inconveniente'];
                $timb["id_inconveniente"]="NULL";
                $timb["matricola"]=$this->linkRefDms['infinity'][$IDcoll];
                $timb["data_inizio"]=date('Ymd');
                $timb["ora_inizio"]=date('H:i');
                $timb["data_fine"]="NULL";
                $timb["ora_fine"]="NULL";
                $timb["tempo_calcolato"]='0.00';
                $timb["causale"]="NULL";
                $timb["tempo_fatturato_man"]='0.00';
                $timb["tempo_fatturato"]='0.00';
                $timb["data_modifica"]=date('Y-m-d H:i:s');
                $timb["id_utente"]="57";
                $timb['note']='PUL'.json_encode($timb['specialTag']);
            }

            $this->apriMarcatura($timb,$actual['dms']);

            //echo json_encode($this->galileo->getLog('query'));

            return true;
        }
        else return false;

    }

    function inizioANT($IDcoll,$checkID) {

        $actual=false;
        if (array_key_exists($IDcoll,$this->marcature)) $actual=$this->marcature[$IDcoll];

        //se non c'è una marcatura aperta ritorna
        if (!$actual) return false;
        //checkID era l'ID della marcatura al momento del rendering della pagina web
        if ($actual && $actual['num_rif_riltem']!=$checkID) return false;

        $rif=false;
        //il riferimento deve essere compatibile con il DMS della marcatura originale
        if (isset($this->config['OOS'][$actual['dms']][$actual['cod_officina']])) {
            $rif=$this->config['OOS'][$actual['dms']][$actual['cod_officina']];
        }

        if (!$rif) return false;

        $timb=array();

        //è possibile chiudere anticipatamente anche le marcature PUL che normalmente rimangaono dell'odl di origine
        if(substr($actual['des_note'],0,3)=='PUL') {
            $timb['speciale']='PUL';
        }

        else if ($actual['des_note']=="") {

            $temp=array(
                "coll"=>$IDcoll,
                "rif"=>$actual['num_rif_movimento'],
                "lam"=>$actual['cod_inconveniente']
            );

            $timb['des_note']='ANT'.json_encode($temp);

            $timb['speciale']='ANT';

        }
        else return false;

        if ($actual['dms']=='concerto') {

            if ($this->actualDms!='concerto') {
                $this->setGalileo('concerto');
            }

            $timb["num_rif_movimento"]=$rif;
            $timb["cod_inconveniente"]="";
            $timb['dms']=$actual['dms'];
            $timb['num_rif_riltem']=$actual['num_rif_riltem'];

            ////////////////////////////////////////////
            //codice inconveniente
            $this->galileo->clearQuery();
            $this->galileo->clearQueryOggetto('default','odl');
            $this->galileo->executeGeneric('odl','getCodIncRiltem',$timb,'');
            $result=$this->galileo->getResult();
            if ($result) {

                $fid=$this->galileo->preFetchBase('maestro');

                while ($row=$this->galileo->getFetchBase('maestro',$fid)) {
                    $timb['cod_inconveniente']=$row['cod_inconveniente'];
                }
            }
            else return false;

            if (!$timb['cod_inconveniente'] || $timb['cod_inconveniente']=="") return false;

        }

        return $this->spostaMarcatura($timb);
    }

    /////////////////////////////////////////////////////////////////
    
    //allinea
    function special_CHI($IDcoll,$tl,$checkID) {

        //"calcola marcature aperte" è già stato chiamato dalla classe chiamante
        if (!array_key_exists($IDcoll,$this->marcature)) return false;

        //checkID era l'ID della marcatura al momento del rendering della pagina web
        if ($this->marcature[$IDcoll]['num_rif_riltem']!=$checkID) return false;

        $fineTurno=mainfunc::gab_mintostring($tl->getFineTurno(mainFunc::gab_stringtomin($this->marcature[$IDcoll]['o_inizio'])));

        if ($fineTurno) {

            if ($fineTurno>$this->marcature[$IDcoll]['o_inizio']) return false;

            $res=array(
                "dms"=>$this->marcature[$IDcoll]['dms'],
                "ID"=>$this->marcature[$IDcoll]['num_rif_riltem'],
                "d_fine"=>$this->marcature[$IDcoll]['d_inizio'],
                "o_fine"=>$fineTurno
            );

            return $res;
        }
        else return false;

        return false;

    }

}

?>