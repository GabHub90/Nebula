<?php
require_once ($_SERVER['DOCUMENT_ROOT']."/nebula/core/magazzino/mag_func.php");
require_once ($_SERVER['DOCUMENT_ROOT']."/nebula/core/divo/divo.php");
require_once ($_SERVER['DOCUMENT_ROOT']."/nebula/core/excalibur/excalibur.php");

class c2rGiacenza_M {

    protected $param=array();

    protected $analisi=array(
        "inizio"=>false,
        "fine"=>false,
        "delta"=>false,
        "comuni"=>false
    );

    protected $deposito=array(
        "totale"=>false,
        "precodici"=>array(),
        "negativi"=>false,
        "obsoleto"=>false,
        "giacenza"=>false
    );

    protected $blocco=array(
        "totale"=>false,
        "gm"=>array(),
        "tots"=>array(),
        "delta"=>false
    );

    protected $totale=array(
        "pos"=>0,
        "qta"=>0,
        "listino"=>0,
        "costo"=>0,
        "obsoleto"=>0,
        "errore"=>0
    );

    protected $tots=array(
        "gm19"=>false
    );

    protected $listino=array();

    protected $tempComuni=array(
        "arr"=>array(),
        "chk"=>"",
        "rif"=>""
    );

    protected $magFunc;
    protected $galileo;

    protected $log=array();

    function __construct($param,$galileo) {

        $this->galileo=$galileo;

        $param['inizio']=str_replace("-","",$param['inizio']);
        $param['fine']=str_replace("-","",$param['fine']);

        $param['txtReparti']=substr(str_replace("'","",$param['reparti']),0,-1);
        $param['txtReparti']=str_replace(","," - ",$param['txtReparti']);

        //{"reparti":"'FCM','VGM',","marche":"","inizio":"20240601","fine":"20240630","prodTipo":"","obso":"36","default":{"mrep":"M","tipo":"standard","totali":"false","collab":"false","repcol":"false","responsabile":"true","collaboratore":"1"},"txtReparti":"FCM - VGM"}
        $this->param=$param;

        $this->magFunc=new nebulaMagFunc($galileo);
    }

    function abilitaTot($index) {
        if (isset($this->tots[$index])) $this->tots[$index]=true;
    }

    function getAnalisi($tipo) {
        if ($tipo=='') return $this->analisi;
        else return $analisi[$tipo];
    }

    function getVal($ana,$dep,$pre,$gm,$obj) {
        //getVal("fine","","","","obsoleto");
        if ($dep=="") return $this->analisi[$ana]['totale'][$obj];
        else if ($gm!="") {
            return $this->analisi[$ana]['depositi'][$dep]['precodici'][$pre]['gm'][$gm][$obj];
        }
        else if ($pre!="") {
            return $this->analisi[$ana]['depositi'][$dep]['precodici'][$pre]['totale'][$obj];
        }
        else {
            return $this->analisi[$ana]['depositi'][$dep]['totale'][$obj];
        }
    }

    function getLines() {

        //echo '<div>'.json_encode(explode(',',$this->param['reparti'])).'</div>';

        $this->analisi['fine']=array(
            "tag"=>"Fine",
            "data"=>$this->param['fine'],
            "depositi"=>array(),
            "totale"=>$this->totale,
            "comune"=>false
        );

        if ($this->param['inizio']!=$this->param['fine']) {
            $this->analisi['inizio']=array(
                "tag"=>"Inizio",
                "data"=>$this->param['inizio'],
                "depositi"=>array(),
                "totale"=>$this->totale,
                "comune"=>false
            );
            $this->analisi['delta']=array(
                "tag"=>"Delta",
                "data"=>"",
                "depositi"=>array(),
                "totale"=>$this->totale
            );
        }

        foreach (explode(',',$this->param['reparti']) as $k=>$rep) {
            if ($rep=='') continue;
            $this->magFunc->setWH(str_replace("'","",$rep));
            $this->magFunc->buildWH($this->param['inizio'],$this->param['fine']);

            $i=$this->param['inizio'];
            $f=$this->param['fine'];

            foreach ($this->magFunc->getExportMap() as $km=>$m) {

                if ($f<$m['inizio']) break;
                if ($i>$m['fine']) continue;

                if ($i<$m['inizio']) $i=$m['inizio'];

                if ($f>$m['fine']) $f=$f['fine'];

                //foreach (explode(',',$this->magFunc->getDepDB($m['dms'],str_replace("'","",$rep))) as $k2=>$dep) {

                    //$this->getLines2($i,$f,$m);
                    $end=(int)substr($f,0,4);
                    $indice=(int)substr($i,0,4);

                    while ($indice<=$end) {

                        $a=array(
                            "dep"=>$this->magFunc->getDepDB($m['dms'],str_replace("'","",$rep)),
                            "inizio"=>($indice==(int)substr($i,0,4))?$i:$indice."0101",
                            "fine"=>($indice==(int)substr($f,0,4))?$f:$indice."1231",
                            "rifCosto"=>date('Y')
                        );

                        if ($a["inizio"]==$this->param['inizio'] || $a["fine"]==$this->param['fine']) {

                            $x=($a["inizio"]==$this->param['inizio'] && $a['fine']==$this->param['fine'])?'x':(($a["inizio"]==$this->param['inizio'])?'i':'f');
                            
                            //echo '<div>'.json_encode($a).$x.'</div>';
                            $map=$this->magFunc->getGiacenzaM($a,$m);

                            if ($map['result']) {

                                $fid=$this->galileo->preFetchPiattaforma($map['piattaforma'],$map['result']);

                                while ($row=$this->galileo->getFetchPiattaforma($map['piattaforma'],$fid)) {
                                    $row['listino']=number_format($row['listino'],2,'.','');
                                    $row["rimanenze_iniziali"]=number_format($row["rimanenze_iniziali"],3,'.','');
                                    $row["imp"]=number_format($row["imp"],3,'.','');
                                    $this->feed($x,$row);
                                }

                                if ($x=='f' || $x=='x') {
                                    $this->addComuni();
                                }
                            }
                        }

                        $indice++;
                    }
                //}

                $f=$this->param['fine'];
            }
        }

        $this->feedTot();
    }

