<?php
require_once ($_SERVER['DOCUMENT_ROOT']."/nebula/core/odl/odl_func.php");
require_once ($_SERVER['DOCUMENT_ROOT']."/nebula/core/panorama/intervallo.php");
require_once ($_SERVER['DOCUMENT_ROOT']."/nebula/core/divo/divo.php");

class c2rBudget_S {

    protected $tpoReparti=array();
    protected $reparti=array();

    protected $diretti=array();
    protected $presDiretti=array();
    protected $presSubDiretti=array();
    protected $presTotDiretti=array();

    protected $indiretti=array();
    protected $percIndiretti=array();
    protected $presCosIndiretti=array();
    protected $presTotIndiretti=array();
    protected $totInd=array();

    protected $catCosti=array();
    protected $totCosti=array();
    protected $recCosti=array();
    protected $raggCosti=array();
    protected $fissi=array();

    protected $gruppi=array();
    protected $rifGruppi=array();
    protected $escGruppi=array();
    protected $gruppiIndiretti=array();

    protected $margine=array();
    protected $ricambi=array();
    protected $defRicambi=array(
        "default"=>45
    );

    protected $blocco=array();

    protected $griglia=array();

    //se qualsiasi reparto prende in prestito dal reparto CAR aumenta la presenza CAR, per qualsiasi altro reparto prestante aumenta TEC
    //se il reparto CAR presta a qualsiasi NON gli viene scalata la presenza, se qualsiasi altro reparto presta a qualsiasi gli viene scalata TEC
    //QUESTE REGOLE SONO LEGATE AD UN PERIODO DI TEMPO QUINDI SE DOVESSERO CAMBIARE BISOGNERÀ SUDDIVIDERE L'ANALISI DEI DIRETTI IN INTERVALLI COMPATIBILI CON LE VARIAZIONI
    //LE ORE PRESTATE E PRESE IN PRESTITO DEVONO ESSERE SUDDIVISE PERCENTUALMENTE NEGLI INTERVALLI E TRATTATE SECONDO LE REGOLE DEI SINGOLI PERIODI
    protected $prodLinkRif=array(
        "inizio"=>"20200101",
        "fine"=>"21001231",
        "common"=>array(
            "CAR"=>array(
                "needed"=>true,
                "generali"=>true,
                "materiali"=>true,
                "presMat"=>array('CAR'),
                "presNOprestTOT"=>'CAR',
                "direttoNOsum"=>true,
                "NOsum"=>array('gen','mat'),
                "NOself"=>array('gec'),
                "oneriNOsum"=>true,
                "obiettivo"=>0
            ),
            "PRP"=>array(
                "needed"=>true,
                "generali"=>false,
                "materiali"=>true,
                "presMat"=>array(),
                "presNOprestTOT"=>false,
                "direttoNOsum"=>true,
                "NOsum"=>array('gen','mat'),
                "NOself"=>array('gec'),
                "oneriNOsum"=>false,
                "obiettivo"=>0        
            )
        ),
        "inprestito"=>array(
            "XXX"=>array(
                "CAR"=>"CAR",
                "XXX"=>"TEC"
            )
        ),
        "prestato"=>array(
            "CAR"=>array(
                "XXX"=>"NONE"
            ),
            "XXX"=>array(
                "XXX"=>"TEC"
            )
        )
    );

    protected $fattLinkRif=array(
        "oneriAll"=>array(
            "CAR"=>array(
                array(
                    'car',
                    100
                ),
            ),
            "PRP"=>array(
                array(
                    'rev',
                    80
                )
            )
        ),
        "oneriCompensation"=>array(
            "UPM"=>array(
                array('ext','ter',100)
            )
        ),
        "oneriVar"=>array(
            "rev"=>array(
               "XXX"=>20
            )
        ),
        "manAll"=>array(
            "CAR"=>array('car')
        ),
        "ricAll"=>array(
            "CAR"=>array('car')
        ),
        "lisAll"=>array(
            "CAR"=>array('car')
        )
    );

    //valori derivanti dal calcolo da attribuire ai reparti comuni
    protected $oneriExtra=array(
        "oneri"=>"Oneri Acquisiti"
    );
    protected $manExtra=array(
        "man"=>"Manodopera Acquisita"
    );
    protected $ricExtra=array(
        "ric"=>"Ricambi Acquisiti"
    );
    protected $lisExtra=array(
        "ric"=>"Listino Acquisito"
    );

    protected $endTariffa="";
    protected $tariffa=array();

    protected $param;
    protected $tpoIntervallo;
    protected $fatturato;
    protected $galileo;

    protected $log=array();

    function __construct($param,$galileo) {

        $this->galileo=$galileo;

        $param['inizio']=str_replace("-","",$param['inizio']);
        $param['fine']=str_replace("-","",$param['fine']);

        foreach (explode(',',substr($param['reparti'],0,-1)) as $x) {
            $x=str_replace("'","",$x);
            $this->reparti[$x]=$x;
        }

        $this->param=$param;

        $info=array(
            'data_i'=>$this->param['inizio'],
            'data_f'=>$this->param['fine']
        );

        $this->tpoIntervallo=new quartetIntervallo($info,$this->reparti,$this->galileo);
        $this->tpoIntervallo->calcola();
        $this->tpoIntervallo->calcolaDayTot();

        $this->diretti=$this->tpoIntervallo->getCollaboratori();

        $this->galileo->getReparti('S','');
        $fetID=$this->galileo->preFetchBase('reparti');

        while($row=$this->galileo->getFetchBase('reparti',$fetID)) {
            $this->tpoReparti[$row['reparto']]=$row;
        }

        if (isset($this->param['prodLink'])) {
            $this->param['prodLink']=json_decode(base64_decode($this->param['prodLink']),true);
        }
    }

    function getTariffa ($tipo,$pointer,$reparto) {

        $a=false;
        $def=false;

        $res=false;

        switch ($tipo) {
            case 'diretto': 
                $a='costoManDir';
                $def='costoDefDir';
            break;
            case 'indiretto': 
                $a='costoManInd';
                $def='costoDefInd';
            break;
        }

        //se è stato definito l'array delle tariffe
        if ($a) {
            if (array_key_exists($reparto,$this->param[$a])) {
                foreach ($this->param[$a][$reparto] as $k=>$t) {
                    $i=mainFunc::gab_tots($t['data_i']);
                    $f=mainFunc::gab_tots($t['data_f']);

                    if ($pointer>=$i && $pointer<=$f) {
                        $res=array(
                            "end"=>$f,
                            "tariffa"=>$t['mano']
                        );
                    }
                }
            }
        }

        //se $res è ancora false ed è stato definito l'array di default
        if (!$res && $def) {
            $res=array(
                "end"=>$pointer,
                "tariffa"=>$this->param[$def]
            );
        }

        return $res;
    }

    function getCostoInd($gruppo,$rep) {

        $res=0;

        if (array_key_exists($rep,$this->param['costoManInd'])) {
            foreach ($this->param['costoManInd'][$rep] as $k=>$t) {
                $i=($this->param['inizio']<$t['data_i'])?$t['data_i']:$this->param['inizio'];
                $f=($this->param['fine']>$t['data_f'])?$t['data_f']:$this->param['fine'];
                $g=mainFunc::gab_delta_tempo($i,$f,'g')+1;
                $v=isset($t['mano'][$gruppo])?$t['mano'][$gruppo]:$t['mano']['gen'];
                $res+=($v*$this->param['gg_eqiv'])*($g/$this->param['totGG'])*8;
            }
        }
        elseif ($this->param['costoDefInd']) {
            $v=isset($this->param['costoDefInd'][$gruppo])?$this->param['costoDefInd'][$gruppo]:$this->param['costoDefInd']['gen'];
            $res=$v*$this->param['gg_eqiv']*8;

            //$this->log[]=array($gruppo,$rep,$v,$res);
        }

        return $res;
    }

    function setGRlink($vgr,$reparto,$repInp) {

        $gr="";
        if (array_key_exists($reparto,$this->prodLinkRif[$vgr])) {
            $gr=(array_key_exists($repInp,$this->prodLinkRif[$vgr][$reparto]))?$this->prodLinkRif[$vgr][$reparto][$repInp]:$this->prodLinkRif[$vgr][$reparto]['XXX'];
        }
        else {
            $gr=(array_key_exists($repInp,$this->prodLinkRif[$vgr]['XXX']))?$this->prodLinkRif[$vgr]['XXX'][$repInp]:$this->prodLinkRif[$vgr]['XXX']['XXX'];
        }

        return $gr;
    }

