<?php

class c2rBlocco {

    //SCHEMA: esempio classe MEC
    /*
        totalone: obj TOTALE
        griglia: {
            "mec": {
                "totale": obj TOTALE
                "tipi": {
                    .......... array TIPI
                }
            }
        }
    */

    //è la marca del veicolo per la quale viene definito il totale e che determina alcuni indici
    protected $marca="";
    protected $param=array();

    //oggetto che tiene il totale del BLOCCO
    protected $totalone;

    protected $indefiniti=array();

    //le classi identificano una suddivisione in macroaree
    //i tipi identificano l'ente pagante
    //ed i sottogruppi (gruppi) relativi ai tipi identificano specifici raggruppamenti
    //a loro volta anche i totali hanno dei sottogruppi per identificare specifici voci di incasso o spesa (Nota accr. - costo lt ...) 

    protected $griglia=array();

    protected $classi=array(
        "mec"=>"Meccanica",
        "car"=>"Carrozzeria",
        "gom"=>"Gommista",
        "ser"=>"Servizi",
        "rev"=>"Revisioni",
        "ter"=>"Terzi",
        "ind"=>"Indefinito",
        "tot"=>"Totali per tipo"
    );

    public static $fattClassi=array(
        "mec"=>"Meccanica",
        "car"=>"Carrozzeria",
        "gom"=>"Gommista",
        "ser"=>"Servizi",
        "rev"=>"Revisioni",
        "ter"=>"Terzi",
        "ind"=>"Indefinito",
        "tot"=>"Totali per tipo"
    );

    //###########
    //ATTENZIONE: Il totale del tipo NON è il totale dei gruppi ma è il totale di tutto ciò che non ha un gruppo specifico
    //quindi i gruppi NON sopno un di cui del tipo ma sono da sommare al totale del tipo
    protected $tipi=array(
        "pag"=>array(
            "tag"=>"Pagamento",
            "operatore"=>"P",
            "flag"=>false,
            "totale"=>null,
            "gruppi"=>array(
                "pack"=>array(
                    "tag"=>"Pacchetti",
                    "flag"=>false,
                    "totale"=>null
                ),
                "corr"=>array(
                    "tag"=>"Correntezza",
                    "flag"=>false,
                    "totale"=>null
                ),
                "ccrr"=>array(
                    "tag"=>"Costi Corr.",
                    "flag"=>false,
                    "totale"=>null
                ),
                "terzi"=>array(
                    "tag"=>"Terzi",
                    "flag"=>false,
                    "totale"=>null
                ),
                "nol"=>array(
                    "tag"=>"Noleggio",
                    "flag"=>false,
                    "totale"=>null
                ),
                "lav"=>array(
                    "tag"=>"Lavaggio",
                    "flag"=>false,
                    "totale"=>null
                ),
                "rev"=>array(
                    "tag"=>"Revisione",
                    "flag"=>false,
                    "totale"=>null
                ),
                "sercar"=>array(
                    "tag"=>"Serv.Carroz.",
                    "flag"=>false,
                    "totale"=>null
                ),
                "met"=>array(
                    "tag"=>"Metano",
                    "flag"=>false,
                    "totale"=>null
                ),
                "rec"=>array(
                    "tag"=>"Recupero",
                    "flag"=>false,
                    "totale"=>null
                ),
                "sco"=>array(
                    "tag"=>"Sconti",
                    "flag"=>false,
                    "totale"=>null
                )
            )
        ),
        "nac"=>array(
            "tag"=>"storno",
            "operatore"=>"M",
            "flag"=>false,
            "totale"=>null,
            "gruppi"=>array(
                "pack"=>array(
                    "tag"=>"Pacchetti",
                    "flag"=>false,
                    "totale"=>null
                ),
                "corr"=>array(
                    "tag"=>"Correntezza",
                    "flag"=>false,
                    "totale"=>null
                ),
                "ccrr"=>array(
                    "tag"=>"Costi Corr.",
                    "flag"=>false,
                    "totale"=>null
                ),
                "terzi"=>array(
                    "tag"=>"Terzi",
                    "flag"=>false,
                    "totale"=>null
                ),
                "nol"=>array(
                    "tag"=>"Noleggio",
                    "flag"=>false,
                    "totale"=>null
                ),
                "lav"=>array(
                    "tag"=>"Lavaggio",
                    "flag"=>false,
                    "totale"=>null
                ),
                "gom"=>array(
                    "tag"=>"Gomme",
                    "flag"=>false,
                    "totale"=>null
                ),
                "rev"=>array(
                    "tag"=>"Revisione",
                    "flag"=>false,
                    "totale"=>null
                ),
                "sercar"=>array(
                    "tag"=>"Serv.Carroz.",
                    "flag"=>false,
                    "totale"=>null
                ),
                "rec"=>array(
                    "tag"=>"Recupero",
                    "flag"=>false,
                    "totale"=>null
                )
            )
        ),
        "gar"=>array(
            "tag"=>"Garanzia",
            "operatore"=>"P",
            "flag"=>false,
            "totale"=>null,
            "gruppi"=>array(
                "hand"=>array(
                    "tag"=>"Handling",
                    "flag"=>false,
                    "totale"=>null
                ),
                "corr"=>array(
                    "tag"=>"Correntezza",
                    "flag"=>false,
                    "totale"=>null
                ),
                "terzi"=>array(
                    "tag"=>"Terzi",
                    "flag"=>false,
                    "totale"=>null
                )
            )
        ),

        "int"=>array(
            "tag"=>"Interno",
            "operatore"=>"P",
            "flag"=>false,
            "totale"=>null,
            "gruppi"=>array(
                "app"=>array(
                    "tag"=>"Approntamento",
                    "flag"=>false,
                    "totale"=>null
                ),
                "rip"=>array(
                    "tag"=>"Ripristino",
                    "flag"=>false,
                    "totale"=>null
                ),
                "terzi"=>array(
                    "tag"=>"Terzi",
                    "flag"=>false,
                    "totale"=>null
                )
            )
        ),
        "ind"=>array(
            "tag"=>"Indefinito",
            "operatore"=>"P",
            "flag"=>false,
            "totale"=>null,
            "gruppi"=>array(
            )
        ),
        "gzi"=>array(
            "tag"=>"GarInt",
            "operatore"=>"P",
            "flag"=>false,
            "totale"=>null,
            "gruppi"=>array(
            )
        )
    );

