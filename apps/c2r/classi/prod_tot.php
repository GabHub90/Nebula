<?php

class c2rProdTot {

    //protected $actualDay="";

    protected $tot=array(

        "presenza"=>array(
            "tag"=>"presenza",
            "valori"=>array(
                "nominale"=>array(
                    "tag"=>"Nominale",
                    "op"=>"P",
                    "valore"=>0
                ),
                "inprestito"=>array(
                    "tag"=>"In prestito",
                    "op"=>"P",
                    "valore"=>0
                ),
                "prestato"=>array(
                    "tag"=>"Prestato",
                    "op"=>"M",
                    "valore"=>0
                ),
                "assenza"=>array(
                    "tag"=>"Assenza",
                    "op"=>"M",
                    "valore"=>0
                ),
                "malattia"=>array(
                    "tag"=>"Malattia",
                    "op"=>"M",
                    "valore"=>0
                ),
                "corsi"=>array(
                    "tag"=>"Corsi",
                    "op"=>"M",
                    "valore"=>0
                ),
                "extra"=>array(
                    "tag"=>"Extra",
                    "op"=>"P",
                    "valore"=>0
                ),
                "servizio"=>array(
                    "tag"=>"Servizio",
                    "op"=>"M",
                    "valore"=>0
                ),
                "esclusione"=>array(
                    "tag"=>"Escluso",
                    "op"=>"M",
                    "valore"=>0
                ),
                "inclusione"=>array(
                    "tag"=>"Incluso",
                    "op"=>"P",
                    "valore"=>0
                ),
                "speciale"=>array(
                    "tag"=>"Speciale",
                    "op"=>"M",
                    "valore"=>0
                )
            )
        ),

        "marcato"=>array(
            "tag"=>"Marcature",
            "prod"=>array(
                "pag"=>array(
                    "tag"=>"Pagamento",
                    "valore"=>0
                ),
                "gar"=>array(
                    "tag"=>"Garanzia",
                    "valore"=>0
                ),
                "rip"=>array(
                    "tag"=>"Ripristino",
                    "valore"=>0
                ),
                "app"=>array(
                    "tag"=>"Approntamento",
                    "valore"=>0
                ),
                "cap"=>array(
                    "tag"=>"Carr.Pag",
                    "valore"=>0
                ),
                "cai"=>array(
                    "tag"=>"Carr.Int",
                    "valore"=>0
                ),
                "gzi"=>array(
                    "tag"=>"Gestione",
                    "valore"=>0,
                    "eff"=>0
                ),
                "ind"=>array(
                    "tag"=>"Indefinito",
                    "valore"=>0,
                    "eff"=>0
                )
            ),
            "extra"=>array(
                "PRV"=>array(
                    "tag"=>"Prova",
                    "op"=>"P",
                    "presenza"=>true,
                    "valore"=>0
                ),
                "PRVM"=>array(
                    "tag"=>"Esc.Prv",
                    "op"=>"M",
                    "presenza"=>true,
                    "valore"=>0
                ),
                "ATT"=>array(
                    "tag"=>"Attesa",
                    "op"=>"P",
                    "presenza"=>true,
                    "valore"=>0
                ),
                "ATTM"=>array(
                    "tag"=>"Esc.Att",
                    "op"=>"M",
                    "presenza"=>true,
                    "valore"=>0
                ),
                "PER"=>array(
                    "tag"=>"Attesa",
                    "op"=>"P",
                    "presenza"=>true,
                    "valore"=>0
                ),
                "PERM"=>array(
                    "tag"=>"Esc.Att",
                    "op"=>"M",
                    "presenza"=>true,
                    "valore"=>0
                ),
                "PUL"=>array(
                    "tag"=>"Pulizia",
                    "op"=>"P",
                    "presenza"=>true,
                    "valore"=>0
                )
            )
        ),

        "chiuso"=>array(
            "tag"=>"Fatturate",
            "valori"=>array(
                "pag"=>array(
                    "tag"=>"Pagamento",
                    "valore"=>0,
                    "eff"=>0
                ),
                "gar"=>array(
                    "tag"=>"Garanzia",
                    "valore"=>0,
                    "eff"=>0
                ),
                "rip"=>array(
                    "tag"=>"Ripristino",
                    "valore"=>0,
                    "eff"=>0
                ),
                "app"=>array(
                    "tag"=>"Approntamento",
                    "valore"=>0,
                    "eff"=>0
                ),
                "cap"=>array(
                    "tag"=>"Carr.Pag",
                    "valore"=>0,
                    "eff"=>0
                ),
                "cai"=>array(
                    "tag"=>"Carr.Int",
                    "valore"=>0,
                    "eff"=>0
                ),
                "gzi"=>array(
                    "tag"=>"Gestione",
                    "valore"=>0,
                    "eff"=>0
                ),
                "ind"=>array(
                    "tag"=>"Indefinito",
                    "valore"=>0,
                    "eff"=>0
                )
            ),
        ),

        "aperto"=>array(
            "tag"=>"Aperte",
            "valori"=>array(
                "pag"=>array(
                    "tag"=>"Pagamento",
                    "valore"=>0,
                    "eff"=>0
                ),
                "gar"=>array(
                    "tag"=>"Garanzia",
                    "valore"=>0,
                    "eff"=>0
                ),
                "rip"=>array(
                    "tag"=>"Ripristino",
                    "valore"=>0,
                    "eff"=>0
                ),
                "app"=>array(
                    "tag"=>"Approntamento",
                    "valore"=>0,
                    "eff"=>0
                ),
                "cap"=>array(
                    "tag"=>"Carr.Pag",
                    "valore"=>0,
                    "eff"=>0
                ),
                "cai"=>array(
                    "tag"=>"Carr.Int",
                    "valore"=>0,
                    "eff"=>0
                ),
                "gzi"=>array(
                    "tag"=>"Gestione",
                    "valore"=>0,
                    "eff"=>0
                ),
                "ind"=>array(
                    "tag"=>"Indefinito",
                    "valore"=>0,
                    "eff"=>0
                )
            ),
        ),

        "prenotato"=>array(
            "valore"=>0,
            "eff"=>0
        )
    );

