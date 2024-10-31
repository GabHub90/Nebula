<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/core/magazzino/mag_func.php");

$param=$_POST['param'];

$ret=array(
    "saldi"=>"",
    "pren"=>"",
    "comm"=>""
);

$tempSaldi=array();

$magFunc=new nebulaMagFunc($galileo);
$magFunc->setWH($param['reparto']);

$magFunc->getOrdiniOfficinaPren();

ob_start();

    $ot=0;
    $rif=0;

    echo '<div style="position:relative;width:95%;">';

        foreach ($magFunc->getOrdini('officina','pren') as $k=>$m) {

            if ($m['qta_dev_ordfor']>0) {

                if (!array_key_exists($m['d_invio'],$tempSaldi)) $tempSaldi[$m['d_invio']]=array();
                if (!array_key_exists($m['rif_ot'],$tempSaldi[$m['d_invio']])) $tempSaldi[$m['d_invio']][$m['rif_ot']]=array();

                $tempSaldi[$m['d_invio']][$m['rif_ot']][$m['id_ofor']]=$m;
            }

            if ($rif!=$m['rif']) {

                echo '<div style="position:relative;width:100%;height:20px;border-top:2px solid black;" ></div>';

                echo '<div style="position:relative;width:100%;padding:2px;box-sizing:border-box;font-size:0.9em;font-weight:bold;" >';
                    echo '<div>';
                        echo '<div style="position:relative;display:inline-block;vertical-align:top;width:15%;" >'.$m['targa'].'</div>';
                        echo '<div style="position:relative;display:inline-block;vertical-align:top;width:30%;" >'.$m['telaio'].'</div>';
                        echo '<div style="position:relative;display:inline-block;vertical-align:top;width:55%;" >'.substr($m['des_veicolo'],0,35).'</div>';
                    echo '</div>';
                    echo '<div>';
                        echo '<div style="position:relative;display:inline-block;vertical-align:top;width:45%;" >'.substr($m['ragsoc'],0,35).'</div>';
                        echo '<div style="position:relative;display:inline-block;vertical-align:top;width:45%;" >- '.substr($m['util_ragsoc'],0,35).'</div>';  
                    echo '</div>';
                    echo '<div>';
                        echo '<div style="position:relative;display:inline-block;vertical-align:top;width:90%;text-align:left;';
                            if (date('Ymd')>$m['d_prenotazione']) echo 'color:red;';
                        echo '" >';
                            echo 'Pren: '.$m['rif'].' - '.mainFunc::gab_todata($m['d_prenotazione']).' '.$m['ora_prenotazione'];
                        echo '</div>';
                    echo '</div>';
                echo '</div>';

                $rif=$m['rif'];
            }

            if ($ot!=$m['rif_ot']) {

                echo '<div style="position:relative;width:100%;border:1px solid black;padding:2px;box-sizing:border-box;background-color:#dddddd;margin-top:5px;" >';
                    echo '<div style="position:relative;display:inline-block;vertical-align:top;width:40%;" >';
                        echo 'OT: '.$m['rif_ot'];
                    echo '</div>';
                    echo '<div style="position:relative;display:inline-block;vertical-align:top;width:60%;text-align:right;">';
                        echo $m['id_utente'].' ('.mainFunc::gab_todata($m['d_ordine']).')';
                    echo '</div>';
                echo '</div>';

                $ot=$m['rif_ot'];
            }

            $color='#777777';

            $dispo=$m['giacenza']-$m['impegnato']-$m['officina']-$m['interno'];
					
            if ($dispo>=0) $color='green';
            else {
                if ($m['qta_dev_ordfor']>0) $color='#e34dd8';
                elseif ($m['giacenza']>0) $color='orange';
                else $color='red';
            }

            echo '<div style="position:relative;width:100%;padding:2px;box-sizing:border-box;" >';
                    echo '<div style="position:relative;display:inline-block;vertical-align:top;width:4%;text-align:center;" >'.$m['precodice'].'</div>';
                    echo '<div style="position:relative;display:inline-block;vertical-align:top;width:22%;" >'.$m['articolo'].'</div>';
                    echo '<div style="position:relative;display:inline-block;vertical-align:top;width:3%;font-size:0.9em;text-align:center;" >'.$m['deposito'].'</div>';
                    echo '<div style="position:relative;display:inline-block;vertical-align:top;width:23%;" >';
                        echo '<div style="position:relative;display:inline-block;vertical-align:top;width:50%;text-align:right;font-weight:bold;color:'.$color.';" >'.number_format($m['quantita'],2,',','').'</div>';
                        echo '<div style="position:relative;display:inline-block;vertical-align:top;width:50%;text-align:center;font-size:0.9em;" >('.number_format($m['giacenza'],2,',','').')</div>';
                    echo '</div>';
                    echo '<div style="position:relative;display:inline-block;vertical-align:top;width:10%;text-align:right;" >&lt; '.number_format($m['qta_dev'],2,',','').'</div>';

                    echo '<div style="position:relative;display:inline-block;vertical-align:top;width:8%;text-align:center;" >';
                        if ($m['id_ordine_for']!=0) {
                            if ($m['qta_dev_ordfor']>0) echo '<img style="width:15px;height:15px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/odl/img/richiesta_NO.png" />';
                            else echo '<img style="width:15px;height:15px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/odl/img/alert/ricambi_OFF.png" />';
                        }
                    echo '</div>';

                    echo '<div style="position:relative;display:inline-block;vertical-align:top;width:10%;text-align:right;" >';
                        if ($m['qta_dev_ordfor']>0) echo '&lt; '.number_format($m['qta_dev_ordfor'],2,',','');
                    echo '</div>';

                    echo '<div style="position:relative;display:inline-block;vertical-align:top;width:18%;text-align:right;font-size:0.9em;" >';    
                        if ($m['qta_dev_ordfor']>0 && $m['d_invio']!='0') echo mainFunc::gab_todata($m['d_invio']).'<span style="font-size:0.9em" > ('.$m['tipo_ofor'].')</span>';
                    echo '</div>';
            echo '</div>';
            
        }

        echo '<div style="position:relative;width:100%;height:20px;border-top:2px solid black;" ></div>';

    echo '</div>';

