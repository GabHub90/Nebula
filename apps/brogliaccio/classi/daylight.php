<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/core/daylight/daylight.php');

class bgcDaylight extends nebulaDaylight {

    protected $statoOverall="OK";

    protected $res=array();

    //i totali OVERALL sono registrati nella variabile TOT
    //dell'oggetto padre ed ha la stessa struttura dei totali definiti dal figlio
    //e caricati con il metodo "loadTotali"

    //records codici per "tag" riferiti al reparto e coll dell'oggetto daylight
    protected $codiciDB=array();

    protected $galileo;
    
    function __construct($label,$galileo) {

        parent::__construct($label);

        $this->galileo=$galileo;

        //##################################
        //caricare i TOTALI
        $a=array(
            "TIM"=>array(
                "titolo"=>"timbrato",
                "codice"=>"TIM",
                "valore"=>0,
                "edit"=>0,
                "verso"=>''
            ),
            "ML"=>array(
                "titolo"=>"malattia",
                "codice"=>"ML",
                "valore"=>0,
                "edit"=>1,
                "verso"=>'M'
            ),
            "P"=>array(
                "titolo"=>"permesso",
                "codice"=>"P",
                "valore"=>0,
                "edit"=>1,
                "verso"=>'M'
            ),
            "F"=>array(
                "titolo"=>"ferie",
                "codice"=>"F",
                "valore"=>0,
                "edit"=>1,
                "verso"=>'M'
            ),
            "HT"=>array(
                "titolo"=>"104",
                "codice"=>"HT",
                "valore"=>0,
                "edit"=>1,
                "verso"=>'M'
            ),
            "MT"=>array(
                "titolo"=>"maternità",
                "codice"=>"MT",
                "valore"=>0,
                "edit"=>1,
                "verso"=>'M'
            ),
            "FS"=>array(
                "titolo"=>"festivo",
                "codice"=>"FS",
                "valore"=>0,
                "edit"=>1,
                "verso"=>'M'
            ),
            "R"=>array(
                "titolo"=>"riposo",
                "codice"=>"R",
                "valore"=>0,
                "edit"=>1,
                "verso"=>'M'
            ),
            "SO"=>array(
                "titolo"=>"str ord",
                "codice"=>"SO",
                "valore"=>0,
                "edit"=>1,
                "verso"=>'P'
            ),
            "SS"=>array(
                "titolo"=>"str sab",
                "codice"=>"SS",
                "valore"=>0,
                "edit"=>1,
                "verso"=>'P'
            ),
            "SD"=>array(
                "titolo"=>"str dom",
                "codice"=>"SD",
                "valore"=>0,
                "edit"=>1,
                "verso"=>'P'
            ),
            "LAV"=>array(
                "titolo"=>"lavorato",
                "codice"=>"LAV",
                "valore"=>0,
                "edit"=>0,
                "verso"=>''
            ),
            "XXX"=>array(
                "titolo"=>"scartato",
                "codice"=>"XXX",
                "valore"=>0,
                "edit"=>1,
                "verso"=>'P'
            )
            
        );

        $this->loadTotali($a);
        //##################################
    }

    function getStatoOverall() {
        return $this->statoOverall;
    }

    function loadCodiciDB($a) {
        $this->codiciDB=$a;
    }