    function __construct($marca,$param) {

        $this->marca=$marca;
        $this->param=$param;

        $this->totalone=new c2rTotale($marca);
        if (isset($param['flag_inc'])) $this->totalone->setExt('inc',$param['flag_inc']);
        if (isset($param['flag_rv'])) $this->totalone->setExt('rv',$param['flag_rv']);
        if (isset($param['flag_rp'])) $this->totalone->setExt('rp',$param['flag_rp']);

        foreach ($this->classi as $kc=>$c) {
            $this->griglia[$kc]=array(
                "totale"=>new c2rTotale($marca),
                "tipi"=>$this->tipi
            );

            if (isset($param['flag_inc'])) $this->griglia[$kc]['totale']->setExt('inc',$param['flag_inc']);
            if (isset($param['flag_rv'])) $this->griglia[$kc]['totale']->setExt('rv',$param['flag_rv']);
            if (isset($param['flag_rp'])) $this->griglia[$kc]['totale']->setExt('rp',$param['flag_rp']);

            foreach ($this->griglia[$kc]['tipi'] as $tipo=>$t) {
                $this->griglia[$kc]['tipi'][$tipo]['totale']=new c2rTotale($marca);
                if (isset($param['flag_inc'])) $this->griglia[$kc]['tipi'][$tipo]['totale']->setExt('inc',$param['flag_inc']);
                if (isset($param['flag_rv'])) $this->griglia[$kc]['tipi'][$tipo]['totale']->setExt('rv',$param['flag_rv']);
                if (isset($param['flag_rp'])) $this->griglia[$kc]['tipi'][$tipo]['totale']->setExt('rp',$param['flag_rp']);

                foreach ($t['gruppi'] as $gruppo=>$g) {
                    $this->griglia[$kc]['tipi'][$tipo]['gruppi'][$gruppo]['totale']=new c2rTotale($marca);
                    if (isset($param['flag_inc'])) $this->griglia[$kc]['tipi'][$tipo]['gruppi'][$gruppo]['totale']->setExt('inc',$param['flag_inc']);
                    if (isset($param['flag_rv'])) $this->griglia[$kc]['tipi'][$tipo]['gruppi'][$gruppo]['totale']->setExt('rv',$param['flag_rv']);
                    if (isset($param['flag_rp'])) $this->griglia[$kc]['tipi'][$tipo]['gruppi'][$gruppo]['totale']->setExt('rp',$param['flag_rp']);
                }
            }
        }

    }

