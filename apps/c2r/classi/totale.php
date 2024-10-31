<?php

class c2rTotale {

    protected $flagIndici=false;
    protected $flagExt=true;

    //sono i contatori delle colonne da visualizzare
    //le colonne base sono 11 (3xM + 3xR + 3xV + ore + pass)
    protected $std=11;
    protected $ext=0;
    protected $ind=0;

    protected $totale=array(
        "totale"=>null,
        "sub"=>array(
            "st"=>array(
                "tag"=>"storno",
                "totale"=>null
            ),
            "lt"=>array(
                "tag"=>"costo",
                "totale"=>null
            )
        )
    );

    //gli INDICI EXTRA sono parametri aggiuntivi in base al contesto
    //gli INDICI vengono calcolati alla fine e dipensdono dai valori che hanno assunto gli altri parametri
    /*protected $row=array(
        "std"=>array(
            "man"=>array(
                "tag"=>"Manodopera",
                "lordo"=>0,
                "netto"=>0
            ),
            "ric"=>array(
                "tag"=>"Ricambi",
                "lordo"=>0,
                "netto"=>0
            ),
            "var"=>array(
                "tag"=>"Vario",
                "lordo"=>0,
                "netto"=>0
            ),
            "ore"=>array(
                "tag"=>"Ore",
                "valore"=>0
            ),
            "pass"=>array(
                "tag"=>"Doc",
                "valore"=>0
            )
        ),

        "ext"=>array(
            "r19"=>array(
                "tag"=>"Ric 1-9",
                "flag"=>true,
                "valore"=>0
            ),
            "inc"=>array(
                "tag"=>"Incentiv.",
                "flag"=>false,
                "valore"=>0
            )
        ),

        "indici"=>array(
            "epass"=>array(
                "tag"=>"€/pass",
                "flag"=>true,
                "valore"=>0
            )
        )

    );*/

    protected $row=array();

    function __construct($marca) {

        $this->row=c2rFatturato_S::getTotRow();

        if ($marca!='') {
            $this->newTot($marca);
        }

        $this->init();

    }

    function init() {

        $this->totale['totale']=$this->row;

        foreach ($this->totale['sub'] as $sub=>$s) {
            $this->totale['sub'][$sub]['totale']=$this->row;
        }

    }

    function newTot($marca) {

        //abilita ricambi 1-9 se la vettura è di VGI o Porsche
        $temp=array('A','C','N','S','V','P','U');
        if (in_array($marca,$temp)) $this->setExt('r19',true);

        //$this->setIndici('epass',true);
    }

    function setExt($ext,$val) {
        //da chiamare subito dopo l'inizializzazione
        $this->row['ext'][$ext]['flag']=$val;
        $this->init();
        //$this->ext++;
    }

    function setIndici($ind,$val) {
        $this->row['indici'][$ind]['flag']=$val;
        $this->init();
        //$this->ind++;
    }

    function countExtInd() {
        //calcola il numero di ext e di indici TRUE
        $this->ext=0;
        $this->ind=0;

        foreach ($this->totale['totale']['ext'] as $ke=>$e) {
            if ($e['flag']) $this->ext++;
        }

        foreach ($this->totale['totale']['indici'] as $ke=>$e) {
            if ($e['flag']) $this->ind++;
        }
    }

    function getTot() {
        return $this->totale;
    }

