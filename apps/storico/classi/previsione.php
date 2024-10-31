<?php
class nebulaStoricoPrevisione {

    protected $param=array(
        'km'=>'',
        'consegna'=>'',
        'delta'=>'',
        'mese'=>0,
        'interventi'=>array()
    );

    protected $temp=array(
        "actual"=>0,
        "actualKm"=>0,
        "limiti"=>array(),
        "d_intervento"=>"",
        "km"=>"",
        "oggetti"=>array()
    );

    protected $tempCache=array();
    protected $main=false;

    protected $oggetti=array();
    protected $eventi=array();
    protected $spunta=array();

    function __construct($km,$consegna,$delta,$oggetti,$eventi) {

        /*"oggetti": {
            "MAN": {
              "dt": "24",
              "mint": 0,
              "maxt": 0,
              "stet": 0,
              "dkm": "30000",
              "minkm": 0,
              "maxkm": 0,
              "stekm": 0,
              "pcx": 0,
              "topt": 0,
              "topkm": 0,
              "first_t": 0,
              "first_km": 0,
              "flag_mov": "ok",
              "base": "gruppo",
              "stat": 0,
              "main":1
            },*/

        /*eventi{"MAN":{"d_rif":"20220308","km":"23159"},"FANT":{"d_rif":"20220308","km":"23159"}}*/

        $this->param['km']=$km;
        $this->param['consegna']=$consegna;
        $this->param['delta']=$delta;
        $this->oggetti=$oggetti;
        $this->eventi=$eventi;

        foreach ($this->oggetti as $k=>$o) {
            $this->spunta[$k]=false;
        }

        $oggi=date('Ymd');

        if ($this->param['consegna']<$oggi) {

            $giorni=mainFunc::gab_delta_tempo($this->param['consegna'],$oggi,'g');
            $this->param['mese']=round( ($this->param['km']/$giorni)*30 );
        }

        if ($this->param['delta']=='') {

            if ($this->param['mese']>3000) $this->param['delta']=1;
            elseif ($this->param['mese']>2000) $this->param['delta']=3;
            else $this->param['delta']=6;
        }

        else $this->param['delta']=(int)$this->param['delta'];

    }

    function calcola() {

        $this->temp['actual']=time();
        $this->temp['actualKm']=$this->param['km'];
        $cycle=0;

        $this->temp['limiti']=array(
            't'=>strtotime('+'.$this->param['delta'].' month',$this->temp['actual']),
            'km'=>$this->temp['actualKm']+((int)$this->param['delta']*$this->param['mese'])
        );

        while (!$this->checkSpunta() && $cycle<50) {

                $this->temp["d_intervento"]="";
                $this->temp["km"]="";
                $this->temp["oggetti"]=array();

            $this->tempCache=array();
            $this->main=false;

            while ($this->temp['d_intervento']=='' && $cycle<50 ) {

                foreach ($this->oggetti as $codice=>$c) {

                    if (isset($c['flag_mov']) && $c['flag_mov']=='del') continue;

                    $dtAll=round(mainFunc::gab_delta_tempo( $this->param['consegna'] ,date('Ymd',$this->temp['limiti']['t']),'g')/30);

                    //se i valori superano l'opzione TOP -> spunta e continua
                    if ($c['topt']!=0 && $dtAll>=$c['topt']) {
                        $this->spunta[$codice]=true;
                        continue;
                    }
                    if ($c['topkm']!=0 && $this->temp['limiti']['km']>=$c['topkm']) {
                        $this->spunta[$codice]=true;
                        continue;
                    }

                    $this->mainCalc($codice,$c);
                }

                if ($this->temp['d_intervento']!='') {

                    //ricalcola main (se un elemento main non è scaduto ma diventerebbe raggruppabile con uno successivo che risultasse scaduto non viene visto)
                    //#########################
                    //far rigirare mainCalc per i soli elementi MAIN non contenuti in tempCache
                    foreach ($this->oggetti as $codice=>$c) {

                        if (isset($c['flag_mov']) && $c['flag_mov']=='del') continue;

                        if ($c['main']==0) continue;
                        if (array_key_exists($codice,$this->tempCache)) continue;
    
                        $dtAll=round(mainFunc::gab_delta_tempo( $this->param['consegna'] ,date('Ymd',$this->temp['limiti']['t']),'g')/30);
    
                        //se i valori superano l'opzione TOP -> spunta e continua
                        if ($c['topt']!=0 && $dtAll>=$c['topt']) {
                            $this->spunta[$codice]=true;
                            continue;
                        }
                        if ($c['topkm']!=0 && $this->temp['limiti']['km']>=$c['topkm']) {
                            $this->spunta[$codice]=true;
                            continue;
                        }
    
                        $this->mainCalc($codice,$c);
                    }
                    
                    //#########################

                    foreach ($this->tempCache as $kcod=>$acod) {

                        $this->temp['oggetti'][$kcod]=array(
                            'dt'=>$acod['dt'],
                            'dkm'=>$acod['dkm'],
                            'ragdt'=>$acod['ragdt'],
                            'ragdkm'=>$acod['ragdkm'],
                            "termine"=>$acod['termine']
                        );

                        $this->eventi[$kcod]=array(
                            'd_rif'=>date('Ymd',$this->temp['actual']),
                            'km'=>$this->temp['actualKm']
                        );

                        $this->spunta[$kcod]=true;
                    }
                }

                //controlla le SPUNTE nel caso siano rimasti solo elementi oltre i valori TOP
                if ($this->checkSpunta()) break;

                //se non ci sono state scadenze per questa data allora aumenta di un mese
                if ($this->temp['d_intervento']=='') {
                    $this->temp['actual']=strtotime('+1 month',$this->temp['actual']);
                    $this->temp['actualKm']+=$this->param['mese'];
                    $this->temp['limiti']=array(
                        't'=>strtotime('+'.$this->param['delta'].' month',$this->temp['actual']),
                        'km'=>$this->temp['actualKm']+($this->param['delta']*$this->param['mese'])
                    );

                    $cycle++;
                }
            }

            if ($this->temp['d_intervento']!='') {
                $this->param['interventi'][]=array(
                    "d_intervento"=>$this->temp['d_intervento'],
                    "oggi"=>date('m-Y'),
                    "km"=>$this->temp['km'],
                    "oggetti"=>$this->temp['oggetti']
                );
            }

            if (!$this->checkSpunta()) {

                $this->temp['actual']=strtotime('+1 month',$this->temp['actual']);
                $this->temp['actualKm']+=$this->param['mese'];
                $this->temp['limiti']=array(
                    't'=>strtotime('+'.$this->param['delta'].' month',$this->temp['actual']),
                    'km'=>$this->temp['actualKm']+($this->param['delta']*$this->param['mese'])
                );

                $cycle++;
            }   
        }
    }