    function setExcalibur($dep,$ana) {

        $this->analisi['fine']['depositi'][$dep]['giacenza']=new excalibur('giacenza_M_'.$dep.'_'.$ana,"Giacenza deposito ".$dep.":");
                
        //########################
        //inizializzazione excalibur
        $conv=array(
            "deposito"=>"deposito",
            "ubicazione"=>"ubicazione",
            "precodice"=>"precodice",
            "articolo"=>"articolo",
            "descr_articolo"=>"descr_articolo",
            "listino"=>"listino",
            "rimanenze_finali"=>"rimanenze_finali",
            "imp"=>"imp",
            "d_carico"=>"d_carico",
            "d_scarico"=>"d_scarico"
        );

        $mappa=array(
            "deposito"=>array("tag"=>"Dep","css"=>"text-align:center;"),
            "ubicazione"=>array("tag"=>"Loc","css"=>"text-align:center;"),
            "precodice"=>array("tag"=>"precodice","css"=>"text-align:center;"),
            "articolo"=>array("tag"=>"articolo"),
            "descr_articolo"=>array("tag"=>"descrizione"),
            "listino"=>array("tag"=>"Listino","css"=>"text-align:right;"),
            "rimanenze_finali"=>array("tag"=>"Fine","css"=>"text-align:right;"),
            "imp"=>array("tag"=>"Impegnato","css"=>"text-align:right;"),
            "d_carico"=>array("tag"=>"Acquisto"),
            "d_scarico"=>array("tag"=>"Vendita")
        );

        $this->analisi['fine']['depositi'][$dep]['giacenza']->build($conv,$mappa);
        $this->analisi['fine']['depositi'][$dep]['giacenza']->setDatatable(false);
    }

    function addComuni() {

        if (count($this->tempComuni['arr'])>1) {

            if (!$this->analisi['comuni']) {

                $this->analisi['comuni']=new excalibur('comuni_M',"Articoli comuni:");
                
                //########################
                //inizializzazione excalibur
                $conv=array(
                    "deposito"=>"deposito",
                    "ubicazione"=>"ubicazione",
                    "precodice"=>"precodice",
                    "articolo"=>"articolo",
                    "descr_articolo"=>"descr_articolo",
                    "listino"=>"listino",
                    "rimanenze_finali"=>"rimanenze_finali",
                    "imp"=>"imp",
                    "d_carico"=>"d_carico",
                    "d_scarico"=>"d_scarico"
                );

                $mappa=array(
                    "deposito"=>array("tag"=>"Dep","css"=>"text-align:center;"),
                    "ubicazione"=>array("tag"=>"Loc","css"=>"text-align:center;"),
                    "precodice"=>array("tag"=>"precodice","css"=>"text-align:center;"),
                    "articolo"=>array("tag"=>"articolo"),
                    "descr_articolo"=>array("tag"=>"descrizione"),
                    "listino"=>array("tag"=>"Listino","css"=>"text-align:right;"),
                    "rimanenze_finali"=>array("tag"=>"Fine","css"=>"text-align:right;"),
                    "imp"=>array("tag"=>"Impegnato","css"=>"text-align:right;"),
                    "d_carico"=>array("tag"=>"Acquisto"),
                    "d_scarico"=>array("tag"=>"Vendita")
                );

                $this->analisi['comuni']->build($conv,$mappa);
                $this->analisi['comuni']->setDatatable(false);
            }

            ksort($this->tempComuni['arr']);

            foreach ($this->tempComuni['arr'] as $kcom=>$rcom) {
                $this->analisi['comuni']->add($rcom);
            }
        }
    }