    function feed($arr) {
        //###########################
        //aggiorna i dati in base ad un nuovo record
        //###########################
        $tr='var';
        switch($arr['ind_tipo_riga']) {
            case "M":
                $tr='man';
            break;
            case "R":
                $tr='ric';
            break;
        }

        $op=($arr['operatore']=='M')?-1:1;

        //se l'analisi dei gruppi in class_fatturato_S ha decretato un abbinamento assoluto
        if (isset($arr['assoluto'])) {
            $this->totale['totale']['std'][$tr][$arr['assoluto']]+=$op*$arr['importo'];
        }
        else {
       
            //se l'importo è inferiore a 0 allora è uno storno (sconto)
            /*if ($arr['listino']<0) {
                if(is_null($this->totale['sub']['st']['totale']))  $this->totale['sub']['st']['totale']=$this->row;

                if ($tr=='man') {
                    $this->totale['sub']['st']['totale']['std'][$tr]['lordo']+=$arr['listino'];
                }
                else {
                    $this->totale['sub']['st']['totale']['std'][$tr]['lordo']+=$arr['listino']*$arr['qta'];
                }
                
                $this->totale['sub']['st']['totale']['std'][$tr]['netto']+=$arr['importo'];

                //$this->totale['sub']['st']['totale']['std'][$tr]['costo']+=$arr['costo'];

                if ($tr=='man') $this->totale['sub']['st']['totale']['std']['ore']['valore']-=$arr['qta'];
            }
            else {*/
                if ($tr=='man') {
                    $this->totale['totale']['std'][$tr]['lordo']+=$op*$arr['listino'];
                }
                else {
                    $this->totale['totale']['std'][$tr]['lordo']+=$op*$arr['listino']*$arr['qta'];
                }

                $this->totale['totale']['std'][$tr]['netto']+=$op*$arr['importo'];

                //$this->totale['totale']['std'][$tr]['costo']+=$arr['costo'];

                if ($tr=='man') $this->totale['totale']['std']['ore']['valore']+=$op*$arr['qta'];
            //}

            $this->totale['totale']['std']['pass']['valore']+=$op*$arr['c2rPass'];
            $this->totale['totale']['std']['cont']['valore']+=$op*$arr['c2rCont'];
        }

        $this->calcolaExt($arr);
    }

    function calcolaCorr($arr) {

    }

    function calcolaExt($arr) {
        //gli INDICI EXTRA sono parametri aggiuntivi in base al contesto
        foreach ($this->row['ext'] as $ke=>$e) {
            if (!$e['flag']) continue;
            call_user_func_array(array($this, 'eval_'.$ke), array($arr) );
        }
    }

    function calcolaIndici() {

        //21.05.2021 ?????? non mi ricordo a cosa serva
        $this->flagIndici=true;

        //###########################
        //viene chiamato alla fine per calcolare gli indici in base ai valori di STD ed EXT
        foreach ($this->row['indici'] as $ke=>$e) {
            if (!$e['flag']) continue;
        }
        //###########################

    }

    function sum($t) {
        //somma a questo un totale fornito dall'esterno
        //il processo di somma non è conseguente al feed dei dati quindi non conosce la vettura e le conseguenze in termini di indici
        $op=1;

        foreach ($t['totale']['std'] as $k=>$v) {
            if ($k=='man' || $k=='ric' || $k=='var') {
                $this->totale['totale']['std'][$k]['lordo']+=$op*$v['lordo'];
                $this->totale['totale']['std'][$k]['netto']+=$op*$v['netto'];
                //$this->totale['totale']['std'][$k]['costo']+=$op*$v['costo'];
            }
            else {
                $this->totale['totale']['std'][$k]['valore']+=$op*$v['valore'];
            }
        }

        foreach ($t['totale']['ext'] as $k=>$v) {
            if ($v['flag']) {   
                $this->totale['totale']['ext'][$k]['valore']+=$op*$v['valore'];
                $this->totale['totale']['ext'][$k]['flag']=true;
            }
        }

        //gli indici vengono calcolati a parte
        //ma questo serve per definire l'header della tabella
        foreach ($t['totale']['indici'] as $k=>$v) {
            if ($v['flag']) { 
                //$this->totale['totale']['indici'][$k]['valore']+=$v['valore'];
                $this->totale['totale']['indici'][$k]['flag']=true;
            }
        }

        ///////////////////////////

        foreach ($t['sub'] as $sub=>$s) {
            foreach ($s['totale']['std'] as $k=>$v) {
                if ($k=='man' || $k=='ric' || $k=='var') {
                    $this->totale['sub'][$sub]['totale']['std'][$k]['lordo']+=$op*$v['lordo'];
                    $this->totale['sub'][$sub]['totale']['std'][$k]['netto']+=$op*$v['netto'];
                    //$this->totale['sub'][$sub]['totale']['std'][$k]['costo']+=$op*$v['costo'];
                }
                else {
                    $this->totale['sub'][$sub]['totale']['std'][$k]['valore']+=$op*$v['valore'];
                }
            }
            foreach ($s['totale']['ext'] as $k=>$v) {
                if ($v['flag']) {   
                    $this->totale['sub'][$sub]['totale']['ext'][$k]['valore']+=$op*$v['valore'];
                    $this->totale['sub'][$sub]['totale']['ext'][$k]['flag']=true;
                }
            }
            
            //gli indici vengono calcolati a parte
            /*foreach ($s['totale']['indici'] as $k=>$v) {
                if ($v['flag']) {   
                    $this->totale['sub'][$sub]['totale']['indici'][$k]['valore']+=$v['valore'];
                    $this->totale['sub'][$sub]['totale']['indici'][$k]['flag']=true;
                }
            }*/
        }
    }