    function getGrid() {
        return $this->griglia;
    }

    function getTotArr() {
        $res=array();
        foreach ($this->griglia as $classe=>$c) {
            $res[$classe]=$c['totale']->getTot();
            $res[$classe]['tag']=$this->classi[$classe];
        }
        return $res;
    }

    function getTotTipi() {
        $res=array();
        foreach ($this->griglia['tot']['tipi'] as $tipo=>$t) {
            $res[$tipo]=$t['totale']->getTot();
        }
        return $res;
    }

    function getTotaloneArr() {
        return $this->totalone->getTot();
    }

    function loadIndefiniti($a) {
        $this->indefiniti=$a;
    }

    function feed($arr) {

        if ($arr['c2rGruppo']=='corr') $this->evalCorr($arr);

        else {

            //se il tipo non è mappato setta su indefinito
            if (!array_key_exists($arr['c2rTipo'],$this->tipi)) $arr['c2rTipo']='ind';

            //se il gruppo non è mappato setta su ""
            if (!array_key_exists($arr['c2rGruppo'],$this->tipi[$arr['c2rTipo']]['gruppi'])) $arr['c2rGruppo']='';

            $this->load($arr);
        }

        //$this->griglia[$arr['c2rClasse']]['tipi'][$arr['c2rTipo']]['flag']=true;
        //if ($arr['c2rGruppo']!='') $this->griglia[$arr['c2rClasse']]['tipi'][$arr['c2rTipo']]['gruppi'][$arr['c2rGruppo']]['flag']=true;

    }

    function evalCorr($arr) {

        //la correntezza carica sia pagamento che garanzia
        //essendo il gruppo "corr" gli importi verrano calcolati di conseguenza
        $arr['c2rTipo']='pag';
        $this->load($arr);

        if ($arr['ind_tipo_riga']=='R') {
            $arr['c2rGruppo']='ccrr';
            $this->load($arr);
        }

        $arr['c2rTipo']='gar';
        $arr['c2rGruppo']='corr';
        $this->load($arr);
    }

    function load($arr) {

        //if (is_null($this->griglia[$arr['c2rClasse']][$arr['c2rTipo']]['totale'])) $this->griglia[$arr['c2rClasse']][$arr['c2rTipo']]['totale']=$this->newTot($arr);

        $arr=$this->buildRecord($arr);

        //se il gruppo non è specificato alimenta TOTALE generico
        if ($arr['c2rGruppo']=='') {
            $this->griglia[$arr['c2rClasse']]['tipi'][$arr['c2rTipo']]['totale']->feed($arr);
        }
        else {
            //if (is_null($this->griglia[$arr['c2rClasse']][$arr['c2rTipo']]['gruppi'][$arr['c2rGruppo']]['totale'])) $this->griglia[$arr['c2rClasse']][$arr['c2rTipo']]['gruppi'][$arr['c2rGruppo']]['totale']=$this->newTot($arr);
            $this->griglia[$arr['c2rClasse']]['tipi'][$arr['c2rTipo']]['gruppi'][$arr['c2rGruppo']]['totale']->feed($arr);
            $this->griglia[$arr['c2rClasse']]['tipi'][$arr['c2rTipo']]['gruppi'][$arr['c2rGruppo']]['flag']=true;
        }

        $this->griglia[$arr['c2rClasse']]['tipi'][$arr['c2rTipo']]['flag']=true;

        $this->griglia[$arr['c2rClasse']]['totale']->feed($arr);

        $this->totalone->feed($arr);

        //alimentazione totale per tipo
        $this->griglia['tot']['tipi'][$arr['c2rTipo']]['flag']=true;
        //$this->griglia['tot']['totale']->feed($arr);
        $this->griglia['tot']['tipi'][$arr['c2rTipo']]['totale']->feed($arr);
        
    }

