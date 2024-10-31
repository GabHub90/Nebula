<?php

class Divo {
    
    /*$info=array(
        index:              ID HTML da attribuire ai DIV
        
        divs:               array(
                                ID: array(
                                        titolo:         titolo del DIV
                                        color:          colore della scritta
                                        check_img:      (0/1) se è presente l'indicazione dello stato
                                        stato:          codice stato (R=rosso , G=giallo , V=verde , Y=grigio, X=bianco) 
                                        codice:         contenuto HTML del DIV
                                        selected:       (1/0) selezionato per default
                                    )   
                            )
        
        htab                altezza del tab in pixel o percentuale (50px || 50%)
        minh:               altezza dei div in pixels o percentuale (50px || 50%)
        fixed:              1/0 minh diventa HEIGHT
    )*/
    protected $info=array();
    
    //CSS di DEFAULT di ogni TAB
    protected $tabCss=array(
        "font-weight"=>"bold",
        "font-size"=>"1em",
        "margin-left"=>"10px",
        "margin-top"=>"0px"
    );

    protected $chkimgCss=array(
        "width"=>"20px",
        "height"=>"20px",
        "top"=>"50%",
        "transform"=>"translate(0%,-50%)",
        "right"=>"0px"
    );

    protected $divLimit=30;

    //codice js aggiuntivo
    protected $js="";

    //colore select
    protected $bk='wheat';
    
    protected $tabs="";
    protected $divs="";
    
    function __construct($index,$htab,$minh,$fixed) {
        
        $this->info['index']=$index;
        $this->info['htab']=$htab;
        $this->info['minh']=$minh;
        $this->info['fixed']=$fixed;
        
        $this->info['divs']=array();
    }

    static function divoInit() {

        echo '<link href="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/divo/style.css" type="text/css" rel="stylesheet"/>';
        echo '<script type="text/javascript" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/divo/divo.js?v='.time().'" />';
    }
    
    function set_selected($index) {
        $this->clear_selected();
        $this->info['divs'][$index]['selected']=1;
    }
    
    function get_selected() {
        $ret=0;
        foreach ($this->info['divs'] as $k=>$d) {
            if ($d['selected']==1) $ret=$k;
        }
        
        return $ret;
    }
    
    function clear_selected() {
        foreach ($this->info['divs'] as $k=>$d) {
            $this->info['divs'][$k]['selected']=0;
        }
    }

    function setBk($colore) {
        $this->bk=$colore;
    }

    function setJS($txt) {
        $this->js=$txt;
    }
    
    function add_div($titolo,$color,$chk,$stato,$codice,$selected,$css) {
        
        $arr=array(
            "titolo"=>$titolo,
            "color"=>$color,
            "check_img"=>$chk,
            "stato"=>$stato,
            "codice"=>$codice,
            "selected"=>$selected,
            "tabCss"=>array()
        );
        
        if ($selected==1) $this->clear_selected();

        $arr['tabCss']=(count($css)>0)?$css:$this->tabCss;
        
        $this->info['divs'][]=$arr;
        
    }

    function setChkimgCss($a) {
        $this->chkimgCss=$a;
    }
    
