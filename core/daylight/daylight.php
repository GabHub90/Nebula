<?php

abstract class nebulaDaylight {

    //l'oggetto è studiato per visualizzare 31 giorni

    protected $label="";

    protected $config=array(
        "title"=>"",
        "today"=>true,
        "todayColor"=>"#ffe6d5",
        "divWidth"=>'100%',
        "labels"=>true,
        "mese"=>true,
        "subs"=>true,
        "totali"=>false,
        "foot"=>false,
        "fill"=>true,
        "info"=>array()
    );

    protected $mesi=array();

    protected $dl=array(
        "giorni"=>0,
        "first"=>"",
        "last"=>"",
        "lista"=>array()
    );

    //ho seguito un approccio differente da "timeline" per lasciare maggiore libertà
    //I subs non vengono agganciati ai giorni ma al loro interno contengono l'array dei giorni
    protected $subs=array();

    //array del modello del totale
    protected $totali=array();
    //valori totali overall dell'oggetto
    protected $tot=array();

    function __construct($label) {
        
        $this->label=$label;
    }

    function setConfig($indice,$valore) {
        $this->config[$indice]=$valore;
    }

    function setInfo($a) {

        foreach ($a as $k=>$v) {
            $this->config['info'][$k]=$v;
        }
    }

    function getDays() {
        return $this->dl;
    }

    function loadDays($a) {
        //l'array accettato è quello che si ottiene da CALENDARIO - CheckDay
        //"20210924":{"tag":"20210924","wd":"5","festa":0,"chiusura":1,
        //      "chi":{"ID":45,"anno":"2021","mese":"09","giorno":"24","nome":"Patrono","tipo":"T","reparto":"AZ","ora_i":"00:00","ora_f":"23:59","rep_exc":"PAS,","parent":""},
        //      "testo":"Patrono","chk":"CH"},
        
        $am="";

        foreach ($a as $tag=>$t) {

            if ($am!=substr($tag,0,6)) {

                $am=substr($tag,0,6);
                $this->mesi[$am]=0;
            }

            $this->dl['lista'][$tag]=array(
                "info"=>$t
            );

            $this->mesi[$am]++;
            $this->dl['giorni']++;

            if ($this->dl['first']=="") $this->dl['first']=$tag;
            $this->dl['last']=$tag;

        }

    }

    function loadSub($label,$a) {
        $this->subs[$label]=$a;
    }

    function loadTotali($a) {

        $this->totali=$a;
        $this->tot=$a;
        $this->config['totali']=true;
    }