    protected $res=array(
        "presenza"=>0,
        "marcato"=>0,
        "chiuso"=>array(
            "valore"=>0,
            "eff"=>0
        ),
        "aperto"=>array(
            "valore"=>0,
            "eff"=>0
        ) 
    );

    function __construct() {

    }

    function getValore($ambito,$tipo,$valore) {
        //aperto - pag - eff

        if ($ambito=='marcato') {

            if (isset($this->tot[$ambito]['prod'][$tipo][$valore])) {
                return $this->tot[$ambito]['prod'][$tipo][$valore];
            }
            else return false;
        }

        else {

            if (isset($this->tot[$ambito]['valori'][$tipo][$valore])) {
                return $this->tot[$ambito]['valori'][$tipo][$valore];
            }
            else return false;
        }

    }

    function getRes() {
        return $this->res;
    }

    function addPresenza($a) {

        $this->tot['presenza']['valori']['nominale']['valore']+=round($a['nominale']/60,2);
    }

    function addPresenzaSpeciale($tipo,$minuti) {

        $this->tot['presenza']['valori'][$tipo]['valore']+=round($minuti/60,2);
    }

    function addMarcaturaBase($addebito,$valore) {

        if (isset($this->tot['marcato']['prod'][$addebito])) {
            $this->tot['marcato']['prod'][$addebito]['valore']+=$valore;
        }
    }

    function addMarcatura($tipo,$addebito,$valore) {

        if (isset($this->tot[$tipo]['valori'][$addebito])) {
            $this->tot[$tipo]['valori'][$addebito]['valore']+=$valore;
        }

    }

    function addMarcaturaExtra($tipo,$ore) {

        $this->tot['marcato']['extra'][$tipo]['valore']+=$ore;
    }

    function addPrenotato($ore) {
        $this->tot['prenotato']['valore']+=$ore;
    }

    function addEventi($a) {

        // [{"permesso":{"P":{"qta":64}},"periodo":{"F":{"qta":32}}}]

        foreach ($a as $classe=>$c) {
            foreach ($c as $tipo=>$t) {
                switch($classe) {
                    case 'permesso': $this->tot['presenza']['valori']['assenza']['valore']+=round($t['qta']/60,2);
                    break;
                    case 'periodo': 
                        if ($tipo=='F') $this->tot['presenza']['valori']['assenza']['valore']+=round($t['qta']/60,2);
                        if ($tipo=='M') $this->tot['presenza']['valori']['malattia']['valore']+=round($t['qta']/60,2);
                    break;
                    case 'extra': $this->tot['presenza']['valori']['extra']['valore']+=round($t['qta']/60,2);
                    break;
                }
            }
        }
    }

