<?php

include('block.php');

//classe che si interfaccia con il DB delle timbrature del badge e le mette a disposizione per l'elaborazione.

class nebulaAlan {

    protected $prefix="";
    protected $macroreparto="";

    //rappresenta lo stato del collaboratore
    protected $info=array(
        "collaboratore"=>false,
        "stato"=>'OK',
        "turno"=>false,
        "data_i"=>"",
        "data_f"=>""
    );

    protected $res=array();

    protected $lista=array();

    protected $regole=array();

    protected $actualR=array(
        "indice"=>false,
        "limite_i"=>"",
        "limite_f"=>""
    );

    protected $today="";

    protected $parent;
    protected $galileo;

    function __construct($macroreparto,$prefix,$parent,$galileo) {

        //parent si riferisce alla classe TEMPO che instanzia ALAN
        //e serve per disegnare gli eventi ma non entra nel calcolo
        $this->macroreparto=$macroreparto;
        $this->prefix=$prefix;
        $this->parent=$parent;
        $this->galileo=$galileo;

        $this->today=date('Ymd');

        //recupera da ALAN_regole
        //TEST

        if ($macroreparto=='V') {

            $this->regole=array(
                array(
                    "data_i"=>"20210101",
                    "data_f"=>"21001231",
                    "arrotondamento"=>15,
                    "tolleranzaE"=>5,
                    "tolleranzaU"=>0,
                    "arrBrogliaccio"=>30,
                    "intervalloMax"=>480,
                    "pausaMin"=>30,
                    "ore0"=>0,
                    "ore1"=>8,
                    "ore2"=>8,
                    "ore3"=>8,
                    "ore4"=>8,
                    "ore5"=>8,
                    "ore6"=>8,
                    "d0"=>"dom",
                    "d1"=>"lav",
                    "d2"=>"lav",
                    "d3"=>"lav",
                    "d4"=>"lav",
                    "d5"=>"lav",
                    "d6"=>"lav"
                )
            );

        }
        else {

            $this->regole=array(
                array(
                    "data_i"=>"20210101",
                    "data_f"=>"21001231",
                    "arrotondamento"=>15,
                    "tolleranzaE"=>5,
                    "tolleranzaU"=>0,
                    "arrBrogliaccio"=>30,
                    "intervalloMax"=>480,
                    "pausaMin"=>30,
                    "ore0"=>0,
                    "ore1"=>8,
                    "ore2"=>8,
                    "ore3"=>8,
                    "ore4"=>8,
                    "ore5"=>8,
                    "ore6"=>0,
                    "d0"=>"dom",
                    "d1"=>"lav",
                    "d2"=>"lav",
                    "d3"=>"lav",
                    "d4"=>"lav",
                    "d5"=>"lav",
                    "d6"=>"sab"
                )
            );
        }
        //ENDTEST
        
    }

    function getRes() {
        return $this->res;
    }

    //lettura delle timbrature da solari in base all'ultimo ID considerato e scrittura in ALAN_timbrature
    function importa() {

        $rif=false;
        
        $this->galileo->executeSelect('alan','ALAN_parametri',"parametro='indice'",'');
        $result=$this->galileo->getResult();
		if($result) {
			$fetID=$this->galileo->preFetch('alan');
			while ($row=$this->galileo->getFetch('alan',$fetID)) {
				$rif=$row['valore'];
			}
		}

        if (!$rif) return;

        $this->galileo->clearQuery();

        $newRif=$rif;

        $this->galileo->getTimbratureSolari($rif);
        $result=$this->galileo->getResult();

        if($result) {
			$fetID=$this->galileo->preFetchBase('badge');
			while ($row=$this->galileo->getFetchBase('badge',$fetID)) {
				$this->galileo->clearQuery();
                $this->galileo->clearQueryOggetto('default','alan');
                $row['DATAO']=$row['d'].' '.$row['h'];
                $this->galileo->executeInsert('alan','ALAN_timbrature',$row);
                //il riferimento aumenta sia che la scrittura sia andata a buon fine o meno
                $newRif=$row['IDTIMBRATURA'];
			}
		}

        //aggiorna indice di riferimento
        if ($newRif>$rif) {
            $this->galileo->clearQuery();
            $arr=array(
                "valore"=>$newRif
            );
            $this->galileo->executeUpdate('alan','ALAN_parametri',$arr,"parametro='indice'");
        }

        //return $this->galileo->getlog('query');
    }