    function drawHead() {
        //echo json_encode($this->totale);
        
        $c=$this->ext+$this->ind;

        echo '<table class="c2r_table" style="">';
            echo '<thead>';
                echo '<tr>';
                    echo '<th colspan="1" style="width:120px;" ></th>';
                    echo '<th colspan="2" style="border-left:1px solid black;border-right:1px solid black;">Manodopera</th>';
                    echo '<th colspan="2" style="border-left:1px solid black;border-right:1px solid black;">Ricambi</th>';
                    echo '<th colspan="2" style="border-left:1px solid black;border-right:1px solid black;">Vario</th>';
                    echo '<th colspan="3" style="border-left:1px solid black;border-right:1px solid black;"></th>';
                    echo '<th colspan="'.$c.'">Indici</th>';
                echo '</tr>';
                echo '<tr>';
                    echo '<th colspan="1" style="width:120px;" ></th>';
                    echo '<th colspan="1" style="border-left:1px solid black;">Listino</th>';
                    //echo '<th colspan="1">Netto</th>';
                    echo '<th colspan="1" style="border-right:1px solid black;">Netto</th>';
                    //echo '<th colspan="1" style="border-right:1px solid black;">Costo</th>';
                    echo '<th colspan="1">Listino</th>';
                    //echo '<th colspan="1">Netto</th>';
                    echo '<th colspan="1" style="border-right:1px solid black;">Netto</th>';
                    //echo '<th colspan="1" style="border-right:1px solid black;">Costo</th>';
                    echo '<th colspan="1">Listino</th>';
                    //echo '<th colspan="1">Netto</th>';
                    echo '<th colspan="1" style="border-right:1px solid black;">Netto</th>';
                    //echo '<th colspan="1" style="border-right:1px solid black;">Costo</th>';
                    echo '<th colspan="1">Ore</th>';
                    echo '<th colspan="1">Passaggi</th>';
                    echo '<th colspan="1" style="border-right:1px solid black;">Contatti</th>';

                    foreach ($this->totale['totale']['ext'] as $kt=>$t) {
                        if (!$t['flag']) continue;
                        echo '<th colspan="1">'.$t['tag'].'</th>';
                    }

                    foreach ($this->totale['totale']['indici'] as $kt=>$t) {
                        if (!$t['flag']) continue;
                        echo '<th colcolspan="1">'.$t['tag'].'</th>';
                    }
                    
                echo '</tr>';
            echo '</thead>';
        echo '</table>';
    }

    function draw($tipo,$ambito,$operatore) {

        //$op=($operatore=='P')?1:-1;
        $op=1;
       
        echo '<table class="c2r_table" style="">';
            echo '<tbody>';
                echo '<tr>';

                    switch ($ambito) {
                        case 'tipo': 
                            echo '<td style="width:120px;text-align:left;font-size:1em;" >'.$tipo.'</td>';
                        break;
                        case 'gruppo': 
                            echo '<td style="width:120px;text-align:left;font-size:0.9em;" >';
                                echo '<span style="margin-left:10px;">'.$tipo.'</span>';
                            echo '</td>';
                        break;
                        default:
                            echo '<td style="width:120px;text-align:left;font-size:0.9em;" >'.$tipo.'</td>';
                    }

                    echo '<td style="border-left:1px solid black;">'.number_format($op*$this->totale['totale']['std']['man']['lordo'],2,',','.').'</td>';
                    echo '<td>'.number_format($op*$this->totale['totale']['std']['man']['netto'],2,',','.').'</td>';
                    //echo '<td style="border-right:1px solid black;">'.number_format($op*($this->totale['totale']['std']['man']['lordo']-$this->totale['totale']['std']['man']['netto']),2,',','.').'</td>';
                    //echo '<td style="border-right:1px solid black;">'.number_format($op*($this->totale['totale']['std']['man']['costo']),2,',','.').'</td>';

                    echo '<td style="border-left:1px solid black;">'.number_format($op*$this->totale['totale']['std']['ric']['lordo'],2,',','.').'</td>';
                    echo '<td>'.number_format($op*$this->totale['totale']['std']['ric']['netto'],2,',','.').'</td>';
                    //echo '<td style="border-right:1px solid black;">'.number_format($op*($this->totale['totale']['std']['ric']['lordo']-$this->totale['totale']['std']['ric']['netto']),2,',','.').'</td>';
                    //echo '<td style="border-right:1px solid black;">'.number_format($op*($this->totale['totale']['std']['ric']['costo']),2,',','.').'</td>';

                    echo '<td style="border-left:1px solid black;">'.number_format($op*$this->totale['totale']['std']['var']['lordo'],2,',','.').'</td>';
                    echo '<td>'.number_format($op*$this->totale['totale']['std']['var']['netto'],2,',','.').'</td>';
                    //echo '<td style="border-right:1px solid black;">'.number_format($op*($this->totale['totale']['std']['var']['lordo']-$this->totale['totale']['std']['var']['netto']),2,',','.').'</td>';
                    //echo '<td style="border-right:1px solid black;">'.number_format($op*($this->totale['totale']['std']['var']['costo']),2,',','.').'</td>';

                    echo '<td>'.number_format($op*$this->totale['totale']['std']['ore']['valore'],2,',','.').'</td>';
                    echo '<td style="">'.number_format($op*$this->totale['totale']['std']['pass']['valore'],0,',','.').'</td>';
                    echo '<td style="border-right:1px solid black;">'.number_format($op*$this->totale['totale']['std']['cont']['valore'],0,',','.').'</td>';
                    
                    foreach ($this->totale['totale']['ext'] as $kt=>$t) {
                        if (!$t['flag']) continue;
                        echo '<td colspan="1">'.number_format($op*$t['valore'],2,',','.').'</td>';
                    }

                    foreach ($this->totale['totale']['indici'] as $kt=>$t) {
                        if (!$t['flag']) continue;
                        echo '<td colspan="1">'.number_format($op*$t['valore'],2,',','.').'</td>';
                    }

            echo '</tbody>';
        echo '</table>';
       
    }