    function drawSubs() {

        $h='20px';
        $h2='45px';

        $tot=array();

        //label (non c'è bisogno di verificare se la funzione è abilitata)
        echo '<td>';
            echo '<div style="text-align:center;font-weight:bold;height:'.$h.';line-height:'.$h.';vertical-align:middle;">';
                echo 'Standard';
            echo '</div>';
            echo '<div style="text-align:center;font-weight:bold;height:'.$h.';line-height:'.$h.';vertical-align:middle;">';
                echo 'Previsto';
            echo '</div>';
            echo '<div style="text-align:center;font-weight:bold;height:'.$h.';line-height:'.$h.';vertical-align:middle;">';
                echo 'Timbrato';
            echo '</div>';
            echo '<div style="text-align:center;font-weight:normal;height:'.$h2.';line-height:'.$h2.';vertical-align:middle;">';
                echo 'codice';
            echo '</div>';
        echo '</td>';

        $counter=0;

        $tot=array();

        foreach ($this->dl['lista'] as $tag=>$l) {

            $counter++;

            /*array(
                "minuti"=>0,
                "arrotondato"=>0,
                "oreSTD"=>$std,
                "tipoSTR"=>$str,
                "actual"=>$actual,
                "actualBro"=>$actualBro,
                "stato"=>'OK',
                "blocks"=>array()
            );*/

            $this->res[$tag]['tot']['TIM']['valore']=$this->subs['presenza']['lista'][$tag]['arrotondato'];
            $this->res[$tag]['tot']['LAV']['valore']=$this->subs['presenza']['lista'][$tag]['arrotondato'];
            $this->tot['TIM']['valore']+=$this->subs['presenza']['lista'][$tag]['arrotondato'];
            $this->tot['LAV']['valore']+=$this->subs['presenza']['lista'][$tag]['arrotondato'];

            echo '<td style="font-size:0.65em;">';
                //echo json_encode($this->subs['presenza']['lista']);
                echo '<div style="text-align:center;height:'.$h.';line-height:'.$h.';vertical-align:middle;">';
                    echo number_format($this->subs['presenza']['lista'][$tag]['oreSTD'],0,'.','');
                echo '</div>';
                echo '<div style="text-align:center;height:'.$h.';line-height:'.$h.';vertical-align:middle;">';
                    echo number_format($this->subs['presenza']['lista'][$tag]['actualBro']/60,1,'.','');
                echo '</div>';
                echo '<div style="text-align:center;height:'.$h.';line-height:'.$h.';vertical-align:middle;border-radius:4px;';
                    if ($this->subs['presenza']['lista'][$tag]['stato']=='KO') {
                        echo 'background-color:#ffa3a3;';
                        $this->statoOverall='KO';
                    }
                    //L'errore di allineamento esiste solo se era prevista una presenza reale o standard
                    elseif ($this->subs['presenza']['lista'][$tag]['stato']=='ALL' && ($this->subs['presenza']['lista'][$tag]['actualBro']>0 || $this->subs['presenza']['lista'][$tag]['oreSTD']>0) ) {
                        echo 'background-color:#fcff44;';
                        if ($this->statoOverall!='KO') $this->statoOverall='ALL';
                    }
                    elseif ($this->subs['presenza']['lista'][$tag]['arrotondato']>($this->subs['presenza']['lista'][$tag]['oreSTD']*60)) echo 'background-color:#c3ffca;';
                    elseif ($this->subs['presenza']['lista'][$tag]['arrotondato']<($this->subs['presenza']['lista'][$tag]['oreSTD']*60)) {
                        if ($this->subs['presenza']['lista'][$tag]['arrotondato']==0) echo 'background-color:#ffcb89;';
                        else echo 'background-color:#c3d5ff;';
                    }
                echo '">';
                    echo number_format($this->subs['presenza']['lista'][$tag]['arrotondato']/60,2,'.','');
                echo '</div>';

                ////////////////////////////

                echo '<div id="bgc_codici_'.$tag.'_'.$this->config['info']['IDcoll'].'_'.$this->config['info']['reparto'].'" style="text-align:center;height:'.$h2.';line-height:'.$h2.';vertical-align:middle;" data-std="'.($this->subs['presenza']['lista'][$tag]['oreSTD']*60).'" data-bgc="'.$this->subs['presenza']['lista'][$tag]['arrotondato'].'" data-reparto="'.$this->config['info']['reparto'].'" data-idcoll="'.$this->config['info']['IDcoll'].'" data-tag="'.$tag.'" >';
                    
                    if ( $this->subs['presenza']['lista'][$tag]['arrotondato']!=($this->subs['presenza']['lista'][$tag]['oreSTD']*60) ) {

                        $this->res[$tag]=array(
                            "delta"=>$this->subs['presenza']['lista'][$tag]['arrotondato']-($this->subs['presenza']['lista'][$tag]['oreSTD']*60),
                            "somma"=>0,
                            "tot"=>$this->totali
                        );

                        //riscrive timbrato e lavorato
                        $this->res[$tag]['tot']['TIM']['valore']=$this->subs['presenza']['lista'][$tag]['arrotondato'];
                        $this->res[$tag]['tot']['LAV']['valore']=$this->subs['presenza']['lista'][$tag]['arrotondato'];

                        $this->calcolaTot($tag);

                        echo '<div id="bgc_codici_div_'.$tag.'_'.$this->config['info']['IDcoll'].'_'.$this->config['info']['reparto'].'" style="cursor:pointer;height:'.$h2.';" >';
                            echo '<img id="bgc_codici_img_'.$tag.'_'.$this->config['info']['IDcoll'].'_'.$this->config['info']['reparto'].'" style="width:20px;height:20px;position: relative;top: 50%;transform: translate(0, -50%);" ';
                                if ($this->subs['presenza']['lista'][$tag]['stato']=='KO' || ( $this->subs['presenza']['lista'][$tag]['stato']=='ALL' && ($this->subs['presenza']['lista'][$tag]['actualBro']>0 || $this->subs['presenza']['lista'][$tag]['oreSTD']>0)) ) {
                                    echo 'src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/brogliaccio/img/Y.png" ';
                                }
                                else {
                                    if ( abs($this->res[$tag]['delta'])!=abs($this->res[$tag]['somma']) ) {
                                        echo 'src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/brogliaccio/img/X.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].bgcOpenCodici(\''.$tag.'\',\''.$this->config['info']['IDcoll'].'\',\''.$this->config['info']['reparto'].'\');"';
                                    }
                                    else {
                                        echo 'src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/brogliaccio/img/V.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].bgcOpenCodici(\''.$tag.'\',\''.$this->config['info']['IDcoll'].'\',\''.$this->config['info']['reparto'].'\');"';
                                    }
                                }
                            echo ' />';
                        echo '</div>';

                        echo '<div id="bgc_codici_index_'.$tag.'_'.$this->config['info']['IDcoll'].'_'.$this->config['info']['reparto'].'" style="width:100%;height:10px;text-align:center;">';
                            //echo '<img style="width:100%;height:100%;margin-top:-23%;border-bottom:4px solid white;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/brogliaccio/img/index.png" />';
                        echo '</div>';
                    }
                echo '</div>';

            echo '</td>';

        }

        while ($counter<31) {
            echo '<td style=""></td>';
            $counter++;
        }
    }