    function build() {

        $tempParam=$this->param;
        $tempParam['reparti']=$tempParam['allReparti'];
        $this->fatturato=new c2rFatturato_S($tempParam,$this->galileo);
        $this->fatturato->getLines();
        $this->param['fattLink']=$this->fatturato->getBudget();
        $this->param['fattTotLink']=$this->fatturato->getTotBudget();
        $this->param['fattTotaloneLink']=$this->fatturato->getTotaloneBudget();

        $allReparti=array();
        foreach (explode(',',substr($this->param['allReparti'],0,-1)) as $x) {
            $x=str_replace("'","",$x);
            $allReparti[$x]=$x;
        }

        foreach ($allReparti as $rep=>$r) {
            $this->griglia[$rep]=array();
            $this->griglia[$rep]['man']=array(
                "valore"=>0,
                "extra"=>0
            );
            $this->griglia[$rep]['ric']=array(
                "valore"=>0,
                "extra"=>0,
                "gesmag"=>0
            );
            $this->griglia[$rep]['lis']=array(
                "valore"=>0,
                "extra"=>0,
                "costo"=>0,
                "excos"=>0
            );
        }
        $this->griglia['tot']=array();
        $this->griglia['tot']['man']=0;
        $this->griglia['tot']['ric']=0;
        $this->griglia['tot']['pareggio']=0;

        //RICAMBI
        //TEST
        $row=array(
            array(
                "data_i"=>"20240101",
                "data_f"=>"21001231",
                "margineMag"=>'{"XXX":2}',
                "marche"=>'["V","P","X"]',
                "sconto"=>'{"XXX":{"V":{"sconto":42,"fatt":"rv"},"P":{"sconto":42,"fatt":"rp"},"X":{"sconto":67,"fatt":""}}}'
            )
        );
        
        foreach ($row as $k=>$v) {

            //per il TEST verifico l'intersezione
            if ($v['data_f']<$this->param['inizio'] || $v['data_i']>$this->param['fine']) continue;

            $v['marche']=json_decode($v['marche'],true);
            $v['margineMag']=json_decode($v['margineMag'],true);
            $v['sconto']=json_decode($v['sconto'],true);

            //VA BENE PRENDERE SOLO L'ULTIMA REGISTRAZIONE
            $this->ricambi=$v;
        }

        //ONERI reparti comuni come la CARROZZERIA
        foreach ($this->fattLinkRif['oneriAll'] as $rep=>$oneri) {
            $this->param[$rep]['budget']['totale']['std']['oneri']['tag']=$this->oneriExtra['oneri'];
            $this->param[$rep]['budget']['totale']['std']['oneri']['netto']=0;

            foreach ($oneri as $ko=>$o) {

                foreach ($this->param['fattLink'] as $krep=>$r) {
                    if ($krep==$rep) continue;
                    foreach ($r as $classe=>$c) {
                        if ($classe==$o[0]) {
                            $this->param[$rep]['budget']['totale']['std']['oneri']['netto']+=$c['totale']['std']['var']['netto']*($o[1]/100);
                            $this->param[$rep]['budget']['totale']['std']['oneri']['perc']=($o[1]==100)?true:false;
                            $this->param[$rep]['budget']['totale']['std']['oneri']['reparti'][$krep]=$c['totale']['std']['var']['netto']*($o[1]/100);
                        }
                    }
                }
            }
        }

        //MANODOPERA reparti comuni come la CARROZZERIA
        foreach ($this->fattLinkRif['manAll'] as $rep=>$oneri) {
            $this->param[$rep]['budget']['totale']['std']['man']['tag']=$this->manExtra['man'];
            $this->param[$rep]['budget']['totale']['std']['man']['netto']=0;

            foreach ($oneri as $ko=>$o) {

                foreach ($this->param['fattLink'] as $krep=>$r) {
                    if ($krep==$rep) continue;
                    foreach ($r as $classe=>$c) {
                        if ($classe==$o) {
                            $this->param[$rep]['budget']['totale']['std']['man']['netto']+=$c['totale']['std']['man']['netto'];
                        }
                    }
                }
            }
        }

        //RICAMBI GRIGLIA
        //$this->griglia['tot']['ric']=0;
        $temp=0;
        //foreach ($this->reparti as $rep=>$r) {
        foreach ($allReparti as $rep=>$r) {
            $val=0;
            if (isset($this->param['fattLink'][$rep])) {
                foreach ($this->param['fattLink'][$rep] as $classe=>$c) {
                    if ($classe=='budget') continue;
                    $tv=(isset($c['totale']['std']['ric']['netto'])?$c['totale']['std']['ric']['netto']:0);
                    $val+=$tv;
                }
            }
            //esclusione dell'HANDLING
            $val-=(isset($this->param['fattLink'][$rep]['budget'])?$this->param['fattLink'][$rep]['budget']['totale']['std']['handling']['netto']:0);
            $this->griglia[$rep]['ric']['valore']+=$val;
            if (array_key_exists($rep,$this->reparti)) {
                $temp+=$val;
            }
        }
        $this->griglia['tot']['ric']+=$temp;

        //LISTINO
        //foreach ($this->reparti as $rep=>$r) {
        foreach ($allReparti as $rep=>$r) {
            $lis=isset($this->param['fattTotaloneLink'][$rep]['totale']['std']['ric']['lordo'])?$this->param['fattTotaloneLink'][$rep]['totale']['std']['ric']['lordo']:0;
            $gar=isset($this->param['fattTotLink'][$rep]['gar']['totale']['std']['ric']['lordo'])?$this->param['fattTotLink'][$rep]['gar']['totale']['std']['ric']['lordo']:0;
            $this->griglia[$rep]['lis']['valore']=$lis-$gar;
        }

        //SCONTO MEDIO
        //foreach ($this->reparti as $rep=>$r) {
        foreach ($allReparti as $rep=>$r) {

            $this->defRicambi['reparti'][$rep]=array(
                "valore"=>$this->defRicambi['default'],
                "default"=>true,
                "tot"=>0
            );

            $this->defRicambi['reparti'][$rep]['tot']=(isset($this->param['fattTotaloneLink'][$rep]['totale']['std']['ric']['netto'])?$this->param['fattTotaloneLink'][$rep]['totale']['std']['ric']['netto']:0);
            if ($this->defRicambi['reparti'][$rep]['tot']==0) {
                //echo '<td style="text-align:right;"></td>';
                continue;
            }
            //"sconto"=>'{"XXX":{"V":{"sconto":42,"fatt":"rv"},"X":{"sconto":67,fatt:""}}'

            $arr=false;
            if (isset($this->ricambi['sconto'][$rep])) $arr=$rep;
            elseif (isset($this->ricambi['sconto']['XXX'])) $arr='XXX';

            $x=array();
            $prc=0;
            $fr=1;

            //$this->log[]=$this->ricambi;

            if ($arr!==false) {
                
                foreach ($this->ricambi['sconto'][$arr] as $tipo=>$t) {

                    //$this->log[]=$t;
                
                    if ($tipo=='X') {
                        $x=$t;
                        continue;
                    }

                    if (isset($this->param['fattTotaloneLink'][$rep]['totale']['ext'][$t['fatt']]['valore'])) {
                        $rv=$this->param['fattTotaloneLink'][$rep]['totale']['ext'][$t['fatt']]['valore'];
                        //totale del tipo di ricambi sul totale
                        $parte=$rv/$this->defRicambi['reparti'][$rep]['tot'];
                        $prc+=$t['sconto']*$parte;
                        $fr-=$parte;
                        $this->defRicambi['reparti'][$rep]['default']=false;
                    }
                }
            }

            $prc+=(isset($x['sconto'])?$x['sconto']:$this->defRicambi['default'])*$fr;
            $this->defRicambi['reparti'][$rep]['valore']=$prc;
            
            //echo '<td style="text-align:right;">'.number_format($this->defRicambi['reparti'][$rep]['valore'],1,',','').'%</td>';
        }

        //RICAMBI reparti comuni come la CARROZZERIA
        foreach ($this->fattLinkRif['ricAll'] as $rep=>$oneri) {
            $this->param[$rep]['budget']['totale']['std']['ric']['tag']=$this->ricExtra['ric'];
            $this->param[$rep]['budget']['totale']['std']['ric']['netto']=0;
            $this->param[$rep]['budget']['totale']['std']['ric']['lordo']=0;

            foreach ($oneri as $ko=>$o) {

                foreach ($this->param['fattLink'] as $krep=>$r) {
                    if ($krep==$rep) continue;
                    //#######################################################################################################
                    //PER IL LORDO FUNZIONA SE NON CI SONO GARANZIE DA TOGLIERE E SIA PER CARROZZERIA CHE REVISIONI VA BENE
                    //NEL CASO NON BASTA UN TOTALE PER CLASSE MA DEVE ESSERE ANCHE SUDDIVISO PER TIPO
                    //#######################################################################################################
                    foreach ($r as $classe=>$c) {
                        if ($classe==$o) {
                            $this->param[$rep]['budget']['totale']['std']['ric']['netto']+=$c['totale']['std']['ric']['netto'];
                            $this->param[$rep]['budget']['totale']['std']['ric']['lordo']+=$c['totale']['std']['ric']['lordo'];
                            $this->param[$rep]['budget']['totale']['std']['ric']['reparti'][$krep]=$c['totale']['std']['ric']['netto'];
                        }
                    }
                }
            }
        }

        //ricalcolo sconto medio
        foreach ($this->reparti as $rep=>$r) {
            //$this->defRicambi['reparti'][$rep]['valore']=$prc;
            //$prc=$this->defRicambi['reparti'][$rep]['valore'];
            $prc=0;
            //se ci sono dei ricambi derivanti da altri reparti
            if (isset($this->param[$rep]['budget']['totale']['std']['ric']['netto'])) { 
                //$tot=$this->param[$rep]['budget']['totale']['std']['ric']['netto']+$this->griglia[$rep]['ric'];
                $tot=$this->param[$rep]['budget']['totale']['std']['ric']['netto'];
                if ($tot!=0) {
                    //$prc=$this->defRicambi['reparti'][$rep]['valore']*($this->griglia[$rep]['ric']/$tot);
                    if (isset($this->param[$rep]['budget']['totale']['std']['ric']['reparti'])) {
                        foreach ($this->param[$rep]['budget']['totale']['std']['ric']['reparti'] as $krep=>$rr) {
                            if (isset($this->defRicambi['reparti'][$krep]['valore'])) {
                                //$this->log[]=array($rep,$krep,$this->defRicambi['reparti'][$krep]['valore'],$rr,$tot,$prc);
                                $prc+=$this->defRicambi['reparti'][$krep]['valore']*($rr/$tot);
                            }
                        }
                    }
                }

                $this->defRicambi['reparti'][$rep]['ricalcolo']=$prc;
            }
        }

        ////////////////////////////////////////////////////////////////////////////////////

        $txt="";
        foreach ($this->prodLinkRif['common'] as $kcom=>$c) {
            if ($c['needed'] && !array_key_exists($kcom,$this->reparti)) $txt.='<div style="font-weight:bold;">Non è stato selezionato il reparto obbligatorio '.$kcom.'.</div>';
        }

        if ($txt!="") die($txt);

        $this->param['diretti']=0;
        $this->param['presDiretti']=0;
        $this->param['indiretti']=0;
        $this->param['gg_eqiv']=0;

        $this->param['costoDefInd']=json_decode('{"gar":17,"bdc":17,"mag":18,"lav":15,"gen":17}',true);
        $this->param['costoDefDir']=json_decode('{"TEC":18,"CAR":19,"RT":20,"RS":20,"RC":20,"altro":18}',true);
        $this->param['costoManDir']=array();
        $this->param['costoManInd']=array();

        $this->param['totGG']=mainfunc::gab_delta_tempo($this->param['inizio'],$this->param['fine'],'g')+1;

        //ORDER BY REPARTO e DATA_I - intervalli intersecanti il periodo di analisi
        //TEST
        $row=array(
            array(
                "reparto"=>"VWS",
                "data_i"=>'20200101',
                "data_f"=>'21001231',
                "mano"=>'{"TEC":18,"RT":20,"RS":20,"RC":20,"CAR":19,"altro":18}'
            ),
            array(
                "reparto"=>"AUS",
                "data_i"=>'20200101',
                "data_f"=>'21001231',
                "mano"=>'{"TEC":18,"RT":20,"RS":20,"RC":20,"CAR":19,"altro":18}'
            )
        );

        foreach ($row as $k=>$v) {

            //per il TEST verifico l'intersezione
            if ($v['data_f']<$this->param['inizio'] || $v['data_i']>$this->param['fine']) continue;
            
            $v['mano']=json_decode($v['mano'],true);

            $this->param['costoManDir'][$v['reparto']][]=$v;
        }

        //END TEST

        //ORDER BY REPARTO e DATA_I - intervalli intersecanti il periodo di analisi
        //TEST
        $row=array(
            array(
                "reparto"=>"VWS",
                "data_i"=>'20200101',
                "data_f"=>'21001231',
                "mano"=>'{"gar":17,"bdc":17,"mag":18,"lav":15,"gen":17}'
            ),
            array(
                "reparto"=>"AUS",
                "data_i"=>'20200101',
                "data_f"=>'21001231',
                "mano"=>'{"gar":17,"bdc":17,"mag":18,"lav":15,"gen":17}'
            )
        );

        foreach ($row as $k=>$v) {

            //per il TEST verifico l'intersezione
            if ($v['data_f']<$this->param['inizio'] || $v['data_i']>$this->param['fine']) continue;
            
            $v['mano']=json_decode($v['mano'],true);

            $this->param['costoManInd'][$v['reparto']][]=$v;
        }

        //END TEST

        //////////////////////////////////////////////////////////////////////
        //TEST
        //gruppi validi in base al Macroreparto S
        $row=array("MAG");
        
        foreach ($row as $k=>$g) {
            $this->escGruppi[]=$g;
        }

        $row=array(
            "TEC"=>array(
                "tag"=>"TEC",
                "pos"=>1
            ),
            "RT"=>array(
                "tag"=>"RT",
                "pos"=>3
            ),
            "RC"=>array(
                "tag"=>"RC",
                "pos"=>4
            ),
            "RS"=>array(
                "tag"=>"RS",
                "pos"=>5
            ),
            "PR"=>array(
                "tag"=>"TEC",
                "pos"=>1
            ),
            "PRT"=>array(
                "tag"=>"RT",
                "pos"=>3
            ),
            "TR"=>array(
                "tag"=>"TEC",
                "pos"=>3
            ),
            "vRT"=>array(
                "tag"=>"RT",
                "pos"=>3
            ),
            "ASS"=>array(
                "tag"=>"RC",
                "pos"=>4
            ),
            "CAR"=>array(
                "tag"=>"CAR",
                "pos"=>2
            ),
            "VER"=>array(
                "tag"=>"CAR",
                "pos"=>2
            ),
            "LAM"=>array(
                "tag"=>"CAR",
                "pos"=>2
            )
        );

        $tgruppi=array();

        foreach ($row as $gruppo=>$g) {
            $this->rifGruppi[$gruppo]=$g;

            if (!array_key_exists($g['tag'],$tgruppi)) {
                $tgruppi[$g['tag']]=$g;
            }
        }

        usort($tgruppi, function ($a, $b) {
            return $a['pos'] <=> $b['pos']; // Usando lo spaceship operator (PHP 7+)
        });

        foreach ($tgruppi as $kg=>$g) {
            $this->gruppi[$g['tag']]=$g;
            $this->gruppi[$g['tag']]['diretti']=0;
            $this->gruppi[$g['tag']]['costo']=0;
        } 


        //END TEST

        $end=mainFunc::gab_tots($this->param['fine']);

        $this->presTotDiretti=$this->gruppi;
        $this->presTotDiretti['altro']['diretti']=0;
        $this->presTotDiretti['altro']['costo']=0;
        
        foreach ($this->diretti as $reparto=>$r) {

            $this->presSubDiretti[$reparto]=$this->gruppi;
            $this->presSubDiretti[$reparto]['altro']['diretti']=0;
            $this->presSubDiretti[$reparto]['altro']['costo']=0;

            foreach ($r as $IDcoll=>$c) {

                $this->param['diretti']++;

                $pointer=mainFunc::gab_tots($this->param['inizio']);

                if (!array_key_exists($IDcoll,$this->presDiretti)) {
                    $this->presDiretti[$IDcoll]['gruppi']=$this->gruppi;
                    $this->presDiretti[$IDcoll]['error']=array();
                }

                foreach ($c as $kc=>$cc) {

                    $endc=mainFunc::gab_tots($cc['data_f']);

                    //timestamp fine validità tariffa
                    $this->endTariffa=0;
                    $this->tariffa=array();

                    while ($pointer<=$end && $pointer<=$endc) {

                        if ($pointer>$this->endTariffa) {
                            $tt=$this->getTariffa('diretto',$pointer,$reparto);
                            if ($tt) {
                                $this->endTariffa=$tt['end'];
                                $this->tariffa=$tt['tariffa'];
                            }
                            else {
                                $this->endTariffa=$pointer;
                                $this->tariffa=array();
                            }
                        }

                        if ($temp=$this->tpoIntervallo->getPresenzaCollDay($reparto,$IDcoll,date('Ymd',$pointer)) ) {

                            $val=$temp['nominale']/60;

                            if ($tempEv=$this->tpoIntervallo->getEventiCollDay($reparto,$IDcoll,date('Ymd',$pointer))) {

                                //$this->log[]=$tempEv;

                                foreach ($tempEv as $classe=>$c) {

                                    if ($classe!='extra') continue;

                                    foreach ($c as $tipo=>$t) {
                                        if ($tipo!='E') continue;
                                        $val+=$t['qta']/60;
                                    }
                                }
                            }

                            if (!in_array($cc['gruppo'],$this->escGruppi)) {

                                if (array_key_exists($this->rifGruppi[$cc['gruppo']]['tag'],$this->presDiretti[$IDcoll]['gruppi'])) {
                                    $this->presDiretti[$IDcoll]['gruppi'][$this->rifGruppi[$cc['gruppo']]['tag']]['diretti']+=$val;
                                    $this->presTotDiretti[$this->rifGruppi[$cc['gruppo']]['tag']]['diretti']+=$val;
                                    $this->presSubDiretti[$reparto][$this->rifGruppi[$cc['gruppo']]['tag']]['diretti']+=$val;

                                    $ttar=$this->setTariffa($reparto,$this->rifGruppi[$cc['gruppo']]['tag']);

                                    $this->presTotDiretti[$this->rifGruppi[$cc['gruppo']]['tag']]['costo']+=$val*$ttar;
                                    $this->presSubDiretti[$reparto][$this->rifGruppi[$cc['gruppo']]['tag']]['costo']+=$val*$ttar;

                                    /*$this->log[]=array(
                                        $IDcoll,date('Ymd',$pointer),$temp['nominale']
                                    );*/
                                }
                                else {
                                    if (!array_key_exists($this->rifGruppi[$cc['gruppo']]['tag'],$this->presDiretti[$IDcoll]['error'])) $this->presDiretti[$IDcoll]['error'][$this->rifGruppi[$cc['gruppo']]['tag']]=0;
                                    $this->presDiretti[$IDcoll]['error'][$this->rifGruppi[$cc['gruppo']]['tag']]+=$val;
                                    $this->presTotDiretti['altro']['diretti']+=$val;
                                    $this->presSubDiretti[$reparto]['altro']['diretti']+=$val;

                                    if (isset($this->tariffa['altro'])) {
                                        $ttar=$this->tariffa['altro'];
                                    }
                                    else {
                                        $ttar=0;
                                        $this->presTotDiretti['altro']['errorTariffa']=true;
                                        $this->presSubDiretti[$reparto]['altro']['errorTariffa']=true;
                                    }

                                    $this->presTotDiretti['altro']['costo']+=$val*$ttar;
                                    $this->presSubDiretti[$reparto]['altro']['costo']+=$val*$ttar;
                                }

                                $this->param['presDiretti']+=$val;
                            }
                        }

                        $pointer=strtotime("+1 day", $pointer);
                    }
                }
            }

            //inserimento INPRESTITO e PRESTATO
            //la tariffa è l'ultima calcolata per il reparto
            foreach (array('inprestito','prestato') as $kgr=>$vgr) {

                $segno=($vgr=='inprestito')?1:-1;

                $this->presDiretti[$vgr][$reparto]['gruppi']=$this->gruppi;
                $this->presDiretti[$vgr]['error']=array();

                //se ci sono dei dati passati da PRODUTTIVITÀ
                if (isset($this->param['prodLink'][$vgr][$reparto])) {

                    //$this->log[]=$vgr.' '.$reparto;

                    foreach ($this->param['prodLink'][$vgr][$reparto] as $repInp=>$rx) {

                        $gr=$this->setGRlink($vgr,$reparto,$repInp);

                        if ($gr=='NONE') continue;

                        $val=(isset($rx['presenza']['valori'][$vgr]['valore']))?$rx['presenza']['valori'][$vgr]['valore']*$segno:0;
                        $ttar=$this->setTariffa($reparto,$gr);

                        $this->presDiretti[$vgr][$reparto]['gruppi'][$gr]['diretti']+=$val;
                        $this->presTotDiretti[$gr]['diretti']+=$val;
                        $this->presSubDiretti[$reparto][$gr]['diretti']+=$val;
                        $this->presTotDiretti[$gr]['costo']+=$val*$ttar;
                        $this->presSubDiretti[$reparto][$gr]['costo']+=$val*$ttar;

                    }
                }
            }
        }

        $this->param['gg_eqiv']=($this->param['diretti']>0)?($this->param['presDiretti']/$this->param['diretti'])/8:0;

        //////////////////////////////////////////////////////////
        //definizione indiretti

        //TEST
        $this->blocco=array();
        foreach ($this->tpoReparti as $k=>$v) {
            $this->blocco[$k]=0;
        }

        $this->blocco['XXX']=0;

        $row=array(
            "gar"=>array(
                "tag"=>"Garanzia",
                "macrorep"=>'S',
                "pos"=>1
            ),
            "bdc"=>array(
                "tag"=>"Bdc",
                "macrorep"=>'S',
                "pos"=>2
            ),
            "mag"=>array(
                "tag"=>"Magazzino",
                "macrorep"=>'S',
                "pos"=>3
            ),
            "lav"=>array(
                "tag"=>"Lavaggio",
                "macrorep"=>'S',
                "pos"=>4
            ),
            "gen"=>array(
                "tag"=>"Generale",
                "macrorep"=>'S',
                "pos"=>5
            )
        );

        foreach ($row as $k=>$v) {
            $v['reparti']=$this->blocco;
            $v['pres']=0;
            $v['XXX']=100;
            $this->gruppiIndiretti[$k]=$v;
            $this->presTotIndiretti[$k]=array();
            $this->presCosIndiretti[$k]=$this->blocco;
        }

        /////////////////////////////////
        /*abbinamenti che intersecano i periodo di analisi
        $row=array(
            array(
                "gruppo"=>"gar",
                "data_i"=>"20200101",
                "data_f"=>"21001231",
                "prc"=>'{"ACS":10,"AUS":40,"VWS":50}'
            ),
            array(
                "gruppo"=>"bdc",
                "data_i"=>"20200101",
                "data_f"=>"21001231",
                "prc"=>'{"ACS":10,"AUS":40,"VWS":50}'
            )
        );

        $totGG=mainfunc::gab_delta_tempo($this->param['inizio'],$this->param['fine'],'g');

        foreach ($row as $k=>$v) {

            if (array_key_exists($v['gruppo'],$this->gruppiIndiretti)) {
                if ($temp=json_decode($v['prc'],true)) {

                    $i=($v['data_i']<$this->param['inizio'])?$this->param['inizio']:$v['data_i'];
                    $f=($v['data_f']>$this->param['fine'])?$this->param['fine']:$v['data_f'];

                    $tempGG=mainfunc::gab_delta_tempo($i,$f,'g');

                    foreach ($temp as $rep=>$r) {
                        if (isset($this->gruppiIndiretti[$v['gruppo']]['reparti'][$rep])) {
                            $tval=$r*($tempGG/$totGG);
                            $this->gruppiIndiretti[$v['gruppo']]['reparti'][$rep]+=$tval;
                        }
                    }
                }
            }
        }*/

        /////////////////////////////////////////////////////
        //ordinati per POS (join gruppo) e ID e data_i
        $row=array(
            array(
                "ID"=>1,
                "gruppo"=>"gar",
                "data_i"=>"20200101",
                "data_f"=>"21001231",
                "coll"=>"Marilena Barbieri",
                "qta"=>1,
                "prc"=>'{"ACS":10,"AUS":35,"VWS":40,"UPM":10,"PNP":5}',
                "pos"=>1,
                "macrorep"=>"S"
            ),
            array(
                "ID"=>2,
                "gruppo"=>"gar",
                "data_i"=>"20240101",
                "data_f"=>"21001231",
                "coll"=>"Giannotti Elisa",
                "qta"=>0.40,
                "prc"=>'{"ACS":10,"AUS":35,"VWS":40,"UPM":10,"PNP":5}',
                "pos"=>1,
                "macrorep"=>"S"
            ),
            array(
                "ID"=>3,
                "gruppo"=>"bdc",
                "data_i"=>"20230101",
                "data_f"=>"21001231",
                "coll"=>"Angelucci Gaia",
                "qta"=>1,
                "prc"=>'{"AUS":40,"VWS":40,"UPM":10,"PNP":10}',
                "pos"=>2,
                "macrorep"=>"S"
            ),
            array(
                "ID"=>4,
                "gruppo"=>"bdc",
                "data_i"=>"20231001",
                "data_f"=>"20240831",
                "coll"=>"Ghillani Silvia",
                "qta"=>1,
                "prc"=>'{"AUS":40,"VWS":40,"UPM":10,"PNP":10}',
                "pos"=>2,
                "macrorep"=>"S"
            ),
            array(
                "ID"=>5,
                "gruppo"=>"bdc",
                "data_i"=>"20231001",
                "data_f"=>"21001231",
                "coll"=>"Serena Galli",
                "qta"=>0.75,
                "prc"=>'{"AUS":60,"VWS":20,"UPM":10,"PNP":10}',
                "pos"=>2,
                "macrorep"=>"S"
            ),
            array(
                "ID"=>6,
                "gruppo"=>"bdc",
                "data_i"=>"20240101",
                "data_f"=>"21001231",
                "coll"=>"Giannotti Elisa",
                "qta"=>0.35,
                "prc"=>'{"AUS":30,"VWS":50,"UPM":10,"PNP":10}',
                "pos"=>2,
                "macrorep"=>"S"
            ),
            array(
                "ID"=>7,
                "gruppo"=>"mag",
                "data_i"=>"20200101",
                "data_f"=>"21001231",
                "coll"=>"Off. VGI Pesaro",
                "qta"=>2,
                "prc"=>'{"AUS":40,"VWS":40,"UPM":15,"PNP":5}',
                "pos"=>3,
                "macrorep"=>"S"
            ),
            array(
                "ID"=>8,
                "gruppo"=>"mag",
                "data_i"=>"20200101",
                "data_f"=>"21001231",
                "coll"=>"POS",
                "qta"=>1,
                "prc"=>'{"POS":50}',
                "pos"=>3,
                "macrorep"=>"S"
            ),
            array(
                "ID"=>9,
                "gruppo"=>"mag",
                "data_i"=>"20200101",
                "data_f"=>"21001231",
                "coll"=>"PAS",
                "qta"=>1,
                "prc"=>'{"PAS":50}',
                "pos"=>3,
                "macrorep"=>"S"
            ),
            array(
                "ID"=>10,
                "gruppo"=>"mag",
                "data_i"=>"20240101",
                "data_f"=>"21001231",
                "coll"=>"Cesena",
                "qta"=>1,
                "prc"=>'{"PCS":25,"ACS":25}',
                "pos"=>3,
                "macrorep"=>"S"
            ),
            array(
                "ID"=>11,
                "gruppo"=>"lav",
                "data_i"=>"20240101",
                "data_f"=>"21001231",
                "coll"=>"Lavagg. Pesaro",
                "qta"=>4,
                "prc"=>'{"AUS":12,"VWS":12,"POS":12,"UPM":12,"PNP":12}',
                "pos"=>4,
                "macrorep"=>"S"
            ),
            array(
                "ID"=>12,
                "gruppo"=>"lav",
                "data_i"=>"20240101",
                "data_f"=>"21001231",
                "coll"=>"Lavagg. Cesena",
                "qta"=>1.5,
                "prc"=>'{"PCS":25,"ACS":25}',
                "pos"=>4,
                "macrorep"=>"S"
            ),
            array(
                "ID"=>13,
                "gruppo"=>"gen",
                "data_i"=>"20240101",
                "data_f"=>"21001231",
                "coll"=>"Info VW Pesaro",
                "qta"=>2,
                "prc"=>'{"VWS":30,"UPM":10}',
                "pos"=>5,
                "macrorep"=>"S"
            ),
            array(
                "ID"=>14,
                "gruppo"=>"gen",
                "data_i"=>"20240101",
                "data_f"=>"21001231",
                "coll"=>"Info AU Pesaro",
                "qta"=>1,
                "prc"=>'{"AUS":40}',
                "pos"=>5,
                "macrorep"=>"S"
            ),
            array(
                "ID"=>15,
                "gruppo"=>"gen",
                "data_i"=>"20240101",
                "data_f"=>"21001231",
                "coll"=>"Info PO Pesaro",
                "qta"=>1,
                "prc"=>'{"POS":30}',
                "pos"=>5,
                "macrorep"=>"S"
            ),
            array(
                "ID"=>16,
                "gruppo"=>"gen",
                "data_i"=>"20240101",
                "data_f"=>"21001231",
                "coll"=>"Info Ancona",
                "qta"=>1,
                "prc"=>'{"PAS":30}',
                "pos"=>5,
                "macrorep"=>"S"
            ),
            array(
                "ID"=>17,
                "gruppo"=>"gen",
                "data_i"=>"20240101",
                "data_f"=>"21001231",
                "coll"=>"Info AU Cesena",
                "qta"=>2,
                "prc"=>'{"PCS":30,"ACS":30}',
                "pos"=>5,
                "macrorep"=>"S"
            ),
            array(
                "ID"=>18,
                "gruppo"=>"gen",
                "data_i"=>"20240101",
                "data_f"=>"21001231",
                "coll"=>"Contabilità",
                "qta"=>4,
                "prc"=>'{"AUS":12,"VWS":12,"POS":10,"UPM":3,"PAS":5,"PCS":5,"ACS":5}',
                "pos"=>5,
                "macrorep"=>"S"
            )
        );

        //$totGG=mainfunc::gab_delta_tempo($this->param['inizio'],$this->param['fine'],'g');

        foreach ($row as $k=>$v) {

            if (!array_key_exists($v['gruppo'],$this->gruppiIndiretti)) continue;

            if ($temp=json_decode($v['prc'],true)) {

                $v['reparti']=$this->blocco;

                $i=($v['data_i']<$this->param['inizio'])?$this->param['inizio']:$v['data_i'];
                $f=($v['data_f']>$this->param['fine'])?$this->param['fine']:$v['data_f'];

                $tempGG=mainfunc::gab_delta_tempo($i,$f,'g')+1;

                $v['i']=$i;
                $v['f']=$f;
                $v['pres']=$v['qta']*($tempGG/$this->param['totGG']);

                $this->gruppiIndiretti[$v['gruppo']]['pres']+=$v['pres'];

                foreach ($temp as $rep=>$r) {
                    if (isset($v['reparti'][$rep])) {
                        $tval=$r*($tempGG/$this->param['totGG']);
                        $v['reparti'][$rep]+=$tval;
                    }
                }

                $this->indiretti[$v['gruppo']][]=$v;
            }
        }

        //////////////////////////////////////////////////////////////////////
        $row=array(
            array(
                "gruppo"=>"gen",
                "tag"=>"Spese Generali",
                "tot"=>true,
                "pos"=>1
            ),
            array(
                "gruppo"=>"gec",
                "tag"=>"Generali Comuni",
                "tot"=>true,
                "css"=>'font-size: 0.9em;color: violet;',
                "pos"=>2
            ),
            array(
                "gruppo"=>"mat",
                "tag"=>"Materiali",
                "tot"=>true,
                "pos"=>3
            ),
            array(
                "gruppo"=>"mac",
                "tag"=>"Materiali Comuni",
                "tot"=>true,
                "css"=>'font-size: 0.9em;color: violet;',
                "pos"=>4
            ),
            array(
                "gruppo"=>"ser",
                "tag"=>"Spese Servizi",
                "tot"=>true,
                "pos"=>5
            ),
            array(
                "gruppo"=>"ext",
                "tag"=>"Spese Esterne",
                "tot"=>true,
                "pos"=>6
            ),
            array(
                "gruppo"=>"alt",
                "tag"=>"Altre Spese",
                "tot"=>true,
                "pos"=>7
            ),
        );

        $this->fissi=array();
        foreach ($row as $k=>$v) {
            $v['costi']=0;
            $this->fissi[$v['gruppo']]=$v;
        }
        foreach ($this->reparti as $rep=>$r) {
            $this->raggCosti[$rep]=$this->fissi;
        }

        ////////////////////////////////////////////////////////////

        $row=array(
            array(
                "cat"=>'aff',
                "gruppo"=>"gen",
                "tag"=>"Affitti",
                "data_i"=>'20200101',
                "data_f"=>'21001231'
            ),
            array(
                "cat"=>'spg',
                "gruppo"=>"gen",
                "tag"=>"Spese Generali",
                "data_i"=>'20200101',
                "data_f"=>'21001231'
            ),
            array(
                "cat"=>'ama',
                "gruppo"=>"mat",
                "tag"=>"Ammort.Attrezzi",
                "data_i"=>'20200101',
                "data_f"=>'21001231'
            ),
            array(
                "cat"=>'ams',
                "gruppo"=>"gen",
                "tag"=>"Ammort.Strutt.",
                "data_i"=>'20200101',
                "data_f"=>'21001231'
            ),
            array(
                "cat"=>'sos',
                "gruppo"=>"ser",
                "tag"=>"Sostitutive",
                "data_i"=>'20200101',
                "data_f"=>'21001231'
            ),
            array(
                "cat"=>'con',
                "gruppo"=>"mat",
                "tag"=>"Mat.Consumo",
                "data_i"=>'20200101',
                "data_f"=>'21001231'
            )
        );

        foreach ($row as $k=>$v) {
            $v['reparti']=$this->blocco;
            $v['prc']=$this->blocco;
            $v['nominale']=0;
            $v['allocato']=0;
            $this->catCosti[$v['cat']]=$v;
        }

        $this->totCosti=$this->blocco;

        //intervalli che intersecano il periodo ORDER BY CAT e DATA_I
        $row=array(
            array(
                "ID"=>1,
                "data_i"=>"20240101",
                "data_f"=>"20240630",
                "cat"=>'aff',
                "costo"=>240000,
                "prc"=>'{"CAR":11,"ACS":8,"AUS":20,"VWS":20,"POS":10,"PAS":10,"PCS":10,"UPM":9,"PNP":2}'
            ),
            array(
                "ID"=>2,
                "data_i"=>"20240701",
                "data_f"=>"20241231",
                "cat"=>'aff',
                "costo"=>240000,
                "prc"=>'{"CAR":11,"ACS":8,"AUS":20,"VWS":20,"POS":10,"PAS":10,"PCS":10,"UPM":9,"PNP":2}'
            ),
            array(
                "ID"=>3,
                "data_i"=>"20240101",
                "data_f"=>"20240630",
                "cat"=>'spg',
                "costo"=>115000,
                "prc"=>'{"CAR":11,"ACS":8,"AUS":20,"VWS":20,"POS":10,"PAS":10,"PCS":10,"UPM":9,"PNP":2}'
            ),
            array(
                "ID"=>4,
                "data_i"=>"20240701",
                "data_f"=>"20241231",
                "cat"=>'spg',
                "costo"=>115000,
                "prc"=>'{"CAR":11,"ACS":8,"AUS":20,"VWS":20,"POS":10,"PAS":10,"PCS":10,"UPM":9,"PNP":2}'
            ),
            array(
                "ID"=>5,
                "data_i"=>"20240101",
                "data_f"=>"20240630",
                "cat"=>'ama',
                "costo"=>115000,
                "prc"=>'{"CAR":11,"ACS":8,"AUS":19,"VWS":19,"POS":10,"PAS":10,"PCS":10,"UPM":9,"PNP":2,"PRP":2}'
            ),
            array(
                "ID"=>6,
                "data_i"=>"20240701",
                "data_f"=>"20241231",
                "cat"=>'ama',
                "costo"=>115000,
                "prc"=>'{"CAR":11,"ACS":8,"AUS":19,"VWS":19,"POS":10,"PAS":10,"PCS":10,"UPM":9,"PNP":2,"PRP":2}'
            ),
            array(
                "ID"=>7,
                "data_i"=>"20240101",
                "data_f"=>"20240630",
                "cat"=>'ams',
                "costo"=>45000,
                "prc"=>'{"CAR":0,"ACS":50,"AUS":0,"VWS":0,"POS":0,"PAS":0,"PCS":50,"UPM":0,"PNP":0}'
            ),
            array(
                "ID"=>8,
                "data_i"=>"20240701",
                "data_f"=>"20241231",
                "cat"=>'ams',
                "costo"=>45000,
                "prc"=>'{"CAR":0,"ACS":50,"AUS":0,"VWS":0,"POS":0,"PAS":0,"PCS":50,"UPM":0,"PNP":0}'
            ),
            array(
                "ID"=>9,
                "data_i"=>"20240101",
                "data_f"=>"20240630",
                "cat"=>'sos',
                "costo"=>65000,
                "prc"=>'{"CAR":0,"ACS":15,"AUS":17,"VWS":20,"POS":22,"PAS":12,"PCS":10,"UPM":4,"PNP":0}'
            ),
            array(
                "ID"=>10,
                "data_i"=>"20240701",
                "data_f"=>"20241231",
                "cat"=>'sos',
                "costo"=>65000,
                "prc"=>'{"CAR":0,"ACS":15,"AUS":17,"VWS":20,"POS":22,"PAS":12,"PCS":10,"UPM":4,"PNP":0}'
            ),
            array(
                "ID"=>11,
                "data_i"=>"20240101",
                "data_f"=>"20240630",
                "cat"=>'con',
                "costo"=>50000,
                "prc"=>'{"CAR":50,"ACS":6,"AUS":11,"VWS":11,"POS":5,"PAS":5,"PCS":6,"UPM":4,"PNP":1,"PRP":1}'
            ),
            array(
                "ID"=>12,
                "data_i"=>"20240701",
                "data_f"=>"20241231",
                "cat"=>'con',
                "costo"=>50000,
                "prc"=>'{"CAR":50,"ACS":6,"AUS":11,"VWS":11,"POS":5,"PAS":5,"PCS":6,"UPM":4,"PNP":1,"PRP":1}'
            )
        );

        foreach ($row as $k=>$v) {

            //per il TEST verifico l'intersezione
            if ($v['data_f']<$this->param['inizio'] || $v['data_i']>$this->param['fine']) continue;

            $v['rowGG']=0;
            $v['intGG']=0;
            $this->recCosti[$k]=$v;

            //se il periodo del record è interamente contenuto nel periodo di analisi prendilo senza calcoli
            //if ($v['data_f']<=$this->param['fine'] && $v['data_i']>=$this->param['inizio']) {
            if (array_key_exists($v['cat'],$this->catCosti)) {

                $this->recCosti[$k]['rowGG']=mainfunc::gab_delta_tempo($v['data_i'],$v['data_f'],'g')+1;
                $this->recCosti[$k]['intGG']=mainfunc::gab_delta_tempo( ($v['data_i']<$this->param['inizio'])?$this->param['inizio']:$v['data_i'],($v['data_f']>$this->param['fine'])?$this->param['fine']:$v['data_f'],'g')+1;

                $tVal=($this->recCosti[$k]['rowGG']==0)?0:$v['costo']*($this->recCosti[$k]['intGG']/$this->recCosti[$k]['rowGG']);

                $this->catCosti[$v['cat']]['nominale']+=$tVal;

                if ($temp=json_decode($v['prc'],true)) {
                    foreach ($temp as $rep=>$r) {
                        if (array_key_exists($rep,$this->catCosti[$v['cat']]['reparti'])) {
                            $this->catCosti[$v['cat']]['reparti'][$rep]+=$tVal*($r/100);
                            $this->catCosti[$v['cat']]['allocato']+=$tVal*($r/100);

                            $this->catCosti[$v['cat']]['prc'][$rep]=$r;
                            $this->totCosti[$rep]+=$tVal*($r/100);

                            if (array_key_exists($rep,$this->raggCosti)) {
                                if (array_key_exists($this->catCosti[$v['cat']]['gruppo'],$this->raggCosti[$rep])) {
                                    $this->raggCosti[$rep][$this->catCosti[$v['cat']]['gruppo']]['costi']+=$tVal*($r/100);
                                }
                                else {
                                    $this->raggCosti[$rep]['alt']['costi']+=$tVal*($r/100);
                                }
                            }
                        }
                    }
                }
            }

                //continue;
            //}
        }

        //suddivisione materiali comuni
        /*$prodLinkRif=array(
        "inizio"=>"20200101",
        "fine"=>"21001231",
        "common"=>array(
            "CAR"=>array(
                "needed"=>true,
                "materiali"=>true,
                "presMat"=>array('CAR')
            ),
        */
        if (array_key_exists('mac',$this->fissi)) {
            foreach ($this->prodLinkRif['common'] as $rep=>$r) {
                if (!$r['materiali']) continue;
                //se non c'è nel reparto comune il campo spese MATERIALI salta
                if (!isset($this->raggCosti[$rep]['mat']['costi'])) continue;
                //per ogni tipo di manodopera che deve essere condivisa
                foreach ($r['presMat'] as $kpres=>$p) {
                    foreach ($this->reparti as $krep=>$reparto) {
                        if ($rep==$reparto) continue;
                        $gr=$this->setGRlink('inprestito',$reparto,$rep);
                        //se il tipo di manodopera è diverso da quello COMMON salta
                        if ($gr!=$p) continue;
                        //se il reparto comune non ha manodopera da condividere salta
                        if ($this->presSubDiretti[$rep][$gr]['diretti']==0) continue;
                        //carica la percentuale di costi nel gruppo MAC
                        $val=(isset($this->param['prodLink']['prestato'][$rep][$reparto]['presenza']['valori']['prestato']['valore']))?$this->param['prodLink']['prestato'][$rep][$reparto]['presenza']['valori']['prestato']['valore']:0;
                        $prc=$val/$this->presSubDiretti[$rep][$gr]['diretti'];
                        $costo=$this->raggCosti[$rep]['mat']['costi']*$prc;
                        $this->raggCosti[$reparto]['mac']['costi']+=$costo;

                        //$this->log[]=array($rep,$p,$reparto,$gr,$this->presSubDiretti[$rep][$gr]['diretti'],$val,$prc,$costo);
                    }
                }
            }
        }

        //suddivisione generali comuni in base alla suddivisione del materiale di consumo
        if (array_key_exists('gec',$this->fissi)) {
            foreach ($this->prodLinkRif['common'] as $rep=>$r) {
                if (!$r['generali']) continue;
                //se non c'è nel reparto comune il campo spese GENERALI salta
                if (!isset($this->raggCosti[$rep]['gen']['costi'])) continue;
                //per ogni reparto in analisi
                foreach ($this->reparti as $krep=>$p) {
                    //if ($rep==$krep) continue;
                    if (isset($this->catCosti['con']['reparti'][$krep])) {
                        $perc=$this->catCosti['con']['reparti'][$krep]/$this->catCosti['con']['nominale'];
                        $this->raggCosti[$krep]['gec']['costi']+=$this->raggCosti[$rep]['gen']['costi']*$perc;
                    } 
                }
            }
        }

        //END TEST

        //////////////////////////////////////////////////////////

        //Recupero della marginalità per il trimestre in cui ricade la data di FINE per i reparti in analisi
        //TEST
            $row=array(
                'ACS'=>8,
                'AUS'=>12,
                'VWS'=>10,
                'POS'=>40,
                'PAS'=>40,
                'PCS'=>40,
                'UPM'=>5,
                'PNP'=>2,
                'CAR'=>20
            );

            foreach ($row as $reparto=>$r) {
                if (array_key_exists($reparto,$this->reparti)) {
                    $this->margine[$reparto]=$r;
                }
            }
        //END TEST

        //////////////////////////////////////////////////////////
        //Recupero delle commissioni esterne
        //TEST
        $arr=array(
            "da"=>$this->param['inizio'],
            "a"=>$this->param['fine']
        );

        $comest=array();
        $elenco=array();

        $odlFunc=new nebulaOdlFunc($this->galileo);

        $this->galileo->executeGeneric('comest','getBudget',$arr,'');
        $fetID=$this->galileo->preFetch('comest');

        while($row=$this->galileo->getFetch('comest',$fetID)) {
            $forn=json_decode($row['fornitore'],true);
            if (!$forn || $forn['ID']!=1) {

                if ($row['odl']!="") {
                    $comest[$row['dms']][$row['odl']]=$row;
                    if (!array_key_exists($row['dms'],$elenco)) $elenco[$row['dms']]="";
                    $elenco[$row['dms']].=$row['odl'].",";
                }
                else {
                    //ATTRIBUZIONE IN BASE ALL'UTENTE CHE HA CREATO LA COMMESSA
                    $this->log[]=$row;
                }
            }
        }

        foreach ($comest as $dms=>$d) {
            foreach ($odlFunc->getCliHeadBudget($dms,array("elenco"=>substr($elenco[$dms],0,-1)),$d) as $rif=>$r) {
                if (array_key_exists($r['reparto'],$this->reparti)) {
                    $this->raggCosti[$r['reparto']]['ext']['costi']+=$r['preventivo'];
                }
            }
        }

        //END TEST

    }

