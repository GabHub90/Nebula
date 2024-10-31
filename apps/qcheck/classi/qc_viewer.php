<?php

require_once(DROOT.'/nebula/core/chekko/chekko.php');

class qcheckViewer extends chekko {

    protected $storico=array();
    protected $variante=array();

    protected $galileo;

    function __construct($controllo,$modulo,$galileo) {

        //in realtà chekko serve solo per leggere la variante tramite le funzioni closure
        parent::__construct('qcview');

        $this->galileo=$galileo;

        $temp=array(
            "controllo"=>$controllo,
            "modulo"=>$modulo
        );

        $this->galileo->executeGeneric('qcheck','getView',$temp,"");
        $result=$this->galileo->getResult();

        if ($result) {
            $fetID=$this->galileo->preFetch('qcheck');
            while ($row=$this->galileo->getFetch('qcheck',$fetID)) {
                $this->storico=$row;
            }
        }

        else die('errore nella lettura del modulo dal DB');
        //else die(json_encode($galileo->getLog('query')));

        $txt='modulo'.$this->storico['modulo'];

        $file=DROOT.'/nebula/apps/qcheck/moduli/'.$txt.'.php';
        $separator="//#####//";
        $docTag='variante';
        $inc=mainFunc::nebulaIncludePart($file,$separator,$docTag,$this->storico['variante']);
        
        try {
            $res = eval($inc);
        } catch (ParseError $e) {
            $this->log[]=$e->getMessage();
        }

        $this->set_closure();

        //definita da CLOSURE
        $this->qcInit();
    }

    function draw_js(){
        //abstract chekko
    }

    function draw_css(){
        //abstract chekko
    } 

    function draw() {
        //abstract chekko
    }

    function view() {
        //le informazioni vengono organizzate tramite tabelle per poter utilizzare "html2pdf"

        $risposte=json_decode($this->storico['risposte'],true);
        $varianti=json_decode($this->storico['varianti'],true);

        echo '<div style="width:100%;">';

            echo '<div style="width:100%;vertical-align:top;font-weight:bold;" >';
                $txt=$this->storico['chiave'].' - '.$this->storico['intestazione'];
                echo substr($txt,0,55);
            echo '</div>';

            echo '<div style="width:100%;vertical-align:top;" >';
                echo $this->storico['titolo'].' - v.'.$this->storico['versione'];
            echo '</div>';

            echo '<div>';

                echo '<table style="width:100%;">';

                    echo '<tr>';

                        echo '<td style="float:left;width:70%;vertical-align:top;font-weight:bold;" >';
                            echo $this->storico['titolo_modulo'].' - '.$varianti[$this->storico['variante']]['tag'];
                        echo '</td>';

                        if (!$a=json_decode($this->storico['punteggio'],true)) $a=array();

                        echo '<td style="float:left;width:25%;vertical-align:top;text-align:right;font-weight:bold;" >';
                            if (count($a)>0) {
                                if ($a['domande']!=0) {
                                    $completezza=round( ($a['risposte']/$a['domande'])*100 ).'%';
                                }
                                else {
                                    $completezza="0%";
                                }

                                echo $a['punteggio'].'<span style="font-size:smaller;"> ( '.$completezza.' )</span>';
                            }
                        echo '</td>';

                    echo '</tr>';

                echo '</table>';

            echo '</div>';
            
            echo '<div>';

                echo '<table style="width:100%;">';

                    echo '<tr>';
                    
                        echo '<td style="display:inline-block;width:70%;vertical-align:top;" >';
                            echo $this->storico['esecutore'].' - ('.( mainFunc::gab_todata(substr($this->storico['d_modulo'],0,8) ).' '.substr($this->storico['d_modulo'],9,5)).')';
                        echo '</td>';

                        echo '<td style="display:inline-block;width:25%;vertical-align:top;text-align:right;" >';
                            echo ($this->storico['des_rif_operatore']!="")?$this->storico['des_rif_operatore']:$this->storico['operatore'];
                        echo '</td>';
                    
                    echo '</tr>';

                echo '</table>';

            echo '</div>';

        echo '</div>';

        $this->builder->view($risposte);
    }

}

?>