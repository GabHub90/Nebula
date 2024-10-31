<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/core/odl/pratica_func.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/core/odl/odl_func.php");

include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_alert.php');

$obj=new galileoAlert();
$nebulaDefault['alert']=array("gab500",$obj);

$galileo->setFunzioniDefault($nebulaDefault);

$param=$_POST['param'];

$tempAlert=json_decode(base64_decode($param['alert']),true);

foreach ($tempAlert as $k=>$a) {
    $tempAlert[$k]['utente']=$param['utente'];
}

/////////////////////////////////////////////////////
//scrivi o modifica il record stato_lam
$arr=array(
    "pratica"=>$param['pratica'],
    "dms"=>$param['dms'],
    "rif"=>$param['rif'],
    "pren"=>$param['pren'],
    "lam"=>isset($param['lam'])?$param['lam']:'',
    "stato"=>isset($param['stato'])?$param['stato']:'XX',
    "dataora"=>date('Ymd:H:i'),
    "utente"=>$param['utente'],
    "scadenza"=>(isset($param['scadenza']) && $param['scadenza']!="")?str_replace('-','',$param['scadenza']):'',
    "alert"=>json_encode($tempAlert),
    "nota"=>iconv('UTF-8','ISO-8859-1', $param['nota']),
    "prevfine"=>"xxxxxxxx:xx:xx"
);

if (isset($param['d']) && isset($param['ora']) && $param['d']!="" && $param['ora']!="") $arr['prevfine']=mainFunc::gab_input_to_db($param['d']).':'.$param['ora'];

$wclause="pratica='".$param['pratica']."' AND dms='".$param['dms']."' AND rif='".$param['rif']."' AND lam='".$param['lam']."' AND pren='".$param['pren']."'";

$galileo->executeUpsert('alert','AVALON_stato_lam',$arr,$wclause);

/////////////////////////////////////////////////////

//$odlFunc=new nebulaOdlFunc($galileo);
//$odlFunc->setGalileo($param['dms']);
//$galileo=$odlFunc->exportGalileo();


//##########################################
//occorre sostituire la riscrittura dell'alert con la riscrittura della prtatica
//oppure ricaricare la pagina
//##########################################

/*$pratica=new nebulaPraticaFunc($param['pratica'],$param['dms'],$param['pren'],$odlFunc);

if ($param['pren']=='S') {

    $a=array(
        "num_rif_movimento"=>$param['rif']
    );

    $galileo->executeGeneric('odl','getPrenotazioni',$a,'');
}
else {
    $a=array(
        "num_rif_movimento"=>$param['rif'],
        "tipo"=>'aperti'
    );

    $galileo->executeGeneric('odl','getCliLamentati',$a,'');
}

$a=false;

if ($galileo->getResult()) {

    $primo=false;

    $fid=$galileo->preFetch('odl');
    while($row=$galileo->getFetch('odl',$fid)) {

        if (!$primo) {
            $pratica->setDefaultAlert();
            $primo=true;
        }

        $pratica->addLam($row);
    }

    $a=array(
        "rif"=>$param['rif'],
        "lam"=>$param['lam'],
        "nota"=>base64_encode($pratica->getNota($param['rif'],$param['lam'])),
        "line"=>""
    );

    //$a['nota']=base64_encode('prova');

    ob_start();
        $pratica->drawLine($param['rif'],$param['lam'],$param['edit'],$param['pren']);
    $a['line']=ob_get_clean();
}

//$a['line']=json_encode($galileo->getLog('query'));

echo json_encode($a);*/

?>