    function feed($x,$row) {
        //{"deposito":"01","precodice":"V","articolo":"04L121157C","gm":"4","famiglia":"RBE","descr_articolo":"T. FLESS.","rimanenze_iniziali":"0.000","rimanenze_finali":"1.000","vendite":"0.000","acquisti":"1.000","listino":"8.9100000","costo":"6.3261000"}
        if ($row['gm']=='') $row['gm']='*';


        if ( ($x=='f' || $x=='x') && $row["rimanenze_finali"]>0) {

            $obso=date('Ymd',strtotime('-'.$this->param['obso'].' month',mainFunc::gab_tots($this->param['fine'])));

            if (!array_key_exists($row['deposito'],$this->analisi['fine']['depositi'])) {
                $this->analisi['fine']['depositi'][$row['deposito']]=$this->deposito;
                $this->analisi['fine']['depositi'][$row['deposito']]['totale']=$this->totale;

                $this->setExcalibur($row['deposito'],'fine');

                ////////////////////////////////////////////////////////////////////////////////////////////

                if ($this->analisi['inizio']) {
                    if (!array_key_exists($row['deposito'],$this->analisi['delta']['depositi'])) {
                        $this->analisi['delta']['depositi'][$row['deposito']]=$this->deposito;
                        $this->analisi['delta']['depositi'][$row['deposito']]['totale']=$this->totale;
                    }
                }
            }
            if (!array_key_exists($row['precodice'],$this->analisi['fine']['depositi'][$row['deposito']]['precodici'])) {
                $this->analisi['fine']['depositi'][$row['deposito']]['precodici'][$row['precodice']]=$this->blocco;
                $this->analisi['fine']['depositi'][$row['deposito']]['precodici'][$row['precodice']]['totale']=$this->totale;
                foreach ($this->tots as $ktot=>$tt) {
                    if ($tt) $this->analisi['fine']['depositi'][$row['deposito']]['precodici'][$row['precodice']]['tots'][$ktot]=$this->totale;
                }

                if ($this->analisi['inizio']) {
                    //if (!array_key_exists($row['precodice'],$this->analisi['delta']['depositi'][$row['deposito']]['precodici'])) {
                    if (!isset($this->analisi['delta']['depositi'][$row['deposito']]['precodici'][$row['precodice']])) {
                        $this->analisi['delta']['depositi'][$row['deposito']]['precodici'][$row['precodice']]=$this->blocco;
                        $this->analisi['delta']['depositi'][$row['deposito']]['precodici'][$row['precodice']]['totale']=$this->totale;
    
                        foreach ($this->tots as $ktot=>$tt) {
                            if ($tt) $this->analisi['delta']['depositi'][$row['deposito']]['precodici'][$row['precodice']]['tots'][$ktot]=$this->totale;
                        }
                    }
                }
            }
            if (!array_key_exists($row['gm'],$this->analisi['fine']['depositi'][$row['deposito']]['precodici'][$row['precodice']]['gm'])) {
                $this->analisi['fine']['depositi'][$row['deposito']]['precodici'][$row['precodice']]['gm'][$row['gm']]=$this->totale;

                if ($this->analisi['inizio']) {
                    if (!array_key_exists($row['gm'],$this->analisi['delta']['depositi'][$row['deposito']]['precodici'][$row['precodice']]['gm'])) {
                        $this->analisi['delta']['depositi'][$row['deposito']]['precodici'][$row['precodice']]['gm'][$row['gm']]=$this->totale;
                    }
                }
            }

            $this->analisi['fine']['depositi'][$row['deposito']]['precodici'][$row['precodice']]['gm'][$row['gm']]['pos']++;
            $this->analisi['fine']['depositi'][$row['deposito']]['precodici'][$row['precodice']]['gm'][$row['gm']]['qta']+=$row["rimanenze_finali"];
            $this->analisi['fine']['depositi'][$row['deposito']]['precodici'][$row['precodice']]['gm'][$row['gm']]['listino']+=($row["rimanenze_finali"]*$row['listino']);
            $this->analisi['fine']['depositi'][$row['deposito']]['precodici'][$row['precodice']]['gm'][$row['gm']]['costo']+=($row["rimanenze_finali"]*$row['costo']);

            $this->analisi['fine']['depositi'][$row['deposito']]['precodici'][$row['precodice']]['totale']['pos']++;
            $this->analisi['fine']['depositi'][$row['deposito']]['precodici'][$row['precodice']]['totale']['qta']+=$row["rimanenze_finali"];
            $this->analisi['fine']['depositi'][$row['deposito']]['precodici'][$row['precodice']]['totale']['listino']+=($row["rimanenze_finali"]*$row['listino']);
            $this->analisi['fine']['depositi'][$row['deposito']]['precodici'][$row['precodice']]['totale']['costo']+=($row["rimanenze_finali"]*$row['costo']);

            if ($row['d_scarico']<$obso && $row['d_carico']<$obso) {
                $this->analisi['fine']['depositi'][$row['deposito']]['precodici'][$row['precodice']]['gm'][$row['gm']]['obsoleto']+=($row["rimanenze_finali"]*$row['listino']);
                $this->analisi['fine']['depositi'][$row['deposito']]['precodici'][$row['precodice']]['totale']['obsoleto']+=($row["rimanenze_finali"]*$row['listino']);

                if (!$this->analisi['fine']['depositi'][$row['deposito']]['obsoleto']) {

                    $this->analisi['fine']['depositi'][$row['deposito']]['obsoleto']=new excalibur('obsoleto_M_'.$row['deposito'].'_fine',"Obsoleto deposito ".$row['deposito'].":");
                    
                    //########################
                    //inizializzazione excalibur
                    $conv=array(
                        "deposito"=>"deposito",
                        "ubicazione"=>"ubicazione",
                        "precodice"=>"precodice",
                        "articolo"=>"articolo",
                        "descr_articolo"=>"descr_articolo",
                        "listino"=>"listino",
                        "rimanenze_finali"=>"rimanenze_finali",
                        "imp"=>"imp",
                        "d_carico"=>"d_carico",
                        "d_scarico"=>"d_scarico"
                    );
    
                    $mappa=array(
                        "deposito"=>array("tag"=>"Dep","css"=>"text-align:center;"),
                        "ubicazione"=>array("tag"=>"Loc","css"=>"text-align:center;"),
                        "precodice"=>array("tag"=>"precodice","css"=>"text-align:center;"),
                        "articolo"=>array("tag"=>"articolo"),
                        "descr_articolo"=>array("tag"=>"descrizione"),
                        "listino"=>array("tag"=>"Listino","css"=>"text-align:right;"),
                        "rimanenze_finali"=>array("tag"=>"Fine","css"=>"text-align:right;"),
                        "imp"=>array("tag"=>"Impegnato","css"=>"text-align:right;"),
                        "d_carico"=>array("tag"=>"Acquisto"),
                        "d_scarico"=>array("tag"=>"Vendita")
                    );
    
                    $this->analisi['fine']['depositi'][$row['deposito']]['obsoleto']->build($conv,$mappa);
                    $this->analisi['fine']['depositi'][$row['deposito']]['obsoleto']->setDatatable(false);
                }

                $this->analisi['fine']['depositi'][$row['deposito']]['obsoleto']->add($row);
            }

            foreach ($this->analisi['fine']['depositi'][$row['deposito']]['precodici'][$row['precodice']]['tots'] as $ktot=>$tt) {

                $fu="check"."_".$ktot;
                if ($r2=$this->$fu($row)) {
                    $this->analisi['fine']['depositi'][$r2['deposito']]['precodici'][$r2['precodice']]['tots'][$ktot]['pos']++;
                    $this->analisi['fine']['depositi'][$r2['deposito']]['precodici'][$r2['precodice']]['tots'][$ktot]['qta']+=$r2["rimanenze_finali"];
                    $this->analisi['fine']['depositi'][$r2['deposito']]['precodici'][$r2['precodice']]['tots'][$ktot]['listino']+=($r2["rimanenze_finali"]*$r2['listino']);
                    $this->analisi['fine']['depositi'][$r2['deposito']]['precodici'][$r2['precodice']]['tots'][$ktot]['costo']+=($r2["rimanenze_finali"]*$r2['costo']);

                    if ($r2['d_scarico']<$obso && $r2['d_carico']<$obso) {
                        $this->analisi['fine']['depositi'][$r2['deposito']]['precodici'][$r2['precodice']]['tots'][$ktot]['obsoleto']+=($r2["rimanenze_finali"]*$r2['listino']);
                    }
                }
            }
            
            ///////////////////////////////
            $this->analisi['fine']['depositi'][$row['deposito']]['giacenza']->add($row);
            ///////////////////////////////

            //i record sono ordinati per precodice ed articolo
            //gli stessi indici su depositi diversi sono attigui
            $this->tempComuni['chk']=$row['precodice'].'_'.$row['articolo'];

            if ($this->tempComuni['chk']!=$this->tempComuni['rif']) {

                $this->addComuni();

                $this->tempComuni['arr']=array();
                $this->tempComuni['arr'][$row['deposito']]=$row;
                $this->tempComuni['rif']=$this->tempComuni['chk'];
            }
            else {
                $this->tempComuni['arr'][$row['deposito']]=$row;
            }

        }
        if ( ($x=='f' || $x=='x') && $row["rimanenze_finali"]<0) {
            
            if (!array_key_exists($row['deposito'],$this->analisi['fine']['depositi'])) {

                $this->analisi['fine']['depositi'][$row['deposito']]=$this->deposito;
                $this->analisi['fine']['depositi'][$row['deposito']]['totale']=$this->totale;

                $this->setExcalibur($row['deposito'],'fine');
            }

            if (!$this->analisi['fine']['depositi'][$row['deposito']]['negativi']) {

                $this->analisi['fine']['depositi'][$row['deposito']]['negativi']=new excalibur('negativi_M_'.$row['deposito'].'_fine',"Negativi deposito ".$row['deposito'].":");
                
                //########################
                //inizializzazione excalibur
                $conv=array(
                    "deposito"=>"deposito",
                    "ubicazione"=>"ubicazione",
                    "precodice"=>"precodice",
                    "articolo"=>"articolo",
                    "descr_articolo"=>"descr_articolo",
                    "listino"=>"listino",
                    "rimanenze_finali"=>"rimanenze_finali",
                    "imp"=>"imp",
                    "d_carico"=>"d_carico",
                    "d_scarico"=>"d_scarico"
                );

                $mappa=array(
                    "deposito"=>array("tag"=>"Dep","css"=>"text-align:center;"),
                    "ubicazione"=>array("tag"=>"Loc","css"=>"text-align:center;"),
                    "precodice"=>array("tag"=>"precodice","css"=>"text-align:center;"),
                    "articolo"=>array("tag"=>"articolo"),
                    "descr_articolo"=>array("tag"=>"descrizione"),
                    "listino"=>array("tag"=>"Listino","css"=>"text-align:right;"),
                    "rimanenze_finali"=>array("tag"=>"Fine","css"=>"text-align:right;"),
                    "imp"=>array("tag"=>"Impegnato","css"=>"text-align:right;"),
                    "d_carico"=>array("tag"=>"Acquisto"),
                    "d_scarico"=>array("tag"=>"Vendita")
                );

                $this->analisi['fine']['depositi'][$row['deposito']]['negativi']->build($conv,$mappa);
                $this->analisi['fine']['depositi'][$row['deposito']]['negativi']->setDatatable(false);
            }

            $this->analisi['fine']['depositi'][$row['deposito']]['giacenza']->add($row);
            $this->analisi['fine']['depositi'][$row['deposito']]['negativi']->add($row);

            //i record sono ordinati per precodice ed articolo
            //gli stessi indici su depositi diversi sono attigui
            $this->tempComuni['chk']=$row['precodice'].'_'.$row['articolo'];

            if ($this->tempComuni['chk']!=$this->tempComuni['rif']) {

                $this->addComuni();

                $this->tempComuni['arr']=array();
                $this->tempComuni['arr'][$row['deposito']]=$row;
                $this->tempComuni['rif']=$this->tempComuni['chk'];
            }
            else {
                $this->tempComuni['arr'][$row['deposito']]=$row;
            }

        }

        if ($this->analisi['inizio']) {

            if ( ($x=='i' || $x=='x') && $row["rimanenze_iniziali"]>0) {

                $obso=date('Ymd',strtotime('-'.$this->param['obso'].' month',mainFunc::gab_tots($this->param['inizio'])));

                if (!array_key_exists($row['deposito'],$this->analisi['inizio']['depositi'])) {
                    $this->analisi['inizio']['depositi'][$row['deposito']]=$this->deposito;
                    $this->analisi['inizio']['depositi'][$row['deposito']]['totale']=$this->totale;
                }

                if (!array_key_exists($row['deposito'],$this->analisi['delta']['depositi'])) {
                    $this->analisi['delta']['depositi'][$row['deposito']]=$this->deposito;
                    $this->analisi['delta']['depositi'][$row['deposito']]['totale']=$this->totale;
                }

                if (!array_key_exists($row['precodice'],$this->analisi['inizio']['depositi'][$row['deposito']]['precodici'])) {
                    $this->analisi['inizio']['depositi'][$row['deposito']]['precodici'][$row['precodice']]=$this->blocco;
                    $this->analisi['inizio']['depositi'][$row['deposito']]['precodici'][$row['precodice']]['totale']=$this->totale;

                    foreach ($this->tots as $ktot=>$tt) {
                        if ($tt) $this->analisi['inizio']['depositi'][$row['deposito']]['precodici'][$row['precodice']]['tots'][$ktot]=$this->totale;
                    }
                }
                if (!array_key_exists($row['precodice'],$this->analisi['delta']['depositi'][$row['deposito']]['precodici'])) {
                    $this->analisi['delta']['depositi'][$row['deposito']]['precodici'][$row['precodice']]=$this->blocco;
                    $this->analisi['delta']['depositi'][$row['deposito']]['precodici'][$row['precodice']]['totale']=$this->totale;

                    foreach ($this->tots as $ktot=>$tt) {
                        if ($tt) $this->analisi['delta']['depositi'][$row['deposito']]['precodici'][$row['precodice']]['tots'][$ktot]=$this->totale;
                    }
                }

                if (!array_key_exists($row['gm'],$this->analisi['inizio']['depositi'][$row['deposito']]['precodici'][$row['precodice']]['gm'])) {
                    $this->analisi['inizio']['depositi'][$row['deposito']]['precodici'][$row['precodice']]['gm'][$row['gm']]=$this->totale;
                }
                if (!array_key_exists($row['gm'],$this->analisi['delta']['depositi'][$row['deposito']]['precodici'][$row['precodice']]['gm'])) {
                    $this->analisi['delta']['depositi'][$row['deposito']]['precodici'][$row['precodice']]['gm'][$row['gm']]=$this->totale;
                }

                $this->analisi['inizio']['depositi'][$row['deposito']]['precodici'][$row['precodice']]['gm'][$row['gm']]['pos']++;
                $this->analisi['inizio']['depositi'][$row['deposito']]['precodici'][$row['precodice']]['gm'][$row['gm']]['qta']+=$row["rimanenze_iniziali"];
                $this->analisi['inizio']['depositi'][$row['deposito']]['precodici'][$row['precodice']]['gm'][$row['gm']]['listino']+=($row["rimanenze_iniziali"]*$row['listino']);
                $this->analisi['inizio']['depositi'][$row['deposito']]['precodici'][$row['precodice']]['gm'][$row['gm']]['costo']+=($row["rimanenze_iniziali"]*($row['costo_rif']>0?$row['costo_rif']:$row['costo']));

                $this->analisi['inizio']['depositi'][$row['deposito']]['precodici'][$row['precodice']]['totale']['pos']++;
                $this->analisi['inizio']['depositi'][$row['deposito']]['precodici'][$row['precodice']]['totale']['qta']+=$row["rimanenze_iniziali"];
                $this->analisi['inizio']['depositi'][$row['deposito']]['precodici'][$row['precodice']]['totale']['listino']+=($row["rimanenze_iniziali"]*$row['listino']);
                $this->analisi['inizio']['depositi'][$row['deposito']]['precodici'][$row['precodice']]['totale']['costo']+=($row["rimanenze_iniziali"]*($row['costo_rif']>0?$row['costo_rif']:$row['costo']));

                if ($row['d_scarico']<$obso && $row['d_carico']<$obso) {
                    $this->analisi['inizio']['depositi'][$row['deposito']]['precodici'][$row['precodice']]['gm'][$row['gm']]['obsoleto']+=($row["rimanenze_iniziali"]*$row['listino']);
                    $this->analisi['inizio']['depositi'][$row['deposito']]['precodici'][$row['precodice']]['totale']['obsoleto']+=($row["rimanenze_iniziali"]*$row['listino']);
                }

                foreach ($this->analisi['inizio']['depositi'][$row['deposito']]['precodici'][$row['precodice']]['tots'] as $ktot=>$tt) {

                    $fu="check"."_".$ktot;
                    if ($r2=$this->$fu($row)) {
                        $this->analisi['inizio']['depositi'][$r2['deposito']]['precodici'][$r2['precodice']]['tots'][$ktot]['pos']++;
                        $this->analisi['inizio']['depositi'][$r2['deposito']]['precodici'][$r2['precodice']]['tots'][$ktot]['qta']+=$r2["rimanenze_iniziali"];
                        $this->analisi['inizio']['depositi'][$r2['deposito']]['precodici'][$r2['precodice']]['tots'][$ktot]['listino']+=($r2["rimanenze_iniziali"]*$r2['listino']);
                        $this->analisi['inizio']['depositi'][$r2['deposito']]['precodici'][$r2['precodice']]['tots'][$ktot]['costo']+=($r2["rimanenze_iniziali"]*($r2['costo_rif']>0?$r2['costo_rif']:$r2['costo']));
    
                        if ($r2['d_scarico']<$obso && $r2['d_carico']<$obso) {
                            $this->analisi['inizio']['depositi'][$r2['deposito']]['precodici'][$r2['precodice']]['tots'][$ktot]['obsoleto']+=($r2["rimanenze_iniziali"]*$r2['listino']);
                        }
                    }
                }
            }

            //if (!array_key_exists($row['deposito'],$this->analisi['delta']['depositi'])) $this->analisi['delta']['depositi'][$row['deposito']]=$this->deposito;
            //if (!array_key_exists($row['precodice'],$this->analisi['delta']['depositi'][$row['deposito']]['precodici'])) $this->analisi['delta']['depositi'][$row['deposito']]['precodici'][$row['precodice']]=$this->blocco;
            //if (!array_key_exists($row['gm'],$this->analisi['delta']['depositi'][$row['deposito']]['precodici'][$row['precodice']]['gm'])) $this->analisi['delta']['depositi'][$row['deposito']]['precodici'][$row['precodice']]['gm'][$row['gm']]=$this->totale;
        }
        if ( ($x=='i' || $x=='x') && $row["rimanenze_iniziali"]<0) {
            //####################################
        }
    }