    function read() {
        return $this->tot;
    }

    function consolida() {

        //valorizza le presenza speciali da escludere
        foreach ($this->tot['marcato']['extra'] as $tipo=>$t) {
 
            if (!$t['presenza']) continue;

            $tempop=$t['op']=='P'?1:-1;

            $this->tot['presenza']['valori']['speciale']['valore']+=($t['valore']*$tempop);

        }

        foreach ($this->tot['presenza']['valori'] as $tipo=>$t) {

            $tempop=$t['op']=='P'?1:-1;

            $this->res['presenza']+=($t['valore']*$tempop);
        }

        //calcola le efficienze
        foreach ($this->tot['marcato']['prod'] as $tipo=>$t) {

            //marcature CHIUSE
            $d=$t['valore']-$this->tot['aperto']['valori'][$tipo]['valore'];

            if ($d!=0) {
                $this->tot['chiuso']['valori'][$tipo]['eff']=$this->tot['chiuso']['valori'][$tipo]['valore']/$d;
            }
            else {
                $this->tot['chiuso']['valori'][$tipo]['eff']=0;
            }

            //marcature APERTE
            if ($t['valore']!=0) {
                $this->tot['aperto']['valori'][$tipo]['eff']=( $this->tot['chiuso']['valori'][$tipo]['valore']+$this->tot['aperto']['valori'][$tipo]['valore'] )/$t['valore'];
            }
            else {
                $this->tot['aperto']['valori'][$tipo]['eff']=0;
            }

            //TOTALI
            $this->res['marcato']+=$t['valore'];
            $this->res['chiuso']['valore']+=$this->tot['chiuso']['valori'][$tipo]['valore'];
            $this->res['aperto']['valore']+=$this->tot['aperto']['valori'][$tipo]['valore'];
        }

        if ($this->res['marcato']!=0) {
            if (($this->res['marcato']-$this->res['aperto']['valore'])==0) $this->res['chiuso']['eff']=0;
            else {
                $this->res['chiuso']['eff']=$this->res['chiuso']['valore']/($this->res['marcato']-$this->res['aperto']['valore']);
            }
            $this->res['aperto']['eff']=($this->res['chiuso']['valore']+$this->res['aperto']['valore'])/$this->res['marcato'];
        }
        else {
            $this->res['chiuso']['eff']=0;
            $this->res['aperto']['eff']=0;
        }

        //statistica
        $this->tot['prenotato']['eff']=($this->res['presenza']==0?0:($this->tot['prenotato']['valore']/$this->res['presenza'])*100 );
    }

    function write($arr,$modo) {

        foreach ($arr['presenza']['valori'] as $k=>$v) {
            if ($k=='speciale') continue;
            $this->tot['presenza']['valori'][$k]['valore']+=$v['valore'];
        }

        if ($modo=='rep') return;

        foreach ($arr['marcato']['prod'] as $k=>$v) {
            $this->tot['marcato']['prod'][$k]['valore']+=$v['valore'];
        }

        foreach ($arr['marcato']['extra'] as $k=>$v) {
            $this->tot['marcato']['extra'][$k]['valore']+=$v['valore'];
        }

        foreach ($arr['chiuso']['valori'] as $k=>$v) {
            $this->tot['chiuso']['valori'][$k]['valore']+=$v['valore'];
        }

        foreach ($arr['aperto']['valori'] as $k=>$v) {
            $this->tot['aperto']['valori'][$k]['valore']+=$v['valore'];
        }

        $this->tot['prenotato']['valore']+=$arr['prenotato']['valore'];
    }

    function writeValore($ambito,$tipo,$valore) {
        $this->tot[$ambito]['valori'][$tipo]['valore']+=$valore;
    }

    function readValore($ambito,$tipo) {
        return $this->tot[$ambito]['valori'][$tipo]['valore'];
    }