    //leggi i dati del collaboratore
    function setCollaboratore($c,$turno) {
        $this->info['collaboratore']=$c;
        $this->info['turno']=$turno;
        $this->info['stato']='OK';

        $this->lista=array();

        $this->actualR=array(
            "indice"=>false,
            "limite_i"=>"",
            "limite_f"=>""
        );

        $this->res=array();
    }

    //leggi le timbrature del collaboratore attuale in ordine di timbratura
    function leggi($da,$a) {

        $this->info['data_i']=$da;
        $this->info['data_f']=$a;

        $arr=array(
            "dipendente"=>$this->info['collaboratore']['IDDIP'],
            "da"=>$da,
            "a"=>$a
        );

        $this->galileo->executeGeneric('alan','getTimbrature',$arr,'');
        $result=$this->galileo->getResult();
        if($result) {
			$fetID=$this->galileo->preFetch('alan');
			while ($row=$this->galileo->getFetch('alan',$fetID)) {

                //arrotondamento in base al VERSO
                //scrittura del campo "actualH"
                //la scrittura seleziona anche la validità delle regole per il giorno in questione
                $row['actualH']=$this->arrotondaH($row);

                //lettura delle caratteristiche del giorno secondo le regole
                $w=date('w',mainFunc::gab_tots($row['d']));
                $row['oreSTD']=$this->regole[$this->actualR['indice']]['ore'.$w];
                $row['tipoSTR']=$this->regole[$this->actualR['indice']]['d'.$w];
                $row['pausaMin']=$this->regole[$this->actualR['indice']]['pausaMin'];
                $row['intervalloMax']=$this->regole[$this->actualR['indice']]['intervalloMax'];

                $ind=($row['actualH']!="")?mainFunc::gab_stringtomin($row['actualH']):mainFunc::gab_stringtomin($row['h']);

                $row['actualM']=$ind;

                //se due marcature avvengono nello stesso arrotondamento la seconda sovrascrive la prima
                $this->lista[$row['d']][$ind]=$row;
            }
        }

        $wc="IDDIP='".$this->info['collaboratore']['IDDIP']."' AND d>='".$this->info['data_i']."' AND d<='".$this->info['data_f']."'";

        $this->galileo->executeSelect('alan','ALAN_timbrature_k',$wc,'IDDIP,d');
        $result=$this->galileo->getResult();
        if($result) {
			$fetID=$this->galileo->preFetch('alan');
			while ($row=$this->galileo->getFetch('alan',$fetID)) {
                $row['IDTIMBRATURA']='k'.$row['IDTIMBRATURA'];
                $row['VERSOO']='E';
                $row['h']='00:00';
                $row['actualH']=$this->arrotondaH($row);
                
                //lettura delle caratteristiche del giorno secondo le regole
                $w=date('w',mainFunc::gab_tots($row['d']));
                $row['oreSTD']=$this->regole[$this->actualR['indice']]['ore'.$w];
                $row['tipoSTR']=$this->regole[$this->actualR['indice']]['d'.$w];
                $row['pausaMin']=$this->regole[$this->actualR['indice']]['pausaMin'];
                $row['intervalloMax']=$this->regole[$this->actualR['indice']]['intervalloMax'];

                $ind=($row['actualH']!="")?mainFunc::gab_stringtomin($row['actualH']):mainFunc::gab_stringtomin($row['h']);

                $row['actualM']=$ind;

                $this->lista[$row['d']][$ind]=$row;
            }
        }

        //TEST
        /*if ($this->info['collaboratore']['ID_coll']=='9') {
            $this->lista['20210216'][0]=array(
                "IDDIP"=>'51',
                "d"=>'20210216',
                "h"=>'00:00',
                "VERSOO"=>'E',
                "IDTIMBRATURA"=>"K1",
                "forza_minuti"=>480,
                "actualH"=>'00:00',
                "actualM"=>0,
                "oreSTD"=>8,
                "tipoSTR"=>'lav'
            );
        }*/
        //END TEST
    }