    function feedTot() {

        foreach ($this->analisi['fine']['depositi'] as $dep=>$d) {
            foreach ($d['precodici'] as $pre=>$p) {
                foreach ($p['totale'] as $kt=>$t) {
                    $this->analisi['fine']['depositi'][$dep]['totale'][$kt]+=$t;
                    $this->analisi['fine']['totale'][$kt]+=$t;
                }
            }
        }
        
        if ($this->analisi['inizio']) {
            foreach ($this->analisi['inizio']['depositi'] as $dep=>$d) {
                foreach ($d['precodici'] as $pre=>$p) {
                    foreach ($p['totale'] as $kt=>$t) {
                        $this->analisi['inizio']['depositi'][$dep]['totale'][$kt]+=$t;
                        $this->analisi['inizio']['totale'][$kt]+=$t;
                    }

                    foreach ($p['totale'] as $kt=>$t) {
                        $this->analisi['delta']['depositi'][$dep]['precodici'][$pre]['totale'][$kt]=$this->analisi['fine']['depositi'][$dep]['precodici'][$pre]['totale'][$kt]-$this->analisi['inizio']['depositi'][$dep]['precodici'][$pre]['totale'][$kt];
                    }

                    //#############################
                    //Aggiunto ISSET 30.08.2024
                    //#############################
                    if (isset($this->analisi['delta']['depositi'][$dep]['totale'])) {
                        foreach ($this->analisi['delta']['depositi'][$dep]['totale'] as $kt=>$t) {
                            $this->analisi['delta']['depositi'][$dep]['totale'][$kt]=$this->analisi['fine']['depositi'][$dep]['totale'][$kt]-$this->analisi['inizio']['depositi'][$dep]['totale'][$kt];
                            $this->analisi['delta']['totale'][$kt]=$this->analisi['fine']['totale'][$kt]-$this->analisi['inizio']['totale'][$kt];
                        }
                    }
                }
            }

            //#########################
            //calcolare la differenza
            foreach ($this->analisi['delta']['depositi'] as $dep=>$d) {
                foreach ($d['precodici'] as $pre=>$p) {
                    foreach ($p['gm'] as $kgm=>$g) {
                        if (isset($this->analisi['inizio']['depositi'][$dep]['precodici'][$pre]['gm'][$kgm])) {
                            $vi=$this->analisi['inizio']['depositi'][$dep]['precodici'][$pre]['gm'][$kgm];
                        }
                        else {
                            $vi=$this->totale;
                            $this->analisi['inizio']['depositi'][$dep]['precodici'][$pre]['gm'][$kgm]=$this->totale;
                        }
                        
                        if (isset($this->analisi['fine']['depositi'][$dep]['precodici'][$pre]['gm'][$kgm])){
                            $vf=$this->analisi['fine']['depositi'][$dep]['precodici'][$pre]['gm'][$kgm];
                        }
                        else {
                            $vf=$this->totale;
                            $this->analisi['fine']['depositi'][$dep]['precodici'][$pre]['gm'][$kgm]=$this->totale;
                        } 

                        foreach ($g as $kv=>$v) {
                            $this->analisi['delta']['depositi'][$dep]['precodici'][$pre]['gm'][$kgm][$kv]=$vf[$kv]-$vi[$kv];
                        } 
                    }

                    foreach ($p['tots'] as $ktot=>$tt) {

                        $this->analisi['delta']['depositi'][$dep]['precodici'][$pre]['tots'][$ktot]['pos']=$this->analisi['fine']['depositi'][$dep]['precodici'][$pre]['tots'][$ktot]['pos']-$this->analisi['inizio']['depositi'][$dep]['precodici'][$pre]['tots'][$ktot]['pos'];
                        $this->analisi['delta']['depositi'][$dep]['precodici'][$pre]['tots'][$ktot]['qta']=$this->analisi['fine']['depositi'][$dep]['precodici'][$pre]['tots'][$ktot]['qta']-$this->analisi['inizio']['depositi'][$dep]['precodici'][$pre]['tots'][$ktot]['qta'];
                        $this->analisi['delta']['depositi'][$dep]['precodici'][$pre]['tots'][$ktot]['listino']=$this->analisi['fine']['depositi'][$dep]['precodici'][$pre]['tots'][$ktot]['listino']-$this->analisi['inizio']['depositi'][$dep]['precodici'][$pre]['tots'][$ktot]['listino'];
                        $this->analisi['delta']['depositi'][$dep]['precodici'][$pre]['tots'][$ktot]['costo']=$this->analisi['fine']['depositi'][$dep]['precodici'][$pre]['tots'][$ktot]['costo']-$this->analisi['inizio']['depositi'][$dep]['precodici'][$pre]['tots'][$ktot]['costo'];
                        $this->analisi['delta']['depositi'][$dep]['precodici'][$pre]['tots'][$ktot]['obsoleto']=$this->analisi['fine']['depositi'][$dep]['precodici'][$pre]['tots'][$ktot]['obsoleto']-$this->analisi['inizio']['depositi'][$dep]['precodici'][$pre]['tots'][$ktot]['obsoleto'];
                        
                    }
                }
            }
        }
        
    }

