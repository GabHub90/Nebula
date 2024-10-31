<?php

$galileo->clearQuery();
$galileo->clearQueryOggetto('default','comest');

$com=array(
    "fornitore"=>""
);

if (isset($param['commessa']['fornitore']) && count($param['commessa']['fornitore'])>0) {
    $com['fornitore']=json_encode($param['commessa']['fornitore']);
}

if ($conferma && (!isset($param['commessa']['d_apertura']) || $param['commessa']['d_apertura']=="") ) {
    $com['d_apertura']=date('Ymd');
    $com['utente_apertura']=isset($param['utente'])?$param['utente']:'';
}

$galileo->executeUpdate('comest','COMEST_commesse',$com,"rif='".$param['commessa']['rif']."'");

$rev=array(
    "preventivo"=>$param['revisione']['preventivo'],
    "riconsegna"=>$param['revisione']['riconsegna'],
    "nota"=>$param['revisione']['nota']
);

if ($conferma) {
    $rev['d_chiusura']=date('Ymd');
    $rev['utente_chiusura']=isset($param['utente'])?$param['utente']:'';
}

$galileo->clearQuery();
$galileo->clearQueryOggetto('default','comest');

$galileo->executeUpdate('comest','COMEST_revisioni',$rev,"commessa='".$param['revisione']['commessa']."' AND revisione='".$param['revisione']['revisione']."'");

//echo json_encode($galileo->getLog('query'));

if (isset($param['revisione']['righe'])) {

    foreach ($param['revisione']['righe'] as $k=>$r) {

        $riga=array(
            "descrizione"=>$r['descrizione'],
        );
        
        $galileo->clearQuery();
        $galileo->clearQueryOggetto('default','comest');

        $galileo->executeUpdate('comest','COMEST_lavorazioni',$riga,"commessa='".$r['commessa']."' AND revisione='".$r['revisione']."' AND ID='".$r['ID']."'");
    }
}

?>