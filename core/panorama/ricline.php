<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/core/timeline/timeline.php');

class panoramaRicline extends nebulaTimeline {

    //minuti durata ricezione
    protected $ricMin;
    //posizione durante il calcolo (all'inizio=index)
    protected $ricPos=0;

    function __construct($res,$subs) {

        parent::__construct($res);

        $this->tl_reset($subs);

    }

    function includiProprietario($index,$info) {
        /*$info=array(
            'ricric'=>""
        );*/

        $this->tl[$index]['subs']['schemi'][$info['skema']]=$info['blocco'];

        if ($this->ricPos==0) $this->ricPos=$index;
        
        while( $this->ricPos<=$this->tl[$index]['end'] ) {

            $delta=$this->tl[$index]['end']-$this->ricPos;

            if ( $delta==0 || $delta>=($this->res/3) ) {
                $this->tl[$index]['subs']['ricric']['flag']=true;
                $this->tl[$index]['subs']['ricric']['qta']++;

                $this->setTrim($index);

                if ($delta==0) break;
                else $this->ricPos+=$this->ricMin;
            }
            else {
                $this->ricPos+=$this->ricMin;
            }
        }
    }

    function escludiProprietario($index,$info){}

    function preInclude($ricric) {
        //fissa ogni quanti minuti è possibile una ricezione
        $this->ricMin=round(60/$ricric);
        $this->ricPos=0;
    }

    function addTl($tl) {

        foreach ($tl as $index=>$i) {

            if ($i['subs']['ricric']['flag']) {
                $this->tl[$index]['subs']['ricric']['flag']=true;
                $this->tl[$index]['subs']['ricric']['qta']+=$i['subs']['ricric']['qta'];
                $this->setTrim($index);
            }

            foreach ($i['subs']['schemi'] as $sk=>$blocco) {
                if ( !in_array($sk,$this->tl[$index]['subs']['schemi']) ) $this->tl[$index]['subs']['schemi'][]=$sk;
            }
        }

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

        if (isset($this->tl["0"]['subs']['ricric'])) {

            $tempry=array(
                "sub"=>'ricric',
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
                "legenda_tag"=>'RicRic'
            );

            $this->drawSubs("ricric",$tempry);
        }

    }

}