    function check_gm19($row) {

        if ($row['precodice']=='V') {
            if ($row['gm']>=1 && $row['gm']<=9) return $row;
        }
        return false;
    }

    function draw() {

        echo '<input id="c2rm_deltaHidden" type="hidden" value="1" />';

        //new DIVO 1
        //($index,$htab,$minh,$fixed)
        $divo1=new Divo('c2rAnalisiM','5%','97%',1);
        $divo1->setBk('#9ddaa2');

        //scrivi i div delle analisi
        foreach ($this->analisi as $ana=>$a) {

            if (!$a || $ana=='delta' || $ana=='comuni') continue;

            ob_start();
            
                echo '<div>';
                    $this->drawDep($ana,$a);
                echo '</div>';

            //add DIV
            //add_div($titolo,$color,$chk,$stato,$codice,$selected,$css)
            $divo1->add_div($a['tag'].' '.mainFunc::gab_todata($a['data']),'black',0,'',ob_get_clean(),($ana=='fine'?1:0),array());
        }

        $divo1->build();
        $divo1->draw();

        //echo '<div>'.json_encode($this->analisi).'</div>';
        //echo json_encode($this->log);
    }

    function drawDep($ana,$a) {

        $divo2=new Divo('c2rDep_'.$ana,'4%','96%',1);
        $divo2->setBk('#a8b4ce');

        $css=array(
            "font-weight"=>"bold",
            "margin-top"=>"0px",
            "font-size"=>"0.8em",
            "text-align"=>"center"
        );

        foreach ($a['depositi'] as $dep=>$d) {
            //##############################################
            //scrittura del DIV per il totale generale in DIVO2
            ob_start();
                echo '<div style="">';
                    $this->drawDepDet($ana,$dep,$d);
                echo '</div>';

            $divo2->add_div($dep,'black',0,'',ob_get_clean(),0,$css);
        }

        if ($ana=='fine') {

            if ($this->analisi['comuni']) {

                ob_start();

                $this->analisi['comuni']->draw();

                $divo2->add_div('Comuni','black',0,'',ob_get_clean(),0,$css);
            }
        }


        $divo2->build();
        $divo2->draw();
    }

