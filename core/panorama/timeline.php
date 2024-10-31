<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/core/timeline/timeline.php');

class panoramaTimeline extends nebulaTimeline {

    function __construct($res,$subs) {

        parent::__construct($res);

        $this->tl_reset($subs);

    }

    function includiProprietario($index,$info) {
        /*$info=array(
            'subrep'=>""
        );*/

        //il metodo di default è settato su bool e permette i valori di quantità 1 o 0
        //per il singolo collaboratore va bene ma non per le timeline riepilogative
        $this->includiDefault($index,'nominale',true);
        $this->includiDefault($index,'actual',true);
        $this->includiDefault($index,'actualBro',true);

        if ($info['subrep']!="") {
            $this->tl[$index]['subs']['agenda'][$info['subrep']]['flag']=true;

            //#################
            //01.06.2021
            if (!isset($this->tl[$index]['subs']['agenda'][$info['subrep']]['qta'])) $this->tl[$index]['subs']['agenda'][$info['subrep']]['qta']=0;
            //#################

            $this->tl[$index]['subs']['agenda'][$info['subrep']]['qta']++;

            $this->tl[$index]['subs']['schemi'][$info['skema']]=$info['blocco'];
        }

        //INCLUDI DEFAULT fa già l'operazione di "trim"

    }

    function escludiProprietario($index,$info) {
        
        //il metodo di default è settato su bool e permette i valori di quantità 1 o 0
        //per il singolo collaboratore va bene ma non per le timeline riepilogative
        $this->escludiDefault($index,'nominale',true);

    }

    function addTl($tl) {
        //somma due TIMELINE - usato nelle timeline riepilogative (NON del singolo collaboratore)

        foreach ($tl as $index=>$i) {

            if ($i['flag']) $this->tl[$index]['flag']=true;

            if ($i['subs']['nominale']['flag']) {
                $this->tl[$index]['subs']['nominale']['flag']=true;
                $this->tl[$index]['subs']['nominale']['qta']+=$i['subs']['nominale']['qta'];
            }

            if ($i['subs']['actual']['flag']) {
                $this->tl[$index]['subs']['actual']['flag']=true;
                $this->tl[$index]['subs']['actual']['qta']+=$i['subs']['actual']['qta'];
                $this->setTrim($index);
            }

            if ($i['subs']['actualBro']['flag']) {
                $this->tl[$index]['subs']['actualBro']['flag']=true;
                $this->tl[$index]['subs']['actualBro']['qta']+=$i['subs']['actualBro']['qta'];
            }

            //GESTIRE EVENTI
            foreach ($i['subs']['eventi'] as $classe=>$e) {
                //se il tipo di evento esiste nell'oggetto attuale sommalo altrimenti inseriscilo
                //il parametro FLAG di ogni record viene lasciato su TRUE come impostato in fase di creazione
                if ( isset($this->tl[$index]['subs']['eventi'][$classe]) ) {

                    foreach ($e as $tipo=>$t) {

                        if (isset($this->tl[$index]['subs']['eventi'][$classe][$tipo]) ) {
                            $this->tl[$index]['subs']['eventi'][$classe][$tipo]['qta']+=$t['qta'];
                        } 
                        else {
                            $this->tl[$index]['subs']['eventi'][$classe][$tipo]=$t;
                        }
                    }
                }
                else {
                    $this->tl[$index]['subs']['eventi'][$classe]=$e;
                }
            }
            //////////////////////////

            foreach ($i['subs']['schemi'] as $sk=>$blocco) {
                if ( !in_array($sk,$this->tl[$index]['subs']['schemi']) ) $this->tl[$index]['subs']['schemi'][]=$sk;
            }

            if (isset($i['subs']['agenda'])) {
                foreach ($i['subs']['agenda'] as $sub=>$s) {
                    if ($s['flag'] && isset($this->tl[$index]['subs']['agenda'][$sub]['qta'])) {
                        $this->tl[$index]['subs']['agenda'][$sub]['flag']=true;
                        $this->tl[$index]['subs']['agenda'][$sub]['qta']+=$s['qta'];
                    }
                }
            }
        }

    }

    function effectPeriodo($tipo) {
        //opera sul singolo collaboratore e viene chiamato dall'esterno
        foreach ($this->tl as $index=>$i) {

            $this->escludiDefault($index,'actual',true);

            //escludi subs agenda
            if (isset($this->tl[$index]['subs']['agenda'])) {
                foreach ($this->tl[$index]['subs']['agenda'] as $sub=>$s) {
                    $this->tl[$index]['subs']['agenda'][$sub]['flag']=false;
                    $this->tl[$index]['subs']['agenda'][$sub]['qta']=0;
                }
            }


            //actual BRO non viene aggiornato se l'assenza è per un CORSO
            if ($tipo=='F' || $tipo=='M') {
                $this->escludiDefault($index,'actualBro',true);
            }
            //$this->tl[$index]['subs']['actual']['flag']=false;
            //$this->tl[$index]['subs']['actual']['qta']=0;

            if ($this->tl[$index]['subs']['nominale']['flag']) {

                $this->tl[$index]['subs']['eventi']['periodo'][$tipo]=array(
                    "flag"=>true,
                    "qta"=>1
                );
                
            }
        }
    }