    function drawSubTot($tipo,$operatore) {

        ///////////////////
        //prova
        //$op=($operatore=='P')?1:-1;
        $op=1;
        ///////////////////

        echo '<table class="c2r_table c2r_subtot" style="">';
            echo '<tbody>';
                echo '<tr>';
                    echo '<td style="width:120px;text-align:left;font-size:0.8em;font-weight:bold;" >'.$tipo.'</td>';
                    echo '<td style="border-left:1px solid black;">'.number_format($op*$this->totale['totale']['std']['man']['lordo'],2,',','.').'</td>';
                    echo '<td>'.number_format($op*$this->totale['totale']['std']['man']['netto'],2,',','.').'</td>';
                    //echo '<td style="border-right:1px solid black;">'.number_format($op*($this->totale['totale']['std']['man']['lordo']-$this->totale['totale']['std']['man']['netto']),2,',','.').'</td>';
                    //echo '<td style="border-right:1px solid black;">'.number_format($op*($this->totale['totale']['std']['man']['costo']),2,',','.').'</td>';

                    echo '<td style="border-left:1px solid black;">'.number_format($op*$this->totale['totale']['std']['ric']['lordo'],2,',','.').'</td>';
                    echo '<td>'.number_format($op*$this->totale['totale']['std']['ric']['netto'],2,',','.').'</td>';
                    //echo '<td style="border-right:1px solid black;">'.number_format($op*($this->totale['totale']['std']['ric']['lordo']-$this->totale['totale']['std']['ric']['netto']),2,',','.').'</td>';
                    //echo '<td style="border-right:1px solid black;">'.number_format($op*($this->totale['totale']['std']['ric']['costo']),2,',','.').'</td>';

                    echo '<td style="border-left:1px solid black;">'.number_format($op*$this->totale['totale']['std']['var']['lordo'],2,',','.').'</td>';
                    echo '<td>'.number_format($op*$this->totale['totale']['std']['var']['netto'],2,',','.').'</td>';
                    //echo '<td style="border-right:1px solid black;">'.number_format($op*($this->totale['totale']['std']['var']['lordo']-$this->totale['totale']['std']['var']['netto']),2,',','.').'</td>';
                    //echo '<td style="border-right:1px solid black;">'.number_format($op*($this->totale['totale']['std']['var']['costo']),2,',','.').'</td>';

                    echo '<td>'.number_format($op*$this->totale['totale']['std']['ore']['valore'],2,',','.').'</td>';
                    echo '<td style="">'.number_format($op*$this->totale['totale']['std']['pass']['valore'],0,',','.').'</td>';
                    echo '<td style="border-right:1px solid black;">'.number_format($op*$this->totale['totale']['std']['cont']['valore'],0,',','.').'</td>';
                    
                    foreach ($this->totale['totale']['ext'] as $kt=>$t) {
                        if (!$t['flag']) continue;
                        echo '<td colspan="1">'.number_format($op*$t['valore'],2,',','.').'</td>';
                    }

                    foreach ($this->totale['totale']['indici'] as $kt=>$t) {
                        if (!$t['flag']) continue;
                        echo '<td colspan="1">'.number_format($op*$t['valore'],2,',','.').'</td>';
                    }
                echo '</tr>';
            echo '</tbody>';
        echo '</table>';
    }

