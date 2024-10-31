<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/apps/idesk/classi/inofficina.php");

$param=$_POST['param'];

$odlFunc=new nebulaOdlFunc($galileo);
$odlFunc->setGalileo($param['dms']);
$galileo=$odlFunc->exportGalileo();

$a=array(
    "reparto"=>$param['reparto'],
    "timeless"=>array(
        "pratica"=>base64_decode($param['pratica']),
        "officina"=>$param['officina']
    )
);

$inoff=new ideskInofficina($a,$galileo);

$inoff->setSearch(true);

$galileo->executeGeneric('odl','getCliLamentati',$a,'');

$a=false;

if ($galileo->getResult()) {

    $fid=$galileo->preFetch('odl');
    while($row=$galileo->getFetch('odl',$fid)) {

        $inoff->evalCore($row,array('dms'=>$row['dms']),true);
    }
    
}

$inoff->sortPratiche();

//$a['line']=json_encode($galileo->getLog('query'));

echo '<div style="margin-top:20px;">';
    //echo json_encode($galileo->getLog('query'));
    //$inoff->drawInofficina();
    $inoff->drawPratica();
echo '</div>';

?>