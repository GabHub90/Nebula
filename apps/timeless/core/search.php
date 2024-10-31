<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/core/odl/odl_func.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/apps/idesk/classi/wormhole.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/apps/idesk/classi/inofficina.php");

$param=$_POST['param'];

$odlFunc=new nebulaOdlFunc($galileo);
$wh=new ideskWHole($param['reparto'],$galileo);

$officina=$odlFunc->getDmsrep('infinity',$param['reparto']);

$i=array(
    "reparto"=>$param['reparto'],
    "timeless"=>array(
        "officina"=>$officina
    )
);

$inoff=new ideskInofficina($i,$galileo);

$inoff->setSearch(true); 

$w=array(
    "inizio"=>date('Ymd',strtotime("-6 month",time())),
    "fine"=>date('Ymd',strtotime("+2 month",time()))
);

$wh->build($w);

/*$a=array(
    "timeless"=>array(
        "officina"=>$odlFunc->getDmsrep('infinity',$param['reparto']),
        "inizio"=>$w['inizio'],
        "fine"=>$w['fine']
    )
);*/

$wh->getTimelessSearch($w['inizio'],$w['fine'],$officina,$param['search']);

//echo json_encode($a);

foreach ($wh->exportMap() as $k=>$m) {

    if ($m['result']) {
        $fid=$galileo->preFetchPiattaforma($m['piattaforma'],$m['result']);

        while ($row=$galileo->getFetchPiattaforma($m['piattaforma'],$fid) ) {

            //echo '<div>'.json_encode($row).'</div>';

            $inoff->evalCore($row,array('dms'=>$row['dms']),true);
        }
    }
}

$inoff->sortPratiche();

echo '<div style="position:relative;top:1%;height:99%;overflow:scroll;overflow-x:hidden;" >';

    echo '<div style="width:92%">';

        $inoff->drawInofficina(false);

    echo '</div>';

echo '</div>';


?>