    function setTariffa($reparto,$gruppo) {

        if (isset($this->tariffa[$gruppo])) {
            $ttar=$this->tariffa[$gruppo];
        }
        elseif (isset($this->param['costoDefDir'][$gruppo])) {
            $ttar=$this->param['costoDefDir'][$gruppo];
            $this->presTotDiretti[$gruppo]['defTariffa']=true;
            $this->presSubDiretti[$reparto][$gruppo]['defTariffa']=true;
        }
        else {
            $ttar=0;
            $this->presTotDiretti[$gruppo]['errorTariffa']=true;
            $this->presSubDiretti[$reparto][$gruppo]['errorTariffa']=true;
        }

        return $ttar;

    }

    function draw() {

        $divo1=new Divo('c2rBudgetRes','5%','97%',1);
        $divo1->setBk('#a89dce');

        $css=array(
            "margin-left"=>"10px",
            "margin-right"=>"5px",
            "font-weight"=>"bold",
            "text-align"=>"center"
        );

        $txt="";

        //serve farlo prima per il calcolo degli indiretti nei PARAMETRI
        ob_start();
            $this->drawIndiretti();
        $ind=ob_get_clean();

        ob_start();
            $this->drawParametri();
        $divo1->add_div('Parametri','black',0,'',ob_get_clean(),0,$css);

        ob_start();
            $this->drawCosti();
        $divo1->add_div('Costi Generali','black',0,'',ob_get_clean(),0,$css);

        ob_start();
            $this->drawDiretti();
        $divo1->add_div('Diretto','black',0,'',ob_get_clean(),0,$css);

        $divo1->add_div('Indiretto','black',0,'',$ind,0,$css);

        ob_start();
            $this->drawAnalisi();
        $divo1->add_div('Analisi','black',0,'',ob_get_clean(),1,$css);

        $divo1->build();
        $divo1->draw();

        unset($divo1);
    }

