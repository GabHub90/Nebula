<?php

require_once('qc_analytics.php');

class qcReport {

    protected $reparto="";
    protected $controllo="";

    protected $analy;

    function __construct($reparto,$controllo,$galileo) {

        $this->reparto=$reparto;
        $this->controllo=$controllo;

        $this->analy=new qcAnalytics($this->reparto,$this->controllo,$galileo);   
    }
    

    function getLines($a) {
        $this->analy->loadConfig($a);
        return $this->analy->getLines();
    }


    function draw_generale($today) {
        //scrive il resoconto dei controlli effettuati (chiusi) nel periodo di tempo
        //$today = YYYYMMDD

        //prendo gli estremi del mese di "today"
        $data_i=substr($today,0,6).'01';
        $tsf=mainFunc::gab_tots($data_i);

        $a=array(
            "data_i"=>$data_i,
            "data_f"=>date('Ymt',$tsf),
            "oggetto"=>"controlli"
        );

        $lines=$this->getLines($a);

        $modulo_default=array(
            "tag"=>"",
                "tot"=>0,
                "punteggio"=>0,
                "domande"=>0,
                "risposte"=>0,
                "esecutori"=>array()
        );

        $tot=array(
            "controlli"=>0,
            "tot_moduli"=>0,
            "punteggio"=>0,
            "domande"=>0,
            "risposte"=>0,
            "moduli"=>array()
        );

        //echo json_encode($lines);
        foreach ($lines as $controllo=>$c) {
            //$ret["".$row['ID_controllo']][$row['modulo']]=$row;
            $tot['controlli']++;

            foreach ($c as $modulo=>$c) {

                if ( !array_key_exists($modulo,$tot['moduli']) ) {
                    $tot['moduli'][$modulo]=$modulo_default;
                }

                $tot['moduli'][$modulo]['tag']=$c['des_modulo'];

                if ($c['stato_modulo']=='chiuso') {

                    $tot['moduli'][$modulo]['tot']++;
                    $tot['tot_moduli']++;

                    $r=json_decode($c['punteggio'],true);

                    $tot['moduli'][$modulo]['punteggio']+=$r['punteggio'];
                    $tot['moduli'][$modulo]['domande']+=$r['domande'];
                    $tot['moduli'][$modulo]['risposte']+=$r['risposte'];

                    $tot['punteggio']+=$r['punteggio'];
                    $tot['domande']+=$r['domande'];
                    $tot['risposte']+=$r['risposte'];

                    if (array_key_exists($c['esecutore'],$tot['moduli'][$modulo]['esecutori'])) {
                        $tot['moduli'][$modulo]['esecutori'][$c['esecutore']]['tot']++;
                    }
                    else $tot['moduli'][$modulo]['esecutori'][$c['esecutore']]['tot']=1;
                }

            }

        }

        ///////////////////////////////////////////////////////////////////

        echo '<div style="border:1px solid black;padding:3px;">';
            echo '<div style="display:inline-block;width:40%;">';
                echo 'Totale controlli:';
            echo '</div>';
            echo '<div style="display:inline-block;width:20%;text-align:right;">';
                echo $tot['controlli'];
            echo '</div>';
            $v1=($tot['tot_moduli']==0)?0:$tot['punteggio']/$tot['tot_moduli'];
            $v2=($tot['domande']==0)?0:$tot['risposte']/$tot['domande'];
            echo '<div style="display:inline-block;width:40%;text-align:right;font-weight:bold;">';
                echo number_format($v1,0,".",",").' ( <span style="font-size:0.8em;">'.number_format( ($v2*100),0,".",",").'% </span>)';
            echo '</div>';
        echo '</div>';

        foreach ($tot['moduli'] as $modulo=>$m) {

            echo '<div style="margin-top:10px;border-bottom:1px solid black;padding:3px;">';

                echo '<div style="font-size:1em;color:brown;font-weight:bold;">';

                    echo '<div style="display:inline-block;width:60%;">';
                        echo $modulo.' - '.$m['tag'];
                    echo '</div>';
                    $v1=($m['tot']==0)?0:$m['punteggio']/$m['tot'];
                    $v2=($m['domande']==0)?0:$m['risposte']/$m['domande'];
                    echo '<div style="display:inline-block;width:40%;text-align:right;font-weight:bold;">';
                        echo number_format($v1,0,".",",").' ( <span style="font-size:0.8em;">'.number_format( ($v2*100),0,".",",").'% </span>)';
                    echo '</div>';

                echo '</div>';

                echo '<div style="font-size:0.8em;">';

                    foreach ($m['esecutori'] as $esecutore=>$e) {

                        echo '<div>';

                            echo '<div style="display:inline-block;width:40%;">';
                                echo $esecutore;
                            echo '</div>';
                            echo '<div style="display:inline-block;width:20%;text-align:right;">';
                                echo $e['tot'];
                            echo '</div>';

                        echo '</div>';

                    }

                echo '</div>';

            echo '</div>';
        }

    }

}

?>