    function arrotondaH($row) {
        //{"IDDIP":164,"d":"20210601","h":"09:17","VERSOO":"E","IDTIMBRATURA":570104}

        if (!$this->actualR['indice'] || $row['d']>$this->actualR['limite_f'] || $row['d']<$this->actualR['limite_i']) {
            $this->selectRule($row['d']);
        }
        if ($this->actualR['indice']===false) return "";

        $min=mainFunc::gab_stringtomin($row['h']);

        $k=(floor($min/$this->regole[$this->actualR['indice']]['arrotondamento']))*$this->regole[$this->actualR['indice']]['arrotondamento'];

        if ($row['VERSOO']=='E') {
            $riferimento=$k;
            $limite=$riferimento+$this->regole[$this->actualR['indice']]['tolleranzaE'];
            if ($min<=$limite) return mainFunc::gab_mintostring($riferimento);
            else return mainFunc::gab_mintostring($riferimento+$this->regole[$this->actualR['indice']]['arrotondamento']);
        }
        else {
            $riferimento=$k+$this->regole[$this->actualR['indice']]['arrotondamento'];
            $limite=$riferimento-$this->regole[$this->actualR['indice']]['tolleranzaU'];
            if ($min>=$limite) return mainFunc::gab_mintostring($riferimento);
            else return mainFunc::gab_mintostring($riferimento-$this->regole[$this->actualR['indice']]['arrotondamento']);
        }
        
    }

    function selectRule($d) {

        $this->actualR=array(
            "indice"=>false,
            "limite_i"=>"",
            "limite_f"=>""
        );

        //seleziona la regola
        foreach ($this->regole as $i=>$r) {
            if ($d>=$r['data_i'] && $d<=$r['data_f']) {
                $this->actualR['indice']=$i;
                $this->actualR['limite_i']=$r['data_i'];
                $this->actualR['limite_f']=$r['data_f'];
                break;
            }
        }
    }

    function getDefaultDay($tag) {

        if (isset ($this->info['turno'][$tag]['actual']) ) {
            $actual=$this->info['turno'][$tag]['actual'];
        }
        else {
            $actual=0;
        }

        if (isset ($this->info['turno'][$tag]['actualBro']) ) {
            $actualBro=$this->info['turno'][$tag]['actualBro'];
        }
        else {
            $actualBro=0;
        }

        if (!isset($this->res[$tag])) {
            $this->selectRule($tag);
            $w=date('w',mainFunc::gab_tots($tag));
            $std=$this->regole[$this->actualR['indice']]['ore'.$w];
            $str=$this->regole[$this->actualR['indice']]['d'.$w];
            $arrot=$this->regole[$this->actualR['indice']]['arrBrogliaccio'];
        }
        else {
            $std=0;
            $str="";
            $arrot=30;
        }

        return array(
            "minuti"=>0,
            "arrotondato"=>0,
            "oreSTD"=>$std,
            "tipoSTR"=>$str,
            "arrotondamento"=>$arrot,
            "nominale"=>($this->info['turno'][$tag])?$this->info['turno'][$tag]:0,
            "actual"=>$actual,
            "actualBro"=>$actualBro,
            "stato"=>'OK',
            "blocks"=>array()
        );

    }