    function buildRecord($arr) {

        //analizza il record e cambia classe,tipo,gruppo...... in base alle caratteristiche
        //PROGRAMMATO SECONDO LE ATTUALI REGOLE SULLA GARANZIA/CORRENTEZZA DI VGI
        //##########################
        //definire una tabella per definire le regole in quanto potrebbero cambiare nel tempo o essere diverse da marchio a marchio
        //###########################

        if ($arr['c2rTipo']=='gar' && $arr['ind_tipo_riga']=='R') {

            //$arr['costo']=0;

            if ($arr['marca_veicolo']=='P') $sch=0.03;
            else $sch=0.15;

            if ($arr['c2rGruppo']!='corr') {

                $arr['importo']=$arr['importo']*$sch;
                
                $arr['c2rGruppo']='hand';
                $arr['c2rPass']=0;
                $arr['c2rCont']=0;
            }
            else {
                //##########################
                //definire il costo in base allo sconto stock
                //$arr['costo']=0;
                $arr['importo']=(($arr['listino']*$arr['qta'])-$arr['importo'])*$sch;
                $arr['listino']=0;
                $arr['c2rPass']=0;
                $arr['c2rCont']=0;
                //##########################
            }
        }

        else if  ($arr['c2rTipo']=='pag' && $arr['ind_tipo_riga']=='R'){
            
            if ($arr['c2rGruppo']=='ccrr') {
                if (!isset($arr['sc_hep'])|| $arr['sc_hep']==0) $arr['sc_hep']=20;
                $arr['importo']=-1*$arr['importo']*($arr['sc_hep']/100);
                $arr['listino']=0;
                $arr['c2rPass']=0;
                $arr['c2rCont']=0;
            }
        }

        //non dovrebbe essere più possibile perché ccrr viene eseguiro solo per righe R
        else if ($arr['c2rTipo']=='pag' && $arr['ind_tipo_riga']=='M' && $arr['c2rGruppo']=='ccrr') {
            $arr['listino']=0;
            $arr['importo']=0;
            $arr['c2rPass']=0;
            $arr['c2rCont']=0;
        }

        else if ($arr['c2rTipo']=='gar' && $arr['ind_tipo_riga']=='M' && $arr['c2rGruppo']=='corr') {
            $arr['importo']=$arr['listino']-$arr['importo'];
            //if ($arr['listino']==0) $arr['qta']=0;
            //else $arr['qta']=$arr['qta']*($arr['importo']/$arr['listino']);
            $arr['listino']=0;
            $arr['c2rPass']=0;
            $arr['c2rCont']=0;
        }

        else if ($arr['c2rTipo']=='gzi') {
            $arr['importo']=0;
        }

        $arr['operatore']=$this->tipi[$arr['c2rTipo']]['operatore'];

        return $arr;
    }

    /*function newTot($arr) {

        //##########################
        //19.05.21 essendo i totali inizializzati in __construct questa funzione non viene mai chiamata
        //##########################

        $obj=new c2rTotale();

        //attiva EXT ed INDICI in base alla situazione
        $temp=array('A','C','N','S','V');
        if (!in_array($arr['marca_veicolo'],$temp)) $obj->setExt('r19',false);

        return $obj;

    }*/

