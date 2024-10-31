<?php

//require_once('qc_report.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/main/nebula_universe.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/core/calendario/calendario.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/core/divo/divo.php');

class qcWidget {

    protected $utente="";
    //contiene il riferimento al "sistema:funzione"
    //la galassia è decisa dal widget
    protected $link="";

    //contesto passato ma window._nebulaMain
    protected $main=array();

    //periodo           tipo di analisi secondaria (settimana,quindicina,mese,bimestre,trimestre,quadrimestre,semestre,anno)
    //giorni            giorni per l'analisi primaria (oggi-giorni)
    protected $args=array(
        "periodo"=>"",
        "data_rif"=>"",
        "data_i"=>"",
        "data_f"=>"",
        "controlli"=>array()
    );

    protected $universe;
    protected $calendario;
    protected $galileo;

    function __construct($utente,$args,$main,$galileo) {

        $this->utente=$utente;
        $this->main=$main;
        $this->galileo=$galileo;

        foreach ($this->args as $k=>$v) {
            if ( array_key_exists($k,$args) ) {
                $this->args[$k]=$args[$k];
            }
        }

        if ($this->args['data_rif']=="") $this->args['data_rif']=date('Ymd');
        
        //calcola periodo, data_i, data_f
        $this->calendario=new nebulaCalendario(substr($this->args['data_rif'],0,4),$this->galileo);
        $this->calendario->setReparto("");

        $this->universe=new nebulaUniverse();
    }

    function getLimitiPeriodo() {

        $res=$this->calendario->getLimitiPeriodo($this->args['data_rif'],$this->args['periodo']);

        if (count($res)>0) {
            $this->args['data_i']=$res['inizio'];
            $this->args['data_f']=$res['fine'];
        }
    }

    function evalFunc($galassia) {

        /*
         "qcheck"=>array(
            "mytech"=>"home:provo"
        )*/
        //se non esiste nessuna configurazione per l'applicazione => ritorna FALSE
        $sm=$this->universe->getStarmap('qcheck');
        if ($sm) {
            if (array_key_exists($galassia,$sm)) {

                $t=explode(':',$sm[$galassia]);

                //$this->link=$t[0].'-'.$t[1];

                if (isset($this->main['configFunzioni'][$galassia][$t[0]][$t[1]]['chk'])) {
                    $this->link=$sm[$galassia];
                    return $this->main['configFunzioni'][$galassia][$t[0]][$t[1]]['chk'];
                }

            }
        }
        
        return false;
    }