    function drawDepDet($ana,$dep,$d) {

        ksort($d['precodici']);

        ob_start();

        foreach ($d['precodici'] as $kp=>$p) {

            echo '<div style="border-top:1px solid black;" >';
                echo '<table style="font-size:10pt;margin-top:10px;border-collapse:collapse;">';
                    $this->drawThead($kp);
                    ksort($p['gm']);

                    echo '<tbody>';
                        foreach ($p['gm'] as $kg=>$g) {
                            echo '<tr>';
                                echo '<td style="text-align:center;">'.$kg.'</td>';
                                echo '<td style="text-align:center;">'.$g['pos'].'</td>';
                                echo '<td style="text-align:center;">'.number_format($g['qta'],2,'.','').'</td>';
                                echo '<td style="text-align:right;">'.number_format($g['listino'],2,'.','.').'</td>';
                                echo '<td style="text-align:right;">'.number_format($g['costo'],2,'.','.').'</td>';
                                echo '<td style="text-align:center;font-size:0.9em;">'.number_format((1-($g['listino']==0?1:$g['costo']/$g['listino']))*100,2,'.','.').' %</td>';
                                echo '<td style="text-align:right;">'.number_format($g['obsoleto'],2,'.','.').'</td>';
                                echo '<td style="text-align:center;font-size:0.9em;">'.number_format(($g['listino']==0?0:$g['obsoleto']/$g['listino'])*100,2,'.','.').' %</td>';
                            echo '</tr>';

                            if ($ana=='fine' && $this->analisi['inizio']) {
                                echo '<tr class="c2rm_deltaTr" style="font-size:0.9em;color:#8474f4;font-weight:bold;" >';
                                    echo '<td style="text-align:center;"></td>';
                                    echo '<td style="text-align:center;">'.$this->analisi['delta']['depositi'][$dep]['precodici'][$kp]['gm'][$kg]['pos'].'</td>';
                                    echo '<td style="text-align:center;">'.number_format($this->analisi['delta']['depositi'][$dep]['precodici'][$kp]['gm'][$kg]['qta'],2,'.','').'</td>';
                                    echo '<td style="text-align:right;">'.number_format($this->analisi['delta']['depositi'][$dep]['precodici'][$kp]['gm'][$kg]['listino'],2,'.','.').'</td>';
                                    echo '<td style="text-align:right;">'.number_format($this->analisi['delta']['depositi'][$dep]['precodici'][$kp]['gm'][$kg]['costo'],2,'.','.').'</td>';
                                    $pf=(1-($g['listino']==0?1:$g['costo']/$g['listino']))*100;
                                    $pi=(1-($this->analisi['inizio']['depositi'][$dep]['precodici'][$kp]['gm'][$kg]['listino']==0?1:$this->analisi['inizio']['depositi'][$dep]['precodici'][$kp]['gm'][$kg]['costo']/$this->analisi['inizio']['depositi'][$dep]['precodici'][$kp]['gm'][$kg]['listino']))*100;
                                    echo '<td style="text-align:center;font-size:0.9em;">'.number_format(($g['listino']==0?0:$pf-$pi),2,'.','.').' %</td>';
                                    echo '<td style="text-align:right;">'.number_format($this->analisi['delta']['depositi'][$dep]['precodici'][$kp]['gm'][$kg]['obsoleto'],2,'.','.').'</td>';
                                    $pf=($g['listino']==0?0:$g['obsoleto']/$g['listino'])*100;
                                    $pi=($this->analisi['inizio']['depositi'][$dep]['precodici'][$kp]['gm'][$kg]['listino']==0?0:$this->analisi['inizio']['depositi'][$dep]['precodici'][$kp]['gm'][$kg]['obsoleto']/$this->analisi['inizio']['depositi'][$dep]['precodici'][$kp]['gm'][$kg]['listino'])*100;
                                    echo '<td style="text-align:center;font-size:0.9em;">'.number_format($pf-$pi,2,'.','.').' %</td>';
                                echo '</tr>';
                            }
                        }

                        $this->drawTot($kp,$p['totale']);

                        if ($ana=='fine' && $this->analisi['inizio']) {
                            echo '<tr class="c2rm_deltaTr" style="font-size:0.9em;color:black;font-weight:bold;background-color:beige;" >';
                                echo '<td style="text-align:center;"></td>';
                                echo '<td style="text-align:center;">'.$this->analisi['delta']['depositi'][$dep]['precodici'][$kp]['totale']['pos'].'</td>';
                                echo '<td style="text-align:center;">'.number_format($this->analisi['delta']['depositi'][$dep]['precodici'][$kp]['totale']['qta'],2,'.','').'</td>';
                                echo '<td style="text-align:right;">'.number_format($this->analisi['delta']['depositi'][$dep]['precodici'][$kp]['totale']['listino'],2,'.','.').'</td>';
                                echo '<td style="text-align:right;">'.number_format($this->analisi['delta']['depositi'][$dep]['precodici'][$kp]['totale']['costo'],2,'.','.').'</td>';
                                //$pf=(1-($g['listino']==0?1:$g['costo']/$g['listino']))*100;
                                //$pi=(1-($this->analisi['inizio']['depositi'][$dep]['precodici'][$kp]['gm'][$kg]['listino']==0?1:$this->analisi['inizio']['depositi'][$dep]['precodici'][$kp]['gm'][$kg]['costo']/$this->analisi['inizio']['depositi'][$dep]['precodici'][$kp]['gm'][$kg]['listino']))*100;
                                echo '<td style="text-align:center;font-size:0.9em;"></td>';
                                //echo '<td style="text-align:center;font-size:0.9em;">'.number_format(($g['listino']==0?0:$pf-$pi),2,'.','.').' %</td>';
                                echo '<td style="text-align:right;">'.number_format($this->analisi['delta']['depositi'][$dep]['precodici'][$kp]['totale']['obsoleto'],2,'.','.').'</td>';
                                //$pf=($g['listino']==0?0:$g['obsoleto']/$g['listino'])*100;
                                //$pi=($this->analisi['inizio']['depositi'][$dep]['precodici'][$kp]['gm'][$kg]['listino']==0?0:$this->analisi['inizio']['depositi'][$dep]['precodici'][$kp]['gm'][$kg]['obsoleto']/$this->analisi['inizio']['depositi'][$dep]['precodici'][$kp]['gm'][$kg]['listino'])*100;
                                echo '<td style="text-align:center;font-size:0.9em;"></td>';
                                //echo '<td style="text-align:center;font-size:0.9em;">'.number_format($pf-$pi,2,'.','.').' %</td>';
                            echo '</tr>';
                        }

                        foreach ($p['tots'] as $ktot=>$tt) {
                            $this->drawTotx($ktot,$tt);

                            if ($ana=='fine' && $this->analisi['inizio']) {
                                echo '<tr class="c2rm_deltaTr" style="font-size:0.9em;color:black;font-weight:bold;background-color:bisque;" >';
                                    echo '<td style="text-align:center;"></td>';
                                    echo '<td style="text-align:center;">'.$this->analisi['delta']['depositi'][$dep]['precodici'][$kp]['tots'][$ktot]['pos'].'</td>';
                                    echo '<td style="text-align:center;">'.number_format($this->analisi['delta']['depositi'][$dep]['precodici'][$kp]['tots'][$ktot]['qta'],2,'.','').'</td>';
                                    echo '<td style="text-align:right;">'.number_format($this->analisi['delta']['depositi'][$dep]['precodici'][$kp]['tots'][$ktot]['listino'],2,'.','.').'</td>';
                                    echo '<td style="text-align:right;">'.number_format($this->analisi['delta']['depositi'][$dep]['precodici'][$kp]['tots'][$ktot]['costo'],2,'.','.').'</td>';
                                    //$pf=(1-($g['listino']==0?1:$g['costo']/$g['listino']))*100;
                                    //$pi=(1-($this->analisi['inizio']['depositi'][$dep]['precodici'][$kp]['gm'][$kg]['listino']==0?1:$this->analisi['inizio']['depositi'][$dep]['precodici'][$kp]['gm'][$kg]['costo']/$this->analisi['inizio']['depositi'][$dep]['precodici'][$kp]['gm'][$kg]['listino']))*100;
                                    echo '<td style="text-align:center;font-size:0.9em;"></td>';
                                    //echo '<td style="text-align:center;font-size:0.9em;">'.number_format(($g['listino']==0?0:$pf-$pi),2,'.','.').' %</td>';
                                    echo '<td style="text-align:right;">'.number_format($this->analisi['delta']['depositi'][$dep]['precodici'][$kp]['tots'][$ktot]['obsoleto'],2,'.','.').'</td>';
                                    //$pf=($g['listino']==0?0:$g['obsoleto']/$g['listino'])*100;
                                    //$pi=($this->analisi['inizio']['depositi'][$dep]['precodici'][$kp]['gm'][$kg]['listino']==0?0:$this->analisi['inizio']['depositi'][$dep]['precodici'][$kp]['gm'][$kg]['obsoleto']/$this->analisi['inizio']['depositi'][$dep]['precodici'][$kp]['gm'][$kg]['listino'])*100;
                                    echo '<td style="text-align:center;font-size:0.9em;"></td>';
                                    //echo '<td style="text-align:center;font-size:0.9em;">'.number_format($pf-$pi,2,'.','.').' %</td>';
                                echo '</tr>';
                            }
                        }

                    echo '</tbody>';

                echo '</table>';

            echo '</div>';
        }

        if ($ana=='inizio') ob_end_flush();
        else {
            $divo4=new Divo('c2rGia_'.$dep.'_'.$ana,'4%','96%',1);
            $divo4->setBk('#cea8a8');

            $css=array(
                "font-weight"=>"bold",
                "margin-top"=>"0px",
                "font-size"=>"0.8em",
                "text-align"=>"center"
            );

            $divo4->add_div('Analisi','black',0,'',ob_get_clean(),1,$css);

            if ($this->analisi['fine']['depositi'][$dep]['giacenza']) {

                ob_start();

                $this->analisi['fine']['depositi'][$dep]['giacenza']->draw();

                $divo4->add_div('Giacenza','black',0,'',ob_get_clean(),0,$css);
            }

            if ($this->analisi['fine']['depositi'][$dep]['negativi']) {

                ob_start();

                $this->analisi['fine']['depositi'][$dep]['negativi']->draw();

                $divo4->add_div('Negativi','black',0,'',ob_get_clean(),0,$css);
            }

            if ($this->analisi['fine']['depositi'][$dep]['obsoleto']) {

                ob_start();

                $this->analisi['fine']['depositi'][$dep]['obsoleto']->draw();

                $divo4->add_div('Obsoleto','black',0,'',ob_get_clean(),0,$css);
            }

            $divo4->build();
            $divo4->draw();
        }
    }