    function sum($g) {

        //return;

        //somma alla griglia attuale una griglia fornita dall'esterno
        foreach ($g as $classe=>$c) {

            /*$this->griglia[$classe]['totale']->sum($c['totale']->getTot());

            //###################################################
            $this->totalone->sum($c['totale']->getTot());
            //###################################################
            */

            foreach ($c['tipi'] as $tipo=>$t) {

                if(!$t['flag']) continue;

                /*if (!isset($this->griglia[$classe]['tipi'][$tipo])) {
                    $this->griglia[$classe]['tipi'][$tipo]=$this->tipi[$tipo];
                    $this->griglia[$classe]['tipi'][$tipo]['totale']=new c2rTotale($this->marca);
                        foreach ($this->tipi[$tipo]['gruppi'] as $gruppo=>$g) {
                            $this->griglia[$classe]['tipi'][$tipo]['gruppi'][$gruppo]['totale']=new c2rTotale($this->marca);
                        }
                }*/

                foreach ($t['gruppi'] as $gruppo=>$r) {

                    if (!$r['flag']) continue;

                    $this->griglia[$classe]['tipi'][$tipo]['gruppi'][$gruppo]['totale']->sum($r['totale']->getTot());
                    //$this->griglia[$classe]['tipi'][$tipo]['gruppi'][$gruppo]['totale']->calcolaIndici();
                    $this->griglia[$classe]['tipi'][$tipo]['gruppi'][$gruppo]['flag']=true;

                }

                $this->griglia[$classe]['tipi'][$tipo]['totale']->sum($t['totale']->getTot());
                //$this->griglia[$classe]['tipi'][$tipo]['totale']->calcolaIndici();
                $this->griglia[$classe]['tipi'][$tipo]['flag']=true;
            }

            $this->griglia[$classe]['totale']->sum($c['totale']->getTot());

            //###################################################
            $this->totalone->sum($c['totale']->getTot());
            //###################################################

        }

        //$this->griglia[$classe]['totale']->calcolaIndici();
        $this->totalone->calcolaIndici();
    }

    function getTotaloneVal($tipo,$indice,$valore) {

        //getTotVal($indice,$valore)
        //indice= man - ric - var
        //valore= lordo - netto - costo

        if ($tipo=='ext') {
            return $this->totalone->getValExt($indice);
        }
        elseif ($tipo=='int') {
            return $this->totalone->getTotVal($indice,$valore);
        }
        else return false;
    }

    function getTotTipo($classe,$tipo,$indice,$valore) {

        //classe= mec - car - gom ....
        //tipo=  pag - gar - int
        //indice= man - ric - var
        //valore= lordo - netto - costo

        return (isset($this->griglia[$classe]['tipi'][$tipo]['totale']))?$this->griglia[$classe]['tipi'][$tipo]['totale']->getTotVal($indice,$valore):0;
    }

    function getTotGruppo($classe,$tipo,$gruppo,$indice,$valore) {

        //classe= mec - car - gom ....
        //tipo=  pag - gar - int
        //indice= man - ric - var
        //valore= lordo - netto - costo

        //echo '<div>'.json_encode($this->griglia[$classe]['tipi'][$tipo]).'</div>';

        return (isset($this->griglia[$classe]['tipi'][$tipo]['gruppi'][$gruppo]))?$this->griglia[$classe]['tipi'][$tipo]['gruppi'][$gruppo]['totale']->getTotVal($indice,$valore):0;
    }