    function effectPermesso($da,$a,$tipo) {
        //opera sul singolo collaboratore

        $this->interval['i']=mainFunc::gab_stringtomin($da);
		$this->interval['f']=mainFunc::gab_stringtomin($a);

        //verifica se nell'intervallo c'è almeno un actual TRUE
        //serve per la validazione della GRIGLIA degli eventi
        if (!$this->chkTrueSub('actual')) return false;
        
        foreach ($this->tl as $k=>$t) {

			if ( $this->chkIndex($k) ) {

                $this->escludiDefault($k,'actual',true);

                //escludi subs agenda
                if (isset($this->tl[$k]['subs']['agenda'])) {
                    foreach ($this->tl[$k]['subs']['agenda'] as $sub=>$s) {
                        $this->tl[$k]['subs']['agenda'][$sub]['flag']=false;
                        $this->tl[$k]['subs']['agenda'][$sub]['qta']=0;
                    }
                }

                //actual BRO non viene aggiornato se l'assenza è per SERVIZIO
                if ($tipo=='P') {
                    $this->escludiDefault($k,'actualBro',true);
                }

                if ($t['subs']['nominale']['flag']) {

                    $this->tl[$k]['subs']['eventi']['permesso'][$tipo]=array(
                        "flag"=>true,
                        "qta"=>1
                    );   
                }
            }
        
        }

        return true;
    }

    function effectExtra($da,$a,$tipo) {
        //questo metodo ha effetto su ACTUAL ma non modifica l'AGENDA

        $this->interval['i']=mainFunc::gab_stringtomin($da);
		$this->interval['f']=mainFunc::gab_stringtomin($a);

        //verifica se nell'intervallo c'è almeno un nominale FALSE
        //serve per la validazione della GRIGLIA degli eventi
        if ($this->chkFalseSub('nominale')) return false;

		foreach ($this->tl as $k=>$t) {

			if ( $this->chkIndex($k) ) {
                
                //SOLO se è veramente EXTRA (potrebbe succedere se viene cambiato lo schema ma rimane settato l'extra)
                if (!$t['subs']['nominale']['flag']) {
                    //anche se potrebbe già essere stato settato nel ciclo normale (se il DB dell'EXTRA aveva valorizzato Panorama e Skema)
                    $this->includiDefault($k,'actual',true);

                    /*includi subs agenda
                    il 14.01.2022 è stato tolto perché aggiungeva tutti i subrep e non SOLO quelli dello schema del collaboratore interessato
                    si potrebbe verificare la possibilità di interessare i subrep o ricric interessati attraverso la funzione tipo "getCollTot('subs',$this->param['wsp_officina'],$coll['ID_coll']);"
                    e verificando i subrep per i quali il collaboratore è presente ma per il momento lasciamo così.

                    if (isset($this->tl[$k]['subs']['agenda'])) {
                        foreach ($this->tl[$k]['subs']['agenda'] as $sub=>$s) {
                            $this->tl[$k]['subs']['agenda'][$sub]['flag']=true;
                            $this->tl[$k]['subs']['agenda'][$sub]['qta']=1;
                        }
                    }*/

                    $this->includiDefault($k,'actualBro',true);

                    $this->tl[$k]['subs']['eventi']['extra'][$tipo]=array(
                        "flag"=>true,
                        "qta"=>1
                    );
                }
            }
		}

        return true;    

    }

    function effectSposta($da,$a,$sub_a) {
        //questo metodo influenza solo i subs in agenda

        $this->interval['i']=mainFunc::gab_stringtomin($da);
		$this->interval['f']=mainFunc::gab_stringtomin($a);

        //verifica se nell'intervallo c'è almeno un actual TRUE
        //serve per la validazione della GRIGLIA degli eventi
        if (!$this->chkTrueSub('actual')) return false;

        foreach ($this->tl as $k=>$t) {

			if ( $this->chkIndex($k) ) {

                if (isset($t['subs']['agenda'])) {
                    foreach ($t['subs']['agenda'] as $sub=>$s) {
                        
                        if ($sub!=$sub_a) {
                            if ($this->tl[$k]['subs']['agenda'][$sub]['flag']) {

                                if ($this->tl[$k]['subs']['agenda'][$sub]['qta']>0) {
                                    $this->tl[$k]['subs']['agenda'][$sub]['qta']--;
                                }

                                if ($this->tl[$k]['subs']['agenda'][$sub]['qta']==0) {
                                    $this->tl[$k]['subs']['agenda'][$sub]['flag']=false;
                                }
                            }
                        }
                        else {
                            $this->tl[$k]['subs']['agenda'][$sub]['flag']=true;
                            if (!isset($this->tl[$k]['subs']['agenda'][$sub]['qta'])) $this->tl[$k]['subs']['agenda'][$sub]['qta']=0;
                            $this->tl[$k]['subs']['agenda'][$sub]['qta']++;
                        }
                    }
                }
            }
        
        }

        return true;
    }