    function draw() {

        $txt='<table style="width:100%;border-collapse:collapse;border:1px solid black;" >';

            $txt.='<tr>';
                $txt.='<th colspan="2" style="border-top:1px solid black;border-left:1px solid black;border-right:1px solid black;" >Presenza</th>';
                $txt.='<th colspan="2" style="border-top:1px solid black;border-left:1px solid black;border-right:1px solid black;" >Marcature</th>';
                $txt.='<th colspan="3" style="border-top:1px solid black;border-left:1px solid black;border-right:1px solid black;" >Fatturate</th>';
                $txt.='<th colspan="3" style="border-top:1px solid black;border-left:1px solid black;border-right:1px solid black;" >Aperte</th>';
            $txt.='</tr>';

            $txt.='<tr style="font-size:0.9em;">';
                $txt.='<th style="width:10%;border-bottom:1px solid black;border-left:1px solid black">tipo</th>';
                $txt.='<th style="width:10%;border-bottom:1px solid black;border-right:1px solid black;">ore</th>';

                $txt.='<th style="width:10%;border-bottom:1px solid black;border-left:1px solid black">tipo</th>';
                $txt.='<th style="width:10%;border-bottom:1px solid black;border-right:1px solid black">ore</th>';

                $txt.='<th style="width:10%;border-bottom:1px solid black;border-left:1px solid black">tipo</th>';
                $txt.='<th style="width:10%;border-bottom:1px solid black;">ore</th>';
                $txt.='<th style="width:10%;border-bottom:1px solid black;border-right:1px solid black">Eff.</th>';

                $txt.='<th style="width:10%;border-bottom:1px solid black;border-left:1px solid black">tipo</th>';
                $txt.='<th style="width:10%;border-bottom:1px solid black;">ore</th>';
                $txt.='<th style="width:10%;border-bottom:1px solid black;border-right:1px solid black">Eff.</th>';    
            $txt.='</tr>';

            $txt.='<tbody>';

                $txt.='<tr style="font-size:0.9em;">';

                    $txt.='<td colspan="2" style="border-left:1px solid black;border-right:1px solid black;vertical-align:top;" >';

                        foreach ($this->tot['presenza']['valori'] as $k=>$v) {

                            if ($k=='speciale') continue;

                            if ($v['valore']==0) continue;

                            $txt.='<div style="width:100%;" >';

                                $txt.='<div style="position:relative;display:inline-block;width:50%;padding-left: 5px;box-sizing: border-box;">';
                                    $txt.=$v['tag'];
                                $txt.='</div>';

                                $tempop=$v['op']=='P'?1:-1;

                                $txt.='<div style="position:relative;display:inline-block;width:50%;text-align:right;padding-right: 5px;box-sizing: border-box;">';
                                    $txt.=number_format($v['valore']*$tempop,2,',','.');
                                $txt.='</div>';

                            $txt.='</div>';

                        }

                    $txt.='</td>';

                    $txt.='<td colspan="2" style="border-left:1px solid black;border-right:1px solid black;vertical-align:top;" >';

                        foreach ($this->tot['marcato']['prod'] as $k=>$v) {

                            if ($k=='ind' && $v['valore']==0) continue;

                            $txt.='<div style="width:100%;" >';

                                $txt.='<div style="position:relative;display:inline-block;width:50%;padding-left: 5px;box-sizing: border-box;">';
                                    $txt.=substr($v['tag'],0,9);
                                $txt.='</div>';

                                $txt.='<div style="position:relative;display:inline-block;width:50%;text-align:right;padding-right: 5px;box-sizing: border-box;">';
                                    $txt.=number_format($v['valore'],2,',','.');
                                $txt.='</div>';

                            $txt.='</div>';

                        }

                    $txt.='</td>';

                    $txt.='<td colspan="3" style="border-left:1px solid black;border-right:1px solid black;vertical-align:top;" >';

                        foreach ($this->tot['chiuso']['valori'] as $k=>$v) {

                            if ($k=='ind' && $v['valore']==0) continue;

                            $txt.='<div style="width:100%;" >';

                                $txt.='<div style="position:relative;display:inline-block;width:33.3%;padding-left: 5px;box-sizing: border-box;">';
                                    $txt.=substr($v['tag'],0,9);
                                $txt.='</div>';

                                $txt.='<div style="position:relative;display:inline-block;width:33.3%;text-align:right;padding-right: 5px;box-sizing: border-box;">';
                                    $txt.=number_format($v['valore'],2,',','.');
                                $txt.='</div>';

                                $txt.='<div style="position:relative;display:inline-block;width:33.3%;text-align:right;padding-right: 5px;box-sizing: border-box;">';
                                    $txt.=number_format($v['eff']*100,2,',','.').'%';
                                $txt.='</div>';

                            $txt.='</div>';

                        }

                    $txt.='</td>';

                    $txt.='<td colspan="3" style="border-left:1px solid black;border-right:1px solid black;vertical-align:top;" >';

                        foreach ($this->tot['aperto']['valori'] as $k=>$v) {

                            if ($k=='ind' && $v['valore']==0) continue;

                            $txt.='<div style="width:100%;" >';

                                $txt.='<div style="position:relative;display:inline-block;width:33.3%;padding-left: 5px;box-sizing: border-box;">';
                                    $txt.=substr($v['tag'],0,9);
                                $txt.='</div>';

                                $txt.='<div style="position:relative;display:inline-block;width:33.3%;text-align:right;padding-right: 5px;box-sizing: border-box;">';
                                    $txt.=number_format($v['valore'],2,',','.');
                                $txt.='</div>';

                                $txt.='<div style="position:relative;display:inline-block;width:33.3%;text-align:right;padding-right: 5px;box-sizing: border-box;">';
                                    $txt.=number_format($v['eff']*100,2,',','.').'%';
                                $txt.='</div>';

                            $txt.='</div>';

                        }

                    $txt.='</td>';

                $txt.='</tr>';

                //////////////////////////////////////////////////////////////////////////////////

                $txt.='<tr style="font-size:0.9em;">';

                    $txt.='<td colspan="2" style="border-left:1px solid black;border-right:1px solid black;vertical-align:top;" >';

                        $txt.='<hr/>';

                        //###################

                        if ($this->tot['presenza']['valori']['speciale']['valore']!=0) {

                            $txt.='<div style="width:100%;" >';

                                $txt.='<div style="position:relative;display:inline-block;width:50%;padding-left: 5px;box-sizing: border-box;">';
                                    $txt.=$this->tot['presenza']['valori']['speciale']['tag'];
                                $txt.='</div>';

                                $tempop=$this->tot['presenza']['valori']['speciale']['op']=='P'?1:-1;

                                $txt.='<div style="position:relative;display:inline-block;width:50%;text-align:right;padding-right: 5px;box-sizing: border-box;">';
                                    $txt.=number_format($this->tot['presenza']['valori']['speciale']['valore']*$tempop,2,',','.');
                                $txt.='</div>';

                            $txt.='</div>';
                        
                        }
                        //###################

                    $txt.='</td>';

                    $txt.='<td colspan="2" style="border-left:1px solid black;border-right:1px solid black;vertical-align:top;" >';
                    
                        $txt.='<hr/>';

                        foreach ($this->tot['marcato']['extra'] as $k=>$v) {

                            if ($v['valore']==0) continue;
        
                            $tempop=$v['op']=='P'?1:-1;
        
                            $txt.='<div style="width:100%;';
                                //if ($k=='PUL') $txt.='background-color:#dddddd;';
                            $txt.='" >';
        
                                $txt.='<div style="position:relative;display:inline-block;width:50%;padding-left: 5px;box-sizing: border-box;">';
                                    $txt.=substr($v['tag'],0,9);
                                $txt.='</div>';
        
                                $txt.='<div style="position:relative;display:inline-block;width:50%;text-align:right;padding-right: 5px;box-sizing: border-box;">';
                                    $txt.=number_format($v['valore']*$tempop,2,',','.');
                                $txt.='</div>';
        
                            $txt.='</div>';
        
                        }

                        $txt.='<div style="height:5px;"></div>';

                    $txt.='</td>';

                    $txt.='<td colspan="3" style="border-left:1px solid black;border-right:1px solid black;vertical-align:top;" ></td>';
                    $txt.='<td colspan="3" style="border-left:1px solid black;border-right:1px solid black;vertical-align:top;" ></td>';
              
                $txt.='</tr>';

                //TOTALI
                $txt.='<tr style="font-weight:bold;">';

                    $txt.='<td colspan="2" style="border-left:1px solid black;border-right:1px solid black;border-top:1px solid black;vertical-align:top;text-align:center;" >';
                        $txt.=number_format($this->res['presenza'],2,',','');
                    $txt.='</td>';

                    $txt.='<td colspan="2" style="border-left:1px solid black;border-right:1px solid black;border-top:1px solid black;vertical-align:top;text-align:center;" >';
                        $txt.=number_format($this->res['marcato'],2,',','');
                    $txt.='</td>';

                    $txt.='<td colspan="2" style="border-left:1px solid black;border-top:1px solid black;vertical-align:top;text-align:center;" >';
                        $txt.=number_format($this->res['chiuso']['valore'],2,',','');
                    $txt.='</td>';

                    $txt.='<td colspan="1" style="border-right:1px solid black;border-top:1px solid black;vertical-align:top;text-align:center;" >';
                        $txt.=number_format($this->res['chiuso']['eff']*100,2,',','').'%';
                    $txt.='</td>';

                    $txt.='<td colspan="2" style="border-left:1px solid black;border-top:1px solid black;vertical-align:top;text-align:center;" >';
                        $txt.=number_format($this->res['aperto']['valore'],2,',','');
                    $txt.='</td>';

                    $txt.='<td colspan="1" style="border-right:1px solid black;border-top:1px solid black;vertical-align:top;text-align:center;" >';
                        $txt.=number_format($this->res['aperto']['eff']*100,2,',','').'%';
                    $txt.='</td>';

                $txt.='</tr>';

            $txt.='</tbody>';

        $txt.='</table>';

        return $txt;
    }