    function draw() {

        //$h=new c2rTotale($this->marca);

        echo '<div style="width:100%;height:100%;overflow:scroll;">';

            echo '<div style="height:9%;width:max-content;border: 3px solid transparent;box-sizing: border-box;padding:3px;">';
                //$h->drawHead();
                $this->totalone->countExtInd();
                $this->totalone->drawHead();
            echo '</div>';

            echo '<div style="height:91%;width:max-content;overflow:scroll;">';

                echo '<div style="margin-top:10px;margin-bottom:10px;margin-right:40px;border: 3px solid #aaaaaa;box-sizing: border-box;padding:3px;">';

                    echo '<div style="font-weight:bold;position:relative;margin-top:4px;margin-bottom:2px;color:red;">'.mainFunc::gab_todata($this->param['inizio']).' - '.mainFunc::gab_todata($this->param['fine']).' ( '.$this->param['txtReparti'].' )</div>';

                    foreach ($this->griglia as $classe=>$c) {

                        /*echo '<div>';
                            echo json_encode($c);
                        echo '</div>';*/

                        echo '<div style="font-weight:bold;position:relative;margin-top:2px;margin-bottom:2px;">'.$this->classi[$classe].'</div>';

                        echo '<div style="width:max-content;border-top:1px solid black;border-bottom:1px solid black;margin-right:15px;">';

                            foreach ($c['tipi'] as $tipo=>$t) {

                                if (!$t['flag']) continue;

                                $temptot=new c2rTotale('');

                                echo '<div>';
                                    if (isset($t['totale'])) {
                                        $t['totale']->draw($t['tag'],'tipo','P');
                                        $temptot->sum($t['totale']->getTot(),'P');
                                    }
                                echo '</div>';

                                foreach ($t['gruppi'] as $gruppo=>$g) {

                                    if (!$g['flag']) continue;

                                    echo '<div>';
                                        if (isset($g['totale'])) {
                                            $g['totale']->draw($g['tag'],'gruppo','P');
                                            $temptot->sum($g['totale']->getTot(),'P');
                                        }
                                    echo '</div>';
                                }

                                if ($classe!='tot') {
                                    echo '<div class="c2rTableLabel" >';
                                        echo '<div>';
                                            $temptot->drawSubTot($t['tag'],'P');
                                        echo '</div>';
                                    echo '</div>';
                                }
                            }

                            unset($tempTot);

                        echo '</div>';
                        
                        if ($classe!='tot') {
                            echo '<div class="c2rTableLabel" style="margin-bottom:10px;" >';
                                $c['totale']->drawTotClasse($this->classi[$classe]);  
                            echo '</div>';
                        }

                        if ($classe=='ind' && count($this->indefiniti)>0) {

                            $blk=new BlockList('c2rind',0);
                            $blk->setHead('Dettaglio indefiniti:');

                            ob_start();

                                $rif=0;

                                foreach ($this->indefiniti as $k=>$i) {

                                    //{\"rif\":22875,\"cod_movimento\":\"OOA\",\"d_fatt\":\"20230124\",\"ind_tipo_riga\":\"R\",\"tipo_riga\":\"N\",\"cod_tipo_articolo\":\"X\",\"cod_articolo\":\"MOCSSC009\",\"flag_prelevato\":\"S\",\"cod_categoria_vendita\":\"\",\"famiglia\":\"\",\"cod_operazione\":\"\",\"cod_varie\":\"\",\"descrizione\":\"CATENE NEVE T9 PLASTIC GRIP SYSTEM MODULA\",\"qta\":\"1.0000000\",\"unita\":\"\",\"listino\":\"330.0000000\",\"prc_sconto\":\"20.000\",\"importo\":\"264.000000\",\"costo\":\"165.000000\",\"ivato\":\"322.080000\",\"cod_iva\":\"22\",\"prc_iva\":\"22.00\",\"lam\":3,\"riga\":9,\"acarico\":\"ACU\",\"cg\":\"\",\"ind_chiuso\":\"N\",\"cod_officina\":\"PU\",\"cod_accettatore\":\"I13\",\"des_accettatore\":\"Ghiandoni Marco\",\"cod_utente\":\"m.ghiandoni\",\"km\":117061,\"id_veicolo\":69673,\"marca_veicolo\":\"A\",\"modello\":\"4GF0GY\",\"d_consegna\":\"20160429\",\"id_cliente\":30002,\"intest_ragsoc\":\"AUGUSTO GABELLINI RICONDIZIONAMENTO\",\"dms\":\"infinity\",\"c2rTipo\":\"ind\",\"c2rGruppo\":\"\",\"c2rClasse\":\"ind\"}"

                                    if ($i['rif']==$rif) continue;

                                    echo '<div style="position:relative;" >';
                                        echo '<div style="position:relative;display:inline-block;vertical-align:top;width:5%;text-align:center;">('.substr($i['dms'],0,1).')</div>';
                                        echo '<div style="position:relative;display:inline-block;vertical-align:top;width:10%;text-align:left;">'.$i['rif'].'</div>';
                                        echo '<div style="position:relative;display:inline-block;vertical-align:top;width:10%;text-align:left;">'.$i['cod_movimento'].'</div>';
                                        echo '<div style="position:relative;display:inline-block;vertical-align:top;width:10%;text-align:left;">'.$i['acarico'].'</div>';
                                        echo '<div style="position:relative;display:inline-block;vertical-align:top;width:10%;text-align:left;">'.$i['cg'].'</div>';
                                    echo '</div>';

                                    $rif=$i['rif'];

                                }

                            $blk->setBody(ob_get_clean());

                            echo $blk->draw();

                            //echo json_encode($this->indefiniti);
                        }
                    }

                    echo '<div class="c2rTableLabel" style="margin-top:10px;margin-bottom:10px;" >';        
                        $this->totalone->drawTotBlocco();  
                    echo '</div>';

                echo '</div>';

            echo '</div>';

        echo '</div>';
    }

}

?>