    function drawParametri() {

        echo '<div style="width:100%;height:100%;overflow:scroll;" >';

            echo '<table style="border-collapse:collapse;width: max-content;margin-bottom:20px;" >';

                echo '<colgroup>';
                    echo '<col span="1" style="width:200px;" />';
                    echo '<col span="1" style="width:400px;" />';
                echo '</colgroup>';

                echo '<thead>';
                    echo '<th style="border-bottom:2px solid black;" >Parametro</th>';
                    echo '<th style="text-align:left;border-bottom:2px solid black;" >Valore</th>';
                echo '</thead>';

                echo '<tbody style="text-align:left;" >';

                    echo '<tr>';
                        echo '<td style="font-weight:bold;text-align:center;" >Reparti</td>';
                        echo '<td>'.substr($this->param['reparti'],0,-1).'</td>';
                    echo '</tr>';

                    echo '<tr>';
                        echo '<td style="font-weight:bold;text-align:center;">Inizio</td>';
                        echo '<td>'.mainFunc::gab_todata($this->param['inizio']).'</td>';
                    echo '</tr>';

                    echo '<tr>';
                        echo '<td style="font-weight:bold;text-align:center;">Fine</td>';
                        echo '<td>'.mainFunc::gab_todata($this->param['fine']).'</td>';
                    echo '</tr>';

                    echo '<tr>';
                        echo '<td style="font-weight:bold;text-align:center;">Giorni</td>';
                        echo '<td>'.$this->param['totGG'].'</td>';
                    echo '</tr>';

                    echo '<tr>';
                        echo '<td style="font-weight:bold;text-align:center;">Diretti</td>';
                        echo '<td>'.$this->param['diretti'].'</td>';
                    echo '</tr>';

                    echo '<tr>';
                        echo '<td style="font-weight:bold;text-align:center;">Indiretti</td>';
                        echo '<td>'.$this->param['indiretti'].'</td>';
                    echo '</tr>';

                    echo '<tr>';
                        echo '<td style="font-weight:bold;text-align:center;">GG Equiv. (8 ore)</td>';
                        echo '<td>'.number_format($this->param['gg_eqiv'],1,'.','').'</td>';
                    echo '</tr>';

                    /*echo '<tr>';
                        echo '<td style="font-weight:bold;text-align:center;">Margine Mag.</td>';
                        echo '<td>'.number_format($this->ricambi['margineMag'],1,'.','').'%</td>';
                    echo '</tr>';*/

                echo '</tbody>';

            echo '</table>';

            //MARGINE MAGAZZINO
            echo '<table style="border-collapse:collapse;width: max-content;margin-bottom:20px;" >';
                echo '<colgroup>';
                    echo '<col span="1" style="width:170px;" />';
                    echo '<col span="'.(count($this->reparti)).'" style="width:100px;" />';
                echo '</colgroup>';

                echo '<thead>';
                    echo '<th style="border-bottom:2px solid black;" >Margine Magazzino</th>';
                    foreach ($this->reparti as $reparto=>$g) {
                        echo '<th style="border-bottom:2px solid black;">'.$reparto.'</th>';
                    }
                echo '</thead>';

                echo '<tbody>';
                    
                    echo '<tr style="text-align:center;">';
                        echo '<td>'.number_format($this->ricambi['margineMag']['XXX'],1,'.','').'%</td>';
                        foreach ($this->reparti as $rep=>$r) {
                            if (isset($this->ricambi['margineMag'][$rep])) {
                                echo '<td>'.number_format($this->ricambi['margineMag'][$rep],1,'.','').'%</td>';
                            }
                            else echo '<td></td>';
                        }
                     echo '</tr>';

                echo '</tbody>';

            echo '</table>';
            
            //MARGINE OBIETTIVO
            echo '<table style="border-collapse:collapse;width: max-content;margin-bottom:20px;" >';
                echo '<colgroup>';
                    echo '<col span="1" style="width:170px;" />';
                    echo '<col span="'.(count($this->reparti)).'" style="width:100px;" />';
                echo '</colgroup>';

                echo '<thead>';
                    echo '<th style="border-bottom:2px solid black;" >Marginalità</th>';
                    foreach ($this->reparti as $reparto=>$g) {
                        echo '<th style="border-bottom:2px solid black;">'.$reparto.'</th>';
                    }
                echo '</thead>';

                echo '<tbody>';
                    
                    echo '<tr style="text-align:center;">';
                        echo '<td></td>';
                        foreach ($this->reparti as $rep=>$r) {
                            if (isset($this->margine[$rep])) {
                                echo '<td>'.number_format($this->margine[$rep],1,'.','').'%</td>';
                            }
                            else echo '<td></td>';
                        }
                     echo '</tr>';

                echo '</tbody>';

            echo '</table>';
            
            //SCONTI RICAMBI
            echo '<table style="border-collapse:collapse;width: max-content;margin-bottom:20px;" >';
                echo '<colgroup>';
                    echo '<col span="1" style="width:170px;" />';
                    echo '<col span="'.(count($this->ricambi['marche'])+1).'" style="width:100px;" />';
                echo '</colgroup>';

                echo '<thead>';
                    echo '<th style="border-bottom:2px solid black;" >Sconto Ricambi</th>';
                    foreach ($this->ricambi['marche'] as $k=>$g) {
                        echo '<th style="border-bottom:2px solid black;">'.$g.'</th>';
                    }
                    echo '<th style="border-bottom:2px solid black;">Altro</th>';
                echo '</thead>';

                echo '<tbody>';

                    echo '<tr style="background-color:#f9e4c9;">';
                        echo '<td style="text-align:center;border-bottom:1px solid black;">Default</td>';
                        foreach ($this->ricambi['marche'] as $k=>$g) {
                            $tval=(isset($this->ricambi['sconto']['XXX'][$g]))?$this->ricambi['sconto']['XXX'][$g]['sconto']:0;
                            echo '<td style="text-align:center;border-bottom:1px solid black;">'.number_format($tval,2,'.','').'</td>';
                        }
                        echo '<td style="text-align:center;border-bottom:1px solid black;">'.number_format($this->defRicambi['default'],2,'.','').'</td>';
                    echo '</tr>';

                    foreach ($this->ricambi['sconto'] as $rep=>$r) {
                        if ($rep=='XXX') continue;
                        echo '<tr style="">';
                            echo '<td style="text-align:center;border-bottom:1px solid black;">'.$rep.'</td>';
                            foreach ($this->ricambi['marche'] as $k=>$g) {
                                $tval=(isset($r[$g]))?$r[$g]['sconto']:0;
                                echo '<td style="text-align:center;border-bottom:1px solid black;">'.number_format($tval,2,'.','').'</td>';
                            }
                             echo '<td></td>';
                        echo '</tr>';
                    }

                echo '</tbody>';

            echo '</table>';
            
            //echo '<div style="font-weight:bold;" >Costo Manodopera Diretta:</div>';

            echo '<table style="border-collapse:collapse;width: max-content;margin-bottom:20px;" >';
                echo '<colgroup>';
                    echo '<col span="1" style="width:170px;" />';
                    echo '<col span="'.(count($this->gruppi)+1).'" style="width:100px;" />';
                echo '</colgroup>';

                echo '<thead>';
                    echo '<th style="border-bottom:2px solid black;" >Man.Diretta</th>';
                    foreach ($this->gruppi as $gruppo=>$g) {
                        echo '<th style="border-bottom:2px solid black;">'.$g['tag'].'</th>';
                    }
                    echo '<th style="border-bottom:2px solid black;">Altro</th>';
                echo '</thead>';

                echo '<tbody>';

                    echo '<tr style="background-color:#f9e4c9;">';
                        echo '<td style="text-align:center;border-bottom:1px solid black;">Default</td>';
                        foreach ($this->gruppi as $gruppo=>$g) {
                            $tval=(isset($this->param['costoDefDir'][$gruppo]))?$this->param['costoDefDir'][$gruppo]:0;
                            echo '<td style="text-align:center;border-bottom:1px solid black;">'.number_format($tval,2,'.','').'</td>';
                        }
                        $tval=(isset($this->param['costoDefDir']['altro']))?$this->param['costoDefDir']['altro']:0;
                        echo '<td style="text-align:center;border-bottom:1px solid black;">'.number_format($tval,2,'.','').'</td>';
                    echo '</tr>';

                    foreach ($this->param['costoManDir'] as $rep=>$rr) {
                        if (!array_key_exists($rep,$this->reparti)) continue;

                        foreach ($rr as $kr=>$r) {

                            echo '<tr>';
                                echo '<td style="text-align:center;border-bottom:1px solid black;">';
                                    echo '<div>'.$rep.'</div>';
                                    echo '<div style="font-size:0.8em;">'.mainfunc::gab_todata($r['data_i']).' - '.mainfunc::gab_todata($r['data_f']).'</div>';
                                echo '</td>';
                                foreach ($this->gruppi as $gruppo=>$g) {
                                    $tval=(isset($r['mano'][$gruppo]))?$r['mano'][$gruppo]:0;
                                    echo '<td style="text-align:center;border-bottom:1px solid black;">'.number_format($tval,2,'.','').'</td>';
                                }
                                $tval=(isset($r['mano']['altro']))?$r['mano']['altro']:0;
                                echo '<td style="text-align:center;border-bottom:1px solid black;">'.number_format($tval,2,'.','').'</td>';
                            echo '</tr>';
                        }
                    }

                echo '</tbody>';

            echo '</table>';

            /////////////////////////////////////////////////////////////////////////////

            echo '<table style="border-collapse:collapse;width: max-content;margin-bottom:20px;" >';
                echo '<colgroup>';
                    echo '<col span="1" style="width:170px;" />';
                    echo '<col span="'.count($this->gruppiIndiretti).'" style="width:100px;" />';
                echo '</colgroup>';

                echo '<thead>';
                    echo '<th style="border-bottom:2px solid black;" >Man.Indiretta</th>';
                    foreach ($this->gruppiIndiretti as $gruppo=>$g) {
                        echo '<th style="border-bottom:2px solid black;">'.$g['tag'].'</th>';
                    }
                echo '</thead>';

                echo '<tbody>';

                    echo '<tr style="background-color:#f9e4c9;">';
                        echo '<td style="text-align:center;border-bottom:1px solid black;">Default</td>';
                        foreach ($this->gruppiIndiretti as $gruppo=>$g) {
                            $tval=(isset($this->param['costoDefInd'][$gruppo]))?$this->param['costoDefInd'][$gruppo]:0;
                            echo '<td style="text-align:center;border-bottom:1px solid black;">'.number_format($tval,2,'.','').'</td>';
                        }
                    echo '</tr>';

                    foreach ($this->param['costoManInd'] as $rep=>$rr) {
                        if (!array_key_exists($rep,$this->reparti)) continue;

                        foreach ($rr as $kr=>$r) {

                            echo '<tr>';
                                echo '<td style="text-align:center;border-bottom:1px solid black;">';
                                    echo '<div>'.$rep.'</div>';
                                    echo '<div style="font-size:0.8em;">'.mainfunc::gab_todata($r['data_i']).' - '.mainfunc::gab_todata($r['data_f']).'</div>';
                                echo '</td>';
                                foreach ($this->gruppiIndiretti as $gruppo=>$g) {
                                    $tval=(isset($r['mano'][$gruppo]))?$r['mano'][$gruppo]:0;
                                    echo '<td style="text-align:center;border-bottom:1px solid black;">'.number_format($tval,2,'.','').'</td>';
                                }
                            echo '</tr>';
                        }
                    }

                echo '</tbody>';

            echo '</table>';

        echo '</div>';
    }