    function resoconto($tag,$tipo,$fisso) {

        $txt='<div>';

            if (!$fisso || $tipo=='standard') {

                $tempres=$this->res['presenza'];

                $txt.='<div ';
                    if (!$fisso) $txt.='id="c2r_resoconto_standard_'.$tag.'" ';
                $txt.='style="position:relative;width:100%;margin-top:10px;background-color:#c3e6c3;';
                    if ($tipo!='standard') $txt.='display:none;';
                $txt.='">';

                    $txt.=$this->resocontoDettaglio('Standard',$tempres);

                $txt.='</div>';

            }

            if (!$fisso || $tipo=='nospec') {

                $tempres=$this->res['presenza']+$this->tot['presenza']['valori']['speciale']['valore'];

                $txt.='<div ';
                    if (!$fisso) $txt.='id="c2r_resoconto_nospec_'.$tag.'" ';
                $txt.='style="position:relative;width:100%;margin-top:10px;background-color:#e3e4a1;';
                    if ($tipo!='nospec') $txt.='display:none;';
                $txt.='">';

                    $txt.=$this->resocontoDettaglio('NO Speciale',$tempres);

                $txt.='</div>';

            }

            if (!$fisso || $tipo=='noescl') {

                $tempres=$this->res['presenza']-$this->tot['presenza']['valori']['inclusione']['valore']+$this->tot['presenza']['valori']['esclusione']['valore'];

                $txt.='<div ';
                    if (!$fisso) $txt.='id="c2r_resoconto_noescl_'.$tag.'" ';
                $txt.='style="position:relative;width:100%;margin-top:10px;background-color:#e3e4a1;';
                    if ($tipo!='noescl') $txt.='display:none;';
                $txt.='">';

                    $txt.=$this->resocontoDettaglio('NO Esclusione',$tempres);

                $txt.='</div>';

            }

            if (!$fisso || $tipo=='flat') {

                $tempres=$this->res['presenza']-$this->tot['presenza']['valori']['inclusione']['valore']+$this->tot['presenza']['valori']['esclusione']['valore']+$this->tot['presenza']['valori']['speciale']['valore'];

                $txt.='<div ';
                    if (!$fisso) $txt.='id="c2r_resoconto_flat_'.$tag.'" ';
                $txt.='style="position:relative;width:100%;margin-top:10px;background-color:#e2bdac;';
                    if ($tipo!='flat') $txt.='display:none;';
                $txt.='">';

                    $txt.=$this->resocontoDettaglio('FLAT',$tempres);

                $txt.='</div>';

            }

            if (!$fisso || $tipo=='nominale') {

                $tempres=$this->tot['presenza']['valori']['nominale']['valore']+$this->tot['presenza']['valori']['inprestito']['valore']-$this->tot['presenza']['valori']['prestato']['valore'];

                $txt.='<div ';
                    if (!$fisso) $txt.='id="c2r_resoconto_nominale_'.$tag.'" ';
                $txt.='style="position:relative;width:100%;margin-top:10px;background-color:#e2bdac;';
                    if ($tipo!='nominale') $txt.='display:none;';
                $txt.='">';

                    $txt.=$this->resocontoDettaglio('NOMINALE',$tempres);

                $txt.='</div>';

            }

        $txt.='</div>';

        return $txt;
    }