    function drawThead($tag) {
        echo '<colgroup>';
            echo '<col span="3" style="width:100px;" />';
            echo '<col span="3" style="width:100px;" />';
            echo '<col span="2" style="width:100px;" />';
        echo '</colgroup>';
        echo '<thead>';
            echo '<tr style="background-color:turquoise;">';
                echo '<th style="text-align:center;">'.$tag.'</th>';
                echo '<th style="text-align:center;">Pos</th>';
                echo '<th style="text-align:center;">Q.tà</th>';
                echo '<th style="text-align:center;"><div>Listino</div><div style="font-size:0.8em;">ultimo '.date('Y').'</div></th>';
                echo '<th style="text-align:center;"><div>Costo</div><div style="font-size:0.8em;">ultimo '.date('Y').'</div></th>';
                echo '<th style="text-align:center;">Sconto</th>';
                echo '<th style="text-align:center;"><div>Obso '.$this->param['obso'].'</div><div style="font-size:0.9em;">car+scar</div></th>';
                echo '<th style="text-align:center;">Prc Obso</th>';
            echo '</tr>';
        echo '</thead>';
    }

    function drawtot($tag,$t) {
        echo '<tr style="background-color:beige;" >';
            echo '<td style="text-align:center;">Totale: '.$tag.'</td>';
            echo '<td style="text-align:center;">'.$t['pos'].'</td>';
            echo '<td style="text-align:center;">'.number_format($t['qta'],2,'.','').'</td>';
            echo '<td style="text-align:right;">'.number_format($t['listino'],2,'.','.').'</td>';
            echo '<td style="text-align:right;">'.number_format($t['costo'],2,'.','.').'</td>';
            echo '<td style="text-align:center;font-size:0.9em;">'.number_format((1-($t['listino']==0?1:$t['costo']/$t['listino']))*100,2,'.','.').' %</td>';
            echo '<td style="text-align:right;">'.number_format($t['obsoleto'],2,'.','.').'</td>';
            echo '<td style="text-align:center;font-size:0.9em;">'.number_format(($t['listino']==0?0:$t['obsoleto']/$t['listino'])*100,2,'.','.').' %</td>';
        echo '</tr>';
    }

