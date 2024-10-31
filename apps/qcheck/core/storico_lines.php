<?php
include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/apps/qcheck/classi/qc_analytics.php");

include_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_qcheck.php');

//setta le funzioni GALILEO necessarie
$nebulaDefault=array();
$obj=new galileoQcheck();
$nebulaDefault['qcheck']=array("gab500",$obj);

$galileo->setFunzioniDefault($nebulaDefault);

$nebulaParams=$_POST['param'];

$qcAny=new qcAnalytics($nebulaParams['reparto'],$nebulaParams['controllo'],$galileo);

$temp=array(
    "data_i"=>$nebulaParams['data_i'],
    "data_f"=>$nebulaParams['data_f'],
    "esecutore"=>$nebulaParams['esecutore'],
    "operatore"=>$nebulaParams['operatore'],
    "modulo"=>$nebulaParams['modulo'],
    "variante"=>$nebulaParams['variante'],
    "chiave"=>$nebulaParams['chiave'],
    "oggetto"=>'storico'
);

$qcAny->loadConfig($temp);

$actualcontrollo=0;
$counter=0;

$lines=$qcAny->getLines();

if(count($lines)==0) die('<div style="text-align:center;margin-top:10px;">Nessun record trovato</div>');

foreach ($lines as $IDcontrollo=>$c) {

    echo '<div style="border:1px solid black;margin-top:3px;margin-bottom:3px;padding:2px;box-sizing:border-box;">';

        foreach ($c as $modulo=>$l) {

            $counter++;

            if ($actualcontrollo!=$IDcontrollo) {
                echo '<div style="display:inline-block;width:100%;vertical-align:top;color:sienna;font-weight:bold;" >';
                    $txt=$l['chiave'].' - '.$l['intestazione'];
                    echo substr($txt,0,55);
                echo '</div>';

                $actualcontrollo=$IDcontrollo;
            }

            echo '<div style="cursor:pointer;" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].view(\''.$IDcontrollo.'\',\''.$modulo.'\');">';

                echo '<hr style="position:relative;width:80%;color:darkgrey;" />';

                echo '<div>';

                    echo '<div style="display:inline-block;width:8%;text-align:center;" >';
                        echo $counter;
                    echo '</div>';

                    echo '<div style="display:inline-block;width:70%;vertical-align:top;font-weight:bold;" >';
                        echo $l['titolo_modulo'].'<span style="font-size:smaller;"> ('.$modulo.')</span> - '.$l['varianti'][$l['variante']]['tag'].'<span style="font-size:smaller;"> ('.$l['variante'].')</span>';
                    echo '</div>';

                    if (!$a=json_decode($l['punteggio'],true)) $a=array();

                    echo '<div style="display:inline-block;width:20%;vertical-align:top;text-align:right;font-weight:bold;" >';
                        if (count($a)>0) {
                            if ($a['domande']!=0) {
                                $completezza=round( ($a['risposte']/$a['domande'])*100 ).'%';
                            }
                            else {
                                $completezza="0%";
                            }

                            echo $a['punteggio'].'<span style="font-size:smaller;"> ( '.$completezza.' )</span>';
                        }
                    echo '</div>';

                echo '</div>';

                echo '<div>';

                    echo '<div style="display:inline-block;width:8%;" >';
                    echo '</div>';
                        
                    echo '<div style="display:inline-block;width:70%;vertical-align:top;" >';
                        echo $l['esecutore'].' - ('.( mainFunc::gab_todata(substr($l['d_modulo'],0,8) ).' '.substr($l['d_modulo'],9,5)).')';
                    echo '</div>';

                    echo '<div style="display:inline-block;width:20%;vertical-align:top;text-align:right;" >';
                        echo ($l['des_rif_operatore']!="")?$l['des_rif_operatore']:$l['operatore'];
                    echo '</div>';

                echo '</div>';
            
            echo '</div>';

        }
    
    echo '</div>';

}



?>