    function resocontoDettaglio($label,$tempres) { 

        $txt='<table style="width:100%;border-collapse:collapse;border:1px solid black;" >';

            $txt.='<tr>';
                $txt.='<td style="width:20%;border-top:1px solid black;border-left:1px solid black;border-right:1px solid black;text-align:center;" >'.$label.'</td>';
                $txt.='<td style="width:20%;border-top:1px solid black;border-left:1px solid black;border-right:1px solid black;text-align:center;" >Grado di Utilizzo</td>';
                $txt.='<td style="width:30%;border-top:1px solid black;border-left:1px solid black;border-right:1px solid black;text-align:center;" >Efficienza / Produttività</td>';
                $txt.='<td style="width:30%;border-top:1px solid black;border-left:1px solid black;border-right:1px solid black;text-align:center;" >Efficienza / Produttività</td>';
            $txt.='</tr>';

            $txt.='<tr style="font-weight:bold;">';

                $txt.='<td style="border-left:1px solid black;border-right:1px solid black;vertical-align:top;text-align:center;" >';
                    $txt.=number_format($tempres,2,',','');
                $txt.='</td>';

                $txt.='<td style="border-left:1px solid black;border-right:1px solid black;vertical-align:top;text-align:center;" >';
                    $tempGU=($tempres==0)?0:($this->res['marcato']/$tempres);
                    if ($tempGU<0) $tempGU=$tempGU*-1;
                    $txt.=number_format( $tempGU*100,2,',','').'%';
                $txt.='</td>';

                $txt.='<td style="border-left:1px solid black;border-right:1px solid black;vertical-align:top;text-align:center;" >';
                    
                    $txt.='<div style="display:inline-block;width:50%;text-align:center;">';
                        $tempEff=($this->res['marcato']==0 || ($this->res['marcato']-$this->res['aperto']['valore'])==0)?0:$this->res['chiuso']['valore']/($this->res['marcato']-$this->res['aperto']['valore']);
                        $txt.=number_format( $tempEff*100,2,',','').'%';
                    $txt.='</div>';

                    $txt.='<div style="display:inline-block;width:50%;text-align:center;">';
                        //$temp=($tempres==0)?0:($this->res['chiuso']['valore']/$tempres);
                        $temp=($tempres==0)?0:$tempEff*$tempGU;
                        $txt.=number_format( $temp*100,2,',','').'%';
                    $txt.='</div>';

                $txt.='</td>';

                $txt.='<td style="border-left:1px solid black;border-right:1px solid black;vertical-align:top;text-align:center;" >';
                    
                    $txt.='<div style="display:inline-block;width:50%;text-align:center;">';
                        $temp=($this->res['marcato']==0)?0:( ($this->res['chiuso']['valore']+$this->res['aperto']['valore'])/$this->res['marcato'] );
                        $txt.=number_format( $temp*100,2,',','').'%';
                    $txt.='</div>';

                    $txt.='<div style="display:inline-block;width:50%;text-align:center;">';
                        $temp=($tempres==0)?0:( ($this->res['chiuso']['valore']+$this->res['aperto']['valore'])/$tempres );
                        $txt.=number_format( $temp*100,2,',','').'%';
                    $txt.='</div>';

                $txt.='</td>';

            $txt.='</tyr>';

        $txt.='</table>';

        return $txt;

    }