    function drawTotClasse($classe) {

        ///////////////////
        //prova
        $op=1;
        ///////////////////
        
        echo '<table class="c2r_table c2r_clatot" style="">';
            echo '<tbody>';
                echo '<tr>';
                    echo '<td style="width:120px;text-align:left;font-size:0.8em;font-weight:bold;" >'.$classe.'</td>';
                    echo '<td style="border-left:1px solid black;">'.number_format($op*$this->totale['totale']['std']['man']['lordo'],2,',','.').'</td>';
                    echo '<td>'.number_format($op*$this->totale['totale']['std']['man']['netto'],2,',','.').'</td>';
                    //echo '<td style="border-right:1px solid black;">'.number_format($op*($this->totale['totale']['std']['man']['lordo']-$this->totale['totale']['std']['man']['netto']),2,',','.').'</td>';
                    //echo '<td style="border-right:1px solid black;">'.number_format($op*($this->totale['totale']['std']['man']['costo']),2,',','.').'</td>';

                    echo '<td style="border-left:1px solid black;">'.number_format($op*$this->totale['totale']['std']['ric']['lordo'],2,',','.').'</td>';
                    echo '<td>'.number_format($op*$this->totale['totale']['std']['ric']['netto'],2,',','.').'</td>';
                    //echo '<td style="border-right:1px solid black;">'.number_format($op*($this->totale['totale']['std']['ric']['lordo']-$this->totale['totale']['std']['ric']['netto']),2,',','.').'</td>';
                    //echo '<td style="border-right:1px solid black;">'.number_format($op*($this->totale['totale']['std']['ric']['costo']),2,',','.').'</td>';

                    echo '<td style="border-left:1px solid black;">'.number_format($op*$this->totale['totale']['std']['var']['lordo'],2,',','.').'</td>';
                    echo '<td>'.number_format($op*$this->totale['totale']['std']['var']['netto'],2,',','.').'</td>';
                    //echo '<td style="border-right:1px solid black;">'.number_format($op*($this->totale['totale']['std']['var']['lordo']-$this->totale['totale']['std']['var']['netto']),2,',','.').'</td>';
                    //echo '<td style="border-right:1px solid black;">'.number_format($op*($this->totale['totale']['std']['var']['costo']),2,',','.').'</td>';

                    echo '<td>'.number_format($op*$this->totale['totale']['std']['ore']['valore'],2,',','.').'</td>';
                    echo '<td style="">'.number_format($op*$this->totale['totale']['std']['pass']['valore'],0,',','.').'</td>';
                    echo '<td style="border-right:1px solid black;">'.number_format($op*$this->totale['totale']['std']['cont']['valore'],0,',','.').'</td>';
                    
                    foreach ($this->totale['totale']['ext'] as $kt=>$t) {
                        if (!$t['flag']) continue;
                        echo '<td colspan="1">'.number_format($op*$t['valore'],2,',','.').'</td>';
                    }

                    foreach ($this->totale['totale']['indici'] as $kt=>$t) {
                        if (!$t['flag']) continue;
                        echo '<td colspan="1">'.number_format($op*$t['valore'],2,',','.').'</td>';
                    }
                echo '</tr>';
            echo '</tbody>';
        echo '</table>';
    }

