<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/core/divo/divo.php');

require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/core/odl/core/body/lamentato.php');

class nebulaOdlBody {

    protected $lamentati=array();
    protected $lampos=array();

    protected $storicoFlag=false;
    protected $storicoPrenotazione=false;
    //contiene tutti gli oggetti validati dagli eventi 
    protected $storicoObjEvt=array();
    protected $storicoObjChk=array();

    protected $wh;
    protected $odlFunc;
    protected $pratica=false;
    protected $galileo;

    protected $log=array();

    function __construct($func,$galileo) {

        $this->galileo=$galileo;
        $this->odlFunc=$func;
    }

    function loadPratica($pratica) {
        //è un oggetto che viene passato per riferimento.
        $this->pratica=$pratica;
    }

    function getLog() {
        return $this->log;
    }

    function setStorico($flag,$prenotazione) {
		$this->storicoFlag=$flag;
        $this->storicoPrenotazione=$prenotazione;
	}

    function getEventi() {
        return $this->storicoObjEvt;
    }

    function build($rif,$lista,$wh) {

        //lista [cli,pre] serve per infinity

        $this->wh=$wh;

        $this->wh->getBodyLamentati($rif,$lista);

        $count=100;

        //MI ASPETTO CHE CI SIA UN SOLO ORDINE DI LAVORO ma eseguo il wormhole per compatibilità
        foreach ($this->wh->exportMap() as $m) {

            if($m['result']) {
                //$pf=$this->wh->getPiattaforma($m['dms']);
                $fetID=$this->galileo->preFetch('odl');

                while ($row=$this->galileo->getFetch('odl',$fetID)) {

                    //$this->log[]=$row;

                    $count++;
                    $row['dms']=$m['dms'];

                    //se non esiste la posizione allora crea il record EXTRA

                    if ($row['pos']=='') {

                        //###############################################
                        //per il momento solo per concerto
                        if ($m['dms']=='concerto') {

                            $temparr=array(
                                "rif"=>$row['rif'],
                                "lam"=>$row['lam'],
                                "pos"=>$count
                            );

                            $this->galileo->clearQuery();
                            $this->galileo->clearQueryOggetto('default','odl');

                            $tempres=$this->wh->insertExtra($temparr);
                        }
                        //################################################

                        $row['pos']=$count;
                    }

                    $this->lampos[$row['pos']]=$row['lam'];

                    $this->lamentati[$row['lam']]=new lamentatoOdl($row,$this->wh,$this->odlFunc,$this->galileo);

                    if ($this->pratica) $this->pratica->addLam($row);


                        /*SPLIT
                        if ($row['cod_pacchetto']=='sopralluogo') $this->sopralluogo=$row['lam'];
                        else if ($row['cod_azione']!='') $split_temp[ 'az_'.$row['cod_azione'] ][]=$row['lam'];
                        else if ($row['tipo_gar']=='*') $split_temp['std'][]=$row['lam'];
                        else $split_temp[ $row['tipo_gar'] ][]=$row['lam'];
                        */
                    
                }

                ksort($this->lampos);

                //ha senso SOLO per CONCERTO
                //$this->calcola_split($split_temp);
                //===================================

                if ($this->storicoFlag) {
                    $this->storicoObjChk=$this->wh->getStoricoObjChk($m['dms'],$rif);
                }
            }
        }

        //##############################################
        //lettura contenuto lamentati
        //è stato separato dalla lettura dei lamentati perchè su infinity è su tabelle differenti
        //bisogna farlo qui e non dall'oggetto lamentato per intercettare gli eventuali elementi orfani dell'indice del lamentato
        $ids=$this->wh->getLamMovements($rif,$lista);

        //$this->log[]=$ids;

        foreach ($ids as $i) {

            while ($row=$this->galileo->getFetchPiattaforma($i['piattaforma'],$i['id'])) {

                if (array_key_exists($row['lam'],$this->lamentati)) {

                    if ($this->storicoFlag) {

                        $row=$this->wh->OTcheckEvento($row);

                        if (isset($row['evento']) && $row['evento']) {

                            //#################
                            //verifica CHK_ACTUAL
                            if ($row['evento']['chk_std']==1) {
                                $row['evento']['chk_actual']='CHK';
                            }

                            if (isset($this->storicoObjChk[$row['evento']['riferimento']])) {
                                $row['evento']['chk_actual']=$this->storicoObjChk[$row['evento']['riferimento']];
                            }
                            //#################

                            //if ($row['evento']['chk_std']==0 || $row['evento']['chk_actual']!='OK') {

                                //if (!isset( $this->storicoObjEvt[$row['evento']['oggetto']]) || ($this->storicoObjEvt[$row['evento']['oggetto']]!='INS' && $this->storicoObjEvt[$row['evento']['oggetto']]!='DEL') ) {
                                if (!isset( $this->storicoObjEvt[$row['evento']['oggetto']]) ) {
                                    $this->storicoObjEvt[$row['evento']['oggetto']]=$row['evento']['chk_actual'];
                                }

                            //}
                        }
                    }

                    $this->lamentati[$row['lam']]->add($row);
                }
            }
        }
        //##############################################

    }