    function statistica($tecnom) {

        $txt='<div style="position:relative;width:100%;margin-top:10px;box-sizing:border-box;">';

            $txt.='<table style="width:100%;border-collapse:collapse;border:2px solid violet;" >';

                $txt.='<tr>';

                    $txt.='<td style="width:20%;border-top:1px solid violet;border-left:1px solid violet;border-right:1px solid violet;text-align:center;padding:5px;vertical-align:top;" >';

                        $txt.='<div>Nominale + Extra</div>';
                        $pres1=$this->tot['presenza']['valori']['nominale']['valore']*(($this->tot['presenza']['valori']['nominale']['op']=='P')?1:-1);
                        $pres1+=$this->tot['presenza']['valori']['extra']['valore']*(($this->tot['presenza']['valori']['extra']['op']=='P')?1:-1);
                        $txt.='<div style="font-weight:bold;">'.number_format($pres1,2,',','').'</div>';

                        $txt.='<div>Escluso + Incluso</div>';
                        $pres2=$this->tot['presenza']['valori']['inclusione']['valore']*(($this->tot['presenza']['valori']['inclusione']['op']=='P')?1:-1);
                        $pres2+=$this->tot['presenza']['valori']['esclusione']['valore']*(($this->tot['presenza']['valori']['esclusione']['op']=='P')?1:-1);
                        $txt.='<div style="font-weight:bold;">'.number_format($pres2,2,',','').'</div>';

                        /*$txt.='<div>Prestato + Inprestito</div>';
                        $pres3=$this->tot['presenza']['valori']['inprestito']['valore']*(($this->tot['presenza']['valori']['inprestito']['op']=='P')?1:-1);
                        $pres3+=$this->tot['presenza']['valori']['prestato']['valore']*(($this->tot['presenza']['valori']['prestato']['op']=='P')?1:-1);
                        $txt.='<div style="font-weight:bold;">'.number_format($pres3,2,',','').'</div>';*/

                        if ($tecnom==0) $dayeq=0;
                        else $dayeq=($pres1/8)/$tecnom;

                    $txt.='</td>';

                    $txt.='<td style="width:20%;border-top:1px solid violet;border-left:1px solid violet;border-right:1px solid violet;text-align:center;padding:5px;vertical-align:top;" >';

                        $txt.='<div>Tecnici Nominali (tutti)</div>';
                        $txt.='<div style="font-weight:bold;">'.number_format($tecnom,2,',','').'</div>';

                        $txt.='<div>Prod. / Improd.</div>';
                        $txt.='<div style="font-weight:bold;">'.number_format( $dayeq==0?$tecnom:$tecnom+(($pres2/8)/$dayeq),2,',','').' / '.number_format( $dayeq==0?0:abs($pres2/8)/$dayeq,2,',','').'</div>';

                    $txt.='</td>';

                    $txt.='<td style="width:30%;border-top:1px solid violet;border-left:1px solid violet;border-right:1px solid violet;text-align:center;padding:5px;vertical-align:top;" >';

                        $txt.='<div>Giorni Nominali Equiv. (8 ore)</div>';
                        $txt.='<div style="font-weight:bold;">'.number_format($dayeq,2,',','').'</div>';

                        $txt.='<div>Produttivi Equivalenti (8 ore)</div>';
                        $txt.='<div style="font-weight:bold;">'.number_format($dayeq==0?0:($this->res['presenza']/8)/$dayeq,2,',','').'</div>';

                    $txt.='</td>';

                    $txt.='<td style="width:30%;border-top:1px solid violet;border-left:1px solid violet;border-right:1px solid violet;text-align:center;padding:5px;">';

                        $txt.='<div>Ore prenotate in agenda</div>';
                        $txt.='<div style="font-weight:bold;">'.number_format($this->tot['prenotato']['valore'],2,',','').'</div>';

                        $txt.='<div>Grado di occupazione</div>';
                        $txt.='<div style="font-weight:bold;">'.number_format($this->tot['prenotato']['eff'],2,',','').'%</div>';

                    $txt.='</td>';

                $txt.='</tr>';

            $txt.='</table>';

        $txt.='</div>';

        return $txt;

    }

    /*viene chiamato in casi particolari come per esempio CAR
    function resocontoPrestato() {

        $tempres=$this->res['presenza']+$this->tot['presenza']['valori']['prestato']['valore'];

        $txt='<div style="position:relative;width:100%;margin-top:10px;background-color:#dddddd;">';

            $txt.=$this->resocontoDettaglio('PRESTATO',$tempres);

        $txt.='</div>';

        return $txt;
    }*/

}