    function drawDirLine($bcindex,$cc,$a,$IDcoll) {

        $bkc=array('#e0e3e3','#dbede7','#ffa2a2');

        echo '<tr style="font-size:0.9em;background-color:'.$bkc[$bcindex].';" >';
            echo '<td>'.iconv("ISO-8859-1", "UTF-8", $cc['cognome']).' '.iconv("ISO-8859-1", "UTF-8", $cc['nome']).'</td>';

            //inserire valori
            //############################################
            foreach ($this->gruppi as $gruppo=>$g) {

                $val=(isset($a[$gruppo]))?$a[$gruppo]['diretti']:0;
               
                echo '<td style="text-align:center;" >'.number_format($val,1,'.','').'</td>';
                //$presSubDiretti[$reparto][$gruppo]['diretti']+=$val;
            }
            //############################################

            $val="";

            foreach ($this->presDiretti[$IDcoll]['error'] as $gruppo=>$g) {
                $val.=$gruppo.'='.$g.' ';
            }

            echo '<td style="text-align:left;color:red;" >'.$val.'</td>';

        echo '</tr>';
    }

    function drawDiretti() {

        /*{
        "VWS": {
            "106": [
            {
                "IDDIP": "113",
                "IDMAT": "166",
                "ID_coll": 106,
                "ID_gruppo": 4,
                "cellulare": "",
                "cod_operaio": "32",
                "cognome": "Gjura",
                "concerto": "n.gjura",
                "data_f": "21001231",
                "data_i": "20170801",
                "des_gruppo": "Tecnico",
                "des_macrogruppo": "Tecnici Service",
                "des_macroreparto": "Service",
                "des_reparto": "Service Volkswagen",
                "flag_sostituzione": true,
                "gruppo": "TEC",
                "macrogruppo": "TES",
                "macroreparto": "S",
                "mail": "",
                "nome": "Nikel",
                "posizione": 1,
                "posizione_macrogruppo": 3,
                "rep_concerto": "PV",
                "reparto": "VWS",
                "sede": "PU",
                "tel_interno": ""
            }
            ],
        */

        echo '<div style="width:100%;height:100%;overflow:scroll;" >';

            echo '<table style="border-collapse:collapse;width: max-content;margin-bottom:20px;" >';

                echo '<colgroup>';
                    echo '<col span="1" style="width:200px;" />';
                    echo '<col span="'.count($this->gruppi).'" style="width:100px;" />';
                    echo '<col span="1" style="width:200px;" />';
                echo '</colgroup>';

                /*echo '<thead>';
                    echo '<tr>';
                        echo '<th style="border-bottom:2px solid black;" >Collaboratore</th>';
                        foreach ($this->gruppi as $gruppo=>$g) {
                            echo '<th style="border-bottom:2px solid black;">'.$g['tag'].'</th>';
                        }
                        echo '<th style="border-bottom:2px solid black;">Altro</th>';
                    echo '</tr>';
                echo '</thead>';*/

                echo '<tbody>';

                        $bcindex=0;

                        foreach ($this->diretti as $reparto=>$r) {

                            echo '<tr>';
                                echo '<th style="border-bottom:2px solid black;" >'.$reparto.'</th>';
                                foreach ($this->gruppi as $gruppo=>$g) {
                                    echo '<th style="border-bottom:2px solid black;">'.$g['tag'].'</th>';
                                }
                                echo '<th style="border-bottom:2px solid black;">Altro</th>';
                            echo '</tr>';

                            foreach ($r as $IDcoll=>$c) {
                                foreach ($c as $kc=>$cc) {
                                    
                                    $this->drawDirLine($bcindex,$cc,$this->presDiretti[$IDcoll]['gruppi'],$IDcoll);

                                    $bcindex=($bcindex==0)?1:0;
                                    
                                    //basta il primo solo per avere l'IDcoll
                                    break;
                                }
                            }

                            foreach (array('inprestito','prestato') as $kgr=>$vgr) {
                                $this->drawDirLine(2,array('cognome'=>$vgr,"nome"=>""),$this->presDiretti[$vgr][$reparto]['gruppi'],$vgr);
                            }

                            //subtotale
                            echo '<tr>';
                                echo '<td style="border-top:1px solid black;" >'.$reparto.'</td>';
                                foreach ($this->presSubDiretti[$reparto] as $gruppo=>$g) {
                                    echo '<td style="border-top:1px solid black;text-align:center;" >'.number_format($g['diretti'],1,'.','').'</td>';
                                }
                            echo '</tr>';
                            echo '<tr>';
                                echo '<td style="border-bottom:1px solid black;" >costo</td>';
                                foreach ($this->presSubDiretti[$reparto] as $gruppo=>$g) {
                                    echo '<td style="text-align:center;border-bottom:1px solid black;';
                                        if (isset($g['errorTariffa'])) echo 'color:red;';
                                        elseif (isset($g['defTariffa'])) echo 'color:#d09900;';
                                    echo '" >';
                                        echo '<div>'.number_format($g['costo'],0,',','.').'</div>';
                                        if (isset($g['errorTariffa'])) echo '<div style="font-size:0.8em;">Tariffe a 0</div>';
                                        elseif (isset($g['defTariffa'])) echo '<div style="font-size:0.8em;">Tariffe Default</div>';
                                    echo '</td>';
                                }
                            echo '</tr>';
                                
                        }

                        //TOTALI
                        echo '<tr>';
                            echo '<td style="font-weight:bold;border-top:1px dashed black;" >TOTALE</td>';
                            foreach ($this->presTotDiretti as $gruppo=>$g) {
                                echo '<td style="text-align:center;font-weight:bold;border-top:1px solid black;" >'.number_format($g['diretti'],1,'.','').'</td>';
                            }
                        echo '</tr>';

                        echo '<tr>';
                            echo '<td style="font-weight:bold;" >COSTO</td>';
                            foreach ($this->presTotDiretti as $gruppo=>$g) {
                                echo '<td style="text-align:center;font-weight:bold;';
                                    if (isset($g['errorTariffa'])) echo 'color:red;';
                                    elseif (isset($g['defTariffa'])) echo 'color:#d09900;';
                                echo '" >';
                                    echo '<div>'.number_format($g['costo'],0,',','.').'</div>';
                                    if (isset($g['errorTariffa'])) echo '<div style="font-size:0.8em;">Tariffe a 0</div>';
                                    elseif (isset($g['defTariffa'])) echo '<div style="font-size:0.8em;">Tariffe Default</div>';
                                echo '</td>';
                            }
                        echo '</tr>';

                echo '</tbody>';

            echo '</table>';

            //echo '<div>'.json_encode($this->param['prodLink']).'</div>';

        echo '</div>';

        //echo json_encode($this->presTotDiretti);

    }

