<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/core/odl/odl_func.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/apps/idesk/classi/wormhole.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/apps/idesk/classi/inofficina.php");

$param=$_POST['param'];

$odlFunc=new nebulaOdlFunc($galileo);
$wh=new ideskWHole($param['reparto'],$galileo);

$w=array(
    "inizio"=>date('Ymd'),
    "fine"=>date('Ymd')
);

$wh->build($w);

$officina=$odlFunc->getDmsrep($wh->getTodayDms(date('Ymd')),$param['reparto']);

$i=array(
    "reparto"=>$param['reparto'],
    "cliente"=>$param['cliente'],
    "officina"=>$officina
);

if ($param['cliente']=='apprip') {
    $i['timeless']=array(
        "officina"=>$officina
    );
}

$inoff=new ideskInofficina($i,$galileo);

//serve a cambiare il prefisso del div di edit degli alert e non farlo sovrapporre a quello che già esiste in un altro DIV
$inoff->setSearch(true);

$wh->getIdeskSearch($officina,$param['search'],$param['cliente']);

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