    function loadRichieste($a) {

        foreach ($a as $ordcli=>$o) {
            foreach ($o as $lam=>$l) {
                if (array_key_exists($lam,$this->lamentati)) {
                    $this->lamentati[$lam]->loadRichieste($l);
                }
            }
        }
    }

    function getValori() {
        //somma i valori di ogni lamentato
        $valori=array(
            'M'=>0,
            'R'=>0,
            'V'=>0,
            'tot'=>0,
            'ore_fatt'=>0,
            'ore_marc'=>0,
            'sconto'=>array(
                'M'=>0,
                'R'=>0,
                'V'=>0,
                'tot'=>0		
            ),
            'ivato'=>array(
                'M'=>0,
                'R'=>0,
                'V'=>0,
                'tot'=>0	
            )
        );

        foreach ($this->lamentati as $l) {

            $l->calcolaTot();
            $t=$l->get_valori();

            if (isset($t['M'])) $valori['M']+=$t['M'];
            if (isset($t['R'])) $valori['R']+=$t['R'];
            if (isset($t['V'])) $valori['V']+=$t['V'];
            if (isset($t['tot'])) $valori['tot']+=$t['tot'];
            if (isset($t['ore_fatt'])) $valori['ore_fatt']+=$t['ore_fatt'];
            if (isset($t['ore_marc'])) $valori['ore_marc']+=$t['ore_marc'];

            if (isset($t['sconto']['M'])) $valori['sconto']['M']+=$t['sconto']['M'];
            if (isset($t['sconto']['R'])) $valori['sconto']['R']+=$t['sconto']['R'];
            if (isset($t['sconto']['V'])) $valori['sconto']['V']+=$t['sconto']['V'];
            if (isset($t['sconto']['tot'])) $valori['sconto']['tot']+=$t['sconto']['tot'];

            if (isset($t['ivato']['M'])) $valori['ivato']['M']+=$t['ivato']['M'];
            if (isset($t['ivato']['R'])) $valori['ivato']['R']+=$t['ivato']['R'];
            if (isset($t['ivato']['V'])) $valori['ivato']['V']+=$t['ivato']['V'];
            if (isset($t['ivato']['tot'])) $valori['ivato']['tot']+=$t['ivato']['tot'];
        }

        return $valori;

    }

    function drawBody() {

        echo '<div id="odielleBodyMain_odl" style="position:relative;" >';

            $this->drawLams();

        echo '</div>';

        echo '<div id="odielleBodyMain_dedalo" style="position:relative;display:none;" >';

            echo '<div id="odielleBodyDedalo_Rit" ></div>';

            echo '<div id="odielleBodyDedalo_Ric" ></div>';

            echo '<div id="odielleBodyDedalo_Lam" style="position:relative;width:95%;" >';

                echo '<div style="position:relative;border-top:1px solid black;border-bottom:1px solid black;margin-top:3px;height:25px;" >';
                echo '</div>';

                echo '<div style="position:relative;">';

                    foreach ($this->lampos as $l) {

                        $this->lamentati[$l]->drawHeadDedalo();
                        /*echo '<div>'.json_encode($l->getInfo()).'</div>';
                        echo '<div>'.json_encode($this->lampos).'</div>';
                        echo '<div>'.json_encode($this->galileo->getLog('query')).'</div>';*/
                    }

                echo '</div>';

            echo '</div>';

        echo '</div>';

        /*echo '<div>';
            echo json_encode($this->log);
        echo '</div>';*/

        /*echo '<div>';
            echo json_encode($this->galileo->getLog('query'));
        echo '</div>';*/

    }

    function drawLams() {

        foreach ($this->lamentati as $l) {

            $l->setStorico($this->storicoFlag,$this->storicoPrenotazione);

            echo '<div style="font-size:11pt;margin-bottom:6px;margin-top:2px;width:95%;">';
                $l->drawLam();
            echo '</div>';

        }

        //echo '<div>'.json_encode($this->storicoObjEvt).'</div>';
    }

}

?>