    function drawIndiretti() {

        $bkc=array('#777777','#000000');

        echo '<div style="width:100%;height:100%;overflow:scroll;" >';

            echo '<table style="border-collapse:collapse;font-size:10pt;width: max-content;margin-bottom:20px;" >';

                echo '<colgroup>';
                    echo '<col span="1" style="width:150px;" />';
                    echo '<col span="1" style="width:50px;" />';
                    echo '<col span="'.(count($this->tpoReparti)+1).'" style="width:50px;" />';
                echo '</colgroup>';

                echo '<thead>';
                    /*echo '<tr>';
                        echo '<th style="border-bottom:2px solid black;" >Collaboratore</th>';
                        echo '<th style="border-bottom:2px solid black;" >Pres</th>';

                        foreach ($this->tpoReparti as $rep=>$r) {
                            echo '<th style="border-bottom:2px solid black;color:';
                                if (!array_key_exists($rep,$this->reparti)) echo $bkc[0];
                                else echo $bkc[1];
                            echo ';">'.$rep.'</th>';
                        }
                        echo '<th style="border-bottom:2px solid black;color:'.$bkc[0].';">Altro</th>';
                    echo '</tr>';*/
                echo '</thead>';

                echo '<tbody>';

                    foreach ($this->gruppiIndiretti as $gruppo=>$g) {

                        echo '<tr style="font-size:0.9em;font-weight:bold;text-align:center;background-color:#fdc3ae;">';
                            echo '<td style="text-align:left;">'.$g['tag'].'</td>';
                            //echo '<td colspan="'.(count($this->tpoReparti)+2).'"></td>';

                            echo '<td style="" >Pres</td>';

                            foreach ($this->tpoReparti as $rep=>$r) {
                                echo '<td style="color:';
                                    if (!array_key_exists($rep,$this->reparti)) echo $bkc[0];
                                    else echo $bkc[1];
                                echo ';">'.$rep.'</td>';
                            }
                            echo '<td style="color:'.$bkc[0].';">Altro</td>';

                            /*foreach ($g['reparti'] as $rep=>$r) {

                                if ($rep=='XXX') {
                                    echo '<td style="text-align:center;font-size:0.9em;color:'.$bkc[0].';">'.number_format($this->gruppiIndiretti[$gruppo]['XXX'],2,'.','').'</td>';
                                    continue;
                                }

                                echo '<td style="text-align:center;font-size:0.9em;color:';
                                    if (!array_key_exists($rep,$this->reparti)) echo $bkc[0];
                                    else echo $bkc[1];
                                echo ';">'.number_format($r,1,'.','').'</td>';

                                $this->gruppiIndiretti[$gruppo]['XXX']-=$r;
                            }*/

                            //echo '<td style="color:'.$bkc[0].';">'.$prc.'</td>';
                        echo '</tr>';

                        $tempTot=0;

                        if (!array_key_exists($gruppo,$this->indiretti)) continue;

                        foreach ($this->indiretti[$gruppo] as $k=>$c) {

                            echo '<tr style="border:1px solid #777777;">';

                                echo '<td style="text-align:left;">';
                                    echo '<div>'.$c['coll'].'</div>';
                                    echo '<div style="font-size:0.8em;">'.mainfunc::gab_todata($c['i']).' - '.mainfunc::gab_todata($c['f']).'</div>';
                                echo '</td>';
                                echo '<td style="text-align:center;" >'.number_format($c['pres'],2,'.','').'</td>';

                                //################################
                                $tempAltro=$c['pres'];
                                $tempTot+=$c['pres'];
                                //################################

                                //foreach ($g['reparti'] as $rep=>$r) {
                                foreach ($c['reparti'] as $rep=>$r) {

                                    if ($rep=='XXX') {
                                        //echo '<td style="text-align:center;font-size:0.9em;color:'.$bkc[0].';">'.number_format(($c['pres']*($this->gruppiIndiretti[$gruppo]['XXX']/100)),2,'.','').'</td>';
                                        echo '<td style="text-align:center;font-size:0.9em;color:'.( ($tempAltro>0.01 || $tempAltro<-0.01) ?'red':$bkc[0]).';';
                                            if ($tempAltro>0.01 || $tempAltro<-0.01) echo 'font-weight:bold;';
                                        echo '">'.number_format($tempAltro,2,'.','').'</td>';
                                        continue;
                                    }

                                    //$tval=$c['pres']*($r/100);
                                    $tval=$c['pres']*($r/100);

                                    echo '<td style="text-align:center;font-size:0.9em;color:';
                                        if (!array_key_exists($rep,$this->reparti)) echo $bkc[0];
                                        else echo $bkc[1];
                                    echo ';">'.number_format($tval,2,'.','').'</td>';

                                    if ($tval!=0 && array_key_exists($rep,$this->reparti)) {
                                        if (!array_key_exists($rep,$this->presTotIndiretti[$gruppo])) {
                                            $this->presTotIndiretti[$gruppo][$rep]=0;
                                        }
                                        if (!array_key_exists($rep,$this->presCosIndiretti[$gruppo])) {
                                            $this->presCosIndiretti[$gruppo][$rep]=0;
                                        }
                                        $this->presTotIndiretti[$gruppo][$rep]+=$tval;

                                        //#################################################
                                        //trova tariffa
                                        $this->presCosIndiretti[$gruppo][$rep]+=$this->getCostoInd($gruppo,$rep)*$tval;
                                        //#################################################

                                        $this->param['indiretti']+=$tval;
                                    }

                                    $tempAltro-=$tval;
                                }

                            echo '</tr>';
                        }

                        //subtotale
                        echo '<tr style="font-weight:bold;">';
                            echo '<td style="text-align:left;">Subtotale:</td>';
                            echo '<td style="text-align:center;">'.$tempTot.'</td>';

                            foreach ($g['reparti'] as $rep=>$r) {

                                if ($rep=='XXX' || !array_key_exists($rep,$this->presTotIndiretti[$gruppo])) {
                                    echo '<td></td>';
                                    continue;
                                }

                                echo '<td style="text-align:center;font-size:0.9em;color:'.$bkc[1].';">'.number_format($this->presTotIndiretti[$gruppo][$rep],2,'.','').'</td>';

                                if (!array_key_exists($rep,$this->totInd)) {
                                    $this->totInd[$rep]=array(
                                        "presenza"=>0,
                                        "costo"=>0
                                    );
                                }
                                $this->totInd[$rep]['presenza']+=$this->presTotIndiretti[$gruppo][$rep];
                            }
                        echo '</tr>';

                        echo '<tr style="font-weight:bold;">';
                            echo '<td style="text-align:left;">Costo:</td>';
                            echo '<td style="text-align:center;"></td>';

                            foreach ($g['reparti'] as $rep=>$r) {

                                if ($rep=='XXX' || !array_key_exists($rep,$this->presTotIndiretti[$gruppo])) {
                                    echo '<td></td>';
                                    continue;
                                }

                                echo '<td style="text-align:center;font-size:0.9em;color:'.$bkc[1].';">'.number_format($this->presCosIndiretti[$gruppo][$rep],0,',','.').'</td>';

                                $this->totInd[$rep]['costo']+=$this->presCosIndiretti[$gruppo][$rep];
                            }
                        echo '</tr>';

                        echo '<tr><td style="height:15px;"></td></tr>';

                    }         

                    //TOTALI
                    echo '<tr><td style="height:15px;"></td></tr>';

                    echo '<tr style="font-size:0.9em;font-weight:bold;text-align:center;background-color:#ffffff;">';
                        echo '<td style="text-align:left;border-top:2px solid black;">TOTALE</td>';

                        echo '<td style="border-top:2px solid black;" ></td>';

                        foreach ($this->tpoReparti as $rep=>$r) {
                            echo '<td style="color:';
                                if (!array_key_exists($rep,$this->reparti)) echo $bkc[0];
                                else echo $bkc[1];
                            echo ';border-top:2px solid black;">'.$rep.'</td>';
                        }
                        echo '<td style="color:'.$bkc[0].';border-top:2px solid black;"></td>';

                    echo '</tr>';

                    echo '<tr style="font-weight:bold;">';
                        echo '<td style="text-align:left;">Presenza:</td>';
                        echo '<td style="text-align:center;"></td>';

                        foreach ($g['reparti'] as $rep=>$r) {

                            if ($rep=='XXX' || !array_key_exists($rep,$this->totInd)) {
                                echo '<td></td>';
                                continue;
                            }

                            echo '<td style="text-align:center;font-size:0.9em;color:'.$bkc[1].';">'.number_format($this->totInd[$rep]['presenza'],2,'.','').'</td>';
                        }
                    echo '</tr>';

                    echo '<tr style="font-weight:bold;">';
                        echo '<td style="text-align:left;">Costo:</td>';
                        echo '<td style="text-align:center;"></td>';

                        foreach ($g['reparti'] as $rep=>$r) {

                            if ($rep=='XXX' || !array_key_exists($rep,$this->totInd)) {
                                echo '<td></td>';
                                continue;
                            }

                            echo '<td style="text-align:center;font-size:0.9em;color:'.$bkc[1].';">'.number_format($this->totInd[$rep]['costo'],0,',','.').'</td>';
                        }
                    echo '</tr>';

                echo '</tbody>';

            echo '</table>';

            //echo json_encode($this->log);

        echo '</div>';

    }

    function drawCosti() {

        $bkc=array('#777777','#000000');

        echo '<div style="width:100%;height:100%;overflow:scroll;" >';

            $cat='';
            foreach ($this->recCosti as $k=>$c) {

                if ($c['cat']!="cat") {

                    if ($cat!='') echo '</div>';

                    echo '<div style="position:relative;width:720px;border:1px solid black;padding:3px;margin-top:5px;margin-bottom:5px;" >';

                    $cat=$c['cat'];
                }

                echo '<div style="position:relative;">';
                    echo '<div style="position:relative;display:inline-block;vertical-align:top;width:140px;">'.$this->catCosti[$c['cat']]['tag'].'</div>';
                    echo '<div style="position:relative;display:inline-block;vertical-align:top;width:120px;">'.mainFunc::gab_todata($c['data_i']).'</div>';
                    echo '<div style="position:relative;display:inline-block;vertical-align:top;width:120px;">'.mainFunc::gab_todata($c['data_f']).'</div>';
                    echo '<div style="position:relative;display:inline-block;vertical-align:top;width:100px;text-align:center;font-size:0.9em;">Giorni: '.$c['rowGG'].'</div>';
                    echo '<div style="position:relative;display:inline-block;vertical-align:top;width:120px;text-align:right;">'.number_format($c['costo'],0,',','.').'</div>';
                    echo '<div style="position:relative;display:inline-block;vertical-align:top;width:120px;text-align:center;font-size:0.9em;">GG Calc: '.$c['intGG'].'</div>';
                echo '</div>';
            }
            if ($cat!='') echo '</div>';

            echo '<table style="text-align:center;border-collapse:collapse;font-size:10pt;width: max-content;margin-bottom:20px;" >';

                echo '<colgroup>';
                    echo '<col span="1" style="width:50px;" />';

                    foreach ($this->catCosti as $cat=>$c) {
                        echo '<col span="1" style="width:120px;" />';
                        echo '<col span="1" style="width:70px;" />';
                    }

                    echo '<col span="1" style="width:120px;" />';
                    
                echo '</colgroup>';

                echo '<thead>';
                    echo '<tr style="background-color:thistle;" >';
                        echo '<th style="text-align:left;">Reparto</th>';

                        foreach ($this->catCosti as $cat=>$c) {
                            echo '<th style="text-align:right;" >'.$c['tag'].'</th>';
                            echo '<th style="" ></th>';
                        }

                        echo '<th style="text-align:right;">Totale</th>';

                    echo '</tr>';
                echo '</thead>';

                echo '<tbody>';

                    echo '<tr style="" >';

                        echo '<td style="">Nominale</td>';

                        foreach ($this->catCosti as $cat=>$c) {
                            echo '<td style="text-align:right;" >'.number_format($c['nominale'],0,',','.').'</td>';
                            echo '<td style="" ></td>';
                        }

                        echo '<td></td>';

                    echo '</tr>';

                    foreach ($this->blocco as $rep=>$b) {

                        if ($rep=='XXX') {

                            echo '<tr style="" >';
                                echo '<td style="color:';
                                    if (!array_key_exists($rep,$this->reparti)) echo $bkc[0];
                                    else echo $bkc[1];
                                echo ';">Check</td>';

                                foreach ($this->catCosti as $cat=>$c) {

                                    $delta=$c['nominale']-$c['allocato'];

                                    echo '<td style="text-align:right;color:'.$bkc[0].';color:'.( ($delta>0.01 || $delta<-0.01)?'red':$bkc[0]).';';
                                        if ($delta>0.01 || $delta<-0.01) echo 'font-weight:bold;';
                                    echo '">'.number_format($delta,0,',','.').'</td>';

                                    echo '<td></td>';
                                }

                                echo '<td></td>';

                            echo '</tr>';

                            continue;
                        }

                        echo '<tr style="" >';

                            echo '<td style="color:';
                                if (!array_key_exists($rep,$this->reparti)) echo $bkc[0];
                                else echo $bkc[1];
                            echo ';">'.$rep.'</td>';

                            foreach ($this->catCosti as $cat=>$c) {

                                echo '<td style="text-align:right;color:';
                                    if (!array_key_exists($rep,$this->reparti)) echo $bkc[0];
                                    else echo $bkc[1];
                                echo ';">'.number_format($c['reparti'][$rep],0,',','.').'</td>';

                                echo '<td style="font-size:0.9em;color:';
                                    if (!array_key_exists($rep,$this->reparti)) echo $bkc[0];
                                    else echo $bkc[1];
                                //echo ';">'.number_format( ($c['prc'][$rep]),0,'.','').'%</td>';
                                echo ';">'.number_format( ($c['reparti'][$rep]/$c['nominale'])*100,0,'.','').'%</td>';
                            }

                            echo '<td style="text-align:right;color:';
                                    if (!array_key_exists($rep,$this->reparti)) echo $bkc[0];
                                    else echo $bkc[1].';font-weight:bold';
                                echo ';">'.number_format($this->totCosti[$rep],0,',','.').'</td>';

                        echo '</tr>';
                            
                    }           

                echo '</tbody>';

            echo '</table>';

        echo '</div>';

        //echo json_encode($this->log);
    }