    function drawFoot() {

        echo '<div id="bgc_codici_foot_'.$this->config['info']['IDcoll'].'_'.$this->config['info']['reparto'].'" style="width:99.5%;text-align:center;border:2px solid #6cab27;margin-bottom: 3px;height: 50px;display:none;" >';
            echo '<div id="bgc_codici_footmain_'.$this->config['info']['IDcoll'].'_'.$this->config['info']['reparto'].'" style="display:inline-block;width:80%;vertical-align:top;height:100%;"></div>';
            echo '<div style="display:inline-block;width:20%;vertical-align:top;height:100%;">';
                echo '<button id="bgc_codici_ok" style="margin-left:20px;position:relative;top: 50%;position: relative;transform: translate(0px, -50%);" data-check="" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].bgcConfirmCodici();">OK</button>';
                echo '<button style="margin-left:20px;position:relative;top: 50%;position: relative;transform: translate(0px, -50%);" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].bgcResetCodici();">annulla</button>';
            echo '</div>';
        echo '</div>';

        /*echo '<div>';
            echo json_encode($this->dl['lista']);
        echo '</div>';*/

        //###############################################################
        //carica TOT in JS in riferimento al reparto ed a IDcoll
        echo '<script type="text/javascript">';
            echo 'var temp='.json_encode($this->res).';';
            echo "window['_nebulaApp_'+window._nebulaApp.getTagFunzione()].bgcLoadTotali(temp,'".$this->config['info']['IDcoll']."','".$this->config['info']['reparto']."');";
        echo '</script>';
        //###############################################################

    }

