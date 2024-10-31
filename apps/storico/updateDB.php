<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/core/odl/odl_func.php");

$odlFunc=new nebulaOdlFunc($galileo);

//$marche=array('A','C','N','S','V','P');
$marche=array('A');

$default=array();

foreach ($marche as $marca) {

    $map=$odlFunc->getOTDefault($marca);

    if ($map['result']) {
        $fid=$galileo->preFetchPiattaforma($map['piattaforma'],$map['result']);

        while ($row=$galileo->getFetchPiattaforma($map['piattaforma'],$fid)) {
            $default[$marca][$row['codice']]=$row;
        }
    }
}

$gruppi=array();

/////////////////////////////////////////////////////////////////////
/*allinea le impostazioni dei GRUPPI 

foreach ($marche as $marca) {

    $map=$odlFunc->getOTGruppi($marca);

    if ($map['result']) {
        $fid=$galileo->preFetchPiattaforma($map['piattaforma'],$map['result']);

        while ($row=$galileo->getFetchPiattaforma($map['piattaforma'],$fid)) {

            $oggetti=json_decode($row['oggetti'],true);

            if (!$oggetti) continue;

            foreach ($oggetti as $codice=>$c) {

                unset($oggetti[$codice]['codice']);

                $oggetti[$codice]['mint']=0;
                $oggetti[$codice]['maxt']=0;
                $oggetti[$codice]['stet']=0;
                $oggetti[$codice]['minkm']=0;
                $oggetti[$codice]['maxkm']=0;
                $oggetti[$codice]['stekm']=0;

                if (isset($default[$marca][$codice])) {

                    $oggetti[$codice]['pcx']=$default[$marca][$codice]['pcx'];
                    $oggetti[$codice]['topt']=$default[$marca][$codice]['topt'];
                    $oggetti[$codice]['topkm']=$default[$marca][$codice]['topkm'];
                    $oggetti[$codice]['first_t']=$default[$marca][$codice]['first_t'];
                    $oggetti[$codice]['first_km']=$default[$marca][$codice]['first_km'];
                    
                }
            }

            $row['oggetti']=stripslashes(json_encode($oggetti));

            $gruppi[]=$row;

            //$odlFunc->updateOTGruppi($row);

        }

    }
}

foreach ($gruppi as $row) {

    $odlFunc->updateOTGruppi($row);

    echo '<div>'.$row['codice'].'</div>';

    sleep(2);

}*/

///////////////////////////////////////////////////////////////////////////////////////////
//allinea i criteri per modello

foreach ($marche as $marca) {

    $map=$odlFunc->getOTCriteriModello($marca,'');

    if ($map['result']) {
        $fid=$galileo->preFetchPiattaforma($map['piattaforma'],$map['result']);

        while ($row=$galileo->getFetchPiattaforma($map['piattaforma'],$fid)) {

            //if ($row['modello']<='9N31D4') continue;

            $oggetti=json_decode($row['edit'],true);

            if (!$oggetti) continue;

            $test=true;

            $obj=array();

            foreach ($oggetti as $codice=>$c) {

                //se è una riga già modificata salta tutto il blocco
                if (array_key_exists('first_t',$c)) {

                    $test=false;
                    break;
                }

                if ($c['flag_mov']=='del') $obj[$codice]=array('flag_mov'=>'del');

                else {

                    $obj[$codice]=array(
                        'dt'=>$c['dt'],
                        'mint'=>0,
                        'maxt'=>0,
                        'stet'=>0,
                        'first_t'=>($c['maxt']!=0)?$c['maxt']:0,
                        'dkm'=>$c['dkm'],
                        'minkm'=>0,
                        'maxkm'=>0,
                        'stekm'=>0,
                        'first_km'=>($c['maxkm']!=0)?$c['maxkm']:0,
                        'pcx'=>$default[$marca][$codice]['pcx'],
                        'topt'=>$default[$marca][$codice]['topt'],
                        'topkm'=>$default[$marca][$codice]['topkm'],
                        'flag_mov'=>'ok'
                    );
                }
            }

            if ($test) $gruppi[$row['marca']][$row['modello']]=$obj;

        }
    }
}

/*foreach ($gruppi as $marca=>$m) {

    foreach ($m as $modello=>$o) {

        $row=array(
            'marca'=>$marca,
            'modello'=>$modello,
            'edit'=>stripslashes(json_encode($o))
        );

        $odlFunc->updateOTCriteriMod($row);

        echo '<div>'.$marca.' - '.$modello.' - '.json_encode($o).'</div>';

        sleep(2);
    }
}*/

//echo '<div>'.json_encode($odlFunc->getLog()).'</div>';

?>