    function getActualTimb($id,$ordine) {
        $arr=array(
            "dipendente"=>$id,
            "da"=>date('Ymd'),
            "a"=>date('Ymd'),
            "timbrature"=>false,
            "ordine_query"=>isset($ordine)?$ordine:false
        );

        $this->galileo->executeGeneric('alan','getTimbrature',$arr,'');
        $result=$this->galileo->getResult();
        if($result) {
			$fetID=$this->galileo->preFetch('alan');
			while ($row=$this->galileo->getFetch('alan',$fetID)) {
                if (!$arr['timbrature']) $arr['timbrature']=array();
                $arr['timbrature'][]=$row;
            }
        }

        return $arr;
    }

    function build() {
        //costruisce i blocchi di timbrature per il collaboratore attivo

        if (!$this->info['collaboratore'] || !$this->info['turno']) return;

        $temp=false;
        $lastday="";

        foreach ($this->lista as $tag=>$t) {
            /*{
                "20210701":{
                    "480":{"IDDIP":19,"d":"20210701","h":"07:55","VERSOO":"E","IDTIMBRATURA":582506,"actualH":"08:00","oreSTD":8,"tipoSTR":"lav"},
                    "720":{"IDDIP":19,"d":"20210701","h":"12:03","VERSOO":"U","IDTIMBRATURA":582598,"actualH":"12:00","oreSTD":8,"tipoSTR":"lav"},
                    "900":{"IDDIP":19,"d":"20210701","h":"14:56","VERSOO":"E","IDTIMBRATURA":582742,"actualH":"15:00","oreSTD":8,"tipoSTR":"lav"},
                    "1140":{"IDDIP":19,"d":"20210701","h":"19:07","VERSOO":"U","IDTIMBRATURA":582834,"actualH":"19:00","oreSTD":8,"tipoSTR":"lav"}
                }
            }*/

            $this->res[$tag]=$this->getDefaultDay($tag);

            if ($temp) {
                $this->res[$lastday]['blocks'][]=$temp;
                $temp=false;
            }

            if ($lastday!=$tag) {

                if ($lastday!="") {
                    $this->arrotondaBrogliaccio($lastday);
                }

                $lastday=$tag;
            }

            foreach ($t as $kh=>$h) {

                if (!$temp) {
                    //se non esiste un blocco attivo crealo ed aggiungici la timbratura in esame
                    $temp=new alanBlock($this->prefix,$this->res[$tag]['arrotondamento']);
                    $this->res[$tag]['minuti']+=$temp->add($h,$this->info['collaboratore']['ID_coll']);
                    $this->res[$tag]['oreSTD']=$h['oreSTD'];
                    $this->res[$tag]['tipoSTR']=$h['tipoSTR'];
                }

                else if ($h['VERSOO']=='E') {
                    //se la timbratura è in entrata (ed a questo punto esiste un blocco attivo)
                    //salva il blocco attivo e creane un altro allegandoci la timbratura
                    $this->res[$tag]['blocks'][]=$temp;
                    $temp=new alanBlock($this->prefix,$this->res[$tag]['arrotondamento']);
                    $this->res[$tag]['minuti']+=$temp->add($h,$this->info['collaboratore']['ID_coll']);
                    $this->res[$tag]['oreSTD']=$h['oreSTD'];
                    $this->res[$tag]['tipoSTR']=$h['tipoSTR'];
                }

                else {
                    //quindi la timbratura è in uscita
                    //se il blocco attivo ha già una timbratura in uscita salvalo e creane un altro
                    //altrimenti aggiungi la timbratura al blocco attivo
                    if ($temp->checkU()) {
                        $this->res[$tag]['blocks'][]=$temp;

                        $temp=new alanBlock($this->prefix,$this->res[$tag]['arrotondamento']);
                        $this->res[$tag]['minuti']+=$temp->add($h,$this->info['collaboratore']['ID_coll']);
                        $this->res[$tag]['oreSTD']=$h['oreSTD'];
                        $this->res[$tag]['tipoSTR']=$h['tipoSTR'];
                    }
                    else {
                        $this->res[$tag]['minuti']+=$temp->add($h,$this->info['collaboratore']['ID_coll']);
                        $this->res[$tag]['oreSTD']=$h['oreSTD'];
                        $this->res[$tag]['tipoSTR']=$h['tipoSTR'];
                    }
                }
            }
        }

        //l'ultimo blocco in sospeso
        if ($temp && $lastday!="") {
            $this->res[$lastday]['blocks'][]=$temp;
            $this->arrotondaBrogliaccio($lastday);
        }
    }

