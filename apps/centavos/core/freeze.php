<?php

include('default.php');

foreach ($nebulaParams['html'] as $coll=>$c) {

    $arr=array(
        'periodo'=>$nebulaParams['periodo'],
        'collaboratore'=>$coll,
        'html'=>$c
    );

    $galileo->executeInsert('centavos','CENTAVOS_freezed',$arr);

    $galileo->clearQuery();
    $galileo->clearQueryOggetto('default','centavos');
}

//////////////////////////
/*$p=False;

$galileo->executeSelect('centavos','CENTAVOS_freezed',"periodo='".$nebulaParams['periodo']."'",'');

if ($result=$galileo->getResult()) {

    $fid=$galileo->preFetch('centavos');

    while ($row=$galileo->getFetch('centavos',$fid)) {
        $p=true;
    }
}

if (!$p) die ('Errore scrittura DB');

$galileo->clearQuery();
$galileo->clearQueryOggetto('default','centavos');*/

$arr=array(
    'stato'=>'freezed'
);

$galileo->executeUpdate('centavos','CENTAVOS_periodi',$arr,"ID='".$nebulaParams['periodo']."'");

//echo json_encode($galileo->getLog('query'));

?>