    function mainCalc($codice,$c) {
        
        ///////////////////////////////////////////////////////
        $rifActual=array(
            "dt"=>round(mainFunc::gab_delta_tempo( (isset($this->eventi[$codice])?$this->eventi[$codice]['d_rif']:$this->param['consegna']) ,date('Ymd',$this->temp['actual']),'g')/30),
            "dkm"=>$this->temp['actualKm']-(isset($this->eventi[$codice]['km'])?$this->eventi[$codice]['km']:0)
        );

        //se è un elemento PCX oppure è la prima occorrenza di un elemento MAIN non andare in avanti del DELTA ma considera il momento
        if ($c['pcx']==1 || ($c['main']==1 && $this->main==false) ) {
        //if ($c['pcx']==1) {
            $dt=$rifActual['dt'];
            $dkm=$rifActual['dkm'];
        }
        else {
            $dt=round(mainFunc::gab_delta_tempo( (isset($this->eventi[$codice])?$this->eventi[$codice]['d_rif']:$this->param['consegna']) ,date('Ymd',$this->temp['limiti']['t']),'g')/30);
            $dkm=$this->temp['limiti']['km']-(isset($this->eventi[$codice]['km'])?$this->eventi[$codice]['km']:0);
        }

        ///////////////////////////////////////////////////////
        
        if ($c['first_t']!=0 && !isset($this->eventi[$codice]) && !$this->spunta[$codice]) $tConf=$c['first_t'];
        else $tConf=(int)$c['dt'];

        if ($c['first_km']!=0 && !isset($this->eventi[$codice]) && !$this->spunta[$codice]) $kmConf=$c['first_km'];
        else $kmConf=(int)$c['dkm'];
        
        if ( ($tConf==0 && $kmConf==0) || ($tConf!=0 && $dt>=$tConf) || ($kmConf!=0 && $dkm>=$kmConf) ) {

            $termine='Raggruppato';
            if (($tConf==0 && $kmConf==0)) $termine='Scaduto';
            elseif ( ($tConf!=0 && $rifActual['dt']>=$tConf) || ($kmConf!=0 && $rifActual['dkm']>=$kmConf) ) $termine='Scaduto';

            $this->tempCache[$codice]=array(
                "codice"=>$codice,
                "dt"=>$rifActual['dt'],
                "dkm"=>$rifActual['dkm'],
                "ragdt"=>($termine=='Raggruppato' && $c['stat']==0)?$dt:-1,
                "ragdkm"=>($termine=='Raggruppato' && $c['stat']==0)?$dkm:-1,
                "termine"=>$termine
            );

            if ($c['main']==1 && $this->main==false) {
                $this->temp['d_intervento']=date('m-Y',$this->temp['actual']);
                $this->temp['km']=number_format(round($this->temp['actualKm']/1000)*1000,0,'','.');
                $this->main=true;
            }
        }
    }

    function checkSpunta() {

        foreach ($this->spunta as $codice=>$c) {
            if (!$c) return false;
        }

        return true;
    }

    function draw() {
        echo json_encode($this->param);
    }

}
?>