    function arrotondaBrogliaccio($tag) {

        if (!$this->actualR['indice'] || $tag>$this->actualR['limite_f'] || $tag<$this->actualR['limite_i']) {
            $this->selectRule($tag);
        }

        //valore a cui arrotondare i minuti (0,15,30)
        $rif=$this->regole[$this->actualR['indice']]['arrBrogliaccio'];

        //ore piene
        $h=floor($this->res[$tag]['minuti']/60);
        //resto in minuti
        $m=$this->res[$tag]['minuti']-($h*60);

        //numero di intervalli rif contenuti nel resto
        $res=($rif>0)?floor($m/$rif):1;

        $m=$res*$rif;

        $this->res[$tag]['arrotondato']=($h*60)+$m;
    }

    //scrittura a video delle timbrature lette
    function draw() {

        if (!$this->info['collaboratore']) return;

        //turno è un array "totCollDayTurni" fornito da intervallo

        $index=mainFunc::gab_tots($this->info['data_i']);
        $end=mainfunc::gab_tots($this->info['data_f']);

        while ($index<=$end) {

            $d=date('Ymd',$index);

            $color='black';
            $bak='white';
            if(!$this->info['turno'][$d]) $bak='#dddddd';
            else if ($this->info['turno'][$d]['nominale']==0 && $this->info['turno'][$d]['actual']==0) $bak='#dddddd';
            else if ($this->info['turno'][$d]['actual']==0) $color='orange';
            else if ($this->info['turno'][$d]['actualBro']<$this->info['turno'][$d]['nominale']) $color='blue';
            else if ($this->info['turno'][$d]['actualBro']>$this->info['turno'][$d]['nominale']) $color='green';

            echo '<div style="font-weight:bold;border: 1px solid black;margin-top: 2px;margin-bottom: 2px;padding: 2px;box-sizing: border-box;color:'.$color.';background-color:'.$bak.';min-height:44px;" >';

                $this->drawDay($d,$index);

            echo '</div>';
            
            //aggiorna stato collaboratore
            //ignora l'errore delle badgiate del giorno corrente in quanto potrebbero essere giustamente incomplete
            if ($this->res[$d]['stato']=='KO' && $d!=$this->today) $this->info['stato']='KO';
            else if ($this->res[$d]['stato']=='ALL' && $this->info['stato']!='KO') $this->info['stato']='ALL';
            
            $index=strtotime("+1 day",$index);
        }

        echo '<input id="'.$this->prefix.'_alan_collInfo_'.$this->info['collaboratore']['ID_coll'].'" type="hidden" data-lista="" data-idcoll="'.$this->info['collaboratore']['ID_coll'].'" data-iddip="'.$this->info['collaboratore']['IDDIP'].'" data-stato="'.$this->info['stato'].'" />';

        /*echo '<script type="text/javascript">';
            //è necessario metterle in fila perché è possibile che siano state aggiunte delle timbrature in fase di correzione
            //che avendo una numerazione non sequenziale le timbrature non sarebbero più in ordine cronologico
            foreach ($this->lista as $d=>$l) {
                ksort($this->lista[$d]);
            }
            echo 'var temp='.json_encode($this->lista).';';
            echo '$("#tpo_alan_'.$this->info['collaboratore']['ID_coll'].'").data("lista",temp);';
        echo '</script>';*/

    }

