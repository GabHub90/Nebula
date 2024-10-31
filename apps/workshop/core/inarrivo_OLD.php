<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
include_once($_SERVER['DOCUMENT_ROOT']."/nebula/core/odl/odl_func.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/apps/workshop/classi/wormhole.php");

$param=$_POST['param'];

$d=mainFunc::gab_input_to_db($param['d']);

$wh=new workshopWHole($param['reparto'],$galileo);
$odlFunc=new nebulaOdlFunc($galileo);

$wh->setOdlfunc($odlFunc);
$wh->build(array('inizio'=>$d,"fine"=>$d));

$wh->getPrenotazioni($param['reparto']);

echo '<div style="width:95%;">';

    //è sicuramente solo uno
    foreach ($wh->exportMap() as $m) {

        if ($m['result']) {
            $fid=$galileo->preFetchPiattaforma($m['piattaforma'],$m['result']);

            $txt="";
            $lam="";
            $ore=0;
            $rif="";
            $count=0;
            $color="#cccccc";

            /*{"dat_inserimento":"20210826","cod_officina":"PV","cod_officina_prenotazione":"PV","cod_stato_commessa":"RP","cod_movimento":"OOP","rif":"1358521","num_commessa":"0","ind_preventivo":"N",
            "cod_tipo_trasporto":"NO","num_riga":1,"lam":"A","des_riga":"INFOTAINMENT SI SPEGNE E DA ERRORI ","ind_stato":"L","ind_chiuso":"N","d_pren":"20210908:07:30","d_ricon":"20210909:18:15",
            "d_entrata":"xxxxxxxx:xx:xx","d_fine":"xxxxxxxx:xx:xx","ore":"1.50","ore_isla":"2.50","subrep":"DIAPV","d_inc":"20210908:12:00","d_fine_inc":"20210909:15:00","d_fix":"xxxxxxxx:xx:xx",
            "distribuzione":"JMP_125_1","prog_spalm":1,"d_spalm":"20210909:13:30","ore_spalm":"1.50","dat_prenotazione_inc":{"date":"2021-09-08 12:00:00.000000","timezone_type":3,"timezone":"Europe\/Berlin"},
            "dat_prenotazione_det":{"date":"2021-09-09 13:30:00.000000","timezone_type":3,"timezone":"Europe\/Berlin"},"num_rif_veicolo":"5043623","num_rif_veicolo_progressivo":1,
            "cod_anagra_util":"142264","cod_anagra_intest":"","cod_anagra_loc":"","cod_anagra_fattura":"","cod_accettatore":"m.ghiandoni","mat_targa":"GA111ND","mat_telaio":"TMBEH6NWXL3089545",
            "cod_veicolo":"NW13C5","des_veicolo":"Scala Sport 1,0 TGI 66 kW 6-Gang mech. G-TEC","util_ragsoc":"MONTANARI DAVIDE","intest_ragsoc":""}*/

            while ($row=$galileo->getFetchPiattaforma($m['piattaforma'],$fid)) {

                if ($m['dms']=='concerto') {

                    //se non c'è data di entrata e non ci sono lamentati significa che è una prenotazione svuotata
                    if ($row['d_entrata']=='xxxxxxxx:xx:xx' && $row['des_riga']=='') continue;

                    if ($row['ind_chiuso']=='S') $row['cod_stato_commessa']='CH';
                    elseif ($row['d_entrata']!='xxxxxxxx:xx:xx') $row['cod_stato_commessa']='AP';
                }

                if ($rif!=$row['rif']) {

                    //scrittura del blocco
                    if ($txt!="") {

                        $txt.='<div style="font-size:1.05em;color:chocolate;font-weight:bold;" ><span style="color:#777777;margin-right:3px;">('.number_format($ore,2,'.','').' ut)</span>'.substr($lam,0,-3).'</div>';

                        $txt='<div style="position:relative;margin-top:2px;margin-bottom:2px;padding:2px;border:1px solid black;box-sizing:border-box;" >'.$txt.'</div>';
                        echo $txt;

                        $txt="";
                    }

                    //se la commessa è chiusa non considerare
                    if ($row['cod_stato_commessa']=='CH') continue;
                    
                    //if (array_key_exists($row['cod_stato_commessa'],$this->statoOdl[$m['dms']])) $color=$this->statoOdl[$m['dms']][$row['cod_stato_commessa']]['colore'];
                    if ($tempstato=$odlFunc->getStatoOdl($row['cod_stato_commessa'],$m['dms'])) $color=$tempstato['colore'];

                    if ($tempstato && $tempstato['codice']=='AP') continue; 

                    $count++;

                    $txt='<div style="background-color:'.$color.';">';
                        $txt.='<div style="position:relative;display:inline-block;width:15%;vertical-align:top;line-height:20px;font-size:0.9em;">';
                            $txt.='<span style="vertical-align:top;">'.$count.' -</span>';

                            if ($row['des_riga']!="") {
                                $txt.='<input id="avalonPrenCheckbox'.$m['dms'].'_'.$row['rif'].'" type="checkbox" style="margin-left:3px;width:10px;vertical-align:top;" data-dms="'.$m['dms'].'" data-rif="'.$row['rif'].'" />';
                            }

                        $txt.='</div>';

                        $txt.='<div style="position:relative;display:inline-block;width:80%;vertical-align:top;font-weight:bold;line-height:20px;">';

                            $txt.=substr($row['d_pren'],9,5);

                            if ($temptra=$odlFunc->getTrasporto($row['cod_tipo_trasporto'],$m['dms'])) {
                                $txt.='<span style="margin-left:3px;font-size:0.8em;">'.substr($temptra['testo'],0,8).'</span>';
                            }

                            $txt.='<img style="position:relative;margin-left:5px;width:15px;height:10px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/main/img/blackarrowR.png" />';
                            $t=mainFunc::gab_tots(substr($row['d_ricon'],0,8));
                            $txt.='<span style="margin-left:5px;">'.substr(mainFunc::gab_weektotag(date('w',$t)),0,3).' '.substr(mainFunc::gab_todata(substr($row['d_ricon'],0,8)),0,5).' '.substr($row['d_ricon'],9,5).'</span>';
                        $txt.='</div>';

                    $txt.='</div>';

                    $txt.='<div style="';
                        if ($row['cod_stato_commessa']!='AP') $txt.='cursor:pointer;';
                    $txt.='" ';
                        if ($row['cod_stato_commessa']!='AP') $txt.='onclick=""';
                    $txt.='>';

                        $txt.='<div>';

                            $txt.='<div style="position:relative;display:inline-block;width:27%;vertical-align:top;line-height:20px;font-size:1em;font-weight:bold;">';
                                $txt.=$row['mat_targa'];
                            $txt.='</div>';

                            $txt.='<div style="position:relative;display:inline-block;width:73%;vertical-align:top;font-weight:bold;line-height:20px;">';
                                if ($row['util_ragsoc']!="") {
                                    $txt.=strtoupper(substr($row['util_ragsoc'],0,25));
                                }
                                else {
                                    $txt.=strtoupper(substr($row['intest_ragsoc'],0,25));
                                }
                            $txt.='</div>';

                        $txt.='</div>';

                        $txt.='<div style="">';

                            $txt.='<div style="position:relative;display:inline-block;width:27%;vertical-align:top;line-height:15px;font-size:0.9em;font-weight:normal;">';
                                $txt.='('.$row['rif'].')'.substr($m['dms'],0,1);
                            $txt.='</div>';

                            $txt.='<div style="position:relative;display:inline-block;width:73%;vertical-align:top;font-size:1em;font-weight:normal;line-height:15px;">';
                                $txt.=strtolower(ucwords(substr($row['des_veicolo'],0,28)));
                            $txt.='</div>';

                        $txt.='</div>';

                    $txt.='</div>';

                    $rif=$row['rif'];
                    $lam="";
                    $ore=0;
                }

                $lam.=utf8_encode(ucfirst(strtolower($row['des_riga']))).' - ';
                $ore+=(float) $row['ore_isla'];

            }

            //scrittura dell'ultimo blocco
            if ($txt!="") {

                $txt.='<div style="font-size:1.05em;color:chocolate;font-weight:bold;"><span style="color:#777777;margin-right:3px;">('.number_format($ore,2,'.','').' ut)</span>'.substr($lam,0,-3).'</div>';

                $txt='<div style="position:relative;margin-top:2px;margin-bottom:2px;padding:2px;border:1px solid black;box-sizing:border-box;" >'.$txt.'</div>';
                echo $txt;
            }
        }
    }

echo '</div>';

?>