    function getPresenza() {

        $a=array(
            "nominale"=>0,
            "actual"=>0,
            "actualBro"=>0
        );

        foreach ($this->tl as $index=>$i) {
            $a['nominale']+=($i['subs']['nominale']['qta']*$this->res);
            $a['actual']+=($i['subs']['actual']['qta']*$this->res);
            $a['actualBro']+=($i['subs']['actualBro']['qta']*$this->res);
        }

        return $a;

    }

    function getTurno() {

        $a=array(
            "nominale"=>0,
            "actual"=>0,
            "actualBro"=>0,
            "turno"=>array(),
            "turnoNominale"=>array()
        );

        $flag=false;
        $flagnom=false;
        $temp=array(
            "i"=>"",
            "f"=>""
        );
        $nom=array(
            "i"=>"",
            "f"=>""
        );

        foreach ($this->tl as $index=>$i) {
            $a['nominale']+=($i['subs']['nominale']['qta']*$this->res);
            $a['actual']+=($i['subs']['actual']['qta']*$this->res);
            $a['actualBro']+=($i['subs']['actualBro']['qta']*$this->res);

            //se NON si è dentro ad un turno ma il blocco è true --> inizia turno
            if (!$flag && $i['flag']) {
                $temp['i']=$i['tag'];
                $temp['f']=mainFunc::gab_mintostring($i['end']);
                $flag=true;
            }
            //se si è dentro ad un turno che continua
            elseif ($flag && $i['flag']) {
                $temp['f']=mainFunc::gab_mintostring($i['end']);
            }
            //se si è dentro ad un turno ma si incontra un blocco false --> chiudi il turno
            elseif ($flag && !$i['flag']) {
                $a['turno'][]=$temp;
                $temp=array(
                    "i"=>"",
                    "f"=>""
                );
                $flag=false;
            }


            //NOMINALE se NON si è dentro ad un turno ma il blocco è true --> inizia turno
            if (!$flagnom && $i['subs']['nominale']['qta']>0) {
                $nom['i']=$i['tag'];
                $nom['f']=mainFunc::gab_mintostring($i['end']);
                $flagnom=true;
            }
            //NOMINALE se si è dentro ad un turno che continua
            elseif ($flagnom && $i['subs']['nominale']['qta']>0) {
                $nom['f']=mainFunc::gab_mintostring($i['end']);
            }
            //NOMINALE se si è dentro ad un turno ma si incontra un blocco false --> chiudi il turno
            elseif ($flagnom && $i['subs']['nominale']['qta']==0) {
                $a['turnoNominale'][]=$nom;
                $nom=array(
                    "i"=>"",
                    "f"=>""
                );
                $flagnom=false;
            } 
        }

        if ($flag) $a['turno'][]=$temp;
        if ($flagnom) $a['turnoNominale'][]=$nom;

        return $a;

    }

    function getEventi() {

        $a=array();

        foreach ($this->tl as $index=>$i) {

            foreach ($i['subs']['eventi'] as $classe=>$e) {

                foreach ($e as $tipo=>$t) {
                    if (!isset($a[$classe][$tipo])) {
                        $a[$classe][$tipo]['qta']=0;
                    }
                    
                    $a[$classe][$tipo]['qta']+=$t['qta']*$this->res;
                }

            }
        }

        return $a;
    }

    function drawProprietario($config) {
        /*
        titolo="",
        range=array(min,max),
        */

        $this->setRange($config['range']);

        $tempry=array(
            "titolo"=>$config['titolo']
        );
        
        $this->drawHead($tempry);

        /////////////////////////////////////
        /*
        "sub"=>""
		"zeros"=>"GRAY",
		"scala"=>array(
			"min"=>"violet",
			"0"=>"red",
			"1"=>"orange",
			"25"=>"yellow",
			"50"=>"ocra",
			"75"=>"green"
		),
		"limite"=>"PERC",
		"legenda_tag"=>"",
		"occupazione"=>false
        */

        if (isset($this->tl["0"]['subs']['agenda'])) {
            
            foreach($this->tl["0"]['subs']['agenda'] as $ks=>$s) {

                $tempry=array(
                    "sub"=>$ks,
                    "scala"=>array(
                        "min"=>'violet',
                        "0"=>'red',
                        "1"=>'orange',
                        "2"=>'ocra',
                        "3"=>'yellow',
                        "4"=>'green'
                    ),
                    "limite"=>'VAL',
                    "valore_decimal"=>0,
                    "legenda_tag"=>$ks
                );

                $this->drawSubs("agenda.".$ks,$tempry);
            }
        }
    }

