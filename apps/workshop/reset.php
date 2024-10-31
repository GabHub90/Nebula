<?php

include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/function_class/nebula_id.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/function_class/app_base_class.php");
include('workshop_class.php');

include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_tempo.php');
//include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_alan.php');

$obj=new galileoTempo();
$nebulaDefault['tempo']=array("gab500",$obj);

$galileo->setFunzioniDefault($nebulaDefault);

/////////////////////////////////////////////
//recupero di tutti i reparti service
$galileo->getReparti('S','');
if ($result=$galileo->getResult()) {

    $ws=false;

    $fid=$galileo->preFetchBase('reparti');
    while ($row=$galileo->getFetchBase('reparti',$fid)) {

        //ob_start();

            if(!$ws) {

                $param=array(
                    'ribbon'=>array(
                        'wsp_officina'=>$row['reparto'],
                        'visuale'=>'reset'
                    ),
                    "nebulaFunzione"=>array('nome'=>'home')
                );

                $ws=new workshopApp($param,$galileo);
            }

            else $ws->init($row['reparto']);

            //$ws->draw();

        //ob_end_clean();
    
    }
}

echo json_encode($ws->getLog());
    

?>