    function draw() {
        //prendere tutti i MODULI dove il collaboratore è ESECUTORE, OPERATORE o RIF_OPERATORE nel periodo
        //giuntati con le informazioni del controllo
        //in ordine di controllo

        $this->getLimitiPeriodo();

        //executeGeneric($tipo,$funzione,$args,$order)
        $res=array();

        //{"periodo":"trimestre","data_rif":"20210312","data_i":"20210101","data_f":"20210331"}
        $this->args['utente']=$this->utente;

        //echo json_encode($this->args);

        $order="sm.ID_controllo";

        $this->galileo->executeGeneric('qcheck','getModuliUtente',$this->args,$order);

        $fetID=$this->galileo->preFetch('qcheck');

        while( $row=$this->galileo->getFetch('qcheck',$fetID) ) {
            $res[]=$row;
        }

        //echo json_encode($res,JSON_UNESCAPED_SLASHES);
        /*{
            "ID_controllo": 1,
            "modulo": "1",
            "variante": "1",
            "esecutore": "m.cecconi",
            "operatore": "f.olmeda",
            "d_modulo": "20210310:10:08",
            "risposte": "{\"qc1\":\"1\",\"qc1n\":\"\",\"qc2\":\"2\",\"qc2n\":\"\",\"qc3\":\"0\",\"qc3n\":\"\",\"qc4\":\"1\",\"qc4n\":\"\",\"qc5\":\"1\",\"qc5n\":\"\",\"qc6\":\"1\",\"qc6n\":\"\",\"qc7\":\"1\",\"qc7n\":\"\",\"qc8\":\"1\",\"qc8n\":\"\",\"qc9\":\"1\",\"qc9n\":\"\",\"qc10\":\"1\",\"qc10n\":\"\",\"qc11\":\"1\",\"qc11n\":\"\",\"qc12\":\"1\",\"qc12n\":\"\",\"qc13\":\"2\",\"qc13n\":\"\"}",
            "punteggio": "{\"punteggio\":\"92\",\"risposte\":\"11\",\"domande\":\"11\"}",
            "stato": "chiuso",
            "rif_operatore": null,
            "controllo":1,
            "titolo": "Controllo del processo di riparazione",
            "des_rif_operatore": ""
        }*/

        $controllo=0;

        $arr=array();

        $c=array(
            "esecutore"=>0,
            "operatore"=>0,
            "punteggio"=>0,
            "domande"=>0,
            "risposte"=>0
        );

        foreach ($res as $r) {

            if ($controllo!=$r['controllo']) {
                $controllo=$r['controllo'];
                $arr[$controllo]['titolo']=$r['titolo'];
                $arr[$controllo]['stat']=$c;
            }
            
            if ($r['esecutore']==$this->utente) {
                $arr[$controllo]['stat']['esecutore']++;
            }

            if ($r['operatore']==$this->utente || $r['des_rif_operatore']==$this->utente) {
                $arr[$controllo]['stat']['operatore']++;

                try{
                    $t=json_decode($r['punteggio'],true);

                    $arr[$controllo]['stat']['punteggio']+=$t['punteggio'];
                    $arr[$controllo]['stat']['risposte']+=$t['risposte'];
                    $arr[$controllo]['stat']['domande']+=$t['domande'];
                }catch(Exception $e) {};
            }
        }

        ///////////////////////////////////////////////////
        //__construct($index,$htab,$minh,$fixed)
        $divo=new Divo('qcwgt','25px','60px',false);

        echo '<div style="position:relative;width:100%;">';

            echo '<div style="font-weight:bold;font-size:1em;margin-left:30px;" >'.mainFunc::gab_todata($this->args['data_i']).' - '.mainFunc::gab_todata($this->args['data_f']).'</div>';

            foreach ($arr as $a) {

                $eF=$this->evalFunc('mytech');

                if ($eF) {
                    $txt='<div data-link="mytech:'.$this->link.'" style="cursor:pointer;" onclick="window._nebulaApp.linkFunk(\'mytech:'.$this->link.'\');" >';
                }
                else {
                    $txt='<div>';
                }

                    $txt.='<div>';
                        $txt.=$a['titolo'];
                    $txt.='</div>';
                    $txt.='<div style="margin-top:3px;font-size:1em;">';
                        $txt.='<div style="display:inline-block;width:70%;">';
                            $txt.='Controlli eseguiti:';
                        $txt.='</div>';
                        $txt.='<div style="display:inline-block;width:25%;text-align:right;">';
                            $txt.=$a['stat']['esecutore'];
                        $txt.='</div>';
                    $txt.='</div>';
                    $txt.='<div style="margin-top:3px;font-size:1em;">';
                        $txt.='<div style="display:inline-block;width:70%;">';
                            $txt.='Controlli operatore:';
                        $txt.='</div>';
                        $txt.='<div style="display:inline-block;width:25%;text-align:right;">';
                            $txt.=$a['stat']['operatore'];
                        $txt.='</div>';
                    $txt.='</div>';
                    
                    $punteggio=($a['stat']['operatore']==0)?0:($a['stat']['punteggio']/$a['stat']['operatore']);
                    $completezza=($a['stat']['domande']==0)?0:($a['stat']['risposte']/$a['stat']['domande']);

                    $txt.='<div style="margin-top:3px;">';
                        $txt.='<div style="display:inline-block;width:45%;">';
                            $txt.='Risultato:';
                        $txt.='</div>';
                        $txt.='<div style="display:inline-block;width:50%;font-size:1.2em;text-align:right;font-weight:bold;">';
                            $txt.=number_format($punteggio,0,"","").'<span style="font-size:smaller;"> ( '.number_format( ($completezza*100),0,"","" ).'% )</span>';
                        $txt.='</div>';
                    $txt.='</div>';

                $txt.='</div>';

                //add_div($titolo,$color,$chk,$stato,$codice,$selected)
                $divo->add_div($a['titolo'],'black',0,"",$txt,0,array());
            }

            if (count($arr)>0) {

                $divo->build();

                echo '<div style="position:relative;margin-top:10px;width:99%;">';
                    $divo->draw();
                echo '</div>';
            }
            else echo '<div style="margin-top:10px;font-weight:bold;text-align:center;">Nessun dato da visualizzare</div>';

        echo '</div>';

    }

    function drawTot() {
        //prende tutti i CONTROLLI indicati nelle impostazioni e ne indica il numero di esecuzioni nel periodo
        //echo json_encode($this->args);

        $eF=$this->evalFunc('mytech');

        if ($eF) {
            echo '<div data-link="mytech:'.$this->link.'" style="cursor:pointer;" onclick="window._nebulaApp.linkFunk(\'mytech:'.$this->link.'\');" >';
        }
        else {
            echo '<div>';
        }

            echo '<div style="position:relative;left:30px;height:28px;">';
                echo 'Controlli eseguiti:';
            echo '</div>';

            foreach ($this->args['controlli'] as $c) {
                $this->args['periodo']=$c['periodo'];
                $this->getLimitiPeriodo();

                $res=array(
                    "titolo"=>"",
                    "range"=>mainFunc::gab_todata($this->args['data_i']).' - '.mainFunc::gab_todata($this->args['data_f']),
                    "qta"=>0,
                );

                $wclause="controllo='".$c['controllo']."' AND stato='chiuso' AND d_controllo>='".$this->args['data_i']."' AND d_controllo<='".$this->args['data_f']."'";
                $this->galileo->executeCount('qcheck','QCHECK_storico_controlli',$wclause);
                $fetID=$this->galileo->preFetch('qcheck');
                while( $row=$this->galileo->getFetch('qcheck',$fetID) ) {
                    $res['qta']=$row['numero_elementi'];
                }

                $wclause="controllo='".$c['controllo']."'";
                $this->galileo->executeSelect('qcheck','QCHECK_abbinamenti',$wclause,"");
                $fetID=$this->galileo->preFetch('qcheck');
                while( $row=$this->galileo->getFetch('qcheck',$fetID) ) {
                    $res['titolo']=$row['titolo'];
                    break;
                }

                echo '<div>';
                    echo '<div style="font-weight:bold;">'.$res['titolo'].'</div>';
                    echo '<div>';
                        echo '<div style="display:inline-block;width:75%;font-size:0.9em;">'.$res['range'].'</div>';
                        echo '<div style="display:inline-block;width:25%;text-align:right;">'.$res['qta'].'</div>';
                    echo '</div>';
                echo '</div>';
            }

        echo '</div>';

    }
    

    

}

?>