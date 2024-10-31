<?php

//classe che si interfaccia con il DB delle timbrature del badge e le mette a disposizione per l'elaborazione.

class nebulaAlan {

    protected $info=array(
        "collaboratore"=>false,
        "data_i"=>"",
        "data_f"=>""
    );

    protected $lista=array();

    protected $regole=array();

    protected $actualR=array(
        "indice"=>false,
        "limite_i"=>"",
        "limite_f"=>""
    );

    protected $parent;
    protected $galileo;

    function __construct($parent,$galileo) {

        //parent si riferisce alla classe TEMPO che instanzia ALAN
        //e serve per disegnare gli eventi ma non entra nel calcolo
        $this->parent=$parent;
        $this->galileo=$galileo;

        //recupera da ALAN_regole
        //TEST
        $this->regole=array(
            array(
                "data_i"=>"20210101",
                "data_f"=>"21001231",
                "arrotondamento"=>15,
                "tolleranzaE"=>5,
                "tolleranzaU"=>0,
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
        //ENDTEST
        
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
    function setCollaboratore($c) {
        $this->info['collaboratore']=$c;

        $this->lista=array();

        $this->actualR=array(
            "indice"=>false,
            "limite_i"=>"",
            "limite_f"=>""
        );
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

                $ind=($row['actualH']!="")?mainFunc::gab_stringtomin($row['actualH']):mainFunc::gab_stringtomin($row['h']);

                $row['actualM']=$ind;

                //se due marcature avvengono nello stesso arrotondamento la seconda sovrascrive la prima
                $this->lista[$row['d']][$ind]=$row;
            }
        }
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

    //scrittura a video delle timbrature lette
    function draw($turno) {

        if (!$this->info['collaboratore']) return;

        //turno è un array "totCollDayTurni" fornito da intervallo

        $index=mainFunc::gab_tots($this->info['data_i']);
        $end=mainfunc::gab_tots($this->info['data_f']);

        while ($index<=$end) {

            $d=date('Ymd',$index);

            $color='black';
            $bak='white';
            if(!$turno[$d]) $bak='#dddddd';
            else if ($turno[$d]['nominale']==0 && $turno[$d]['actual']==0) $bak='#dddddd';
            else if ($turno[$d]['actual']==0) $color='orange';
            else if ($turno[$d]['actual']<$turno[$d]['nominale']) $color='blue';
            else if ($turno[$d]['actual']>$turno[$d]['nominale']) $color='green';

            echo '<div style="font-weight:bold;border: 1px solid black;margin-top: 2px;margin-bottom: 2px;padding: 2px;box-sizing: border-box;color:'.$color.';background-color:'.$bak.';min-height:44px;" >';

                echo '<div>';

                    echo '<div id="alan_dayintest_'.$d.'_'.$this->info['collaboratore']['ID_coll'].'" style="display:inline-block;width:55%;vertical-align: top;">';

                        echo '<div>';

                            echo '<div id="alan_dayinfo_'.$d.'_'.$this->info['collaboratore']['ID_coll'].'" style="display:inline-block;width:30%;" data-idcoll="'.$this->info['collaboratore']['ID_coll'].'" data-tag="'.$d.'" data-nominale="'.(($turno[$d])?$turno[$d]['nominale']:'').'" data-actual="'.(($turno[$d])?$turno[$d]['actual']:'').'" data-calc="0" >';
                                echo substr(mainFunc::gab_weektotag(date('w',$index)),0,3).' '.mainFunc::gab_todata($d);
                            echo '</div>';

                            echo '<div style="display:inline-block;width:35%;">';
                                echo 'Nominale:';
                                if ($turno[$d]) {
                                    echo '<span style="margin-left:5px;" >'.number_format($turno[$d]['nominale']/60,2,'.','').'</span>';
                                }
                            echo '</div>';

                            echo '<div style="display:inline-block;width:35%;">';
                                echo 'Previsto:';
                                if ($turno[$d]) {
                                    echo '<span style="margin-left:5px;" >'.number_format($turno[$d]['actual']/60,2,'.','').'</span>';
                                }
                            echo '</div>';

                        echo '</div>';

                        $temp="";
                        if ($turno[$d]) {
                            foreach ($turno[$d]['turno'] as $t) {
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
                        $this->parent->drawBadgeEvents($this->info['collaboratore']['ID_coll'],$d);

                    echo '</div>';

                echo '</div>';

                echo '<div id="alanContainer_'.$this->info['collaboratore']['ID_coll'].'_'.$d.'" style="font-weight:normal;color:black;" >';

                echo '</div>';

            echo '</div>';  

            $index=strtotime("+1 day",$index);
        }

        echo '<input id="tpo_alan_'.$this->info['collaboratore']['ID_coll'].'" type="hidden" data-lista="" data-idcoll="'.$this->info['collaboratore']['ID_coll'].'" data-iddip="'.$this->info['collaboratore']['IDDIP'].'" data-badgeindex="'.$this->info['collaboratore']['badgeIndex'].'" />';

        echo '<script type="text/javascript">';
            //è necessario metterle in fila perché è possibile che siano state aggiunte delle timbrature in fase di correzione
            //che avendo una numerazione non sequenziale le timbrature non sarebbero più in ordine cronologico
            foreach ($this->lista as $d=>$l) {
                ksort($this->lista[$d]);
            }
            echo 'var temp='.json_encode($this->lista).';';
            echo '$("#tpo_alan_'.$this->info['collaboratore']['ID_coll'].'").data("lista",temp);';
        echo '</script>';

    }

}

?>