    function drawDay($d,$index) {

        if (!isset ($this->res[$d])) $this->res[$d]=$this->getDefaultDay($d);

        echo '<div>';

            echo '<div id="'.$this->prefix.'_alan_dayintest_'.$d.'_'.$this->info['collaboratore']['ID_coll'].'" style="display:inline-block;width:55%;vertical-align: top;">';

                echo '<div id="'.$this->prefix.'_alan_dayintestTag_'.$d.'_'.$this->info['collaboratore']['ID_coll'].'" >';

                    echo '<div style="display:inline-block;width:30%;">';
                        echo substr(mainFunc::gab_weektotag(date('w',$index)),0,3).' '.mainFunc::gab_todata($d);
                    echo '</div>';

                    echo '<div style="display:inline-block;width:30%;">';
                        echo 'Nominale:';
                        if ($this->info['turno'][$d]) {
                            echo '<span style="margin-left:5px;" >'.number_format($this->info['turno'][$d]['nominale']/60,2,'.','').'</span>';
                        }
                        else {
                            echo '<span style="margin-left:5px;" >'.number_format(0,2,'.','').'</span>';
                        }
                    echo '</div>';

                    echo '<div style="display:inline-block;width:40%;">';
                        echo 'Previsto:';
                        if ($this->info['turno'][$d]) {
                            echo '<span style="margin-left:5px;" >'.number_format($this->info['turno'][$d]['actualBro']/60,2,'.','').'</span>';
                        }
                        else {
                            echo '<span style="margin-left:5px;" >'.number_format(0,2,'.','').'</span>';
                        }

                        if ($this->info['turno'][$d] && ($this->info['turno'][$d]['nominale']==0 || $this->res[$d]['arrotondato']>$this->info['turno'][$d]['actualBro']) ) {
                            echo '<img style="position:relative;width:16px;height:16px;margin-left:15px;margin-top:2px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/alan/img/piu.png" onclick="window._'.$this->prefix.'_alan.tempoCallEvent(\''.$this->info['collaboratore']['ID_coll'].'\',\''.$d.'\',\'extra\');" />';
                        }
                        if ($this->info['turno'][$d] && $this->info['turno'][$d]['actualBro']>0) {
                            echo '<img style="position:relative;width:16px;height:16px;margin-left:15px;margin-top:2px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/alan/img/meno.png" onclick="window._'.$this->prefix.'_alan.tempoCallEvent(\''.$this->info['collaboratore']['ID_coll'].'\',\''.$d.'\',\'permesso\');" />';
                        }
                    echo '</div>';

                echo '</div>';

                $temp="";
                if ($this->info['turno'][$d]) {
                    foreach ($this->info['turno'][$d]['turnoNominale'] as $t) {
                        $temp.=$t['i'].'/'.$t['f'].' - ';
                    }
                }

                if ($temp!="") {

                    echo '<div style="color: #777777;" >';
                        echo 'Turno:';
                        
                        echo '<span style="margin-left:5px;" >'.substr($temp,0,-2).'</span>';
                    echo '</div>';

                }

            echo '</div>';

            echo '<div style="display:inline-block;width:45%;font-weight:normal;">';
                
                //EVENTI
                if (!is_null($this->parent)) {
                    $this->parent->drawBadgeEvents($this->info['collaboratore']['ID_coll'],$d);
                }

            echo '</div>';

        echo '</div>';

        echo '<div id="alanContainer_'.$this->info['collaboratore']['ID_coll'].'_'.$d.'" style="font-weight:normal;color:black;" data-tag="'.$d.'" data-idcoll="'.$this->info['collaboratore']['ID_coll'].'" data-iddip="'.$this->info['collaboratore']['IDDIP'].'" >';

            $this->drawContainer($d);

        echo '</div>';

    }