$ret['pren']=base64_encode(ob_get_clean());

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$magFunc->getOrdiniOfficinaComm();

ob_start();

    $ot=0;
    $rif=0;

    echo '<div style="position:relative;width:95%;">';

        foreach ($magFunc->getOrdini('officina','comm') as $k=>$m) {

            if ($m['qta_dev_ordfor']>0) {

                if (!array_key_exists($m['d_invio'],$tempSaldi)) $tempSaldi[$m['d_invio']]=array();
                if (!array_key_exists($m['rif_ot'],$tempSaldi[$m['d_invio']])) $tempSaldi[$m['d_invio']][$m['rif_ot']]=array();

                $tempSaldi[$m['d_invio']][$m['rif_ot']][$m['id_ofor']]=$m;
            }

            if ($rif!=$m['rif']) {

                echo '<div style="position:relative;width:100%;height:20px;border-top:2px solid black;" ></div>';

                echo '<div style="position:relative;width:100%;padding:2px;box-sizing:border-box;font-size:0.9em;font-weight:bold;" >';
                    echo '<div>';
                        echo '<div style="position:relative;display:inline-block;vertical-align:top;width:15%;" >'.$m['targa'].'</div>';
                        echo '<div style="position:relative;display:inline-block;vertical-align:top;width:30%;" >'.$m['telaio'].'</div>';
                        echo '<div style="position:relative;display:inline-block;vertical-align:top;width:55%;" >'.substr($m['des_veicolo'],0,35).'</div>';
                    echo '</div>';
                    echo '<div>';
                        echo '<div style="position:relative;display:inline-block;vertical-align:top;width:45%;" >'.substr($m['ragsoc'],0,35).'</div>';
                        echo '<div style="position:relative;display:inline-block;vertical-align:top;width:45%;" >- '.substr($m['util_ragsoc'],0,35).'</div>';  
                    echo '</div>';
                    echo '<div>';
                        echo '<div style="position:relative;display:inline-block;vertical-align:top;width:90%;text-align:left;';
                            if ($m['d_fatt']!="") echo 'color:red;';
                        echo '" >';
                            echo 'Comm: '.$m['rif'].' - '.mainFunc::gab_todata($m['d_commessa']);
                            if ($m['d_fatt']!="") echo ' CHIUSA'; 
                        echo '</div>';
                    echo '</div>';
                echo '</div>';

                $rif=$m['rif'];
            }

            if ($ot!=$m['rif_ot']) {

                echo '<div style="position:relative;width:100%;border:1px solid black;padding:2px;box-sizing:border-box;background-color:#dddddd;margin-top:5px;" >';
                    echo '<div style="position:relative;display:inline-block;vertical-align:top;width:40%;" >';
                        echo 'OT: '.$m['rif_ot'];
                    echo '</div>';
                    echo '<div style="position:relative;display:inline-block;vertical-align:top;width:60%;text-align:right;">';
                        echo $m['id_utente'].' ('.mainFunc::gab_todata($m['d_ordine']).')';
                    echo '</div>';
                echo '</div>';

                $ot=$m['rif_ot'];
            }

            $color='#777777';

            $dispo=$m['giacenza']-$m['impegnato']-$m['officina']-$m['interno'];
                    
            if ($dispo>=0) $color='green';
            else {
                if ($m['qta_dev_ordfor']>0) $color='#e34dd8';
                elseif ($m['giacenza']>0) $color='orange';
                else $color='red';
            }

            echo '<div style="position:relative;width:100%;padding:2px;box-sizing:border-box;" >';
                    echo '<div style="position:relative;display:inline-block;vertical-align:top;width:4%;text-align:center;" >'.$m['precodice'].'</div>';
                    echo '<div style="position:relative;display:inline-block;vertical-align:top;width:22%;" >'.$m['articolo'].'</div>';
                    echo '<div style="position:relative;display:inline-block;vertical-align:top;width:3%;font-size:0.9em;text-align:center;" >'.$m['deposito'].'</div>';
                    echo '<div style="position:relative;display:inline-block;vertical-align:top;width:23%;" >';
                        echo '<div style="position:relative;display:inline-block;vertical-align:top;width:50%;text-align:right;font-weight:bold;color:'.$color.';" >'.number_format($m['quantita'],2,',','').'</div>';
                        echo '<div style="position:relative;display:inline-block;vertical-align:top;width:50%;text-align:center;font-size:0.9em;" >('.number_format($m['giacenza'],2,',','').')</div>';
                    echo '</div>';
                    echo '<div style="position:relative;display:inline-block;vertical-align:top;width:10%;text-align:right;" >&lt; '.number_format($m['qta_dev'],2,',','').'</div>';

                    echo '<div style="position:relative;display:inline-block;vertical-align:top;width:8%;text-align:center;" >';
                        if ($m['id_ordine_for']!=0) {
                            if ($m['qta_dev_ordfor']>0) echo '<img style="width:15px;height:15px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/odl/img/richiesta_NO.png" />';
                            else echo '<img style="width:15px;height:15px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/odl/img/alert/ricambi_OFF.png" />';
                        }
                    echo '</div>';

                    echo '<div style="position:relative;display:inline-block;vertical-align:top;width:10%;text-align:right;" >';
                        if ($m['qta_dev_ordfor']>0) echo '&lt; '.number_format($m['qta_dev_ordfor'],2,',','');
                    echo '</div>';

                    echo '<div style="position:relative;display:inline-block;vertical-align:top;width:18%;text-align:right;font-size:0.9em;" >';    
                        if ($m['qta_dev_ordfor']>0 && $m['d_invio']!='0') echo mainFunc::gab_todata($m['d_invio']).'<span style="font-size:0.9em" > ('.$m['tipo_ofor'].')</span>';
                    echo '</div>';
            echo '</div>';
            
        }

        echo '<div style="position:relative;width:100%;height:20px;border-top:2px solid black;" ></div>';

    echo '</div>';