    function drawAnalisi() {

        //$this->griglia['tot']=array();

        echo '<div style="width:100%;height:100%;overflow:scroll;" >';

            echo '<table style="border-collapse:collapse;font-size:10pt;width: max-content;margin-bottom:20px;margin-right:50px;" >';

                echo '<colgroup>';
                    echo '<col span="1" style="width:150px;" />';
                    echo '<col span="'.(count($this->reparti)).'" style="width:100px;" />';
                    echo '<col span="1" style="width:100px;" />';
                echo '</colgroup>';

                echo '<thead>';
                    echo '<tr>';
                        echo '<th style="border-bottom:2px solid black;" >Voce</th>';
                        
                        foreach ($this->reparti as $rep=>$r) {
                            echo '<th style="border-bottom:2px solid black;text-align:right;';
                                if (array_key_exists($rep,$this->prodLinkRif['common'])) echo 'color:violet;';
                            echo '">'.$rep.'</th>';

                            //$this->griglia[$rep]=array();
                        }

                        echo '<th style="border-bottom:2px solid black;text-align:right;" >Totale</th>';
                        
                    echo '</tr>';

                echo '</thead>';

                echo '<tbody>';
                    //COSTI FISSI

                    //##################
                    $compensation=array();
                    //##################

                    $this->griglia['tot']['fissi']=0;
                    echo '<tr style="background-color:#ff927e;" >';
                        echo '<th>COSTI FISSI</th>';
                        foreach ($this->reparti as $rep=>$r) {
                            echo '<th></th>';
                            $this->griglia[$rep]['fissi']=0;
                        }
                        echo '<th></th>';
                    echo '</tr>';

                    foreach ($this->fissi as $gruppo=>$f) {
                        echo '<tr style="';
                            if (isset($f['css'])) echo $f['css']; 
                        echo '">';
                            echo '<td>'.$f['tag'].'</td>';
                            $temp=0;
                            foreach ($this->reparti as $rep=>$r) {

                                if (array_key_exists($rep,$this->prodLinkRif['common']) && in_array($gruppo,$this->prodLinkRif['common'][$rep]['NOself'])) {
                                    $self=false;
                                }
                                else $self=true;

                                echo '<td style="text-align:right;';
                                    if (array_key_exists($rep,$this->prodLinkRif['common']) && in_array($gruppo,$this->prodLinkRif['common'][$rep]['NOsum'])) {
                                        echo 'color:red;';
                                    }
                                    else {
                                        if ($this->fissi[$gruppo]['tot']) {
                                            $temp+=$this->raggCosti[$rep][$gruppo]['costi'];
                                        }
                                    }
                                echo '" >';
                                    if ($self) {
                                        echo number_format($this->raggCosti[$rep][$gruppo]['costi'],0,',','.');
                                        $this->griglia[$rep]['fissi']+=$this->raggCosti[$rep][$gruppo]['costi'];
                                    }
                                    else echo '('.number_format($this->raggCosti[$rep][$gruppo]['costi'],0,',','.').')';
                                echo '</td>';

                                //$this->griglia[$rep]['fissi']+=$this->raggCosti[$rep][$gruppo]['costi'];

                                if (isset($this->fattLinkRif['oneriCompensation'][$rep])) {
                                    foreach ($this->fattLinkRif['oneriCompensation'][$rep] as $kcomp=>$comp) {
                                        if ($gruppo==$comp[0]) {
                                            $compensation[$rep][$comp[1]]=$this->raggCosti[$rep][$gruppo]['costi']*($comp[2]/100);
                                        }
                                    }
                                }
                            }
                            echo '<td style="text-align:right;" >'.number_format($temp,0,',','.').'</td>';
                            $this->griglia['tot']['fissi']+=$temp;
                        echo '</tr>';
                    }

                    echo '<tr>';
                        echo '<td>Personale Diretto</td>';
                        $temp=0;
                        foreach ($this->reparti as $rep=>$r) {
                            $costo=0;
                            if (isset($this->presSubDiretti[$rep])) {
                                foreach ($this->presSubDiretti[$rep] as $gruppo=>$g) {
                                    $costo+=$g['costo'];
                                }
                            }
                            echo '<td style="text-align:right;';
                                if (array_key_exists($rep,$this->prodLinkRif['common']) && $this->prodLinkRif['common'][$rep]['direttoNOsum']) {
                                    echo 'color:red;';
                                }
                                else {
                                    $temp+=$costo;
                                }
                            echo '" >'.number_format($costo,0,',','.').'</td>';
                            $this->griglia[$rep]['fissi']+=$costo;
                        }
                        echo '<td style="text-align:right;" >'.number_format($temp,0,',','.').'</td>';
                        $this->griglia['tot']['fissi']+=$temp;
                    echo '</tr>';

                    echo '<tr style="font-size:0.9em;color:violet;" >';
                   
                        echo '<td>Diretto non prestato</td>';

                        //definizione degli importi TOTALI da suddividere
                        $dnp=array();
                        $totDnp=array();
                        foreach ($this->reparti as $rep=>$r) {
                            $totDnp[$rep]=0;
                        }

                        foreach ($this->reparti as $rep=>$r) {
                            $costo=0;
                            if (array_key_exists($rep,$this->prodLinkRif['common']) && $this->prodLinkRif['common'][$rep]['presNOprestTOT']) {
                                $pres=0;
                                if (isset($this->param['prodLink']['proprio'][$rep])) {
                                    foreach ($this->param['prodLink']['proprio'][$rep]['presenza']['valori'] as $kval=>$val) {
                                        $segno=($val['op']=='M')?-1:1;
                                        $pres+=$val['valore']*$segno;
                                    }
                                }
                                $ter=$this->getTariffa('diretto',$this->param['fine'],$rep);
                                $costo=$pres*( isset($ter['tariffa'][$this->prodLinkRif['common'][$rep]['presNOprestTOT']])?$ter['tariffa'][$this->prodLinkRif['common'][$rep]['presNOprestTOT']]:0 );
                                $dnp[$rep]=array(
                                    "flag"=>true,
                                    "valore"=>$costo,
                                    "distr"=>0
                                );
                                $totDnp[$rep]+=$costo;
                                echo '<td style="text-align:right;">('.number_format($costo,0,',','.').')</td>';
                            }
                            else {
                                echo '<td></td>';
                                $dnp[$rep]=array(
                                    "flag"=>false,
                                    "valore"=>0,
                                    "distr"=>0
                                );
                            }
                        }
                        echo '<td></td>';
                    echo '</tr>';

                    foreach ($dnp as $rep=>$r) {
                        //calcolo importi per ogni reparto ricevente in base alla suddivisione dei materiali di consumo
                        //if (!$r['flag']) {
                            if (isset($this->catCosti['con']['reparti'][$rep])) {
                                $perc=$this->catCosti['con']['reparti'][$rep]/$this->catCosti['con']['nominale'];
                                foreach ($totDnp as $rr=>$rval) {
                                    if ($rr!=$rep) $dnp[$rep]['distr']+=$rval*$perc;
                                }
                            }
                        //}
                    }

                    echo '<tr style="font-size:0.9em;color:violet;" >';
                        
                        echo '<td>Distrib. Dir. no Prest.</td>';

                        //scrittura dei valori
                        $temp=0;
                        foreach ($this->reparti as $rep=>$r) {
                            echo '<td style="text-align:right;">'.number_format($dnp[$rep]['distr'],0,',','.').'</td>';
                            $this->griglia[$rep]['fissi']+=$dnp[$rep]['distr'];
                            $temp+=$dnp[$rep]['distr'];
                        }
                        echo '<td style="text-align:right;" >'.number_format($temp,0,',','.').'</td>';
                        $this->griglia['tot']['fissi']+=$temp;
                    echo '</tr>';

                    echo '<tr>';
                        echo '<td>Personale Indiretto</td>';
                        $temp=0;
                        foreach ($this->reparti as $rep=>$r) {
                            $costo=(isset($this->totInd[$rep]))?$this->totInd[$rep]['costo']:0;
                            echo '<td style="text-align:right;" >'.number_format($costo,0,',','.').'</td>';
                            $temp+=$costo;
                            $this->griglia[$rep]['fissi']+=$costo;
                        }
                        echo '<td style="text-align:right;" >'.number_format($temp,0,',','.').'</td>';
                        $this->griglia['tot']['fissi']+=$temp;
                    echo '</tr>';

                    //##################################################################################
                    echo '<tr style="border-top: 2px solid black;border-bottom: 2px solid black;">';
                        echo '<th style="text-align:left;height:40px;">Totale Fissi:</th>';
                        foreach ($this->reparti as $rep=>$r) {
                            echo '<th style="text-align:right;';
                                if (isset($this->prodLinkRif['common'][$rep])) {
                                    echo 'color:violet;" >('.number_format($this->griglia[$rep]['fissi'],0,'','.').')';
                                }
                                else {
                                    echo '" >'.number_format($this->griglia[$rep]['fissi'],0,'','.');
                                }
                            echo '</th>';
                        }
                        echo '<th style="text-align:right;" >'.number_format($this->griglia['tot']['fissi'],0,'','.').'</th>';
                    echo '</tr>';
                    
                    //##################################################################################

                    //ONERI
                    $this->griglia['tot']['oneri']=0;
                    echo '<tr style="background-color:#96cd82;" >';
                        echo '<th>ONERI VARI</th>';
                        foreach ($this->reparti as $rep=>$r) {
                            echo '<th></th>';
                            $this->griglia[$rep]['oneri']=0;
                        }
                        echo '<th></th>';
                    echo '</tr>';
                    
                    //#####################################
                    //eventualmente da riprendere se serve
                    $esclusioni=array();
                    //#####################################

                    /*$temp=0;
                    //TERZI
                    echo '<tr>';
                        echo '<td>Terzi</td>';
                        foreach ($this->reparti as $rep=>$r) {
                            if (isset($this->param['fattLink'][$rep]['budget'])) {
                                $val=$this->param['fattLink'][$rep]['budget']['totale']['std']['terzi']['netto'];
                            }
                            else {
                                echo '<td></td>';
                                continue;
                            }
                            echo '<td style="text-align:right;">'.number_format($val,0,'','.').'</td>';
                            if (!isset($esclusioni[$rep]) || !isset($esclusioni[$rep][$this->param['fattLink'][$rep]['budget']['totale']['std']['terzi']['classe']])) $esclusioni[$rep][$this->param['fattLink'][$rep]['budget']['totale']['std']['terzi']['classe']]=0;
                            $esclusioni[$rep][$this->param['fattLink'][$rep]['budget']['totale']['std']['terzi']['classe']]+=$val;
                            $temp+=$val;
                        }
                        echo '<td style="text-align:right;">'.number_format($temp,0,'','.').'</td>';
                    echo '</tr>';*/
                    
                    foreach (c2rBlocco::$fattClassi as $k=>$c) {

                        if ($k=='tot') continue;

                        $temp=0;
                        echo '<tr>';
                            echo '<td>'.$c.'</td>';
                            foreach ($this->reparti as $rep=>$r) {

                                $var=false;

                                if (isset($this->fattLinkRif['oneriVar'][$k])) {
                                    if (isset($this->fattLinkRif['oneriVar'][$k][$rep])) {
                                        $perc=$this->fattLinkRif['oneriVar'][$k][$rep];
                                        $var=true;
                                    }
                                    elseif (isset($this->fattLinkRif['oneriVar'][$k]['XXX'])) {
                                        $perc=$this->fattLinkRif['oneriVar'][$k]['XXX'];
                                        $var=true;
                                    }
                                    else {
                                        $perc=100;
                                    }
                                }
                                else $perc=100;

                                $val=(isset($this->param['fattLink'][$rep][$k])?$this->param['fattLink'][$rep][$k]['totale']['std']['var']['netto']:0)*($perc/100);
                                //if (isset($esclusioni[$rep][$k])) $val-=$esclusioni[$rep][$k];
                                
                                echo '<td style="text-align:right;';
                                    if ($var) echo 'color:orange;';
                                echo '">';
                                    echo '<div>'.number_format($val,0,'','.').'</div>';
                                    if ($var) echo '<div style="font-size:0.8em;">'.$perc.'%</div>';
                                echo '</td>';
                                $temp+=$val;
                                $this->griglia[$rep]['oneri']+=$val;
                            }
                            echo '<td style="text-align:right;">'.number_format($temp,0,'','.').'</td>';
                            $this->griglia['tot']['oneri']+=$temp;
                        echo '</tr>';

                        foreach ($compensation as $comprep=>$comp) {
                            if (array_key_exists($k,$comp)) {
                                $temp=0;
                                echo '<tr style="color:violet;font-size:0.9em;" >';
                                    echo '<td>Compensazione '.$c.'</td>';
                                    foreach ($this->reparti as $rep=>$r) {
                                        if (isset($compensation[$rep][$k])) {
                                            echo '<td style="text-align:right;">'.number_format($compensation[$rep][$k],0,'','.').'</td>';
                                            $this->griglia[$rep]['oneri']+=$compensation[$rep][$k];
                                            //$this->griglia['tot']['oneri']+=$compensation[$rep][$k];
                                            $temp+=$compensation[$rep][$k];
                                        }
                                        else echo '<td></td>';
                                    }
                                    echo '<td style="text-align:right;">'.number_format($temp,0,'','.').'</td>';
                                    $this->griglia['tot']['oneri']+=$temp;
                                echo '</tr>';
                                break;
                            }
                        }

                        /*foreach ($this->fattLinkRif['oneriExclusion'] as $comprep=>$comp) {
                            if (array_key_exists($k,$comp) || array_key_exists('XXX',$comp)) {
                                $temp=0;
                                echo '<tr style="color:violet;font-size:0.9em;" >';
                                    echo '<td>Esclusione '.$c.'</td>';




                            foreach ($this->fattLinkRif['oneriExclusion'][$rep] as $kcomp=>$comp) {
                                if ($gruppo==$comp[0]) {
                                    $exclusion[$rep][$comp[1]]=$this->raggCosti[$rep][$gruppo]['costi']*($comp[2]/100);
                                }
                            }
                        })*/
                    }

                    //HANDLING
                    $temp=0;
                    echo '<tr>';
                        echo '<td>Handling</td>';
                        foreach ($this->reparti as $rep=>$r) {
                            $val=(isset($this->param['fattLink'][$rep]['budget'])?$this->param['fattLink'][$rep]['budget']['totale']['std']['handling']['netto']:0);
                            //if (isset($esclusioni[$rep]['handling'])) $val-=$esclusioni[$rep]['handling'];
                            echo '<td style="text-align:right;">'.number_format($val,0,'','.').'</td>';
                            $temp+=$val;
                            $this->griglia[$rep]['oneri']+=$val;
                        }
                        echo '<td style="text-align:right;">'.number_format($temp,0,'','.').'</td>';
                        $this->griglia['tot']['oneri']+=$temp;
                    echo '</tr>';

                    //ONERI EXTRA derivanti dal calcolo
                    $temp=0;
                    foreach ($this->oneriExtra as $onere=>$o) {
                        echo '<tr style="color:violet;font-size:0.9em;" >';
                            echo '<td>'.$o.'</td>';
                            foreach ($this->reparti as $rep=>$r) {
                                if (isset($this->param[$rep]['budget']['totale']['std'][$onere])) {
                                    $val=$this->param[$rep]['budget']['totale']['std'][$onere]['netto'];
                                    echo '<td style="text-align:right;';
                                        if (!$this->param[$rep]['budget']['totale']['std'][$onere]['perc']) echo 'color:orange;';
                                    echo '">';
                                        echo '<div>'.number_format($val,0,'','.').'</div>';
                                        if (!$this->param[$rep]['budget']['totale']['std'][$onere]['perc']) {
                                            echo '<div>parziale</div>';
                                        }
                                    echo '</td>';
                                    $this->griglia[$rep]['oneri']+=$val;
                                    if (isset($this->prodLinkRif['common'][$rep]) && !$this->prodLinkRif['common'][$rep]['oneriNOsum']) {
                                        if (isset($this->param[$rep]['budget']['totale']['std']['oneri']['reparti'])) {
                                            foreach ($this->param[$rep]['budget']['totale']['std']['oneri']['reparti'] as $krep=>$p) {
                                                if (array_key_exists($krep,$this->reparti)) {
                                                    $temp+=$p;
                                                }
                                            }
                                        }   
                                    }
                                }
                                else echo '<td></td>';
                            }
                            echo '<td style="text-align:right;">'.number_format($temp,0,'','.').'</td>';
                            $this->griglia['tot']['oneri']+=$temp;

                        echo '</tr>';
                    }

                    //################################################################
                    echo '<tr style="border-top: 2px solid black;">';
                        echo '<th style="text-align:left;height:40px;">Totale Oneri:</th>';
                        //foreach ($this->griglia as $rep=>$r) {
                            //if ($rep=='tot') continue;
                        foreach ($this->reparti as $rep=>$r) {
                            //echo '<th style="text-align:right;" >'.number_format($this->griglia[$rep]['oneri'],0,'','.').'</th>';
                            echo '<th style="text-align:right;';
                                if (isset($this->prodLinkRif['common'][$rep])) {
                                    echo 'color:violet;" >('.number_format($this->griglia[$rep]['oneri'],0,'','.').')';
                                }
                                else {
                                    echo '" >'.number_format($this->griglia[$rep]['oneri'],0,'','.');
                                }
                            echo '</th>';
                        }
                        echo '<th style="text-align:right;" >'.number_format($this->griglia['tot']['oneri'],0,'','.').'</th>';
                    echo '</tr>';

                    echo '<tr style="background-color:#ffdf61;" >';
                        echo '<th style="text-align:left;height:20px;">1° Step:</th>';
                        //foreach ($this->griglia as $rep=>$r) {
                            //if ($rep=='tot') continue;
                        foreach ($this->reparti as $rep=>$r) {
                            echo '<th style="text-align:right;" >'.number_format($this->griglia[$rep]['oneri']-$this->griglia[$rep]['fissi'],0,'','.').'</th>';
                        }
                        echo '<th style="text-align:right;" >'.number_format($this->griglia['tot']['oneri']-$this->griglia['tot']['fissi'],0,'','.').'</th>';
                    echo '</tr>';
                    echo '<tr style="border-bottom: 2px solid black;background-color:#ffdf61;" >';
                        echo '<th style="text-align:left;height:20px;"></th>';
                        //foreach ($this->griglia as $rep=>$r) {
                            //if ($rep=='tot') continue;
                        foreach ($this->reparti as $rep=>$r) {
                            echo '<th style="text-align:right;" >'.$rep.'</th>';
                        }
                        echo '<th style="text-align:right;" >Totale</th>';
                    echo '</tr>';
                    //################################################################

                    echo '<tr style="background-color:#96cd82;" >';
                        echo '<th>Fatturato</th>';
                        foreach ($this->reparti as $rep=>$r) {
                            echo '<th></th>';
                            //$this->griglia[$rep]['man']=0;
                            //$this->griglia[$rep]['ric']=0;
                        }
                        echo '<th></th>';
                    echo '</tr>';

                    //MANODOPERA
                    //$this->griglia['tot']['man']=0;
                    echo '<tr>';
                        echo '<td>Manodopera Netta</td>';
                        $temp=0;
                        foreach ($this->reparti as $rep=>$r) {
                            $val=0;
                            if (isset($this->param['fattLink'][$rep])) {
                                foreach ($this->param['fattLink'][$rep] as $classe=>$c) {
                                    if ($classe=='budget') continue;
                                    $tv=(isset($c['totale']['std']['man']['netto'])?$c['totale']['std']['man']['netto']:0);
                                    $val+=$tv;
                                    $this->griglia[$rep]['man']['valore']+=$tv;
                                }
                            }
                            echo '<td style="text-align:right;">'.number_format($val,0,'','.').'</td>';
                            $temp+=$val;
                        }
                        echo '<td style="text-align:right;">'.number_format($temp,0,'','.').'</td>';
                        $this->griglia['tot']['man']+=$temp;
                    echo '</tr>';

                    //MANODOPERA EXTRA derivante dal calcolo
                    foreach ($this->manExtra as $onere=>$o) {
                        echo '<tr style="color:violet;font-size:0.9em;" >';
                            echo '<td>'.$o.'</td>';
                            foreach ($this->reparti as $rep=>$r) {
                                if (isset($this->param[$rep]['budget']['totale']['std'][$onere])) {
                                    $val=$this->param[$rep]['budget']['totale']['std'][$onere]['netto'];
                                    echo '<td style="text-align:right;">'.number_format($val,0,'','.').'</td>';
                                    $this->griglia[$rep]['man']['extra']+=$val;
                                }
                                else echo '<td></td>';
                            }
                            echo '<td style="text-align:right;"></td>';
                        echo '</tr>';
                    }

                    //RICAMBI
                    //$this->griglia['tot']['ric']=0;
                    echo '<tr>';
                        echo '<td>Ricambi Netti</td>';
                        //$temp=0;
                        foreach ($this->reparti as $rep=>$r) {
                            /*$val=0;
                            if (isset($this->param['fattLink'][$rep])) {
                                foreach ($this->param['fattLink'][$rep] as $classe=>$c) {
                                    if ($classe=='budget') continue;
                                    $tv=(isset($c['totale']['std']['ric']['netto'])?$c['totale']['std']['ric']['netto']:0);
                                    $val+=$tv;
                                }
                            }
                            //esclusione dell'HANDLING
                            $val-=(isset($this->param['fattLink'][$rep]['budget'])?$this->param['fattLink'][$rep]['budget']['totale']['std']['handling']['netto']:0);
                            $this->griglia[$rep]['ric']+=$val;*/
                            //echo '<td style="text-align:right;">'.number_format($val,0,'','.').'</td>';
                            echo '<td style="text-align:right;">'.number_format($this->griglia[$rep]['ric']['valore'],0,'','.').'</td>';
                            //$temp+=$val;
                        }
                        //echo '<td style="text-align:right;">'.number_format($temp,0,'','.').'</td>';
                        echo '<td style="text-align:right;">'.number_format($this->griglia['tot']['ric'],0,'','.').'</td>';
                        //$this->griglia['tot']['ric']+=$temp;
                    echo '</tr>';

                    //RICAMBI EXTRA derivante dal calcolo
                    foreach ($this->ricExtra as $onere=>$o) {
                        echo '<tr style="color:violet;font-size:0.9em;" >';
                            echo '<td>'.$o.'</td>';
                            foreach ($this->reparti as $rep=>$r) {
                                if (isset($this->param[$rep]['budget']['totale']['std'][$onere])) {
                                    $val=$this->param[$rep]['budget']['totale']['std'][$onere]['netto'];
                                    echo '<td style="text-align:right;">'.number_format($val,0,'','.').'</td>';
                                    $this->griglia[$rep]['ric']['extra']+=$val;
                                }
                                else echo '<td></td>';
                            }
                            echo '<td style="text-align:right;"></td>';
                        echo '</tr>';
                    }

                    //indice ricambi
                    echo '<tr style="color:#777777;font-size:0.9em;" >';
                        echo '<td>Indice ricambi</td>';
                        foreach ($this->reparti as $rep=>$r) {
                            $man=$this->griglia[$rep]['man']['valore']+$this->griglia[$rep]['man']['extra'];
                            $ric=$this->griglia[$rep]['ric']['valore']+$this->griglia[$rep]['ric']['extra'];
                            echo '<td style="text-align:right;">'.number_format(($man==0?0:$ric/$man),2,',','').'</td>';  
                        }
                        echo '<td style="text-align:right;"></td>';
                    echo '</tr>';
                    
                    //totale fatturato
                    $this->griglia['tot']['fatt']=0;
                    echo '<tr style="border-top: 2px solid black;border-bottom: 2px solid black;">';
                        echo '<th style="text-align:left;height:40px;">Totale Fatturato:</th>';
                        foreach ($this->reparti as $rep=>$r) {
                            $this->griglia[$rep]['fatt']=$this->griglia[$rep]['man']['valore']+$this->griglia[$rep]['man']['extra']+$this->griglia[$rep]['ric']['valore']+$this->griglia[$rep]['ric']['extra'];
                            $this->griglia['tot']['fatt']+=$this->griglia[$rep]['man']['valore']+$this->griglia[$rep]['ric']['valore'];
                            //echo '<th style="text-align:right;" >'.number_format($this->griglia[$rep]['fatt'],0,'','.').'</th>';
                            echo '<th style="text-align:right;';
                                if (isset($this->prodLinkRif['common'][$rep])) {
                                    echo 'color:violet;" >('.number_format($this->griglia[$rep]['fatt'],0,'','.').')';
                                }
                                else {
                                    echo '" >'.number_format($this->griglia[$rep]['fatt'],0,'','.');
                                }
                            echo '</th>';
                        }
                        echo '<th style="text-align:right;" >'.number_format($this->griglia['tot']['fatt'],0,'','.').'</th>';
                    echo '</tr>';

                    //SCONTO MEDIO
                    echo '<tr style="color:#777777;font-size:0.9em;border-top:1px solid black;border-bottom:1px solid black;" >';
                        echo '<td>Sconto Medio</td>';

                        foreach ($this->reparti as $rep=>$r) {

                            /*$this->defRicambi['reparti'][$rep]=array(
                                "valore"=>$this->defRicambi['default'],
                                "default"=>true
                            );

                            $tot=(isset($this->param['fattTotaloneLink'][$rep]['totale']['std']['ric']['netto'])?$this->param['fattTotaloneLink'][$rep]['totale']['std']['ric']['netto']:0);*/
                            //if ($tot==0) {
                            if (!isset($this->defRicambi['reparti'][$rep]['tot']) || ($this->defRicambi['reparti'][$rep]['tot']==0 && !isset($this->defRicambi['reparti'][$rep]['ricalcolo'])) ) {
                                echo '<td style="text-align:right;"></td>';
                                continue;
                            }
                            //"sconto"=>'{"XXX":{"V":{"sconto":42,"fatt":"rv"},"X":{"sconto":67,fatt:""}}'

                            /*$arr=false;
                            if (isset($this->ricambi['sconto'][$rep])) $arr=$rep;
                            elseif (isset($this->ricambi['sconto']['XXX'])) $arr='XXX';

                            $x=array();
                            $prc=0;
                            $fr=1;

                            //$this->log[]=$this->ricambi;

                            if ($arr!==false) {
                                
                                foreach ($this->ricambi['sconto'][$arr] as $tipo=>$t) {

                                    //$this->log[]=$t;
                                
                                    if ($tipo=='X') {
                                        $x=$t;
                                        continue;
                                    }
        
                                    if (isset($this->param['fattTotaloneLink'][$rep]['totale']['ext'][$t['fatt']]['valore'])) {
                                        $rv=$this->param['fattTotaloneLink'][$rep]['totale']['ext'][$t['fatt']]['valore'];
                                        //totale del tipo di ricambi sul totale
                                        $parte=$rv/$tot;
                                        $prc+=$t['sconto']*$parte;
                                        $fr-=$parte;
                                        $this->defRicambi['reparti'][$rep]['default']=false;
                                    }
                                }
                            }

                            $prc+=(isset($x['sconto'])?$x['sconto']:$this->defRicambi['default'])*$fr;
                            $this->defRicambi['reparti'][$rep]['valore']=$prc;*/
                            
                            echo '<td style="text-align:right;">';
                                echo '<div style="height:15px;">'.number_format($this->defRicambi['reparti'][$rep]['valore'],1,',','').'%</div>';
                                echo '<div style="height:15px;">';
                                    if (isset($this->defRicambi['reparti'][$rep]['ricalcolo'])) 
                                        echo '('.number_format($this->defRicambi['reparti'][$rep]['ricalcolo'],1,',','').'%)';
                                echo '</div>';
                            echo '</td>';
                        }
                        echo '<td style="text-align:right;"></td>';
                        
                    echo '</tr>';

                    //LISTINO
                    echo '<tr style="color:#777777;font-size:0.9em;" >';
                        echo '<td>Listino</td>';

                        foreach ($this->reparti as $rep=>$r) {
                            echo '<td style="text-align:right;">'.number_format($this->griglia[$rep]['lis']['valore'],0,'','.').'</td>';
                        }
                        echo '<td style="text-align:right;"></td>';

                    echo '</tr>';

                    //COSTI VARIABILI
                    $this->griglia['tot']['variabili']=0;
                    echo '<tr style="color:hotpink;" >';
                        echo '<td>Costi Variabili</td>';

                        foreach ($this->reparti as $rep=>$r) {

                            if ($this->griglia[$rep]['lis']['valore']==0) {
                                echo '<td style="text-align:right;"></td>';
                                continue;
                            }

                            //$sc=(isset($this->defRicambi['reparti'][$rep]['ricalcolo'])?$this->defRicambi['reparti'][$rep]['ricalcolo']:$this->defRicambi['reparti'][$rep]['valore']);
                            $sc=(isset($this->defRicambi['reparti'][$rep]['valore'])?$this->defRicambi['reparti'][$rep]['valore']:0);

                            $this->griglia[$rep]['lis']['costo']=$this->griglia[$rep]['lis']['valore']*(1-($sc/100));

                            echo '<td style="text-align:right;">'.number_format($this->griglia[$rep]['lis']['costo']*-1,0,'','.').'</td>';
                            $this->griglia['tot']['variabili']+=$this->griglia[$rep]['lis']['costo'];
                        }
                        echo '<td style="text-align:right;">'.number_format($this->griglia['tot']['variabili']*-1,0,'','.').'</td>';

                    echo '</tr>';

                    //LISTINO EXTRA derivante dal calcolo
                    foreach ($this->lisExtra as $onere=>$o) {
                        echo '<tr style="color:#777777;font-size:0.9em;" >';
                            echo '<td>'.$o.'</td>';
                            foreach ($this->reparti as $rep=>$r) {
                                if (isset($this->param[$rep]['budget']['totale']['std'][$onere])) {
                                    $val=$this->param[$rep]['budget']['totale']['std'][$onere]['lordo'];
                                    echo '<td style="text-align:right;">'.number_format($val,0,'','.').'</td>';
                                    $this->griglia[$rep]['lis']['extra']+=$val;
                                }
                                else echo '<td></td>';
                            }
                            echo '<td style="text-align:right;"></td>';
                        echo '</tr>';
                    }

                    //COSTI VARIABILI ACQUISITI
                    foreach ($this->lisExtra as $onere=>$o) {
                        echo '<tr style="color:#a256a2;font-size:0.9em;" >';
                            echo '<td>Costi var. acq.</td>';
                            foreach ($this->reparti as $rep=>$r) {
                                if (isset($this->param[$rep]['budget']['totale']['std'][$onere])) {
                                    $sc=(isset($this->defRicambi['reparti'][$rep]['ricalcolo'])?$this->defRicambi['reparti'][$rep]['ricalcolo']:0);
                                    $val=$this->param[$rep]['budget']['totale']['std'][$onere]['lordo']*(1-($sc/100));
                                    echo '<td style="text-align:right;">'.number_format($val*-1,0,'','.').'</td>';
                                    $this->griglia[$rep]['lis']['excos']+=$val;
                                }
                                else echo '<td></td>';
                            }
                            echo '<td style="text-align:right;"></td>';
                        echo '</tr>';
                    }

                    //COSTI GESTIONE RICAMBI
                    echo '<tr style="color:#a256a2;font-size:0.9em;" >';
                        echo '<td>Gestione ricambi</td>';
                        $param=0;
                        foreach ($this->reparti as $rep=>$r) {
                            //$ric=$this->griglia[$rep]['ric']['valore']+$this->griglia[$rep]['ric']['extra'];
                            $ric=$this->griglia[$rep]['ric']['valore'];
                            $prc=(isset($this->ricambi['margineMag'][$rep])?$this->ricambi['margineMag'][$rep]:$this->ricambi['margineMag']['XXX']);
                            $val=$ric*$prc/100;
                            echo '<td style="text-align:right;">'.number_format($val*-1,0,'','.').'</td>';
                            $this->griglia[$rep]['ric']['gesmag']+=$val;
                            $param+=$val;
                        }
                        echo '<td style="text-align:right;">'.number_format($param*-1,0,'','.').'</td>';
                        $this->griglia['tot']['variabili']+=$param;
                    echo '</tr>';
                    
                    //PAREGGIO
                    echo '<tr style="background-color:#aecce7;border-top:2px solid black;" >';
                        echo '<th style="text-align:left;height:20px;">Pareggio:</th>';
                        $this->griglia['tot']['pareggio']=$this->griglia['tot']['fissi']+$this->griglia['tot']['variabili'];
                        foreach ($this->reparti as $rep=>$r) {
                            $this->griglia[$rep]['pareggio']=$this->griglia[$rep]['fissi']+$this->griglia[$rep]['ric']['gesmag']+$this->griglia[$rep]['lis']['costo']+$this->griglia[$rep]['lis']['excos'];
                            //$this->griglia['tot']['pareggio']+=$this->griglia[$rep]['ric']['gesmag']+$this->griglia[$rep]['lis']['costo'];
                            echo '<th style="text-align:right;" >'.number_format($this->griglia[$rep]['pareggio']*-1,0,'','.').'</th>';
                        }
                        echo '<th style="text-align:right;" >'.number_format($this->griglia['tot']['pareggio']*-1,0,'','.').'</th>';
                    echo '</tr>';

                    //RISULTATO
                    $this->griglia['tot']['risultato']=0;
                    echo '<tr style="background-color:#aecce7;" >';
                        echo '<th style="text-align:left;height:20px;">Risultato:</th>';
                        //if (!isset($this->prodLinkRif['common'][$rep])) {
                            $this->griglia['tot']['risultato']=$this->griglia['tot']['oneri']+$this->griglia['tot']['fatt'];
                        //}
                        foreach ($this->reparti as $rep=>$r) {
                            $this->griglia[$rep]['risultato']=$this->griglia[$rep]['oneri']+$this->griglia[$rep]['fatt'];
                            echo '<th style="text-align:right;" >'.number_format($this->griglia[$rep]['risultato'],0,'','.').'</th>';
                        }
                        echo '<th style="text-align:right;" >'.number_format($this->griglia['tot']['risultato'],0,'','.').'</th>';
                    echo '</tr>';
                    
                    //Margine
                    //echo '<tr style="background-color:#ffdf61;" >';
                    echo '<tr>';
                        echo '<th style="text-align:left;height:20px;">Margine:</th>';
                        foreach ($this->reparti as $rep=>$r) {
                            //echo '<th style="text-align:right;" >'.number_format($this->griglia[$rep]['risultato']-$this->griglia[$rep]['pareggio'],0,'','.').'</th>';
                            echo '<th style="text-align:right;';
                                if (isset($this->prodLinkRif['common'][$rep])) {
                                    echo 'color:violet;" >('.number_format($this->griglia[$rep]['risultato']-$this->griglia[$rep]['pareggio'],0,'','.').')';
                                }
                                else {
                                    echo '" >'.number_format($this->griglia[$rep]['risultato']-$this->griglia[$rep]['pareggio'],0,'','.');
                                }
                            echo '</th>';
                        }
                        echo '<th style="text-align:right;" >'.number_format($this->griglia['tot']['risultato']-$this->griglia['tot']['pareggio'],0,'','.').'</th>';
                    echo '</tr>';

                    //echo '<tr style="border-bottom: 2px solid black;background-color:#ffdf61;" >';
                    echo '<tr style="border-bottom: 2px solid black;" >';
                        echo '<th style="text-align:left;height:20px;"></th>';
                        foreach ($this->reparti as $rep=>$r) {
                            echo '<th style="text-align:right;" >'.$rep.'</th>';
                        }
                        echo '<th style="text-align:right;" >Totale</th>';
                    echo '</tr>';

                    //OBIETTIVO:
                    echo '<tr style="background-color:#96cd82;" >';
                        echo '<th style="text-align:left;height:20px;">OBIETTIVO:</th>';
                        $temp=0;
                        foreach ($this->reparti as $rep=>$r) {
                            $mar=isset($this->margine[$rep])?$this->margine[$rep]:0;
                            $this->griglia[$rep]['obiettivo']=$this->griglia[$rep]['pareggio']*(1+($mar/100));
                            echo '<th style="text-align:right;" >';
                                echo '<div>('.number_format($mar,0,'','.').'%)</div>';
                                echo '<div>'.number_format($this->griglia[$rep]['obiettivo'],0,'','.').'</div>';
                            echo '</th>';
                            $percTot=(isset($this->prodLinkRif['common'][$rep]))?$this->prodLinkRif['common'][$rep]['obiettivo']:1;
                            $temp+=$this->griglia[$rep]['obiettivo']*$percTot;
                        }
                        echo '<th style="text-align:right;" >'.number_format($temp,0,'','.').'</th>';
                    echo '</tr>';

                    echo '<tr style="border-bottom: 2px solid black;">';
                        echo '<th style="text-align:left;height:40px;">Raggiungimento:</th>';
                        foreach ($this->reparti as $rep=>$r) {
                            $prc=($this->griglia[$rep]['obiettivo']==0)?0:$this->griglia[$rep]['risultato']/$this->griglia[$rep]['obiettivo'];
                            echo '<th style="text-align:right;" >'.number_format($prc*100,2,',','').'%</th>';
                        }
                        $percTot=($temp==0)?0:$this->griglia['tot']['risultato']/$temp;
                        echo '<th style="text-align:right;" >'.number_format($percTot*100,2,',','').'%</th>';
                    echo '</tr>';

                echo '</tbody>';

            echo '</table>';
            
            //echo '<div>'.json_encode($this->param).'</div>';
            //echo '<div>'.json_encode($this->param['fattTotLink']).'</div>';

        echo '</div>';
    }

}

?>