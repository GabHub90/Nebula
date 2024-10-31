<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/apps/comest/classi/comest.php");

$param=$_POST['param'];

//construct inizializza galileo per COMEST
$c=new nebulaComest($galileo);

if ($param['operazione']=='cancella') {

    $galileo->setTransaction(true);

    $galileo->executeGeneric('comest','cancellaCommessa',array('rif'=>$param['commessa']),'');

    if ($galileo->getResult()) {
        echo 'Commessa '.$param['commessa'].' cancellata.';
    }
    else {
        echo 'ERRORE - la commessa '.$param['commessa'].' non è stata cancellata.';
    }
}

else if ($param['operazione']=='annulla') {

    $arg=array(
        "d_annullo"=>date('Ymd'),
        "utente_annullo"=>$param['utente']
    );

    $galileo->executeUpdate('comest','COMEST_commesse',$arg,"rif='".$param['commessa']."'");

    $c->init(array('rif'=>$param['commessa']));
    $c->draw();
}

?>