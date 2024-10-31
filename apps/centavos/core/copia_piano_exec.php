<?php

include('default.php');

$old=false;
$var=array();
$mod=array();
$par=array();
$idx=array(
    "piani"=>false,
    "moduli"=>false,
    "varianti"=>false,
    "parametri"=>false
);

$galileo->executeSelect('centavos','CENTAVOS_piani',"ID='".$nebulaParams['old']."'",'');

if ($galileo->getResult()) {

    $fid=$galileo->preFetch('centavos');

    while ($row=$galileo->getFetch('centavos',$fid)) {
        $old=$row;
    }
}

if ($old) {

    $galileo->clearQuery();

    $galileo->executeSelect('centavos','CENTAVOS_varianti',"piano='".$old['ID']."'",'');

    if ($galileo->getResult()) {

        $fid=$galileo->preFetch('centavos');

        while ($row=$galileo->getFetch('centavos',$fid)) {
            $var[$row['ID']]['row']=$row;
            $var[$row['ID']]['moduli']=array();
        }

    }

    foreach ($var as $k=>$v) {

        $temp=json_decode($v['row']['moduli'],true);
        
        foreach ($temp as $km=>$m) {

            $galileo->clearQuery();

            $galileo->executeSelect('centavos','CENTAVOS_moduli',"ID='".$m."'",'');
        
            if ($galileo->getResult()) {
        
                $fid=$galileo->preFetch('centavos');
        
                while ($row=$galileo->getFetch('centavos',$fid)) {
                    $var[$k]['moduli'][$row['ID']]=$row;
                    $mod[$row['ID']]=array(
                        "newID"=>"",
                        "titolo"=>$row['titolo'],
                        "map"=>array("p"=>json_decode($row['principali'],true),"m"=>json_decode($row['modificatori'],true))
                    );
                }
            }
        }
    }

    //$in="";

    foreach ($var as $k=>$v) {

        foreach ($v['moduli'] as $km=>$m) {

            $temp=json_decode($m['principali'],true);

            if ($temp) {
                foreach ($temp as $a=>$b) {
                    if (!isset($par[$b])) {
                        //$in.="'".$b."'";
                        $par[$b]="";
                    }
                }
            }

            $temp=json_decode($m['modificatori'],true);

            if ($temp) {
                foreach ($temp as $a=>$b) {
                    if (!isset($par[$b])) {
                        //$in.="'".$b."'";
                        $par[$b]="";
                    }
                }
            }
        }
    }

    /////////////////////////////////////////////////////
    $galileo->clearQuery();

    $galileo->executeGeneric('centavos','maxIdx',array(),'');

    if ($galileo->getResult()) {

        $fid=$galileo->preFetch('centavos');

        while ($row=$galileo->getFetch('centavos',$fid)) {
            $idx=$row;
        }
    }

    //////////////////////////////////////////////////////////////////////////////
    //TRANSACTION
    $galileo->setTransaction(true);
    $galileo->clearQuery();
    $galileo->clearQueryOggetto('default','centavos');

    $a=array(
        "tag"=>$nebulaParams['desc'],
        "piano"=>$old,
        "varianti"=>$var,
        "moduli"=>$mod,
        "parametri"=>$par,
        "idx"=>$idx
    );

    ########################################
    //$galileo->setRollback(true);
    ########################################

    //creazione piano
    $galileo->executeGeneric('centavos','copyPiano',$a,'');

}

echo '<div>Operazione eseguita</div>';
echo '<div>Ricordati di cambiare le date di validità del PIANO</div>';

//echo '<div>'.json_encode($galileo->getLog('errori'));

//echo '<div>'.json_encode($old).'</div>';
//echo '<div>'.json_encode($var).'</div>';
//echo '<div>'.json_encode($mod).'</div>';
//echo '<div>'.json_encode($par).'</div>';
//echo '<div>'.json_encode($idx).'</div>';

?>