<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");

$param=$_POST['param'];

$alert="";

foreach ($param as $codice=>$c) {

    if ( !array_key_exists('edit',$c) ) continue;

    $galileo->clearQuery();
    $galileo->clearQueryOggetto('base','schemi');

    //"AU_RS":{"panorama":55,"skema":"AU_RS","turno":11,"collaboratore":"2","data_i":"20201201","data_f":"21001231","edit":{"op":"insert"}}}

    //leggere la data di fine max per i collegamenti di questo schema (potrebbe essere null="")
    $maxFine="";
    $maxInizio="";

    $galileo->getCollskMaxDate($c['panorama'],$c['collaboratore'],$c['skema']);
    $result=$galileo->getResult();
    if ($result) {
        $fetID=$galileo->preFetchBase('schemi');
        while($row=$galileo->getFetchBase('schemi',$fetID)) {
            $maxFine=$row['fine'];
            $maxInizio=$row['inizio'];
        }
    }

    $galileo->clearQuery();
    $galileo->clearQueryOggetto('base','schemi');


    if ($c['edit']['op']=='insert') {
        //se esite max data_f ed è minore della data_i che vogliamo inserire non eseguire
        if ($maxFine!="" && $maxfine<=$c['data_i']) {
            $alert.="impossibile inserire ".$codice." in data ".$c['data_i']." (".$maxFine.")\n";
        }
        else {
            //inserire il movimento
            $galileo->insertCollsk($c);
            $result=$galileo->getResult();
            if (!$result) {
                $alert("Inserimento ".$codice." non riuscito\n");
            }
        } 
    }
    else if ($c['edit']['op']=='delete') {
        //se la data_f da inserire è <= a max data_f non eseguire il movimento
        //non viene controllato l'inizio perché non sarebbe stato possibile inserire una data inizio inferiore di una data fine già esistente
        if ($maxFine!="" && $maxFine!="21001231" && $maxfine<=$c['data_f']) {
            $alert.="impossibile annullare ".$codice." in data ".$c['data_f']." (".$maxInizio.")\n";
        }
        else {
            //altrimenti aggiornare il collegamento con la data di fine
            $galileo->updateCollsk($c);
            $result=$galileo->getResult();
            if (!$result) {
                $alert("Aggiornamento ".$codice." non riuscito\n");
            }
        }
    }
    else if ($c['edit']['op']=='switch') {
        //considerare data_i(-1)
        $drif=strtotime("-1 day",mainFunc::gab_tots($c['data_i']));
        //se data_i(-1) da inserire <= maxFine e dataFine!="21001231" non eseguire
        if ($maxFine!="" && $maxFine!="21001231" && $maxFine<=date('Ymd',$drif) ) {
            $alert.="impossibile eseguire lo scambio ".$codice." in data ".$c['data_i']." (".$maxFine.")\n";
        }
        else {
            //altrimenti aggiorna il collegamento esistente con data_f=data_i(-1)
            //ed inserisci il nuovo movimento (transaction)
            //{"SB_PARCC":{"panorama":55,"collaboratore":2,"skema":"SB_PARCC","turno":12,"data_i":"20210813","data_f":"20210813","posizione":3,"colore":"#295CF7","edit":{"op":"switch","turnoBAK":"11","dataBAK":"20200701"}}
            $a=array();

            $a[0]=array(
                "panorama"=>$c['panorama'],
                "collaboratore"=>$c['collaboratore'],
                "skema"=>$c['skema'],
                "turno"=>$c['edit']['turnoBAK'],
                "data_i"=>$c['edit']['dataBAK'],
                "data_f"=>date('Ymd',$drif),
                "edit"=>array(
                    "op"=>"update"
                )
            );

            $a[1]=array(
                "panorama"=>$c['panorama'],
                "collaboratore"=>$c['collaboratore'],
                "skema"=>$c['skema'],
                "turno"=>$c['turno'],
                "data_i"=>$c['data_i'],
                "data_f"=>'21001231',
                "edit"=>array(
                    "op"=>"insert"
                )
            );

            
            
            $result=$galileo->switchCollsk($a);

            if (!$result) {
                $alert("Aggiornamento ".$codice." non riuscito\n");
            }
        }
    }

}
//"alert"=>json_encode($galileo->getLog('query'))
$arr=array(
    "alert"=>$alert,
    "query"=>json_encode($galileo->getLog('query'))
);

echo json_encode($arr);


?>