    function avalonGrid($occupazione) {

        $this->sezioni['totale']='PAN';
        $this->sezioni['totale_tag']='SI';

        foreach($this->tl["0"]['subs']['agenda'] as $ks=>$s) {

            $tempry=array(
                "sub"=>$ks,
                "legenda_tag"=>$ks,
                "valore_decimal"=>0,
                "occupazione"=>true
            );

            $info=$this->default;

            foreach ($info as $k=>$v) {
                if ( array_key_exists($k,$tempry) ) $info[$k]=$tempry[$k];
            }

            $temprif=explode('.',"agenda.".$ks);

            $totDispo=0;
            $totOccu=0;

            foreach ($this->tl as $index=>$o) {

                if ($index>$this->range['max']) break;
                if ($index<$this->range['min']) continue;

                $blocco=&$o['subs'][$temprif[0]];

                if ( isset($temprif[1]) ) $blocco=&$blocco[$temprif[1]];

                /*se occupazione è abilitato ed esiste l'array che la quantifica
                if ($info['occupazione'] && isset($this->occupazione[$info['sub']][$index]) ) {
                    $totOccu+=$this->occupazione[$info['sub']][$index];
                }*/

                $totDispo+=$blocco['qta'];
            }

            $totDispo=($totDispo*$this->res)/60;
            //$totOccu=($totOccu*$this->res)/60;
            //l'occupazione viene passata come argomento
            if (isset($occupazione[$ks])) {
                $totOccu=$occupazione[$ks];
            }
            else $totOccu=0;

            echo '<div style="position:relative;min-height:'.$this->css['line_h'].';width:90%;margin-left:5%;background-size:cover;background-repeat:no-repeat;margin-top:1px;margin-bottom:1px;';
                
                //eventualmente background-image
                if ($this->sezioni['totale_bk']=='SI') {
                    if ($totDispo>0) {
                        if ($totOccu<$totDispo) {
                            echo "background-image:url(http://".$_SERVER['SERVER_ADDR']."/nebula/apps/tempo/img/sfondo_perc.png);";
                        }
                        else {
                            echo "background-color:#fb82ec;";
                        }
                    }
                    else {
                        echo "background-color:#777777;";
                    }
                }

            echo '">';

                //cover bianca
                if ($this->sezioni['totale_bk']=='SI') {
                    echo '<div style="position:absolute;right:0px;top:0px;height:100%;background-color:#ffffffaa;z-index:2;';
                        if ($totDispo==0) $temperc=100;
                        else $temperc=round((1-($totOccu/$totDispo))*100);
                        echo 'width:'.($temperc>100?"100":$temperc).'%;';
                    echo '">';
                    echo '</div>';
                }

                echo '<div style="position:relative;width:100%;height:100%;font-size:'.$this->css['font_size'].';padding:2px;box-sizing:border-box;z-index:3;">';

                    echo '<div style="text-align:center;font-weight:bold;font-size:0.9em;">';

                        if ($this->sezioni['totale_tag']=='SI') {
                            echo '<div>';
                                echo $info['legenda_tag'];
                            echo '</div>';
                        }

                        echo '<div>';
                            if ($this->sezioni['totale']=='DELTA') echo number_format($totDispo-$totOccu,$info['valore_decimal'],".","");
                            if ($this->sezioni['totale']=='PAN') echo number_format($totOccu,$info['valore_decimal'],".","")." / ".number_format($totDispo,$info['valore_decimal'],".","");
                        echo '</div>';

                    echo '</div>';

                echo '</div>';

            echo '</div>';

        }
            
    }

    function getFineTurno($min) {

        $ret=1440;

        $turno=false;

        foreach ($this->tl as $index=>$t) {

            if ($min>$t['end']) continue;

            //se il turno è finito (dopo che è iniziato)
            if (!$t['flag'] && $turno) {
                $ret=$index;
                break;
            }

            //se si è in un truno valido allora segna che la prossima fine è valida
            if ($t['flag']) $turno=true;
        }

        //se non si è mai incrociato un periodo valido NON chiudere la marcarura
        if (!$turno) return false;
        else return $ret;
    }

}