    function aggiornaDB($tag) {

        $obj=array();

        foreach ($this->res[$tag]['tot'] as $k=>$v) {
            if ($v['edit']==1) {
                $obj[$k]=$v['valore'];
            }
        }

        $arr=array(
            "reparto"=>$this->config['info']['reparto'],
            "coll"=>$this->config['info']['IDcoll'],
            "tag"=>$tag,
            "obj"=>json_encode($obj)
        );

        $this->galileo->clearQuery();
        $this->galileo->clearQueryOggetto('default','tempo');
        $this->galileo->executeInsert('tempo','TEMPO_dettaglio_bgc',$arr);
    }

    function calcolaTot($tag) {

        $temp=(array_key_exists($tag,$this->codiciDB))?$this->codiciDB[$tag]:false;

        //se ci sono delle registrazioni già impostate
        if ($temp) {

            $obj=json_decode($temp['obj'],true);

            foreach ($this->res[$tag]['tot'] as $k=>$v) {
                if ( array_key_exists($k,$obj) ) {
                    $this->res[$tag]['tot'][$k]['valore']=$obj[$k];
                }
            }
        }

        else {

            //in caso di straordinario pre imposta in base alle regole vigenti
            if ($this->res[$tag]['delta']>0) {
                if ($this->subs['presenza']['lista'][$tag]['tipoSTR']=='lav') $this->res[$tag]['tot']['SO']['valore']=$this->res[$tag]['delta'];
                elseif ($this->subs['presenza']['lista'][$tag]['tipoSTR']=='sab') $this->res[$tag]['tot']['SS']['valore']=$this->res[$tag]['delta'];
                elseif ($this->subs['presenza']['lista'][$tag]['tipoSTR']=='dom') $this->res[$tag]['tot']['SD']['valore']=$this->res[$tag]['delta'];

                /////////////////////
                //SCRIVI IL DB
                $this->aggiornaDB($tag);
                ////////////////////
            
            }

            elseif ($this->res[$tag]['delta']<0) {

                if ($this->dl['lista'][$tag]['info']['festa']==1) {
                    $this->res[$tag]['tot']['FS']['valore']=abs($this->res[$tag]['delta']);

                    /////////////////////
                    //SCRIVI IL DB
                    $this->aggiornaDB($tag);
                    ////////////////////
                }

                else {
                    if (isset($this->config['info']['eventi'])) {
                        if (array_key_exists($tag,$this->config['info']['eventi'])) {

                            foreach ($this->config['info']['eventi'][$tag] as $e) {
                                //può esserci un solo periodo al giorno (M=malattia , F=ferie)
                                if ($e['tipo']=='M') {
                                    $this->res[$tag]['tot']['ML']['valore']=abs($this->res[$tag]['delta']);
                                    /////////////////////
                                    //SCRIVI IL DB
                                    $this->aggiornaDB($tag);
                                    ////////////////////
                                }
                                elseif ($e['tipo']=='F') {
                                    //se il periodo è maggiore di un giorno
                                    if ($e['data_i']!=$e['data_f']) {
                                        $this->res[$tag]['tot']['F']['valore']=abs($this->res[$tag]['delta']);
                                        /////////////////////
                                        //SCRIVI IL DB
                                        $this->aggiornaDB($tag);
                                        ////////////////////
                                    }
                                }
                            }
                        }
                    }
                }
                
            }

        }

        //////////////////////////
        //somma
        if ($this->res[$tag]['delta']>0) $tempVerso='P';
        else $tempVerso='M';

        $this->res[$tag]['somma']=0;

        foreach ($this->res[$tag]['tot'] as $k=>$t) {

            if ($t['verso']=='') continue;

            if ($t['verso']==$tempVerso || $t['verso']=='T') {
                $this->res[$tag]['somma']+=$t['valore'];

                //###########################################
                $this->tot[$k]['valore']+=$t['valore'];
                
                if ($tempVerso=='M') {
                    $this->tot['LAV']['valore']+=$t['valore'];
                    //$this->res[$tag]['tot']['LAV']['valore']+=$t['valore'];
                }
                if ($tempVerso=='P') {
                    //$this->res[$tag]['tot']['LAV']['valore']-=$t['valore'];
                    //$this->res[$tag]['tot']['TIM']['valore']+=$t['valore'];
                    if ($k=='XXX') {
                        //$this->res[$tag]['tot']['LAV']['valore']-=$t['valore'];
                        //$this->res[$tag]['tot']['TIM']['valore']-=$t['valore'];
                        $this->tot['LAV']['valore']-=$t['valore'];
                        //$this->tot['TIM']['valore']-=$t['valore'];
                    }
                }
                
                //###########################################
            }
            //forza il valore a zero perché è sicuramente un errore
            //else $this->res[$tag]['tot'][$k]['valore']=0;

        }

    }