    function draw() {

        if ($this->config['title']!="") {
            echo '<div style="font-weight:bold;margin-top:5px;">';
                echo $this->config['title'];
            echo '</div>';
        }

        echo '<div style="white-space: nowrap;">';

            echo '<div style="display:inline-block;width:'.$this->config['divWidth'].';vertical-align:top;">';

                echo '<table style="width:100%;border-space:2px;margin-top:5px;">';

                    $w=100;
                    if ($this->config['labels']) $w-=10;

                    echo '<colgroup>';

                        if ($this->config['labels']) {
                            echo '<col span="1" style="width:10%;" />';
                        }

                        //echo '<col span="31" style="width:'.number_format($w/31,2,'.','').'%;" >';
                        echo '<col span="'.$this->dl['giorni'].'" style="width:'.number_format($w/$this->dl['giorni'],2,'.','').'%;" />';

                    echo '</colgroup>';

                    //mesi
                    echo '<tr>';

                        if ($this->config['labels']) {
                            echo '<th></th>';
                        }

                        $counter=0;

                        foreach ($this->mesi as $am=>$x) {

                            $counter+=$x;

                            echo '<th colspan="'.$x.'" style="text-align:center;border:1px solid black;background-color:#dddddd;" >';
                                $txt=mainFunc::gab_monthtotag(((int)substr($am,4,2))-1);
                                $txt=($x>4)?$txt." ".substr($am,0,4):substr($txt,0,3)." ".substr($am,2,2);
                                echo $txt;
                            echo '</th>';
                        }

                        //serve per arrotondare a 31 giorni il mese (Esempio Brogliaccio)
                        if ($this->config['fill'] && $counter<31) {
                            echo '<th colspan="'.(31-$counter).'" style="text-align:center;border:1px solid black;background-color:#dddddd;" ></th>';
                        }

                    echo '</tr>';

                    //giorni
                    echo '<tr>';

                        if ($this->config['labels']) {
                            echo '<th></th>';
                        }

                        $counter=0;

                        $h=24;

                        $today=date('Ymd');

                        foreach ($this->dl['lista'] as $tag=>$l) { 

                            $index=mainFunc::gab_tots($tag);
                            $counter++;

                            echo '<th style="text-align:center;border:1px solid black;font-size:0.6em;height:'.$h.'px;';

                                if ($l['info']['wd']==0 || $l['info']['festa']==1) echo 'color:red;';
                                elseif ($l['info']['chiusura']==1) echo 'color:violet;';

                                if ($this->config['today'] && $tag==$today) {
                                    echo 'background-color:'.$this->config['todayColor'].';';
                                }

                            echo '" >';

                                echo '<div>';
                                    echo substr(mainFunc::gab_weektotag($l['info']['wd']),0,2);
                                echo '</div>';

                                echo '<div>';
                                    echo substr($tag,6,2);
                                echo '</div>';

                            echo '</th>';
                        }

                        //serve per arrotondare a 31 giorni il mese (Esempio Brogliaccio)
                        if ($this->config['fill']) {
                            while ($counter<31) {
                                echo '<th style="background-color:#cccccc;"></th>';
                                $counter++;
                            }
                        }

                    echo '</tr>';

                    if ($this->config['subs']) {
                    
                        //i subs vengono scritti in una unica ROW
                        echo '<tr>';
                            $this->drawSubs();
                        echo '</tr>';

                    }


                echo '</table>';

                if ($this->config['foot']) {
                    $this->drawFoot();
                }

            echo '</div>';

            if ($this->config['totali']) {

                $this->drawTotali();
            }
        
        echo '</div>';

    }

    //all'occorrenza può essere sovrascritta
    function drawTotali() {

        $h=26;

        echo '<div style="display:inline-block;vertical-align:top;">';
        
            echo '<table style="border-space:2px;margin-top:5px;width:max-content;">';

                echo '<colgroup>';

                    echo '<col span="'.count($this->tot).'" style="width:60px;" >';

                echo '</colgroup>';

                echo '<tr>';
                    echo '<th colspan="'.count($this->tot).'" style="text-align:center;border:1px solid black;background-color:#e2efff;" >Totali</th>';
                echo '</tr>';

                echo '<tr>';
                    foreach ($this->tot as $ktot=>$t) {
                        echo '<th style="text-align:center;border:1px solid black;height:'.$h.'px;font-size:0.7em;" >';
                            echo substr($t['titolo'],0,8);
                        echo '</th>';
                    }
                echo '</tr>';

                echo '<tr>';
                    foreach ($this->tot as $ktot=>$t) {
                        echo '<td id="" style="text-align:center;height:'.$h.'px;font-size:0.7em;" >';
                            echo number_format($t['valore']/60,2,'.','');
                        echo '</td>';
                    }
                echo '</tr>';

            echo '</table>';
        echo '</div>';
    }

    function exportHead() {

        $txt="";
        $counter=0;

        foreach ($this->dl['lista'] as $tag=>$l) { 

            $index=mainFunc::gab_tots($tag);
            $counter++;

            $txt.=substr(mainFunc::gab_weektotag($l['info']['wd']),0,2);
            $txt.=' '.substr($tag,6,2).';';
        }

        while ($counter<31) {
            $txt.=';';
            $counter++;
        }

        foreach ($this->tot as $ktot=>$t) {
            $txt.=substr($t['titolo'],0,8).';';
        }

        $txt.="\n";

        return $txt;
    }

    //serve per scrivere i subs in maniera dedicata
    abstract function drawSubs();

    //serve per scrivere un footer in maniera dedicata
    abstract function drawFoot();

    //serve per esportare i dati
    abstract function export();

}
?>