    function drawTotBlocco() {

        ///////////////////
        //prova
        $op=1;
        ///////////////////
        
        echo '<table class="c2r_table c2r_blotot" style="">';
            echo '<tbody>';
                echo '<tr>';
                    echo '<td style="width:120px;text-align:left;font-size:0.8em;font-weight:bold;" ></td>';
                    echo '<td style="border-left:1px solid black;">'.number_format($op*$this->totale['totale']['std']['man']['lordo'],2,',','.').'</td>';
                    echo '<td>'.number_format($op*$this->totale['totale']['std']['man']['netto'],2,',','.').'</td>';
                    //echo '<td style="border-right:1px solid black;">'.number_format($op*($this->totale['totale']['std']['man']['lordo']-$this->totale['totale']['std']['man']['netto']),2,',','.').'</td>';
                    //echo '<td style="border-right:1px solid black;">'.number_format($op*($this->totale['totale']['std']['man']['costo']),2,',','.').'</td>';

                    echo '<td style="border-left:1px solid black;">'.number_format($op*$this->totale['totale']['std']['ric']['lordo'],2,',','.').'</td>';
                    echo '<td>'.number_format($op*$this->totale['totale']['std']['ric']['netto'],2,',','.').'</td>';
                    //echo '<td style="border-right:1px solid black;">'.number_format($op*($this->totale['totale']['std']['ric']['lordo']-$this->totale['totale']['std']['ric']['netto']),2,',','.').'</td>';
                    //echo '<td style="border-right:1px solid black;">'.number_format($op*($this->totale['totale']['std']['ric']['costo']),2,',','.').'</td>';

                    echo '<td style="border-left:1px solid black;">'.number_format($op*$this->totale['totale']['std']['var']['lordo'],2,',','.').'</td>';
                    echo '<td>'.number_format($op*$this->totale['totale']['std']['var']['netto'],2,',','.').'</td>';
                    //echo '<td style="border-right:1px solid black;">'.number_format($op*($this->totale['totale']['std']['var']['lordo']-$this->totale['totale']['std']['var']['netto']),2,',','.').'</td>';
                    //echo '<td style="border-right:1px solid black;">'.number_format($op*($this->totale['totale']['std']['var']['costo']),2,',','.').'</td>';

                    echo '<td>'.number_format($op*$this->totale['totale']['std']['ore']['valore'],2,',','.').'</td>';
                    echo '<td style="">'.number_format($op*$this->totale['totale']['std']['pass']['valore'],0,',','.').'</td>';
                    echo '<td style="border-right:1px solid black;">'.number_format($op*$this->totale['totale']['std']['cont']['valore'],0,',','.').'</td>';
                    
                    foreach ($this->totale['totale']['ext'] as $kt=>$t) {
                        if (!$t['flag']) continue;
                        echo '<td colspan="1">'.number_format($op*$t['valore'],2,',','.').'</td>';
                    }

                    foreach ($this->totale['totale']['indici'] as $kt=>$t) {
                        if (!$t['flag']) continue;
                        echo '<td colspan="1">'.number_format($op*$t['valore'],2,',','.').'</td>';
                    }
                echo '</tr>';
            echo '</tbody>';
        echo '</table>';
    }

    function eval_r19($arr) {

        $op=($arr['operatore']=='M')?-1:1;

        if ($arr['ind_tipo_riga']=='R') {

            if ((int)$arr['cod_categoria_vendita']>0 && (int)$arr['cod_categoria_vendita']<10) {
                $this->totale['totale']['ext']['r19']['valore']+=$op*$arr['listino']*$arr['qta'];
            }
        }
    }

    function eval_rv($arr) {

        $op=($arr['operatore']=='M')?-1:1;

        if ($arr['ind_tipo_riga']=='R') {

            if ($arr['cod_tipo_articolo']=='V') {
                //$this->totale['totale']['ext']['rv']['valore']+=$op*$arr['listino']*$arr['qta'];
                $this->totale['totale']['ext']['rv']['valore']+=$op*$arr['importo'];
            }
        }
    }

    function eval_rp($arr) {

        $op=($arr['operatore']=='M')?-1:1;

        if ($arr['ind_tipo_riga']=='R') {

            if ($arr['cod_tipo_articolo']=='P') {
                //$this->totale['totale']['ext']['rp']['valore']+=$op*$arr['listino']*$arr['qta'];
                $this->totale['totale']['ext']['rp']['valore']+=$op*$arr['importo'];
            }
        }
    }

    function eval_inc($arr) {

        if (isset($arr['valore_incentivo'])) {
            $this->totale['totale']['ext']['inc']['valore']+=$arr['valore_incentivo'];
        } 

    }

    //=======================================

    function getValExt($ext) {

        if (isset($this->totale['totale']['ext'][$ext])){
            return $this->totale['totale']['ext'][$ext]['valore'];
        }
        else return false;
    }

    function getTotVal($indice,$valore) {
        //indice= man - ric - var
        //valore= lordo - netto - costo

        return $this->totale['totale']['std'][$indice][$valore];
    }

}

?>