    //SOVRASCRIVE LA FUNZIONE DELLA CLASSE PARENT
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
                        echo '<td id="bgc_totaliOverall_'.$this->config['info']['IDcoll'].'_'.$this->config['info']['reparto'].'_'.$ktot.'" style="text-align:center;height:'.$h.'px;font-size:0.7em;" data-codice="'.$ktot.'" >';
                            echo number_format($t['valore']/60,2,'.','');
                        echo '</td>';
                    }
                echo '</tr>';

            echo '</table>';
        echo '</div>';
    }

    function export() {

        ob_start();
            $this->drawSubs();
        ob_end_clean();

        $txt="";
        $counter=0;

        foreach ($this->dl['lista'] as $tag=>$l) { 

            $counter++;

            $txt.=number_format($this->subs['presenza']['lista'][$tag]['arrotondato']/60,2,'.','').';';

        }

        while ($counter<31) {
            $txt.=';';
            $counter++;
        }

        foreach ($this->tot as $ktot=>$t) {
            $txt.=number_format($t['valore']/60,2,'.','').';';
        }

        $txt.="\n";

        return $txt;
    }

    function exportCodici() {

        $txt="";
        $counter=0;

        foreach ($this->res as $tag=>$t) {

            $counter++;

            if (array_key_exists('delta',$t)) {

                if ($t['delta']==0) $txt.=';';

                else {
                    $temp='';

                    if ($this->subs['presenza']['lista'][$tag]['stato']=='KO') $temp='ERR-';
                    elseif ($this->subs['presenza']['lista'][$tag]['stato']=='ALL' && ($this->subs['presenza']['lista'][$tag]['actualBro']>0 || $this->subs['presenza']['lista'][$tag]['oreSTD']>0) ) $temp='ERR-';

                    else {
                        foreach ($t['tot'] as $k=>$c) {
                            if ($c['verso']!='') {
                                if ($c['valore']!=0) {
                                    $temp.=$k.'-';
                                }
                            }
                        }
                    }

                    $txt.=substr($temp,0,-1).';';
                }
            }

            else $txt.=';';
        }

        while ($counter<31) {
            $txt.=';';
            $counter++;
        }

        foreach ($this->tot as $ktot=>$t) {
            $txt.=';';
        }

        $txt.="\n";

        return $txt;

    }


}
?>