$ret['comm']=base64_encode(ob_get_clean());

//$ret['comm']=json_encode($galileo->getLog('query'));

///////////////////////////////////////////////////////////////////////////////////////////////////////////

ksort($tempSaldi);

ob_start();

    echo '<div style="position:relative;width:95%;">';

        foreach ($tempSaldi as $k=>$s) {


            echo '<div style="position:relative;width:100%;height:20px;border-top:2px solid black;" ></div>';

            echo '<div style="font-weight:bold;">Data Invio ordine fornitore: '.mainFunc::gab_todata($k).'</div>';

            foreach ($s as $ot=>$o) {

                $first=true;

                foreach ($o as $km=>$m) {

                    if ($first) {

                        echo '<div style="position:relative;width:100%;border:1px solid black;padding:2px;box-sizing:border-box;background-color:#dddddd;margin-top:5px;" >';
                            echo '<div>';
                                echo '<div style="position:relative;display:inline-block;vertical-align:top;width:15%;" >'.$m['targa'].'</div>';
                                echo '<div style="position:relative;display:inline-block;vertical-align:top;width:30%;" >'.$m['telaio'].'</div>';
                                echo '<div style="position:relative;display:inline-block;vertical-align:top;width:55%;" >'.substr($m['des_veicolo'],0,35).'</div>';
                            echo '</div>';
                            echo '<div>';
                                echo '<div style="position:relative;display:inline-block;vertical-align:top;width:45%;" >'.substr($m['ragsoc'],0,35).'</div>';
                                echo '<div style="position:relative;display:inline-block;vertical-align:top;width:45%;" >- '.substr($m['util_ragsoc'],0,35).'</div>';  
                            echo '</div>';
                            echo '<div>';
                                echo '<div style="position:relative;display:inline-block;vertical-align:top;width:70%;text-align:left;font-size:0.9em;font-weight:bold;';
                                    if ($m['tipo']=='comm' && $m['d_fatt']!="") echo 'color:red;';
                                    elseif ($m['tipo']=='pren' && date('Ymd')>$m['d_prenotazione']) echo 'color:red;';
                                echo '" >';
                                    echo 'OT: '.$m['rif_ot'];
                                    if ($m['tipo']=='comm') {
                                        echo ' - Comm: '.$m['rif'].' - '.mainFunc::gab_todata($m['d_commessa']);
                                        if ($m['d_fatt']!="") echo ' CHIUSA';
                                    }
                                    elseif ($m['tipo']=='pren') {
                                        echo ' - Pren: '.$m['rif'].' - '.mainFunc::gab_todata($m['d_prenotazione']).' '.$m['ora_prenotazione'];
                                    }
                                echo '</div>';
                                echo '<div style="position:relative;display:inline-block;vertical-align:top;width:28%;text-align:right;font-size:0.9em;font-weight:bold;">';
                                    echo mainFunc::gab_todata($k);
                                echo '</div>';
                            echo '</div>';
                        echo '</div>';

                        $first=false;
                    }

                    $color='#777777';

                    $dispo=$m['giacenza']-$m['impegnato']-$m['officina']-$m['interno'];
                            
                    if ($dispo>=0) $color='green';
                    else {
                        if ($m['qta_dev_ordfor']>0) $color='#e34dd8';
                        elseif ($m['giacenza']>0) $color='orange';
                        else $color='red';
                    }

                    echo '<div style="position:relative;width:100%;padding:2px;box-sizing:border-box;" >';
                        echo '<div style="position:relative;display:inline-block;vertical-align:top;width:4%;text-align:center;" >'.$m['precodice'].'</div>';
                        echo '<div style="position:relative;display:inline-block;vertical-align:top;width:22%;" >'.$m['articolo'].'</div>';
                        echo '<div style="position:relative;display:inline-block;vertical-align:top;width:3%;font-size:0.9em;text-align:center;" >'.$m['deposito'].'</div>';
                        echo '<div style="position:relative;display:inline-block;vertical-align:top;width:23%;" >';
                            echo '<div style="position:relative;display:inline-block;vertical-align:top;width:50%;text-align:right;font-weight:bold;color:'.$color.';" >'.number_format($m['quantita'],2,',','').'</div>';
                            echo '<div style="position:relative;display:inline-block;vertical-align:top;width:50%;text-align:center;font-size:0.9em;" >('.number_format($m['giacenza'],2,',','').')</div>';
                        echo '</div>';
                        echo '<div style="position:relative;display:inline-block;vertical-align:top;width:10%;text-align:right;" >&lt; '.number_format($m['qta_dev'],2,',','').'</div>';

                        echo '<div style="position:relative;display:inline-block;vertical-align:top;width:8%;text-align:center;" >';
                            if ($m['qta_dev_ordfor']>0) echo '<img style="width:15px;height:15px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/odl/img/richiesta_NO.png" />';
                            else echo '<img style="width:15px;height:15px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/odl/img/alert/ricambi_OFF.png" />';
                        echo '</div>';

                        echo '<div style="position:relative;display:inline-block;vertical-align:top;width:10%;text-align:right;" >';
                            echo '&lt; '.number_format($m['qta_dev_ordfor'],2,',','');
                        echo '</div>';

                        echo '<div style="position:relative;display:inline-block;vertical-align:top;width:18%;text-align:right;font-size:0.9em;" >';    
                            echo $m['id_ordine_for'].' ('.$m['tipo_ofor'].')';
                        echo '</div>';
                    echo '</div>';
                }
            }
        }

        echo '<div style="position:relative;width:100%;height:20px;border-top:2px solid black;" ></div>';

    echo '</div>';

$ret['saldi']=base64_encode(ob_get_clean());

//$ret['saldi']=json_encode($tempSaldi);

echo json_encode($ret);

?>