    function build() {

        $c=count($this->info['divs']);

        if ($c==0) return;

        if ($c>$this->divLimit) {
            $temp=round($c/2)-1;
            $split=array($temp,$c);

            $temp1=preg_split('#(?<=\d)(?=[a-z%])#i', $this->info['minh']);
            $temp2=preg_split('#(?<=\d)(?=[a-z%])#i', $this->info['htab']);
            $this->info['minh']=''.((int)$temp1[0]-(int)$temp2[0]).$temp1[1];
        }
        else {
            $temp=$c-1;
            $split=array($c);
        }
        
        try {
            $tw=round(98/($temp+1),1);
        } catch (Exception $e) {
            return;
        }
        
        $tabs="";
        $divs="";

        $counter=-1;

        foreach ($split as $ksplit) {
        
            $tabs.='<div style="position:relative;width:100%;height:'.$this->info['htab'].';">';
            
                foreach ($this->info['divs'] as $k=>$d) {

                    if ($k<=$counter) continue;

                    $counter=$k;
                
                    $tabs.='<div id="divo_tab_'.$this->info['index'].'_'.$k.'" style="position:relative;display:inline-block;border:1px solid black;height:98%;width:'.$tw.'%;padding:2px;box-sizing:border-box;border-radius:20px 0px 0px 0px;" onclick="window._'.$this->info['index'].'_divo.preSel('.$k.');">';
                    
                        $tabs.='<div style="position:relative;overflow:hidden;color:'.$d['color'].';cursor:pointer;';
                            foreach ($d['tabCss'] as $ks=>$cs) {
                                $tabs.=$ks.':'.$cs.';';
                            }
                        $tabs.='">';
                            $tabs.=$d['titolo'];
                        $tabs.='</div>';
                        
                        if ($d['check_img']==1) {
                                
                            $s='';
                            if ($d['stato']=='') $s='X';
                            else $s=$d['stato'];

                            //codice stato (R=rosso , G=giallo , V=verde , Y=grigio)
                            $tabs.='<img id="divo_checkimg_'.$this->info['index'].'_'.$k.'" data-stato="'.$s.'" style="position:absolute;z-index:2;';
                            foreach ($this->chkimgCss as $kstyle=>$vstyle) {
                                $tabs.=$kstyle.':'.$vstyle.';';
                            }
                            $tabs.='" src="http://'.$_SERVER['SERVER_ADDR'].'/apps/gals/divo/img/stato_'.$s.'.png"/>';
                        }
                    
                    $tabs.='</div>';
                    
                    //$divs.='<div id="divo_div_'.$this->info['index'].'_'.$k.'" style="position:relative;left:0px;top:5px;width:97%;height:'.$this->info['perc_div'].'%;padding:2px;display:block;visibility:hidden;margin-bottom:3px;">';
                    //$h=($this->info['fixed'])?'height:'.$this->info['minh']:'min-height:'.$this->info['minh'];
                    //$divs.='<div id="divo_div_'.$this->info['index'].'_'.$k.'" style="position:relative;left:0px;top:0px;width:100%;'.$h.';display:block;visibility:hidden;margin-bottom:3px;">';
                    //$divs.='<div id="divo_div_'.$this->info['index'].'_'.$k.'" style="position:relative;left:0px;top:0px;width:100%;display:block;visibility:hidden;">';
                    $divs.='<div id="divo_div_'.$this->info['index'].'_'.$k.'" style="position:relative;left:0px;top:0px;width:100%;display:none;">';
                        $divs.=$d['codice'];
                    $divs.='</div>';

                    if ($k==$ksplit) break;
                }
            
            //$tabs.='<div style="clear:both;"></div>';
            $tabs.='</div>';

        }

        $this->tabs=$tabs;
        $this->divs=$divs;
    }
    
    function draw() {

        $c=count($this->info['divs']);

        if ($c==0) return "";
    
        //body
        echo '<div style="position:relative;width:100%;">';

            echo '<script type="text/javascript" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/divo/divo.js"></script>';

            echo '<script type="text/javascript">';
                echo 'window._'.$this->info['index'].'_divo=new divoObj(\''.$this->info['index'].'\','.$this->get_selected().');';
                    echo 'window._'.$this->info['index'].'_divo.setBk(\''.$this->bk.'\');';

                    echo $this->js;

                    echo 'window._'.$this->info['index'].'_divo.setDef();';
            echo '</script>';
            
            //tabs
            echo $this->tabs;
            //divs
            $h=($this->info['fixed'])?'height:'.$this->info['minh']:'min-height:'.$this->info['minh'];
            echo '<div style="position:relative;left:0px;top:1%;width:100%;padding:2px;box-sizing:border-box;'.$h.';';
                if($this->info['fixed']) echo 'overflow:scroll;overflow-x:hidden;';
            echo '">';
                echo $this->divs;
            echo '</div>';
            
        echo '</div>';
        
    }
}

?>