    function drawContainer($d) {

        if (!isset ($this->res[$d])) $this->res[$d]=$this->getDefaultDay($d);

        echo '<div style="display:inline-block;width:60%;">';
            echo '<div style="width:100%;">';
                echo '<div style="display:inline-block;width:25%;text-align:center;">Entrata</div>';
                echo '<div style="display:inline-block;width:25%;text-align:center;">Uscita</div>';
                echo '<div style="display:inline-block;width:15%;text-align:center;">Ore</div>';
                echo '<div style="display:inline-block;width:15%;text-align:center;">Forza</div>';
                echo '<div style="display:inline-block;width:20%;text-align:center;"></div>';
            echo '</div>';

            $this->drawBlocks($d,$this->res[$d]['arrotondato'],(($this->info['turno'][$d])?$this->info['turno'][$d]['actualBro']:0));
        echo '</div>';

        echo '<div style="display:inline-block;width:40%;vertical-align:top;">';

            echo '<div style="text-align:center;vertical-align:top;">';

                echo '<div>Ore calcolate: ';

                    echo '<span style="font-weight:bold;';
                        if ($this->res[$d]['stato']=='OK') echo 'color:black;';
                        else echo 'color:red;';
                    echo '" >';

                    if ($this->res[$d]['stato']=='OK' || $this->res[$d]['stato']=='ALL') {
                        echo number_format($this->res[$d]['arrotondato']/60,2,',','');

                        if ($this->info['turno'][$d] && $this->info['turno'][$d]['actualBro']>0 && $this->res[$d]['arrotondato']==0) {
                            echo '<button style="margin-left:5px;" onclick="window._'.$this->prefix.'_alan.addK(\''.$d.'\',\''.$this->info['collaboratore']['IDDIP'].'\',\''.$this->info['collaboratore']['ID_coll'].'\');">';
                                echo '<img style="position:relative;width:10px;height:10px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/alan/img/piu.png" />';
                                echo '<span style="margin-left:3px;font-size:0.8em;">Timbratura</span>';
                            echo '</button>';
                        }
                    }
                    else echo 'errore';

                    echo '</span>';

                echo '</div>';

                echo '<div>';

                    echo 'ore standard:&nbsp;<span style="font-weight:bold;">'.$this->res[$d]['oreSTD'].'</span>';
                    echo ' (straordinario: '.$this->res[$d]['tipoSTR'].')';

                echo '</div>';

            echo '</div>';

        echo '</div>';

        echo '<input id="'.$this->prefix.'_alan_dayinfo_'.$d.'_'.$this->info['collaboratore']['ID_coll'].'" type="hidden" data-idcoll="'.$this->info['collaboratore']['ID_coll'].'" data-tag="'.$d.'" data-nominale="'.(($this->info['turno'][$d])?$this->info['turno'][$d]['nominale']:'').'" data-actual="'.(($this->info['turno'][$d])?$this->info['turno'][$d]['actualBro']:'').'" data-calc="'.$this->res[$d]['arrotondato'].'" data-stato="'.$this->res[$d]['stato'].'" />';
    }

    function drawBlocks($d,$arrotondato,$previsto) {

        foreach ($this->res[$d]['blocks'] as $kb=>$b) {

            $delta=$arrotondato-$previsto;

            echo $b->draw($delta);

            $tempst=$b->getStato();

            if ($tempst!='OK') $this->res[$d]['stato']='KO';
        }

        //se le timbrature sono corrette ma la somma del tempo è diversa da quello che ci aspettiamo segnale errore di allinemento
        if ($d!=$this->today) {
            if ($this->res[$d]['arrotondato']!=$this->res[$d]['actualBro'] && $this->res[$d]['stato']!='KO') $this->res[$d]['stato']='ALL';
        }
    }

    function controllaUscita($giorno,$orario,$IDDIP) {
        //ritorna una timbratura in qualsiasi verso maggiore o uguale all'orario fornito come argomento o FALSE

        $ret=false;

        $arr=array(
            "data"=>$giorno.' '.$orario,
            "IDDIP"=>$IDDIP
        );

        $this->galileo->clearQuery();
        $this->galileo->clearQueryOggetto('default','alan');

        $this->galileo->executeGeneric('alan','controllaUscita',$arr,'');
        $result=$this->galileo->getResult();
        if($result) {
			$fetID=$this->galileo->preFetch('alan');
			while ($row=$this->galileo->getFetch('alan',$fetID)) {
                $ret=$row;
            }
        }

        return $ret;

    }

}

?>