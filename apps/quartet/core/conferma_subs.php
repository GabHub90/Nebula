<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");

$param=$_POST['param'];

$galileo->delPanSubs($param['panorama']);

foreach ($param['subs'] as $pos=>$s) {

    $arr=array(
        "panorama"=>$param['panorama'],
        "subrep"=>$s['sub'],
        "cod_def"=>$s['cod_def'],
        "pos"=>$pos+1
    );

    $galileo->insertPanSub($arr);
}

$result=$galileo->executeSubs();

if (!$result) die ('Inserimento non riuscito '.json_encode($galileo->getLog('query')));

?>