    function drawtotx($tag,$t) {
        echo '<tr style="background-color:bisque;" >';
            echo '<td style="text-align:center;">Totale: '.$tag.'</td>';
            echo '<td style="text-align:center;">'.$t['pos'].'</td>';
            echo '<td style="text-align:center;">'.number_format($t['qta'],2,'.','').'</td>';
            echo '<td style="text-align:right;">'.number_format($t['listino'],2,'.','.').'</td>';
            echo '<td style="text-align:right;">'.number_format($t['costo'],2,'.','.').'</td>';
            echo '<td style="text-align:center;font-size:0.9em;">'.number_format((1-($t['listino']==0?1:$t['costo']/$t['listino']))*100,2,'.','.').' %</td>';
            echo '<td style="text-align:right;">'.number_format($t['obsoleto'],2,'.','.').'</td>';
            echo '<td style="text-align:center;font-size:0.9em;">'.number_format(($t['listino']==0?0:$t['obsoleto']/$t['listino'])*100,2,'.','.').' %</td>';
        echo '</tr>';
    }

    function drawTot_() {

        $divo2=new Divo('c2rMarche','4%','96%',1);
        $divo2->setBk('#a8b4ce');

        $css=array(
            "font-weight"=>"bold",
            "margin-top"=>"0px",
            "font-size"=>"0.8em",
            "text-align"=>"center"
        );

        //##############################################
        //scrittura del DIV per il totale generale in DIVO2
        /*$txt='<div>';
            ob_start();
                $this->analisi['tot']['blocco']['totale']->draw();
            $txt.=ob_get_clean();
        $txt.='</div>';

        $divo2->add_div('TOT','black',0,'',$txt,1,$css);*/
        //###############################################

        ksort($this->analisi['tot']['blocco']['marche']);

        foreach ($this->analisi['tot']['blocco']['marche'] as $marca=>$m) {

            $divo3=new Divo('c2rModelli_'.$marca,'4%','95%',1);
            $divo3->setBk('#a8cead');

            //##############################################
            //scrittura del DIV per il totale di marca in DIVO3
            $t3='<div>';
                ob_start();
                    $m['totale']->draw();
                $t3.=ob_get_clean();
            $t3.='</div>';

            $divo3->add_div('TOT','black',0,'',$t3,1,$css);
            //###############################################

            ksort($m['modelli']);

            //creazione del div dei modelli
            foreach ($m['modelli'] as $modello=>$o) {

                $t2='<div>';
                    ob_start();
                        $o->draw();
                    $t2.=ob_get_clean();            
                $t2.='</div>';

                $divo3->add_div($modello,'black',0,'',$t2,0,$css);
            }

            //scrivi DIVO delle marche
            $txt='<div>';
                $divo3->build();
                ob_start();
                    $divo3->draw();
                $txt.=ob_get_clean();
            $txt.='</div>';

            $divo2->add_div($marca,'black',0,'',$txt,0,$css);
        }

        $divo2->build();

        ob_start();
            $divo2->draw();
        return ob_get_clean();

    }

}

?>